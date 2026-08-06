<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';

$kaprodi = Database::fetchAll("SELECT id, nama, email, role, program_studi FROM users WHERE role = 'kaprodi'");
print_r($kaprodi);
