<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if(isLoggedIn()){header('Location:'. BASE_URL . '/index');exit;}

$error='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $un=trim($_POST['username']??'');$pw=$_POST['password']??'';
    if(empty($un)||empty($pw)){$error='Username dan password wajib diisi.';}
    else{
        $user=dbQueryOne("
            SELECT u.* 
            FROM users u 
            LEFT JOIN mahasiswa m ON u.username = m.nim AND u.role = 'mahasiswa'
            WHERE u.username=? OR u.email=? OR m.email_pribadi=? 
            LIMIT 1",
            [$un,$un,$un]
        );
        if($user&&password_verify($pw,$user['password_hash'])){
            $_SESSION['user_id']=$user['id'];$_SESSION['username']=$user['username'];$_SESSION['nama']=$user['nama'];
            $_SESSION['role']=$user['role'];$_SESSION['prodi_id']=$user['prodi_id'];$_SESSION['foto']=$user['foto'];
            dbExecute("UPDATE users SET last_login=NOW() WHERE id=?",[$user['id']]);
            logActivity('Login','auth','Login berhasil');
            // Redirect berdasarkan role dari database (satu pintu)
            if ($user['role'] === 'mahasiswa') {
                header('Location:' . BASE_URL . '/mhs/index');
            } elseif ($user['role'] === 'dosen') {
                header('Location:' . BASE_URL . '/dosen/index');
            } else {
                header('Location:' . BASE_URL . '/index');
            }
            exit;
        }else{$error='Username atau password salah.';}
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIAKAD Pascasarjana | Universitas Nusa Putra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        nusa: { DEFAULT: '#961d5a', dark: '#6b1040', light: '#b8277a' },
                        slate: { 850: '#151e2e', 900: '#0f172a' }
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style type="text/tailwindcss">
        .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
        .dark .glass-panel { background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.05); }
        .input-premium { @apply w-full py-3.5 rounded-xl border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800/50 text-slate-800 dark:text-slate-100 transition-all duration-300; }
        .input-premium:focus { @apply outline-none border-nusa ring-4 ring-nusa/20 bg-white dark:bg-slate-800; }
        .btn-premium { @apply relative overflow-hidden w-full py-3.5 px-6 bg-gradient-to-r from-nusa to-[#b8277a] hover:from-nusa-dark hover:to-nusa text-white font-bold rounded-xl transition-all duration-300 shadow-lg shadow-nusa/30 hover:shadow-xl hover:shadow-nusa/40 hover:-translate-y-0.5 active:translate-y-0 flex justify-center items-center gap-3; }
        .checkbox-premium { @apply w-5 h-5 rounded border-slate-300 text-nusa focus:ring-nusa/30 dark:border-slate-600 dark:bg-slate-700 transition-colors; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 transition-colors duration-300 min-h-screen flex items-center justify-center p-4 sm:p-8 relative overflow-x-hidden" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
    <div class="absolute top-0 -left-4 w-96 h-96 bg-nusa/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
    <div class="absolute top-0 -right-4 w-96 h-96 bg-blue-400/20 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
    
    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="absolute top-6 right-6 z-50 p-3 rounded-full bg-white dark:bg-slate-800 shadow-lg text-slate-600 dark:text-slate-300 hover:scale-110 transition-transform">
        <svg x-show="!darkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        <svg x-show="darkMode" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
    </button>

    <div class="w-full max-w-5xl glass-panel rounded-[2rem] shadow-2xl overflow-hidden flex flex-col md:flex-row relative z-10 border border-white/50 dark:border-slate-700/50">
        <div class="relative w-full md:w-5/12 lg:w-1/2 p-10 flex flex-col justify-center overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://kfmap.asia/storage/thumbs/storage/photos/ID.SUK.UNIV.NPU/ID.SUK.UNIV.NPU_2.jpg');"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-nusa-dark/60 via-nusa/50 to-nusa-light/40 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-nusa-dark/50 via-nusa/30 to-transparent"></div>
            
            <div class="relative z-10 text-white">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/10 backdrop-blur-md mb-8 border border-white/20 shadow-xl p-2">
                    <img src="assets/images/LOGO-UNIVERSITAS-NUSA-PUTRA.png" alt="Logo NPU" class="w-full h-full object-contain" onerror="this.style.display='none'">
                </div>
                
                <h1 class="font-display font-bold text-4xl mb-4 leading-tight">SIAKAD<br/>Pascasarjana</h1>
                <p class="text-white/80 text-lg mb-8 font-light tracking-wide">Universitas Nusa Putra</p>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-4 text-white/90">
                        <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="font-medium text-sm">Terintegrasi & Praktis</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-7/12 lg:w-1/2 p-10 flex flex-col justify-center bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm overflow-y-auto max-h-[85vh] md:max-h-none" x-data="{ showPass: false, loading: false }">
            <div class="max-w-md w-full mx-auto">
                <div class="mb-8 text-center md:text-left">
                    <h2 class="font-display font-bold text-3xl mb-2 text-slate-800 dark:text-white">Selamat Datang! 👋</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">Masuk sebagai Admin, Kaprodi, Dosen, atau Mahasiswa.</p>
                </div>

                <?php if($error): ?>
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 text-red-600 dark:text-red-400 text-sm rounded-xl px-4 py-3 mb-6 flex items-start gap-3 shadow-sm">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="POST" @submit="loading=true" class="space-y-5">
                    <div>
                        <label class="block text-slate-700 dark:text-slate-300 text-sm font-bold mb-2 ml-1">Username / Email / NIM</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-nusa transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </div>
                            <input type="text" name="username" required autofocus placeholder="Masukkan ID Anda" class="input-premium pl-11 pr-4 shadow-sm text-base font-medium">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2 ml-1">
                            <label class="block text-slate-700 dark:text-slate-300 text-sm font-bold" for="user-pass">Password</label>
                            <a href="<?= BASE_URL ?>/lupa_password" class="text-xs text-nusa dark:text-nusa-light hover:underline font-semibold">Lupa password?</a>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-nusa transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" name="password" id="user-pass" required placeholder="Masukkan Password" class="input-premium pl-11 pr-11 shadow-sm text-base font-medium tracking-wide">
                            <button type="button" @click="showPass=!showPass" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-nusa transition-colors focus:outline-none">
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>


                    <div class="pt-2">
                        <button type="submit" class="btn-premium w-full" :disabled="loading">
                            <span x-show="!loading" class="flex items-center justify-center gap-2">Masuk <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                            <span x-show="loading" class="flex items-center justify-center gap-2" x-cloak><svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Memproses...</span>
                        </button>
                    </div>

                    <div class="relative flex items-center py-2">
                        <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                        <span class="flex-shrink-0 mx-4 text-slate-400 dark:text-slate-500 text-xs uppercase font-medium tracking-wider">Atau</span>
                        <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    </div>
                    
                    <a href="<?= BASE_URL ?>/google_login_manual" class="w-full flex items-center justify-center gap-3 px-6 py-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors shadow-sm focus:ring-4 focus:ring-slate-100 dark:focus:ring-slate-800">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Lanjutkan dengan Google
                    </a>
                </form>

                <div class="mt-8 p-4 rounded-xl text-xs bg-slate-50 dark:bg-slate-700/40 border border-slate-200 dark:border-slate-700">
                    <p class="font-semibold text-slate-600 dark:text-slate-300 mb-2">Demo Akun (Tanpa Google):</p>
                    <p class="text-slate-500 dark:text-slate-400 mb-1">Kaprodi/Admin: <code class="text-nusa font-bold">admin</code> / <code class="text-nusa font-bold">password</code></p>
                    <p class="text-slate-500 dark:text-slate-400 mb-1">Dosen: <code class="text-blue-600 dark:text-blue-400 font-bold">0412078801</code> / <code class="text-blue-600 dark:text-blue-400 font-bold">password</code></p>
                    <p class="text-slate-500 dark:text-slate-400">Mahasiswa: <code class="text-emerald-600 dark:text-emerald-400 font-bold">20240140011</code> / <code class="text-emerald-600 dark:text-emerald-400 font-bold">nusaputraku</code></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
