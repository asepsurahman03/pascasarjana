<?php
$pageTitle = 'Mahasiswa Bimbingan';
require_once 'header.php';

$mahasiswa = [
  ['nama'=>'Ahmad Rizki Pratama','nim'=>'2023MIF001','prodi'=>'Magister Informatika','angkatan'=>2023,'sem'=>4,
   'judul'=>'Implementasi Deep Learning untuk Deteksi Anomali Jaringan','status'=>'Penelitian',
   'bimbingan'=>7,'target_bimbingan'=>8,'persen'=>65,'logbook_pending'=>0,'sidang'=>'Terdaftar','color'=>'#3b82f6'],
  ['nama'=>'Budi Hermawan','nim'=>'2023MIF002','prodi'=>'Magister Informatika','angkatan'=>2023,'sem'=>4,
   'judul'=>'Sistem Rekomendasi Berbasis Collaborative Filtering untuk E-Commerce','status'=>'Penulisan',
   'bimbingan'=>10,'target_bimbingan'=>8,'persen'=>80,'logbook_pending'=>1,'sidang'=>'Polling Jadwal','color'=>'#8b5cf6'],
  ['nama'=>'Citra Dewi Santika','nim'=>'2022MIF001','prodi'=>'Magister Informatika','angkatan'=>2022,'sem'=>6,
   'judul'=>'Analisis Faktor Keberhasilan Sistem ERP pada UMKM','status'=>'Bimbingan Awal',
   'bimbingan'=>5,'target_bimbingan'=>8,'persen'=>45,'logbook_pending'=>0,'sidang'=>'Belum Daftar','color'=>'#10b981'],
];
$statusColor = [
  'Bimbingan Awal' => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600',
  'Penelitian'     => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800',
  'Penulisan'      => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800',
  'Sidang'         => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
];
?>

<!-- Stat cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <?php
  $stats = [
    ['v'=>count($mahasiswa),'l'=>'Total Bimbingan','i'=>'👥','c'=>'from-nusa to-nusa-dark'],
    ['v'=>1,'l'=>'Logbook Pending','i'=>'📝','c'=>'from-amber-400 to-amber-600'],
    ['v'=>1,'l'=>'Terdaftar Sidang','i'=>'🎓','c'=>'from-emerald-500 to-teal-600'],
    ['v'=>1,'l'=>'Sidang Bulan Ini','i'=>'📅','c'=>'from-purple-500 to-purple-700'],
  ];
  foreach($stats as $s): ?>
  <div class="rounded-2xl p-5 text-white bg-gradient-to-br <?= $s['c'] ?> shadow-lg relative overflow-hidden">
    <div class="absolute top-0 right-0 w-16 h-16 rounded-full bg-white/10" style="transform:translate(30%,-30%)"></div>
    <div class="text-2xl mb-2"><?= $s['i'] ?></div>
    <div class="font-display font-bold text-2xl"><?= $s['v'] ?></div>
    <div class="text-white/80 text-xs"><?= $s['l'] ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- List Mahasiswa -->
<div class="space-y-4">
  <?php foreach($mahasiswa as $m): ?>
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow overflow-hidden" x-data="{open:false}">
    <!-- Header row -->
    <div class="flex items-start gap-4 p-5">
      <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-base flex-shrink-0" style="background:<?= $m['color'] ?>"><?= strtoupper(substr($m['nama'],0,1)) ?></div>
      <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-2 mb-0.5">
          <span class="font-display font-bold text-slate-800 dark:text-white text-sm"><?= $m['nama'] ?></span>
          <span class="text-xs text-slate-400"><?= $m['nim'] ?></span>
          <span class="px-2 py-0.5 rounded-full text-xs font-bold border <?= $statusColor[$m['status']] ?>"><?= $m['status'] ?></span>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 italic truncate max-w-xl">"<?= $m['judul'] ?>"</p>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
          <span class="text-xs text-slate-500 dark:text-slate-400"><?= $m['prodi'] ?></span>
          <span class="text-xs text-slate-500 dark:text-slate-400">Angkatan <?= $m['angkatan'] ?> · Sem <?= $m['sem'] ?></span>
          <span class="text-xs text-slate-500 dark:text-slate-400">Sidang: <span class="font-semibold text-slate-700 dark:text-slate-200"><?= $m['sidang'] ?></span></span>
          <?php if($m['logbook_pending']): ?>
          <span class="text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 font-bold px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800">⏳ <?= $m['logbook_pending'] ?> logbook menunggu</span>
          <?php endif; ?>
        </div>
        <!-- Progress bimbingan -->
        <div class="mt-2.5 flex items-center gap-2">
          <span class="text-xs text-slate-400 flex-shrink-0"><?= $m['bimbingan'] ?>/<?= $m['target_bimbingan'] ?> bimbingan</span>
          <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-full h-1.5">
            <div class="h-1.5 rounded-full transition-all" style="width:<?= min($m['persen'],100) ?>%;background:<?= $m['color'] ?>"></div>
          </div>
          <span class="text-xs font-bold flex-shrink-0" style="color:<?= $m['color'] ?>"><?= $m['persen'] ?>%</span>
        </div>
      </div>
      <!-- Action buttons -->
      <div class="flex items-center gap-2 flex-shrink-0">
        <a href="logbook?nim=<?= $m['nim'] ?>" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Logbook</a>
        <button @click="open=!open" class="px-3 py-1.5 text-xs font-bold rounded-lg text-white transition" style="background:linear-gradient(135deg,#961d5a,#6b1040)">
          <span x-text="open?'Tutup':'Detail'"></span>
        </button>
      </div>
    </div>

    <!-- Expanded Detail -->
    <div x-show="open" x-transition class="border-t border-slate-100 dark:border-slate-700 px-5 pb-5 pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Catatan Progress</div>
        <textarea rows="3" placeholder="Tambahkan catatan perkembangan mahasiswa..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-nusa transition-colors"></textarea>
        <button class="mt-2 px-4 py-1.5 text-xs font-bold text-white rounded-lg transition hover:shadow-md" style="background:linear-gradient(135deg,#961d5a,#6b1040)">Simpan Catatan</button>
      </div>
      <div>
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Aksi Cepat</div>
        <div class="flex flex-col gap-2">
          <button class="flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg text-left transition border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">
            <svg class="w-4 h-4 text-nusa" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            Kirim Pesan ke Mahasiswa
          </button>
          <button class="flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg text-left transition border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20">
            <svg class="w-4 h-4 text-nusa" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            ACC Kelayakan Sidang
          </button>
          <button class="flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg text-left transition border border-red-200 dark:border-red-900/40 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Beri Peringatan Kemajuan
          </button>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php require_once 'footer.php'; ?>
