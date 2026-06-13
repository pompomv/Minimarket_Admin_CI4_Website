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
 * Test Modul Transaksi (POS) + Keamanan Akses
 *
 * Mencakup TC-TRX-01 s/d TC-TRX-05, TC-TRX-07, TC-TRX-09
 * dan TC-SEC-01, TC-SEC-03 dari dokumen SQA.
 */
final class TransactionControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use CreateTestTablesTrait;

    protected $migrate   = false;
    protected $namespace = null;

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
        $customerModel->insert([
            'id'   => 'CUST-001',
            'name' => 'Pelanggan Test',
        ]);

        $productModel = new ProductModel();
        $productModel->insert([
            'product_type' => 'FOOD',
            'name'         => 'Roti Tawar',
            'price'        => 15000.00,
            'stock'        => 20,
            'category'     => 'Makanan',
        ]);
        $productModel->insert([
            'product_type' => 'BEVERAGE',
            'name'         => 'Susu Kotak',
            'price'        => 8000.00,
            'stock'        => 30,
            'category'     => 'Minuman',
        ]);
        $productModel->insert([
            'product_type' => 'FOOD',
            'name'         => 'Kerupuk',
            'price'        => 5000.00,
            'stock'        => 2,  // stok sedikit
            'category'     => 'Snack',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        session()->destroy();
    }

    // ═════════════════════════════════════════════════════════════
    // TC-SEC-01: Akses tanpa login → redirect ke /login
    // ═════════════════════════════════════════════════════════════
    public function testAksesTransaksiTanpaLogin(): void
    {
        $result = $this->get('/transactions');
        $result->assertRedirectTo('/login');
    }

    // ═════════════════════════════════════════════════════════════
    // TC-SEC-03: Kasir akses halaman admin → redirect /403
    // ═════════════════════════════════════════════════════════════
    public function testKasirAksesLaporan(): void
    {
        $this->loginAs('cashier');

        $result = $this->get('/reports');
        $result->assertRedirectTo('/403');
    }

    public function testKasirAksesSupplier(): void
    {
        $this->loginAs('cashier');

        $result = $this->get('/suppliers');
        $result->assertRedirectTo('/403');
    }

    // ═════════════════════════════════════════════════════════════
    // TC-TRX-01: Buat transaksi berhasil
    // ═════════════════════════════════════════════════════════════
    public function testBuatTransaksiBerhasil(): void
    {
        $this->loginAs('cashier');

        $productModel = new ProductModel();
        $products     = $productModel->findAll();
        $pid1         = $products[0]['id']; // Roti Tawar
        $pid2         = $products[1]['id']; // Susu Kotak

        $stockBefore1 = (int) $products[0]['stock']; // 20
        $stockBefore2 = (int) $products[1]['stock']; // 30

        $result = $this->post('/transactions/store', [
            'customer_id' => 'CUST-001',
            'notes'       => 'Test transaksi',
            'product_id'  => [$pid1, $pid2],
            'quantity'    => [2, 3], // Roti ×2, Susu ×3
        ]);

        // Harus redirect ke detail transaksi
        $result->assertStatus(302);

        // Verifikasi stok berkurang
        $updatedProd1 = $productModel->find($pid1);
        $updatedProd2 = $productModel->find($pid2);

        $this->assertEquals($stockBefore1 - 2, (int) $updatedProd1['stock']);
        $this->assertEquals($stockBefore2 - 3, (int) $updatedProd2['stock']);

        // Verifikasi transaksi tersimpan di DB
        $txModel      = new TransactionModel();
        $transactions = $txModel->findAll();
        $this->assertCount(1, $transactions);

        $tx = $transactions[0];
        $this->assertEquals('COMPLETED', $tx['status']);
        $this->assertEquals('CUST-001', $tx['customer_id']);

        // Verifikasi total = (15000×2) + (8000×3) = 54000
        $expectedTotal = (15000 * 2) + (8000 * 3);
        $this->assertEquals($expectedTotal, (float) $tx['total_amount']);
    }

    // ═════════════════════════════════════════════════════════════
    // TC-TRX-02: Transaksi tanpa produk (qty semua 0)
    // ═════════════════════════════════════════════════════════════
    public function testTransaksiTanpaProduk(): void
    {
        $this->loginAs('cashier');

        // Kirim tanpa product_id[]
        $result = $this->post('/transactions/store', [
            'customer_id' => 'CUST-001',
            'notes'       => '',
        ]);

        // Harus redirect back dengan error
        $result->assertStatus(302);

        // Tidak ada transaksi tersimpan
        $txModel = new TransactionModel();
        $this->assertEquals(0, $txModel->countAll());
    }

    // ═════════════════════════════════════════════════════════════
    // TC-TRX-03: Stok tidak cukup
    // ═════════════════════════════════════════════════════════════
    public function testTransaksiStokTidakCukup(): void
    {
        $this->loginAs('cashier');

        $productModel = new ProductModel();
        $kerupuk      = $productModel->where('name', 'Kerupuk')->first();
        $pid           = $kerupuk['id'];
        $stockBefore   = (int) $kerupuk['stock']; // hanya 2

        $result = $this->post('/transactions/store', [
            'customer_id' => 'CUST-001',
            'product_id'  => [$pid],
            'quantity'    => [10], // mau beli 10, stok cuma 2
        ]);

        // Harus redirect back (rollback)
        $result->assertStatus(302);

        // Stok TIDAK berubah
        $kerupukAfter = $productModel->find($pid);
        $this->assertEquals($stockBefore, (int) $kerupukAfter['stock']);
    }

    // ═════════════════════════════════════════════════════════════
    // TC-TRX-04: Transaksi walk-in (tanpa pelanggan)
    // ═════════════════════════════════════════════════════════════
    public function testTransaksiWalkin(): void
    {
        $this->loginAs('cashier');

        $productModel = new ProductModel();
        $product      = $productModel->where('name', 'Roti Tawar')->first();

        $result = $this->post('/transactions/store', [
            'customer_id' => '',  // walk-in, tanpa pelanggan
            'notes'       => 'Pembeli umum',
            'product_id'  => [$product['id']],
            'quantity'    => [1],
        ]);

        $result->assertStatus(302);

        $txModel = new TransactionModel();
        $tx      = $txModel->first();

        $this->assertNotNull($tx);
        $this->assertNull($tx['customer_id']); // customer_id = NULL
        $this->assertEquals('COMPLETED', $tx['status']);
    }

    // ═════════════════════════════════════════════════════════════
    // TC-TRX-05: Lihat daftar transaksi
    // ═════════════════════════════════════════════════════════════
    public function testLihatDaftarTransaksi(): void
    {
        $this->loginAs('cashier');

        $result = $this->get('/transactions');

        $result->assertOK();
        $result->assertSee('Daftar Transaksi');
    }

    // ═════════════════════════════════════════════════════════════
    // TC-TRX-07: Admin batalkan transaksi PENDING → stok kembali
    // ═════════════════════════════════════════════════════════════
    public function testAdminBatalkanTransaksiPending(): void
    {
        $this->loginAs('admin');

        // Buat transaksi PENDING secara manual
        $productModel = new ProductModel();
        $product      = $productModel->where('name', 'Susu Kotak')->first();
        $pid           = $product['id'];
        $stockBefore   = (int) $product['stock']; // 30

        helper('uuid');
        $txId = generate_uuid();

        $txModel = new TransactionModel();
        $txModel->insert([
            'id'               => $txId,
            'transaction_date' => date('Y-m-d H:i:s'),
            'customer_id'      => 'CUST-001',
            'status'           => 'PENDING',
            'total_amount'     => 16000,
        ]);

        $detailModel = new TransactionDetailModel();
        $detailModel->insert([
            'transaction_id' => $txId,
            'product_id'     => $pid,
            'quantity'       => 2,
            'unit_price'     => 8000,
            'subtotal'       => 16000,
        ]);

        // Kurangi stok manual (simulasi flow asli)
        $productModel->update($pid, ['stock' => $stockBefore - 2]);

        // Cancel
        $result = $this->get("/transactions/cancel/{$txId}");
        $result->assertRedirectTo('/transactions');

        // Verifikasi status berubah ke CANCELLED
        $tx = $txModel->find($txId);
        $this->assertEquals('CANCELLED', $tx['status']);

        // Verifikasi stok dikembalikan
        $productAfter = $productModel->find($pid);
        $this->assertEquals($stockBefore, (int) $productAfter['stock']);
    }

    // ═════════════════════════════════════════════════════════════
    // TC-TRX-09: Kasir tidak bisa batalkan transaksi
    // ═════════════════════════════════════════════════════════════
    public function testKasirTidakBisaCancelTransaksi(): void
    {
        $this->loginAs('cashier');

        $result = $this->get('/transactions/cancel/some-tx-id');

        // RoleFilter harus redirect ke /403
        $result->assertRedirectTo('/403');
    }
}



