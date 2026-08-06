<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'ID tidak valid']);
    exit;
}

$user = getCurrentUser();
$surat = dbQueryOne("SELECT id, prodi_id FROM surat WHERE id=?", [$id]);
if (!$surat) {
    echo json_encode(['ok' => false, 'error' => 'Surat tidak ditemukan']);
    exit;
}

// Cek hak akses
if (!isSuperAdmin() && $surat['prodi_id'] != $user['prodi_id']) {
    echo json_encode(['ok' => false, 'error' => 'Akses ditolak']);
    exit;
}

// Hapus data terkait surat (menghindari error foreign key jika tidak ada cascade)
// Hapus surat utama
try {
    dbExecute("DELETE FROM surat WHERE id=?", [$id]);
    
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
