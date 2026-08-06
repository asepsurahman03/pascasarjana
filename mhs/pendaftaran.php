<?php
$pageTitle = 'Layanan Pendaftaran Akademik';
require_once 'header.php';

// Library metadata untuk semua kemungkinan pendaftaran
$layananMeta = [
    'proposal' => [
        'desc' => 'Pendaftaran untuk ujian/seminar proposal.',
        'icon' => '📄',
        'color' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
        'border' => 'border-emerald-200 dark:border-emerald-800',
        'status' => 'Buka',
        'status_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'
    ],
    'tesis' => [
        'desc' => 'Pendaftaran ujian akhir (pendadaran).',
        'icon' => '🎓',
        'color' => 'bg-nusa/10 text-nusa dark:bg-pink-900/30 dark:text-pink-400',
        'border' => 'border-nusa/30 dark:border-pink-800',
        'status' => 'Buka',
        'status_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'
    ],
    'kualifikasi' => [
        'desc' => 'Pendaftaran ujian kualifikasi kompetensi mahasiswa.',
        'icon' => '📝',
        'color' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        'border' => 'border-amber-200 dark:border-amber-800',
        'status' => 'Tutup Sementara',
        'status_color' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
    ],
    'iamp' => [
        'desc' => 'International Academic Mobility Program (IAMP).',
        'icon' => '🌍',
        'color' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        'border' => 'border-blue-200 dark:border-blue-800',
        'status' => 'Buka',
        'status_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'
    ],
    'capstone' => [
        'desc' => 'Pendaftaran sidang akhir Capstone Project.',
        'icon' => '🚀',
        'color' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
        'border' => 'border-indigo-200 dark:border-indigo-800',
        'status' => 'Buka',
        'status_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'
    ],
    'kolokium' => [
        'desc' => 'Pendaftaran presentasi progres/kolokium rutin.',
        'icon' => '🗣️',
        'color' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
        'border' => 'border-purple-200 dark:border-purple-800',
        'status' => 'Memenuhi Syarat',
        'status_color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400'
    ]
];

// Bangun daftar layanan khusus untuk mahasiswa ini berdasarkan menuPendaftaran dari header.php
$layanan = [];
foreach ($menuPendaftaran as $menuItem) {
    $key = $menuItem['key'];
    if (isset($layananMeta[$key])) {
        $meta = $layananMeta[$key];
        $layanan[] = [
            'id' => $key,
            'title' => $menuItem['label'], // Ambil nama dinamis (misal Tesis vs Disertasi)
            'desc' => $meta['desc'],
            'icon' => $meta['icon'],
            'color' => $meta['color'],
            'border' => $meta['border'],
            'link' => 'daftar_sidang?jenis=' . $key,
            'status' => $meta['status'],
            'status_color' => $meta['status_color']
        ];
    }
}
?>

<div class="max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="relative rounded-2xl overflow-hidden mb-8 p-8 md:p-10 text-white bg-gradient-to-br from-[#8c0c4c] to-[#6b1040] shadow-lg">
        <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10" style="background:white;transform:translate(30%,-30%)"></div>
        <div class="absolute bottom-0 left-20 w-40 h-40 rounded-full opacity-10" style="background:white;transform:translate(-20%,40%)"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="font-display font-bold text-3xl md:text-4xl mb-2">Layanan Pendaftaran</h1>
                <p class="text-white/80 max-w-xl text-sm md:text-base leading-relaxed">
                    Pilih jenis pendaftaran ujian atau kegiatan akademik yang ingin Anda ajukan. Pastikan Anda telah memenuhi persyaratan (seperti jumlah sesi bimbingan logbook) sebelum mendaftar.
                </p>
            </div>
            <div class="hidden md:flex flex-shrink-0 items-center justify-center w-24 h-24 bg-white/10 rounded-2xl border border-white/20 backdrop-blur-sm shadow-xl">
                <span class="text-5xl">📋</span>
            </div>
        </div>
    </div>

    <!-- Pendaftaran Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        <?php foreach($layanan as $l): ?>
        <a href="<?= htmlspecialchars($l['link']) ?>" class="group block bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:border-nusa/40 transition-all duration-300 relative overflow-hidden flex flex-col h-full">
            <!-- Decorative Accent -->
            <div class="absolute top-0 left-0 w-full h-1 <?= str_replace('text-', 'bg-', explode(' ', $l['color'])[1]) ?> opacity-80 group-hover:h-1.5 transition-all"></div>
            
            <div class="p-6 md:p-8 flex-1 flex flex-col">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-14 h-14 rounded-2xl <?= $l['color'] ?> <?= $l['border'] ?> border flex items-center justify-center text-3xl shadow-inner group-hover:scale-110 transition-transform duration-300">
                        <?= $l['icon'] ?>
                    </div>
                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg <?= $l['status_color'] ?>">
                        <?= $l['status'] ?>
                    </span>
                </div>
                
                <h3 class="font-display font-bold text-lg text-slate-800 dark:text-white mb-2 group-hover:text-nusa dark:group-hover:text-pink-400 transition-colors">
                    <?= htmlspecialchars($l['title']) ?>
                </h3>
                
                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed mb-6 flex-1">
                    <?= htmlspecialchars($l['desc']) ?>
                </p>
                
                <div class="mt-auto flex items-center justify-between text-sm font-semibold">
                    <span class="text-nusa dark:text-pink-400 group-hover:underline">Mulai Daftar</span>
                    <span class="text-slate-300 dark:text-slate-600 group-hover:translate-x-1 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>


    <!-- Bantuan / Info -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-2xl p-6 flex items-start gap-4 shadow-sm">
        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 text-lg mt-0.5">
            💡
        </div>
        <div>
            <h4 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Butuh Bantuan Pendaftaran?</h4>
            <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 leading-relaxed max-w-3xl">
                Pastikan Anda telah memeriksa persetujuan pembimbing (Logbook) dan telah menyelesaikan persyaratan minimum administratif. Jika Anda menemui kendala teknis saat mendaftar, silakan hubungi <a href="#" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Admin Program Studi</a> Anda atau lihat <a href="#" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Panduan Akademik</a>.
            </p>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
