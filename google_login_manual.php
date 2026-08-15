<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Jika sudah login, langsung ke dashboard
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index');
    exit;
} elseif (isMahasiswaLoggedIn()) {
    header('Location: ' . BASE_URL . '/mhs/index');
    exit;
}

// Ambil Google Client ID dan Secret dari database settings
$clientId     = trim(getSetting('google_client_id'));
$clientSecret = trim(getSetting('google_client_secret'));

if (empty($clientId) || empty($clientSecret)) {
    die("Error: Google Client ID dan Client Secret belum diatur. Harap masukkan di menu Pengaturan → Konfigurasi Sistem.");
}

// URL file ini sebagai callback dari Google (sesuai konfigurasi di console)
$redirectUri = BASE_URL . '/?page=auth/google/callback';

// 1. Jika tidak ada 'code', redirect user ke halaman login Google
if (!isset($_GET['code'])) {
    $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'online',
        'prompt' => 'select_account' // Selalu minta pilih akun
    ]);
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

// 2. Terima 'code' dari Google dan tukar dengan token (Access Token & ID Token)
$code = $_GET['code'];
$tokenUrl = 'https://oauth2.googleapis.com/token';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code',
    'code' => $code
]));
$response = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($response, true);

if (isset($tokenData['error'])) {
    $errorDesc = $tokenData['error_description'] ?? $tokenData['error'];
    $rawResponse = htmlspecialchars($response);
    die("<h3>Error mendapatkan token dari Google!</h3>
         <p><strong>Pesan:</strong> {$errorDesc}</p>
         <p><em>(Catatan: Jika Anda me-refresh halaman ini, kode otorisasi sudah kadaluarsa. Silakan ulangi login dari awal.)</em></p>
         <p><strong>Response Mentah:</strong> <code>{$rawResponse}</code></p>
         <a href='login' style='display:inline-block; margin-top:15px; padding:10px 15px; background:var(--color-primary); color:#000; text-decoration:none; border-radius:8px; font-weight:bold;'>← Ulangi Login</a>");
}

$accessToken = $tokenData['access_token'] ?? '';

if (!$accessToken) {
    die("Error: Access token tidak ditemukan.");
}

// 3. Gunakan Access Token untuk mengambil data profil user dari Google
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);
$userInfoResponse = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode($userInfoResponse, true);

if (isset($userInfo['error'])) {
    die("Error mendapatkan info user: " . htmlspecialchars($userInfo['error']['message'] ?? 'Unknown error'));
}

$email = $userInfo['email'] ?? '';

if (empty($email)) {
    die("Error: Email tidak ditemukan di profil Google Anda.");
}

// 4. Cek apakah email terdaftar di tabel users (Admin)
$user = dbQueryOne("SELECT * FROM users WHERE email=? LIMIT 1", [$email]);

if ($user) {
    // Jika ada, set session (Login Berhasil)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['prodi_id'] = $user['prodi_id'];
    $_SESSION['foto'] = $user['foto'];
    
    // Update last login
    dbExecute("UPDATE users SET last_login=NOW() WHERE id=?", [$user['id']]);
    logActivity('Login', 'auth', 'Login berhasil via Google (Manual API)');
    
    // Redirect berdasarkan role dari database
    if ($user['role'] === 'mahasiswa') {
        header('Location: ' . BASE_URL . '/mhs/index');
    } elseif ($user['role'] === 'dosen') {
        header('Location: ' . BASE_URL . '/dosen/index');
    } else {
        header('Location: ' . BASE_URL . '/index');
    }
    exit;
} else {
    // 5. Cek apakah email terdaftar di tabel mahasiswa
    $mhs = dbQueryOne("SELECT * FROM mahasiswa WHERE email=? LIMIT 1", [$email]);
    
    if ($mhs) {
        // Set session Mahasiswa
        $_SESSION['mhs_id'] = $mhs['id'];
        $_SESSION['mhs_nama'] = $mhs['nama'];
        $_SESSION['mhs_nim'] = $mhs['nim'];
        $_SESSION['mhs_prodi_id'] = $mhs['prodi_id'];
        
        // Arahkan ke dashboard mahasiswa
        header('Location: ' . BASE_URL . '/mhs/index');
        exit;
    } else {
        // Jika email tidak ditemukan sama sekali, otomatis daftarkan sebagai Mahasiswa
        $nama = $userInfo['name'] ?? explode('@', $email)[0];
        
        // Buat data dummy untuk field yang wajib (NOT NULL)
        $nim = 'GGL-' . strtoupper(substr(md5($email . time()), 0, 6)); // Pastikan unik
        $angkatan = date('Y');
        
        // Ambil prodi pertama sebagai default
        $prodiDefault = dbQueryOne("SELECT id FROM prodi LIMIT 1");
        $prodi_id = $prodiDefault ? $prodiDefault['id'] : 1; 

        // Insert ke tabel mahasiswa
        $newId = dbExecute(
            "INSERT INTO mahasiswa (nim, nama, prodi_id, angkatan, email, status) VALUES (?, ?, ?, ?, ?, 'Aktif')",
            [$nim, $nama, $prodi_id, $angkatan, $email]
        );
        
        if ($newId) {
            $mhs = dbQueryOne("SELECT * FROM mahasiswa WHERE id=?", [$newId]);
            // Set session Mahasiswa
            $_SESSION['mhs_id'] = $mhs['id'];
            $_SESSION['mhs_nama'] = $mhs['nama'];
            $_SESSION['mhs_nim'] = $mhs['nim'];
            $_SESSION['mhs_prodi_id'] = $mhs['prodi_id'];
            
            // Arahkan ke dashboard mahasiswa
            header('Location: ' . BASE_URL . '/mhs/index');
            exit;
        } else {
            die("Error: Gagal mendaftarkan otomatis akun mahasiswa.");
        }
    }
}
