<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;

final class SecuritySqlInjectionTest extends CIUnitTestCase
{
    use FeatureTestTrait, DatabaseTestTrait, CreateTestTablesTrait;

    protected $migrate = false;

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

    public function testSqlInjectionOnLoginBypass(): void
    {
        // Testing classic boolean inference SQLi
        $result = $this->post('/login/auth', [
            'username' => "admin' OR 1=1 --",
            'password' => 'wrongpassword'
        ]);

        // CodeIgniter Query Builder should escape this and fail to find the user.
        $result->assertRedirectTo('/login');
    }

    public function testSqlInjectionOnProductSearch(): void
    {
        // Must be logged in to search products in a real app, assuming admin here
        $this->withSession(['user_id' => 1, 'username' => 'admin_user', 'role' => 'admin', 'logged_in' => true]);

        // Testing Union based SQLi
        $result = $this->get('/products?search=1 UNION SELECT 1,2,3,version()--');

        // Should just return the page normally without executing the union
        $result->assertOK();
        // Should not expose database version
        $result->assertDontSee('5.7.30'); // Example version check avoidance
        $result->assertDontSee('8.0.');
    }

    public function testSqlInjectionOnTransactionStore(): void
    {
        $this->withSession(['user_id' => 1, 'username' => 'cashier_user', 'role' => 'cashier', 'logged_in' => true]);

        // Malicious Customer ID payload
        $result = $this->post('/transactions/store', [
            'customer_id' => "1'); DROP TABLE users; --",
            'product_id' => ['PRD-001'],
            'quantity' => [1]
        ]);

        // Because of UUID or int validation, or Query Builder escaping, this should safely fail or proceed with invalid ID
        // The important part is the application doesn't crash with a database syntax error
        $result->assertRedirect(); // Usually redirects back to form with validation errors

        
        // Verify the user was not dropped
        $this->seeInDatabase('users', ['id' => 1]);
    }
}
