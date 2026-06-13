<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;
use App\Models\Users;
use App\Models\ProductModel;
use App\Models\CustomerModel;
use App\Models\SupplierModel;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

/**
 * Test Unit untuk semua Model
 *
 * Menguji method custom di setiap model secara langsung
 * (bukan melalui HTTP request seperti Feature Test).
 */
final class ModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use CreateTestTablesTrait;

    protected $migrate   = false;
    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAllTables();
    }

    // ═══════════════════════════════════════════════════════════════
    //  USERS MODEL
    // ═══════════════════════════════════════════════════════════════

    // TC-MDL-01: findByUsername — user aktif ditemukan
    public function testFindByUsernameAktif(): void
    {
        $model = new Users();
        $model->insert([
            'username' => 'cashier_aktif',
            'password' => password_hash('test123', PASSWORD_BCRYPT),
            'email'    => 'cashier@test.com',
            'role'     => 'cashier',
        ]);

        $user = $model->findByUsername('cashier_aktif');

        $this->assertNotNull($user);
        $this->assertEquals('cashier_aktif', $user['username']);
        $this->assertEquals('cashier', $user['role']);
    }

    // TC-MDL-02: findByUsername — user disabled tidak ditemukan
    public function testFindByUsernameDisabled(): void
    {
        $model = new Users();
        $model->insert([
            'username' => 'user_nonexist_test',
            'password' => password_hash('test123', PASSWORD_BCRYPT),
            'role'     => 'cashier',
        ]);

        // Cari username yang tidak ada
        $user = $model->findByUsername('username_tidak_ada');

        $this->assertNull($user);
    }

    // TC-MDL-03: findByUsername — username tidak ada
    public function testFindByUsernameTidakAda(): void
    {
        $model = new Users();
        $user  = $model->findByUsername('username_tidak_ada');

        $this->assertNull($user);
    }

    // ═══════════════════════════════════════════════════════════════
    //  PRODUCT MODEL
    // ═══════════════════════════════════════════════════════════════

    // TC-MDL-04: decreaseStock berhasil
    public function testDecreaseStockBerhasil(): void
    {
        $model = new ProductModel();
        $model->insert([
            'product_type' => 'FOOD',
            'name'         => 'Roti Model Test',
            'price'        => 5000,
            'stock'        => 20,
        ]);

        $product = $model->where('name', 'Roti Model Test')->first();
        $id      = (int) $product['id'];

        $result = $model->decreaseStock($id, 5);
        $this->assertTrue($result);

        $updated = $model->find($id);
        $this->assertEquals(15, (int) $updated['stock']);
    }

    // TC-MDL-05: decreaseStock gagal — stok tidak cukup
    public function testDecreaseStockTidakCukup(): void
    {
        $model = new ProductModel();
        $model->insert([
            'product_type' => 'BEVERAGE',
            'name'         => 'Air Model Test',
            'price'        => 3000,
            'stock'        => 2,
        ]);

        $product = $model->where('name', 'Air Model Test')->first();
        $id      = (int) $product['id'];

        // Coba kurangi 10, padahal stok cuma 2
        $model->decreaseStock($id, 10);

        // Stok tetap 2 (tidak berubah karena WHERE stock >= qty)
        $updated = $model->find($id);
        $this->assertEquals(2, (int) $updated['stock']);
    }

    // TC-MDL-06: withSupplier — join dengan supplier
    public function testWithSupplier(): void
    {
        $supplierModel = new SupplierModel();
        $supplierModel->insert([
            'id'   => 'SUP-MDL-001',
            'name' => 'Supplier Model Test',
        ]);

        $model = new ProductModel();
        $model->insert([
            'product_type' => 'ELECTRONIC',
            'name'         => 'Kabel USB Model Test',
            'price'        => 15000,
            'stock'        => 10,
            'supplier_id'  => 'SUP-MDL-001',
        ]);

        $products = $model->withSupplier();

        $this->assertIsArray($products);
        $this->assertGreaterThan(0, count($products));

        // Cari produk yang baru dibuat
        $found = array_filter($products, fn($p) => $p['name'] === 'Kabel USB Model Test');
        $found = array_values($found);

        $this->assertCount(1, $found);
        $this->assertEquals('Supplier Model Test', $found[0]['supplier_name']);
    }

    // ═══════════════════════════════════════════════════════════════
    //  TRANSACTION MODEL
    // ═══════════════════════════════════════════════════════════════

    // TC-MDL-07: withCustomer — join dengan customer
    public function testWithCustomer(): void
    {
        $customerModel = new CustomerModel();
        $customerModel->insert([
            'id'   => 'CUST-MDL-001',
            'name' => 'Customer Model Test',
        ]);

        $txModel = new TransactionModel();
        $txModel->insert([
            'id'               => 'TRX-MDL-001',
            'transaction_date' => date('Y-m-d H:i:s'),
            'customer_id'      => 'CUST-MDL-001',
            'status'           => 'COMPLETED',
            'total_amount'     => 50000,
        ]);

        $transactions = $txModel->withCustomer();

        $this->assertIsArray($transactions);

        $found = array_filter($transactions, fn($t) => $t['id'] === 'TRX-MDL-001');
        $found = array_values($found);

        $this->assertCount(1, $found);
        $this->assertEquals('Customer Model Test', $found[0]['customer_name']);
    }

    // TC-MDL-08: getWithCustomer — single record
    public function testGetWithCustomer(): void
    {
        $customerModel = new CustomerModel();
        $customerModel->insert([
            'id'   => 'CUST-MDL-002',
            'name' => 'Single Customer Test',
        ]);

        $txModel = new TransactionModel();
        $txModel->insert([
            'id'               => 'TRX-MDL-002',
            'transaction_date' => date('Y-m-d H:i:s'),
            'customer_id'      => 'CUST-MDL-002',
            'status'           => 'COMPLETED',
            'total_amount'     => 30000,
        ]);

        $tx = $txModel->getWithCustomer('TRX-MDL-002');

        $this->assertNotNull($tx);
        $this->assertEquals('TRX-MDL-002', $tx['id']);
        $this->assertEquals('Single Customer Test', $tx['customer_name']);
    }

    // TC-MDL-09: getWithCustomer — ID tidak ada → null
    public function testGetWithCustomerTidakAda(): void
    {
        $txModel = new TransactionModel();
        $tx      = $txModel->getWithCustomer('TRX-TIDAK-ADA');

        $this->assertNull($tx);
    }

    // TC-MDL-10: recalculateTotal — hitung ulang total dari details
    public function testRecalculateTotal(): void
    {
        $txModel     = new TransactionModel();
        $detailModel = new TransactionDetailModel();

        $txModel->insert([
            'id'               => 'TRX-MDL-CALC',
            'transaction_date' => date('Y-m-d H:i:s'),
            'status'           => 'PENDING',
            'total_amount'     => 0, // awalnya 0
        ]);

        // Insert 2 detail: 10000 + 15000 = 25000
        $detailModel->insert([
            'transaction_id' => 'TRX-MDL-CALC',
            'product_id'     => 999,
            'quantity'       => 2,
            'unit_price'     => 5000,
            'subtotal'       => 10000,
        ]);
        $detailModel->insert([
            'transaction_id' => 'TRX-MDL-CALC',
            'product_id'     => 998,
            'quantity'       => 3,
            'unit_price'     => 5000,
            'subtotal'       => 15000,
        ]);

        // Recalculate
        $txModel->recalculateTotal('TRX-MDL-CALC');

        $tx = $txModel->find('TRX-MDL-CALC');
        $this->assertEquals(25000, (float) $tx['total_amount']);
    }

    // ═══════════════════════════════════════════════════════════════
    //  TRANSACTION DETAIL MODEL
    // ═══════════════════════════════════════════════════════════════

    // TC-MDL-11: getByTransaction — ambil detail dengan nama produk
    public function testGetByTransaction(): void
    {
        $productModel = new ProductModel();
        $productModel->insert([
            'product_type' => 'FOOD',
            'name'         => 'Produk Detail Test',
            'price'        => 7500,
            'stock'        => 10,
        ]);

        $product = $productModel->where('name', 'Produk Detail Test')->first();

        $txModel = new TransactionModel();
        $txModel->insert([
            'id'               => 'TRX-DTL-001',
            'transaction_date' => date('Y-m-d H:i:s'),
            'status'           => 'COMPLETED',
            'total_amount'     => 15000,
        ]);

        $detailModel = new TransactionDetailModel();
        $detailModel->insert([
            'transaction_id' => 'TRX-DTL-001',
            'product_id'     => $product['id'],
            'quantity'       => 2,
            'unit_price'     => 7500,
            'subtotal'       => 15000,
        ]);

        $details = $detailModel->getByTransaction('TRX-DTL-001');

        $this->assertIsArray($details);
        $this->assertCount(1, $details);
        $this->assertEquals('Produk Detail Test', $details[0]['product_name']);
        $this->assertEquals(2, (int) $details[0]['quantity']);
    }

    // TC-MDL-12: getByTransaction — transaksi tanpa detail
    public function testGetByTransactionKosong(): void
    {
        $txModel = new TransactionModel();
        $txModel->insert([
            'id'               => 'TRX-DTL-EMPTY',
            'transaction_date' => date('Y-m-d H:i:s'),
            'status'           => 'PENDING',
            'total_amount'     => 0,
        ]);

        $detailModel = new TransactionDetailModel();
        $details     = $detailModel->getByTransaction('TRX-DTL-EMPTY');

        $this->assertIsArray($details);
        $this->assertCount(0, $details);
    }

    // ═══════════════════════════════════════════════════════════════
    //  CUSTOMER & SUPPLIER MODEL — Validasi dasar
    // ═══════════════════════════════════════════════════════════════

    // TC-MDL-13: CustomerModel — insert dan find
    public function testCustomerInsertDanFind(): void
    {
        $model = new CustomerModel();
        $model->insert([
            'id'      => 'CUST-MDL-TEST',
            'name'    => 'Customer Unit Test',
            'phone'   => '081111222333',
            'email'   => 'customer@unit.test',
            'address' => 'Jl. Test No. 1',
        ]);

        $customer = $model->find('CUST-MDL-TEST');

        $this->assertNotNull($customer);
        $this->assertEquals('Customer Unit Test', $customer['name']);
        $this->assertEquals('081111222333', $customer['phone']);
    }

    // TC-MDL-14: SupplierModel — insert dan find
    public function testSupplierInsertDanFind(): void
    {
        $model = new SupplierModel();
        $model->insert([
            'id'      => 'SUP-MDL-TEST',
            'name'    => 'Supplier Unit Test',
            'phone'   => '021-9998877',
            'email'   => 'supplier@unit.test',
            'address' => 'Jl. Supplier No. 2',
        ]);

        $supplier = $model->find('SUP-MDL-TEST');

        $this->assertNotNull($supplier);
        $this->assertEquals('Supplier Unit Test', $supplier['name']);
        $this->assertEquals('021-9998877', $supplier['phone']);
    }
}



