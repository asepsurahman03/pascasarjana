<?php
try {
    $p = new PDO('mysql:host=localhost;dbname=pascasarjana_unp;charset=utf8','root','');
    $p->exec('ALTER TABLE mahasiswa ADD COLUMN email_pribadi VARCHAR(150) NULL AFTER email');
    echo "Column email_pribadi added successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
