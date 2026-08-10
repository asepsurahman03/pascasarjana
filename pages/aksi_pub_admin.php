<?php
/**
 * Admin Action Handler — Hapus Publikasi (Mahasiswa atau Dosen)
 * POST: type=mhs|dosen, id=int
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/publikasi_mahasiswa');
    exit;
}

$type   = $_POST['type'] ?? '';
$id     = (int)($_POST['id'] ?? 0);
$back   = $_POST['back'] ?? ($type === 'dosen' ? 'penelitian_dosen' : 'publikasi_mahasiswa');
$ref    = BASE_URL . '/pages/' . $back;

if (!$id || !in_array($type, ['mhs', 'dosen'])) {
    setFlash('error', 'Parameter tidak valid.');
    header('Location: ' . $ref);
    exit;
}

if ($type === 'mhs') {
    $row = dbQueryOne("SELECT * FROM mahasiswa_publikasi WHERE id=?", [$id]);
    if (!$row) { setFlash('error', 'Data tidak ditemukan.'); header('Location: '.$ref); exit; }
    // Hapus file fisik jika ada
    foreach (['file_jurnal', 'file_bukti_bayar'] as $col) {
        if (!empty($row[$col]) && file_exists(__DIR__ . '/../' . $row[$col])) {
            @unlink(__DIR__ . '/../' . $row[$col]);
        }
    }
    dbExecute("DELETE FROM mahasiswa_publikasi WHERE id=?", [$id]);
    setFlash('success', 'Publikasi mahasiswa berhasil dihapus.');
} else {
    $row = dbQueryOne("SELECT * FROM dosen_publikasi WHERE id=?", [$id]);
    if (!$row) { setFlash('error', 'Data tidak ditemukan.'); header('Location: '.$ref); exit; }
    if (!empty($row['file_jurnal']) && file_exists(__DIR__ . '/../' . $row['file_jurnal'])) {
        @unlink(__DIR__ . '/../' . $row['file_jurnal']);
    }
    dbExecute("DELETE FROM dosen_publikasi WHERE id=?", [$id]);
    setFlash('success', 'Publikasi dosen berhasil dihapus.');
}

header('Location: ' . $ref);
exit;
