<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login Sistem Pengunduran Diri Mahasiswa Universitas Nusa Putra">
    <title>Tautkan Akun Google - Sistem Pengunduran Diri Mahasiswa | Universitas Nusa Putra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        nusa: {
                            DEFAULT: '#961d5a',
                            dark: '#6b1040',
                            light: '#b8277a',
                        },
                        slate: {
                            850: '#151e2e',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                        'blob': 'blob 7s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .dark .glass-panel {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .input-premium {
            @apply w-full px-4 py-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-800 dark:text-slate-100 transition-all duration-300;
        }
        .input-premium:focus {
            @apply outline-none border-nusa ring-4 ring-nusa/10 bg-white dark:bg-slate-800;
        }
        .btn-premium {
            @apply relative overflow-hidden w-full py-3.5 px-6 bg-gradient-to-r from-nusa to-[#b8277a] hover:from-nusa-dark hover:to-nusa text-white font-bold rounded-xl transition-all duration-300 shadow-lg shadow-nusa/30 hover:shadow-xl hover:shadow-nusa/40 hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center gap-3;
        }
        .btn-premium::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
            transform: skewX(-20deg);
            transition: all 0.7s ease;
        }
        .btn-premium:hover::after {
            left: 150%;
        }
        .gradient-text {
            background: linear-gradient(135deg, #C1121F 0%, #8B0000 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .gradient-text {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* Checkbox styling */
        .checkbox-premium {
            @apply w-5 h-5 rounded border-slate-300 text-nusa focus:ring-nusa/30 dark:border-slate-600 dark:bg-slate-700 transition-colors;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 transition-colors duration-300 min-h-screen flex items-center justify-center p-4 sm:p-8 relative overflow-x-hidden" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">

    <!-- Background Decoration -->
    <div class="absolute top-0 -left-4 w-96 h-96 bg-nusa/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
    <div class="absolute top-0 -right-4 w-96 h-96 bg-blue-400/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-8 left-20 w-96 h-96 bg-pink-500/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-4000"></div>

    <!-- Back Button -->
    <a href="<?= APP_URL ?>/" class="absolute top-6 left-6 z-50 px-4 py-2.5 rounded-full bg-white dark:bg-slate-800 shadow-lg border border-slate-100 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:scale-105 hover:text-nusa dark:hover:text-nusa transition-all flex items-center gap-2 font-medium text-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>

    <!-- Theme Toggle -->
    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="absolute top-6 right-6 z-50 p-3 rounded-full bg-white dark:bg-slate-800 shadow-lg text-slate-600 dark:text-slate-300 hover:scale-110 transition-transform">
        <svg x-show="!darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        <svg x-show="darkMode" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
    </button>

    <div class="w-full max-w-6xl glass-panel rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.3)] overflow-hidden flex flex-col md:flex-row relative z-10 border border-white/50 dark:border-slate-700/50">
        
        <!-- LEFT: Beautiful Hero Section -->
        <div class="relative w-full md:w-5/12 lg:w-1/2 p-10 md:p-14 flex flex-col justify-center overflow-hidden">
            <!-- Background Image -->
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://kfmap.asia/storage/thumbs/storage/photos/ID.SUK.UNIV.NPU/ID.SUK.UNIV.NPU_2.jpg');"></div>
            <!-- Maroon Overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-nusa-dark/75 via-nusa/70 to-nusa-light/60 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-nusa-dark/60 via-nusa/50 to-transparent"></div>
            
            <!-- Decorative Elements -->
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px; z-index: 1;"></div>
            
            <div class="absolute top-20 -right-20 w-64 h-64 border-[30px] border-white/10 rounded-full animate-float"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 border-[40px] border-white/10 rounded-full animate-float-delayed"></div>
            
            <div class="relative z-10 text-white">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md mb-8 border border-white/20 shadow-xl p-2">
                    <img src="/img/Logo_Universitas_Nusa_Putra.png" alt="Logo Nusa Putra" class="w-full h-full object-contain">
                </div>
                
                <h1 class="font-display font-bold text-4xl lg:text-5xl mb-4 leading-tight">Universitas<br/>Nusa Putra</h1>
                <p class="text-white/80 text-lg mb-8 font-light tracking-wide">Sistem Informasi Pengunduran Diri Mahasiswa.</p>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-4 text-white/90">
                        <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="font-medium text-sm">Proses Cepat & Terintegrasi</span>
                    </div>
                    <div class="flex items-center gap-4 text-white/90">
                        <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <span class="font-medium text-sm">Aman & Terenkripsi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Login Form -->
        <div class="w-full md:w-7/12 lg:w-1/2 p-10 md:p-12 flex flex-col justify-center bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm overflow-y-auto max-h-[85vh] md:max-h-none scroll-smooth" x-data="{ showPass: false, loading: false }" style="scrollbar-width: thin; scrollbar-color: #961d5a transparent;">
            
            <div class="max-w-md w-full mx-auto py-4">
                <div class="mb-10 text-center md:text-left">
                    <h2 class="font-display font-bold text-3xl mb-2 text-slate-800 dark:text-white">Tautkan Akun 🔗</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Akun Google <strong><?= e($data['google']['email'] ?? '') ?></strong> belum tertaut dengan data mahasiswa mana pun.</p>
                    
                    <div class="flex items-center gap-3 p-3 bg-nusa/10 dark:bg-nusa/20 rounded-xl border border-nusa/20 inline-block">
                        <img src="<?= e($data['google']['avatar'] ?? '') ?>" class="w-10 h-10 rounded-full" alt="Google Avatar" referrerpolicy="no-referrer">
                        <div class="text-left">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200"><?= e($data['google']['nama'] ?? '') ?></p>
                            <p class="text-xs text-slate-500"><?= e($data['google']['email'] ?? '') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Flash Messages -->
                <?php foreach (getFlash('error') as $msg): ?>
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-600 dark:text-red-400 text-sm rounded-xl px-4 py-3 mb-6 flex items-start gap-3 shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <div>
                        <span class="font-semibold block mb-0.5">Login Gagal</span>
                        <?= e($msg) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php foreach (getFlash('success') as $msg): ?>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50 text-emerald-600 dark:text-emerald-400 text-sm rounded-xl px-4 py-3 mb-6 flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <?= e($msg) ?>
                </div>
                <?php endforeach; ?>

                <form method="POST" action="<?= APP_URL ?>/?page=auth/link-google/process" @submit="loading=true" class="space-y-6">
                    <?= csrfField() ?>

                    <!-- NIM Field -->
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 text-sm font-bold mb-2 ml-1" for="user-nim">NIM Mahasiswa</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-nusa transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <input type="text" name="nim" id="user-nim" required
                                placeholder="Masukkan NIM Anda"
                                class="input-premium pl-12 shadow-sm bg-white dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-800 transition-all border-slate-200 dark:border-slate-700 focus:border-nusa focus:ring-nusa/30 hover:border-slate-300 dark:hover:border-slate-600 rounded-xl text-base font-medium">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                            <label class="block text-slate-700 dark:text-slate-300 text-sm font-bold" for="user-pass">Password</label>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-nusa transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" name="password" id="user-pass" required
                                placeholder="Masukkan Password Anda"
                                class="input-premium pl-12 pr-12 shadow-sm bg-white dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-800 transition-all border-slate-200 dark:border-slate-700 focus:border-nusa focus:ring-nusa/30 hover:border-slate-300 dark:hover:border-slate-600 rounded-xl text-base font-medium tracking-wide">
                            <button type="button" @click="showPass=!showPass"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-nusa transition-colors focus:outline-none">
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit & Skip Buttons -->
                    <div class="pt-2 flex flex-col gap-3">
                        <button type="submit" class="btn-premium w-full" :disabled="loading">
                            <span x-show="!loading" class="flex items-center justify-center gap-2">
                                Tautkan Akun & Masuk
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            </span>
                            <span x-show="loading" class="flex items-center justify-center gap-2" x-cloak>
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Sedang Memproses...
                            </span>
                        </button>
                        
                        <button type="submit" name="skip" value="1" formnovalidate class="w-full flex items-center justify-center gap-2 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition-colors bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl" :disabled="loading">
                            Lewati untuk saat ini
                        </button>
                    </div>
                </form>

                <div class="mt-10 text-center">
                    <p class="text-xs text-slate-500 dark:text-slate-500">
                        &copy; <?= date('Y') ?> Universitas Nusa Putra.<br/>Sistem Informasi Pengunduran Diri Mahasiswa.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
