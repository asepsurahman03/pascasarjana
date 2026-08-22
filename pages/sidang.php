<?php
$pageTitle = 'Manajemen Sidang & Seminar';
require_once __DIR__.'/../includes/functions.php';
requireAdmin();

// ─── Handle Admin POST Actions ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'update_status' && $id > 0) {
        $newStatus = trim($_POST['status'] ?? 'Pending');
        $urgent = isset($_POST['urgent']) ? 1 : 0;
        
        dbExecute(
            "UPDATE pendaftaran_sidang SET status = ?, urgent = ?, updated_at = NOW() WHERE id = ?",
            [$newStatus, $urgent, $id]
        );
        $_SESSION['flash'] = ['type' => 'success', 'message' => "Status pendaftaran berhasil diperbarui menjadi '$newStatus'."];
        header('Location: sidang');
        exit;
    }

    if ($action === 'delete' && $id > 0) {
        // Optional: Remove physical uploaded files if desired
        $row = dbQueryOne("SELECT * FROM pendaftaran_sidang WHERE id = ?", [$id]);
        if ($row) {
            $berkasCols = [
                'berkas_jurnal', 'berkas_bukti_bayar_jurnal', 'berkas_persetujuan', 'berkas_khs',
                'berkas_bebas_perpus', 'berkas_buku_sumbangan', 'berkas_bebas_admin', 'berkas_foto',
                'berkas_draft_tesis', 'berkas_code_program', 'berkas_presentasi', 'berkas_plagiarisme'
            ];
            foreach ($berkasCols as $col) {
                if (!empty($row[$col])) {
                    $filePath = __DIR__ . '/../' . ltrim($row[$col], '/');
                    if (file_exists($filePath) && is_file($filePath)) {
                        @unlink($filePath);
                    }
                }
            }
        }
        dbExecute("DELETE FROM pendaftaran_sidang WHERE id = ?", [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data pendaftaran sidang berhasil dihapus.'];
        header('Location: sidang');
        exit;
    }
}

// ─── Filter & Search ──────────────────────────────────────────────────────────
$fs = trim($_GET['status'] ?? '');
$fp = (int)($_GET['prodi_id'] ?? 0);
$q  = trim($_GET['q'] ?? '');

$params = [];
$where = ['1=1'];

if ($fs !== '') {
    $where[] = 'ps.status = ?';
    $params[] = $fs;
}
if ($fp > 0) {
    $where[] = 'm.prodi_id = ?';
    $params[] = $fp;
}
if ($q !== '') {
    $where[] = '(m.nama LIKE ? OR m.nim LIKE ? OR ps.judul_tesis LIKE ? OR ps.email LIKE ? OR ps.no_hp LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$whereClause = implode(' AND ', $where);

// ─── Fetch Registrations ──────────────────────────────────────────────────────
$pendaftaran = dbQuery("
    SELECT ps.*,
           m.nama as mhs_nama, m.nim, m.email as mhs_email_db, m.no_hp as mhs_hp_db,
           p.id as prodi_id, p.nama as pnama, p.warna_hex,
           mp.judul_artikel as pub_judul, mp.nama_jurnal as pub_jurnal, mp.doi as pub_doi, mp.status_publikasi as pub_status
    FROM pendaftaran_sidang ps
    LEFT JOIN mahasiswa m ON m.id = ps.mahasiswa_id
    LEFT JOIN prodi p ON p.id = m.prodi_id
    LEFT JOIN mahasiswa_publikasi mp ON mp.pendaftaran_sidang_id = ps.id
    WHERE $whereClause
    ORDER BY ps.urgent DESC, ps.created_at DESC
    LIMIT 100
", $params) ?? [];

// ─── Count Statistics (4 Clean Statuses) ───────────────────────────────────────
$statTotal   = dbCount('pendaftaran_sidang', "1=1");
$statPending = dbCount('pendaftaran_sidang', "status IN ('Pending','Menunggu Review','Verifikasi Berkas')");
$statVerif   = dbCount('pendaftaran_sidang', "status IN ('Diverifikasi','Polling Jadwal','Jadwal Ditetapkan')");
$statSelesai = dbCount('pendaftaran_sidang', "status = 'Selesai'");

$allProdi = getAllProdi();

$statusStyles = [
    'Pending'             => 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
    'Menunggu Verifikasi' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
    'Menunggu Review'     => 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
    'Verifikasi Berkas'   => 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
    'Diverifikasi'        => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
    'Selesai'             => 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800',
    'Ditolak'             => 'bg-rose-100 dark:bg-rose-900/30 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800'
];

require_once __DIR__.'/../includes/header.php';
?>

<div x-data="{
  modalOpen: false,
  selectedItem: null,
  activeTab: 'berkas',
  openDetail(item) {
    this.selectedItem = item;
    this.modalOpen = true;
    this.activeTab = 'berkas';
  },
  getWhatsappLink(phone) {
    if (!phone) return '#';
    let clean = phone.replace(/[^0-9]/g, '');
    if (clean.startsWith('0')) clean = '62' + clean.slice(1);
    return 'https://wa.me/' + clean;
  }
}">

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
      <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
        <span>Akademik</span>
        <span>/</span>
        <span class="text-[#8c0c4c] dark:text-pink-400 font-bold">Pendaftaran Sidang</span>
      </div>
      <h1 class="font-display font-extrabold text-2xl md:text-3xl text-slate-800 dark:text-white">
        Manajemen Sidang & Seminar
      </h1>
      <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
        Verifikasi kelengkapan 12 dokumen persyaratan, pantau luaran publikasi, dan kelola status sidang mahasiswa
      </p>
    </div>
  </div>

  <!-- Stats Cards (4 Cards) -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-600 to-slate-800 text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-xs">
        📑
      </div>
      <div>
        <div class="text-2xl font-extrabold text-slate-800 dark:text-white"><?= $statTotal ?></div>
        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-0.5">Total Pendaftaran</div>
      </div>
    </div>

    <div class="bg-white dark:bg-slate-800 border border-amber-200/80 dark:border-amber-800/60 rounded-2xl p-5 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-xs">
        ⏳
      </div>
      <div>
        <div class="text-2xl font-extrabold text-amber-700 dark:text-amber-300"><?= $statPending ?></div>
        <div class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider mt-0.5">Menunggu Verifikasi</div>
      </div>
    </div>

    <div class="bg-white dark:bg-slate-800 border border-emerald-200/80 dark:border-emerald-800/60 rounded-2xl p-5 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-xs">
        ✓
      </div>
      <div>
        <div class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300"><?= $statVerif ?></div>
        <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mt-0.5">Diverifikasi / Lolos</div>
      </div>
    </div>

    <div class="bg-white dark:bg-slate-800 border border-purple-200/80 dark:border-purple-800/60 rounded-2xl p-5 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-xs">
        🎓
      </div>
      <div>
        <div class="text-2xl font-extrabold text-[#8c0c4c] dark:text-pink-300"><?= $statSelesai ?></div>
        <div class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-0.5">Sidang Selesai</div>
      </div>
    </div>

  </div>

  <!-- Filter Bar -->
  <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-xs p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
      
      <div class="flex-1 min-w-[220px]">
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <input type="text" name="q" value="<?= e($q) ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" placeholder="Cari Nama, NIM, Judul Tesis, Email...">
        </div>
      </div>

      <div class="min-w-[170px]">
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Program Studi</label>
        <select name="prodi_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
          <option value="">Semua Prodi</option>
          <?php foreach ($allProdi as $pr): ?>
          <option value="<?= $pr['id'] ?>" <?= $fp == $pr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pr['nama']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="min-w-[170px]">
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
        <select name="status" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
          <option value="">Semua Status</option>
          <option value="Pending" <?= $fs === 'Pending' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
          <option value="Diverifikasi" <?= $fs === 'Diverifikasi' ? 'selected' : '' ?>>Diverifikasi</option>
          <option value="Selesai" <?= $fs === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
          <option value="Ditolak" <?= $fs === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
        </select>
      </div>

      <div class="flex gap-2">
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] hover:from-[#a3155b] hover:to-[#c41e73] text-white rounded-xl font-bold text-sm shadow-md transition">
          Filter
        </button>
        <a href="sidang" class="inline-flex items-center px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 rounded-xl font-semibold text-sm transition">
          Reset
        </a>
      </div>

    </form>
  </div>

  <!-- Table -->
  <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-3xl shadow-xs overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/80 flex items-center justify-between">
      <div>
        <h2 class="font-display font-extrabold text-lg text-slate-800 dark:text-white">Daftar Pendaftaran Sidang & Seminar</h2>
        <p class="text-xs text-slate-500 mt-0.5">Menampilkan <strong class="text-slate-700 dark:text-slate-300"><?= count($pendaftaran) ?></strong> pendaftaran mahasiswa</p>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/50">
          <tr>
            <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Mahasiswa</th>
            <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Judul Tesis & Pembimbing</th>
            <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Luaran Publikasi</th>
            <th class="text-center py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Berkas</th>
            <th class="text-center py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
            <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal</th>
            <th class="text-right py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
          <?php if (empty($pendaftaran)): ?>
          <tr>
            <td colspan="7" class="py-20">
              <div class="flex flex-col items-center justify-center text-slate-400">
                <div class="w-20 h-20 bg-slate-100 dark:bg-slate-700/30 rounded-3xl flex items-center justify-center mb-3">
                  <svg class="w-10 h-10 opacity-40 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="font-bold text-base text-slate-700 dark:text-slate-300">Belum Ada Data Pendaftaran</p>
                <p class="text-xs text-slate-400 mt-0.5">Tidak ada pendaftaran sidang yang cocok dengan kriteria filter.</p>
              </div>
            </td>
          </tr>
          <?php else: foreach ($pendaftaran as $p):
            // Calculate count of uploaded files out of 12
            $berkasKeys = [
                'berkas_jurnal', 'berkas_bukti_bayar_jurnal', 'berkas_persetujuan', 'berkas_khs',
                'berkas_bebas_perpus', 'berkas_buku_sumbangan', 'berkas_bebas_admin', 'berkas_foto',
                'berkas_draft_tesis', 'berkas_code_program', 'berkas_presentasi', 'berkas_plagiarisme'
            ];
            $uploadedCount = 0;
            foreach ($berkasKeys as $bk) {
                if (!empty($p[$bk])) $uploadedCount++;
            }
            $pct = round(($uploadedCount / 12) * 100);
            $statusCls = $statusStyles[$p['status'] ?? 'Pending'] ?? 'bg-slate-100 text-slate-600 border border-slate-200';
          ?>
          <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group <?= ($p['urgent'] ?? 0) ? 'border-l-4 border-l-rose-500' : '' ?>">
            
            <!-- Mahasiswa -->
            <td class="py-4 px-6">
              <div class="flex items-center gap-3">
                <?php if (!empty($p['berkas_foto'])): ?>
                <img src="../<?= htmlspecialchars(ltrim($p['berkas_foto'], '/')) ?>" class="w-10 h-10 rounded-2xl object-cover border border-slate-200 dark:border-slate-700 shadow-2xs shrink-0">
                <?php else: ?>
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#8c0c4c] to-[#c2527a] text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-2xs">
                  <?= strtoupper(substr($p['mhs_nama'] ?? '?', 0, 1)) ?>
                </div>
                <?php endif; ?>
                <div>
                  <div class="font-bold text-slate-800 dark:text-white group-hover:text-[#8c0c4c] transition-colors">
                    <?= htmlspecialchars($p['mhs_nama'] ?? '-') ?>
                  </div>
                  <div class="text-xs text-slate-400 flex items-center gap-1.5 mt-0.5">
                    <span class="font-mono"><?= htmlspecialchars($p['nim'] ?? '-') ?></span>
                    <?php if (!empty($p['pnama'])): ?>
                    <span>·</span>
                    <span class="font-semibold" style="color:<?= htmlspecialchars($p['warna_hex'] ?? '#888') ?>"><?= htmlspecialchars($p['pnama']) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </td>

            <!-- Judul Tesis & Pembimbing -->
            <td class="py-4 px-6 max-w-xs">
              <div class="font-semibold text-slate-800 dark:text-slate-100 line-clamp-2 text-xs italic" title="<?= htmlspecialchars($p['judul_tesis'] ?? '') ?>">
                "<?= htmlspecialchars($p['judul_tesis'] ?? 'Belum mengisi judul') ?>"
              </div>
              <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                <strong>Pembimbing:</strong> <?= htmlspecialchars($p['pembimbing1'] ?? '-') ?>
                <?php if (!empty($p['pembimbing2'])): ?>
                <span class="text-slate-400">/ <?= htmlspecialchars($p['pembimbing2']) ?></span>
                <?php endif; ?>
              </div>
            </td>

            <!-- Luaran Publikasi -->
            <td class="py-4 px-6">
              <span class="px-2 py-0.5 rounded-lg bg-pink-100 dark:bg-pink-950/40 text-[#8c0c4c] dark:text-pink-300 font-bold text-[11px] inline-block">
                <?= htmlspecialchars($p['status_luaran'] ?? 'Publikasi Jurnal') ?>
              </span>
              <?php if (!empty($p['link_luaran'])): ?>
              <a href="<?= htmlspecialchars($p['link_luaran']) ?>" target="_blank" class="block text-[11px] text-blue-600 dark:text-blue-400 underline truncate max-w-[180px] mt-0.5">
                <?= htmlspecialchars($p['link_luaran']) ?>
              </a>
              <?php endif; ?>
            </td>

            <!-- Kelengkapan Berkas -->
            <td class="py-4 px-6 text-center">
              <div class="inline-flex flex-col items-center">
                <div class="w-20 bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden mb-1">
                  <div class="h-2 rounded-full transition-all <?= $pct >= 100 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-rose-500') ?>" style="width:<?= $pct ?>%"></div>
                </div>
                <span class="text-[11px] font-bold <?= $pct >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500' ?>">
                  <?= $uploadedCount ?> / 12 Berkas
                </span>
              </div>
            </td>

            <!-- Status -->
            <td class="py-4 px-6 text-center">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold <?= $statusCls ?>">
                <?= htmlspecialchars($p['status'] ?? 'Pending') ?>
              </span>
              <?php if ($p['urgent'] ?? 0): ?>
              <span class="block text-[10px] text-rose-600 font-bold mt-1">⚠️ Urgent</span>
              <?php endif; ?>
            </td>

            <!-- Tanggal -->
            <td class="py-4 px-6 text-xs text-slate-500 dark:text-slate-400">
              <?= isset($p['created_at']) ? formatTanggal($p['created_at']) : '-' ?>
            </td>

            <!-- Aksi -->
            <td class="py-4 px-6 text-right">
              <div class="flex justify-end items-center gap-1.5">
                <button type="button" @click="openDetail(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)" 
                        class="px-3.5 py-1.5 text-xs font-bold bg-[#8c0c4c] text-white hover:bg-[#a3155b] rounded-xl shadow-xs transition-all">
                  Detail & Verifikasi
                </button>
                <?php if (!empty($p['no_hp'])): ?>
                <a :href="getWhatsappLink('<?= e($p['no_hp']) ?>')" target="_blank" title="WhatsApp Mahasiswa" 
                   class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-200 flex items-center justify-center transition">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                </a>
                <?php endif; ?>
              </div>
            </td>

          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ====== MODAL DETAIL & VERIFIKASI SIDANG ====== -->
  <div x-show="modalOpen" 
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       style="display:none;"
       class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/70 backdrop-blur-md">
    
    <div @click.away="modalOpen=false" 
         x-show="modalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="bg-white dark:bg-slate-800 w-full max-w-5xl max-h-[92vh] rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col">
      
      <!-- Modal Header -->
      <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] text-white flex items-center justify-center text-xl font-bold shadow-xs">
            🎓
          </div>
          <div>
            <h3 class="text-base font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
              <span x-text="selectedItem ? selectedItem.mhs_nama : 'Detail Pendaftaran'"></span>
              <span class="text-xs px-2.5 py-0.5 rounded-lg bg-pink-100 dark:bg-pink-900/40 text-[#8c0c4c] dark:text-pink-300 font-bold" x-text="selectedItem ? selectedItem.pnama : ''"></span>
            </h3>
            <div class="text-xs text-slate-500 font-mono mt-0.5">
              NIM: <span x-text="selectedItem ? selectedItem.nim : '-'"></span> · Angkatan: <span x-text="selectedItem ? selectedItem.angkatan : '-'"></span>
            </div>
          </div>
        </div>

        <button type="button" @click="modalOpen=false" 
                class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-900/40 dark:hover:text-rose-300 flex items-center justify-center text-slate-500 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- Modal Body (Scrollable) -->
      <div class="p-6 overflow-y-auto flex-1 space-y-6 text-xs">
        
        <template x-if="selectedItem">
          <div class="space-y-6">
            
            <!-- Row 1: Identity & Thesis Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              
              <!-- Card: Info Mahasiswa & Kontak -->
              <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                <div class="font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider text-[11px] pb-2 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                  <span>👤 Data Mahasiswa & Kontak</span>
                  <a :href="getWhatsappLink(selectedItem.no_hp || selectedItem.mhs_hp_db)" target="_blank" 
                     class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                    <span>Chat WhatsApp ↗</span>
                  </a>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                  <div>
                    <span class="text-slate-400 block text-[10px]">Email Mahasiswa:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200" x-text="selectedItem.email || selectedItem.mhs_email_db || '-'"></span>
                  </div>
                  <div>
                    <span class="text-slate-400 block text-[10px]">No. WhatsApp:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 font-mono" x-text="selectedItem.no_hp || selectedItem.mhs_hp_db || '-'"></span>
                  </div>
                  <div class="col-span-2">
                    <span class="text-slate-400 block text-[10px]">Dosen Pembimbing 1 (Ketua):</span>
                    <span class="font-bold text-purple-700 dark:text-purple-300" x-text="selectedItem.pembimbing1 || '-'"></span>
                  </div>
                  <div class="col-span-2">
                    <span class="text-slate-400 block text-[10px]">Dosen Pembimbing 2 (Anggota):</span>
                    <span class="font-bold text-blue-700 dark:text-blue-300" x-text="selectedItem.pembimbing2 || '-'"></span>
                  </div>
                </div>
              </div>

              <!-- Card: Judul Tesis & Luaran Publikasi -->
              <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/80 space-y-3">
                <div class="font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider text-[11px] pb-2 border-b border-slate-200 dark:border-slate-700">
                  📖 Judul Tesis & Publikasi
                </div>

                <div>
                  <span class="text-slate-400 block text-[10px]">Judul Tesis:</span>
                  <p class="font-bold text-slate-800 dark:text-slate-100 italic leading-relaxed" x-text="selectedItem.judul_tesis || '-'"></p>
                </div>

                <div class="grid grid-cols-2 gap-2.5 pt-1">
                  <div>
                    <span class="text-slate-400 block text-[10px]">Status / Kategori Luaran:</span>
                    <span class="font-bold text-[#8c0c4c] dark:text-pink-400" x-text="selectedItem.status_luaran || '-'"></span>
                  </div>
                  <div>
                    <span class="text-slate-400 block text-[10px]">Link Publikasi:</span>
                    <template x-if="selectedItem.link_luaran">
                      <a :href="selectedItem.link_luaran" target="_blank" class="text-blue-600 underline font-bold truncate block" x-text="selectedItem.link_luaran"></a>
                    </template>
                    <template x-if="!selectedItem.link_luaran">
                      <span class="text-slate-400">-</span>
                    </template>
                  </div>
                </div>
              </div>

            </div>

            <!-- Row 2: 12 Dokumen Berkas Persyaratan (Full Checklist with direct links) -->
            <div class="p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 space-y-4">
              <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                <h4 class="font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider text-[11px] flex items-center gap-2">
                  <span>📑 Verifikasi 12 Dokumen Persyaratan Sidang</span>
                </h4>
                <a :href="'../mhs/cetak_pendaftaran_sidang.php?judul=' + encodeURIComponent(selectedItem.judul_tesis || '') + '&angkatan=' + encodeURIComponent(selectedItem.angkatan || '') + '&pembimbing1=' + encodeURIComponent(selectedItem.pembimbing1 || '') + '&pembimbing2=' + encodeURIComponent(selectedItem.pembimbing2 || '') + '&email=' + encodeURIComponent(selectedItem.email || '') + '&hp=' + encodeURIComponent(selectedItem.no_hp || '')" 
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold transition">
                  <svg class="w-3.5 h-3.5 text-[#8c0c4c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                  <span>Cetak Form Pendaftaran PDF</span>
                </a>
              </div>

              <!-- Grid 12 Berkas -->
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                
                <?php
                $docsMeta = [
                    ['col' => 'berkas_jurnal', 'label' => '1. Manuskrip Jurnal Ilmiah', 'format' => 'PDF/DOCX'],
                    ['col' => 'berkas_bukti_bayar_jurnal', 'label' => '2. Bukti Bayar / Invoice Jurnal', 'format' => 'PDF/IMG'],
                    ['col' => 'berkas_persetujuan', 'label' => '3. Lembar Persetujuan Pembimbing', 'format' => 'PDF'],
                    ['col' => 'berkas_khs', 'label' => '4. KHS Sementara (Sem 1-3)', 'format' => 'PDF'],
                    ['col' => 'berkas_bebas_perpus', 'label' => '5. Surat Bebas Pinjam Perpus', 'format' => 'PDF'],
                    ['col' => 'berkas_buku_sumbangan', 'label' => '6. Bukti Sumbangan 3 Buku', 'format' => 'PDF'],
                    ['col' => 'berkas_bebas_admin', 'label' => '7. Bebas Administrasi SASU', 'format' => 'PDF'],
                    ['col' => 'berkas_foto', 'label' => '8. Pas Foto 4x6 Background Merah', 'format' => 'IMG'],
                    ['col' => 'berkas_draft_tesis', 'label' => '9. Draft Naskah Tesis Lengkap', 'format' => 'PDF'],
                    ['col' => 'berkas_code_program', 'label' => '10. Source Code Program', 'format' => 'ZIP'],
                    ['col' => 'berkas_presentasi', 'label' => '11. Slide Presentasi Sidang', 'format' => 'PPT/PDF'],
                    ['col' => 'berkas_plagiarisme', 'label' => '12. Hasil Cek Plagiarisme (Turnitin)', 'format' => 'PDF']
                ];
                ?>

                <?php foreach ($docsMeta as $dm): ?>
                <div class="p-3 rounded-2xl border transition-all flex flex-col justify-between"
                     :class="selectedItem.<?= $dm['col'] ?> ? 'bg-emerald-50/50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-900/30 border-slate-200 dark:border-slate-700/60'">
                  
                  <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                      <div class="font-bold text-slate-800 dark:text-slate-200 text-xs">
                        <?= htmlspecialchars($dm['label']) ?>
                      </div>
                      <span class="text-[10px] text-slate-400 font-mono"><?= $dm['format'] ?></span>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold"
                          :class="selectedItem.<?= $dm['col'] ?> ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-200 text-slate-500 dark:bg-slate-700'">
                      <span x-text="selectedItem.<?= $dm['col'] ?> ? 'Tersedia' : 'Kosong'"></span>
                    </span>
                  </div>

                  <template x-if="selectedItem.<?= $dm['col'] ?>">
                    <a :href="'../' + selectedItem.<?= $dm['col'] ?>.replace(/^\//, '')" target="_blank" 
                       class="inline-flex items-center justify-center gap-1.5 w-full py-1.5 rounded-xl bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] hover:from-[#a3155b] hover:to-[#c41e73] text-white font-bold text-[11px] shadow-2xs transition">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      <span>Lihat / Unduh Dokumen</span>
                    </a>
                  </template>

                  <template x-if="!selectedItem.<?= $dm['col'] ?>">
                    <div class="text-center py-1 text-[11px] text-slate-400 italic">
                      Dokumen belum diunggah
                    </div>
                  </template>

                </div>
                <?php endforeach; ?>

              </div>
            </div>

            <!-- Row 3: Update Status & Verifikasi Form Action -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-[#8c0c4c]/5 via-purple-500/5 to-blue-500/5 border border-[#8c0c4c]/20">
              <form method="POST" action="sidang" class="flex flex-col sm:flex-row items-end justify-between gap-4">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" :value="selectedItem.id">

                <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">
                      Ubah Status Pendaftaran:
                    </label>
                    <select name="status" x-model="selectedItem.status" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#8c0c4c]/30">
                      <option value="Pending">🟡 Menunggu Verifikasi</option>
                      <option value="Diverifikasi">🟢 Diverifikasi (Berkas Lolos)</option>
                      <option value="Selesai">🟣 Selesai (Lulus Sidang)</option>
                      <option value="Ditolak">🔴 Ditolak (Perlu Revisi)</option>
                    </select>
                  </div>

                  <div class="flex items-center gap-3 pt-6">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                      <input type="checkbox" name="urgent" value="1" :checked="selectedItem.urgent == 1" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                      <span class="text-xs font-bold text-rose-600 dark:text-rose-400">Tandai Prioritas / Urgent</span>
                    </label>
                  </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                  <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] hover:from-[#a3155b] hover:to-[#c41e73] text-white font-bold text-xs shadow-md transition-all">
                    Simpan Perubahan Status
                  </button>
                </div>
              </form>
            </div>

          </div>
        </template>

      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-3.5 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
        <form method="POST" action="sidang" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pendaftaran sidang ini?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" :value="selectedItem ? selectedItem.id : 0">
          <button type="submit" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline">
            🗑️ Hapus Pendaftaran
          </button>
        </form>

        <button type="button" @click="modalOpen=false" class="px-5 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
          Tutup
        </button>
      </div>

    </div>
  </div>

</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
