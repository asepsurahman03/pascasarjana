<?php
/**
 * API: Upload surat ke Google Drive
 * POST body: { id: suratId }
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/google_drive_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['error' => 'Unauthorized']); exit; }

$data    = json_decode(file_get_contents('php://input'), true);
$suratId = (int)($data['id'] ?? 0);

if (!$suratId) { echo json_encode(['error' => 'ID surat tidak valid.']); exit; }

// Ambil data surat + prodi
$surat = dbQueryOne("
    SELECT s.*, p.nama AS prodi_nama, p.kode AS prodi_kode, p.prefix_surat, p.kota_surat,
           p.kaprodi AS nama_kaprodi
    FROM surat s JOIN prodi p ON p.id = s.prodi_id
    WHERE s.id = ?", [$suratId]);

if (!$surat) { echo json_encode(['error' => 'Surat tidak ditemukan.']); exit; }

// Cek akses
$user = getCurrentUser();
if (!isSuperAdmin() && $surat['prodi_id'] != $user['prodi_id']) {
    echo json_encode(['error' => 'Akses ditolak.']); exit;
}

// Ambil konfigurasi Drive dari settings
$serviceAccountJson = getSetting('google_drive_service_account');
$folderId           = getSetting('google_drive_folder_id') ?: '1hLAII1Ba-mZ6ORJA97IjuYqTpQqwns3b';

if (empty($serviceAccountJson)) {
    echo json_encode(['error' => 'Konfigurasi Google Drive belum diatur. Buka Pengaturan → Google Drive dan upload Service Account JSON Key.']);
    exit;
}

try {
    $drive = GoogleDriveHelper::fromJson($serviceAccountJson);
} catch (Exception $e) {
    echo json_encode(['error' => 'Service Account tidak valid: ' . $e->getMessage()]);
    exit;
}

// Bangun konten HTML surat yang rapi untuk di-upload
$isiSurat   = $surat['isi_surat'] ?? '';
$nomorSurat = $surat['nomor_surat'] ?? '';
$perihal    = $surat['perihal'] ?? '';
$penerima   = $surat['nama_penerima'] ?? '';
$tanggal    = $surat['tanggal'] ? date('d F Y', strtotime($surat['tanggal'])) : '';
$prodiNama  = $surat['prodi_nama'] ?? '';
$kaprodi    = $surat['nama_kaprodi'] ?? '';
$kota       = $surat['kota_surat'] ?? 'Sukabumi';

// Nama file: NomorSurat_Perihal.html (sanitized)
$safeNomor   = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $nomorSurat);
$safePerihal = preg_replace('/[^a-zA-Z0-9\s\-_]/', '', $perihal);
$safePerihal = trim(preg_replace('/\s+/', '_', substr($safePerihal, 0, 50)));
$filename    = "{$safeNomor}_{$safePerihal}.html";

$nowDate     = date('d/m/Y H:i');

// Generate HTML konten surat (format A4)
$htmlContent = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$nomorSurat} — {$perihal}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: 'Times New Roman', serif; font-size: 12pt; color: #000; background: #fff; }
  .page { max-width: 210mm; margin: 0 auto; padding: 20mm 25mm; min-height: 297mm; }
  .header-info { text-align: center; margin-bottom: 24px; border-bottom: 3px solid #8c0c4c; padding-bottom: 12px; }
  .header-info h1 { font-size: 14pt; font-weight: bold; color: #8c0c4c; }
  .header-info p { font-size: 10pt; color: #555; margin-top: 4px; }
  .nomor-table { width: 100%; margin-bottom: 20px; }
  .nomor-table td { vertical-align: top; font-size: 11pt; padding: 2px 0; }
  .nomor-table td:first-child { width: 140px; }
  .isi-surat { line-height: 1.8; text-align: justify; }
  .isi-surat p { margin-bottom: 10px; }
  .footer-info { margin-top: 40px; font-size: 10pt; color: #888; border-top: 1px solid #ddd; padding-top: 10px; text-align: right; }
  .kota-tanggal { text-align: right; margin-bottom: 30px; }
  .ttd-block { margin-top: 50px; width: 200px; }
  .ttd-block p { margin-bottom: 60px; }
</style>
</head>
<body>
<div class="page">
  <div class="header-info">
    <h1>Program Studi {$prodiNama}</h1>
    <p>Universitas Nusa Putra — {$kota}</p>
  </div>

  <table class="nomor-table">
    <tr><td>Nomor</td><td>: {$nomorSurat}</td></tr>
    <tr><td>Lampiran</td><td>: {$surat['lampiran']}</td></tr>
    <tr><td>Perihal</td><td>: {$perihal}</td></tr>
  </table>

  <div class="kota-tanggal">{$kota}, {$tanggal}</div>

  <div style="margin-bottom: 20px;">
    <p>Kepada Yth.</p>
    <p><strong>{$penerima}</strong></p>
    <p>di Tempat</p>
  </div>

  <div style="margin-bottom: 12px;">Dengan hormat,</div>

  <div class="isi-surat">
    {$isiSurat}
  </div>

  <div class="kota-tanggal" style="margin-top:40px;">
    <p>{$kota}, {$tanggal}</p>
    <p>Ketua Program Studi</p>
    <div style="height:60px;"></div>
    <p><strong>{$kaprodi}</strong></p>
  </div>

  <div class="footer-info">
    Diarsipkan secara digital — Sistem Surat Pascasarjana Universitas Nusa Putra<br>
    Diunggah: {$nowDate}
  </div>
</div>
</body>
</html>
HTML;

try {
    $existingFileId = $surat['drive_file_id'] ?? null;
    $result = $drive->uploadHtml($folderId, $filename, $htmlContent, $existingFileId ?: null);

    // Simpan file_id dan URL ke database
    dbExecute(
        "UPDATE surat SET drive_file_id=?, drive_url=?, drive_uploaded_at=NOW() WHERE id=?",
        [$result['file_id'], $result['url'], $suratId]
    );

    logActivity('Upload Drive', 'surat', "$nomorSurat → Drive ID: {$result['file_id']}");

    echo json_encode([
        'ok'      => true,
        'file_id' => $result['file_id'],
        'url'     => $result['url'],
        'filename' => $filename,
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
