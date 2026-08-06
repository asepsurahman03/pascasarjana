<?php
require 'includes/functions.php';
try {
    dbExecute("ALTER TABLE riwayat_lampiran ADD COLUMN jenis ENUM('tesis', 'proposal') NOT NULL DEFAULT 'tesis' AFTER prodi_id");
    echo "Column 'jenis' added successfully.";
} catch (Exception $e) {
    echo "Error or already exists: " . $e->getMessage();
}
