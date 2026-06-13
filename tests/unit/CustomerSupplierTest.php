<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;
use App\Models\CustomerModel;
use App\Models\SupplierModel;

/**
 * Test Modul Pelanggan & Supplier (CRUD)
 *
 * Mencakup TC-CUST-01 s/d TC-CUST-06
 * dan TC-SUP-01 s/d TC-SUP-06 dari dokumen SQA.
 */
final class CustomerSupplierTest extends CIUnitTestCase
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

        // Seed customer
        $customerModel = new CustomerModel();
        $customerModel->insert([
            'id'      => 'CUST-001',
            'name'    => 'Budi Santoso',
            'phone'   => '081234567890',
            'email'   => 'budi@email.com',
            'address' => 'Jl. Merdeka No. 10',
        ]);

        // Seed supplier
        $supplierModel = new SupplierModel();
        $supplierModel->insert([
            'id'      => 'SUP-001',
            'name'    => 'PT Distributor Jaya',
            'phone'   => '021-5551234',
            'email'   => 'info@distributorjaya.com',
            'address' => 'Jl. Industri No. 5',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        session()->destroy();
    }

    // ═══════════════════════════════════════════════════════════════
    //  MODUL PELANGGAN (CUSTOMER)
    // ═══════════════════════════════════════════════════════════════

    // TC-CUST-01: Lihat daftar pelanggan
    public function testLihatDaftarPelanggan(): void
    {
        $this->loginAs('admin');

        $result = $this->get('/customers');
        $result->assertOK();
        $result->assertSee('Budi Santoso');
    }

    // TC-CUST-02: Tambah pelanggan berhasil
    public function testTambahPelangganBerhasil(): void
    {
        $this->loginAs('admin');

        $result = $this->post('/customers/store', [
            'name'    => 'Siti Aminah',
            'phone'   => '081298765432',
            'email'   => 'siti@email.com',
            'address' => 'Jl. Sudirman No. 20',
        ]);

        $result->assertRedirectTo('/customers');

        $model    = new CustomerModel();
        $customer = $model->where('name', 'Siti Aminah')->first();

        $this->assertNotNull($customer);
        $this->assertEquals('081298765432', $customer['phone']);
    }

    // TC-CUST-03: Tambah pelanggan gagal — nama kosong
    public function testTambahPelangganGagalNamaKosong(): void
    {
        $this->loginAs('admin');

        $result = $this->post('/customers/store', [
            'name'  => '', // required, kosong
            'phone' => '08123',
        ]);

        $result->assertStatus(302);

        // Pelanggan baru tidak tersimpan
        $model = new CustomerModel();
        $count = $model->countAll();
        $this->assertEquals(1, $count); // hanya seed awal
    }

    // TC-CUST-04: Edit pelanggan berhasil
    public function testEditPelangganBerhasil(): void
    {
        $this->loginAs('admin');

        $result = $this->post('/customers/update/CUST-001', [
            'name'    => 'Budi Santoso Updated',
            'phone'   => '089999999999',
            'email'   => 'budi.new@email.com',
            'address' => 'Jl. Baru No. 1',
        ]);

        $result->assertRedirectTo('/customers');

        $model   = new CustomerModel();
        $updated = $model->find('CUST-001');
        $this->assertEquals('Budi Santoso Updated', $updated['name']);
        $this->assertEquals('089999999999', $updated['phone']);
    }

    // TC-CUST-05: Hapus pelanggan
    public function testHapusPelanggan(): void
    {
        $this->loginAs('admin');

        $model       = new CustomerModel();
        $countBefore = $model->countAll();

        $result = $this->get('/customers/destroy/CUST-001');
        $result->assertRedirectTo('/customers');

        $countAfter = $model->countAll();
        $this->assertEquals($countBefore - 1, $countAfter);
    }

    // TC-CUST-06: Kasir bisa lihat daftar pelanggan
    public function testKasirBisaLihatPelanggan(): void
    {
        $this->loginAs('cashier');

        $result = $this->get('/customers');
        $result->assertOK();
        $result->assertSee('Budi Santoso');
    }

    // TC-CUST-07: Kasir tidak bisa edit pelanggan
    public function testKasirTidakBisaEditPelanggan(): void
    {
        $this->loginAs('cashier');

        $result = $this->get('/customers/edit/CUST-001');
        $result->assertRedirectTo('/403');
    }

    // TC-CUST-08: Kasir tidak bisa hapus pelanggan
    public function testKasirTidakBisaHapusPelanggan(): void
    {
        $this->loginAs('cashier');

        $result = $this->get('/customers/destroy/CUST-001');
        $result->assertRedirectTo('/403');
    }

    // ═══════════════════════════════════════════════════════════════
    //  MODUL SUPPLIER
    // ═══════════════════════════════════════════════════════════════

    // TC-SUP-01: Lihat daftar supplier (Admin)
    public function testLihatDaftarSupplier(): void
    {
        $this->loginAs('admin');

        $result = $this->get('/suppliers');
        $result->assertOK();
        $result->assertSee('PT Distributor Jaya');
    }

    // TC-SUP-02: Tambah supplier berhasil
    public function testTambahSupplierBerhasil(): void
    {
        $this->loginAs('admin');

        $result = $this->post('/suppliers/store', [
            'name'    => 'CV Maju Bersama',
            'phone'   => '021-7778899',
            'email'   => 'maju@supplier.com',
            'address' => 'Jl. Raya Bogor KM 30',
        ]);

        $result->assertRedirectTo('/suppliers');

        $model    = new SupplierModel();
        $supplier = $model->where('name', 'CV Maju Bersama')->first();

        $this->assertNotNull($supplier);
        $this->assertEquals('021-7778899', $supplier['phone']);
    }

    // TC-SUP-03: Tambah supplier gagal — nama kosong
    public function testTambahSupplierGagalNamaKosong(): void
    {
        $this->loginAs('admin');

        $result = $this->post('/suppliers/store', [
            'name' => '', // required, kosong
        ]);

        $result->assertStatus(302);

        $model = new SupplierModel();
        $count = $model->countAll();
        $this->assertEquals(1, $count); // hanya seed awal
    }

    // TC-SUP-04: Edit supplier berhasil
    public function testEditSupplierBerhasil(): void
    {
        $this->loginAs('admin');

        $result = $this->post('/suppliers/update/SUP-001', [
            'name'    => 'PT Distributor Jaya Updated',
            'phone'   => '021-1112233',
            'email'   => 'updated@distributorjaya.com',
            'address' => 'Jl. Industri No. 10',
        ]);

        $result->assertRedirectTo('/suppliers');

        $model   = new SupplierModel();
        $updated = $model->find('SUP-001');
        $this->assertEquals('PT Distributor Jaya Updated', $updated['name']);
    }

    // TC-SUP-05: Hapus supplier
    public function testHapusSupplier(): void
    {
        $this->loginAs('admin');

        $model       = new SupplierModel();
        $countBefore = $model->countAll();

        $result = $this->get('/suppliers/destroy/SUP-001');
        $result->assertRedirectTo('/suppliers');

        $countAfter = $model->countAll();
        $this->assertEquals($countBefore - 1, $countAfter);
    }

    // TC-SUP-06: Kasir tidak bisa akses supplier
    public function testKasirTidakBisaAksesSupplier(): void
    {
        $this->loginAs('cashier');

        $result = $this->get('/suppliers');
        $result->assertRedirectTo('/403');
    }

    // TC-SUP-07: Kasir tidak bisa tambah supplier
    public function testKasirTidakBisaTambahSupplier(): void
    {
        $this->loginAs('cashier');

        $result = $this->post('/suppliers/store', [
            'name' => 'Supplier Ilegal',
        ]);

        $result->assertRedirectTo('/403');
    }
}



