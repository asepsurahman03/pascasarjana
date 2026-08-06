<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
    header('Location: ../login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: penelitian');
    exit;
}

$action  = $_POST['action'] ?? '';
$dosenId = (int)($_POST['dosen_id'] ?? $_SESSION['user_id']);
$pdo     = getDB();

// Verify dosen ID matches session
$dosenRow = dbQueryOne("SELECT id FROM dosen WHERE id = ?", [$dosenId]);
if (!$dosenRow) {
    // Try to find by user_id
    $userRow = dbQueryOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    if ($userRow) {
        $dosenRow = dbQueryOne("SELECT id FROM dosen WHERE nidn = ? OR email = ?", [$userRow['username'], $userRow['email'] ?? '']);
    }
    if (!$dosenRow) {
        setFlash('error', 'Data dosen tidak ditemukan.');
        header('Location: penelitian');
        exit;
    }
    $dosenId = $dosenRow['id'];
}

// ─── INSERT ───────────────────────────────────────────────────────────────────
if ($action === 'insert') {
    $judul   = trim($_POST['judul_artikel'] ?? '');
    $jurnal  = trim($_POST['nama_jurnal'] ?? '');
    $kw      = trim($_POST['kata_kunci'] ?? '');
    $doi     = trim($_POST['doi'] ?? '');
    $link    = trim($_POST['link_artikel'] ?? '');
    $status  = trim($_POST['status_publikasi'] ?? 'Publish');
    $abstrak = trim($_POST['abstrak'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $tahun   = !empty($_POST['tahun_terbit']) ? (int)$_POST['tahun_terbit'] : null;
    $ref     = trim($_POST['referensi'] ?? '');

    if (empty($judul)) {
        setFlash('error', 'Judul artikel wajib diisi.');
        header('Location: penelitian');
        exit;
    }

    // Handle file upload
    $uploadDir  = __DIR__ . '/../uploads/publikasi_dosen/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $fileJurnal = null;
    if (isset($_FILES['file_jurnal']) && $_FILES['file_jurnal']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file_jurnal']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['php','exe','sh','bat'])) $ext = 'txt';
        $safeJudul = preg_replace('/[^a-zA-Z0-9\-_]/', '_', substr($judul, 0, 30));
        $newName   = "DOSEN_{$dosenId}_{$safeJudul}_" . time() . ".{$ext}";
        if (move_uploaded_file($_FILES['file_jurnal']['tmp_name'], $uploadDir . $newName)) {
            $fileJurnal = 'uploads/publikasi_dosen/' . $newName;
        }
    }

    $pdo->prepare("INSERT INTO dosen_publikasi 
        (dosen_id, judul_artikel, nama_jurnal, kata_kunci, doi, link_artikel, status_publikasi, 
         abstrak, penulis, tahun_terbit, referensi, file_jurnal, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())")
        ->execute([$dosenId, $judul, $jurnal ?: null, $kw ?: null, $doi ?: null, $link ?: null,
                   $status, $abstrak ?: null, $penulis ?: null, $tahun, $ref ?: null, $fileJurnal]);

    setFlash('success', 'Publikasi berhasil ditambahkan ke portofolio Anda.');
    header('Location: penelitian');
    exit;
}

// ─── DELETE ───────────────────────────────────────────────────────────────────
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);

    $pub = dbQueryOne("SELECT * FROM dosen_publikasi WHERE id = ? AND dosen_id = ?", [$id, $dosenId]);
    if ($pub) {
        if (!empty($pub['file_jurnal']) && file_exists(__DIR__ . '/../' . $pub['file_jurnal'])) {
            unlink(__DIR__ . '/../' . $pub['file_jurnal']);
        }
        $pdo->prepare("DELETE FROM dosen_publikasi WHERE id = ?")->execute([$id]);
        setFlash('success', 'Publikasi berhasil dihapus.');
    } else {
        setFlash('error', 'Publikasi tidak ditemukan atau Anda tidak memiliki akses.');
    }

    header('Location: penelitian');
    exit;
}

header('Location: penelitian');
exit;
