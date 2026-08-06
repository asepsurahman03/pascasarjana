<?php
/**
 * API: Test koneksi Google Drive
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/google_drive_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isSuperAdmin()) {
    echo json_encode(['error' => 'Unauthorized']); exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$jsonKey  = trim($data['service_account_json'] ?? getSetting('google_drive_service_account') ?? '');
$folderId = trim($data['folder_id'] ?? getSetting('google_drive_folder_id') ?? '');

if (empty($jsonKey)) {
    echo json_encode(['error' => 'Service Account JSON belum diisi.']); exit;
}
if (empty($folderId)) {
    echo json_encode(['error' => 'Folder ID belum diisi.']); exit;
}

try {
    $drive  = GoogleDriveHelper::fromJson($jsonKey);
    $result = $drive->testConnection($folderId);

    // Parse email service account dari JSON
    $parsed = json_decode($jsonKey, true);
    $email  = $parsed['client_email'] ?? '(tidak diketahui)';

    echo json_encode([
        'ok'      => true,
        'message' => "✅ Terhubung ke Google Drive!",
        'email'   => $email,
        'files_in_folder' => $result['files_found'],
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
