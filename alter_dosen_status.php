<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

try {
    dbExecute("ALTER TABLE dosen ADD COLUMN status ENUM('Aktif', 'Tidak Aktif') DEFAULT 'Aktif'");
    echo "Column 'status' added successfully.";
} catch (Exception $e) {
    echo "Error (it may already exist): " . $e->getMessage();
}
