<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Coba panggil PHPMailer jika autoload ada
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: daftar_sidang');
    exit;
}

$mhsId   = (int)($_POST['mahasiswa_id'] ?? 0);
$jenis   = trim($_POST['jenis_sidang'] ?? 'Sidang Tesis');

// Hitung dan Upload berkas administratif saja
$berkasFields = [
    'berkas_persetujuan', 'berkas_khs', 'berkas_bebas_perpus', 
    'berkas_buku_sumbangan', 'berkas_bebas_admin', 'berkas_foto', 
    'berkas_draft_tesis', 'berkas_code_program', 'berkas_presentasi'
];

$berkasOk    = 0;
$berkasTotal = count($berkasFields);
$berkasData  = [];
$uploadedFiles = [];

$uploadDir = __DIR__ . '/../uploads/sidang/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Helper untuk membersihkan string agar aman dijadikan nama file
$safeNim = preg_replace('/[^a-zA-Z0-9\-_]/', '_', trim($_POST['nim'] ?? "MHS_$mhsId"));
$safeNama = preg_replace('/[^a-zA-Z0-9\-_]/', '_', trim($_POST['nama'] ?? 'TanpaNama'));

$fieldLabels = [
    'berkas_persetujuan' => 'Persetujuan_Pembimbing',
    'berkas_khs' => 'KHS_Smt_1-3',
    'berkas_bebas_perpus' => 'Bebas_Perpus',
    'berkas_buku_sumbangan' => 'Bukti_Buku_Sumbangan',
    'berkas_bebas_admin' => 'Bebas_Administrasi',
    'berkas_foto' => 'Foto_Background_Merah',
    'berkas_draft_tesis' => 'Draft_Tesis',
    'berkas_code_program' => 'Code_Program',
    'berkas_presentasi' => 'File_Presentasi'
];

foreach ($berkasFields as $field) {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES[$field]['tmp_name'];
        $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
        // Keamanan sederhana
        if (in_array(strtolower($ext), ['php','exe','sh','bat'])) $ext = 'txt';
        
        $docName = $fieldLabels[$field] ?? $field;
        // Format rapi: NIM - Nama - Jenis_Dokumen.ext
        $newName = $safeNim . '-' . $safeNama . '-' . $docName . '.' . $ext;
        $dest = $uploadDir . $newName;
        
        if (move_uploaded_file($tmp, $dest)) {
            $berkasData[$field] = 'uploads/sidang/' . $newName;
            $berkasOk++;
            $uploadedFiles[] = $dest;
        } else {
            $berkasData[$field] = null;
        }
    } else {
        $berkasData[$field] = null;
    }
}

try {
    $pdo = getDb();
    // Gunakan transaction untuk memastikan atomicity
    $pdo->beginTransaction();

    // Cek apakah sudah ada pendaftaran aktif
    $existing = $pdo->prepare(
        "SELECT id FROM pendaftaran_sidang WHERE mahasiswa_id=? AND status IN ('Pending','Diverifikasi')"
    );
    $existing->execute([$mhsId]);

    if ($existing->rowCount() > 0) {
        setFlash('warning', 'Anda sudah memiliki pendaftaran sidang yang sedang diproses.');
        echo "EXISTING";
        exit;
    }

    $sql = "INSERT INTO pendaftaran_sidang (
        mahasiswa_id, jenis_sidang, angkatan, email, no_hp,
        pembimbing1, pembimbing2, judul_tesis,
        berkas_persetujuan,
        berkas_khs, berkas_bebas_perpus, berkas_buku_sumbangan,
        berkas_bebas_admin, berkas_foto, berkas_draft_tesis,
        berkas_code_program, berkas_presentasi,
        berkas_ok, berkas_total, status, created_at
    ) VALUES (
        ?, ?, ?, ?, ?,
        ?, ?, ?,
        ?,
        ?, ?, ?,
        ?, ?, ?,
        ?, ?,
        ?, ?, 'Pending', NOW()
    )";

    $params = [
        $mhsId,
        $jenis,
        trim($_POST['angkatan'] ?? ''),
        trim($_POST['email']    ?? ''),
        trim($_POST['no_hp']    ?? ''),
        trim($_POST['pembimbing1']   ?? ''),
        trim($_POST['pembimbing2']   ?? ''),
        trim($_POST['judul_tesis']   ?? ''),
        $berkasData['berkas_persetujuan'],
        $berkasData['berkas_khs'],
        $berkasData['berkas_bebas_perpus'],
        $berkasData['berkas_buku_sumbangan'],
        $berkasData['berkas_bebas_admin'],
        $berkasData['berkas_foto'],
        $berkasData['berkas_draft_tesis'],
        $berkasData['berkas_code_program'],
        $berkasData['berkas_presentasi'],
        $berkasOk,
        $berkasTotal,
    ];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pendaftaranId = $pdo->lastInsertId();

    // Tautkan semua publikasi di portofolio dengan pendaftaran sidang ini
    $pdo->prepare("UPDATE mahasiswa_publikasi SET pendaftaran_sidang_id=? WHERE mahasiswa_id=? AND pendaftaran_sidang_id IS NULL")
        ->execute([$pendaftaranId, $mhsId]);

    $pdo->commit();

    // Update judul_tesis di tabel mahasiswa jika ada
    if (!empty($_POST['judul_tesis'])) {
        $pdo->prepare("UPDATE mahasiswa SET judul_tesis=? WHERE id=?")
            ->execute([trim($_POST['judul_tesis']), $mhsId]);
    }

    // Buat notifikasi untuk admin
    $mhsRow = dbQueryOne("SELECT nama, nim FROM mahasiswa WHERE id=?", [$mhsId]);
    $namaDisplay = $mhsRow ? $mhsRow['nama'] : "Mahasiswa #$mhsId";
    $pdo->prepare("INSERT INTO notifikasi (judul, pesan, tipe, created_at) VALUES (?, ?, 'info', NOW())")
        ->execute([
            "Pendaftaran Sidang Baru",
            "$namaDisplay mendaftar sidang ($jenis). Berkas diunggah: $berkasOk/$berkasTotal."
        ]);

    // ==== MENGIRIM EMAIL OTOMATIS ====
    $emailMsg = "Namun pengiriman email otomatis gagal (Kredensial SMTP belum diset). Admin tetap bisa melihat file Anda di sistem.";
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mail = new PHPMailer(true);
        try {
            // Konfigurasi SMTP Dummy (Akan diubah oleh admin nanti)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sistem.pascasarjana.dummy@gmail.com'; // TODO: Ganti dengan email aktif
            $mail->Password   = 'app_password_dummy'; // TODO: Ganti dengan App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
    
            $mail->setFrom('sistem.pascasarjana.dummy@gmail.com', 'Sistem Pascasarjana Nusa Putra');
            $mail->addAddress('sidang@nusaputra.ac.id', 'Admin Sidang');
    
            // Attachments
            foreach ($uploadedFiles as $file) {
                $mail->addAttachment($file);
            }
    
            $mail->isHTML(true);
            $mail->Subject = 'Pendaftaran Sidang Tesis Baru - ' . trim($_POST['nama'] ?? '') . ' (' . trim($_POST['nim'] ?? '') . ')';
            $mail->Body    = 'Yth. Admin Administrasi Sidang Pascasarjana,<br><br>
                              Terdapat pendaftaran Sidang Tesis baru dengan detail sebagai berikut:<br>
                              <b>Nama:</b> ' . trim($_POST['nama'] ?? '') . '<br>
                              <b>NIM:</b> ' . trim($_POST['nim'] ?? '') . '<br>
                              <b>Judul Tesis:</b> ' . trim($_POST['judul_tesis'] ?? '') . '<br><br>
                              Semua berkas persyaratan fisik telah diunggah dan terlampir pada email ini serta tersimpan otomatis di sistem SIAKAD.<br><br>
                              Terima kasih.';
    
            // Silenced send so it doesn't break if credentials are wrong
            if (@$mail->send()) {
                $emailMsg = "Email beserta lampiran berhasil dikirim otomatis ke sidang@nusaputra.ac.id.";
            }
        } catch (Exception $e) {
            // Biarkan pesan default error SMTP
        }
    }

    setFlash('success', 'Pendaftaran sidang berhasil disimpan! ' . $emailMsg);
    echo "OK";
    exit;

} catch (Exception $e) {
    setFlash('error', 'Gagal menyimpan pendaftaran: ' . $e->getMessage());
    echo "ERROR";
    exit;
}
