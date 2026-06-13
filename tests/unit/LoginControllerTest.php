<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;
use App\Models\Users;

/**
 * Test Modul Autentikasi (Login & Register)
 *
 * Mencakup TC-AUTH-01 s/d TC-AUTH-10 dari dokumen SQA.
 */
final class LoginControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use CreateTestTablesTrait;

    protected $migrateOnce = false;
    protected $migrate     = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsersTable();

        // Insert test user (admin)
        $model = new Users();
        $model->insert([
            'username'   => 'administrator',
            'password'   => password_hash('admin123', PASSWORD_BCRYPT),
            'email'      => 'admin@minimarket.com',
            'role'       => 'admin',
        ]);

        // Insert cashier user
        $model->insert([
            'username'   => 'cashier_user',
            'password'   => password_hash('password123', PASSWORD_BCRYPT),
            'email'      => 'cashier@minimarket.com',
            'role'       => 'cashier',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        session()->destroy();
    }

    // ═════════════════════════════════════════════════════════════
    // TC-AUTH-01: Login berhasil
    // ═════════════════════════════════════════════════════════════
    public function testLoginBerhasil(): void
    {
        $result = $this->post('/login/auth', [
            'username' => 'administrator',
            'password' => 'admin123',
        ]);

        $result->assertRedirectTo('/dashboard');
    }

    // ═════════════════════════════════════════════════════════════
    // TC-AUTH-02: Login gagal — username salah
    // ═════════════════════════════════════════════════════════════
    public function testLoginGagalUsernameSalah(): void
    {
        $result = $this->post('/login/auth', [
            'username' => 'tidak_ada',
            'password' => 'admin123',
        ]);

        $result->assertRedirectTo('/login');
    }

    // ═════════════════════════════════════════════════════════════
    // TC-AUTH-03: Login gagal — password salah
    // ═════════════════════════════════════════════════════════════
    public function testLoginGagalPasswordSalah(): void
    {
        $result = $this->post('/login/auth', [
            'username' => 'administrator',
            'password' => 'salah_banget',
        ]);

        $result->assertRedirectTo('/login');
    }

    // ═════════════════════════════════════════════════════════════
    // TC-AUTH-04: Login gagal — field kosong
    // ═════════════════════════════════════════════════════════════
    public function testLoginGagalFieldKosong(): void
    {
        $result = $this->post('/login/auth', [
            'username' => '',
            'password' => '',
        ]);

        $result->assertRedirectTo('/login');
    }

    // ═════════════════════════════════════════════════════════════
    // TC-AUTH-05: Login berhasil cashier
    // ═════════════════════════════════════════════════════════════
    public function testLoginBerhasilCashier(): void
    {
        $result = $this->post('/login/auth', [
            'username' => 'cashier_user',
            'password' => 'password123',
        ]);

        $result->assertRedirectTo('/dashboard');
    }

    // ═════════════════════════════════════════════════════════════
    // TC-AUTH-06: Register berhasil
    // ═════════════════════════════════════════════════════════════
    public function testRegisterBerhasil(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'newuser',
            'email'            => 'new@test.com',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $result->assertRedirectTo('/login');
    }

    // ═════════════════════════════════════════════════════════════
    // TC-AUTH-07: Register gagal username duplikat
    // ═════════════════════════════════════════════════════════════
    public function testRegisterGagalUsernameDuplikat(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'administrator',
            'email'            => 'newemail@test.com',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $result->assertStatus(302);

        $model = new Users();
        $count = $model->where('username', 'administrator')->countAllResults();
        $this->assertEquals(1, $count);
    }

    // ═════════════════════════════════════════════════════════════
    // TC-AUTH-08: Register gagal password pendek
    // ═════════════════════════════════════════════════════════════
    public function testRegisterGagalPasswordPendek(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'shortpw',
            'password'         => '12345',
            'password_confirm' => '12345',
        ]);

        $result->assertStatus(302);

        $model = new Users();
        $user = $model->where('username', 'shortpw')->first();
        $this->assertNull($user);
    }

    // ═════════════════════════════════════════════════════════════
    // TC-AUTH-09: Register gagal konfirmasi tidak cocok
    // ═════════════════════════════════════════════════════════════
    public function testRegisterGagalKonfirmasiTidakCocok(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'mismatch',
            'password'         => 'password123',
            'password_confirm' => 'berbeda456',
        ]);

        $result->assertStatus(302);

        $model = new Users();
        $user = $model->where('username', 'mismatch')->first();
        $this->assertNull($user);
    }

    // ═════════════════════════════════════════════════════════════
    // TC-AUTH-10: Logout
    // ═════════════════════════════════════════════════════════════
    public function testLogout(): void
    {
        // Login dulu
        $this->post('/login/auth', [
            'username' => 'administrator',
            'password' => 'admin123',
        ]);

        $result = $this->get('/logout');
        $result->assertRedirectTo('/login');
    }

    // ═════════════════════════════════════════════════════════════
    // TC-AUTH-11: Halaman login tampil
    // ═════════════════════════════════════════════════════════════
    public function testHalamanLoginTampil(): void
    {
        $result = $this->get('/login');
        $result->assertOK();
    }
}

