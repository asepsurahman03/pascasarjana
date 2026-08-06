<?php
require 'config/database.php';
getDB()->query("UPDATE mahasiswa SET angkatan = 2026 WHERE nim LIKE '2026%'");
echo "Updated angkatan to 2026.";
