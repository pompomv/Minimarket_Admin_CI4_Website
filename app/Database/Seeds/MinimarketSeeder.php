<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * MinimarketSeeder
 *
 * Seeds all Minimarket POS tables with realistic dummy data.
 * This seeder is IDEMPOTENT — safe to run multiple times.
 *
 * Run via Spark CLI:
 *   php spark db:seed MinimarketSeeder
 *
 * Or from another seeder:
 *   $this->call('MinimarketSeeder');
 *
 * Seeding order follows FK dependencies:
 *   suppliers → customers → users → products → transactions → transaction_details
 */
class MinimarketSeeder extends Seeder
{
    public function run(): void
    {
        helper('uuid'); // loads app/Helpers/uuid_helper.php

        $now = date('Y-m-d H:i:s');

        // Disable FK checks while truncating so ORDER doesn't matter
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['transaction_details', 'transactions', 'products', 'suppliers', 'customers'] as $t) {
            $this->db->table($t)->truncate();
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        // =========================================================
        // 1. SUPPLIERS  (VARCHAR PK — UUID)
        // =========================================================
        $suppliers = [
            [
                'id' => generate_uuid(),
                'name' => 'PT Indofood Sukses Makmur',
                'phone' => '021-5795-8822',
                'email' => 'procurement@indofood.co.id',
                'address' => 'Jl. Jend. Sudirman Kav. 76-78, Jakarta Pusat',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => generate_uuid(),
                'name' => 'CV Berkah Elektronik',
                'phone' => '0274-551234',
                'email' => 'sales@berkah-elektronik.com',
                'address' => 'Jl. Malioboro No. 42, Yogyakarta',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => generate_uuid(),
                'name' => 'UD Sumber Minuman Segar',
                'phone' => '031-378-9900',
                'email' => null,
                'address' => 'Jl. Raya Darmo No. 135, Surabaya',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->db->table('suppliers')->insertBatch($suppliers);

        $supFoodId = $suppliers[0]['id'];
        $supElecId = $suppliers[1]['id'];
        $supDrinkId = $suppliers[2]['id'];

        // =========================================================
        // 2. CUSTOMERS  (VARCHAR PK — UUID)
        // =========================================================
        $customers = [
            [
                'id' => generate_uuid(),
                'name' => 'Budi Santoso',
                'phone' => '0812-3456-7890',
                'email' => 'budi.santoso@email.com',
                'address' => 'Jl. Kenanga No. 5, Bandung',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => generate_uuid(),
                'name' => 'Siti Rahayu',
                'phone' => '0856-9876-5432',
                'email' => null,
                'address' => 'Jl. Melati Blok C No. 12, Bekasi',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => generate_uuid(),
                'name' => 'Ahmad Fauzi',
                'phone' => null,
                'email' => 'ahmad.fauzi@gmail.com',
                'address' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->db->table('customers')->insertBatch($customers);

        $custBudiId = $customers[0]['id'];
        $custSitiId = $customers[1]['id'];

        // =========================================================
        // 3. USERS  (BIGINT PK AUTO_INCREMENT)
        //    Note: if the `users` table still uses the OLD schema
        //    (user_id / user_name / user_email), this block is skipped.
        //    Run the new migration or drop the old users table first.
        // =========================================================
        $usersColumns = array_column(
            $this->db->query("SHOW COLUMNS FROM `users`")->getResultArray(),
            'Field'
        );

        if (in_array('username', $usersColumns, true) && in_array('email', $usersColumns, true)) {
            // New Minimarket schema — safe to insert
            $users = [
                [
                    'username' => 'admin',
                    'password' => password_hash('admin123', PASSWORD_BCRYPT),
                    'email' => 'admin@minimarket.local',
                    'role' => 'admin',
                    'enabled' => 1,
                    'created_at' => $now,
                ],
                [
                    'username' => 'kasir1',
                    'password' => password_hash('kasir123', PASSWORD_BCRYPT),
                    'email' => 'kasir1@minimarket.local',
                    'role' => 'kasir',
                    'enabled' => 1,
                    'created_at' => $now,
                ],
                [
                    'username' => 'kasir2',
                    'password' => password_hash('kasir456', PASSWORD_BCRYPT),
                    'email' => null,
                    'role' => 'kasir',
                    'enabled' => 0,
                    'created_at' => $now,
                ],
            ];
            $this->db->table('users')->insertBatch($users);
            $usersSeeded = count($users);
        } else {
            // Old schema (user_id / user_name / etc) — skip to avoid column mismatch
            echo "    ⚠  SKIPPED users: table uses the old schema. Drop the old\n";
            echo "       users table and re-run migrations to upgrade it.\n";
            $usersSeeded = 0;
        }

        // =========================================================
        // 4. PRODUCTS  (BIGINT PK AUTO_INCREMENT)
        //    supplier_id is VARCHAR(50) → matches suppliers.id
        // =========================================================
        $products = [
            // FOOD
            [
                'product_type' => 'FOOD',
                'name' => 'Indomie Goreng',
                'price' => 3500.00,
                'stock' => 200,
                'description' => 'Mi instan goreng rasa ayam bawang',
                'supplier_id' => $supFoodId,
                'expiry_date' => '2026-12-31',
                'category' => 'Mie Instan',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_type' => 'FOOD',
                'name' => 'Chitato Original 68g',
                'price' => 12000.00,
                'stock' => 80,
                'description' => 'Keripik kentang rasa original',
                'supplier_id' => $supFoodId,
                'expiry_date' => '2026-08-15',
                'category' => 'Snack',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // BEVERAGE
            [
                'product_type' => 'BEVERAGE',
                'name' => 'Aqua 600ml',
                'price' => 4000.00,
                'stock' => 300,
                'description' => 'Air mineral dalam kemasan botol',
                'supplier_id' => $supDrinkId,
                'expiry_date' => '2027-01-01',
                'category' => 'Air Mineral',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_type' => 'BEVERAGE',
                'name' => 'Teh Botol Sosro 450ml',
                'price' => 5000.00,
                'stock' => 150,
                'description' => 'Teh manis dalam botol kaca',
                'supplier_id' => $supDrinkId,
                'expiry_date' => '2026-09-30',
                'category' => 'Minuman Teh',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // ELECTRONIC
            [
                'product_type' => 'ELECTRONIC',
                'name' => 'Baterai ABC AA Isi 2',
                'price' => 8500.00,
                'stock' => 60,
                'description' => 'Baterai alkaline ukuran AA isi 2 pcs',
                'supplier_id' => $supElecId,
                'expiry_date' => null,
                'category' => 'Baterai',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'product_type' => 'ELECTRONIC',
                'name' => 'Lampu LED Philips 9W',
                'price' => 45000.00,
                'stock' => 25,
                'description' => 'Lampu LED hemat energi 9 Watt',
                'supplier_id' => $supElecId,
                'expiry_date' => null,
                'category' => 'Lampu',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->db->table('products')->insertBatch($products);

        // Read back auto-generated BIGINT IDs
        $productRows = $this->db->table('products')
            ->select('id, name')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $pidIndomie = $productRows[0]['id'];
        $pidChitato = $productRows[1]['id'];
        $pidAqua = $productRows[2]['id'];
        $pidTehBotol = $productRows[3]['id'];
        $pidBaterai = $productRows[4]['id'];
        $pidLampu = $productRows[5]['id'];

        // =========================================================
        // 5. TRANSACTIONS  (VARCHAR PK — UUID)
        //    customer_id → VARCHAR(50) matches customers.id
        // =========================================================
        $transactions = [
            [
                'id' => generate_uuid(),
                'transaction_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'customer_id' => $custBudiId,
                'total_amount' => 0.00,
                'status' => 'COMPLETED',
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => generate_uuid(),
                'transaction_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'customer_id' => $custSitiId,
                'total_amount' => 0.00,
                'status' => 'COMPLETED',
                'notes' => 'Pembayaran tunai',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => generate_uuid(),
                'transaction_date' => $now,
                'customer_id' => null, // walk-in anonymous
                'total_amount' => 0.00,
                'status' => 'PENDING',
                'notes' => 'Belum lunas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->db->table('transactions')->insertBatch($transactions);

        $txId1 = $transactions[0]['id'];
        $txId2 = $transactions[1]['id'];
        $txId3 = $transactions[2]['id'];

        // =========================================================
        // 6. TRANSACTION_DETAILS  (BIGINT PK AUTO_INCREMENT)
        //    transaction_id → VARCHAR(50)  matches transactions.id
        //    product_id     → BIGINT        matches products.id
        // =========================================================
        $details = [
            // Tx 1: Budi → Indomie x5, Aqua x2
            ['transaction_id' => $txId1, 'product_id' => $pidIndomie, 'quantity' => 5, 'unit_price' => 3500.00, 'subtotal' => 5 * 3500.00],
            ['transaction_id' => $txId1, 'product_id' => $pidAqua, 'quantity' => 2, 'unit_price' => 4000.00, 'subtotal' => 2 * 4000.00],
            // Tx 2: Siti → Chitato x1, Teh Botol x3, Baterai x1
            ['transaction_id' => $txId2, 'product_id' => $pidChitato, 'quantity' => 1, 'unit_price' => 12000.00, 'subtotal' => 12000.00],
            ['transaction_id' => $txId2, 'product_id' => $pidTehBotol, 'quantity' => 3, 'unit_price' => 5000.00, 'subtotal' => 3 * 5000.00],
            ['transaction_id' => $txId2, 'product_id' => $pidBaterai, 'quantity' => 1, 'unit_price' => 8500.00, 'subtotal' => 8500.00],
            // Tx 3: Walk-in → Lampu x2
            ['transaction_id' => $txId3, 'product_id' => $pidLampu, 'quantity' => 2, 'unit_price' => 45000.00, 'subtotal' => 2 * 45000.00],
        ];

        $this->db->table('transaction_details')->insertBatch($details);

        // Update denormalized totals on transactions
        $txTotals = [
            $txId1 => (5 * 3500) + (2 * 4000),             // 25,500
            $txId2 => 12000 + (3 * 5000) + 8500,           // 35,500
            $txId3 => 2 * 45000,                             // 90,000
        ];
        foreach ($txTotals as $txId => $total) {
            $this->db->table('transactions')
                ->where('id', $txId)
                ->update(['total_amount' => $total, 'updated_at' => $now]);
        }

        // Summary
        echo "\n✅  MinimarketSeeder completed successfully.\n";
        echo "    → Suppliers      : " . count($suppliers) . " rows\n";
        echo "    → Customers      : " . count($customers) . " rows\n";
        echo "    → Users          : {$usersSeeded} rows\n";
        echo "    → Products       : " . count($products) . " rows\n";
        echo "    → Transactions   : " . count($transactions) . " rows\n";
        echo "    → Tx Details     : " . count($details) . " rows\n";
        echo "    → Tx Totals      : Rp25.500 | Rp35.500 | Rp90.000\n";
    }
}
