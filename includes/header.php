<?php
require_once __DIR__ . '/functions.php';
requireLogin();
$user        = getCurrentUser();
$unreadCount = countUnread();
$allProdi    = getAllProdi();
$flash       = getFlash();
$notifikasi  = dbQuery("SELECT * FROM notifikasi WHERE user_id=? ORDER BY created_at DESC LIMIT 8",[$user['id']]);

$pageFile = basename($_SERVER['PHP_SELF'], '.php');
if ($pageFile === 'surat_buat') $activePage = 'surat';
elseif (in_array($pageFile, ['surat_keluaran','agenda_surat'])) $activePage = $pageFile;
elseif (basename(dirname($_SERVER['PHP_SELF']))==='pages') $activePage = $pageFile;
else $activePage = 'index';

// Grouping menu items for horizontal nav
$navMasterData = ['dosen_pasca', 'mahasiswa', 'prodi'];
$navAkademik = ['sidang', 'jadwal', 'penelitian_dosen', 'tugas', 'analisis_rapot'];
$navSurat = ['surat', 'surat_keluaran', 'agenda_surat'];
$navLampiran = ['buat_lampiran_tesis', 'buat_lampiran_proposal', 'buat_lampiran_capstone', 'buat_lampiran_iamp', 'buat_lampiran_kolokium', 'buat_lampiran_kualifikasi'];
$navPanduan = ['panduan_tesis', 'panduan_kolokium', 'panduan_capstone', 'panduan_iamp', 'panduan_kualifikasi'];
$navSistem = ['whatsapp', 'laporan', 'settings'];
?>
<!DOCTYPE html>
<html lang="id" x-data="{ mobileMenuOpen: false, profileDropdown: false, langDropdown: false, lang: document.cookie.includes('googtrans=/id/en') ? 'English' : 'Indonesia' }" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Dashboard') ?> — Pascasarjana NPU</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={darkMode:'class',theme:{fontFamily:{sans:['Inter','sans-serif'],display:['Poppins','sans-serif']},extend:{colors:{nusa:{'DEFAULT':'#961d5a','dark':'#6b1040','light':'#b8277a','50':'#fdf0f6','100':'#fce7f3','700':'#961d5a','800':'#6b1040'}}}}}</script>
<script>
  if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
  function toggleDarkMode() {
    document.documentElement.classList.toggle('dark');
    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
  }
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet'/>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<style>
  *{font-family:'Inter',sans-serif} h1,h2,h3,.font-display{font-family:'Poppins',sans-serif}
  ::-webkit-scrollbar{width:5px;height:5px} ::-webkit-scrollbar-thumb{background:#961d5a44;border-radius:99px}
  .nav-link { font-weight: 600; font-size: 0.875rem; transition: all 0.2s; padding: 0.5rem 0.75rem; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem; color: rgba(255,255,255,0.7); }
  .nav-link:hover { background-color: rgba(255,255,255,0.1); color: white; }
  .nav-link.active { color: white; background-color: rgba(255,255,255,0.2); }
  
  @media print {
    @page { margin: 1.5cm; }
    nav, header, .no-print { display: none !important; }
    body { background: white !important; color: black !important; margin: 0 !important; padding: 0 !important; }
    .card, .overflow-hidden, .overflow-x-auto { box-shadow: none !important; border: none !important; border-radius: 0 !important; padding: 0 !important; background: transparent !important; overflow: visible !important; }
    .min-h-screen { min-height: auto !important; }
    main { padding: 0 !important; }
    table { width: 100% !important; border-collapse: collapse !important; margin-bottom: 2rem !important; table-layout: auto !important; }
    th, td { border: 1px solid #334155 !important; padding: 8px 12px !important; text-align: left !important; vertical-align: middle !important; }
    td { color: #000 !important; }
    th { background-color: #961d5a !important; color: #fff !important; font-weight: bold !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }

  /* Google Translate Widget Override */
  body, html { top: 0px !important; margin-top: 0px !important; }
  iframe.goog-te-banner-frame, .goog-te-banner-frame { display: none !important; }
  .VIpgJd-ZVi9od-ORHb-OEVmcd { display: none !important; }
  #google_translate_element { display: none !important; }
</style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen text-slate-800 dark:text-slate-200 transition-colors duration-300">

<!-- Top Navigation Bar -->
<nav class="w-full flex flex-col z-50 sticky top-0 shadow-sm">
  <!-- Top Brand Bar -->
  <div class="bg-[#8c0c4c] relative">
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-50">
      <div class="flex items-center justify-between py-2 md:py-3">
        <!-- Logo & Brand -->
        <a href="<?= BASE_URL ?>/index" class="flex items-center gap-3 md:gap-4 hover:opacity-90 transition-opacity cursor-pointer">
          <div class="w-10 h-10 md:w-11 md:h-11 rounded-full bg-white flex items-center justify-center p-1 shadow-md shrink-0">
            <img src="<?= BASE_URL ?>/assets/images/LOGO-UNIVERSITAS-NUSA-PUTRA.png" alt="Logo Nusa Putra" class="w-full h-full object-contain">
          </div>
          <div>
            <div class="text-white/90 text-[10px] md:text-xs font-semibold mb-0.5">Admin Panel</div>
            <div class="font-display font-bold text-white text-sm md:text-lg leading-none tracking-wide">Pascasarjana NPU</div>
          </div>
        </a>

        <!-- Right Actions -->
        <div class="hidden md:flex items-center gap-5">
          <!-- Language Toggle Dropdown -->
          <div class="relative" @click.away="langDropdown = false">
            <button @click="langDropdown = !langDropdown" class="flex items-center gap-1.5 text-white text-[11px] font-bold bg-white/10 hover:bg-white/20 transition px-3 py-1.5 rounded-full focus:outline-none">
               <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
               <span x-text="lang">Indonesia</span>
               <svg class="w-3.5 h-3.5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="langDropdown" x-transition.opacity class="absolute right-0 top-full mt-2 w-36 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-1.5 z-50">
              <button @click="changeLanguage('Indonesia')" class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 flex items-center justify-between">
                <span>Indonesia</span>
                <svg x-show="lang === 'Indonesia'" class="w-4 h-4 text-[#8c0c4c] dark:text-[#f06ea4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              </button>
              <button @click="changeLanguage('English')" class="w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 flex items-center justify-between">
                <span>English</span>
                <svg x-show="lang === 'English'" class="w-4 h-4 text-[#8c0c4c] dark:text-[#f06ea4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              </button>
            </div>
          </div>
          
          <div id="google_translate_element" style="display:none;"></div>
          
          <!-- Notification -->
          <div class="relative" x-data="{ notifOpen: false }" @click.away="notifOpen = false">
            <button @click="notifOpen = !notifOpen" class="relative text-white/80 hover:text-white transition focus:outline-none">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/></svg>
              <?php if($unreadCount>0): ?>
                <span class="absolute -top-1.5 -right-2 bg-red-500 text-[9px] font-bold text-white w-4 h-4 rounded-full flex items-center justify-center border border-[#8c0c4c]"><?= $unreadCount ?></span>
              <?php endif; ?>
            </button>
            <div x-show="notifOpen" x-transition.opacity class="absolute right-0 top-full mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-1.5 z-50 overflow-hidden" style="display: none;">
              <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                <span class="font-semibold text-sm text-slate-800 dark:text-white">Notifikasi</span>
              </div>
              <div class="max-h-72 overflow-y-auto">
                <?php if(empty($notifikasi)): ?>
                  <div class="p-4 text-center text-sm text-slate-500">Tidak ada notifikasi baru</div>
                <?php else: foreach($notifikasi as $n): ?>
                  <a href="<?= e($n['link']??'#') ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition <?= $n['is_read']?'opacity-60':'' ?>">
                    <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 <?= $n['is_read']?'bg-slate-300 dark:bg-slate-600':'bg-nusa' ?>"></div>
                    <div>
                      <p class="text-sm text-slate-700 dark:text-slate-300"><?= e($n['pesan']) ?></p>
                      <p class="text-xs text-slate-400 mt-1"><?= formatTanggal($n['created_at'], true) ?></p>
                    </div>
                  </a>
                <?php endforeach; endif; ?>
              </div>
            </div>
          </div>

          <!-- Dark mode -->
          <button onclick="toggleDarkMode()" class="text-white/80 hover:text-white transition">
             <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 0 1-4.4 2.26 5.403 5.403 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/></svg>
             <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          </button>

          <!-- Profile -->
          <div class="relative ml-2" @click.away="profileDropdown = false">
            <button @click="profileDropdown = !profileDropdown" class="flex items-center gap-2 md:gap-3 focus:outline-none">
              <div class="w-8 h-8 rounded-full bg-amber-400 flex items-center justify-center text-amber-900 font-bold text-sm shrink-0">
                <?= strtoupper(substr($user['nama'] ?? 'A', 0, 1)) ?>
              </div>
              <div class="text-left hidden sm:block">
                <div class="text-white font-bold text-xs uppercase tracking-wide"><?= substr(e($user['nama']), 0, 20) ?></div>
                <div class="text-white/80 text-[10px]"><?= $user['role']==='super_admin' ? 'Super Admin' : 'Kaprodi' ?></div>
              </div>
              <svg class="w-3.5 h-3.5 text-white/50 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="profileDropdown" x-transition.opacity class="absolute right-0 top-full mt-3 w-56 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-2 z-50">
              <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700 mb-2">
                <div class="font-bold text-sm text-slate-800 dark:text-white"><?= e($user['nama']) ?></div>
                <div class="text-xs text-slate-500"><?= $user['email'] ?? '' ?></div>
              </div>
              <a href="<?= BASE_URL ?>/pages/ganti_password" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-nusa transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                Ganti Password
              </a>
              <a href="<?= BASE_URL ?>/logout" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 border-t border-slate-100 dark:border-slate-700 mt-1 pt-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Keluar
              </a>
            </div>
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition">
          <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Main Navigation Bar -->
  <div class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shadow-sm relative z-40 hidden md:block">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="hidden md:flex items-center h-12 gap-1 relative">
        
        <!-- Dashboard -->
        <a href="<?= BASE_URL ?>/index" class="h-full px-4 text-[13px] font-medium flex items-center whitespace-nowrap <?= $pageFile==='index'?'text-[#8c0c4c] dark:text-[#f06ea4] border-b-2 border-[#8c0c4c] dark:border-[#f06ea4]':'text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
          Dashboard
        </a>

        <!-- Master Data -->
        <div class="relative flex items-center h-full group">
          <button class="h-full px-4 text-[13px] font-medium flex items-center gap-1.5 whitespace-nowrap <?= in_array($pageFile, $navMasterData)?'text-[#8c0c4c] dark:text-[#f06ea4] border-b-2 border-[#8c0c4c] dark:border-[#f06ea4]':'text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
            Master Data
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div class="absolute top-full left-0 hidden group-hover:block w-48 bg-white dark:bg-slate-800 rounded-b-xl shadow-lg border border-t-0 border-slate-100 dark:border-slate-700 py-2">
            <a href="<?= BASE_URL ?>/pages/mahasiswa" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Data Mahasiswa</a>
            <a href="<?= BASE_URL ?>/pages/dosen_pasca" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Dosen Pasca</a>
            <a href="<?= BASE_URL ?>/pages/prodi" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Manajemen Prodi</a>
          </div>
        </div>

        <!-- Akademik -->
        <div class="relative flex items-center h-full group">
          <button class="h-full px-4 text-[13px] font-medium flex items-center gap-1.5 whitespace-nowrap <?= in_array($pageFile, $navAkademik)?'text-[#8c0c4c] dark:text-[#f06ea4] border-b-2 border-[#8c0c4c] dark:border-[#f06ea4]':'text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
            Akademik
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div class="absolute top-full left-0 hidden group-hover:block w-48 bg-white dark:bg-slate-800 rounded-b-xl shadow-lg border border-t-0 border-slate-100 dark:border-slate-700 py-2">
            <a href="<?= BASE_URL ?>/pages/sidang" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Sidang & Seminar</a>
            <a href="<?= BASE_URL ?>/pages/jadwal" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Jadwal Agenda</a>
            <a href="<?= BASE_URL ?>/pages/penelitian_dosen" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Penelitian Dosen</a>
            <a href="<?= BASE_URL ?>/pages/tugas" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Tugas & Catatan</a>
            <a href="<?= BASE_URL ?>/pages/analisis_rapot" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Analisis Rapot</a>
          </div>
        </div>

        <!-- Persuratan -->
        <div class="relative flex items-center h-full group">
          <button class="h-full px-4 text-[13px] font-medium flex items-center gap-1.5 whitespace-nowrap <?= (in_array($pageFile, $navSurat) || $activePage === 'surat')?'text-[#8c0c4c] dark:text-[#f06ea4] border-b-2 border-[#8c0c4c] dark:border-[#f06ea4]':'text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
            Persuratan
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div class="absolute top-full left-0 hidden group-hover:block w-56 bg-white dark:bg-slate-800 rounded-b-xl shadow-lg border border-t-0 border-slate-100 dark:border-slate-700 py-2">
            <a href="<?= BASE_URL ?>/pages/surat_buat" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Buat Surat</a>
            <a href="<?= BASE_URL ?>/pages/surat_keluaran" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Surat Keluaran</a>
            <a href="<?= BASE_URL ?>/pages/agenda_surat" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Buku Agenda</a>
          </div>
        </div>

        <!-- Dokumen & Lampiran (Nested Submenus) -->
        <div class="relative flex items-center h-full group">
          <button class="h-full px-4 text-[13px] font-medium flex items-center gap-1.5 whitespace-nowrap <?= (in_array($pageFile, $navLampiran) || in_array($pageFile, $navPanduan))?'text-[#8c0c4c] dark:text-[#f06ea4] border-b-2 border-[#8c0c4c] dark:border-[#f06ea4]':'text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
            Dokumen
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          
          <div class="absolute top-full left-0 hidden group-hover:block w-56 bg-white dark:bg-slate-800 rounded-b-xl shadow-lg border border-t-0 border-slate-100 dark:border-slate-700 py-2">
            
            <!-- Submenu 1: Cetak Lampiran Form -->
            <div class="relative group/sub1">
              <button class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c] flex items-center justify-between">
                Cetak Lampiran Form
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              </button>
              
              <div class="absolute top-0 left-[98%] hidden group-hover/sub1:block w-56 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 py-2">
                <a href="<?= BASE_URL ?>/pages/buat_lampiran_proposal" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Seminar Proposal</a>
                <a href="<?= BASE_URL ?>/pages/buat_lampiran_tesis" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Sidang Tesis</a>
                <a href="<?= BASE_URL ?>/pages/buat_lampiran_capstone" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Capstone Project</a>
                <a href="<?= BASE_URL ?>/pages/buat_lampiran_iamp" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">IAMP</a>
                <a href="<?= BASE_URL ?>/pages/buat_lampiran_kolokium" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Kolokium</a>
                <a href="<?= BASE_URL ?>/pages/buat_lampiran_kualifikasi" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Ujian Kualifikasi</a>
              </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>

            <!-- Submenu 2: Panduan & Template -->
            <div class="relative group/sub2">
              <button class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c] flex items-center justify-between">
                Panduan & Template
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              </button>
              
              <div class="absolute top-0 left-[98%] hidden group-hover/sub2:block w-56 bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 py-2">
                <a href="<?= BASE_URL ?>/pages/panduan_tesis" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Tesis & Proposal</a>
                <a href="<?= BASE_URL ?>/pages/panduan_capstone" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Capstone Project</a>
                <a href="<?= BASE_URL ?>/pages/panduan_iamp" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">IAMP</a>
                <a href="<?= BASE_URL ?>/pages/panduan_kolokium" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Kolokium & Kualifikasi</a>
              </div>
            </div>

          </div>
        </div>

        <!-- Pengaturan -->
        <div class="relative flex items-center h-full group">
          <button class="h-full px-4 text-[13px] font-medium flex items-center gap-1.5 whitespace-nowrap <?= in_array($pageFile, $navSistem)?'text-[#8c0c4c] dark:text-[#f06ea4] border-b-2 border-[#8c0c4c] dark:border-[#f06ea4]':'text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
            Pengaturan
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div class="absolute top-full left-0 md:right-0 md:left-auto hidden group-hover:block w-48 bg-white dark:bg-slate-800 rounded-b-xl shadow-lg border border-t-0 border-slate-100 dark:border-slate-700 py-2">
            <a href="<?= BASE_URL ?>/pages/whatsapp" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">WhatsApp Logs</a>
            <a href="<?= BASE_URL ?>/pages/laporan" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Laporan Sistem</a>
            <a href="<?= BASE_URL ?>/pages/settings" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c]">Pengaturan</a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Mobile Menu Content -->
  <div x-show="mobileMenuOpen" x-transition class="md:hidden border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-4 space-y-1">
    <a href="<?= BASE_URL ?>/index" class="block px-4 py-2.5 text-sm font-medium rounded-lg <?= $pageFile==='index'?'bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-[#f06ea4]/10 dark:text-[#f06ea4]':'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' ?>">Dashboard</a>
    
    <div class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-2 border-t border-slate-100 dark:border-slate-800 pt-4">Master Data</div>
    <a href="<?= BASE_URL ?>/pages/mahasiswa" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Data Mahasiswa</a>
    <a href="<?= BASE_URL ?>/pages/dosen_pasca" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Dosen Pasca</a>
    <a href="<?= BASE_URL ?>/pages/prodi" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Manajemen Prodi</a>
    
    <div class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-2 border-t border-slate-100 dark:border-slate-800 pt-4">Akademik</div>
    <a href="<?= BASE_URL ?>/pages/sidang" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Sidang & Seminar</a>
    <a href="<?= BASE_URL ?>/pages/jadwal" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Jadwal Agenda</a>
    <a href="<?= BASE_URL ?>/pages/penelitian_dosen" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Penelitian Dosen</a>
    <a href="<?= BASE_URL ?>/pages/tugas" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Tugas & Catatan</a>
    <a href="<?= BASE_URL ?>/pages/analisis_rapot" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Analisis Rapot</a>
    
    <div class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-2 border-t border-slate-100 dark:border-slate-800 pt-4">Persuratan</div>
    <a href="<?= BASE_URL ?>/pages/surat_buat" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Buat Surat</a>
    <a href="<?= BASE_URL ?>/pages/surat_keluaran" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Surat Keluaran</a>
    
    <!-- Mobile Dokumen Group -->
    <div x-data="{ openDokumen: false }" class="mt-2 border-t border-slate-100 dark:border-slate-800 pt-3">
      <button @click="openDokumen = !openDokumen" class="w-full flex items-center justify-between px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider hover:text-slate-600">
        <span>Dokumen & Lampiran</span>
        <svg class="w-3.5 h-3.5 transition-transform" :class="openDokumen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      
      <div x-show="openDokumen" x-collapse class="pl-3 pr-2 space-y-3 mt-1 pb-2">
        <!-- Cetak Lampiran -->
        <div>
          <div class="px-2 py-1 text-[9px] font-bold text-slate-400/80 uppercase">Cetak Lampiran Form</div>
          <div class="space-y-0.5">
            <a href="<?= BASE_URL ?>/pages/buat_lampiran_proposal" class="block px-3 py-2 text-sm rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Seminar Proposal</a>
            <a href="<?= BASE_URL ?>/pages/buat_lampiran_tesis" class="block px-3 py-2 text-sm rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Sidang Tesis</a>
            <a href="<?= BASE_URL ?>/pages/buat_lampiran_capstone" class="block px-3 py-2 text-sm rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Capstone Project</a>
            <a href="<?= BASE_URL ?>/pages/buat_lampiran_iamp" class="block px-3 py-2 text-sm rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">IAMP</a>
            <a href="<?= BASE_URL ?>/pages/buat_lampiran_kolokium" class="block px-3 py-2 text-sm rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Kolokium & Kualifikasi</a>
          </div>
        </div>
        
        <!-- Panduan & Template -->
        <div>
          <div class="px-2 py-1 text-[9px] font-bold text-slate-400/80 uppercase">Panduan & Template</div>
          <div class="space-y-0.5">
            <a href="<?= BASE_URL ?>/pages/panduan_tesis" class="block px-3 py-2 text-sm rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Tesis & Proposal</a>
            <a href="<?= BASE_URL ?>/pages/panduan_capstone" class="block px-3 py-2 text-sm rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Capstone Project</a>
            <a href="<?= BASE_URL ?>/pages/panduan_iamp" class="block px-3 py-2 text-sm rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">IAMP</a>
            <a href="<?= BASE_URL ?>/pages/panduan_kolokium" class="block px-3 py-2 text-sm rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Kolokium & Kualifikasi</a>
          </div>
        </div>
      </div>
    </div>
    
    <div class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-2 border-t border-slate-100 dark:border-slate-800 pt-4">Pengaturan</div>
    <a href="<?= BASE_URL ?>/pages/whatsapp" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">WhatsApp Logs</a>
    <a href="<?= BASE_URL ?>/pages/laporan" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Laporan Sistem</a>
    <a href="<?= BASE_URL ?>/pages/settings" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Pengaturan</a>

    <div class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-2 border-t border-slate-200 dark:border-slate-800 pt-3">Pilih Bahasa</div>
    <div class="flex px-4 py-2 pb-4 gap-3">
      <button @click="changeLanguage('Indonesia')" :class="lang === 'Indonesia' ? 'bg-[#8c0c4c] text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="px-4 py-2.5 text-xs font-semibold rounded-xl flex-1 text-center transition border border-transparent dark:border-slate-700">Indonesia</button>
      <button @click="changeLanguage('English')" :class="lang === 'English' ? 'bg-[#8c0c4c] text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300'" class="px-4 py-2.5 text-xs font-semibold rounded-xl flex-1 text-center transition border border-transparent dark:border-slate-700">English</button>
    </div>
  </div>
</nav>

<!-- Breadcrumb Bar -->
<div class="bg-[#fcfdfd] dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800 hidden md:block relative z-30">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
        <svg class="w-3 h-3 text-slate-700 dark:text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
        <span class="font-medium text-slate-600 dark:text-slate-300">Admin</span>
        <span class="text-slate-300">></span>
        <span class="text-slate-400"><?= $pageTitle ?? 'Beranda' ?></span>
      </div>
      <div id="topbar-actions" class="hidden md:flex text-sm"></div>
    </div>
  </div>
</div>

<!-- Google Translate Scripts -->
<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({
    pageLanguage: 'id',
    includedLanguages: 'en,id',
    layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
    autoDisplay: false
  }, 'google_translate_element');
}
function changeLanguage(langCode) {
  var code = langCode === 'English' ? '/id/en' : '/id/id';
  document.cookie = "googtrans=" + code + "; path=/";
  document.cookie = "googtrans=" + code + "; path=/; domain=" + window.location.hostname;
  window.location.reload();
}
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<!-- Main Wrapper -->
<div class="flex flex-col min-h-screen pt-2 md:pt-4">
  
  <?php if($flash): ?>
  <div x-data="{ show: true }" x-show="show" class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mb-2">
    <div class="p-4 rounded-xl flex items-center justify-between text-sm shadow-sm <?= $flash['type']==='success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400' : 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400' ?>">
      <div class="flex items-center gap-2">
        <?php if($flash['type']==='success'): ?>
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?php else: ?>
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?php endif; ?>
        <span><?= e($flash['message']) ?></span>
      </div>
      <button @click="show = false" class="opacity-50 hover:opacity-100 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
    </div>
  </div>
  <?php endif; ?>

  <!-- Page Content -->
  <main class="flex-1 px-4 sm:px-6 lg:px-8 py-2 md:py-4 max-w-7xl mx-auto w-full">
