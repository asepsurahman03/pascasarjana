<?php
requireAdminOrKaprodi();
$data['title'] = 'Data Mahasiswa - ' . APP_NAME;
include BASE_PATH . '/includes/admin_layout_top.php';
$list       = $data['list'];
$filters    = $data['filters'];
$pagination = $data['pagination'];
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h1 class="font-display font-bold text-2xl text-slate-800 dark:text-white">Data Mahasiswa</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm">Total <?= number_format($pagination['total']) ?> mahasiswa terdaftar</p>
  </div>
  <button onclick="openModal()"
    class="flex items-center justify-center sm:justify-start gap-2 px-4 py-2.5 bg-nusa text-white text-sm font-semibold rounded-xl hover:bg-nusa-dark shadow-md shadow-nusa/30 transition w-full sm:w-auto">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Tambah Mahasiswa
  </button>
</div>

<!-- Filters -->
<div class="card p-4 mb-5">
  <form method="GET" class="flex flex-wrap gap-3">
    <input type="hidden" name="page" value="admin/mahasiswa">
    <input type="text" name="search" placeholder="🔍 Cari NIM atau nama..."
      value="<?= e($filters['search']) ?>"
      class="flex-1 min-w-[200px] px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50 text-slate-800 dark:text-slate-200">
    <?php if (isKaprodi()): ?>
      <input type="hidden" name="program_studi" value="<?= e($filters['program_studi']) ?>">
      <div class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/50 text-slate-600 dark:text-slate-400 text-sm truncate">
        <?= e($filters['program_studi']) ?>
      </div>
    <?php else: ?>
    <select name="program_studi" class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-nusa/50">
      <option value="">Semua Prodi</option>
      <?php foreach (PROGRAM_STUDI as $prodi): ?>
      <option value="<?= e($prodi) ?>" <?= $filters['program_studi'] === $prodi ? 'selected' : '' ?>><?= e($prodi) ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <input type="number" name="angkatan" placeholder="Angkatan" min="2000" max="2030"
      value="<?= e($filters['angkatan']) ?>"
      class="w-28 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50 text-slate-700 dark:text-slate-300">
    <button type="submit" class="px-4 py-2 bg-nusa text-white text-sm font-semibold rounded-lg hover:bg-nusa-dark transition">Filter</button>
    <a href="?page=admin/mahasiswa" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 text-sm rounded-lg hover:bg-slate-200 transition">Reset</a>
  </form>
</div>

<!-- Table -->
<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-700/50">
        <tr>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">No</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Mahasiswa</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Program Studi</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Angkatan</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
          <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php if (empty($list)): ?>
        <tr><td colspan="6" class="px-4 py-16 text-center text-slate-400">Tidak ada data</td></tr>
        <?php else: ?>
        <?php foreach ($list as $no => $m): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
          <td class="px-4 py-3 text-slate-500 font-medium"><?= ($pagination['offset'] ?? 0) + $no + 1 ?></td>
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-gradient-to-br from-nusa to-nusa-dark flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                <?= strtoupper(substr($m['nama'], 0, 1)) ?>
              </div>
              <div>
                <p class="font-semibold text-slate-800 dark:text-slate-200"><?= e($m['nama']) ?></p>
                <p class="text-slate-400 text-xs"><?= e($m['nim']) ?> · <?= e($m['email'] ?? '—') ?></p>
              </div>
            </div>
          </td>
          <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs"><?= e(str_replace('Magister ', 'M. ', $m['program_studi'])) ?></td>
          <td class="px-4 py-3 text-slate-600 dark:text-slate-400 font-medium"><?= e($m['angkatan']) ?></td>
          <td class="px-4 py-3">
            <span class="badge <?= $m['status_beasiswa'] === 'Beasiswa' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400' ?>">
              <?= e($m['status_beasiswa']) ?>
            </span>
          </td>
          <td class="px-4 py-3">
            <div class="flex justify-center gap-2">
              <button onclick="editMahasiswa(<?= htmlspecialchars(json_encode($m), ENT_QUOTES) ?>)"
                class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 transition" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
              <button onclick="deleteMahasiswa(<?= $m['id'] ?>)"
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
  <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
    <p class="text-sm text-slate-500">Halaman <?= $pagination['current_page'] ?> dari <?= $pagination['total_pages'] ?></p>
    <div class="flex gap-1">
      <?php if ($pagination['has_prev']): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page_num' => $pagination['prev_page']])) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 text-sm hover:bg-slate-50 transition">←</a>
      <?php endif; ?>
      <?php if ($pagination['has_next']): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page_num' => $pagination['next_page']])) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-600 text-sm hover:bg-slate-50 transition">→</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Add/Edit Mahasiswa -->
<div id="modalMahasiswa" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50">
  <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden">
    <div class="flex items-center justify-between p-5 border-b border-slate-200 dark:border-slate-700">
      <h3 id="modalTitle" class="font-display font-bold text-slate-800 dark:text-white text-lg">Tambah Mahasiswa</h3>
      <button onclick="closeModal()" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition text-slate-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/?page=admin/mahasiswa/save" class="p-5">
      <?= csrfField() ?>
      <input type="hidden" name="id" id="modalId" value="0">
      <div class="grid md:grid-cols-2 gap-4">
        <?php
        $mFields = [
          ['name'=>'nim',             'label'=>'NIM',             'type'=>'text',   'req'=>true],
          ['name'=>'nama',            'label'=>'Nama Lengkap',    'type'=>'text',   'req'=>true],
          ['name'=>'email',           'label'=>'Email',           'type'=>'email',  'req'=>false],
          ['name'=>'tanggal_lahir',   'label'=>'Tanggal Lahir',   'type'=>'date',   'req'=>true],
          ['name'=>'angkatan',        'label'=>'Angkatan',        'type'=>'number', 'req'=>true],
          ['name'=>'no_hp',           'label'=>'No. HP',          'type'=>'tel',    'req'=>false],
        ];
        foreach ($mFields as $f):
        ?>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            <?= $f['label'] ?> <?= $f['req'] ? '<span class="text-nusa">*</span>' : '' ?>
          </label>
          <input type="<?= $f['type'] ?>" name="<?= $f['name'] ?>" id="modal_<?= $f['name'] ?>"
            <?= $f['req'] ? 'required' : '' ?>
            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
        </div>
        <?php endforeach; ?>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Program Studi <span class="text-nusa">*</span></label>
          <?php if (isKaprodi()): ?>
            <input type="text" name="program_studi" id="modal_program_studi_display" value="<?= e(currentUser()['program_studi']) ?>" readonly class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-700 text-sm text-slate-600 dark:text-slate-400 cursor-not-allowed">
            <input type="hidden" name="program_studi" id="modal_program_studi" value="<?= e(currentUser()['program_studi']) ?>">
          <?php else: ?>
          <select name="program_studi" id="modal_program_studi" required
            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-nusa/50">
            <?php foreach (PROGRAM_STUDI as $prodi): ?>
            <option value="<?= e($prodi) ?>"><?= e($prodi) ?></option>
            <?php endforeach; ?>
          </select>
          <?php endif; ?>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status <span class="text-nusa">*</span></label>
          <select name="status_beasiswa" id="modal_status_beasiswa" required
            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-nusa/50">
            <option value="Non Beasiswa">Non Beasiswa</option>
            <option value="Beasiswa">Beasiswa</option>
          </select>
        </div>
      </div>
      <div class="mt-4">
        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat</label>
        <textarea name="alamat" id="modal_alamat" rows="2"
          class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50 resize-none"></textarea>
      </div>
      <div class="flex justify-end gap-3 mt-5 pt-4 border-t border-slate-200 dark:border-slate-700">
        <button type="button" onclick="closeModal()" class="px-5 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 text-sm font-medium rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">Batal</button>
        <button type="submit" class="px-6 py-2.5 bg-nusa text-white text-sm font-semibold rounded-xl hover:bg-nusa-dark transition shadow-md shadow-nusa/20">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(data = null) {
  document.getElementById('modalTitle').textContent = data ? 'Edit Mahasiswa' : 'Tambah Mahasiswa';
  document.getElementById('modalId').value = data?.id || 0;
  ['nim','nama','email','tanggal_lahir','angkatan','no_hp','program_studi','status_beasiswa','alamat'].forEach(f => {
    const el = document.getElementById('modal_' + f);
    if (el) el.value = data ? (data[f] || '') : '';
  });
  document.getElementById('modalMahasiswa').style.display = 'flex';
}
function editMahasiswa(data) { openModal(data); }
function closeModal() { document.getElementById('modalMahasiswa').style.display = 'none'; }
async function deleteMahasiswa(id) {
  const result = await Swal.fire({
    title: 'Hapus Mahasiswa?', text:'Data mahasiswa dan seluruh pengajuannya akan dihapus!',
    icon:'warning', showCancelButton:true,
    confirmButtonColor:'#C1121F', cancelButtonColor:'#6B7280',
    confirmButtonText:'Hapus!', cancelButtonText:'Batal',
  });
  if (result.isConfirmed) {
    const ok = await ajaxAction(BASE_URL + '/?page=admin/mahasiswa/delete', {id});
    if (ok) setTimeout(() => location.reload(), 2500);
  }
}
</script>

<?php include BASE_PATH . '/includes/admin_layout_bottom.php'; ?>
