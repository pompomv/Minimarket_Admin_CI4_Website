<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;
use App\Models\CustomerModel;
use App\Models\SupplierModel;

final class CustomerSupplierValidationTest extends CIUnitTestCase
{
    use FeatureTestTrait, DatabaseTestTrait, CreateTestTablesTrait;

    protected $migrate = false;

    private function loginAs(string $role = 'admin'): void
    {
        $this->withSession(['user_id'=>1,'username'=>$role.'_user','role'=>$role,'logged_in'=>true]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAllTables();

        (new CustomerModel())->insert(['id'=>'CUST-VAL-001','name'=>'Pelanggan Validasi','phone'=>'081234567890','email'=>'pelanggan@val.com','address'=>'Jl. Validasi No. 1']);
        (new SupplierModel())->insert(['id'=>'SUP-VAL-001','name'=>'Supplier Validasi','phone'=>'021-7778899','email'=>'supplier@val.com','address'=>'Jl. Supplier No. 2']);
    }

    protected function tearDown(): void { parent::tearDown(); session()->destroy(); }

    public function testFormTambahCustomerTampil(): void
    {
        $this->loginAs('admin');
        $this->get('/customers/add')->assertOK();
    }

    public function testTambahCustomerDataLengkap(): void
    {
        $this->loginAs('admin');
        $this->post('/customers/store',['name'=>'Customer Lengkap','phone'=>'08555111222','email'=>'l@t.com','address'=>'Jl. 99']);
        $c = (new CustomerModel())->where('name','Customer Lengkap')->first();
        $this->assertNotNull($c);
        $this->assertEquals('08555111222',$c['phone']);
    }

    public function testTambahCustomerHanyaNama(): void
    {
        $this->loginAs('admin');
        $this->post('/customers/store',['name'=>'Customer Minimal','phone'=>'','email'=>'','address'=>'']);
        $c = (new CustomerModel())->where('name','Customer Minimal')->first();
        $this->assertNotNull($c);
    }

    public function testFormEditCustomerTampil(): void
    {
        $this->loginAs('admin');
        $this->get('/customers/edit/CUST-VAL-001')->assertOK();
    }

    public function testEditCustomerTidakDitemukan(): void
    {
        $this->loginAs('admin');
        $this->get('/customers/edit/CUST-TIDAK-ADA')->assertRedirectTo('/customers');
    }

    public function testUpdateCustomerSemuaField(): void
    {
        $this->loginAs('admin');
        $this->post('/customers/update/CUST-VAL-001',['name'=>'Updated','phone'=>'089888','email'=>'up@v.com','address'=>'Jl. Baru']);
        $u = (new CustomerModel())->find('CUST-VAL-001');
        $this->assertEquals('Updated',$u['name']);
    }

    public function testCustomerIdAdalahUUID(): void
    {
        $this->loginAs('admin');
        $this->post('/customers/store',['name'=>'UUID Cust']);
        $c = (new CustomerModel())->where('name','UUID Cust')->first();
        $this->assertNotNull($c);
        $this->assertIsString($c['id']);
        $this->assertGreaterThan(10,strlen($c['id']));
    }

    public function testFormTambahSupplierTampil(): void
    {
        $this->loginAs('admin');
        $this->get('/suppliers/add')->assertOK();
    }

    public function testTambahSupplierDataLengkap(): void
    {
        $this->loginAs('admin');
        $this->post('/suppliers/store',['name'=>'Supplier Lengkap','phone'=>'021-123','email'=>'s@t.com','address'=>'Jl. 50']);
        $s = (new SupplierModel())->where('name','Supplier Lengkap')->first();
        $this->assertNotNull($s);
    }

    public function testFormEditSupplierTampil(): void
    {
        $this->loginAs('admin');
        $this->get('/suppliers/edit/SUP-VAL-001')->assertOK();
    }

    public function testEditSupplierTidakDitemukan(): void
    {
        $this->loginAs('admin');
        $this->get('/suppliers/edit/SUP-TIDAK-ADA')->assertRedirectTo('/suppliers');
    }

    public function testUpdateSupplierBerhasil(): void
    {
        $this->loginAs('admin');
        $this->post('/suppliers/update/SUP-VAL-001',['name'=>'Supplier Updated','phone'=>'021-999','email'=>'su@v.com','address'=>'Jl. 77']);
        $u = (new SupplierModel())->find('SUP-VAL-001');
        $this->assertEquals('Supplier Updated',$u['name']);
    }

    public function testSupplierIdAdalahUUID(): void
    {
        $this->loginAs('admin');
        $this->post('/suppliers/store',['name'=>'UUID Sup']);
        $s = (new SupplierModel())->where('name','UUID Sup')->first();
        $this->assertNotNull($s);
        $this->assertGreaterThan(10,strlen($s['id']));
    }

    public function testKasirBisaTambahCustomerTidakBisaTambahSupplier(): void
    {
        $this->loginAs('cashier');
        $this->post('/customers/store',['name'=>'Dari Kasir'])->assertRedirectTo('/customers');
        $c = (new CustomerModel())->where('name','Dari Kasir')->first();
        $this->assertNotNull($c);

        $this->post('/suppliers/store',['name'=>'Sup Kasir'])->assertRedirectTo('/403');
    }
}

