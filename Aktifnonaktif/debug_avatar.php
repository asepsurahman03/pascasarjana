<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/models/User.php';

$user = currentUser();
$userId = $user['id'];
$freshUser = Database::fetchOne("SELECT id, nama, email, role, avatar, auth_provider, program_studi FROM users WHERE id = ?", [$userId]);
$avatar = $freshUser['avatar'] ?? null;

echo "<h3>Test Avatar Render</h3>";
echo "<p>Avatar URL: <code>" . htmlspecialchars($avatar ?? 'NULL') . "</code></p>";
echo "<p>empty() result: " . (empty($avatar) ? 'TRUE (kosong)' : 'FALSE (ada isi)') . "</p>";

if (!empty($avatar)) {
    echo "<p><strong>Gambar:</strong></p>";
    echo '<img src="' . htmlspecialchars($avatar) . '" style="width:80px;height:80px;border-radius:50%;border:2px solid #ccc;">';
} else {
    echo "<p style='color:red'>Avatar kosong/null, inisial ditampilkan</p>";
}
