<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;
use App\Models\ProductModel;
use App\Models\SupplierModel;

/**
 * Test Modul Manajemen Produk (Admin Only)
 *
 * Mencakup TC-PROD-01 s/d TC-PROD-07 dari dokumen SQA.
 */
final class ProductControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use CreateTestTablesTrait;

    protected $migrate   = false;
    protected $namespace = null;

    /**
     * Simulasi login sebagai admin/kasir sebelum request.
     */
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

        // Seed supplier
        $supplierModel = new SupplierModel();
        $supplierModel->insert([
            'id'    => 'SUP-001',
            'name'  => 'PT Supplier Test',
            'phone' => '08123456789',
        ]);

        // Seed produk
        $productModel = new ProductModel();
        $productModel->insert([
            'product_type' => 'FOOD',
            'name'         => 'Mie Instan Test',
            'price'        => 3500.00,
            'stock'        => 100,
            'description'  => 'Produk test',
            'supplier_id'  => 'SUP-001',
            'category'     => 'Makanan',
        ]);

        $productModel->insert([
            'product_type' => 'BEVERAGE',
            'name'         => 'Air Mineral Test',
            'price'        => 5000.00,
            'stock'        => 5, // stok rendah
            'supplier_id'  => 'SUP-001',
            'category'     => 'Minuman',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        session()->destroy();
    }

    // ═════════════════════════════════════════════════════════════
    // TC-PROD-01: Lihat daftar produk (Admin)
    // ═════════════════════════════════════════════════════════════
    public function testLihatDaftarProdukAdmin(): void
    {
        $this->loginAs('admin');

        $result = $this->get('/products');

        $result->assertOK();
        $result->assertSee('Mie Instan Test');
        $result->assertSee('Air Mineral Test');
    }

    // ═════════════════════════════════════════════════════════════
    // TC-PROD-02: Tambah produk berhasil (Admin)
    // ═════════════════════════════════════════════════════════════
    public function testTambahProdukBerhasil(): void
    {
        $this->loginAs('admin');

        $result = $this->post('/products/store', [
            'product_type' => 'ELECTRONIC',
            'name'         => 'Charger USB Test',
            'price'        => 25000.00,
            'stock'        => 50,
            'description'  => 'Charger test',
            'supplier_id'  => 'SUP-001',
            'category'     => 'Elektronik',
        ]);

        $result->assertRedirectTo('/products');

        // Verifikasi data tersimpan
        $model   = new ProductModel();
        $product = $model->where('name', 'Charger USB Test')->first();

        $this->assertNotNull($product);
        $this->assertEquals('ELECTRONIC', $product['product_type']);
        $this->assertEquals(25000.00, (float) $product['price']);
        $this->assertEquals(50, (int) $product['stock']);
    }

    // ═════════════════════════════════════════════════════════════
    // TC-PROD-03: Tambah produk gagal — field required kosong
    // ═════════════════════════════════════════════════════════════
    public function testTambahProdukGagalValidasi(): void
    {
        $this->loginAs('admin');

        $result = $this->post('/products/store', [
            'product_type' => '',  // required, kosong
            'name'         => '',  // required, kosong
            'price'        => '',  // required
            'stock'        => '',  // required
        ]);

        // Harus redirect back (validasi gagal)
        $result->assertStatus(302);

        // Produk TIDAK boleh tersimpan
        $model = new ProductModel();
        $count = $model->countAll();
        $this->assertEquals(2, $count); // hanya 2 dari setUp
    }

    // ═════════════════════════════════════════════════════════════
    // TC-PROD-04: Tambah produk gagal — tipe invalid
    // ═════════════════════════════════════════════════════════════
    public function testTambahProdukTipeInvalid(): void
    {
        $this->loginAs('admin');

        $result = $this->post('/products/store', [
            'product_type' => 'INVALID_TYPE', // bukan FOOD/BEVERAGE/ELECTRONIC
            'name'         => 'Produk Invalid',
            'price'        => 10000.00,
            'stock'        => 10,
        ]);

        $result->assertStatus(302);

        $model   = new ProductModel();
        $product = $model->where('name', 'Produk Invalid')->first();
        $this->assertNull($product);
    }

    // ═════════════════════════════════════════════════════════════
    // TC-PROD-05: Edit produk berhasil
    // ═════════════════════════════════════════════════════════════
    public function testEditProdukBerhasil(): void
    {
        $this->loginAs('admin');

        // Ambil ID produk pertama
        $model   = new ProductModel();
        $product = $model->where('name', 'Mie Instan Test')->first();
        $id      = $product['id'];

        $result = $this->post("/products/update/{$id}", [
            'product_type' => 'FOOD',
            'name'         => 'Mie Instan Updated',
            'price'        => 4000.00,
            'stock'        => 150,
            'description'  => 'Updated',
            'supplier_id'  => 'SUP-001',
            'category'     => 'Makanan',
        ]);

        $result->assertRedirectTo('/products');

        // Verifikasi data terupdate
        $updated = $model->find($id);
        $this->assertEquals('Mie Instan Updated', $updated['name']);
        $this->assertEquals(4000.00, (float) $updated['price']);
        $this->assertEquals(150, (int) $updated['stock']);
    }

    // ═════════════════════════════════════════════════════════════
    // TC-PROD-06: Hapus produk
    // ═════════════════════════════════════════════════════════════
    public function testHapusProduk(): void
    {
        $this->loginAs('admin');

        $model   = new ProductModel();
        $product = $model->where('name', 'Air Mineral Test')->first();
        $id      = $product['id'];

        $countBefore = $model->countAll();

        $result = $this->get("/products/destroy/{$id}");
        $result->assertRedirectTo('/products');

        $countAfter = $model->countAll();
        $this->assertEquals($countBefore - 1, $countAfter);

        // Produk tidak ditemukan lagi
        $deleted = $model->find($id);
        $this->assertNull($deleted);
    }

    // ═════════════════════════════════════════════════════════════
    // TC-PROD-07: Kasir tidak bisa akses halaman produk
    // ═════════════════════════════════════════════════════════════
    public function testKasirTidakBisaAksesProduk(): void
    {
        $this->loginAs('cashier');

        $result = $this->get('/products');

        // RoleFilter harus redirect ke /403
        $result->assertRedirectTo('/403');
    }
}



