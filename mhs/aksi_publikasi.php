<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Cek autentikasi mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    setFlash('error', 'Akses ditolak.');
    header('Location: ../login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: penelitian');
    exit;
}

$action = $_POST['action'] ?? '';
$mhsId = $_POST['mahasiswa_id'] ?? $_SESSION['user_id'];
$pdo = getDb();

if ($action === 'insert') {
    $judul   = trim($_POST['judul_artikel'] ?? '');
    $dosenId = !empty($_POST['dosen_id']) ? (int)$_POST['dosen_id'] : null;
    $dosen   = trim($_POST['dosen_pendamping'] ?? '');
    $rekan   = trim($_POST['rekan_penulis'] ?? '');
    $status  = trim($_POST['status_publikasi'] ?? 'Publish');
    $link    = trim($_POST['link_artikel'] ?? '');
    $doi     = trim($_POST['doi'] ?? '');
    $abstrak = trim($_POST['abstrak'] ?? '');
    $katakunci = trim($_POST['kata_kunci'] ?? '');
    $namaJurnal = trim($_POST['nama_jurnal'] ?? '');
    $tahun   = !empty($_POST['tahun_terbit']) ? (int)$_POST['tahun_terbit'] : null;
    $referensi = trim($_POST['referensi'] ?? '');
    $volume = trim($_POST['volume'] ?? '');
    $nomorTerbit = trim($_POST['nomor_terbit'] ?? '');
    $halaman = trim($_POST['halaman'] ?? '');

    // Jika pilih dari dropdown, ambil nama dosen dari DB
    if ($dosenId) {
        $dosenRow = dbQueryOne("SELECT nama FROM dosen WHERE id=?", [$dosenId]);
        if ($dosenRow) $dosen = $dosenRow['nama'];
    } elseif (!empty($_POST['dosen_pendamping_manual'])) {
        $dosen = trim($_POST['dosen_pendamping_manual']);
    }

    if (empty($judul)) {
        setFlash('error', 'Judul artikel wajib diisi.');
        header('Location: penelitian');
        exit;
    }

    $uploadDir = __DIR__ . '/../uploads/sidang/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileJurnal = null;
    $fileBukti  = null;

    $safeJudul = preg_replace('/[^a-zA-Z0-9\-_]/', '_', substr($judul, 0, 30));
    $timestamp = time();

    if (isset($_FILES['file_jurnal']) && $_FILES['file_jurnal']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['file_jurnal']['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['php','exe','sh','bat'])) $ext = 'txt';
        $newName = "MHS_{$mhsId}_Jurnal_{$safeJudul}_{$timestamp}.{$ext}";
        if (move_uploaded_file($_FILES['file_jurnal']['tmp_name'], $uploadDir . $newName)) {
            $fileJurnal = 'uploads/sidang/' . $newName;
        }
    }

    if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['file_bukti']['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['php','exe','sh','bat'])) $ext = 'txt';
        $newName = "MHS_{$mhsId}_Bukti_{$safeJudul}_{$timestamp}.{$ext}";
        if (move_uploaded_file($_FILES['file_bukti']['tmp_name'], $uploadDir . $newName)) {
            $fileBukti = 'uploads/sidang/' . $newName;
        }
    }

    $kategori = trim($_POST['kategori_publikasi'] ?? 'Lainnya');

    $stmt = $pdo->prepare("INSERT INTO mahasiswa_publikasi 
        (mahasiswa_id, dosen_id, judul_artikel, kategori_publikasi, dosen_pendamping, rekan_penulis, status_publikasi,
         link_artikel, doi, abstrak, kata_kunci, nama_jurnal, volume, nomor_terbit, halaman, 
         tahun_terbit, referensi, file_jurnal, file_bukti_bayar, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$mhsId, $dosenId, $judul, $kategori ?: 'Lainnya', $dosen, $rekan, $status, $link, $doi ?: null,
                    $abstrak ?: null, $katakunci ?: null, $namaJurnal ?: null, $volume ?: null, $nomorTerbit ?: null, $halaman ?: null,
                    $tahun, $referensi ?: null, $fileJurnal, $fileBukti]);

    setFlash('success', 'Publikasi berhasil ditambahkan ke portofolio Anda.');
    header('Location: penelitian');
    exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    
    // Verifikasi kepemilikan
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa_publikasi WHERE id=? AND mahasiswa_id=?");
    $stmt->execute([$id, $mhsId]);
    $pub = $stmt->fetch();

    if ($pub) {
        // Hapus file fisik jika ada
        if (!empty($pub['file_jurnal']) && file_exists(__DIR__ . '/../' . $pub['file_jurnal'])) {
            unlink(__DIR__ . '/../' . $pub['file_jurnal']);
        }
        if (!empty($pub['file_bukti_bayar']) && file_exists(__DIR__ . '/../' . $pub['file_bukti_bayar'])) {
            unlink(__DIR__ . '/../' . $pub['file_bukti_bayar']);
        }

        $pdo->prepare("DELETE FROM mahasiswa_publikasi WHERE id=?")->execute([$id]);
        setFlash('success', 'Publikasi berhasil dihapus.');
    } else {
        setFlash('error', 'Publikasi tidak ditemukan atau Anda tidak memiliki akses.');
    }
    
    header('Location: penelitian');
    exit;
}

header('Location: penelitian');
exit;
