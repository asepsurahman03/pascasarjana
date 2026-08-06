<?php
require 'config/database.php';
$db = getDB();

// Fix semua password
$hash = password_hash('password', PASSWORD_BCRYPT);
$hash2 = password_hash('admin123', PASSWORD_BCRYPT);

$db->prepare("UPDATE users SET password_hash=? WHERE username IN ('admin','superadmin','admin_mif','admin_mm')")->execute([$hash]);

// Test login
$s = $db->prepare("SELECT * FROM users WHERE username=? OR email=? LIMIT 1");
$s->execute(['admin','admin@gmail.com']);
$u = $s->fetch();

if($u) {
    $ok = password_verify('password', $u['password_hash']);
    echo "<div style='font-family:monospace;background:var(--color-bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm);color:var(--color-text);padding:20px;border-radius:12px'>";
    echo "<h2 style='color:#22c55e'>✅ Setup Berhasil!</h2>";
    echo "<p>User: <strong style='color:var(--color-primary)'>{$u['username']}</strong> | Email: <strong style='color:var(--color-primary)'>{$u['email']}</strong></p>";
    echo "<p>Password verify: <strong style='color:" . ($ok ? '#22c55e' : '#f87171') . "'>" . ($ok ? '✅ VALID' : '❌ GAGAL') . "</strong></p>";
    echo "<hr style='border-color:var(--color-border);margin:15px 0'>";
    echo "<h3>Kredensial Login:</h3>";
    echo "<ul style='color:var(--color-text-muted)'><li>username: <code style='color:var(--color-primary)'>admin</code> | password: <code style='color:var(--color-primary)'>password</code></li>";
    echo "<li>email: <code style='color:var(--color-primary)'>admin@gmail.com</code> | password: <code style='color:var(--color-primary)'>password</code></li></ul>";
    echo "<br><a href='login' style='background:var(--color-primary);color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold'>→ Login Sekarang</a>";
    echo "</div>";
} else {
    echo "<div style='color:red'>❌ User tidak ditemukan</div>";
}
?>
