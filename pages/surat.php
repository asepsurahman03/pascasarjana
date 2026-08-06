<?php
$pageTitle  = 'Surat Menyurat';
$breadcrumb = [['label' => 'Surat']];
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$allProdi = getAllProdi();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    if ($a === 'delete') {
        dbExecute("DELETE FROM surat WHERE id=?", [(int)$_POST['id']]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Surat dihapus.'];
        header('Location: surat'); exit;
    }
    if ($a === 'update_status') {
        $sid = (int)$_POST['id'];
        $ns  = $_POST['status'];
        // Simpan versi lama jika dari Selesai
        $old = dbQueryOne("SELECT status, isi_surat FROM surat WHERE id=?",[$sid]);
        if ($old && $old['status']==='Selesai' && $ns!=='Selesai') {
            dbExecute("INSERT INTO surat_versi(surat_id,isi_lama,diubah_oleh)VALUES(?,?,?)",[$sid,$old['isi_surat'],$_SESSION['user_id']]);
        }
        dbExecute("UPDATE surat SET status=?,updated_by=?,updated_at=NOW() WHERE id=?",[$ns,$_SESSION['user_id'],$sid]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Status diperbarui.'];
        header('Location: surat'); exit;
    }
}

// Filter & paginasi
$fp   = (int)($_GET['prodi_id'] ?? 0);
$fs   = $_GET['status']  ?? '';
$fj   = $_GET['jenis']   ?? '';
$q    = trim($_GET['q']  ?? '');
$fb   = (int)($_GET['bulan'] ?? 0);
$fy   = (int)($_GET['tahun'] ?? 0);
$pg   = (int)($_GET['page']  ?? 1);

$w = ['1=1']; $p = [];
if ($fp) { $w[] = 's.prodi_id=?';  $p[] = $fp; }
if ($fs) { $w[] = 's.status=?';    $p[] = $fs; }
if ($fj) { $w[] = 's.jenis_surat=?'; $p[] = $fj; }
if ($fb) { $w[] = 'MONTH(s.tanggal)=?'; $p[] = $fb; }
if ($fy) { $w[] = 'YEAR(s.tanggal)=?';  $p[] = $fy; }
if ($q)  { $w[] = '(s.nomor_surat LIKE ? OR s.nama_penerima LIKE ? OR s.perihal LIKE ?)'; $p[] = "%$q%"; $p[] = "%$q%"; $p[] = "%$q%"; }
$ws  = implode(' AND ', $w);
$tot = dbQueryOne("SELECT COUNT(*) as c FROM surat s WHERE $ws", $p)['c'];
$pag = paginate($tot, 15, $pg);
$list= dbQuery("SELECT s.*,p.nama as pnama,p.kode as pkode FROM surat s JOIN prodi p ON p.id=s.prodi_id WHERE $ws ORDER BY s.created_at DESC LIMIT 15 OFFSET {$pag['offset']}", $p);

// Stats ringkas
$statsStatus = dbQuery("SELECT status, COUNT(*) as c FROM surat GROUP BY status");
$statsMap    = array_column($statsStatus, 'c', 'status');

$jenisList = dbQuery("SELECT DISTINCT jenis_surat FROM surat ORDER BY jenis_surat");
$tahunList = dbQuery("SELECT DISTINCT YEAR(tanggal) as tahun FROM surat ORDER BY tahun DESC");

require_once __DIR__ . '/../includes/header.php';
?>

<script>
document.getElementById('topbar-actions').innerHTML = `
  <div class="flex gap-2">
    <a href="<?=BASE_URL?>/api/export_surat?<?=http_build_query($_GET)?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg font-semibold transition-all text-sm text-xs">📥 Export CSV</a>
    <a href="<?=BASE_URL?>/pages/surat_buat" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-nusa hover:bg-nusa-dark text-white rounded-lg font-semibold transition-all shadow hover:shadow-md text-sm text-xs">+ Buat Surat</a>
  </div>`;
</script>

<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
<?php
$stDef = [
    'Draf'         => ['var(--color-text-muted)','📝'],
    'Proses'       => ['#f59e0b','⚡'],
    'Selesai'      => ['#22c55e','✅'],
];
$stDef['TOTAL'] = ['#e2e8f0','📋'];
foreach (array_keys($stDef) as $stk):
    if ($stk === 'TOTAL') { $cnt = $tot; $col = '#e2e8f0'; $ico = '📋'; }
    else { $cnt = $statsMap[$stk] ?? 0; [$col,$ico] = $stDef[$stk]; }
?>
<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm stat-bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-4 text-center cursor-pointer hover:border-blue-500/50" onclick="filterStatus('<?=$stk==='TOTAL'?'':$stk?>')">
  <div class="text-2xl mb-1"><?=$ico?></div>
  <div class="text-xl font-bold" style="color:<?=$col?>"><?=$cnt?></div>
  <div class="text-xs text-slate-400 dark:text-slate-500"><?=$stk?></div>
</div>
<?php endforeach; ?>
</div>

<!-- Filter Bar -->
<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-4 mb-4">
  <form method="GET" id="filter-form" class="flex flex-wrap gap-3 items-end">
    <div>
      <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Cari</label>
      <input type="text" name="q" value="<?=e($q)?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors text-sm" style="width:200px" placeholder="Nomor / Penerima / Perihal">
    </div>
    <div>
      <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Prodi</label>
      <select name="prodi_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors text-sm">
        <option value="">Semua</option>
        <?php foreach($allProdi as $pr):?>
        <option value="<?=$pr['id']?>" <?=$fp==$pr['id']?'selected':''?>><?=e($pr['nama'])?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div>
      <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Jenis</label>
      <select name="jenis" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors text-sm">
        <option value="">Semua</option>
        <?php foreach($jenisList as $j):?>
        <option value="<?=e($j['jenis_surat'])?>" <?=$fj===$j['jenis_surat']?'selected':''?>><?=e($j['jenis_surat'])?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div>
      <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Status</label>
      <select name="status" id="inp-status-filter" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors text-sm">
        <option value="">Semua</option>
        <?php foreach(['Draf','Proses','Selesai'] as $st):?>
        <option value="<?=$st?>" <?=$fs===$st?'selected':''?>><?=$st?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div>
      <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Tahun</label>
      <select name="tahun" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors text-sm">
        <option value="">Semua</option>
        <?php foreach($tahunList as $ty):?>
        <option value="<?=$ty['tahun']?>" <?=$fy==$ty['tahun']?'selected':''?>><?=$ty['tahun']?></option>
        <?php endforeach;?>
      </select>
    </div>
    <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-nusa hover:bg-nusa-dark text-white rounded-lg font-semibold transition-all shadow hover:shadow-md text-sm text-sm">Filter</button>
    <a href="surat" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg font-semibold transition-all text-sm text-sm">Reset</a>
  </form>
</div>

<!-- Tabel Surat -->
<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
  <div class="p-4 border-b flex items-center justify-between border-slate-200 dark:border-slate-700">
    <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white">Daftar Surat <span class="text-slate-500 dark:text-slate-400 text-sm">(<?=$tot?> surat)</span></h2>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">
        <tr><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nomor Surat</th><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Jenis</th><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Penerima</th><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Prodi</th><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tanggal</th><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th><th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Aksi</th></tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
      <?php if (empty($list)): ?>
      <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" colspan="7" class="text-center text-slate-400 dark:text-slate-500 py-10">
        <div class="text-4xl mb-2">📭</div>
        Tidak ada surat ditemukan
      </td></tr>
      <?php else: foreach ($list as $s):
        $stColors = ['Draf'=>'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white','Proses'=>'bg-amber-900/60 text-amber-300','Selesai'=>'bg-green-900/60 text-green-300'];
        $stColor  = $stColors[$s['status']] ?? 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-white';
      ?>
      <tr>
        <td class="py-3 px-4 py-3 px-4">
          <code class="text-blue-400 text-xs"><?=e($s['nomor_surat'])?></code>
          <?php if ($s['jenis_penerima'] === 'massal'): ?>
          <span class="ml-1 text-xs px-1 py-0.5 rounded" style="background:#f59e0b22;color:#f59e0b">Massal</span>
          <?php endif; ?>
        </td>
        <td class="py-3 px-4 text-sm text-slate-800 dark:text-white"><?=e($s['jenis_surat'])?></td>
        <td class="py-3 px-4 py-3 px-4">
          <div class="text-sm text-slate-800 dark:text-white max-w-xs truncate"><?=e($s['nama_penerima'])?></div>
          <?php if (!empty($s['nim_nidn'])): ?>
          <div class="text-xs text-slate-400 dark:text-slate-500 font-mono"><?=e($s['nim_nidn'])?></div>
          <?php endif; ?>
        </td>
        <td class="py-3 px-4 py-3 px-4">
          <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:var(--color-primary)22;color:var(--color-primary)"><?=e($s['pkode'])?></span>
        </td>
        <td class="py-3 px-4 text-xs text-slate-500 dark:text-slate-400"><?=formatTanggal($s['tanggal'])?></td>
        <td class="py-3 px-4 py-3 px-4">
          <form method="POST" class="inline">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" value="<?=$s['id']?>">
            <select name="status" onchange="this.form.submit()" class="text-xs rounded-full px-2 py-0.5 border-0 cursor-pointer font-medium <?=$stColor?>" style="background:transparent">
              <?php foreach(['Draf','Proses','Selesai'] as $stOpt): ?>
              <option value="<?=$stOpt?>" <?=$s['status']===$stOpt?'selected':''?> class="bg-slate-50 dark:bg-slate-900-bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm"><?=$stOpt?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </td>
        <td class="py-3 px-4 py-3 px-4">
          <div class="flex gap-1 items-center">
            <a href="<?=BASE_URL?>/api/cetak_surat?id=<?=$s['id']?>&mode=view" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg font-semibold transition-all text-sm text-xs py-1 px-2" title="Preview">👁️</a>
            <a href="<?=BASE_URL?>/api/cetak_surat?id=<?=$s['id']?>&mode=print" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg font-semibold transition-all text-sm text-xs py-1 px-2" title="Cetak">🖨️</a>
            <a href="<?=BASE_URL?>/pages/surat_buat?dup=<?=$s['id']?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg font-semibold transition-all text-sm text-xs py-1 px-2" title="Duplikat">⎘</a>
            <form method="POST" class="inline" onsubmit="return confirm('Hapus surat <?=e(addslashes($s['nomor_surat']))?> ?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?=$s['id']?>">
              <button class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold transition-all shadow hover:shadow-md text-sm text-xs py-1 px-2">🗑</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div class="px-4 pb-4">
    <?= renderPagination($pag, "surat.php?q=$q&prodi_id=$fp&status=$fs&jenis=$fj&tahun=$fy&bulan=$fb") ?>
  </div>
</div>

<?php
$pageScript = "
function filterStatus(st){
    document.getElementById('inp-status-filter').value = st;
    document.getElementById('filter-form').submit();
}
";
require_once __DIR__ . '/../includes/footer.php';
?>
