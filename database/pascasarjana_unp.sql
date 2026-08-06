-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 30, 2026 at 06:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pascasarjana_unp`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `aksi` varchar(100) NOT NULL,
  `modul` varchar(50) DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `aksi`, `modul`, `detail`, `ip_address`, `created_at`) VALUES
(1, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-06-10 15:31:15'),
(2, 1, 'Buat Surat', 'surat', '001/MH/NPU/VI/2026', '::1', '2026-06-10 16:09:52'),
(3, 1, 'Cetak Surat', 'surat', '001/MH/NPU/VI/2026', '::1', '2026-06-10 16:10:56'),
(4, 1, 'Buat Surat', 'surat', '002/MH/NPU/VI/2026', '::1', '2026-06-10 16:12:49'),
(5, 1, 'Cetak Surat', 'surat', '002/MH/NPU/VI/2026', '::1', '2026-06-10 16:12:49'),
(6, 1, 'Buat Surat', 'surat', '001/MIF/NPU/VI/2026', '::1', '2026-06-10 16:15:41'),
(7, 1, 'Cetak Surat', 'surat', '001/MIF/NPU/VI/2026', '::1', '2026-06-10 16:15:41'),
(8, 1, 'Cetak Surat', 'surat', '001/MIF/NPU/VI/2026', '::1', '2026-06-10 16:17:46'),
(9, 1, 'Buat Surat', 'surat', '002/MIF/NPU/VI/2026', '::1', '2026-06-10 16:18:55'),
(10, 1, 'Cetak Surat', 'surat', '002/MIF/NPU/VI/2026', '::1', '2026-06-10 16:18:55'),
(11, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-17 12:31:32'),
(12, 1, 'Logout', 'auth', '', '::1', '2026-07-17 12:39:35'),
(13, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-17 12:40:44'),
(14, 1, 'Logout', 'auth', '', '::1', '2026-07-17 12:53:02'),
(15, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-17 12:55:24'),
(16, 1, 'Logout', 'auth', '', '::1', '2026-07-17 12:57:18'),
(17, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-17 12:57:55'),
(18, 1, 'Logout', 'auth', '', '::1', '2026-07-17 14:25:37'),
(19, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-17 14:37:00'),
(20, 1, 'Logout', 'auth', '', '::1', '2026-07-17 14:38:16'),
(21, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-17 15:30:42'),
(22, 1, 'Logout', 'auth', '', '::1', '2026-07-17 15:30:50'),
(23, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-17 15:50:51'),
(24, 1, 'Logout', 'auth', '', '::1', '2026-07-18 05:03:06'),
(25, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-18 07:14:17'),
(26, 1, 'Logout', 'auth', '', '::1', '2026-07-18 10:17:29'),
(27, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-18 10:17:50'),
(28, 1, 'Logout', 'auth', '', '::1', '2026-07-18 10:18:07'),
(29, 4, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-18 10:18:14'),
(30, 4, 'Logout', 'auth', '', '::1', '2026-07-18 10:19:10'),
(31, 4, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-18 10:19:27'),
(32, 4, 'Logout', 'auth', '', '::1', '2026-07-18 10:46:14'),
(33, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-18 10:46:20'),
(34, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-18 12:57:12'),
(35, 1, 'Hapus mahasiswa', 'mahasiswa', 'NIM: 20260130002 - SITI FAZIUR RAHMAH', '::1', '2026-07-18 12:57:37'),
(36, 1, 'Hapus mahasiswa', 'mahasiswa', 'NIM: 20260130001 - PUTRINDA CHOIRUNNISA', '::1', '2026-07-18 12:57:44'),
(37, 1, 'Logout', 'auth', '', '::1', '2026-07-18 14:34:27'),
(38, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-18 14:39:32'),
(39, 1, 'Logout', 'auth', '', '::1', '2026-07-18 14:40:02'),
(40, 134, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-18 14:40:08'),
(41, 134, 'Logout', 'auth', '', '::1', '2026-07-18 14:41:57'),
(42, 134, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-18 14:42:04'),
(43, 134, 'Logout', 'auth', '', '::1', '2026-07-18 14:42:25'),
(44, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-18 14:42:34'),
(45, 1, 'Kirim WA', 'whatsapp', 'Ke: 085351221602', '::1', '2026-07-19 00:02:03'),
(46, 1, 'Kirim WA', 'whatsapp', 'Ke: 085659838977', '::1', '2026-07-19 00:02:46'),
(47, 1, 'Logout', 'auth', '', '::1', '2026-07-19 11:06:42'),
(48, 1, 'Login', 'auth', 'Login berhasil via Google (Manual API)', '::1', '2026-07-19 11:28:07'),
(49, 1, 'Logout', 'auth', '', '::1', '2026-07-19 11:39:48'),
(50, 134, 'Login', 'auth', 'Login berhasil via Google (Manual API)', '::1', '2026-07-19 11:44:08'),
(51, 134, 'Logout', 'auth', '', '::1', '2026-07-19 11:44:29'),
(52, 134, 'Login', 'auth', 'Login berhasil via Google (Manual API)', '::1', '2026-07-19 11:50:05'),
(53, 2, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-19 14:38:31'),
(54, 2, 'Logout', 'auth', '', '::1', '2026-07-19 14:39:38'),
(55, 6, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-19 14:39:46'),
(56, 2, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-20 02:27:23'),
(57, 181, 'Login', 'auth', 'Login berhasil via Google (Manual API)', '::1', '2026-07-21 01:19:53'),
(58, 181, 'Logout', 'auth', '', '::1', '2026-07-21 01:20:11'),
(59, 134, 'Login', 'auth', 'Login berhasil via Google (Manual API)', '::1', '2026-07-21 01:20:27'),
(60, 2, 'Login', 'auth', 'Login berhasil', '127.0.0.1', '2026-07-22 01:40:54'),
(61, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-23 03:30:48'),
(62, 134, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-23 03:33:11'),
(63, 181, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-23 06:17:21'),
(64, 181, 'Logout', 'auth', '', '::1', '2026-07-23 06:24:20'),
(65, 134, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-23 06:24:33'),
(66, 134, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-24 02:04:38'),
(67, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-24 02:08:41'),
(68, 181, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-27 01:47:49'),
(69, 181, 'Logout', 'auth', '', '::1', '2026-07-27 01:48:36'),
(70, 181, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-27 01:48:52'),
(71, 181, 'Logout', 'auth', '', '::1', '2026-07-27 01:49:00'),
(72, 134, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-27 01:50:10'),
(73, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-27 02:00:42'),
(74, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-29 02:03:19'),
(75, 134, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-29 07:50:14'),
(76, 1, 'Login', 'auth', 'Login berhasil', '::1', '2026-07-29 07:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `catatan`
--

CREATE TABLE `catatan` (
  `id` int(11) NOT NULL,
  `isi` text NOT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `warna` varchar(7) DEFAULT '#f59e0b',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id` int(11) NOT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `nidn` varchar(50) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `kualifikasi` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `scopus_id` varchar(50) DEFAULT NULL,
  `sinta_id` varchar(50) DEFAULT NULL,
  `orcid_id` varchar(50) DEFAULT NULL,
  `wos_id` varchar(50) DEFAULT NULL,
  `google_scholar` varchar(100) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Aktif','Tidak Aktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `prodi_id`, `nidn`, `nama`, `kualifikasi`, `email`, `scopus_id`, `sinta_id`, `orcid_id`, `wos_id`, `google_scholar`, `jabatan`, `created_at`, `status`) VALUES
(1, 4, '0427118201', 'Dr. Dyah Lyesmaya, S.S., M.Pd.', 'Doktor', '	dyah.lyesmaya@nusaputra.ac.id', '57201234501', '6100200', '0000-0002-1122-3355', 'AAO-7788-2020', 'dyah_l_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(2, 4, '0008046702', 'Assoc. Prof. Dr. Entit Puspita, S.Pd., M.Si.', 'Doktor', 'entit.pustpita@nusaputra.ac.id', '57201234503', '6100400', '0000-0001-3344-5577', 'AAP-8899-2021', 'entit_p_scholar', 'Lektor Kepala', '2026-07-17 12:32:04', 'Aktif'),
(3, 4, '0415087009', 'Assoc. Prof. Dr. Ayi Abdurahman, M.Pd., M.M.', 'Doktor', 'ayi.abdurahman@nusaputra.ac.id', '57201234502', '6100300', '0000-0003-2233-4466', NULL, 'ayi_a_scholar', 'Lektor Kepala', '2026-07-17 12:32:04', 'Aktif'),
(4, 4, '0429087304', 'Dr. Agus Hendriyanto, M.Pd.', 'Doktor', 'agus.hendriyanto@nusaputra.ac.id', '57201234507', '6100800', '0000-0002-7788-9911', 'AAR-0011-2021', 'agus_h_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(5, 4, '8915420021', 'Dr. H. Ujang Syarip Hidayat, M.Pd.', 'Doktor', 'ujang.syarif@nusaputra.ac.id', '57201234506', '6100700', '0000-0001-6677-8800', NULL, 'ujang_s_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(6, 4, '401056501', 'Dr. Hj. Wiwin Winarni M., M.Pd.', 'Doktor', 'wiwin.winarni@nusaputra.ac.id', '57201234505', '6100600', '0000-0003-5566-7799', 'AAQ-9900-2022', 'wiwin_w_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(7, 4, '8906160022', 'Prof. Muhibbin Syah, M.Ed.', 'Profesor', '-', '55456789000', '6100900', '0000-0003-8899-0022', 'AAS-1122-2018', 'muhibbin_sc', 'Guru Besar', '2026-07-17 12:32:04', 'Aktif'),
(8, 4, '0403048803', 'Assoc. Prof. Dr. Samsul Pahmi, S.Pd., M.Pd.', 'Doktor', 'samsul.pahmi@nusaputra.ac.id ', '57201234504', '6100500', '0000-0002-4455-6688', NULL, 'samsul_p_scholar', 'Lektor Kepala', '2026-07-17 12:32:04', 'Aktif'),
(10, 3, '0405058002', 'Prof. Dr. RR. Dewi Anggraeni, S.H., M.H.', 'Profesor', 'rrdewianggraeni@nusaputra.ac.id', '57209678901', '6555666', '0000-0001-7788-9900', 'AAM-5566-2020', 'dewi_a_scholar', 'Guru Besar', '2026-07-17 12:32:04', 'Aktif'),
(11, 3, '0426069302', 'Dr. Armansyah, S.H., M.H.', 'Doktor', 'armansyah@nusaputra.ac.id', '57210789012', '6666777', '0000-0003-8899-0011', NULL, 'armansyah_sc', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(12, 3, '0414058705', 'CSA Teddy Lesmana, S.H., M.H.', 'Magister', 'teddy.lesmana@nusaputra.ac.id', '57213111222', '6999111', '0000-0002-1111-2222', 'TED-2222-2021', 'teddy_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(13, 3, '0407016701', 'Dr. dr. Heri Heriyanto, M.M.', 'Doktor', 'heri.heriyanto@nusaputra.ac.id', '57209999111', '6888111', '0000-0002-9999-1111', 'HER-1111-2022', 'heri_scholar_id', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(14, 3, '0415098002', 'Dr. (Cand). Rida Ista Sitepu, S.H., M.H.', 'Magister', 'rida@nusaputra.ac.id', '57213222333', '6999222', '0000-0003-2222-3333', NULL, 'rida_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(15, 3, '0420057201', 'Dr. Bram Bachrum Baan, M.P.H.', 'Doktor', 'bram.bbaan@nusaputra.ac.id', '57211890123', '6777888', '0000-0002-9900-1122', 'AAN-6677-2021', 'bram_b_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(16, 3, '041703870', 'Dr. M. Roken Fadly, M.K., M.H.', 'Doktor', '-', '57213012345', '6999000', '0000-0001-0011-2233', NULL, 'roken_scholar_id', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(17, 3, '0402048801', 'Dr. (Cand). Nuchraha Alhuda Hasnda, S.H., M.H.', 'Magister', 'nuchraha.alhuda@nusaputra.ac.id', '57213333444', '6999333', '0000-0001-3333-4444', 'NUC-4444-2020', 'nuchraha_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(18, 3, '0401067608', 'Dr. Padlilah, S.H., M.H.', 'Doktor', 'Padlilah@nusaputra.ac.id\n', '57212901234', '6888999', NULL, NULL, 'padlilah_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(19, 3, '0120240047', 'Prof. MARCO MARCHETTI, Ph.D', 'Profesor', '-', '36026892600', '6223344', '0000-0001-2233-4455', 'AAJ-2233-2018', 'marco_m_scholar', 'Guru Besar', '2026-07-17 12:32:04', 'Aktif'),
(20, 1, '0303037304', 'Assoc. Prof. Dr. Adhi Kusnadi, S.T., M.Si.', 'Doktor', 'adhi.kusnadi@nusaputra.ac.id', '57205123456', '6012345', '0000-0003-2345-6789', 'AAC-2345-2020', 'adhi_scholar_id', 'Lektor Kepala', '2026-07-17 12:32:04', 'Aktif'),
(21, 1, '0206077202', 'Prof. Deden Witarsyah, S.T., M.Eng., Ph.D.', 'Profesor', 'deden.witarsyah@nusaputra.ac.id', '57191234567', '6123456', '0000-0002-8765-4321', 'AAD-3456-2019', 'deden_scholar_id', 'Guru Besar', '2026-07-17 12:32:04', 'Aktif'),
(22, 1, '0418038201', 'Risnandar, Ph.D.', 'Doktor', 'risnandar@nusaputra.ac.id', '57202345678', '6234567', '0000-0001-3456-7890', 'AAE-4567-2020', 'risnand_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(23, 1, '4.26018003E8', 'Assoc. Prof. Dr. Aswan Supriyadi Sunge, S.E., M.Kom., CDS.', 'Doktor', 'aswan.supriyadi@nusaputra.ac.id', '57214789012', '6890123', '0000-0001-9012-3456', 'AAH-7890-2021', 'aswan_scholar_id', 'Lektor Kepala', '2026-07-17 12:32:04', 'Aktif'),
(24, 1, '2.0058305E7', 'Assoc. Prof. Dr. Chaerur Rozikin, S.Kom., M.Kom.', 'Doktor', 'chaerur.rozikin@nusaputra.ac.id', '57215890123', '6901234', '0000-0003-0123-4567', NULL, 'chaerur_scholar', 'Lektor Kepala', '2026-07-17 12:32:04', 'Aktif'),
(25, 1, '4.10107806E8', 'Zeldi Suryady, S.T., M.Sc., Ph.D.', 'Doktor', 'zeldi.suryady@nusaputra.ac.id', '57211345678', '6345678', '0000-0003-4567-8901', NULL, 'zeldi_scholar_id', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(26, 1, '0428118903', 'Dr. Eng. Agustami Sitorus, S.TP., M.Si.', 'Doktor', 'agustami@nusaputra.ac.id', '57203456789', '6456789', '0000-0002-5678-9012', 'AAF-5678-2021', 'agustami_scholar', 'Asisten Ahli', '2026-07-17 12:32:04', 'Aktif'),
(27, 1, '0413038701', 'Dr. Indra Hermawan, S.Kom., M.Kom.', 'Doktor', 'indra.hermawan@nusaputra.ac.id', '57204567890', '6567890', '0000-0001-6789-0123', NULL, 'indra_h_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(28, 2, '0415029304\'', 'Dr. Yusuf Iskandar, S.Si., M.M.', 'Doktor', 'yusuf.iskandar@nusaputra.ac.id', '57212567890', '6678901', '0000-0003-7890-1234', 'AAG-6789-2022', 'yusuf_scholar_id', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(29, 2, '0307028902', 'Dr. Slamet Sutrisno, S.T., M.M.', 'Doktor', '-', '57213678901', '6789012', '0000-0002-8901-2345', NULL, 'slamet_scholar_id', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(30, 2, '0401017601', 'Dr. Hari Muharam, S.E., M.M.', 'Doktor', '-', '57205234567', '6111222', '0000-0002-3344-5566', 'AAK-3344-2021', 'hari_m_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(31, 2, '0407036606', 'Assoc. Prof. Hesri Mintawati, S.Pd., M.M., Ph.D.', 'Doktor', 'hesri.mintawati@nusaputra.ac.id', '57207456789', '6333444', '0000-0003-5566-7788', 'AAL-4455-2022', 'hesri_scholar_id', 'Lektor Kepala', '2026-07-17 12:32:04', 'Aktif'),
(32, 2, '0416107003', 'Dr. Sitti Hikmawatty, S.ST., M.Pd.', 'Doktor', 'hikmahibu20@gmail.com', '57208567890', '6444555', '0000-0002-6677-8899', NULL, 'sitti_h_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(33, 2, '0429087304', 'Dr. Nur Hasan Kurniawan, S.E., M.M.', 'Doktor', '-', '57206345678', '6222333', '0000-0001-4455-6677', NULL, 'nurhasan_scholar', 'Lektor', '2026-07-17 12:32:04', 'Aktif'),
(34, 2, '-', 'Prof. Angel Esmeralda, Ph.D.', 'Profesor', '-', '57201111222', '6112233', '0000-0002-1122-3344', 'AAI-1122-2020', 'angel_scholar_id', 'Guru Besar', '2026-07-17 12:32:04', 'Aktif'),
(35, 5, '0404128502', 'Assoc. Prof. Nunik Destria Arianti, Ph.D.', 'Doktor', 'nunikdestriaarianti@gmail.com', '57213856700', '6005883', '0000-0002-1234-5678', 'AAB-1234-2021', 'nunik_scholar_id', 'Lektor Kepala', '2026-07-17 12:32:04', 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime DEFAULT NULL,
  `jenis_event` enum('Seminar Proposal','Sidang Tesis','Ujian Komprehensif','Wisuda','Rapat Prodi','Deadline','Lainnya') DEFAULT 'Lainnya',
  `warna` varchar(7) DEFAULT '#60a5fa',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id`, `judul`, `deskripsi`, `prodi_id`, `tanggal_mulai`, `tanggal_selesai`, `jenis_event`, `warna`, `created_by`, `created_at`) VALUES
(1, 'Libur Nasional: Hari Kemerdekaan Republik Indonesia Ke-81', NULL, NULL, '2025-08-17 00:00:00', '2025-08-17 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(2, 'Masa Perwalian & Pengisian KRS Semester Gasal TA 2025-2026', NULL, NULL, '2025-09-01 00:00:00', '2025-09-05 23:59:59', 'Lainnya', '#8c0c4c', 1, '2026-07-18 09:09:46'),
(3, 'Masa MABIM Mahasiswa Angkatan 2025', NULL, NULL, '2025-09-08 00:00:00', '2025-09-14 23:59:59', 'Lainnya', '#0284c7', 1, '2026-07-18 09:09:46'),
(4, 'Batas Akhir Pengajuan & Pembayaran Cuti Semester Gasal', NULL, NULL, '2025-09-14 00:00:00', '2025-09-14 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(5, 'Hari Pertama Perkuliahan Semester Gasal TA 2025-2026', NULL, NULL, '2025-09-15 00:00:00', '2025-09-15 23:59:59', 'Lainnya', '#2563eb', 1, '2026-07-18 09:09:46'),
(6, 'Pembukaan Pendaftaran Mahasiswa Baru Gelombang I', NULL, NULL, '2025-09-20 00:00:00', '2025-09-20 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(7, 'Batas Akhir Pembayaran SPP Tahap 2 Angkatan 2022, 2023, 2024', NULL, NULL, '2025-10-10 00:00:00', '2025-10-10 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(8, 'Batas Akhir Pembayaran SPP Tahap 1 Angkatan 2025', NULL, NULL, '2025-10-10 00:00:00', '2025-10-10 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(9, 'Batas Akhir Pendaftaran Mahasiswa Baru Gelombang I', NULL, NULL, '2025-10-09 00:00:00', '2025-10-09 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(10, 'Test / Seleksi Penerimaan Mahasiswa Baru Gelombang I', NULL, NULL, '2025-10-13 00:00:00', '2025-10-13 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(11, 'Pembukaan Pendaftaran Mahasiswa Baru Gelombang II', NULL, NULL, '2025-10-13 00:00:00', '2025-10-13 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(12, 'Pengumuman Hasil Seleksi PMB Gelombang I', NULL, NULL, '2025-10-17 00:00:00', '2025-10-17 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(13, 'Batas Akhir Daftar Ulang Mahasiswa Baru Gelombang I', NULL, NULL, '2025-11-03 00:00:00', '2025-11-03 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(14, 'Minggu Tenang UTS Semester Gasal TA 2025-2026', NULL, NULL, '2025-11-03 00:00:00', '2025-11-07 23:59:59', 'Lainnya', '#94a3b8', 1, '2026-07-18 09:09:46'),
(15, 'Libur Nasional: Maulid Nabi Muhammad SAW 1447H', NULL, NULL, '2025-11-05 00:00:00', '2025-11-05 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(16, 'Pelaksanaan UTS Semester Gasal TA 2025-2026', NULL, NULL, '2025-11-08 00:00:00', '2025-11-16 23:59:59', 'Lainnya', '#dc2626', 1, '2026-07-18 09:09:46'),
(17, 'Minggu Susulan UTS Semester Gasal TA 2025-2026', NULL, NULL, '2025-11-17 00:00:00', '2025-11-23 23:59:59', 'Lainnya', '#f87171', 1, '2026-07-18 09:09:46'),
(18, 'Batas Akhir Penyelesaian Pembayaran Dispensasi', NULL, NULL, '2025-11-21 00:00:00', '2025-11-21 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(19, 'Mulai Perkuliahan Semester Gasal Tahap 2', NULL, NULL, '2025-11-24 00:00:00', '2025-11-24 23:59:59', 'Lainnya', '#2563eb', 1, '2026-07-18 09:09:46'),
(20, 'Batas Akhir Pendaftaran Mahasiswa Baru Gelombang II', NULL, NULL, '2025-11-07 00:00:00', '2025-11-07 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(21, 'Test / Seleksi Penerimaan Mahasiswa Baru Gelombang II', NULL, NULL, '2025-11-17 00:00:00', '2025-11-17 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(22, 'Pembukaan Pendaftaran Mahasiswa Baru Gelombang III', NULL, NULL, '2025-11-17 00:00:00', '2025-11-17 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(23, 'Pengumuman Hasil Seleksi PMB Gelombang II', NULL, NULL, '2025-11-21 00:00:00', '2025-11-21 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(24, 'Batas Akhir Daftar Ulang Mahasiswa Baru Gelombang II', NULL, NULL, '2025-12-05 00:00:00', '2025-12-05 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(25, 'Batas Akhir Pendaftaran Mahasiswa Baru Gelombang III', NULL, NULL, '2025-12-08 00:00:00', '2025-12-08 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(26, 'Batas Pembayaran SPP Tahap 2 Angkatan 2025', NULL, NULL, '2025-12-29 00:00:00', '2025-12-29 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(27, 'Libur Nasional: Hari Raya Natal', NULL, NULL, '2025-12-25 00:00:00', '2025-12-25 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(28, 'Cuti Bersama Hari Raya Natal', NULL, NULL, '2025-12-26 00:00:00', '2025-12-26 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(29, 'Libur Perkuliahan Akhir Tahun', NULL, NULL, '2025-12-27 00:00:00', '2026-01-01 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(30, 'Libur Nasional: Tahun Baru Masehi', NULL, NULL, '2026-01-01 00:00:00', '2026-01-01 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(31, 'Batas Akhir Daftar Ulang Mahasiswa Baru Gelombang III', NULL, NULL, '2026-01-03 00:00:00', '2026-01-03 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(32, 'Batas Akhir Pembayaran Semester Pendek', NULL, NULL, '2026-01-02 00:00:00', '2026-01-02 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(33, 'Batas Akhir Daftar Ulang Mahasiswa Baru Gelombang IV', NULL, NULL, '2026-01-04 00:00:00', '2026-01-04 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(34, 'Test / Seleksi Penerimaan Mahasiswa Baru Gelombang III', NULL, NULL, '2026-01-05 00:00:00', '2026-01-05 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(35, 'Pembukaan Pendaftaran Mahasiswa Baru Gelombang IV', NULL, NULL, '2026-01-05 00:00:00', '2026-01-05 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(36, 'Pengumuman Hasil Seleksi PMB Gelombang III', NULL, NULL, '2026-01-09 00:00:00', '2026-01-09 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(37, 'Libur Nasional: Isra Mi\'raj Nabi Muhammad SAW 1447H', NULL, NULL, '2026-01-27 00:00:00', '2026-01-27 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(38, 'Minggu Tenang UAS Semester Gasal TA 2025-2026', NULL, NULL, '2026-01-19 00:00:00', '2026-01-23 23:59:59', 'Lainnya', '#94a3b8', 1, '2026-07-18 09:09:46'),
(39, 'Pelaksanaan UAS Semester Gasal TA 2025-2026', NULL, NULL, '2026-01-24 00:00:00', '2026-02-01 23:59:59', 'Lainnya', '#dc2626', 1, '2026-07-18 09:09:46'),
(40, 'Minggu Susulan UAS Semester Gasal TA 2025-2026', NULL, NULL, '2026-02-02 00:00:00', '2026-02-08 23:59:59', 'Lainnya', '#f87171', 1, '2026-07-18 09:09:46'),
(41, 'Masa Remedial Semester Gasal TA 2025-2026', NULL, NULL, '2026-02-02 00:00:00', '2026-02-08 23:59:59', 'Lainnya', '#ea580c', 1, '2026-07-18 09:09:46'),
(42, 'Batas Akhir Pembayaran SPP Tahap 1 / Registrasi Genap', NULL, NULL, '2026-02-09 00:00:00', '2026-02-09 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(43, 'Masa Perwalian & Pengisian KRS Semester Genap TA 2025-2026', NULL, NULL, '2026-02-09 00:00:00', '2026-02-13 23:59:59', 'Lainnya', '#8c0c4c', 1, '2026-07-18 09:09:46'),
(44, 'Batas Akhir Pengajuan Tambahan SKS & Cuti Semester Genap', NULL, NULL, '2026-02-15 00:00:00', '2026-02-15 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(45, 'Hari Pertama Perkuliahan Semester Genap TA 2025-2026', NULL, NULL, '2026-02-16 00:00:00', '2026-02-16 23:59:59', 'Lainnya', '#0891b2', 1, '2026-07-18 09:09:46'),
(46, 'Libur Nasional: Tahun Baru Imlek 2577', NULL, NULL, '2026-02-17 00:00:00', '2026-02-17 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(47, 'Batas Akhir Pendaftaran Wisuda Ke-13', NULL, NULL, '2025-07-31 00:00:00', '2025-07-31 23:59:59', 'Wisuda', '#9333ea', 1, '2026-07-18 09:09:46'),
(48, 'Pelaksanaan Wisuda Ke-13', NULL, NULL, '2025-09-03 00:00:00', '2025-09-03 23:59:59', 'Wisuda', '#9333ea', 1, '2026-07-18 09:09:46'),
(49, 'Batas Akhir Pendaftaran Wisuda Ke-14', NULL, NULL, '2026-02-06 00:00:00', '2026-02-06 23:59:59', 'Wisuda', '#9333ea', 1, '2026-07-18 09:09:46'),
(50, 'Pelaksanaan Wisuda Ke-14', NULL, NULL, '2026-04-03 00:00:00', '2026-04-03 23:59:59', 'Wisuda', '#9333ea', 1, '2026-07-18 09:09:46'),
(51, 'Batas Akhir Pendaftaran Wisuda Ke-15', NULL, NULL, '2026-07-10 00:00:00', '2026-07-10 23:59:59', 'Wisuda', '#9333ea', 1, '2026-07-18 09:09:46'),
(52, 'Pelaksanaan Wisuda Ke-15', NULL, NULL, '2026-08-11 00:00:00', '2026-08-11 23:59:59', 'Wisuda', '#9333ea', 1, '2026-07-18 09:09:46'),
(53, 'Batas Akhir Pendaftaran Wisuda Ke-16', NULL, NULL, '2026-08-14 00:00:00', '2026-08-14 23:59:59', 'Wisuda', '#9333ea', 1, '2026-07-18 09:09:46'),
(54, 'Pelaksanaan Wisuda Ke-16', NULL, NULL, '2026-09-09 00:00:00', '2026-09-09 23:59:59', 'Wisuda', '#9333ea', 1, '2026-07-18 09:09:46'),
(55, 'Libur Nasional: Hari Suci Nyepi', NULL, NULL, '2026-03-19 00:00:00', '2026-03-19 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(56, 'Libur Nasional: Hari Raya Idul Fitri 1447H', NULL, NULL, '2026-03-30 00:00:00', '2026-03-31 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(57, 'Cuti Bersama Hari Raya Idul Fitri 1447H', NULL, NULL, '2026-03-21 00:00:00', '2026-03-29 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(58, 'Libur Nasional: Jumat Agung (Good Friday)', NULL, NULL, '2026-04-03 00:00:00', '2026-04-03 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(59, 'Batas Akhir Daftar Ulang Mahasiswa Baru Gelombang IV', NULL, NULL, '2026-04-10 00:00:00', '2026-04-10 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(60, 'Test / Seleksi Penerimaan Mahasiswa Baru Gelombang IV', NULL, NULL, '2026-04-13 00:00:00', '2026-04-13 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(61, 'Pembukaan Pendaftaran Mahasiswa Baru Gelombang V', NULL, NULL, '2026-04-13 00:00:00', '2026-04-13 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(62, 'Pengumuman Hasil Seleksi PMB Gelombang IV', NULL, NULL, '2026-04-17 00:00:00', '2026-04-17 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(63, 'Minggu Tenang UTS Semester Genap TA 2025-2026', NULL, NULL, '2026-04-20 00:00:00', '2026-04-24 23:59:59', 'Lainnya', '#94a3b8', 1, '2026-07-18 09:09:46'),
(64, 'Pelaksanaan UTS Semester Genap TA 2025-2026', NULL, NULL, '2026-04-25 00:00:00', '2026-05-03 23:59:59', 'Lainnya', '#dc2626', 1, '2026-07-18 09:09:46'),
(65, 'Batas Akhir Pembayaran Tambahan SKS Mengulang Gasal TA 2025-2026', NULL, NULL, '2026-04-24 00:00:00', '2026-04-24 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(66, 'Libur Nasional: Hari Buruh Internasional', NULL, NULL, '2026-05-01 00:00:00', '2026-05-01 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(67, 'Batas Akhir Daftar Ulang Mahasiswa Baru Gelombang V', NULL, NULL, '2026-05-09 00:00:00', '2026-05-09 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(68, 'Minggu Susulan UTS Semester Genap TA 2025-2026', NULL, NULL, '2026-05-04 00:00:00', '2026-05-10 23:59:59', 'Lainnya', '#f87171', 1, '2026-07-18 09:09:46'),
(69, 'Libur Nasional: Kenaikan Isa Almasih', NULL, NULL, '2026-05-14 00:00:00', '2026-05-14 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(70, 'Test / Seleksi Penerimaan Mahasiswa Baru Gelombang V', NULL, NULL, '2026-05-18 00:00:00', '2026-05-18 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(71, 'Pengumuman Hasil Seleksi PMB Gelombang V', NULL, NULL, '2026-05-22 00:00:00', '2026-05-22 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(72, 'Batas Akhir Pendaftaran Mahasiswa Baru Gelombang V', NULL, NULL, '2026-05-22 00:00:00', '2026-05-22 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(73, 'Libur Nasional: Hari Raya Waisak', NULL, NULL, '2026-05-31 00:00:00', '2026-05-31 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(74, 'Libur Nasional: Hari Lahir Pancasila', NULL, NULL, '2026-06-01 00:00:00', '2026-06-01 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(75, 'Pembukaan Pendaftaran Semester Pendek TA 2025-2026', NULL, NULL, '2026-06-01 00:00:00', '2026-06-01 23:59:59', 'Lainnya', '#d97706', 1, '2026-07-18 09:09:46'),
(76, 'Libur Nasional: Hari Raya Idul Adha 1447H', NULL, NULL, '2026-06-27 00:00:00', '2026-06-27 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(77, 'Penutupan & Batas Pembayaran Semester Pendek TA 2025-2026', NULL, NULL, '2026-06-26 00:00:00', '2026-06-26 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(78, 'Pelaksanaan UAS Semester Genap TA 2025-2026', NULL, NULL, '2026-07-04 00:00:00', '2026-07-12 23:59:59', 'Lainnya', '#dc2626', 1, '2026-07-18 09:09:46'),
(79, 'Test / Seleksi Penerimaan Mahasiswa Baru Gelombang IV (Susulan)', NULL, NULL, '2026-07-12 00:00:00', '2026-07-12 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(80, 'Pengumuman Hasil Seleksi PMB Gelombang IV (Susulan)', NULL, NULL, '2026-07-14 00:00:00', '2026-07-14 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(81, 'Libur Nasional: Tahun Baru Hijriyah 1448H', NULL, NULL, '2026-07-16 00:00:00', '2026-07-16 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(82, 'Minggu Susulan UAS Semester Genap TA 2025-2026', NULL, NULL, '2026-07-13 00:00:00', '2026-07-19 23:59:59', 'Lainnya', '#f87171', 1, '2026-07-18 09:09:46'),
(83, 'Masa Remedial Semester Genap TA 2025-2026', NULL, NULL, '2026-07-20 00:00:00', '2026-07-26 23:59:59', 'Lainnya', '#ea580c', 1, '2026-07-18 09:09:46'),
(84, 'Penutupan Pendaftaran Semester Pendek TA 2025-2026', NULL, NULL, '2026-07-26 00:00:00', '2026-07-26 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(85, 'Penetapan Nilai KHS Semester Genap TA 2025-2026', NULL, NULL, '2026-07-27 00:00:00', '2026-07-31 23:59:59', 'Lainnya', '#64748b', 1, '2026-07-18 09:09:46'),
(86, 'Penentuan Mata Kuliah Semester Pendek Semester Genap', NULL, NULL, '2026-07-27 00:00:00', '2026-07-31 23:59:59', 'Lainnya', '#d97706', 1, '2026-07-18 09:09:46'),
(87, 'Perkuliahan Semester Pendek TA 2025-2026', NULL, NULL, '2026-08-03 00:00:00', '2026-08-23 23:59:59', 'Lainnya', '#d97706', 1, '2026-07-18 09:09:46'),
(88, 'Masa Perwalian & Pengisian KRS Semester Gasal TA 2026-2027', NULL, NULL, '2026-08-03 00:00:00', '2026-08-07 23:59:59', 'Lainnya', '#8c0c4c', 1, '2026-07-18 09:09:46'),
(89, 'Test / Seleksi Penerimaan Mahasiswa Baru Gelombang I (2026/2027)', NULL, NULL, '2026-08-11 00:00:00', '2026-08-11 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(90, 'Pengumuman Hasil Seleksi PMB Gelombang I (2026/2027)', NULL, NULL, '2026-08-13 00:00:00', '2026-08-13 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(91, 'Batas Akhir Pembayaran Tahap 1 / Registrasi Gasal TA 2026-2027', NULL, NULL, '2026-08-07 00:00:00', '2026-08-07 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(92, 'Batas Pembayaran Tambah SKS Mengulang Gasal TA 2026-2027', NULL, NULL, '2026-08-13 00:00:00', '2026-08-13 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(93, 'Libur Nasional: Hari Kemerdekaan Republik Indonesia Ke-81', NULL, NULL, '2026-08-17 00:00:00', '2026-08-17 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(94, 'Batas Akhir Pendaftaran Mahasiswa Baru Gelombang V (2026/2027)', NULL, NULL, '2026-08-22 00:00:00', '2026-08-22 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(95, 'Test / Seleksi Penerimaan Mahasiswa Baru Gelombang V (2026/2027)', NULL, NULL, '2026-08-23 00:00:00', '2026-08-23 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(96, 'Pengumuman Hasil Seleksi PMB Gelombang V (2026/2027)', NULL, NULL, '2026-08-24 00:00:00', '2026-08-24 23:59:59', 'Lainnya', '#16a34a', 1, '2026-07-18 09:09:46'),
(97, 'Minggu Ujian Semester Pendek TA 2025-2026', NULL, NULL, '2026-08-24 00:00:00', '2026-08-28 23:59:59', 'Lainnya', '#d97706', 1, '2026-07-18 09:09:46'),
(98, 'Penetapan KHS Semester Genap TA 2025-2026', NULL, NULL, '2026-08-31 00:00:00', '2026-09-04 23:59:59', 'Lainnya', '#64748b', 1, '2026-07-18 09:09:46'),
(99, 'Batas Akhir Daftar Ulang Mahasiswa Baru Gelombang I (2026/2027)', NULL, NULL, '2026-09-07 00:00:00', '2026-09-07 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(101, 'Masa MABIM Mahasiswa Angkatan 2026 TA 2026-2027', NULL, NULL, '2026-09-14 00:00:00', '2026-09-20 23:59:59', 'Lainnya', '#0284c7', 1, '2026-07-18 09:09:46'),
(102, 'Hari Pertama Perkuliahan Semester Gasal TA 2026-2027', NULL, NULL, '2026-09-21 00:00:00', '2026-09-21 23:59:59', 'Lainnya', '#2563eb', 1, '2026-07-18 09:09:46'),
(103, 'Libur Nasional: Maulid Nabi Muhammad SAW 1448H', NULL, NULL, '2026-09-25 00:00:00', '2026-09-25 23:59:59', '', '#94a3b8', 1, '2026-07-18 09:09:46'),
(104, 'Batas Akhir Pengajuan & Pembayaran Cuti Semester Gasal TA 2026-2027', NULL, NULL, '2026-09-25 00:00:00', '2026-09-25 23:59:59', 'Deadline', '#ef4444', 1, '2026-07-18 09:09:46'),
(105, 'Sidang Tesis S2 Magister Manajemen', '', 2, '2026-07-18 09:00:00', '2026-07-18 11:00:00', 'Sidang Tesis', '#8c0c4c', 1, '2026-07-18 11:15:00'),
(106, 'sidang tesis', 'sidang', 1, '2026-07-31 08:00:00', '2026-07-31 16:00:00', 'Sidang Tesis', '#16a34a', 1, '2026-07-29 03:18:04');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('Pria','Wanita') DEFAULT NULL,
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
  `nama_ibu` varchar(100) DEFAULT NULL,
  `agama` varchar(20) DEFAULT 'Islam',
  `kelas` varchar(20) DEFAULT 'Kelas B',
  `konsentrasi` varchar(50) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `nim`, `nik`, `nama`, `jenis_kelamin`, `prodi_id`, `angkatan`, `status`, `no_hp`, `email`, `alamat`, `judul_tesis`, `dosen_pembimbing`, `catatan_admin`, `created_at`, `updated_at`, `nama_ibu`, `agama`, `kelas`, `konsentrasi`, `tempat_lahir`, `tanggal_lahir`) VALUES
(12, '20260140001', '3202292509940005', 'SIROJUDIN, S.AK', 'Pria', 4, '2026', 'Aktif', '08980614440', 'sirojudin_mpd26@nusaputra.ac.id', 'Kp Kutasirna Rt 08 Rw 03 Desa Kutasirna Kec Cisaat Kab Sukabumi', 'Magni aut quasi quia', NULL, NULL, '2026-07-18 09:32:19', '2026-07-18 10:23:04', 'EMIM', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1995-04-05'),
(13, '20260140002', '3202450301980004', 'MOH. IMAM', 'Pria', 4, '2026', 'Aktif', '085217143894', 'moh.imam_mpd26@nusaputra.ac.id', 'KP. KUTA RT 002 RW 006, DESA PURABAYA, KECAMATAN PURABAYA, KABUPATEN SUKABUMI', NULL, NULL, NULL, '2026-07-18 09:32:19', '2026-07-18 09:48:00', 'ROCHYATI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1998-01-03'),
(14, '20260140003', '3203160607990005', 'ANGGI SUGIRI', 'Pria', 4, '2026', 'Aktif', '085624059512', 'anggi.sugiri_mpd26@nusaputra.ac.id', 'KP. PADANGENYANG', NULL, NULL, NULL, '2026-07-18 09:32:19', '2026-07-18 09:48:00', 'MARNI', 'Islam', 'Kelas B', NULL, 'CIANJUR', '1999-07-08'),
(15, '20260140004', '3202245307890003', 'RANI YULIANI', 'Wanita', 4, '2026', 'Aktif', '085718575693', 'rani.yuliani_mpd26@nusaputra.ac.id', 'kp. kadaleman', NULL, NULL, NULL, '2026-07-18 09:32:19', '2026-07-18 09:48:00', 'ITAH', 'Islam', 'Kelas B', NULL, 'KABUPATEN SUKABUMI', '1989-07-13'),
(16, '20250140001', '3272081050000001', 'RIKA RAHAYU', 'Wanita', 4, '2025', 'Aktif', '085861528003', 'rika.rahayu_mpd25@nusaputra.ac.id', 'Cipangkar selatan RT 004 RW 006 kelurahan Citamur Kecamatan Cikole kota Sukabumi 43115', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'LINA', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '2000-05-21'),
(17, '20250140002', '3202245075850008', 'LUSIANA ALAWIYAH', 'Wanita', 4, '2025', 'Aktif', '081563900085', 'lusiana.alawiyah@nusaputra.ac.id', 'Kp. Cikurak RT 001 RW 009, Kelurahan Sudah, Kecamatan Sukabumi, Kabupaten Sukabumi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:11:10', 'EUIS HERAWATI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1985-07-15'),
(18, '20250140003', '3202241110860007', 'DEDE KURNIA SAFARI', 'Pria', 4, '2025', 'Aktif', '0816335497', 'dede.kurnia@nusaputra.ac.id', 'Kp. Cikuak', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'HALIMAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1986-10-01'),
(19, '20250140004', '3801130102900001', 'ESIH SUKAESIH', 'Wanita', 4, '2025', 'Aktif', '082321733008', 'esih.sukaesih@nusaputra.ac.id', 'PESANTREN AL-AMI TUG JL.KADUDAMPIT KM.5 KP.CIKARIOYA', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'SAPIAH', 'Islam', 'Kelas B', NULL, 'PANDEGLANG', '1990-02-11'),
(20, '20250140005', '3202370605790002', 'SAEPULANA', 'Pria', 4, '2025', 'Aktif', '085864557969', 'saepulana@nusaputra.ac.id', 'KP. CIBIRU', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'KARTIWI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1979-05-06'),
(21, '20250140006', '3602011028800005', 'DENY SETIAWAN', 'Pria', 4, '2025', 'Aktif', '081563672677', 'deny.setiawan_mpd25@nusaputra.ac.id', 'Kp. Cikamunding RT.2 RW 1 Cikamunding', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:11:10', 'JUMASIH', 'Islam', 'Kelas B', NULL, 'LEBAK', '1980-02-11'),
(22, '20250140007', '3202331004850001', 'ASEP RUSWANDI', 'Pria', 4, '2025', 'Aktif', '085603925600', 'asep.ruswandi_mpd25@nusaputra.ac.id', 'Kp. Cirrumit', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'UMAMAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1985-04-10'),
(23, '20250140008', '3272042509890002', 'ISMATULLAH', 'Pria', 4, '2025', 'Aktif', '085720756290', 'ismattullah5451@gmail.com', 'kp nagrak tengah rt 15 rw 02 desa nagrak kelurahan cisaat kab sukabumi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'AJAN', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1990-05-25'),
(24, '20250140009', '3202112611950001', 'SATRIA NURRAJAB TANOEJIWA', 'Pria', 4, '2025', 'Aktif', '085659445944', 'satria.nurrajab@nusaputra.ac.id', 'Jl. Primer No.327', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'LIA YULIATNI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1995-11-26'),
(25, '20250140010', '3202122004880003', 'HERU HERMAWAN', 'Pria', 4, '2025', 'Aktif', '085722573025', 'heru.hermawan@nusaputra.ac.id', 'Kp. Bobojong', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:11:10', 'E. KARTINAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1988-04-20'),
(26, '20250140011', '3202380212950003', 'ASEP PURNAWAN', 'Pria', 4, '2025', 'Aktif', '085759629433', 'asep.purnawan@nusaputra.ac.id', 'KP.CIKARAE RT.01/07 DESA CIKARAE KECAMATAN KEC. PURABAYA', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:11:10', 'YUYUN', 'Islam', 'Kelas B', NULL, 'KABUPATEN SUKABUMI', '1995-12-02'),
(27, '20250140012', '3202215304910005', 'LUKY BELA MULTINA', 'Wanita', 4, '2025', 'Aktif', '085881222062', 'luky.bela@nusaputra.ac.id', 'Perum Griya Pratama rt 001 rw 011, desa karang Rt 001 rw 011, desa karang sukabumi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:11:10', 'NENDEN EMI SRI MULTIANI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1995-09-13'),
(28, '20250140013', '3202420707720001', 'CUCU KARNELIS', 'Wanita', 4, '2025', 'Aktif', '085720988160', 'cucu.karnelis_mpd25@nusaputra.ac.id', 'Kp. Cisaak', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'HJ. EUIS HERNAWATI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1972-07-17'),
(29, '20250140014', '3202372401820001', 'E. SURAHMAN', 'Pria', 4, '2025', 'Aktif', '6285624156421', 'e.surahman.mpd25@nusaputra.ac.id', 'Kp. Pasiriling RT 005 RW 002 Desa Pabuaran Kec. Pabuaran Kab. Sukabumi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'O. AISYAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1982-01-24'),
(30, '20250140015', '3202420308880002', 'DEDI RUKMANA', 'Pria', 4, '2025', 'Aktif', '085860621442', 'dedi.rukmana_mpd25@nusaputra.ac.id', 'Kp. Pataruman', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'HJ. DEYIH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1988-03-03'),
(31, '20250140016', '3202371908840005', 'MUHTADIN', 'Pria', 4, '2025', 'Aktif', '6285793405479', 'muhtadin_mpd25@nusaputra.ac.id', 'Kp. Sindangsemi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'YOYOH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1984-08-19'),
(32, '20250140017', '3202421310900005', 'JEPRI ISKANDAR', 'Pria', 4, '2025', 'Aktif', '085285572311', 'jepri.iskandar_mpd25@nusaputra.ac.id', 'Kp. Sumuduh RT 001/RW 001 Desa Mekartanjung Kec. Mekartanjung Kec. Cikidang', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:11:10', 'MASITOH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1990-10-13'),
(33, '20250140018', '3202344608980004', 'ANA MARIAH', 'Wanita', 4, '2025', 'Aktif', '081291979408', 'ana.mariah@nusaputra.ac.id', 'Kp. PAMATUTAN desa BOJONG GENTENG kecamatan BOJONG GENTENG rt 14 rw 08 kab.sukabumi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'CUCU FATIMAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1998-08-06'),
(34, '20250140019', '3202660036200007', 'RASMINI MASRIYANTI', 'Wanita', 4, '2025', 'Aktif', '082112816330', 'rasmini.masriyanti_mpd25@nusaputra.ac.id', 'PERUM GUNUNG JAYA PERMAI J, CEMPAKA NO.30, RT 33/RW 09, DESA GUNUNGURUH, KECAMATAN CISAAT, KABUPATEN SUKABUMI', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'NASITI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1990-03-22'),
(35, '20250140020', '3202165030100002', 'AI DALFA', 'Wanita', 4, '2025', 'Aktif', '088295019580', 'ai.dalfa_mpd25@nusaputra.ac.id', 'KP. CISEUPAN HILIR', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'ELAH NURLAILAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '2001-03-25'),
(36, '20250140021', '3202229204990002', 'ADNAN ABDILLAH', 'Pria', 4, '2025', 'Aktif', '085864478857', 'adnan.abdillah_mpd25@nusaputra.ac.id', 'Kp. Cibolang RT 030 RW.007 Desa Cibolts Kecamatan Cisaat Kabupaten Sukabumi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'LILIM HALIMAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1999-02-17'),
(37, '20250140022', '3202265507890004', 'NELIS', 'Wanita', 4, '2025', 'Aktif', '085219416104', 'nelis.mpd@nusaputra.ac.id', 'Perumaham Gunung Guruh Blok l No 20', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'LINA', 'Islam', 'Kelas B', NULL, 'KAB. SUKABUMI', '1989-07-10'),
(38, '20250140023', '3202155087770001', 'DEWI NANIK YULIAWATI', 'Wanita', 4, '2025', 'Aktif', '081382993101', 'dewi.nanik_mpd@nusaputra.ac.id', 'Kp. Panjaiu', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'ODAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1985-08-19'),
(39, '20250140024', '3202165609010005', 'SEPTI LAILA ANJANI', 'Wanita', 4, '2025', 'Aktif', '083861632707', 'septi.laila_mpd25@nusaputra.ac.id', 'KP. BENTENG', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'AGUSTIANA SOFIE ISMAYA', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '2001-09-03'),
(40, '20250140025', '1407050805980003', 'JAKA MUA\'RIF', 'Pria', 4, '2025', 'Aktif', '082353264177', 'jaka.muarif_mpd25@nusaputra.ac.id', 'Perumahan Griya Green Hill, Blok D5, No.34, Babunnggali, Cibadak, Sukabumi, Jawa Barat. Kode Pos 4333', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'SARIPAH', 'Islam', 'Kelas B', NULL, 'ACEH', '1998-05-08'),
(41, '20250140026', '3202284800200004', 'RESTU ALIFA ZAHRA', 'Wanita', 4, '2025', 'Aktif', '089517062887', 'restu.alifa_mpd25@nusaputra.ac.id', 'Jl.Catingin Cikaluku RT 07 RW 02 Desa Citatih Kecamatan Cikembar, Kabupaten Sukabumi, Jawa Barat', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'JUJU JUARIAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '2002-06-08'),
(42, '20250140027', '3202115712890002', 'SAWAAN LISA ILINA', 'Wanita', 4, '2025', 'Aktif', '081572727127', 'sawaan.lisa_mpd25@nusaputra.ac.id', 'PERUM TAMAN BOLO JAYUH JL. TENTUJAYA BLOK A NO 10', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:11:11', 'Fith Yanti', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1988-12-17'),
(43, '20250140028', '3202431008870001', 'ANGGY MAULANA JOHN WINARA', 'Pria', 4, '2025', 'Aktif', '085759871498', 'anggy.maulana_mpd25@nusaputra.ac.id', 'Kp. Bobojaya Rt 005 Rw.002 Desa Cibolang Kecamatan Cibolang Kabupaten Sukabumi Provinsi Jawa Barat Kode Pos 43184', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'ONIN', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1987-08-12'),
(44, '20250140029', '3202115811880004', 'NURHASANAH', 'Wanita', 4, '2025', 'Aktif', '083109813293', 'nurhasanah_mpd25@nusaputra.ac.id', 'Kp. Bamianunag rkan ton RT 05/07 Desa Sekarwangi Kec. Cibadak Kab. Sukabumi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'ALIYAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1988-11-28'),
(45, '20250140030', '3202294803900008', 'ENENG NURAFIFAH FAUZIYAH', 'Wanita', 4, '2025', 'Aktif', '6285798408265', 'eneng.nurafifah_mpd25@nusaputra.ac.id', 'Selajambit Rt 21/08', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'IMAS SUMRAT', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1990-03-08'),
(46, '20250140031', '3202371606880001', 'AHMAD SIDIK PERMANA', 'Pria', 4, '2025', 'Aktif', '085723011190', 'ahmad.sidik_mpd25@nusaputra.ac.id', 'Kp. Sukajaya Rt 001 RW 001 Desa Sukajaya Kec. Sukabumi Kec. Pelabuhan Ratu', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:11:11', 'ANI MARYANI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1988-05-16'),
(47, '20250140032', '3272705009840021', 'ANIS ANISSA NUR', 'Wanita', 4, '2025', 'Aktif', '085793981321', 'anis.anissa_mpd25@nusaputra.ac.id', 'Jl. CIBUNTU CIMUANG RW RT003/RW001 KEL.SINDANGPALAY KEC CIBEUREUM', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 14:52:50', 'SOLIHAT', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1984-10-10'),
(48, '20240140001', '3202011102960010', 'ENCEP ABDULLAH ALMUKAROM', 'Pria', 4, '2024', 'Aktif', '08157028106', 'encepdabdullahalmukarom@gmail.com', 'KP. BABAKAN PESANTREN', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'BAHTIAH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1996-02-11'),
(49, '20240140002', '3204140401810006', 'IIS ISNAWATI', 'Wanita', 4, '2024', 'Aktif', '6291281055162', 'iis.isnawati@nusaputra.ac.id', 'Blh pantai ratu Indah blok l no 5/6', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'HJ.', 'Islam', 'Kelas A', NULL, 'LEBAK', '1981-04-04'),
(50, '20240140003', '3202019020930006', 'SITI RAHMAWATI', 'Wanita', 4, '2024', 'Aktif', '085962003974', 'siti.rahmawati@nusaputra.ac.id', 'Kp. Babakan sirna Rt. 003/004 Desa Sukamerang', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'IBAH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1993-01-16'),
(51, '20240140004', '3202113008610008', 'HILLMAN ARIF', 'Pria', 4, '2024', 'Aktif', '085357794204', 'hillman.arif@nusaputra.ac.id', 'RO2 RW. 004 DESA TENJOLAYA KECAMATAN CIBADAK', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'IOP SUPRIYANTI', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1981-07-30'),
(52, '20240140005', '3202010907700005', 'MAMAT RUSLAN', 'Pria', 4, '2024', 'Aktif', '085798161785', 'mamat.ruslan@nusaputra.ac.id', 'KP. CIENRIK RT 001 RW 005 DESA KARANGPAPAK KECAMATAN CISOLOK KABUPATEN SUKABUMI PROVINSI JAWA BARAT', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'ONY1', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1970-07-10'),
(53, '20240140006', '3202118607770001', 'YENYEN HENDRAYANI', 'Wanita', 4, '2024', 'Aktif', '085173316056', 'yenyen.hendrayani_mpd24@nusaputra.ac.id', 'KP. SUKAMAJU', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'TITIN KARTINAH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1977-06-28'),
(54, '20240140007', '3202165102900002', 'SITI FATIMAH', 'Wanita', 4, '2024', 'Aktif', '085872144452', 'siti.fatimah_mpd24@nusaputra.ac.id', 'Jl. Cikultu Caringin Km. 04', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'NENGSH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1990-01-29'),
(55, '20240140008', '3202310104960009', 'ARMAN MAULANA', 'Pria', 4, '2024', 'Aktif', '085863410331', 'am12111416@gmail.com', '', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'AISYAH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1996-04-01'),
(56, '20240140009', '3202299601000004', 'AULIA RAHMA', 'Wanita', 4, '2024', 'Aktif', '085794419640', 'nctxenxexol2@gmail.com', 'Kp. Cisade 2, RT 08 RW 02 Desa Cijalingan, Kec. Cicantayan, Kab. Sukabumi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'ETI SUMIATI', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '2000-01-29'),
(57, '20240140010', '3202090706860005', 'SEPTIANA PUTRI JUARIYAH', 'Wanita', 4, '2024', 'Aktif', '085797668281', 'septianaputrijuariyah2@gmail.com', 'KP. WARUNG KIARA RT 002 RW 001 DESA WARUNG KIARA KECAMATAN WARUNG KIARA KABUPATEN SUKABUMI JAWA BARAT', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'YATI HAYATI', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1986-09-17'),
(58, '20240140011', '3202100886000002', 'ADI FITRIYADI', 'Pria', 4, '2024', 'Aktif', '085650017887', 'adi.fitriyadi_mpd24@nusaputra.ac.id', 'KP CIARI', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'DEDEH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1986-08-10'),
(59, '20240140012', '3202486040820002', 'DEASY FATMASARI', 'Wanita', 4, '2024', 'Aktif', '085660055078', 'daffaabisali@gmail.com', 'Kp. Sindangsari RT/RW 002/005, Desa Bantarjukung, Kec. Bantarjukung, Kabupaten Sukabumi Jawa Barat', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'NIYU FATIMAH', 'Islam', '', NULL, 'KABUPATEN SUKABUMI', '1982-04-26'),
(60, '20240140013', '3202053008750003', 'TEGUH AL-HADI', 'Pria', 4, '2024', 'Aktif', '085720286662', 'teguh.al-hadi_mpd24@nusaputra.ac.id', 'Jln. Sukalerus Rt 12 Rw 04 Des Sukalerus Kecamatan Parakansalak Kabupaten Sukabumi Provinsi Jawa Barat', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'CHOLILAH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1975-07-13'),
(61, '20240140014', '3202114507840002', 'AI MULYANI', 'Wanita', 4, '2024', 'Aktif', '085754141657', 'ai.mulyani@nusaputra.ac.id', 'Kp. Cikaleka Rt 03/07 Desa Sukarasa Kecamatan Cisarua, Kabupaten Sukabumi Jawa Barat', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'YATI', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1984-07-05'),
(62, '20240140015', '3202016108690008', 'NANI SUPARTINI', 'Wanita', 4, '2024', 'Aktif', '08154694716', 'nani.supartini@nusaputra.ac.id', 'Blok E.3 No 3', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'SUNAESH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1969-01-21'),
(63, '20240140016', '3202328019850002', 'DIMAS CAESAR ALKAUTSAR', 'Pria', 4, '2024', 'Aktif', '08225899351400', 'dimas.caesar_mpd24@nusaputra.ac.id', 'No. 49 Desa Citarik Kecamatan Sukabumi Kab Sukabumi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'AI JULAZHA', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1985-10-26'),
(64, '20240140017', '3202810508030001', 'NIA NURNIATI', 'Wanita', 4, '2024', 'Aktif', '085282200097', 'nia.nurniati_mpd24@nusaputra.ac.id', 'Kp. Ciawi rt 17 rw 08', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'IPAH', 'Islam', 'Kelas A', NULL, 'JAKARTA', '1983-03-31'),
(65, '20240140018', '3202746081810004', 'NIA MEYLANI', 'Wanita', 4, '2024', 'Aktif', '085698419676', 'nia.meylani_mpd24@nusaputra.ac.id', 'Perum mahri resident blok D no 5', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'NANI SUHAENI', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1981-07-08'),
(66, '20240140019', '3202448118030004', 'HENI SURYANI', 'Wanita', 4, '2024', 'Aktif', '085794657710', 'heni.suryani_mpd24@nusaputra.ac.id', 'Kp. Rancaluring RT/RW 013/005 Desa Padawarsa Kec. Cicantayan Kab. Sukabumi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'ODAH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1983-11-08'),
(67, '20240140020', '3202425504900001', 'ROSA YULISTIANI', 'Wanita', 4, '2024', 'Aktif', '085963026293', 'rosa.yulistiani_mpd24@nusaputra.ac.id', 'Kp. Cisari, Rt 02/2 Rw 04 Desa Padawarsa, Kec. Cicantayan', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'ROHANAH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1990-04-15'),
(68, '20240140021', '3202287119890005', 'NADIA YULISMA', 'Wanita', 4, '2024', 'Aktif', '085600626047', 'nadia.yulisma_mpd24@nusaputra.ac.id', 'kp. cikohop RT.444', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'ELIS HERAWATI', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1989-08-31'),
(69, '20240140022', '3202296418860008', 'HETI YULIATI', 'Wanita', 4, '2024', 'Aktif', '081573249898', 'heti.yuliati_mpd24@nusaputra.ac.id', 'KP. Cimenrang', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'YAYAT', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1986-12-24'),
(70, '20240140023', '3272075707770001', 'NENDEN HENDARSIH', 'Wanita', 4, '2024', 'Aktif', '085946076517', 'nenden.hendarsih@nusaputra.ac.id', 'SINDANGPALAY', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'ENING', 'Islam', 'Kelas A', NULL, 'GARUT', '1977-07-17'),
(71, '20240140024', '3202412509820004', 'ABDULOH RIDWAN', 'Pria', 4, '2024', 'Aktif', '085795070568', 'abduloh.ridwan_mpd24@nusaputra.ac.id', 'Kp. CISOLOK DESA CISOLOK KECAMATAN CISOLOK KABUPATEN SUKABUMI', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'NONOH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1982-05-25'),
(72, '20240140025', '3202036008860008', 'MARWAN GUNAWAN', 'Pria', 4, '2024', 'Aktif', '085644960925', 'marwan.gunawan_mpd24@nusaputra.ac.id', 'Kp Palbuaran Rt.04 Rw.01 Ds.Cibilang Kec.Gunung Guruh', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'CUCU NURCAHAYA', 'Islam', '', NULL, 'SUKABUMI', '1986-08-06'),
(73, '20240140026', '3202905512790003', 'TUTI HERNAWATI', 'Wanita', 4, '2024', 'Aktif', '085609416112', 'tuti.hernawati_mpd24@nusaputra.ac.id', '', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'ITOH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1979-12-25'),
(74, '20240140027', '3202300601730000', 'HAPIPAH', 'Wanita', 4, '2024', 'Aktif', '08132816997', 'hapipah_mpd24@nusaputra.ac.id', '', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'HJ. KULRIJIN', 'Islam', '', NULL, 'JAKARTA', '1973-01-19'),
(75, '20240140028', '3202304205710009', 'YUYU YULIAWATI', 'Wanita', 4, '2024', 'Aktif', '081385502041', 'yuyu.yuliawati_mpd24@nusaputra.ac.id', '', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'SOLHAT', 'Islam', '', NULL, 'SUKABUMI', '1971-05-02'),
(76, '20240140029', '3202476507860001', 'SUSI HERAYATI', 'Wanita', 4, '2024', 'Aktif', '085720667456', 'susi.herayati_mpd24@nusaputra.ac.id', 'KP CIGACOG', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'HOLIS', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1986-07-25'),
(77, '20240140030', '3202280900820001', 'VIA ULFAH', 'Wanita', 4, '2024', 'Aktif', '085720953224', 'viaulfah.art@gmail.com', 'KP KEMANG', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'ELA NUR LAELA', 'Islam', '', NULL, 'SUKABUMI', '1982-09-22'),
(78, '20240140031', '3202131500750003', 'YAMAN', 'Pria', 4, '2024', 'Aktif', '085720809600', 'yaman_mpd24@nusaputra.ac.id', '', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'tolety rakyah', 'Islam', '', NULL, 'SUKABUMI', '1975-05-15'),
(79, '20240140032', '3202312100900002', 'ILHAM SEPTIAN MAULANA', 'Pria', 4, '2024', 'Aktif', '089657669869', 'ilham.septian@nusaputra.ac.id', 'KP BABAKAN PEUNDEY', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'DITI PATNI', 'Islam', 'Kelas A', NULL, 'BOGOR', '1990-09-03'),
(80, '20240140033', '3272025103890002', 'DESTI MAHARDIKAWATI', 'Wanita', 4, '2024', 'Aktif', '085720448618', 'desti.mahardikawati@nusaputra.ac.id', 'Jl. Nusabanten/sirna Rt.05 rw.06 Kelurahaan Cibeber Kota Sukabumi', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'EUIS SITI ROGATYAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1989-03-11'),
(81, '20240140034', '3202474410900001', 'SITI WAHYUNINGSIH', 'Wanita', 4, '2024', 'Aktif', '085724701872', 'stway1990@gmail.com', 'KP. PASIR ANGIN RT 007/002 DESA CIMAHI KECAMATAN CIKIDANG KABUPATEN SUKABUMI', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'SOPIAH', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1990-04-04'),
(82, '20240140035', '3202290451840005', 'ATI SUSILAWATI', 'Wanita', 4, '2024', 'Aktif', '085723213116', 'ati.susilawati@nusaputra.ac.id', 'Kp. Salajampit Rt 10 RW 04', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'ITA', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1984-01-06'),
(83, '20240140036', '3202160309900001', 'SYIPA RODIATUL ZANNAH', 'Wanita', 4, '2024', 'Aktif', '085919683733', 'syiparzanna@gmail.com', 'KP. PANDUMAN GANG AMP1 1 RT 009/012 NO. 46 PADASARI CIBINONG, BOGOR', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'DEDEH YUNINGSH', 'Islam', '', NULL, 'BOGOR', '1990-08-22'),
(84, '20240140037', '3202114707790019', 'LIA YULIANTI', 'Wanita', 4, '2024', 'Aktif', '085720327565', 'liayulianti309@gmail.com', 'Jl.Siliwangi no.21', NULL, NULL, NULL, '2026-07-18 09:32:20', '2026-07-18 13:27:42', 'SITI JAMILAH', 'Islam', '', NULL, 'CIANJUR', '1979-07-07'),
(85, '20260150001', '6473022606940008', 'IRWAN GUNAWAN', 'Pria', 2, '2026', 'Aktif', '081347475837', 'irwan.gunawan_mm26@nusaputra.ac.id', 'JL. PADAT KARYART.001 RW.001', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'SRIANA', 'Islam', '', NULL, 'TARAKAN', '1994-06-26'),
(86, '20260150002', '3202431505960001', 'HIDAYATULLOH', 'Pria', 2, '2026', 'Aktif', '085524913815', 'hidayatulloh_mm26@nusaputra.ac.id', 'KP. Cimahi Rt 08/03 Desa Cimahi, Kecamatan Cidolog, Kabupaten Sukabumi', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'HIHIM', 'Islam', '', NULL, 'SUKABUMI', '1996-05-15'),
(87, '20260150003', '3272043009930001', 'SEPTO SUHARYANTO', 'Pria', 2, '2026', 'Aktif', '081281082980', 'septo.suharyanto_mm26@nusaputra.ac.id', 'Jl. Babakan sirna no 7', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'YANTI HERAWATI', 'Islam', '', NULL, 'SUKABUMI', '1993-09-30'),
(88, '20240150001', '3202232009010002', 'TAUFIK NURHADI', 'Pria', 2, '2024', 'Aktif', '085797986909', 'taufik.nurhadi@nusaputra.ac.id', 'Kp. Pasir Kadu, 009/003, Sukaluyu, Kalibunder, Sukabumi Jawa Barat', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'HERTI', 'Islam', '', NULL, 'SUKABUMI', '2001-06-22'),
(89, '20240150002', '3272076108010001', 'ROZAN VINA RAHMANI', 'Wanita', 2, '2024', 'Aktif', '081525777030', 'rozan.vina@nusaputra.ac.id', 'JL.METERAI NO.6', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'GINA PURNAMA INSANY', 'Islam', '', NULL, 'BANDUNG', '2001-08-21'),
(90, '20240150003', '3203016709960011', 'YOSSY ROSALINDA', 'Wanita', 2, '2024', 'Aktif', '082115164215', 'yossyrosalinda11@gmail.com', 'PESONA PAMOYANAN', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'AI NUR\'AINI', 'Islam', '', NULL, 'CIANJUR', '1996-09-27'),
(91, '20240150004', '3272071010760001', 'HERI FIRMANSYAH', 'Pria', 2, '2024', 'Aktif', '0885861158955', 'firmansyahheri5@gmail.com', '', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'DEDE H', 'Islam', '', NULL, 'BANDUNG', '1976-10-10'),
(92, '20240150005', '3202046301970002', 'DEDE HERMAWAN', 'Pria', 2, '2024', 'Aktif', '085720519538', 'dedehermawan1201@gmail.com', 'Kp. Cijambe', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'IROH', 'Islam', '', NULL, 'SUKABUMI', '1997-01-12'),
(93, '20240150006', '3202296009750002', 'IMELDA DELFINA SIBARANI', 'Wanita', 2, '2024', 'Aktif', '628562277911', 'dmr.mitraraya@gmail.com', 'KP. CIBOLANG', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'NURLIANA DORA B', 'Islam', '', NULL, 'PEMATANG SIANTAR', '1975-09-20'),
(94, '20240150007', '3202290812640003', 'AJIZ', 'Pria', 2, '2024', 'Aktif', '62816735031', 'HM.Ajiz63@gmail.com', 'KP. DANGDEUR NO 17', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'HJ. ATIH', 'Islam', '', NULL, 'SUKABUMI', '1963-12-08'),
(95, '20240150008', '3272044501730002', 'LELY FAUZIAH', 'Wanita', 2, '2024', 'Aktif', '08569993960', 'fauziahlely73@gmail.com', 'JL LETTU BAKRI TERUSAN', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'HJ. IMAS AISAH', 'Islam', '', NULL, 'SUKABUMI', '1973-01-05'),
(96, '20240150009', '3202062211880001', 'RAMDAN RUSTARMONO', 'Pria', 2, '2024', 'Aktif', '6285722771563', 'ramdan.rustarmono@nusaputra.ac.id', 'KP. CITATAH', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'RUSMIATI', 'Islam', '', NULL, 'SUKABUMI', '1988-11-22'),
(97, '20240150010', '3671031808790007', 'R. MOCH. AGUS RAMDHAN', 'Pria', 2, '2024', 'Aktif', '08111549669', 'moch.agus_mm24@nusaputra.ac.id', 'JL PEMANDIAN CIGUNUNG NO 217', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'HJ. LILIS KURAESIN', 'Islam', '', NULL, 'SUKABUMI', '1979-08-18'),
(98, '20240150011', '3202291505680007', 'TOTONG SUPARMAN', 'Pria', 2, '2024', 'Aktif', '6281911825256', 'tos.artos80@gmail.com', 'Jl. Raya Rambay', NULL, NULL, NULL, '2026-07-18 09:43:31', '2026-07-18 13:14:26', 'alm. E HASANAH', 'Islam', '', NULL, 'TASIKMALAYA', '1968-05-15'),
(99, '20230130024', NULL, 'Mouhameden El Bousseiry', 'Pria', 1, '2023', 'Aktif', '22238020405', 'medboss101@gmail.com', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:49:23', 'VATIMETOU YACOUB', 'Islam', 'Kelas I', NULL, 'MAURITANIENNE', '1997-06-01'),
(100, '20230130025', '3272044310890001', 'RIRIN OKTAVIANIVA', 'Wanita', 1, '2023', 'Aktif', '081283266246', 'ririn.oktavianiva@nusaputra.ac.id', 'Jl jend sudirman no 70 sukabumi', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:49:23', 'AGUSNIARTI', 'Islam', 'Kelas I', NULL, 'SUKABUMI', '1989-10-03'),
(101, '20230130026', '3202382105870002', 'ADE RAMDANI', 'Pria', 1, '2023', 'Aktif', '085793717614', 'ramdanieade@nusaputra.ac.id', 'Kp. Cibaregbog Rt. 002 Rw. 002', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:49:23', 'SITI NURJAMILAH', 'Islam', 'Kelas I', NULL, 'KAB. SUKABUMI', '1987-05-21'),
(102, '20230130027', '3202380607950004', 'UJANG RUKMAN', 'Pria', 1, '2023', 'Aktif', '085281833566', 'ujangrukmann@gmail.com', 'KP. MIRAMONTANA RT 007 RW 002 DESA PURABAYA KEC. PURABAYA KAB. SUKABUMI PROV. JAWA BARAT', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:49:23', 'IIS', 'Islam', 'Kelas I', NULL, 'SUKABUMI', '1995-07-06'),
(103, '20230130028', NULL, 'HADI AHMED ABDULLAH AL MOHAB', 'Pria', 1, '2023', 'Aktif', '085281645766', 'hadi.almohab@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:49:23', 'MULLUK GHASEM SHALEH AL MOHAB', 'Islam', 'Kelas I', NULL, 'HAJAH - YEM', '1998-01-02'),
(104, '20230130029', '3202132608980003', 'YOGA VIKRIANSYAH W', 'Pria', 1, '2023', 'Aktif', '085863607745', 'yogavwijaya@gmail.com', 'Parungkuda, sundawenang', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:49:23', 'IDA FARIDA', 'Islam', 'Kelas I', NULL, 'SUKABUMI', '1998-08-26'),
(105, '20230130031', '3202310103910011', 'MUCHTAR JAMY F', 'Pria', 1, '2023', 'Aktif', '085624293507', 'muchtar.jamy_mif@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:49:23', 'YATI SUGIYATI', 'Islam', 'Kelas I', NULL, 'SUMEDANG', '1991-03-01'),
(106, '20230130032', NULL, 'HANEEN A A ABUSHAMMALA', 'Wanita', 1, '2023', 'Aktif', '00970599848729', 'haneen.a@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:49:23', 'Suad', 'Islam', 'Kelas I', NULL, 'JABALIA', '1997-12-31'),
(107, '20240130001', '3174040704540006', 'Bekto Suprapto', 'Pria', 1, '2024', 'Aktif', '085697087746', 'bekto.suprapto@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'Sugiran', 'Kristen', 'Kelas A', NULL, 'YOGYAKARTA', '1954-04-07'),
(108, '20240130002', '3275100803850025', 'I Made Redi Hartana', 'Pria', 1, '2024', 'Aktif', '081295582006', 'made.redi@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'Lusiyanti', 'Islam', 'Kelas A', NULL, 'INDRAMAYU', '1985-03-18'),
(109, '20240130003', '3374092509750003', 'Antonius Endro Prabowo', 'Pria', 1, '2024', 'Aktif', '085713314242', 'antonius.endro@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'Kustiwati Rahaju', 'Katolik', 'Kelas A', NULL, 'KABUPATEN SEMARANG', '1975-09-25'),
(110, '20240130005', '3200117020500003', 'Gun Gun Febrianza', 'Pria', 1, '2024', 'Aktif', '081313190101', 'gungun.febrianza@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'Rumyati', 'Islam', 'Kelas A', NULL, 'BANDUNG', '1992-06-24'),
(111, '20240130006', '3203042104900001', 'Mulyana Yusuf', 'Pria', 1, '2024', 'Aktif', '085710867033', 'mulyana.yusuf@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'Titin Pirhatin', 'Islam', 'Kelas A', NULL, 'CIANJUR', '1990-04-21'),
(112, '20240130007', '7171040506020001', 'Roger Wagiu', 'Pria', 1, '2024', 'Aktif', '087709106468', 'roger.wagiu@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'Evelien Manseke', 'Kristen', 'Kelas A', NULL, 'MANADO', '2002-06-05'),
(113, '20240130008', '7702185812020001', 'Defttry Praise Utomo', 'Wanita', 1, '2024', 'Aktif', '0895377346062', 'defttry.praise@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'Devah Athiva Gerungan', 'Kristen', 'Kelas A', NULL, 'MANADO', '2002-12-18'),
(114, '20240130009', '3517011308750008', 'Andi Hermawan Abdillah', 'Pria', 1, '2024', 'Aktif', '081237078213', 'andi.hermawan@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'DESI MARSUKITA', 'Islam', 'Kelas A', NULL, 'JOMBANG', '1975-08-13'),
(115, '20240130010', '3202421701950001', 'Samsul Alamm', 'Pria', 1, '2024', 'Aktif', '085888223395', 'alam.steksa@gmail.com', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'Nurhayati', 'Islam', 'Kelas A', NULL, 'SUKABUMI', '1995-01-17'),
(116, '20240130011', '3174095005960002', 'Widya Purnama Dewi', 'Wanita', 1, '2024', 'Aktif', '081298737392', 'widya.purnama@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'Tite Pusda', 'Islam', 'Kelas A', NULL, 'BOGOR', '1996-05-25'),
(117, '20240130012', '3170210104930004', 'Muhammad Nurifki Filino', 'Pria', 1, '2024', 'Aktif', '082129579398', 'nurifki.filino@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'Sri Nurbeti Marhalim', 'Islam', 'Kelas A', NULL, 'JAKARTA', '1993-04-01'),
(118, '20240130013', '3174092209930005', 'Dhisa Septianto', 'Pria', 1, '2024', 'Aktif', '08196017710', 'dhisa.septianto@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'Tite Pusda', 'Islam', 'Kelas A', NULL, 'JAKARTA', '1993-09-22'),
(119, '20240130015', '1272025111020022', 'RIZA RUMAYANTI DEWI', 'Wanita', 1, '2024', 'Aktif', '08562359664', 'rizarumayantidewi@gmail.com', 'GG CIPELANG LEUTIK VII NO 181', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'LIANA SALDIA ASIH', 'Islam', 'Kelas I', NULL, 'GARUT', '1993-11-11'),
(120, '20240130016', '3202286100010002', 'RIKA AGISHA SITI NURAZIZAH', 'Wanita', 1, '2024', 'Aktif', '083891250749', 'rika.agisha@nusaputra.ac.id', 'Perumahan Griya Lestari Cipalingan Blok A No 7', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'ANI NURHAYATI', 'Islam', 'Kelas I', NULL, 'SUKABUMI', '2001-08-21'),
(121, '20240130017', '3202421701950001', 'SAMSUL ALAM', 'Pria', 1, '2024', 'Aktif', '085888223395', 'samsul.alam@nusaputra.ac.id', 'KP. PASANGRAHAN', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'NURHAYATI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1995-01-17'),
(122, '20240130018', '3213156003990002', 'REHAENI ATIPAH', 'Wanita', 1, '2024', 'Aktif', '085759133462', 'rehaeni.atipah@nusaputra.ac.id', 'Perum Sara Regency Blok F 151', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'ANI MARNI', 'Islam', 'Kelas B', NULL, 'SUBANG', '1999-03-25'),
(123, '20240130019', '3872011202870042', 'FERRY FEBIANSAH', 'Pria', 1, '2024', 'Aktif', '085872129448', 'ferry.febiansah_cs24@nusaputra.ac.id', 'KP TALLIN RT 004/003 DESA GUNUNG BRENTANG KABUPATEN SUKABUMI', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'SUMARNI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1987-02-12'),
(124, '20240130020', '3202117005000007', 'SITI NADIYA FAUZANI', 'Wanita', 1, '2024', 'Aktif', '085721571390', 'siti.nadiya@nusaputra.ac.id', 'Jl. Karangtenggah No 891', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'TITIN SUTINAH', 'Islam', 'Kelas B', NULL, 'KABUPATEN SUKABUMI', '2000-05-30'),
(125, '20240130021', '3202286701950004', 'NUR ELAH', 'Wanita', 1, '2024', 'Aktif', '085759842831', 'nur.elah@nusaputra.ac.id', 'KP KAUM KALER', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'IMAS', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1995-01-27'),
(126, '20240130022', '3202293311930007', 'AHDI NAUFAL HAMDI', 'Pria', 1, '2024', 'Aktif', '08562044620', 'ahdi.naufal_mif24@nusaputra.ac.id', 'Kp. Tipar KT/RW 047/010 Desa Cibolang Kaler Kec. Cisaat Kab. Sukabumi', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'ANIS HILMIYAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1993-11-23'),
(127, '20240130023', '3272012502910001', 'RIKI MULYANA', 'Pria', 1, '2024', 'Aktif', '08560888824', 'riki.mulyana_mif24@nusaputra.ac.id', 'Kp. Tegalega', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'YUYUN RUSMIANTI', 'Islam', 'Kelas I', NULL, 'SUKABUMI', '1991-02-22'),
(128, '20240130024', '3202328291960002', 'MUHAMAD HAVIDZ ALKAUSAR', 'Pria', 1, '2024', 'Aktif', '081383963163', 'muhamad.havidz_mif24@nusaputra.ac.id', 'Kp. Bojong Tengah', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'IMAS MASITOH', 'Islam', 'Kelas B', NULL, 'KABUPATEN SUKABUMI', '1996-10-29'),
(129, '20240130025', '3202141601890002', 'TAUFIK ALFIKAR', 'Pria', 1, '2024', 'Aktif', '085720835080', 'taufik.alfikar_mif24@nusaputra.ac.id', 'KP. PAMATUTAN RT 23 RW 09, DESA BOLONGONGENTENG KECAMATAN BOLONGONGENTENG, KABUPATEN SUKABUMI, JAWA BARAT 43503', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'SITI JUBAEDAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1989-01-15'),
(130, '20240130026', '3272022005000043', 'ARIS MAULANA', 'Pria', 1, '2024', 'Aktif', '089523913849', 'maulanaaris831@gmail.com', 'JL. RA KOSASIH GG ADOCA', NULL, NULL, NULL, '2026-07-18 12:56:28', '2026-07-18 14:47:10', 'IIS MELIHANDAYANI', 'Islam', 'Kelas I', NULL, 'SUKABUMI', '2000-05-20'),
(131, '20240130027', '3202071501650003', 'YUSUF SUPRIYANTO', 'Pria', 1, '2024', 'Aktif', '08567890135', 'yusufsupriyanto@sukabumikab.go.id', 'KP CIGANGGENG KEL.', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'Ibu', 'Islam', 'Kelas I', NULL, 'SUKABUMI', '1965-01-15'),
(132, '20240130028', '3202251206900003', 'DZAKY IBNU RUSYD', 'Pria', 1, '2024', 'Aktif', '085777511702', 'dzaky.rusyd22@gmail.com', 'Perum Villa Adprima Blok F3 No 7 Jl.Kesna', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'ENTIN KARTINI', 'Islam', 'Kelas B', NULL, 'YOGYAKARTA', '1990-09-12'),
(133, '20240130029', '3202296708900008', 'SUNOKO', 'Pria', 1, '2024', 'Aktif', '081911876296', 'sunoko.11121267@gmail.com', 'KP TALIN PEUNTAS', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'SUNIPAH', 'Islam', 'Kelas B', NULL, 'BLORA', '1990-07-07'),
(134, '20240130030', '3202260508860002', 'ERIP SURATNO', 'Pria', 1, '2024', 'Aktif', '081282984433', 'eripsuratno@gmail.com', 'KP SIDOMUKTI', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'JUANSIH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1985-08-03'),
(135, '20240130031', '3202010706940021', 'FAHMY RODIBILLAH', 'Pria', 1, '2024', 'Aktif', '08562141792', 'fferob@gmail.com', 'KP BAKANJATI', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'EUIS MARDIYAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1994-07-07'),
(136, '20240130032', '3202393804900003', 'ARGI GINANJAR', 'Pria', 1, '2024', 'Aktif', '6285863128883', 'argi.ginanjar@gmail.com', 'KP. JATI', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'IDA SUHAETI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1990-04-20'),
(137, '20240130033', '3273061710760001', 'CEPI SURYADI MARWAN', 'Pria', 1, '2024', 'Aktif', '081564655569', 'csmlike@gmail.com', 'JERUK NYELAP', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'BADRIAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1976-10-17'),
(138, '20240130034', NULL, 'PREETAM KUMAR', 'Pria', 1, '2024', 'Aktif', '923472078690', 'preetam.kthv4@gmail.com', NULL, NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'LACHHMAN', 'Hindu', 'Kelas I', NULL, 'THARPARKAR', '1986-02-03'),
(139, '20240130035', NULL, 'DULE ABERA', 'Pria', 1, '2024', 'Aktif', '251923568165', 'duleabera06@gmail.com', NULL, NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'ADANECH BEFERA', 'Kristen', 'Kelas I', NULL, 'WOLISO', '1999-11-22'),
(140, '20240130036', NULL, 'SISSOKO MAKAN', 'Pria', 1, '2024', 'Aktif', '22392714168', 'makansissoko156@gmail.com', NULL, NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'Assatou Traore', 'Islam', 'Kelas I', NULL, 'BAMAKO', '1969-04-07'),
(141, '20250130001', '3203160203030004', 'ASEP SURAHMAN SULAEMAN', 'Pria', 1, '2025', 'Aktif', '085695838977', 'asep.surahman@nusaputra.ac.id', 'Kp.Babakan Kondang, Desa Hegarmanah, Kecamatan Takokak, Kabupaten Cianjur Jawa Barat', 'Quaerat blanditiis s', NULL, NULL, '2026-07-18 12:56:29', '2026-07-24 02:11:28', 'AMAH', 'Islam', 'Kelas B', NULL, 'CIANJUR', '2003-03-01'),
(142, '20250130002', '3272032012860042', 'ANGGI TRIANA', 'Wanita', 1, '2025', 'Aktif', '085720505756', 'anggi.triana_mif25@nusaputra.ac.id', 'Jl. Kadubampit, Komplek Perumahan The Royal Slugungo Blok C8, Gunung Guruh, Kec. Cisaat', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'AI HERLINA S PD', 'Islam', '', NULL, 'KABUPATEN SUKABUMI', '1986-12-20'),
(143, '20250130006', '602343968', 'ABUBAKAR IBRAHIM MUHAMMAD', 'Pria', 1, '2025', 'Aktif', '6209038156236', 'kufaabubakar58@gmail.com', '', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', '', 'Islam', '', NULL, 'NIGERIA', '1996-08-04'),
(144, '20250130007', '3202291104000002', 'ARMAN SARI', 'Pria', 1, '2025', 'Aktif', '085724421146', 'arman.sari_mif25@nusaputra.ac.id', 'jl. jadi/pairan 2 kp. cendoy tongan ds. lukamanah rt 24 rs 08, cisaat kab. sukabumi', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'Partini Hunaeni', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '2000-04-14'),
(145, '20250130008', '3202232404000002', 'MUHAMMAD ABDUL AZIZ', 'Pria', 1, '2025', 'Aktif', '088809571974', 'muhammad.abdul_mif25@nusaputra.ac.id', 'Kp. sirna tengah rt 004, sukabumi', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'SOLINAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '2000-04-24'),
(146, '20250130009', '3272006201980001', 'HILMAN YUNUS', 'Pria', 1, '2025', 'Aktif', '087720840673', 'hilman.yunus_mif25@nusaputra.ac.id', 'Jl. anyelir/kawasana no 13', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'NURIATI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1998-01-02'),
(147, '20250130016', '3202029608050008', 'TASYA YUSTIRA AGUSTIAN', 'Wanita', 1, '2025', 'Aktif', '08561035618', 'tasya.yustira@nusaputra.ac.id', 'Kp. Cibolang Gg. Muliajim', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'TATI MARYATI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '2005-08-26'),
(148, '20250130017', '3272022412990001', 'DEAS AGHELLAR', 'Wanita', 1, '2025', 'Aktif', '08974158574', 'deas.aghellar@nusaputra.ac.id', 'Gg. Kaluori 3 No.6 Rt 006/005', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'ASTIANI', 'Islam', 'Kelas B', NULL, 'BEKASI', '1999-12-09'),
(149, '20250130018', '3202120403000002', 'DINDA TASYA MAHARDIKA', 'Wanita', 1, '2025', 'Aktif', '085157410032', 'dinda.tasya_mif25@nusaputra.ac.id', 'Kp.Pamunjulan RT009/007, desa Cibolang kota, Kp. Cibolang Kp.Cibolang', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'IKA RENKA', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '2000-03-04'),
(150, '20250130021', '3202250245590001', 'GUGUN NURYAKIN', 'Pria', 1, '2025', 'Aktif', '082169016716', 'gugun.nuryakin_cs25@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'IBU', 'Islam', '', NULL, 'SUKABUMI', '1989-08-24'),
(151, '20250130033', '3202321100904', 'SEPTI MUHAMMAD AGUSTIRA', 'Pria', 1, '2025', 'Aktif', '0895411395552', 'septi.muhammad_mm25@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'CUCU IM', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '2004-08-15'),
(152, '20250130035', '3202230711950001', 'RISWAN ZAENAL ARIPIN', 'Pria', 1, '2025', 'Aktif', '087834671918', 'riswan.zaenal@nusaputra.ac.id', 'VILLA ADPRIMA Jl. ADPRIMA CS NO.24', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'TETI SUFIATI', 'Islam', 'Kelas B', NULL, 'TANGERANG', '1995-11-07'),
(153, '20250130036', '3202250240620006', 'ASEP RIZKI FIRDAUS', 'Pria', 1, '2025', 'Aktif', '089648137995', 'muhammadrizkifirdaus111@gmail.com', 'Kp. Cibencu RT.001/008 Cisaat, Cisaat Kabupaten Sukabumi', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'ICEU SUMIATI', 'Islam', '', NULL, 'SUKABUMI', '2002-04-20'),
(154, '20250130038', '3276021710670004', 'PANUTAN SAKTI SULENDRAKUSUMA', 'Pria', 1, '2025', 'Aktif', '081319535927', 'panutan.sakti@nusaputra.ac.id', 'PURI SRIWEDARI BLOK O NO.9A', NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:47:10', 'SUHETTY', 'Islam', '', NULL, 'TASIKMALAYA', '1967-10-17'),
(157, '20260130003', '3202424910030001', 'SHANIKA LAYUNG MEDAL WANGI', 'Wanita', 1, '2026', 'Aktif', '08979853273', 'shanika.layung_mif26@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 12:56:29', '2026-07-18 14:49:23', 'CUCU KARNELIS, S.Pd., Gr', 'Islam', NULL, NULL, 'JAKARTA', '2003-10-09'),
(158, '20250150002', '3272074105690002', 'MEILINA SUKMARINI', 'Wanita', 2, '2025', 'Aktif', '085862689767', 'meilina.sukmarini@nusaputra.ac.id', 'Jl. Malabar No. 8 Puri Cibeureum Permai', NULL, NULL, NULL, '2026-07-18 13:23:42', '2026-07-18 13:23:42', 'HJ. CUCU SAMSIAH', 'Islam', 'Kelas B', NULL, 'KOTA SUKABUMI', '1969-05-01'),
(159, '20250150003', '3272040412890900', 'NENDA SUHANDA', 'Pria', 2, '2025', 'Aktif', '085797518000', 'nenda.suhanda@nusaputra.ac.id', 'Perum Panggon Mas Blok 32B', NULL, NULL, NULL, '2026-07-18 13:23:42', '2026-07-18 13:23:42', 'CICIH SUKAESIH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1989-12-04'),
(160, '20250150004', '3203035804950001', 'N. FITRIYAH NUR WAHIDAH', 'Wanita', 2, '2025', 'Aktif', '087725874518', 'n.fitriyah_mm25@nusaputra.ac.id', 'KP. BABAKAN GELAR', NULL, NULL, NULL, '2026-07-18 13:23:42', '2026-07-18 13:23:42', 'SADIYAH', 'Islam', 'Kelas B', NULL, 'CIANJUR', '1995-04-18'),
(161, '20250150005', '3175016009900001', 'SELVIYANI', 'Wanita', 2, '2025', 'Aktif', '085863020944', 'selviyani@nusaputra.ac.id', 'Jl. Siliwangi No. 61', NULL, NULL, NULL, '2026-07-18 13:23:42', '2026-07-18 13:23:42', 'AZUARNI', 'Islam', 'Kelas B', NULL, 'JAKARTA', '1990-09-20'),
(162, '20250150006', '3272024810970002', 'YANA PRIYANA', 'Pria', 2, '2025', 'Aktif', '085321514410', 'yana.priyana_mm25@nusaputra.ac.id', '', NULL, NULL, NULL, '2026-07-18 13:23:42', '2026-07-18 13:23:42', 'LILIS LISNIAWATI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1997-10-10'),
(163, '20250150007', '3202392202890001', 'JATNIKA EKA PATRA', 'Pria', 2, '2025', 'Aktif', '085794728473', 'jatnika.eka@nusaputra.ac.id', 'KP SETIA BAKTI RT 02/01 DESA KOMPA', NULL, NULL, NULL, '2026-07-18 13:23:42', '2026-07-18 13:23:42', 'HAYUN', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1989-02-22'),
(164, '20250150008', '3211204205780006', 'INTAN NURHAYATI', 'Wanita', 2, '2025', 'Aktif', '081546573793', 'intan.nurhayati_mm25@nusaputra.ac.id', 'PERUMAHAN MUTIARA BUMI METRO', NULL, NULL, NULL, '2026-07-18 13:23:42', '2026-07-18 13:23:42', 'Uka Rumyati', 'Islam', 'Kelas B', NULL, 'SUMEDANG', '1978-05-02'),
(165, '20250150009', '3272066909850001', 'SITI BADRIYAH', 'Wanita', 2, '2025', 'Aktif', '083890384239', 'siti.badriyah@nusaputra.ac.id', 'Jl. Proklamasi Kp. Cicadas Hilir RT 004 RW 009 Kel. Cikundul Kec. Lembursitu Kota Sukabumi', NULL, NULL, NULL, '2026-07-18 13:23:42', '2026-07-18 13:23:42', 'YAYAN NURYANAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1985-09-29'),
(166, '20250150010', '3674041907990002', 'MUHAMMAD ASAADULHAQ ASH SHIDIQ', 'Pria', 2, '2025', 'Aktif', '081807558950', 'muhammad.asaadulhaq@nusaputra.ac.id', 'Villa Gunung Lestari blok F1 no.2', NULL, NULL, NULL, '2026-07-18 13:23:42', '2026-07-18 13:23:42', 'ZETTI MARNI', 'Islam', 'Kelas B', NULL, 'JAKARTA', '1999-07-19'),
(167, '20250150011', '3276056904880005', 'FITRI AFRINA', 'Wanita', 2, '2025', 'Aktif', '081210191601', 'fitri.afrina@nusaputra.ac.id', 'Jln Sukamulya 1 No 101 Serua Indah Ciputat', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'TRI SARTIKA', 'Islam', 'Kelas B', NULL, 'JAKARTA', '1988-04-29'),
(168, '20250150012', '3272025002000901', 'ALYA SYIFA FADILLA', 'Wanita', 2, '2025', 'Aktif', '081311564678', 'alya.syifa_mm25@nusaputra.ac.id', 'Jalan Ciaul Kaler', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'YULIANTI', 'Islam', 'Kelas B', NULL, 'KOTA SUKABUMI', '2000-02-10'),
(169, '20250150013', '3205014912890002', 'NENG MEDINA', 'Wanita', 2, '2025', 'Aktif', '081563240240', 'neng.medina_mm25@nusaputra.ac.id', 'Jalan Selabintana KM. 3', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'SITI APOH DZALFAH', 'Islam', 'Kelas B', NULL, 'GARUT', '1989-12-09'),
(170, '20250150014', '3202125601900004', 'CITRA DWIYANTI RIDWAN', 'Wanita', 2, '2025', 'Aktif', '085603400813', 'citra.dwiyanti_mm25@nusaputra.ac.id', 'Kp cisadaria rt 05/02 ds.Cisarua kec nagrak kab sukabumi', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'YATI NURLAELA SARI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1990-01-16'),
(171, '20250150015', '3202411804970003', 'IRVAN AGUNG APRIANSYAH', 'Pria', 2, '2025', 'Aktif', '085656566562', 'irvan.agung_mm25@nusaputra.ac.id', 'KP.BOJONGHERANG', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'HJ.IIS SUMYATI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1997-04-18'),
(172, '20250150016', '3202280410920003', 'MUHAMMAD RIHAN RISKI FAHLEVI', 'Pria', 2, '2025', 'Aktif', '0816338854', 'muhammad.rihan_mm25@nusaputra.ac.id', 'Jalan cisande RT 06 RW 02 desa Cijalingan kec. Cicantayan Kab Sukabumi 43155', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'YUYU YUHANAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1992-10-04'),
(173, '20250150017', '3272041807880001', 'SOLEH GUNAWAN', 'Pria', 2, '2025', 'Aktif', '087819002147', 'soleh.gunawan_mm25@nusaputra.ac.id', 'Perumahan bumi marhamah blok p no 7', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'CICAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1988-07-18'),
(174, '20250150018', '3202331409930004', 'MUHAMMAD HAMDI', 'Pria', 2, '2025', 'Aktif', '085892082629', 'muhammad.hamdi@nusaputra.ac.id', 'PERUM CIKEMBANG PERMAI', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'TITIN FATIMAH', 'Islam', 'Kelas B', NULL, 'JAKARTA', '1993-09-14'),
(175, '20250150019', '3202101105840005', 'FERRY PRIANTONO', 'Pria', 2, '2025', 'Aktif', '085720897200', 'ferry.priantono_mm25@nusaputra.ac.id', '', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'Uus Yusyawati', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1984-05-11'),
(176, '20250150020', '3202294712010007', 'GINA SAHADATUN NISA', 'Wanita', 2, '2025', 'Aktif', '085871555317', 'gina.sahadatun_mm25@nusaputra.ac.id', 'KP. CISAAT', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'NENENG EKASARI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '2001-12-07');
INSERT INTO `mahasiswa` (`id`, `nim`, `nik`, `nama`, `jenis_kelamin`, `prodi_id`, `angkatan`, `status`, `no_hp`, `email`, `alamat`, `judul_tesis`, `dosen_pembimbing`, `catatan_admin`, `created_at`, `updated_at`, `nama_ibu`, `agama`, `kelas`, `konsentrasi`, `tempat_lahir`, `tanggal_lahir`) VALUES
(177, '20250150021', '3202277110830001', 'NANI NAFISAH', 'Wanita', 2, '2025', 'Aktif', '6287876183630', 'nani.nafisah_mm25@nusaputra.ac.id', 'PERUM MANGKALAYA', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'LILIS SUMIATI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1983-10-31'),
(178, '20250150022', '3202160603920001', 'ARGI RAMDHAN MUBARAK', 'Pria', 2, '2025', 'Aktif', '085719160160', 'argi.ramdhan_mm25@nusaputra.ac.id', 'Perum Griya Karang Asri Blok E.11, RT.006/008, Desa Ciheulang Tonggoh, Kecamatan Cibadak', NULL, NULL, NULL, '2026-07-18 13:23:43', '2026-07-18 13:23:43', 'NURLAILAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1992-03-06'),
(179, '20250150023', '3272066008930001', 'YULI SUSANTI', 'Wanita', 2, '2025', 'Aktif', '085780700272', 'yuli.susanti_mm25@nusaputra.ac.id', 'KADULAWANG RT002/RW001 KELURAHAN SITUMEKAR KECAMATAN LEMBURSITU KOTA SUKABUMI', NULL, NULL, NULL, '2026-07-18 13:23:44', '2026-07-18 13:23:44', 'HJ.ECIH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1993-08-20'),
(180, '20250150024', '3215270103010002', 'MUHAMMAD PAJRUL PALAH', 'Pria', 2, '2025', 'Aktif', '081222537671', 'muhammad.pajrul_mm25@nusaputra.ac.id', 'KP. BABAKAN CITAMIANG DESA SUKARATU KECAMATAN GEKBRONG KABUPATEN CIANJUR', NULL, NULL, NULL, '2026-07-18 13:23:44', '2026-07-18 13:23:44', 'ENDAH', 'Islam', 'Kelas B', NULL, 'KABUPATEN KARAWANG', '2001-03-01'),
(181, '20250150025', '3202311104930002', 'MUHAMMAD SALIM MACHFUD', 'Pria', 2, '2025', 'Aktif', '081310910231', 'muhammad.salim_mm@nusaputra.ac.id', 'Kp. Cibalung RT 002 RW 004 Desa Talaga Kecamatan Caringin Kabupaten Sukabumi', NULL, NULL, NULL, '2026-07-18 13:23:44', '2026-07-18 13:23:44', 'MISRIYAH', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1993-04-11'),
(182, '20250150026', '3202276607880001', 'LUSSY YULIASARI, S.E', 'Wanita', 2, '2025', 'Aktif', '085723923699', 'lussy.yuliasari_mm@nusaputra.ac.id', 'Kp Selaawi Rt 02/01 Des.Cisaat Kec.Cisaat Kab.Sukabumi', NULL, NULL, NULL, '2026-07-18 13:23:44', '2026-07-18 13:23:44', 'IIS ISMAYANTI', 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1988-07-26'),
(183, '20250150027', '3202160501750014', 'ATENG HAMDANI', 'Pria', 2, '2025', 'Aktif', '6281219107989', 'ateng.hamdani_mpd@nusaputra.ac.id', '', NULL, NULL, NULL, '2026-07-18 13:23:44', '2026-07-18 13:23:44', 'E MARPUAH', 'Islam', '', NULL, 'SUKABUMI', '1975-01-05'),
(184, '20230130023', NULL, 'MUPENZI NKUNDUWERA JULIEN', 'Pria', 1, '2023', 'Aktif', '250790027644', 'mupenzi.nkunduwera@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 13:57:16', '2026-07-18 14:49:23', 'GASENGAJIKE JULIENNE', 'Kristen', 'Kelas I', NULL, 'MASISI', '1997-04-01'),
(185, '20260130001', '3674056310950002', 'PUTRINDA CHOIRUNNISA', 'Wanita', 1, '2026', 'Aktif', '089633407352', 'putrinda.choirunnisa@nusaputra.ac.id', 'JL. KRAMAT NO. 47', NULL, NULL, NULL, '2026-07-18 13:57:16', '2026-07-18 14:49:23', 'MAYANTI', 'Islam', NULL, NULL, 'TANGERANG', '1995-10-23'),
(186, '20260130002', '3674044810970010', 'SITI FAZIUR RAHMAH', 'Wanita', 1, '2026', 'Aktif', '089501392136', 'siti.faziur@nusaputra.ac.id', 'JL. KH. DEWANTORO KP SAWAH', NULL, NULL, NULL, '2026-07-18 13:57:16', '2026-07-18 14:49:23', 'MURNI', 'Islam', NULL, NULL, 'TANGERANG', '1997-10-08'),
(187, '20250150001', NULL, 'AGRI HAMDANI APRIANA', 'Pria', 2, '2025', 'Aktif', '081282016824', 'agri@nusaputra.ac.id', NULL, NULL, NULL, NULL, '2026-07-18 15:07:30', '2026-07-18 15:07:30', NULL, 'Islam', 'Kelas B', NULL, 'SUKABUMI', '1990-04-15');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa_publikasi`
--

CREATE TABLE `mahasiswa_publikasi` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `dosen_id` int(11) DEFAULT NULL,
  `pendaftaran_sidang_id` int(11) DEFAULT NULL,
  `judul_artikel` varchar(255) NOT NULL,
  `dosen_pendamping` varchar(255) DEFAULT NULL,
  `rekan_penulis` varchar(255) DEFAULT NULL,
  `status_publikasi` varchar(50) NOT NULL,
  `link_artikel` text DEFAULT NULL,
  `doi` varchar(255) DEFAULT NULL,
  `abstrak` text DEFAULT NULL,
  `kata_kunci` varchar(500) DEFAULT NULL,
  `nama_jurnal` varchar(300) DEFAULT NULL,
  `volume` varchar(50) DEFAULT NULL,
  `nomor_terbit` varchar(50) DEFAULT NULL,
  `halaman` varchar(50) DEFAULT NULL,
  `tahun_terbit` year(4) DEFAULT NULL,
  `referensi` text DEFAULT NULL,
  `file_jurnal` varchar(255) DEFAULT NULL,
  `file_bukti_bayar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mahasiswa_publikasi`
--

INSERT INTO `mahasiswa_publikasi` (`id`, `mahasiswa_id`, `dosen_id`, `pendaftaran_sidang_id`, `judul_artikel`, `dosen_pendamping`, `rekan_penulis`, `status_publikasi`, `link_artikel`, `doi`, `abstrak`, `kata_kunci`, `nama_jurnal`, `volume`, `nomor_terbit`, `halaman`, `tahun_terbit`, `referensi`, `file_jurnal`, `file_bukti_bayar`, `created_at`, `updated_at`) VALUES
(1, 141, NULL, 3, 'Analisis Sentimen Menggunakan Pendekatan Deep Learning pada Ulasan Aplikasi Akademik', 'Dr. Ahmad Fauzi, M.Kom', 'Budi Santoso, Siti Aminah', 'Publish', 'https://doi.org/10.1234/csj.2025.01', '10.1234/csj.2025.vol11.no2.145', 'Penelitian ini mengkaji penerapan algoritma deep learning berbasis LSTM dan BERT untuk analisis sentimen pada ulasan pengguna aplikasi akademik perguruan tinggi. Dataset dikumpulkan dari Google Play Store sebanyak 12.500 ulasan. Hasil eksperimen menunjukkan bahwa model BERT mencapai akurasi 94.2% dalam mengklasifikasikan sentimen positif, negatif, dan netral, melampaui pendekatan machine learning tradisional seperti SVM dan Naive Bayes. Penelitian ini berkontribusi pada pengembangan sistem monitoring kepuasan mahasiswa berbasis kecerdasan buatan.', 'Deep Learning, Analisis Sentimen, BERT, LSTM, Aplikasi Akademik', 'Journal of Computer Science and Information Technology (JCSIT)', '11', '2', '145-162', '2025', '[1] Devlin, J., Chang, M. W., Lee, K., & Toutanova, K. (2019). BERT: Pre-training of Deep Bidirectional Transformers for Language Understanding. NAACL-HLT 2019.\n[2] Hochreiter, S., & Schmidhuber, J. (1997). Long Short-Term Memory. Neural Computation, 9(8), 1735û1780.\n[3] Pang, B., & Lee, L. (2008). Opinion Mining and Sentiment Analysis. Foundations and Trends in Information Retrieval, 2(1û2), 1û135.', NULL, NULL, '2026-07-05 12:40:43', '2026-07-24 06:32:40'),
(2, 141, NULL, 3, 'Pengembangan Arsitektur Microservices untuk Skalabilitas Sistem Informasi Pendidikan', 'Prof. Dr. Hendra Kusuma, M.Cs', 'Rina Marlina', 'ACC', 'https://jurnal.nusaputra.ac.id/index.php/jika/article/view/100', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-14 12:40:43', '2026-07-24 06:32:40'),
(3, 141, NULL, 3, 'Evaluasi Kinerja Algoritma Klasifikasi Machine Learning dalam Memprediksi Kelulusan Tepat Waktu', 'Dr. Dewi Lestari, M.T.', NULL, 'Sedang Review', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-17 12:40:43', '2026-07-24 06:32:40');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pesan` text NOT NULL,
  `jenis` enum('tugas','jadwal','surat','sistem') DEFAULT 'sistem',
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `user_id`, `pesan`, `jenis`, `is_read`, `link`, `created_at`) VALUES
(1, 1, 'Tugas \"Review Tesis Ahmad Rizki\" deadline besok!', 'tugas', 0, 'pages/tugas.php', '2026-06-10 15:26:44'),
(2, 1, 'Seminar Proposal Ahmad Rizki dijadwalkan 2 hari lagi', 'jadwal', 0, 'pages/jadwal.php', '2026-06-10 15:26:44'),
(3, 1, 'Surat 003/NPU/MIF/II/2025 menunggu tanda tangan', 'surat', 0, 'pages/surat.php', '2026-06-10 15:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran_sidang`
--

CREATE TABLE `pendaftaran_sidang` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `jenis_sidang` varchar(100) NOT NULL,
  `berkas_ok` int(11) DEFAULT 0,
  `berkas_total` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'Menunggu Review',
  `urgent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `angkatan` varchar(10) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `pembimbing1` varchar(150) DEFAULT NULL,
  `pembimbing2` varchar(150) DEFAULT NULL,
  `status_luaran` varchar(50) DEFAULT NULL,
  `link_luaran` text DEFAULT NULL,
  `judul_tesis` text DEFAULT NULL,
  `berkas_jurnal` varchar(255) DEFAULT NULL,
  `berkas_bukti_bayar_jurnal` varchar(255) DEFAULT NULL,
  `berkas_persetujuan` varchar(255) DEFAULT NULL,
  `berkas_khs` varchar(255) DEFAULT NULL,
  `berkas_bebas_perpus` varchar(255) DEFAULT NULL,
  `berkas_buku_sumbangan` varchar(255) DEFAULT NULL,
  `berkas_bebas_admin` varchar(255) DEFAULT NULL,
  `berkas_foto` varchar(255) DEFAULT NULL,
  `berkas_draft_tesis` varchar(255) DEFAULT NULL,
  `berkas_code_program` varchar(255) DEFAULT NULL,
  `berkas_presentasi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pendaftaran_sidang`
--

INSERT INTO `pendaftaran_sidang` (`id`, `mahasiswa_id`, `jenis_sidang`, `berkas_ok`, `berkas_total`, `status`, `urgent`, `created_at`, `updated_at`, `angkatan`, `email`, `no_hp`, `pembimbing1`, `pembimbing2`, `status_luaran`, `link_luaran`, `judul_tesis`, `berkas_jurnal`, `berkas_bukti_bayar_jurnal`, `berkas_persetujuan`, `berkas_khs`, `berkas_bebas_perpus`, `berkas_buku_sumbangan`, `berkas_bebas_admin`, `berkas_foto`, `berkas_draft_tesis`, `berkas_code_program`, `berkas_presentasi`) VALUES
(1, 12, 'Sidang Tesis', 11, 11, 'Menunggu Review', 0, '2026-07-18 10:23:04', '2026-07-24 03:38:57', 'Ut officia', 'pajuzanono@mailinator.com', 'Et anim in repudiand', 'Dr. Dyah Lyesmaya, S.S., M.Pd.', 'Assoc. Prof. Dr. Ayi Abdurahman, M.Pd., M.M.', 'Sedang Review', 'https://www.texon.org', 'Magni aut quasi quia', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1'),
(3, 141, 'Sidang Tesis', 4, 9, 'Menunggu Review', 0, '2026-07-24 06:32:40', '2026-07-24 06:32:40', 'Aut volupt', 'kyhegypega@mailinator.com', 'Facilis esse fugiat', 'Assoc. Prof. Dr. Adhi Kusnadi, S.T., M.Si.', 'Dr. Indra Hermawan, S.Kom., M.Kom.', NULL, NULL, 'Quaerat blanditiis s', NULL, NULL, 'uploads/sidang/20250130001-ASEP_SURAHMAN_SULAEMAN-Persetujuan_Pembimbing.jpg', 'uploads/sidang/20250130001-ASEP_SURAHMAN_SULAEMAN-KHS_Smt_1-3.jpg', NULL, NULL, NULL, 'uploads/sidang/20250130001-ASEP_SURAHMAN_SULAEMAN-Foto_Background_Merah.jpg', NULL, NULL, 'uploads/sidang/20250130001-ASEP_SURAHMAN_SULAEMAN-File_Presentasi.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `pengumpulan_revisi`
--

CREATE TABLE `pengumpulan_revisi` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `jenis_revisi` varchar(100) NOT NULL,
  `file_dokumen` varchar(255) NOT NULL,
  `file_persetujuan` varchar(255) NOT NULL,
  `status` enum('Pending','Diterima','Ditolak') DEFAULT 'Pending',
  `catatan_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prodi`
--

CREATE TABLE `prodi` (
  `id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prodi`
--

INSERT INTO `prodi` (`id`, `kode`, `nama`, `jenjang`, `kaprodi`, `sekretaris`, `kontak`, `no_wa_grup`, `prefix_surat`, `kota_surat`, `warna_hex`, `deskripsi`, `created_at`) VALUES
(1, 'MIF', 'Magister Informatika', 'S2', 'Dr. Ahmad Fauzi, M.Kom', 'Siti Rahayu, M.Kom', '081234567890', '6281234567890', NULL, 'Sukabumi', '#60a5fa', 'Program Studi S2 Magister Informatika', '2026-06-10 15:26:43'),
(2, 'MM', 'Magister Manajemen', 'S2', 'Dr. Budi Santoso, M.M', 'Dewi Kurniawati, M.M', '081234567891', '6281234567891', NULL, 'Sukabumi', '#22c55e', 'Program Studi S2 Magister Manajemen', '2026-06-10 15:26:43'),
(3, 'MH', 'Magister Hukum', 'S2', 'Dr. Armansyah, S.H., M.H', 'Rina Sari, S.H., M.H', '081234567892', '6281234567892', NULL, 'Sukabumi', '#f59e0b', 'Program Studi S2 Magister Hukum', '2026-06-10 15:26:43'),
(4, 'MP', 'Magister Pedagogi', 'S2', 'Dr. Dian Lestari, M.Pd', 'Hendra Gunawan, M.Pd', '081234567893', '6281234567893', NULL, 'Sukabumi', '#a78bfa', 'Program Studi S2 Magister Pedagogi', '2026-06-10 15:26:43'),
(5, 'DIK', 'Doktor Ilmu Komputer', 'S3', 'Prof. Dr. Eko Prasetyo, M.Cs', 'Farida Hanum, M.Cs', '081234567894', '6281234567894', NULL, 'Sukabumi', '#f87171', 'Program Studi S3 Doktor Ilmu Komputer', '2026-06-10 15:26:43');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_lampiran`
--

CREATE TABLE `riwayat_lampiran` (
  `id` int(11) NOT NULL,
  `prodi_id` int(11) NOT NULL,
  `jenis` enum('tesis','proposal') NOT NULL DEFAULT 'tesis',
  `nama_mhs` varchar(100) NOT NULL,
  `nim_mhs` varchar(20) NOT NULL,
  `judul_tesis` text NOT NULL,
  `tanggal_sidang` date NOT NULL,
  `ketua_pembimbing` varchar(100) NOT NULL,
  `anggota_pembimbing` varchar(100) NOT NULL,
  `ketua_penguji` varchar(100) NOT NULL,
  `anggota_penguji` varchar(100) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `riwayat_lampiran`
--

INSERT INTO `riwayat_lampiran` (`id`, `prodi_id`, `jenis`, `nama_mhs`, `nim_mhs`, `judul_tesis`, `tanggal_sidang`, `ketua_pembimbing`, `anggota_pembimbing`, `ketua_penguji`, `anggota_penguji`, `created_by`, `created_at`) VALUES
(1, 4, 'tesis', 'Quia animi culpa e', 'Magna non inventore ', 'Et voluptatem non n', '1996-04-08', 'Dr. H. Ujang Syarip Hidayat, M.Pd.', 'Dr. H. Ujang Syarip Hidayat, M.Pd.', 'Dr. Agus Hendriyanto, M.Pd.', 'Assoc. Prof. Dr. Ayi Abdurahman, M.Pd., M.M.', 1, '2026-07-17 12:43:22'),
(2, 1, 'tesis', 'Dule Abera', '20240130035', 'REAL-TIME CRISIS \r\nRECOGNITION AND \r\nRESPONSE  SYSTEM \r\nFOR PROTEST \r\nVIOLENCE DETECTION', '2026-07-22', 'Zeldi Suryady, S.T., M.Sc., Ph.D.', 'Risnandar, Ph.D.', 'Assoc. Prof. Dr. Adhi Kusnadi, S.T., M.Si.', 'Prof. Deden Witarsyah, S.T., M.Eng., Ph.D.', 2, '2026-07-22 01:42:28');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `value`, `updated_at`) VALUES
(1, 'nama_universitas', 'Universitas Nusa Putra', '2026-06-10 15:26:44'),
(2, 'logo_url', 'assets/images/logo.png', '2026-06-10 15:26:44'),
(3, 'tahun_akademik', '2024/2025', '2026-06-10 15:26:44'),
(4, 'semester_aktif', 'Genap', '2026-06-10 15:26:44'),
(5, 'format_nomor_surat', '[No]/NPU/[kode_prodi]/[bulan_romawi]/[tahun]', '2026-06-10 15:26:44'),
(6, 'wa_api_key', '', '2026-06-10 15:26:44'),
(7, 'wa_nomor_pengirim', '', '2026-06-10 15:26:44'),
(8, 'wa_gateway', 'fonnte', '2026-06-10 15:26:44'),
(9, 'tema_default', 'dark', '2026-06-10 15:26:44'),
(10, 'gemini_api_key', 'gsk_DUMMY_KEY_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', '2026-06-10 16:02:46'),
(11, 'ai_system_prompt', 'Anda adalah Asisten Penulis Surat Resmi. Tulislah draf surat formal dalam bahasa Indonesia. Output HANYA HTML mentah. \nATURAN WAJIB:\n1. Paragraf gunakan tag <p> (otomatis rata kiri-kanan).\n2. Jika ada informasi bersusun (misal: Hari/Tanggal, Waktu, Tempat, Agenda), WAJIB gunakan format tabel khusus ini:\n<table class=\"layout-tabel\">\n<tr><td style=\"width:120px\">Hari/Tanggal</td><td style=\"width:20px\">:</td><td>...</td></tr>\n<tr><td>Waktu</td><td>:</td><td>...</td></tr>\n</table>\n3. JANGAN membuat bagian Tanda Tangan (Hormat kami, nama terang, dll) di akhir surat! Cukup akhiri dengan paragraf penutup. Bagian tanda tangan sudah digenerate otomatis oleh sistem.', '2026-06-10 16:21:03');

-- --------------------------------------------------------

--
-- Table structure for table `surat`
--

CREATE TABLE `surat` (
  `id` int(11) NOT NULL,
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
  `is_pinned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surat`
--

INSERT INTO `surat` (`id`, `nomor_surat`, `jenis_surat`, `prodi_id`, `nama_penerima`, `jenis_penerima`, `nim_nidn`, `perihal`, `keperluan`, `tanggal`, `hari`, `kota`, `status`, `lampiran`, `isi_surat`, `file_pdf`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_pinned`) VALUES
(1, '001/NPU/MIF/I/2025', 'Surat Keterangan Aktif', 1, 'Ahmad Rizki Pratama', 'individual', '2023MIF001', 'Keterangan Mahasiswa Aktif', NULL, '2025-01-10', NULL, 'Sukabumi', 'Selesai', '-', 'Yang bertanda tangan di bawah ini...', NULL, 1, NULL, '2026-06-10 15:26:44', '2026-06-10 15:26:44', 0),
(2, '002/NPU/MM/I/2025', 'Surat Tugas', 2, 'Dr. Budi Santoso', 'individual', 'NIDN001', 'Menghadiri Seminar Nasional', NULL, '2025-01-15', NULL, 'Sukabumi', 'Selesai', '-', 'Yang bertanda tangan di bawah ini...', NULL, 1, NULL, '2026-06-10 15:26:44', '2026-06-10 15:26:44', 0),
(4, '004/NPU/MH/II/2025', 'Undangan Seminar', 3, 'Peserta Seminar', 'individual', '-', 'Seminar Hukum Digital', NULL, '2025-02-10', NULL, 'Sukabumi', 'Selesai', '-', 'Yang bertanda tangan di bawah ini...', NULL, 1, NULL, '2026-06-10 15:26:44', '2026-06-10 15:26:44', 0),
(5, '005/NPU/MP/III/2025', 'SK Kelulusan', 4, 'Hana Safitri', 'individual', '2023MP001', 'Surat Keterangan Lulus', NULL, '2025-03-01', NULL, 'Sukabumi', 'Draf', '-', 'Yang bertanda tangan di bawah ini...', NULL, 1, NULL, '2026-06-10 15:26:44', '2026-06-10 15:26:44', 0),
(8, '001/MH/NPU/VI/2026', 'Berita Acara', 3, 'Wakil Rektor 1', 'custom', '', 'Undangan Visitaasi Akreditasi Prodi Magister Manajemen', NULL, '2026-06-10', 'Rabu', 'Sukabumi', 'Draf', '-', '<p>Yth. Wakil Rektor 1 Universitas Nusa Putra di tempat,</p><p>Dengan hormat, kami beritahukan bahwa Program Studi Magister Manajemen Universitas Nusa Putra akan mengadakan visitasi akreditasi pada tanggal 20 November.</p><p>Kegiatan ini bertujuan untuk meningkatkan kualitas pendidikan dan memenuhi standar akreditasi yang telah ditetapkan.</p><p>Oleh karena itu, kami mengundang Bapak/Ibu untuk hadir dalam kegiatan visitasi akreditasi tersebut.</p><p>Terima kasih atas perhatian dan partisipasi Bapak/Ibu.</p><p>Hormat kami,<br>{{NAMA_KAPRODI}}<br>{{GELAR_KAPRODI}}<br>NIDN: {{NIDN_KAPRODI}}<br>Ketua Program Studi Magister Manajemen</p>', NULL, 1, NULL, '2026-06-10 16:09:52', '2026-06-10 16:09:52', 0),
(9, '002/MH/NPU/VI/2026', 'Berita Acara', 3, 'Mahasiswa', 'custom', '', 'Undangan Akreditasi Prodi S2 Magister Manajemen', NULL, '2026-06-10', 'Rabu', 'Sukabumi', 'Draf', '-', '<p>Yth. Mahasiswa Program Studi Magister Manajemen Universitas Nusa Putra</p><p>Dengan ini kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri acara Akreditasi Program Studi Magister Manajemen yang akan diselenggarakan pada:</p><p>Tanggal: 20 Juni 2026</p><p>Waktu: 10.00 WIB - selesai</p><p>Tempat: Kampus Universitas Nusa Putra</p><p>Acara ini bertujuan untuk memenuhi persyaratan akreditasi Program Studi Magister Manajemen dan kami mengharapkan kehadiran Bapak/Ibu/Saudara/i untuk memberikan kontribusi dalam acara ini.</p><p>Terima kasih atas perhatian Bapak/Ibu/Saudara/i.</p><p>Saya hormat,</p><p><strong>{{NAMA_KAPRODI}}<br>{{GELAR_KAPRODI}}<br>NIDN: {{NIDN_KAPRODI}}<br>Kaprodi Program Studi Magister Manajemen</strong></p>', NULL, 1, NULL, '2026-06-10 16:12:49', '2026-06-10 16:12:49', 0),
(10, '001/MIF/NPU/VI/2026', 'Berita Acara', 1, 'Mahasiswa S2 Informatika', 'custom', '', 'Undangan Penugasan Dosen', NULL, '2026-06-10', 'Rabu', 'Sukabumi', 'Draf', '-', '<p>Yth. {{NAMA_MAHASISWA}} {{NIM}},</p><p>Bersama ini kami beritahukan bahwa Anda telah ditugaskan untuk melaksanakan penugasan dosen pada mata kuliah {{MATA_KULIAH}} dengan bobot {{SKS}} SKS pada semester {{SEMESTER}} tahun akademik {{TAHUN_AKADEMIK}}.</p><p>Penugasan dosen ini bertujuan untuk meningkatkan kemampuan dan pengetahuan Anda dalam bidang informatika, serta memberikan pengalaman nyata dalam menerapkan teori yang telah dipelajari.</p><p>Penugasan dosen ini akan dilaksanakan pada {{TANGGAL_MULAI}} sampai dengan {{TANGGAL_SELESAI}} di {{LOKASI_PENELITIAN}}.</p><p>Anda diharapkan dapat mempersiapkan diri dengan baik dan mengikuti semua instruksi yang diberikan oleh dosen pembimbing.</p><p>Jika Anda memiliki pertanyaan atau kebutuhan lebih lanjut, silakan menghubungi kami.</p><p>Terima kasih atas perhatian Anda.</p><p>Saya hormat,</p><p>{{NAMA_KAPRODI}} {{GELAR_KAPRODI}}<br>{{NIDN_KAPRODI}}<br>Ketua Program Studi {{NAMA_PRODI}}</p>', NULL, 1, NULL, '2026-06-10 16:15:41', '2026-06-10 16:15:41', 0),
(11, '002/MIF/NPU/VI/2026', 'Berita Acara', 1, 'Dosen Penguji', 'custom', '', 'Undangan Sidang Tesis', NULL, '2026-06-10', 'Rabu', 'Sukabumi', 'Draf', '-', '<p>Yth. Bapak/Ibu Dosen Penguji,</p><p>Dengan ini, kami mengundang Bapak/Ibu untuk hadir dalam sidang tesis mahasiswa program studi Magister Pendidikan (S2 Pedagogy) yang akan dilaksanakan sebagai berikut:</p><table class=\"layout-tabel\"><tr><td style=\"width:120px\">Hari/Tanggal</td><td style=\"width:20px\">:</td><td>{{TANGGAL_KEGIATAN}}</td></tr><tr><td>Waktu</td><td>:</td><td>{{WAKTU}}</td></tr><tr><td>Tempat</td><td>:</td><td>{{TEMPAT}}</td></tr><tr><td>Mahasiswa</td><td>:</td><td>{{NAMA_MAHASISWA}}</td></tr><tr><td>Judul Tesis</td><td>:</td><td>{{JUDUL_TESIS}}</td></tr></table><p>Kehadiran Bapak/Ibu sangatlah penting untuk kelancaran sidang tesis tersebut. Atas perhatian dan kerja sama Bapak/Ibu, kami mengucapkan terima kasih.</p><p>Hormat kami,<br>{{NAMA_KAPRODI}}<br>{{GELAR_KAPRODI}}<br>NIDN: {{NIDN_KAPRODI}}<br>Ketua Program Studi S2 Pendidikan (Pedagody)', NULL, 1, NULL, '2026-06-10 16:18:55', '2026-06-10 16:18:55', 0);

-- --------------------------------------------------------

--
-- Table structure for table `surat_autosave`
--

CREATE TABLE `surat_autosave` (
  `id` int(11) NOT NULL,
  `surat_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `konten_html` longtext DEFAULT NULL,
  `disimpan_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `session_key` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surat_autosave`
--

INSERT INTO `surat_autosave` (`id`, `surat_id`, `user_id`, `konten_html`, `disimpan_pada`, `session_key`) VALUES
(1, NULL, 4, '', '2026-05-10 05:40:47', 'e31488a8b2873ffd8ae36572e03868eb'),
(2, NULL, 4, '', '2026-05-10 08:11:24', '687b3250df65eb3527a789c85939f4b7'),
(3, NULL, 4, '', '2026-05-10 08:16:58', 'dd53b8a4ad3f05a334bd3b5c6672bafa'),
(4, NULL, 4, '', '2026-05-10 08:18:27', '373e35fbd2253c72985993b17cb62d63');

-- --------------------------------------------------------

--
-- Table structure for table `surat_versi`
--

CREATE TABLE `surat_versi` (
  `id` int(11) NOT NULL,
  `surat_id` int(11) NOT NULL,
  `isi_lama` longtext DEFAULT NULL,
  `diubah_oleh` int(11) NOT NULL,
  `diubah_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `template_surat`
--

CREATE TABLE `template_surat` (
  `id` int(11) NOT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `jenis_surat` varchar(100) NOT NULL,
  `nama_template` varchar(150) DEFAULT NULL,
  `isi_template` longtext DEFAULT NULL,
  `header_html` text DEFAULT NULL,
  `variabel_tersedia` text DEFAULT NULL,
  `is_massal` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `template_surat`
--

INSERT INTO `template_surat` (`id`, `prodi_id`, `jenis_surat`, `nama_template`, `isi_template`, `header_html`, `variabel_tersedia`, `is_massal`, `created_at`) VALUES
(18, NULL, 'Surat Tugas Mengajar', 'Surat Tugas Mengajar Dosen', '<p>Dengan hormat,</p><p>Berdasarkan kebutuhan akademik Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, bersama ini kami menugaskan <strong>{{NAMA_PENERIMA}}</strong> untuk mengampu mata kuliah <strong>{{MATA_KULIAH}}</strong> ({{SKS}} SKS) pada Semester {{SEMESTER}} Tahun Akademik {{TAHUN_AKADEMIK}}.</p><p>Demikian surat tugas ini diberikan untuk dapat dilaksanakan dengan penuh tanggung jawab.</p>', NULL, '{{NAMA_PENERIMA}},{{MATA_KULIAH}},{{SKS}},{{SEMESTER}},{{TAHUN_AKADEMIK}}', 0, '2026-07-17 12:32:22'),
(19, NULL, 'Surat Tugas Pembimbing', 'SK Dosen Pembimbing Tesis', '<p>Berdasarkan kebutuhan akademik dalam proses penyusunan Tesis mahasiswa Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, maka dengan ini menugaskan <strong>{{NAMA_PENERIMA}}</strong> sebagai <strong>Dosen Pembimbing Tesis</strong> bagi mahasiswa:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Judul Tesis</strong></td><td>: <em>{{JUDUL_PENELITIAN}}</em></td></tr><tr><td><strong>Tahun Akademik</strong></td><td>: {{TAHUN_AKADEMIK}}</td></tr></table><p>Pembimbing diharapkan memberikan bimbingan secara berkala hingga mahasiswa tersebut menyelesaikan tesisnya.</p>', NULL, '{{NAMA_PENERIMA}},{{NAMA_MAHASISWA}},{{NIM}},{{JUDUL_PENELITIAN}},{{TAHUN_AKADEMIK}}', 0, '2026-07-17 12:32:22'),
(20, NULL, 'Surat Keterangan Aktif', 'Surat Keterangan Aktif Kuliah', '<p>Yang bertanda tangan di bawah ini, Ketua Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, menerangkan bahwa:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Angkatan</strong></td><td>: {{ANGKATAN}}</td></tr><tr><td><strong>Status</strong></td><td>: Mahasiswa Aktif</td></tr></table><p>Mahasiswa tersebut benar terdaftar sebagai mahasiswa aktif pada Tahun Akademik <strong>{{TAHUN_AKADEMIK}}</strong>. Surat ini diterbitkan untuk keperluan: <strong>{{KEPERLUAN}}</strong>.</p><p>Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>', NULL, '{{NIM}},{{NAMA_MAHASISWA}},{{ANGKATAN}},{{KEPERLUAN}}', 0, '2026-07-17 12:32:22'),
(21, NULL, 'Surat Izin Penelitian', 'Surat Izin Penelitian Tesis', '<p>Dengan hormat,</p><p>Sehubungan dengan pelaksanaan penelitian Tesis, kami mengajukan permohonan izin penelitian atas nama mahasiswa:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Judul Penelitian</strong></td><td>: {{JUDUL_PENELITIAN}}</td></tr><tr><td><strong>Lokasi</strong></td><td>: {{LOKASI_PENELITIAN}}</td></tr><tr><td><strong>Periode</strong></td><td>: {{TANGGAL_MULAI}} s.d. {{TANGGAL_SELESAI}}</td></tr></table><p>Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>', NULL, '{{NIM}},{{NAMA_MAHASISWA}},{{JUDUL_PENELITIAN}},{{LOKASI_PENELITIAN}},{{TANGGAL_MULAI}},{{TANGGAL_SELESAI}}', 0, '2026-07-17 12:32:22'),
(22, NULL, 'Undangan Seminar Proposal', 'Undangan Seminar Proposal Tesis', '<p>Dengan hormat,</p><p>Dalam rangka Seminar Proposal Tesis Program Studi {{NAMA_PRODI}}, kami mengundang Bapak/Ibu untuk hadir pada:</p><table style=\"width:100%;border-collapse:collapse;margin:12px 0\"><tr><td style=\"width:35%;padding:5px 0\"><strong>Jenis Kegiatan</strong></td><td>: Seminar Proposal Tesis</td></tr><tr><td style=\"padding:5px 0\"><strong>Presenter</strong></td><td>: {{NAMA_PRESENTER}} ({{NIM}})</td></tr><tr><td style=\"padding:5px 0\"><strong>Judul</strong></td><td>: <em>{{JUDUL_TESIS}}</em></td></tr><tr><td style=\"padding:5px 0\"><strong>Hari/Tanggal</strong></td><td>: {{TANGGAL_KEGIATAN}}</td></tr><tr><td style=\"padding:5px 0\"><strong>Pukul</strong></td><td>: {{WAKTU}} WIB</td></tr><tr><td style=\"padding:5px 0\"><strong>Tempat</strong></td><td>: {{TEMPAT}}</td></tr></table><p>Kehadiran Bapak/Ibu sangat kami harapkan. Atas perhatian dan kehadirannya, kami ucapkan terima kasih.</p>', NULL, '{{NAMA_PRESENTER}},{{NIM}},{{JUDUL_TESIS}},{{TANGGAL_KEGIATAN}},{{WAKTU}},{{TEMPAT}}', 1, '2026-07-17 12:32:22'),
(23, NULL, 'Undangan Sidang Tesis', 'Undangan Sidang Tesis (Tim Penguji)', '<p>Dengan hormat,</p><p>Dalam rangka Sidang Tesis Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, kami mengundang Bapak/Ibu Dosen sebagai Tim Penguji pada:</p><table style=\"width:100%;border-collapse:collapse;margin:12px 0\"><tr><td style=\"width:35%;padding:5px 0\"><strong>Jenis Ujian</strong></td><td>: Sidang Tesis</td></tr><tr><td style=\"padding:5px 0\"><strong>Nama Mahasiswa</strong></td><td>: {{NAMA_PRESENTER}}</td></tr><tr><td style=\"padding:5px 0\"><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td style=\"padding:5px 0\"><strong>Judul Tesis</strong></td><td>: <em>{{JUDUL_TESIS}}</em></td></tr><tr><td style=\"padding:5px 0\"><strong>Hari/Tanggal</strong></td><td>: {{TANGGAL_KEGIATAN}}</td></tr><tr><td style=\"padding:5px 0\"><strong>Pukul</strong></td><td>: {{WAKTU}} WIB</td></tr><tr><td style=\"padding:5px 0\"><strong>Tempat</strong></td><td>: {{TEMPAT}}</td></tr></table><p>Kehadiran Bapak/Ibu sebagai penguji sangat kami harapkan. Mohon konfirmasi kehadiran kepada sekretariat selambat-lambatnya 2 hari sebelum pelaksanaan.</p><p>Atas perhatian dan kesediaan Bapak/Ibu, kami ucapkan terima kasih.</p>', NULL, '{{NAMA_PRESENTER}},{{NIM}},{{JUDUL_TESIS}},{{TANGGAL_KEGIATAN}},{{WAKTU}},{{TEMPAT}}', 1, '2026-07-17 12:32:22'),
(24, NULL, 'SK Kelulusan', 'SK Kelulusan / Yudisium', '<p>Berdasarkan hasil Sidang Yudisium Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra tanggal {{TANGGAL_YUDISIUM}}, maka:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>IPK</strong></td><td>: {{IPK}}</td></tr><tr><td><strong>Predikat</strong></td><td>: <strong>{{PREDIKAT}}</strong></td></tr></table><p>Dinyatakan <strong>LULUS</strong> dan berhak memperoleh gelar sesuai Program Studi yang ditempuh.</p>', NULL, '{{NIM}},{{NAMA_MAHASISWA}},{{IPK}},{{TANGGAL_YUDISIUM}},{{PREDIKAT}}', 0, '2026-07-17 12:32:22'),
(25, NULL, 'Surat Rekomendasi', 'Surat Rekomendasi Mahasiswa', '<p>Yang bertanda tangan di bawah ini, Ketua Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, memberikan rekomendasi kepada:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr></table><p>{{PRESTASI}}</p><p>Kami merekomendasikan yang bersangkutan untuk: <strong>{{TUJUAN_REKOMENDASI}}</strong>.</p><p>Demikian surat rekomendasi ini dibuat untuk dapat digunakan sebagaimana mestinya.</p>', NULL, '{{NIM}},{{NAMA_MAHASISWA}},{{TUJUAN_REKOMENDASI}},{{PRESTASI}}', 0, '2026-07-17 12:32:22'),
(26, NULL, 'Surat Pengantar Ijazah', 'Surat Pengantar Pengambilan Ijazah', '<p>Dengan hormat,</p><p>Bersama ini kami sampaikan bahwa mahasiswa berikut telah menyelesaikan studi dan berhak mengambil ijazah:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Nomor Ijazah</strong></td><td>: {{NO_IJAZAH}}</td></tr></table><p>Mohon ijazah dapat diserahkan kepada yang bersangkutan dengan menunjukkan surat ini dan kartu identitas.</p>', NULL, '{{NIM}},{{NAMA_MAHASISWA}},{{NO_IJAZAH}}', 0, '2026-07-17 12:32:22'),
(27, NULL, 'Surat Pemberitahuan', 'Surat Pemberitahuan Umum (Massal)', '<p>Dengan hormat,</p><p>Bersama ini kami sampaikan pemberitahuan mengenai: <strong>{{PERIHAL}}</strong></p><p>{{ISI_PEMBERITAHUAN}}</p><p>Kegiatan akan dilaksanakan pada tanggal <strong>{{TANGGAL_PELAKSANAAN}}</strong>.</p><p>Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>', NULL, '{{PERIHAL}},{{ISI_PEMBERITAHUAN}},{{TANGGAL_PELAKSANAAN}}', 1, '2026-07-17 12:32:22'),
(28, NULL, 'Surat Permohonan', 'Surat Permohonan ke Instansi Luar', '<p>Dengan hormat,</p><p>Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra mengajukan permohonan kepada <strong>{{NAMA_INSTANSI}}</strong> yang beralamat di {{ALAMAT_INSTANSI}}.</p><p>Adapun permohonan kami: {{ISI_PERMOHONAN}}</p><p>Besar harapan kami permohonan ini dapat dikabulkan. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>', NULL, '{{NAMA_INSTANSI}},{{ALAMAT_INSTANSI}},{{ISI_PERMOHONAN}}', 0, '2026-07-17 12:32:22'),
(29, NULL, 'Surat Perpanjangan Studi', 'Surat Permohonan Perpanjangan Studi', '<p>Dengan hormat,</p><p>Sehubungan dengan belum selesainya penyusunan tesis, kami mengajukan permohonan perpanjangan masa studi bagi mahasiswa:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Angkatan</strong></td><td>: {{ANGKATAN}}</td></tr><tr><td><strong>Alasan</strong></td><td>: {{KEPERLUAN}}</td></tr><tr><td><strong>Target Selesai</strong></td><td>: {{TANGGAL_SELESAI}}</td></tr></table><p>Mahasiswa berkomitmen menyelesaikan studi sesuai target. Atas perhatian dan kebijaksanaan Bapak/Ibu, kami ucapkan terima kasih.</p>', NULL, '{{NAMA_MAHASISWA}},{{NIM}},{{ANGKATAN}},{{KEPERLUAN}},{{TANGGAL_SELESAI}}', 0, '2026-07-17 12:32:22'),
(30, NULL, 'Surat Cuti Akademik', 'Surat Permohonan Cuti Akademik', '<p>Dengan hormat,</p><p>Kami menerangkan bahwa mahasiswa berikut mengajukan permohonan cuti akademik:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr><tr><td><strong>Semester</strong></td><td>: {{SEMESTER}}</td></tr><tr><td><strong>Alasan</strong></td><td>: {{KEPERLUAN}}</td></tr><tr><td><strong>Periode Cuti</strong></td><td>: {{TANGGAL_MULAI}} s.d. {{TANGGAL_SELESAI}}</td></tr></table><p>Selama masa cuti, mahasiswa tidak diperkenankan mengikuti kegiatan akademik. Demikian untuk dapat digunakan sebagaimana mestinya.</p>', NULL, '{{NAMA_MAHASISWA}},{{NIM}},{{SEMESTER}},{{KEPERLUAN}},{{TANGGAL_MULAI}},{{TANGGAL_SELESAI}}', 0, '2026-07-17 12:32:22'),
(31, NULL, 'Surat Bebas Perpustakaan', 'Surat Keterangan Bebas Perpustakaan', '<p>Yang bertanda tangan di bawah ini, Ketua Program Studi {{NAMA_PRODI}} Pascasarjana Universitas Nusa Putra, menerangkan bahwa:</p><table style=\"width:100%;margin:12px 0\"><tr><td style=\"width:35%\"><strong>Nama</strong></td><td>: {{NAMA_MAHASISWA}}</td></tr><tr><td><strong>NIM</strong></td><td>: {{NIM}}</td></tr><tr><td><strong>Program Studi</strong></td><td>: {{NAMA_PRODI}}</td></tr></table><p>Mahasiswa tersebut dinyatakan <strong>BEBAS</strong> dari pinjaman dan tanggungan perpustakaan Universitas Nusa Putra. Surat ini diberikan untuk keperluan: <strong>{{KEPERLUAN}}</strong>.</p>', NULL, '{{NAMA_MAHASISWA}},{{NIM}},{{KEPERLUAN}}', 0, '2026-07-17 12:32:22'),
(32, NULL, 'Surat Undangan Rapat', 'Undangan Rapat Program Studi (Massal)', '<p>Dengan hormat,</p><p>Mengharap kehadiran Bapak/Ibu dalam kegiatan:</p><table style=\"width:100%;border-collapse:collapse;margin:12px 0\"><tr><td style=\"width:35%;padding:5px 0\"><strong>Acara</strong></td><td>: {{JENIS_KEGIATAN}}</td></tr><tr><td style=\"padding:5px 0\"><strong>Hari/Tanggal</strong></td><td>: {{TANGGAL_KEGIATAN}}</td></tr><tr><td style=\"padding:5px 0\"><strong>Pukul</strong></td><td>: {{WAKTU}} WIB</td></tr><tr><td style=\"padding:5px 0\"><strong>Tempat</strong></td><td>: {{TEMPAT}}</td></tr></table><p>Agenda: {{ISI_PEMBERITAHUAN}}</p><p>Kehadiran Bapak/Ibu tepat waktu sangat kami harapkan. Atas perhatian dan kehadirannya, kami ucapkan terima kasih.</p>', NULL, '{{JENIS_KEGIATAN}},{{TANGGAL_KEGIATAN}},{{WAKTU}},{{TEMPAT}},{{ISI_PEMBERITAHUAN}}', 1, '2026-07-17 12:32:22'),
(33, NULL, 'Berita Acara', 'Berita Acara Kegiatan', '<p style=\"text-align:center\"><strong>BERITA ACARA {{JENIS_KEGIATAN}}</strong></p><p>Pada hari ini, {{TANGGAL_KEGIATAN}}, telah dilaksanakan <strong>{{JENIS_KEGIATAN}}</strong> dengan peserta: {{PESERTA}}.</p><p><strong>Hasil Kegiatan:</strong></p><p>{{HASIL_KEGIATAN}}</p><p>Demikian berita acara ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>', NULL, '{{JENIS_KEGIATAN}},{{TANGGAL_KEGIATAN}},{{PESERTA}},{{HASIL_KEGIATAN}}', 0, '2026-07-17 12:32:22');

-- --------------------------------------------------------

--
-- Table structure for table `tugas`
--

CREATE TABLE `tugas` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `prioritas` enum('Tinggi','Sedang','Rendah') DEFAULT 'Sedang',
  `deadline` date DEFAULT NULL,
  `status` enum('Belum','Dikerjakan','Selesai') DEFAULT 'Belum',
  `label_warna` varchar(7) DEFAULT '#60a5fa',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tugas`
--

INSERT INTO `tugas` (`id`, `judul`, `deskripsi`, `prodi_id`, `prioritas`, `deadline`, `status`, `label_warna`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Review Tesis Ahmad Rizki', 'Review dan feedback tesis mahasiswa semester 4', 1, 'Tinggi', '2026-06-11', 'Dikerjakan', '#60a5fa', 1, '2026-06-10 15:26:44', '2026-06-10 15:26:44'),
(2, 'Siapkan Dokumen Akreditasi', 'Kumpulkan semua dokumen untuk akreditasi prodi', 2, 'Tinggi', '2026-06-15', 'Belum', '#22c55e', 1, '2026-06-10 15:26:44', '2026-06-10 15:26:44'),
(3, 'Update Data Mahasiswa 2025', 'Perbarui data mahasiswa angkatan 2025', NULL, 'Sedang', '2026-06-17', 'Belum', '#f59e0b', 1, '2026-06-10 15:26:44', '2026-06-10 15:26:44'),
(4, 'Rapat Koordinasi Prodi', 'Rapat koordinasi seluruh kaprodi', NULL, 'Sedang', '2026-06-13', 'Belum', '#a78bfa', 1, '2026-06-10 15:26:44', '2026-06-10 15:26:44'),
(5, 'Kirim Laporan Bulanan', 'Laporan bulanan ke rektorat', NULL, 'Rendah', '2026-06-24', 'Belum', '#f87171', 1, '2026-06-10 15:26:44', '2026-06-10 15:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin_prodi','mahasiswa','dosen') NOT NULL,
  `prodi_id` int(11) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_exp` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `prodi_id`, `nama`, `email`, `foto`, `last_login`, `created_at`, `reset_token`, `reset_token_exp`) VALUES
(1, 'pascasarjana', '$2y$10$pGDpZyZm8ceX8NlYo9aRJu/s.UqnkPy5HzOV7wlXUzxKpnaaG4v16', 'super_admin', NULL, 'Admin Pascasarjana', 'pascasarjana@nusaputra.ac.id', NULL, '2026-07-29 07:51:44', '2026-06-10 15:26:44', NULL, NULL),
(2, 'admin', '$2y$10$pGDpZyZm8ceX8NlYo9aRJu/s.UqnkPy5HzOV7wlXUzxKpnaaG4v16', 'super_admin', NULL, 'Administrator', 'admin@nusaputra.ac.id', NULL, '2026-07-22 01:40:54', '2026-06-10 15:26:44', NULL, NULL),
(3, 'admin_mif', '$2y$10$sj7JN0OZPcsiZ6qHTsxD9OG2Va.dBrpF2H0p7wemhKZh3ViAaDCma', 'admin_prodi', 1, 'Admin Magister Informatika', 'admin.mif@NPU.ac.id', NULL, NULL, '2026-06-10 15:26:44', NULL, NULL),
(4, '20260140001', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'SIROJUDIN, S.AK', 'sirojudin_mpd26@nusaputra.ac.id', NULL, '2026-07-18 10:19:27', '2026-07-18 10:16:56', NULL, NULL),
(5, '20260140002', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'MOH. IMAM', 'moh.imam_mpd26@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(6, '20260140003', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ANGGI SUGIRI', 'anggi.sugiri_mpd26@nusaputra.ac.id', NULL, '2026-07-19 14:39:46', '2026-07-18 10:16:56', NULL, NULL),
(7, '20260140004', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'RANI YULIANI', 'rani.yuliani_mpd26@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(8, '20250140001', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'RIKA RAHAYU', 'rika.rahayu_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(9, '20250140002', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'LUSIANA ALAWIYAH', 'lusiana.alawiyah@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(10, '20250140003', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'DEDE KURNIA SAFARI', 'dede.kurnia@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(11, '20250140004', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ESIH SUKAESIH', 'esih.sukaesih@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(12, '20250140005', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'SAEPULANA', 'saepulana@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(13, '20250140006', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'DENY SETIAWAN', 'deny.setiawan_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(14, '20250140007', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ASEP RUSWANDI', 'asep.ruswandi_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(15, '20250140008', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ISMATULLAH', 'ismattullah5451@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(16, '20250140009', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'SATRIA NURRAJAB TANOEJIWA', 'satria.nurrajab@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(17, '20250140010', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'HERU HERMAWAN', 'heru.hermawan@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(18, '20250140011', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ASEP PURNAWAN', 'asep.purnawan@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(19, '20250140012', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'LUKY BELA MULTINA', 'luky.bela@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(20, '20250140013', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'CUCU KARNELIS', 'cucu.karnelis_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(21, '20250140014', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'E. SURAHMAN', 'e.surahman.mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(22, '20250140015', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'DEDI RUKMANA', 'dedi.rukmana_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(23, '20250140016', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'MUHTADIN', 'muhtadin_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(24, '20250140017', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'JEPRI ISKANDAR', 'jepri.iskandar_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(25, '20250140018', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ANA MARIAH', 'ana.mariah@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(26, '20250140019', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'RASMINI MASRIYANTI', 'rasmini.masriyanti_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(27, '20250140020', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'AI DALFA', 'ai.dalfa_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(28, '20250140021', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ADNAN ABDILLAH', 'adnan.abdillah_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(29, '20250140022', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'NELIS', 'nelis.mpd@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(30, '20250140023', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'DEWI NANIK YULIAWATI', 'dewi.nanik_mpd@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(31, '20250140024', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'SEPTI LAILA ANJANI', 'septi.laila_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(32, '20250140025', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'JAKA MUA\'RIF', 'jaka.muarif_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(33, '20250140026', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'RESTU ALIFA ZAHRA', 'restu.alifa_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(34, '20250140027', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'SAWAAN LISA ILINA', 'sawaan.lisa_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(35, '20250140028', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ANGGY MAULANA JOHN WINARA', 'anggy.maulana_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(36, '20250140029', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'NURHASANAH', 'nurhasanah_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(37, '20250140030', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ENENG NURAFIFAH FAUZIYAH', 'eneng.nurafifah_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(38, '20250140031', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'AHMAD SIDIK PERMANA', 'ahmad.sidik_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(39, '20250140032', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ANIS ANISSA NUR', 'anis.anissa_mpd25@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(40, '20240140001', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ENCEP ABDULLAH ALMUKAROM', 'encepdabdullahalmukarom@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(41, '20240140002', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'IIS ISNAWATI', 'iis.isnawati@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(42, '20240140003', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'SITI RAHMAWATI', 'siti.rahmawati@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(43, '20240140004', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'HILLMAN ARIF', 'hillman.arif@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(44, '20240140005', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'MAMAT RUSLAN', 'mamat.ruslan@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(45, '20240140006', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'YENYEN HENDRAYANI', 'yenyen.hendrayani_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(46, '20240140007', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'SITI FATIMAH', 'siti.fatimah_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(47, '20240140008', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ARMAN MAULANA', 'am12111416@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(48, '20240140009', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'AULIA RAHMA', 'nctxenxexol2@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(49, '20240140010', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'SEPTIANA PUTRI JUARIYAH', 'septianaputrijuariyah2@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(50, '20240140011', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ADI FITRIYADI', 'adi.fitriyadi_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(51, '20240140012', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'DEASY FATMASARI', 'daffaabisali@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(52, '20240140013', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'TEGUH AL-HADI', 'teguh.al-hadi_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(53, '20240140014', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'AI MULYANI', 'ai.mulyani@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(54, '20240140015', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'NANI SUPARTINI', 'nani.supartini@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(55, '20240140016', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'DIMAS CAESAR ALKAUTSAR', 'dimas.caesar_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(56, '20240140017', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'NIA NURNIATI', 'nia.nurniati_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(57, '20240140018', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'NIA MEYLANI', 'nia.meylani_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(58, '20240140019', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'HENI SURYANI', 'heni.suryani_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(59, '20240140020', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ROSA YULISTIANI', 'rosa.yulistiani_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(60, '20240140021', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'NADIA YULISMA', 'nadia.yulisma_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(61, '20240140022', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'HETI YULIATI', 'heti.yuliati_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(62, '20240140023', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'NENDEN HENDARSIH', 'nenden.hendarsih@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(63, '20240140024', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ABDULOH RIDWAN', 'abduloh.ridwan_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(64, '20240140025', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'MARWAN GUNAWAN', 'marwan.gunawan_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(65, '20240140026', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'TUTI HERNAWATI', 'tuti.hernawati_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(66, '20240140027', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'HAPIPAH', 'hapipah_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(67, '20240140028', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'YUYU YULIAWATI', 'yuyu.yuliawati_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(68, '20240140029', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'SUSI HERAYATI', 'susi.herayati_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(69, '20240140030', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'VIA ULFAH', 'viaulfah.art@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(70, '20240140031', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'YAMAN', 'yaman_mpd24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(71, '20240140032', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ILHAM SEPTIAN MAULANA', 'ilham.septian@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(72, '20240140033', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'DESTI MAHARDIKAWATI', 'desti.mahardikawati@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(73, '20240140034', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'SITI WAHYUNINGSIH', 'stway1990@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(74, '20240140035', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'ATI SUSILAWATI', 'ati.susilawati@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(75, '20240140036', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'SYIPA RODIATUL ZANNAH', 'syiparzanna@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(76, '20240140037', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 4, 'LIA YULIANTI', 'liayulianti309@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(77, '20260150001', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'IRWAN GUNAWAN', 'irwan.gunawan_mm26@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(78, '20260150002', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'HIDAYATULLOH', 'hidayatulloh_mm26@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(79, '20260150003', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'SEPTO SUHARYANTO', 'septo.suharyanto_mm26@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(80, '20240150001', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'TAUFIK NURHADI', 'taufik.nurhadi@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(81, '20240150002', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'ROZAN VINA RAHMANI', 'rozan.vina@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(82, '20240150003', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'YOSSY ROSALINDA', 'yossyrosalinda11@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(83, '20240150004', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'HERI FIRMANSYAH', 'firmansyahheri5@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(84, '20240150005', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'DEDE HERMAWAN', 'dedehermawan1201@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(85, '20240150006', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'IMELDA DELFINA SIBARANI', 'dmr.mitraraya@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(86, '20240150007', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'AJIZ', 'HM.Ajiz63@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(87, '20240150008', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'LELY FAUZIAH', 'fauziahlely73@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(88, '20240150009', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'RAMDAN RUSTARMONO', 'ramdan.rustarmono@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(89, '20240150010', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'R. MOCH. AGUS RAMDHAN', 'moch.agus_mm24@nusaputra.ac.id', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(90, '20240150011', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', 2, 'TOTONG SUPARMAN', 'tos.artos80@gmail.com', NULL, NULL, '2026-07-18 10:16:56', NULL, NULL),
(91, '20230130023', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'MUPENZI NKUNDUWERA JULIEN', 'mupenzi.nkunduwera@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:46:03', NULL, NULL),
(92, '20230130024', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Mouhameden El Bousseiry', 'medboss101@gmail.com', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(93, '20230130025', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'RIRIN OKTAVIANIVA', 'ririn.oktavianiva@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(94, '20230130026', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'ADE RAMDANI', 'ramdanieade@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(95, '20230130027', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'UJANG RUKMAN', 'ujangrukmann@gmail.com', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(96, '20230130028', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'HADI AHMED ABDULLAH AL MOHAB', 'hadi.almohab@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(97, '20230130029', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'YOGA VIKRIANSYAH W', 'yogavwijaya@gmail.com', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(98, '20230130031', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'MUCHTAR JAMY F', 'muchtar.jamy_mif@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(99, '20230130032', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'HANEEN A A ABUSHAMMALA', 'haneen.a@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(100, '20240130001', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Bekto Suprapto', 'bekto.suprapto@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(101, '20240130002', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'I Made Redi Hartana', 'made.redi@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(102, '20240130003', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Antonius Endro Prabowo', 'antonius.endro@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(103, '20240130005', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Gun Gun Febrianza', 'gungun.febrianza@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(104, '20240130006', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Mulyana Yusuf', 'mulyana.yusuf@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(105, '20240130007', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Roger Wagiu', 'roger.wagiu@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(106, '20240130008', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Defttry Praise Utomo', 'defttry.praise@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(107, '20240130009', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Andi Hermawan Abdillah', 'andi.hermawan@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(108, '20240130010', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Samsul Alamm', 'alam.steksa@gmail.com', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(109, '20240130011', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Widya Purnama Dewi', 'widya.purnama@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(110, '20240130012', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Muhammad Nurifki Filino', 'nurifki.filino@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(111, '20240130013', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'Dhisa Septianto', 'dhisa.septianto@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(112, '20240130015', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'RIZA RUMAYANTI DEWI', 'rizarumayantidewi@gmail.com', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(113, '20240130016', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'RIKA AGISHA SITI NURAZIZAH', 'rika.agisha@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(114, '20240130017', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'SAMSUL ALAM', 'samsul.alam@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(115, '20240130018', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'REHAENI ATIPAH', 'rehaeni.atipah@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(116, '20240130019', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'FERRY FEBIANSAH', 'ferry.febiansah_cs24@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(117, '20240130020', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'SITI NADIYA FAUZANI', 'siti.nadiya@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(118, '20240130021', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'NUR ELAH', 'nur.elah@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(119, '20240130022', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'AHDI NAUFAL HAMDI', 'ahdi.naufal_mif24@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(120, '20240130023', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'RIKI MULYANA', 'riki.mulyana_mif24@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(121, '20240130024', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'MUHAMAD HAVIDZ ALKAUSAR', 'muhamad.havidz_mif24@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(122, '20240130025', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'TAUFIK ALFIKAR', 'taufik.alfikar_mif24@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(123, '20240130026', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'ARIS MAULANA', 'maulanaaris831@gmail.com', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(124, '20240130027', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'YUSUF SUPRIYANTO', 'yusufsupriyanto@sukabumikab.go.id', NULL, NULL, '2026-07-18 12:56:28', NULL, NULL),
(125, '20240130028', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'DZAKY IBNU RUSYD', 'dzaky.rusyd22@gmail.com', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(126, '20240130029', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'SUNOKO', 'sunoko.11121267@gmail.com', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(127, '20240130030', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'ERIP SURATNO', 'eripsuratno@gmail.com', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(128, '20240130031', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'FAHMY RODIBILLAH', 'fferob@gmail.com', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(129, '20240130032', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'ARGI GINANJAR', 'argi.ginanjar@gmail.com', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(130, '20240130033', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'CEPI SURYADI MARWAN', 'csmlike@gmail.com', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(131, '20240130034', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'PREETAM KUMAR', 'preetam.kthv4@gmail.com', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(132, '20240130035', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'DULE ABERA', 'duleabera06@gmail.com', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(133, '20240130036', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'SISSOKO MAKAN', 'makansissoko156@gmail.com', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(134, '20250130001', '$2y$10$EJ1/yNGHfIiLPKnMXDhWhOUJQZh6pyc3lvqrcUAw0wl7jav8bTGQW', 'mahasiswa', NULL, 'ASEP SURAHMAN SULAEMAN', 'asep.surahman@nusaputra.ac.id', NULL, '2026-07-29 07:50:14', '2026-07-18 12:56:29', NULL, NULL),
(135, '20250130002', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'ANGGI TRIANA', 'anggi.triana_mif25@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(136, '20250130006', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'ABUBAKAR IBRAHIM MUHAMMAD', 'kufaabubakar58@gmail.com', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(137, '20250130007', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'ARMAN SARI', 'arman.sari_mif25@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(138, '20250130008', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'MUHAMMAD ABDUL AZIZ', 'muhammad.abdul_mif25@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(139, '20250130009', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'HILMAN YUNUS', 'hilman.yunus_mif25@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(140, '20250130016', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'TASYA YUSTIRA AGUSTIAN', 'tasya.yustira@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(141, '20250130017', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'DEAS AGHELLAR', 'deas.aghellar@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(142, '20250130018', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'DINDA TASYA MAHARDIKA', 'dinda.tasya_mif25@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(143, '20250130021', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'GUGUN NURYAKIN', 'gugun.nuryakin_cs25@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(144, '20250130033', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'SEPTI MUHAMMAD AGUSTIRA', 'septi.muhammad_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(145, '20250130035', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'RISWAN ZAENAL ARIPIN', 'riswan.zaenal@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(146, '20250130036', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'ASEP RIZKI FIRDAUS', 'muhammadrizkifirdaus111@gmail.com', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(147, '20250130038', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'PANUTAN SAKTI SULENDRAKUSUMA', 'panutan.sakti@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(148, '20260130001', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'PUTRINDA CHOIRUNNISA', 'putrinda.choirunnisa@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(149, '20260130002', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'SITI FAZIUR RAHMAH', 'siti.faziur@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(150, '20260130003', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'SHANIKA LAYUNG MEDAL WANGI', 'shanika.layung_mif26@nusaputra.ac.id', NULL, NULL, '2026-07-18 12:56:29', NULL, NULL),
(151, '20250150001', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'AGRI HAMDANI APRIANA', 'agri@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:14:26', NULL, NULL),
(152, '20250150002', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'MEILINA SUKMARINI', 'meilina.sukmarini@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:42', NULL, NULL),
(153, '20250150003', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'NENDA SUHANDA', 'nenda.suhanda@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:42', NULL, NULL),
(154, '20250150004', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'N. FITRIYAH NUR WAHIDAH', 'n.fitriyah_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:42', NULL, NULL),
(155, '20250150005', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'SELVIYANI', 'selviyani@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:42', NULL, NULL),
(156, '20250150006', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'YANA PRIYANA', 'yana.priyana_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:42', NULL, NULL),
(157, '20250150007', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'JATNIKA EKA PATRA', 'jatnika.eka@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:42', NULL, NULL),
(158, '20250150008', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'INTAN NURHAYATI', 'intan.nurhayati_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:42', NULL, NULL),
(159, '20250150009', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'SITI BADRIYAH', 'siti.badriyah@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:42', NULL, NULL),
(160, '20250150010', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'MUHAMMAD ASAADULHAQ ASH SHIDIQ', 'muhammad.asaadulhaq@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:42', NULL, NULL),
(161, '20250150011', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'FITRI AFRINA', 'fitri.afrina@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(162, '20250150012', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'ALYA SYIFA FADILLA', 'alya.syifa_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(163, '20250150013', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'NENG MEDINA', 'neng.medina_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(164, '20250150014', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'CITRA DWIYANTI RIDWAN', 'citra.dwiyanti_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(165, '20250150015', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'IRVAN AGUNG APRIANSYAH', 'irvan.agung_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(166, '20250150016', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'MUHAMMAD RIHAN RISKI FAHLEVI', 'muhammad.rihan_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(167, '20250150017', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'SOLEH GUNAWAN', 'soleh.gunawan_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(168, '20250150018', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'MUHAMMAD HAMDI', 'muhammad.hamdi@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(169, '20250150019', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'FERRY PRIANTONO', 'ferry.priantono_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(170, '20250150020', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'GINA SAHADATUN NISA', 'gina.sahadatun_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(171, '20250150021', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'NANI NAFISAH', 'nani.nafisah_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(172, '20250150022', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'ARGI RAMDHAN MUBARAK', 'argi.ramdhan_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:43', NULL, NULL),
(173, '20250150023', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'YULI SUSANTI', 'yuli.susanti_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:44', NULL, NULL),
(174, '20250150024', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'MUHAMMAD PAJRUL PALAH', 'muhammad.pajrul_mm25@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:44', NULL, NULL),
(175, '20250150025', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'MUHAMMAD SALIM MACHFUD', 'muhammad.salim_mm@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:44', NULL, NULL),
(176, '20250150026', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'LUSSY YULIASARI, S.E', 'lussy.yuliasari_mm@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:44', NULL, NULL),
(177, '20250150027', '$2y$10$bnbK.80StYD2zNHrXh9qb.Z5tCJkiMcRIaJrd.UjCMabW92Lldqt6', 'mahasiswa', NULL, 'ATENG HAMDANI', 'ateng.hamdani_mpd@nusaputra.ac.id', NULL, NULL, '2026-07-18 13:23:44', NULL, NULL),
(181, 'asep.surahman.sulaeman', '$2y$10$pGDpZyZm8ceX8NlYo9aRJu/s.UqnkPy5HzOV7wlXUzxKpnaaG4v16', 'super_admin', NULL, 'Asep Surahman Sulaeman', 'asep.surahman.sulaeman@nusaputra.ac.id', NULL, '2026-07-27 01:48:52', '2026-07-19 11:23:30', NULL, NULL),
(182, 'mif', '$2y$10$pGDpZyZm8ceX8NlYo9aRJu/s.UqnkPy5HzOV7wlXUzxKpnaaG4v16', 'admin_prodi', 1, 'Kaprodi Magister Informatika', 'mif@nusaputra.ac.id', NULL, NULL, '2026-07-19 11:23:30', NULL, NULL),
(183, 'pedagogy', '$2y$10$pGDpZyZm8ceX8NlYo9aRJu/s.UqnkPy5HzOV7wlXUzxKpnaaG4v16', 'admin_prodi', 4, 'Kaprodi Magister Pedagogi', 'pedagogy@nusaputra.ac.id', NULL, NULL, '2026-07-19 11:23:30', NULL, NULL),
(184, 'master.management', '$2y$10$pGDpZyZm8ceX8NlYo9aRJu/s.UqnkPy5HzOV7wlXUzxKpnaaG4v16', 'admin_prodi', 2, 'Kaprodi Magister Manajemen', 'master.management@nusaputra.ac.id', NULL, NULL, '2026-07-19 11:23:30', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_log`
--

CREATE TABLE `whatsapp_log` (
  `id` int(11) NOT NULL,
  `tujuan` varchar(50) NOT NULL,
  `jenis_tujuan` enum('individu','grup') DEFAULT 'individu',
  `pesan` text NOT NULL,
  `status` enum('Terkirim','Gagal','Pending') DEFAULT 'Pending',
  `waktu_kirim` timestamp NULL DEFAULT NULL,
  `jadwal_kirim` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_log`
--

INSERT INTO `whatsapp_log` (`id`, `tujuan`, `jenis_tujuan`, `pesan`, `status`, `waktu_kirim`, `jadwal_kirim`, `created_by`, `created_at`) VALUES
(1, '6281234567890', 'grup', 'Pengingat: Seminar Proposal besok pukul 09.00 WIB di Ruang Seminar Lantai 3', 'Terkirim', '2026-06-10 13:26:44', NULL, 1, '2026-06-10 15:26:44'),
(2, '6281111111111', 'individu', 'Halo Ahmad Rizki, mohon segera kumpulkan revisi proposal paling lambat besok pagi.', 'Terkirim', '2026-06-10 10:26:44', NULL, 1, '2026-06-10 15:26:44'),
(3, '085351221602', 'individu', 'Yth. [NAMA],\r\n\r\nDiundang menghadiri:\r\n*SEMINAR [JUDUL]*\r\n📅 [TANGGAL] | ⏰ [WAKTU]\r\n📍 [TEMPAT]\r\n\r\nSalam,\r\nAdmin Pascasarjana NPU', 'Terkirim', '2026-07-19 00:02:03', NULL, 1, '2026-07-19 00:02:03'),
(4, '085659838977', 'individu', 'Yth. [NAMA],\r\n\r\nDiundang menghadiri:\r\n*SEMINAR [JUDUL]*\r\n📅 [TANGGAL] | ⏰ [WAKTU]\r\n📍 [TEMPAT]\r\n\r\nSalam,\r\nAdmin Pascasarjana NPU', 'Terkirim', '2026-07-19 00:02:46', NULL, 1, '2026-07-19 00:02:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `catatan`
--
ALTER TABLE `catatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prodi_id` (`prodi_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prodi_id` (`prodi_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `prodi_id` (`prodi_id`);

--
-- Indexes for table `mahasiswa_publikasi`
--
ALTER TABLE `mahasiswa_publikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `pendaftaran_sidang_id` (`pendaftaran_sidang_id`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pendaftaran_sidang`
--
ALTER TABLE `pendaftaran_sidang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indexes for table `pengumpulan_revisi`
--
ALTER TABLE `pengumpulan_revisi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`);

--
-- Indexes for table `prodi`
--
ALTER TABLE `prodi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indexes for table `riwayat_lampiran`
--
ALTER TABLE `riwayat_lampiran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prodi_id` (`prodi_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_name` (`key_name`);

--
-- Indexes for table `surat`
--
ALTER TABLE `surat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD KEY `prodi_id` (`prodi_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `surat_autosave`
--
ALTER TABLE `surat_autosave`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `surat_id` (`surat_id`);

--
-- Indexes for table `surat_versi`
--
ALTER TABLE `surat_versi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surat_id` (`surat_id`);

--
-- Indexes for table `template_surat`
--
ALTER TABLE `template_surat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prodi_id` (`prodi_id`);

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prodi_id` (`prodi_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `prodi_id` (`prodi_id`);

--
-- Indexes for table `whatsapp_log`
--
ALTER TABLE `whatsapp_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `catatan`
--
ALTER TABLE `catatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `mahasiswa_publikasi`
--
ALTER TABLE `mahasiswa_publikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pendaftaran_sidang`
--
ALTER TABLE `pendaftaran_sidang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengumpulan_revisi`
--
ALTER TABLE `pengumpulan_revisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prodi`
--
ALTER TABLE `prodi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `riwayat_lampiran`
--
ALTER TABLE `riwayat_lampiran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `surat`
--
ALTER TABLE `surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `surat_autosave`
--
ALTER TABLE `surat_autosave`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `surat_versi`
--
ALTER TABLE `surat_versi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `template_surat`
--
ALTER TABLE `template_surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT for table `whatsapp_log`
--
ALTER TABLE `whatsapp_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `catatan`
--
ALTER TABLE `catatan`
  ADD CONSTRAINT `catatan_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `catatan_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`);

--
-- Constraints for table `mahasiswa_publikasi`
--
ALTER TABLE `mahasiswa_publikasi`
  ADD CONSTRAINT `mahasiswa_publikasi_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mahasiswa_publikasi_ibfk_2` FOREIGN KEY (`pendaftaran_sidang_id`) REFERENCES `pendaftaran_sidang` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pendaftaran_sidang`
--
ALTER TABLE `pendaftaran_sidang`
  ADD CONSTRAINT `pendaftaran_sidang_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengumpulan_revisi`
--
ALTER TABLE `pengumpulan_revisi`
  ADD CONSTRAINT `pengumpulan_revisi_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `riwayat_lampiran`
--
ALTER TABLE `riwayat_lampiran`
  ADD CONSTRAINT `riwayat_lampiran_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`),
  ADD CONSTRAINT `riwayat_lampiran_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `surat`
--
ALTER TABLE `surat`
  ADD CONSTRAINT `fk_surat_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `surat_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`),
  ADD CONSTRAINT `surat_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `surat_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `surat_versi`
--
ALTER TABLE `surat_versi`
  ADD CONSTRAINT `surat_versi_ibfk_1` FOREIGN KEY (`surat_id`) REFERENCES `surat` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `template_surat`
--
ALTER TABLE `template_surat`
  ADD CONSTRAINT `template_surat_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tugas_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `prodi` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `whatsapp_log`
--
ALTER TABLE `whatsapp_log`
  ADD CONSTRAINT `whatsapp_log_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
