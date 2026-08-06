<?php
/**
 * API: Autocomplete mahasiswa/dosen untuk form surat (AJAX)
 * GET /api/autocomplete_penerima.php?q=nama&prodi_id=1&type=mahasiswa
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode([]); exit; }

$q       = trim($_GET['q'] ?? '');
$prodiId = (int)($_GET['prodi_id'] ?? 0);
$type    = $_GET['type'] ?? 'mahasiswa';

if (strlen($q) < 2) { echo json_encode([]); exit; }

$like = "%$q%";
$results = [];

if ($type === 'mahasiswa') {
    $where  = '(nama LIKE ? OR nim LIKE ?)';
    $params = [$like, $like];
    if ($prodiId) { $where .= ' AND prodi_id = ?'; $params[] = $prodiId; }
    $rows = dbQuery("SELECT id, nim, nama, no_hp, email FROM mahasiswa WHERE $where LIMIT 8", $params);
    foreach ($rows as $r) {
        $results[] = ['id' => $r['id'], 'label' => $r['nama'] . ' (' . $r['nim'] . ')', 'nim_nidn' => $r['nim'], 'nama' => $r['nama'], 'hp' => $r['no_hp'] ?? ''];
    }
} else {
    // Dosen — cari dari field dosen_pembimbing mahasiswa (belum tabel dosen terpisah)
    $rows = dbQuery("SELECT DISTINCT dosen_pembimbing as nama FROM mahasiswa WHERE dosen_pembimbing LIKE ? AND dosen_pembimbing != '' LIMIT 8", [$like]);
    foreach ($rows as $r) {
        $results[] = ['id' => null, 'label' => $r['nama'], 'nim_nidn' => '', 'nama' => $r['nama'], 'hp' => ''];
    }
}

echo json_encode($results);
