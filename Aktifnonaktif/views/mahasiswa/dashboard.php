<?php
requireMahasiswa();
$data['title'] = 'Dashboard - ' . APP_NAME;
include BASE_PATH . '/includes/mahasiswa_layout_top.php';

$user           = $data['user'];
$mahasiswa      = $data['mahasiswa'];
$lastPengajuan  = $data['lastPengajuan'];
$progress       = $data['progress'];
$totalPengajuan = $data['totalPengajuan'];
$pengajuan      = $data['pengajuan'];
?>

<!-- Welcome Card -->
<div class="rounded-3xl bg-gradient-to-br from-nusa via-[#7a1346] to-[#500b2a] p-8 text-white mb-8 relative overflow-hidden shadow-xl shadow-nusa/20">
  <div class="absolute top-0 right-0 w-80 h-80 bg-white/5 rounded-full -translate-y-1/3 translate-x-1/3 blur-2xl"></div>
  <div class="absolute bottom-0 left-1/4 w-60 h-60 bg-white/5 rounded-full translate-y-1/3 blur-xl"></div>
  
  <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-6">
    <div class="w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-4xl flex-shrink-0 border border-white/20 shadow-inner">
      <?php if (!empty($user['avatar'])): ?>
          <img src="<?= e($user['avatar']) ?>" alt="Avatar" class="w-full h-full object-cover rounded-2xl">
      <?php else: ?>
          🎓
      <?php endif; ?>
    </div>
    <div class="flex-1">
      <p class="text-white/80 text-sm mb-1 font-medium tracking-wide">Selamat datang kembali,</p>
      <h1 class="font-display font-bold text-3xl md:text-4xl tracking-tight mb-2"><?= e($mahasiswa['nama'] ?? $user['nama']) ?></h1>
      
      <?php if (!empty($mahasiswa)): ?>
          <div class="flex flex-wrap items-center gap-3 text-white/80 text-sm font-medium">
            <span class="bg-white/10 px-3 py-1 rounded-lg border border-white/10"><?= e($mahasiswa['program_studi']) ?></span>
            <span class="bg-white/10 px-3 py-1 rounded-lg border border-white/10">Angkatan <?= e($mahasiswa['angkatan']) ?></span>
            <span class="bg-white/10 px-3 py-1 rounded-lg border border-white/10">NIM: <?= e($mahasiswa['nim']) ?></span>
          </div>
      <?php else: ?>
          <div class="flex items-center gap-2 text-amber-200 text-sm font-medium bg-amber-500/20 w-fit px-4 py-1.5 rounded-lg border border-amber-500/30">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Profil Anda belum lengkap. Silakan isi formulir pengunduran diri.
          </div>
      <?php endif; ?>
    </div>
    
    <?php if (!empty($mahasiswa['status_beasiswa'])): ?>
    <div class="flex-shrink-0 self-start md:self-center mt-4 md:mt-0">
      <span class="px-4 py-2 rounded-xl bg-white/20 text-white text-sm font-bold backdrop-blur-md border border-white/30 shadow-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        <?= e($mahasiswa['status_beasiswa']) ?>
      </span>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <!-- Total Pengajuan -->
  <div class="card p-4">
    <div class="flex items-start justify-between mb-3">
      <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      </div>
    </div>
    <p class="font-display font-bold text-2xl text-gray-800 dark:text-white"><?= $totalPengajuan ?></p>
    <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">Total Pengajuan</p>
  </div>

  <!-- Status Terakhir -->
  <?php
  $statusIconClasses = [
      'Approved' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400',
      'Rejected' => 'bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400',
      'Pending'  => 'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400'
  ];
  $iconClass = $lastPengajuan ? ($statusIconClasses[$lastPengajuan['status']] ?? 'bg-slate-50 text-slate-500') : 'bg-slate-100 dark:bg-slate-800 text-slate-400';
  ?>
  <div class="card p-4">
    <div class="flex items-start justify-between mb-3">
      <div class="w-10 h-10 rounded-xl flex items-center justify-center <?= $iconClass ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
    </div>
    <?php if ($lastPengajuan): ?>
    <span class="badge <?= statusBadge($lastPengajuan['status']) ?>"><?= statusLabel($lastPengajuan['status']) ?></span>
    <?php else: ?>
    <p class="font-display font-bold text-2xl text-gray-400">—</p>
    <?php endif; ?>
    <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">Status Terakhir</p>
  </div>

  <!-- Progress -->
  <div class="card p-4 col-span-2">
    <div class="flex items-center justify-between mb-2">
      <p class="text-gray-700 dark:text-gray-300 text-sm font-semibold">Progress Pengajuan</p>
      <span class="text-nusa font-bold text-sm"><?= $progress ?>%</span>
    </div>
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-2">
      <div class="bg-gradient-to-r from-nusa to-nusa-light h-3 rounded-full transition-all duration-700"
           style="width: <?= $progress ?>%"></div>
    </div>
    <div class="flex justify-between text-xs text-gray-400">
      <span>Pengisian</span>
      <span>Terkirim</span>
      <span>Proses</span>
      <span>Selesai</span>
    </div>
  </div>
</div>

<!-- Quick Actions + Recent Submissions -->
<div class="grid md:grid-cols-3 gap-6">

  <!-- Quick Actions -->
  <div class="card p-5">
    <h2 class="font-display font-bold text-gray-800 dark:text-white text-base mb-4">Aksi Cepat</h2>
    <div class="space-y-3">
      <a href="<?= APP_URL ?>/?page=mahasiswa/form"
         class="flex items-center gap-3 p-3.5 rounded-xl bg-nusa/5 hover:bg-nusa/10 border border-nusa/20 transition-all duration-200 group">
        <div class="w-9 h-9 rounded-lg bg-nusa flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </div>
        <div>
          <p class="text-gray-800 dark:text-white text-sm font-semibold">Isi Formulir Baru</p>
          <p class="text-gray-500 dark:text-gray-400 text-xs">Ajukan pengunduran diri</p>
        </div>
      </a>

      <a href="<?= APP_URL ?>/?page=mahasiswa/riwayat"
         class="flex items-center gap-3 p-3.5 rounded-xl bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 border border-blue-100 dark:border-blue-800 transition-all duration-200 group">
        <div class="w-9 h-9 rounded-lg bg-blue-500 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
          <p class="text-gray-800 dark:text-white text-sm font-semibold">Riwayat Pengajuan</p>
          <p class="text-gray-500 dark:text-gray-400 text-xs">Lihat semua pengajuan</p>
        </div>
      </a>

      <?php if ($lastPengajuan && $lastPengajuan['bersedia_mundur'] === 'YES'): ?>
      <a href="<?= APP_URL ?>/?page=pdf&id=<?= $lastPengajuan['id'] ?>&print=1"
         target="_blank"
         class="flex items-center gap-3 p-3.5 rounded-xl bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 border border-green-100 dark:border-green-800 transition-all duration-200 group">
        <div class="w-9 h-9 rounded-lg bg-green-500 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        </div>
        <div>
          <p class="text-gray-800 dark:text-white text-sm font-semibold">Cetak Formulir</p>
          <p class="text-gray-500 dark:text-gray-400 text-xs">PDF pengajuan terakhir</p>
        </div>
      </a>
      
      <a href="<?= APP_URL ?>/?page=docx&id=<?= $lastPengajuan['id'] ?>"
         class="flex items-center gap-3 p-3.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 transition-all duration-200 group">
        <div class="w-9 h-9 rounded-lg bg-indigo-500 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        </div>
        <div>
          <p class="text-gray-800 dark:text-white text-sm font-semibold">Download Word</p>
          <p class="text-gray-500 dark:text-gray-400 text-xs">DOCX pengajuan terakhir</p>
        </div>
      </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recent Submissions -->
  <div class="card p-5 md:col-span-2">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-display font-bold text-gray-800 dark:text-white text-base">Pengajuan Terbaru</h2>
      <a href="<?= APP_URL ?>/?page=mahasiswa/riwayat" class="text-nusa text-xs font-medium hover:underline">Lihat semua →</a>
    </div>

    <?php if (empty($pengajuan)): ?>
    <!-- Empty State -->
    <div class="flex flex-col items-center justify-center py-10 text-center">
      <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      </div>
      <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Belum Ada Pengajuan</p>
      <p class="text-gray-400 dark:text-gray-500 text-xs mb-4">Anda belum pernah mengajukan pengunduran diri</p>
      <a href="<?= APP_URL ?>/?page=mahasiswa/form"
         class="px-5 py-2.5 bg-nusa text-white text-sm font-semibold rounded-xl hover:bg-nusa-dark transition shadow-md shadow-nusa/30">
        Isi Formulir Sekarang
      </a>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
      <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700/50 dark:text-gray-300">
          <tr>
            <th scope="col" class="px-4 py-3">No. Surat</th>
            <th scope="col" class="px-4 py-3">Tanggal</th>
            <th scope="col" class="px-4 py-3 text-right">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <?php foreach (array_slice($pengajuan, 0, 4) as $p): ?>
          <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
              <?= e($p['nomor_surat'] ?? 'Belum ada nomor') ?>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
              <?= formatTanggal($p['tanggal_surat']) ?>
            </td>
            <td class="px-4 py-3 text-right whitespace-nowrap">
              <span class="badge <?= statusBadge($p['status']) ?>"><?= statusLabel($p['status']) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php include BASE_PATH . '/includes/mahasiswa_layout_bottom.php'; ?>
