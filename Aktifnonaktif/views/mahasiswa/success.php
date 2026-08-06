<?php
$data['title'] = 'Pengajuan Berhasil - ' . APP_NAME;
include BASE_PATH . '/includes/public_layout_top.php';
$pengajuan = $data['pengajuan'];
?>

<div class="max-w-2xl mx-auto">
  <!-- Success Hero -->
  <div class="card text-center p-10 mb-6 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/10 dark:to-emerald-900/10"></div>
    <div class="relative z-10">
      <div class="w-24 h-24 rounded-full bg-green-100 dark:bg-green-900/30 border-4 border-green-300 dark:border-green-700 flex items-center justify-center mx-auto mb-6 animate-bounce" style="animation-duration:2s">
        <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <h1 class="font-display font-bold text-3xl text-gray-800 dark:text-white mb-2">Pengajuan Berhasil! 🎉</h1>
      <p class="text-gray-500 dark:text-gray-400 text-base mb-6">Formulir pengunduran diri Anda telah berhasil dikirim dan sedang menunggu proses verifikasi dari administrator.</p>

      <!-- Status Badge -->
      <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-sm font-semibold border border-yellow-200 dark:border-yellow-700">
        <span class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></span>
        Status: Menunggu Persetujuan Admin
      </span>
    </div>
  </div>

  <!-- Summary Card -->
  <div class="card p-6 mb-6">
    <h2 class="font-display font-bold text-gray-800 dark:text-white text-base mb-4">Ringkasan Pengajuan</h2>
    <div class="space-y-3 text-sm">
      <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
        <span class="text-gray-500 dark:text-gray-400">Nomor Surat</span>
        <span class="font-semibold text-gray-800 dark:text-white">
          <?= e($pengajuan['nomor_surat'] ?? 'Akan diberikan oleh administrator') ?>
        </span>
      </div>
      <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
        <span class="text-gray-500 dark:text-gray-400">Tanggal Surat</span>
        <span class="font-semibold text-gray-800 dark:text-white"><?= formatTanggal($pengajuan['tanggal_surat']) ?></span>
      </div>
      <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
        <span class="text-gray-500 dark:text-gray-400">Nama Pemohon</span>
        <span class="font-semibold text-gray-800 dark:text-white"><?= e($pengajuan['nama_pemohon']) ?></span>
      </div>
      <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
        <span class="text-gray-500 dark:text-gray-400">NIM</span>
        <span class="font-semibold text-gray-800 dark:text-white"><?= e($pengajuan['nim']) ?></span>
      </div>
      <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
        <span class="text-gray-500 dark:text-gray-400">Program Studi</span>
        <span class="font-semibold text-gray-800 dark:text-white"><?= e($pengajuan['program_studi']) ?></span>
      </div>
      <div class="flex items-center justify-between py-2">
        <span class="text-gray-500 dark:text-gray-400">Bersedia Mundur</span>
        <span class="font-semibold <?= $pengajuan['bersedia_mundur'] === 'YES' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
          <?= $pengajuan['bersedia_mundur'] === 'YES' ? '✅ Ya' : '❌ Tidak' ?>
        </span>
      </div>
    </div>
  </div>

  <!-- What's Next -->
  <div class="card p-6 mb-6">
    <h2 class="font-display font-bold text-gray-800 dark:text-white text-base mb-4">Langkah Selanjutnya</h2>
    <div class="space-y-4">
      <div class="flex gap-3">
        <div class="w-8 h-8 rounded-full bg-nusa/10 flex items-center justify-center text-nusa font-bold text-sm flex-shrink-0">1</div>
        <div>
          <p class="text-gray-800 dark:text-gray-200 text-sm font-semibold">Pengajuan Diterima</p>
          <p class="text-gray-500 dark:text-gray-400 text-xs mt-0.5">Formulir Anda sudah masuk ke sistem dan dalam antrian review</p>
        </div>
      </div>
      <div class="flex gap-3">
        <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 font-bold text-sm flex-shrink-0">2</div>
        <div>
          <p class="text-gray-800 dark:text-gray-200 text-sm font-semibold">Verifikasi Admin</p>
          <p class="text-gray-500 dark:text-gray-400 text-xs mt-0.5">Administrator akan memverifikasi data dan dokumen Anda</p>
        </div>
      </div>
      <div class="flex gap-3">
        <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 font-bold text-sm flex-shrink-0">3</div>
        <div>
          <p class="text-gray-800 dark:text-gray-200 text-sm font-semibold">Keputusan</p>
          <p class="text-gray-500 dark:text-gray-400 text-xs mt-0.5">Anda akan mendapat notifikasi mengenai hasil pengajuan</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="flex flex-col sm:flex-row gap-3">
    <?php if ($pengajuan['bersedia_mundur'] === 'YES'): ?>
    <a href="<?= APP_URL ?>/?page=pdf&id=<?= $pengajuan['id'] ?>&print=1" target="_blank"
       class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-nusa text-white font-semibold rounded-xl hover:bg-nusa-dark shadow-md shadow-nusa/30 transition-all duration-200">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
      Cetak PDF
    </a>
    <a href="<?= APP_URL ?>/?page=docx&id=<?= $pengajuan['id'] ?>"
       class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 shadow-md shadow-blue-600/30 transition-all duration-200">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
      Download Word
    </a>
    <?php endif; ?>
    <a href="<?= APP_URL ?>/?page=mahasiswa/form"
       class="flex-1 flex items-center justify-center gap-2 py-3.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/></svg>
      Kembali ke Form
    </a>
  </div>
</div>

<?php include BASE_PATH . '/includes/public_layout_bottom.php'; ?>
