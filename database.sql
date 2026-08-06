-- ============================================
-- DATABASE: pascasarjana_unp
-- Sistem Dashboard Admin Pascasarjana NPU
-- ============================================

CREATE DATABASE IF NOT EXISTS pascasarjana_unp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pascasarjana_unp;

CREATE TABLE IF NOT EXISTS prodi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    jenjang ENUM('S2','S3') NOT NULL DEFAULT 'S2',
    kaprodi VARCHAR(100),
    sekretaris VARCHAR(100),
    kontak VARCHAR(20),
    no_wa_grup VARCHAR(20),
    prefix_surat VARCHAR(20),
    kota_surat VARCHAR(50) DEFAULT 'Sukabumi',
    warna_hex VARCHAR(7) DEFAULT '#60a5fa',
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin','admin_prodi') NOT NULL DEFAULT 'admin_prodi',
    prodi_id INT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    foto VARCHAR(255),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    prodi_id INT NOT NULL,
    angkatan YEAR NOT NULL,
    status ENUM('Aktif','Cuti','Lulus','DO') DEFAULT 'Aktif',
    no_hp VARCHAR(20),
    email VARCHAR(100),
    alamat TEXT,
    judul_tesis TEXT,
    dosen_pembimbing VARCHAR(100),
    catatan_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id)
);

CREATE TABLE IF NOT EXISTS template_surat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prodi_id INT NULL,
    jenis_surat VARCHAR(100) NOT NULL,
    nama_template VARCHAR(150),
    isi_template LONGTEXT,
    header_html TEXT,
    variabel_tersedia TEXT,
    is_massal TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS surat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_surat VARCHAR(100) NOT NULL UNIQUE,
    jenis_surat VARCHAR(100) NOT NULL,
    prodi_id INT NOT NULL,
    nama_penerima VARCHAR(100),
    jenis_penerima VARCHAR(50) DEFAULT 'individual',
    nim_nidn VARCHAR(30),
    perihal VARCHAR(255),
    keperluan TEXT,
    tanggal DATE NOT NULL,
    hari VARCHAR(20),
    kota VARCHAR(50) DEFAULT 'Sukabumi',
    status ENUM('Draf','Proses','Selesai','Terarsip') DEFAULT 'Draf',
    lampiran VARCHAR(100) DEFAULT '-',
    isi_surat LONGTEXT,
    file_pdf VARCHAR(255),
    created_by INT NOT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    prodi_id INT NULL,
    prioritas ENUM('Tinggi','Sedang','Rendah') DEFAULT 'Sedang',
    deadline DATE,
    status ENUM('Belum','Dikerjakan','Selesai') DEFAULT 'Belum',
    label_warna VARCHAR(7) DEFAULT '#60a5fa',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS catatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isi TEXT NOT NULL,
    prodi_id INT NULL,
    warna VARCHAR(7) DEFAULT '#f59e0b',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS whatsapp_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tujuan VARCHAR(50) NOT NULL,
    jenis_tujuan ENUM('individu','grup') DEFAULT 'individu',
    pesan TEXT NOT NULL,
    status ENUM('Terkirim','Gagal','Pending') DEFAULT 'Pending',
    waktu_kirim TIMESTAMP NULL,
    jadwal_kirim TIMESTAMP NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    prodi_id INT NULL,
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME,
    jenis_event ENUM('Seminar Proposal','Sidang Tesis','Ujian Komprehensif','Wisuda','Rapat Prodi','Deadline','Lainnya') DEFAULT 'Lainnya',
    warna VARCHAR(7) DEFAULT '#60a5fa',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prodi_id) REFERENCES prodi(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pesan TEXT NOT NULL,
    jenis ENUM('tugas','jadwal','surat','sistem') DEFAULT 'sistem',
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    aksi VARCHAR(100) NOT NULL,
    modul VARCHAR(50),
    detail TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- DATA DUMMY
-- ============================================

INSERT IGNORE INTO prodi (kode, nama, jenjang, kaprodi, sekretaris, kontak, no_wa_grup, warna_hex, deskripsi) VALUES
('MIF', 'Magister Informatika', 'S2', 'Dr. Ahmad Fauzi, M.Kom', 'Siti Rahayu, M.Kom', '081234567890', '6281234567890', '#60a5fa', 'Program Studi S2 Magister Informatika'),
('MM', 'Magister Manajemen', 'S2', 'Dr. Budi Santoso, M.M', 'Dewi Kurniawati, M.M', '081234567891', '6281234567891', '#22c55e', 'Program Studi S2 Magister Manajemen'),
('MH', 'Magister Hukum', 'S2', 'Dr. Armansyah, S.H., M.H', 'Rina Sari, S.H., M.H', '081234567892', '6281234567892', '#f59e0b', 'Program Studi S2 Magister Hukum'),
('MP', 'Magister Pedagogi', 'S2', 'Dr. Dian Lestari, M.Pd', 'Hendra Gunawan, M.Pd', '081234567893', '6281234567893', '#a78bfa', 'Program Studi S2 Magister Pedagogi'),
('DIK', 'Doktor Ilmu Komputer', 'S3', 'Prof. Dr. Eko Prasetyo, M.Cs', 'Farida Hanum, M.Cs', '081234567894', '6281234567894', '#f87171', 'Program Studi S3 Doktor Ilmu Komputer');

-- Password 'password' bcrypt hash (akan di-update oleh setup script)
INSERT IGNORE INTO users (username, password_hash, role, prodi_id, nama, email) VALUES
('admin', '$2y$10$1MpY29/1wBZWrhGbQAAQUOL/WdMQvUA3ynkYHvetv4Rkyd0EnqGfm', 'super_admin', NULL, 'Administrator', 'admin@gmail.com'),
('superadmin', '$2y$10$1MpY29/1wBZWrhGbQAAQUOL/WdMQvUA3ynkYHvetv4Rkyd0EnqGfm', 'super_admin', NULL, 'Super Administrator', 'superadmin@NPU.ac.id'),
('admin_mif', '$2y$10$1MpY29/1wBZWrhGbQAAQUOL/WdMQvUA3ynkYHvetv4Rkyd0EnqGfm', 'admin_prodi', 1, 'Admin Magister Informatika', 'admin.mif@NPU.ac.id');

INSERT IGNORE INTO mahasiswa (nim, nama, prodi_id, angkatan, status, no_hp, email, judul_tesis, dosen_pembimbing) VALUES
('2023MIF001', 'Ahmad Rizki Pratama', 1, 2023, 'Aktif', '081111111111', 'ahmad.rizki@gmail.com', 'Implementasi Deep Learning untuk Deteksi Anomali Jaringan', 'Dr. Ahmad Fauzi, M.Kom'),
('2023MIF002', 'Budi Hermawan', 1, 2023, 'Aktif', '081111111112', 'budi.h@gmail.com', 'Sistem Rekomendasi Berbasis Collaborative Filtering', 'Dr. Ahmad Fauzi, M.Kom'),
('2022MIF001', 'Citra Dewi', 1, 2022, 'Aktif', '081111111113', 'citra.d@gmail.com', 'Keamanan IoT Menggunakan Blockchain', 'Dr. Ahmad Fauzi, M.Kom'),
('2022MIF002', 'Doni Setiawan', 1, 2022, 'Lulus', '081111111114', 'doni.s@gmail.com', 'Analisis Sentimen Media Sosial', 'Dr. Ahmad Fauzi, M.Kom'),
('2023MM001', 'Eka Putri', 2, 2023, 'Aktif', '081222222221', 'eka.p@gmail.com', 'Pengaruh Digital Marketing terhadap Penjualan', 'Dr. Budi Santoso, M.M'),
('2023MM002', 'Fajar Nugroho', 2, 2023, 'Aktif', '081222222222', 'fajar.n@gmail.com', 'Manajemen Risiko Rantai Pasok', 'Dr. Budi Santoso, M.M'),
('2023MH001', 'Galih Wicaksono', 3, 2023, 'Aktif', '081333333331', 'galih.w@gmail.com', 'Perlindungan Hukum Data Pribadi di Era Digital', 'Dr. Cahya Permana, S.H., M.H'),
('2023MP001', 'Hana Safitri', 4, 2023, 'Aktif', '081444444441', 'hana.s@gmail.com', 'Model Pembelajaran Berbasis Project Based Learning', 'Dr. Dian Lestari, M.Pd'),
('2022DIK001', 'Irwan Kusuma', 5, 2022, 'Aktif', '081555555551', 'irwan.k@gmail.com', 'Quantum Computing untuk Optimasi Algoritma', 'Prof. Dr. Eko Prasetyo, M.Cs'),
('2023DIK001', 'Joko Widodo', 5, 2023, 'Aktif', '081555555552', 'joko.w@gmail.com', 'Federated Learning pada Sistem Terdistribusi', 'Prof. Dr. Eko Prasetyo, M.Cs');

INSERT IGNORE INTO surat (nomor_surat, jenis_surat, prodi_id, nama_penerima, nim_nidn, perihal, tanggal, status, isi_surat, created_by) VALUES
('001/NPU/MIF/I/2025', 'Surat Keterangan Aktif', 1, 'Ahmad Rizki Pratama', '2023MIF001', 'Keterangan Mahasiswa Aktif', '2025-01-10', 'Selesai', 'Yang bertanda tangan di bawah ini...', 1),
('002/NPU/MM/I/2025', 'Surat Tugas', 2, 'Dr. Budi Santoso', 'NIDN001', 'Menghadiri Seminar Nasional', '2025-01-15', 'Selesai', 'Yang bertanda tangan di bawah ini...', 1),
('003/NPU/MIF/II/2025', 'Surat Izin Penelitian', 1, 'Citra Dewi', '2022MIF001', 'Izin Penelitian Tesis', '2025-02-01', 'Proses', 'Yang bertanda tangan di bawah ini...', 1),
('004/NPU/MH/II/2025', 'Undangan Seminar', 3, 'Peserta Seminar', '-', 'Seminar Hukum Digital', '2025-02-10', 'Selesai', 'Yang bertanda tangan di bawah ini...', 1),
('005/NPU/MP/III/2025', 'SK Kelulusan', 4, 'Hana Safitri', '2023MP001', 'Surat Keterangan Lulus', '2025-03-01', 'Draf', 'Yang bertanda tangan di bawah ini...', 1);

INSERT IGNORE INTO tugas (judul, deskripsi, prodi_id, prioritas, deadline, status, label_warna, created_by) VALUES
('Review Tesis Ahmad Rizki', 'Review dan feedback tesis mahasiswa semester 4', 1, 'Tinggi', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Dikerjakan', '#60a5fa', 1),
('Siapkan Dokumen Akreditasi', 'Kumpulkan semua dokumen untuk akreditasi prodi', 2, 'Tinggi', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Belum', '#22c55e', 1),
('Update Data Mahasiswa 2025', 'Perbarui data mahasiswa angkatan 2025', NULL, 'Sedang', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Belum', '#f59e0b', 1),
('Rapat Koordinasi Prodi', 'Rapat koordinasi seluruh kaprodi', NULL, 'Sedang', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Belum', '#a78bfa', 1),
('Kirim Laporan Bulanan', 'Laporan bulanan ke rektorat', NULL, 'Rendah', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Belum', '#f87171', 1);

INSERT IGNORE INTO jadwal (judul, deskripsi, prodi_id, tanggal_mulai, tanggal_selesai, jenis_event, warna, created_by) VALUES
('Seminar Proposal Ahmad Rizki', 'Seminar proposal tesis Ahmad Rizki Pratama', 1, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 2 DAY), 'Seminar Proposal', '#60a5fa', 1),
('Sidang Tesis Doni Setiawan', 'Sidang tesis mahasiswa MIF', 1, DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 5 DAY), 'Sidang Tesis', '#22c55e', 1),
('Rapat Prodi MM', 'Rapat rutin program studi Magister Manajemen', 2, DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY), 'Rapat Prodi', '#f59e0b', 1),
('Wisuda Semester Genap 2025', 'Wisuda sarjana dan pascasarjana', NULL, DATE_ADD(NOW(), INTERVAL 30 DAY), DATE_ADD(NOW(), INTERVAL 30 DAY), 'Wisuda', '#a78bfa', 1);

INSERT IGNORE INTO whatsapp_log (tujuan, jenis_tujuan, pesan, status, waktu_kirim, created_by) VALUES
('6281234567890', 'grup', 'Pengingat: Seminar Proposal besok pukul 09.00 WIB di Ruang Seminar Lantai 3', 'Terkirim', DATE_SUB(NOW(), INTERVAL 2 HOUR), 1),
('6281111111111', 'individu', 'Halo Ahmad Rizki, mohon segera kumpulkan revisi proposal paling lambat besok pagi.', 'Terkirim', DATE_SUB(NOW(), INTERVAL 5 HOUR), 1);

INSERT IGNORE INTO notifikasi (user_id, pesan, jenis, is_read, link) VALUES
(1, 'Tugas "Review Tesis Ahmad Rizki" deadline besok!', 'tugas', 0, 'pages/tugas.php'),
(1, 'Seminar Proposal Ahmad Rizki dijadwalkan 2 hari lagi', 'jadwal', 0, 'pages/jadwal.php'),
(1, 'Surat 003/NPU/MIF/II/2025 menunggu tanda tangan', 'surat', 0, 'pages/surat.php');

INSERT IGNORE INTO settings (key_name, value) VALUES
('nama_universitas', 'Universitas Nusa Putra'),
('logo_url', 'assets/images/logo.png'),
('tahun_akademik', '2024/2025'),
('semester_aktif', 'Genap'),
('format_nomor_surat', '[No]/NPU/[kode_prodi]/[bulan_romawi]/[tahun]'),
('wa_api_key', ''),
('wa_nomor_pengirim', ''),
('wa_gateway', 'fonnte'),
('tema_default', 'dark'),
('gemini_api_key', 'gsk_DUMMY_KEY_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'),
('ai_system_prompt', 'Anda adalah Asisten Penulis Surat Resmi. Tulislah draf surat formal dalam bahasa Indonesia. Output HANYA HTML mentah. \nATURAN WAJIB:\n1. Paragraf gunakan tag <p> (otomatis rata kiri-kanan).\n2. Jika ada informasi bersusun (misal: Hari/Tanggal, Waktu, Tempat, Agenda), WAJIB gunakan format tabel khusus ini:\n<table class=\"layout-tabel\">\n<tr><td style=\"width:120px\">Hari/Tanggal</td><td style=\"width:20px\">:</td><td>...</td></tr>\n<tr><td>Waktu</td><td>:</td><td>...</td></tr>\n</table>\n3. JANGAN membuat bagian Tanda Tangan (Hormat kami, nama terang, dll) di akhir surat! Cukup akhiri dengan paragraf penutup. Bagian tanda tangan sudah digenerate otomatis oleh sistem.');


CREATE TABLE `riwayat_lampiran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prodi_id` int(11) NOT NULL,
  `nama_mhs` varchar(100) NOT NULL,
  `nim_mhs` varchar(20) NOT NULL,
  `judul_tesis` text NOT NULL,
  `tanggal_sidang` date NOT NULL,
  `ketua_pembimbing` varchar(100) NOT NULL,
  `anggota_pembimbing` varchar(100) NOT NULL,
  `ketua_penguji` varchar(100) NOT NULL,
  `anggota_penguji` varchar(100) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `prodi_id` (`prodi_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `riwayat_lampiran_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`),
  CONSTRAINT `riwayat_lampiran_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `riwayat_lampiran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prodi_id` int(11) NOT NULL,
  `nama_mhs` varchar(100) NOT NULL,
  `nim_mhs` varchar(20) NOT NULL,
  `judul_tesis` text NOT NULL,
  `tanggal_sidang` date NOT NULL,
  `ketua_pembimbing` varchar(100) NOT NULL,
  `anggota_pembimbing` varchar(100) NOT NULL,
  `ketua_penguji` varchar(100) NOT NULL,
  `anggota_penguji` varchar(100) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `prodi_id` (`prodi_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `riwayat_lampiran_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`),
  CONSTRAINT `riwayat_lampiran_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
