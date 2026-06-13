<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;
use App\Models\ProductModel;
use App\Models\SupplierModel;

/**
 * Test Validasi & Edge Case Manajemen Produk
 */
final class ProductValidationTest extends CIUnitTestCase
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

        $supplierModel = new SupplierModel();
        $supplierModel->insert(['id' => 'SUP-VAL-001', 'name' => 'Supplier Validasi']);

        $productModel = new ProductModel();
        $productModel->insert(['product_type' => 'FOOD', 'name' => 'Produk Validasi Test', 'price' => 5000.00, 'stock' => 25, 'supplier_id' => 'SUP-VAL-001', 'category' => 'Makanan']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        session()->destroy();
    }

    public function testFormTambahProdukTampil(): void
    {
        $this->loginAs('admin');
        $result = $this->get('/products/add');
        $result->assertOK();
    }

    public function testFormEditProdukTampil(): void
    {
        $this->loginAs('admin');
        $model   = new ProductModel();
        $product = $model->where('name', 'Produk Validasi Test')->first();
        $result  = $this->get('/products/edit/' . $product['id']);
        $result->assertOK();
    }

    public function testEditProdukTidakAda(): void
    {
        $this->loginAs('admin');
        $result = $this->get('/products/edit/99999');
        $result->assertRedirectTo('/products');
    }

    public function testTambahProdukFieldOptionalKosong(): void
    {
        $this->loginAs('admin');
        $result = $this->post('/products/store', [
            'product_type' => 'FOOD', 'name' => 'Produk Minimal', 'price' => 1000.00, 'stock' => 1,
            'description' => '', 'supplier_id' => '', 'expiry_date' => '', 'category' => '',
        ]);
        $result->assertRedirectTo('/products');

        $model   = new ProductModel();
        $product = $model->where('name', 'Produk Minimal')->first();
        $this->assertNotNull($product);
    }

    public function testTambahProdukTipeFood(): void
    {
        $this->loginAs('admin');
        $this->post('/products/store', ['product_type' => 'FOOD', 'name' => 'Makanan Test', 'price' => 5000.00, 'stock' => 10]);
        $model   = new ProductModel();
        $product = $model->where('name', 'Makanan Test')->first();
        $this->assertNotNull($product);
        $this->assertEquals('FOOD', $product['product_type']);
    }

    public function testTambahProdukTipeBeverage(): void
    {
        $this->loginAs('admin');
        $this->post('/products/store', ['product_type' => 'BEVERAGE', 'name' => 'Minuman Test', 'price' => 3000.00, 'stock' => 20]);
        $model   = new ProductModel();
        $product = $model->where('name', 'Minuman Test')->first();
        $this->assertNotNull($product);
        $this->assertEquals('BEVERAGE', $product['product_type']);
    }

    public function testTambahProdukTipeElectronic(): void
    {
        $this->loginAs('admin');
        $this->post('/products/store', ['product_type' => 'ELECTRONIC', 'name' => 'Elektronik Test', 'price' => 50000.00, 'stock' => 5]);
        $model   = new ProductModel();
        $product = $model->where('name', 'Elektronik Test')->first();
        $this->assertNotNull($product);
        $this->assertEquals('ELECTRONIC', $product['product_type']);
    }

    public function testUpdateProdukGagalNamaKosong(): void
    {
        $this->loginAs('admin');
        $model   = new ProductModel();
        $product = $model->where('name', 'Produk Validasi Test')->first();
        $result  = $this->post('/products/update/' . $product['id'], ['product_type' => 'FOOD', 'name' => '', 'price' => 5000.00, 'stock' => 10]);
        $result->assertStatus(302);
        $updated = $model->find($product['id']);
        $this->assertEquals('Produk Validasi Test', $updated['name']);
    }

    public function testTambahProdukDenganExpiryDate(): void
    {
        $this->loginAs('admin');
        $this->post('/products/store', ['product_type' => 'FOOD', 'name' => 'Produk Expiry', 'price' => 7500.00, 'stock' => 15, 'expiry_date' => '2027-12-31']);
        $model   = new ProductModel();
        $product = $model->where('name', 'Produk Expiry')->first();
        $this->assertNotNull($product);
        $this->assertEquals('2027-12-31', $product['expiry_date']);
    }

    public function testTambahProdukDenganSupplier(): void
    {
        $this->loginAs('admin');
        $this->post('/products/store', ['product_type' => 'BEVERAGE', 'name' => 'Produk Supplier', 'price' => 4000.00, 'stock' => 30, 'supplier_id' => 'SUP-VAL-001']);
        $model   = new ProductModel();
        $product = $model->where('name', 'Produk Supplier')->first();
        $this->assertNotNull($product);
        $this->assertEquals('SUP-VAL-001', $product['supplier_id']);
    }

    public function testHapusProdukVerifikasi(): void
    {
        $this->loginAs('admin');
        $model   = new ProductModel();
        $product = $model->where('name', 'Produk Validasi Test')->first();
        $id      = $product['id'];
        $this->get("/products/destroy/{$id}");

        $deleted = $model->find($id);
        $this->assertNull($deleted);
    }
}

