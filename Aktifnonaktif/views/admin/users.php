<?php
requireAdmin();
$data['title'] = 'Manajemen Users - ' . APP_NAME;
include BASE_PATH . '/includes/admin_layout_top.php';
$users = $data['users'];
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
  <div>
    <h1 class="font-display font-bold text-2xl text-slate-800 dark:text-white">Manajemen Users</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm">Daftar semua pengguna sistem</p>
  </div>
  <button onclick="openModal()"
    class="flex items-center justify-center sm:justify-start gap-2 px-4 py-2.5 bg-nusa text-white text-sm font-semibold rounded-xl hover:bg-nusa-dark shadow-md shadow-nusa/30 transition w-full sm:w-auto">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Tambah User
  </button>
</div>

<?php foreach (getFlash('success') as $msg): ?>
<div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 text-sm rounded-xl px-4 py-3 mb-5">✓ <?= e($msg) ?></div>
<?php endforeach; ?>
<?php foreach (getFlash('error') as $msg): ?>
<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 text-sm rounded-xl px-4 py-3 mb-5">✗ <?= e($msg) ?></div>
<?php endforeach; ?>

<div class="card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-700/50">
        <tr>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">No</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nama</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Email</th>
          <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Role</th>
          <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">TTD</th>
          <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Login Terakhir</th>
          <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php foreach ($users as $i => $u): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
          <td class="px-4 py-3 text-slate-500"><?= $i + 1 ?></td>
          <td class="px-4 py-3">
            <div class="flex items-center gap-3">
              <?php if (!empty($u['avatar'])): ?>
                <?php $avatarSrc = APP_URL . '/avatar.php?url=' . urlencode($u['avatar']); ?>
                <img src="<?= $avatarSrc ?>" alt="<?= e($u['nama']) ?>"
                  class="w-8 h-8 rounded-full object-cover ring-2 ring-slate-100 dark:ring-slate-700 flex-shrink-0"
                  onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="w-8 h-8 rounded-full items-center justify-center font-bold text-xs flex-shrink-0 <?= $u['role'] === 'admin' ? 'bg-amber-100 text-amber-700' : 'bg-nusa/10 text-nusa' ?>" style="display:none;">
                  <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                </div>
              <?php else: ?>
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0 <?= $u['role'] === 'admin' ? 'bg-amber-100 text-amber-700' : 'bg-nusa/10 text-nusa' ?>">
                  <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                </div>
              <?php endif; ?>
              <div>
                <span class="font-semibold text-slate-800 dark:text-slate-200"><?= e($u['nama']) ?></span>
                <?php if (!empty($u['program_studi'])): ?>
                <p class="text-xs text-slate-400"><?= e($u['program_studi']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td class="px-4 py-3 text-slate-500 dark:text-slate-400"><?= e($u['email']) ?></td>
          <td class="px-4 py-3 text-center">
            <span class="badge <?= $u['role'] === 'admin' ? 'bg-amber-100 text-amber-700' : ($u['role'] === 'kaprodi' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400') ?>">
              <?= ucfirst($u['role']) ?>
            </span>
          </td>
          <!-- Kolom TTD: hanya tampil untuk kaprodi -->
          <td class="px-4 py-3 text-center">
            <?php if ($u['role'] === 'kaprodi'): ?>
              <?php if (!empty($u['ttd_path'])): ?>
                <img src="<?= e(APP_URL . '/' . ltrim($u['ttd_path'], '/')) ?>"
                     alt="TTD <?= e($u['nama']) ?>"
                     class="h-8 max-w-[80px] object-contain mx-auto cursor-pointer rounded border border-slate-200 bg-white p-0.5"
                     title="Tanda tangan <?= e($u['nama']) ?>"
                     onclick="previewTtd('<?= e(APP_URL . '/' . ltrim($u['ttd_path'], '/')) ?>', '<?= e($u['nama']) ?>')">
              <?php else: ?>
                <span class="text-xs text-slate-400 italic">Belum ada</span>
              <?php endif; ?>
            <?php else: ?>
              <span class="text-slate-300 dark:text-slate-600">—</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-3 text-center">
            <span class="badge <?= $u['is_active'] ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700' ?>">
              <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
            </span>
          </td>
          <td class="px-4 py-3 text-slate-400 text-xs">
            <?= $u['last_login'] ? timeAgo($u['last_login']) : 'Belum pernah' ?>
          </td>
          <td class="px-4 py-3">
            <div class="flex justify-center gap-2">
              <button onclick="editUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)"
                class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 transition" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
              <?php if ($u['role'] !== 'admin' && $u['id'] !== currentUser()['id']): ?>
              <button onclick="deleteUser(<?= $u['id'] ?>)"
                class="p-1.5 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 transition" title="Hapus">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</div>

<!-- Modal Add/Edit User -->
<div id="modalUser" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50">
  <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
    <div class="flex items-center justify-between p-5 border-b border-slate-200 dark:border-slate-700 flex-shrink-0">
      <h3 id="modalTitle" class="font-display font-bold text-slate-800 dark:text-white text-lg">Tambah User</h3>
      <button onclick="closeModal()" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition text-slate-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <!-- Form WAJIB enctype multipart agar upload file bisa bekerja -->
    <form method="POST" action="<?= APP_URL ?>/?page=admin/users/save"
          enctype="multipart/form-data"
          class="p-5 overflow-y-auto flex-1">
      <?= csrfField() ?>
      <input type="hidden" name="id" id="modalId" value="0">
      <input type="hidden" name="hapus_ttd" id="hapusTtdInput" value="0">
      
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap <span class="text-nusa">*</span></label>
          <input type="text" name="nama" id="modal_nama" required
            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
        </div>
        
        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email <span class="text-nusa">*</span></label>
          <input type="email" name="email" id="modal_email" required
            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password</label>
          <input type="password" name="password" id="modal_password"
            placeholder="Kosongkan jika tidak ingin mengubah (saat edit)"
            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50">
          <p class="text-xs text-slate-500 mt-1" id="password_hint">Wajib diisi untuk user baru.</p>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Role <span class="text-nusa">*</span></label>
            <select name="role" id="modal_role" required onchange="toggleProdi()"
              class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-nusa/50">
              <option value="mahasiswa">Mahasiswa</option>
              <option value="kaprodi">Kaprodi</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="flex items-center mt-7">
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" name="is_active" id="modal_is_active" value="1" class="sr-only peer" checked>
              <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-green-600"></div>
              <span class="ml-3 text-sm font-medium text-slate-700 dark:text-slate-300">Akun Aktif</span>
            </label>
          </div>
        </div>

        <div id="prodi_container" style="display:none;">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Program Studi (Untuk Kaprodi)</label>
          <select name="program_studi" id="modal_program_studi"
            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-nusa/50">
            <option value="">-- Pilih Program Studi --</option>
            <?php foreach (PROGRAM_STUDI as $prodi): ?>
            <option value="<?= e($prodi) ?>"><?= e($prodi) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Upload TTD — hanya tampil untuk role Kaprodi -->
        <div id="ttd_container" style="display:none;">
          <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            ✍️ Tanda Tangan (TTD)
            <span class="text-slate-400 font-normal text-xs ml-1">PNG/JPG, maks 2MB</span>
          </label>

          <!-- Preview TTD yang sudah ada -->
          <div id="ttd_current" class="mb-3 hidden">
            <p class="text-xs text-slate-500 mb-1.5">TTD saat ini:</p>
            <div class="flex items-center gap-3 p-2 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600">
              <img id="ttd_current_img" src="" alt="TTD Saat Ini"
                   class="h-12 max-w-[120px] object-contain rounded bg-white p-1 border border-slate-200">
              <button type="button" onclick="hapusTtd()"
                class="ml-auto flex items-center gap-1 px-3 py-1.5 text-xs text-red-600 bg-red-50 hover:bg-red-100 rounded-lg font-medium transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus TTD
              </button>
            </div>
          </div>

          <!-- Drop zone upload file baru -->
          <label for="ttd_file_input"
            class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition group">
            <div id="ttd_drop_content" class="flex flex-col items-center gap-1.5 text-center px-4">
              <svg class="w-7 h-7 text-slate-400 group-hover:text-nusa transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
              </svg>
              <p class="text-sm text-slate-500">Klik atau seret file TTD ke sini</p>
              <p class="text-xs text-slate-400">PNG, JPG, GIF · maks 2MB</p>
            </div>
            <div id="ttd_preview_wrap" class="hidden flex-col items-center gap-1">
              <img id="ttd_preview_img" src="" alt="Preview TTD" class="h-14 max-w-[150px] object-contain rounded">
              <p id="ttd_preview_name" class="text-xs text-slate-500"></p>
            </div>
            <input type="file" id="ttd_file_input" name="ttd_file"
                   accept=".png,.jpg,.jpeg,.gif,.webp" class="hidden"
                   onchange="previewTtdUpload(this)">
          </label>
        </div>
      </div>
      
      <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
        <button type="button" onclick="closeModal()" class="px-5 py-2.5 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 text-sm font-medium rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition">Batal</button>
        <button type="submit" class="px-6 py-2.5 bg-nusa text-white text-sm font-semibold rounded-xl hover:bg-nusa-dark transition shadow-md shadow-nusa/20">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Preview TTD Besar -->
<div id="modalPreviewTtd" class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-black/70" onclick="this.style.display='none'">
  <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 max-w-sm w-full shadow-2xl text-center" onclick="event.stopPropagation()">
    <p id="previewTtdNama" class="font-semibold text-slate-700 dark:text-slate-200 mb-4 text-sm"></p>
    <img id="previewTtdImg" src="" alt="Preview TTD" class="max-h-40 max-w-full object-contain mx-auto rounded border border-slate-200 bg-slate-50 p-2">
    <button onclick="document.getElementById('modalPreviewTtd').style.display='none'"
      class="mt-4 px-4 py-2 text-sm text-slate-500 hover:text-slate-700 transition">Tutup</button>
  </div>
</div>

<script>
function openModal(data = null) {
  document.getElementById('modalTitle').textContent = data ? 'Edit User' : 'Tambah User';
  document.getElementById('modalId').value = data?.id || 0;
  document.getElementById('hapusTtdInput').value = '0';
  
  if (data) {
    document.getElementById('modal_nama').value = data.nama || '';
    document.getElementById('modal_email').value = data.email || '';
    document.getElementById('modal_role').value = data.role || 'mahasiswa';
    document.getElementById('modal_is_active').checked = data.is_active == 1;
    document.getElementById('modal_program_studi').value = data.program_studi || '';
    document.getElementById('modal_password').required = false;
    document.getElementById('password_hint').style.display = 'block';

    // TTD saat ini
    const ttdCurrent = document.getElementById('ttd_current');
    const ttdCurrentImg = document.getElementById('ttd_current_img');
    if (data.ttd_path && data.role === 'kaprodi') {
      ttdCurrentImg.src = BASE_URL + '/' + data.ttd_path;
      ttdCurrent.classList.remove('hidden');
    } else {
      ttdCurrent.classList.add('hidden');
    }
  } else {
    document.getElementById('modal_nama').value = '';
    document.getElementById('modal_email').value = '';
    document.getElementById('modal_role').value = 'mahasiswa';
    document.getElementById('modal_is_active').checked = true;
    document.getElementById('modal_program_studi').value = '';
    document.getElementById('modal_password').required = true;
    document.getElementById('password_hint').style.display = 'block';
    document.getElementById('ttd_current').classList.add('hidden');
  }
  document.getElementById('modal_password').value = '';

  // Reset preview upload
  resetTtdUpload();
  toggleProdi();
  
  document.getElementById('modalUser').style.display = 'flex';
}

function toggleProdi() {
  const role = document.getElementById('modal_role').value;
  const isKaprodi = role === 'kaprodi';
  document.getElementById('prodi_container').style.display = isKaprodi ? 'block' : 'none';
  document.getElementById('ttd_container').style.display  = isKaprodi ? 'block' : 'none';
  document.getElementById('modal_program_studi').required = isKaprodi;
}

function previewTtdUpload(input) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('ttd_drop_content').classList.add('hidden');
    const wrap = document.getElementById('ttd_preview_wrap');
    wrap.classList.remove('hidden');
    wrap.style.display = 'flex';
    document.getElementById('ttd_preview_img').src = e.target.result;
    document.getElementById('ttd_preview_name').textContent = file.name;
  };
  reader.readAsDataURL(file);
}

function resetTtdUpload() {
  document.getElementById('ttd_file_input').value = '';
  document.getElementById('ttd_drop_content').classList.remove('hidden');
  const wrap = document.getElementById('ttd_preview_wrap');
  wrap.classList.add('hidden');
  wrap.style.display = 'none';
}

function hapusTtd() {
  document.getElementById('hapusTtdInput').value = '1';
  document.getElementById('ttd_current').classList.add('hidden');
  showToast('info', 'TTD akan dihapus saat disimpan.');
}

function previewTtd(url, nama) {
  document.getElementById('previewTtdImg').src = url;
  document.getElementById('previewTtdNama').textContent = 'TTD: ' + nama;
  document.getElementById('modalPreviewTtd').style.display = 'flex';
}

function editUser(data) { openModal(data); }
function closeModal() { document.getElementById('modalUser').style.display = 'none'; }

async function deleteUser(id) {
  const result = await Swal.fire({
    title: 'Hapus User?', 
    text: 'Aksi ini tidak dapat dibatalkan!',
    icon: 'warning', 
    showCancelButton: true,
    confirmButtonColor: '#C1121F', 
    cancelButtonColor: '#6B7280',
    confirmButtonText: 'Hapus!', 
    cancelButtonText: 'Batal',
  });
  if (result.isConfirmed) {
    const ok = await ajaxAction(BASE_URL + '/?page=admin/users/delete', {id});
    if (ok) setTimeout(() => location.reload(), 2500);
  }
}
</script>

<?php include BASE_PATH . '/includes/admin_layout_bottom.php'; ?>
