<?php
$pageTitle = 'Penelitian & Publikasi';
require_once 'header.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// --- Dosen identity dari session (atau fallback hardcode sementara) ---
$dosenId = $_SESSION['user_id'] ?? 0;
$dosenRow = dbQueryOne("SELECT * FROM dosen WHERE id = ?", [$dosenId]);
// Fallback jika belum login sebagai dosen yang ada di tabel dosen (cek via username/email)
if (!$dosenRow && isset($_SESSION['username'])) {
    $dosenRow = dbQueryOne("SELECT * FROM dosen WHERE nidn = ? OR email = ?", [$_SESSION['username'], $_SESSION['username']]);
}
if (!$dosenRow) {
    // Ambil dosen pertama sebagai fallback untuk demo
    $dosenRow = dbQueryOne("SELECT * FROM dosen LIMIT 1", []);
}
$dosenId = $dosenRow['id'] ?? 0;

// --- Flash message ---
$flash = getFlash();

// --- Filter & Search ---
$searchQ        = trim($_GET['q'] ?? '');
$filterYear     = (int)($_GET['year'] ?? 0);
$filterStatus   = trim($_GET['status'] ?? '');
$filterKategori = trim($_GET['kategori'] ?? '');

// --- Build query ---
$where = ['dosen_id = ?'];
$params = [$dosenId];
if ($searchQ) {
    $where[] = '(judul_artikel LIKE ? OR nama_jurnal LIKE ? OR kata_kunci LIKE ? OR penulis LIKE ? OR kategori_publikasi LIKE ?)';
    $params = array_merge($params, ["%$searchQ%", "%$searchQ%", "%$searchQ%", "%$searchQ%", "%$searchQ%"]);
}
if ($filterYear) {
    $where[] = 'tahun_terbit = ?';
    $params[] = $filterYear;
}
if ($filterStatus) {
    $where[] = 'LOWER(status_publikasi) LIKE ?';
    $params[] = "%$filterStatus%";
}
if ($filterKategori) {
    $where[] = 'LOWER(kategori_publikasi) LIKE ?';
    $params[] = "%" . strtolower($filterKategori) . "%";
}
$whereStr = implode(' AND ', $where);

$publikasi = dbQuery("SELECT * FROM dosen_publikasi WHERE $whereStr ORDER BY tahun_terbit DESC, created_at DESC", $params);

// --- Stats ---
$total       = dbQueryOne("SELECT COUNT(*) c FROM dosen_publikasi WHERE dosen_id=?", [$dosenId])['c'] ?? 0;
$publish     = dbQueryOne("SELECT COUNT(*) c FROM dosen_publikasi WHERE dosen_id=? AND LOWER(status_publikasi) LIKE 'publish%'", [$dosenId])['c'] ?? 0;
$acc         = dbQueryOne("SELECT COUNT(*) c FROM dosen_publikasi WHERE dosen_id=? AND LOWER(status_publikasi) LIKE 'acc%'", [$dosenId])['c'] ?? 0;
$review      = dbQueryOne("SELECT COUNT(*) c FROM dosen_publikasi WHERE dosen_id=? AND LOWER(status_publikasi) LIKE '%review%'", [$dosenId])['c'] ?? 0;
$scopusCount = dbQueryOne("SELECT COUNT(*) c FROM dosen_publikasi WHERE dosen_id=? AND LOWER(kategori_publikasi) LIKE '%scopus%'", [$dosenId])['c'] ?? 0;
$sintaCount  = dbQueryOne("SELECT COUNT(*) c FROM dosen_publikasi WHERE dosen_id=? AND LOWER(kategori_publikasi) LIKE '%sinta%'", [$dosenId])['c'] ?? 0;

// --- Years for filter ---
$years = dbQuery("SELECT DISTINCT tahun_terbit FROM dosen_publikasi WHERE dosen_id=? AND tahun_terbit IS NOT NULL ORDER BY tahun_terbit DESC", [$dosenId]);
?>

<?php // Override page padding to be full-width ?>
<style>
  .sd-title-link::before { content:''; position:absolute; inset:0; }
  .sd-card { position: relative; }
</style>

<?php if ($flash): ?>
<div class="mb-4 p-4 rounded-xl text-sm font-bold flex items-center gap-3 shadow-sm <?= $flash['type']==='error' ? 'bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/50' : 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50' ?>">
  <span><?= $flash['type']==='error' ? '❌' : '✅' ?></span>
  <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>

<!-- Page Header -->
<div class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shadow-sm -mx-4 -mt-4 md:-mx-6 md:-mt-6 px-4 md:px-6 pt-5 pb-6 mb-6">
  <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-end justify-between">
    <div>
      <h1 class="text-2xl font-display font-bold text-slate-800 dark:text-white">Penelitian & Publikasi</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola daftar karya ilmiah dan publikasi akademik Anda</p>
    </div>
    <div class="flex items-center gap-2.5 flex-wrap">
      <!-- Stats pills -->
      <span class="text-xs font-bold px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-full border border-slate-200 dark:border-slate-700"><?= $total ?> Total</span>
      <span class="text-xs font-bold px-3 py-1.5 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 rounded-full border border-amber-300 dark:border-amber-700/60 shadow-xs flex items-center gap-1">
        ⭐ Scopus: <strong><?= $scopusCount ?></strong>
      </span>
      <span class="text-xs font-bold px-3 py-1.5 bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 rounded-full border border-blue-300 dark:border-blue-700/60 shadow-xs flex items-center gap-1">
        🏅 SINTA: <strong><?= $sintaCount ?></strong>
      </span>
      <span class="text-xs font-bold px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full border border-emerald-200 dark:border-emerald-800/50"><?= $publish ?> Publish</span>
      
      <?php if ($total > 0): ?>
      <a href="export_publikasi_excel<?= ($searchQ||$filterYear||$filterStatus||$filterKategori) ? '?'.http_build_query(array_filter(['q'=>$searchQ,'year'=>$filterYear,'status'=>$filterStatus,'kategori'=>$filterKategori])) : '' ?>"
         id="btnExportExcelDosen"
         target="_blank" rel="noopener"
         class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-white text-xs transition hover:shadow-lg hover:-translate-y-0.5 bg-emerald-600 hover:bg-emerald-700 ml-1"
         title="Download data publikasi sebagai file Excel (.xlsx)">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        Export Excel
      </a>
      <?php endif; ?>
      <button onclick="document.getElementById('addModal').classList.remove('hidden')"
              class="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-white text-xs transition hover:shadow-lg hover:-translate-y-0.5 bg-gradient-to-r from-[#8c0c4c] to-[#c41e73]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Publikasi
      </button>
    </div>
  </div>

  <!-- Search bar -->
  <form method="GET" class="mt-4 flex gap-2">
    <div class="relative flex-1 max-w-lg">
      <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
      <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="Cari judul, jurnal, kata kunci, penulis, indeksasi..."
             class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/15 transition">
    </div>
    <button type="submit" class="px-4 py-2.5 bg-[#8c0c4c] text-white rounded-xl text-sm font-bold hover:bg-[#a3155b] transition">Cari</button>
    <?php if ($searchQ || $filterYear || $filterStatus || $filterKategori): ?>
    <a href="penelitian" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-bold hover:bg-slate-200 transition">Reset</a>
    <?php endif; ?>
  </form>
</div>

<!-- Main Layout -->
<div class="flex gap-6 items-start">

  <!-- Sidebar Filter -->
  <aside class="hidden lg:block w-56 shrink-0 sticky top-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm mb-4">
      <div class="bg-[#8c0c4c] px-4 py-3 flex items-center justify-between">
        <h3 class="text-white text-sm font-bold uppercase tracking-wide">Filter</h3>
        <?php if ($filterStatus || $filterKategori || $filterYear): ?>
        <a href="penelitian" class="text-[11px] text-white/80 hover:text-white underline">Reset</a>
        <?php endif; ?>
      </div>

      <!-- By Indeksasi / Kategori -->
      <div class="p-4 border-b border-slate-100 dark:border-slate-700">
        <h4 class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2.5 flex items-center justify-between">
          <span>Indeksasi</span>
          <span class="text-[10px] text-slate-400 font-normal">Kategori</span>
        </h4>
        <div class="space-y-1">
          <?php
          $kats = [
            ['val'=>'',        'label'=>'Semua Indeksasi'],
            ['val'=>'scopus',  'label'=>'⭐ Scopus'],
            ['val'=>'sinta',   'label'=>'🏅 SINTA'],
            ['val'=>'internasional','label'=>'🌐 Jurnal Internasional'],
            ['val'=>'nasional', 'label'=>'🏛️ Jurnal Nasional'],
            ['val'=>'prosiding','label'=>'📑 Prosiding'],
            ['val'=>'hki',      'label'=>'💡 HKI / Paten / Buku'],
          ];
          foreach ($kats as $kt):
            $active = ($filterKategori === $kt['val']);
            $href   = '?'.http_build_query(array_filter(['q'=>$searchQ,'year'=>$filterYear,'status'=>$filterStatus,'kategori'=>$kt['val']]));
          ?>
          <a href="<?= $href ?>" class="flex items-center justify-between group rounded-lg px-2.5 py-1.5 <?= $active ? 'bg-[#8c0c4c]/10 dark:bg-[#f06ea4]/15 text-[#8c0c4c] dark:text-[#f06ea4] font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60' ?> text-xs transition-colors">
            <span><?= $kt['label'] ?></span>
            <?php if ($active): ?><span class="w-1.5 h-1.5 rounded-full bg-[#8c0c4c] dark:bg-[#f06ea4]"></span><?php endif; ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- By Status -->
      <div class="p-4 border-b border-slate-100 dark:border-slate-700">
        <h4 class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Status</h4>
        <div class="space-y-1.5">
          <?php
          $statuses = [
            ['val'=>'',       'label'=>'Semua Status',  'count'=>$total,   'col'=>'text-slate-600'],
            ['val'=>'publish','label'=>'Sudah Publish',  'count'=>$publish, 'col'=>'text-emerald-600'],
            ['val'=>'acc',    'label'=>'ACC / Diterima', 'count'=>$acc,     'col'=>'text-blue-600'],
            ['val'=>'review', 'label'=>'Sedang Review',  'count'=>$review,  'col'=>'text-amber-600'],
          ];
          foreach ($statuses as $st):
            $active = ($filterStatus === $st['val']);
            $href   = '?'.http_build_query(array_filter(['q'=>$searchQ,'year'=>$filterYear,'kategori'=>$filterKategori,'status'=>$st['val']]));
          ?>
          <a href="<?= $href ?>" class="flex items-center justify-between group rounded-lg px-2.5 py-1.5 <?= $active ? 'bg-[#8c0c4c]/10 dark:bg-[#f06ea4]/15 text-[#8c0c4c] dark:text-[#f06ea4] font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/60' ?> text-xs transition-colors">
            <span><?= $st['label'] ?></span>
            <span class="text-[11px] px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 <?= $st['col'] ?> font-bold"><?= $st['count'] ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <!-- By Year -->
      <?php if ($years): ?>
      <div class="p-4">
        <h4 class="text-xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Tahun</h4>
        <div class="space-y-1.5">
          <?php foreach ($years as $y):
            $active = ($filterYear === (int)$y['tahun_terbit']);
            $href   = '?'.http_build_query(array_filter(['q'=>$searchQ,'status'=>$filterStatus,'year'=>$active ? 0 : $y['tahun_terbit']]));
          ?>
          <a href="<?= $href ?>" class="flex items-center justify-between rounded-lg px-2.5 py-2 <?= $active ? 'bg-[#8c0c4c]/8 text-[#8c0c4c] dark:text-[#f06ea4] font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?> text-sm transition">
            <?= $y['tahun_terbit'] ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="flex-1 min-w-0 space-y-4">
    <?php if (empty($publikasi)): ?>
    <div class="bg-white dark:bg-slate-800 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 p-16 text-center">
      <div class="w-16 h-16 mx-auto rounded-2xl bg-[#8c0c4c]/10 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-[#8c0c4c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      </div>
      <p class="font-bold text-slate-700 dark:text-slate-300 text-lg mb-2">Belum ada publikasi</p>
      <p class="text-sm text-slate-400 dark:text-slate-500 mb-5">Klik "Tambah Publikasi" atau tempel DOI untuk mengisi data otomatis.</p>
      <button onclick="document.getElementById('addModal').classList.remove('hidden')"
              class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#c41e73] text-white rounded-xl text-sm font-bold hover:shadow-lg transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Publikasi Pertama
      </button>
    </div>
    <?php else: ?>
    <?php foreach ($publikasi as $pub):
      $doi = trim($pub['doi'] ?? '');
      $kws = $pub['kata_kunci'] ? array_map('trim', explode(',', $pub['kata_kunci'])) : [];
      // Status badge
      $sp = strtolower($pub['status_publikasi'] ?? '');
      if (str_contains($sp,'publish')) { $badgeCls='bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800'; $dotCls='bg-emerald-500'; }
      elseif (str_contains($sp,'acc')) { $badgeCls='bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800'; $dotCls='bg-blue-500'; }
      elseif (str_contains($sp,'review')) { $badgeCls='bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800'; $dotCls='bg-amber-500'; }
      else { $badgeCls='bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600'; $dotCls='bg-slate-400'; }
    ?>
    <article class="sd-card bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 hover:border-[#8c0c4c]/40 dark:hover:border-[#8c0c4c]/50 hover:shadow-md transition-all">
      <!-- Row 1: Meta -->
      <div class="flex items-center gap-2.5 mb-3 flex-wrap">
        <?= getKategoriBadge($pub['kategori_publikasi'] ?? 'Lainnya') ?>
        <?php if ($pub['nama_jurnal']): ?>
        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 truncate max-w-[50%]"><?= htmlspecialchars($pub['nama_jurnal']) ?></span>
        <?php endif; ?>
        <?php if ($pub['tahun_terbit']): ?>
        <span class="text-xs text-slate-400">• <?= $pub['tahun_terbit'] ?></span>
        <?php endif; ?>
        <span class="ml-auto inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-md <?= $badgeCls ?>">
          <span class="w-1.5 h-1.5 rounded-full <?= $dotCls ?>"></span>
          <?= htmlspecialchars($pub['status_publikasi']) ?>
        </span>
      </div>

      <!-- Row 2: Title -->
      <h2 class="mb-2">
        <a href="detail_publikasi?id=<?= $pub['id'] ?>" class="sd-title-link before:absolute before:inset-0 text-lg font-bold text-slate-800 dark:text-slate-100 leading-snug hover:text-[#8c0c4c] dark:hover:text-[#f06ea4] transition-colors">
            <?= htmlspecialchars($pub['judul_artikel']) ?>
        </a>
      </h2>

      <!-- Row 3: Authors -->
      <?php if ($pub['penulis']): ?>
      <p class="text-sm text-slate-600 dark:text-slate-400 mb-2">
        <?php
          $authors = array_map('trim', explode(',', $pub['penulis']));
          foreach ($authors as $idx => $author): ?>
          <?php if ($idx > 0): ?><span class="text-slate-400 mx-1">,</span><?php endif; ?>
          <span class="<?= $idx === 0 ? 'font-semibold text-slate-800 dark:text-slate-300' : '' ?>"><?= htmlspecialchars($author) ?></span>
        <?php endforeach; ?>
      </p>
      <?php endif; ?>

      <!-- Row 4: DOI -->
      <?php if ($doi): ?>
      <p class="text-xs text-slate-400 dark:text-slate-500 mb-3 flex items-center gap-1.5 font-mono relative z-10">
        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        <a href="https://doi.org/<?= htmlspecialchars($doi) ?>" target="_blank" class="hover:text-[#8c0c4c] dark:hover:text-[#f06ea4] transition-colors">
          https://doi.org/<?= htmlspecialchars($doi) ?>
        </a>
      </p>
      <?php endif; ?>

      <!-- Row 5: Abstract -->
      <?php if ($pub['abstrak']): ?>
      <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed mb-4 line-clamp-2">
        <?= htmlspecialchars(mb_strimwidth($pub['abstrak'], 0, 280, '...')) ?>
      </p>
      <?php endif; ?>

      <!-- Row 6: Keywords -->
      <?php if (!empty($kws)): ?>
      <div class="flex flex-wrap gap-1.5 mb-4">
        <?php foreach (array_slice($kws, 0, 5) as $kw): ?>
        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700/60 px-2.5 py-0.5 rounded border border-slate-200 dark:border-slate-600"><?= htmlspecialchars($kw) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Row 7: Actions -->
      <div class="flex flex-wrap items-center gap-2 relative z-10">
        <?php if ($pub['link_artikel']): ?>
        <a href="<?= htmlspecialchars($pub['link_artikel']) ?>" target="_blank"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-[#8c0c4c] hover:bg-[#a3155b] px-4 py-2 rounded-lg transition-colors shadow-sm">
          Kunjungi Jurnal ↗
        </a>
        <?php endif; ?>
        <?php if ($pub['file_jurnal']): ?>
        <a href="../<?= htmlspecialchars($pub['file_jurnal']) ?>" target="_blank"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-[#8c0c4c] dark:text-[#f06ea4] border border-[#8c0c4c]/30 hover:bg-[#8c0c4c]/5 px-4 py-2 rounded-lg transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Download PDF
        </a>
        <?php endif; ?>

        <!-- Date -->
        <span class="ml-auto text-[11px] text-slate-400 dark:text-slate-500">
          Ditambahkan <?= date('d M Y', strtotime($pub['created_at'])) ?>
        </span>

        <!-- Hapus -->
        <form action="aksi_publikasi_dosen" method="POST" onsubmit="return confirm('Yakin ingin menghapus publikasi ini?')" class="inline">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $pub['id'] ?>">
          <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-red-500 dark:text-red-400 hover:text-white hover:bg-red-500 dark:hover:bg-red-600 border border-red-200 dark:border-red-800/60 hover:border-red-500 px-3 py-1.5 rounded-lg transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Hapus
          </button>
        </form>
      </div>
    </article>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- ===== ADD MODAL ===== -->
<div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
  <div class="relative w-full max-w-3xl bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">

    <!-- Modal header -->
    <div class="bg-gradient-to-r from-[#6b0a3a] via-[#8c0c4c] to-[#c41e73] px-7 py-5 relative overflow-hidden">
      <div class="absolute inset-0 opacity-[.07]" style="background-image:radial-gradient(circle at 2px 2px,white 1px,transparent 0);background-size:18px 18px;"></div>
      <div class="relative flex justify-between items-start">
        <div>
          <h3 class="text-xl font-bold text-white">Tambah Karya Ilmiah Baru</h3>
          <p class="text-pink-100/75 text-xs mt-1">Lengkapi data artikel sesuai standar metadata jurnal akademik</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-white/70 hover:text-white hover:bg-white/20 rounded-full p-2 transition-all ml-4">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    <!-- Modal body -->
    <form action="aksi_publikasi_dosen" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="insert">
      <input type="hidden" name="dosen_id" value="<?= $dosenId ?>">

      <!-- ✨ DOI Quick-Fill Banner -->
      <div class="px-7 sm:px-8 pt-5 pb-0">
        <div class="bg-gradient-to-r from-[#8c0c4c]/8 to-purple-500/5 dark:from-[#8c0c4c]/20 dark:to-purple-900/10 border border-[#8c0c4c]/20 dark:border-[#8c0c4c]/30 rounded-2xl p-4">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-[#8c0c4c] to-[#c41e73] flex items-center justify-center flex-shrink-0">
              <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <p class="text-xs font-bold text-[#8c0c4c] dark:text-[#f06ea4]">Isi Otomatis via DOI</p>
            <span class="ml-auto text-[10px] text-slate-400 dark:text-slate-500 font-medium">Opsional — lewati jika tidak punya DOI</span>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3 pl-8">Tempel DOI lalu klik <strong>Tarik Data</strong>. Judul, jurnal, penulis, tahun, abstrak & referensi otomatis terisi.</p>
          <div class="flex gap-2 pl-8">
            <input type="text" id="doi_input_dosen" name="doi" placeholder="Contoh: 10.1016/j.jbusres.2023.114132"
              class="flex-1 bg-white dark:bg-slate-800 border border-[#8c0c4c]/25 dark:border-[#8c0c4c]/40 text-slate-900 dark:text-white rounded-xl px-4 py-2.5 text-sm placeholder-slate-400 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/15 transition-all">
            <button type="button" id="btnFetchDoiDosen" class="px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#c41e73] hover:from-[#a3155b] hover:to-[#d4217f] text-white rounded-xl text-xs font-bold transition-all flex-shrink-0 flex items-center gap-1.5 shadow-md hover:shadow-lg hover:-translate-y-0.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              Tarik Data
            </button>
          </div>
        </div>
      </div>

      <!-- Form Fields -->
      <div class="p-7 sm:p-8 space-y-6 max-h-[55vh] overflow-y-auto">
        <?php
        $inputCls = 'w-full bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-600 text-slate-900 dark:text-white rounded-xl px-4 py-3 text-sm placeholder-slate-400 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/15 transition-all';
        $sections = [
          'Identitas Artikel' => [
            ['name'=>'judul_artikel',      'label'=>'Judul Artikel *', 'type'=>'text', 'req'=>true, 'placeholder'=>'Judul lengkap artikel...', 'full'=>true],
            ['name'=>'kategori_publikasi', 'label'=>'Kategori / Indeksasi Jurnal *', 'type'=>'select', 'req'=>true, 'options'=>[''=>'-- Pilih Kategori / Indeksasi Yang Sesuai --'] + array_combine(getKategoriPublikasiList(), getKategoriPublikasiList()), 'full'=>false],
            ['name'=>'nama_jurnal',        'label'=>'Nama Jurnal / Prosiding', 'type'=>'text', 'req'=>false, 'placeholder'=>'Nama jurnal termasuk Volume & Issue', 'full'=>false],
            ['name'=>'kata_kunci',         'label'=>'Kata Kunci', 'type'=>'text', 'req'=>false, 'placeholder'=>'Pisahkan dengan koma', 'full'=>false],
            ['name'=>'link_artikel',       'label'=>'URL / Link Artikel', 'type'=>'url', 'req'=>false, 'placeholder'=>'https://...', 'full'=>false],
            ['name'=>'status_publikasi',    'label'=>'Status Publikasi', 'type'=>'select', 'req'=>true, 'options'=>['Publish'=>'✅ Sudah Publish','ACC / Diterima'=>'🟦 ACC / Diterima','Sedang Review'=>'⏳ Sedang Review'], 'full'=>false],
          ],
          'Bibliografi' => [
            ['name'=>'tahun_terbit', 'label'=>'Tahun Terbit', 'type'=>'number', 'req'=>false, 'placeholder'=>date('Y'), 'full'=>false, 'min'=>1900, 'max'=>(int)date('Y')+2],
          ],
          'Penulis' => [
            ['name'=>'penulis', 'label'=>'Daftar Penulis', 'type'=>'dynamic_authors', 'req'=>false, 'placeholder'=>'', 'full'=>false],
          ],
        ];

        foreach ($sections as $sectionTitle => $fields):
        ?>
        <div>
          <p class="text-[10px] font-bold text-[#8c0c4c] dark:text-[#f06ea4] uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="flex-1 h-px bg-[#8c0c4c]/20"></span> <?= $sectionTitle ?> <span class="flex-1 h-px bg-[#8c0c4c]/20"></span>
          </p>
          <div class="grid grid-cols-2 gap-4">
          <?php foreach ($fields as $f): ?>
          <div class="<?= $f['full'] ? 'col-span-2' : 'col-span-2 sm:col-span-1' ?>">
            <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide block mb-1.5"><?= $f['label'] ?></label>
            <?php if ($f['type'] === 'select'): ?>
            <select name="<?= $f['name'] ?>" <?= ($f['req']??false)?'required':'' ?> class="<?= $inputCls ?> appearance-none">
              <?php foreach ($f['options'] as $val => $lbl): ?>
              <option value="<?= $val ?>"><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
            <?php if ($f['name'] === 'kategori_publikasi'): ?>
            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400">
              <span>Verifikasi resmi:</span>
              <a href="https://sinta.kemdikbud.go.id/journals" target="_blank" rel="noopener" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-0.5">🔍 Cek di SINTA Kemdikbud ↗</a>
              <span>·</span>
              <a href="https://www.scimagojr.com" target="_blank" rel="noopener" class="font-semibold text-amber-600 dark:text-amber-400 hover:underline inline-flex items-center gap-0.5">🔍 Cek di ScimagoJR ↗</a>
            </div>
            <div id="kategoriHelperNoteDosen" class="mt-2 text-xs font-semibold text-amber-800 dark:text-amber-200 bg-amber-50 dark:bg-amber-950/40 p-2.5 rounded-xl border border-amber-200 dark:border-amber-800/60 hidden"></div>
            <?php endif; ?>
            <?php elseif ($f['type'] === 'dynamic_authors'): ?>
            <div x-data="{ authors: [''] }" @set-authors-dosen.window="authors = $event.detail.length ? $event.detail : ['']">
              <template x-for="(author, index) in authors" :key="index">
                <div class="flex gap-2 mb-2">
                  <input type="text" x-model="authors[index]" placeholder="Nama penulis (termasuk nama Anda)" class="<?= $inputCls ?>">
                  <button type="button" @click="authors.splice(index, 1)" x-show="authors.length > 1" class="px-3 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-xl hover:bg-red-200 transition font-bold flex-shrink-0">✕</button>
                </div>
              </template>
              <button type="button" @click="authors.push('')" class="text-[11px] font-bold text-[#8c0c4c] dark:text-[#f06ea4] hover:underline flex items-center gap-1 mt-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg> Tambah Penulis
              </button>
              <input type="hidden" name="penulis" :value="authors.filter(a => a.trim() !== '').join(', ')">
            </div>
            <?php else: ?>
            <input type="<?= $f['type'] ?>" name="<?= $f['name'] ?>" <?= ($f['req']??false)?'required':'' ?>
                   placeholder="<?= $f['placeholder'] ?>" <?= isset($f['min']) ? 'min="'.$f['min'].'"' : '' ?> <?= isset($f['max']) ? 'max="'.$f['max'].'"' : '' ?>
                   class="<?= $inputCls ?>">
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>

        <!-- Abstrak -->
        <div>
          <p class="text-[10px] font-bold text-[#8c0c4c] dark:text-[#f06ea4] uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="flex-1 h-px bg-[#8c0c4c]/20"></span> Abstrak <span class="flex-1 h-px bg-[#8c0c4c]/20"></span>
          </p>
          <textarea name="abstrak" rows="4" placeholder="Tuliskan abstrak artikel..." class="<?= $inputCls ?> resize-none leading-relaxed"></textarea>
        </div>

        <!-- Referensi -->
        <div>
          <p class="text-[10px] font-bold text-[#8c0c4c] dark:text-[#f06ea4] uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="flex-1 h-px bg-[#8c0c4c]/20"></span> Referensi <span class="flex-1 h-px bg-[#8c0c4c]/20"></span>
          </p>
          <textarea name="referensi" rows="4" placeholder="Daftar referensi akan terisi otomatis saat Tarik Data..." class="<?= $inputCls ?> resize-none leading-relaxed text-xs font-mono"></textarea>
        </div>

        <!-- Upload File -->
        <div>
          <p class="text-[10px] font-bold text-[#8c0c4c] dark:text-[#f06ea4] uppercase tracking-widest mb-4 flex items-center gap-2">
            <span class="flex-1 h-px bg-[#8c0c4c]/20"></span> Unggah File <span class="flex-1 h-px bg-[#8c0c4c]/20"></span>
          </p>
          <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">File PDF Artikel <span class="font-normal normal-case">(opsional)</span></label>
          <p class="text-[11px] text-slate-400 my-2">PDF, DOC, DOCX</p>
          <input type="file" name="file_jurnal" accept=".pdf,.doc,.docx" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-200 file:text-slate-700 dark:file:bg-slate-700 dark:file:text-slate-300 hover:file:bg-slate-300 file:cursor-pointer file:transition-colors">
        </div>
      </div>

      <!-- Modal footer -->
      <div class="px-7 sm:px-8 py-5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row-reverse gap-3">
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#8c0c4c] to-[#c41e73] hover:from-[#a3155b] hover:to-[#d4217f] px-8 py-3 text-sm font-bold text-white shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          Simpan ke Portofolio
        </button>
        <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="inline-flex items-center justify-center rounded-xl bg-white dark:bg-slate-700 px-6 py-3 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
          Batal
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnFetchDoiDosen');
    const doiInput = document.getElementById('doi_input_dosen');

    if (btn && doiInput) {
        btn.addEventListener('click', async function() {
            let doi = doiInput.value.trim();
            if (!doi) { alert('Silakan masukkan DOI terlebih dahulu!'); return; }
            doi = doi.replace(/^(https?:\/\/)?(dx\.)?doi\.org\//i, '');

            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mencari...';
            btn.disabled = true;

            try {
                // Fetch 5 sumber secara PARALEL: Crossref, OpenAlex, Semantic Scholar, DataCite, + DOI Content Negotiation
                const [crossrefRes, openAlexRes, s2Res, dataciteRes, doiCnRes] = await Promise.allSettled([
                    fetch(`https://api.crossref.org/works/${encodeURIComponent(doi)}`),
                    fetch(`https://api.openalex.org/works/doi:${encodeURIComponent(doi)}?select=title,authorships,publication_year,primary_location,biblio,doi,abstract_inverted_index,keywords,concepts,referenced_works`),
                    fetch(`https://api.semanticscholar.org/graph/v1/paper/DOI:${encodeURIComponent(doi)}?fields=title,authors,year,publicationVenue,abstract,externalIds,references,references.title,references.authors,references.year,references.venue,references.externalIds`),
                    fetch(`https://api.datacite.org/dois/${encodeURIComponent(doi)}`),
                    fetch(`https://doi.org/${encodeURIComponent(doi)}`, { headers: { Accept: 'application/vnd.citationstyles.csl+json' } })
                ]);

                const cr   = crossrefRes.status === 'fulfilled' && crossrefRes.value.ok ? await crossrefRes.value.json() : null;
                const oaw  = openAlexRes.status  === 'fulfilled' && openAlexRes.value.ok ? await openAlexRes.value.json() : null;
                const s2   = s2Res.status        === 'fulfilled' && s2Res.value.ok       ? await s2Res.value.json()       : null;
                const dc   = dataciteRes.status  === 'fulfilled' && dataciteRes.value.ok ? await dataciteRes.value.json() : null;
                const csl  = doiCnRes.status     === 'fulfilled' && doiCnRes.value.ok    ? await doiCnRes.value.json()    : null;
                const item   = cr?.message;
                const dcAttr = dc?.data?.attributes;

                if (!item && !oaw && !s2 && !dcAttr && !csl) {
                    const elLink = document.querySelector('input[name="link_artikel"]');
                    if (elLink) elLink.value = `https://doi.org/${doi}`;
                    alert('⚠️ Data artikel tidak ditemukan di database publik manapun.\n\nKemungkinan jurnal ini belum mendaftarkan metadata ke Crossref/DataCite.\n\nSilakan isi data secara manual. Link artikel telah diisi otomatis.');
                    return;
                }

                const fill = (sel, val) => { const el = document.querySelector(sel); if (el && val) el.value = val; };

                // ── JUDUL ──────────────────────────────────────────────────
                const judul = item?.title?.[0] || oaw?.title || s2?.title || dcAttr?.titles?.[0]?.title || (Array.isArray(csl?.title) ? csl.title[0] : csl?.title) || '';
                if (judul) fill('input[name="judul_artikel"]', judul);

                // ── NAMA JURNAL + VOLUME ───────────────────────────────────
                const jurnalBase = item?.['container-title']?.[0] || oaw?.primary_location?.source?.display_name || s2?.publicationVenue?.name || dcAttr?.container?.title || csl?.['container-title'] || '';
                if (jurnalBase) {
                    const vol   = item?.volume   || oaw?.biblio?.volume  || dcAttr?.container?.volume || csl?.volume || '';
                    const issue = item?.issue    || oaw?.biblio?.issue   || dcAttr?.container?.issue  || csl?.issue  || '';
                    let jurnal  = jurnalBase;
                    if (vol)   jurnal += `, Vol. ${vol}`;
                    if (issue) jurnal += `, No. ${issue}`;
                    fill('input[name="nama_jurnal"]', jurnal);
                }

                // ── TAHUN ──────────────────────────────────────────────────
                const tahun = item?.published?.['date-parts']?.[0]?.[0]
                           || item?.issued?.['date-parts']?.[0]?.[0]
                           || oaw?.publication_year || s2?.year || dcAttr?.publicationYear
                           || csl?.issued?.['date-parts']?.[0]?.[0] || '';
                if (tahun) fill('input[name="tahun_terbit"]', tahun);

                // ── URL ────────────────────────────────────────────────────
                const url = item?.URL
                          || (oaw?.doi ? (oaw.doi.startsWith('http') ? oaw.doi : `https://doi.org/${oaw.doi}`) : '')
                          || csl?.URL
                          || (dcAttr || csl ? `https://doi.org/${doi}` : '');
                if (url) fill('input[name="link_artikel"]', url);

                // ── DETEKSI KATEGORI / INDEKSASI (SCOPUS / SINTA / PROSIDING) ──
                function detectKategori(crItem, oaWork, s2Work, dcWork, cslWork, rawDoi) {
                    const doiStr = (rawDoi || '').toLowerCase();
                    const type = (crItem?.type || oaWork?.type || cslWork?.type || '').toLowerCase();
                    const pubName = (crItem?.['container-title']?.[0] || oaWork?.primary_location?.source?.display_name || s2Work?.publicationVenue?.name || cslWork?.['container-title'] || '').toLowerCase();
                    const publisher = (crItem?.publisher || oaWork?.primary_location?.source?.host_organization_name || dcWork?.publisher || cslWork?.publisher || '').toLowerCase();
                    const title = (crItem?.title?.[0] || oaWork?.title || s2Work?.title || '').toLowerCase();

                    // 1. Cek Prosiding / Konferensi
                    const isConference = type.includes('proceedings') || type.includes('conference') || pubName.includes('conference') || pubName.includes('proceeding') || pubName.includes('symposium') || pubName.includes('ieee') || pubName.includes('acm') || doiStr.includes('10.1109/') || doiStr.includes('10.1145/') || pubName.includes('proceedings of');
                    if (isConference) {
                        if (pubName.includes('indonesia') || pubName.includes('nasional') || publisher.includes('indonesia')) {
                            return 'Prosiding Nasional';
                        }
                        return 'Prosiding Internasional (Scopus/IEEE)';
                    }

                    // 2. Buku / Book Chapter
                    if (type.includes('book') || type.includes('chapter') || type.includes('monograph')) {
                        return 'Buku / Book Chapter';
                    }

                    // 3. Cek Jurnal Scopus Indonesia Terkenal
                    const indoScopus = [
                        'telkomnika', 'ijeecs', 'bulletin of electrical', 'joiv', 'ijaseit', 'ijtech',
                        'international journal of technology', 'jurnal ilmu komputer dan informasi',
                        'indonesian journal of electrical', 'indonesian journal of science and technology',
                        'ijost', 'jurnal pendidikan ipa indonesia', 'jpii', 'biodiversitas', 'agrivita',
                        'kukila', 'atom indonesia', 'acta medica indonesiana', 'indonesian journal of chemistry',
                        'ijc', 'indonesian journal of biotechnology', 'medical journal of indonesia',
                        'indonesian journal of applied linguistics', 'journal of engineering and technological sciences',
                        'iaes'
                    ];
                    if (indoScopus.some(j => pubName.includes(j) || publisher.includes(j) || doiStr.includes('10.11591/'))) {
                        return 'Scopus Q2';
                    }

                    // 4. Cek DOI Prefix & Publisher Scopus Dunia
                    if (doiStr.includes('10.1016/')) return 'Scopus Q1'; // Elsevier / ScienceDirect
                    if (doiStr.includes('10.1038/')) return 'Scopus Q1'; // Nature
                    if (doiStr.includes('10.1002/')) return 'Scopus Q1'; // Wiley
                    if (doiStr.includes('10.1080/')) return 'Scopus Q2'; // Taylor & Francis
                    if (doiStr.includes('10.1007/')) return 'Scopus Q2'; // Springer
                    if (doiStr.includes('10.3390/')) return 'Scopus Q2'; // MDPI
                    if (doiStr.includes('10.1108/')) return 'Scopus Q2'; // Emerald
                    if (doiStr.includes('10.1088/')) return 'Scopus Q2'; // IOP
                    if (doiStr.includes('10.1063/')) return 'Scopus Q2'; // AIP
                    if (doiStr.includes('10.1371/')) return 'Scopus Q1'; // PLOS
                    if (doiStr.includes('10.1186/')) return 'Scopus Q1'; // BioMed Central
                    if (doiStr.includes('10.1093/')) return 'Scopus Q1'; // Oxford
                    if (doiStr.includes('10.1017/')) return 'Scopus Q1'; // Cambridge
                    if (doiStr.includes('10.1177/')) return 'Scopus Q2'; // SAGE
                    if (doiStr.includes('10.1504/')) return 'Scopus Q3'; // Inderscience
                    if (doiStr.includes('10.3389/')) return 'Scopus Q1'; // Frontiers
                    if (doiStr.includes('10.1039/')) return 'Scopus Q1'; // RSC
                    if (doiStr.includes('10.1021/')) return 'Scopus Q1'; // ACS
                    if (doiStr.includes('10.1515/')) return 'Scopus Q2'; // De Gruyter

                    // 5. Cek Publisher Scopus Terkenal dari teks
                    const scopusPublishersQ1 = ['elsevier', 'nature', 'wiley', 'cell press', 'plos', 'oxford university press', 'cambridge university press', 'frontiers in'];
                    if (scopusPublishersQ1.some(p => publisher.includes(p) || pubName.includes(p))) {
                        return 'Scopus Q1';
                    }

                    const scopusPublishersQ2 = ['springer', 'taylor & francis', 'taylor and francis', 'routledge', 'emerald', 'mdpi', 'sage', 'iop publishing', 'aip publishing', 'de gruyter', 'informs', 'wolters kluwer'];
                    if (scopusPublishersQ2.some(p => publisher.includes(p) || pubName.includes(p))) {
                        return 'Scopus Q2';
                    }

                    // 6. Cek SINTA spesifik jika ada di teks
                    const sintaMatch = (pubName + ' ' + title).match(/sinta\s*([1-6])/i);
                    if (sintaMatch) {
                        return `SINTA ${sintaMatch[1]}`;
                    }

                    // 7. Cek Jurnal Indonesia / Nasional (Default ke SINTA 2 / SINTA 3 yang merupakan standar akreditasi umum)
                    const isIndonesian = pubName.includes('jurnal') || pubName.includes('indonesia') || pubName.includes('nasional') || publisher.includes('universitas') || publisher.includes('institut') || publisher.includes('politeknik') || publisher.includes('asosi') || publisher.includes('perkumpulan') || publisher.includes('stmik') || publisher.includes('lldikti');
                    if (isIndonesian) {
                        if (oaWork?.primary_location?.source?.is_in_doaj || pubName.includes('international') || pubName.includes('journal of')) {
                            return 'SINTA 2';
                        }
                        return 'SINTA 3';
                    }

                    // 8. Jika jurnal internasional lain
                    if (pubName) {
                        if (pubName.includes('international') || pubName.includes('journal')) {
                            return 'Jurnal Internasional Terindeks';
                        }
                        return 'SINTA 3';
                    }

                    return 'Lainnya';
                }

                const detectedKategori = detectKategori(item, oaw, s2, dcAttr, csl, doi);
                const elKategori = document.querySelector('select[name="kategori_publikasi"]');
                const noteEl = document.getElementById('kategoriHelperNoteDosen');

                // ── Helper: Update UI kategori ────────────────────────────
                function applyKategori(kategori, source) {
                    if (!elKategori) return;
                    elKategori.value = kategori;
                    elKategori.classList.add('ring-2', 'ring-[#8c0c4c]');
                    setTimeout(() => elKategori.classList.remove('ring-2', 'ring-[#8c0c4c]'), 2500);
                    if (noteEl) {
                        const srcLabel = source === 'sinta' ? '✅ <strong>Data resmi SINTA Kemdikbud</strong>' : '🔍 Rekomendasi berdasarkan metadata DOI';
                        noteEl.innerHTML = `${srcLabel}: Terpilih <u>${kategori}</u>. ${source === 'sinta' ? 'Peringkat ini diambil langsung dari database SINTA.' : 'Pastikan sesuai dengan sertifikat resmi jurnal Anda.'}`;
                        noteEl.className = source === 'sinta'
                            ? 'mt-2 text-xs font-semibold text-green-800 dark:text-green-200 bg-green-50 dark:bg-green-950/40 p-2.5 rounded-xl border border-green-200 dark:border-green-800/60'
                            : 'mt-2 text-xs font-semibold text-amber-800 dark:text-amber-200 bg-amber-50 dark:bg-amber-950/40 p-2.5 rounded-xl border border-amber-200 dark:border-amber-800/60';
                    }
                }

                // Terapkan dulu rekomendasi lokal
                if (detectedKategori) {
                    applyKategori(detectedKategori, 'local');
                }

                // ── Panggil API SINTA real-time ───────────────────────────
                const issn = (
                    item?.ISSN?.[0] ||
                    oaw?.primary_location?.source?.issn_l ||
                    (oaw?.primary_location?.source?.issn || [])[0] ||
                    s2?.publicationVenue?.issn ||
                    csl?.ISSN?.[0] || ''
                ).replace(/[^0-9X]/gi, '').replace(/(.{4})(.{4})/, '$1-$2');

                const sintaQuery = issn || (item?.['container-title']?.[0] || oaw?.primary_location?.source?.display_name || s2?.publicationVenue?.name || csl?.['container-title'] || '');

                if (sintaQuery && sintaQuery.length > 3) {
                    if (noteEl) {
                        noteEl.className = 'mt-2 text-xs font-semibold text-blue-800 dark:text-blue-200 bg-blue-50 dark:bg-blue-950/40 p-2.5 rounded-xl border border-blue-200 dark:border-blue-800/60';
                        noteEl.innerHTML = '🔄 Memeriksa ke database SINTA Kemdikbud...';
                    }
                    const sintaParam = issn ? `issn=${encodeURIComponent(issn)}` : `q=${encodeURIComponent(sintaQuery)}`;
                    fetch(`<?= rtrim(str_replace(DIRECTORY_SEPARATOR, '/', str_replace($_SERVER['DOCUMENT_ROOT'] ?? '', '', dirname(__DIR__))), '/') ?>/api/check_sinta.php?${sintaParam}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.sinta_rank && /^SINTA\s*[1-6]$/i.test(data.sinta_rank.trim())) {
                                applyKategori(data.sinta_rank.trim(), 'sinta');
                            } else if (detectedKategori && /^SINTA/i.test(detectedKategori)) {
                                // Sudah ada rekomendasi lokal SINTA, tidak perlu ubah
                            } else if (data.error) {
                                if (noteEl && !detectedKategori) {
                                    noteEl.className = 'mt-2 text-xs font-semibold text-amber-800 dark:text-amber-200 bg-amber-50 dark:bg-amber-950/40 p-2.5 rounded-xl border border-amber-200 dark:border-amber-800/60';
                                    noteEl.innerHTML = '⚠️ Tidak dapat memeriksa SINTA. Silakan cek manual di <a href="https://sinta.kemdikbud.go.id/journals" target="_blank" class="underline font-bold">SINTA Kemdikbud ↗</a>';
                                }
                            }
                        })
                        .catch(() => { /* silent fail */ });
                }

                // ── PENULIS (Authors) ──────────────────────────────────────
                let parsedAuthors = [];
                if (item?.author?.length) {
                    parsedAuthors = item.author.map(a => [a.given, a.family].filter(Boolean).join(' ')).filter(Boolean);
                } else if (oaw?.authorships?.length) {
                    parsedAuthors = oaw.authorships.map(a => a.author?.display_name || '').filter(Boolean);
                } else if (s2?.authors?.length) {
                    parsedAuthors = s2.authors.map(a => a.name || '').filter(Boolean);
                } else if (dcAttr?.creators?.length) {
                    parsedAuthors = dcAttr.creators.map(c => {
                        if (c.givenName && c.familyName) return `${c.givenName} ${c.familyName}`;
                        return c.name || '';
                    }).filter(Boolean);
                } else if (csl?.author?.length) {
                    parsedAuthors = csl.author.map(a => [a.given, a.family].filter(Boolean).join(' ')).filter(Boolean);
                }
                if (parsedAuthors.length) {
                    window.dispatchEvent(new CustomEvent('set-authors-dosen', { detail: parsedAuthors }));
                }

                // ── ABSTRAK ────────────────────────────────────────────────
                let abstrak = '';
                if (item?.abstract) {
                    abstrak = item.abstract.replace(/<[^>]*>?/gm, '').trim();
                } else if (oaw?.abstract_inverted_index) {
                    const inv = oaw.abstract_inverted_index;
                    const maxPos = Math.max(...Object.values(inv).flat());
                    const words = new Array(maxPos + 1).fill('');
                    for (const [word, positions] of Object.entries(inv)) {
                        positions.forEach(pos => { words[pos] = word; });
                    }
                    abstrak = words.join(' ').trim();
                } else if (s2?.abstract) {
                    abstrak = s2.abstract;
                } else if (dcAttr?.descriptions?.length) {
                    const desc = dcAttr.descriptions.find(d => d.descriptionType === 'Abstract' || d.descriptionType === 'Description');
                    if (desc?.description) abstrak = desc.description.replace(/<[^>]*>?/gm, '').trim();
                } else if (csl?.abstract) {
                    abstrak = csl.abstract.replace(/<[^>]*>?/gm, '').trim();
                }
                if (abstrak) fill('textarea[name="abstrak"]', abstrak);

                // ── KATA KUNCI ─────────────────────────────────────────────
                let kataKunci = '';
                if (item?.subject?.length) {
                    kataKunci = item.subject.join(', ');
                } else if (oaw?.keywords?.length) {
                    kataKunci = oaw.keywords.map(k => k.keyword || k.display_name || k).filter(Boolean).slice(0, 10).join(', ');
                } else if (oaw?.concepts?.length) {
                    kataKunci = oaw.concepts.filter(c => c.score > 0.3).slice(0, 8).map(c => c.display_name).join(', ');
                } else if (dcAttr?.subjects?.length) {
                    kataKunci = dcAttr.subjects.map(s => s.subject || s).filter(s => typeof s === 'string').slice(0, 10).join(', ');
                }
                if (kataKunci) fill('input[name="kata_kunci"]', kataKunci);

                // ── REFERENSI ──────────────────────────────────────────────
                const rawRefs = item?.reference?.length ? item.reference : [];
                const oaRefUrls = oaw?.referenced_works?.length ? oaw.referenced_works : [];
                const s2Refs = s2?.references?.length ? s2.references : [];
                const elReferensi = document.querySelector('textarea[name="referensi"]');
                
                if (elReferensi) {
                    // Bersihkan DOI dari berbagai format yang mungkin kotor
                    function cleanDoi(raw) {
                        if (!raw) return null;
                        return raw.trim()
                            .replace(/^(https?:\/\/)?(dx\.)?doi\.org\//i, '')
                            .replace(/^doi:\s*/i, '')
                            .trim();
                    }

                    // Rekonstruksi APA dari OpenAlex work object
                    function openAlexToApa(w) {
                        const parts = [];
                        if (w.authorships?.length) {
                            const names = w.authorships.slice(0, 5).map(a => a.author?.display_name || '').filter(Boolean);
                            if (names.length) parts.push(names.join(', ') + (w.authorships.length > 5 ? ', et al.' : '') + '.');
                        }
                        if (w.publication_year) parts.push(`(${w.publication_year}).`);
                        if (w.title) parts.push(w.title + '.');
                        const src = w.primary_location?.source?.display_name;
                        if (src) {
                            let j = src;
                            if (w.biblio?.volume) j += `, ${w.biblio.volume}`;
                            if (w.biblio?.issue)  j += `(${w.biblio.issue})`;
                            if (w.biblio?.first_page) {
                                const pg = (w.biblio.last_page && w.biblio.last_page !== w.biblio.first_page)
                                    ? `${w.biblio.first_page}–${w.biblio.last_page}` : w.biblio.first_page;
                                j += `, ${pg}`;
                            }
                            parts.push(j + '.');
                        }
                        if (w.doi) parts.push(w.doi.startsWith('http') ? w.doi : `https://doi.org/${w.doi}`);
                        return parts.length >= 2 ? parts.join(' ') : null;
                    }

                    // Fetch batch OpenAlex dengan filter ids.openalex yang benar
                    async function fetchOpenAlexBatch(oaIds) {
                        if (!oaIds.length) return [];
                        try {
                            const url = `https://api.openalex.org/works?filter=ids.openalex:${oaIds.join('|')}&select=id,title,authorships,publication_year,primary_location,biblio,doi&per-page=50`;
                            const r = await fetch(url);
                            if (!r.ok) return [];
                            const d = await r.json();
                            return d.results || [];
                        } catch (_) { return []; }
                    }

                    if (rawRefs.length > 0 || oaRefUrls.length > 0 || s2Refs.length > 0) {
                        (async () => {
                            const lines = [];

                            if (rawRefs.length > 0) {
                                // PATH A: Crossref punya list referensi
                                const refs = rawRefs.slice(0, 40);
                                elReferensi.value = `Sedang memuat ${refs.length} referensi...`;

                                function buildFromInline(ref) {
                                    if (ref.unstructured?.trim()) return ref.unstructured.replace(/<[^>]+>/g, '').trim();
                                    const p = [];
                                    if (ref.author) p.push(ref.author + '.');
                                    if (ref.year) p.push(`(${ref.year}).`);
                                    if (ref['article-title']) p.push(ref['article-title'] + '.');
                                    if (ref['journal-title']) p.push(ref['journal-title']);
                                    if (ref.volume) p.push(`, ${ref.volume}`);
                                    if (ref['first-page']) p.push(`, ${ref['first-page']}`);
                                    if (ref.DOI && p.length > 0) p.push(`. https://doi.org/${ref.DOI}`);
                                    return p.length > 1 ? p.join('') : null;
                                }

                                async function fetchRefOpenAlex(rawDoi) {
                                    const doi = cleanDoi(rawDoi);
                                    if (!doi) return null;
                                    try {
                                        const r = await fetch(`https://api.openalex.org/works/doi:${encodeURIComponent(doi)}?select=title,authorships,publication_year,primary_location,biblio,doi`);
                                        if (!r.ok) return null;
                                        return openAlexToApa(await r.json());
                                    } catch (_) { return null; }
                                }

                                const BATCH = 8;
                                for (let b = 0; b < refs.length; b += BATCH) {
                                    const batch = refs.slice(b, b + BATCH);
                                    const results = await Promise.all(batch.map(async ref => {
                                        let text = buildFromInline(ref);
                                        if (!text && ref.DOI) text = await fetchRefOpenAlex(ref.DOI);
                                        if (!text && ref.DOI) text = `https://doi.org/${cleanDoi(ref.DOI)}`;
                                        return text?.trim() || null;
                                    }));
                                    results.forEach(t => { if (t) lines.push(`[${lines.length + 1}] ${t}`); });
                                    elReferensi.value = `Memuat referensi... (${Math.min(b + BATCH, refs.length)}/${refs.length})`;
                                }

                            } else if (oaRefUrls.length > 0) {
                                // PATH B: Fallback ke OpenAlex referenced_works batch
                                const oaRefIds = oaRefUrls
                                    .map(url => url.split('/').pop())
                                    .filter(id => /^W\d+$/.test(id))
                                    .slice(0, 50);

                                elReferensi.value = `Sedang memuat ${oaRefIds.length} referensi (via OpenAlex)...`;

                                const BATCH = 25;
                                for (let b = 0; b < oaRefIds.length; b += BATCH) {
                                    const batchIds = oaRefIds.slice(b, b + BATCH);
                                    const works = await fetchOpenAlexBatch(batchIds);
                                    works.forEach(w => {
                                        const text = openAlexToApa(w);
                                        if (text) lines.push(`[${lines.length + 1}] ${text}`);
                                    });
                                    elReferensi.value = `Memuat referensi... (${Math.min(b + BATCH, oaRefIds.length)}/${oaRefIds.length})`;
                                }
                            } else if (s2Refs.length > 0) {
                                // PATH C: Fallback ke Semantic Scholar references
                                const refs = s2Refs.slice(0, 40);
                                elReferensi.value = `Sedang memuat ${refs.length} referensi (via Semantic Scholar)...`;
                                
                                refs.forEach(ref => {
                                    const p = [];
                                    if (ref.authors?.length) {
                                        const names = ref.authors.slice(0, 5).map(a => a.name).filter(Boolean);
                                        if (names.length) p.push(names.join(', ') + (ref.authors.length > 5 ? ', et al.' : '') + '.');
                                    }
                                    if (ref.year) p.push(`(${ref.year}).`);
                                    if (ref.title) p.push(ref.title + '.');
                                    if (ref.venue) p.push(ref.venue + '.');
                                    const refDoi = ref.externalIds?.DOI;
                                    if (refDoi) p.push(`https://doi.org/${refDoi}`);
                                    
                                    if (p.length > 1) {
                                        lines.push(`[${lines.length + 1}] ${p.join(' ')}`);
                                    }
                                });
                            }

                            elReferensi.value = lines.join('\n') || '(Metadata referensi tidak disediakan oleh penerbit di database publik manapun. Anda mungkin perlu mengisinya secara manual)';
                        })();
                    } else {
                        elReferensi.value = '';
                    }
                }

                btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Berhasil!';
                btn.classList.replace('from-[#8c0c4c]', 'from-emerald-600');
                btn.classList.replace('to-[#c41e73]', 'to-emerald-500');
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.replace('from-emerald-600', 'from-[#8c0c4c]');
                    btn.classList.replace('to-emerald-500', 'to-[#c41e73]');
                    btn.disabled = false;
                }, 2500);

            } catch (err) {
                alert('Gagal: ' + err.message);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        });
    }
});
</script>

<?php require_once 'footer.php'; ?>
