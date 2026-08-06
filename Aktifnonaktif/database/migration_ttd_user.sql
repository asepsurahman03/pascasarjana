-- ============================================================
-- MIGRATION: Tambah kolom ttd_path pada tabel users
-- Untuk fitur upload tanda tangan kaprodi dari Manage Users
-- Jalankan query ini di database yang sudah ada (hosting)
-- ============================================================

ALTER TABLE `users`
  ADD COLUMN `ttd_path` VARCHAR(255) DEFAULT NULL
    COMMENT 'Path file tanda tangan kaprodi (relatif dari root)'
  AFTER `avatar`;

-- Verifikasi (aman untuk hosting shared)
SHOW COLUMNS FROM `users` LIKE 'ttd_path';

