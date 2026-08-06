<?php
$pageTitle='Pengaturan Sistem';
require_once __DIR__.'/../includes/functions.php';requireAdmin();
$allProdi=getAllProdi();
$cur=dbQueryOne("SELECT * FROM users WHERE id=?",[$_SESSION['user_id']]);
if($_SERVER['REQUEST_METHOD']==='POST'){
    $a=$_POST['action']??'';
    if($a==='profil'){$n=trim($_POST['nama']);$e=trim($_POST['email']);dbExecute("UPDATE users SET nama=?,email=? WHERE id=?",[$n,$e,$_SESSION['user_id']]);if(!empty($_POST['pw_baru'])){if(!password_verify($_POST['pw_lama'],$cur['password_hash'])){$_SESSION['flash']=['type'=>'error','message'=>'Password lama salah.'];header('Location: settings?tab=profil');exit;}dbExecute("UPDATE users SET password_hash=? WHERE id=?",[password_hash($_POST['pw_baru'],PASSWORD_BCRYPT),$_SESSION['user_id']]);} $_SESSION['nama']=$n;$_SESSION['flash']=['type'=>'success','message'=>'Profil berhasil diperbarui.'];header('Location: settings?tab=profil');exit;}
    if($a==='sistem'&&isSuperAdmin()){foreach(['nama_universitas','tahun_akademik','semester_aktif','format_nomor_surat','gemini_api_key','groq_api_key','google_client_id','google_client_secret'] as $k){if(isset($_POST[$k]))dbExecute("INSERT INTO settings(key_name,value)VALUES(?,?)ON DUPLICATE KEY UPDATE value=?",[$k,$_POST[$k],$_POST[$k]]);}$_SESSION['flash']=['type'=>'success','message'=>'Konfigurasi sistem disimpan.'];header('Location: settings?tab=sistem');exit;}
    if($a==='wa'&&isSuperAdmin()){foreach(['wa_api_key','wa_nomor_pengirim','wa_gateway'] as $k)dbExecute("INSERT INTO settings(key_name,value)VALUES(?,?)ON DUPLICATE KEY UPDATE value=?",[$k,$_POST[$k]??'',$_POST[$k]??'']);$_SESSION['flash']=['type'=>'success','message'=>'Konfigurasi WhatsApp disimpan.'];header('Location: settings?tab=wa');exit;}
    if($a==='drive'&&isSuperAdmin()){
        $fid=trim($_POST['google_drive_folder_id']??'');
        if($fid) dbExecute("INSERT INTO settings(key_name,value)VALUES(?,?)ON DUPLICATE KEY UPDATE value=?",['google_drive_folder_id',$fid,$fid]);
        // Handle JSON file upload
        if(!empty($_FILES['drive_json']['tmp_name'])){
            $jsonContent=file_get_contents($_FILES['drive_json']['tmp_name']);
            $parsed=json_decode($jsonContent,true);
            if($parsed && ($parsed['type']??'')!=='service_account'){
                $_SESSION['flash']=['type'=>'error','message'=>'File JSON bukan tipe service_account yang valid.'];
                header('Location: settings?tab=drive');exit;
            }
            dbExecute("INSERT INTO settings(key_name,value)VALUES(?,?)ON DUPLICATE KEY UPDATE value=?",['google_drive_service_account',$jsonContent,$jsonContent]);
        }
        $_SESSION['flash']=['type'=>'success','message'=>'Konfigurasi Google Drive disimpan.'];
        header('Location: settings?tab=drive');exit;
    }
}
$cfg=array_column(dbQuery("SELECT key_name,value FROM settings"),'value','key_name');
$tab=$_GET['tab']??'profil';
$users=isSuperAdmin()?dbQuery("SELECT u.*,p.nama as pnama FROM users u LEFT JOIN prodi p ON p.id=u.prodi_id WHERE u.role IN ('super_admin', 'admin_prodi') ORDER BY u.role,u.nama"):[];

$navTabs=[
    'profil'=>['label'=>'Profil Admin','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    'sistem'=>['label'=>'Konfigurasi Sistem','icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
    'wa'=>['label'=>'WhatsApp Gateway','icon'=>'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
    'drive'=>['label'=>'Google Drive','icon'=>'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z'],
];
if(isSuperAdmin()) $navTabs['users']=['label'=>'Manajemen Pengguna','icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'];

$inputCls="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all";

require_once __DIR__.'/../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
  <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Pengaturan</h1>
  <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kelola konfigurasi profil, sistem, dan integrasi</p>
</div>

<div class="flex flex-col lg:flex-row gap-6">
  
  <!-- Sidebar Navigation -->
  <div class="lg:w-64 shrink-0 space-y-2">
    <?php foreach($navTabs as $tid=>$tdata): ?>
    <a href="?tab=<?=$tid?>" class="flex items-center gap-3 px-4 py-3 rounded-2xl font-medium text-sm transition-all <?=$tab===$tid?'bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white shadow-md shadow-[#8c0c4c]/20':'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-100 dark:border-slate-700/60 hover:border-[#8c0c4c]/30 hover:text-[#8c0c4c] dark:hover:text-[#f06ea4]'?>">
      <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 <?=$tab===$tid?'bg-white/20':'bg-slate-100 dark:bg-slate-700'?>">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?=$tdata['icon']?>"/></svg>
      </div>
      <span><?=$tdata['label']?></span>
    </a>
    <?php endforeach; ?>
    
    <!-- Logout Button -->
    <a href="../logout" class="flex items-center gap-3 px-4 py-3 rounded-2xl font-medium text-sm transition-all bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/40 mt-4">
      <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 bg-red-100 dark:bg-red-900/30">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      </div>
      Keluar dari Sistem
    </a>
  </div>

  <!-- Content Area -->
  <div class="flex-1">
    
    <?php if($tab==='profil'): ?>
    <!-- Profile Tab -->
    <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8">
      <!-- Profile Avatar -->
      <div class="flex items-center gap-5 mb-8 pb-6 border-b border-slate-100 dark:border-slate-700">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-2xl font-display shadow-lg">
          <?=strtoupper(substr($cur['nama'],0,1))?>
        </div>
        <div>
          <div class="font-display font-bold text-xl text-slate-800 dark:text-white"><?=e($cur['nama'])?></div>
          <div class="text-sm text-slate-500 dark:text-slate-400 mt-0.5"><?=$cur['role']==='super_admin'?'Super Administrator':'Kaprodi'?></div>
        </div>
      </div>

      <form method="POST" class="space-y-6">
        <input type="hidden" name="action" value="profil">
        <div>
          <h3 class="font-display font-bold text-base text-slate-800 dark:text-white mb-4">Informasi Akun</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
              <input type="text" name="nama" value="<?=e($cur['nama'])?>" class="<?=$inputCls?>" required>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Alamat Email</label>
              <input type="email" name="email" value="<?=e($cur['email']??'')?>" class="<?=$inputCls?>">
            </div>
          </div>
        </div>
        
        <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
          <h3 class="font-display font-bold text-base text-slate-800 dark:text-white mb-1">Ubah Password</h3>
          <p class="text-xs text-slate-500 mb-4">Kosongkan jika tidak ingin mengubah password.</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Password Lama</label>
              <input type="password" name="pw_lama" class="<?=$inputCls?>" placeholder="Password saat ini">
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Password Baru</label>
              <input type="password" name="pw_baru" class="<?=$inputCls?>" placeholder="Password baru">
            </div>
          </div>
        </div>
        
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Simpan Profil
        </button>
      </form>
    </div>

    <?php elseif($tab==='sistem' && isSuperAdmin()): ?>
    <!-- Sistem Tab -->
    <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8">
      <div class="mb-6">
        <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white">Konfigurasi Sistem</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pengaturan utama dan identitas sistem</p>
      </div>
      <form method="POST" class="space-y-5">
        <input type="hidden" name="action" value="sistem">
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nama Universitas</label>
          <input type="text" name="nama_universitas" value="<?=e($cfg['nama_universitas']??'Universitas Nusa Putra')?>" class="<?=$inputCls?>">
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun Akademik</label>
            <input type="text" name="tahun_akademik" value="<?=e($cfg['tahun_akademik']??'2024/2025')?>" class="<?=$inputCls?>">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Semester Aktif</label>
            <select name="semester_aktif" class="<?=$inputCls?>">
              <option value="Ganjil" <?=($cfg['semester_aktif']??'')==='Ganjil'?'selected':''?>>Ganjil</option>
              <option value="Genap" <?=($cfg['semester_aktif']??'')==='Genap'?'selected':''?>>Genap</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Format Nomor Surat</label>
          <input type="text" name="format_nomor_surat" value="<?=e($cfg['format_nomor_surat']??'[No]/NPU/[kode]/[bulan]/[tahun]')?>" class="<?=$inputCls?> font-mono">
          <p class="text-xs text-slate-400 mt-1.5">Gunakan placeholder: [No], [kode], [bulan], [tahun]</p>
        </div>
        <div class="border-t border-slate-100 dark:border-slate-700 pt-5">
          <h3 class="font-bold text-sm text-slate-800 dark:text-white mb-4">Integrasi AI & Google</h3>
          <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">API Key Gemini (Google)</label>
                <input type="text" name="gemini_api_key" value="<?=e($cfg['gemini_api_key']??'')?>" class="<?=$inputCls?> font-mono" placeholder="AIzaSyxxxx...">
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">API Key Groq</label>
                <input type="text" name="groq_api_key" value="<?=e($cfg['groq_api_key']??'')?>" class="<?=$inputCls?> font-mono" placeholder="gsk_xxxx...">
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Google Client ID</label>
                <input type="text" name="google_client_id" value="<?=e($cfg['google_client_id']??'')?>" class="<?=$inputCls?> font-mono" placeholder="xxxx.apps.googleusercontent.com">
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Google Client Secret</label>
                <input type="text" name="google_client_secret" value="<?=e($cfg['google_client_secret']??'')?>" class="<?=$inputCls?> font-mono" placeholder="GOCSPX-xxxxx">
              </div>
            </div>
          </div>
        </div>
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Simpan Konfigurasi
        </button>
      </form>
    </div>

    <?php elseif($tab==='wa' && isSuperAdmin()): ?>
    <!-- WhatsApp Tab -->
    <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8">
      <div class="mb-6">
        <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white">WhatsApp Gateway</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Konfigurasi integrasi pengiriman notifikasi WhatsApp</p>
      </div>
      
      <div class="p-4 rounded-2xl mb-6 flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-900/40">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
          <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">Menggunakan Fonnte API</p>
          <p class="text-xs text-amber-600 dark:text-amber-500 mt-1">Daftarkan nomor WhatsApp di <a href="https://fonnte.com" target="_blank" class="underline font-semibold">fonnte.com</a> dan salin token API dari dashboard.</p>
        </div>
      </div>
      
      <form method="POST" class="space-y-5">
        <input type="hidden" name="action" value="wa">
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">API Token Fonnte</label>
          <input type="text" name="wa_api_key" value="<?=e($cfg['wa_api_key']??'')?>" class="<?=$inputCls?> font-mono" placeholder="Token dari Fonnte">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Nomor Pengirim</label>
          <input type="text" name="wa_nomor_pengirim" value="<?=e($cfg['wa_nomor_pengirim']??'')?>" class="<?=$inputCls?>" placeholder="628xxxxxxxxxx">
          <p class="text-xs text-slate-400 mt-1.5">Format nomor internasional tanpa tanda +</p>
        </div>
        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Simpan Konfigurasi WA
        </button>
      </form>
    </div>

    <?php elseif($tab==='users' && isSuperAdmin()): ?>
    <!-- Users Tab -->
    <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden">
      <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700">
        <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white">Manajemen Pengguna</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar seluruh admin yang memiliki akses sistem</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 dark:bg-slate-900/50">
            <tr>
              <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Admin</th>
              <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Username</th>
              <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Role & Prodi</th>
              <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Login Terakhir</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
            <?php foreach($users as $u): ?>
            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-gradient-to-br <?=$u['role']==='super_admin'?'from-purple-500 to-fuchsia-600':'from-blue-500 to-indigo-600'?> text-white font-bold text-sm flex items-center justify-center shrink-0">
                    <?=strtoupper(substr($u['nama'],0,1))?>
                  </div>
                  <div>
                    <div class="font-semibold text-slate-800 dark:text-white"><?=e($u['nama'])?></div>
                    <div class="text-xs text-slate-400"><?=e($u['email']??'')?></div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6"><code class="text-xs font-mono bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-1 rounded-lg"><?=e($u['username'])?></code></td>
              <td class="py-4 px-6">
                <div class="flex flex-col gap-1">
                  <span class="inline-flex text-xs px-2.5 py-1 rounded-lg font-bold w-fit <?=$u['role']==='super_admin'?'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400':'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'?>">
                    <?=$u['role']==='super_admin'?'Super Admin':'Kaprodi'?>
                  </span>
                  <span class="text-xs text-slate-400"><?=e($u['pnama']??'Semua Prodi')?></span>
                </div>
              </td>
              <td class="py-4 px-6 text-xs text-slate-500 dark:text-slate-400">
                <?=$u['last_login']?formatTanggal($u['last_login'],true):'Belum pernah login'?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php elseif($tab==='drive' && isSuperAdmin()): ?>
    <!-- Google Drive Tab -->
    <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:p-8 space-y-8">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <div class="w-10 h-10 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,#4285F4,#34A853)">
            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M4.433 22l4-6.928H22l-4 6.928H4.433zM2 17.072L6 10.144 8 13.608 4 20.536 2 17.072zM8.433 3l4 6.928H2l4-6.928H8.433z"/></svg>
          </div>
          <div>
            <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white">Integrasi Google Drive</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Simpan surat otomatis ke folder Google Drive Anda</p>
          </div>
        </div>
      </div>

      <!-- Status koneksi -->
      <?php
        $driveJson = $cfg['google_drive_service_account'] ?? '';
        $driveFolder = $cfg['google_drive_folder_id'] ?? '';
        $driveParsed = $driveJson ? json_decode($driveJson, true) : null;
        $driveEmail  = $driveParsed['client_email'] ?? null;
        $driveProject = $driveParsed['project_id'] ?? null;
      ?>
      <?php if ($driveEmail): ?>
      <div class="p-4 rounded-2xl flex items-start gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-900/40">
        <div class="text-2xl">✅</div>
        <div>
          <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400">Service Account Aktif</p>
          <p class="text-xs text-emerald-600 dark:text-emerald-500 font-mono mt-0.5"><?= e($driveEmail) ?></p>
          <?php if ($driveProject): ?>
          <p class="text-xs text-emerald-600 dark:text-emerald-500 mt-0.5">Project: <?= e($driveProject) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php else: ?>
      <div class="p-4 rounded-2xl flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-900/40">
        <div class="text-2xl">⚠️</div>
        <div>
          <p class="text-sm font-bold text-amber-700 dark:text-amber-400">Belum Dikonfigurasi</p>
          <p class="text-xs text-amber-600 dark:text-amber-500 mt-0.5">Upload Service Account JSON Key untuk mengaktifkan integrasi Drive.</p>
        </div>
      </div>
      <?php endif; ?>

      <!-- Form konfigurasi -->
      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="action" value="drive">

        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">📁 Folder ID Google Drive</label>
          <input type="text" name="google_drive_folder_id"
                 value="<?= e($driveFolder ?: '1hLAII1Ba-mZ6ORJA97IjuYqTpQqwns3b') ?>"
                 class="<?=$inputCls?> font-mono"
                 placeholder="1hLAII1Ba-mZ6ORJA97IjuYqTpQqwns3b">
          <p class="text-xs text-slate-400 mt-1.5">Salin dari URL folder Drive Anda: drive.google.com/drive/folders/<strong>ID_INI</strong></p>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">🔑 Service Account JSON Key</label>
          <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl p-6 text-center hover:border-[#8c0c4c] transition-colors group cursor-pointer" onclick="document.getElementById('drive-json-input').click()">
            <div class="text-3xl mb-2">📄</div>
            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300 group-hover:text-[#8c0c4c] transition-colors">Klik untuk upload file JSON</p>
            <p class="text-xs text-slate-400 mt-1">File berformat <code>.json</code> yang diunduh dari Google Cloud Console</p>
          </div>
          <input type="file" id="drive-json-input" name="drive_json" accept=".json" class="hidden" onchange="showJsonFilename(this)">
          <p id="json-filename" class="text-xs text-emerald-600 mt-2 hidden"></p>
          <?php if ($driveEmail): ?>
          <p class="text-xs text-slate-400 mt-2">💡 Biarkan kosong jika tidak ingin mengubah JSON Key yang sudah ada.</p>
          <?php endif; ?>
        </div>

        <div class="flex gap-3">
          <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-[#4285F4] to-[#34A853] text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan Konfigurasi Drive
          </button>
          <button type="button" onclick="testDriveConnection()" id="btn-test-drive" class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-semibold text-sm hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">
            🧪 Test Koneksi
          </button>
        </div>
        <div id="drive-test-result" class="hidden p-4 rounded-xl text-sm"></div>
      </form>

      <!-- Panduan Setup -->
      <div class="border border-slate-100 dark:border-slate-700 rounded-2xl p-5">
        <h3 class="font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2"><span>📋</span> Panduan Setup (Sekali Saja — ±10 menit)</h3>
        <ol class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
          <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-[#8c0c4c] text-white flex items-center justify-center text-xs font-bold flex-shrink-0">1</span>
            Buka <a href="https://console.cloud.google.com" target="_blank" class="text-blue-600 underline font-semibold">console.cloud.google.com</a> → Buat project baru</li>
          <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-[#8c0c4c] text-white flex items-center justify-center text-xs font-bold flex-shrink-0">2</span>
            APIs &amp; Services → Library → cari <strong>Google Drive API</strong> → Enable</li>
          <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-[#8c0c4c] text-white flex items-center justify-center text-xs font-bold flex-shrink-0">3</span>
            Credentials → Create Credentials → <strong>Service Account</strong> → isi nama → Done</li>
          <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-[#8c0c4c] text-white flex items-center justify-center text-xs font-bold flex-shrink-0">4</span>
            Klik service account → Tab <strong>Keys</strong> → Add Key → JSON → download file</li>
          <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-[#4285F4] text-white flex items-center justify-center text-xs font-bold flex-shrink-0">5</span>
            Buka folder Drive Anda → Klik kanan → <strong>Share</strong> → masukkan email service account (dari file JSON, field <code>client_email</code>) → Beri akses <strong>Editor</strong></li>
          <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-[#34A853] text-white flex items-center justify-center text-xs font-bold flex-shrink-0">6</span>
            Upload file JSON di form di atas → isi Folder ID → Simpan → klik <strong>Test Koneksi</strong></li>
        </ol>
      </div>
    </div>

    <script>
    function showJsonFilename(input) {
        var el = document.getElementById('json-filename');
        if (input.files[0]) {
            el.textContent = '✅ File dipilih: ' + input.files[0].name;
            el.classList.remove('hidden');
        }
    }
    function testDriveConnection() {
        var btn = document.getElementById('btn-test-drive');
        var res = document.getElementById('drive-test-result');
        btn.disabled = true;
        btn.textContent = '⏳ Menghubungi Drive...';
        res.className = 'hidden';
        fetch(window._sb.baseUrl + '/api/drive_test.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        })
        .then(r => r.json())
        .then(d => {
            res.classList.remove('hidden');
            if (d.ok) {
                res.className = 'p-4 rounded-xl text-sm bg-emerald-50 border border-emerald-200 text-emerald-700';
                res.innerHTML = '✅ <strong>Terhubung!</strong><br>Service Account: <code>' + d.email + '</code><br>File di folder: ' + d.files_in_folder + ' file';
            } else {
                res.className = 'p-4 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700';
                res.innerHTML = '❌ Gagal: ' + d.error;
            }
            btn.disabled = false;
            btn.textContent = '🧪 Test Koneksi';
        })
        .catch(e => {
            res.classList.remove('hidden');
            res.className = 'p-4 rounded-xl text-sm bg-red-50 border border-red-200 text-red-700';
            res.innerHTML = '❌ Error jaringan: ' + e.message;
            btn.disabled = false;
            btn.textContent = '🧪 Test Koneksi';
        });
    }
    </script>

    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
