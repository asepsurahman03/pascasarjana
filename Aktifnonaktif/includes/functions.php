<?php
/**
 * Global Helper Functions
 * Universitas Nusa Putra - Sistem Pengunduran Diri Mahasiswa
 */

// ============================================================
// SANITIZE & SECURITY
// ============================================================

/**
 * Sanitize output against XSS
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Sanitize input string
 */
function sanitize(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Generate CSRF token
 */
function csrfToken(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Render CSRF hidden input
 */
function csrfField(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrfToken() . '">';
}

/**
 * Validate CSRF token
 */
function verifyCsrf(): bool
{
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    return hash_equals($_SESSION[CSRF_TOKEN_NAME] ?? '', $token);
}

// ============================================================
// REDIRECT & RESPONSE
// ============================================================

/**
 * Redirect to URL
 */
function redirect(string $url): never
{
    header('Location: ' . APP_URL . '/' . ltrim($url, '/'));
    exit;
}

/**
 * Redirect back
 */
function redirectBack(): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? (APP_URL . '/');
    header('Location: ' . $ref);
    exit;
}

/**
 * JSON response
 */
function jsonResponse(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// FLASH MESSAGES
// ============================================================

/**
 * Set a flash message
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][$type][] = $message;
}

/**
 * Get and clear flash messages
 */
function getFlash(string $type): array
{
    $msgs = $_SESSION['flash'][$type] ?? [];
    unset($_SESSION['flash'][$type]);
    return $msgs;
}

/**
 * Check if there is a flash of given type
 */
function hasFlash(string $type): bool
{
    return !empty($_SESSION['flash'][$type]);
}

// ============================================================
// DATE & TIME
// ============================================================

/**
 * Format date to Indonesian locale
 */
function formatTanggal(string $date, bool $withDay = false): string
{
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
    ];

    $ts = strtotime($date);
    $d  = (int)date('j', $ts);
    $m  = (int)date('n', $ts);
    $y  = date('Y', $ts);

    if ($withDay) {
        $dayName = $hari[date('l', $ts)];
        return "$dayName, $d {$bulan[$m]} $y";
    }

    return "$d {$bulan[$m]} $y";
}

/**
 * Format datetime to Indonesian
 */
function formatDatetime(string $datetime): string
{
    $ts = strtotime($datetime);
    return date('d/m/Y H:i', $ts) . ' WIB';
}

/**
 * Time ago (relative)
 */
function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);

    if ($diff < 60)      return 'Baru saja';
    if ($diff < 3600)    return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400)   return floor($diff / 3600) . ' jam lalu';
    if ($diff < 2592000) return floor($diff / 86400) . ' hari lalu';

    return formatTanggal($datetime);
}

// ============================================================
// NOMOR SURAT
// ============================================================

/**
 * Generate nomor surat otomatis
 */
function generateNomorSurat(): string
{
    $prefix  = 'NPU/PD/';
    $year    = date('Y');
    $month   = date('m');

    // Get the last nomor_surat for the current year
    $last = Database::fetchOne(
        "SELECT nomor_surat FROM pengunduran_diri WHERE YEAR(created_at) = ? AND nomor_surat LIKE ? ORDER BY id DESC LIMIT 1",
        [$year, "$prefix$year/%"]
    );
    
    $counter = 1;
    if ($last && !empty($last['nomor_surat'])) {
        $parts = explode('/', $last['nomor_surat']);
        $lastCounter = (int)end($parts);
        $counter = $lastCounter + 1;
    }

    return sprintf('%s%s/%s/%04d', $prefix, $year, $month, $counter);
}

// ============================================================
// PAGINATION
// ============================================================

/**
 * Generate pagination data
 */
function paginate(int $total, int $perPage = 10, int $currentPage = 1): array
{
    $totalPages  = (int)ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset      = ($currentPage - 1) * $perPage;

    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => $offset,
        'has_prev'     => $currentPage > 1,
        'has_next'     => $currentPage < $totalPages,
        'prev_page'    => $currentPage - 1,
        'next_page'    => $currentPage + 1,
    ];
}

// ============================================================
// FILE / IMAGE
// ============================================================

/**
 * Save base64 signature image to file
 */
function saveSignatureImage(string $base64Data, string $filename): string|false
{
    // Remove header
    $data = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
    $data = base64_decode($data);

    if ($data === false) return false;

    $dir  = SIGNATURE_PATH;
    if (!is_dir($dir)) @mkdir($dir, 0755, true);

    $path = $dir . $filename;
    return @file_put_contents($path, $data) !== false ? $filename : false;
}

/**
 * Get asset URL
 */
function asset(string $path): string
{
    return APP_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Get upload URL
 */
function uploadUrl(string $path): string
{
    return APP_URL . '/uploads/' . ltrim($path, '/');
}

// ============================================================
// STATUS BADGE
// ============================================================

/**
 * Return Tailwind badge classes for a status
 */
function statusBadge(string $status): string
{
    return match ($status) {
        'Approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'Rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'Pending'  => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'Draft'    => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        default    => 'bg-gray-100 text-gray-800',
    };
}

/**
 * Status label in Indonesian
 */
function statusLabel(string $status): string
{
    return match ($status) {
        'Approved' => 'Disetujui',
        'Rejected' => 'Ditolak',
        'Pending'  => 'Menunggu',
        'Draft'    => 'Draft',
        default    => $status,
    };
}

// ============================================================
// INPUT HELPERS
// ============================================================

/**
 * Get old input value (after validation failure)
 */
function old(string $key, string $default = ''): string
{
    return e($_SESSION['old_input'][$key] ?? $default);
}

/**
 * Set old input values
 */
function setOldInput(array $data): void
{
    $_SESSION['old_input'] = $data;
}

/**
 * Clear old input
 */
function clearOldInput(): void
{
    unset($_SESSION['old_input']);
}

/**
 * Get validation error
 */
function error(string $key): string
{
    return $_SESSION['errors'][$key] ?? '';
}

/**
 * Check if field has error
 */
function hasError(string $key): bool
{
    return !empty($_SESSION['errors'][$key]);
}

// ============================================================
// QR CODE
// ============================================================

/**
 * Generate QR Code URL (using external service for simplicity)
 */
function qrCodeUrl(string $data): string
{
    return 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($data);
}

// ============================================================
// TANDA TANGAN KAPRODI
// ============================================================

/**
 * Mapping program studi → nama file TTD (tanpa ekstensi, case-insensitive).
 * Sistem akan mencari file ini di folder TTD Kaprodi/ (prioritas) lalu TTD Dosen/.
 *
 * Urutan pencarian:
 * 0. Cek ttd_path dari tabel users (diupload via Manage Users) — PRIORITAS UTAMA
 * 1. Cari nama file yang mengandung keyword program studi
 * 2. Cari nama file yang mengandung kata-kata dari nama kaprodi (dari settings)
 * 3. Fallback ke file pertama yang tersedia
 */
function getKaprodiTtdUrl(string $programStudi): ?string
{
    // --- STRATEGI 0: Cek ttd_path dari database (diupload via Manage Users) ---
    $kaprodiUser = User::findByProgramStudiAndRole($programStudi, 'kaprodi');
    if ($kaprodiUser && !empty($kaprodiUser['ttd_path'])) {
        $filePath = BASE_PATH . '/' . ltrim($kaprodiUser['ttd_path'], '/');
        if (file_exists($filePath)) {
            return APP_URL . '/' . ltrim($kaprodiUser['ttd_path'], '/');
        }
    }
    // Mapping prodi ke kata kunci nama file TTD (berdasarkan nama prodi)
    $prodiToFileKeyword = [
        'S1 - Teknik Informatika'             => 'teknik informatika',
        'S1 - Manajemen'                      => 'manajemen',
        'S1 - Akuntansi'                      => 'akuntansi',
        'S1 - Teknik Sipil'                   => 'sipil',
        'S1 - Sistem Informasi'               => 'sistem informasi',
        'S1 - Hukum'                          => 'hukum',
        'S1 - Pendidikan Guru Sekolah Dasar'  => 'pgsd',
        'S1 - Teknik Mesin'                   => 'mesin',
        'S1 - Teknik Elektro'                 => 'elektro',
        'S1 - Desain Komunikasi Visual'       => 'dkv',
        'S1 - Gizi'                           => 'gizi',
        'S1 - Bioteknologi'                   => 'bioteknologi',
        'S1 - Teknologi Pangan'               => 'pangan',
        'S1 - Administrasi Kesehatan'         => 'kesehatan',
        'D3 - Keperawatan'                    => 'keperawatan',
        'S2 - Magister Informatika'           => 'informatika',
        'S2 - Magister Hukum'                 => 'hukum',
        'S2 - Magister Pedagogi'              => 'pedagogi',
        'S2 - Magister Manajemen'             => 'manajemen',
        'S3 - Doktor Ilmu Komputer'           => 'komputer',
    ];


    // Cek folder TTD Kaprodi/ terlebih dahulu (prioritas utama)
    // kemudian folder TTD Dosen/ sebagai fallback
    $searchDirs = [
        TTD_KAPRODI_PATH => TTD_KAPRODI_URL,
        TTD_DOSEN_PATH   => TTD_DOSEN_URL,
    ];

    // Kumpulkan semua file gambar dari kedua folder
    $allFiles = []; // ['path' => dirPath, 'url' => dirUrl, 'file' => fileName, 'lower' => lowerName]
    foreach ($searchDirs as $dirPath => $dirUrl) {
        if (!is_dir($dirPath)) continue;
        $files = @scandir($dirPath);
        if (!$files) continue;
        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) continue;
            $allFiles[] = [
                'path'  => $dirPath,
                'url'   => $dirUrl,
                'file'  => $file,
                'lower' => strtolower($file),
            ];
        }
    }

    if (empty($allFiles)) return null;

    // --- STRATEGI 1: Cari berdasarkan keyword nama prodi di nama file ---
    $keyword = $prodiToFileKeyword[$programStudi] ?? null;
    if ($keyword) {
        foreach ($allFiles as $f) {
            if (str_contains($f['lower'], strtolower($keyword))) {
                return $f['url'] . rawurlencode($f['file']);
            }
        }
    }

    // --- STRATEGI 2: Strategi 2: Coba cocokkan dengan nama Kaprodi yang diset di pengaturan
    $map = getProdiSettingsKeyMap();
    $settingsKey = $map[$programStudi] ?? null;
    if ($settingsKey) {
        $namaKaprodi = Settings::get($settingsKey, '');
        if (!empty($namaKaprodi)) {
            // Ekstrak kata-kata bermakna dari nama kaprodi (minimal 4 karakter)
            $words = preg_split('/[\s,\.]+/', $namaKaprodi);
            foreach ($words as $word) {
                $word = trim($word);
                if (strlen($word) < 4) continue; // skip gelar pendek (Dr, Ir, dsb)
                foreach ($allFiles as $f) {
                    if (str_contains($f['lower'], strtolower($word))) {
                        return $f['url'] . rawurlencode($f['file']);
                    }
                }
            }
        }
    }

    // --- STRATEGI 3: Tidak ada fallback otomatis. Biarkan kosong jika tidak ditemukan ---
    return null;
}

function getProdiSettingsKeyMap(): array {
    return [
        'S1 - Teknik Informatika'             => 'ketua_prodi_s1_teknik_informatika',
        'S1 - Manajemen'                      => 'ketua_prodi_s1_manajemen',
        'S1 - Akuntansi'                      => 'ketua_prodi_s1_akuntansi',
        'S1 - Teknik Sipil'                   => 'ketua_prodi_s1_teknik_sipil',
        'S1 - Sistem Informasi'               => 'ketua_prodi_s1_sistem_informasi',
        'S1 - Hukum'                          => 'ketua_prodi_s1_hukum',
        'S1 - Pendidikan Guru Sekolah Dasar'  => 'ketua_prodi_s1_pendidikan_guru_sekolah_dasar',
        'S1 - Teknik Mesin'                   => 'ketua_prodi_s1_teknik_mesin',
        'S1 - Teknik Elektro'                 => 'ketua_prodi_s1_teknik_elektro',
        'S1 - Desain Komunikasi Visual'       => 'ketua_prodi_s1_desain_komunikasi_visual',
        'S1 - Gizi'                           => 'ketua_prodi_s1_gizi',
        'S1 - Bioteknologi'                   => 'ketua_prodi_s1_bioteknologi',
        'S1 - Teknologi Pangan'               => 'ketua_prodi_s1_teknologi_pangan',
        'S1 - Administrasi Kesehatan'         => 'ketua_prodi_s1_administrasi_kesehatan',
        'D3 - Keperawatan'                    => 'ketua_prodi_d3_keperawatan',
        'S2 - Magister Informatika'           => 'ketua_prodi_s2_magister_informatika',
        'S2 - Magister Hukum'                 => 'ketua_prodi_s2_magister_hukum',
        'S2 - Magister Pedagogi'              => 'ketua_prodi_s2_magister_pedagogi',
        'S2 - Magister Manajemen'             => 'ketua_prodi_s2_magister_manajemen',
        'S3 - Doktor Ilmu Komputer'           => 'ketua_prodi_s3_doktor_ilmu_komputer',
    ];
}

function getKaprodiName(string $programStudi): string
{
    // Prioritas 1: Ambil dari Settings admin
    $map = getProdiSettingsKeyMap();
    $settingsKey = $map[$programStudi] ?? null;
    if ($settingsKey) {
        $nama = Settings::get($settingsKey, '');
        if (!empty($nama)) {
            return $nama;
        }
    }

    // Prioritas 2: Ambil dari nama User (Kaprodi) jika sudah ada di sistem
    $kaprodiUser = User::findByProgramStudiAndRole($programStudi, 'kaprodi');
    if ($kaprodiUser && !empty($kaprodiUser['nama'])) {
        return $kaprodiUser['nama'];
    }
    
    return '_____________________________';
}

// ============================================================
// ACTIVITY LOG
// ============================================================

/**
 * Log an activity
 */
function logActivity(string $action, string $description, ?string $model = null, ?int $modelId = null, ?array $old = null, ?array $new = null): void
{
    try {
        Database::query(
            "INSERT INTO activity_logs (user_id, action, description, model, model_id, old_values, new_values, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $_SESSION[SESSION_PREFIX . 'user_id'] ?? null,
                $action,
                $description,
                $model,
                $modelId,
                $old ? json_encode($old) : null,
                $new  ? json_encode($new)  : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]
        );
    } catch (Exception $e) {
        error_log('[ACTIVITY LOG ERROR] ' . $e->getMessage());
    }
}
