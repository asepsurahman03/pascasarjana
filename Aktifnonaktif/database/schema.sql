-- ============================================================
-- DATABASE: pengunduran_diri_mahasiswa
-- Universitas Nusa Putra - Sistem Pengunduran Diri Mahasiswa
-- Created: 2026-07-04
-- ============================================================

CREATE DATABASE IF NOT EXISTS `pengunduran_diri_mahasiswa`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `pengunduran_diri_mahasiswa`;

-- ============================================================
-- TABLE: users (Admin & Mahasiswa accounts)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`          VARCHAR(100) NOT NULL,
  `email`         VARCHAR(150) NOT NULL UNIQUE,
  `password`      VARCHAR(255) NOT NULL,
  `role`          ENUM('admin','mahasiswa','kaprodi') NOT NULL DEFAULT 'mahasiswa',
  `program_studi` VARCHAR(100) DEFAULT NULL,
  `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `last_login`    DATETIME DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: mahasiswa (Student profiles)
-- ============================================================
CREATE TABLE IF NOT EXISTS `mahasiswa` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED DEFAULT NULL,
  `nim`           VARCHAR(20) NOT NULL UNIQUE,
  `nama`          VARCHAR(100) NOT NULL,
  `email`         VARCHAR(150) DEFAULT NULL,
  `tanggal_lahir` DATE NOT NULL,
  `angkatan`      YEAR NOT NULL,
  `program_studi` VARCHAR(100) NOT NULL,
  `status_beasiswa` ENUM('Beasiswa','Non Beasiswa') NOT NULL DEFAULT 'Non Beasiswa',
  `no_hp`         VARCHAR(20) DEFAULT NULL,
  `alamat`        TEXT DEFAULT NULL,
  `foto`          VARCHAR(255) DEFAULT NULL,
  `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_nim` (`nim`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_program_studi` (`program_studi`),
  KEY `idx_angkatan` (`angkatan`),
  CONSTRAINT `fk_mahasiswa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: pengunduran_diri (Resignation submissions)
-- ============================================================
CREATE TABLE IF NOT EXISTS `pengunduran_diri` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_surat`     VARCHAR(50) DEFAULT NULL UNIQUE,
  `mahasiswa_id`    INT UNSIGNED NOT NULL,
  `tanggal_surat`   DATE NOT NULL,
  `nama_pemohon`    VARCHAR(100) NOT NULL,
  `nim`             VARCHAR(20) NOT NULL,
  `angkatan`        YEAR NOT NULL,
  `program_studi`   VARCHAR(100) NOT NULL,
  `status_mahasiswa` ENUM('Beasiswa','Non Beasiswa') NOT NULL DEFAULT 'Non Beasiswa',
  `bersedia_mundur` ENUM('YES','NO') NOT NULL DEFAULT 'NO',
  `alasan`          TEXT NOT NULL,
  `status`          ENUM('Draft','Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `catatan_admin`   TEXT DEFAULT NULL,
  `approved_by`     INT UNSIGNED DEFAULT NULL,
  `approved_at`     DATETIME DEFAULT NULL,
  `ip_address`      VARCHAR(45) DEFAULT NULL,
  `qr_code`         VARCHAR(255) DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mahasiswa_id` (`mahasiswa_id`),
  KEY `idx_status` (`status`),
  KEY `idx_tanggal` (`tanggal_surat`),
  KEY `idx_nim` (`nim`),
  KEY `idx_nomor_surat` (`nomor_surat`),
  CONSTRAINT `fk_pd_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pd_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: digital_signature (Canvas signature data)
-- ============================================================
CREATE TABLE IF NOT EXISTS `digital_signature` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pengunduran_id`    INT UNSIGNED NOT NULL,
  `mahasiswa_id`      INT UNSIGNED NOT NULL,
  `signature_data`    LONGTEXT NOT NULL COMMENT 'Base64 PNG dari canvas',
  `signature_path`    VARCHAR(255) DEFAULT NULL COMMENT 'Path file PNG tersimpan',
  `ip_address`        VARCHAR(45) DEFAULT NULL,
  `user_agent`        VARCHAR(500) DEFAULT NULL,
  `signed_at`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pengunduran_id` (`pengunduran_id`),
  KEY `idx_mahasiswa_id` (`mahasiswa_id`),
  CONSTRAINT `fk_ds_pengunduran` FOREIGN KEY (`pengunduran_id`) REFERENCES `pengunduran_diri` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ds_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: activity_logs (Admin activity tracking)
-- ============================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `action`      VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `model`       VARCHAR(50) DEFAULT NULL,
  `model_id`    INT UNSIGNED DEFAULT NULL,
  `old_values`  JSON DEFAULT NULL,
  `new_values`  JSON DEFAULT NULL,
  `ip_address`  VARCHAR(45) DEFAULT NULL,
  `user_agent`  VARCHAR(500) DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: settings (System configuration)
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key_name`    VARCHAR(100) NOT NULL UNIQUE,
  `value`       TEXT DEFAULT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: login_attempts (Rate limiting)
-- ============================================================
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `identifier` VARCHAR(150) NOT NULL COMMENT 'email atau NIM',
  `ip_address` VARCHAR(45) NOT NULL,
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_identifier` (`identifier`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA: Default Admin User
-- ============================================================
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `is_active`) VALUES
(
  'Administrator',
  'admin@nusaputra.ac.id',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- nusaputraku
  'admin',
  1
);

-- ============================================================
-- SEED DATA: Sample Mahasiswa Users
-- ============================================================
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `is_active`) VALUES
('Ahmad Fauzi', 'ahmad.fauzi@student.nusaputra.ac.id', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 1),
('Siti Rahayu', 'siti.rahayu@student.nusaputra.ac.id', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 1),
('Budi Santoso', 'budi.santoso@student.nusaputra.ac.id', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 1);

-- ============================================================
-- SEED DATA: Sample Mahasiswa Profiles
-- ============================================================
INSERT INTO `mahasiswa` (`user_id`, `nim`, `nama`, `email`, `tanggal_lahir`, `angkatan`, `program_studi`, `status_beasiswa`, `no_hp`) VALUES
(2, '2024001001', 'Ahmad Fauzi', 'ahmad.fauzi@student.nusaputra.ac.id', '1999-05-15', 2024, 'Magister Informatika', 'Non Beasiswa', '081234567890'),
(3, '2024002002', 'Siti Rahayu', 'siti.rahayu@student.nusaputra.ac.id', '2000-03-22', 2024, 'Magister Manajemen', 'Beasiswa', '082345678901'),
(4, '2023001003', 'Budi Santoso', 'budi.santoso@student.nusaputra.ac.id', '1998-11-08', 2023, 'Magister Hukum', 'Non Beasiswa', '083456789012');

-- ============================================================
-- SEED DATA: Default Settings
-- ============================================================
INSERT INTO `settings` (`key_name`, `value`, `description`) VALUES
('app_name', 'Sistem Pengunduran Diri Mahasiswa', 'Nama aplikasi'),
('university_name', 'Universitas Nusa Putra', 'Nama universitas'),
('university_address', 'Jl. Cibolang Kaler No.21, Cisaat, Kec. Cisaat, Kabupaten Sukabumi, Jawa Barat 43152', 'Alamat universitas'),
('university_phone', '(0266) 222 678', 'Telepon universitas'),
('university_email', 'info@nusaputra.ac.id', 'Email universitas'),
('university_website', 'www.nusaputra.ac.id', 'Website universitas'),
('nomor_surat_prefix', 'NPU/PD/', 'Prefix nomor surat'),
('nomor_surat_counter', '1', 'Counter nomor surat terakhir'),
('session_timeout', '3600', 'Session timeout dalam detik'),
('max_login_attempts', '5', 'Maksimal percobaan login'),
('app_logo', 'logo-nusaputra.png', 'Logo aplikasi'),
('ketua_prodi_informatika', 'Dr. Ahmad Mukhtar, M.Kom', 'Ketua Program Studi Informatika'),
('ketua_prodi_hukum', 'Dr. Siti Aminah, S.H., M.H.', 'Ketua Program Studi Hukum'),
('ketua_prodi_manajemen', 'Dr. Budi Raharjo, M.M.', 'Ketua Program Studi Manajemen'),
('ketua_prodi_pedagogi', 'Dr. Rini Handayani, M.Pd.', 'Ketua Program Studi Pedagogi'),
('dark_mode_default', '0', 'Dark mode default (0=off, 1=on)');
