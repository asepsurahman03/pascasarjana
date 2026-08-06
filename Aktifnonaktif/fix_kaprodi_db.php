<?php
require_once 'config/config.php';
require_once 'config/database.php';

Database::query("UPDATE users SET program_studi = 'S1 - Sistem Informasi' WHERE id = 12");
Database::query("UPDATE users SET program_studi = 'S1 - Teknik Elektro' WHERE id = 13");
echo "Updated kaprodi records.\n";
