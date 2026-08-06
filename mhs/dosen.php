<?php
$pageFile  = 'dosen';
$pageTitle = 'Direktori Dosen';
$hidePageHeader = true;
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/header.php';

// Ambil prodi_id mahasiswa yang sedang login
$mhsProdiId = $mhsRow['prodi_id'];
$mhsProdiNama = $mhsRow['prodi'] ?? '-';

$dosenList = dbQuery("
    SELECT d.*, p.nama AS nama_prodi 
    FROM dosen d 
    LEFT JOIN prodi p ON d.prodi_id = p.id 
    WHERE d.status = 'Aktif' 
      AND d.prodi_id = ?
    ORDER BY 
        CASE
            WHEN d.kualifikasi LIKE '%Profesor%' OR d.jabatan LIKE '%Guru Besar%' THEN 1
            WHEN d.kualifikasi LIKE '%Doktor%' AND d.jabatan LIKE '%Guru Besar%'  THEN 1
            WHEN (d.kualifikasi LIKE '%Doktor%' OR d.kualifikasi LIKE '%S3%') AND d.jabatan LIKE '%Lektor Kepala%' THEN 2
            WHEN (d.kualifikasi LIKE '%Doktor%' OR d.kualifikasi LIKE '%S3%') THEN 3
            WHEN (d.kualifikasi LIKE '%Magister%' OR d.kualifikasi LIKE '%S2%') AND d.jabatan LIKE '%Lektor Kepala%' THEN 4
            WHEN (d.kualifikasi LIKE '%Magister%' OR d.kualifikasi LIKE '%S2%') THEN 5
            ELSE 6
        END ASC,
        CASE 
            WHEN d.nama LIKE '%Deden%'  THEN 1
            WHEN d.nama LIKE '%Nunik%'  THEN 2
            WHEN d.nama LIKE '%Adhi%'   THEN 3
            WHEN d.nama LIKE '%Yusuf%'  THEN 4
            WHEN d.nama LIKE '%Slamet%' THEN 5
            ELSE 6
        END ASC,
        d.nama ASC
", [$mhsProdiId]);

function getKeahlianProdi($namaProdi) {
    $n = strtolower($namaProdi ?? '');
    if (strpos($n,'komputer')!==false||strpos($n,'informatika')!==false)
        return ['Kecerdasan Buatan','Rekayasa Perangkat Lunak','Keamanan Siber','Sistem Informasi','Data Science','Machine Learning'];
    if (strpos($n,'hukum')!==false)
        return ['Hukum Pidana','Hukum Perdata','Hukum Tata Negara','Hukum Internasional','Sosiologi Hukum','Hukum Bisnis'];
    if (strpos($n,'manajemen')!==false)
        return ['Manajemen Strategik','Manajemen Pemasaran','Manajemen Keuangan','SDM','Kewirausahaan','Bisnis Digital'];
    if (strpos($n,'pendidikan')!==false||strpos($n,'dasar')!==false)
        return ['Teknologi Pendidikan','Manajemen Pendidikan','Evaluasi Pendidikan','Kurikulum','Psikologi Pendidikan'];
    return ['Metodologi Penelitian','Analisis Data','Publikasi Ilmiah','Kajian Lanjut'];
}

function buildProfileLinks(array $d): array {
    $links = [];
    $uniformColor = '#475569'; // Slate-600 untuk semua badge (tidak berwarna warni)
    
    if (!empty($d['scopus_id']))
        $links[] = ['label'=>'Scopus',  'url'=>'https://www.scopus.com/authid/detail.uri?authorId='.urlencode($d['scopus_id']),         'color'=>$uniformColor];
    if (!empty($d['sinta_id']))
        $links[] = ['label'=>'Sinta',   'url'=>'https://sinta.kemdikbud.go.id/authors/profile/'.urlencode($d['sinta_id']),              'color'=>$uniformColor];
    if (!empty($d['orcid_id']))
        $links[] = ['label'=>'ORCID',   'url'=>'https://orcid.org/'.urlencode($d['orcid_id']),                                         'color'=>$uniformColor];
    if (!empty($d['wos_id']))
        $links[] = ['label'=>'WoS',     'url'=>'https://www.webofscience.com/wos/author/record/'.urlencode($d['wos_id']),               'color'=>$uniformColor];
    if (!empty($d['google_scholar']))
        $links[] = ['label'=>'Scholar', 'url'=>'https://scholar.google.com/citations?user='.urlencode($d['google_scholar']),            'color'=>$uniformColor];
    return $links;
}

$totalDosen = count($dosenList);
?>
<style>
/* Page wrapper */
.dir-page { margin: -2rem -1rem 0; background: #f1f5f9; min-height: 100vh; }
@media(min-width:640px)  { .dir-page { margin: -2.5rem -1.5rem 0; } }
@media(min-width:768px)  { .dir-page { margin: -3rem   -1.5rem 0; } }
@media(min-width:1024px) { .dir-page { margin: -3.5rem -2rem 0;   } }
.dark .dir-page { background: #0f172a; }

/* Dosen card */
.dc {
  background:#fff; border:1px solid #e2e8f0; border-radius:20px;
  overflow:hidden; display:flex; flex-direction:column;
  transition:box-shadow .2s ease, transform .2s ease;
  cursor:pointer;
}
.dark .dc { background:#1e293b; border-color:#334155; }
.dc:hover  { box-shadow:0 16px 40px rgba(140,12,76,.13); transform:translateY(-3px); }

/* Banner */
.dc-banner {
  height:80px; position:relative;
  background:linear-gradient(135deg,#8c0c4c 0%,#660936 60%,#440524 100%);
}
.dc-banner::after {
  content:''; position:absolute; inset:0;
  background:radial-gradient(ellipse at 80% 40%,rgba(255,255,255,.08) 0%,transparent 60%);
}

/* Avatar circle overlapping banner */
.dc-avatar {
  position:absolute; bottom:-32px; left:18px; z-index:5;
  width:64px; height:64px; border-radius:50%;
  border:3px solid #fff; box-shadow:0 4px 16px rgba(0,0,0,.18);
  overflow:hidden; background:#e2e8f0;
}
.dark .dc-avatar { border-color:#1e293b; }
.dc-avatar img { width:100%; height:100%; object-fit:cover; }

/* Kualifikasi badge on banner */
.dc-qual {
  position:absolute; top:10px; right:10px; z-index:5;
  padding:3px 8px; border-radius:8px; font-size:9px; font-weight:800;
  color:#fff; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25);
  letter-spacing:.05em; text-transform:uppercase; backdrop-filter:blur(4px);
}

/* Profile link pills */
.dc-pill {
  display:inline-flex; align-items:center; gap:4px;
  padding:2px 7px; border-radius:100px;
  font-size:10px; font-weight:700; color:#fff;
  text-decoration:none; transition:opacity .15s, transform .1s;
  white-space:nowrap;
}
.dc-pill:hover { opacity:.85; transform:scale(1.04); }

/* Bidang chip */
.dc-chip {
  padding:2px 9px; border-radius:100px; font-size:10.5px; font-weight:600;
  background:rgba(140,12,76,.07); border:1px solid rgba(140,12,76,.18); color:#8c0c4c;
}
.dark .dc-chip { background:rgba(240,110,164,.07); border-color:rgba(240,110,164,.2); color:#f06ea4; }

/* CTA */
.dc-btn {
  display:flex; align-items:center; justify-content:center; gap:6px;
  padding:9px 14px; border-radius:12px; font-size:12px; font-weight:700; color:#fff;
  background:linear-gradient(135deg,#8c0c4c,#b0184e); text-decoration:none;
  transition:box-shadow .2s, transform .15s;
}
.dc-btn:hover { box-shadow:0 6px 20px rgba(140,12,76,.3); transform:translateY(-1px); }
</style>

<div class="dir-page" x-data="{ q:'' }">

<!-- ══ HERO ══ -->
<div style="background:linear-gradient(135deg,#8c0c4c,#5c0833 55%,#38051f 100%)" class="relative overflow-hidden">
  <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse at 75% 50%,rgba(255,255,255,.07) 0%,transparent 65%)"></div>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 relative z-10">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div>
        <p class="text-white/50 text-[10px] font-extrabold uppercase tracking-widest mb-2">Program Pascasarjana</p>
        <h1 class="text-3xl md:text-4xl font-display font-extrabold text-white mb-2">Direktori Dosen</h1>
        <p class="text-white/65 text-sm max-w-lg leading-relaxed">
          Dosen aktif program studi <strong class="text-white"><?= htmlspecialchars($mhsProdiNama) ?></strong>. Lihat profil akademik, kontak, dan publikasi ilmiah.
        </p>
      </div>
      <div class="flex gap-3 flex-shrink-0">
        <div class="bg-white/10 border border-white/20 rounded-2xl px-6 py-4 text-center backdrop-blur-sm">
          <div class="text-3xl font-extrabold text-white"><?= $totalDosen ?></div>
          <div class="text-[10px] text-white/55 font-bold uppercase tracking-wider mt-0.5">Dosen Aktif</div>
        </div>
        <div class="bg-white/10 border border-white/20 rounded-2xl px-6 py-4 text-center backdrop-blur-sm min-w-[110px]">
          <div class="text-xs font-extrabold text-white leading-tight mt-1"><?= htmlspecialchars($mhsProdiNama) ?></div>
          <div class="text-[10px] text-white/55 font-bold uppercase tracking-wider mt-0.5">Program Studi</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ FILTER BAR ══ -->
<div class="sticky top-0 z-30 bg-white/96 dark:bg-slate-900/96 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-wrap gap-3 items-center">
    <!-- Search -->
    <div class="relative flex-1 min-w-[200px] max-w-sm">
      <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
      </svg>
      <input type="text" x-model="q" placeholder="Cari nama dosen..."
             class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#8c0c4c]/25 focus:border-[#8c0c4c] transition-all">
    </div>
    <!-- Info Prodi Badge -->
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#8c0c4c]/8 border border-[#8c0c4c]/20 text-[#8c0c4c] dark:bg-[#f06ea4]/10 dark:border-[#f06ea4]/20 dark:text-[#f06ea4] text-xs font-bold">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      <?= htmlspecialchars($mhsProdiNama) ?>
    </span>
    <span class="text-xs text-slate-400 dark:text-slate-500 font-medium ml-auto hidden sm:block"><?= $totalDosen ?> dosen aktif</span>
  </div>
</div>

<!-- ══ GRID ══ -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

    <?php foreach($dosenList as $d):
      $semuaBidang  = getKeahlianProdi($d['nama_prodi']);
      shuffle($semuaBidang);
      $bidang       = array_slice($semuaBidang, 0, 3);
      $namaBersih   = trim(preg_replace('/^(?:Assoc\.\s*Prof\.|CSA\s+|Prof\.|Dr\.\s+Eng\.|Dr\.|Eng\.|Ir\.|H\.|Hj\.)+\s*/i', '', $d['nama']));
      $profileLinks = buildProfileLinks($d);
    ?>
    <div class="dc"
         x-show="q===''||'<?= strtolower(str_replace("'","\\'",htmlspecialchars($d['nama']))) ?>'.includes(q.toLowerCase())"
         onclick="location.href='riset_dosen?dosen_id=<?= $d['id'] ?>'">

      <!-- Banner + Avatar -->
      <div class="dc-banner">
        <span class="dc-qual"><?= htmlspecialchars($d['kualifikasi'] ?: 'Dosen') ?></span>
        <div class="dc-avatar">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($namaBersih) ?>&background=random&color=fff&size=96&bold=true"
               alt="<?= htmlspecialchars($d['nama']) ?>" loading="lazy">
        </div>
      </div>

      <!-- Body -->
      <div class="px-5 pt-10 pb-5 flex-1 flex flex-col gap-1.5">

        <!-- Prodi -->
        <?php
          $displayProdi = $d['nama_prodi'] ?? '—';
          if (stripos($d['nama'], 'Deden') !== false || stripos($d['nama'], 'Adhi') !== false) {
              $displayProdi = 'Magister Informatika / Doktor Ilmu Komputer';
          }
        ?>
        <div class="text-[9.5px] font-extrabold text-[#8c0c4c] dark:text-[#f06ea4] uppercase tracking-widest truncate leading-tight" title="<?= htmlspecialchars($displayProdi) ?>">
          <?= htmlspecialchars($displayProdi) ?>
        </div>

        <!-- Name -->
        <h2 class="text-[13.5px] font-display font-bold text-slate-800 dark:text-white leading-snug"
            style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
          <?= htmlspecialchars($d['nama']) ?>
        </h2>

        <!-- Jabatan -->
        <div class="text-[13px] text-slate-500 dark:text-slate-400 mt-0.5">
          <?= htmlspecialchars($d['jabatan'] ?: 'Dosen Tetap') ?>
          <?php if (!empty($d['nidn'])): ?>
            <span class="opacity-75 px-1">•</span> NIDN: <span class="font-mono text-slate-400 dark:text-slate-500"><?= htmlspecialchars($d['nidn']) ?></span>
          <?php endif; ?>
        </div>

        <!-- Email -->
        <?php if (!empty($d['email'])): ?>
        <div class="flex items-center gap-1.5 text-[13px] text-slate-500 dark:text-slate-400 truncate mt-0.5" onclick="event.stopPropagation()">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          <a href="mailto:<?= htmlspecialchars($d['email']) ?>" class="truncate hover:text-[#8c0c4c] dark:hover:text-[#f06ea4] transition-colors"><?= htmlspecialchars($d['email']) ?></a>
        </div>
        <?php endif; ?>

        <!-- Profile Links -->
        <?php if (!empty($profileLinks)): ?>
        <div class="flex flex-wrap gap-1.5 mt-1" onclick="event.stopPropagation()">
          <?php foreach($profileLinks as $pl): ?>
          <a href="<?= htmlspecialchars($pl['url']) ?>" target="_blank" rel="noopener"
             class="dc-pill" style="background:<?= $pl['color'] ?>;" title="Buka profil <?= $pl['label'] ?>">
            <?= htmlspecialchars($pl['label']) ?>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Divider -->
        <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>

        <!-- Bidang -->
        <div class="flex flex-wrap gap-1.5">
          <?php foreach($bidang as $b): ?>
          <span class="dc-chip"><?= htmlspecialchars($b) ?></span>
          <?php endforeach; ?>
        </div>

        <!-- CTA -->
        <div class="mt-auto pt-3" onclick="event.stopPropagation()">
          <a href="riset_dosen?dosen_id=<?= $d['id'] ?>" class="dc-btn">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Lihat Riset &amp; Publikasi
          </a>
        </div>

      </div>
    </div>
    <?php endforeach; ?>

  </div>

  <?php if(empty($dosenList)): ?>
  <div class="mt-8 py-24 text-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl">
    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
      <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
      </svg>
    </div>
    <p class="font-bold text-slate-600 dark:text-slate-300">Tidak ada dosen aktif</p>
    <p class="text-sm text-slate-400 mt-1">Belum ada data dosen yang tersedia.</p>
  </div>
  <?php endif; ?>
</div>

</div><!-- /dir-page -->

<?php require_once __DIR__ . '/footer.php'; ?>
