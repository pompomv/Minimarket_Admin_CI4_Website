-- ============================================================
-- MIGRATION: Tambah kolom API untuk integrasi Flutter (Kasir)
-- Jalankan di database: minimarket_db (yang dipakai CI4)
-- ============================================================

-- 1. Tambah kolom user_id, payment_method, bayar ke tabel transactions
--    (kolom ini diperlukan untuk mencatat kasir yang memproses transaksi)
ALTER TABLE transactions
    ADD COLUMN IF NOT EXISTS user_id     INT          NULL AFTER customer_id,
    ADD COLUMN IF NOT EXISTS payment_method VARCHAR(20) NOT NULL DEFAULT 'cash' AFTER user_id,
    ADD COLUMN IF NOT EXISTS bayar       DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER payment_method;

-- 2. Tambah foreign key ke tabel users (opsional, bisa di-skip jika error)
-- ALTER TABLE transactions
--     ADD CONSTRAINT fk_transactions_user
--     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- 3. Pastikan kolom role di tabel users sudah ada
--    (CI4 users model sudah punya kolom role)
-- Cek: DESCRIBE users;

-- ============================================================
-- VERIFIKASI: Cek struktur tabel yang diperbarui
-- ============================================================
-- DESCRIBE transactions;
-- SELECT * FROM users LIMIT 5;
