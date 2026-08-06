-- ============================================================
-- DATABASE: pengunduran_diri_mahasiswa
-- Universitas Nusa Putra - Sistem Pengunduran Diri Mahasiswa
-- Updated: 2026-07-09 (Disesuaikan lengkap dengan semua model & controller)
-- ============================================================

DROP DATABASE IF EXISTS `pengunduran_diri_mahasiswa`;

CREATE DATABASE `pengunduran_diri_mahasiswa`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `pengunduran_diri_mahasiswa`;

-- ============================================================
-- TABLE: users (Admin, Mahasiswa & Kaprodi accounts)
-- Kolom google_id & avatar ditambahkan sesuai User.php & AuthController
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`            VARCHAR(100) NOT NULL,
  `email`           VARCHAR(150) NOT NULL UNIQUE,
  `password`        VARCHAR(255) NOT NULL DEFAULT '',
  `role`            ENUM('admin','mahasiswa','kaprodi') NOT NULL DEFAULT 'mahasiswa',
  `program_studi`   VARCHAR(100) DEFAULT NULL,
  `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
  `google_id`       VARCHAR(100) DEFAULT NULL COMMENT 'Google OAuth ID',
  `avatar`          VARCHAR(255) DEFAULT NULL COMMENT 'URL foto profil dari Google atau upload',
  `auth_provider`   ENUM('local','google') NOT NULL DEFAULT 'local' COMMENT 'Metode autentikasi',
  `remember_token`  VARCHAR(100) DEFAULT NULL,
  `last_login`      DATETIME DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_google_id` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: mahasiswa (Student profiles)
-- ============================================================
CREATE TABLE IF NOT EXISTS `mahasiswa` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         INT UNSIGNED DEFAULT NULL,
  `nim`             VARCHAR(20) NOT NULL UNIQUE,
  `nama`            VARCHAR(100) NOT NULL,
  `email`           VARCHAR(150) DEFAULT NULL,
  `tanggal_lahir`   DATE NOT NULL,
  `angkatan`        YEAR NOT NULL,
  `program_studi`   VARCHAR(100) NOT NULL,
  `status_beasiswa` ENUM('Beasiswa','Non Beasiswa') NOT NULL DEFAULT 'Non Beasiswa',
  `no_hp`           VARCHAR(20) DEFAULT NULL,
  `alamat`          TEXT DEFAULT NULL,
  `foto`            VARCHAR(255) DEFAULT NULL,
  `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_surat`      VARCHAR(50) DEFAULT NULL UNIQUE,
  `mahasiswa_id`     INT UNSIGNED NOT NULL,
  `tanggal_surat`    DATE NOT NULL,
  `nama_pemohon`     VARCHAR(100) NOT NULL,
  `nim`              VARCHAR(20) NOT NULL,
  `angkatan`         YEAR NOT NULL,
  `program_studi`    VARCHAR(100) NOT NULL,
  `status_mahasiswa` ENUM('Beasiswa','Non Beasiswa') NOT NULL DEFAULT 'Non Beasiswa',
  `bersedia_mundur`  ENUM('YES','NO') NOT NULL DEFAULT 'NO',
  `alasan`           TEXT NOT NULL,
  `status`           ENUM('Draft','Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `catatan_admin`    TEXT DEFAULT NULL,
  `approved_by`      INT UNSIGNED DEFAULT NULL,
  `approved_at`      DATETIME DEFAULT NULL,
  `ip_address`       VARCHAR(45) DEFAULT NULL,
  `qr_code`          VARCHAR(255) DEFAULT NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pengunduran_id`  INT UNSIGNED NOT NULL,
  `mahasiswa_id`    INT UNSIGNED NOT NULL,
  `signature_data`  LONGTEXT NOT NULL COMMENT 'Base64 PNG dari canvas',
  `signature_path`  VARCHAR(255) DEFAULT NULL COMMENT 'Path file PNG tersimpan',
  `ip_address`      VARCHAR(45) DEFAULT NULL,
  `user_agent`      VARCHAR(500) DEFAULT NULL,
  `signed_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `identifier`   VARCHAR(150) NOT NULL COMMENT 'email atau NIM',
  `ip_address`   VARCHAR(45) NOT NULL,
  `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_identifier` (`identifier`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA: Default Admin User
-- Password: nusaputraku
-- Hash dibuat dengan: password_hash('nusaputraku', PASSWORD_BCRYPT, ['cost'=>12])
-- ============================================================
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `program_studi`, `is_active`) VALUES
('Administrator', 'admin@nusaputra.ac.id',
 '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm',
 'admin', NULL, 1);

-- ============================================================
-- SEED DATA: Kaprodi Users (satu per program studi)
-- Password: nusaputraku
-- ============================================================
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `program_studi`, `is_active`) VALUES
('Dr. Ahmad Mukhtar, M.Kom',      'kaprodi.teknik.informatika@nusaputra.ac.id',  '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Teknik Informatika', 1),
('Dr. Budi Raharjo, M.M.',         'kaprodi.manajemen@nusaputra.ac.id',           '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Manajemen', 1),
('Dr. Hendra Kusuma, S.E., M.Ak.', 'kaprodi.akuntansi@nusaputra.ac.id',           '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Akuntansi', 1),
('Dr. Ir. Wahyu Pratama, M.T.',    'kaprodi.teknik.sipil@nusaputra.ac.id',        '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Teknik Sipil', 1),
('Dr. Rizki Nugroho, M.Kom.',      'kaprodi.sistem.informasi@nusaputra.ac.id',    '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Sistem Informasi', 1),
('Dr. Siti Aminah, S.H., M.H.',    'kaprodi.hukum@nusaputra.ac.id',               '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Hukum', 1),
('Dr. Rini Handayani, M.Pd.',      'kaprodi.pgsd@nusaputra.ac.id',                '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Pendidikan Guru Sekolah Dasar', 1),
('Dr. Ir. Eko Santoso, M.T.',      'kaprodi.teknik.mesin@nusaputra.ac.id',        '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Teknik Mesin', 1),
('Dr. Ir. Farid Hidayat, M.T.',    'kaprodi.teknik.elektro@nusaputra.ac.id',      '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Teknik Elektro', 1),
('Dr. Indah Permatasari, M.Ds.',   'kaprodi.dkv@nusaputra.ac.id',                 '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Desain Komunikasi Visual', 1),
('Dr. Ayu Lestari, S.Gz., M.Gizi.','kaprodi.gizi@nusaputra.ac.id',               '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Gizi', 1),
('Dr. Dewi Rahayu, M.Si.',         'kaprodi.bioteknologi@nusaputra.ac.id',        '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Bioteknologi', 1),
('Dr. Nita Kurniawati, M.Tp.',     'kaprodi.teknologi.pangan@nusaputra.ac.id',   '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Teknologi Pangan', 1),
('Dr. Mira Susanti, M.Kes.',       'kaprodi.adm.kesehatan@nusaputra.ac.id',      '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S1 - Administrasi Kesehatan', 1),
('Ns. Rina Marlina, S.Kep., M.Kep.','kaprodi.keperawatan@nusaputra.ac.id',       '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'D3 - Keperawatan', 1),
('Dr. Ahmad Mukhtar, M.Kom.',      'kaprodi.s2.informatika@nusaputra.ac.id',      '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S2 - Magister Informatika', 1),
('Dr. Armansyah, S.H., M.H.',    'kaprodi.s2.hukum@nusaputra.ac.id',           '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S2 - Magister Hukum', 1),
('Dr. Rini Handayani, M.Pd.',      'kaprodi.s2.pedagogi@nusaputra.ac.id',        '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S2 - Magister Pedagogi', 1),
('Dr. Budi Raharjo, M.M.',         'kaprodi.s2.manajemen@nusaputra.ac.id',       '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S2 - Magister Manajemen', 1),
('Prof. Dr. Ir. Yusuf Hidayat, M.Sc.','kaprodi.s3.ilmu.komputer@nusaputra.ac.id','$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'kaprodi', 'S3 - Doktor Ilmu Komputer', 1);

-- ============================================================
-- SEED DATA: Sample Mahasiswa Users
-- Password: nusaputraku
-- ============================================================
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `is_active`) VALUES
('Ahmad Fauzi',   'ahmad.fauzi@student.nusaputra.ac.id',   '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'mahasiswa', 1),
('Siti Rahayu',   'siti.rahayu@student.nusaputra.ac.id',   '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'mahasiswa', 1),
('Budi Santoso',  'budi.santoso@student.nusaputra.ac.id',  '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'mahasiswa', 1),
('Dewi Anggraeni','dewi.anggraeni@student.nusaputra.ac.id','$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'mahasiswa', 1),
('Rizky Pratama', 'rizky.pratama@student.nusaputra.ac.id', '$2y$12$2MVP1OeyVKFeYS4s1E8cAOVLtg5qtVprIzeSlFzQq3QKFupEZxZNm', 'mahasiswa', 1);

-- ============================================================
-- SEED DATA: Sample Mahasiswa Profiles
-- user_id disesuaikan: kaprodi = id 2-21, mahasiswa = id 22-26
-- ============================================================
INSERT INTO `mahasiswa` (`user_id`, `nim`, `nama`, `email`, `tanggal_lahir`, `angkatan`, `program_studi`, `status_beasiswa`, `no_hp`, `alamat`) VALUES
(22, '2024001001', 'Ahmad Fauzi',   'ahmad.fauzi@student.nusaputra.ac.id',   '1999-05-15', 2024, 'S2 - Magister Informatika', 'Non Beasiswa', '081234567890', 'Jl. Merdeka No.1, Sukabumi'),
(23, '2024002002', 'Siti Rahayu',   'siti.rahayu@student.nusaputra.ac.id',   '2000-03-22', 2024, 'S2 - Magister Manajemen',   'Beasiswa',     '082345678901', 'Jl. Ahmad Yani No.5, Sukabumi'),
(24, '2023001003', 'Budi Santoso',  'budi.santoso@student.nusaputra.ac.id',  '1998-11-08', 2023, 'S2 - Magister Hukum',       'Non Beasiswa', '083456789012', 'Jl. Sudirman No.10, Sukabumi'),
(25, '2024003004', 'Dewi Anggraeni','dewi.anggraeni@student.nusaputra.ac.id','2001-07-30', 2024, 'S1 - Teknik Informatika',   'Beasiswa',     '084567890123', 'Jl. Diponegoro No.15, Sukabumi'),
(26, '2023004005', 'Rizky Pratama', 'rizky.pratama@student.nusaputra.ac.id', '2000-12-01', 2023, 'S1 - Manajemen',            'Non Beasiswa', '085678901234', 'Jl. Gatot Subroto No.20, Sukabumi');

-- ============================================================
-- SEED DATA: Sample Pengajuan Pengunduran Diri
-- ============================================================
INSERT INTO `pengunduran_diri` (`nomor_surat`, `mahasiswa_id`, `tanggal_surat`, `nama_pemohon`, `nim`, `angkatan`, `program_studi`, `status_mahasiswa`, `bersedia_mundur`, `alasan`, `status`) VALUES
('NPU/PD/2024/001', 1, '2024-06-01', 'Ahmad Fauzi',   '2024001001', 2024, 'S2 - Magister Informatika', 'Non Beasiswa', 'YES', 'Alasan keluarga dan kondisi ekonomi yang tidak memungkinkan untuk melanjutkan studi saat ini.', 'Approved'),
('NPU/PD/2024/002', 2, '2024-06-15', 'Siti Rahayu',   '2024002002', 2024, 'S2 - Magister Manajemen',   'Beasiswa',     'YES', 'Mendapatkan tawaran pekerjaan yang mengharuskan pindah ke luar kota dan tidak bisa mengikuti perkuliahan.', 'Pending'),
(NULL,               3, '2024-07-01', 'Budi Santoso',  '2023001003', 2023, 'S2 - Magister Hukum',       'Non Beasiswa', 'NO',  'Ingin melanjutkan studi di luar negeri dengan program beasiswa yang lebih sesuai bidang minat.', 'Rejected'),
(NULL,               4, '2024-07-05', 'Dewi Anggraeni','2024003004', 2024, 'S1 - Teknik Informatika',   'Beasiswa',     'YES', 'Kondisi kesehatan yang memerlukan istirahat panjang dan perawatan intensif.', 'Draft'),
(NULL,               5, '2024-07-08', 'Rizky Pratama', '2023004005', 2023, 'S1 - Manajemen',            'Non Beasiswa', 'YES', 'Faktor finansial keluarga yang tidak mendukung dan harus membantu usaha orang tua.', 'Pending');

-- ============================================================
-- SEED DATA: Default Settings (Sesuai dengan semua program studi)
-- ============================================================
INSERT INTO `settings` (`key_name`, `value`, `description`) VALUES
('app_name',            'Sistem Pengunduran Diri Mahasiswa', 'Nama aplikasi'),
('university_name',     'Universitas Nusa Putra',            'Nama universitas'),
('university_address',  'Jl. Cibolang Kaler No.21, Cisaat, Kec. Cisaat, Kabupaten Sukabumi, Jawa Barat 43152', 'Alamat universitas'),
('university_phone',    '(0266) 222 678',                   'Telepon universitas'),
('university_email',    'info@nusaputra.ac.id',             'Email universitas'),
('university_website',  'www.nusaputra.ac.id',              'Website universitas'),
('nomor_surat_prefix',  'NPU/PD/',                          'Prefix nomor surat'),
('nomor_surat_counter', '5',                                'Counter nomor surat terakhir'),
('session_timeout',     '3600',                             'Session timeout dalam detik'),
('max_login_attempts',  '5',                                'Maksimal percobaan login'),
('app_logo',            'logo-nusaputra.png',               'Logo aplikasi'),
('dark_mode_default',   '0',                                'Dark mode default (0=off, 1=on)'),
-- Kaprodi per program studi (sesuai AdminController.php -> saveSettings())
('ketua_prodi_s1_teknik_informatika',          'Dr. Ahmad Mukhtar, M.Kom',          'Ketua Prodi S1 Teknik Informatika'),
('ketua_prodi_s1_manajemen',                   'Dr. Budi Raharjo, M.M.',            'Ketua Prodi S1 Manajemen'),
('ketua_prodi_s1_akuntansi',                   'Dr. Hendra Kusuma, S.E., M.Ak.',    'Ketua Prodi S1 Akuntansi'),
('ketua_prodi_s1_teknik_sipil',                'Dr. Ir. Wahyu Pratama, M.T.',       'Ketua Prodi S1 Teknik Sipil'),
('ketua_prodi_s1_sistem_informasi',            'Dr. Rizki Nugroho, M.Kom.',         'Ketua Prodi S1 Sistem Informasi'),
('ketua_prodi_s1_hukum',                       'Dr. Siti Aminah, S.H., M.H.',       'Ketua Prodi S1 Hukum'),
('ketua_prodi_s1_pendidikan_guru_sekolah_dasar','Dr. Rini Handayani, M.Pd.',        'Ketua Prodi S1 PGSD'),
('ketua_prodi_s1_teknik_mesin',                'Dr. Ir. Eko Santoso, M.T.',         'Ketua Prodi S1 Teknik Mesin'),
('ketua_prodi_s1_teknik_elektro',              'Dr. Ir. Farid Hidayat, M.T.',       'Ketua Prodi S1 Teknik Elektro'),
('ketua_prodi_s1_desain_komunikasi_visual',    'Dr. Indah Permatasari, M.Ds.',      'Ketua Prodi S1 DKV'),
('ketua_prodi_s1_gizi',                        'Dr. Ayu Lestari, S.Gz., M.Gizi.',  'Ketua Prodi S1 Gizi'),
('ketua_prodi_s1_bioteknologi',                'Dr. Dewi Rahayu, M.Si.',            'Ketua Prodi S1 Bioteknologi'),
('ketua_prodi_s1_teknologi_pangan',            'Dr. Nita Kurniawati, M.Tp.',        'Ketua Prodi S1 Teknologi Pangan'),
('ketua_prodi_s1_administrasi_kesehatan',      'Dr. Mira Susanti, M.Kes.',          'Ketua Prodi S1 Administrasi Kesehatan'),
('ketua_prodi_d3_keperawatan',                 'Ns. Rina Marlina, S.Kep., M.Kep.', 'Ketua Prodi D3 Keperawatan'),
('ketua_prodi_s2_magister_informatika',        'Dr. Ahmad Mukhtar, M.Kom.',         'Ketua Prodi S2 Magister Informatika'),
('ketua_prodi_s2_magister_hukum',              'Dr. Armansyah, S.H., M.H.',       'Plt. Ketua Program Studi Magister Hukum'),
('ketua_prodi_s2_magister_pedagogi',           'Dr. Rini Handayani, M.Pd.',         'Ketua Prodi S2 Magister Pedagogi'),
('ketua_prodi_s2_magister_manajemen',          'Dr. Budi Raharjo, M.M.',            'Ketua Prodi S2 Magister Manajemen'),
('ketua_prodi_s3_doktor_ilmu_komputer',        'Prof. Dr. Ir. Yusuf Hidayat, M.Sc.','Ketua Prodi S3 Doktor Ilmu Komputer');

-- ============================================================
-- SEED DATA: Sample Activity Logs
-- ============================================================
INSERT INTO `activity_logs` (`user_id`, `action`, `description`, `model`, `model_id`) VALUES
(1, 'LOGIN',            'Admin login ke sistem',                             NULL,              NULL),
(1, 'APPROVE',          'Pengajuan #1 (Ahmad Fauzi) disetujui',              'pengunduran_diri', 1),
(1, 'REJECT',           'Pengajuan #3 (Budi Santoso) ditolak',               'pengunduran_diri', 3),
(1, 'UPDATE_SETTINGS',  'Pengaturan sistem diperbarui',                      NULL,              NULL),
(22,'LOGIN',            'Mahasiswa Ahmad Fauzi login',                       NULL,              NULL),
(22,'CREATE_PENGAJUAN', 'Pengajuan pengunduran diri dibuat oleh Ahmad Fauzi','pengunduran_diri', 1);

-- ============================================================
-- VERIFIKASI AKHIR
-- ============================================================
SELECT 'Database pengunduran_diri_mahasiswa berhasil dibuat!' AS status;
SELECT TABLE_NAME, TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'pengunduran_diri_mahasiswa';
