<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;
use App\Models\ProductModel;

final class ErrorHandlingTest extends CIUnitTestCase
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

    private function loginAsAdmin(): void
    {
        $this->withSession(['user_id' => 1, 'username' => 'admin_user', 'role' => 'admin', 'logged_in' => true]);
    }

    /**
     * Test Handling of 404 Pages
     */
    public function test404PageNotFound(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('/this-path-does-not-exist');
    }

    /**
     * Test Handling of 404 for invalid records
     */
    public function test404ForInvalidRecordId(): void
    {
        $this->loginAsAdmin();
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('/products/edit/INVALID-ID-12345');
    }

    /**
     * Test Database Error Handling / Invalid Input Handling
     */
    public function testHandlingInvalidDatabasePayload(): void
    {
        $this->loginAsAdmin();
        
        // Attempting to insert a product with missing required fields to trigger validation/database errors
        $result = $this->post('/products/store', [
            'product_type' => 'UNKNOWN_TYPE', // Invalid type based on DB enum or validation
            'price' => -100, // Invalid negative price
        ]);

        // Should ideally return to the form (302 redirect) with validation errors, NOT crash with 500
        $result->assertRedirect();
        
        // Assert that the session has errors
        $this->assertTrue(session()->has('error') || session()->has('errors') || session()->has('_ci_validation_errors'));
    }

    /**
     * Test Error Handling on Unauthorized actions
     */
    public function testUnauthorizedActionHandling(): void
    {
        // Login as Cashier and attempt Admin action
        $this->withSession(['user_id' => 2, 'username' => 'cashier_user', 'role' => 'cashier', 'logged_in' => true]);
        
        $result = $this->get('/products');
        // System should handle this gracefully and redirect to a 403 page or dashboard
        $result->assertRedirectTo('/403');
    }

    /**
     * Test Exception Handling on Transaction with zero quantity
     */
    public function testErrorHandlingZeroQuantityTransaction(): void
    {
        $this->withSession(['user_id' => 2, 'username' => 'cashier_user', 'role' => 'cashier', 'logged_in' => true]);
        
        $pm = new ProductModel();
        $pm->insert(['id' => 'PRD-ERR-001', 'product_type' => 'FOOD', 'name' => 'Error Bread', 'price' => 1000, 'stock' => 10]);

        // Attempt checkout with 0 quantity
        $result = $this->post('/transactions/store', [
            'customer_id' => '',
            'product_id' => ['PRD-ERR-001'],
            'quantity' => [0]
        ]);

        // System should handle validation error and redirect back
        $result->assertRedirect();
        // Since different validation setups use different keys, checking for redirect is enough for error handling mitigate check
        $this->assertTrue(session()->has('error') || session()->has('errors') || session()->has('_ci_validation_errors'));
    }
}
