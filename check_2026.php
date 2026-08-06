<?php
require 'config/database.php';
$m = getDB()->query("SELECT nim, nama, angkatan FROM mahasiswa WHERE nim LIKE '%2026%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($m);
