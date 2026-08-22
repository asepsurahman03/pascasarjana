<?php
$pageTitle = 'Layanan Pendaftaran Akademik';
require_once 'header.php';

// Library metadata lengkap pendaftaran, syarat, dan cara mendapatkan dokumen
$layananMeta = [
    'proposal' => [
        'desc' => 'Pendaftaran untuk ujian/seminar proposal tesis.',
        'detail_desc' => 'Seminar Proposal Tesis merupakan tahapan awal evaluasi kelayakan topik, metodologi, dan kajian pustaka penelitian tesis mahasiswa di hadapan dewan penguji.',
        'icon' => '📄',
        'color' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
        'border' => 'border-emerald-200 dark:border-emerald-800',
        'status' => 'Buka',
        'status_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
        'dokumen' => [
            [
                'nama' => 'Draft Proposal Tesis (Bab 1 - 3)',
                'tempat' => 'Mahasiswa / Pembimbing',
                'tempat_color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                'cara' => 'Naskah proposal tesis yang telah selesai disusun dan disetujui untuk diseminarkan oleh Ketua Pembimbing.',
                'format' => 'PDF / DOCX'
            ],
            [
                'nama' => 'Lembar Persetujuan Pembimbing',
                'tempat' => 'Ketua & Anggota Pembimbing',
                'tempat_color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                'cara' => 'Download format lembar persetujuan dari Google Drive template, lalu mintakan tanda tangan (fisik/digital) dari Ketua dan Anggota Pembimbing.',
                'link' => 'https://drive.google.com/drive/u/0/folders/15ZqsneMSvLTYj4my1rPTcgtCdwAxaE9o',
                'link_label' => '📥 Klik disini untuk Unduh Template Form Persetujuan (Google Drive) ↗',
                'format' => 'PDF'
            ],
            [
                'nama' => 'Transkrip Nilai / KHS Sementara',
                'tempat' => 'Bagian Akademik / SIAKAD',
                'tempat_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                'cara' => 'Unduh Kartu Hasil Studi (KHS) melalui portal SIAKAD Nusa Putra atau minta cetak lembar KHS resmi ke Bagian Administrasi Akademik Pascasarjana.',
                'link' => 'https://siakad.nusaputra.ac.id',
                'link_label' => '🌐 Klik disini untuk Buka Portal SIAKAD (siakad.nusaputra.ac.id) ↗',
                'format' => 'PDF'
            ],
            [
                'nama' => 'Bukti Bebas Biaya Administrasi',
                'tempat' => 'Student Administration Service Unit (SASU)',
                'tempat_color' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                'cara' => 'Silahkan datang langsung ke loket Student Administration Service Unit (SASU) untuk menyelesaikan administrasi perkuliahan/seminar dan memperoleh Surat Bebas Administrasi.',
                'format' => 'PDF / JPG'
            ],
            [
                'nama' => 'Slide Materi Presentasi (PPT)',
                'tempat' => 'Mahasiswa',
                'tempat_color' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                'cara' => 'Slide presentasi ringkas (maks 15-20 slide) yang memuat latar belakang, rumusan masalah, metodologi, dan rencana penelitian.',
                'format' => 'PPTX / PDF'
            ]
        ],
        'alur' => [
            '1. Mahasiswa melengkapi berkas & mendaftar online melalui formulir.',
            '2. Admin Program Studi melakukan verifikasi dokumen kelayakan.',
            '3. Dosen Penguji melakukan voting jadwal sidang yang cocok.',
            '4. Penerbitan Surat Undangan & Pelaksanaan Seminar Proposal Tesis.',
            '5. Input Berita Acara & Revisi Pasca Seminar.'
        ]
    ],
    'tesis' => [
        'desc' => 'Pendaftaran ujian akhir (pendadaran) tesis program magister.',
        'detail_desc' => 'Sidang Tesis merupakan ujian komprehensif penentu kelulusan program magister. Mahasiswa mempresentasikan hasil akhir penelitian dan karya luaran publikasi ilmiah.',
        'icon' => '🎓',
        'color' => 'bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-pink-900/30 dark:text-pink-400',
        'border' => 'border-[#8c0c4c]/30 dark:border-pink-800',
        'status' => 'Buka',
        'status_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
        'dokumen' => [
            [
                'nama' => 'Upload Persetujuan Pembimbing',
                'tempat' => 'Ketua & Anggota Pembimbing',
                'tempat_color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                'cara' => 'Unduh form persetujuan sidang tesis, lalu mintakan tanda tangan / persetujuan resmi dari Ketua Pembimbing dan Anggota Pembimbing setelah naskah tesis selesai direview.',
                'link' => 'https://drive.google.com/drive/u/0/folders/1ZbSRYjiSc4vaPMo-oRRmGwgTe8UUCa5D',
                'link_label' => '📥 Klik disini untuk Unduh Template Form Persetujuan (Google Drive) ↗',
                'format' => 'PDF'
            ],
            [
                'nama' => 'Luaran Artikel Jurnal (Scopus / SINTA)',
                'tempat' => 'Publisher / Jurnal Ilmiah',
                'tempat_color' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                'cara' => 'Unggah manuskrip artikel ilmiah yang telah publish / accepted (LoA) / under review di jurnal terindeks Scopus atau SINTA 1-3. Sertakan juga bukti bayar jurnal.',
                'format' => 'PDF / DOCX'
            ],
            [
                'nama' => 'Upload KHS Sementara Semester 1 - 3',
                'tempat' => 'Bagian Akademik Pascasarjana',
                'tempat_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                'cara' => 'Unduh rekap KHS semester 1 s/d 3 melalui portal SIAKAD Nusa Putra atau ajukan cetak transkrip sementara ke staf Administrasi Akademik Pascasarjana.',
                'link' => 'https://siakad.nusaputra.ac.id',
                'link_label' => '🌐 Klik disini untuk Buka Portal SIAKAD (siakad.nusaputra.ac.id) ↗',
                'format' => 'PDF'
            ],
            [
                'nama' => 'Surat Bebas Pinjam Perpustakaan',
                'tempat' => 'Perpustakaan Universitas Nusa Putra',
                'tempat_color' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                'cara' => 'Datang ke loket layanan Perpustakaan Pusat Universitas Nusa Putra untuk memastikan tidak ada pinjaman buku yang tertunggak dan meminta Surat Bebas Pinjam.',
                'format' => 'PDF'
            ],
            [
                'nama' => 'Bukti Penyerahan Buku Sumbangan (3 Buah)',
                'tempat' => 'Perpustakaan Universitas Nusa Putra',
                'tempat_color' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                'cara' => 'Serahkan 3 buah buku referensi ke Bagian Perpustakaan Universitas Nusa Putra, lalu staf perpustakaan akan memberikan Surat Bukti Penyerahan Buku Sumbangan.',
                'format' => 'PDF'
            ],
            [
                'nama' => 'Bukti Bebas Administrasi & Keuangan',
                'tempat' => 'Student Administration Service Unit (SASU)',
                'tempat_color' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                'cara' => 'Silahkan datang langsung ke loket Student Administration Service Unit (SASU) untuk menyelesaikan seluruh administrasi perkuliahan/sidang dan memperoleh Surat Bebas Administrasi.',
                'format' => 'PDF'
            ],
            [
                'nama' => 'Pas Foto Resmi Ukuran 4x6 Background Merah',
                'tempat' => 'Studio Foto / Mandiri',
                'tempat_color' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                'cara' => 'Foto formal ukuran 4x6 latar belakang merah. Pria: memakai jas hitam, kemeja putih, dan dasi. Wanita: memakai blazer hitam dan kemeja putih rapi.',
                'format' => 'JPG / PNG'
            ],
            [
                'nama' => 'Draft Naskah Tesis Lengkap & Code Program',
                'tempat' => 'Mahasiswa',
                'tempat_color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                'cara' => 'Upload naskah tesis final lengkap (Bab 1 s/d Penutup) serta file source code program / sistem yang dikembangkan yang sudah di-ZIP.',
                'format' => 'PDF + ZIP'
            ],
            [
                'nama' => 'Slide Presentasi Sidang',
                'tempat' => 'Mahasiswa',
                'tempat_color' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
                'cara' => 'Siapkan file paparan materi sidang tesis (maks 20 slide) yang memuat novelty, metodologi, hasil pengujian, dan kesimpulan.',
                'format' => 'PPTX / PDF'
            ],
            [
                'nama' => 'Hasil Cek Plagiarisme (Turnitin)',
                'tempat' => 'Perpustakaan Universitas Nusa Putra',
                'tempat_color' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                'cara' => 'Unggah lembar hasil cek plagiarisme / similarity index naskah tesis (Turnitin) resmi dari Perpustakaan Nusa Putra (toleransi similarity maks 20-25%).',
                'format' => 'PDF'
            ]
        ],
        'alur' => [
            '1. Pendaftaran online & unggah seluruh dokumen persyaratan di portal ini.',
            '2. Verifikasi berkas & persyaratan kelulusan bimbingan oleh Admin Prodi.',
            '3. Polling jadwal & kesediaan waktu oleh para Dosen Penguji.',
            '4. Pelaksanaan Sidang Tesis (Presentasi & Tanya Jawab Penguji).',
            '5. Pengumuman Hasil Sidang, Revisi Naskah, & Pengesahan Yudisium.'
        ]
    ],
    'capstone' => [
        'desc' => 'Pendaftaran sidang akhir Capstone Project.',
        'detail_desc' => 'Capstone Project merupakan proyek terapan integratif yang mengimplementasikan keilmuan pascasarjana untuk memecahkan problem industri atau masyarakat.',
        'icon' => '🚀',
        'color' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
        'border' => 'border-indigo-200 dark:border-indigo-800',
        'status' => 'Buka',
        'status_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
        'dokumen' => [
            [
                'nama' => 'Laporan Akhir Capstone Project',
                'tempat' => 'Mahasiswa / Tim Proyek',
                'tempat_color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                'cara' => 'Laporan komprehensif perancangan, implementasi, dan pengujian sistem / produk Capstone.',
                'format' => 'PDF'
            ],
            [
                'nama' => 'Lembar Pengesahan Pembimbing & Mentor',
                'tempat' => 'Dosen Pembimbing / Mentor Mitra',
                'tempat_color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                'cara' => 'Ditandatangani oleh Dosen Pembimbing Akademik dan Mentor Industri / Mitra Proyek.',
                'format' => 'PDF'
            ],
            [
                'nama' => 'Dokumentasi Video Demo & Source Code',
                'tempat' => 'Mahasiswa',
                'tempat_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                'cara' => 'Tautan video demo produk serta arsip repositori source code (.zip / link GitHub).',
                'format' => 'ZIP / URL'
            ]
        ],
        'alur' => [
            '1. Upload Laporan & Pengesahan Capstone Project.',
            '2. Verifikasi kesiapan demo produk oleh Program Studi.',
            '3. Penjadwalan Sidang Presentasi & Demo Produk.',
            '4. Evaluasi oleh Dewan Penguji dan Pengesahan Akhir.'
        ]
    ],
    'iamp' => [
        'desc' => 'International Academic Mobility Program (IAMP).',
        'detail_desc' => 'Program mobilitas akademik internasional untuk presentasi riset, konferensi global, atau kolaborasi laboratorium di universitas mitra luar negeri.',
        'icon' => '🌍',
        'color' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        'border' => 'border-blue-200 dark:border-blue-800',
        'status' => 'Buka',
        'status_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
        'dokumen' => [
            [
                'nama' => 'Proposal Kegiatan IAMP',
                'tempat' => 'Mahasiswa / Kantor Urusan Internasional',
                'tempat_color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                'cara' => 'Proposal terstruktur mengenai tujuan, universitas tujuan, durasi, dan output riset internasional.',
                'format' => 'PDF'
            ],
            [
                'nama' => 'Letter of Acceptance (LoA) / Invitation',
                'tempat' => 'Universitas Mitra / Host Institution',
                'tempat_color' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                'cara' => 'Surat penerimaan resmi dari universitas penyelenggara atau panitia konferensi internasional.',
                'format' => 'PDF'
            ],
            [
                'nama' => 'Sertifikat Kemampuan Bahasa Inggris & Paspor',
                'tempat' => 'Lembaga Bahasa / Imigrasi',
                'tempat_color' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                'cara' => 'Scan sertifikat TOEFL/IELTS/Duolingo serta paspor yang masih berlaku minimal 6 bulan.',
                'format' => 'PDF / JPG'
            ]
        ],
        'alur' => [
            '1. Pengajuan berkas pendaftaran IAMP & LoA Mitra.',
            '2. Seleksi & Wawancara oleh Tim Kerjasama Internasional & Prodi.',
            '3. Penerbitan Surat Tugas & Pelaksanaan Program Mobilitas.',
            '4. Pelaporan Kegiatan & Konversi SKS Akademik.'
        ]
    ],
    'kualifikasi' => [
        'desc' => 'Pendaftaran ujian kualifikasi kompetensi mahasiswa doktoral.',
        'detail_desc' => 'Ujian Kualifikasi menguji penguasaan komprehensif bidang ilmu dasar dan terapan sebelum menyusun proposal disertasi.',
        'icon' => '📝',
        'color' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        'border' => 'border-amber-200 dark:border-amber-800',
        'status' => 'Tutup Sementara',
        'status_color' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
        'dokumen' => [
            [
                'nama' => 'Transkrip Nilai Lengkap',
                'tempat' => 'Bagian Akademik',
                'tempat_color' => 'bg-emerald-100 text-emerald-700',
                'cara' => 'Telah lulus seluruh mata kuliah wajib sesuai kurikulum.',
                'format' => 'PDF'
            ]
        ],
        'alur' => [
            '1. Pendaftaran Ujian Kualifikasi Komprehensif.',
            '2. Verifikasi kelulusan mata kuliah prasyarat.',
            '3. Pelaksanaan Ujian Tulis & Lisan Kualifikasi.'
        ]
    ],
    'kolokium' => [
        'desc' => 'Pendaftaran presentasi progres/kolokium rutin.',
        'detail_desc' => 'Kolokium berkala untuk memaparkan perkembangan penelitian di hadapan pembimbing dan sesama mahasiswa pascasarjana.',
        'icon' => '🗣️',
        'color' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
        'border' => 'border-purple-200 dark:border-purple-800',
        'status' => 'Memenuhi Syarat',
        'status_color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
        'dokumen' => [
            [
                'nama' => 'Progress Report Penelitian',
                'tempat' => 'Mahasiswa',
                'tempat_color' => 'bg-purple-100 text-purple-700',
                'cara' => 'Laporan bab / analisis data yang sudah diselesaikan.',
                'format' => 'PDF'
            ]
        ],
        'alur' => [
            '1. Mendaftar sesi kolokium bulanan.',
            '2. Presentasi perkembangan riset & diskusi masukan.'
        ]
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
            'title' => $menuItem['label'],
            'desc' => $meta['desc'],
            'detail_desc' => $meta['detail_desc'] ?? $meta['desc'],
            'icon' => $meta['icon'],
            'color' => $meta['color'],
            'border' => $meta['border'],
            'link' => 'daftar_sidang?jenis=' . $key,
            'status' => $meta['status'],
            'status_color' => $meta['status_color'],
            'dokumen' => $meta['dokumen'] ?? [],
            'alur' => $meta['alur'] ?? []
        ];
    }
}
?>

<div class="max-w-7xl mx-auto w-full" x-data="{
    expandedId: 'tesis', // Default membuka tesis atau null
    activeTab: {},
    toggleCard(id) {
        this.expandedId = this.expandedId === id ? null : id;
        if (!this.activeTab[id]) this.activeTab[id] = 'dokumen';
    }
}">
    <!-- Header Section -->
    <div class="relative rounded-3xl overflow-hidden mb-8 p-8 md:p-10 text-white bg-gradient-to-br from-[#8c0c4c] via-[#731041] to-[#540a2e] shadow-xl">
        <div class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10" style="background:white;transform:translate(30%,-30%)"></div>
        <div class="absolute bottom-0 left-20 w-40 h-40 rounded-full opacity-10" style="background:white;transform:translate(-20%,40%)"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-xs font-semibold backdrop-blur-md mb-3 border border-white/20">
                    <span>🎓 Portal Pascasarjana</span> • <span>Program Studi <?= e($mhs['prodi']) ?></span>
                </div>
                <h1 class="font-display font-extrabold text-3xl md:text-4xl mb-2 tracking-tight">Layanan Pendaftaran Sidang & Akademik</h1>
                <p class="text-white/80 max-w-2xl text-sm md:text-base leading-relaxed">
                    Klik pada kartu pendaftaran di bawah ini untuk <strong>membuka penjelasan persyaratan, panduan cara mendapatkan dokumen, dan alur sidang</strong> secara langsung di dalam grid.
                </p>
            </div>
            <div class="hidden md:flex flex-shrink-0 items-center justify-center w-24 h-24 bg-white/10 rounded-2xl border border-white/20 backdrop-blur-sm shadow-xl">
                <span class="text-5xl">📋</span>
            </div>
        </div>
    </div>

    <!-- Pendaftaran Grid (Inline Expandable Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10 items-start">
        <?php foreach($layanan as $l): ?>
        <div class="bg-white dark:bg-slate-800 rounded-3xl border shadow-sm transition-all duration-300 relative overflow-hidden flex flex-col"
             :class="expandedId === '<?= $l['id'] ?>' 
                ? 'border-[#8c0c4c] dark:border-pink-500 shadow-2xl ring-2 ring-[#8c0c4c]/20 md:col-span-2 lg:col-span-3' 
                : 'border-slate-200 dark:border-slate-700 hover:shadow-xl hover:border-[#8c0c4c]/40 hover:-translate-y-0.5'">
            
            <!-- Decorative Top Accent -->
            <div class="absolute top-0 left-0 w-full h-1.5 <?= str_replace('text-', 'bg-', explode(' ', $l['color'])[1]) ?> opacity-80"
                 :class="expandedId === '<?= $l['id'] ?>' ? 'h-2' : ''"></div>
            
            <!-- Card Header & Summary (Clickable to Toggle) -->
            <div @click="toggleCard('<?= $l['id'] ?>')" class="p-6 md:p-7 cursor-pointer select-none">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-14 h-14 rounded-2xl <?= $l['color'] ?> <?= $l['border'] ?> border flex items-center justify-center text-3xl shadow-inner transition-transform duration-300"
                         :class="expandedId === '<?= $l['id'] ?>' ? 'scale-110 rotate-3' : 'group-hover:scale-105'">
                        <?= $l['icon'] ?>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg <?= $l['status_color'] ?>">
                            <?= $l['status'] ?>
                        </span>
                        <span class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 flex items-center justify-center transition-transform duration-300"
                              :class="expandedId === '<?= $l['id'] ?>' ? 'rotate-180 bg-[#8c0c4c] text-white' : ''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </div>
                </div>
                
                <h3 class="font-display font-bold text-lg md:text-xl text-slate-800 dark:text-white mb-2"
                    :class="expandedId === '<?= $l['id'] ?>' ? 'text-[#8c0c4c] dark:text-pink-400' : ''">
                    <?= htmlspecialchars($l['title']) ?>
                </h3>
                
                <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 leading-relaxed mb-4">
                    <?= htmlspecialchars($l['desc']) ?>
                </p>

                <!-- Document Badges Counter & Toggle Bar -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700/60 text-xs">
                    <span class="text-slate-500 dark:text-slate-400 font-medium flex items-center gap-1.5">
                        <span>📋</span> <strong><?= count($l['dokumen']) ?> Berkas Dokumen</strong>
                    </span>
                    <span class="font-bold text-[#8c0c4c] dark:text-pink-400 flex items-center gap-1">
                        <span x-text="expandedId === '<?= $l['id'] ?>' ? 'Tutup Rincian' : 'Klik Buka Panduan & Syarat'"></span>
                        <span x-text="expandedId === '<?= $l['id'] ?>' ? '▴' : '▾'"></span>
                    </span>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- INLINE EXPANDED PANEL (ANIMATED SLIDE DOWN INSIDE THE CARD)   -->
            <!-- ============================================================= -->
            <div x-show="expandedId === '<?= $l['id'] ?>'" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 style="display: none;"
                 class="border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/60 p-6 md:p-8 space-y-6">
                
                <!-- Overview Banner -->
                <div class="bg-gradient-to-r from-[#8c0c4c]/10 via-purple-500/5 to-pink-500/10 border border-[#8c0c4c]/20 dark:border-[#8c0c4c]/30 rounded-2xl p-5">
                    <div class="flex items-start gap-3.5">
                        <span class="text-2xl flex-shrink-0">💡</span>
                        <div>
                            <h4 class="font-display font-bold text-sm text-[#8c0c4c] dark:text-pink-400 mb-1">Penjelasan & Tujuan</h4>
                            <p class="text-xs md:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                                <?= htmlspecialchars($l['detail_desc']) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabs Selection inside Expanded Panel -->
                <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-3">
                    <button type="button" @click="activeTab['<?= $l['id'] ?>'] = 'dokumen'"
                            :class="(activeTab['<?= $l['id'] ?>'] || 'dokumen') === 'dokumen' 
                                ? 'bg-[#8c0c4c] text-white shadow-sm font-bold' 
                                : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 font-medium'"
                            class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 border border-slate-200/60 dark:border-slate-700">
                        <span>📁 Dokumen & Cara Mendapatkannya</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" 
                              :class="(activeTab['<?= $l['id'] ?>'] || 'dokumen') === 'dokumen' ? 'bg-white/20 text-white' : 'bg-[#8c0c4c]/10 text-[#8c0c4c]'">
                            <?= count($l['dokumen']) ?>
                        </span>
                    </button>
                    <button type="button" @click="activeTab['<?= $l['id'] ?>'] = 'alur'"
                            :class="activeTab['<?= $l['id'] ?>'] === 'alur' 
                                ? 'bg-[#8c0c4c] text-white shadow-sm font-bold' 
                                : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 font-medium'"
                            class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 border border-slate-200/60 dark:border-slate-700">
                        <span>⚙️ Alur & Tahapan Ujian</span>
                    </button>
                </div>

                <!-- TAB 1: DOKUMEN & CARA MENDAPATKANNYA -->
                <div x-show="(activeTab['<?= $l['id'] ?>'] || 'dokumen') === 'dokumen'" class="space-y-3.5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach($l['dokumen'] as $idx => $doc): ?>
                        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-4.5 shadow-xs hover:border-[#8c0c4c]/40 transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-pink-900/30 dark:text-pink-300 text-xs font-bold flex items-center justify-center flex-shrink-0">
                                            <?= $idx + 1 ?>
                                        </span>
                                        <h5 class="text-xs font-bold text-slate-800 dark:text-white">
                                            <?= htmlspecialchars($doc['nama']) ?>
                                        </h5>
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-1.5 mb-3 pl-8">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold <?= $doc['tempat_color'] ?? 'bg-blue-100 text-blue-700' ?>">
                                        📍 <?= htmlspecialchars($doc['tempat']) ?>
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-mono font-bold">
                                        <?= htmlspecialchars($doc['format']) ?>
                                    </span>
                                </div>

                                <div class="pl-8 text-[11px] text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/70 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800 leading-relaxed space-y-2">
                                    <div>
                                        <span class="font-bold text-[#8c0c4c] dark:text-pink-400">💡 Cara Mendapatkan:</span><br>
                                        <?= htmlspecialchars($doc['cara']) ?>
                                    </div>
                                    <?php if (!empty($doc['link'])): ?>
                                    <div class="pt-1">
                                        <a href="<?= htmlspecialchars($doc['link']) ?>" target="_blank" @click.stop class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#8c0c4c] text-white text-[10px] font-bold hover:bg-[#a3155b] transition shadow-2xs">
                                            <?= htmlspecialchars($doc['link_label']) ?>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- TAB 2: ALUR TAHAPAN -->
                <div x-show="activeTab['<?= $l['id'] ?>'] === 'alur'" style="display:none;" class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <?php foreach($l['alur'] as $sIdx => $step): ?>
                        <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-start gap-3 shadow-xs">
                            <div class="w-7 h-7 rounded-xl bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-xs">
                                <?= $sIdx + 1 ?>
                            </div>
                            <p class="text-xs text-slate-700 dark:text-slate-300 font-medium leading-relaxed pt-0.5">
                                <?= htmlspecialchars($step) ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <button type="button" @click="expandedId = null" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        ▴ Tutup Rincian
                    </button>
                    <a href="<?= htmlspecialchars($l['link']) ?>" 
                       class="w-full sm:w-auto px-8 py-3 rounded-xl bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] hover:from-[#a3155b] hover:to-[#c41e73] text-white text-xs font-bold shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2 hover:-translate-y-0.5">
                        <span>Lanjut Isi Formulir <?= htmlspecialchars($l['title']) ?></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Bantuan / Info -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/20 border border-blue-200 dark:border-blue-800 rounded-3xl p-6 md:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-500 text-white flex items-center justify-center flex-shrink-0 text-2xl shadow-md">
                💡
            </div>
            <div>
                <h4 class="font-display font-bold text-slate-800 dark:text-white text-base mb-1">Pusat Bantuan & Panduan Akademik</h4>
                <p class="text-xs md:text-sm text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl">
                    Pastikan seluruh berkas administratif seperti persetujuan pembimbing dan bukti bebas administrasi telah lengkap sebelum mendaftar. Untuk pertanyaan lebih lanjut, hubungi helpdesk Pascasarjana.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="https://drive.google.com/drive/u/0/folders/1ZbSRYjiSc4vaPMo-oRRmGwgTe8UUCa5D" target="_blank" class="px-5 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-50 transition shadow-xs flex items-center gap-2">
                📁 Template Resmi Drive ↗
            </a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
