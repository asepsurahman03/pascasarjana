<?php
// Halaman: Ganti Password (untuk user yang sudah login)
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user   = getCurrentUser();
$error  = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPw  = $_POST['old_password']  ?? '';
    $newPw  = $_POST['new_password']  ?? '';
    $confPw = $_POST['confirm_password'] ?? '';

    $dbUser = dbQueryOne("SELECT * FROM users WHERE id=?", [$user['id']]);

    if (empty($oldPw) || empty($newPw) || empty($confPw)) {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!password_verify($oldPw, $dbUser['password_hash'])) {
        $error = 'Password lama tidak sesuai.';
    } elseif (strlen($newPw) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } elseif ($newPw !== $confPw) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $hash = password_hash($newPw, PASSWORD_DEFAULT);
        dbExecute("UPDATE users SET password_hash=? WHERE id=?", [$hash, $user['id']]);
        logActivity('Ganti Password', 'auth', 'Password berhasil diubah');
        $success = 'Password berhasil diubah!';
    }
}

$pageTitle = 'Ganti Password';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-lg mx-auto mt-6">
  <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700/60 overflow-hidden">
    <!-- Header -->
    <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-nusa to-[#b8277a] flex items-center justify-center text-white shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
      </div>
      <div>
        <h1 class="font-display font-bold text-xl text-slate-800 dark:text-white">Ganti Password</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Perbarui kata sandi akun Anda</p>
      </div>
    </div>

    <div class="p-6 md:p-8" x-data="{ showOld: false, showNew: false, showConf: false }">
      <?php if ($error): ?>
      <div class="mb-5 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl px-4 py-3 text-sm">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
        <span><?= e($error) ?></span>
      </div>
      <?php endif; ?>

      <?php if ($success): ?>
      <div class="mb-5 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl px-4 py-3 text-sm">
        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span><?= e($success) ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" class="space-y-5">
        <!-- Password Lama -->
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Password Lama</label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-nusa transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <input :type="showOld ? 'text' : 'password'" name="old_password" required placeholder="Masukkan password lama"
              class="w-full py-3 pl-11 pr-11 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-nusa focus:ring-4 focus:ring-nusa/20 transition-all">
            <button type="button" @click="showOld=!showOld" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-nusa transition-colors">
              <svg x-show="!showOld" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <svg x-show="showOld" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            </button>
          </div>
        </div>

        <!-- Password Baru -->
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Password Baru</label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-nusa transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <input :type="showNew ? 'text' : 'password'" name="new_password" id="new_password" required placeholder="Min. 8 karakter"
              class="w-full py-3 pl-11 pr-11 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-nusa focus:ring-4 focus:ring-nusa/20 transition-all">
            <button type="button" @click="showNew=!showNew" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-nusa transition-colors">
              <svg x-show="!showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <svg x-show="showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            </button>
          </div>
          <!-- Strength indicator -->
          <div class="mt-2" x-data="pwStrength()" x-init="$watch('pw', v => check(v))">
            <input type="hidden" x-model="pw" x-bind:value="$el.closest('form').querySelector('#new_password').value" @input.document="pw=$event.target.id==='new_password'?$event.target.value:pw">
            <div class="flex gap-1 mt-1">
              <div class="h-1 flex-1 rounded-full transition-colors" :class="score>=1?'bg-red-400':'bg-slate-200 dark:bg-slate-700'"></div>
              <div class="h-1 flex-1 rounded-full transition-colors" :class="score>=2?'bg-amber-400':'bg-slate-200 dark:bg-slate-700'"></div>
              <div class="h-1 flex-1 rounded-full transition-colors" :class="score>=3?'bg-yellow-400':'bg-slate-200 dark:bg-slate-700'"></div>
              <div class="h-1 flex-1 rounded-full transition-colors" :class="score>=4?'bg-emerald-400':'bg-slate-200 dark:bg-slate-700'"></div>
            </div>
            <p class="text-xs mt-1 transition-colors" :class="score>=3?'text-emerald-600 dark:text-emerald-400':'text-slate-400'" x-text="label"></p>
          </div>
        </div>

        <!-- Konfirmasi Password -->
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Konfirmasi Password Baru</label>
          <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-nusa transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <input :type="showConf ? 'text' : 'password'" name="confirm_password" required placeholder="Ulangi password baru"
              class="w-full py-3 pl-11 pr-11 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 text-slate-800 dark:text-slate-100 focus:outline-none focus:border-nusa focus:ring-4 focus:ring-nusa/20 transition-all">
            <button type="button" @click="showConf=!showConf" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-nusa transition-colors">
              <svg x-show="!showConf" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <svg x-show="showConf" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            </button>
          </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
          <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-nusa to-[#b8277a] hover:from-nusa-dark hover:to-nusa text-white font-bold rounded-xl shadow-lg shadow-nusa/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan Password
          </button>
          <a href="<?= BASE_URL ?>/index" class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 font-semibold transition-colors text-sm">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function pwStrength() {
  return {
    pw: '',
    score: 0,
    label: '',
    check(v) {
      let s = 0;
      if (v.length >= 8) s++;
      if (/[A-Z]/.test(v)) s++;
      if (/[0-9]/.test(v)) s++;
      if (/[^A-Za-z0-9]/.test(v)) s++;
      this.score = s;
      this.label = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'][s] || '';
    }
  }
}
// Sync password field to Alpine component
document.addEventListener('DOMContentLoaded', () => {
  const pwField = document.getElementById('new_password');
  if (pwField) {
    pwField.addEventListener('input', e => {
      pwField.dispatchEvent(new Event('input', { bubbles: true }));
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
