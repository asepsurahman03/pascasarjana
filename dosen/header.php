<?php
$dosen = ['nama'=>'Dr. Ahmad Fauzi, M.Kom', 'nidn'=>'0412078801', 'prodi'=>'Magister Informatika'];
$pageFile = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: window.innerWidth >= 1024, darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Portal Dosen' ?> — SIAKAD Pascasarjana NPU</title>
<script>tailwind.config={darkMode:'class',theme:{extend:{colors:{nusa:{'DEFAULT':'#961d5a','dark':'#6b1040','light':'#b8277a'}}}}}</script>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
  *{font-family:'Inter',sans-serif} h1,h2,h3,.font-display{font-family:'Poppins',sans-serif}
  .sidebar-link{display:flex;align-items:center;gap:.75rem;padding:.65rem 1rem;border-radius:.75rem;font-size:.875rem;font-weight:500;transition:all .2s;color:rgba(255,255,255,.65);text-decoration:none}
  .sidebar-link:hover{background:rgba(255,255,255,.12);color:#fff}
  .sidebar-link.active{background:rgba(255,255,255,.22);color:#fff}
  ::-webkit-scrollbar{width:5px} ::-webkit-scrollbar-thumb{background:#961d5a44;border-radius:99px}
</style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen">
<div x-show="sidebarOpen" @click="sidebarOpen=false" class="fixed inset-0 bg-black/50 z-20 lg:hidden"></div>

<!-- Sidebar Dosen -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed top-0 left-0 h-full w-64 z-30 flex flex-col transition-transform duration-300" style="background:linear-gradient(160deg,#961d5a 0%,#6b1040 50%,#4a0d2e 100%)">
  <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center font-display font-bold text-white text-lg">D</div>
    <div>
      <div class="font-display font-bold text-white text-sm">Portal Dosen</div>
      <div class="text-white/60 text-xs">SIAKAD Pascasarjana NPU</div>
    </div>
  </div>
  <!-- Profile -->
  <div class="mx-3 mt-4 p-3 rounded-xl bg-white/10 border border-white/10">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-full bg-white/20 text-white font-bold text-sm flex items-center justify-center"><?= strtoupper(substr($dosen['nama'],3,1)) ?></div>
      <div>
        <div class="text-white font-semibold text-xs truncate"><?= $dosen['nama'] ?></div>
        <div class="text-white/60 text-xs">NIDN: <?= $dosen['nidn'] ?></div>
      </div>
    </div>
  </div>
  <!-- Nav -->
  <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
    <?php
    $links = [
      ['id'=>'index',       'label'=>'Dashboard',         'href'=>'index.php',       'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
      ['id'=>'mahasiswa',   'label'=>'Mhs. Bimbingan',    'href'=>'mahasiswa.php',   'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
      ['id'=>'penelitian',  'label'=>'Penelitian Saya',   'href'=>'penelitian.php',  'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
      ['id'=>'logbook',     'label'=>'Approve Logbook',   'href'=>'logbook.php',     'icon'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
      ['id'=>'nilai',       'label'=>'Input Nilai',       'href'=>'nilai.php',       'icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
      ['id'=>'vote_jadwal', 'label'=>'Vote Jadwal',       'href'=>'vote_jadwal.php', 'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
    ];
    foreach($links as $l): $isActive = ($pageFile === $l['id']); ?>
    <a href="<?= $l['href'] ?>" class="sidebar-link <?= $isActive?'active':'' ?>">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $l['icon'] ?>"/></svg>
      <?= $l['label'] ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="px-3 pb-4 border-t border-white/10 pt-2">
    <a href="../login" class="sidebar-link">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Keluar
    </a>
  </div>
</aside>

<!-- Main -->
<div class="lg:ml-64 flex flex-col min-h-screen">
  <header class="sticky top-0 z-10 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <button @click="sidebarOpen=!sidebarOpen" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <h1 class="font-display font-bold text-slate-800 dark:text-white text-base"><?= $pageTitle ?? 'Portal Dosen' ?></h1>
    </div>
    <button @click="darkMode=!darkMode;localStorage.setItem('darkMode',darkMode)" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
      <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
      <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
    </button>
  </header>
  <main class="flex-1 p-4 md:p-6">
