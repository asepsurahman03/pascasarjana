<?php
$pageTitle = 'Publikasi Mahasiswa';
require_once __DIR__ . '/../includes/header.php';

// ─── Filter & Search ──────────────────────────────────────────────────────────
$filterStatus = $_GET['status']   ?? '';
$filterProdi  = (int)($_GET['prodi_id'] ?? 0);
$filterTahun  = $_GET['tahun']    ?? '';
$searchQ      = trim($_GET['q']   ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 20;

$params = []; $where = ['1=1'];
if ($filterStatus) { $where[] = 'mp.status_publikasi = ?'; $params[] = $filterStatus; }
if ($filterProdi)  { $where[] = 'm.prodi_id = ?';          $params[] = $filterProdi;  }
if ($filterTahun)  { $where[] = 'mp.tahun_terbit = ?';     $params[] = (int)$filterTahun; }
if ($searchQ)      {
    $where[] = '(mp.judul_artikel LIKE ? OR mp.nama_jurnal LIKE ? OR m.nama LIKE ? OR m.nim LIKE ?)';
    $params[] = "%$searchQ%"; $params[] = "%$searchQ%"; $params[] = "%$searchQ%"; $params[] = "%$searchQ%";
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
    SUM(mp.status_publikasi='ACC / Diterima') AS acc,
    SUM(mp.status_publikasi='Sedang Review') AS review,
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
    global $filterStatus, $filterProdi, $filterTahun, $searchQ;
    $p = array_filter(array_merge([
        'status'   => $filterStatus,
        'prodi_id' => $filterProdi ?: '',
        'tahun'    => $filterTahun,
        'q'        => $searchQ,
    ], $extra));
    return 'publikasi_mahasiswa' . ($p ? '?' . http_build_query($p) : '');
}

$exportUrl = 'export_pub_mhs_admin' . ($filterStatus||$filterProdi||$filterTahun||$searchQ
    ? '?' . http_build_query(array_filter(['status'=>$filterStatus,'prodi_id'=>$filterProdi,'tahun'=>$filterTahun,'q'=>$searchQ]))
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
    ['label'=>'Total Publikasi', 'val'=>$stats['total'],   'color'=>'from-[#8c0c4c] to-[#a3155b]',  'bg'=>'bg-[#8c0c4c]/5 dark:bg-[#8c0c4c]/20', 'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ['label'=>'Publish',        'val'=>$stats['publish'],  'color'=>'from-emerald-500 to-teal-600',  'bg'=>'bg-emerald-50 dark:bg-emerald-900/20','icon'=>'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
    ['label'=>'ACC / Diterima', 'val'=>$stats['acc'],      'color'=>'from-blue-500 to-indigo-600',   'bg'=>'bg-blue-50 dark:bg-blue-900/20',      'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
    ['label'=>'Sedang Review',  'val'=>$stats['review'],   'color'=>'from-amber-500 to-orange-600',  'bg'=>'bg-amber-50 dark:bg-amber-900/20',    'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['label'=>'Mhs. Berkontribusi','val'=>$stats['jml_mhs'],'color'=>'from-violet-500 to-purple-600','bg'=>'bg-violet-50 dark:bg-violet-900/20',  'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
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
      <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="Cari judul, jurnal, NIM, nama mahasiswa..."
             class="w-full pl-9 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/15 text-slate-800 dark:text-white placeholder-slate-400 transition-all">
    </div>
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
    <?php if ($filterStatus||$filterProdi||$filterTahun||$searchQ): ?>
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
      <?php if ($filterStatus||$filterProdi||$filterTahun||$searchQ): ?>
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
          <th class="text-left py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Jurnal / Bibliografi</th>
          <th class="text-left py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="text-left py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Tahun</th>
          <th class="text-right py-3.5 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
        <?php foreach ($pubs as $i => $p): ?>
        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
