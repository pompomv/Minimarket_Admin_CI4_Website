<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Minimarket POS System — Full Schema Migration
 *
 * Tables created (in dependency order):
 *   1. users
 *   2. customers
 *   3. suppliers
 *   4. products          (FK → suppliers.id  VARCHAR 50)
 *   5. transactions      (FK → customers.id  VARCHAR 50)
 *   6. transaction_details (FK → transactions.id VARCHAR 50, products.id BIGINT)
 *
 * Collation : utf8mb4_unicode_ci
 * Engine    : InnoDB
 *
 * Run  : php spark migrate
 * Roll : php spark migrate:rollback
 */
class CreateMinimarketTables extends Migration
{
    private string $charset = 'utf8mb4';
    private string $collate = 'utf8mb4_unicode_ci';
    private string $engine = 'InnoDB';

    // -----------------------------------------------------------------
    // UP
    // -----------------------------------------------------------------
    public function up(): void
    {
        $this->createUsers();
        $this->createCustomers();
        $this->createSuppliers();
        $this->createProducts();
        $this->createTransactions();
        $this->createTransactionDetails();
    }

    // -----------------------------------------------------------------
    // DOWN
    // -----------------------------------------------------------------
    public function down(): void
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        $tables = [
            'transaction_details',
            'transactions',
            'products',
            'suppliers',
            'customers',
            'users',
        ];

        foreach ($tables as $table) {
            $this->forge->dropTable($table, true);
        }

        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    // =================================================================
    // TABLE BUILDERS
    // =================================================================

    // -----------------------------------------------------------------
    // 1. users   — BIGINT AUTO_INCREMENT PK
    // -----------------------------------------------------------------
    private function createUsers(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'BCrypt hashed',
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'default' => null,
            ],
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'USER',
                'comment' => 'USER or ADMIN',
            ],
            'enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('users', true, [
            'ENGINE' => $this->engine,
            'DEFAULT CHARSET' => $this->charset,
            'COLLATE' => $this->collate,
        ]);
    }

    // -----------------------------------------------------------------
    // 2. customers — VARCHAR(50) PK (UUID, NOT auto-increment)
    // -----------------------------------------------------------------
    private function createCustomers(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'default' => null,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'default' => null,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => null,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('customers', true, [
            'ENGINE' => $this->engine,
            'DEFAULT CHARSET' => $this->charset,
            'COLLATE' => $this->collate,
        ]);
    }

    // -----------------------------------------------------------------
    // 3. suppliers — VARCHAR(50) PK (UUID, NOT auto-increment)
    // -----------------------------------------------------------------
    private function createSuppliers(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'default' => null,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'default' => null,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => null,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('suppliers', true, [
            'ENGINE' => $this->engine,
            'DEFAULT CHARSET' => $this->charset,
            'COLLATE' => $this->collate,
        ]);
    }

    // -----------------------------------------------------------------
    // 4. products — BIGINT AUTO_INCREMENT PK
    //    supplier_id → VARCHAR(50) matches suppliers.id exactly
    // -----------------------------------------------------------------
    private function createProducts(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'product_type' => [
                'type' => 'VARCHAR',
                'constraint' => 31,
                'comment' => 'Discriminator: FOOD, BEVERAGE, ELECTRONIC',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'stock' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'default' => null,
            ],
            // ⚠ Must be VARCHAR(50) — same type/length as suppliers.id
            'supplier_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'default' => null,
            ],
            'expiry_date' => [
                'type' => 'DATE',
                'null' => true,
                'default' => null,
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => null,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('supplier_id');
        $this->forge->createTable('products', true, [
            'ENGINE' => $this->engine,
            'DEFAULT CHARSET' => $this->charset,
            'COLLATE' => $this->collate,
        ]);

        // FK: products.supplier_id → suppliers.id (both VARCHAR 50)
        $this->db->query('
            ALTER TABLE `products`
            ADD CONSTRAINT `fk_products_supplier`
            FOREIGN KEY (`supplier_id`)
            REFERENCES `suppliers` (`id`)
            ON DELETE SET NULL
            ON UPDATE CASCADE
        ');
    }

    // -----------------------------------------------------------------
    // 5. transactions — VARCHAR(50) PK (UUID)
    //    customer_id → VARCHAR(50) matches customers.id exactly
    // -----------------------------------------------------------------
    private function createTransactions(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            // CI4 Forge quotes string defaults → 'CURRENT_TIMESTAMP' breaks MySQL.
            // We set null here and apply the real default via ALTER TABLE below.
            'transaction_date' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => null,
            ],
            // ⚠ Must be VARCHAR(50) — same type/length as customers.id
            'customer_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'default' => null,
            ],
            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => '0.00',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'PENDING',
                'comment' => 'PENDING, COMPLETED, CANCELLED',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => null,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
                'default' => null,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('customer_id');
        $this->forge->createTable('transactions', true, [
            'ENGINE' => $this->engine,
            'DEFAULT CHARSET' => $this->charset,
            'COLLATE' => $this->collate,
        ]);

        // Apply CURRENT_TIMESTAMP default without quoting
        $this->db->query('
            ALTER TABLE `transactions`
            MODIFY `transaction_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ');

        // FK: transactions.customer_id → customers.id (both VARCHAR 50)
        $this->db->query('
            ALTER TABLE `transactions`
            ADD CONSTRAINT `fk_transactions_customer`
            FOREIGN KEY (`customer_id`)
            REFERENCES `customers` (`id`)
            ON DELETE SET NULL
            ON UPDATE CASCADE
        ');
    }

    // -----------------------------------------------------------------
    // 6. transaction_details — BIGINT AUTO_INCREMENT PK
    //    transaction_id → VARCHAR(50)       matches transactions.id
    //    product_id     → BIGINT UNSIGNED   matches products.id
    // -----------------------------------------------------------------
    private function createTransactionDetails(): void
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            // ⚠ VARCHAR(50) — same type/length as transactions.id
            'transaction_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            // ⚠ BIGINT UNSIGNED — same type/length as products.id
            'product_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'unit_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'comment' => 'Price snapshot at time of sale',
            ],
            'subtotal' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('transaction_id');
        $this->forge->addKey('product_id');
        $this->forge->createTable('transaction_details', true, [
            'ENGINE' => $this->engine,
            'DEFAULT CHARSET' => $this->charset,
            'COLLATE' => $this->collate,
        ]);

        // FK: transaction_id → transactions.id (VARCHAR 50 → VARCHAR 50) ✓
        $this->db->query('
            ALTER TABLE `transaction_details`
            ADD CONSTRAINT `fk_td_transaction`
            FOREIGN KEY (`transaction_id`)
            REFERENCES `transactions` (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
        ');

        // FK: product_id → products.id (BIGINT UNSIGNED → BIGINT UNSIGNED) ✓
        $this->db->query('
            ALTER TABLE `transaction_details`
            ADD CONSTRAINT `fk_td_product`
            FOREIGN KEY (`product_id`)
            REFERENCES `products` (`id`)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
        ');
    }
}
