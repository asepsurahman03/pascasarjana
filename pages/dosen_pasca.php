<?php
$pageTitle = 'Daftar Dosen Pascasarjana';
require_once __DIR__.'/../includes/functions.php';
requireAdmin();
$allProdi = getAllProdi();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['action'] ?? '';
    if ($a === 'add' || $a === 'edit') {
        $prodi_id   = !empty($_POST['prodi_id']) ? (int)$_POST['prodi_id'] : null;
        $nidn       = trim($_POST['nidn']       ?? '');
        $nama       = trim($_POST['nama']       ?? '');
        $kualifikasi= trim($_POST['kualifikasi']?? '');
        $email      = trim($_POST['email']      ?? '');
        $jabatan    = trim($_POST['jabatan']    ?? '');
        $status     = $_POST['status']           ?? 'Aktif';
        $scopus     = trim($_POST['scopus_id']  ?? '') ?: null;
        $sinta      = trim($_POST['sinta_id']   ?? '') ?: null;
        $orcid      = trim($_POST['orcid_id']   ?? '') ?: null;
        $wos        = trim($_POST['wos_id']     ?? '') ?: null;
        $scholar    = trim($_POST['google_scholar'] ?? '') ?: null;

        if ($a === 'add') {
            dbExecute("INSERT INTO dosen(prodi_id,nidn,nama,kualifikasi,email,jabatan,status,scopus_id,sinta_id,orcid_id,wos_id,google_scholar)VALUES(?,?,?,?,?,?,?,?,?,?,?,?)",
                [$prodi_id,$nidn,$nama,$kualifikasi,$email,$jabatan,$status,$scopus,$sinta,$orcid,$wos,$scholar]);
            $_SESSION['flash']=['type'=>'success','message'=>'Dosen berhasil ditambahkan.'];
        } else {
            dbExecute("UPDATE dosen SET prodi_id=?,nidn=?,nama=?,kualifikasi=?,email=?,jabatan=?,status=?,scopus_id=?,sinta_id=?,orcid_id=?,wos_id=?,google_scholar=? WHERE id=?",
                [$prodi_id,$nidn,$nama,$kualifikasi,$email,$jabatan,$status,$scopus,$sinta,$orcid,$wos,$scholar,(int)$_POST['id']]);
            $_SESSION['flash']=['type'=>'success','message'=>'Data dosen diperbarui.'];
        }
        header('Location: dosen_pasca'); exit;
    }
    if ($a === 'delete') { dbExecute("DELETE FROM dosen WHERE id=?",[(int)$_POST['id']]);$_SESSION['flash']=['type'=>'success','message'=>'Dosen dihapus.'];header('Location: dosen_pasca');exit; }
}

$q = trim($_GET['q']??'');$fp=(int)($_GET['prodi_id']??0);$fs=$_GET['status']??'';$pg=(int)($_GET['page']??1);
$w=['1=1'];$p=[];
if($q){$w[]='(d.nidn LIKE ? OR d.nama LIKE ?)';$p[]="%$q%";$p[]="%$q%";}
if($fp){$w[]='d.prodi_id=?';$p[]=$fp;}
if($fs){$w[]='d.status=?';$p[]=$fs;}
$ws=implode(' AND ',$w);
$tot=dbQueryOne("SELECT COUNT(*) as c FROM dosen d WHERE $ws",$p)['c']??0;
$pag=paginate($tot,15,$pg);
$dosenData=dbQuery("SELECT d.*,p.nama as pnama,p.warna_hex FROM dosen d LEFT JOIN prodi p ON d.prodi_id=p.id WHERE $ws ORDER BY d.nama ASC LIMIT 15 OFFSET {$pag['offset']}",$p);
$totAktif=dbCount('dosen','status=?',['Aktif']);
$totDosen=dbCount('dosen');

require_once __DIR__.'/../includes/header.php';
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Dosen Pascasarjana</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Manajemen data dosen dan tenaga pengajar</p>
  </div>
  <button onclick="openModal('modal-dosen')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] hover:from-[#7a0a42] hover:to-[#8c0c4c] text-white rounded-xl font-semibold transition-all shadow-md hover:shadow-lg text-sm self-start sm:self-auto">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Tambah Dosen
  </button>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-2 gap-4 mb-8">
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-2xl p-5 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] flex items-center justify-center shrink-0">
      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?=$totDosen?></div>
      <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Dosen</div>
    </div>
  </div>
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-2xl p-5 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center shrink-0">
      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?=$totAktif?></div>
      <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Aktif Mengajar</div>
    </div>
  </div>
</div>

<!-- Filter -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-2xl shadow-sm p-5 mb-6">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
      <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Cari Dosen</label>
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
        <input type="text" name="q" value="<?=e($q)?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" placeholder="Cari NIDN atau Nama...">
      </div>
    </div>
    <div class="min-w-[160px]">
      <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Program Studi</label>
      <select name="prodi_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        <option value="">Semua Prodi</option>
        <?php foreach($allProdi as $pr):?><option value="<?=$pr['id']?>" <?=$fp==$pr['id']?'selected':''?>><?=e($pr['nama'])?></option><?php endforeach;?>
      </select>
    </div>
    <div class="min-w-[130px]">
      <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Status</label>
      <select name="status" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        <option value="">Semua</option>
        <option value="Aktif" <?=$fs==='Aktif'?'selected':''?>>Aktif</option>
        <option value="Tidak Aktif" <?=$fs==='Tidak Aktif'?'selected':''?>>Tidak Aktif</option>
      </select>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold text-sm shadow-md">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
        Filter
      </button>
      <a href="dosen_pasca" class="inline-flex items-center px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl font-semibold text-sm">Reset</a>
    </div>
  </form>
</div>

<!-- Data Table -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden">
  <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
    <div>
      <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white">Daftar Dosen</h2>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Total <strong class="text-slate-700 dark:text-slate-300"><?=$tot?></strong> dosen ditemukan</p>
    </div>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-900/50">
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Dosen</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">NIDN</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Program Studi</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Kualifikasi</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Jabatan</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="text-right py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
        <?php if(empty($dosenData)): ?>
        <tr><td colspan="7" class="py-20">
          <div class="flex flex-col items-center justify-center text-slate-400">
            <div class="w-20 h-20 bg-slate-50 dark:bg-slate-700/30 rounded-full flex items-center justify-center mb-4">
              <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <p class="font-semibold">Tidak ada data dosen</p>
          </div>
        </td></tr>
        <?php else: foreach($dosenData as $d): ?>
        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group">
          <td class="py-4 px-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold flex items-center justify-center text-sm shadow-sm shrink-0">
                <?=strtoupper(substr($d['nama'],0,1))?>
              </div>
              <div>
                <div class="font-semibold text-slate-800 dark:text-white group-hover:text-[#8c0c4c] dark:group-hover:text-[#f06ea4] transition-colors"><?=e($d['nama'])?></div>
                <div class="text-xs text-slate-400 truncate max-w-[180px]"><?=e($d['email']??'-')?></div>
              </div>
            </div>
          </td>
          <td class="py-4 px-6"><code class="text-xs text-slate-500 dark:text-slate-400 font-mono bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded-lg"><?=e($d['nidn']??'-')?></code></td>
          <td class="py-4 px-6">
            <?php if(!empty($d['pnama'])): ?>
            <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-lg font-semibold" style="background:<?=e($d['warna_hex'])?>20;color:<?=e($d['warna_hex'])?>"><?=e($d['pnama'])?></span>
            <?php else: ?><span class="text-slate-400">-</span><?php endif; ?>
          </td>
          <td class="py-4 px-6 text-sm text-slate-600 dark:text-slate-400"><?=e($d['kualifikasi']??'-')?></td>
          <td class="py-4 px-6">
            <?php if(!empty($d['jabatan'])): ?>
            <span class="text-xs px-2.5 py-1 rounded-lg font-semibold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400"><?=e($d['jabatan'])?></span>
            <?php else: ?><span class="text-slate-400 text-xs">-</span><?php endif; ?>
          </td>
          <td class="py-4 px-6">
            <?php $isAktif=($d['status']??'Aktif')==='Aktif'; ?>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold <?=$isAktif?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400':'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'?>">
              <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
              <?=$isAktif?'Aktif':'Tidak Aktif'?>
            </span>
          </td>
          <td class="py-4 px-6">
            <div class="flex justify-end items-center gap-1">
              <button onclick="editDosen(<?=htmlspecialchars(json_encode($d),ENT_QUOTES)?>)" class="w-8 h-8 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 transition-all" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
              <form method="POST" class="inline" onsubmit="return confirm('Hapus data dosen ini?')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$d['id']?>">
                <button class="w-8 h-8 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all">
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
  <?php if($pag['total_pages']>1): ?>
  <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700"><?=renderPagination($pag,"dosen_pasca.php?q=$q&prodi_id=$fp&status=$fs")?></div>
  <?php endif; ?>
</div>

<!-- Modal Tambah/Edit Dosen -->
<div id="modal-dosen" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-black/60 backdrop-blur-sm absolute inset-0" onclick="closeModal('modal-dosen')"></div>
  <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xl">
    <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <h3 id="modal-dosen-title" class="font-display font-bold text-lg text-slate-800 dark:text-white">Tambah Dosen</h3>
      </div>
      <button onclick="closeModal('modal-dosen')" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="action" id="dosen-action" value="add">
      <input type="hidden" name="id" id="dosen-id">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">NIDN</label>
          <input type="text" name="nidn" id="dosen-nidn" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Status <span class="text-red-500">*</span></label>
          <select name="status" id="dosen-status" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" required>
            <option value="Aktif">Aktif</option><option value="Tidak Aktif">Tidak Aktif</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Nama Lengkap & Gelar <span class="text-red-500">*</span></label>
        <input type="text" name="nama" id="dosen-nama" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" required placeholder="Prof. Dr. Nama, M.T.">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Program Studi</label>
        <select name="prodi_id" id="dosen-prodi" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
          <option value="">-- Pilih Prodi --</option>
          <?php foreach($allProdi as $pr):?><option value="<?=$pr['id']?>"><?=e($pr['nama'])?></option><?php endforeach;?>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Kualifikasi</label>
          <input type="text" name="kualifikasi" id="dosen-kualifikasi" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" placeholder="S3, Profesor...">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Jabatan Akademik</label>
          <input type="text" name="jabatan" id="dosen-jabatan" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" placeholder="Lektor Kepala...">
        </div>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Email</label>
        <input type="email" name="email" id="dosen-email" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
      </div>
      <!-- Academic Profile IDs -->
      <p class="text-[10px] font-bold text-[#8c0c4c] uppercase tracking-widest flex items-center gap-2">
        <span class="flex-1 h-px bg-[#8c0c4c]/20"></span> ID Profil Akademik <span class="flex-1 h-px bg-[#8c0c4c]/20"></span>
      </p>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Scopus ID</label>
          <input type="text" name="scopus_id" id="dosen-scopus" placeholder="57211234567" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Sinta ID</label>
          <input type="text" name="sinta_id" id="dosen-sinta" placeholder="6123456" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">ORCID</label>
          <input type="text" name="orcid_id" id="dosen-orcid" placeholder="0000-0000-0000-0000" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">WoS / ResearcherID</label>
          <input type="text" name="wos_id" id="dosen-wos" placeholder="AAA-1234-2020" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        </div>
        <div class="col-span-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase tracking-wider">Google Scholar (User ID)</label>
          <input type="text" name="google_scholar" id="dosen-scholar" placeholder="xxxxxxxxxxxxxxxxx" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        </div>
      </div>
      <div class="flex gap-3 pt-2 border-t border-slate-100 dark:border-slate-700">
        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Simpan
        </button>
        <button type="button" onclick="closeModal('modal-dosen')" class="flex-1 inline-flex items-center justify-center px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl font-semibold text-sm transition-all">Batal</button>
      </div>
    </form>
  </div>
</div>

<?php
$pageScript = "
function editDosen(d) {
    document.getElementById('modal-dosen-title').textContent='Edit Dosen';
    document.getElementById('dosen-action').value='edit';
    document.getElementById('dosen-id').value=d.id;
    document.getElementById('dosen-prodi').value=d.prodi_id||'';
    document.getElementById('dosen-nidn').value=d.nidn||'';
    document.getElementById('dosen-nama').value=d.nama||'';
    document.getElementById('dosen-kualifikasi').value=d.kualifikasi||'';
    document.getElementById('dosen-email').value=d.email||'';
    document.getElementById('dosen-jabatan').value=d.jabatan||'';
    document.getElementById('dosen-status').value=d.status||'Aktif';
    document.getElementById('dosen-scopus').value=d.scopus_id||'';
    document.getElementById('dosen-sinta').value=d.sinta_id||'';
    document.getElementById('dosen-orcid').value=d.orcid_id||'';
    document.getElementById('dosen-wos').value=d.wos_id||'';
    document.getElementById('dosen-scholar').value=d.google_scholar||'';
    openModal('modal-dosen');
}";
require_once __DIR__.'/../includes/footer.php';
?>
