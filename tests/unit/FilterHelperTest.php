<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;

/**
 * Test Unit untuk Filter (Auth, RoleFilter) dan Helper (UUID)
 *
 * Mencakup TC-FLT-01 s/d TC-FLT-06
 * dan TC-HLP-01 s/d TC-HLP-04 dari dokumen SQA.
 */
final class FilterHelperTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use CreateTestTablesTrait;

    protected $migrate   = false;
    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAllTables();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        session()->destroy();
    }

    // ═══════════════════════════════════════════════════════════════
    //  AUTH FILTER
    // ═══════════════════════════════════════════════════════════════

    // TC-FLT-01: Akses dashboard tanpa login → redirect /login
    public function testAuthFilterRedirectDashboard(): void
    {
        $result = $this->get('/dashboard');
        $result->assertRedirectTo('/login');
    }

    // TC-FLT-02: Akses transactions tanpa login → redirect /login
    public function testAuthFilterRedirectTransactions(): void
    {
        $result = $this->get('/transactions');
        $result->assertRedirectTo('/login');
    }

    // TC-FLT-03: Akses customers tanpa login → redirect /login
    public function testAuthFilterRedirectCustomers(): void
    {
        $result = $this->get('/customers');
        $result->assertRedirectTo('/login');
    }

    // TC-FLT-04: Akses products tanpa login → redirect /login
    public function testAuthFilterRedirectProducts(): void
    {
        $result = $this->get('/products');
        $result->assertRedirectTo('/login');
    }

    // ═══════════════════════════════════════════════════════════════
    //  ROLE FILTER
    // ═══════════════════════════════════════════════════════════════

    // TC-FLT-05: Kasir akses halaman admin (products) → redirect /403
    public function testRoleFilterKasirAksesProducts(): void
    {
        $this->withSession([
            'user_id'   => 1,
            'username'  => 'cashier_test',
            'role'      => 'cashier',
            'logged_in' => true,
        ]);

        $result = $this->get('/products');
        $result->assertRedirectTo('/403');
    }

    // TC-FLT-06: Kasir akses halaman admin (suppliers) → redirect /403
    public function testRoleFilterKasirAksesSuppliers(): void
    {
        $this->withSession([
            'user_id'   => 1,
            'username'  => 'cashier_test',
            'role'      => 'cashier',
            'logged_in' => true,
        ]);

        $result = $this->get('/suppliers');
        $result->assertRedirectTo('/403');
    }

    // TC-FLT-07: Kasir akses halaman admin (reports) → redirect /403
    public function testRoleFilterKasirAksesReports(): void
    {
        $this->withSession([
            'user_id'   => 1,
            'username'  => 'cashier_test',
            'role'      => 'cashier',
            'logged_in' => true,
        ]);

        $result = $this->get('/reports');
        $result->assertRedirectTo('/403');
    }

    // TC-FLT-08: Admin bisa akses semua halaman admin
    public function testRoleFilterAdminAksesSemua(): void
    {
        $this->withSession([
            'user_id'   => 1,
            'username'  => 'admin_test',
            'role'      => 'admin',
            'logged_in' => true,
        ]);

        $result1 = $this->get('/products');
        $result1->assertOK();

        $result2 = $this->get('/suppliers');
        $result2->assertOK();

        $result3 = $this->get('/reports');
        $result3->assertOK();
    }

    // ═══════════════════════════════════════════════════════════════
    //  UUID HELPER
    // ═══════════════════════════════════════════════════════════════

    // TC-HLP-01: generate_uuid() menghasilkan format UUID v4 yang valid
    public function testGenerateUuidFormatValid(): void
    {
        helper('uuid');

        $uuid = generate_uuid();

        // UUID v4 format: xxxxxxxx-xxxx-4xxx-[89ab]xxx-xxxxxxxxxxxx
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

        $this->assertMatchesRegularExpression($pattern, $uuid);
    }

    // TC-HLP-02: generate_uuid() menghasilkan UUID unik setiap kali
    public function testGenerateUuidUnik(): void
    {
        helper('uuid');

        $uuid1 = generate_uuid();
        $uuid2 = generate_uuid();
        $uuid3 = generate_uuid();

        $this->assertNotEquals($uuid1, $uuid2);
        $this->assertNotEquals($uuid2, $uuid3);
        $this->assertNotEquals($uuid1, $uuid3);
    }

    // TC-HLP-03: generate_uuid() panjang 36 karakter
    public function testGenerateUuidPanjang36(): void
    {
        helper('uuid');

        $uuid = generate_uuid();

        $this->assertEquals(36, strlen($uuid));
    }

    // TC-HLP-04: generate_short_id() default 12 karakter
    public function testGenerateShortIdDefault(): void
    {
        helper('uuid');

        $shortId = generate_short_id();

        $this->assertEquals(12, strlen($shortId));
        // Hanya berisi karakter hex
        $this->assertMatchesRegularExpression('/^[0-9a-f]{12}$/', $shortId);
    }

    // TC-HLP-05: generate_short_id() custom length
    public function testGenerateShortIdCustomLength(): void
    {
        helper('uuid');

        $id8  = generate_short_id(8);
        $id20 = generate_short_id(20);

        $this->assertEquals(8, strlen($id8));
        $this->assertEquals(20, strlen($id20));
    }

    // TC-HLP-06: generate_short_id() menghasilkan ID unik
    public function testGenerateShortIdUnik(): void
    {
        helper('uuid');

        $id1 = generate_short_id();
        $id2 = generate_short_id();

        $this->assertNotEquals($id1, $id2);
    }
}



