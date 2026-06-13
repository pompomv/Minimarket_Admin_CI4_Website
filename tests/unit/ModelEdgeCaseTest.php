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

final class ModelEdgeCaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait, CreateTestTablesTrait;

    protected $migrate = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAllTables();
    }

    // Users
    public function testUsersTimestampsTerisi(): void
    {
        $m = new Users();
        $m->insert(['username'=>'ts_test','password'=>password_hash('p',PASSWORD_BCRYPT),'role'=>'cashier']);
        $u = $m->where('username','ts_test')->first();
        $this->assertNotNull($u['created_at']);
    }

    public function testUsersAllowedFields(): void
    {
        $m = new Users();
        $r = new \ReflectionClass($m);
        $p = $r->getProperty('allowedFields'); $p->setAccessible(true);
        $f = $p->getValue($m);
        $this->assertContains('username',$f);
        $this->assertContains('password',$f);
        $this->assertContains('role',$f);
    }

    public function testFindByUsernameEmpty(): void
    {
        $this->assertNull((new Users())->findByUsername(''));
    }

    // Product
    public function testDecreaseStockSampaiHabis(): void
    {
        $m = new ProductModel();
        $m->insert(['product_type'=>'FOOD','name'=>'Habis','price'=>1000,'stock'=>5]);
        $p = $m->where('name','Habis')->first();
        $m->decreaseStock((int)$p['id'],5);
        $this->assertEquals(0,(int)$m->find($p['id'])['stock']);
    }

    public function testWithSupplierTanpaSupplier(): void
    {
        $m = new ProductModel();
        $m->insert(['product_type'=>'ELECTRONIC','name'=>'NoSup','price'=>30000,'stock'=>15]);
        $all = $m->withSupplier();
        $found = array_filter($all,fn($p)=>$p['name']==='NoSup');
        $this->assertCount(1,$found);
    }

    public function testProductValidationRulesExist(): void
    {
        $m = new ProductModel();
        $r = new \ReflectionClass($m);
        $p = $r->getProperty('validationRules'); $p->setAccessible(true);
        $rules = $p->getValue($m);
        $this->assertArrayHasKey('product_type',$rules);
        $this->assertArrayHasKey('name',$rules);
    }

    // Transaction
    public function testRecalculateTotalTanpaDetail(): void
    {
        $m = new TransactionModel();
        $m->insert(['id'=>'TRX-EMPTY','transaction_date'=>date('Y-m-d H:i:s'),'status'=>'PENDING','total_amount'=>99999]);
        $m->recalculateTotal('TRX-EMPTY');
        $this->assertEquals(0,(float)$m->find('TRX-EMPTY')['total_amount']);
    }

    public function testRecalculateTotalSingleDetail(): void
    {
        $m = new TransactionModel();
        $m->insert(['id'=>'TRX-ONE','transaction_date'=>date('Y-m-d H:i:s'),'status'=>'PENDING','total_amount'=>0]);
        (new TransactionDetailModel())->insert(['transaction_id'=>'TRX-ONE','product_id'=>1,'quantity'=>3,'unit_price'=>7000,'subtotal'=>21000]);
        $m->recalculateTotal('TRX-ONE');
        $this->assertEquals(21000,(float)$m->find('TRX-ONE')['total_amount']);
    }

    // TransactionDetail
    public function testGetByTransactionIdTidakAda(): void
    {
        $d = (new TransactionDetailModel())->getByTransaction('TRX-NOPE');
        $this->assertIsArray($d);
        $this->assertCount(0,$d);
    }

    // Customer & Supplier
    public function testCustomerInsertDanDelete(): void
    {
        $m = new CustomerModel();
        $m->insert(['id'=>'CUST-DEL','name'=>'Del Test']);
        $this->assertNotNull($m->find('CUST-DEL'));
        $m->delete('CUST-DEL');
        $this->assertNull($m->find('CUST-DEL'));
    }

    public function testSupplierInsertDanUpdate(): void
    {
        $m = new SupplierModel();
        $m->insert(['id'=>'SUP-UPD','name'=>'Before','phone'=>'021-1']);
        $m->update('SUP-UPD',['name'=>'After','phone'=>'021-2']);
        $u = $m->find('SUP-UPD');
        $this->assertEquals('After',$u['name']);
        $this->assertEquals('021-2',$u['phone']);
    }
}

