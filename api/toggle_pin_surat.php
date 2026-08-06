<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$user = getCurrentUser();

if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID tidak valid']);
    exit;
}

// Cek kepemilikan jika bukan super_admin
if (!isSuperAdmin()) {
    $surat = dbQueryOne("SELECT id FROM surat WHERE id=? AND prodi_id=?", [$id, $user['prodi_id']]);
    if (!$surat) {
        echo json_encode(['ok' => false, 'error' => 'Akses ditolak']);
        exit;
    }
}

// Balikkan status is_pinned
dbExecute("UPDATE surat SET is_pinned = NOT is_pinned WHERE id=?", [$id]);
$newState = dbQueryOne("SELECT is_pinned FROM surat WHERE id=?", [$id])['is_pinned'];

echo json_encode(['ok' => true, 'is_pinned' => (bool)$newState]);
