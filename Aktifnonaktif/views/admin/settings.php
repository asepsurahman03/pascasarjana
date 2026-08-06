<?php
requireAdmin();
$data['title'] = 'Pengaturan Sistem - ' . APP_NAME;
include BASE_PATH . '/includes/admin_layout_top.php';
$settings = $data['settings'];
// Convert to key=>value map
$sMap = [];
foreach ($settings as $s) $sMap[$s['key_name']] = $s['value'];
?>

<div class="mb-6">
  <h1 class="font-display font-bold text-2xl text-slate-800 dark:text-white">Pengaturan Sistem</h1>
  <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Konfigurasi umum sistem pengunduran diri mahasiswa</p>
</div>

<?php foreach (getFlash('success') as $msg): ?>
<div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 text-sm rounded-xl px-4 py-3 mb-5">✓ <?= e($msg) ?></div>
<?php endforeach; ?>
<?php foreach (getFlash('error') as $msg): ?>
<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 text-sm rounded-xl px-4 py-3 mb-5">✗ <?= e($msg) ?></div>
<?php endforeach; ?>

<form method="POST" action="<?= APP_URL ?>/?page=admin/settings/save">
  <?= csrfField() ?>

  <div class="grid lg:grid-cols-2 gap-6">

    <!-- Informasi Universitas -->
    <div class="card p-6">
      <h2 class="font-display font-bold text-slate-700 dark:text-slate-200 text-sm mb-4 uppercase tracking-wider flex items-center gap-2">
        🏫 Informasi Universitas
      </h2>
      <div class="space-y-4">
        <?php
        $uniFields = [
          ['key' => 'university_name',    'label' => 'Nama Universitas'],
          ['key' => 'university_address', 'label' => 'Alamat'],
          ['key' => 'university_phone',   'label' => 'Telepon'],
          ['key' => 'university_email',   'label' => 'Email'],
          ['key' => 'university_website', 'label' => 'Website'],
        ];
        foreach ($uniFields as $f):
        ?>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5"><?= $f['label'] ?></label>
          <input type="text" name="<?= $f['key'] ?>" value="<?= e($sMap[$f['key']] ?? '') ?>"
            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Surat & Sistem -->
    <div class="space-y-6">
      <div class="card p-6">
        <h2 class="font-display font-bold text-slate-700 dark:text-slate-200 text-sm mb-4 uppercase tracking-wider">📝 Nomor Surat</h2>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Prefix Nomor Surat</label>
          <input type="text" name="nomor_surat_prefix" value="<?= e($sMap['nomor_surat_prefix'] ?? 'NPU/PD/') ?>"
            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50 font-mono">
          <p class="text-slate-400 text-xs mt-1">Contoh: NPU/PD/ → NPU/PD/2024/06/0001</p>
        </div>
      </div>

      <div class="card p-6">
        <h2 class="font-display font-bold text-slate-700 dark:text-slate-200 text-sm mb-4 uppercase tracking-wider">🔒 Keamanan</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Session Timeout (detik)</label>
            <input type="number" name="session_timeout" value="<?= e($sMap['session_timeout'] ?? '3600') ?>"
              min="300" max="86400"
              class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
            <p class="text-slate-400 text-xs mt-1">3600 = 1 jam</p>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Maks. Percobaan Login</label>
            <input type="number" name="max_login_attempts" value="<?= e($sMap['max_login_attempts'] ?? '5') ?>"
              min="3" max="20"
              class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
          </div>
        </div>
      </div>
    </div>

    <!-- Ketua Program Studi -->
    <div class="card p-6 lg:col-span-2">
      <h2 class="font-display font-bold text-slate-700 dark:text-slate-200 text-sm mb-4 uppercase tracking-wider">👨‍🏫 Ketua Program Studi</h2>
      <div class="grid md:grid-cols-2 gap-4">
        <?php
        $ketuaFields = [
          ['key' => 'ketua_prodi_s1_teknik_informatika', 'label' => 'Ketua Prodi S1 - Teknik Informatika'],
          ['key' => 'ketua_prodi_s1_manajemen', 'label' => 'Ketua Prodi S1 - Manajemen'],
          ['key' => 'ketua_prodi_s1_akuntansi', 'label' => 'Ketua Prodi S1 - Akuntansi'],
          ['key' => 'ketua_prodi_s1_teknik_sipil', 'label' => 'Ketua Prodi S1 - Teknik Sipil'],
          ['key' => 'ketua_prodi_s1_sistem_informasi', 'label' => 'Ketua Prodi S1 - Sistem Informasi'],
          ['key' => 'ketua_prodi_s1_hukum', 'label' => 'Ketua Prodi S1 - Hukum'],
          ['key' => 'ketua_prodi_s1_pendidikan_guru_sekolah_dasar', 'label' => 'Ketua Prodi S1 - Pendidikan Guru Sekolah Dasar'],
          ['key' => 'ketua_prodi_s1_teknik_mesin', 'label' => 'Ketua Prodi S1 - Teknik Mesin'],
          ['key' => 'ketua_prodi_s1_teknik_elektro', 'label' => 'Ketua Prodi S1 - Teknik Elektro'],
          ['key' => 'ketua_prodi_s1_desain_komunikasi_visual', 'label' => 'Ketua Prodi S1 - Desain Komunikasi Visual'],
          ['key' => 'ketua_prodi_s1_gizi', 'label' => 'Ketua Prodi S1 - Gizi'],
          ['key' => 'ketua_prodi_s1_bioteknologi', 'label' => 'Ketua Prodi S1 - Bioteknologi'],
          ['key' => 'ketua_prodi_s1_teknologi_pangan', 'label' => 'Ketua Prodi S1 - Teknologi Pangan'],
          ['key' => 'ketua_prodi_s1_administrasi_kesehatan', 'label' => 'Ketua Prodi S1 - Administrasi Kesehatan'],
          ['key' => 'ketua_prodi_d3_keperawatan', 'label' => 'Ketua Prodi D3 - Keperawatan'],
          ['key' => 'ketua_prodi_s2_magister_informatika', 'label' => 'Ketua Prodi S2 - Magister Informatika'],
          ['key' => 'ketua_prodi_s2_magister_hukum', 'label' => 'Ketua Prodi S2 - Magister Hukum'],
          ['key' => 'ketua_prodi_s2_magister_pedagogi', 'label' => 'Ketua Prodi S2 - Magister Pedagogi'],
          ['key' => 'ketua_prodi_s2_magister_manajemen', 'label' => 'Ketua Prodi S2 - Magister Manajemen'],
          ['key' => 'ketua_prodi_s3_doktor_ilmu_komputer', 'label' => 'Ketua Prodi S3 - Doktor Ilmu Komputer'],
        ];
        foreach ($ketuaFields as $f):
        ?>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5"><?= $f['label'] ?></label>
          <input type="text" name="<?= $f['key'] ?>" value="<?= e($sMap[$f['key']] ?? '') ?>"
            placeholder="Nama lengkap beserta gelar"
            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="flex justify-end mt-6">
    <button type="submit"
      class="px-8 py-3 bg-nusa text-white font-semibold rounded-xl hover:bg-nusa-dark shadow-md shadow-nusa/30 transition flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      Simpan Semua Pengaturan
    </button>
  </div>
</form>

<?php include BASE_PATH . '/includes/admin_layout_bottom.php'; ?>
