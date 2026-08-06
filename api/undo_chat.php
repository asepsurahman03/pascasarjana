<?php
/**
 * API: Undo/Revert chat ke versi sebelum prompt tertentu.
 * Menghapus semua surat_chat dari chat_id tersebut ke atas (lebih baru),
 * lalu meng-update isi_surat ke versi sebelum prompt itu dikirim.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$suratId  = (int)($data['surat_id'] ?? 0);
$fromIdx  = (int)($data['from_hist_index'] ?? -1); // index di _chatHistory array (0-based)

if (!$suratId || $fromIdx < 0) {
    echo json_encode(['error' => 'Parameter tidak valid.']);
    exit;
}

$user  = getCurrentUser();
$surat = dbQueryOne("SELECT * FROM surat WHERE id=?", [$suratId]);
if (!$surat) {
    echo json_encode(['error' => 'Surat tidak ditemukan.']);
    exit;
}
if (!isSuperAdmin() && $surat['prodi_id'] != $user['prodi_id']) {
    echo json_encode(['error' => 'Akses ditolak.']);
    exit;
}

// Ambil seluruh chat history surat ini terurut ASC
$allChat = dbQuery(
    "SELECT * FROM surat_chat WHERE surat_id=? ORDER BY created_at ASC, id ASC",
    [$suratId]
);

// from_hist_index merujuk pada index bubble di _chatHistory JS
// (yang merupakan flat array semua role).
// Kita perlu tahu chat DB mana yang perlu dihapus.
// fromIdx adalah index pertama yang harus di-undo (termasuk turn user itu sendiri)
// Semua surat_chat dengan index >= fromIdx di-hapus.

if ($fromIdx >= count($allChat)) {
    echo json_encode(['error' => 'Index di luar range.']);
    exit;
}

// ID minimum yang harus dihapus
$deleteFromId = (int)$allChat[$fromIdx]['id'];

// Versi surat SEBELUM prompt ini: ambil content assistant terakhir SEBELUM fromIdx
$prevHtml = '';
for ($i = $fromIdx - 1; $i >= 0; $i--) {
    if ($allChat[$i]['role'] === 'assistant') {
        $prevHtml = $allChat[$i]['content'];
        break;
    }
}

// Jika tidak ada versi sebelumnya, artinya ini prompt pertama — tidak boleh di-undo ke kosong
// Tetap hapus, dan set isi_surat ke konten assistant pertama (versi awal)
// atau kosongkan saja (akan dibiarkan di-handle frontend)

// Hapus semua surat_chat dari index tersebut ke atas
dbExecute(
    "DELETE FROM surat_chat WHERE surat_id=? AND id >= ?",
    [$suratId, $deleteFromId]
);

// Update isi_surat surat ke versi sebelumnya
if ($prevHtml !== '') {
    dbExecute("UPDATE surat SET isi_surat=? WHERE id=?", [$prevHtml, $suratId]);
}

logActivity('Undo Revisi', 'surat', "Surat ID $suratId, undo ke hist index $fromIdx");

echo json_encode([
    'ok'        => true,
    'prev_html' => $prevHtml,
    'deleted_from_index' => $fromIdx,
]);
