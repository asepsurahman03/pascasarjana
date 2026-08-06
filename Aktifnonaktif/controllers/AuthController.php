<?php
/**
 * Authentication Controller
 */
class AuthController
{
    public function login(): void
    {
        if (isLoggedIn()) {
            $this->redirectByRole();
        }

        // Try remember-me
        if (!isLoggedIn() && !empty($_COOKIE['remember_token'])) {
            if (tryRememberLogin()) {
                $this->redirectByRole();
            }
        }

        $data = [
            'title'      => 'Login - ' . APP_NAME,
            'error'      => '',
            'login_type' => $_GET['type'] ?? 'admin',
        ];

        require BASE_PATH . '/views/auth/login.php';
    }

    public function processLogin(): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
            redirect('?page=login');
        }

        $emailOrNim = sanitize($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';
        $remember   = isset($_POST['remember']);

        if (empty($emailOrNim) || empty($password)) {
            flash('error', 'Email/NIM dan password wajib diisi.');
            redirect('?page=login');
        }

        if (isLockedOut($emailOrNim)) {
            $remaining = lockoutTimeRemaining($emailOrNim);
            flash('error', "Terlalu banyak percobaan login. Coba lagi dalam $remaining menit.");
            redirect('?page=login');
        }

        // 1. Try to find user by email
        $user = User::findByEmail($emailOrNim);

        // 2. If not found by email, try to find by NIM and Tanggal Lahir (Password)
        if (!$user) {
            $mahasiswa = Mahasiswa::findByNimAndTanggalLahir($emailOrNim, $password);
            if ($mahasiswa) {
                $user = $mahasiswa['user_id'] ? User::findById($mahasiswa['user_id']) : $this->createMahasiswaUser($mahasiswa);
                if ($user) {
                    clearLoginAttempts($emailOrNim);
                    setUserSession($user, $mahasiswa);
                    User::updateLastLogin($user['id']);
                    if ($remember) setRememberToken($user['id']);
                    logActivity('LOGIN', 'Mahasiswa login via NIM: ' . $emailOrNim);
                    redirect('?page=mahasiswa/dashboard');
                    return;
                }
            }
        }

        // 3. If user found by email, verify password
        if ($user && password_verify($password, $user['password'])) {
            clearLoginAttempts($emailOrNim);
            if ($user['role'] === 'admin' || $user['role'] === 'kaprodi') {
                setUserSession($user);
                User::updateLastLogin($user['id']);
                if ($remember) setRememberToken($user['id']);
                logActivity('LOGIN', ucfirst($user['role']) . ' login: ' . $emailOrNim);
                redirect('?page=admin/dashboard');
            } else {
                $mahasiswa = Mahasiswa::findByUserId($user['id']);
                setUserSession($user, $mahasiswa ?? []);
                User::updateLastLogin($user['id']);
                if ($remember) setRememberToken($user['id']);
                logActivity('LOGIN', 'Mahasiswa login via email: ' . $emailOrNim);
                redirect('?page=mahasiswa/form');
            }
            return;
        }

        // Failed login
        recordLoginAttempt($emailOrNim);
        flash('error', 'Email/NIM atau password salah.');
        setOldInput(['email' => $emailOrNim]);
        redirect('?page=login');
    }

    private function createMahasiswaUser(array $mahasiswa): ?array
    {
        // Auto-create user account for NIM login
        $defaultPass = password_hash($mahasiswa['nim'] . $mahasiswa['tanggal_lahir'], PASSWORD_BCRYPT, ['cost' => 12]);

        Database::query(
            "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'mahasiswa')",
            [$mahasiswa['nama'], $mahasiswa['email'] ?? ($mahasiswa['nim'] . '@student.nusaputra.ac.id'), $defaultPass]
        );
        $newUserId = (int)Database::lastInsertId();

        // Link mahasiswa to user
        Database::query("UPDATE mahasiswa SET user_id = ? WHERE id = ?", [$newUserId, $mahasiswa['id']]);

        return User::findById($newUserId);
    }

    public function logout(): void
    {
        $userId = currentUserId();
        if ($userId) {
            clearRememberToken($userId);
            logActivity('LOGOUT', 'User logout');
        }
        destroySession();
        flash('success', 'Anda berhasil logout.');
        redirect('?page=login');
    }

    // ============================================================
    // GOOGLE OAUTH 2.0
    // ============================================================

    /**
     * Step 1: Redirect user to Google consent screen.
     */
    public function googleLogin(): void
    {
        if (isLoggedIn()) {
            $this->redirectByRole();
        }

        $url = GoogleOAuth::getAuthUrl();
        header('Location: ' . $url);
        exit;
    }

    /**
     * Step 2: Handle callback from Google.
     */
    public function googleCallback(): void
    {
        // CSRF state check
        $state = $_GET['state'] ?? '';
        if (empty($state) || $state !== ($_SESSION['google_oauth_state'] ?? '')) {
            flash('error', 'Permintaan login Google tidak valid (state mismatch). Silakan coba lagi.');
            redirect('?page=login');
        }
        unset($_SESSION['google_oauth_state']);

        // Error from Google (user denied, etc.)
        if (isset($_GET['error'])) {
            flash('error', 'Login Google dibatalkan: ' . htmlspecialchars($_GET['error']));
            redirect('?page=login');
        }

        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            flash('error', 'Kode otorisasi Google tidak ditemukan.');
            redirect('?page=login');
        }

        // Exchange code for access token
        $tokenData = GoogleOAuth::getTokenFromCode($code);
        if (!$tokenData) {
            flash('error', 'Gagal mendapatkan token dari Google. Silakan coba lagi.');
            redirect('?page=login');
        }

        // Fetch user info from Google
        $googleUser = GoogleOAuth::getUserInfo($tokenData['access_token']);
        if (!$googleUser || empty($googleUser['email'])) {
            flash('error', 'Gagal mengambil data akun Google Anda.');
            redirect('?page=login');
        }

        $googleId = $googleUser['sub'];
        $email    = $googleUser['email'];
        $nama     = $googleUser['name']  ?? $email;
        $avatar   = $googleUser['picture'] ?? null;

        try {
            Database::beginTransaction();

            // 1. Cari user by google_id
            $user = Database::fetchOne(
                "SELECT * FROM users WHERE google_id = ? LIMIT 1",
                [$googleId]
            );

            // 2. Cari by email
            if (!$user) {
                $user = Database::fetchOne(
                    "SELECT * FROM users WHERE email = ? LIMIT 1",
                    [$email]
                );
                if ($user) {
                    Database::query(
                        "UPDATE users SET google_id = ?, avatar = ?, auth_provider = 'google' WHERE id = ?",
                        [$googleId, $avatar, $user['id']]
                    );
                    $user['google_id']     = $googleId;
                    $user['auth_provider'] = 'google';
                } else {
                    // Coba cari di tabel mahasiswa
                    $mhs = Database::fetchOne("SELECT * FROM mahasiswa WHERE email = ? LIMIT 1", [$email]);
                    if ($mhs) {
                        if ($mhs['user_id']) {
                            $user = User::findById($mhs['user_id']);
                            if ($user) {
                                Database::query("UPDATE users SET google_id = ?, avatar = ?, auth_provider = 'google' WHERE id = ?", [$googleId, $avatar, $user['id']]);
                                $user['google_id'] = $googleId;
                            }
                        } else {
                            $user = $this->createMahasiswaUser($mhs);
                            Database::query("UPDATE users SET google_id = ?, avatar = ?, auth_provider = 'google' WHERE id = ?", [$googleId, $avatar, $user['id']]);
                            $user['google_id'] = $googleId;
                        }
                    }
                }
            }

            // 3. Jika belum ketemu, auto-register sebagai mahasiswa baru (blank user) dan langsung masuk
            if (!$user) {
                Database::query(
                    "INSERT INTO users (nama, email, password, role, google_id, avatar, auth_provider, is_active)
                     VALUES (?, ?, ?, 'mahasiswa', ?, ?, 'google', 1)",
                    [$nama, $email, '', $googleId, $avatar]
                );
                $newId = (int)Database::lastInsertId();
                $user  = User::findById($newId);
                logActivity('REGISTER', 'Auto-register via Google: ' . $email);
            }

            Database::commit();

            if (!$user['is_active']) {
                flash('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
                redirect('?page=login');
            }

            // Update last login & avatar
            Database::query(
                "UPDATE users SET last_login = NOW(), avatar = ? WHERE id = ?",
                [$avatar, $user['id']]
            );

            // Set session
            $mahasiswa = null;
            if ($user['role'] === 'mahasiswa') {
                $mahasiswa = Mahasiswa::findByUserId($user['id']);
            }
            setUserSession($user, $mahasiswa ?? []);

            logActivity('LOGIN', 'Login via Google: ' . $email);

            if ($user['role'] === 'admin' || $user['role'] === 'kaprodi') {
                redirect('?page=admin/dashboard');
            }
            redirect('?page=mahasiswa/form');
        } catch (\Throwable $e) {
            Database::rollback();
            flash('error', 'Terjadi kesalahan saat proses login Google. Silakan coba lagi.');
            redirect('?page=login');
        }
    }

    public function linkGoogle(): void
    {
        if (isLoggedIn()) {
            $this->redirectByRole();
        }

        if (empty($_SESSION['pending_google_link'])) {
            flash('error', 'Sesi Google telah habis. Silakan coba login Google kembali.');
            redirect('?page=login');
        }

        $data = [
            'title' => 'Tautkan Akun Google - ' . APP_NAME,
            'google' => $_SESSION['pending_google_link']
        ];

        require BASE_PATH . '/views/auth/link_google.php';
    }

    public function processLinkGoogle(): void
    {
        if (!verifyCsrf()) {
            flash('error', 'Token keamanan tidak valid.');
            redirect('?page=auth/link-google');
        }

        if (empty($_SESSION['pending_google_link'])) {
            flash('error', 'Sesi tautan Google tidak ditemukan. Silakan ulangi login via Google.');
            redirect('?page=login');
        }

        $google = $_SESSION['pending_google_link'];

        if (isset($_POST['skip']) && $_POST['skip'] == '1') {
            // User chooses to skip linking. Auto-register as new blank user.
            Database::query(
                "INSERT INTO users (nama, email, password, role, google_id, avatar, auth_provider, is_active)
                 VALUES (?, ?, ?, 'mahasiswa', ?, ?, 'google', 1)",
                [$google['nama'], $google['email'], '', $google['google_id'], $google['avatar']]
            );
            $newId = (int)Database::lastInsertId();
            $user  = User::findById($newId);

            unset($_SESSION['pending_google_link']);
            setUserSession($user, []);
            logActivity('LOGIN', 'User baru Google melewati tautan: ' . $google['email']);
            
            flash('success', 'Berhasil masuk! Silakan isi formulir pengunduran diri jika belum.');
            redirect('?page=mahasiswa/dashboard');
            return;
        }

        $nim      = sanitize($_POST['nim'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($nim) || empty($password)) {
            flash('error', 'NIM dan Password wajib diisi.');
            redirect('?page=auth/link-google');
        }

        // Cari mahasiswa by NIM & Tanggal Lahir (Password)
        $mahasiswa = Mahasiswa::findByNimAndTanggalLahir($nim, $password);
        if (!$mahasiswa) {
            flash('error', 'Data tidak ditemukan atau NIM/Password salah.');
            redirect('?page=auth/link-google');
        }

        // Tautkan akun
        $user = $mahasiswa['user_id'] ? User::findById($mahasiswa['user_id']) : $this->createMahasiswaUser($mahasiswa);
        if ($user) {
            Database::query(
                "UPDATE users SET google_id = ?, avatar = ?, auth_provider = 'google', last_login = NOW() WHERE id = ?",
                [$google['google_id'], $google['avatar'], $user['id']]
            );
            $user['google_id']     = $google['google_id'];
            $user['avatar']        = $google['avatar'];
            $user['auth_provider'] = 'google';

            unset($_SESSION['pending_google_link']);

            setUserSession($user, $mahasiswa);
            logActivity('LINK_GOOGLE', "NIM {$nim} menautkan akun Google {$google['email']}");
            
            flash('success', 'Akun Google berhasil ditautkan. Selamat datang!');
            redirect('?page=mahasiswa/dashboard');
        } else {
            flash('error', 'Gagal menautkan akun.');
            redirect('?page=auth/link-google');
        }
    }

    private function redirectByRole(): never
    {
        if (isAdmin() || isKaprodi()) {
            redirect('?page=admin/dashboard');
        }
        redirect('?page=mahasiswa/dashboard');
    }
}
