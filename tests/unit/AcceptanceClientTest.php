<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\CreateTestTablesTrait;
use App\Models\ProductModel;
use App\Models\CustomerModel;
use App\Models\TransactionModel;

final class AcceptanceClientTest extends CIUnitTestCase
{
    use FeatureTestTrait, DatabaseTestTrait, CreateTestTablesTrait;

    protected $migrate = false;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAllTables();

        // Seed some basic data required for the acceptance test
        (new CustomerModel())->insert(['id' => 'CUST-ACC-001', 'name' => 'Client User']);
        $pm = new ProductModel();
        $pm->insert(['id' => 'PRD-ACC-001', 'product_type' => 'FOOD', 'name' => 'Bread', 'price' => 15000, 'stock' => 50]);
        $pm->insert(['id' => 'PRD-ACC-002', 'product_type' => 'BEVERAGE', 'name' => 'Water', 'price' => 5000, 'stock' => 100]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        session()->destroy();
    }

    /**
     * Acceptance Testing (Seolah olah Client nya yg nyoba)
     * Simulates a user logging in, navigating to the dashboard, checking products,
     * creating a transaction, and viewing the success result.
     */
    public function testClientHappyPathFlow(): void
    {
        // 1. Client visits the home page and is redirected to login
        $result = $this->get('/');
        $result->assertRedirectTo('/login');

        // 2. Client logs in as Cashier
        $loginResult = $this->post('/login/auth', [
            'username' => 'cashier_user',
            'password' => 'password123' // Setup by CreateTestTablesTrait
        ]);
        $loginResult->assertRedirectTo('/dashboard');

        // Maintain session for subsequent requests
        $this->withSession(['user_id' => 2, 'username' => 'cashier_user', 'role' => 'cashier', 'logged_in' => true]);

        // 3. Client visits Dashboard
        $dashboardResult = $this->get('/dashboard');
        $dashboardResult->assertOK();
        $dashboardResult->assertSee('cashier_user'); // Should see their name on dashboard
        
        // 4. Client navigates to Point of Sale (Transactions page)
        $posResult = $this->get('/transactions');
        $posResult->assertOK();
        // The view should render the POS interface
        $posResult->assertSee('Point of Sales');

        // 5. Client adds products to cart and completes transaction
        $pm = new ProductModel();
        $prd1 = $pm->find('PRD-ACC-001');
        $prd2 = $pm->find('PRD-ACC-002');

        $transactionPayload = [
            'customer_id' => 'CUST-ACC-001',
            'product_id' => [$prd1['id'], $prd2['id']],
            'quantity' => [2, 5],
            'notes' => 'Takeaway order'
        ];

        $checkoutResult = $this->post('/transactions/store', $transactionPayload);
        $checkoutResult->assertRedirectTo('/transactions');

        // 6. Verify Transaction was recorded correctly in the database
        $transaction = (new TransactionModel())->orderBy('created_at', 'DESC')->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('COMPLETED', $transaction['status']);
        
        // 2 Bread (30,000) + 5 Water (25,000) = 55,000
        $this->assertEquals(55000, (float)$transaction['total_amount']);

        // 7. Verify inventory stock was depleted correctly
        $updatedPrd1 = $pm->find('PRD-ACC-001');
        $updatedPrd2 = $pm->find('PRD-ACC-002');
        $this->assertEquals(48, (int)$updatedPrd1['stock']);
        $this->assertEquals(95, (int)$updatedPrd2['stock']);

        // 8. Client logs out
        $logoutResult = $this->get('/logout');
        $logoutResult->assertRedirectTo('/login');
    }
}
