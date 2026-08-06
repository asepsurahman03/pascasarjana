<?php
require_once __DIR__ . '/config/database.php';

try {
    Database::query("ALTER TABLE mahasiswa MODIFY program_studi VARCHAR(100) NOT NULL");
    Database::query("ALTER TABLE pengunduran_diri MODIFY program_studi VARCHAR(100) NOT NULL");
    echo "<h3>Sukses!</h3>";
    echo "<p>Struktur tabel database telah berhasil diperbarui. Program Studi sekarang menggunakan tipe VARCHAR(100).</p>";
    echo "<p>Silakan coba isi formulir kembali.</p>";
} catch (Exception $e) {
    echo "<h3>Gagal</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
