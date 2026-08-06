<?php
requireAdminOrKaprodi();
$data['title'] = 'Detail Pengajuan - ' . APP_NAME;
include BASE_PATH . '/includes/admin_layout_top.php';
$p         = $data['pengajuan'];
$signature = $data['signature'];
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center gap-4">
  <div class="flex items-center gap-4">
    <a href="<?= APP_URL ?>/?page=admin/pengajuan"
       class="p-2 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition flex-shrink-0">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    </a>
    <div>
      <h1 class="font-display font-bold text-2xl text-slate-800 dark:text-white">Detail Pengajuan</h1>
      <p class="text-slate-500 dark:text-slate-400 text-sm"><?= e($p['nomor_surat'] ?? 'Nomor belum ditetapkan') ?></p>
    </div>
  </div>
  <div class="md:ml-auto flex flex-wrap gap-2 items-center">
    <span class="badge <?= statusBadge($p['status']) ?> text-sm px-3 py-1.5"><?= statusLabel($p['status']) ?></span>
    <?php if ($p['bersedia_mundur'] === 'YES'): ?>
    <a href="<?= APP_URL ?>/?page=pdf&id=<?= $p['id'] ?>&print=1" target="_blank"
       class="flex-1 md:flex-none flex items-center justify-center gap-1.5 px-4 py-2 bg-nusa text-white text-sm font-semibold rounded-xl hover:bg-nusa-dark transition shadow-md shadow-nusa/30 whitespace-nowrap">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
      Cetak PDF
    </a>
    <a href="<?= APP_URL ?>/?page=docx&id=<?= $p['id'] ?>"
       class="flex-1 md:flex-none flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition shadow-md shadow-blue-600/30 whitespace-nowrap">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
      Unduh Word
    </a>
    <?php endif; ?>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">

  <!-- Main Data -->
  <div class="lg:col-span-2 space-y-6">

    <!-- Data Mahasiswa -->
    <div class="card p-6">
      <h2 class="font-display font-bold text-slate-700 dark:text-slate-200 text-sm mb-4 uppercase tracking-wider">Data Mahasiswa</h2>
      <div class="grid md:grid-cols-2 gap-4 text-sm">
        <?php $fields = [
          'Nama Pemohon'     => $p['nama_pemohon'],
          'NIM'              => $p['nim'],
          'Angkatan'         => $p['angkatan'],
          'Program Studi'    => $p['program_studi'],
          'Status Mahasiswa' => $p['status_mahasiswa'],
          'Tanggal Surat'    => formatTanggal($p['tanggal_surat'], true),
        ]; ?>
        <?php foreach ($fields as $label => $value): ?>
        <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
          <p class="text-slate-400 dark:text-slate-500 text-xs font-medium uppercase tracking-wide mb-1"><?= e($label) ?></p>
          <p class="text-slate-800 dark:text-slate-200 font-semibold"><?= e($value) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Pernyataan -->
    <div class="card p-6">
      <h2 class="font-display font-bold text-slate-700 dark:text-slate-200 text-sm mb-4 uppercase tracking-wider">Pernyataan</h2>

      <div class="flex items-center gap-3 p-4 rounded-xl mb-4 <?= $p['bersedia_mundur'] === 'YES' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700' ?>">
        <span class="text-2xl"><?= $p['bersedia_mundur'] === 'YES' ? '✅' : '❌' ?></span>
        <div>
          <p class="font-semibold <?= $p['bersedia_mundur'] === 'YES' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' ?>">
            <?= $p['bersedia_mundur'] === 'YES' ? 'Bersedia Mengundurkan Diri' : 'Tidak Bersedia Mengundurkan Diri' ?>
          </p>
        </div>
      </div>

      <div>
        <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wide mb-2">Alasan Pengunduran Diri:</p>
        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-xl p-4 text-slate-700 dark:text-slate-300 text-sm leading-relaxed italic border border-slate-100 dark:border-slate-600">
          <?= nl2br(e($p['alasan'])) ?>
        </div>
      </div>

      <!-- Signature -->
      <?php if ($signature && $p['bersedia_mundur'] === 'YES'): ?>
      <div class="mt-5 pt-5 border-t border-slate-200 dark:border-slate-600">
        <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wide mb-3">Tanda Tangan Digital:</p>
        <div class="bg-white border border-slate-200 rounded-xl p-3 inline-block">
          <img src="<?= e($signature['signature_data']) ?>" class="max-h-24" alt="Tanda Tangan">
        </div>
        <p class="text-slate-400 text-xs mt-2">Ditandatangani pada: <?= formatDatetime($signature['signed_at']) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <!-- Nomor Surat (Update) -->
    <div class="card p-6">
      <h2 class="font-display font-bold text-slate-700 dark:text-slate-200 text-sm mb-4 uppercase tracking-wider">Nomor Surat</h2>
      <div class="flex gap-3">
        <input type="text" id="nomorSuratInput" value="<?= e($p['nomor_surat'] ?? '') ?>"
          placeholder="Contoh: NPU/PD/2024/06/0001"
          class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50 font-mono">
        <button onclick="updateNomor(<?= $p['id'] ?>)"
          class="px-5 py-2.5 bg-nusa text-white text-sm font-semibold rounded-xl hover:bg-nusa-dark transition shadow-md shadow-nusa/20">
          Simpan
        </button>
      </div>
    </div>
  </div>

  <!-- Sidebar: Admin Actions -->
  <div class="space-y-6">

    <!-- Status Timeline -->
    <div class="card p-5">
      <h2 class="font-display font-bold text-slate-700 dark:text-slate-200 text-sm mb-4 uppercase tracking-wider">Status</h2>

      <div class="space-y-3">
        <?php
        $timeline = [
          ['label' => 'Pengajuan Diterima', 'time' => $p['created_at'], 'done' => true],
          ['label' => 'Menunggu Review', 'time' => $p['created_at'], 'done' => in_array($p['status'], ['Pending','Approved','Rejected'])],
          ['label' => 'Keputusan Admin', 'time' => $p['approved_at'] ?? null, 'done' => in_array($p['status'], ['Approved','Rejected'])],
        ];
        foreach ($timeline as $i => $tl):
        ?>
        <div class="flex gap-3">
          <div class="flex flex-col items-center">
            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center <?= $tl['done'] ? 'bg-nusa border-nusa' : 'border-slate-300 dark:border-slate-600' ?>">
              <?php if ($tl['done']): ?>
              <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              <?php endif; ?>
            </div>
            <?php if ($i < count($timeline)-1): ?>
            <div class="w-0.5 h-6 <?= $tl['done'] ? 'bg-nusa' : 'bg-slate-200 dark:bg-slate-700' ?>"></div>
            <?php endif; ?>
          </div>
          <div class="pb-3">
            <p class="text-sm font-medium <?= $tl['done'] ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 dark:text-slate-500' ?>"><?= e($tl['label']) ?></p>
            <?php if ($tl['done'] && $tl['time']): ?>
            <p class="text-xs text-slate-400 dark:text-slate-500"><?= formatDatetime($tl['time']) ?></p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Approval Actions -->
    <?php if ($p['status'] === 'Pending'): ?>
    <div class="card p-5">
      <h2 class="font-display font-bold text-slate-700 dark:text-slate-200 text-sm mb-4 uppercase tracking-wider">Tindakan</h2>

      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">Catatan (opsional)</label>
        <textarea id="adminCatatan" rows="3" placeholder="Catatan untuk mahasiswa..."
          class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-nusa/50 resize-none"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <button onclick="approveAction(<?= $p['id'] ?>)"
          class="flex items-center justify-center gap-2 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition shadow-md shadow-green-600/20">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Setujui
        </button>
        <button onclick="rejectAction(<?= $p['id'] ?>)"
          class="flex items-center justify-center gap-2 py-3 bg-nusa text-white font-semibold rounded-xl hover:bg-nusa-dark transition shadow-md shadow-nusa/20">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          Tolak
        </button>
      </div>
    </div>
    <?php elseif ($p['status'] !== 'Draft'): ?>
    <div class="card p-5">
      <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 mb-2">
        <?= $p['status'] === 'Approved' ? '✅ Disetujui oleh' : '❌ Ditolak oleh' ?>
      </p>
      <p class="text-slate-800 dark:text-slate-200 font-bold"><?= e($p['approved_by_nama'] ?? 'Administrator') ?></p>
      <p class="text-slate-400 text-xs mt-1"><?= formatDatetime($p['approved_at'] ?? $p['updated_at']) ?></p>
      <?php if (!empty($p['catatan_admin'])): ?>
      <div class="mt-3 p-3 bg-slate-50 dark:bg-slate-700 rounded-xl text-sm text-slate-600 dark:text-slate-300 italic">
        "<?= e($p['catatan_admin']) ?>"
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Meta Info -->
    <div class="card p-5 text-xs text-slate-500 dark:text-slate-400 space-y-2">
      <div class="flex justify-between"><span>ID Pengajuan</span><span class="font-mono font-bold text-slate-700 dark:text-slate-300">#<?= $p['id'] ?></span></div>
      <div class="flex justify-between"><span>IP Address</span><span class="font-mono"><?= e($p['ip_address'] ?? '—') ?></span></div>
      <div class="flex justify-between"><span>Dibuat</span><span><?= formatDatetime($p['created_at']) ?></span></div>
      <div class="flex justify-between"><span>Diperbarui</span><span><?= formatDatetime($p['updated_at']) ?></span></div>
    </div>
  </div>
</div>

<script>
async function approveAction(id) {
  const catatan = document.getElementById('adminCatatan')?.value || '';
  const result = await Swal.fire({
    title: 'Setujui Pengajuan?',
    text: 'Pengajuan ini akan disetujui dan mahasiswa akan mendapat notifikasi.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#16A34A',
    cancelButtonColor: '#6B7280',
    confirmButtonText: '✓ Ya, Setujui',
    cancelButtonText: 'Batal',
  });
  if (result.isConfirmed) {
    const ok = await ajaxAction(BASE_URL + '/?page=admin/approve', {id, catatan}, 'Pengajuan disetujui', 'Gagal menyetujui');
    if (ok) setTimeout(() => location.reload(), 2500);
  }
}

async function rejectAction(id) {
  const catatan = document.getElementById('adminCatatan')?.value || '';
  if (!catatan.trim()) {
    Swal.fire({ icon:'warning', title:'Catatan Diperlukan', text:'Silakan isi catatan alasan penolakan terlebih dahulu.', confirmButtonColor:'#C1121F' });
    return;
  }
  const result = await Swal.fire({
    title: 'Tolak Pengajuan?',
    html: `<p>Pengajuan ini akan <strong class="text-red-600">ditolak</strong>.</p><p class="text-sm text-gray-500 mt-1">Catatan: ${catatan}</p>`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#C1121F',
    cancelButtonColor: '#6B7280',
    confirmButtonText: 'Ya, Tolak',
    cancelButtonText: 'Batal',
  });
  if (result.isConfirmed) {
    const ok = await ajaxAction(BASE_URL + '/?page=admin/reject', {id, catatan}, 'Pengajuan ditolak', 'Gagal menolak');
    if (ok) setTimeout(() => location.reload(), 2500);
  }
}

async function updateNomor(id) {
  const nomor = document.getElementById('nomorSuratInput').value;
  if (!nomor) { showToast('warning', 'Nomor surat tidak boleh kosong'); return; }
  const ok = await ajaxAction(BASE_URL + '/?page=admin/update-nomor', {id, nomor_surat: nomor}, 'Nomor surat diperbarui', 'Gagal memperbarui nomor');
  if (ok) showToast('success', 'Nomor surat: ' + nomor);
}
</script>

<?php include BASE_PATH . '/includes/admin_layout_bottom.php'; ?>
