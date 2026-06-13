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

final class TransactionE2ETest extends CIUnitTestCase
{
    use FeatureTestTrait, DatabaseTestTrait, CreateTestTablesTrait;

    protected $migrate = false;

    private function loginAs(string $role = 'admin'): void
    {
        $this->withSession(['user_id' => 1, 'username' => $role.'_user', 'role' => $role, 'logged_in' => true]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAllTables();

        (new CustomerModel())->insert(['id' => 'CUST-E2E-001', 'name' => 'Andi E2E']);
        (new CustomerModel())->insert(['id' => 'CUST-E2E-002', 'name' => 'Budi E2E']);

        $pm = new ProductModel();
        $pm->insert(['product_type'=>'FOOD','name'=>'Nasi Kotak E2E','price'=>20000,'stock'=>100]);
        $pm->insert(['product_type'=>'BEVERAGE','name'=>'Es Teh E2E','price'=>5000,'stock'=>200]);
        $pm->insert(['product_type'=>'ELECTRONIC','name'=>'Baterai E2E','price'=>15000,'stock'=>50]);
    }

    protected function tearDown(): void { parent::tearDown(); session()->destroy(); }

    public function testAlurLengkapTransaksiMultiProduk(): void
    {
        $this->loginAs('cashier');
        $pm = new ProductModel(); $p = $pm->findAll();

        $this->post('/transactions/store', [
            'customer_id'=>'CUST-E2E-001','product_id'=>[$p[0]['id'],$p[1]['id'],$p[2]['id']],
            'quantity'=>[2,5,1],'notes'=>'Lengkap',
        ]);

        $this->assertEquals(98, (int)$pm->find($p[0]['id'])['stock']);
        $this->assertEquals(195,(int)$pm->find($p[1]['id'])['stock']);
        $this->assertEquals(49, (int)$pm->find($p[2]['id'])['stock']);

        $tx = (new TransactionModel())->first();
        $this->assertEquals('COMPLETED', $tx['status']);
        $this->assertEquals(80000, (float)$tx['total_amount']);
    }

    public function testAlurWalkinTanpaPelanggan(): void
    {
        $this->loginAs('cashier');
        $p = (new ProductModel())->where('name','Es Teh E2E')->first();
        $this->post('/transactions/store',['customer_id'=>'','product_id'=>[$p['id']],'quantity'=>[3]]);
        $tx = (new TransactionModel())->first();
        $this->assertNotNull($tx);
        $this->assertEquals(15000,(float)$tx['total_amount']);
    }

    public function testTransaksiGagalStokTidakCukup(): void
    {
        $this->loginAs('cashier');
        $p = (new ProductModel())->where('name','Baterai E2E')->first();
        $sb = (int)$p['stock'];
        $this->post('/transactions/store',['customer_id'=>'CUST-E2E-001','product_id'=>[$p['id']],'quantity'=>[999]]);
        $this->assertEquals($sb,(int)(new ProductModel())->find($p['id'])['stock']);
        $this->assertEquals(0,(new TransactionModel())->countAll());
    }

    public function testMultipleTransaksiBerurutan(): void
    {
        $this->loginAs('cashier');
        $pm = new ProductModel(); $p = $pm->where('name','Nasi Kotak E2E')->first();
        $pid = $p['id']; $init = (int)$p['stock'];

        $this->post('/transactions/store',['customer_id'=>'CUST-E2E-001','product_id'=>[$pid],'quantity'=>[10]]);
        $this->assertEquals($init-10,(int)$pm->find($pid)['stock']);

        $this->post('/transactions/store',['customer_id'=>'CUST-E2E-002','product_id'=>[$pid],'quantity'=>[20]]);
        $this->assertEquals($init-30,(int)$pm->find($pid)['stock']);
        $this->assertEquals(2,(new TransactionModel())->countAll());
    }

    public function testTransaksiDenganNotes(): void
    {
        $this->loginAs('cashier');
        $p = (new ProductModel())->first();
        $this->post('/transactions/store',['customer_id'=>'CUST-E2E-001','notes'=>'Meja 5','product_id'=>[$p['id']],'quantity'=>[1]]);
        $tx = (new TransactionModel())->first();
        $this->assertNotNull($tx);
        $this->assertEquals('Meja 5',$tx['notes']);
    }
}

