<?php
require_once __DIR__ . '/../../includes/functions.php';
requireMahasiswaLogin();
$mhs = getCurrentMahasiswa();
$activePage = basename($_SERVER['PHP_SELF'], '.php');
$menuItems = [
    ['id'=>'index', 'label'=>'Beranda', 'href'=>BASE_URL.'/mhs/index.php', 'icon'=>'dashboard'],
];
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?=e($pageTitle??'Portal Mahasiswa')?> — Pascasarjana NPU</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={darkMode:'class',theme:{extend:{colors:{bg:'#0f1117',sidebar:'#161b27',card:'#1e2736',border:'#2d3748'},fontFamily:{sans:['Inter','sans-serif']}}}}</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--sw:240px}
body{background:#0f1117;color:#e2e8f0;font-family:'Inter',sans-serif}
.sidebar{width:var(--sw);transition:transform .3s}
.main-content{margin-left:var(--sw);transition:margin .3s}
@media(max-width:768px){.sidebar{transform:translateX(-100%);position:fixed;z-index:50}.sidebar.open{transform:translateX(0)}.main-content{margin-left:0}}
.nav-item{transition:all .2s;border-radius:8px}
.nav-item:hover{background:rgba(96,165,250,.1);color:#60a5fa}
.nav-item.active{background:rgba(96,165,250,.15);color:#60a5fa}
.card{background:#1e2736;border:1px solid #2d3748;border-radius:12px}
</style>
</head>
<body class="min-h-screen">
<aside class="sidebar fixed top-0 left-0 h-full flex flex-col" style="background:#161b27;border-right:1px solid #2d3748" id="sidebar">
  <div class="p-5 border-b" style="border-color:#2d3748">
    <div class="flex items-center gap-3">
      <img src="<?=BASE_URL?>/assets/images/LOGO-UNIVERSITAS-NUSA-PUTRA.png" alt="Logo Nusa Putra" class="w-10 h-10 object-contain bg-white rounded-lg p-1">
      <div><div class="font-bold text-sm text-white leading-tight">Portal Mahasiswa</div><div class="text-xs" style="color:#64748b">Pascasarjana NPU</div></div>
    </div>
  </div>
  <nav class="flex-1 overflow-y-auto p-3 space-y-0.5">
    <div class="text-xs font-semibold px-3 mb-2 mt-1" style="color:#4b5563;letter-spacing:.08em">MENU</div>
    <?php foreach($menuItems as $item):
      $isActive = ($item['id']===$activePage);
    ?>
    <a href="<?=e($item['href'])?>" class="nav-item <?=$isActive?'active':''?> flex items-center gap-3 px-3 py-2 text-sm" style="color:<?=$isActive?'#60a5fa':'#94a3b8'?>">
      <?=svgIcon($item['icon'])?>
      <span class="flex-1"><?=e($item['label'])?></span>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="p-4 border-t" style="border-color:#2d3748">
    <a href="<?=BASE_URL?>/logout" class="flex items-center gap-2 text-red-400 hover:text-red-300 text-sm font-medium transition px-2 py-1.5 rounded-lg hover:bg-red-400/10">
      <?=svgIcon('logout')?> Keluar
    </a>
  </div>
</aside>

<main class="main-content min-h-screen flex flex-col relative">
  <header class="h-16 flex items-center justify-between px-6 sticky top-0 z-40 bg-bg/80 backdrop-blur-md border-b" style="border-color:#2d3748">
    <div class="flex items-center gap-4">
      <button onclick="document.getElementById('sidebar').classList.toggle('open')" class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition"><?=svgIcon('menu')?></button>
      <h2 class="text-lg font-semibold text-white tracking-tight hidden sm:block"><?=e($pageTitle??'Portal')?></h2>
    </div>
    <div class="flex items-center gap-4">
      <div class="flex items-center gap-3 bg-card px-3 py-1.5 rounded-full border" style="border-color:#2d3748">
        <div class="w-7 h-7 rounded-full bg-blue-900 text-blue-300 flex items-center justify-center font-bold text-xs uppercase"><?=substr($mhs['nama'],0,1)?></div>
        <div class="hidden sm:block"><div class="text-sm font-semibold leading-none"><?=e($mhs['nama'])?></div><div class="text-xs text-gray-500 mt-0.5"><?=e($mhs['nim'])?></div></div>
      </div>
    </div>
  </header>
  <div class="p-6 flex-1 max-w-7xl w-full mx-auto">
