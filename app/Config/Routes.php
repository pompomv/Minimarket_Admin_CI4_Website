<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ─────────────────────────────────────────
// Public routes (no auth required)
// ─────────────────────────────────────────
$routes->get('/', 'LoginController::index');
$routes->get('/login', 'LoginController::index');
$routes->post('/login/auth', 'LoginController::auth');
$routes->get('/logout', 'LoginController::logout');
$routes->get('/register', 'RegisterController::index');
$routes->post('/register/save', 'RegisterController::save');

// ─────────────────────────────────────────
// 403 Forbidden page
// ─────────────────────────────────────────
$routes->get('/403', 'DashboardController::forbidden');

// ─────────────────────────────────────────
// Protected routes — all logged-in users
// ─────────────────────────────────────────
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Transactions — admin & cashier
    $routes->get('transactions', 'TransactionController::index');
    $routes->get('transactions/create', 'TransactionController::create');
    $routes->post('transactions/store', 'TransactionController::store');
    $routes->get('transactions/detail/(:any)', 'TransactionController::detail/$1');

    // Customers — admin & cashier can view & add
    $routes->get('customers', 'CustomerController::index');
    $routes->get('customers/add', 'CustomerController::add');
    $routes->post('customers/store', 'CustomerController::store');

    // ─────────────────────────────────────────
    // Protected routes — Admin Only
    // ─────────────────────────────────────────

    // Products (admin only)
    $routes->get('products', 'ProductController::index', ['filter' => 'role:admin']);
    $routes->get('products/add', 'ProductController::add', ['filter' => 'role:admin']);
    $routes->post('products/store', 'ProductController::store', ['filter' => 'role:admin']);
    $routes->get('products/edit/(:num)', 'ProductController::edit/$1', ['filter' => 'role:admin']);
    $routes->post('products/update/(:num)', 'ProductController::update/$1', ['filter' => 'role:admin']);
    $routes->get('products/destroy/(:num)', 'ProductController::destroy/$1', ['filter' => 'role:admin']);

    // Suppliers (admin only)
    $routes->get('suppliers', 'SupplierController::index', ['filter' => 'role:admin']);
    $routes->get('suppliers/add', 'SupplierController::add', ['filter' => 'role:admin']);
    $routes->post('suppliers/store', 'SupplierController::store', ['filter' => 'role:admin']);
    $routes->get('suppliers/edit/(:any)', 'SupplierController::edit/$1', ['filter' => 'role:admin']);
    $routes->post('suppliers/update/(:any)', 'SupplierController::update/$1', ['filter' => 'role:admin']);
    $routes->get('suppliers/destroy/(:any)', 'SupplierController::destroy/$1', ['filter' => 'role:admin']);

    // Reports (admin only)
    $routes->get('reports', 'ReportController::index', ['filter' => 'role:admin']);

    // Customers — edit & delete (admin only)
    $routes->get('customers/edit/(:any)', 'CustomerController::edit/$1', ['filter' => 'role:admin']);
    $routes->post('customers/update/(:any)', 'CustomerController::update/$1', ['filter' => 'role:admin']);
    $routes->get('customers/destroy/(:any)', 'CustomerController::destroy/$1', ['filter' => 'role:admin']);

    // Void / cancel transaction (admin only)
    $routes->get('transactions/cancel/(:any)', 'TransactionController::cancel/$1', ['filter' => 'role:admin']);
});

// ─────────────────────────────────────────────────────────
// REST API Routes — accessed by Flutter Cashier App
// ─────────────────────────────────────────────────────────

// Public API: Login (no token required)
$routes->post('api/auth/login', 'Api\AuthController::login');
$routes->options('api/(:any)', static function () {}); // Handle CORS preflight

// Protected API: All routes below require a valid JWT token
$routes->group('api', ['filter' => 'jwt'], function ($routes) {

    // Auth
    $routes->post('auth/logout', 'Api\AuthController::logout');

    // Dashboard
    $routes->get('dashboard', 'Api\DashboardApiController::index');

    // Products (GET + CRUD for kasir/admin via Flutter)
    $routes->get('products', 'Api\ProductApiController::index');
    $routes->get('products/(:num)', 'Api\ProductApiController::show/$1');
    $routes->post('products', 'Api\ProductApiController::store');
    $routes->put('products/(:num)', 'Api\ProductApiController::update/$1');
    $routes->delete('products/(:num)', 'Api\ProductApiController::destroy/$1');

    // Transactions
    $routes->get('transactions', 'Api\TransactionApiController::index');
    $routes->post('transactions', 'Api\TransactionApiController::store');
    $routes->get('transactions/(:any)', 'Api\TransactionApiController::show/$1');

    // Customers (name lookup)
    $routes->get('customers', 'Api\CustomerApiController::index');
    $routes->get('customers/(:num)', 'Api\CustomerApiController::show/$1');
});