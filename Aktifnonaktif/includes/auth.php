<?php
/**
 * Authentication & Authorization Helpers
 * Universitas Nusa Putra - Sistem Pengunduran Diri Mahasiswa
 */

// ============================================================
// LOGIN CHECK
// ============================================================

/**
 * Check if a user is logged in
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION[SESSION_PREFIX . 'user_id']);
}

/**
 * Get current user ID from session
 */
function currentUserId(): ?int
{
    return $_SESSION[SESSION_PREFIX . 'user_id'] ?? null;
}

/**
 * Get current user role from session
 */
function currentRole(): ?string
{
    return $_SESSION[SESSION_PREFIX . 'role'] ?? null;
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool
{
    return currentRole() === 'admin';
}

/**
 * Check if current user is kaprodi
 */
function isKaprodi(): bool
{
    return currentRole() === 'kaprodi';
}

/**
 * Check if current user is mahasiswa
 */
function isMahasiswa(): bool
{
    return currentRole() === 'mahasiswa';
}

/**
 * Get full current user data from session
 */
function currentUser(): array
{
    $prodi = $_SESSION[SESSION_PREFIX . 'program_studi'] ?? null;
    $role = $_SESSION[SESSION_PREFIX . 'role'] ?? '';
    $userId = $_SESSION[SESSION_PREFIX . 'user_id'] ?? null;

    // Auto-heal empty program studi for kaprodi if they had missing data previously
    if ($role === 'kaprodi' && empty($prodi) && $userId) {
        $userDb = Database::fetchOne("SELECT program_studi FROM users WHERE id = ?", [$userId]);
        if ($userDb && !empty($userDb['program_studi'])) {
            $prodi = $userDb['program_studi'];
            $_SESSION[SESSION_PREFIX . 'program_studi'] = $prodi;
        }
    }

    return [
        'id'            => $userId,
        'nama'          => $_SESSION[SESSION_PREFIX . 'nama'] ?? '',
        'email'         => $_SESSION[SESSION_PREFIX . 'email'] ?? '',
        'role'          => $role,
        'program_studi' => $prodi,
        'avatar'        => $_SESSION[SESSION_PREFIX . 'avatar'] ?? null,
        'auth_provider' => $_SESSION[SESSION_PREFIX . 'auth_provider'] ?? 'local',
        'mahasiswa'     => $_SESSION[SESSION_PREFIX . 'mahasiswa'] ?? [],
    ];
}

/**
 * Get current user Google avatar URL (or null)
 */
function currentUserAvatar(): ?string
{
    return $_SESSION[SESSION_PREFIX . 'avatar'] ?? null;
}

// ============================================================
// ROUTE GUARDS
// ============================================================

/**
 * Require login — redirect to login if not authenticated
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect('?page=login');
    }
    checkSessionTimeout();
}

/**
 * Require admin role
 */
function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        if (isKaprodi()) {
            redirect('?page=admin/dashboard');
        } else {
            redirect('?page=mahasiswa/dashboard');
        }
    }
}

/**
 * Require admin or kaprodi role
 */
function requireAdminOrKaprodi(): void
{
    requireLogin();
    if (!isAdmin() && !isKaprodi()) {
        redirect('?page=mahasiswa/dashboard');
    }
}

/**
 * Require mahasiswa role
 */
function requireMahasiswa(): void
{
    requireLogin();
    if (!isMahasiswa()) {
        redirect('?page=admin/dashboard');
    }
}

// ============================================================
// SESSION MANAGEMENT
// ============================================================

/**
 * Set user session after successful login
 */
function setUserSession(array $user, array $mahasiswa = []): void
{
    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);

    $_SESSION[SESSION_PREFIX . 'user_id']      = (int)$user['id'];
    $_SESSION[SESSION_PREFIX . 'nama']         = $user['nama'];
    $_SESSION[SESSION_PREFIX . 'email']        = $user['email'];
    $_SESSION[SESSION_PREFIX . 'role']         = $user['role'];
    $_SESSION[SESSION_PREFIX . 'program_studi']= $user['program_studi'] ?? null;
    $_SESSION[SESSION_PREFIX . 'avatar']       = $user['avatar'] ?? null;
    $_SESSION[SESSION_PREFIX . 'auth_provider']= $user['auth_provider'] ?? 'local';
    $_SESSION[SESSION_PREFIX . 'last_active']  = time();

    if (!empty($mahasiswa)) {
        $_SESSION[SESSION_PREFIX . 'mahasiswa'] = $mahasiswa;
    }
}

/**
 * Destroy user session (logout)
 */
function destroySession(): void
{
    // Clear all session data
    $_SESSION = [];

    // Destroy session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Check session timeout — logout if expired
 */
function checkSessionTimeout(): void
{
    $lastActive = $_SESSION[SESSION_PREFIX . 'last_active'] ?? 0;
    $timeout    = SESSION_TIMEOUT;

    if ((time() - $lastActive) > $timeout) {
        destroySession();
        flash('warning', 'Sesi Anda telah berakhir. Silakan login kembali.');
        redirect('?page=login');
    }

    // Update last active time
    $_SESSION[SESSION_PREFIX . 'last_active'] = time();
}

// ============================================================
// RATE LIMITING (Login Attempts)
// ============================================================

/**
 * Record a failed login attempt
 */
function recordLoginAttempt(string $identifier): void
{
    Database::query(
        "INSERT INTO login_attempts (identifier, ip_address) VALUES (?, ?)",
        [$identifier, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0']
    );
}

/**
 * Check if identifier is locked out
 */
function isLockedOut(string $identifier): bool
{
    $window    = date('Y-m-d H:i:s', time() - LOGIN_LOCKOUT_TIME);
    $maxAttempts = MAX_LOGIN_ATTEMPTS;

    $result = Database::fetchOne(
        "SELECT COUNT(*) as attempts FROM login_attempts
         WHERE identifier = ? AND attempted_at > ?",
        [$identifier, $window]
    );

    return ($result['attempts'] ?? 0) >= $maxAttempts;
}

/**
 * Clear login attempts for an identifier (on successful login)
 */
function clearLoginAttempts(string $identifier): void
{
    Database::query(
        "DELETE FROM login_attempts WHERE identifier = ?",
        [$identifier]
    );
}

/**
 * Get remaining lockout time in minutes
 */
function lockoutTimeRemaining(string $identifier): int
{
    $result = Database::fetchOne(
        "SELECT MAX(attempted_at) as last_attempt FROM login_attempts WHERE identifier = ?",
        [$identifier]
    );

    if (!$result || !$result['last_attempt']) return 0;

    $elapsed = time() - strtotime($result['last_attempt']);
    $remaining = LOGIN_LOCKOUT_TIME - $elapsed;

    return max(0, (int)ceil($remaining / 60));
}

// ============================================================
// REMEMBER ME
// ============================================================

/**
 * Set remember me cookie and DB token
 */
function setRememberToken(int $userId): void
{
    $token = bin2hex(random_bytes(32));
    $expires = time() + (30 * 24 * 3600); // 30 days

    // Store hashed token in DB
    Database::query(
        "UPDATE users SET remember_token = ? WHERE id = ?",
        [hash('sha256', $token), $userId]
    );

    // Set cookie
    setcookie('remember_token', $token, $expires, '/', '', false, true);
}

/**
 * Try to login via remember me cookie
 */
function tryRememberLogin(): bool
{
    $token = $_COOKIE['remember_token'] ?? '';
    if (empty($token)) return false;

    $user = Database::fetchOne(
        "SELECT * FROM users WHERE remember_token = ? AND is_active = 1",
        [hash('sha256', $token)]
    );

    if (!$user) return false;

    // Load mahasiswa data if applicable
    $mahasiswa = [];
    if ($user['role'] === 'mahasiswa') {
        $mahasiswa = Database::fetchOne(
            "SELECT * FROM mahasiswa WHERE user_id = ?",
            [$user['id']]
        ) ?? [];
    }

    setUserSession($user, $mahasiswa);
    return true;
}

/**
 * Clear remember me token
 */
function clearRememberToken(int $userId): void
{
    Database::query("UPDATE users SET remember_token = NULL WHERE id = ?", [$userId]);
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}
