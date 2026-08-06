<?php
requireMahasiswa();
$data['title'] = 'Profil Saya - ' . APP_NAME;
include BASE_PATH . '/includes/mahasiswa_layout_top.php';
$mahasiswa = $data['mahasiswa'];
$user      = $data['user'];
?>

<div class="max-w-2xl mx-auto">
  <div class="mb-6">
    <h1 class="font-display font-bold text-2xl text-gray-800 dark:text-white">Profil Saya</h1>
    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Informasi akun dan data mahasiswa Anda</p>
  </div>

  <?php foreach (getFlash('success') as $msg): ?>
  <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 text-sm rounded-xl px-4 py-3 mb-4">
    ✓ <?= e($msg) ?>
  </div>
  <?php endforeach; ?>

  <!-- Profile Card -->
  <div class="card p-6 mb-6">
    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
      <!-- Avatar -->
      <div class="relative flex-shrink-0">
        <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-nusa to-nusa-dark flex items-center justify-center text-4xl text-white shadow-lg shadow-nusa/30 overflow-hidden border border-nusa/20">
          <?php $avatarUrl = currentUserAvatar(); ?>
          <?php if (!empty($avatarUrl)): ?>
              <img src="<?= e($avatarUrl) ?>" alt="Avatar" class="w-full h-full object-cover">
          <?php else: ?>
              <?= strtoupper(substr($mahasiswa['nama'] ?? $user['nama'] ?? 'M', 0, 1)) ?>
          <?php endif; ?>
        </div>
        <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-green-500 border-2 border-white dark:border-gray-800"></div>
      </div>

      <!-- Basic Info -->
      <div class="flex-1 text-center sm:text-left">
        <h2 class="font-display font-bold text-gray-800 dark:text-white text-xl"><?= e($mahasiswa['nama'] ?? $user['nama']) ?></h2>
        <p class="text-nusa font-medium text-sm mt-1"><?= e($mahasiswa['nim'] ?? '—') ?></p>
        <p class="text-gray-500 dark:text-gray-400 text-sm"><?= e($mahasiswa['program_studi'] ?? '—') ?></p>

        <div class="flex flex-wrap gap-2 mt-3 justify-center sm:justify-start">
          <span class="px-3 py-1 bg-nusa/10 text-nusa dark:text-nusa-light text-xs font-semibold rounded-full">
            Angkatan <?= e($mahasiswa['angkatan'] ?? '—') ?>
          </span>
          <span class="px-3 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded-full">
            <?= e($mahasiswa['status_beasiswa'] ?? '—') ?>
          </span>
          <span class="px-3 py-1 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-xs font-semibold rounded-full">
            Aktif
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Data Academic (Read-Only) -->
  <div class="card p-6 mb-6">
    <h3 class="font-display font-bold text-gray-700 dark:text-gray-200 text-sm mb-4 uppercase tracking-wider">Data Akademik</h3>
    <div class="grid md:grid-cols-2 gap-4 text-sm">
      <?php $fields = [
        'NIM'            => $mahasiswa['nim'] ?? '—',
        'Angkatan'       => $mahasiswa['angkatan'] ?? '—',
        'Program Studi'  => $mahasiswa['program_studi'] ?? '—',
        'Status Beasiswa'=> $mahasiswa['status_beasiswa'] ?? '—',
        'Email'          => $user['email'] ?? '—',
        'Bergabung Sejak'=> formatTanggal($mahasiswa['created_at'] ?? date('Y-m-d')),
      ]; ?>
      <?php foreach ($fields as $label => $value): ?>
      <div class="flex flex-col gap-1 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
        <span class="text-gray-400 dark:text-gray-500 text-xs font-medium uppercase tracking-wide"><?= e($label) ?></span>
        <span class="text-gray-800 dark:text-gray-200 font-semibold"><?= e($value) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Editable Fields -->
  <form method="POST" action="<?= APP_URL ?>/?page=mahasiswa/profile/update">
    <?= csrfField() ?>
    <div class="card p-6 mb-6">
      <h3 class="font-display font-bold text-gray-700 dark:text-gray-200 text-sm mb-4 uppercase tracking-wider">Informasi Kontak</h3>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Nomor HP/WhatsApp</label>
          <input type="tel" name="no_hp" value="<?= e($mahasiswa['no_hp'] ?? '') ?>"
            placeholder="Contoh: 081234567890"
            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-nusa/50 focus:border-nusa transition-all">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Alamat Lengkap</label>
          <textarea name="alamat" rows="3"
            placeholder="Alamat lengkap Anda..."
            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-nusa/50 focus:border-nusa transition-all resize-none"><?= e($mahasiswa['alamat'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <div class="flex justify-end">
      <button type="submit"
        class="px-8 py-3 bg-nusa text-white font-semibold rounded-xl hover:bg-nusa-dark shadow-md shadow-nusa/30 transition-all duration-200 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Simpan Perubahan
      </button>
    </div>
  </form>
</div>

<?php include BASE_PATH . '/includes/mahasiswa_layout_bottom.php'; ?>
