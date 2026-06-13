<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;
use App\Models\Users;

/**
 * Test Modul Registrasi Pengguna Baru
 *
 * Mencakup TC-REG-01 s/d TC-REG-14 dari dokumen SQA.
 * Menguji semua skenario pendaftaran akun baru termasuk
 * validasi, edge case, dan keamanan password.
 */
final class RegisterControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;
    use CreateTestTablesTrait;

    protected $migrate = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createUsersTable();

        // Seed existing user untuk test duplikasi
        $model = new Users();
        $model->insert([
            'username' => 'existing_user',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'email'    => 'existing@test.com',
            'role'     => 'cashier',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        session()->destroy();
    }

    // TC-REG-01: Halaman register tampil dengan benar
    public function testHalamanRegisterTampil(): void
    {
        $result = $this->get('/register');
        $result->assertOK();
    }

    // TC-REG-02: Register berhasil dengan data lengkap
    public function testRegisterBerhasilDataLengkap(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'user_baru',
            'email'            => 'user.baru@test.com',
            'password'         => 'rahasia123',
            'password_confirm' => 'rahasia123',
        ]);

        $result->assertRedirectTo('/login');

        $model = new Users();
        $user  = $model->where('username', 'user_baru')->first();

        $this->assertNotNull($user);
        $this->assertEquals('cashier', $user['role']);
        $this->assertEquals('user.baru@test.com', $user['email']);
        $this->assertTrue(password_verify('rahasia123', $user['password']));
    }

    // TC-REG-03: Register berhasil tanpa email (optional)
    public function testRegisterBerhasilTanpaEmail(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'user_tanpa_email',
            'email'            => '',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $result->assertRedirectTo('/login');

        $model = new Users();
        $user  = $model->where('username', 'user_tanpa_email')->first();
        $this->assertNotNull($user);
    }

    // TC-REG-04: Register gagal — username kosong
    public function testRegisterGagalUsernameKosong(): void
    {
        $result = $this->post('/register/save', [
            'username'         => '',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $result->assertStatus(302);

        $model = new Users();
        $count = $model->countAll();
        $this->assertEquals(1, $count);
    }

    // TC-REG-05: Register gagal — username terlalu pendek (<3)
    public function testRegisterGagalUsernamePendek(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'ab',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $result->assertStatus(302);

        $model = new Users();
        $user  = $model->where('username', 'ab')->first();
        $this->assertNull($user);
    }

    // TC-REG-06: Register gagal — username duplikat
    public function testRegisterGagalUsernameDuplikat(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'existing_user',
            'email'            => 'new@test.com',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $result->assertStatus(302);

        $model = new Users();
        $count = $model->where('username', 'existing_user')->countAllResults();
        $this->assertEquals(1, $count);
    }

    // TC-REG-07: Register gagal — email duplikat
    public function testRegisterGagalEmailDuplikat(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'user_baru_email_dup',
            'email'            => 'existing@test.com',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $result->assertStatus(302);

        $model = new Users();
        $user  = $model->where('username', 'user_baru_email_dup')->first();
        $this->assertNull($user);
    }

    // TC-REG-08: Register gagal — email format invalid
    public function testRegisterGagalEmailInvalid(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'user_email_invalid',
            'email'            => 'bukan-email-valid',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $result->assertStatus(302);

        $model = new Users();
        $user  = $model->where('username', 'user_email_invalid')->first();
        $this->assertNull($user);
    }

    // TC-REG-09: Register gagal — password kosong
    public function testRegisterGagalPasswordKosong(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'user_no_pass',
            'password'         => '',
            'password_confirm' => '',
        ]);

        $result->assertStatus(302);

        $model = new Users();
        $user  = $model->where('username', 'user_no_pass')->first();
        $this->assertNull($user);
    }

    // TC-REG-10: Register gagal — password terlalu pendek (<6)
    public function testRegisterGagalPasswordPendek(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'user_short_pw',
            'password'         => '12345',
            'password_confirm' => '12345',
        ]);

        $result->assertStatus(302);

        $model = new Users();
        $user  = $model->where('username', 'user_short_pw')->first();
        $this->assertNull($user);
    }

    // TC-REG-11: Register gagal — konfirmasi password tidak cocok
    public function testRegisterGagalKonfirmasiTidakCocok(): void
    {
        $result = $this->post('/register/save', [
            'username'         => 'user_mismatch',
            'password'         => 'password123',
            'password_confirm' => 'berbeda456',
        ]);

        $result->assertStatus(302);

        $model = new Users();
        $user  = $model->where('username', 'user_mismatch')->first();
        $this->assertNull($user);
    }

    // TC-REG-12: Password tersimpan sebagai hash (bukan plaintext)
    public function testPasswordTerHashDenganBcrypt(): void
    {
        $this->post('/register/save', [
            'username'         => 'user_hash_test',
            'password'         => 'myPassword123',
            'password_confirm' => 'myPassword123',
        ]);

        $model = new Users();
        $user  = $model->where('username', 'user_hash_test')->first();

        $this->assertNotNull($user);
        $this->assertNotEquals('myPassword123', $user['password']);
        $this->assertTrue(password_verify('myPassword123', $user['password']));
        $this->assertStringStartsWith('$2y$', $user['password']);
    }

    // TC-REG-13: Default role adalah cashier
    public function testDefaultRoleAdalahCashier(): void
    {
        $this->post('/register/save', [
            'username'         => 'role_test_user',
            'password'         => 'password123',
            'password_confirm' => 'password123',
        ]);

        $model = new Users();
        $user  = $model->where('username', 'role_test_user')->first();

        $this->assertNotNull($user);
        $this->assertEquals('cashier', $user['role']);
    }
}

