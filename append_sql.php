<?php
$sql = "\n\nCREATE TABLE `riwayat_lampiran` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n";
file_put_contents('database.sql', $sql, FILE_APPEND);
file_put_contents('database_export_terbaru.sql', $sql, FILE_APPEND);
echo "Appended successfully.";
