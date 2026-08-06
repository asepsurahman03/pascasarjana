<?php
$pageTitle = 'Dashboard';
require_once 'header.php';

// Dummy data
$tahapan = [
  ['label'=>'Aktif Kuliah',      'done'=>true,  'date'=>'Agustus 2023'],
  ['label'=>'Seminar Proposal',  'done'=>true,  'date'=>'Maret 2024'],
  ['label'=>'Penelitian',        'done'=>false, 'date'=>'Berjalan...', 'current'=>true],
  ['label'=>'Sidang Tesis',      'done'=>false, 'date'=>'Target: Agustus 2025'],
  ['label'=>'Yudisium & Lulus',  'done'=>false, 'date'=>'—'],
];
$prodiId = $mhs['prodi_id'] ?? null;
$jadwal_upcoming = dbQuery(
  "SELECT j.*, p.nama as pnama FROM jadwal j
   LEFT JOIN prodi p ON p.id = j.prodi_id
   WHERE (j.prodi_id IS NULL OR j.prodi_id = ?) AND j.tanggal_mulai >= CURDATE()
   ORDER BY j.tanggal_mulai ASC LIMIT 5",
  [$prodiId]
);
$notifikasi = [
  ['ikon'=>'📋','pesan'=>'Pendaftaran sidang Anda sedang diverifikasi admin','waktu'=>'2 jam lalu','type'=>'info'],
  ['ikon'=>'📅','pesan'=>'Jadwal sidang Budi Hermawan telah ditetapkan: Senin 21 Jul','waktu'=>'5 jam lalu','type'=>'success'],
  ['ikon'=>'⚠️','pesan'=>'Batas logbook bimbingan: Anda baru 7/8 sesi minimum','waktu'=>'1 hari lalu','type'=>'warning'],
];
?>

<!-- Progress Header Card -->
<div class="relative rounded-2xl overflow-hidden mb-6 p-6 text-white bg-gradient-to-br from-nusa to-nusa-dark shadow-md">
  <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10" style="background:white;transform:translate(30%,-30%)"></div>
  <div class="absolute bottom-0 right-20 w-32 h-32 rounded-full opacity-10" style="background:white;transform:translate(0,40%)"></div>
  <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <p class="text-white/70 text-sm font-medium">Selamat datang kembali 👋</p>
      <h2 class="font-display font-bold text-2xl mt-0.5"><?= $mhs['nama'] ?></h2>
      <p class="text-white/70 text-sm mt-1"><?= $mhs['nim'] ?> &nbsp;·&nbsp; <?= $mhs['prodi'] ?> &nbsp;·&nbsp; Semester <?= $mhs['semester'] ?></p>
      <p class="text-white/80 text-sm mt-2 max-w-lg italic">"<?= $mhs['judul_tesis'] ?>"</p>
    </div>
    <div class="flex-shrink-0 flex flex-col items-center bg-white/10 rounded-2xl px-8 py-5 border border-white/20">
      <div class="font-display font-bold text-4xl"><?= $mhs['pct_progress'] ?>%</div>
      <div class="text-white/70 text-xs mt-1">Kemajuan Studi</div>
      <div class="w-32 bg-white/20 rounded-full h-2 mt-3">
        <div class="bg-white rounded-full h-2" style="width:<?= $mhs['pct_progress'] ?>%"></div>
      </div>
    </div>
  </div>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <?php
  $stats = [
    ['label'=>'IPK Sementara',    'val'=>number_format($mhs['ipk'],2), 'sub'=>'Sangat Memuaskan', 'icon'=>'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'bg'=>'from-amber-400 to-orange-500'],
    ['label'=>'Sesi Bimbingan',   'val'=>$mhs['jml_bimbingan'].'/8', 'sub'=>'Min. 8 untuk sidang', 'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'bg'=>'from-nusa to-nusa-dark'],
    ['label'=>'Status Tesis',     'val'=>$mhs['status_tesis'], 'sub'=>'Tahap berjalan', 'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'bg'=>'from-pink-500 to-rose-700'],
    ['label'=>'Sisa Waktu Studi', 'val'=>'14 Bln', 'sub'=>'Batas: Agustus 2026', 'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0', 'bg'=>'from-emerald-400 to-teal-600'],
  ];
  foreach($stats as $s): ?>
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 relative overflow-hidden group hover:shadow-md transition">
    <div class="absolute top-0 right-0 w-24 h-24 rounded-full opacity-10 bg-gradient-to-br <?= $s['bg'] ?>" style="transform:translate(30%,-30%)"></div>
    <div class="w-10 h-10 rounded-xl bg-gradient-to-br <?= $s['bg'] ?> flex items-center justify-center text-white mb-3 shadow-md">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $s['icon'] ?>"/></svg>
    </div>
    <div class="font-display font-bold text-2xl text-slate-800 dark:text-white"><?= $s['val'] ?></div>
    <div class="text-xs font-semibold text-slate-700 dark:text-slate-200 mt-0.5"><?= $s['label'] ?></div>
    <div class="text-xs text-slate-400 mt-0.5"><?= $s['sub'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Timeline Kemajuan -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-6">
  <h3 class="font-display font-bold text-slate-800 dark:text-white text-sm mb-6">📍 Timeline Kemajuan Studi</h3>
  <div class="relative">
    <div class="absolute top-5 left-0 right-0 h-0.5 bg-slate-100 dark:bg-slate-700"></div>
    <div class="flex justify-between relative">
      <?php foreach($tahapan as $i=>$t): ?>
      <div class="flex flex-col items-center gap-2 flex-1">
        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shadow-md z-10 <?= $t['done']?'bg-emerald-500 text-white':(!empty($t['current'])?'bg-nusa text-white ring-4 ring-nusa/20':'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400') ?>">
          <?= $t['done'] ? '✓' : ($i+1) ?>
        </div>
        <div class="text-center px-1">
          <div class="font-semibold text-xs text-slate-700 dark:text-slate-200 leading-tight"><?= $t['label'] ?></div>
          <div class="text-xs mt-0.5 <?= $t['done']?'text-emerald-500 font-medium':(!empty($t['current'])?'text-nusa font-semibold':'text-slate-500 dark:text-slate-400') ?>"><?= $t['date'] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Bottom Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
  <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
    <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
      <h3 class="font-display font-bold text-slate-800 dark:text-white text-sm">📅 Agenda Akademik Terdekat</h3>
      <a href="jadwal" class="text-xs text-nusa font-semibold hover:underline">Lihat semua →</a>
    </div>
    <?php if (empty($jadwal_upcoming)): ?>
    <div class="flex flex-col items-center justify-center py-10 text-slate-400">
      <p class="text-sm font-medium">Tidak ada agenda mendatang</p>
    </div>
    <?php else: foreach($jadwal_upcoming as $j):
      $warna = htmlspecialchars($j['warna'] ?? '#8c0c4c');
      $isHex = str_starts_with($warna, '#');
      $dotStyle = $isHex ? "background:{$warna}" : '';
      $ts = strtotime($j['tanggal_mulai']);
      $tglStr = date('D, d M Y', $ts);
      $tglMap = ['Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab','Sun'=>'Min'];
      foreach ($tglMap as $en => $id) $tglStr = str_replace($en, $id, $tglStr);
    ?>
    <div class="flex items-center gap-4 p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition border-b border-slate-100 dark:border-slate-700 last:border-0">
      <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:<?=$warna?>18;color:<?=$warna?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
      </div>
      <div class="flex-1 min-w-0">
        <div class="font-semibold text-sm text-slate-800 dark:text-white truncate"><?= htmlspecialchars($j['judul']) ?></div>
        <div class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($j['jenis_event'] ?? '') ?><?= !empty($j['pnama']) ? ' · '.$j['pnama'] : '' ?></div>
      </div>
      <div class="text-right flex-shrink-0">
        <div class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?= $tglStr ?></div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Notifikasi + CTA -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col">
    <div class="p-5 border-b border-slate-100 dark:border-slate-700">
      <h3 class="font-display font-bold text-slate-800 dark:text-white text-sm">🔔 Notifikasi</h3>
    </div>
    <div class="divide-y divide-slate-100 dark:divide-slate-700 flex-1">
      <?php foreach($notifikasi as $n): ?>
      <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition flex gap-3">
        <span class="text-xl flex-shrink-0"><?= $n['ikon'] ?></span>
        <div>
          <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed"><?= $n['pesan'] ?></p>
          <p class="text-xs text-slate-400 dark:text-slate-500 mt-1"><?= $n['waktu'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">
      <a href="pendaftaran" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold text-white transition hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0" style="background:linear-gradient(135deg,#961d5a,#6b1040)">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Layanan Pendaftaran Akademik
      </a>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
