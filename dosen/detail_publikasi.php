<?php
$pageTitle = 'Detail Publikasi';
$hidePageHeader = true;
require_once 'header.php';
require_once __DIR__ . '/../includes/functions.php';

$dosenRow = dbQueryOne("SELECT id, nama FROM dosen WHERE id = ?", [$_SESSION['user_id']]);
if (!$dosenRow) {
    $userRow = dbQueryOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    if ($userRow) {
        $dosenRow = dbQueryOne("SELECT id, nama FROM dosen WHERE nidn = ? OR email = ?", [$userRow['username'], $userRow['email'] ?? '']);
    }
}
$dosenId = $dosenRow['id'] ?? 0;
$dosenNama = $dosenRow['nama'] ?? '';

$pubId  = (int)($_GET['id'] ?? 0);
if (!$pubId || !$dosenId) { header('Location: penelitian'); exit; }

$pub = dbQueryOne(
    "SELECT * FROM dosen_publikasi WHERE id = ? AND dosen_id = ?",
    [$pubId, $dosenId]
);
if (!$pub) { header('Location: penelitian'); exit; }

$pageTitle = htmlspecialchars($pub['judul_artikel']);

$ps   = strtolower($pub['status_publikasi']);
$badgeCls = 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
$dotCls   = 'bg-slate-400';
if (str_contains($ps,'publish')) { $badgeCls='bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'; $dotCls='bg-emerald-500'; }
if (str_contains($ps,'acc'))     { $badgeCls='bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';             $dotCls='bg-blue-500'; }
if (str_contains($ps,'review'))  { $badgeCls='bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';         $dotCls='bg-amber-500'; }

$keywords  = $pub['kata_kunci'] ? array_map('trim', explode(',', $pub['kata_kunci'])) : [];
$refLines  = $pub['referensi']  ? array_values(array_filter(array_map('trim', explode("\n", $pub['referensi'])))) : [];
// If rekan_penulis is filled, use it as the full author list; else fall back to student name
$coAuthors = [];
if (!empty($pub['penulis'])) {
    $coAuthors = array_map('trim', explode(',', $pub['penulis']));
} else {
    $coAuthors = [$dosenNama];
}
$doi       = $pub['doi'] ?? null;

// Build citation string using penulis field (or fallback to student name)
function buildCitation($pub, $coAuthors, $dosenNama): string {
    $parts = [];
    $authorList = !empty($coAuthors) ? implode(', ', array_map('htmlspecialchars', $coAuthors)) : htmlspecialchars($dosenNama);
    $parts[] = $authorList . '.';
    $parts[] = '(' . ($pub['tahun_terbit'] ?: date('Y')) . ').';
    $parts[] = htmlspecialchars($pub['judul_artikel']) . '.';
    if ($pub['nama_jurnal']) {
        $parts[] = '<em>' . htmlspecialchars($pub['nama_jurnal']) . '</em>.';
    }
    if ($pub['doi']) $parts[] = 'https://doi.org/' . htmlspecialchars($pub['doi']);
    return implode(' ', $parts);
}
$citationHtml = buildCitation($pub, $coAuthors, $dosenNama);
?>

<style>
/* Make this page full-width within the main wrapper and offset top padding */
  .sd-detail-page { margin: -2rem -1rem 0; }
  @media(min-width:640px)  { .sd-detail-page { margin: -2.5rem -1.5rem 0; } }
  @media(min-width:768px)  { .sd-detail-page { margin: -3rem -1.5rem 0; } }
  @media(min-width:1024px) { .sd-detail-page { margin: -3.5rem -2rem 0; } }

.sd-section-title {
  font-size:.7rem; font-weight:800; color:#8c0c4c; text-transform:uppercase;
  letter-spacing:.12em; display:flex; align-items:center; gap:.75rem; margin-bottom:1.25rem;
}
.dark .sd-section-title { color:#f06ea4; }
.sd-section-title::before, .sd-section-title::after { content:''; flex:1; height:1px; background:rgba(140,12,76,.18); }

.sd-label { font-size:.65rem; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:#94a3b8; }
.dark .sd-label { color:#64748b; }
</style>

<div class="sd-detail-page">

<!-- ══════════════════════════════════
     JOURNAL HEADER BANNER
═══════════════════════════════════════ -->
<div class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">



    <!-- Journal Source Line (like ScienceDirect "source info" at top) -->
    <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500 dark:text-slate-400 mb-4">
      <?php if ($pub['nama_jurnal']): ?>
      <span class="font-bold text-[#8c0c4c] dark:text-[#f06ea4]"><?= htmlspecialchars($pub['nama_jurnal']) ?></span>
      <?php if ($pub['tahun_terbit']): ?><span>•</span><span><?= $pub['tahun_terbit'] ?></span><?php endif; ?>
      <?php endif; ?>

      <!-- Status badge -->
      <span class="ml-auto inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-md <?= $badgeCls ?>">
        <span class="w-1.5 h-1.5 rounded-full <?= $dotCls ?> animate-pulse"></span>
        <?= htmlspecialchars($pub['status_publikasi']) ?>
      </span>
    </div>

    <!-- ARTICLE TITLE -->
    <h1 class="text-2xl sm:text-3xl font-display font-bold text-slate-900 dark:text-white leading-tight mb-5">
      <?= htmlspecialchars($pub['judul_artikel']) ?>
    </h1>

    <!-- AUTHORS LINE -->
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm mb-4">
      <?php foreach ($coAuthors as $idx => $co): ?>
      <?php if ($idx > 0): ?><span class="text-slate-300 dark:text-slate-600">|</span><?php endif; ?>
      <span class="<?= $idx === 0 ? 'font-semibold text-slate-800 dark:text-slate-200' : 'text-slate-600 dark:text-slate-400' ?> hover:text-[#8c0c4c] dark:hover:text-[#f06ea4] cursor-pointer transition-colors"><?= htmlspecialchars($co) ?></span>
      <?php endforeach; ?>
    </div>

    <!-- DOI & Source identifier line -->
    <?php if ($doi || $pub['link_artikel']): ?>
    <div class="flex flex-wrap items-center gap-4 text-xs">
      <?php if ($doi): ?>
      <div class="flex items-center gap-1.5 font-mono text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-lg">
        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        <span class="font-semibold text-slate-600 dark:text-slate-300">DOI:</span>
        <a href="https://doi.org/<?= htmlspecialchars($doi) ?>" target="_blank" class="text-[#8c0c4c] dark:text-[#f06ea4] hover:underline"><?= htmlspecialchars($doi) ?></a>
      </div>
      <?php endif; ?>
      <?php if ($pub['link_artikel']): ?>
      <a href="<?= htmlspecialchars($pub['link_artikel']) ?>" target="_blank" class="flex items-center gap-1.5 text-slate-500 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4] transition-colors font-semibold">
        Lihat di Jurnal <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
      </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- ══════════════════════════════════
     MAIN BODY (2-column like ScienceDirect)
═══════════════════════════════════════ -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex gap-8 items-start">

  <!-- ── LEFT / MAIN ── -->
  <div class="flex-1 min-w-0 space-y-6">

    <!-- ABSTRACT -->
    <?php if ($pub['abstrak']): ?>
    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-7 shadow-sm">
      <h2 class="sd-section-title">Abstract / Abstrak</h2>
      <p class="text-slate-700 dark:text-slate-300 leading-relaxed text-[.9375rem] text-justify">
        <?= nl2br(htmlspecialchars($pub['abstrak'])) ?>
      </p>

      <!-- Keywords -->
      <?php if (!empty($keywords)): ?>
      <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-700/50">
        <p class="sd-label mb-3">Kata Kunci</p>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($keywords as $kw): ?>
          <span class="bg-[#8c0c4c]/8 dark:bg-[#f06ea4]/10 border border-[#8c0c4c]/20 text-[#8c0c4c] dark:text-[#f06ea4] px-3 py-1.5 rounded-lg text-xs font-semibold">
            <?= htmlspecialchars($kw) ?>
          </span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </section>
    <?php else: ?>
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 rounded-2xl p-5 flex gap-4">
      <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
      <div>
        <p class="text-sm font-bold text-amber-800 dark:text-amber-300 mb-1">Abstrak belum diisi</p>
        <p class="text-xs text-amber-700 dark:text-amber-400">Silakan tambahkan abstrak untuk melengkapi metadata artikel ini.</p>
      </div>
    </div>
    <?php endif; ?>

    <!-- ARTICLE INFO TABLE (like ScienceDirect "Article outline / Information") -->
    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-7 shadow-sm">
      <h2 class="sd-section-title">Informasi Artikel</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-0 divide-y divide-slate-100 dark:divide-slate-700/50 sm:divide-y-0">
        <?php
        $infoItems = [
          ['Judul',            $pub['judul_artikel']],
          ['Nama Jurnal',      $pub['nama_jurnal']],
          ['Tahun Terbit',     $pub['tahun_terbit']],
          ['DOI',              $doi ? '<a href="https://doi.org/'.htmlspecialchars($doi).'" target="_blank" class="text-[#8c0c4c] dark:text-[#f06ea4] hover:underline font-mono">'.htmlspecialchars($doi).'</a>' : null],
          ['Status',           htmlspecialchars($pub['status_publikasi'])],
          ['Penulis Utama',    htmlspecialchars($coAuthors[0] ?? $dosenNama)],
          ['Diunggah',         date('d F Y', strtotime($pub['created_at']))],
        ];
        $half = ceil(count($infoItems)/2);
        $left = array_slice($infoItems, 0, $half);
        $right= array_slice($infoItems, $half);
        ?>
        <!-- left col -->
        <div class="space-y-0">
          <?php foreach ($left as [$lbl, $val]):
            if (!$val) continue; ?>
          <div class="flex gap-4 py-3 border-b border-slate-100 dark:border-slate-700/50 last:border-0">
            <dt class="sd-label w-32 shrink-0 pt-0.5"><?= $lbl ?></dt>
            <dd class="text-sm text-slate-800 dark:text-slate-200"><?= $val ?></dd>
          </div>
          <?php endforeach; ?>
        </div>
        <!-- right col -->
        <div class="space-y-0">
          <?php foreach ($right as [$lbl, $val]):
            if (!$val) continue; ?>
          <div class="flex gap-4 py-3 border-b border-slate-100 dark:border-slate-700/50 sm:border-b last:border-0">
            <dt class="sd-label w-32 shrink-0 pt-0.5"><?= $lbl ?></dt>
            <dd class="text-sm text-slate-800 dark:text-slate-200"><?= $val ?></dd>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- CITATION (How to Cite) -->
    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-7 shadow-sm">
      <h2 class="sd-section-title">Cara Mengutip (Sitasi)</h2>
      <div class="space-y-4">

        <!-- APA style -->
        <div>
          <p class="sd-label mb-2">Format APA</p>
          <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4 text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-serif">
            <?= $citationHtml ?>
          </div>
          <button onclick="copyCitation(this, '<?= htmlspecialchars(strip_tags($citationHtml)) ?>')"
                  class="mt-2 text-xs font-bold text-[#8c0c4c] dark:text-[#f06ea4] hover:underline transition-colors">
            Salin Sitasi
          </button>
        </div>

        <!-- BibTeX style -->
        <?php
        $bibtexKey = strtolower(preg_replace('/\W+/','_', substr($coAuthors[0] ?? $dosenNama,0,10))) . ($pub['tahun_terbit'] ?: date('Y'));
        $allAuthorsStr = !empty($coAuthors) ? implode(' and ', $coAuthors) : $dosenNama;
        
        $bibtex = "@article{{$bibtexKey},\n".
                  "  author    = {{$allAuthorsStr}},\n".
                  "  title     = {{".htmlspecialchars($pub['judul_artikel'])."}},\n".
                  ($pub['nama_jurnal']    ? "  journal   = {".htmlspecialchars($pub['nama_jurnal'])."},\n" : '').
                  "  year      = {".($pub['tahun_terbit'] ?: date('Y'))."},\n".
                  ($doi                   ? "  doi       = {".htmlspecialchars($doi)."},\n" : '').
                  "}";
        ?>
        <div x-data="{showBib:false}">
          <button @click="showBib=!showBib" class="text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4] transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            Tampilkan BibTeX
          </button>
          <div x-show="showBib" x-transition class="mt-2 bg-slate-900 dark:bg-slate-950 rounded-xl p-4 overflow-x-auto" style="display:none;">
            <pre class="text-xs text-green-400 font-mono leading-relaxed whitespace-pre"><?= htmlspecialchars($bibtex) ?></pre>
            <button onclick="copyCitation(this, <?= htmlspecialchars(json_encode($bibtex)) ?>)" class="mt-2 text-xs font-bold text-slate-400 hover:text-green-400 transition-colors">Salin BibTeX</button>
          </div>
        </div>
      </div>
    </section>

    <!-- REFERENCES -->
    <?php if (!empty($refLines)): ?>
    <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-7 shadow-sm">
      <h2 class="sd-section-title">Referensi <span class="text-sm font-semibold text-slate-400 ml-2 normal-case tracking-normal">(<?= count($refLines) ?> sumber)</span></h2>
      <ol class="space-y-4">
        <?php foreach ($refLines as $i => $ref): ?>
        <li class="flex gap-4 group">
          <span class="shrink-0 w-7 h-7 rounded-lg bg-[#8c0c4c]/8 dark:bg-[#f06ea4]/10 text-[#8c0c4c] dark:text-[#f06ea4] text-xs font-bold flex items-center justify-center mt-0.5 group-hover:bg-[#8c0c4c] group-hover:text-white transition-colors">
            <?= $i + 1 ?>
          </span>
          <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed group-hover:text-slate-800 dark:group-hover:text-slate-200 transition-colors">
            <?php
              // Strip leading [n] numbering if already present in text
              $refText = preg_replace('/^\[\d+\]\s*/', '', $ref);
              // Make DOI/URL links clickable
              $refText = preg_replace(
                '/(https?:\/\/[^\s]+)/i',
                '<a href="$1" target="_blank" class="text-[#8c0c4c] dark:text-[#f06ea4] hover:underline break-all">$1</a>',
                htmlspecialchars($refText)
              );
              echo $refText;
            ?>
          </p>
        </li>
        <?php endforeach; ?>
      </ol>
    </section>
    <?php endif; ?>

  </div><!-- end left main -->

  <!-- ── RIGHT SIDEBAR ── -->
  <aside class="hidden lg:block w-72 shrink-0 sticky top-4 space-y-5">

    <!-- DOWNLOAD BOX -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
      <div class="bg-[#8c0c4c] px-5 py-4">
        <h3 class="text-white text-sm font-bold uppercase tracking-wide">Akses Artikel</h3>
      </div>
      <div class="p-5 space-y-3">
        <?php if ($pub['file_jurnal']): ?>
        <a href="../<?= htmlspecialchars($pub['file_jurnal']) ?>" target="_blank"
           class="flex items-center justify-center gap-2 w-full bg-[#8c0c4c] hover:bg-[#a3155b] text-white text-sm font-bold py-3 rounded-xl shadow-md hover:shadow-lg transition-all hover:-translate-y-0.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Download PDF
        </a>
        <?php else: ?>
        <div class="text-center py-3 text-xs text-slate-400 border border-dashed border-slate-200 dark:border-slate-600 rounded-xl">
          File PDF belum diunggah
        </div>
        <?php endif; ?>

        <?php if ($pub['link_artikel']): ?>
        <a href="<?= htmlspecialchars($pub['link_artikel']) ?>" target="_blank"
           class="flex items-center justify-center gap-2 w-full bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 text-sm font-semibold py-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          Lihat di Jurnal
        </a>
        <?php endif; ?>

        <?php if ($pub['file_bukti_bayar']): ?>
        <a href="../<?= htmlspecialchars($pub['file_bukti_bayar']) ?>" target="_blank"
           class="flex items-center justify-center gap-2 w-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 text-xs font-semibold py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Bukti / Surat LoA
        </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- DOI INFO -->
    <?php if ($doi): ?>
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
      <p class="sd-label mb-3">Identifikasi Digital</p>
      <div class="bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl p-3">
        <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">DOI</p>
        <a href="https://doi.org/<?= htmlspecialchars($doi) ?>" target="_blank"
           class="text-xs font-mono text-[#8c0c4c] dark:text-[#f06ea4] hover:underline break-all leading-relaxed">
          <?= htmlspecialchars($doi) ?>
        </a>
      </div>
    </div>
    <?php endif; ?>

    <!-- AUTHOR INFO -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
      <div class="bg-gradient-to-r from-[#8c0c4c] to-[#c41e73] px-5 py-3">
        <h3 class="text-white text-sm font-bold uppercase tracking-wide">Penulis</h3>
      </div>
      <div class="p-5 space-y-4">
        <?php 
        // Iterate actual author list
        foreach ($coAuthors as $idx => $authorName): 
            $isDosen = strcasecmp($authorName, $dosenNama) === 0;
            $roleLabel = ($idx === 0) ? 'Penulis Utama' : 'Co-Author';
        ?>
        <div class="flex items-start gap-3 <?= $idx > 0 ? 'pt-3 border-t border-slate-100 dark:border-slate-700/50' : '' ?>">
          <div class="w-10 h-10 rounded-full <?= $isDosen ? 'bg-[#8c0c4c] text-white shadow-sm' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' ?> flex items-center justify-center font-bold flex-shrink-0">
            <?= strtoupper(substr($authorName,0,1)) ?>
          </div>
          <div>
            <p class="text-sm font-bold <?= $isDosen ? 'text-slate-800 dark:text-slate-200' : 'text-slate-700 dark:text-slate-300' ?>"><?= htmlspecialchars($authorName) ?></p>
            <div class="flex flex-wrap gap-2 mt-0.5 items-center">
                <p class="text-[10px] <?= $idx === 0 ? 'text-[#8c0c4c] dark:text-[#f06ea4]' : 'text-slate-500' ?> font-bold uppercase tracking-wide"><?= $roleLabel ?></p>
                <?php if ($isDosen): ?>
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 px-1.5 py-0.5 rounded font-mono border border-slate-200 dark:border-slate-700">Anda (Dosen)</span>
                <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm space-y-2">
      <p class="sd-label mb-3">Tindakan</p>
      <a href="penelitian" class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4] font-medium transition-colors py-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Repository
      </a>
      <form action="aksi_publikasi_dosen" method="POST" onsubmit="return confirm('Yakin ingin menghapus artikel ini dari portofolio?');">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= $pub['id'] ?>">
        ">
        <button type="submit" class="flex items-center gap-2 text-sm text-red-400 hover:text-red-600 font-medium transition-colors py-1.5 w-full text-left">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          Hapus dari Portofolio
        </button>
      </form>
    </div>

  </aside><!-- end right sidebar -->
</div><!-- end two-col -->
</div><!-- end sd-detail-page -->

<script>
function copyCitation(btn, text) {
  navigator.clipboard.writeText(text).then(() => {
    const orig = btn.textContent;
    btn.textContent = '✓ Tersalin!';
    btn.style.color = '#059669';
    setTimeout(() => { btn.textContent = orig; btn.style.color = ''; }, 2000);
  });
}
</script>

<?php require_once 'footer.php'; ?>
