-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: pascasarjana_unp
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `aksi` varchar(100) NOT NULL,
  `modul` varchar(50) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,1,'Login','auth','Login berhasil','::1','2026-06-10 15:31:15'),(2,1,'Buat Surat','surat','001/MH/NPU/VI/2026','::1','2026-06-10 16:09:52'),(3,1,'Cetak Surat','surat','001/MH/NPU/VI/2026','::1','2026-06-10 16:10:56'),(4,1,'Buat Surat','surat','002/MH/NPU/VI/2026','::1','2026-06-10 16:12:49'),(5,1,'Cetak Surat','surat','002/MH/NPU/VI/2026','::1','2026-06-10 16:12:49'),(6,1,'Buat Surat','surat','001/MIF/NPU/VI/2026','::1','2026-06-10 16:15:41'),(7,1,'Cetak Surat','surat','001/MIF/NPU/VI/2026','::1','2026-06-10 16:15:41'),(8,1,'Cetak Surat','surat','001/MIF/NPU/VI/2026','::1','2026-06-10 16:17:46'),(9,1,'Buat Surat','surat','002/MIF/NPU/VI/2026','::1','2026-06-10 16:18:55'),(10,1,'Cetak Surat','surat','002/MIF/NPU/VI/2026','::1','2026-06-10 16:18:55');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catatan`
--

DROP TABLE IF EXISTS `catatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isi` text NOT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `warna` varchar(7) DEFAULT '#f59e0b',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `prodi_id` (`prodi_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `catatan_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE SET NULL,
  CONSTRAINT `catatan_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catatan`
--

LOCK TABLES `catatan` WRITE;
/*!40000 ALTER TABLE `catatan` DISABLE KEYS */;
/*!40000 ALTER TABLE `catatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jadwal`
--

DROP TABLE IF EXISTS `jadwal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `jenis_event` enum('Seminar Proposal','Sidang Tesis','Ujian Komprehensif','Wisuda','Rapat Prodi','Deadline','Lainnya') DEFAULT 'Lainnya',
  `warna` varchar(7) DEFAULT '#60a5fa',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `prodi_id` (`prodi_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jadwal`
--

LOCK TABLES `jadwal` WRITE;
/*!40000 ALTER TABLE `jadwal` DISABLE KEYS */;
INSERT INTO `jadwal` VALUES (1,'Seminar Proposal Ahmad Rizki','Seminar proposal tesis Ahmad Rizki Pratama',1,'2026-06-12 22:26:44','2026-06-12 22:26:44','Seminar Proposal','#60a5fa',1,'2026-06-10 15:26:44'),(2,'Sidang Tesis Doni Setiawan','Sidang tesis mahasiswa MIF',1,'2026-06-15 22:26:44','2026-06-15 22:26:44','Sidang Tesis','#22c55e',1,'2026-06-10 15:26:44'),(3,'Rapat Prodi MM','Rapat rutin program studi Magister Manajemen',2,'2026-06-13 22:26:44','2026-06-13 22:26:44','Rapat Prodi','#f59e0b',1,'2026-06-10 15:26:44'),(4,'Wisuda Semester Genap 2025','Wisuda sarjana dan pascasarjana',NULL,'2026-07-10 22:26:44','2026-07-10 22:26:44','Wisuda','#a78bfa',1,'2026-06-10 15:26:44');
/*!40000 ALTER TABLE `jadwal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mahasiswa`
--

DROP TABLE IF EXISTS `mahasiswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mahasiswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nim` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `prodi_id` int(11) NOT NULL,
  `angkatan` year(4) NOT NULL,
  `status` enum('Aktif','Cuti','Lulus','DO') DEFAULT 'Aktif',
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `judul_tesis` text DEFAULT NULL,
  `dosen_pembimbing` varchar(100) DEFAULT NULL,
  `catatan_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim` (`nim`),
  KEY `prodi_id` (`prodi_id`),
  CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mahasiswa`
--

LOCK TABLES `mahasiswa` WRITE;
/*!40000 ALTER TABLE `mahasiswa` DISABLE KEYS */;
INSERT INTO `mahasiswa` VALUES (1,'2023MIF001','Ahmad Rizki Pratama',1,2023,'Aktif','081111111111','ahmad.rizki@gmail.com',NULL,'Implementasi Deep Learning untuk Deteksi Anomali Jaringan','Dr. Ahmad Fauzi, M.Kom',NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(2,'2023MIF002','Budi Hermawan',1,2023,'Aktif','081111111112','budi.h@gmail.com',NULL,'Sistem Rekomendasi Berbasis Collaborative Filtering','Dr. Ahmad Fauzi, M.Kom',NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(3,'2022MIF001','Citra Dewi',1,2022,'Aktif','081111111113','citra.d@gmail.com',NULL,'Keamanan IoT Menggunakan Blockchain','Dr. Ahmad Fauzi, M.Kom',NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(4,'2022MIF002','Doni Setiawan',1,2022,'Lulus','081111111114','doni.s@gmail.com',NULL,'Analisis Sentimen Media Sosial','Dr. Ahmad Fauzi, M.Kom',NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(5,'2023MM001','Eka Putri',2,2023,'Aktif','081222222221','eka.p@gmail.com',NULL,'Pengaruh Digital Marketing terhadap Penjualan','Dr. Budi Santoso, M.M',NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(6,'2023MM002','Fajar Nugroho',2,2023,'Aktif','081222222222','fajar.n@gmail.com',NULL,'Manajemen Risiko Rantai Pasok','Dr. Budi Santoso, M.M',NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(7,'2023MH001','Galih Wicaksono',3,2023,'Aktif','081333333331','galih.w@gmail.com',NULL,'Perlindungan Hukum Data Pribadi di Era Digital','Dr. Cahya Permana, S.H., M.H',NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(8,'2023MP001','Hana Safitri',4,2023,'Aktif','081444444441','hana.s@gmail.com',NULL,'Model Pembelajaran Berbasis Project Based Learning','Dr. Dian Lestari, M.Pd',NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(9,'2022DIK001','Irwan Kusuma',5,2022,'Aktif','081555555551','irwan.k@gmail.com',NULL,'Quantum Computing untuk Optimasi Algoritma','Prof. Dr. Eko Prasetyo, M.Cs',NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(10,'2023DIK001','Joko Widodo',5,2023,'Aktif','081555555552','joko.w@gmail.com',NULL,'Federated Learning pada Sistem Terdistribusi','Prof. Dr. Eko Prasetyo, M.Cs',NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44');
/*!40000 ALTER TABLE `mahasiswa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifikasi`
--

DROP TABLE IF EXISTS `notifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pesan` text NOT NULL,
  `jenis` enum('tugas','jadwal','surat','sistem') DEFAULT 'sistem',
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifikasi`
--

LOCK TABLES `notifikasi` WRITE;
/*!40000 ALTER TABLE `notifikasi` DISABLE KEYS */;
INSERT INTO `notifikasi` VALUES (1,1,'Tugas \"Review Tesis Ahmad Rizki\" deadline besok!','tugas',0,'pages/tugas.php','2026-06-10 15:26:44'),(2,1,'Seminar Proposal Ahmad Rizki dijadwalkan 2 hari lagi','jadwal',0,'pages/jadwal.php','2026-06-10 15:26:44'),(3,1,'Surat 003/NPU/MIF/II/2025 menunggu tanda tangan','surat',0,'pages/surat.php','2026-06-10 15:26:44');
/*!40000 ALTER TABLE `notifikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prodi`
--

DROP TABLE IF EXISTS `prodi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prodi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenjang` enum('S2','S3') NOT NULL DEFAULT 'S2',
  `kaprodi` varchar(100) DEFAULT NULL,
  `sekretaris` varchar(100) DEFAULT NULL,
  `kontak` varchar(20) DEFAULT NULL,
  `no_wa_grup` varchar(20) DEFAULT NULL,
  `prefix_surat` varchar(20) DEFAULT NULL,
  `kota_surat` varchar(50) DEFAULT 'Sukabumi',
  `warna_hex` varchar(7) DEFAULT '#60a5fa',
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prodi`
--

LOCK TABLES `prodi` WRITE;
/*!40000 ALTER TABLE `prodi` DISABLE KEYS */;
INSERT INTO `prodi` VALUES (1,'MIF','Magister Informatika','S2','Dr. Ahmad Fauzi, M.Kom','Siti Rahayu, M.Kom','081234567890','6281234567890',NULL,'Sukabumi','#60a5fa','Program Studi S2 Magister Informatika','2026-06-10 15:26:43'),(2,'MM','Magister Manajemen','S2','Dr. Budi Santoso, M.M','Dewi Kurniawati, M.M','081234567891','6281234567891',NULL,'Sukabumi','#22c55e','Program Studi S2 Magister Manajemen','2026-06-10 15:26:43'),(3,'MH','Magister Hukum','S2','Dr. Armansyah, S.H., M.H','Rina Sari, S.H., M.H','081234567892','6281234567892',NULL,'Sukabumi','#f59e0b','Program Studi S2 Magister Hukum','2026-06-10 15:26:43'),(4,'MP','Magister Pedagogi','S2','Dr. Dian Lestari, M.Pd','Hendra Gunawan, M.Pd','081234567893','6281234567893',NULL,'Sukabumi','#a78bfa','Program Studi S2 Magister Pedagogi','2026-06-10 15:26:43'),(5,'DIK','Doktor Ilmu Komputer','S3','Prof. Dr. Eko Prasetyo, M.Cs','Farida Hanum, M.Cs','081234567894','6281234567894',NULL,'Sukabumi','#f87171','Program Studi S3 Doktor Ilmu Komputer','2026-06-10 15:26:43');
/*!40000 ALTER TABLE `prodi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'nama_universitas','Universitas Nusa Putra','2026-06-10 15:26:44'),(2,'logo_url','assets/images/logo.png','2026-06-10 15:26:44'),(3,'tahun_akademik','2024/2025','2026-06-10 15:26:44'),(4,'semester_aktif','Genap','2026-06-10 15:26:44'),(5,'format_nomor_surat','[No]/NPU/[kode_prodi]/[bulan_romawi]/[tahun]','2026-06-10 15:26:44'),(6,'wa_api_key','','2026-06-10 15:26:44'),(7,'wa_nomor_pengirim','','2026-06-10 15:26:44'),(8,'wa_gateway','fonnte','2026-06-10 15:26:44'),(9,'tema_default','dark','2026-06-10 15:26:44'),(10,'gemini_api_key','gsk_DUMMY_KEY_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX','2026-06-10 16:02:46'),(11,'ai_system_prompt','Anda adalah Asisten Penulis Surat Resmi. Tulislah draf surat formal dalam bahasa Indonesia. Output HANYA HTML mentah. \nATURAN WAJIB:\n1. Paragraf gunakan tag <p> (otomatis rata kiri-kanan).\n2. Jika ada informasi bersusun (misal: Hari/Tanggal, Waktu, Tempat, Agenda), WAJIB gunakan format tabel khusus ini:\n<table class=\"layout-tabel\">\n<tr><td style=\"width:120px\">Hari/Tanggal</td><td style=\"width:20px\">:</td><td>...</td></tr>\n<tr><td>Waktu</td><td>:</td><td>...</td></tr>\n</table>\n3. JANGAN membuat bagian Tanda Tangan (Hormat kami, nama terang, dll) di akhir surat! Cukup akhiri dengan paragraf penutup. Bagian tanda tangan sudah digenerate otomatis oleh sistem.','2026-06-10 16:21:03');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surat`
--

DROP TABLE IF EXISTS `surat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `surat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(100) NOT NULL,
  `jenis_surat` varchar(100) NOT NULL,
  `prodi_id` int(11) NOT NULL,
  `nama_penerima` varchar(100) DEFAULT NULL,
  `jenis_penerima` varchar(50) DEFAULT 'individual',
  `nim_nidn` varchar(30) DEFAULT NULL,
  `perihal` varchar(255) DEFAULT NULL,
  `keperluan` text DEFAULT NULL,
  `tanggal` date NOT NULL,
  `hari` varchar(20) DEFAULT NULL,
  `kota` varchar(50) DEFAULT 'Sukabumi',
  `status` enum('Draf','Proses','Selesai','Terarsip') DEFAULT 'Draf',
  `lampiran` varchar(100) DEFAULT '-',
  `isi_surat` longtext DEFAULT NULL,
  `file_pdf` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_surat` (`nomor_surat`),
  KEY `prodi_id` (`prodi_id`),
  KEY `created_by` (`created_by`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `surat_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`),
  CONSTRAINT `surat_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `surat_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surat`
--

LOCK TABLES `surat` WRITE;
/*!40000 ALTER TABLE `surat` DISABLE KEYS */;
INSERT INTO `surat` VALUES (1,'001/NPU/MIF/I/2025','Surat Keterangan Aktif',1,'Ahmad Rizki Pratama','individual','2023MIF001','Keterangan Mahasiswa Aktif',NULL,'2025-01-10',NULL,'Sukabumi','Selesai','-','Yang bertanda tangan di bawah ini...',NULL,1,NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(2,'002/NPU/MM/I/2025','Surat Tugas',2,'Dr. Budi Santoso','individual','NIDN001','Menghadiri Seminar Nasional',NULL,'2025-01-15',NULL,'Sukabumi','Selesai','-','Yang bertanda tangan di bawah ini...',NULL,1,NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(3,'003/NPU/MIF/II/2025','Surat Izin Penelitian',1,'Citra Dewi','individual','2022MIF001','Izin Penelitian Tesis',NULL,'2025-02-01',NULL,'Sukabumi','Proses','-','Yang bertanda tangan di bawah ini...',NULL,1,NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(4,'004/NPU/MH/II/2025','Undangan Seminar',3,'Peserta Seminar','individual','-','Seminar Hukum Digital',NULL,'2025-02-10',NULL,'Sukabumi','Selesai','-','Yang bertanda tangan di bawah ini...',NULL,1,NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(5,'005/NPU/MP/III/2025','SK Kelulusan',4,'Hana Safitri','individual','2023MP001','Surat Keterangan Lulus',NULL,'2025-03-01',NULL,'Sukabumi','Draf','-','Yang bertanda tangan di bawah ini...',NULL,1,NULL,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(8,'001/MH/NPU/VI/2026','Berita Acara',3,'Wakil Rektor 1','custom','','Undangan Visitaasi Akreditasi Prodi Magister Manajemen',NULL,'2026-06-10','Rabu','Sukabumi','Draf','-','<p>Yth. Wakil Rektor 1 Universitas Nusa Putra di tempat,</p><p>Dengan hormat, kami beritahukan bahwa Program Studi Magister Manajemen Universitas Nusa Putra akan mengadakan visitasi akreditasi pada tanggal 20 November.</p><p>Kegiatan ini bertujuan untuk meningkatkan kualitas pendidikan dan memenuhi standar akreditasi yang telah ditetapkan.</p><p>Oleh karena itu, kami mengundang Bapak/Ibu untuk hadir dalam kegiatan visitasi akreditasi tersebut.</p><p>Terima kasih atas perhatian dan partisipasi Bapak/Ibu.</p><p>Hormat kami,<br>{{NAMA_KAPRODI}}<br>{{GELAR_KAPRODI}}<br>NIDN: {{NIDN_KAPRODI}}<br>Ketua Program Studi Magister Manajemen</p>',NULL,1,NULL,'2026-06-10 16:09:52','2026-06-10 16:09:52'),(9,'002/MH/NPU/VI/2026','Berita Acara',3,'Mahasiswa','custom','','Undangan Akreditasi Prodi S2 Magister Manajemen',NULL,'2026-06-10','Rabu','Sukabumi','Draf','-','<p>Yth. Mahasiswa Program Studi Magister Manajemen Universitas Nusa Putra</p><p>Dengan ini kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri acara Akreditasi Program Studi Magister Manajemen yang akan diselenggarakan pada:</p><p>Tanggal: 20 Juni 2026</p><p>Waktu: 10.00 WIB - selesai</p><p>Tempat: Kampus Universitas Nusa Putra</p><p>Acara ini bertujuan untuk memenuhi persyaratan akreditasi Program Studi Magister Manajemen dan kami mengharapkan kehadiran Bapak/Ibu/Saudara/i untuk memberikan kontribusi dalam acara ini.</p><p>Terima kasih atas perhatian Bapak/Ibu/Saudara/i.</p><p>Saya hormat,</p><p><strong>{{NAMA_KAPRODI}}<br>{{GELAR_KAPRODI}}<br>NIDN: {{NIDN_KAPRODI}}<br>Kaprodi Program Studi Magister Manajemen</strong></p>',NULL,1,NULL,'2026-06-10 16:12:49','2026-06-10 16:12:49'),(10,'001/MIF/NPU/VI/2026','Berita Acara',1,'Mahasiswa S2 Informatika','custom','','Undangan Penugasan Dosen',NULL,'2026-06-10','Rabu','Sukabumi','Draf','-','<p>Yth. {{NAMA_MAHASISWA}} {{NIM}},</p><p>Bersama ini kami beritahukan bahwa Anda telah ditugaskan untuk melaksanakan penugasan dosen pada mata kuliah {{MATA_KULIAH}} dengan bobot {{SKS}} SKS pada semester {{SEMESTER}} tahun akademik {{TAHUN_AKADEMIK}}.</p><p>Penugasan dosen ini bertujuan untuk meningkatkan kemampuan dan pengetahuan Anda dalam bidang informatika, serta memberikan pengalaman nyata dalam menerapkan teori yang telah dipelajari.</p><p>Penugasan dosen ini akan dilaksanakan pada {{TANGGAL_MULAI}} sampai dengan {{TANGGAL_SELESAI}} di {{LOKASI_PENELITIAN}}.</p><p>Anda diharapkan dapat mempersiapkan diri dengan baik dan mengikuti semua instruksi yang diberikan oleh dosen pembimbing.</p><p>Jika Anda memiliki pertanyaan atau kebutuhan lebih lanjut, silakan menghubungi kami.</p><p>Terima kasih atas perhatian Anda.</p><p>Saya hormat,</p><p>{{NAMA_KAPRODI}} {{GELAR_KAPRODI}}<br>{{NIDN_KAPRODI}}<br>Ketua Program Studi {{NAMA_PRODI}}</p>',NULL,1,NULL,'2026-06-10 16:15:41','2026-06-10 16:15:41'),(11,'002/MIF/NPU/VI/2026','Berita Acara',1,'Dosen Penguji','custom','','Undangan Sidang Tesis',NULL,'2026-06-10','Rabu','Sukabumi','Draf','-','<p>Yth. Bapak/Ibu Dosen Penguji,</p><p>Dengan ini, kami mengundang Bapak/Ibu untuk hadir dalam sidang tesis mahasiswa program studi Magister Pendidikan (S2 Pedagogy) yang akan dilaksanakan sebagai berikut:</p><table class=\"layout-tabel\"><tr><td style=\"width:120px\">Hari/Tanggal</td><td style=\"width:20px\">:</td><td>{{TANGGAL_KEGIATAN}}</td></tr><tr><td>Waktu</td><td>:</td><td>{{WAKTU}}</td></tr><tr><td>Tempat</td><td>:</td><td>{{TEMPAT}}</td></tr><tr><td>Mahasiswa</td><td>:</td><td>{{NAMA_MAHASISWA}}</td></tr><tr><td>Judul Tesis</td><td>:</td><td>{{JUDUL_TESIS}}</td></tr></table><p>Kehadiran Bapak/Ibu sangatlah penting untuk kelancaran sidang tesis tersebut. Atas perhatian dan kerja sama Bapak/Ibu, kami mengucapkan terima kasih.</p><p>Hormat kami,<br>{{NAMA_KAPRODI}}<br>{{GELAR_KAPRODI}}<br>NIDN: {{NIDN_KAPRODI}}<br>Ketua Program Studi S2 Pendidikan (Pedagody)',NULL,1,NULL,'2026-06-10 16:18:55','2026-06-10 16:18:55');
/*!40000 ALTER TABLE `surat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `template_surat`
--

DROP TABLE IF EXISTS `template_surat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template_surat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prodi_id` int(11) DEFAULT NULL,
  `jenis_surat` varchar(100) NOT NULL,
  `nama_template` varchar(150) DEFAULT NULL,
  `isi_template` longtext DEFAULT NULL,
  `header_html` text DEFAULT NULL,
  `variabel_tersedia` text DEFAULT NULL,
  `is_massal` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `prodi_id` (`prodi_id`),
  CONSTRAINT `template_surat_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `template_surat`
--

LOCK TABLES `template_surat` WRITE;
/*!40000 ALTER TABLE `template_surat` DISABLE KEYS */;
INSERT INTO `template_surat` VALUES (1,NULL,'Surat Tugas Mengajar','Surat Tugas Mengajar Dosen','<p>Dengan hormat,</p><p>Berdasarkan kebutuhan akademik Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, bersama ini kami menugaskan <strong>{{NAMA_PENERIMA}}</strong> untuk mengampu mata kuliah <strong>{{MATA_KULIAH}}</strong> ({{SKS}} SKS) pada Semester {{SEMESTER}} Tahun Akademik {{TAHUN_AKADEMIK}}.</p><p>Demikian surat tugas ini diberikan untuk dapat dilaksanakan dengan penuh tanggung jawab.</p>',NULL,'{{NAMA_PENERIMA}},{{MATA_KULIAH}},{{SKS}},{{SEMESTER}},{{TAHUN_AKADEMIK}}',0,'2026-06-10 15:26:54'),(2,NULL,'Surat Tugas Pembimbing','SK Dosen Pembimbing Tesis','<p>Berdasarkan kebutuhan akademik dalam proses penyusunan Tesis mahasiswa Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, maka dengan ini menugaskan <strong>{{NAMA_PENERIMA}}</strong> sebagai <strong>Dosen Pembimbing Tesis</strong> bagi mahasiswa:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Judul Tesis</strong></td><td>: <em>{{JUDUL_PENELITIAN}}</em></td></tr><tr><td><strong>Tahun Akademik</strong></td><td>: {{TAHUN_AKADEMIK}}</td></tr></table><p>Pembimbing diharapkan memberikan bimbingan secara berkala hingga mahasiswa tersebut menyelesaikan tesisnya.</p>',NULL,'{{NAMA_PENERIMA}},{{NAMA_MAHASISWA}},{{NIM}},{{JUDUL_PENELITIAN}},{{TAHUN_AKADEMIK}}',0,'2026-06-10 15:26:54'),(3,NULL,'Surat Keterangan Aktif','Surat Keterangan Aktif Kuliah','<p>Yang bertanda tangan di bawah ini, Ketua Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, menerangkan bahwa:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Angkatan</strong></td><td>: {{ANGKATAN}}</td></tr><tr><td><strong>Status</strong></td><td>: Mahasiswa Aktif</td></tr></table><p>Mahasiswa tersebut benar terdaftar sebagai mahasiswa aktif pada Tahun Akademik <strong>{{TAHUN_AKADEMIK}}</strong>. Surat ini diterbitkan untuk keperluan: <strong>{{KEPERLUAN}}</strong>.</p><p>Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>',NULL,'{{NIM}},{{NAMA_MAHASISWA}},{{ANGKATAN}},{{KEPERLUAN}}',0,'2026-06-10 15:26:54'),(4,NULL,'Surat Izin Penelitian','Surat Izin Penelitian Tesis','<p>Dengan hormat,</p><p>Sehubungan dengan pelaksanaan penelitian Tesis, kami mengajukan permohonan izin penelitian atas nama mahasiswa:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Judul Penelitian</strong></td><td>: {{JUDUL_PENELITIAN}}</td></tr><tr><td><strong>Lokasi</strong></td><td>: {{LOKASI_PENELITIAN}}</td></tr><tr><td><strong>Periode</strong></td><td>: {{TANGGAL_MULAI}} s.d. {{TANGGAL_SELESAI}}</td></tr></table><p>Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>',NULL,'{{NIM}},{{NAMA_MAHASISWA}},{{JUDUL_PENELITIAN}},{{LOKASI_PENELITIAN}},{{TANGGAL_MULAI}},{{TANGGAL_SELESAI}}',0,'2026-06-10 15:26:54'),(5,NULL,'Undangan Seminar Proposal','Undangan Seminar Proposal Tesis','<p>Dengan hormat,</p><p>Dalam rangka Seminar Proposal Tesis Program Studi {{NAMA_PRODI}}, kami mengundang Bapak/Ibu untuk hadir pada:</p><table style=\"width:100%;border-collapse:collapse;margin:12px 0\"><tr><td style=\"width:35%;padding:5px 0\"><strong>Jenis Kegiatan</strong></td><td>: Seminar Proposal Tesis</td></tr><tr><td style=\"padding:5px 0\"><strong>Presenter</strong></td><td>: {{NAMA_PRESENTER}} ({{NIM}})</td></tr><tr><td style=\"padding:5px 0\"><strong>Judul</strong></td><td>: <em>{{JUDUL_TESIS}}</em></td></tr><tr><td style=\"padding:5px 0\"><strong>Hari/Tanggal</strong></td><td>: {{TANGGAL_KEGIATAN}}</td></tr><tr><td style=\"padding:5px 0\"><strong>Pukul</strong></td><td>: {{WAKTU}} WIB</td></tr><tr><td style=\"padding:5px 0\"><strong>Tempat</strong></td><td>: {{TEMPAT}}</td></tr></table><p>Kehadiran Bapak/Ibu sangat kami harapkan. Atas perhatian dan kehadirannya, kami ucapkan terima kasih.</p>',NULL,'{{NAMA_PRESENTER}},{{NIM}},{{JUDUL_TESIS}},{{TANGGAL_KEGIATAN}},{{WAKTU}},{{TEMPAT}}',1,'2026-06-10 15:26:54'),(6,NULL,'Undangan Sidang Tesis','Undangan Sidang Tesis (Tim Penguji)','<p>Dengan hormat,</p><p>Dalam rangka Sidang Tesis Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, kami mengundang Bapak/Ibu Dosen sebagai Tim Penguji pada:</p><table style=\"width:100%;border-collapse:collapse;margin:12px 0\"><tr><td style=\"width:35%;padding:5px 0\"><strong>Jenis Ujian</strong></td><td>: Sidang Tesis</td></tr><tr><td style=\"padding:5px 0\"><strong>Nama Mahasiswa</strong></td><td>: {{NAMA_PRESENTER}}</td></tr><tr><td style=\"padding:5px 0\"><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td style=\"padding:5px 0\"><strong>Judul Tesis</strong></td><td>: <em>{{JUDUL_TESIS}}</em></td></tr><tr><td style=\"padding:5px 0\"><strong>Hari/Tanggal</strong></td><td>: {{TANGGAL_KEGIATAN}}</td></tr><tr><td style=\"padding:5px 0\"><strong>Pukul</strong></td><td>: {{WAKTU}} WIB</td></tr><tr><td style=\"padding:5px 0\"><strong>Tempat</strong></td><td>: {{TEMPAT}}</td></tr></table><p>Kehadiran Bapak/Ibu sebagai penguji sangat kami harapkan. Mohon konfirmasi kehadiran kepada sekretariat selambat-lambatnya 2 hari sebelum pelaksanaan.</p><p>Atas perhatian dan kesediaan Bapak/Ibu, kami ucapkan terima kasih.</p>',NULL,'{{NAMA_PRESENTER}},{{NIM}},{{JUDUL_TESIS}},{{TANGGAL_KEGIATAN}},{{WAKTU}},{{TEMPAT}}',1,'2026-06-10 15:26:54'),(7,NULL,'SK Kelulusan','SK Kelulusan / Yudisium','<p>Berdasarkan hasil Sidang Yudisium Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra tanggal {{TANGGAL_YUDISIUM}}, maka:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>IPK</strong></td><td>: {{IPK}}</td></tr><tr><td><strong>Predikat</strong></td><td>: <strong>{{PREDIKAT}}</strong></td></tr></table><p>Dinyatakan <strong>LULUS</strong> dan berhak memperoleh gelar sesuai Program Studi yang ditempuh.</p>',NULL,'{{NIM}},{{NAMA_MAHASISWA}},{{IPK}},{{TANGGAL_YUDISIUM}},{{PREDIKAT}}',0,'2026-06-10 15:26:54'),(8,NULL,'Surat Rekomendasi','Surat Rekomendasi Mahasiswa','<p>Yang bertanda tangan di bawah ini, Ketua Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, memberikan rekomendasi kepada:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr></table><p>{{PRESTASI}}</p><p>Kami merekomendasikan yang bersangkutan untuk: <strong>{{TUJUAN_REKOMENDASI}}</strong>.</p><p>Demikian surat rekomendasi ini dibuat untuk dapat digunakan sebagaimana mestinya.</p>',NULL,'{{NIM}},{{NAMA_MAHASISWA}},{{TUJUAN_REKOMENDASI}},{{PRESTASI}}',0,'2026-06-10 15:26:54'),(9,NULL,'Surat Pengantar Ijazah','Surat Pengantar Pengambilan Ijazah','<p>Dengan hormat,</p><p>Bersama ini kami sampaikan bahwa mahasiswa berikut telah menyelesaikan studi dan berhak mengambil ijazah:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Nomor Ijazah</strong></td><td>: {{NO_IJAZAH}}</td></tr></table><p>Mohon ijazah dapat diserahkan kepada yang bersangkutan dengan menunjukkan surat ini dan kartu identitas.</p>',NULL,'{{NIM}},{{NAMA_MAHASISWA}},{{NO_IJAZAH}}',0,'2026-06-10 15:26:54'),(10,NULL,'Surat Pemberitahuan','Surat Pemberitahuan Umum (Massal)','<p>Dengan hormat,</p><p>Bersama ini kami sampaikan pemberitahuan mengenai: <strong>{{PERIHAL}}</strong></p><p>{{ISI_PEMBERITAHUAN}}</p><p>Kegiatan akan dilaksanakan pada tanggal <strong>{{TANGGAL_PELAKSANAAN}}</strong>.</p><p>Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>',NULL,'{{PERIHAL}},{{ISI_PEMBERITAHUAN}},{{TANGGAL_PELAKSANAAN}}',1,'2026-06-10 15:26:54'),(11,NULL,'Surat Permohonan','Surat Permohonan ke Instansi Luar','<p>Dengan hormat,</p><p>Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra mengajukan permohonan kepada <strong>{{NAMA_INSTANSI}}</strong> yang beralamat di {{ALAMAT_INSTANSI}}.</p><p>Adapun permohonan kami: {{ISI_PERMOHONAN}}</p><p>Besar harapan kami permohonan ini dapat dikabulkan. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>',NULL,'{{NAMA_INSTANSI}},{{ALAMAT_INSTANSI}},{{ISI_PERMOHONAN}}',0,'2026-06-10 15:26:54'),(12,NULL,'Surat Perpanjangan Studi','Surat Permohonan Perpanjangan Studi','<p>Dengan hormat,</p><p>Sehubungan dengan belum selesainya penyusunan tesis, kami mengajukan permohonan perpanjangan masa studi bagi mahasiswa:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Angkatan</strong></td><td>: {{ANGKATAN}}</td></tr><tr><td><strong>Alasan</strong></td><td>: {{KEPERLUAN}}</td></tr><tr><td><strong>Target Selesai</strong></td><td>: {{TANGGAL_SELESAI}}</td></tr></table><p>Mahasiswa berkomitmen menyelesaikan studi sesuai target. Atas perhatian dan kebijaksanaan Bapak/Ibu, kami ucapkan terima kasih.</p>',NULL,'{{NAMA_MAHASISWA}},{{NIM}},{{ANGKATAN}},{{KEPERLUAN}},{{TANGGAL_SELESAI}}',0,'2026-06-10 15:26:54'),(13,NULL,'Surat Cuti Akademik','Surat Permohonan Cuti Akademik','<p>Dengan hormat,</p><p>Kami menerangkan bahwa mahasiswa berikut mengajukan permohonan cuti akademik:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Semester</strong></td><td>: {{SEMESTER}}</td></tr><tr><td><strong>Alasan</strong></td><td>: {{KEPERLUAN}}</td></tr><tr><td><strong>Periode Cuti</strong></td><td>: {{TANGGAL_MULAI}} s.d. {{TANGGAL_SELESAI}}</td></tr></table><p>Selama masa cuti, mahasiswa tidak diperkenankan mengikuti kegiatan akademik. Demikian untuk dapat digunakan sebagaimana mestinya.</p>',NULL,'{{NAMA_MAHASISWA}},{{NIM}},{{SEMESTER}},{{KEPERLUAN}},{{TANGGAL_MULAI}},{{TANGGAL_SELESAI}}',0,'2026-06-10 15:26:54'),(14,NULL,'Surat Bebas Perpustakaan','Surat Keterangan Bebas Perpustakaan','<p>Yang bertanda tangan di bawah ini, Ketua Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, menerangkan bahwa:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr></table><p>Mahasiswa tersebut dinyatakan <strong>BEBAS</strong> dari pinjaman dan tanggungan perpustakaan Universitas Nusa Putra. Surat ini diberikan untuk keperluan: <strong>{{KEPERLUAN}}</strong>.</p>',NULL,'{{NAMA_MAHASISWA}},{{NIM}},{{KEPERLUAN}}',0,'2026-06-10 15:26:54'),(15,NULL,'Surat Undangan Rapat','Undangan Rapat Program Studi (Massal)','<p>Dengan hormat,</p><p>Mengharap kehadiran Bapak/Ibu dalam kegiatan:</p><table style=\"width:100%;border-collapse:collapse;margin:12px 0\"><tr><td style=\"width:35%;padding:5px 0\"><strong>Acara</strong></td><td>: {{JENIS_KEGIATAN}}</td></tr><tr><td style=\"padding:5px 0\"><strong>Hari/Tanggal</strong></td><td>: {{TANGGAL_KEGIATAN}}</td></tr><tr><td style=\"padding:5px 0\"><strong>Pukul</strong></td><td>: {{WAKTU}} WIB</td></tr><tr><td style=\"padding:5px 0\"><strong>Tempat</strong></td><td>: {{TEMPAT}}</td></tr></table><p>Agenda: {{ISI_PEMBERITAHUAN}}</p><p>Kehadiran Bapak/Ibu tepat waktu sangat kami harapkan. Atas perhatian dan kehadirannya, kami ucapkan terima kasih.</p>',NULL,'{{JENIS_KEGIATAN}},{{TANGGAL_KEGIATAN}},{{WAKTU}},{{TEMPAT}},{{ISI_PEMBERITAHUAN}}',1,'2026-06-10 15:26:54'),(16,NULL,'Berita Acara','Berita Acara Kegiatan','<p style=\"text-align:center\"><strong>BERITA ACARA {{JENIS_KEGIATAN}}</strong></p><p>Pada hari ini, {{TANGGAL_KEGIATAN}}, telah dilaksanakan <strong>{{JENIS_KEGIATAN}}</strong> dengan peserta: {{PESERTA}}.</p><p><strong>Hasil Kegiatan:</strong></p><p>{{HASIL_KEGIATAN}}</p><p>Demikian berita acara ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>',NULL,'{{JENIS_KEGIATAN}},{{TANGGAL_KEGIATAN}},{{PESERTA}},{{HASIL_KEGIATAN}}',0,'2026-06-10 15:26:54');
/*!40000 ALTER TABLE `template_surat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tugas`
--

DROP TABLE IF EXISTS `tugas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tugas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `prioritas` enum('Tinggi','Sedang','Rendah') DEFAULT 'Sedang',
  `deadline` date DEFAULT NULL,
  `status` enum('Belum','Dikerjakan','Selesai') DEFAULT 'Belum',
  `label_warna` varchar(7) DEFAULT '#60a5fa',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `prodi_id` (`prodi_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tugas_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tugas`
--

LOCK TABLES `tugas` WRITE;
/*!40000 ALTER TABLE `tugas` DISABLE KEYS */;
INSERT INTO `tugas` VALUES (1,'Review Tesis Ahmad Rizki','Review dan feedback tesis mahasiswa semester 4',1,'Tinggi','2026-06-11','Dikerjakan','#60a5fa',1,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(2,'Siapkan Dokumen Akreditasi','Kumpulkan semua dokumen untuk akreditasi prodi',2,'Tinggi','2026-06-15','Belum','#22c55e',1,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(3,'Update Data Mahasiswa 2025','Perbarui data mahasiswa angkatan 2025',NULL,'Sedang','2026-06-17','Belum','#f59e0b',1,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(4,'Rapat Koordinasi Prodi','Rapat koordinasi seluruh kaprodi',NULL,'Sedang','2026-06-13','Belum','#a78bfa',1,'2026-06-10 15:26:44','2026-06-10 15:26:44'),(5,'Kirim Laporan Bulanan','Laporan bulanan ke rektorat',NULL,'Rendah','2026-06-24','Belum','#f87171',1,'2026-06-10 15:26:44','2026-06-10 15:26:44');
/*!40000 ALTER TABLE `tugas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin_prodi') NOT NULL DEFAULT 'admin_prodi',
  `prodi_id` int(11) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `prodi_id` (`prodi_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$F7s5RP2JIiyHCRrqNqCNGO6aU6LDoaIc1bmcdolEWv0pEwx.Y2wia','super_admin',NULL,'Administrator','admin@gmail.com',NULL,'2026-06-10 15:31:15','2026-06-10 15:26:44'),(2,'superadmin','$2y$10$F7s5RP2JIiyHCRrqNqCNGO6aU6LDoaIc1bmcdolEWv0pEwx.Y2wia','super_admin',NULL,'Super Administrator','superadmin@NPU.ac.id',NULL,NULL,'2026-06-10 15:26:44'),(3,'admin_mif','$2y$10$F7s5RP2JIiyHCRrqNqCNGO6aU6LDoaIc1bmcdolEWv0pEwx.Y2wia','admin_prodi',1,'Admin Magister Informatika','admin.mif@NPU.ac.id',NULL,NULL,'2026-06-10 15:26:44');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_log`
--

DROP TABLE IF EXISTS `whatsapp_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tujuan` varchar(50) NOT NULL,
  `jenis_tujuan` enum('individu','grup') DEFAULT 'individu',
  `pesan` text NOT NULL,
  `status` enum('Terkirim','Gagal','Pending') DEFAULT 'Pending',
  `waktu_kirim` timestamp NULL DEFAULT NULL,
  `jadwal_kirim` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `whatsapp_log_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_log`
--

LOCK TABLES `whatsapp_log` WRITE;
/*!40000 ALTER TABLE `whatsapp_log` DISABLE KEYS */;
INSERT INTO `whatsapp_log` VALUES (1,'6281234567890','grup','Pengingat: Seminar Proposal besok pukul 09.00 WIB di Ruang Seminar Lantai 3','Terkirim','2026-06-10 13:26:44',NULL,1,'2026-06-10 15:26:44'),(2,'6281111111111','individu','Halo Ahmad Rizki, mohon segera kumpulkan revisi proposal paling lambat besok pagi.','Terkirim','2026-06-10 10:26:44',NULL,1,'2026-06-10 15:26:44');
/*!40000 ALTER TABLE `whatsapp_log` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-10 23:21:24


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
