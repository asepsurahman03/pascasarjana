<?php
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/config/database.php";
try {
    Database::query("DELETE FROM users WHERE id NOT IN (SELECT user_id FROM mahasiswa WHERE user_id IS NOT NULL) AND role = 'mahasiswa'");
    echo "Cleaned blank users.\n";
} catch(Exception $e) { echo $e->getMessage(); }
