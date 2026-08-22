<?php
$pageTitle  = 'Input Data Raport Dosen';
$breadcrumb = [['label' => 'Akademik', 'url' => '#'], ['label' => 'Input Raport Dosen']];
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
require_once __DIR__ . '/../functions/excel_raport_helper.php';

// Load semua periode dari DB
$allPeriode = dbQuery("SELECT rp.*, (SELECT COUNT(*) FROM raport_dosen_data rdd WHERE rdd.periode=rp.label) as jumlah_dosen FROM raport_periode rp ORDER BY rp.tahun_awal DESC, rp.semester ASC");

// Periode aktif dari GET
$periodeParam = trim($_GET['periode'] ?? '');
if (empty($periodeParam) && !empty($allPeriode)) $periodeParam = $allPeriode[0]['label'];
$periodeValid = array_filter($allPeriode, fn($p) => $p['label'] === $periodeParam);
if (empty($periodeValid) && !empty($allPeriode)) $periodeParam = $allPeriode[0]['label'];

// Hitung jumlah dosen untuk periode ini
$dbRowCount = 0;
foreach ($allPeriode as $ap) {
    if ($ap['label'] === $periodeParam) { $dbRowCount = (int)$ap['jumlah_dosen']; break; }
}

$apiBase  = BASE_URL . '/api/raport_dosen_api.php';
$tahunNow = (int)date('Y');
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<style>
/* ── Tabel ── */
#input-table { table-layout: fixed; border-collapse: collapse; width: 100%; }
#input-table th { position: sticky; top: 0; z-index: 10; background: #f8fafc; }
.dark #input-table th { background: #0f172a; }
#input-table th, #input-table td { padding: 0 8px; font-size: 13px; white-space: nowrap; }
#input-table tbody tr { height: 42px; }
#input-table tbody tr:hover { background: rgba(140,12,76,.04); }

/* Kolom lebar */
.col-no   { width:44px; text-align:center; }
.col-nama { width:260px; }
.col-prodi{ width:180px; }
.col-num  { width:75px; text-align:center; }
.col-resp { width:110px; text-align:center; }
.col-act  { width:90px; text-align:center; }

/* Inline edit cell */
.editable {
  display: block; width: 100%; height: 32px; line-height: 32px;
  padding: 0 6px; border-radius: 6px; cursor: text;
  transition: box-shadow .15s, background .15s;
}
.editable:focus { outline: none; box-shadow: 0 0 0 2px #8c0c4c; background: #fff8f5 !important; }
.dark .editable:focus { background: #2d1020 !important; }

/* Autocomplete dropdown */
.ac-dropdown {
  position: absolute; z-index: 200; left: 0; top: 100%;
  width: max-content; min-width: 100%;
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.12);
  max-height: 260px; overflow-y: auto; padding: 4px 0;
}
.dark .ac-dropdown { background: #1e293b; border-color: #334155; }
.ac-item {
  padding: 8px 14px; cursor: pointer; font-size: 13px;
  display: flex; flex-direction: column; gap: 1px;
  transition: background .12s;
}
.ac-item:hover, .ac-item.active { background: #fdf0f6; }
.dark .ac-item:hover, .dark .ac-item.active { background: #2d1a2a; }
.ac-item .ac-nama { font-weight: 600; color: #1e293b; }
.dark .ac-item .ac-nama { color: #f1f5f9; }
.ac-item .ac-sub  { font-size: 11px; color: #8c0c4c; }

/* Saving dot */
.dot { display:inline-block; width:7px; height:7px; border-radius:50%; background:#cbd5e1; transition:background .3s; }
.dot.saving { background:#f59e0b; }
.dot.saved  { background:#10b981; }
.dot.error  { background:#ef4444; }

/* Modal */
.modal-backdrop {
  position: fixed; inset: 0; z-index: 9000;
  background: rgba(0,0,0,.55); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center; padding: 16px;
}
/* PENTING: override Tailwind's .hidden supaya modal benar-benar tersembunyi */
.modal-backdrop.hidden { display: none !important; }
#modal:not(.hidden), #import-modal:not(.hidden) { display: flex !important; }
.modal-box {
  background: #fff; border-radius: 20px; box-shadow: 0 24px 60px rgba(0,0,0,.2);
  width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto;
}
.dark .modal-box { background: #1e293b; }

/* Number input */
input[type=number]::-webkit-inner-spin-button { opacity: .5; }
input.num-in {
  width: 100%; text-align: center; background: #f8fafc;
  border: 1px solid #e2e8f0; border-radius: 8px;
  padding: 6px 4px; font-size: 13px; color: #1e293b;
  transition: border-color .15s;
}
.dark input.num-in { background: #0f172a; border-color: #334155; color: #f1f5f9; }
input.num-in:focus { outline: none; border-color: #8c0c4c; }
</style>

<div class="px-4 md:px-8 pb-16">

  <!-- ═══ HEADER ═══ -->
  <div class="flex flex-wrap items-center justify-between gap-4 mb-6 mt-2">
    <div>
      <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Input Data Raport Dosen</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
        Periode: <strong class="text-[#8c0c4c] dark:text-pink-400"><?= e($periodeParam) ?></strong>
        &middot; <?= $dbRowCount ?> dosen
      </p>
    </div>
    <div class="flex flex-wrap gap-2">
      <a href="<?= $apiBase ?>?action=export_excel&periode=<?= urlencode($periodeParam) ?>"
         class="flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export Excel
      </a>
      <a href="raport_dosen.php?periode=<?= urlencode($periodeParam) ?>"
         class="flex items-center gap-1.5 px-3 py-2 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-sm font-semibold">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Lihat Raport
      </a>
    </div>
  </div>

  <!-- ═══ PERIODE TABS ═══ -->
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-5 mb-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Pilih Periode</span>
      <button type="button" id="btn-add-periode" class="flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Tambah Periode
      </button>
    </div>
    <div class="flex flex-wrap gap-2" id="periode-tabs">
      <?php if (empty($allPeriode)): ?>
        <span class="text-slate-400 text-sm italic">Belum ada periode.</span>
      <?php else: ?>
        <?php foreach ($allPeriode as $ap): $active = $ap['label'] === $periodeParam; ?>
        <div class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold border transition-all
                   <?= $active ? 'bg-[#8c0c4c] text-white border-[#8c0c4c] shadow' : 'bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:bg-slate-100' ?>">
          <a href="?periode=<?= urlencode($ap['label']) ?>" class="flex items-center gap-1.5 text-inherit no-underline">
            <?= e($ap['label']) ?>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold <?= $active ? 'bg-white/25 text-white' : 'bg-slate-200 dark:bg-slate-600 text-slate-500 dark:text-slate-300' ?>"><?= (int)$ap['jumlah_dosen'] ?></span>
          </a>
          <button type="button" onclick="deletePeriode('<?= e($ap['label']) ?>', <?= (int)$ap['jumlah_dosen'] ?>, event)"
                  title="Hapus Periode <?= e($ap['label']) ?>"
                  class="opacity-40 hover:opacity-100 hover:text-red-300 text-sm font-bold ml-1 transition-opacity cursor-pointer">
            &times;
          </button>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <!-- Form Tambah Periode -->
    <div id="form-periode" class="hidden mt-4 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-700">
      <div class="text-xs font-bold text-slate-600 dark:text-white mb-3">Tambah Periode Baru</div>
      <div class="flex flex-wrap gap-3 items-end">
        <div><label class="block text-xs text-slate-500 mb-1">Semester</label>
          <select id="np-sem" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c]">
            <option>Gasal</option><option>Genap</option>
          </select></div>
        <div><label class="block text-xs text-slate-500 mb-1">Tahun Mulai</label>
          <input type="number" id="np-ta" value="<?= $tahunNow ?>" class="w-28 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c]"></div>
        <div><label class="block text-xs text-slate-500 mb-1">Tahun Selesai</label>
          <input type="number" id="np-tb" value="<?= $tahunNow+1 ?>" class="w-28 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c]"></div>
        <div class="flex gap-2">
          <button type="button" id="btn-cancel-periode" class="px-3 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm">Batal</button>
          <button type="button" id="btn-save-periode" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold">Simpan</button>
        </div>
      </div>
      <p class="text-xs text-slate-400 mt-2">Preview: <strong id="np-preview"></strong></p>
    </div>
  </div>

  <!-- ═══ TOOLBAR ═══ -->
  <div class="flex flex-wrap items-center gap-3 mb-4">
    <button type="button" id="btn-add" class="flex items-center gap-1.5 px-4 py-2 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl text-sm font-semibold shadow transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Tambah Dosen
    </button>
    <button type="button" id="btn-import" class="flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold cursor-pointer">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
      Import Excel
    </button>
    <input type="file" id="import-file" accept=".xlsx,.xls" class="hidden">
    <!-- Filter Prodi -->
    <select id="filter-prodi" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c]">
      <option value="">Semua Prodi</option>
    </select>
    <!-- Status -->
    <div class="flex items-center gap-1.5 ml-auto text-xs text-slate-400">
      <span class="dot" id="g-dot"></span>
      <span id="g-status">Siap</span>
    </div>
  </div>

  <!-- ═══ TABEL ═══ -->
  <div id="status-bar" class="hidden mb-3 px-4 py-2 rounded-xl text-sm font-medium"></div>
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full" id="input-table">
        <thead>
          <tr class="border-b border-slate-200 dark:border-slate-700">
            <th class="col-no py-3 text-xs font-bold text-slate-400 uppercase">No</th>
            <th class="col-nama py-3 text-left text-xs font-bold text-slate-400 uppercase pl-2">Nama Dosen</th>
            <th class="col-prodi py-3 text-left text-xs font-bold text-slate-400 uppercase pl-2">Program Studi</th>
            <th class="col-num py-3 text-xs font-bold text-slate-400 uppercase">MK</th>
            <th class="col-num py-3 text-xs font-bold text-slate-400 uppercase">Kelas</th>
            <th class="col-resp py-3 text-xs font-bold text-slate-400 uppercase">Responden</th>
            <th class="col-num py-3 text-xs font-bold text-slate-400 uppercase">Kuesioner</th>
            <th class="col-num py-3 text-xs font-bold text-slate-400 uppercase">Hadir</th>
            <th class="col-num py-3 text-xs font-bold text-slate-400 uppercase">Konten</th>
            <th class="col-num py-3 text-xs font-bold text-slate-400 uppercase">Penelitian</th>
            <th class="col-num py-3 text-xs font-bold text-slate-400 uppercase">Pengabdian</th>
            <th class="col-act py-3 text-xs font-bold text-slate-400 uppercase">Aksi</th>
          </tr>
        </thead>
        <tbody id="tbl-body">
          <tr><td colspan="12" class="py-12 text-center text-slate-400">Memuat data...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div id="modal" class="modal-backdrop hidden" onclick="if(event.target===this)closeDosenModal()">
  <div class="modal-box p-6" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modal-title">Tambah Dosen</h3>
      <button type="button" id="modal-close" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- ── Nama Dosen (Autocomplete) ── -->
    <div class="mb-4">
      <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Nama Dosen <span class="text-red-500">*</span></label>
      <div class="relative">
        <input type="text" id="m-nama" autocomplete="off" placeholder="Ketik nama atau pilih dari daftar..."
          class="w-full bg-slate-50 dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] transition-colors font-medium">
        <div id="ac-dropdown" class="ac-dropdown hidden"></div>
      </div>
      <p class="text-xs text-slate-400 mt-1" id="nama-hint">Dipilih dari master dosen &mdash; prodi otomatis terisi</p>
    </div>

    <!-- ── Prodi (auto-filled, tapi bisa ganti manual) ── -->
    <div class="mb-4">
      <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Program Studi</label>
      <select id="m-prodi" class="w-full bg-slate-50 dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] transition-colors">
        <option value="">-- Pilih Prodi --</option>
      </select>
    </div>

    <!-- ── Row: MK + Kelas + Responden ── -->
    <div class="grid grid-cols-3 gap-3 mb-4">
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Jml Matkul</label>
        <input type="number" id="m-mk" min="0" max="20" value="0" class="num-in">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Jml Kelas</label>
        <input type="number" id="m-kelas" min="0" max="50" value="0" class="num-in">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Responden</label>
        <input type="text" id="m-resp" placeholder="misal: 45 dari 80" class="num-in text-left px-3">
      </div>
    </div>

    <!-- ── Row: Nilai Kuesioner + Kehadiran + Konten ── -->
    <div class="mb-2">
      <div class="text-xs font-bold text-slate-500 uppercase mb-2 flex items-center gap-2">
        <span>B. Rekapitulasi Nilai</span>
        <span class="text-slate-300 font-normal">(isi angka desimal pakai titik, mis. 4.52)</span>
      </div>
      <div class="grid grid-cols-3 gap-3 mb-3">
        <div>
          <label class="block text-xs text-slate-500 mb-1.5">Nilai Kuesioner</label>
          <input type="number" id="m-kuis" step="0.001" min="0" max="5" placeholder="0.000" class="num-in">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1.5">Kehadiran (pertemuan)</label>
          <input type="number" id="m-hadir" step="0.5" min="0" max="16" placeholder="0" class="num-in">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1.5">Konten LMS</label>
          <input type="number" id="m-konten" step="0.001" min="0" max="5" placeholder="0.000" class="num-in">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs text-slate-500 mb-1.5">Jumlah Penelitian</label>
          <input type="number" id="m-penel" min="0" max="50" value="0" class="num-in">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1.5">Jumlah Pengabdian</label>
          <input type="number" id="m-pengab" min="0" max="50" value="0" class="num-in">
        </div>
      </div>
    </div>

    <!-- ── Collapsible: C. Rekomendasi Perbaikan ── -->
    <details class="mb-3 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
      <summary class="px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-300 cursor-pointer bg-slate-50 dark:bg-slate-900/50 hover:bg-slate-100 select-none">
        C. Rekomendasi Perbaikan (P1–P5) — opsional
      </summary>
      <div class="p-4 space-y-2 bg-white dark:bg-slate-800">
        <?php for ($i=1;$i<=5;$i++): ?>
        <div>
          <label class="block text-xs text-slate-500 mb-1">P<?= $i ?></label>
          <textarea id="m-p<?= $i ?>" rows="2" placeholder="Aspek perbaikan <?= $i ?>..."
            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm resize-none focus:outline-none focus:border-[#8c0c4c]"></textarea>
        </div>
        <?php endfor; ?>
      </div>
    </details>

    <!-- ── Collapsible: D. Catatan ── -->
    <details class="mb-5 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
      <summary class="px-4 py-3 text-xs font-bold text-slate-600 dark:text-slate-300 cursor-pointer bg-slate-50 dark:bg-slate-900/50 hover:bg-slate-100 select-none">
        D. Catatan Mahasiswa (K1–K4) — opsional
      </summary>
      <div class="p-4 space-y-2 bg-white dark:bg-slate-800">
        <?php for ($i=1;$i<=4;$i++): ?>
        <div>
          <label class="block text-xs text-slate-500 mb-1">K<?= $i ?></label>
          <textarea id="m-k<?= $i ?>" rows="2" placeholder="Catatan mahasiswa <?= $i ?>..."
            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm resize-none focus:outline-none focus:border-[#8c0c4c]"></textarea>
        </div>
        <?php endfor; ?>
      </div>
    </details>

    <!-- Buttons -->
    <div class="flex gap-3 justify-end">
      <button type="button" id="modal-cancel" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-200">Batal</button>
      <button type="button" id="modal-save" class="px-6 py-2.5 bg-[#8c0c4c] text-white rounded-xl text-sm font-semibold hover:bg-[#a3155b] shadow flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Simpan
      </button>
    </div>
  </div>
</div>

<!-- ═════ IMPORT MODAL ════ -->
<div id="import-modal" class="modal-backdrop hidden" style="z-index:9999" onclick="if(event.target===this){document.getElementById('import-modal').classList.add('hidden');document.getElementById('import-file').value='';}">
  <div class="modal-box p-6 max-w-sm" onclick="event.stopPropagation()">
    <h3 class="font-bold text-slate-800 dark:text-white mb-3">Import Excel</h3>
    <p class="text-sm text-slate-500 mb-3">Periode: <strong class="text-[#8c0c4c]"><?= e($periodeParam) ?></strong></p>
    <label class="flex items-center gap-2 text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 rounded-xl border border-amber-200 dark:border-amber-800 p-3 mb-4 cursor-pointer">
      <input type="checkbox" id="overwrite-check" class="accent-amber-600">
      Hapus data lama sebelum import
    </label>
    <div id="import-fname" class="text-sm text-emerald-600 font-semibold mb-4 hidden"></div>
    <div class="flex gap-2 justify-end">
      <button type="button" id="import-cancel" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold">Batal</button>
      <button type="button" id="import-confirm" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold disabled:opacity-50" disabled>Import</button>
    </div>
  </div>
</div>

<script>
/* ─── Global Error Handler ───────────────────────────────────── */
window.onerror = function(msg, src, line, col, err) {
  const bar = document.getElementById('status-bar');
  if (bar) {
    bar.textContent = 'JS Error: ' + msg + ' (line ' + line + ')';
    bar.className = 'mb-3 px-4 py-2 rounded-xl text-sm font-medium bg-red-50 text-red-700 border border-red-200';
    bar.classList.remove('hidden');
  }
  console.error('JS Error:', msg, src, line, col, err);
  return false;
};

/* ─── Constants ─────────────────────────────────────────────────── */
const PERIODE  = <?= json_encode($periodeParam ?: 'Gasal 2025-2026') ?>;
const API      = <?= json_encode($apiBase) ?>;

// Sanity check
if (!PERIODE || !API) {
  document.addEventListener('DOMContentLoaded', () => {
    const bar = document.getElementById('status-bar');
    if (bar) { bar.textContent = 'Error: PERIODE atau API kosong. Hubungi admin.'; bar.classList.remove('hidden'); }
  });
}

/* ─── State ─────────────────────────────────────────────────────── */
let allRows    = [];
let masterDosen = [];   // [{id,nama,prodi_label}]
let editNo     = null;  // null=tambah baru, int=edit existing

/* ─── Boot ───────────────────────────────────────────────────────────── */
(async () => {
  try {
    await Promise.all([loadMasterDosen(), loadData()]);
  } catch(e) {
    console.error('Boot error:', e);
    showStatus('Gagal memuat halaman: ' + e.message + '. Coba refresh.', 'error');
  }
})();

/* ─── Master Dosen & Prodi ───────────────────────────────────────────── */
async function loadMasterDosen() {
  // Load semua prodi S1+S2+S3
  const rProdik = await fetch(`${API}?action=get_all_prodis`);
  if (!rProdik.ok) throw new Error('Gagal load prodi (HTTP '+rProdik.status+')');
  const dProdik = await rProdik.json();
  if (!dProdik.success) throw new Error(dProdik.message || 'Gagal load prodi');

  // Group by jenjang untuk optgroup
  const prodis = dProdik.rows || [];
  const grouped = {};
  prodis.forEach(p => {
    if (!grouped[p.jenjang]) grouped[p.jenjang] = [];
    grouped[p.jenjang].push(p);
  });

  // Isi select prodi di modal (dengan optgroup S1/S2/S3/D3)
  const sel = document.getElementById('m-prodi');
  let html = '<option value="">-- Pilih Prodi --</option>';
  Object.keys(grouped).sort().forEach(jenjang => {
    html += `<optgroup label="${esc(jenjang)}">`;
    grouped[jenjang].forEach(p => {
      const val = p.label || (p.jenjang + ' - ' + p.nama);
      html += `<option value="${esc(val)}">${esc(val)}</option>`;
    });
    html += '</optgroup>';
  });
  sel.innerHTML = html;

  // Isi filter-prodi di toolbar
  const fp = document.getElementById('filter-prodi');
  let fpHtml = '<option value="">Semua Prodi</option>';
  Object.keys(grouped).sort().forEach(jenjang => {
    fpHtml += `<optgroup label="${esc(jenjang)}">`;
    grouped[jenjang].forEach(p => {
      const val = p.label || (p.jenjang + ' - ' + p.nama);
      fpHtml += `<option value="${esc(val)}">${esc(val)}</option>`;
    });
    fpHtml += '</optgroup>';
  });
  fp.innerHTML = fpHtml;

  // Load dosen aktif (untuk autocomplete nama)
  try {
    const rDosen = await fetch(`${API}?action=get_all_dosen`);
    if (rDosen.ok) {
      const dDosen = await rDosen.json();
      masterDosen = dDosen.rows || [];
    }
  } catch(e) { console.warn('Autocomplete dosen tidak tersedia:', e); }
}

/* ─── Load Data ──────────────────────────────────────────────────── */
async function loadData(prodi = '') {
  let url = `${API}?action=list&periode=${enc(PERIODE)}`;
  if (prodi) url += '&prodi=' + enc(prodi);
  const d = await (await fetch(url)).json();
  allRows = d.rows || [];
  renderTable(allRows);
}

/* ─── Render Table ───────────────────────────────────────────────── */
function renderTable(rows) {
  const tb = document.getElementById('tbl-body');
  if (!rows.length) {
    tb.innerHTML = `<tr><td colspan="12" class="py-14 text-center">
      <div class="flex flex-col items-center gap-3 text-slate-400">
        <svg class="w-12 h-12 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <div class="text-sm">Belum ada data.<br>Klik <strong>+ Tambah Dosen</strong> atau <strong>Import Excel</strong>.</div>
      </div></td></tr>`;
    return;
  }

  tb.innerHTML = rows.map((r, i) => {
    const hasDetail = r.p1||r.p2||r.k1||r.k2;
    const badgeKuis = kuisBadge(parseFloat(r.nilai_kuesioner||0));
    return `<tr data-no="${r.no}" class="border-b border-slate-100 dark:border-slate-700/60 group">
      <td class="col-no text-slate-400 text-xs">${r.no}</td>
      <td class="col-nama">
        <!-- Nama: inline edit on click -->
        <div class="relative flex items-center gap-1.5">
          <span class="font-semibold text-slate-800 dark:text-white text-[13px] truncate max-w-[220px]" title="${esc(r.nama)}">${esc(r.nama)}</span>
          <span class="dot" id="dot-${r.no}"></span>
        </div>
      </td>
      <td class="col-prodi text-[12px] text-[#8c0c4c] dark:text-pink-400 font-medium truncate" title="${esc(r.prodi)}">${esc(r.prodi)||'—'}</td>
      <!-- Editable numeric cells -->
      <td class="col-num"><span contenteditable="true" class="editable text-center" data-no="${r.no}" data-f="jumlah_matkul">${r.jumlah_matkul||''}</span></td>
      <td class="col-num"><span contenteditable="true" class="editable text-center" data-no="${r.no}" data-f="jumlah_kelas">${r.jumlah_kelas||''}</span></td>
      <td class="col-resp"><span contenteditable="true" class="editable text-center text-xs" data-no="${r.no}" data-f="jumlah_responden">${esc(r.jumlah_responden)}</span></td>
      <td class="col-num">
        <span contenteditable="true" class="editable text-center font-bold ${badgeKuis}" data-no="${r.no}" data-f="nilai_kuesioner">${r.nilai_kuesioner>0?parseFloat(r.nilai_kuesioner).toFixed(2):''}</span>
      </td>
      <td class="col-num"><span contenteditable="true" class="editable text-center" data-no="${r.no}" data-f="jumlah_kehadiran">${r.jumlah_kehadiran>0?parseFloat(r.jumlah_kehadiran):''}</span></td>
      <td class="col-num"><span contenteditable="true" class="editable text-center" data-no="${r.no}" data-f="konten">${r.konten>0?parseFloat(r.konten).toFixed(2):''}</span></td>
      <td class="col-num"><span contenteditable="true" class="editable text-center" data-no="${r.no}" data-f="jumlah_penelitian">${r.jumlah_penelitian||''}</span></td>
      <td class="col-num"><span contenteditable="true" class="editable text-center" data-no="${r.no}" data-f="jumlah_pengabdian">${r.jumlah_pengabdian||''}</span></td>
      <td class="col-act">
        <div class="flex items-center gap-1 justify-center opacity-0 group-hover:opacity-100 transition-opacity">
          <!-- Lihat Rapot (Wizard Skor Bobot & Penilaian) -->
          <a href="raport_dosen.php?step=skor_bobot&periode=${enc(PERIODE)}&ids=${r.no}" target="_blank" title="Lihat Rapot & Penilaian Tridharma"
            class="w-7 h-7 flex items-center justify-center rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </a>
          <!-- Edit Form -->
          <button type="button" onclick="openEdit(${r.no})" title="Edit Form Lengkap"
            class="w-7 h-7 flex items-center justify-center rounded-lg ${hasDetail?'text-[#8c0c4c] ring-1 ring-[#8c0c4c]/30':'text-blue-500'} hover:bg-blue-50 dark:hover:bg-blue-900/20">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </button>
          <!-- Hapus -->
          <button type="button" onclick="deleteRow(${r.no})" title="Hapus Dosen"
            class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </div>
      </td>
    </tr>`;
  }).join('');

  // Attach inline-edit listeners
  document.querySelectorAll('#tbl-body .editable').forEach(el => {
    el.addEventListener('blur', () => schedSave(el.dataset.no));
    el.addEventListener('keydown', e => { if (e.key==='Enter'){e.preventDefault();el.blur();} });
  });
}

function kuisBadge(v) {
  if (v >= 4.58) return 'text-emerald-600 dark:text-emerald-400';
  if (v >= 4.12) return 'text-blue-600 dark:text-blue-400';
  if (v >= 3.66) return 'text-amber-600 dark:text-amber-400';
  if (v > 0)     return 'text-red-600 dark:text-red-400';
  return 'text-slate-700 dark:text-slate-300';
}

/* ─── Inline Save ────────────────────────────────────────────────── */
const saveTimers = {};
function schedSave(no) {
  clearTimeout(saveTimers[no]);
  setDot(no,'saving');
  saveTimers[no] = setTimeout(()=>saveRowInline(no), 600);
}

async function saveRowInline(no) {
  const tr  = document.querySelector(`#tbl-body tr[data-no="${no}"]`);
  if (!tr) return;
  const get = f => tr.querySelector(`[data-f="${f}"]`)?.innerText.trim() ?? '';
  const existing = allRows.find(r=>String(r.no)===String(no)) || {};

  const data = {
    action:'save_row', periode:PERIODE, no,
    nama: existing.nama || '',
    prodi: existing.prodi || '',
    jumlah_matkul:   get('jumlah_matkul'),
    jumlah_kelas:    get('jumlah_kelas'),
    jumlah_responden:get('jumlah_responden'),
    nilai_kuesioner: get('nilai_kuesioner'),
    jumlah_kehadiran:get('jumlah_kehadiran'),
    konten:          get('konten'),
    jumlah_penelitian:get('jumlah_penelitian'),
    jumlah_pengabdian:get('jumlah_pengabdian'),
    p1:existing.p1||'',p2:existing.p2||'',p3:existing.p3||'',p4:existing.p4||'',p5:existing.p5||'',
    k1:existing.k1||'',k2:existing.k2||'',k3:existing.k3||'',k4:existing.k4||'',
  };

  try {
    const fd = new FormData();
    Object.entries(data).forEach(([k,v])=>fd.append(k,v));
    const res = await (await fetch(API,{method:'POST',body:fd})).json();
    if (res.success) {
      setDot(no,'saved');
      const idx = allRows.findIndex(r=>String(r.no)===String(no));
      if (idx>=0) Object.assign(allRows[idx], data);
    } else { setDot(no,'error'); }
  } catch { setDot(no,'error'); }
}

/* ─── Modal: Tambah / Edit ───────────────────────────────────────── */
function openDosenModal(no=null) {
  editNo = no;
  const r = no ? allRows.find(x=>String(x.no)===String(no)) : null;
  document.getElementById('modal-title').textContent = r ? 'Edit Dosen' : 'Tambah Dosen';

  // Reset form
  document.getElementById('m-nama').value   = r?.nama || '';
  document.getElementById('m-prodi').value  = r?.prodi || '';
  document.getElementById('m-mk').value     = r?.jumlah_matkul || 0;
  document.getElementById('m-kelas').value  = r?.jumlah_kelas || 0;
  document.getElementById('m-resp').value   = r?.jumlah_responden || '';
  document.getElementById('m-kuis').value   = r?.nilai_kuesioner > 0 ? parseFloat(r.nilai_kuesioner).toFixed(3) : '';
  document.getElementById('m-hadir').value  = r?.jumlah_kehadiran > 0 ? r.jumlah_kehadiran : '';
  document.getElementById('m-konten').value = r?.konten > 0 ? parseFloat(r.konten).toFixed(3) : '';
  document.getElementById('m-penel').value  = r?.jumlah_penelitian || 0;
  document.getElementById('m-pengab').value = r?.jumlah_pengabdian || 0;
  for (let i=1;i<=5;i++) document.getElementById(`m-p${i}`).value = r?.[`p${i}`]||'';
  for (let i=1;i<=4;i++) document.getElementById(`m-k${i}`).value = r?.[`k${i}`]||'';

  document.getElementById('modal').classList.remove('hidden');
  setTimeout(()=>document.getElementById('m-nama').focus(), 80);
}

function openEdit(no) { openDosenModal(no); }
document.getElementById('btn-add').addEventListener('click', () => openDosenModal(null));
document.getElementById('modal-close').addEventListener('click', closeDosenModal);
document.getElementById('modal-cancel').addEventListener('click', closeDosenModal);
function closeDosenModal() {
  document.getElementById('modal').classList.add('hidden');
  document.getElementById('ac-dropdown').classList.add('hidden');
}

document.getElementById('modal-save').addEventListener('click', async () => {
  const nama = document.getElementById('m-nama').value.trim();
  if (!nama) {
    document.getElementById('m-nama').classList.add('ring-2','ring-red-400');
    document.getElementById('m-nama').focus();
    setTimeout(() => document.getElementById('m-nama').classList.remove('ring-2','ring-red-400'), 2000);
    return;
  }

  const btn = document.getElementById('modal-save');
  btn.disabled = true;
  btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="30 70"/></svg> Menyimpan...';

  const data = {
    action:'save_row', periode:PERIODE,
    no: editNo ?? 0,
    nama,
    prodi:            document.getElementById('m-prodi').value,
    jumlah_matkul:    document.getElementById('m-mk').value,
    jumlah_kelas:     document.getElementById('m-kelas').value,
    jumlah_responden: document.getElementById('m-resp').value,
    nilai_kuesioner:  document.getElementById('m-kuis').value,
    jumlah_kehadiran: document.getElementById('m-hadir').value,
    konten:           document.getElementById('m-konten').value,
    jumlah_penelitian: document.getElementById('m-penel').value,
    jumlah_pengabdian: document.getElementById('m-pengab').value,
  };
  for (let i=1;i<=5;i++) data[`p${i}`]=document.getElementById(`m-p${i}`).value;
  for (let i=1;i<=4;i++) data[`k${i}`]=document.getElementById(`m-k${i}`).value;

  try {
    const fd = new FormData();
    Object.entries(data).forEach(([k,v])=>fd.append(k,v));
    const resp = await fetch(API, {method:'POST', body:fd, credentials:'same-origin'});

    // Tangkap non-JSON response (redirect ke login, dll)
    const text = await resp.text();
    let res;
    try { res = JSON.parse(text); }
    catch { 
      console.error('Non-JSON response:', text.slice(0,200));
      if (resp.status === 401) showStatus('Sesi habis. Silakan refresh dan login ulang.', 'error');
      else if (resp.status === 403) showStatus('Akses tidak diizinkan.', 'error');
      else showStatus(`Server error (${resp.status}). Cek console untuk detail.`, 'error');
      return;
    }

    if (res.success) {
      closeDosenModal();
      await loadData(document.getElementById('filter-prodi').value);
      showStatus((editNo ? 'Data diperbarui' : 'Dosen berhasil ditambahkan') + '!', 'success');
    } else {
      showStatus('Gagal: ' + (res.message || 'Unknown error'), 'error');
    }
  } catch(e) {
    console.error('Save error:', e);
    showStatus('Koneksi error: ' + e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Simpan';
  }
});

/* ─── Autocomplete Nama ──────────────────────────────────────────── */
const acDropdown = document.getElementById('ac-dropdown');
const namaInput  = document.getElementById('m-nama');
let acIdx = -1;

namaInput.addEventListener('input', function() {
  const q = this.value.trim().toLowerCase();
  if (q.length < 1) { acDropdown.classList.add('hidden'); return; }

  const hits = masterDosen.filter(d => d.nama.toLowerCase().includes(q)).slice(0, 12);
  if (!hits.length) { acDropdown.classList.add('hidden'); return; }

  acIdx = -1;
  acDropdown.innerHTML = hits.map((d,i) => {
    const pLabel = d.prodi_standard || d.prodi_label || '';
    return `<div class="ac-item" data-idx="${i}" data-nama="${esc(d.nama)}" data-prodi="${esc(pLabel)}">
       <span class="ac-nama">${highlight(d.nama,q)}</span>
       <span class="ac-sub">${esc(pLabel || '—')}</span>
     </div>`;
  }).join('');

  acDropdown.querySelectorAll('.ac-item').forEach(el => {
    el.addEventListener('mousedown', e => {
      e.preventDefault();
      pickDosen(el.dataset.nama, el.dataset.prodi);
    });
  });
  acDropdown.classList.remove('hidden');
});

namaInput.addEventListener('keydown', e => {
  const items = acDropdown.querySelectorAll('.ac-item');
  if (!items.length || acDropdown.classList.contains('hidden')) return;
  if (e.key==='ArrowDown'){e.preventDefault();acIdx=Math.min(acIdx+1,items.length-1);highlightAc(items);}
  else if(e.key==='ArrowUp'){e.preventDefault();acIdx=Math.max(acIdx-1,0);highlightAc(items);}
  else if(e.key==='Enter'||e.key==='Tab'){
    if(acIdx>=0){e.preventDefault();const el=items[acIdx];pickDosen(el.dataset.nama,el.dataset.prodi);}
    else acDropdown.classList.add('hidden');
  }
  else if(e.key==='Escape'){acDropdown.classList.add('hidden');}
});
namaInput.addEventListener('blur', ()=>setTimeout(()=>acDropdown.classList.add('hidden'),150));

function highlightAc(items){
  items.forEach((el,i)=>el.classList.toggle('active',i===acIdx));
}
function pickDosen(nama, prodi) {
  namaInput.value = nama;
  if (prodi) {
    const sel = document.getElementById('m-prodi');
    const normP = prodi.trim().toLowerCase();
    
    // 1. Coba exact match
    let opt = [...sel.options].find(o => o.value.trim().toLowerCase() === normP);
    
    // 2. Coba partial match jika tidak ketemu exact
    if (!opt) {
      opt = [...sel.options].find(o => {
        const oNorm = o.value.trim().toLowerCase();
        return oNorm.includes(normP) || normP.includes(oNorm);
      });
    }
    
    if (opt) {
      sel.value = opt.value;
    } else {
      const o = new Option(prodi, prodi, true, true);
      sel.appendChild(o);
    }
  }
  acDropdown.classList.add('hidden');
  document.getElementById('nama-hint').textContent = '✓ Dipilih: ' + nama;
  document.getElementById('m-mk').focus();
}

function highlight(text, q) {
  const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
  return esc(text).replace(re,'<mark class="bg-yellow-200 dark:bg-yellow-800 rounded px-0.5">$1</mark>');
}

/* ─── Delete Row ─────────────────────────────────────────────────── */
async function deleteRow(no) {
  if (!confirm('Hapus dosen ini?')) return;
  const fd = new FormData();
  fd.append('action','delete_row'); fd.append('periode',PERIODE); fd.append('no',no);
  const r = await (await fetch(API,{method:'POST',body:fd})).json();
  if (r.success) { await loadData(document.getElementById('filter-prodi').value); showStatus('Dihapus.','success'); }
}

/* ─── Filter Prodi ───────────────────────────────────────────────── */
document.getElementById('filter-prodi').addEventListener('change', function(){loadData(this.value);});

/* ─── Import ─────────────────────────────────────────────────────── */
// Reset file input on load to prevent browser-cached change events
document.getElementById('import-file').value = '';

// Tombol Import: buka file picker
document.getElementById('btn-import').addEventListener('click', () => {
  document.getElementById('import-file').click();
});

document.getElementById('import-file').addEventListener('change', function(){
  if (!this.files || !this.files.length) return;
  document.getElementById('import-fname').textContent = '📄 '+this.files[0].name;
  document.getElementById('import-fname').classList.remove('hidden');
  document.getElementById('import-confirm').disabled = false;
  document.getElementById('import-modal').classList.remove('hidden');
});
document.getElementById('import-cancel').addEventListener('click', ()=>{
  document.getElementById('import-modal').classList.add('hidden');
  document.getElementById('import-file').value='';
});
document.getElementById('import-confirm').addEventListener('click', async ()=>{
  const btn = document.getElementById('import-confirm');
  btn.disabled=true; btn.textContent='Mengimport...';
  const fd=new FormData();
  fd.append('action','import_excel'); fd.append('periode',PERIODE);
  fd.append('overwrite',document.getElementById('overwrite-check').checked?'1':'0');
  fd.append('file',document.getElementById('import-file').files[0]);
  try{
    const r=await(await fetch(API,{method:'POST',body:fd})).json();
    document.getElementById('import-modal').classList.add('hidden');
    if(r.success){await loadData();showStatus(`Import selesai! ${r.imported} dosen.`,'success');}
    else showStatus('Import gagal: '+r.message,'error');
  }catch(e){showStatus('Error: '+e.message,'error');}
  finally{btn.disabled=false;btn.textContent='Import';document.getElementById('import-file').value='';}
});

/* ─── Periode Management ─────────────────────────────────────────── */
document.getElementById('btn-add-periode').addEventListener('click',()=>{
  document.getElementById('form-periode').classList.toggle('hidden');
});
document.getElementById('btn-cancel-periode').addEventListener('click',()=>{
  document.getElementById('form-periode').classList.add('hidden');
});
['np-sem','np-ta','np-tb'].forEach(id=>{
  document.getElementById(id).addEventListener('input',updatePreview);
  document.getElementById(id).addEventListener('change',updatePreview);
});
document.getElementById('np-ta').addEventListener('input',function(){
  document.getElementById('np-tb').value=parseInt(this.value||2025)+1;
  updatePreview();
});
function updatePreview(){
  document.getElementById('np-preview').textContent =
    document.getElementById('np-sem').value+' '+
    document.getElementById('np-ta').value+'-'+
    document.getElementById('np-tb').value;
}
updatePreview();

document.getElementById('btn-save-periode').addEventListener('click',async()=>{
  const fd=new FormData();
  fd.append('action','add_periode');
  fd.append('semester',document.getElementById('np-sem').value);
  fd.append('tahun_awal',document.getElementById('np-ta').value);
  fd.append('tahun_akhir',document.getElementById('np-tb').value);
  const r=await(await fetch(API,{method:'POST',body:fd})).json();
  if(r.success){
    showStatus('Periode '+r.label+' ditambahkan!','success');
    setTimeout(()=>location.href='?periode='+encodeURIComponent(r.label),1000);
  } else showStatus('Gagal: '+r.message,'error');
});

async function deletePeriode(label, count, evt){
  if (evt) { evt.preventDefault(); evt.stopPropagation(); }
  let msg = `Hapus periode "${label}"?`;
  if (count > 0) {
    msg = `⚠️ PERINGATAN: Periode "${label}" memiliki ${count} data dosen.\n\nMenghapus periode ini akan menghapus SELURUH ${count} data dosen di dalamnya secara permanen.\n\nApakah Anda yakin ingin melanjutkan?`;
  }
  if (!confirm(msg)) return;

  const fd = new FormData();
  fd.append('action', 'delete_periode');
  fd.append('label', label);
  fd.append('force', '1');

  try {
    const res = await fetch(API, { method: 'POST', body: fd });
    const r = await res.json();
    if (r.success) {
      showStatus(`Periode "${label}" berhasil dihapus.`, 'success');
      setTimeout(() => {
        // Jika periode yang dihapus adalah periode yang sedang dibuka, redirect ke halaman input tanpa parameter
        if (PERIODE === label) {
          location.href = 'input_raport_dosen.php';
        } else {
          location.reload();
        }
      }, 700);
    } else {
      showStatus('Gagal: ' + (r.message || 'Terjadi kesalahan.'), 'error');
    }
  } catch (err) {
    showStatus('Gagal menghubungi server: ' + err.message, 'error');
  }
}

/* ─── Helpers ────────────────────────────────────────────────────── */
function enc(s){return encodeURIComponent(s);}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

function setDot(no,state){
  const d=document.getElementById('dot-'+no);
  if(!d)return;
  d.className='dot';
  if(state==='saving')d.classList.add('saving');
  else if(state==='saved'){d.classList.add('saved');setTimeout(()=>{d.className='dot';},2500);}
  else if(state==='error')d.classList.add('error');
}

function showStatus(msg,type='success'){
  const b=document.getElementById('status-bar');
  b.textContent=msg;
  b.className=`mb-3 px-4 py-2 rounded-xl text-sm font-medium ${type==='success'?'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200':'bg-red-50 text-red-700 border border-red-200'}`;
  b.classList.remove('hidden');
  setTimeout(()=>b.classList.add('hidden'),4000);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
