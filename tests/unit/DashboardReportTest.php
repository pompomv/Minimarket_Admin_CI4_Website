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
 * Test Modul Dashboard & Laporan
 *
 * Mencakup TC-DASH-01 s/d TC-DASH-04
 * dan TC-RPT-01 s/d TC-RPT-04 dari dokumen SQA.
 */
final class DashboardReportTest extends CIUnitTestCase
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
            'name' => 'Pelanggan Dashboard Test',
        ]);

        $productModel = new ProductModel();
        $productModel->insert([
            'product_type' => 'FOOD',
            'name'         => 'Roti Test',
            'price'        => 10000.00,
            'stock'        => 5, // stok rendah (≤ 10)
            'category'     => 'Makanan',
        ]);
        $productModel->insert([
            'product_type' => 'BEVERAGE',
            'name'         => 'Air Mineral Test',
            'price'        => 5000.00,
            'stock'        => 50,
            'category'     => 'Minuman',
        ]);

        // Seed transaksi COMPLETED hari ini
        $txModel = new TransactionModel();
        $today   = date('Y-m-d H:i:s');

        $txModel->insert([
            'id'               => 'TRX-DASH-001',
            'transaction_date' => $today,
            'customer_id'      => 'CUST-001',
            'status'           => 'COMPLETED',
            'total_amount'     => 25000.00,
        ]);

        $txModel->insert([
            'id'               => 'TRX-DASH-002',
            'transaction_date' => $today,
            'customer_id'      => null,
            'status'           => 'COMPLETED',
            'total_amount'     => 15000.00,
        ]);

        // Transaksi PENDING
        $txModel->insert([
            'id'               => 'TRX-DASH-003',
            'transaction_date' => $today,
            'customer_id'      => 'CUST-001',
            'status'           => 'PENDING',
            'total_amount'     => 10000.00,
        ]);

        // Seed transaction details untuk report
        $detailModel = new TransactionDetailModel();
        $products    = $productModel->findAll();

        $detailModel->insert([
            'transaction_id' => 'TRX-DASH-001',
            'product_id'     => $products[0]['id'],
            'quantity'       => 2,
            'unit_price'     => 10000,
            'subtotal'       => 20000,
        ]);
        $detailModel->insert([
            'transaction_id' => 'TRX-DASH-001',
            'product_id'     => $products[1]['id'],
            'quantity'       => 1,
            'unit_price'     => 5000,
            'subtotal'       => 5000,
        ]);
        $detailModel->insert([
            'transaction_id' => 'TRX-DASH-002',
            'product_id'     => $products[1]['id'],
            'quantity'       => 3,
            'unit_price'     => 5000,
            'subtotal'       => 15000,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        session()->destroy();
    }

    // ═══════════════════════════════════════════════════════════════
    //  MODUL DASHBOARD
    // ═══════════════════════════════════════════════════════════════

    // TC-DASH-01: Admin bisa akses dashboard
    public function testAdminAksesDashboard(): void
    {
        $this->loginAs('admin');

        $result = $this->get('/dashboard');

        $result->assertOK();
        $result->assertSee('Dashboard');
    }

    // TC-DASH-02: Kasir bisa akses dashboard
    public function testKasirAksesDashboard(): void
    {
        $this->loginAs('cashier');

        $result = $this->get('/dashboard');

        $result->assertOK();
        $result->assertSee('Dashboard');
    }

    // TC-DASH-03: Dashboard tanpa login → redirect
    public function testDashboardTanpaLogin(): void
    {
        $result = $this->get('/dashboard');
        $result->assertRedirectTo('/login');
    }

    // TC-DASH-04: Halaman 403 bisa diakses
    public function testHalaman403(): void
    {
        $result = $this->get('/403');

        $result->assertOK();
        $result->assertSee('403');
    }

    // ═══════════════════════════════════════════════════════════════
    //  MODUL LAPORAN (REPORT)
    // ═══════════════════════════════════════════════════════════════

    // TC-RPT-01: Admin bisa akses laporan
    public function testAdminAksesLaporan(): void
    {
        $this->loginAs('admin');

        $result = $this->get('/reports');

        $result->assertOK();
        $result->assertSee('Laporan');
    }

    // TC-RPT-02: Kasir tidak bisa akses laporan
    public function testKasirTidakBisaAksesLaporan(): void
    {
        $this->loginAs('cashier');

        $result = $this->get('/reports');
        $result->assertRedirectTo('/403');
    }

    // TC-RPT-03: Laporan dengan filter tanggal
    public function testLaporanDenganFilterTanggal(): void
    {
        $this->loginAs('admin');

        $startDate = date('Y-m-01');
        $endDate   = date('Y-m-d');

        $result = $this->get("/reports?start_date={$startDate}&end_date={$endDate}");

        $result->assertOK();
        $result->assertSee('Laporan');
    }

    // TC-RPT-04: Laporan tanpa transaksi (range kosong)
    public function testLaporanTanpaTransaksi(): void
    {
        $this->loginAs('admin');

        // Pakai tanggal di masa depan agar tidak ada data
        $result = $this->get('/reports?start_date=2099-01-01&end_date=2099-12-31');

        $result->assertOK();
    }

    // TC-RPT-05: Laporan tanpa login → redirect
    public function testLaporanTanpaLogin(): void
    {
        $result = $this->get('/reports');
        $result->assertRedirectTo('/login');
    }
}



