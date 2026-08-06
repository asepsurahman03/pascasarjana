<?php
/**
 * Public Layout Top (No Session Required)
 */
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($data['title'] ?? APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {'50': '#fff1f2', '100': '#ffe4e6', '200': '#fecdd3', '300': '#fda4af', '400': '#fb7185', '500': '#f43f5e', '600': '#e11d48', '700': '#be123c', '800': '#9f1239', '900': '#881337', '950': '#4c0519'},
                        nusa: { DEFAULT: '#961d5a', dark: '#6b1040', light: '#b8277a', faint: 'rgba(150,29,90,0.08)' },
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css">
    <style>
        .bg-hero {
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            position: relative;
        }
        .bg-hero::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(ellipse at top right, rgba(150,29,90,0.2) 0%, transparent 60%),
                        radial-gradient(ellipse at bottom left, rgba(150,29,90,0.15) 0%, transparent 50%);
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

<!-- Navbar -->
<nav class="bg-nusa shadow-md sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="<?= APP_URL ?>/" class="flex items-center gap-3 hover:opacity-90 transition">
            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center overflow-hidden p-1 shadow-sm">
                <img src="/img/Logo_Universitas_Nusa_Putra.png" alt="Logo Nusa Putra" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="font-display font-bold text-white leading-tight">Universitas Nusa Putra</h1>
                <p class="text-[10px] font-semibold text-white/80 uppercase tracking-widest">Sistem Pengunduran Diri</p>
            </div>
        </a>
        <div>
            <?php if (isLoggedIn()): ?>
                <?php $currentUserData = currentUser(); ?>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 focus:outline-none bg-white/10 hover:bg-white/20 transition pl-2 pr-3 py-1.5 rounded-full border border-white/10">
                        <?php if(!empty($currentUserData['avatar'])): ?>
                            <img src="<?= e($currentUserData['avatar']) ?>" alt="Avatar" class="w-7 h-7 rounded-full object-cover shadow-sm">
                        <?php else: ?>
                            <div class="w-7 h-7 rounded-full bg-nusa-light text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                <?= substr(e($currentUserData['nama']), 0, 1) ?>
                            </div>
                        <?php endif; ?>
                        <span class="text-sm font-medium text-white hidden sm:block max-w-[120px] truncate"><?= e($currentUserData['nama']) ?></span>
                        <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden z-50">
                        <a href="<?= APP_URL ?>/?page=<?= isAdmin() ? 'admin/dashboard' : 'mahasiswa/dashboard' ?>" class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-nusa transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Dashboard
                        </a>
                        <div class="h-px bg-slate-100"></div>
                        <a href="<?= APP_URL ?>/?page=logout" class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= APP_URL ?>/?page=login" class="flex items-center gap-2 px-5 py-2 text-sm font-semibold text-nusa bg-white hover:bg-slate-100 rounded-xl transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Main Content wrapper -->
<main class="flex-1 w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
