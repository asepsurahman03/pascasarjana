<?php
/**
 * Global Configuration
 * Universitas Nusa Putra - Sistem Pengunduran Diri Mahasiswa
 */

// ============================================================
// APPLICATION SETTINGS
// ============================================================
define('APP_NAME',    'Sistem Pengunduran Diri Mahasiswa');
define('APP_VERSION', '1.0.0');
define('APP_URL',     'http://localhost/Aktifnonaktif');
define('BASE_PATH',   dirname(__DIR__));

// ============================================================
// DIRECTORY PATHS
// ============================================================
define('UPLOAD_PATH',      BASE_PATH . '/uploads/');
define('SIGNATURE_PATH',   BASE_PATH . '/uploads/signatures/');
define('LOGO_PATH',        BASE_PATH . '/assets/images/');
define('TTD_DOSEN_PATH',   BASE_PATH . '/TTD Dosen/');
define('TTD_KAPRODI_PATH', BASE_PATH . '/Ttd Kaprodi/');
define('TTD_DOSEN_URL',    APP_URL . '/TTD%20Dosen/');
define('TTD_KAPRODI_URL',  APP_URL . '/Ttd%20Kaprodi/');

// ============================================================
// SESSION CONFIG
// ============================================================
define('SESSION_NAME',    'nusaputra_session');
define('SESSION_TIMEOUT', 3600);   // 1 hour
define('SESSION_PREFIX',  'np_');

// ============================================================
// SECURITY
// ============================================================
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// ============================================================
// GOOGLE OAUTH 2.0
// Daftarkan credentials di: https://console.cloud.google.com/
// Authorized redirect URI: APP_URL . '/?page=auth/google/callback'
// ============================================================
define('GOOGLE_CLIENT_ID',     'ISI_DENGAN_GOOGLE_CLIENT_ID_ANDA');
define('GOOGLE_CLIENT_SECRET', 'ISI_DENGAN_GOOGLE_CLIENT_SECRET_ANDA');
define('GOOGLE_REDIRECT_URI',  'http://localhost/Aktifnonaktif/?page=auth/google/callback');

// ============================================================
// TIMEZONE
// ============================================================
date_default_timezone_set('Asia/Jakarta');

// ============================================================
// ERROR REPORTING (set to 0 on production)
// ============================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============================================================
// SESSION INITIALIZATION
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_name(SESSION_NAME);
    session_start();
}

// ============================================================
// PROGRAM STUDI LIST
// ============================================================
define('PROGRAM_STUDI', [
    'S1 - Teknik Informatika',
    'S1 - Manajemen',
    'S1 - Akuntansi',
    'S1 - Teknik Sipil',
    'S1 - Sistem Informasi',
    'S1 - Hukum',
    'S1 - Pendidikan Guru Sekolah Dasar',
    'S1 - Teknik Mesin',
    'S1 - Teknik Elektro',
    'S1 - Desain Komunikasi Visual',
    'S1 - Gizi',
    'S1 - Bioteknologi',
    'S1 - Teknologi Pangan',
    'S1 - Administrasi Kesehatan',
    'D3 - Keperawatan',
    'S2 - Magister Informatika',
    'S2 - Magister Hukum',
    'S2 - Magister Pedagogi',
    'S2 - Magister Manajemen',
    'S3 - Doktor Ilmu Komputer',
]);

// ============================================================
// STATUS PENGAJUAN
// ============================================================
define('STATUS_COLORS', [
    'Draft'    => 'gray',
    'Pending'  => 'yellow',
    'Approved' => 'green',
    'Rejected' => 'red',
]);
