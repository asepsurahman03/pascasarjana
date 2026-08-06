<?php
$pageTitle = 'Penelitian Dosen';
require_once '../includes/header.php';

$penelitian = [
  ['id'=>1,'dosen'=>'Dr. Ahmad Fauzi, M.Kom','nidn'=>'0412078801','prodi'=>'Magister Informatika','judul'=>'Optimasi Arsitektur CNN untuk Klasifikasi Citra Medis Berbasis Transfer Learning','bidang'=>'Deep Learning, AI','status'=>'Sedang Berjalan','tahun'=>2024,'dana'=>'DIKTI BRIN','anggaran'=>'Rp 125.000.000','sinta'=>'S2','scopus'=>true,'mhs_terlibat'=>2,'publikasi'=>1],
  ['id'=>2,'dosen'=>'Prof. Dr. Hendra Kusuma, M.Cs','nidn'=>'0807076501','prodi'=>'Doktor Ilmu Komputer','judul'=>'Framework Deteksi Intrusi Berbasis Federated Learning pada Infrastruktur Kritis','bidang'=>'Keamanan Siber, Blockchain','status'=>'Sedang Berjalan','tahun'=>2023,'dana'=>'Mandiri','anggaran'=>'Rp 50.000.000','sinta'=>'S1','scopus'=>true,'mhs_terlibat'=>1,'publikasi'=>2],
  ['id'=>3,'dosen'=>'Dr. Siti Rahayu, M.Kom','nidn'=>'1105079001','prodi'=>'Magister Informatika','judul'=>'Analisis Sentimen Multibahasa Menggunakan Large Language Model (LLM)','bidang'=>'Big Data, NLP','status'=>'Perencanaan','tahun'=>2025,'dana'=>'DIKTI','anggaran'=>'Rp 200.000.000','sinta'=>'S3','scopus'=>false,'mhs_terlibat'=>0,'publikasi'=>0],
  ['id'=>4,'dosen'=>'Dr. Rizal Maulana, M.T.','nidn'=>'0923088201','prodi'=>'Magister Informatika','judul'=>'Optimisasi Pengelolaan Energi Smart Building dengan Reinforcement Learning','bidang'=>'IoT, Edge Computing','status'=>'Publikasi','tahun'=>2022,'dana'=>'Mandiri','anggaran'=>'Rp 30.000.000','sinta'=>'S2','scopus'=>true,'mhs_terlibat'=>0,'publikasi'=>3],
  ['id'=>5,'dosen'=>'Dr. Dewi Lestari, M.T.','nidn'=>'0615089101','prodi'=>'Magister Pedagogi','judul'=>'Pengembangan Chatbot Cerdas untuk Layanan Akademik Berbasis Transformer','bidang'=>'Sistem Informasi, NLP','status'=>'Selesai','tahun'=>2023,'dana'=>'DIKTI','anggaran'=>'Rp 80.000.000','sinta'=>'S3','scopus'=>false,'mhs_terlibat'=>0,'publikasi'=>1],
];

$statusCl = [
  'Sedang Berjalan'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 border-blue-200 dark:border-blue-800/50',
  'Perencanaan'    =>'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border-amber-200 dark:border-amber-800/50',
  'Selesai'        =>'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600/50',
  'Publikasi'      =>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50',
];
$colors = ['#8c0c4c','#a3155b','#1e40af','#047857','#b45309'];
?>

<!-- Page Header & Overview -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
  <div>
    <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Penelitian Dosen</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Daftar rekam jejak penelitian dan hibah Dosen Pascasarjana</p>
  </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
  <?php
  $stats = [
    ['label'=>'Total Penelitian','val'=>count($penelitian),'icon'=>'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10','color'=>'from-blue-500 to-indigo-600','bg'=>'bg-blue-50 dark:bg-blue-900/20'],
    ['label'=>'Sedang Berjalan','val'=>2,'icon'=>'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'from-amber-500 to-orange-600','bg'=>'bg-amber-50 dark:bg-amber-900/20'],
    ['label'=>'Sudah Publikasi','val'=>3,'icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253','color'=>'from-emerald-500 to-teal-600','bg'=>'bg-emerald-50 dark:bg-emerald-900/20'],
    ['label'=>'Dana Terserap','val'=>'Rp 485 Jt','icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'from-[#8c0c4c] to-[#a3155b]','bg'=>'bg-[#8c0c4c]/5 dark:bg-[#8c0c4c]/20'],
  ];
  foreach($stats as $s): ?>
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-6 relative overflow-hidden group hover:shadow-md transition-all">
    <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full <?=$s['bg']?> group-hover:scale-150 transition-transform duration-500"></div>
    <div class="relative z-10 flex items-center justify-between mb-4">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-br <?=$s['color']?> text-white flex items-center justify-center shadow-md">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?=$s['icon']?>"/></svg>
      </div>
    </div>
    <div class="relative z-10">
      <div class="font-display font-bold text-2xl text-slate-800 dark:text-white mb-1"><?= $s['val'] ?></div>
      <div class="text-sm font-semibold text-slate-500 dark:text-slate-400"><?= $s['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Main Area -->
<div x-data="{view:'card', modalOpen:false}">
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm mb-6 flex flex-col md:flex-row gap-4 justify-between items-center p-4">
    <div class="flex items-center gap-3">
      <div class="bg-slate-100 dark:bg-slate-900 rounded-xl p-1 flex">
        <button @click="view='card'" :class="view==='card'?'bg-white dark:bg-slate-700 text-[#8c0c4c] dark:text-[#f06ea4] shadow-sm font-bold':'text-slate-500 font-medium hover:text-slate-700 dark:hover:text-slate-300'" class="px-4 py-2 rounded-lg text-sm transition-all flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg> Grid
        </button>
        <button @click="view='table'" :class="view==='table'?'bg-white dark:bg-slate-700 text-[#8c0c4c] dark:text-[#f06ea4] shadow-sm font-bold':'text-slate-500 font-medium hover:text-slate-700 dark:hover:text-slate-300'" class="px-4 py-2 rounded-lg text-sm transition-all flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg> Tabel
        </button>
      </div>
      <div class="hidden md:block w-px h-8 bg-slate-200 dark:bg-slate-700"></div>
      <select class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:outline-none focus:border-[#8c0c4c] transition-colors">
        <option>Semua Prodi</option><option>Magister Informatika</option><option>Doktor Ilmu Komputer</option>
      </select>
    </div>
    
    <button @click="modalOpen=true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold shadow-md hover:shadow-lg transition-all text-sm w-full md:w-auto justify-center">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Tambah Penelitian
    </button>
  </div>

  <!-- GRID / CARD VIEW -->
  <div x-show="view==='card'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <?php foreach($penelitian as $i=>$p): ?>
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col">
      <div class="h-2 w-full" style="background:<?= $colors[$i % count($colors)] ?>"></div>
      <div class="p-6 flex-1 flex flex-col">
        <div class="flex items-start justify-between gap-3 mb-4">
          <span class="px-2.5 py-1 rounded-lg text-xs font-bold border <?= $statusCl[$p['status']] ?>"><?= $p['status'] ?></span>
          <div class="flex gap-1.5 flex-wrap justify-end">
            <?php if($p['scopus']): ?><span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400 border border-orange-200 dark:border-orange-800/50">SCOPUS</span><?php endif; ?>
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded <?= $p['sinta']==='S1'?'bg-yellow-100 text-yellow-700 border-yellow-200 dark:bg-yellow-900/40 dark:text-yellow-400 dark:border-yellow-800/50':($p['sinta']==='S2'?'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800/50':'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600') ?>">SINTA <?= $p['sinta'] ?></span>
          </div>
        </div>
        
        <h3 class="font-display font-bold text-base text-slate-800 dark:text-white leading-snug mb-3 flex-1 line-clamp-3" title="<?= $p['judul'] ?>"><?= $p['judul'] ?></h3>
        
        <div class="flex flex-wrap gap-1.5 mb-4">
          <?php foreach(explode(', ', $p['bidang']) as $b): ?>
            <span class="text-[10px] font-semibold bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 px-2.5 py-1 rounded-md"><?= $b ?></span>
          <?php endforeach; ?>
        </div>

        <div class="flex items-center gap-3 mb-5 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/50">
          <div class="w-10 h-10 rounded-xl text-white font-bold text-sm flex items-center justify-center flex-shrink-0 shadow-sm" style="background:<?= $colors[$i % count($colors)] ?>"><?= strtoupper(substr($p['dosen'],3,1)) ?></div>
          <div>
            <div class="text-sm text-slate-800 dark:text-white font-bold leading-tight"><?= $p['dosen'] ?></div>
            <div class="text-xs text-slate-500 dark:text-slate-400"><?= $p['prodi'] ?></div>
          </div>
        </div>
        
        <div class="pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
          <div class="flex gap-4">
            <div>
              <div class="text-[10px] text-slate-500 uppercase tracking-wider font-bold mb-0.5">Dana</div>
              <div class="font-bold text-sm text-[#8c0c4c] dark:text-[#f06ea4]"><?= $p['anggaran'] ?></div>
            </div>
          </div>
          <div class="flex gap-1">
            <button class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
            <button class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition" title="Hapus">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- TABLE VIEW -->
  <div x-show="view==='table'" class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden mb-8">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/50">
          <tr>
            <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Penelitian & Peneliti</th>
            <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Status / Mulai</th>
            <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Dana & Anggaran</th>
            <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Keluaran</th>
            <th class="text-right py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
          <?php foreach($penelitian as $i=>$p): ?>
          <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
            <td class="py-4 px-6">
              <div class="flex items-start gap-3">
                <div class="w-2.5 h-2.5 rounded-full mt-1.5 flex-shrink-0" style="background:<?= $colors[$i%count($colors)] ?>"></div>
                <div>
                  <div class="font-bold text-sm text-slate-800 dark:text-white line-clamp-2 max-w-sm mb-1"><?= $p['judul'] ?></div>
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400"><?= $p['dosen'] ?></span>
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-slate-500"><?= $p['prodi'] ?></span>
                  </div>
                </div>
              </div>
            </td>
            <td class="py-4 px-6">
              <div class="mb-1"><span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $statusCl[$p['status']] ?>"><?= $p['status'] ?></span></div>
              <div class="text-xs text-slate-500 font-medium">Tahun: <?= $p['tahun'] ?></div>
            </td>
            <td class="py-4 px-6">
              <div class="text-sm font-bold text-[#8c0c4c] dark:text-[#f06ea4]"><?= $p['anggaran'] ?></div>
              <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mt-1"><?= $p['dana'] ?></div>
            </td>
            <td class="py-4 px-6">
              <div class="flex gap-1.5 flex-wrap mb-1.5">
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded <?= $p['sinta']==='S1'?'bg-yellow-100 text-yellow-700 border border-yellow-200':($p['sinta']==='S2'?'bg-blue-100 text-blue-700 border border-blue-200':'bg-slate-100 text-slate-600 border border-slate-200') ?>">SINTA <?= $p['sinta'] ?></span>
                <?php if($p['scopus']): ?><span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-700 border border-orange-200">SCOPUS</span><?php endif; ?>
              </div>
              <div class="text-[10px] text-slate-500 font-medium">Mhs: <?= $p['mhs_terlibat'] ?> | Pub: <?= $p['publikasi'] ?></div>
            </td>
            <td class="py-4 px-6 text-right">
              <div class="flex gap-1 justify-end">
                <button class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                <button class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Tambah Penelitian -->
  <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
    <div @click="modalOpen=false" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" x-transition.opacity></div>
    <div class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-2xl border border-slate-200 dark:border-slate-700 max-h-[90vh] overflow-y-auto" @click.stop x-transition>
      <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
        <h3 class="font-display font-bold text-xl text-slate-800 dark:text-white flex items-center gap-2">
          <span class="w-8 h-8 rounded-xl bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></span>
          Tambah Penelitian Baru
        </h3>
        <button @click="modalOpen=false" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition">✕</button>
      </div>
      <div class="p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="md:col-span-2">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Dosen Peneliti <span class="text-red-500">*</span></label>
            <select class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
              <option value="">-- Pilih Dosen --</option>
              <option>Dr. Ahmad Fauzi, M.Kom</option><option>Prof. Dr. Hendra Kusuma, M.Cs</option><option>Dr. Siti Rahayu, M.Kom</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Judul Penelitian <span class="text-red-500">*</span></label>
            <textarea rows="2" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20" placeholder="Masukkan judul lengkap penelitian..."></textarea>
          </div>
          <div class="md:col-span-2">
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bidang Keahlian <span class="text-red-500">*</span></label>
            <input type="text" placeholder="Misal: Deep Learning, Computer Vision" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sumber Dana</label>
            <select class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
              <option>Mandiri</option><option>DIKTI</option><option>DIKTI BRIN</option><option>Kerjasama Industri</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Anggaran</label>
            <input type="text" placeholder="Rp 100.000.000" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun Mulai</label>
            <input type="number" placeholder="2025" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Status Penelitian</label>
            <select class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
              <option>Perencanaan</option><option>Sedang Berjalan</option><option>Selesai</option><option>Publikasi</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">SINTA ID</label>
            <input type="text" placeholder="S1, S2, dsb" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm uppercase focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Terindeks Scopus?</label>
            <label class="flex items-center gap-2 mt-2 cursor-pointer">
              <input type="checkbox" class="w-5 h-5 rounded border-slate-300 text-[#8c0c4c] focus:ring-[#8c0c4c]/20">
              <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Ya, Jurnal Scopus</span>
            </label>
          </div>
        </div>
        
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
          <button @click="modalOpen=false" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl font-semibold text-sm transition-all">Batal</button>
          <button @click="modalOpen=false" class="px-6 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">Simpan Penelitian</button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
