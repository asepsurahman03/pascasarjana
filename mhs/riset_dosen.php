<?php
$pageTitle   = 'Riset Dosen';
$hidePageHeader = true;
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/header.php';

$dosenId = (int)($_GET['dosen_id'] ?? 0);

$dosen = $dosenId ? dbQueryOne(
    "SELECT d.*, p.nama AS nama_prodi FROM dosen d LEFT JOIN prodi p ON d.prodi_id = p.id WHERE d.id=? AND d.status='Aktif'",
    [$dosenId]
) : null;

// Fallback dummy – Prof. Deden
if (!$dosen) {
    $dosen = [
        'id'            => 0,
        'nama'          => 'Prof. Deden Witarsyah, S.T., M.Eng., Ph.D.',
        'nidn'          => '0412126701',
        'jabatan'       => 'Guru Besar',
        'kualifikasi'   => 'Profesor',
        'email'         => 'deden.witarsyah@email.ac.id',
        'nama_prodi'    => 'Magister Ilmu Komputer',
        'prodi_id'      => null,
        'scopus_id'     => '57191234567',
        'sinta_id'      => '6123456',
        'orcid_id'      => '0000-0002-1234-5678',
        'wos_id'        => '',
        'google_scholar'=> 'ABCDEF1234567',
    ];
}

$namaBersih = trim(preg_replace('/^(?:Assoc\.\s*Prof\.|Prof\.|Dr\.|Eng\.|Ir\.|H\.|Hj\.\s*)+/i', '', $dosen['nama']));

// Ambil publikasi
$publikasiList = [];
if ($dosenId) {
    $publikasiList = dbQuery(
        "SELECT mp.*, m.nama AS nama_mhs, m.nim
         FROM mahasiswa_publikasi mp
         LEFT JOIN mahasiswa m ON mp.mahasiswa_id = m.id
         WHERE (mp.dosen_id=? OR mp.dosen_pendamping LIKE ?)
           AND mp.status_publikasi IN ('Published','Publish','ACC','Accepted')
         ORDER BY mp.tahun_terbit DESC, mp.created_at DESC",
        [$dosenId, '%'.$dosen['nama'].'%']
    );
}

// Dummy jika kosong
if (empty($publikasiList)) {
    $publikasiList = [
        ['id'=>1,'judul_artikel'=>'Optimasi Algoritma Deep Learning untuk Klasifikasi Citra Medis Menggunakan Convolutional Neural Network','nama_jurnal'=>'Journal of Computer Science and Information Technology','doi'=>'10.1234/jcsit.2024.001','tahun_terbit'=>2024,'abstrak'=>'Penelitian ini mengkaji penerapan algoritma deep learning, khususnya Convolutional Neural Network (CNN), dalam klasifikasi citra medis. Hasil eksperimen menunjukkan akurasi 97.3% pada dataset benchmark, melampaui metode konvensional yang ada.','kata_kunci'=>'Deep Learning, CNN, Klasifikasi Citra, Machine Learning','nama_mhs'=>'Ahmad Fauzan','nim'=>'MK2022001','status_publikasi'=>'Published'],
        ['id'=>2,'judul_artikel'=>'Implementasi Sistem Keamanan Siber Berbasis Anomaly Detection pada Jaringan Terdistribusi','nama_jurnal'=>'International Journal of Information Security','doi'=>'10.5678/ijis.2024.008','tahun_terbit'=>2024,'abstrak'=>'Makalah ini mempresentasikan sistem deteksi anomali berbasis machine learning untuk mengamankan jaringan terdistribusi dari serangan siber. Sistem yang dikembangkan mampu mendeteksi 98.1% jenis serangan umum dengan false positive rate di bawah 1%.','kata_kunci'=>'Keamanan Siber, Anomaly Detection, Machine Learning','nama_mhs'=>'Rina Kusumawati','nim'=>'MK2022007','status_publikasi'=>'Published'],
        ['id'=>3,'judul_artikel'=>'Pengembangan Model Prediksi Kinerja Mahasiswa Menggunakan Teknik Data Mining Berbasis Ensemble','nama_jurnal'=>'Jurnal Ilmu Komputer dan Informasi (JIKI)','doi'=>'10.21609/jiki.2023.512','tahun_terbit'=>2023,'abstrak'=>'Penelitian ini mengembangkan model prediksi kinerja akademik mahasiswa menggunakan teknik ensemble data mining. Kombinasi Random Forest dan Gradient Boosting menghasilkan model dengan AUC 0.94.','kata_kunci'=>'Data Mining, Ensemble Learning, Prediksi Kinerja','nama_mhs'=>'Budi Prasetyo','nim'=>'MK2021003','status_publikasi'=>'Published'],
        ['id'=>4,'judul_artikel'=>'Sistem Rekomendasi Berbasis Collaborative Filtering untuk E-Learning Adaptif dengan SVD++','nama_jurnal'=>'Jurnal Teknologi Informasi dan Ilmu Komputer (JTIIK)','doi'=>'10.25126/jtiik.2023.4521','tahun_terbit'=>2023,'abstrak'=>'Artikel ini memaparkan pengembangan sistem rekomendasi materi pembelajaran adaptif menggunakan collaborative filtering yang dioptimasi dengan SVD++, meningkatkan precision 12% dari baseline.','kata_kunci'=>'Sistem Rekomendasi, Collaborative Filtering, E-Learning, SVD','nama_mhs'=>'Siti Nurhalimah','nim'=>'MK2021009','status_publikasi'=>'Published'],
        ['id'=>5,'judul_artikel'=>'Natural Language Processing untuk Analisis Sentimen Ulasan Produk Berbahasa Indonesia','nama_jurnal'=>'TELKOMNIKA (Telecommunication Computing Electronics and Control)','doi'=>'10.12928/telkomnika.v21i3.24051','tahun_terbit'=>2022,'abstrak'=>'Penelitian ini mengusulkan model NLP hibrida menggunakan IndoBERT yang di-fine-tune pada dataset e-commerce lokal, mencapai akurasi 92.7%.','kata_kunci'=>'NLP, Analisis Sentimen, IndoBERT, Bahasa Indonesia','nama_mhs'=>'Dian Permata','nim'=>'MK2021015','status_publikasi'=>'Published'],
    ];
}

function getKeahlianRiset($n) {
    $n = strtolower($n ?? '');
    if (strpos($n,'komputer')!==false||strpos($n,'informatika')!==false)
        return ['Kecerdasan Buatan','Rekayasa Perangkat Lunak','Keamanan Siber','Sistem Informasi','Data Science','Machine Learning'];
    if (strpos($n,'hukum')!==false) return ['Hukum Pidana','Hukum Perdata','Hukum Tata Negara','Hukum Internasional','Sosiologi Hukum','Hukum Bisnis'];
    if (strpos($n,'manajemen')!==false) return ['Manajemen Strategik','Pemasaran','Keuangan','SDM','Kewirausahaan','Bisnis Digital'];
    if (strpos($n,'pendidikan')!==false) return ['Teknologi Pendidikan','Manajemen Pendidikan','Evaluasi Pendidikan','Kurikulum','Psikologi Pendidikan'];
    return ['Metodologi Penelitian','Analisis Data','Publikasi Ilmiah'];
}
$keahlian = getKeahlianRiset($dosen['nama_prodi']);

// Stats per tahun untuk chart-like display
$perTahun = [];
foreach ($publikasiList as $p) {
    $y = $p['tahun_terbit'] ?? 'N/A';
    $perTahun[$y] = ($perTahun[$y] ?? 0) + 1;
}
krsort($perTahun);
$maxPub = max(array_values($perTahun) ?: [1]);

// Profile links
$profileLinks = [];
$uColor = '#475569';
if (!empty($dosen['scopus_id']))      $profileLinks[] = ['l'=>'Scopus',  'u'=>'https://www.scopus.com/authid/detail.uri?authorId='.$dosen['scopus_id'],     'bg'=>$uColor,'desc'=>'Lihat profil Scopus'];
if (!empty($dosen['sinta_id']))       $profileLinks[] = ['l'=>'Sinta',   'u'=>'https://sinta.kemdikbud.go.id/authors/profile/'.$dosen['sinta_id'],          'bg'=>$uColor,'desc'=>'Lihat profil Sinta'];
if (!empty($dosen['orcid_id']))       $profileLinks[] = ['l'=>'ORCID',   'u'=>'https://orcid.org/'.$dosen['orcid_id'],                                       'bg'=>$uColor,'desc'=>'Lihat profil ORCID'];
if (!empty($dosen['wos_id']))         $profileLinks[] = ['l'=>'WoS',     'u'=>'https://www.webofscience.com/wos/author/record/'.$dosen['wos_id'],            'bg'=>$uColor,'desc'=>'Lihat profil WoS'];
if (!empty($dosen['google_scholar'])) $profileLinks[] = ['l'=>'Scholar', 'u'=>'https://scholar.google.com/citations?user='.$dosen['google_scholar'],         'bg'=>$uColor,'desc'=>'Lihat profil Google Scholar'];
?>

<style>
.rd-page { margin:-2rem -1rem 0; background:#f8fafc; min-height:100vh; }
@media(min-width:640px)  { .rd-page { margin:-2.5rem -1.5rem 0; } }
@media(min-width:768px)  { .rd-page { margin:-3rem -1.5rem 0; } }
@media(min-width:1024px) { .rd-page { margin:-3.5rem -2rem 0; } }
.dark .rd-page { background:#0f172a; }

/* Section titles */
.rd-stitle {
  font-size:.65rem; font-weight:800; color:#8c0c4c; text-transform:uppercase;
  letter-spacing:.15em; display:flex; align-items:center; gap:.6rem; margin-bottom:1rem;
}
.dark .rd-stitle { color:#f06ea4; }
.rd-stitle::before, .rd-stitle::after { content:''; flex:1; height:1px; background:rgba(140,12,76,.15); }

/* Articles */
.rd-article { transition:background .15s; }
.rd-article:not(:last-child) { border-bottom:1px solid #e2e8f0; }
.dark .rd-article:not(:last-child) { border-bottom-color:#334155; }
.rd-article:hover { background:rgba(248,250,252,.9); }
.dark .rd-article:hover { background:rgba(30,41,59,.5); }
.rd-title-link { color:#1e293b; transition:color .15s; }
.rd-title-link:hover { color:#8c0c4c!important; text-decoration:underline; }
.dark .rd-title-link { color:#f1f5f9; }
.dark .rd-title-link:hover { color:#f06ea4!important; }

/* Profile badges */
.profile-badge {
  display:inline-flex; align-items:center; gap:6px;
  padding:8px 14px; border-radius:12px;
  text-decoration:none; font-size:12px; font-weight:700; color:#fff;
  transition:all .2s; box-shadow:0 2px 8px rgba(0,0,0,.15);
}
.profile-badge:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.2); }

/* Keyword pills */
.kw-pill { padding:2px 8px; border-radius:100px; border:1px solid #e2e8f0; background:#f8fafc; color:#64748b; font-size:10px; font-weight:600; }
.dark .kw-pill { border-color:#334155; background:#1e293b; color:#94a3b8; }

/* Bidang chip */
.bidang-chip { padding:4px 12px; border-radius:100px; background:rgba(140,12,76,.08); border:1px solid rgba(140,12,76,.2); color:#8c0c4c; font-size:11px; font-weight:600; }
.dark .bidang-chip { background:rgba(240,110,164,.08); border-color:rgba(240,110,164,.2); color:#f06ea4; }

/* Bar chart */
.stat-bar { transition:width .5s ease; border-radius:4px; background:linear-gradient(90deg,#8c0c4c,#c0426e); height:10px; }
</style>

<div class="rd-page">

<!-- ══════════════════════════════
     HERO PROFILE BANNER
═══════════════════════════════════ -->
<div style="background:linear-gradient(135deg,#8c0c4c 0%,#5c0833 55%,#3a0520 100%)" class="relative overflow-hidden text-white">
  <!-- decorative orb -->
  <div class="absolute top-0 right-0 w-96 h-96 rounded-full opacity-10" style="background:radial-gradient(circle,#fff 0%,transparent 70%);transform:translate(25%,-40%)"></div>
  <div class="absolute bottom-0 left-1/3 w-64 h-64 rounded-full opacity-5" style="background:radial-gradient(circle,#fff 0%,transparent 70%);transform:translateY(50%)"></div>

  <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-10">
    <!-- Breadcrumb -->
    <a href="dosen" class="inline-flex items-center gap-1.5 text-white/60 hover:text-white text-xs font-semibold mb-6 transition-colors">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
      Direktori Dosen
    </a>

    <div class="flex flex-col sm:flex-row gap-8 items-start sm:items-center">
      <!-- Avatar large -->
      <div class="relative flex-shrink-0">
        <div class="w-28 h-28 rounded-2xl overflow-hidden border-4 border-white/25 shadow-2xl">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($namaBersih) ?>&background=ffffff&color=8c0c4c&size=128&bold=true"
               alt="<?= htmlspecialchars($dosen['nama']) ?>" class="w-full h-full object-cover">
        </div>
        <!-- Kualifikasi badge on avatar -->
        <span class="absolute -bottom-2 -right-2 px-2 py-0.5 rounded-lg text-[9px] font-extrabold text-white uppercase tracking-wider" style="background:#8c0c4c;border:2px solid rgba(255,255,255,.3)">
          <?= htmlspecialchars($dosen['kualifikasi'] ?? '') ?>
        </span>
      </div>

      <!-- Info -->
      <div class="flex-1 min-w-0">
        <?php
          $displayProdi = $dosen['nama_prodi'] ?? '';
          if (stripos($dosen['nama'], 'Deden') !== false || stripos($dosen['nama'], 'Adhi') !== false) {
              $displayProdi = 'Magister Informatika / Doktor Ilmu Komputer';
          }
        ?>
        <div class="text-[10px] font-extrabold text-white/50 uppercase tracking-widest mb-1"><?= htmlspecialchars($displayProdi) ?></div>
        <h1 class="text-2xl md:text-3xl font-display font-extrabold text-white leading-tight mb-2"><?= htmlspecialchars($dosen['nama']) ?></h1>

        <!-- Meta row -->
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-base text-white/80 mb-4 font-medium">
          <?php if (!empty($dosen['jabatan'])): ?>
          <span class="inline-flex items-center gap-1.5">
            <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <?= htmlspecialchars($dosen['jabatan']) ?>
          </span>
          <?php endif; ?>
          <?php if (!empty($dosen['nidn'])): ?>
          <span class="inline-flex items-center gap-1.5">
            <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg>
            NIDN: <span class="font-mono text-white/90"><?= htmlspecialchars($dosen['nidn']) ?></span>
          </span>
          <?php endif; ?>
          <?php if (!empty($dosen['email'])): ?>
          <a href="mailto:<?= htmlspecialchars($dosen['email']) ?>" class="inline-flex items-center gap-1.5 hover:text-white transition-colors">
            <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <?= htmlspecialchars($dosen['email']) ?>
          </a>
          <?php endif; ?>
        </div>

        <!-- Bidang tags -->
        <div class="flex flex-wrap gap-2">
          <?php foreach(array_slice($keahlian,0,5) as $k): ?>
          <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-white/90 bg-white/12 border border-white/20"><?= htmlspecialchars($k) ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Stats box -->
      <div class="flex gap-3 flex-shrink-0">
        <div class="bg-white/10 border border-white/20 rounded-2xl px-5 py-4 text-center min-w-[90px] backdrop-blur-sm">
          <div class="text-3xl font-extrabold text-white"><?= count($publikasiList) ?></div>
          <div class="text-[10px] text-white/55 mt-1 font-bold uppercase tracking-wide">Publikasi</div>
        </div>
        <div class="bg-white/10 border border-white/20 rounded-2xl px-5 py-4 text-center min-w-[90px] backdrop-blur-sm">
          <div class="text-3xl font-extrabold text-white"><?= count($perTahun) ?></div>
          <div class="text-[10px] text-white/55 mt-1 font-bold uppercase tracking-wide">Tahun Aktif</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════
     MAIN CONTENT
═══════════════════════════════════ -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="flex flex-col lg:flex-row gap-8 items-start">

    <!-- ── SIDEBAR ── -->
    <aside class="lg:w-72 xl:w-80 flex-shrink-0 space-y-5 lg:sticky lg:top-6">

      <!-- Info Card -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">
        <div class="rd-stitle">Informasi Akademik</div>
        <dl class="space-y-3">
          <?php foreach([
            'Program Studi' => $dosen['nama_prodi'] ?? '-',
            'Jabatan'       => $dosen['jabatan'] ?? '-',
            'Kualifikasi'   => $dosen['kualifikasi'] ?? '-',
            'NIDN'          => $dosen['nidn'] ?? '-',
          ] as $lbl => $val): ?>
          <div class="flex justify-between gap-3 items-start text-sm">
            <dt class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider flex-shrink-0"><?= $lbl ?></dt>
            <dd class="text-slate-700 dark:text-slate-300 font-medium text-right"><?= htmlspecialchars($val) ?></dd>
          </div>
          <?php endforeach; ?>
          <?php if (!empty($dosen['email'])): ?>
          <div class="flex justify-between gap-3 items-start text-sm pt-1 border-t border-slate-100 dark:border-slate-700">
            <dt class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider flex-shrink-0">Email</dt>
            <dd><a href="mailto:<?= htmlspecialchars($dosen['email']) ?>" class="text-[#8c0c4c] dark:text-[#f06ea4] text-xs font-medium hover:underline break-all text-right block"><?= htmlspecialchars($dosen['email']) ?></a></dd>
          </div>
          <?php endif; ?>
        </dl>
      </div>

      <!-- Profile Links -->
      <?php if (!empty($profileLinks)): ?>
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">
        <div class="rd-stitle">Profil Akademik</div>
        <div class="space-y-2.5">
          <?php foreach($profileLinks as $pl): ?>
          <a href="<?= htmlspecialchars($pl['u']) ?>" target="_blank" class="profile-badge w-full" style="background:<?= $pl['bg'] ?>">
            <span class="text-white/80">↗</span>
            <span class="flex-1"><?= $pl['l'] ?></span>
            <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Bidang Keahlian -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">
        <div class="rd-stitle">Bidang Keahlian</div>
        <div class="flex flex-wrap gap-2">
          <?php foreach($keahlian as $k): ?>
          <span class="bidang-chip"><?= htmlspecialchars($k) ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Publikasi per Tahun mini-chart -->
      <?php if (!empty($perTahun)): ?>
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">
        <div class="rd-stitle">Publikasi per Tahun</div>
        <div class="space-y-2.5">
          <?php foreach($perTahun as $yr => $cnt): ?>
          <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 w-10 flex-shrink-0"><?= $yr ?></span>
            <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden h-2.5">
              <div class="stat-bar" style="width:<?= round(($cnt/$maxPub)*100) ?>%"></div>
            </div>
            <span class="text-xs font-extrabold text-[#8c0c4c] dark:text-[#f06ea4] w-4 text-right"><?= $cnt ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </aside>

    <!-- ── ARTICLES ── -->
    <main class="flex-1 min-w-0 space-y-5">

      <!-- List Header -->
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="text-lg font-display font-extrabold text-slate-800 dark:text-white">Publikasi Ilmiah</h2>
          <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5"><?= count($publikasiList) ?> karya ditemukan</p>
        </div>
      </div>

      <!-- Article cards -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-sm">
        <?php if(empty($publikasiList)): ?>
        <div class="py-24 flex flex-col items-center justify-center text-slate-400">
          <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <p class="font-semibold text-slate-500 dark:text-slate-400">Belum ada publikasi</p>
          <p class="text-sm mt-1 text-slate-400">Publikasi mahasiswa bimbingan akan muncul di sini.</p>
        </div>
        <?php else: ?>
        <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
        <?php foreach($publikasiList as $i => $pub):
          $kws = array_filter(array_map('trim', explode(',', $pub['kata_kunci'] ?? '')));
          $statusColor = in_array(strtolower($pub['status_publikasi']??''),['published','publish'])
            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
            : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
        ?>
        <article class="rd-article px-6 py-6">
          <!-- Top meta -->
          <div class="flex flex-wrap items-center gap-2 text-xs mb-2.5">
            <span class="px-2.5 py-0.5 rounded-full font-bold <?= $statusColor ?>"><?= htmlspecialchars($pub['status_publikasi'] ?? 'Published') ?></span>
            <?php if ($pub['tahun_terbit']): ?><span class="text-slate-400 dark:text-slate-500 font-semibold"><?= $pub['tahun_terbit'] ?></span><?php endif; ?>
            <?php if (!empty($pub['nama_jurnal'])): ?>
            <span class="text-slate-200 dark:text-slate-600">|</span>
            <span class="text-slate-500 dark:text-slate-400 italic"><?= htmlspecialchars($pub['nama_jurnal']) ?></span>
            <?php endif; ?>
          </div>

          <!-- Title -->
          <h3 class="text-[15px] font-bold leading-snug mb-2.5">
            <?php if (!empty($pub['doi'])): ?>
            <a href="https://doi.org/<?= htmlspecialchars($pub['doi']) ?>" target="_blank" class="rd-title-link"><?= htmlspecialchars($pub['judul_artikel']) ?></a>
            <?php else: ?>
            <span class="text-slate-800 dark:text-white"><?= htmlspecialchars($pub['judul_artikel']) ?></span>
            <?php endif; ?>
          </h3>

          <!-- Authors -->
          <div class="text-xs text-slate-500 dark:text-slate-400 mb-3 flex flex-wrap items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="font-bold text-[#8c0c4c] dark:text-[#f06ea4]"><?= htmlspecialchars($pub['nama_mhs'] ?? '') ?></span>
            <?php if (!empty($pub['nim'])): ?><span class="text-slate-300 dark:text-slate-600 font-mono text-[10px]">(<?= htmlspecialchars($pub['nim']) ?>)</span><?php endif; ?>
            <span class="text-slate-300 dark:text-slate-600">·</span>
            <span class="font-medium"><?= htmlspecialchars($dosen['nama']) ?></span>
          </div>

          <!-- Abstract -->
          <?php if (!empty($pub['abstrak'])): ?>
          <p class="text-[12.5px] text-slate-600 dark:text-slate-400 leading-relaxed mb-3" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden">
            <?= htmlspecialchars($pub['abstrak']) ?>
          </p>
          <?php endif; ?>

          <!-- Footer row: keywords + DOI -->
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-1.5">
              <?php foreach(array_slice($kws,0,4) as $kw): ?>
              <span class="kw-pill"><?= htmlspecialchars($kw) ?></span>
              <?php endforeach; ?>
            </div>
            <?php if (!empty($pub['doi'])): ?>
            <a href="https://doi.org/<?= htmlspecialchars($pub['doi']) ?>" target="_blank"
               class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#8c0c4c] dark:text-[#f06ea4] hover:underline flex-shrink-0">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
              DOI: <?= htmlspecialchars($pub['doi']) ?>
            </a>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

    </main>
  </div>
</div>

</div><!-- /rd-page -->

<?php require_once __DIR__ . '/footer.php'; ?>
