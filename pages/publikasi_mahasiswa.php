<?php
$pageTitle = 'Publikasi Mahasiswa';
require_once __DIR__ . '/../includes/header.php';

// ─── Filter & Search ──────────────────────────────────────────────────────────
$filterStatus   = $_GET['status']   ?? '';
$filterProdi    = (int)($_GET['prodi_id'] ?? 0);
$filterTahun    = $_GET['tahun']    ?? '';
$filterKategori = $_GET['kategori'] ?? '';
$searchQ        = trim($_GET['q']   ?? '');
$page           = max(1, (int)($_GET['page'] ?? 1));
$perPage        = 20;

$params = []; $where = ['1=1'];
if ($filterStatus)   { $where[] = 'mp.status_publikasi = ?'; $params[] = $filterStatus; }
if ($filterProdi)    { $where[] = 'm.prodi_id = ?';          $params[] = $filterProdi;  }
if ($filterTahun)    { $where[] = 'mp.tahun_terbit = ?';     $params[] = (int)$filterTahun; }
if ($filterKategori) { $where[] = 'mp.kategori_publikasi LIKE ?'; $params[] = "%$filterKategori%"; }
if ($searchQ)        {
    $where[] = '(mp.judul_artikel LIKE ? OR mp.nama_jurnal LIKE ? OR mp.kategori_publikasi LIKE ? OR m.nama LIKE ? OR m.nim LIKE ?)';
    $params[] = "%$searchQ%"; $params[] = "%$searchQ%"; $params[] = "%$searchQ%"; $params[] = "%$searchQ%"; $params[] = "%$searchQ%";
}
$whereStr = implode(' AND ', $where);

// Total count
$total = (int)dbQueryOne("SELECT COUNT(*) AS c FROM mahasiswa_publikasi mp LEFT JOIN mahasiswa m ON mp.mahasiswa_id=m.id WHERE $whereStr", $params)['c'];
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

// Data
$pubs = dbQuery(
    "SELECT mp.*, m.nama AS mhs_nama, m.nim AS mhs_nim, p.nama AS prodi_nama
     FROM mahasiswa_publikasi mp
     LEFT JOIN mahasiswa m ON mp.mahasiswa_id = m.id
     LEFT JOIN prodi p ON m.prodi_id = p.id
     WHERE $whereStr
     ORDER BY mp.tahun_terbit DESC, mp.created_at DESC
     LIMIT $perPage OFFSET $offset",
    $params
);

// ─── Statistik ────────────────────────────────────────────────────────────────
$stats = dbQueryOne("SELECT
    COUNT(*) AS total,
    SUM(mp.status_publikasi='Publish') AS publish,
    SUM(mp.kategori_publikasi LIKE '%Scopus%') AS scopus,
    SUM(mp.kategori_publikasi LIKE '%SINTA%') AS sinta,
    COUNT(DISTINCT mp.mahasiswa_id) AS jml_mhs
    FROM mahasiswa_publikasi mp", []);

$allProdi   = dbQuery("SELECT id, nama FROM prodi ORDER BY nama", []);
$tahunList  = dbQuery("SELECT DISTINCT tahun_terbit FROM mahasiswa_publikasi WHERE tahun_terbit IS NOT NULL ORDER BY tahun_terbit DESC", []);

// ─── Warna status ─────────────────────────────────────────────────────────────
$statusColor = [
    'Publish'        => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/50',
    'ACC / Diterima' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 border-blue-200 dark:border-blue-800/50',
    'Sedang Review'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 border-amber-200 dark:border-amber-800/50',
];
$defaultStatusColor = 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600/50';

// URL helper
function pubUrl(array $extra = []): string {
    global $filterStatus, $filterProdi, $filterTahun, $filterKategori, $searchQ;
    $p = array_filter(array_merge([
        'status'   => $filterStatus,
        'prodi_id' => $filterProdi ?: '',
        'tahun'    => $filterTahun,
        'kategori' => $filterKategori,
        'q'        => $searchQ,
    ], $extra));
    return 'publikasi_mahasiswa' . ($p ? '?' . http_build_query($p) : '');
}

$exportUrl = 'export_pub_mhs_admin' . ($filterStatus||$filterProdi||$filterTahun||$filterKategori||$searchQ
    ? '?' . http_build_query(array_filter(['status'=>$filterStatus,'prodi_id'=>$filterProdi,'tahun'=>$filterTahun,'kategori'=>$filterKategori,'q'=>$searchQ]))
    : '');
?>

<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
  <div>
    <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Publikasi Ilmiah Mahasiswa</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Manajemen dan rekapitulasi publikasi ilmiah seluruh mahasiswa Pascasarjana</p>
  </div>
  <?php if ($total > 0): ?>
  <a href="<?= $exportUrl ?>" target="_blank" rel="noopener"
     class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all hover:-translate-y-0.5 w-full md:w-auto justify-center">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
    </svg>
    Export Excel
  </a>
  <?php endif; ?>
</div>

<?php if ($flash): ?>
<div class="mb-6 p-4 rounded-xl text-sm font-bold flex items-center gap-3 shadow-sm <?= $flash['type']==='error'?'bg-red-50 dark:bg-red-900/30 text-red-600 border border-red-200':'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 border border-emerald-200' ?>">
  <span><?= $flash['type']==='error'?'❌':'✅' ?></span><?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>

<!-- ─── Statistik ──────────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
  <?php
  $statItems = [
    ['label'=>'Total Publikasi',     'val'=>$stats['total']??0,   'color'=>'from-[#8c0c4c] to-[#a3155b]',  'bg'=>'bg-[#8c0c4c]/5 dark:bg-[#8c0c4c]/20', 'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ['label'=>'⭐ Terindeks Scopus',  'val'=>$stats['scopus']??0,  'color'=>'from-amber-500 to-amber-600',  'bg'=>'bg-amber-50 dark:bg-amber-950/20','icon'=>'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
    ['label'=>'🏅 Terakreditasi SINTA','val'=>$stats['sinta']??0,  'color'=>'from-blue-500 to-indigo-600',   'bg'=>'bg-blue-50 dark:bg-blue-900/20',      'icon'=>'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
    ['label'=>'Sudah Publish',       'val'=>$stats['publish']??0, 'color'=>'from-emerald-500 to-teal-600',  'bg'=>'bg-emerald-50 dark:bg-emerald-900/20','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
    ['label'=>'Mhs. Berkontribusi',  'val'=>$stats['jml_mhs']??0, 'color'=>'from-violet-500 to-purple-600', 'bg'=>'bg-violet-50 dark:bg-violet-900/20',  'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
  ];
  foreach ($statItems as $s): ?>
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-5 relative overflow-hidden group hover:shadow-md transition-all">
    <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full <?= $s['bg'] ?> group-hover:scale-150 transition-transform duration-500"></div>
    <div class="relative z-10 flex items-center justify-between mb-3">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-br <?= $s['color'] ?> text-white flex items-center justify-center shadow-md">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $s['icon'] ?>"/></svg>
      </div>
    </div>
    <div class="relative z-10">
      <div class="font-display font-bold text-2xl text-slate-800 dark:text-white mb-0.5"><?= $s['val'] ?></div>
      <div class="text-xs font-semibold text-slate-500 dark:text-slate-400"><?= $s['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ─── Filter & Search ────────────────────────────────────────────────── -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-4 mb-5">
  <form method="GET" class="flex flex-wrap gap-3 items-center">
    <div class="relative flex-1 min-w-[200px]">
      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="Cari judul, jurnal, NIM, nama mahasiswa, indeksasi..."
             class="w-full pl-9 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/15 text-slate-800 dark:text-white placeholder-slate-400 transition-all">
    </div>
    <select name="kategori" class="text-sm bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#8c0c4c] transition-colors">
      <option value="">Semua Indeksasi / Kategori</option>
      <option value="Scopus" <?= $filterKategori==='Scopus'?'selected':'' ?>>⭐ Semua Scopus</option>
      <option value="SINTA" <?= $filterKategori==='SINTA'?'selected':'' ?>>🏅 Semua SINTA</option>
      <?php foreach (getKategoriPublikasiList() as $kat): ?>
      <option value="<?= $kat ?>" <?= $filterKategori===$kat?'selected':'' ?>><?= $kat ?></option>
      <?php endforeach; ?>
    </select>
    <select name="status" class="text-sm bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#8c0c4c] transition-colors">
      <option value="">Semua Status</option>
      <?php foreach (['Publish','ACC / Diterima','Sedang Review'] as $st): ?>
      <option value="<?= $st ?>" <?= $filterStatus===$st?'selected':'' ?>><?= $st ?></option>
      <?php endforeach; ?>
    </select>
    <select name="prodi_id" class="text-sm bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#8c0c4c] transition-colors">
      <option value="">Semua Prodi</option>
      <?php foreach ($allProdi as $pr): ?>
      <option value="<?= $pr['id'] ?>" <?= $filterProdi==$pr['id']?'selected':'' ?>><?= htmlspecialchars($pr['nama']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="tahun" class="text-sm bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#8c0c4c] transition-colors">
      <option value="">Semua Tahun</option>
      <?php foreach ($tahunList as $t): ?>
      <option value="<?= $t['tahun_terbit'] ?>" <?= $filterTahun==$t['tahun_terbit']?'selected':'' ?>><?= $t['tahun_terbit'] ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="px-5 py-2.5 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl text-sm font-bold transition-colors shadow-sm">Filter</button>
    <?php if ($filterStatus||$filterProdi||$filterTahun||$filterKategori||$searchQ): ?>
    <a href="publikasi_mahasiswa" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Reset</a>
    <?php endif; ?>
  </form>
</div>

<!-- ─── Tabel Data ─────────────────────────────────────────────────────── -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden mb-6">
  <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
    <div>
      <span class="font-bold text-sm text-slate-800 dark:text-white">
        <?= $total ?> publikasi ditemukan
      </span>
      <?php if ($filterStatus||$filterProdi||$filterTahun||$filterKategori||$searchQ): ?>
      <span class="ml-2 text-xs text-slate-400">(filter aktif)</span>
      <?php endif; ?>
    </div>
    <span class="text-xs text-slate-400">Halaman <?= $page ?> / <?= $totalPages ?></span>
  </div>

  <?php if (empty($pubs)): ?>
  <div class="flex flex-col items-center justify-center py-20 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-4">
      <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    </div>
    <p class="text-slate-500 font-semibold">Belum ada data publikasi</p>
    <p class="text-slate-400 text-sm mt-1">Mahasiswa belum menginput publikasi ilmiah.</p>
  </div>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/50">
        <tr>
          <th class="text-left py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-8">#</th>
          <th class="text-left py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Mahasiswa & Artikel</th>
          <th class="text-left py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori & Bibliografi</th>
          <th class="text-left py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="text-left py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Tahun</th>
          <th class="text-right py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
        <?php foreach ($pubs as $i => $p): ?>
        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group"
            data-id="<?= $p['id'] ?>"
            data-judul="<?= htmlspecialchars($p['judul_artikel'], ENT_QUOTES) ?>"
            data-kategori="<?= htmlspecialchars($p['kategori_publikasi'] ?? 'Lainnya', ENT_QUOTES) ?>"
            data-nama="<?= htmlspecialchars($p['mhs_nama'] ?? '', ENT_QUOTES) ?>"
            data-nim="<?= htmlspecialchars($p['mhs_nim'] ?? '', ENT_QUOTES) ?>"
            data-prodi="<?= htmlspecialchars($p['prodi_nama'] ?? '', ENT_QUOTES) ?>"
            data-jurnal="<?= htmlspecialchars($p['nama_jurnal'] ?? '', ENT_QUOTES) ?>"
            data-doi="<?= htmlspecialchars($p['doi'] ?? '', ENT_QUOTES) ?>"
            data-penulis="<?= htmlspecialchars($p['rekan_penulis'] ?? '', ENT_QUOTES) ?>"
            data-pembimbing="<?= htmlspecialchars($p['dosen_pendamping'] ?? '', ENT_QUOTES) ?>"
            data-tahun="<?= $p['tahun_terbit'] ?>"
            data-volume="<?= htmlspecialchars($p['volume'] ?? '', ENT_QUOTES) ?>"
            data-nomor="<?= htmlspecialchars($p['nomor_terbit'] ?? '', ENT_QUOTES) ?>"
            data-halaman="<?= htmlspecialchars($p['halaman'] ?? '', ENT_QUOTES) ?>"
            data-status="<?= htmlspecialchars($p['status_publikasi'] ?? '', ENT_QUOTES) ?>"
            data-kata_kunci="<?= htmlspecialchars($p['kata_kunci'] ?? '', ENT_QUOTES) ?>"
            data-abstrak="<?= htmlspecialchars($p['abstrak'] ?? '', ENT_QUOTES) ?>"
            data-link="<?= htmlspecialchars($p['link_artikel'] ?? '', ENT_QUOTES) ?>"
            data-file="<?= !empty($p['file_jurnal']) ? BASE_URL.'/'.$p['file_jurnal'] : '' ?>"
            data-file_bukti="<?= !empty($p['file_bukti_bayar']) ? BASE_URL.'/'.$p['file_bukti_bayar'] : '' ?>"
            data-referensi="<?= htmlspecialchars($p['referensi'] ?? '', ENT_QUOTES) ?>"
            data-created="<?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>">
          <td class="py-4 px-4 text-xs text-slate-400 font-medium"><?= $offset + $i + 1 ?></td>
          <td class="py-4 px-4 max-w-sm">
            <div class="font-bold text-sm text-slate-800 dark:text-white line-clamp-2 mb-1">
              <?= htmlspecialchars($p['judul_artikel']) ?>
            </div>
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
              <span class="font-semibold text-[#8c0c4c] dark:text-[#f06ea4]"><?= htmlspecialchars($p['mhs_nama'] ?? '-') ?></span>
              <span class="text-slate-300">·</span>
              <span class="text-slate-500 font-medium"><?= htmlspecialchars($p['mhs_nim'] ?? '') ?></span>
            </div>
            <?php if (!empty($p['prodi_nama'])): ?>
            <span class="inline-block mt-1 text-[10px] bg-slate-100 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded font-medium"><?= htmlspecialchars($p['prodi_nama']) ?></span>
            <?php endif; ?>
          </td>
          <td class="py-4 px-4">
            <div class="mb-1.5"><?= getKategoriBadge($p['kategori_publikasi'] ?? 'Lainnya') ?></div>
            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200 line-clamp-1"><?= htmlspecialchars($p['nama_jurnal'] ?? '-') ?></div>
            <?php if (!empty($p['doi'])): ?>
            <div class="text-xs text-slate-400 font-mono mt-0.5 truncate max-w-[200px]" title="<?= htmlspecialchars($p['doi']) ?>">DOI: <?= htmlspecialchars($p['doi']) ?></div>
            <?php endif; ?>
            <div class="flex flex-wrap gap-1 mt-1">
              <?php if (!empty($p['volume'])): ?>
              <span class="text-[10px] bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded font-medium">Vol. <?= htmlspecialchars($p['volume']) ?></span>
              <?php endif; ?>
              <?php if (!empty($p['nomor_terbit'])): ?>
              <span class="text-[10px] bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded font-medium">No. <?= htmlspecialchars($p['nomor_terbit']) ?></span>
              <?php endif; ?>
              <?php if (!empty($p['halaman'])): ?>
              <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 px-1.5 py-0.5 rounded font-medium">Hal. <?= htmlspecialchars($p['halaman']) ?></span>
              <?php endif; ?>
            </div>
          </td>
          <td class="py-4 px-4">
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold border <?= $statusColor[$p['status_publikasi']] ?? $defaultStatusColor ?>">
              <?= htmlspecialchars($p['status_publikasi'] ?? '-') ?>
            </span>
          </td>
          <td class="py-4 px-4">
            <div class="font-bold text-sm text-slate-700 dark:text-slate-200"><?= $p['tahun_terbit'] ?: '-' ?></div>
            <div class="text-[10px] text-slate-400 mt-0.5"><?= date('d/m/Y', strtotime($p['created_at'])) ?></div>
          </td>
          <td class="py-4 px-4 text-right">
            <div class="flex gap-1 justify-end">
              <button type="button" onclick="showDetail(this.closest('tr'))" title="Lihat Detail"
                class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-[#8c0c4c] hover:bg-[#8c0c4c]/10 dark:hover:bg-[#8c0c4c]/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              </button>
              <?php if (!empty($p['link_artikel'])): ?>
              <a href="<?= htmlspecialchars($p['link_artikel']) ?>" target="_blank" rel="noopener"
                 class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition" title="Buka Artikel">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
              </a>
              <?php endif; ?>
              <?php if (!empty($p['file_jurnal'])): ?>
              <a href="<?= BASE_URL ?>/<?= htmlspecialchars($p['file_jurnal']) ?>" target="_blank"
                 class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition" title="Unduh File Jurnal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </a>
              <?php endif; ?>
              <form method="POST" action="aksi_pub_admin" onsubmit="return confirm('Hapus publikasi ini? Tindakan tidak bisa dibatalkan.')">
                <input type="hidden" name="type" value="mhs">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <input type="hidden" name="back" value="publikasi_mahasiswa">
                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition" title="Hapus">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- ─── Pagination ─────────────────────────────────────────────────────── -->
<?php if ($totalPages > 1): ?>
<div class="flex items-center justify-center gap-2 mb-8">
  <?php if ($page > 1): ?>
  <a href="<?= pubUrl(['page' => $page - 1]) ?>" class="px-4 py-2 rounded-xl text-sm font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-[#8c0c4c] hover:text-[#8c0c4c] transition-colors">← Prev</a>
  <?php endif; ?>
  <?php
  $start = max(1, $page - 2); $end = min($totalPages, $page + 2);
  for ($pg = $start; $pg <= $end; $pg++): ?>
  <a href="<?= pubUrl(['page' => $pg]) ?>"
     class="w-9 h-9 flex items-center justify-center rounded-xl text-sm font-bold transition-colors <?= $pg === $page ? 'bg-[#8c0c4c] text-white shadow-md' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-[#8c0c4c] hover:text-[#8c0c4c]' ?>">
    <?= $pg ?>
  </a>
  <?php endfor; ?>
  <?php if ($page < $totalPages): ?>
  <a href="<?= pubUrl(['page' => $page + 1]) ?>" class="px-4 py-2 rounded-xl text-sm font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-[#8c0c4c] hover:text-[#8c0c4c] transition-colors">Next →</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ─── Modal Detail ──────────────────────────────────────────────────── -->
<div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
  <div onclick="closeDetail()" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
  <div class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-3xl border border-slate-200 dark:border-slate-700 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
    <!-- Header Modal -->
    <div class="sticky top-0 z-10 bg-white dark:bg-slate-800 flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700 rounded-t-3xl">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] text-white flex items-center justify-center shadow">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
          <h3 class="font-display font-bold text-base text-slate-800 dark:text-white">Detail Publikasi Mahasiswa</h3>
          <p class="text-xs text-slate-400" id="dmSubtitle"></p>
        </div>
      </div>
      <button onclick="closeDetail()" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 transition text-lg font-bold">✕</button>
    </div>
    <!-- Body -->
    <div class="p-6 space-y-5">
      <!-- Judul + Status -->
      <div class="flex items-start justify-between gap-4">
        <h4 id="dmJudul" class="font-display font-bold text-lg text-slate-800 dark:text-white leading-snug flex-1"></h4>
        <div class="flex flex-col items-end gap-1.5 shrink-0">
          <span id="dmKategori" class="px-3 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200"></span>
          <span id="dmStatus" class="px-3 py-1 rounded-xl text-xs font-bold border"></span>
        </div>
      </div>

      <!-- Info Mahasiswa -->
      <div class="bg-gradient-to-r from-[#8c0c4c]/5 to-[#a3155b]/5 dark:from-[#8c0c4c]/15 dark:to-[#a3155b]/15 rounded-2xl p-4">
        <div class="text-[10px] font-bold uppercase tracking-wider text-[#8c0c4c] dark:text-[#f06ea4] mb-2">Identitas Mahasiswa</div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div><div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Nama</div><div id="dmNama" class="text-sm font-bold text-slate-800 dark:text-white"></div></div>
          <div><div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">NIM</div><div id="dmNim" class="text-sm font-mono font-semibold text-slate-700 dark:text-slate-200"></div></div>
          <div><div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Program Studi</div><div id="dmProdi" class="text-sm font-semibold text-slate-700 dark:text-slate-200"></div></div>
        </div>
        <div id="dmPembimbingWrap" class="mt-3">
          <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Dosen Pembimbing / Pendamping</div>
          <div id="dmPembimbing" class="text-sm font-semibold text-slate-700 dark:text-slate-200"></div>
        </div>
      </div>

      <!-- Bibliografi -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-3">Bibliografi Jurnal</div>
          <div class="space-y-2">
            <div><div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Nama Jurnal</div><div id="dmJurnal" class="text-sm font-bold text-slate-800 dark:text-white"></div></div>
            <div class="grid grid-cols-3 gap-2">
              <div><div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Volume</div><div id="dmVolume" class="text-sm font-semibold text-blue-600 dark:text-blue-400"></div></div>
              <div><div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Nomor</div><div id="dmNomor" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400"></div></div>
              <div><div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Halaman</div><div id="dmHalaman" class="text-sm font-semibold text-slate-700 dark:text-slate-200"></div></div>
            </div>
            <div><div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Tahun Terbit</div><div id="dmTahun" class="text-sm font-bold text-slate-800 dark:text-white"></div></div>
          </div>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4 space-y-2">
          <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-3">Identifikasi</div>
          <div><div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">DOI</div><div id="dmDoi" class="text-xs font-mono text-slate-700 dark:text-slate-200 break-all"></div></div>
          <div><div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Penulis / Rekan Penulis</div><div id="dmPenulis" class="text-sm text-slate-700 dark:text-slate-200"></div></div>
          <div><div class="text-[10px] text-slate-400 uppercase tracking-wider mb-0.5">Kata Kunci</div><div id="dmKataKunci" class="text-xs text-slate-500 dark:text-slate-400 italic"></div></div>
        </div>
      </div>

      <!-- Abstrak -->
      <div id="dmAbstrakWrap" class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Abstrak</div>
        <p id="dmAbstrak" class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed"></p>
      </div>

      <!-- Referensi -->
      <div id="dmReferensiWrap" class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Referensi</div>
        <p id="dmReferensi" class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap"></p>
      </div>

      <!-- Links & Files -->
      <div class="flex flex-wrap gap-3" id="dmLinksWrap"></div>

      <!-- Footer info -->
      <div class="text-xs text-slate-400 text-right border-t border-slate-100 dark:border-slate-700 pt-3">
        Ditambahkan: <span id="dmCreated" class="font-semibold"></span>
      </div>
    </div>
  </div>
</div>

<script>
const statusColor = {
  'Publish':        'bg-emerald-100 text-emerald-700 border-emerald-200',
  'ACC / Diterima': 'bg-blue-100 text-blue-700 border-blue-200',
  'Sedang Review':  'bg-amber-100 text-amber-700 border-amber-200',
};
function showDetail(row) {
  const d = row.dataset;
  document.getElementById('dmSubtitle').textContent = (d.nim ? d.nim + ' · ' : '') + (d.prodi || '');
  document.getElementById('dmJudul').textContent    = d.judul || '-';
  document.getElementById('dmKategori').textContent = d.kategori || 'Lainnya';
  const stEl = document.getElementById('dmStatus');
  stEl.textContent = d.status || '-';
  stEl.className = 'shrink-0 px-3 py-1 rounded-xl text-xs font-bold border ' + (statusColor[d.status] || 'bg-slate-100 text-slate-600 border-slate-200');
  document.getElementById('dmNama').textContent      = d.nama || '-';
  document.getElementById('dmNim').textContent       = d.nim  || '-';
  document.getElementById('dmProdi').textContent     = d.prodi|| '-';
  const pbWrap = document.getElementById('dmPembimbingWrap');
  if (d.pembimbing) { document.getElementById('dmPembimbing').textContent = d.pembimbing; pbWrap.classList.remove('hidden'); }
  else { pbWrap.classList.add('hidden'); }
  document.getElementById('dmJurnal').textContent   = d.jurnal  || '-';
  document.getElementById('dmVolume').textContent   = d.volume  || '-';
  document.getElementById('dmNomor').textContent    = d.nomor   || '-';
  document.getElementById('dmHalaman').textContent  = d.halaman || '-';
  document.getElementById('dmTahun').textContent    = d.tahun   || '-';
  document.getElementById('dmDoi').textContent      = d.doi     || '-';
  document.getElementById('dmPenulis').textContent  = d.penulis || '-';
  document.getElementById('dmKataKunci').textContent= d.kata_kunci || '-';
  const absWrap = document.getElementById('dmAbstrakWrap');
  if (d.abstrak) { document.getElementById('dmAbstrak').textContent = d.abstrak; absWrap.classList.remove('hidden'); }
  else { absWrap.classList.add('hidden'); }
  const refWrap = document.getElementById('dmReferensiWrap');
  if (d.referensi) { document.getElementById('dmReferensi').textContent = d.referensi; refWrap.classList.remove('hidden'); }
  else { refWrap.classList.add('hidden'); }
  document.getElementById('dmCreated').textContent  = d.created || '-';
  // Links
  const lw = document.getElementById('dmLinksWrap'); lw.innerHTML = '';
  if (d.link) lw.innerHTML += `<a href="${d.link}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>Buka Artikel</a>`;
  if (d.file) lw.innerHTML += `<a href="${d.file}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>File Jurnal</a>`;
  if (d.file_bukti) lw.innerHTML += `<a href="${d.file_bukti}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-bold transition"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Bukti Bayar</a>`;
  // Show modal
  const m = document.getElementById('detailModal');
  m.classList.remove('hidden'); m.classList.add('flex');
  document.body.style.overflow = 'hidden';
}
function closeDetail() {
  const m = document.getElementById('detailModal');
  m.classList.add('hidden'); m.classList.remove('flex');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
