<?php
require_once 'config/config.php';
require_once 'config/database.php';

$users = Database::fetchAll("SELECT id, nama, email, role, program_studi FROM users WHERE role = 'kaprodi'");
echo "USERS KAPRODI:\n";
print_r($users);

$pengajuan = Database::fetchAll("SELECT id, mahasiswa_id, nama_pemohon, program_studi FROM pengunduran_diri");
echo "\nPENGAJUAN:\n";
print_r($pengajuan);

$mahasiswa = Database::fetchAll("SELECT id, nama, program_studi FROM mahasiswa");
echo "\nMAHASISWA:\n";
print_r($mahasiswa);
