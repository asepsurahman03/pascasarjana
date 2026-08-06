<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();

$hash = password_hash('password', PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE users SET password_hash = ?");
$stmt->execute([$hash]);

echo "Updated " . $stmt->rowCount() . " users to password 'password'.\n";
