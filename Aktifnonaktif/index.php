<?php
/**
 * Front Controller / Router
 * Universitas Nusa Putra - Sistem Pengunduran Diri Mahasiswa
 *
 * URL format: index.php?page=admin/dashboard
 *             index.php?page=mahasiswa/form
 *             index.php?page=auth/login
 */

// ============================================================
// BOOTSTRAP
// ============================================================
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Models
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Mahasiswa.php';
require_once __DIR__ . '/models/PengunduranDiri.php';
require_once __DIR__ . '/models/Models.php'; // DigitalSignature, ActivityLog, Settings

// Controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/MahasiswaController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/PDFController.php';
require_once __DIR__ . '/controllers/DocxController.php';

// Google OAuth Helper
require_once __DIR__ . '/config/google.php';

// ============================================================
// ROUTING
// ============================================================
$page   = sanitize($_GET['page'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

// Route map: [page => [controller, method, http_method]]
$routes = [
    // Auth
    'login'                      => ['AuthController',      'login',           'GET'],
    'auth/login'                 => ['AuthController',      'processLogin',    'POST'],
    'logout'                     => ['AuthController',      'logout',          'GET|POST'],
    'auth/google'                => ['AuthController',      'googleLogin',     'GET'],
    'auth/google/callback'       => ['AuthController',      'googleCallback',  'GET'],
    'auth/link-google'           => ['AuthController',      'linkGoogle',      'GET'],
    'auth/link-google/process'   => ['AuthController',      'processLinkGoogle','POST'],

    // Mahasiswa
    'mahasiswa/dashboard'      => ['MahasiswaController', 'dashboard',       'GET'],
    'mahasiswa/form'           => ['MahasiswaController', 'form',            'GET'],
    'pdf'                      => ['PDFController',       'generate',        'GET'],
    'docx'                     => ['DocxController',      'generate',        'GET'],
    'mahasiswa/submit'         => ['MahasiswaController', 'submitForm',      'POST'],
    'mahasiswa/csrf-token'     => ['MahasiswaController', 'csrfToken',       'GET'],
    'mahasiswa/success'        => ['MahasiswaController', 'success',         'GET'],
    'mahasiswa/riwayat'        => ['MahasiswaController', 'riwayat',         'GET'],
    'mahasiswa/profile'        => ['MahasiswaController', 'profile',         'GET'],
    'mahasiswa/profile/update' => ['MahasiswaController', 'updateProfile',   'POST'],
    'mahasiswa/cetak'          => ['MahasiswaController', 'cetakPdf',        'GET'],

    // Admin
    'admin/dashboard'          => ['AdminController',     'dashboard',       'GET'],
    'admin/pengajuan'          => ['AdminController',     'pengajuan',       'GET'],
    'admin/detail'             => ['AdminController',     'detail',          'GET'],
    'admin/approve'            => ['AdminController',     'approve',         'POST'],
    'admin/reject'             => ['AdminController',     'reject',          'POST'],
    'admin/update-nomor'       => ['AdminController',     'updateNomor',     'POST'],
    'admin/delete-pengajuan'   => ['AdminController',     'deletePengajuan', 'POST'],
    'admin/mahasiswa'          => ['AdminController',     'dataMahasiswa',   'GET'],
    'admin/mahasiswa/save'     => ['AdminController',     'saveMahasiswa',   'POST'],
    'admin/mahasiswa/delete'   => ['AdminController',     'deleteMahasiswa', 'POST'],
    'admin/users'              => ['AdminController',     'users',           'GET'],
    'admin/users/save'         => ['AdminController',     'saveUser',        'POST'],
    'admin/users/delete'       => ['AdminController',     'deleteUser',      'POST'],
    'admin/settings'           => ['AdminController',     'settings',        'GET'],
    'admin/settings/save'      => ['AdminController',     'saveSettings',    'POST'],
    'admin/cetak'              => ['AdminController',     'printPdf',        'GET'],
    'admin/export-excel'       => ['AdminController',     'exportExcel',     'GET'],
];

// Default route: redirect to form
if ($page === '' || $page === 'index') {
    if (isLoggedIn() && isAdmin()) {
        redirect('?page=admin/dashboard');
    }
    redirect('?page=mahasiswa/form');
}

// Dispatch
if (isset($routes[$page])) {
    [$controllerClass, $action, $allowedMethods] = $routes[$page];

    // Check HTTP method
    $allowed = explode('|', $allowedMethods);
    if (!in_array($method, $allowed)) {
        http_response_code(405);
        require __DIR__ . '/views/errors/404.php';
        exit;
    }

    $controller = new $controllerClass();
    $controller->$action();
} else {
    http_response_code(404);
    require __DIR__ . '/views/errors/404.php';
}
