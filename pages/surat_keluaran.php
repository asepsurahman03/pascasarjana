<?php
$pageTitle  = 'Surat Keluaran';
$breadcrumb = [['label'=>'Surat Keluaran']];
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$allProdi = getAllProdi();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    if ($a === 'delete') {
        dbExecute("DELETE FROM surat WHERE id=?", [(int)$_POST['id']]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Surat dihapus.'];
        header('Location: surat_keluaran' . ($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '')); exit;
    }
    if ($a === 'update_status') {
        $sid = (int)$_POST['id'];
        $old = dbQueryOne("SELECT status,isi_surat FROM surat WHERE id=?",[$sid]);
        if ($old && $old['status']==='Selesai') {
            dbExecute("INSERT INTO surat_versi(surat_id,isi_lama,diubah_oleh)VALUES(?,?,?)",[$sid,$old['isi_surat'],$_SESSION['user_id']]);
        }
        dbExecute("UPDATE surat SET status=?,updated_by=?,updated_at=NOW() WHERE id=?",[$_POST['status'],$_SESSION['user_id'],$sid]);
        echo json_encode(['ok'=>true]); exit;
    }
}

// Filters
$fp  = (int)($_GET['prodi_id'] ?? 0);
$fs  = $_GET['status'] ?? '';
$fj  = $_GET['jenis']  ?? '';
$fb  = (int)($_GET['bulan'] ?? 0);
$fy  = (int)($_GET['tahun']  ?? 0);
$q   = trim($_GET['q'] ?? '');
$pg  = max(1,(int)($_GET['page'] ?? 1));
$pp  = 20;

$w = ['1=1']; $p = [];
if ($fp) { $w[] = 's.prodi_id=?'; $p[] = $fp; }
if ($fs) { $w[] = 's.status=?';   $p[] = $fs; }
if ($fj) { $w[] = 's.jenis_surat=?'; $p[] = $fj; }
if ($fb) { $w[] = 'MONTH(s.tanggal)=?'; $p[] = $fb; }
if ($fy) { $w[] = 'YEAR(s.tanggal)=?';  $p[] = $fy; }
if ($q)  { $w[] = '(s.nomor_surat LIKE ? OR s.nama_penerima LIKE ? OR s.perihal LIKE ?)'; $p[]="%$q%";$p[]="%$q%";$p[]="%$q%"; }

$ws  = implode(' AND ', $w);
$tot = (int)(dbQueryOne("SELECT COUNT(*) as c FROM surat s WHERE $ws", $p)['c'] ?? 0);
$pag = paginate($tot, $pp, $pg);

$list = dbQuery("
    SELECT s.*, p.nama as pnama, p.kode as pkode, p.prefix_surat, p.warna_hex,
           s.drive_url, s.drive_file_id
    FROM surat s JOIN prodi p ON p.id=s.prodi_id
    WHERE $ws
    ORDER BY s.tanggal DESC, s.id DESC
    LIMIT $pp OFFSET {$pag['offset']}", $p);

// Stats ringkas untuk filter aktif
$statsRaw = dbQuery("SELECT status, COUNT(*) as c FROM surat s WHERE $ws GROUP BY status", $p);
$statsMap  = array_column($statsRaw, 'c', 'status');

// Dropdown helpers
$jenisList  = dbQuery("SELECT DISTINCT jenis_surat FROM surat ORDER BY jenis_surat");
$tahunList  = dbQuery("SELECT DISTINCT YEAR(tanggal) as tahun FROM surat ORDER BY tahun DESC");
$namaBulan  = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

$stColors = [
    'Draf'        => ['bg'=>'from-slate-400 to-slate-500', 'text'=>'text-slate-600 dark:text-slate-400', 'icon'=>'📝', 'border'=>'border-slate-200 dark:border-slate-700'],
    'Proses'      => ['bg'=>'from-amber-400 to-orange-500', 'text'=>'text-amber-600 dark:text-amber-400', 'icon'=>'⚡', 'border'=>'border-amber-200 dark:border-amber-800/50'],
    'Selesai'     => ['bg'=>'from-emerald-400 to-teal-500', 'text'=>'text-emerald-600 dark:text-emerald-400', 'icon'=>'✅', 'border'=>'border-emerald-200 dark:border-emerald-800/50'],
];

require_once __DIR__ . '/../includes/header.php';

// Ambil semua template untuk panel cepat
$allTpl = dbQuery("SELECT id, jenis_surat, nama_template, is_massal, variabel_tersedia FROM template_surat ORDER BY jenis_surat");
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Surat Keluaran</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola pembuatan dan pengiriman surat resmi Pascasarjana</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="agenda_surat" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold transition-all text-sm shadow-sm border bg-white text-slate-600 border-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg> Buku Agenda
    </a>
    <?php $exportQs = http_build_query($_GET); ?>
    <a href="../api/export_surat?<?= $exportQs ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold transition-all text-sm shadow-sm border bg-white text-slate-600 border-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Export CSV
    </a>
    <a href="surat_buat" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold shadow-md hover:shadow-lg transition-all text-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Buat Surat
    </a>
  </div>
</div>

<!-- Stat Mini Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-5 text-center cursor-pointer hover:shadow-md hover:-translate-y-1 transition-all" onclick="filterByStatus('')">
    <div class="text-3xl font-display font-bold text-slate-800 dark:text-white mb-1"><?= $tot ?></div>
    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Surat</div>
  </div>
  <?php foreach ($stColors as $stk => $stv): ?>
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-5 text-center cursor-pointer hover:shadow-md hover:-translate-y-1 transition-all relative overflow-hidden group" onclick="filterByStatus('<?=$stk?>')">
    <div class="absolute -right-4 -top-4 w-16 h-16 rounded-full bg-gradient-to-br <?=$stv['bg']?> opacity-10 group-hover:scale-150 transition-transform duration-500"></div>
    <div class="relative z-10">
      <div class="text-3xl font-display font-bold mb-1 <?=$stv['text']?>"><?= $statsMap[$stk] ?? 0 ?></div>
      <div class="text-xs font-bold uppercase tracking-wider <?=$stv['text']?> opacity-80"><?= $stv['icon'] ?> <?= $stk ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ══ PANEL TEMPLATE SURAT CEPAT ══ -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden mb-6">
  <div class="p-5 flex items-center justify-between cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors" onclick="toggleTpl()" id="tpl-header">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-lg shadow-inner">
        📋
      </div>
      <div>
        <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white leading-none mb-1">Template Surat Cepat</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400">Pilih template untuk membuat surat dengan cepat</p>
      </div>
    </div>
    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 transition-transform duration-300" id="tpl-chevron">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>
  </div>

  <div id="tpl-panel" class="hidden border-t border-slate-100 dark:border-slate-700/60 p-5 bg-slate-50/50 dark:bg-slate-800/50">
    <?php
    $tplGroups = [
      '📨 Undangan' => ['Undangan Sidang Tesis','Undangan Seminar Proposal','Surat Undangan Rapat'],
      '📄 Keterangan & SK' => ['Surat Keterangan Aktif','SK Kelulusan','Surat Tugas Pembimbing','Surat Bebas Perpustakaan','Surat Pengantar Ijazah'],
      '📝 Permohonan & Surat Resmi' => ['Surat Izin Penelitian','Surat Permohonan','Surat Perpanjangan Studi','Surat Cuti Akademik','Surat Rekomendasi'],
      '📢 Pemberitahuan & Lainnya' => ['Surat Pemberitahuan','Surat Tugas Mengajar','Berita Acara'],
    ];
    $tplByJenis = [];
    foreach ($allTpl as $t) $tplByJenis[$t['jenis_surat']] = $t;

    $iconMap = [
      'Undangan Sidang Tesis'      => ['icon'=>'🎓','color'=>'#22c55e'],
      'Undangan Seminar Proposal'  => ['icon'=>'📊','color'=>'#3b82f6'],
      'Surat Undangan Rapat'       => ['icon'=>'🤝','color'=>'#3b82f6'],
      'Surat Keterangan Aktif'     => ['icon'=>'✅','color'=>'#34d399'],
      'SK Kelulusan'               => ['icon'=>'🏆','color'=>'#f59e0b'],
      'Surat Tugas Pembimbing'     => ['icon'=>'👨‍🏫','color'=>'#3b82f6'],
      'Surat Bebas Perpustakaan'   => ['icon'=>'📚','color'=>'#818cf8'],
      'Surat Pengantar Ijazah'     => ['icon'=>'📜','color'=>'#f472b6'],
      'Surat Izin Penelitian'      => ['icon'=>'🔬','color'=>'#34d399'],
      'Surat Permohonan'           => ['icon'=>'📮','color'=>'#fb923c'],
      'Surat Perpanjangan Studi'   => ['icon'=>'⏳','color'=>'#facc15'],
      'Surat Cuti Akademik'        => ['icon'=>'🏖️','color'=>'#94a3b8'],
      'Surat Rekomendasi'          => ['icon'=>'⭐','color'=>'#f59e0b'],
      'Surat Pemberitahuan'        => ['icon'=>'📣','color'=>'#3b82f6'],
      'Surat Tugas Mengajar'       => ['icon'=>'📖','color'=>'#3b82f6'],
      'Berita Acara'               => ['icon'=>'📋','color'=>'#94a3b8'],
    ];
    ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
      <?php foreach ($tplGroups as $grpLabel => $tplJenisList): ?>
      <div>
        <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3"><?= e($grpLabel) ?></h4>
        <div class="space-y-2">
          <?php foreach ($tplJenisList as $jenis):
            $t = $tplByJenis[$jenis] ?? null;
            $ic = $iconMap[$jenis] ?? ['icon'=>'📄','color'=>'#94a3b8'];
            $url = 'surat_buat.php' . ($t ? '?jenis='.urlencode($t['jenis_surat']).'&tpl_id='.$t['id'] : '');
          ?>
          <a href="<?= $url ?>" class="flex items-center gap-3 p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-700/50 hover:shadow-md hover:border-<?=$ic['color']?>/30 transition-all group">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0" style="background:<?= $ic['color'] ?>15; color:<?= $ic['color'] ?>">
              <?= $ic['icon'] ?>
            </div>
            <div class="flex-1 min-w-0">
              <h5 class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"><?= e($jenis) ?></h5>
              <?php if (!empty($t['is_massal'])): ?>
              <span class="inline-block mt-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 uppercase">Massal</span>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    
    <!-- Lainnya -->
    <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
      <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Lainnya & Kustom</h4>
      <div class="flex flex-wrap gap-3">
        <?php
        foreach ($allTpl as $t):
          $allInGroups = array_merge(...array_values($tplGroups));
          if (in_array($t['jenis_surat'], $allInGroups)) continue;
        ?>
        <a href="surat_buat?jenis=<?= urlencode($t['jenis_surat']) ?>&tpl_id=<?= $t['id'] ?>" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 hover:shadow-md transition-all">
          <span>📄</span> <?= e($t['jenis_surat']) ?>
        </a>
        <?php endforeach; ?>
        <a href="surat_buat" class="flex items-center gap-2 px-4 py-2 bg-slate-800 dark:bg-slate-200 border border-transparent rounded-xl text-sm font-semibold text-white dark:text-slate-900 hover:shadow-md transition-all">
          <span>✏️</span> Buat Kosong (AI)
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Filter Bar -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-5 mb-6">
  <form method="GET" id="filter-form" class="flex flex-wrap gap-4 items-end">
    <div class="flex-1 min-w-[200px]">
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">🔍 Cari</label>
      <input type="text" name="q" value="<?=e($q)?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" placeholder="Nomor / Penerima / Perihal...">
    </div>
    <div class="w-full sm:w-auto min-w-[150px]">
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Prodi</label>
      <select name="prodi_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] transition-colors">
        <option value="">Semua Prodi</option>
        <?php foreach($allProdi as $pr): ?>
        <option value="<?=$pr['id']?>" <?=$fp==$pr['id']?'selected':''?>><?=e($pr['nama'])?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="w-full sm:w-auto min-w-[150px]">
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Jenis</label>
      <select name="jenis" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] transition-colors">
        <option value="">Semua Jenis</option>
        <?php foreach($jenisList as $j): ?>
        <option value="<?=e($j['jenis_surat'])?>" <?=$fj===$j['jenis_surat']?'selected':''?>><?=e($j['jenis_surat'])?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex gap-2 w-full sm:w-auto">
      <div class="flex-1 min-w-[120px]">
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bulan</label>
        <select name="bulan" id="sel-bulan" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c]">
          <option value="">Semua</option>
          <?php for($b=1;$b<=12;$b++): ?>
          <option value="<?=$b?>" <?=$fb==$b?'selected':''?>><?=$namaBulan[$b]?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="flex-1 min-w-[100px]">
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun</label>
        <select name="tahun" id="sel-tahun" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c]">
          <option value="">Semua</option>
          <?php foreach($tahunList as $ty): ?>
          <option value="<?=$ty['tahun']?>" <?=$fy==$ty['tahun']?'selected':''?>><?=$ty['tahun']?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="w-full sm:w-auto min-w-[120px]">
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
      <select name="status" id="sel-status" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c]">
        <option value="">Semua</option>
        <?php foreach(array_keys($stColors) as $st): ?>
        <option value="<?=$st?>" <?=$fs===$st?'selected':''?>><?=$st?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex gap-2 w-full md:w-auto">
      <button type="submit" class="flex-1 md:flex-none px-6 py-2.5 bg-slate-800 dark:bg-slate-200 text-white dark:text-slate-900 rounded-xl font-semibold shadow-md hover:shadow-lg transition-all text-sm">Cari</button>
      <a href="surat_keluaran" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl font-semibold transition-all text-sm">Reset</a>
    </div>
  </form>
</div>

<!-- Tabel Surat Keluaran -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden mb-8">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700/60">
        <tr>
          <th class="text-center py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-12">No</th>
          <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider min-w-[200px]">Surat & Jenis</th>
          <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider min-w-[250px]">Penerima & Perihal</th>
          <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-32">Tanggal</th>
          <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider w-36">Status</th>
          <th class="text-right py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider w-40">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
      <?php if (empty($list)): ?>
      <tr>
        <td colspan="6" class="py-16 text-center">
          <div class="text-6xl mb-4 opacity-50">📭</div>
          <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-1">Tidak ada surat ditemukan</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">Coba ubah filter pencarian Anda atau buat surat baru.</p>
          <a href="surat_buat" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold shadow-md transition-all text-sm">Buat Surat Baru</a>
        </td>
      </tr>
      <?php else:
        $no = $pag['offset'] + 1;
        foreach ($list as $s):
          $scfg = $stColors[$s['status']] ?? ['bg'=>'from-slate-400 to-slate-500','text'=>'text-slate-500','border'=>'border-slate-200'];
      ?>
      <tr id="row-<?=$s['id']?>" class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group">
        <td class="py-4 px-4 text-center font-medium text-slate-400"><?=$no++?></td>
        <td class="py-4 px-4">
          <div class="font-mono text-sm font-bold text-[#8c0c4c] dark:text-[#f06ea4] mb-1 flex items-center gap-2">
            <?=e($s['nomor_surat'])?>
            <?php if (!empty($s['drive_url'])): ?>
            <a href="<?=e($s['drive_url'])?>" target="_blank"
               title="Sudah di Google Drive — Klik untuk buka"
               class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 hover:bg-blue-200 transition">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M4.433 22l4-6.928H22l-4 6.928H4.433zM2 17.072L6 10.144 8 13.608 4 20.536 2 17.072zM8.433 3l4 6.928H2l4-6.928H8.433z"/></svg>
                Drive
            </a>
            <?php endif; ?>
          </div>
          <div class="flex items-center gap-2 flex-wrap mt-1">
            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md border border-slate-200 dark:border-slate-600"><?=e($s['jenis_surat'])?></span>
            <?php if ($s['jenis_penerima'] !== 'individual'): ?>
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 uppercase tracking-wider">Massal</span>
            <?php endif; ?>
          </div>
        </td>
        <td class="py-4 px-4">
          <div class="mb-1">
            <span class="text-xs px-2 py-0.5 rounded-full font-bold mr-1 align-middle" style="background:<?=e($s['warna_hex'])?>15;color:<?=e($s['warna_hex'])?>; border: 1px solid <?=e($s['warna_hex'])?>40">
              <?=e($s['prefix_surat'] ?: $s['pkode'])?>
            </span>
            <span class="font-bold text-slate-800 dark:text-white align-middle"><?=e($s['nama_penerima'])?></span>
            <?php if (!empty($s['nim_nidn'])): ?>
            <span class="text-xs text-slate-400 dark:text-slate-500 font-mono ml-1 align-middle">(<?=e($s['nim_nidn'])?>)</span>
            <?php endif; ?>
          </div>
          <div class="text-sm text-slate-500 dark:text-slate-400 line-clamp-1" title="<?=e($s['perihal'])?>"><?=e($s['perihal'])?></div>
        </td>
        <td class="py-4 px-4">
          <?php $ts = strtotime($s['tanggal']); ?>
          <div class="font-semibold text-slate-800 dark:text-white"><?=date('d/m/Y',$ts)?></div>
          <div class="text-xs text-slate-400 dark:text-slate-500"><?=e($s['hari'] ?? getNamaHari($s['tanggal']))?></div>
        </td>
        <td class="py-4 px-4">
          <select onchange="updateStatus(<?=$s['id']?>, this.value, this)"
                  class="text-xs font-bold rounded-xl px-3 py-2 border cursor-pointer outline-none focus:ring-2 focus:ring-opacity-50 w-full transition-all bg-white dark:bg-slate-900 <?=$scfg['text']?> <?=$scfg['border']?>">
            <?php foreach(array_keys($stColors) as $stOpt): ?>
            <option value="<?=$stOpt?>" <?=$s['status']===$stOpt?'selected':''?> class="text-slate-800 dark:text-slate-200"><?=$stOpt?></option>
            <?php endforeach; ?>
          </select>
        </td>
        <td class="py-4 px-6 text-right">
          <div class="flex gap-1.5 justify-end opacity-60 group-hover:opacity-100 transition-opacity">
            <a href="../api/cetak_surat?id=<?=$s['id']?>&mode=view" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white dark:bg-blue-900/30 dark:hover:bg-blue-600 transition-colors" title="Preview">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </a>
            <a href="../api/cetak_surat?id=<?=$s['id']?>&mode=print" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white dark:bg-emerald-900/30 dark:hover:bg-emerald-600 transition-colors" title="Cetak / PDF">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            </a>
            <a href="surat_buat?dup=<?=$s['id']?>" class="w-8 h-8 flex items-center justify-center rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white dark:bg-amber-900/30 dark:hover:bg-amber-600 transition-colors" title="Gunakan sbg Template (Duplikat)">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
            </a>
            <form method="POST" class="inline" onsubmit="return confirm('Hapus surat permanen?\n\nNomor: <?=e(addslashes($s['nomor_surat']))?>')">
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$s['id']?>">
              <button class="w-8 h-8 flex items-center justify-center rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white dark:bg-red-900/30 dark:hover:bg-red-600 transition-colors" title="Hapus">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if($tot > 0): ?>
  <div class="flex flex-col sm:flex-row items-center justify-between p-5 border-t border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-900/30 gap-4">
    <div class="text-sm font-semibold text-slate-500 dark:text-slate-400">
      Menampilkan <span class="text-slate-800 dark:text-white"><?= $pag['offset']+1 ?></span> – <span class="text-slate-800 dark:text-white"><?= min($pag['offset']+$pp, $tot) ?></span> dari <span class="text-slate-800 dark:text-white"><?= $tot ?></span> surat
    </div>
    <div class="flex items-center gap-1.5">
      <?php
        $qStr = http_build_query(array_filter(['q'=>$q,'prodi_id'=>$fp,'status'=>$fs,'jenis'=>$fj,'bulan'=>$fb,'tahun'=>$fy]));
        for ($i=1; $i<=$pag['total_pages']; $i++):
          $active = ($i===$pag['current']) ? 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-900 shadow-md' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700';
      ?>
      <a href="?<?=$qStr?>&page=<?=$i?>" class="min-w-[32px] h-8 flex items-center justify-center rounded-lg text-sm font-bold transition-all <?=$active?>"><?=$i?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
function toggleTpl() {
  const p = document.getElementById('tpl-panel');
  const c = document.getElementById('tpl-chevron');
  if (p.classList.contains('hidden')) {
    p.classList.remove('hidden');
    c.style.transform = 'rotate(180deg)';
  } else {
    p.classList.add('hidden');
    c.style.transform = 'rotate(0deg)';
  }
}
if(window.location.hash === '#tpl') toggleTpl();

function filterByStatus(st) {
    document.getElementById('sel-status').value = st;
    document.getElementById('filter-form').submit();
}

async function updateStatus(id, status, sel) {
    try {
        const r = await fetch('surat_keluaran.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `action=update_status&id=${id}&status=${encodeURIComponent(status)}`
        });
        const d = await r.json();
        if (d.ok) {
            showToast('Status diperbarui','success');
            // Reload after small delay to reflect color changes
            setTimeout(()=>window.location.reload(), 1000);
        } else {
            showToast('Gagal update','error');
        }
    } catch(e) { showToast('Koneksi error','error'); }
}

// Autofilter bulan/tahun
document.getElementById('sel-bulan')?.addEventListener('change', () => document.getElementById('filter-form').submit());
document.getElementById('sel-tahun')?.addEventListener('change', () => document.getElementById('filter-form').submit());
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
