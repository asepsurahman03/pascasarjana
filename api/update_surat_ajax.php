<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$isi_surat = $data['isi_surat'] ?? '';

if (!$id) {
    echo json_encode(['error' => 'ID Surat tidak valid']);
    exit;
}

$user = getCurrentUser();
$surat = dbQueryOne("SELECT * FROM surat WHERE id=?", [$id]);
if (!$surat) {
    echo json_encode(['error' => 'Surat tidak ditemukan']);
    exit;
}

if (!isSuperAdmin() && $surat['prodi_id'] != $user['prodi_id']) {
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$prompt = trim($data['prompt'] ?? '');
$ai_reply = trim($data['ai_reply'] ?? 'Revisi telah diterapkan.');
if ($prompt) {
    dbExecute("INSERT INTO surat_chat (surat_id, role, content) VALUES (?, 'user', ?)", [$id, $prompt]);
}
$chatId = 0;
if ($isi_surat) {
    dbExecute("INSERT INTO surat_chat (surat_id, role, content, ai_reply) VALUES (?, 'assistant', ?, ?)", [$id, $isi_surat, $ai_reply]);
    $chatId = (int)dbQueryOne("SELECT LAST_INSERT_ID() as lid")['lid'];
}
dbExecute("UPDATE surat SET isi_surat=? WHERE id=?", [$isi_surat, $id]);
echo json_encode(['ok' => true, 'chat_id' => $chatId]);
