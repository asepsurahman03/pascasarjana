<?php
/**
 * API: Autosave konten editor surat
 * POST /api/autosave_surat.php
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['ok'=>false]); exit; }

$d       = json_decode(file_get_contents('php://input'), true);
$suratId = (int)($d['surat_id'] ?? 0) ?: null;
$sKey    = $d['session_key'] ?? '';
$konten  = $d['konten'] ?? '';
$uid     = (int)$_SESSION['user_id'];

if ($suratId) {
    // Update existing
    $existing = dbQueryOne("SELECT id FROM surat_autosave WHERE surat_id=? AND user_id=?", [$suratId, $uid]);
    if ($existing) {
        dbExecute("UPDATE surat_autosave SET konten_html=?, disimpan_pada=NOW() WHERE id=?", [$konten, $existing['id']]);
    } else {
        dbExecute("INSERT INTO surat_autosave(surat_id,user_id,konten_html,session_key) VALUES(?,?,?,?)", [$suratId, $uid, $konten, $sKey]);
    }
} else {
    // New surat — pakai session_key
    $existing = dbQueryOne("SELECT id FROM surat_autosave WHERE session_key=? AND user_id=?", [$sKey, $uid]);
    if ($existing) {
        dbExecute("UPDATE surat_autosave SET konten_html=?, disimpan_pada=NOW() WHERE id=?", [$konten, $existing['id']]);
    } else {
        dbExecute("INSERT INTO surat_autosave(surat_id,user_id,konten_html,session_key) VALUES(?,?,?,?)", [null, $uid, $konten, $sKey]);
    }
}

echo json_encode(['ok'=>true, 'ts'=>date('H:i:s')]);
