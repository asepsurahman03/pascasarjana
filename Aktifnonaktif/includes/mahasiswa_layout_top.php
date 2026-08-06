<?php
/**
 * Mahasiswa Sidebar Layout Include
 * Usage: include this at top of each mahasiswa view, pass $data array
 */
$user      = $data['user']      ?? currentUser();
$mahasiswa = $data['mahasiswa'] ?? ($user['mahasiswa'] ?? []);
$title     = $data['title']     ?? APP_NAME;
$activePage = $_GET['page'] ?? '';
?>
<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: window.innerWidth >= 1024, darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Dashboard Mahasiswa - <?= e(APP_NAME) ?>">
<title><?= e($title) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={darkMode:'class',theme:{extend:{colors:{nusa:{'DEFAULT':'#961d5a','dark':'#6b1040','light':'#b8277a','50':'#fdf0f6','100':'#fce7f3','700':'#961d5a','800':'#6b1040'}},fontFamily:{inter:['Inter','sans-serif'],poppins:['Poppins','sans-serif']}}}}</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<style type="text/tailwindcss">
  *{font-family:'Inter',sans-serif}
  h1,h2,h3,.font-display{font-family:'Poppins',sans-serif}
  .sidebar-item{@apply flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 cursor-pointer}
  .sidebar-item:hover{@apply bg-white/10 text-white}
  .sidebar-item.active{@apply bg-white/20 text-white shadow-sm border border-white/20}
  .sidebar-item.inactive{@apply text-white/70 hover:text-white}
  .card{@apply bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700}
  .card-gradient{@apply bg-gradient-to-br rounded-2xl shadow-md}
  .badge{@apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold}
  ::-webkit-scrollbar{width:6px;height:6px}
  ::-webkit-scrollbar-track{background:transparent}
  ::-webkit-scrollbar-thumb{background:#C1121F33;border-radius:99px}
  ::-webkit-scrollbar-thumb:hover{background:#C1121F66}
  .animate-skeleton{animation:skeleton 1.5s ease-in-out infinite}
  @keyframes skeleton{0%,100%{opacity:1}50%{opacity:.5}}
  .toast-enter{animation:toastIn 0.4s ease}
  @keyframes toastIn{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen transition-colors duration-300"
      @resize.window="sidebarOpen = window.innerWidth >= 1024">

<!-- Mobile overlay -->
<div x-show="sidebarOpen && window.innerWidth < 1024"
  @click="sidebarOpen=false"
  x-transition:enter="transition-opacity ease-linear duration-300"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition-opacity ease-linear duration-300"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  class="fixed inset-0 z-20 bg-black/50 lg:hidden"></div>

<!-- SIDEBAR -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
  class="fixed top-0 left-0 h-full w-64 z-30 transition-transform duration-300 ease-in-out
         bg-gradient-to-b from-nusa-dark via-nusa to-[#6b0010]
         flex flex-col shadow-2xl lg:translate-x-0">

  <!-- Logo -->
  <div class="p-5 border-b border-white/10">
    <a href="<?= APP_URL ?>/?page=mahasiswa/dashboard" class="flex items-center gap-3 hover:opacity-90 transition">
      <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0 p-1">
        <img src="/img/Logo_Universitas_Nusa_Putra.png" alt="Logo Nusa Putra" class="w-full h-full object-contain">
      </div>
      <div>
        <p class="text-white font-display font-bold text-sm leading-tight">Nusa Putra</p>
        <p class="text-white/60 text-xs">Universitas Nusa Putra</p>
      </div>
    </a>
  </div>

  <!-- User Profile -->
  <div class="p-4 border-b border-white/10">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0 ring-2 ring-white/30 overflow-hidden">
        <?php
          $avatar = currentUserAvatar();
          if (!empty($mahasiswa['foto'])): ?>
        <img src="<?= uploadUrl('photos/' . e($mahasiswa['foto'])) ?>" class="w-10 h-10 rounded-full object-cover" alt="Foto">
        <?php elseif (!empty($avatar)): ?>
        <img src="<?= e($avatar) ?>" class="w-10 h-10 rounded-full object-cover" referrerpolicy="no-referrer" alt="Avatar Google">
        <?php else: ?>
        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        <?php endif; ?>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-white text-sm font-semibold truncate"><?= e($mahasiswa['nama'] ?? $user['nama']) ?></p>
        <p class="text-white/50 text-xs truncate"><?= e($mahasiswa['nim'] ?? 'Mahasiswa') ?></p>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
    <?php
    $navItems = [
      ['page' => 'mahasiswa/dashboard', 'label' => 'Dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
      ['page' => 'mahasiswa/form',      'label' => 'Isi Formulir', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>'],
      ['page' => 'mahasiswa/riwayat',   'label' => 'Riwayat Pengajuan', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
      ['page' => 'mahasiswa/profile',   'label' => 'Profil Saya', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
    ];
    foreach ($navItems as $item):
      $isActive = $activePage === $item['page'];
    ?>
    <a href="<?= APP_URL ?>/?page=<?= $item['page'] ?>"
       class="sidebar-item <?= $isActive ? 'active' : 'inactive' ?>">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $item['icon'] ?></svg>
      <span><?= $item['label'] ?></span>
      <?php if ($isActive): ?>
      <div class="ml-auto w-1.5 h-5 rounded-full bg-white/60"></div>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Logout -->
  <div class="p-3 border-t border-white/10">
    <a href="<?= APP_URL ?>/?page=logout"
       onclick="return confirm('Yakin ingin keluar?')"
       class="sidebar-item inactive hover:bg-red-900/30 w-full">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      <span>Keluar</span>
    </a>
  </div>
</aside>

<!-- MAIN CONTENT WRAPPER -->
<div class="lg:ml-64 min-h-screen flex flex-col">

  <!-- TOP NAVBAR -->
  <header class="sticky top-0 z-10 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border-b border-gray-200 dark:border-gray-700 px-4 lg:px-6 py-3 flex items-center gap-4">
    <!-- Mobile menu button -->
    <button @click="sidebarOpen=!sidebarOpen" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 flex-1">
      <a href="<?= APP_URL ?>/?page=mahasiswa/dashboard" class="hover:text-nusa transition">Dashboard</a>
      <?php if ($activePage !== 'mahasiswa/dashboard'): ?>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-gray-800 dark:text-white font-medium"><?= e(ucwords(str_replace(['mahasiswa/', '-', '_'], ['', ' ', ' '], $activePage))) ?></span>
      <?php endif; ?>
    </nav>

    <!-- Dark Mode + User -->
    <div class="flex items-center gap-3">
      <button @click="darkMode=!darkMode; localStorage.setItem('darkMode', darkMode)"
        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      </button>

      <div class="flex items-center gap-2 text-sm">
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-nusa to-nusa-dark flex items-center justify-center text-white text-xs font-bold">
          <?= strtoupper(substr($mahasiswa['nama'] ?? $user['nama'] ?? 'M', 0, 1)) ?>
        </div>
        <span class="hidden md:block text-gray-700 dark:text-gray-300 font-medium truncate max-w-[120px]">
          <?= e($mahasiswa['nama'] ?? $user['nama']) ?>
        </span>
      </div>
    </div>
  </header>

  <!-- PAGE CONTENT starts here — close in footer include -->
  <main class="flex-1 p-4 lg:p-6">
