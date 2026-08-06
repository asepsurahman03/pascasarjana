<?php
/**
 * API: Upload blob PDF (Lampiran) ke Google Drive
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/google_drive_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['error' => 'Unauthorized']); exit; }

$riwayatId = (int)($_POST['id'] ?? 0);
$filename = $_POST['filename'] ?? 'Lampiran.pdf';

if (!$riwayatId) { echo json_encode(['error' => 'ID riwayat tidak valid.']); exit; }
if (!isset($_FILES['pdf']) || $_FILES['pdf']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Gagal menerima file PDF dari browser.']); exit;
}

// Ambil data riwayat
$riwayat = dbQueryOne("SELECT * FROM riwayat_lampiran WHERE id = ?", [$riwayatId]);
if (!$riwayat) { echo json_encode(['error' => 'Riwayat Lampiran tidak ditemukan.']); exit; }

// Ambil konfigurasi Drive
$serviceAccountJson = getSetting('google_drive_service_account');
$folderId = getSetting('google_drive_folder_id') ?: '1hLAII1Ba-mZ6ORJA97IjuYqTpQqwns3b';

if (empty($serviceAccountJson)) {
    echo json_encode(['error' => 'Konfigurasi Google Drive belum diatur.']); exit;
}

try {
    $drive = GoogleDriveHelper::fromJson($serviceAccountJson);
    
    // Baca file sementara
    $filePath = $_FILES['pdf']['tmp_name'];
    $fileData = file_get_contents($filePath);
    $mimeType = 'application/pdf';

    // Upload ke Drive
    $driveResponse = $drive->uploadFile($filename, $fileData, $mimeType, $folderId);
    
    if (!$driveResponse || empty($driveResponse['id'])) {
        echo json_encode(['error' => 'Gagal upload ke Google Drive.']); exit;
    }
    
    $fileId = $driveResponse['id'];
    
    // Set permission agar link bisa dibuka siapa saja
    $drive->setFilePermissionToAnyone($fileId);
    
    // Ambil webViewLink
    $fileMeta = $drive->getFileMetadata($fileId);
    $webViewLink = $fileMeta['webViewLink'] ?? '';
    
    if (!$webViewLink) {
        $webViewLink = "https://drive.google.com/file/d/{$fileId}/view";
    }

    // Update database
    dbExecute("UPDATE riwayat_lampiran SET drive_file_id = ?, drive_url = ?, drive_uploaded_at = NOW() WHERE id = ?",
        [$fileId, $webViewLink, $riwayatId]);

    echo json_encode([
        'success' => true,
        'drive_url' => $webViewLink
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Gagal upload: ' . $e->getMessage()]);
}
