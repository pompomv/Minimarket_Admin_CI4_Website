<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use App\Models\ProductModel;
use App\Models\CustomerModel;

/**
 * Test Detail & Pembatalan Transaksi
 */
final class TransactionDetailTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use CreateTestTablesTrait;

    protected $migrate = false;

    private function loginAs(string $role = 'admin'): void
    {
        $this->withSession([
            'user_id'   => 1,
            'username'  => $role . '_user',
            'role'      => $role,
            'logged_in' => true,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAllTables();

        // Seed data
        $customerModel = new CustomerModel();
        $customerModel->insert(['id' => 'CUST-DTL-001', 'name' => 'Pelanggan Detail Test', 'phone' => '08112233445']);

        $productModel = new ProductModel();
        $productModel->insert(['product_type' => 'FOOD', 'name' => 'Roti Detail Test', 'price' => 12000.00, 'stock' => 50, 'category' => 'Makanan']);
        $productModel->insert(['product_type' => 'BEVERAGE', 'name' => 'Jus Detail Test', 'price' => 8000.00, 'stock' => 30, 'category' => 'Minuman']);

        $txModel = new TransactionModel();
        $txModel->insert(['id' => 'TRX-DTL-COMP', 'transaction_date' => date('Y-m-d H:i:s'), 'customer_id' => 'CUST-DTL-001', 'status' => 'COMPLETED', 'total_amount' => 44000.00]);
        $txModel->insert(['id' => 'TRX-DTL-PEND', 'transaction_date' => date('Y-m-d H:i:s'), 'customer_id' => 'CUST-DTL-001', 'status' => 'PENDING', 'total_amount' => 24000.00]);
        $txModel->insert(['id' => 'TRX-DTL-CANC', 'transaction_date' => date('Y-m-d H:i:s'), 'customer_id' => null, 'status' => 'CANCELLED', 'total_amount' => 0]);

        $products     = $productModel->findAll();
        $detailModel  = new TransactionDetailModel();
        $detailModel->insert(['transaction_id' => 'TRX-DTL-COMP', 'product_id' => $products[0]['id'], 'quantity' => 2, 'unit_price' => 12000, 'subtotal' => 24000]);
        $detailModel->insert(['transaction_id' => 'TRX-DTL-COMP', 'product_id' => $products[1]['id'], 'quantity' => 2, 'unit_price' => 8000, 'subtotal' => 16000]);
        $detailModel->insert(['transaction_id' => 'TRX-DTL-PEND', 'product_id' => $products[0]['id'], 'quantity' => 2, 'unit_price' => 12000, 'subtotal' => 24000]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        session()->destroy();
    }

    public function testLihatDetailTransaksiAdmin(): void
    {
        $this->loginAs('admin');
        $result = $this->get('/transactions/detail/TRX-DTL-COMP');
        $result->assertOK();
    }

    public function testLihatDetailTransaksiCashier(): void
    {
        $this->loginAs('cashier');
        $result = $this->get('/transactions/detail/TRX-DTL-COMP');
        $result->assertOK();
    }

    public function testDetailTransaksiTidakDitemukan(): void
    {
        $this->loginAs('admin');
        $result = $this->get('/transactions/detail/TRX-TIDAK-ADA');
        $result->assertRedirectTo('/transactions');
    }

    public function testDetailTanpaLogin(): void
    {
        $result = $this->get('/transactions/detail/TRX-DTL-COMP');
        $result->assertRedirectTo('/login');
    }

    public function testCancelTransaksiPendingBerhasil(): void
    {
        $this->loginAs('admin');

        $productModel = new ProductModel();
        $products     = $productModel->findAll();
        $pid          = $products[0]['id'];
        $stockBefore  = (int) $products[0]['stock'];
        $productModel->update($pid, ['stock' => $stockBefore - 2]);

        $result = $this->get('/transactions/cancel/TRX-DTL-PEND');
        $result->assertRedirectTo('/transactions');

        $txModel = new TransactionModel();
        $tx      = $txModel->find('TRX-DTL-PEND');
        $this->assertEquals('CANCELLED', $tx['status']);

        $productAfter = $productModel->find($pid);
        $this->assertEquals($stockBefore, (int) $productAfter['stock']);
    }

    public function testCancelTransaksiCompletedGagal(): void
    {
        $this->loginAs('admin');
        $result = $this->get('/transactions/cancel/TRX-DTL-COMP');
        $result->assertRedirectTo('/transactions');

        $txModel = new TransactionModel();
        $tx      = $txModel->find('TRX-DTL-COMP');
        $this->assertEquals('COMPLETED', $tx['status']);
    }

    public function testCancelTransaksiTidakAda(): void
    {
        $this->loginAs('admin');
        $result = $this->get('/transactions/cancel/TRX-TIDAK-ADA-CANCEL');
        $result->assertRedirectTo('/transactions');
    }

    public function testKasirTidakBisaCancel(): void
    {
        $this->loginAs('cashier');
        $result = $this->get('/transactions/cancel/TRX-DTL-PEND');
        $result->assertRedirectTo('/403');
    }

    public function testHalamanBuatTransaksiBaru(): void
    {
        $this->loginAs('cashier');
        $result = $this->get('/transactions/create');
        $result->assertOK();
    }
}

