<?php
$pageTitle = 'Tugas & Catatan';
require_once __DIR__.'/../includes/functions.php';
requireAdmin();

$allProdi = getAllProdi();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    if ($a === 'add') {
        dbExecute("INSERT INTO tugas(judul,deskripsi,prodi_id,prioritas,deadline,label_warna,created_by)VALUES(?,?,?,?,?,?,?)",
        [trim($_POST['judul']),trim($_POST['deskripsi']??''),($_POST['prodi_id']?:null),$_POST['prioritas'],($_POST['deadline']?:null),$_POST['label_warna']??'#3b82f6',$_SESSION['user_id']]);
        $_SESSION['flash']=['type'=>'success','message'=>'Tugas berhasil ditambahkan.'];
        header('Location: tugas'); exit;
    }
    if ($a === 'delete') {
        dbExecute("DELETE FROM tugas WHERE id=?",[(int)$_POST['id']]);
        $_SESSION['flash']=['type'=>'success','message'=>'Tugas dihapus.'];
        header('Location: tugas'); exit;
    }
    if ($a === 'status') {
        dbExecute("UPDATE tugas SET status=? WHERE id=?",[$_POST['status'],(int)$_POST['id']]);
        header('Location: tugas'); exit;
    }
    if ($a === 'add_catatan') {
        dbExecute("INSERT INTO catatan(isi,prodi_id,warna,created_by)VALUES(?,?,?,?)",[trim($_POST['isi']),($_POST['prodi_id']?:null),$_POST['warna']??'#f59e0b',$_SESSION['user_id']]);
        $_SESSION['flash']=['type'=>'success','message'=>'Catatan ditambahkan.'];
        header('Location: tugas'); exit;
    }
    if ($a === 'del_catatan') {
        dbExecute("DELETE FROM catatan WHERE id=?",[(int)$_POST['id']]);
        $_SESSION['flash']=['type'=>'success','message'=>'Catatan dihapus.'];
        header('Location: tugas'); exit;
    }
}

$fp   = (int)($_GET['prodi_id'] ?? 0);
$fst  = $_GET['status'] ?? '';
$view = $_GET['view'] ?? 'kanban';

$w=['1=1']; $p=[];
if ($fp) { $w[]='t.prodi_id=?'; $p[]=$fp; }
if ($fst) { $w[]='t.status=?'; $p[]=$fst; }
$ws = implode(' AND ', $w);

$list = dbQuery("SELECT t.*,p.nama as pnama, p.warna_hex FROM tugas t LEFT JOIN prodi p ON p.id=t.prodi_id WHERE $ws ORDER BY FIELD(t.prioritas,'Tinggi','Sedang','Rendah'),t.deadline ASC", $p);
$catatan = dbQuery("SELECT c.*,p.nama as pnama FROM catatan c LEFT JOIN prodi p ON p.id=c.prodi_id WHERE c.created_by=? ORDER BY c.created_at DESC", [$_SESSION['user_id']]);

$totalTugas = count($list);
$totalSelesai = count(array_filter($list, fn($x) => $x['status'] === 'Selesai'));

require_once __DIR__.'/../includes/header.php';
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Task Board</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola daftar tugas dan catatan pribadi</p>
  </div>
  <div class="flex gap-2">
    <a href="?view=list" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold transition-all text-sm shadow-sm border <?=$view==='list'?'bg-slate-800 text-white border-slate-800 dark:bg-slate-200 dark:text-slate-900':'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300'?>">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg> List
    </a>
    <a href="?view=kanban" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold transition-all text-sm shadow-sm border <?=$view==='kanban'?'bg-slate-800 text-white border-slate-800 dark:bg-slate-200 dark:text-slate-900':'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300'?>">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg> Kanban
    </a>
    <button onclick="openModal('modal-tugas')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold shadow-md hover:shadow-lg transition-all text-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Tugas
    </button>
  </div>
</div>

<!-- Progress Overview -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-5 mb-6 flex flex-col md:flex-row gap-6 items-center">
  <div class="w-full md:w-1/3">
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Progress Keseluruhan</span>
      <span class="text-sm font-bold text-[#8c0c4c] dark:text-[#f06ea4]"><?=$totalTugas>0?round(($totalSelesai/$totalTugas)*100):0?>%</span>
    </div>
    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden">
      <div class="h-2.5 rounded-full bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] transition-all" style="width:<?=$totalTugas>0?($totalSelesai/$totalTugas)*100:0?>%"></div>
    </div>
    <p class="text-xs text-slate-500 mt-2"><?=$totalSelesai?> dari <?=$totalTugas?> tugas selesai</p>
  </div>

  <div class="hidden md:block w-px h-12 bg-slate-200 dark:bg-slate-700"></div>

  <form method="GET" class="w-full md:flex-1 flex gap-3 flex-wrap">
    <input type="hidden" name="view" value="<?=e($view)?>">
    <div class="flex-1 min-w-[150px]">
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Filter Prodi</label>
      <select name="prodi_id" onchange="this.form.submit()" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        <option value="">Semua Program Studi</option>
        <?php foreach($allProdi as $pr):?><option value="<?=$pr['id']?>" <?=$fp==$pr['id']?'selected':''?>><?=e($pr['nama'])?></option><?php endforeach;?>
      </select>
    </div>
    <?php if($view==='list'): ?>
    <div class="min-w-[130px]">
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
      <select name="status" onchange="this.form.submit()" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        <option value="">Semua</option>
        <?php foreach(['Belum','Dikerjakan','Selesai'] as $st):?><option value="<?=$st?>" <?=$fst===$st?'selected':''?>><?=$st?></option><?php endforeach;?>
      </select>
    </div>
    <?php endif; ?>
  </form>
</div>

<?php if($view==='list'): ?>
<!-- LIST VIEW -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden mb-8">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/50">
        <tr>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Detail Tugas</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Prioritas</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Deadline</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="text-right py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
        <?php if(empty($list)): ?>
        <tr><td colspan="5" class="py-12 text-center text-slate-400">Tidak ada tugas ditemukan</td></tr>
        <?php else: foreach($list as $t): 
          $isDone = $t['status']==='Selesai';
          $isDekat = isDeadlineDekat($t['deadline']??'');
        ?>
        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group <?= $isDone ? 'opacity-60' : '' ?>">
          <td class="py-4 px-6">
            <div class="flex items-start gap-3">
              <span class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0" style="background:<?=e($t['label_warna']??'#8c0c4c')?>"></span>
              <div>
                <p class="font-bold text-slate-800 dark:text-white <?=$isDone?'line-through':''?>"><?=e($t['judul'])?></p>
                <div class="flex items-center gap-2 mt-1 text-xs">
                  <span class="text-slate-500 font-medium"><?=e($t['pnama']??'Umum')?></span>
                </div>
              </div>
            </div>
          </td>
          <td class="py-4 px-6"><?=prioritasBadge($t['prioritas'])?></td>
          <td class="py-4 px-6">
            <?php if($t['deadline']): ?>
              <span class="inline-flex items-center gap-1.5 font-mono text-xs <?=$isDekat&&!$isDone?'text-red-500 font-bold':'text-slate-500'?>">
                <?=$isDekat&&!$isDone?'⚠️ ':''?><?=formatTanggal($t['deadline'])?>
              </span>
            <?php else: ?><span class="text-slate-400">-</span><?php endif; ?>
          </td>
          <td class="py-4 px-6">
            <form method="POST" class="inline">
              <input type="hidden" name="action" value="status">
              <input type="hidden" name="id" value="<?=$t['id']?>">
              <select name="status" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1.5 text-xs font-bold focus:ring-2 focus:ring-[#8c0c4c]/20 outline-none
                <?=$t['status']==='Selesai'?'text-emerald-600':($t['status']==='Dikerjakan'?'text-amber-600':'text-slate-600')?>">
                <option value="Belum" <?=$t['status']==='Belum'?'selected':''?>>Belum</option>
                <option value="Dikerjakan" <?=$t['status']==='Dikerjakan'?'selected':''?>>Dikerjakan</option>
                <option value="Selesai" <?=$t['status']==='Selesai'?'selected':''?>>Selesai</option>
              </select>
            </form>
          </td>
          <td class="py-4 px-6 text-right">
            <form method="POST" onsubmit="return confirm('Hapus tugas ini?')">
              <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$t['id']?>">
              <button class="w-8 h-8 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
            </form>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<!-- KANBAN VIEW -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
  <?php 
  $kanban = ['Belum'=>[],'Dikerjakan'=>[],'Selesai'=>[]];
  foreach($list as $t) $kanban[$t['status']][]=$t;
  
  $columns = [
    'Belum' => ['icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'col'=>'text-slate-600', 'bg'=>'bg-slate-100', 'border'=>'border-slate-200'],
    'Dikerjakan' => ['icon'=>'M13 10V3L4 14h7v7l9-11h-7z', 'col'=>'text-amber-600', 'bg'=>'bg-amber-100', 'border'=>'border-amber-200'],
    'Selesai' => ['icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'col'=>'text-emerald-600', 'bg'=>'bg-emerald-100', 'border'=>'border-emerald-200']
  ];
  
  foreach($columns as $sk => $sconf): 
  ?>
  <div class="flex flex-col bg-slate-50 dark:bg-slate-900/50 rounded-3xl border border-slate-100 dark:border-slate-700/60 p-4">
    <!-- Kanban Header -->
    <div class="flex items-center justify-between mb-4 px-2">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center <?=$sconf['bg']?> dark:bg-opacity-20 <?=$sconf['col']?>">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?=$sconf['icon']?>"/></svg>
        </div>
        <h3 class="font-bold text-slate-800 dark:text-white"><?=$sk?></h3>
      </div>
      <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm text-slate-600 dark:text-slate-400"><?=count($kanban[$sk])?></span>
    </div>
    
    <!-- Kanban Items -->
    <div class="flex-1 space-y-3 min-h-[200px]">
      <?php if(empty($kanban[$sk])): ?>
      <div class="h-full flex items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl">
        <span class="text-sm text-slate-400 font-medium">Kosong</span>
      </div>
      <?php else: foreach($kanban[$sk] as $t): $isDone = $sk==='Selesai'; ?>
      <div class="bg-white dark:bg-slate-800 border-l-4 rounded-2xl shadow-sm p-4 relative group transition-all hover:shadow-md <?=$isDone?'opacity-70':''?>" style="border-left-color:<?=e($t['label_warna']??'#8c0c4c')?>">
        
        <!-- Status Dropdown Quick Update -->
        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
          <form method="POST">
            <input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?=$t['id']?>">
            <select name="status" onchange="this.form.submit()" class="bg-slate-100 dark:bg-slate-700 text-[10px] font-bold rounded px-1 py-0.5 cursor-pointer outline-none text-slate-600 dark:text-slate-300">
              <option value="Belum" <?=$t['status']==='Belum'?'selected':''?>>Ke Belum</option>
              <option value="Dikerjakan" <?=$t['status']==='Dikerjakan'?'selected':''?>>Ke Dikerjakan</option>
              <option value="Selesai" <?=$t['status']==='Selesai'?'selected':''?>>Ke Selesai</option>
            </select>
          </form>
        </div>

        <div class="pr-6 mb-2">
          <h4 class="font-bold text-sm text-slate-800 dark:text-white leading-snug <?=$isDone?'line-through text-slate-500':''?>"><?=e($t['judul'])?></h4>
          <?php if(!empty($t['deskripsi'])): ?>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2"><?=e($t['deskripsi'])?></p>
          <?php endif; ?>
        </div>
        
        <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100 dark:border-slate-700/60">
          <div class="flex items-center gap-1.5">
            <?=prioritasBadge($t['prioritas'])?>
          </div>
          <?php if($t['deadline']): ?>
          <div class="flex items-center gap-1 text-xs font-mono <?=(!$isDone && isDeadlineDekat($t['deadline']))?'text-red-500 font-bold':'text-slate-400'">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <?=date('d/m',strtotime($t['deadline']))?>
          </div>
          <?php endif; ?>
        </div>
        
        <form method="POST" onsubmit="return confirm('Hapus tugas?')" class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
           <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$t['id']?>">
           <button class="w-6 h-6 bg-red-100 text-red-500 rounded flex items-center justify-center hover:bg-red-500 hover:text-white transition-colors"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </form>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- CATATAN SECTION -->
<div class="mb-8">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white flex items-center gap-2">
      <span>📌</span> Catatan Cepat
    </h2>
    <button onclick="openModal('modal-catatan')" class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl font-semibold transition-all text-sm shadow-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Tambah
    </button>
  </div>
  
  <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
    <?php if(empty($catatan)): ?>
    <div class="col-span-full py-8 text-center text-slate-400 bg-white/50 border-2 border-dashed border-slate-200 rounded-3xl">
      Tidak ada catatan tersimpan.
    </div>
    <?php else: foreach($catatan as $c): ?>
    <div class="relative group rounded-3xl p-5 overflow-hidden shadow-sm hover:shadow-md transition-all hover:-translate-y-1 cursor-default" style="background-color: <?=e($c['warna'])?>15; border: 1px solid <?=e($c['warna'])?>40;">
      <div class="absolute top-0 right-0 w-16 h-16 transform translate-x-8 -translate-y-8 rounded-full" style="background-color: <?=e($c['warna'])?>20;"></div>
      
      <p class="text-sm font-medium text-slate-800 dark:text-slate-100 whitespace-pre-wrap leading-relaxed relative z-10"><?=e($c['isi'])?></p>
      
      <div class="flex items-center justify-between mt-4 pt-3 relative z-10" style="border-top: 1px solid <?=e($c['warna'])?>30;">
        <span class="text-[10px] font-bold uppercase tracking-wider" style="color: <?=e($c['warna'])?>; opacity: 0.8;"><?=e($c['pnama']??'Umum')?></span>
        <form method="POST" onsubmit="return confirm('Hapus catatan?')" class="inline-block opacity-0 group-hover:opacity-100 transition-opacity">
          <input type="hidden" name="action" value="del_catatan"><input type="hidden" name="id" value="<?=$c['id']?>">
          <button class="w-6 h-6 flex items-center justify-center rounded-full bg-red-100 text-red-500 hover:bg-red-500 hover:text-white transition-colors">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </form>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<!-- Modal Tambah Tugas -->
<div id="modal-tugas" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-black/60 backdrop-blur-sm absolute inset-0" onclick="closeModal('modal-tugas')"></div>
  <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xl">
    <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
      <h3 class="font-display font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2"><svg class="w-5 h-5 text-[#8c0c4c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg> Tambah Tugas</h3>
      <button onclick="closeModal('modal-tugas')" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition">✕</button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" value="add">
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Judul Tugas <span class="text-red-500">*</span></label>
        <input type="text" name="judul" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20" required>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Deskripsi</label>
        <textarea name="deskripsi" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20" rows="3"></textarea>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Terkait Prodi</label>
          <select name="prodi_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
            <option value="">Semua (Umum)</option>
            <?php foreach($allProdi as $pr):?><option value="<?=$pr['id']?>"><?=e($pr['nama'])?></option><?php endforeach;?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Prioritas</label>
          <select name="prioritas" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
            <option value="Sedang">Sedang</option><option value="Tinggi">Tinggi</option><option value="Rendah">Rendah</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tenggat Waktu (Deadline)</label>
          <input type="date" name="deadline" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Label Warna</label>
          <input type="color" name="label_warna" value="#3b82f6" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-2 py-1.5 h-[42px] cursor-pointer focus:outline-none focus:border-[#8c0c4c]">
        </div>
      </div>
      <div class="flex gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
        <button type="submit" class="flex-1 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold text-sm shadow-md transition-all">Simpan Tugas</button>
        <button type="button" onclick="closeModal('modal-tugas')" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl font-semibold text-sm transition-all">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Tambah Catatan -->
<div id="modal-catatan" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-black/60 backdrop-blur-sm absolute inset-0" onclick="closeModal('modal-catatan')"></div>
  <div class="relative w-full max-w-sm rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xl">
    <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
      <h3 class="font-display font-bold text-lg text-slate-800 dark:text-white">Tambah Catatan</h3>
      <button onclick="closeModal('modal-catatan')" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition">✕</button>
    </div>
    <form method="POST" class="p-5 space-y-4">
      <input type="hidden" name="action" value="add_catatan">
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Isi Catatan <span class="text-red-500">*</span></label>
        <textarea name="isi" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c]" rows="4" required placeholder="Tuliskan catatan ringkas..."></textarea>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Terkait Prodi</label>
          <select name="prodi_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c]">
            <option value="">Umum</option>
            <?php foreach($allProdi as $pr):?><option value="<?=$pr['id']?>"><?=e($pr['kode'])?></option><?php endforeach;?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Warna</label>
          <input type="color" name="warna" value="#f59e0b" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-2 py-1 h-[38px] cursor-pointer">
        </div>
      </div>
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 px-5 py-2.5 bg-[#f59e0b] hover:bg-[#d97706] text-white rounded-xl font-semibold text-sm shadow-md transition-all">Simpan</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
