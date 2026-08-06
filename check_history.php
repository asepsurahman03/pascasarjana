<?php
session_start();
require 'config/database.php';
require 'includes/functions.php';

if (!isLoggedIn()) {
    die('Silakan login dulu.');
}

$user = getCurrentUser();
$isSuper = isSuperAdmin();

echo "<h2>Debug Info</h2>";
echo "<b>User ID:</b> " . $_SESSION['user_id'] . "<br>";
echo "<b>Role:</b> " . ($_SESSION['role'] ?? 'TIDAK ADA SESSION role') . "<br>";
echo "<b>Is Super Admin:</b> " . ($isSuper ? 'YES' : 'NO') . "<br>";
echo "<b>prodi_id:</b> " . ($user['prodi_id'] ?? 'NULL') . "<br>";
echo "<br>";

if ($isSuper) {
    $rows = dbQuery("SELECT id, jenis_surat, perihal, created_at, is_pinned, prodi_id FROM surat ORDER BY created_at DESC LIMIT 10");
    echo "<b>Query (Super Admin - semua surat):</b><br>";
} else {
    $rows = dbQuery("SELECT id, jenis_surat, perihal, created_at, is_pinned, prodi_id FROM surat WHERE prodi_id = ? ORDER BY created_at DESC LIMIT 10", [$user['prodi_id']]);
    echo "<b>Query (Admin Prodi - prodi_id=" . ($user['prodi_id'] ?? 'NULL') . "):</b><br>";
}

echo "<b>Jumlah hasil:</b> " . count($rows) . "<br><br>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">ID</th><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Prodi ID</th><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Jenis</th><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Perihal</th><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Created At</th></tr>";
foreach ($rows as $r) {
    echo "<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4 py-3 px-4">{$r['id']}</td><td class="py-3 px-4 py-3 px-4">{$r['prodi_id']}</td><td class="py-3 px-4 py-3 px-4">{$r['jenis_surat']}</td><td class="py-3 px-4 py-3 px-4">{$r['perihal']}</td><td class="py-3 px-4 py-3 px-4">{$r['created_at']}</td></tr>";
}
echo "</table>";
