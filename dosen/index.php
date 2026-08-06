<?php
$pageTitle = 'Dashboard Dosen';
require_once 'header.php';

$mahasiswa_bimbingan = [
  ['nama'=>'Ahmad Rizki Pratama','nim'=>'2023MIF001','status_tesis'=>'Penelitian','bimbingan'=>7,'persen'=>65,'last_bimbingan'=>'08 Jul 2025','logbook_pending'=>0],
  ['nama'=>'Budi Hermawan','nim'=>'2023MIF002','status_tesis'=>'Penulisan','bimbingan'=>10,'persen'=>80,'last_bimbingan'=>'10 Jul 2025','logbook_pending'=>1],
  ['nama'=>'Citra Dewi','nim'=>'2022MIF001','status_tesis'=>'Penelitian','bimbingan'=>5,'persen'=>45,'last_bimbingan'=>'01 Jul 2025','logbook_pending'=>0],
];
$undangan_penguji = [
  ['mahasiswa'=>'Budi Hermawan','jenis'=>'Sidang Tesis','judul'=>'Sistem Rekomendasi Berbasis CF','batas_vote'=>'Besok, 18 Jul 2025 23:59','voted'=>false],
];
$jadwal_upcoming = [
  ['jenis'=>'Seminar Proposal','mahasiswa'=>'Hana Safitri','tanggal'=>'Rabu, 23 Jul 2025','jam'=>'13.00','ruang'=>'R. Sidang A','peran'=>'Pembimbing'],
  ['jenis'=>'Sidang Tesis','mahasiswa'=>'Budi Hermawan','tanggal'=>'Senin, 21 Jul 2025','jam'=>'09.00','ruang'=>'R. Seminar Lt.3','peran'=>'Penguji Utama'],
];
?>

<!-- Welcome -->
<div class="relative rounded-2xl overflow-hidden mb-6 p-6 text-white" style="background:linear-gradient(135deg,#961d5a 0%,#6b1040 60%,#4a0d2e 100%)">
  <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10" style="background:white;transform:translate(30%,-30%)"></div>
  <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <p class="text-white/70 text-sm">Selamat datang 👋</p>
      <h2 class="font-display font-bold text-2xl mt-0.5"><?= $dosen['nama'] ?></h2>
      <p class="text-white/70 text-sm mt-1">NIDN: <?= $dosen['nidn'] ?> &nbsp;·&nbsp; <?= $dosen['prodi'] ?></p>
    </div>
    <?php if(!empty($undangan_penguji)): ?>
    <div class="flex-shrink-0 bg-white/10 border border-white/20 rounded-2xl p-4 flex items-start gap-3">
      <span class="text-2xl">🔔</span>
      <div>
        <div class="text-white font-bold text-sm">Ada undangan voting jadwal!</div>
        <div class="text-white/70 text-xs mt-0.5">Batas vote: <?= $undangan_penguji[0]['batas_vote'] ?></div>
        <a href="vote_jadwal" class="mt-2 inline-block bg-white text-nusa text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-slate-50 transition">Vote Sekarang →</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <?php
  $stats = [
    ['label'=>'Mahasiswa Aktif','val'=>count($mahasiswa_bimbingan),'sub'=>'Bimbingan saat ini','icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z','bg'=>'from-nusa to-nusa-dark'],
    ['label'=>'Approve Logbook','val'=>1,'sub'=>'Menunggu review','icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z','bg'=>'from-amber-400 to-amber-600'],
    ['label'=>'Jadwal Sidang','val'=>2,'sub'=>'Bulan ini','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','bg'=>'from-emerald-500 to-teal-600'],
    ['label'=>'Penelitian Aktif','val'=>2,'sub'=>'Sedang berjalan','icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','bg'=>'from-purple-500 to-purple-700'],
  ];
  foreach($stats as $s): ?>
  <div class="rounded-2xl p-5 text-white bg-gradient-to-br <?= $s['bg'] ?> shadow-lg relative overflow-hidden">
    <div class="absolute top-0 right-0 w-20 h-20 rounded-full bg-white/10" style="transform:translate(30%,-30%)"></div>
    <svg class="w-6 h-6 mb-3 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $s['icon'] ?>"/></svg>
    <div class="font-display font-bold text-2xl"><?= $s['val'] ?></div>
    <div class="text-white/90 text-xs font-semibold"><?= $s['label'] ?></div>
    <div class="text-white/60 text-xs"><?= $s['sub'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Mahasiswa Bimbingan -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
  <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
    <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
      <h3 class="font-display font-bold text-slate-800 dark:text-white text-sm">👥 Mahasiswa Bimbingan</h3>
      <a href="mahasiswa" class="text-xs text-nusa font-semibold hover:underline">Lihat semua →</a>
    </div>
    <div class="divide-y divide-slate-100 dark:divide-slate-700">
      <?php foreach($mahasiswa_bimbingan as $m): ?>
      <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-nusa to-nusa-dark text-white font-bold text-sm flex items-center justify-center flex-shrink-0"><?= strtoupper(substr($m['nama'],0,1)) ?></div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
              <span class="font-semibold text-sm text-slate-800 dark:text-white"><?= $m['nama'] ?></span>
              <span class="text-xs text-slate-400"><?= $m['nim'] ?></span>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Status: <span class="font-semibold text-slate-700 dark:text-slate-200"><?= $m['status_tesis'] ?></span> · Bimbingan: <?= $m['bimbingan'] ?>x · Terakhir: <?= $m['last_bimbingan'] ?></div>
            <div class="mt-2 flex items-center gap-2">
              <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-full h-1.5">
                <div class="h-1.5 rounded-full bg-gradient-to-r from-nusa to-nusa-dark" style="width:<?= $m['persen'] ?>%"></div>
              </div>
              <span class="text-xs font-bold text-nusa dark:text-nusa-light"><?= $m['persen'] ?>%</span>
            </div>
          </div>
          <?php if($m['logbook_pending']): ?>
          <span class="text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 px-2 py-0.5 rounded-full font-bold border border-amber-200 dark:border-amber-800 flex-shrink-0">1 logbook</span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Jadwal Sidang -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
    <div class="p-5 border-b border-slate-100 dark:border-slate-700">
      <h3 class="font-display font-bold text-slate-800 dark:text-white text-sm">📅 Jadwal Sidang Saya</h3>
    </div>
    <div class="divide-y divide-slate-100 dark:divide-slate-700">
      <?php foreach($jadwal_upcoming as $j): ?>
      <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
        <div class="text-xs font-bold text-blue-600 dark:text-blue-400 mb-1"><?= $j['peran'] ?></div>
        <div class="font-semibold text-sm text-slate-800 dark:text-white"><?= $j['mahasiswa'] ?></div>
        <div class="text-xs text-slate-500 dark:text-slate-400"><?= $j['jenis'] ?></div>
        <div class="mt-2 text-xs font-semibold text-slate-700 dark:text-slate-200"><?= $j['tanggal'] ?>, <?= $j['jam'] ?> WIB</div>
        <div class="text-xs text-slate-400"><?= $j['ruang'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
