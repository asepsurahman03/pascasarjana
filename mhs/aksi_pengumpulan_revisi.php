<?php
require_once '../includes/functions.php';

// Pastikan yang akses adalah mahasiswa yang login
if (!isLoggedIn() || $_SESSION['role'] !== 'mahasiswa') {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mahasiswa_id = $_SESSION['user_id'];
    $jenis_revisi = $_POST['jenis_revisi'] ?? '';
    
    // Validasi input
    if (empty($jenis_revisi)) {
        die("Jenis revisi tidak boleh kosong.");
    }
    
    // Folder tujuan upload (disesuaikan dengan kebutuhan, misal di folder uploads/revisi di luar htdocs/root atau di dalam web root)
    $upload_dir = '../uploads/revisi/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_dokumen = '';
    $file_persetujuan = '';
    
    // Proses File Dokumen (Naskah)
    if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['file_dokumen']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'pdf') {
            die("File dokumen harus berformat PDF.");
        }
        if ($_FILES['file_dokumen']['size'] > 10 * 1024 * 1024) { // 10MB
            die("File dokumen terlalu besar.");
        }
        
        $filename = "Dokumen_" . $jenis_revisi . "_" . $mahasiswa_id . "_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $upload_dir . $filename)) {
            $file_dokumen = $filename;
        }
    } else {
        die("Gagal mengunggah file dokumen revisi.");
    }

    // Proses File Persetujuan (Bukti ACC)
    if (isset($_FILES['file_persetujuan']) && $_FILES['file_persetujuan']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['file_persetujuan']['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'pdf') {
            die("File persetujuan harus berformat PDF.");
        }
        if ($_FILES['file_persetujuan']['size'] > 5 * 1024 * 1024) { // 5MB
            die("File persetujuan terlalu besar.");
        }
        
        $filename = "ACC_" . $jenis_revisi . "_" . $mahasiswa_id . "_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['file_persetujuan']['tmp_name'], $upload_dir . $filename)) {
            $file_persetujuan = $filename;
        }
    } else {
        die("Gagal mengunggah lembar persetujuan.");
    }
    
    // Simpan ke database
    $query = "INSERT INTO pengumpulan_revisi (mahasiswa_id, jenis_revisi, file_dokumen, file_persetujuan, status) 
              VALUES (?, ?, ?, ?, 'Pending')";
    
    $params = [$mahasiswa_id, $jenis_revisi, $file_dokumen, $file_persetujuan];
    
    if (dbExecute($query, $params)) {
        // Jika sukses, kembalikan ke halaman status pendaftaran / dashboard
        echo "<script>
            alert('Berhasil mengunggah dokumen revisi. Silakan tunggu verifikasi admin.');
            window.location.href = 'status_sidang.php';
        </script>";
    } else {
        echo "Gagal menyimpan data ke database.";
    }
} else {
    echo "Metode tidak diizinkan.";
}
?>
