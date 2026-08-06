<?php
$pageTitle  = 'Manajemen Program Studi';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a   = $_POST['action'] ?? '';
    $pid = (int)$_POST['prodi_id'];
    $prodi = dbQueryOne("SELECT * FROM prodi WHERE id=?", [$pid]);
    if (!$prodi) { $_SESSION['flash']=['type'=>'error','message'=>'Prodi tidak ditemukan.']; header('Location: prodi'); exit; }
    $kode = $prodi['kode'];

    if ($a === 'update_identitas') {
        dbExecute("UPDATE prodi SET kaprodi=?,nidn_kaprodi=?,kota_surat=?,prefix_surat=?,no_wa_grup=? WHERE id=?",
            [trim($_POST['kaprodi']),trim($_POST['nidn_kaprodi']),
             trim($_POST['kota_surat']??'Sukabumi'),trim($_POST['prefix_surat']),trim($_POST['no_wa_grup']??''),$pid]);
        logActivity('Update Identitas Prodi','prodi',$kode);
        $_SESSION['flash']=['type'=>'success','message'=>'Identitas prodi diperbarui.'];
        header("Location: prodi.php?tab=prodi-$pid"); exit;
    }

    // Upload gambar generik (ttd, cap, header, footer)
    $uploadMap = [
        'upload_ttd'    => ['file_ttd',    'ttd',      'ttd_',  'foto_ttd',    500*1024, ['image/png'],           'png'],
        'upload_cap'    => ['file_cap',    'cap',      'cap_',  'foto_cap',    500*1024, ['image/png'],           'png'],
        'upload_header' => ['file_header', 'kop',      'header_','foto_header', 2*1024*1024, ['image/png','image/jpeg','image/jpg'], null],
        'upload_footer' => ['file_footer', 'kop',      'footer_','foto_footer', 2*1024*1024, ['image/png','image/jpeg','image/jpg'], null],
    ];

    if (isset($uploadMap[$a])) {
        [$fileKey, $subdir, $prefix, $dbField, $maxSize, $allowedMimes, $forceExt] = $uploadMap[$a];
        $f = $_FILES[$fileKey] ?? null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) { $_SESSION['flash']=['type'=>'error','message'=>'File gagal diupload.']; header("Location: prodi.php?tab=prodi-$pid"); exit; }
        $mime = mime_content_type($f['tmp_name']);
        if (!in_array($mime, $allowedMimes)) { $_SESSION['flash']=['type'=>'error','message'=>'Format file tidak diizinkan.']; header("Location: prodi.php?tab=prodi-$pid"); exit; }
        if ($f['size'] > $maxSize) { $_SESSION['flash']=['type'=>'error','message'=>'Ukuran melebihi batas.']; header("Location: prodi.php?tab=prodi-$pid"); exit; }
        $ext     = $forceExt ?: pathinfo($f['name'], PATHINFO_EXTENSION);
        $fname   = $prefix . $kode . '.' . $ext;
        $saveTo  = BASE_PATH . '/uploads/' . $subdir . '/' . $fname;
        // Hapus file lama jika ada
        foreach (glob(BASE_PATH.'/uploads/'.$subdir.'/'.$prefix.$kode.'.*') ?: [] as $old) @unlink($old);
        if (!move_uploaded_file($f['tmp_name'], $saveTo)) { $_SESSION['flash']=['type'=>'error','message'=>'Gagal menyimpan.']; header("Location: prodi.php?tab=prodi-$pid"); exit; }
        dbExecute("UPDATE prodi SET $dbField=? WHERE id=?", [$fname, $pid]);
        logActivity("Upload $a",'prodi',$kode);
        $_SESSION['flash']=['type'=>'success','message'=>'File berhasil diupload.'];
        header("Location: prodi.php?tab=prodi-$pid"); exit;
    }

    // Hapus gambar
    $hapusMap = ['hapus_ttd'=>['ttd','ttd_','foto_ttd'],'hapus_cap'=>['cap','cap_','foto_cap'],'hapus_header'=>['kop','header_','foto_header'],'hapus_footer'=>['kop','footer_','foto_footer']];
    if (isset($hapusMap[$a])) {
        [$subdir,$prefix,$dbField] = $hapusMap[$a];
        foreach (glob(BASE_PATH.'/uploads/'.$subdir.'/'.$prefix.$kode.'.*') ?: [] as $f) @unlink($f);
        dbExecute("UPDATE prodi SET $dbField=NULL WHERE id=?",[$pid]);
        $_SESSION['flash']=['type'=>'success','message'=>'File dihapus.'];
        header("Location: prodi.php?tab=prodi-$pid"); exit;
    }
}

$prodis = getAllProdi();
$tab    = $_GET['tab'] ?? 'prodi-'.($prodis[0]['id'] ?? 1);
$subTab = $_GET['subtab'] ?? 'identitas';
$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
  <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Program Studi</h1>
  <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Konfigurasi identitas, kaprodi, dan template dokumen prodi</p>
</div>

<div class="flex flex-col lg:flex-row gap-6">
  <!-- Sidebar Prodi -->
  <div class="w-full lg:w-64 flex-shrink-0 space-y-2">
    <?php foreach($prodis as $pr): $isAct = ($tab==='prodi-'.$pr['id']); ?>
    <a href="?tab=prodi-<?=$pr['id']?>&subtab=<?=$subTab?>"
       class="flex items-center gap-3 px-4 py-3 rounded-2xl font-medium text-sm transition-all <?= $isAct ? 'bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white shadow-md shadow-[#8c0c4c]/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-100 dark:border-slate-700/60 hover:border-[#8c0c4c]/30 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]' ?>">
      <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" style="background:<?=$isAct?'rgba(255,255,255,0.2)':e($pr['warna_hex']).'20'?>;color:<?=$isAct?'#fff':e($pr['warna_hex'])?>">
        <span class="font-bold text-xs"><?=e($pr['kode'])?></span>
      </div>
      <span class="truncate"><?=$pr['nama']?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Content -->
  <div class="flex-1 min-w-0">
  <?php foreach($prodis as $pr):
    if ($tab !== 'prodi-'.$pr['id']) continue;
    $kode      = $pr['kode'];
    $ttdUrl    = getTtdUrl($kode);
    $capUrl    = getCapUrl($kode);
    $kopUrl    = getKopPath($kode, true);
    $footerUrl = getFooterPath($kode, true);
    $inputCls="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all";
  ?>

    <!-- Header Prodi Aktif -->
    <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-5">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-bold text-xl text-white shadow-md" style="background:<?=e($pr['warna_hex'])?>">
          <?=e($kode)?>
        </div>
        <div>
          <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white"><?=e($pr['nama'])?></h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5"><?=$pr['jenjang']?> &middot; Prefix Surat: <code class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 font-mono text-[#8c0c4c] dark:text-[#f06ea4] font-bold"><?=e($pr['prefix_surat']??$kode)?></code></p>
        </div>
      </div>
    </div>

    <!-- Navigation SubTab -->
    <div class="flex gap-2 p-1.5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 mb-6 shadow-sm overflow-x-auto w-max max-w-full custom-scrollbar">
      <?php 
      $subtabs = [
        'identitas'=>['icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z','label'=>'Identitas Kaprodi'],
        'kopfooter'=>['icon'=>'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z','label'=>'Kop & Footer Surat'],
        'ttd'=>['icon'=>'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z','label'=>'Tanda Tangan & Cap']
      ];
      foreach($subtabs as $st=>$stdata): ?>
      <a href="?tab=prodi-<?=$pr['id']?>&subtab=<?=$st?>" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all whitespace-nowrap <?=$subTab===$st?'bg-[#8c0c4c]/10 text-[#8c0c4c] dark:text-[#f06ea4]':'text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-700/50'?>">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?=$stdata['icon']?>"/></svg>
        <?=$stdata['label']?>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if ($subTab === 'identitas'): ?>
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <div class="xl:col-span-2 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8">
        <h3 class="font-display font-bold text-lg text-slate-800 dark:text-white mb-6">Konfigurasi Pengaturan Surat</h3>
        <form method="POST" class="space-y-5">
          <input type="hidden" name="action" value="update_identitas">
          <input type="hidden" name="prodi_id" value="<?=$pr['id']?>">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Kaprodi</label>
              <input type="text" name="kaprodi" value="<?=e($pr['kaprodi']??'')?>" class="<?=$inputCls?>" placeholder="Prof. Dr. Nama Lengkap, M.T.">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">NIDN Kaprodi</label>
              <input type="text" name="nidn_kaprodi" value="<?=e($pr['nidn_kaprodi']??'')?>" class="<?=$inputCls?> font-mono" placeholder="0001020304">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Kota Surat</label>
              <input type="text" name="kota_surat" value="<?=e($pr['kota_surat']??'Sukabumi')?>" class="<?=$inputCls?>">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Prefix Nomor Surat</label>
              <input type="text" name="prefix_surat" value="<?=e($pr['prefix_surat']??'')?>" class="<?=$inputCls?> font-mono" placeholder="MTI">
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nomor WhatsApp Grup</label>
              <input type="text" name="no_wa_grup" value="<?=e($pr['no_wa_grup']??'')?>" class="<?=$inputCls?> font-mono" placeholder="628xxxxxx (opsional)">
            </div>
          </div>
          <div class="pt-4 mt-2 border-t border-slate-100 dark:border-slate-700">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              Simpan Identitas
            </button>
          </div>
        </form>
      </div>

      <!-- Live Preview Signature -->
      <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6">
        <h3 class="font-display font-bold text-base text-slate-800 dark:text-white mb-4">Preview Tanda Tangan</h3>
        <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 flex justify-center shadow-inner">
          <div class="w-full max-w-[240px] text-center text-sm relative" style="font-family:'Times New Roman',Times,serif;color:#000;background:#fff;padding:25px;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1)">
            <p style="text-align:left;font-size:12px;margin-bottom:8px"><?=e($pr['kota_surat']??'Sukabumi')?>, <?=date('d').' '.$namaBulan[(int)date('n')].' '.date('Y')?></p>
            <p style="font-size:11px">Mengetahui,</p>
            <p style="font-size:11px;margin-bottom:10px;font-weight:bold">Ketua Program Studi<br><?=e($pr['nama'])?></p>
            <div style="position:relative;height:80px;margin:0 auto">
              <?php if($ttdUrl):?>
                <img src="<?=$ttdUrl?>" style="max-width:160px;max-height:70px;position:absolute;left:50%;transform:translateX(-50%)" alt="TTD">
              <?php else:?>
                <div style="border:2px dashed #cbd5e1;width:150px;height:65px;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:10px;margin:0 auto;border-radius:8px">(Tanda Tangan)</div>
              <?php endif;?>
              <?php if($capUrl):?>
                <img src="<?=$capUrl?>" style="max-width:110px;max-height:50px;position:absolute;left:35%;transform:translateX(-50%) rotate(-5deg);opacity:0.85;top:10px" alt="Cap">
              <?php endif;?>
            </div>
            <p style="font-weight:bold;font-size:12px;margin-top:5px;text-decoration:underline"><?=e($pr['kaprodi']??'Nama Kaprodi')?></p>
            <?php if(!empty($pr['nidn_kaprodi'])):?><p style="font-size:11px;margin-top:2px">NIDN. <?=e($pr['nidn_kaprodi'])?></p><?php endif;?>
          </div>
        </div>
      </div>
    </div>

    <?php elseif ($subTab === 'kopfooter'): ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Kop Surat -->
      <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8">
        <h3 class="font-display font-bold text-lg text-slate-800 dark:text-white mb-2">Header Kop Surat</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Muncul di bagian atas dokumen surat (Rekomendasi 1900x300px, max 2MB).</p>
        
        <div class="mb-5 flex items-center justify-center rounded-2xl overflow-hidden bg-slate-50 dark:bg-slate-900 shadow-inner" style="border:2px dashed <?=$kopUrl?'#10b981':'#cbd5e1'?>; min-height: 120px;">
          <?php if ($kopUrl): ?>
            <img src="<?=$kopUrl?>?v=<?=time()?>" alt="Kop Surat" class="w-full max-h-32 object-contain p-2" id="prev-header-<?=$pr['id']?>">
          <?php else: ?>
            <div class="text-center text-slate-400 p-6" id="prev-header-<?=$pr['id']?>">
              <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <p class="text-sm font-medium">Belum ada gambar kop</p>
            </div>
          <?php endif; ?>
        </div>

        <form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
          <input type="hidden" name="action" value="upload_header">
          <input type="hidden" name="prodi_id" value="<?=$pr['id']?>">
          <input type="file" name="file_header" accept=".png,.jpg,.jpeg,image/png,image/jpeg" class="<?=$inputCls?> flex-1" required onchange="previewImg(this,'prev-header-<?=$pr['id']?>')">
          <div class="flex gap-2">
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">Upload</button>
            <?php if($kopUrl): ?>
            <button type="submit" name="action" value="hapus_header" onclick="return confirm('Hapus gambar kop?')" class="px-4 py-2.5 bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 rounded-xl font-semibold text-sm transition-all">🗑</button>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- Footer Surat -->
      <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8">
        <h3 class="font-display font-bold text-lg text-slate-800 dark:text-white mb-2">Footer Surat</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Muncul di bagian paling bawah surat (Rekomendasi 1900x250px, max 2MB).</p>
        
        <div class="mb-5 flex items-center justify-center rounded-2xl overflow-hidden bg-slate-50 dark:bg-slate-900 shadow-inner" style="border:2px dashed <?=$footerUrl?'#10b981':'#cbd5e1'?>; min-height: 120px;">
          <?php if ($footerUrl): ?>
            <img src="<?=$footerUrl?>?v=<?=time()?>" alt="Footer Surat" class="w-full max-h-24 object-contain p-2" id="prev-footer-<?=$pr['id']?>">
          <?php else: ?>
            <div class="text-center text-slate-400 p-6" id="prev-footer-<?=$pr['id']?>">
              <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              <p class="text-sm font-medium">Belum ada gambar footer</p>
            </div>
          <?php endif; ?>
        </div>

        <form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
          <input type="hidden" name="action" value="upload_footer">
          <input type="hidden" name="prodi_id" value="<?=$pr['id']?>">
          <input type="file" name="file_footer" accept=".png,.jpg,.jpeg,image/png,image/jpeg" class="<?=$inputCls?> flex-1" required onchange="previewImg(this,'prev-footer-<?=$pr['id']?>')">
          <div class="flex gap-2">
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">Upload</button>
            <?php if($footerUrl): ?>
            <button type="submit" name="action" value="hapus_footer" onclick="return confirm('Hapus footer?')" class="px-4 py-2.5 bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 rounded-xl font-semibold text-sm transition-all">🗑</button>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <?php elseif ($subTab === 'ttd'): ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- TTD Kaprodi -->
      <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8">
        <h3 class="font-display font-bold text-lg text-slate-800 dark:text-white mb-2">Tanda Tangan Kaprodi</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Wajib format PNG transparan, maksimal 500KB.</p>
        
        <div class="mb-5 flex items-center justify-center rounded-2xl bg-slate-50 dark:bg-slate-900 shadow-inner" style="border:2px dashed <?=$ttdUrl?'#10b981':'#cbd5e1'?>; min-height: 140px; padding: 20px;">
          <?php if($ttdUrl):?>
            <img src="<?=$ttdUrl?>?v=<?=time()?>" class="max-h-24 object-contain" id="prev-ttd-<?=$pr['id']?>" alt="TTD">
          <?php else:?>
            <div class="text-center text-slate-400" id="prev-ttd-<?=$pr['id']?>">
              <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
              <p class="text-sm font-medium">Belum ada Tanda Tangan</p>
            </div>
          <?php endif;?>
        </div>

        <form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
          <input type="hidden" name="action" value="upload_ttd">
          <input type="hidden" name="prodi_id" value="<?=$pr['id']?>">
          <input type="file" name="file_ttd" accept=".png,image/png" class="<?=$inputCls?> flex-1" required onchange="previewImg(this,'prev-ttd-<?=$pr['id']?>')">
          <div class="flex gap-2">
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">Upload</button>
            <?php if($ttdUrl):?>
            <button type="submit" name="action" value="hapus_ttd" onclick="return confirm('Hapus tanda tangan?')" class="px-4 py-2.5 bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 rounded-xl font-semibold text-sm transition-all">🗑</button>
            <?php endif;?>
          </div>
        </form>
      </div>

      <!-- Cap Prodi -->
      <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8">
        <h3 class="font-display font-bold text-lg text-slate-800 dark:text-white mb-2">Stempel / Cap Prodi</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Wajib format PNG transparan, maksimal 500KB.</p>
        
        <div class="mb-5 flex items-center justify-center rounded-2xl bg-slate-50 dark:bg-slate-900 shadow-inner" style="border:2px dashed <?=$capUrl?'#10b981':'#cbd5e1'?>; min-height: 140px; padding: 20px;">
          <?php if($capUrl):?>
            <img src="<?=$capUrl?>?v=<?=time()?>" class="max-h-24 object-contain" id="prev-cap-<?=$pr['id']?>" alt="Cap">
          <?php else:?>
            <div class="text-center text-slate-400" id="prev-cap-<?=$pr['id']?>">
              <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
              <p class="text-sm font-medium">Belum ada Stempel</p>
            </div>
          <?php endif;?>
        </div>

        <form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
          <input type="hidden" name="action" value="upload_cap">
          <input type="hidden" name="prodi_id" value="<?=$pr['id']?>">
          <input type="file" name="file_cap" accept=".png,image/png" class="<?=$inputCls?> flex-1" required onchange="previewImg(this,'prev-cap-<?=$pr['id']?>')">
          <div class="flex gap-2">
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">Upload</button>
            <?php if($capUrl):?>
            <button type="submit" name="action" value="hapus_cap" onclick="return confirm('Hapus stempel?')" class="px-4 py-2.5 bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 rounded-xl font-semibold text-sm transition-all">🗑</button>
            <?php endif;?>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

  <?php endforeach; ?>
  </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { height: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
</style>

<script>
function previewImg(input, previewId) {
    if (!input.files?.[0]) return;
    const file = input.files[0];
    if (file.size > 2*1024*1024) { showToast('Ukuran file terlalu besar! (Max 2MB)','error'); input.value=''; return; }
    const reader = new FileReader();
    reader.onload = e => {
        const prev = document.getElementById(previewId);
        if (!prev) return;
        if (prev.tagName === 'IMG') {
            prev.src = e.target.result;
        } else {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'max-h-24 w-full object-contain p-2';
            img.id = previewId;
            prev.replaceWith(img);
        }
    };
    reader.readAsDataURL(file);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
