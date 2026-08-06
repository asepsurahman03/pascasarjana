<?php
$pageTitle='Data Mahasiswa';
require_once __DIR__.'/../includes/functions.php';requireAdmin();
$allProdi=getAllProdi();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $a=$_POST['action']??'';
    if($a==='add'||$a==='edit'){
        $nim       = trim($_POST['nim']??'');
        $nama      = trim($_POST['nama']??'');
        $prodi_id  = (int)($_POST['prodi_id']??0);
        $angkatan  = (int)($_POST['angkatan']??date('Y'));
        $status    = $_POST['status']??'Aktif';
        $jk        = $_POST['jenis_kelamin']??'';
        $nik       = trim($_POST['nik']??'');
        $nama_ibu  = trim($_POST['nama_ibu']??'');
        $agama     = trim($_POST['agama']??'Islam');
        $kelas     = trim($_POST['kelas']??'Kelas B');
        $konsentrasi = trim($_POST['konsentrasi']??'');
        $tempat_lahir = trim($_POST['tempat_lahir']??'');
        $tanggal_lahir = trim($_POST['tanggal_lahir']??'')?:null;
        $no_hp     = trim($_POST['no_hp']??'');
        $email     = trim($_POST['email']??'');
        $alamat    = trim($_POST['alamat']??'');
        $judul_tesis = trim($_POST['judul_tesis']??'');
        $dosen_pembimbing = trim($_POST['dosen_pembimbing']??'');

        if($a==='add'){
            dbExecute("INSERT INTO mahasiswa(nim,nama,prodi_id,angkatan,status,nik,jenis_kelamin,nama_ibu,agama,kelas,konsentrasi,tempat_lahir,tanggal_lahir,no_hp,email,alamat,judul_tesis,dosen_pembimbing)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [$nim,$nama,$prodi_id,$angkatan,$status,$nik,$jk,$nama_ibu,$agama,$kelas,$konsentrasi,$tempat_lahir,$tanggal_lahir,$no_hp,$email,$alamat,$judul_tesis,$dosen_pembimbing]);
            logActivity('Tambah mahasiswa','mahasiswa',"NIM: $nim - $nama");
            $_SESSION['flash']=['type'=>'success','message'=>'Mahasiswa berhasil ditambahkan.'];
        } else {
            $id=(int)($_POST['id']??0);
            dbExecute("UPDATE mahasiswa SET nim=?,nama=?,prodi_id=?,angkatan=?,status=?,nik=?,jenis_kelamin=?,nama_ibu=?,agama=?,kelas=?,konsentrasi=?,tempat_lahir=?,tanggal_lahir=?,no_hp=?,email=?,alamat=?,judul_tesis=?,dosen_pembimbing=? WHERE id=?",
                [$nim,$nama,$prodi_id,$angkatan,$status,$nik,$jk,$nama_ibu,$agama,$kelas,$konsentrasi,$tempat_lahir,$tanggal_lahir,$no_hp,$email,$alamat,$judul_tesis,$dosen_pembimbing,$id]);
            logActivity('Edit mahasiswa','mahasiswa',"NIM: $nim - $nama");
            $_SESSION['flash']=['type'=>'success','message'=>'Data mahasiswa diperbarui.'];
        }
        header('Location: mahasiswa');exit;
    }
    if($a==='delete'){
        $id=(int)($_POST['id']??0);
        $mhs=dbQueryOne("SELECT nim,nama FROM mahasiswa WHERE id=?",[$id]);
        dbExecute("DELETE FROM mahasiswa WHERE id=?",[$id]);
        logActivity('Hapus mahasiswa','mahasiswa',"NIM: {$mhs['nim']} - {$mhs['nama']}");
        $_SESSION['flash']=['type'=>'success','message'=>'Mahasiswa dihapus.'];
        header('Location: mahasiswa');exit;
    }
}
$fp=(int)($_GET['prodi_id']??0);$fs=$_GET['status']??'';$fa=(int)($_GET['angkatan']??0);$q=trim($_GET['q']??'');$pg=(int)($_GET['page']??1);
$w=['1=1'];$p=[];if($fp){$w[]='m.prodi_id=?';$p[]=$fp;}if($fs){$w[]='m.status=?';$p[]=$fs;}if($fa){$w[]='m.angkatan=?';$p[]=$fa;}if($q){$w[]='(m.nim LIKE ? OR m.nama LIKE ?)';$p[]="$q%";$p[]="$q%";}
$ws=implode(' AND ',$w);$tot=dbQueryOne("SELECT COUNT(*) as c FROM mahasiswa m WHERE $ws",$p)['c'];$pag=paginate($tot,15,$pg);
$list=dbQuery("SELECT m.*,p.nama as pnama,p.warna_hex FROM mahasiswa m LEFT JOIN prodi p ON p.id=m.prodi_id WHERE $ws ORDER BY m.angkatan DESC,m.nim ASC LIMIT 15 OFFSET {$pag['offset']}",$p);
$angkatanList=dbQuery("SELECT DISTINCT angkatan FROM mahasiswa ORDER BY angkatan DESC");
$aktif=dbCount('mahasiswa','status=?',['Aktif']);
$cuti=dbCount('mahasiswa','status=?',['Cuti']);
$lulus=dbCount('mahasiswa','status=?',['Lulus']);
$flash=getFlash();
require_once __DIR__.'/../includes/header.php';
?>

<?php if($flash): ?>
<div id="flash-msg" class="mb-4 px-5 py-4 rounded-2xl font-semibold text-sm flex items-center gap-3 <?=$flash['type']==='success'?'bg-emerald-50 text-emerald-700 border border-emerald-200':'bg-red-50 text-red-700 border border-red-200'?>">
  <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?=$flash['type']==='success'?'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z':'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'?>"/></svg>
  <?=e($flash['message'])?>
</div>
<?php endif; ?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Data Mahasiswa</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola seluruh data mahasiswa program pascasarjana</p>
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <a href="../api/export_mahasiswa?<?=http_build_query($_GET)?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl font-semibold transition-all text-sm shadow-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      Export CSV
    </a>
    <button onclick="openAddMhs()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] hover:from-[#7a0a42] hover:to-[#8c0c4c] text-white rounded-xl font-semibold transition-all shadow-md hover:shadow-lg text-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Tambah Mahasiswa
    </button>
  </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-2xl p-5 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-slate-500 to-slate-700 flex items-center justify-center shrink-0">
      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?=$tot?></div>
      <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total</div>
    </div>
  </div>
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-2xl p-5 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center shrink-0">
      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?=$aktif?></div>
      <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Aktif</div>
    </div>
  </div>
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-2xl p-5 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shrink-0">
      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?=$cuti?></div>
      <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Cuti</div>
    </div>
  </div>
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-2xl p-5 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shrink-0">
      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
    </div>
    <div>
      <div class="text-2xl font-bold text-slate-800 dark:text-white"><?=$lulus?></div>
      <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Lulus</div>
    </div>
  </div>
</div>

<!-- Filter Panel -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-2xl shadow-sm p-5 mb-6">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
      <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Cari Mahasiswa</label>
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <input type="text" name="q" value="<?=e($q)?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" placeholder="Cari NIM atau nama...">
      </div>
    </div>
    <div class="min-w-[160px]">
      <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Program Studi</label>
      <select name="prodi_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        <option value="">Semua Prodi</option>
        <?php foreach($allProdi as $pr):?><option value="<?=$pr['id']?>" <?=$fp==$pr['id']?'selected':''?>><?=e($pr['nama'])?></option><?php endforeach;?>
      </select>
    </div>
    <div class="min-w-[120px]">
      <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Angkatan</label>
      <select name="angkatan" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        <option value="">Semua</option>
        <?php foreach($angkatanList as $ang):?><option value="<?=$ang['angkatan']?>" <?=$fa==$ang['angkatan']?'selected':''?>><?=$ang['angkatan']?></option><?php endforeach;?>
      </select>
    </div>
    <div class="min-w-[120px]">
      <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Status</label>
      <select name="status" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        <option value="">Semua</option>
        <?php foreach(['Aktif','Cuti','Lulus','DO'] as $st):?><option value="<?=$st?>" <?=$fs===$st?'selected':''?>><?=$st?></option><?php endforeach;?>
      </select>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold transition-all shadow-md hover:shadow-lg text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
        Filter
      </button>
      <a href="mahasiswa" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl font-semibold transition-all text-sm">Reset</a>
    </div>
  </form>
</div>

<!-- Data Table -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden">
  <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
    <div>
      <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white">Daftar Mahasiswa</h2>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Menampilkan <strong class="text-slate-700 dark:text-slate-300"><?=count($list)?></strong> dari <strong class="text-slate-700 dark:text-slate-300"><?=$tot?></strong> mahasiswa</p>
    </div>
    <?php if($q||$fp||$fs||$fa): ?>
    <span class="px-3 py-1.5 bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-[#8c0c4c]/20 dark:text-[#f06ea4] text-xs font-bold rounded-lg">Filter Aktif</span>
    <?php endif; ?>
  </div>
  
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-slate-50 dark:bg-slate-900/50">
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mahasiswa</th>
          <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Angkatan</th>
          <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kontak</th>
          <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tempat, Tgl Lahir</th>
          <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
          <th class="text-right py-4 px-6 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
        <?php if(empty($list)): ?>
        <tr>
          <td colspan="6" class="py-20">
            <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500">
              <div class="w-20 h-20 bg-slate-50 dark:bg-slate-700/30 rounded-full flex items-center justify-center mb-4">
                <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <p class="text-base font-semibold mb-1">Tidak ada mahasiswa ditemukan</p>
              <p class="text-sm">Coba ubah filter pencarian Anda</p>
            </div>
          </td>
        </tr>
        <?php else: foreach($list as $m): ?>
        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group">
          <td class="py-4 px-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#8c0c4c] to-[#c2527a] text-white font-bold flex items-center justify-center text-sm shadow-sm shrink-0">
                <?=strtoupper(substr($m['nama'],0,1))?>
              </div>
              <div>
                <div class="font-semibold text-slate-800 dark:text-white group-hover:text-[#8c0c4c] dark:group-hover:text-[#f06ea4] transition-colors"><?=e($m['nama'])?></div>
                <div class="flex items-center gap-2 mt-0.5">
                  <code class="text-xs text-slate-400 dark:text-slate-500 font-mono"><?=e($m['nim'])?></code>
                  <?php if($m['jenis_kelamin']): ?>
                  <span class="text-[10px] font-bold px-1.5 py-0.5 rounded <?=$m['jenis_kelamin']==='Pria'?'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400':'bg-pink-100 text-pink-600 dark:bg-pink-900/30 dark:text-pink-400'?>"><?=$m['jenis_kelamin']?></span>
                  <?php endif; ?>
                </div>
                <?php if($m['pnama']): ?>
                <span class="inline-flex items-center text-[10px] px-1.5 py-0.5 rounded font-semibold mt-1" style="background:<?=e($m['warna_hex']??'#8c0c4c')?>20;color:<?=e($m['warna_hex']??'#8c0c4c')?>"><?=e($m['pnama'])?></span>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td class="py-4 px-4">
            <span class="text-sm font-bold text-slate-700 dark:text-slate-300"><?=$m['angkatan']?></span>
            <?php if($m['kelas']): ?><div class="text-xs text-slate-400 mt-0.5"><?=e($m['kelas'])?></div><?php endif; ?>
          </td>
          <td class="py-4 px-4">
            <div class="flex flex-col gap-1">
              <?php if($m['no_hp']): ?>
              <a href="https://wa.me/<?=preg_replace('/[^0-9]/','',$m['no_hp'])?>" target="_blank" class="inline-flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 hover:underline font-mono">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                <?=e($m['no_hp'])?>
              </a>
              <?php endif; ?>
              <?php if($m['email']): ?>
              <a href="mailto:<?=e($m['email'])?>" class="text-xs text-slate-500 dark:text-slate-400 hover:text-[#8c0c4c] transition break-all"><?=e($m['email'])?></a>
              <?php endif; ?>
            </div>
          </td>
          <td class="py-4 px-4">
            <div class="text-xs text-slate-600 dark:text-slate-400">
              <?php if($m['tempat_lahir']): ?><div class="font-medium"><?=e($m['tempat_lahir'])?></div><?php endif; ?>
              <?php if($m['tanggal_lahir'] && $m['tanggal_lahir']!='0000-00-00'): ?><div class="text-slate-400"><?=formatTanggal($m['tanggal_lahir'])?></div><?php endif; ?>
            </div>
          </td>
          <td class="py-4 px-4"><?=statusMhsBadge($m['status'])?></td>
          <td class="py-4 px-6">
            <div class="flex justify-end items-center gap-1">
              <button onclick="detailMhs(<?=htmlspecialchars(json_encode($m),ENT_QUOTES)?>)" class="w-8 h-8 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-all" title="Detail">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              </button>
              <button onclick="editMhs(<?=htmlspecialchars(json_encode($m),ENT_QUOTES)?>)" class="w-8 h-8 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 transition-all" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
              <form method="POST" class="inline" onsubmit="return confirm('Hapus mahasiswa ini? Tindakan tidak bisa dibatalkan.')">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$m['id']?>">
                <button class="w-8 h-8 inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all" title="Hapus">
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
  <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">
    <?=renderPagination($pag,"mahasiswa.php?q=".urlencode($q)."&prodi_id=$fp&status=".urlencode($fs)."&angkatan=$fa")?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Detail -->
<div id="modal-detail-mhs" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-black/60 backdrop-blur-sm absolute inset-0" onclick="closeModal('modal-detail-mhs')"></div>
  <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xl">
    <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <h3 class="font-display font-bold text-lg text-slate-800 dark:text-white">Detail Mahasiswa</h3>
      </div>
      <button onclick="closeModal('modal-detail-mhs')" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="p-6 space-y-4 text-sm">
      <div class="grid grid-cols-2 gap-4">
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">NIM</span>
          <strong id="det-nim" class="text-slate-800 dark:text-white text-base font-mono"></strong>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">NIK</span>
          <strong id="det-nik" class="text-slate-800 dark:text-white font-mono"></strong>
        </div>
        <div class="col-span-2 bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Nama Lengkap</span>
          <strong id="det-nama" class="text-slate-800 dark:text-white text-base"></strong>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Jenis Kelamin</span>
          <strong id="det-jk" class="text-slate-800 dark:text-white"></strong>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Nama Ibu</span>
          <strong id="det-ibu" class="text-slate-800 dark:text-white"></strong>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Program Studi</span>
          <strong id="det-prodi" class="text-[#8c0c4c] dark:text-[#f06ea4]"></strong>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Angkatan / Kelas</span>
          <strong id="det-angkatan" class="text-slate-800 dark:text-white text-base"></strong>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Tempat, Tgl Lahir</span>
          <strong id="det-lahir" class="text-slate-800 dark:text-white"></strong>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Agama</span>
          <strong id="det-agama" class="text-slate-800 dark:text-white"></strong>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Status</span>
          <span id="det-status" class="px-2.5 py-1 rounded-lg text-xs font-bold inline-block mt-0.5"></span>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">No. HP / WhatsApp</span>
          <a id="det-hp-link" href="#" target="_blank" class="text-emerald-600 font-mono text-xs"></a>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Email</span>
          <strong id="det-email" class="text-slate-800 dark:text-white break-all text-xs"></strong>
        </div>
        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Dosen Pembimbing</span>
          <strong id="det-dosen" class="text-slate-800 dark:text-white"></strong>
        </div>
        <div class="col-span-2 bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Judul Tesis / Disertasi</span>
          <p id="det-judul" class="text-slate-700 dark:text-slate-300 leading-relaxed font-medium"></p>
        </div>
        <div class="col-span-2 bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-4">
          <span class="block text-[10px] uppercase tracking-wider font-bold text-slate-400 mb-1.5">Alamat Lengkap</span>
          <p id="det-alamat" class="text-slate-600 dark:text-slate-400"></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah/Edit -->
<div id="modal-mhs" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-black/60 backdrop-blur-sm absolute inset-0" onclick="closeModal('modal-mhs')"></div>
  <div class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xl">
    <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        </div>
        <h3 id="modal-mhs-title" class="font-display font-bold text-lg text-slate-800 dark:text-white">Tambah Mahasiswa</h3>
      </div>
      <button onclick="closeModal('modal-mhs')" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form method="POST" class="p-6" id="form-mhs">
      <input type="hidden" name="action" id="mhs-action" value="add">
      <input type="hidden" name="id" id="mhs-id">
      
      <!-- Identitas -->
      <div class="mb-5">
        <h4 class="text-xs font-bold text-[#8c0c4c] uppercase tracking-wider mb-3 flex items-center gap-2">
          <div class="w-1 h-4 bg-[#8c0c4c] rounded"></div> Identitas Mahasiswa
        </h4>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">NIM <span class="text-red-500">*</span></label>
            <input type="text" name="nim" id="mhs-nim" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all font-mono" required>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">NIK</label>
            <input type="text" name="nik" id="mhs-nik" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all font-mono" maxlength="16" placeholder="16 digit NIK">
          </div>
          <div class="col-span-2">
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" name="nama" id="mhs-nama" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" required>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Jenis Kelamin</label>
            <select name="jenis_kelamin" id="mhs-jk" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
              <option value="">- Pilih -</option>
              <option value="Pria">Pria</option>
              <option value="Wanita">Wanita</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Nama Ibu Kandung</label>
            <input type="text" name="nama_ibu" id="mhs-ibu" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Tempat Lahir</label>
            <input type="text" name="tempat_lahir" id="mhs-tempat" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" id="mhs-tgl-lahir" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Agama</label>
            <select name="agama" id="mhs-agama" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
              <?php foreach(['Islam','Kristen Protestan','Kristen Katolik','Hindu','Buddha','Konghucu'] as $ag):?><option value="<?=$ag?>"><?=$ag?></option><?php endforeach;?>
            </select>
          </div>
        </div>
      </div>

      <!-- Akademik -->
      <div class="mb-5">
        <h4 class="text-xs font-bold text-[#8c0c4c] uppercase tracking-wider mb-3 flex items-center gap-2">
          <div class="w-1 h-4 bg-[#8c0c4c] rounded"></div> Info Akademik
        </h4>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Program Studi <span class="text-red-500">*</span></label>
            <select name="prodi_id" id="mhs-prodi" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" required>
              <option value="">-- Pilih Prodi --</option>
              <?php foreach($allProdi as $pr):?><option value="<?=$pr['id']?>"><?=e($pr['nama'])?></option><?php endforeach;?>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Angkatan <span class="text-red-500">*</span></label>
            <input type="number" name="angkatan" id="mhs-angkatan" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" value="<?=date('Y')?>" required>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Kelas</label>
            <select name="kelas" id="mhs-kelas" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
              <option value="Kelas A">Kelas A</option>
              <option value="Kelas B" selected>Kelas B</option>
              <option value="Kelas C">Kelas C</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Status</label>
            <select name="status" id="mhs-status" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
              <option value="Aktif">Aktif</option><option value="Cuti">Cuti</option><option value="Lulus">Lulus</option><option value="DO">DO</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Konsentrasi</label>
            <input type="text" name="konsentrasi" id="mhs-konsentrasi" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Dosen Pembimbing</label>
            <input type="text" name="dosen_pembimbing" id="mhs-dosen" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
          </div>
          <div class="col-span-2">
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Judul Tesis / Disertasi</label>
            <textarea name="judul_tesis" id="mhs-judul" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" rows="2"></textarea>
          </div>
        </div>
      </div>

      <!-- Kontak -->
      <div class="mb-5">
        <h4 class="text-xs font-bold text-[#8c0c4c] uppercase tracking-wider mb-3 flex items-center gap-2">
          <div class="w-1 h-4 bg-[#8c0c4c] rounded"></div> Kontak & Alamat
        </h4>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">No. HP (WhatsApp)</label>
            <input type="text" name="no_hp" id="mhs-hp" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all font-mono" placeholder="08xx-xxxx-xxxx">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Email</label>
            <input type="email" name="email" id="mhs-email" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
          </div>
          <div class="col-span-2">
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 uppercase tracking-wider">Alamat Lengkap</label>
            <textarea name="alamat" id="mhs-alamat" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" rows="2"></textarea>
          </div>
        </div>
      </div>
      
      <div class="flex gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] hover:from-[#7a0a42] hover:to-[#8c0c4c] text-white rounded-xl font-semibold transition-all shadow-md hover:shadow-lg text-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Simpan Data
        </button>
        <button type="button" onclick="closeModal('modal-mhs')" class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl font-semibold transition-all text-sm">Batal</button>
      </div>
    </form>
  </div>
</div>

<?php
$pageScript="
function detailMhs(d) {
    document.getElementById('det-nim').textContent = d.nim||'-';
    document.getElementById('det-nik').textContent = d.nik||'-';
    document.getElementById('det-nama').textContent = d.nama||'-';
    document.getElementById('det-jk').textContent = d.jenis_kelamin||'-';
    document.getElementById('det-ibu').textContent = d.nama_ibu||'-';
    document.getElementById('det-prodi').textContent = d.pnama||'-';
    document.getElementById('det-angkatan').textContent = (d.angkatan||'-') + (d.kelas ? ' / ' + d.kelas : '');
    document.getElementById('det-lahir').textContent = (d.tempat_lahir||'') + (d.tanggal_lahir ? ', ' + d.tanggal_lahir : '');
    document.getElementById('det-agama').textContent = d.agama||'-';
    const stMap={'Aktif':'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400','Cuti':'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400','Lulus':'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400','DO':'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400'};
    document.getElementById('det-status').className='px-2.5 py-1 rounded-lg text-xs font-bold inline-block mt-0.5 '+(stMap[d.status]||'bg-slate-100 text-slate-500');
    document.getElementById('det-status').textContent=d.status||'-';
    const hp = d.no_hp||'';
    const hpEl = document.getElementById('det-hp-link');
    hpEl.textContent = hp||'-';
    hpEl.href = hp ? 'https://wa.me/'+hp.replace(/[^0-9]/g,'') : '#';
    document.getElementById('det-email').textContent=d.email||'-';
    document.getElementById('det-dosen').textContent=d.dosen_pembimbing||'-';
    document.getElementById('det-judul').textContent=d.judul_tesis||'-';
    document.getElementById('det-alamat').textContent=d.alamat||'-';
    openModal('modal-detail-mhs');
}
function openAddMhs(){
    document.getElementById('modal-mhs-title').textContent='Tambah Mahasiswa';
    document.getElementById('mhs-action').value='add';
    document.getElementById('mhs-id').value='';
    document.getElementById('form-mhs').reset();
    document.getElementById('mhs-angkatan').value=new Date().getFullYear();
    openModal('modal-mhs');
}
function editMhs(d){
    document.getElementById('modal-mhs-title').textContent='Edit Mahasiswa';
    document.getElementById('mhs-action').value='edit';
    document.getElementById('mhs-id').value=d.id;
    document.getElementById('mhs-nim').value=d.nim||'';
    document.getElementById('mhs-nik').value=d.nik||'';
    document.getElementById('mhs-nama').value=d.nama||'';
    document.getElementById('mhs-jk').value=d.jenis_kelamin||'';
    document.getElementById('mhs-ibu').value=d.nama_ibu||'';
    document.getElementById('mhs-tempat').value=d.tempat_lahir||'';
    document.getElementById('mhs-tgl-lahir').value=d.tanggal_lahir||'';
    document.getElementById('mhs-agama').value=d.agama||'Islam';
    document.getElementById('mhs-prodi').value=d.prodi_id||'';
    document.getElementById('mhs-angkatan').value=d.angkatan||'';
    document.getElementById('mhs-kelas').value=d.kelas||'Kelas B';
    document.getElementById('mhs-status').value=d.status||'Aktif';
    document.getElementById('mhs-konsentrasi').value=d.konsentrasi||'';
    document.getElementById('mhs-dosen').value=d.dosen_pembimbing||'';
    document.getElementById('mhs-judul').value=d.judul_tesis||'';
    document.getElementById('mhs-hp').value=d.no_hp||'';
    document.getElementById('mhs-email').value=d.email||'';
    document.getElementById('mhs-alamat').value=d.alamat||'';
    openModal('modal-mhs');
}
// Auto-hide flash
setTimeout(()=>{ const f=document.getElementById('flash-msg'); if(f) f.style.display='none'; },4000);
";
require_once __DIR__.'/../includes/footer.php';
?>
