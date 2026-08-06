<?php
requireAdminOrKaprodi();
$data['title'] = 'Data Pengajuan - ' . APP_NAME;
include BASE_PATH . '/includes/admin_layout_top.php';

$pengajuan  = $data['pengajuan'];
$filters    = $data['filters'];
$pagination = $data['pagination'];
?>
<!-- Print Only Header -->
<div class="hidden print:block mb-8 text-center border-b-2 border-black pb-4">
  <h1 class="text-2xl font-bold uppercase">Laporan Data Pengajuan Pengunduran Diri</h1>
  <p class="text-lg">Nusa Putra University</p>
  <p class="text-sm mt-2">Tanggal Cetak: <?= date('d/m/Y H:i') ?></p>
</div>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 no-print">
  <div>
    <h1 class="font-display font-bold text-2xl text-slate-800 dark:text-white">Data Pengajuan</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Total <?= number_format($pagination['total']) ?> pengajuan ditemukan</p>
  </div>
  <!-- Export Buttons -->
  <div class="flex gap-2">
    <a href="<?= APP_URL ?>/?page=admin/export-excel<?= http_build_query(array_merge(['page'=>'admin/export-excel'], $filters)) ? '&' . http_build_query($filters) : '' ?>"
       class="flex items-center gap-1.5 px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition shadow-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      Export Excel
    </a>
    <?php
       // Bangun URL untuk tombol cetak dengan menyisipkan print_all=1 
       $printQuery = array_merge($_GET, ['print_all' => 1]);
       unset($printQuery['page_num']); // Reset page num agar cetak dari data pertama
       $printUrl = APP_URL . '/?' . http_build_query($printQuery);
    ?>
    <a href="<?= $printUrl ?>" 
       class="flex items-center gap-1.5 px-3 py-2 bg-slate-600 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition shadow-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
      Print PDF
    </a>
  </div>
</div>

<?php if (isset($_GET['print_all'])): ?>
<script>
  window.onload = function() {
    window.print();
    // Opsional: hapus param print_all dari URL agar tidak selalu ngeprint saat di-refresh
    window.history.replaceState({}, document.title, window.location.pathname + "?" + new URLSearchParams(window.location.search).toString().replace(/&?print_all=1/, ''));
  };
</script>
<?php endif; ?>

<!-- Filters -->
<div class="card p-4 mb-5 no-print">
  <form id="filter-form" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
    <input type="hidden" name="page" value="admin/pengajuan">

    <div class="sm:col-span-2 lg:col-span-2">
      <input type="text" name="search" placeholder="🔍 Cari nama, NIM, nomor surat..."
        value="<?= e($filters['search']) ?>"
        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
    </div>

    <select name="status" class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
      <option value="">Semua Status</option>
      <?php foreach (['Pending','Approved','Rejected','Draft'] as $s): ?>
      <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= statusLabel($s) ?></option>
      <?php endforeach; ?>
    </select>

    <?php if (isKaprodi()): ?>
      <input type="hidden" name="program_studi" value="<?= e($filters['program_studi']) ?>">
      <div class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-400 text-sm truncate">
        <?= e($filters['program_studi']) ?>
      </div>
    <?php else: ?>
    <select name="program_studi" class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
      <option value="">Semua Prodi</option>
      <?php foreach (PROGRAM_STUDI as $prodi): ?>
      <option value="<?= e($prodi) ?>" <?= $filters['program_studi'] === $prodi ? 'selected' : '' ?>><?= e($prodi) ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <input type="number" name="angkatan" placeholder="Angkatan" min="2000" max="2030"
      value="<?= e($filters['angkatan']) ?>"
      class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">

    <div class="flex gap-2">
      <button type="submit" class="flex-1 px-4 py-2 bg-nusa text-white text-sm font-semibold rounded-lg hover:bg-nusa-dark transition">Filter</button>
      <a href="<?= APP_URL ?>/?page=admin/pengajuan" class="px-3 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 text-sm font-medium rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition">Reset</a>
    </div>
  </form>

  <!-- Date range filter -->
  <div class="grid grid-cols-2 gap-3 mt-3">
    <div class="flex items-center gap-2">
      <label class="text-xs text-slate-500 whitespace-nowrap">Dari:</label>
      <input type="date" name="tanggal_dari" form="filter-form" value="<?= e($filters['tanggal_dari']) ?>"
        class="flex-1 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
    </div>
    <div class="flex items-center gap-2">
      <label class="text-xs text-slate-500 whitespace-nowrap">Sampai:</label>
      <input type="date" name="tanggal_sampai" form="filter-form" value="<?= e($filters['tanggal_sampai']) ?>"
        class="flex-1 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
    </div>
  </div>
</div>

<!-- Table -->
<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm" id="tableData">
      <thead class="bg-slate-50 dark:bg-slate-700/50">
        <tr>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">No</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider no-print">Nomor Surat</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mahasiswa</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Program Studi</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
          <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bersedia</th>
          <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
          <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider no-print">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php if (empty($pengajuan)): ?>
        <tr>
          <td colspan="8" class="px-4 py-16 text-center">
            <div class="flex flex-col items-center">
              <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              <p class="text-slate-500 dark:text-slate-400 font-medium">Tidak ada data ditemukan</p>
              <p class="text-slate-400 dark:text-slate-500 text-xs mt-1">Coba ubah filter pencarian</p>
            </div>
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($pengajuan as $no => $p): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
          <td class="px-4 py-3 text-slate-500 dark:text-slate-400 font-medium">
            <?= ($pagination['offset'] ?? 0) + $no + 1 ?>
          </td>
          <td class="px-4 py-3 no-print">
            <span class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-300">
              <?= e($p['nomor_surat'] ?? '—') ?>
            </span>
          </td>
          <td class="px-4 py-3">
            <p class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[140px]"><?= e($p['nama_pemohon']) ?></p>
            <p class="text-slate-400 dark:text-slate-500 text-xs"><?= e($p['nim']) ?> · <?= e($p['angkatan']) ?></p>
          </td>
          <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs max-w-[130px]">
            <?= e(str_replace('Magister ', 'M. ', $p['program_studi'])) ?>
          </td>
          <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
            <?= formatTanggal($p['tanggal_surat']) ?>
          </td>
          <td class="px-4 py-3 text-center">
            <span class="font-semibold text-xs <?= $p['bersedia_mundur'] === 'YES' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
              <?= $p['bersedia_mundur'] ?>
            </span>
          </td>
          <td class="px-4 py-3 text-center">
            <span class="badge <?= statusBadge($p['status']) ?>"><?= statusLabel($p['status']) ?></span>
          </td>
          <td class="px-4 py-3 no-print">
            <div class="flex items-center justify-center gap-2">
              <!-- Detail -->
              <a href="<?= APP_URL ?>/?page=admin/detail&id=<?= $p['id'] ?>"
                 class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 transition" title="Detail">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              </a>
              <?php if ($p['bersedia_mundur'] === 'YES'): ?>
              <!-- Print -->
              <a href="<?= APP_URL ?>/?page=pdf&id=<?= $p['id'] ?>&print=1" target="_blank"
                 class="p-1.5 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 hover:bg-green-100 transition" title="Cetak PDF">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
              </a>
              <!-- Word -->
              <a href="<?= APP_URL ?>/?page=docx&id=<?= $p['id'] ?>"
                 class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 transition" title="Download Word">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              </a>
              <?php endif; ?>
              <?php if ($p['status'] === 'Pending'): ?>
              <!-- Setujui -->
              <button type="button" onclick="approvePengajuan(<?= $p['id'] ?>)"
                class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 transition" title="Setujui">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              </button>
              <!-- Tolak -->
              <button type="button" onclick="rejectPengajuan(<?= $p['id'] ?>)"
                class="p-1.5 rounded-lg bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-100 transition" title="Tolak">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
              <?php endif; ?>
              <!-- Delete -->
              <button type="button" onclick="deletePengajuan(<?= $p['id'] ?>)"
                class="p-1.5 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 transition" title="Hapus">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($pagination['total_pages'] > 1): ?>
  <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-3">
    <p class="text-sm text-slate-500 dark:text-slate-400">
      Menampilkan <?= $pagination['offset'] + 1 ?>–<?= min($pagination['offset'] + 10, $pagination['total']) ?> dari <?= number_format($pagination['total']) ?> data
    </p>
    <div class="flex gap-1">
      <?php if ($pagination['has_prev']): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page_num' => $pagination['prev_page']])) ?>"
         class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition">←</a>
      <?php endif; ?>
      <?php for ($i = max(1, $pagination['current_page']-2); $i <= min($pagination['total_pages'], $pagination['current_page']+2); $i++): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page_num' => $i])) ?>"
         class="px-3 py-1.5 rounded-lg text-sm transition <?= $i === $pagination['current_page'] ? 'bg-nusa text-white' : 'border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
        <?= $i ?>
      </a>
      <?php endfor; ?>
      <?php if ($pagination['has_next']): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page_num' => $pagination['next_page']])) ?>"
         class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition">→</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
function deletePengajuan(id) {
  Swal.fire({
    title: 'Hapus Pengajuan?',
    text: 'Data yang dihapus tidak dapat dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#C1121F',
    cancelButtonColor: '#6B7280',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal',
  }).then(async result => {
    if (result.isConfirmed) {
      const ok = await ajaxAction(BASE_URL + '/?page=admin/delete-pengajuan', {id}, 'Data berhasil dihapus', 'Gagal menghapus data');
      if (ok) setTimeout(() => location.reload(), 2500);
    }
  });
}

async function approvePengajuan(id) {
  const { value: catatan } = await Swal.fire({
    title: 'Setujui Pengajuan?',
    text: 'Pengajuan ini akan disetujui.',
    input: 'textarea',
    inputPlaceholder: 'Catatan opsional...',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#10B981',
    cancelButtonColor: '#6B7280',
    confirmButtonText: '✓ Ya, Setujui',
    cancelButtonText: 'Batal',
  });
  if (catatan !== undefined) {
    const ok = await ajaxAction(BASE_URL + '/?page=admin/approve', {id, catatan}, 'Pengajuan disetujui', 'Gagal menyetujui');
    if (ok) setTimeout(() => location.reload(), 1500);
  }
}

async function rejectPengajuan(id) {
  const { value: catatan } = await Swal.fire({
    title: 'Tolak Pengajuan?',
    text: 'Pengajuan ini akan ditolak.',
    input: 'textarea',
    inputPlaceholder: 'Catatan penolakan (wajib)...',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#EF4444',
    cancelButtonColor: '#6B7280',
    confirmButtonText: 'Ya, Tolak',
    cancelButtonText: 'Batal',
    inputValidator: (value) => {
      if (!value) return 'Anda harus mengisi catatan penolakan!';
    }
  });
  if (catatan) {
    const ok = await ajaxAction(BASE_URL + '/?page=admin/reject', {id, catatan}, 'Pengajuan ditolak', 'Gagal menolak');
    if (ok) setTimeout(() => location.reload(), 1500);
  }
}
</script>

<?php include BASE_PATH . '/includes/admin_layout_bottom.php'; ?>
