<?php
$pageTitle='Dashboard';
require_once __DIR__.'/includes/functions.php';

if (isset($_GET['page']) && $_GET['page'] === 'auth/google/callback') {
    require_once __DIR__ . '/google_login_manual.php';
    exit;
}

requireAdmin();
$totalMhs   =dbCount('mahasiswa');
$mhsAktif   =dbCount('mahasiswa','status=?',['Aktif']);
$suratHari  =dbCount('surat','DATE(created_at)=CURDATE()');
$tugasPend  =dbCount('tugas','status!=?',['Selesai']);
$agendaBulan=dbCount('jadwal','MONTH(tanggal_mulai)=MONTH(CURDATE()) AND YEAR(tanggal_mulai)=YEAR(CURDATE())');
$suratPend  =dbCount('surat',"status IN('Draf','Proses')");
$mhsPerProdi=dbQuery("SELECT p.nama,p.warna_hex,COUNT(m.id) as total FROM prodi p LEFT JOIN mahasiswa m ON m.prodi_id=p.id GROUP BY p.id ORDER BY p.jenjang,p.nama");
$suratTerbaru=dbQuery("SELECT s.*,p.nama as pnama,u.nama as unama FROM surat s JOIN prodi p ON p.id=s.prodi_id JOIN users u ON u.id=s.created_by ORDER BY s.created_at DESC LIMIT 5");
$tugasHari  =dbQuery("SELECT t.*,p.nama as pnama,p.warna_hex FROM tugas t LEFT JOIN prodi p ON p.id=t.prodi_id WHERE t.status!='Selesai' AND (t.deadline IS NULL OR t.deadline<=DATE_ADD(CURDATE(),INTERVAL 5 DAY)) ORDER BY t.deadline ASC,t.prioritas DESC LIMIT 6");
$waLog      =dbQuery("SELECT w.*,u.nama as snama FROM whatsapp_log w JOIN users u ON u.id=w.created_by ORDER BY w.created_at DESC LIMIT 5");
$agendaList =dbQuery("SELECT j.*,p.nama as pnama FROM jadwal j LEFT JOIN prodi p ON p.id=j.prodi_id WHERE j.tanggal_mulai>=CURDATE() ORDER BY j.tanggal_mulai ASC LIMIT 6");
require_once __DIR__.'/includes/header.php';
$stats=[
    ['label'=>'Total Mahasiswa','value'=>$totalMhs,'sub'=>$mhsAktif.' Aktif','color'=>'from-blue-600 to-indigo-700','icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
    ['label'=>'Surat Hari Ini','value'=>$suratHari,'sub'=>$suratPend.' Pending','color'=>'from-emerald-500 to-teal-700','icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ['label'=>'Tugas Pending','value'=>$tugasPend,'sub'=>'Perlu diselesaikan','color'=>'from-amber-500 to-orange-600','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
    ['label'=>'Agenda Bulan Ini','value'=>$agendaBulan,'sub'=>date('F Y'),'color'=>'from-purple-500 to-fuchsia-600','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
];
?>
<!-- Welcome Banner -->
<div class="relative rounded-[2rem] overflow-hidden mb-8 p-8 md:p-10 text-white shadow-2xl shadow-nusa/20">
  <div class="absolute inset-0 bg-gradient-to-br from-[#8c0c4c] via-[#a3155b] to-[#6b1040]"></div>
  <!-- Decorative shapes -->
  <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full opacity-10 bg-gradient-to-tl from-white to-transparent blur-3xl mix-blend-overlay" style="transform:translate(30%,-30%)"></div>
  <div class="absolute bottom-0 left-20 w-[300px] h-[300px] rounded-full opacity-10 bg-gradient-to-tr from-white to-transparent blur-2xl mix-blend-overlay" style="transform:translate(-30%,40%)"></div>
  
  <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
    <div class="space-y-2">
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 backdrop-blur-md text-xs font-medium text-white/90 mb-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        Sistem Online & Berjalan
      </div>
      <h2 class="font-display font-bold text-3xl md:text-4xl text-white tracking-tight">
        Selamat datang, <?= e($user['nama']) ?> 👋
      </h2>
      <p class="text-white/80 text-sm md:text-base font-medium flex items-center gap-2">
        <span><?= $user['role'] === 'super_admin' ? 'Super Admin' : 'Kaprodi' ?></span>
        <span class="w-1 h-1 rounded-full bg-white/50"></span>
        <span><?= formatTanggal(date('Y-m-d'), true) ?></span>
      </p>
    </div>
    
    <!-- Quick Stats -->
    <div class="flex items-center gap-4">
      <div class="flex-shrink-0 flex flex-col items-center bg-white/10 backdrop-blur-md rounded-2xl px-6 py-4 border border-white/20 hover:bg-white/20 transition cursor-default">
        <div class="font-display font-bold text-3xl text-white"><?= $suratPend + $tugasPend ?></div>
        <div class="text-white/70 text-[11px] font-medium uppercase tracking-wider mt-1">Pending</div>
      </div>
    </div>
  </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
<?php foreach($stats as $i => $s): ?>
<div class="group relative bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-700/60 hover:shadow-xl hover:border-slate-200 dark:hover:border-slate-600 transition-all duration-300 hover:-translate-y-1">
  <div class="absolute inset-0 bg-gradient-to-br <?= $s['color'] ?> opacity-0 group-hover:opacity-5 transition-opacity duration-300 rounded-3xl"></div>
  <div class="flex justify-between items-start mb-4">
    <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-gradient-to-br <?= $s['color'] ?> text-white shadow-lg shadow-<?= explode('-', $s['color'])[1] ?>/30">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $s['icon'] ?>"/></svg>
    </div>
    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider bg-slate-100 dark:bg-slate-900 px-2 py-1 rounded-lg">#<?= $i+1 ?></span>
  </div>
  <div>
    <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium"><?= $s['label'] ?></h3>
    <div class="flex items-baseline gap-2 mt-1">
      <p class="font-display font-bold text-3xl text-slate-800 dark:text-white"><?= number_format($s['value']) ?></p>
    </div>
    <p class="text-slate-400 dark:text-slate-500 text-[11px] font-medium mt-2 flex items-center gap-1.5">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
      <?= $s['sub'] ?>
    </p>
  </div>
</div>
<?php endforeach; ?>
</div>

<!-- Main Grid Layout -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
  
  <!-- Chart Section -->
  <div class="xl:col-span-2 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8 hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white">Statistik Mahasiswa</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Distribusi jumlah mahasiswa per program studi</p>
      </div>
      <a href="<?=BASE_URL?>/pages/mahasiswa" class="px-4 py-2 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition-colors flex items-center gap-2">
        Kelola <span aria-hidden="true">&rarr;</span>
      </a>
    </div>
    <div class="relative h-64 w-full">
      <canvas id="chartProdi"></canvas>
    </div>
  </div>

  <!-- Tugas Section -->
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8 flex flex-col h-full hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white">Tugas Aktif</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Daftar prioritas pekerjaan</p>
      </div>
      <a href="<?=BASE_URL?>/pages/tugas" class="text-slate-400 hover:text-nusa transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></a>
    </div>
    
    <div class="space-y-3 flex-1 overflow-y-auto pr-2 custom-scrollbar">
      <?php if(empty($tugasHari)): ?>
      <div class="h-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 py-10">
        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-700/30 rounded-full flex items-center justify-center mb-4">
          <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        </div>
        <p class="text-sm font-medium text-center">Semua tugas telah selesai!<br><span class="text-xs font-normal">Tidak ada pekerjaan tertunda.</span></p>
      </div>
      <?php else: foreach($tugasHari as $t): ?>
      <div class="group flex items-start gap-3 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-900/30 hover:bg-white dark:hover:bg-slate-800 hover:shadow-lg hover:border-nusa/30 dark:hover:border-nusa/30 transition-all cursor-pointer">
        <input type="checkbox" class="task-check mt-1 w-4 h-4 text-nusa border-slate-300 rounded focus:ring-nusa cursor-pointer flex-shrink-0" data-id="<?=$t['id']?>" <?=$t['status']==='Selesai'?'checked':''?>>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-slate-800 dark:text-white leading-snug <?=$t['status']==='Selesai'?'line-through opacity-50':''?> group-hover:text-nusa transition-colors"><?=e($t['judul'])?></p>
          <div class="flex flex-wrap items-center gap-2 mt-2">
            <?php if($t['deadline']): ?>
            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-md <?=isDeadlineDekat($t['deadline'])?'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400':'bg-slate-200/60 text-slate-600 dark:bg-slate-700 dark:text-slate-300'?>">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <?=formatTanggal($t['deadline'])?>
            </span>
            <?php endif; ?>
            <?=prioritasBadge($t['prioritas'])?>
          </div>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- Lower Grid Layout -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
  
  <!-- Surat Terbaru -->
  <div class="xl:col-span-2 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow">
    <div class="p-6 lg:px-8 lg:pt-8 lg:pb-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-700">
      <div>
        <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white">Surat Terbaru</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Daftar surat masuk & keluar terakhir</p>
      </div>
      <a href="<?=BASE_URL?>/pages/surat" class="px-4 py-2 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition-colors flex items-center gap-2">
        Lihat Semua <span aria-hidden="true">&rarr;</span>
      </a>
    </div>
    
    <div class="overflow-x-auto flex-1">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/50">
          <tr>
            <th class="text-left py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Detail Surat</th>
            <th class="text-left py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerima & Prodi</th>
            <th class="text-right py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
          <?php if(empty($suratTerbaru)): ?>
          <tr>
            <td colspan="3" class="py-16">
              <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-700/30 rounded-full flex items-center justify-center mb-4">
                  <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-sm font-medium">Belum ada aktivitas persuratan</p>
              </div>
            </td>
          </tr>
          <?php else: foreach($suratTerbaru as $s): ?>
          <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group">
            <td class="py-4 px-6">
              <div class="flex flex-col">
                <span class="font-semibold text-slate-800 dark:text-white group-hover:text-nusa transition-colors"><?=e($s['jenis_surat'])?></span>
                <span class="font-mono text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?=e($s['nomor_surat'])?></span>
              </div>
            </td>
            <td class="py-4 px-6">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                  <?= substr($s['nama_penerima'],0,1) ?>
                </div>
                <div class="flex flex-col">
                  <span class="font-medium text-slate-700 dark:text-slate-200"><?=e($s['nama_penerima'])?></span>
                  <span class="text-[11px] text-slate-500 dark:text-slate-400"><?=e($s['pnama'])?></span>
                </div>
              </div>
            </td>
            <td class="py-4 px-6 text-right">
              <?=statusBadge($s['status'])?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Agenda Mendatang -->
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8 flex flex-col hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white">Agenda</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jadwal kegiatan terdekat</p>
      </div>
      <a href="<?=BASE_URL?>/pages/jadwal" class="text-slate-400 hover:text-nusa transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></a>
    </div>
    
    <div class="space-y-4 flex-1 overflow-y-auto pr-2 custom-scrollbar">
      <?php if(empty($agendaList)): ?>
      <div class="h-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 py-10">
        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-700/30 rounded-full flex items-center justify-center mb-4">
           <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <p class="text-sm font-medium">Kosong</p>
        <p class="text-xs mt-1">Tidak ada agenda bulan ini</p>
      </div>
      <?php else: foreach($agendaList as $ag): 
        $w = e($ag['warna']??'#8c0c4c');
      ?>
      <div class="group flex items-start gap-4 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-750 transition cursor-pointer relative overflow-hidden">
        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl" style="background-color:<?=$w?>"></div>
        <div class="flex-shrink-0 text-center w-12 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 py-2">
          <div class="text-lg font-bold text-slate-800 dark:text-white leading-none"><?=date('d',strtotime($ag['tanggal_mulai']))?></div>
          <div class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase mt-1"><?=date('M',strtotime($ag['tanggal_mulai']))?></div>
        </div>
        <div class="flex-1 py-1">
          <p class="text-sm font-medium text-slate-800 dark:text-white leading-snug group-hover:text-nusa transition-colors"><?=e($ag['judul'])?></p>
          <div class="flex items-center gap-2 mt-1.5">
            <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-semibold rounded-md"><?=e($ag['jenis_event'])?></span>
          </div>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- Logs Section -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden hover:shadow-md transition-shadow mb-8">
  <div class="p-6 lg:px-8 lg:pt-8 lg:pb-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-700">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400 flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
      </div>
      <div>
        <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white">Log WhatsApp</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Riwayat notifikasi sistem terbaru</p>
      </div>
    </div>
    <a href="<?=BASE_URL?>/pages/whatsapp" class="px-4 py-2 bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition-colors flex items-center gap-2">
      Log Lengkap <span aria-hidden="true">&rarr;</span>
    </a>
  </div>
  
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/50">
        <tr>
          <th class="text-left py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerima (Tujuan)</th>
          <th class="text-left py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Cuplikan Pesan</th>
          <th class="text-left py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu Kirim</th>
          <th class="text-right py-4 px-6 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php if(empty($waLog)): ?>
        <tr><td colspan="4" class="py-12 text-center text-slate-400 dark:text-slate-500 text-sm">Belum ada riwayat pengiriman pesan</td></tr>
        <?php else: foreach($waLog as $w): 
          $wc=['Terkirim'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800','Gagal'=>'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 border border-red-200 dark:border-red-800','Pending'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border border-amber-200 dark:border-amber-800'][$w['status']]??'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white'; 
        ?>
        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
          <td class="py-4 px-6 font-mono text-[13px] text-slate-600 dark:text-slate-300"><?=e($w['tujuan'])?></td>
          <td class="py-4 px-6 text-[13px] text-slate-600 dark:text-slate-400 max-w-sm truncate" title="<?=e($w['pesan'])?>"><?=e(mb_substr($w['pesan'],0,70))?>...</td>
          <td class="py-4 px-6 text-[12px] text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <?=$w['waktu_kirim']?formatTanggal($w['waktu_kirim'],true):'-'?>
            </div>
          </td>
          <td class="py-4 px-6 text-right">
            <span class="inline-flex px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider <?=$wc?>"><?=$w['status']?></span>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
/* Custom scrollbar for lists */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>

<?php
// Initialize chart data
$cd=json_encode([
    'labels'=>array_map(fn($p)=>$p['nama'],$mhsPerProdi),
    'data'=>array_map(fn($p)=>$p['total'],$mhsPerProdi),
    'colors'=>array_map(fn($p)=>$p['warna_hex'],$mhsPerProdi)
]);

$pageScript="
const cd=$cd;
const isDark = document.documentElement.classList.contains('dark');
const gridColor = isDark ? '#334155' : '#e2e8f0';
const textColor = isDark ? '#94a3b8' : '#64748b';

new Chart(document.getElementById('chartProdi'),{
    type:'bar',
    data:{
        labels:cd.labels,
        datasets:[{
            label:'Total Mahasiswa',
            data:cd.data,
            backgroundColor:cd.colors.map(c=>c+'E6'),
            hoverBackgroundColor: cd.colors,
            borderWidth:0,
            borderRadius:8,
            barPercentage: 0.6
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio: false,
        plugins:{
            legend:{display:false},
            tooltip: {
                backgroundColor: isDark ? '#1e293b' : '#ffffff',
                titleColor: isDark ? '#f8fafc' : '#0f172a',
                bodyColor: isDark ? '#cbd5e1' : '#475569',
                borderColor: isDark ? '#334155' : '#e2e8f0',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 12,
                displayColors: false
            }
        },
        scales:{
            y:{
                beginAtZero:true,
                grid:{color: gridColor, drawBorder: false},
                ticks:{color: textColor, padding: 10}
            },
            x:{
                grid:{display:false},
                ticks:{color: textColor, maxRotation:45, minRotation: 0, padding: 10}
            }
        },
        interaction: {
            mode: 'index',
            intersect: false,
        }
    }
});
";
require_once __DIR__.'/includes/footer.php';
?>