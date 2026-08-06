<?php
requireMahasiswa();
$data['title'] = 'Riwayat Pengajuan - ' . APP_NAME;
include BASE_PATH . '/includes/mahasiswa_layout_top.php';
$pengajuan = $data['pengajuan'];
$mahasiswa = $data['mahasiswa'];
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="font-display font-bold text-2xl text-gray-800 dark:text-white">Riwayat Pengajuan</h1>
    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Semua formulir pengunduran diri yang pernah Anda ajukan</p>
  </div>
  <a href="<?= APP_URL ?>/?page=mahasiswa/form"
     class="flex items-center justify-center sm:justify-start gap-2 px-4 py-2.5 bg-nusa text-white text-sm font-semibold rounded-xl hover:bg-nusa-dark shadow-md shadow-nusa/30 transition w-full sm:w-auto">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Ajukan Baru
  </a>
</div>

<?php if (empty($pengajuan)): ?>
<!-- Empty State -->
<div class="card flex flex-col items-center justify-center py-20 text-center">
  <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-5">
    <svg class="w-10 h-10 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
  </div>
  <h2 class="font-display font-bold text-gray-700 dark:text-gray-200 text-lg mb-2">Belum Ada Pengajuan</h2>
  <p class="text-gray-400 dark:text-gray-500 text-sm mb-6 max-w-sm">Anda belum pernah mengajukan formulir pengunduran diri. Klik tombol di bawah untuk memulai.</p>
  <a href="<?= APP_URL ?>/?page=mahasiswa/form"
     class="px-6 py-3 bg-nusa text-white font-semibold rounded-xl hover:bg-nusa-dark shadow-md shadow-nusa/30 transition">
    Isi Formulir Sekarang
  </a>
</div>

<?php else: ?>
<!-- Pengajuan Cards -->
<div class="space-y-4">
  <?php foreach ($pengajuan as $i => $p): ?>
  <div class="card p-5 hover:shadow-md transition-shadow duration-200 group">
    <div class="flex flex-col md:flex-row md:items-center gap-4">

      <!-- Icon + Number -->
      <div class="flex items-center gap-3 flex-1 min-w-0">
        <div class="w-12 h-12 rounded-xl bg-nusa/10 flex items-center justify-center flex-shrink-0 group-hover:bg-nusa/20 transition">
          <svg class="w-6 h-6 text-nusa" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div class="min-w-0">
          <p class="font-semibold text-gray-800 dark:text-white text-sm">
            <?= e($p['nomor_surat'] ?? 'Nomor belum ditetapkan') ?>
          </p>
          <p class="text-gray-400 dark:text-gray-500 text-xs mt-0.5">
            Diajukan <?= timeAgo($p['created_at']) ?>
          </p>
        </div>
      </div>

      <!-- Info Columns -->
      <div class="flex flex-wrap gap-4 md:gap-6 text-sm">
        <div>
          <p class="text-gray-400 dark:text-gray-500 text-xs">Tanggal Surat</p>
          <p class="text-gray-700 dark:text-gray-300 font-medium"><?= formatTanggal($p['tanggal_surat']) ?></p>
        </div>
        <div>
          <p class="text-gray-400 dark:text-gray-500 text-xs">Program Studi</p>
          <p class="text-gray-700 dark:text-gray-300 font-medium text-xs"><?= e($p['program_studi']) ?></p>
        </div>
        <div>
          <p class="text-gray-400 dark:text-gray-500 text-xs">Bersedia Mundur</p>
          <p class="font-medium text-xs <?= $p['bersedia_mundur'] === 'YES' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
            <?= $p['bersedia_mundur'] === 'YES' ? '✅ Ya' : '❌ Tidak' ?>
          </p>
        </div>
      </div>

      <!-- Status + Actions -->
      <div class="flex items-center gap-3 flex-shrink-0">
        <span class="badge <?= statusBadge($p['status']) ?>"><?= statusLabel($p['status']) ?></span>

        <div class="flex gap-2">
          <?php if ($p['bersedia_mundur'] === 'YES'): ?>
          <a href="<?= APP_URL ?>/?page=pdf&id=<?= $p['id'] ?>&print=1" target="_blank"
             class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-nusa hover:text-white transition"
             title="Cetak PDF">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Catatan Admin (if rejected) -->
    <?php if ($p['status'] === 'Rejected' && !empty($p['catatan_admin'])): ?>
    <div class="mt-4 pt-4 border-t border-red-100 dark:border-red-900/30">
      <p class="text-xs font-semibold text-red-600 dark:text-red-400 mb-1">📝 Catatan Administrator:</p>
      <p class="text-sm text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 rounded-lg px-3 py-2">
        <?= e($p['catatan_admin']) ?>
      </p>
    </div>
    <?php endif; ?>

    <!-- Approved info -->
    <?php if ($p['status'] === 'Approved'): ?>
    <div class="mt-4 pt-4 border-t border-green-100 dark:border-green-900/30">
      <p class="text-xs text-green-600 dark:text-green-400 font-medium">
        ✓ Disetujui pada <?= formatDatetime($p['approved_at'] ?? $p['updated_at']) ?>
      </p>
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include BASE_PATH . '/includes/mahasiswa_layout_bottom.php'; ?>
