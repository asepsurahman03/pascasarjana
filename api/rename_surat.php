<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['error' => 'Unauthorized']); exit; }

$data    = json_decode(file_get_contents('php://input'), true);
$id      = (int)($data['id'] ?? 0);
$perihal = trim($data['perihal'] ?? '');

if (!$id || $perihal === '') {
    echo json_encode(['error' => 'Parameter tidak valid.']); exit;
}

$user  = getCurrentUser();
$surat = dbQueryOne("SELECT * FROM surat WHERE id=?", [$id]);
if (!$surat) { echo json_encode(['error' => 'Surat tidak ditemukan.']); exit; }
if (!isSuperAdmin() && $surat['prodi_id'] != $user['prodi_id']) {
    echo json_encode(['error' => 'Akses ditolak.']); exit;
}

dbExecute("UPDATE surat SET perihal=? WHERE id=?", [$perihal, $id]);
logActivity('Rename Surat', 'surat', "ID $id → $perihal");
echo json_encode(['ok' => true, 'perihal' => $perihal]);
