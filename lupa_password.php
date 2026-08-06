<?php
// Halaman Lupa Password (tanpa kirim email, pakai reset by admin/token langsung ditampilkan)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Jika sudah login, redirect
if (isLoggedIn()) { header('Location: ' . BASE_URL . '/index'); exit; }

$step    = $_GET['step'] ?? 'request';   // request | reset
$token   = $_GET['token'] ?? '';
$error   = '';
$success = '';

// ─── STEP 2: Form reset password (via token) ───────────────────────────────
if ($step === 'reset' && $token) {
    $dbUser = dbQueryOne(
        "SELECT * FROM users WHERE reset_token=? AND reset_token_exp > NOW() LIMIT 1",
        [hash('sha256', $token)]
    );

    if (!$dbUser) {
        $error = 'Link reset tidak valid atau sudah kadaluarsa. Silakan minta ulang.';
        $step  = 'expired';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPw  = $_POST['new_password']  ?? '';
        $confPw = $_POST['confirm_password'] ?? '';
        if (strlen($newPw) < 8) {
            $error = 'Password minimal 8 karakter.';
        } elseif ($newPw !== $confPw) {
            $error = 'Konfirmasi password tidak cocok.';
        } else {
            $hash = password_hash($newPw, PASSWORD_DEFAULT);
            dbExecute(
                "UPDATE users SET password_hash=?, reset_token=NULL, reset_token_exp=NULL WHERE id=?",
                [$hash, $dbUser['id']]
            );
            $success = 'Password berhasil direset! Silakan login dengan password baru.';
            $step = 'done';
        }
    }

// ─── STEP 1: Form request reset ────────────────────────────────────────────
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'request') {
    $emailOrUser = trim($_POST['email'] ?? '');
    if (empty($emailOrUser)) {
        $error = 'Masukkan email atau username akun Anda.';
    } else {
        $dbUser = dbQueryOne(
            "SELECT * FROM users WHERE email=? OR username=? LIMIT 1",
            [$emailOrUser, $emailOrUser]
        );
        if (!$dbUser) {
            $error = 'Akun dengan email/username tersebut tidak ditemukan.';
        } else {
            // Generate raw token & hash yang disimpan di DB
            $rawToken    = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $rawToken);
            $expiry      = date('Y-m-d H:i:s', time() + 3600); // 1 jam
            dbExecute(
                "UPDATE users SET reset_token=?, reset_token_exp=? WHERE id=?",
                [$hashedToken, $expiry, $dbUser['id']]
            );

            // Tampilkan link langsung (karena tidak ada SMTP, kita tampilkan di layar)
            $resetLink = BASE_URL . '/lupa_password?step=reset&token=' . $rawToken;
            $success   = $resetLink;
            $step      = 'link_shown';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password — SIAKAD Pascasarjana NPU</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{nusa:{DEFAULT:'#961d5a',dark:'#6b1040',light:'#b8277a'}},fontFamily:{sans:['Inter','sans-serif'],display:['Outfit','sans-serif']}}}}</script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
  <style>
    .glass { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); }
    .dark .glass { background: rgba(15,23,42,0.9); }
    .input-field { @apply w-full py-3.5 px-4 pl-11 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-nusa focus:ring-4 focus:ring-nusa/20 transition-all; }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-slate-50 dark:bg-slate-900 transition-colors" x-data="{ darkMode: localStorage.getItem('darkMode')==='true' }" :class="{'dark': darkMode}">
  <!-- BG blobs -->
  <div class="fixed top-0 -left-4 w-96 h-96 bg-nusa/20 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
  <div class="fixed bottom-0 -right-4 w-96 h-96 bg-pink-400/20 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

  <!-- Dark mode toggle -->
  <button @click="darkMode=!darkMode; localStorage.setItem('darkMode', darkMode)" class="fixed top-6 right-6 z-50 p-3 rounded-full bg-white dark:bg-slate-800 shadow-lg text-slate-600 dark:text-slate-300 hover:scale-110 transition-transform">
    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
    <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
  </button>

  <div class="w-full max-w-md glass rounded-3xl shadow-2xl border border-white/50 dark:border-slate-700/50 overflow-hidden relative z-10">
    <!-- Top stripe -->
    <div class="h-2 bg-gradient-to-r from-nusa-dark via-nusa to-nusa-light"></div>

    <div class="p-8 md:p-10">
      <!-- Logo & Back -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-nusa to-nusa-light flex items-center justify-center text-white shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
          </div>
          <span class="font-display font-bold text-slate-800 dark:text-white text-lg">Lupa Password</span>
        </div>
        <a href="<?= BASE_URL ?>/login" class="text-sm text-slate-500 hover:text-nusa dark:text-slate-400 dark:hover:text-nusa-light transition-colors flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Login
        </a>
      </div>

      <?php if ($step === 'request'): ?>
      <!-- Step 1: Request -->
      <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Masukkan email atau username akun Anda. Kami akan membuat link reset password untuk Anda.</p>

      <?php if ($error): ?>
      <div class="mb-5 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl px-4 py-3 text-sm">
        ⚠️ <?= e($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/lupa_password" class="space-y-5">
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Email atau Username</label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-nusa transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
            </div>
            <input type="text" name="email" required autofocus placeholder="contoh: admin@nusaputra.ac.id"
              class="w-full py-3.5 pl-11 pr-4 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-nusa focus:ring-4 focus:ring-nusa/20 transition-all">
          </div>
        </div>
        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-nusa to-[#b8277a] hover:from-nusa-dark hover:to-nusa text-white font-bold rounded-xl shadow-lg shadow-nusa/30 hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          Buat Link Reset
        </button>
      </form>

      <?php elseif ($step === 'link_shown'): ?>
      <!-- Step 1b: Link ditampilkan (karena tidak ada SMTP) -->
      <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5 mb-6">
        <div class="flex items-start gap-3">
          <span class="text-2xl">⚠️</span>
          <div>
            <p class="font-bold text-amber-800 dark:text-amber-300 text-sm mb-2">Link Reset Password</p>
            <p class="text-amber-700 dark:text-amber-400 text-xs mb-3">Karena sistem email belum dikonfigurasi, link reset ditampilkan langsung di sini. Link berlaku 1 jam.</p>
            <div class="bg-white dark:bg-slate-800 rounded-xl p-3 border border-amber-200 dark:border-amber-800">
              <a href="<?= e($success) ?>" class="text-nusa dark:text-nusa-light text-xs break-all hover:underline font-mono"><?= e($success) ?></a>
            </div>
          </div>
        </div>
      </div>
      <a href="<?= e($success) ?>" class="w-full py-3.5 bg-gradient-to-r from-nusa to-[#b8277a] text-white font-bold rounded-xl shadow-lg shadow-nusa/30 hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
        🔑 Buka Halaman Reset
      </a>

      <?php elseif ($step === 'reset' && !$error): ?>
      <!-- Step 2: Form reset password -->
      <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Buat password baru untuk akun <strong class="text-slate-700 dark:text-slate-300"><?= e($dbUser['nama']) ?></strong>.</p>

      <?php if ($error): ?>
      <div class="mb-5 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl px-4 py-3 text-sm">
        ⚠️ <?= e($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/lupa_password?step=reset&token=<?= urlencode($token) ?>" class="space-y-5" x-data="{ showNew:false, showConf:false }">
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Password Baru</label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-nusa transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <input :type="showNew?'text':'password'" name="new_password" required placeholder="Min. 8 karakter"
              class="w-full py-3.5 pl-11 pr-11 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-nusa focus:ring-4 focus:ring-nusa/20 transition-all">
            <button type="button" @click="showNew=!showNew" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-nusa transition-colors">
              <svg x-show="!showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <svg x-show="showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            </button>
          </div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Konfirmasi Password</label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-nusa transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <input :type="showConf?'text':'password'" name="confirm_password" required placeholder="Ulangi password baru"
              class="w-full py-3.5 pl-11 pr-11 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-nusa focus:ring-4 focus:ring-nusa/20 transition-all">
            <button type="button" @click="showConf=!showConf" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-nusa transition-colors">
              <svg x-show="!showConf" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <svg x-show="showConf" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            </button>
          </div>
        </div>
        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-nusa to-[#b8277a] hover:from-nusa-dark hover:to-nusa text-white font-bold rounded-xl shadow-lg shadow-nusa/30 hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Reset Password
        </button>
      </form>

      <?php elseif ($step === 'done'): ?>
      <!-- Done -->
      <div class="text-center py-4">
        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
          <svg class="w-10 h-10 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="font-display font-bold text-2xl text-slate-800 dark:text-white mb-2">Password Berhasil Direset!</h3>
        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Silakan masuk dengan password baru Anda.</p>
        <a href="<?= BASE_URL ?>/login" class="inline-flex items-center gap-2 py-3 px-8 bg-gradient-to-r from-nusa to-[#b8277a] text-white font-bold rounded-xl shadow-lg shadow-nusa/30 hover:-translate-y-0.5 transition-all duration-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
          Ke Halaman Login
        </a>
      </div>

      <?php elseif ($step === 'expired' || ($step === 'reset' && $error)): ?>
      <!-- Expired / Error -->
      <div class="text-center py-4">
        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
          <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="font-display font-bold text-xl text-slate-800 dark:text-white mb-2">Link Tidak Valid</h3>
        <p class="text-slate-500 dark:text-slate-400 text-sm mb-6"><?= e($error) ?></p>
        <a href="<?= BASE_URL ?>/lupa_password" class="inline-flex items-center gap-2 py-3 px-8 bg-gradient-to-r from-nusa to-[#b8277a] text-white font-bold rounded-xl shadow-lg shadow-nusa/30 hover:-translate-y-0.5 transition-all duration-200">
          Coba Lagi
        </a>
      </div>
      <?php endif; ?>
    </div>

    <div class="px-8 py-4 border-t border-slate-100 dark:border-slate-700 text-center text-xs text-slate-400 dark:text-slate-500">
      &copy; <?= date('Y') ?> Universitas Nusa Putra — Pascasarjana
    </div>
  </div>
</body>
</html>
