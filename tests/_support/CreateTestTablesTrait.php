<?php

namespace Tests\Support;

/**
 * Trait to create SQLite tables for testing.
 * All setUp() methods should call these helpers to ensure a clean state
 * by dropping and recreating tables before each test.
 */
trait CreateTestTablesTrait
{
    /**
     * Buat semua tabel yang diperlukan.
     * Drop dulu untuk memastikan state bersih.
     */
    protected function createAllTables(): void
    {
        $db = \Config\Database::connect();

        $db->query("DROP TABLE IF EXISTS transaction_details");
        $db->query("DROP TABLE IF EXISTS transactions");
        $db->query("DROP TABLE IF EXISTS products");
        $db->query("DROP TABLE IF EXISTS suppliers");
        $db->query("DROP TABLE IF EXISTS customers");
        $db->query("DROP TABLE IF EXISTS users");

        $db->query("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            role VARCHAR(20) DEFAULT 'cashier',
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $db->query("CREATE TABLE customers (
            id VARCHAR(50) PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(100),
            address VARCHAR(255),
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $db->query("CREATE TABLE suppliers (
            id VARCHAR(50) PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(100),
            address VARCHAR(255),
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $db->query("CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_type VARCHAR(31) NOT NULL,
            name VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            stock INT DEFAULT 0,
            description VARCHAR(500),
            supplier_id VARCHAR(50),
            expiry_date DATE,
            category VARCHAR(50),
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $db->query("CREATE TABLE transactions (
            id VARCHAR(50) PRIMARY KEY,
            transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            customer_id VARCHAR(50),
            total_amount DECIMAL(12,2) DEFAULT 0.00,
            status VARCHAR(20) DEFAULT 'PENDING',
            notes TEXT,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");

        $db->query("CREATE TABLE transaction_details (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            transaction_id VARCHAR(50) NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(12,2) NOT NULL
        )");
    }

    /**
     * Buat hanya tabel users
     */
    protected function createUsersTable(): void
    {
        $db = \Config\Database::connect();
        $db->query("DROP TABLE IF EXISTS users");
        $db->query("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            role VARCHAR(20) DEFAULT 'cashier',
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )");
    }
}
