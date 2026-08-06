<?php
require_once __DIR__ . '/../includes/functions.php';

// Cek autentikasi
if (!isLoggedIn() || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ' . BASE_URL . '/login?portal=mahasiswa');
    exit;
}

$username = $_SESSION['username']; // Pada mahasiswa, username adalah NIM
$mhsRow = dbQueryOne("SELECT m.*, p.nama AS prodi FROM mahasiswa m LEFT JOIN prodi p ON m.prodi_id = p.id WHERE m.nim = ?", [$username]);

if (!$mhsRow) {
    die("Data mahasiswa tidak ditemukan di database.");
}

// Data riil dari database
$mhs = [
    'id'          => $mhsRow['id'],
    'nama'        => $mhsRow['nama'],
    'nim'         => $mhsRow['nim'],
    'prodi'       => $mhsRow['prodi'] ?? '-',
    'prodi_id'    => $mhsRow['prodi_id'],
    'angkatan'    => $mhsRow['angkatan'],
    'semester'    => max(1, (date('Y') - $mhsRow['angkatan']) * 2), // Estimasi semester
    'status'      => $mhsRow['status'],
    'foto'        => $_SESSION['foto'],
    'status_tesis'=> 'Penelitian', // Bisa dinamis nanti
    'judul_tesis' => $mhsRow['judul_tesis'] ?? '-',
    'pembimbing'  => $mhsRow['dosen_pembimbing'] ?? '-',
    'ipk'         => 3.82, // Placeholder
    'jml_bimbingan'=> 7,   // Placeholder
    'pct_progress'=> 58,   // Placeholder
];

// Mapping jenis pendaftaran per Program Studi
$prodiNama = strtolower($mhs['prodi']);
$menuPendaftaran = [];

// URL lampiran Google Drive per jenis
$LAMPIRAN_URL = [
    'proposal'   => 'https://drive.google.com/drive/u/0/folders/15ZqsneMSvLTYj4my1rPTcgtCdwAxaE9o',
    'tesis'      => 'https://drive.google.com/drive/u/0/folders/1ZbSRYjiSc4vaPMo-oRRmGwgTe8UUCa5D',
    'disertasi'  => 'https://drive.google.com/drive/u/0/folders/1srB0cGrThFiuQ8K-x-o3tWdjK87ILOPc',
];

if (str_contains($prodiNama, 'informatika')) {
    // S2 Informatika: Proposal, Sidang Tesis, Capstone Project
    $menuPendaftaran = [
        ['key' => 'proposal', 'label' => 'Seminar Proposal Tesis', 'lampiran_url' => $LAMPIRAN_URL['proposal']],
        ['key' => 'tesis',    'label' => 'Sidang Tesis',            'lampiran_url' => $LAMPIRAN_URL['tesis']],
        ['key' => 'capstone', 'label' => 'Capstone Project',        'lampiran_url' => '#'],
    ];
} elseif (str_contains($prodiNama, 'manajemen')) {
    // S2 Manajemen: Proposal, Sidang Tesis, IAMP
    $menuPendaftaran = [
        ['key' => 'proposal', 'label' => 'Seminar Proposal Tesis', 'lampiran_url' => $LAMPIRAN_URL['proposal']],
        ['key' => 'tesis',    'label' => 'Sidang Tesis',            'lampiran_url' => $LAMPIRAN_URL['tesis']],
        ['key' => 'iamp',     'label' => 'Program IAMP',            'lampiran_url' => '#'],
    ];
} elseif (str_contains($prodiNama, 'pedagogi') || str_contains($prodiNama, 'pendidikan')) {
    // S2 Pedagogi: Proposal, Sidang Tesis, Kolokium
    $menuPendaftaran = [
        ['key' => 'proposal', 'label' => 'Seminar Proposal Tesis', 'lampiran_url' => $LAMPIRAN_URL['proposal']],
        ['key' => 'tesis',    'label' => 'Sidang Tesis',            'lampiran_url' => $LAMPIRAN_URL['tesis']],
        ['key' => 'kolokium', 'label' => 'Kolokium',                'lampiran_url' => '#'],
    ];
} elseif (str_contains($prodiNama, 'hukum')) {
    // S2 Hukum: Proposal, Sidang Tesis
    $menuPendaftaran = [
        ['key' => 'proposal', 'label' => 'Seminar Proposal Tesis', 'lampiran_url' => $LAMPIRAN_URL['proposal']],
        ['key' => 'tesis',    'label' => 'Sidang Tesis',            'lampiran_url' => $LAMPIRAN_URL['tesis']],
    ];
} elseif (str_contains($prodiNama, 'doktor') || str_contains($prodiNama, 's3')) {
    // S3 Doktor: Proposal Disertasi, Sidang Disertasi, Ujian Kualifikasi
    $menuPendaftaran = [
        ['key' => 'proposal',    'label' => 'Seminar Proposal Disertasi', 'lampiran_url' => $LAMPIRAN_URL['proposal']],
        ['key' => 'tesis',       'label' => 'Sidang Disertasi',           'lampiran_url' => $LAMPIRAN_URL['disertasi']],
        ['key' => 'kualifikasi', 'label' => 'Ujian Kualifikasi',          'lampiran_url' => '#'],
    ];
} else {
    // Default fallback
    $menuPendaftaran = [
        ['key' => 'proposal', 'label' => 'Seminar Proposal', 'lampiran_url' => $LAMPIRAN_URL['proposal']],
        ['key' => 'tesis',    'label' => 'Sidang Tesis',     'lampiran_url' => $LAMPIRAN_URL['tesis']],
    ];
}

$pageFile = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id" x-data="{ mobileMenuOpen: false, profileDropdown: false, akademikDropdown: false, dokumenDropdown: false }" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Portal Mahasiswa' ?> — SIAKAD Pascasarjana NPU</title>
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
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet'/>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
  *{font-family:'Inter',sans-serif}
  h1,h2,h3,.font-display{font-family:'Poppins',sans-serif}
  ::-webkit-scrollbar{width:5px;height:5px}
  ::-webkit-scrollbar-thumb{background:#961d5a44;border-radius:99px}
  .step-done{background:#10b981;color:#fff}
  .step-current{background:#961d5a;color:#fff;box-shadow:0 0 0 4px rgba(150,29,90,.2)}
  .step-pending{background:#e2e8f0;color:#94a3b8}
  .dark .step-pending{background:#334155;color:#64748b}
  .nav-link { font-weight: 600; font-size: 0.875rem; transition: all 0.2s; padding: 0.5rem 0.75rem; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem; color: rgba(255,255,255,0.7); }
  .nav-link:hover { background-color: rgba(255,255,255,0.1); color: white; }
  .nav-link.active { color: white; background-color: rgba(255,255,255,0.2); }
  
  /* Google Translate Widget Override */
  body, html { top: 0px !important; margin-top: 0px !important; }
  iframe.goog-te-banner-frame, .goog-te-banner-frame { display: none !important; }
  .VIpgJd-ZVi9od-ORHb-OEVmcd { display: none !important; }
  #google_translate_element { display: none !important; }
</style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen text-slate-800 dark:text-slate-200 transition-colors duration-300">

<!-- Top Navigation Bar -->
<nav x-data="{ mobileMenuOpen: false, profileDropdown: false, langDropdown: false, lang: document.cookie.includes('googtrans=/id/en') ? 'English' : 'Indonesia' }" class="w-full flex flex-col z-50 sticky top-0 shadow-sm">
  <!-- Top Brand Bar -->
  <div class="bg-[#8c0c4c] relative">
    <!-- Subtle Pattern Overlay -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-50">
      <div class="flex items-center justify-between py-2 md:py-3">
        <!-- Logo & Brand -->
        <a href="index" class="flex items-center gap-3 md:gap-4 hover:opacity-90 transition-opacity cursor-pointer">
          <div class="w-10 h-10 md:w-11 md:h-11 rounded-full bg-white flex items-center justify-center p-1 shadow-md shrink-0">
            <img src="../assets/images/LOGO-UNIVERSITAS-NUSA-PUTRA.png" alt="Logo Nusa Putra" class="w-full h-full object-contain">
          </div>
          <div>
            <div class="text-white/90 text-[10px] md:text-xs font-semibold mb-0.5">SIM Akademik</div>
            <div class="font-display font-bold text-white text-sm md:text-lg leading-none tracking-wide">Universitas Nusa Putra</div>
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
          <a href="notifikasi" class="relative text-white/80 hover:text-white transition">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/></svg>
            <span class="absolute -top-1.5 -right-2 bg-red-500 text-[9px] font-bold text-white w-4 h-4 rounded-full flex items-center justify-center border border-[#8c0c4c]">9+</span>
          </a>

          <a href="#" class="text-white/80 hover:text-white transition">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-1.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/></svg>
          </a>

          <button onclick="toggleDarkMode()" class="text-white/80 hover:text-white transition">
             <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 0 1-4.4 2.26 5.403 5.403 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/></svg>
             <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          </button>

          <!-- Profile -->
          <div class="relative ml-2" @click.away="profileDropdown = false">
            <button @click="profileDropdown = !profileDropdown" class="flex items-center gap-2 md:gap-3 focus:outline-none">
              <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
              </div>
              <div class="text-left hidden sm:block">
                <div class="text-white font-bold text-xs uppercase tracking-wide"><?= substr($mhs['nama'], 0, 20) ?>...</div>
                <div class="text-white/80 text-[10px]">Mahasiswa <?= htmlspecialchars($mhs['prodi']) ?></div>
              </div>
              <svg class="w-3.5 h-3.5 text-white/50 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="profileDropdown" x-transition.opacity class="absolute right-0 top-full mt-3 w-56 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-2 z-50">
              <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700 mb-2">
                <div class="font-bold text-sm text-slate-800 dark:text-white"><?= $mhs['nama'] ?></div>
                <div class="text-xs text-slate-500"><?= $mhs['nim'] ?></div>
              </div>
              <a href="profil" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Profil Saya</a>
              <a href="../logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 mt-2 border-t border-slate-100 dark:border-slate-700 pt-2">Keluar</a>
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

  <!-- Main Navigation Bar (Desktop) -->
  <div class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shadow-sm relative z-40 hidden md:block">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="hidden md:flex items-center h-12 gap-1 relative">
        
        <!-- Dashboard -->
        <a href="index" class="h-full px-4 text-[13px] font-medium flex items-center whitespace-nowrap <?= $pageFile==='index'?'text-[#8c0c4c] dark:text-[#f06ea4]':'text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
          Dashboard
        </a>

        <!-- Pendaftaran Dropdown -->
        <div class="relative flex items-center h-full group">
          <button class="h-full px-4 text-[13px] font-medium flex items-center gap-1.5 whitespace-nowrap <?= in_array($pageFile, ['pendaftaran','daftar_sidang','status_sidang'])?'text-[#8c0c4c] dark:text-[#f06ea4]':'text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
            Pendaftaran
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div class="absolute top-full left-0 hidden group-hover:block w-56 bg-white dark:bg-slate-800 rounded-b-xl shadow-lg border border-t-0 border-slate-100 dark:border-slate-700 py-2">
            
            <?php if (!empty($menuPendaftaran)): ?>
            <?php foreach($menuPendaftaran as $index => $item): ?>
            <!-- Menu <?= htmlspecialchars($item['label']) ?> (Nested Sub-menu) -->
            <div class="relative group/nested<?= $index ?>">
                <button class="w-full text-left px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4] flex items-center justify-between">
                    <span><?= htmlspecialchars($item['label']) ?></span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="absolute left-full top-0 hidden group-hover/nested<?= $index ?>:block w-56 bg-white dark:bg-slate-800 rounded-r-xl rounded-bl-xl shadow-lg border border-slate-100 dark:border-slate-700 py-2 ml-0">
                    <a href="daftar_sidang?jenis=<?= $item['key'] ?>" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]">
                        Daftar Sidang
                    </a>
                    <a href="#" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]">
                        Panduan
                    </a>
                    <a href="<?= htmlspecialchars($item['lampiran_url'] ?? '#') ?>" target="_blank" rel="noopener" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]">
                        Lampiran Persetujuan/Logbook
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Jadwal & Kalender -->
        <a href="jadwal" class="h-full px-4 text-[13px] font-medium flex items-center whitespace-nowrap <?= $pageFile==='jadwal'?'text-[#8c0c4c] dark:text-[#f06ea4]':'text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
          Jadwal &amp; Kalender
        </a>

        <!-- Daftar Dosen -->
        <a href="dosen" class="h-full px-4 text-[13px] font-medium flex items-center whitespace-nowrap <?= $pageFile==='dosen'?'text-[#8c0c4c] dark:text-[#f06ea4]':'text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
          Daftar Dosen
        </a>

        <!-- Penelitian -->
        <a href="penelitian" class="h-full px-4 text-[13px] font-medium flex items-center whitespace-nowrap <?= $pageFile==='penelitian'?'text-[#8c0c4c] dark:text-[#f06ea4]':'text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
          Penelitian
        </a>

        <!-- Dokumen Dropdown -->
        <div class="relative flex items-center h-full group">
          <button class="h-full px-4 text-[13px] font-medium flex items-center gap-1.5 whitespace-nowrap text-slate-600 dark:text-slate-300 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]">
            Dokumen
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div class="absolute top-full left-0 md:right-0 md:left-auto hidden group-hover:block w-56 bg-white dark:bg-slate-800 rounded-b-xl shadow-lg border border-t-0 border-slate-100 dark:border-slate-700 py-2">
            <a href="#" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-2"><span class="text-lg">📄</span> Template Proposal Tesis</a>
            <a href="#" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-2"><span class="text-lg">📄</span> Template Tesis Final</a>
            <a href="#" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-2"><span class="text-lg">📝</span> Form Persetujuan Ujian</a>
            <a href="#" class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-2"><span class="text-lg">📘</span> Panduan Penulisan</a>
          </div>
        </div>

      </div>
    </div>
  </div>


  <!-- Mobile Menu Content -->
  <div x-show="mobileMenuOpen" x-transition class="md:hidden border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-4 space-y-1">
    <a href="index" class="block px-4 py-2.5 text-sm font-medium rounded-lg <?= $pageFile==='index'?'bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-[#f06ea4]/10 dark:text-[#f06ea4]':'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' ?> flex items-center gap-3 transition-colors"><span class="text-lg">🏠</span> Dashboard</a>
    
    <div class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-2 border-t border-slate-100 dark:border-slate-800 pt-4">Pendaftaran</div>
    
    <?php if (!empty($menuPendaftaran)): ?>
    <?php foreach($menuPendaftaran as $index => $item): ?>
    <div x-data="{ openMenu<?= $index ?>: false }">
        <button @click="openMenu<?= $index ?> = !openMenu<?= $index ?>" class="w-full px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-between transition-colors mt-1">
            <span><?= htmlspecialchars($item['label']) ?></span>
            <svg class="w-4 h-4 transition-transform" :class="openMenu<?= $index ?> ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="openMenu<?= $index ?>" x-collapse class="pl-8 pr-4 py-1 space-y-1">
            <a href="daftar_sidang?jenis=<?= $item['key'] ?>" class="block py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4] transition-colors">
                Daftar Sidang
            </a>
            <a href="#" class="block py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4] transition-colors">
                Panduan
            </a>
            <a href="<?= htmlspecialchars($item['lampiran_url'] ?? '#') ?>" target="_blank" rel="noopener" class="block py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4] transition-colors">
                Lampiran Persetujuan/Logbook
            </a>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
    
    <div class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-2 border-t border-slate-100 dark:border-slate-800 pt-4">Jadwal &amp; Penelitian</div>
    <a href="jadwal" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-3 transition-colors"><span class="text-lg">📅</span> Jadwal &amp; Kalender</a>
    <a href="dosen" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-3 transition-colors"><span class="text-lg">👨‍🏫</span> Daftar Dosen</a>
    <a href="penelitian" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-3 transition-colors"><span class="text-lg">🔬</span> Penelitian Dosen</a>
    
    <div class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-2 border-t border-slate-100 dark:border-slate-800 pt-4">Dokumen</div>
    <a href="#" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-3 transition-colors"><span class="text-lg">📄</span> Formulir &amp; Template</a>
    <a href="#" class="block px-4 py-2.5 text-sm font-medium rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-3 transition-colors"><span class="text-lg">📘</span> Panduan Akademik</a>
    
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
    <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
      <svg class="w-3 h-3 text-slate-700 dark:text-slate-300" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
      <span class="hover:text-[#8c0c4c] cursor-pointer font-medium text-slate-600 dark:text-slate-300">Beranda</span>
      <span class="text-slate-300">></span>
      <span class="text-slate-400"><?= $pageTitle ?? 'Beranda' ?></span>
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
<div class="flex flex-col min-h-screen">
  <!-- Page Content -->
  <main class="flex-1 px-4 pt-4 pb-8 sm:px-6 sm:pt-5 lg:px-8 max-w-7xl mx-auto w-full">
