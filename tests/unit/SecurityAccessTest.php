<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;

final class SecurityAccessTest extends CIUnitTestCase
{
    use FeatureTestTrait, DatabaseTestTrait, CreateTestTablesTrait;

    protected $migrate = false;

    private function loginAs(string $role = 'admin'): void
    {
        $this->withSession(['user_id'=>1,'username'=>$role.'_user','role'=>$role,'logged_in'=>true]);
    }

    protected function setUp(): void { parent::setUp(); $this->createAllTables(); }
    protected function tearDown(): void { parent::tearDown(); session()->destroy(); }

    // Tanpa login
    public function testDashboardTanpaLogin(): void { $this->get('/dashboard')->assertRedirectTo('/login'); }
    public function testTransactionsTanpaLogin(): void { $this->get('/transactions')->assertRedirectTo('/login'); }
    public function testProductsTanpaLogin(): void { $this->get('/products')->assertRedirectTo('/login'); }
    public function testCustomersTanpaLogin(): void { $this->get('/customers')->assertRedirectTo('/login'); }
    public function testSuppliersTanpaLogin(): void { $this->get('/suppliers')->assertRedirectTo('/login'); }
    public function testReportsTanpaLogin(): void { $this->get('/reports')->assertRedirectTo('/login'); }

    public function testStoreProductTanpaLogin(): void
    {
        $this->post('/products/store',['product_type'=>'FOOD','name'=>'X','price'=>1000,'stock'=>10])
             ->assertRedirectTo('/login');
    }

    public function testStoreSupplierTanpaLogin(): void
    {
        $this->post('/suppliers/store',['name'=>'X'])->assertRedirectTo('/login');
    }

    // Kasir → Admin-only
    public function testKasirAksesProducts(): void { $this->loginAs('cashier'); $this->get('/products')->assertRedirectTo('/403'); }
    public function testKasirAksesSuppliers(): void { $this->loginAs('cashier'); $this->get('/suppliers')->assertRedirectTo('/403'); }
    public function testKasirAksesReports(): void { $this->loginAs('cashier'); $this->get('/reports')->assertRedirectTo('/403'); }
    public function testKasirStoreProduct(): void
    {
        $this->loginAs('cashier');
        $this->post('/products/store',['product_type'=>'FOOD','name'=>'X','price'=>1000,'stock'=>10])
             ->assertRedirectTo('/403');
    }
    public function testKasirStoreSupplier(): void
    {
        $this->loginAs('cashier');
        $this->post('/suppliers/store',['name'=>'X'])->assertRedirectTo('/403');
    }
    public function testKasirCancelTransaksi(): void
    {
        $this->loginAs('cashier');
        $this->get('/transactions/cancel/any-id')->assertRedirectTo('/403');
    }

    // Positive access
    public function testAdminBisaAksesSemua(): void
    {
        $this->loginAs('admin');
        $this->get('/dashboard')->assertOK();
        $this->get('/products')->assertOK();
        $this->get('/suppliers')->assertOK();
        $this->get('/reports')->assertOK();
        $this->get('/customers')->assertOK();
        $this->get('/transactions')->assertOK();
    }

    public function testKasirBisaAksesDashboardDanTransaksi(): void
    {
        $this->loginAs('cashier');
        $this->get('/dashboard')->assertOK();
        $this->get('/transactions')->assertOK();
        $this->get('/customers')->assertOK();
    }

    public function testHalamanLoginTanpaAuth(): void { $this->get('/login')->assertOK(); }
    public function testHalamanRegisterTanpaAuth(): void { $this->get('/register')->assertOK(); }

    public function testKasirEditCustomer(): void
    {
        $this->loginAs('cashier');
        $this->get('/customers/edit/ANY-ID')->assertRedirectTo('/403');
    }

    public function testKasirDestroyCustomer(): void
    {
        $this->loginAs('cashier');
        $this->get('/customers/destroy/ANY-ID')->assertRedirectTo('/403');
    }

    // ─────────────────────────────────────────
    // Security Testing (SQL Injection)
    // ─────────────────────────────────────────
    public function testSqlInjectionLoginBypass(): void
    {
        // Mencoba bypass login dengan input SQL Injection klasik: ' OR 1=1 --
        // Karena CI4 menggunakan Query Builder & Prepared Statements, 
        // input ini akan dianggap sebagai string literal biasa dan akan ditolak.
        $result = $this->post('/login/auth', [
            'username' => "' OR 1=1 --",
            'password' => 'password123'
        ]);

        // Ekspektasinya adalah gagal login dan dikembalikan ke halaman login
        $result->assertRedirectTo('/login');
    }
}

