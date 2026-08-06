<?php
/**
 * Admin Layout Top Include
 */
$user       = $data['user'] ?? currentUser();
$title      = $data['title'] ?? 'Admin - ' . APP_NAME;
$activePage = $_GET['page'] ?? '';

// Fetch fresh user data from DB to always get current avatar
if (!empty($user['id'])) {
    $freshUser = Database::fetchOne("SELECT id, nama, email, role, avatar, auth_provider, program_studi FROM users WHERE id = ?", [$user['id']]);
    if ($freshUser) {
        $user['avatar'] = $freshUser['avatar'] ?? null;
    }
}
?>
<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: window.innerWidth >= 1024, darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={darkMode:'class',theme:{extend:{colors:{nusa:{'DEFAULT':'#961d5a','dark':'#6b1040','light':'#b8277a','50':'#fdf0f6','100':'#fce7f3','700':'#961d5a','800':'#6b1040'}}}}}</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
  *{font-family:'Inter',sans-serif} h1,h2,h3,.font-display{font-family:'Poppins',sans-serif}
  .sidebar-item{display:flex;align-items:center;gap:0.75rem;padding:0.7rem 1rem;border-radius:0.75rem;font-size:0.875rem;font-weight:500;transition:all .2s;cursor:pointer}
  .sidebar-item:hover{background:rgba(255,255,255,.12);color:#fff}
  .sidebar-item.active{background:rgba(255,255,255,.2);color:#fff;box-shadow:0 2px 8px rgba(0,0,0,.15)}
  .sidebar-item.inactive{color:rgba(255,255,255,.65)}
  .card{background:#fff;border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f1f5f9}
  .dark .card{background:#1e293b;border-color:#334155}
  .badge{display:inline-flex;align-items:center;padding:.2rem .7rem;border-radius:9999px;font-size:.75rem;font-weight:600}
  ::-webkit-scrollbar{width:5px;height:5px} ::-webkit-scrollbar-thumb{background:#961d5a44;border-radius:99px}
  .stat-card{position:relative;overflow:hidden;border-radius:1.2rem;padding:1.5rem;color:#fff}
  .stat-card::after{content:'';position:absolute;top:-30px;right:-30px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.08)}
  /* DataTables override */
  .dataTables_wrapper .dataTables_filter input{border:1px solid #e2e8f0;border-radius:0.5rem;padding:.4rem .8rem;font-size:.85rem}
  .dark .dataTables_wrapper .dataTables_filter input{border-color:#475569;background:#1e293b;color:#e2e8f0}
  .dataTables_wrapper .dataTables_length select{border:1px solid #e2e8f0;border-radius:.5rem;padding:.35rem .6rem}
  .dark .dataTables_wrapper{color:#cbd5e1}
  table.dataTable tbody tr{transition:background .15s}
  table.dataTable tbody tr:hover{background:#fff7f7}
  .dark table.dataTable tbody tr:hover{background:#1e293b}
  
  @media print {
    @page { margin: 1.5cm; }
    aside, header, .no-print { display: none !important; }
    .lg\:ml-64 { margin-left: 0 !important; }
    body { background: white !important; color: black !important; margin: 0 !important; padding: 0 !important; }
    .card, .overflow-hidden, .overflow-x-auto { 
      box-shadow: none !important; 
      border: none !important; 
      border-radius: 0 !important; 
      padding: 0 !important; 
      background: transparent !important; 
      overflow: visible !important; 
    }
    .min-h-screen { min-height: auto !important; }
    main { padding: 0 !important; }
    table { width: 100% !important; border-collapse: collapse !important; margin-bottom: 2rem !important; table-layout: auto !important; }
    th, td { border: 1px solid #334155 !important; padding: 8px 12px !important; text-align: center !important; vertical-align: middle !important; }
    td { color: #000 !important; }
    th { background-color: #961d5a !important; color: #fff !important; font-weight: bold !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    h1, h2, h3, p { color: #000 !important; }
  }
</style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 min-h-screen transition-colors duration-300" 
      @resize.window="sidebarOpen = window.innerWidth >= 1024">

<div x-show="sidebarOpen && window.innerWidth < 1024" @click="sidebarOpen=false"
  class="fixed inset-0 z-20 bg-black/50 lg:hidden"></div>

<!-- SIDEBAR -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
  class="fixed top-0 left-0 h-full w-64 z-30 transition-transform duration-300
         bg-gradient-to-b from-[#2d0820] via-[#961d5a] to-[#4a0e2f] flex flex-col shadow-2xl lg:translate-x-0">

  <!-- Logo -->
  <div class="p-5 border-b border-white/10">
    <a href="<?= APP_URL ?>/?page=admin/dashboard" class="flex items-center gap-3 hover:opacity-90 transition">
      <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0 p-1">
        <img src="/img/Logo_Universitas_Nusa_Putra.png" alt="Logo Nusa Putra" class="w-full h-full object-contain">
      </div>
      <div>
        <h1 class="text-white font-display font-bold leading-tight">Admin Panel</h1>
        <p class="text-white/50 text-xs">Nusa Putra University</p>
      </div>
    </a>
  </div>

  <!-- Admin User -->
  <div class="p-4 border-b border-white/10">
    <div class="flex items-center gap-3">
      <?php if (!empty($user['avatar'])): ?>
        <?php $avatarSrc = APP_URL . '/avatar.php?url=' . urlencode($user['avatar']); ?>
        <img src="<?= $avatarSrc ?>" alt="<?= e($user['nama'] ?? 'A') ?>"
          class="w-9 h-9 rounded-full object-cover ring-2 ring-white/30 flex-shrink-0"
          onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="w-9 h-9 rounded-full bg-amber-400 items-center justify-center text-amber-900 font-bold text-sm flex-shrink-0" style="display:none;">
          <?= strtoupper(substr($user['nama'] ?? 'A', 0, 1)) ?>
        </div>
      <?php else: ?>
        <div class="w-9 h-9 rounded-full bg-amber-400 flex items-center justify-center text-amber-900 font-bold text-sm flex-shrink-0">
          <?= strtoupper(substr($user['nama'] ?? 'A', 0, 1)) ?>
        </div>
      <?php endif; ?>
      <div class="flex-1 min-w-0">
        <p class="text-white text-sm font-semibold truncate"><?= e($user['nama'] ?? 'Admin') ?></p>
        <p class="text-white/50 text-xs truncate">
          <?php if (isKaprodi()): ?>
            <?= e($user['program_studi'] ?? 'Kaprodi') ?>
          <?php else: ?>
            Administrator
          <?php endif; ?>
        </p>
      </div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
    <?php
    $adminNav = [
      ['page' => 'admin/dashboard',   'label' => 'Dashboard',          'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
      ['page' => 'admin/mahasiswa',   'label' => 'Data Mahasiswa',     'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
      ['page' => 'admin/pengajuan',   'label' => 'Pengunduran Diri',   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ];
    if (isAdmin()) {
        $adminNav[] = ['page' => 'admin/users',       'label' => 'Users',              'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'];
        $adminNav[] = ['page' => 'admin/settings',    'label' => 'Pengaturan',         'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'];
    }
    foreach ($adminNav as $item):
      $isActive = $activePage === $item['page'];
    ?>
    <a href="<?= APP_URL ?>/?page=<?= $item['page'] ?>"
       class="sidebar-item <?= $isActive ? 'active' : 'inactive' ?>">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $item['icon'] ?>"/>
      </svg>
      <span><?= $item['label'] ?></span>
      <?php if ($isActive): ?><div class="ml-auto w-1.5 h-5 rounded-full bg-white/60"></div><?php endif; ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Logout -->
  <div class="p-3 border-t border-white/10">
    <a href="<?= APP_URL ?>/?page=logout"
       onclick="return confirm('Keluar dari sistem?')"
       class="sidebar-item inactive hover:bg-red-900/30">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      <span>Keluar</span>
    </a>
  </div>
</aside>

<!-- MAIN -->
<div class="lg:ml-64 min-h-screen flex flex-col">
  <!-- TOP BAR -->
  <header class="sticky top-0 z-10 bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-700 px-4 lg:px-6 py-3 flex items-center gap-4">
    <button @click="sidebarOpen=!sidebarOpen" class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 flex-1">
      <span class="text-nusa font-semibold">Admin</span>
      <?php if ($activePage !== 'admin/dashboard'): ?>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
      <span class="text-slate-800 dark:text-white font-medium"><?= e(ucwords(str_replace(['admin/', '-', '_'], ['', ' ', ' '], $activePage))) ?></span>
      <?php endif; ?>
    </nav>

    <div class="flex items-center gap-3">
      <!-- Dark mode -->
      <button @click="darkMode=!darkMode; localStorage.setItem('darkMode', darkMode)"
        class="p-2 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      </button>

      <div class="flex items-center gap-2">
        <?php if (!empty($user['avatar'])): ?>
          <?php $avatarSrc = APP_URL . '/avatar.php?url=' . urlencode($user['avatar']); ?>
          <img src="<?= $avatarSrc ?>" alt="<?= e($user['nama'] ?? 'A') ?>"
            class="w-8 h-8 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-600"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
          <div class="w-8 h-8 rounded-full bg-amber-400 items-center justify-center text-amber-900 font-bold text-xs" style="display:none;">
            <?= strtoupper(substr($user['nama'] ?? 'A', 0, 1)) ?>
          </div>
        <?php else: ?>
          <div class="w-8 h-8 rounded-full bg-amber-400 flex items-center justify-center text-amber-900 font-bold text-xs">
            <?= strtoupper(substr($user['nama'] ?? 'A', 0, 1)) ?>
          </div>
        <?php endif; ?>
        <span class="hidden md:block text-sm text-slate-700 dark:text-slate-300 font-medium"><?= e($user['nama'] ?? 'Admin') ?></span>
      </div>
    </div>
  </header>

  <main class="flex-1 p-4 lg:p-6">
