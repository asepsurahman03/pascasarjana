<?php
require_once __DIR__ . '/../config/database.php';

function isLoggedIn(): bool { return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']); }
function requireLogin(): void { if (!isLoggedIn()) { header('Location: '. BASE_URL . '/login'); exit; } }
function isSuperAdmin(): bool { return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin'; }
function isAdminOrKaprodi(): bool { return isset($_SESSION['role']) && in_array($_SESSION['role'], ['super_admin', 'admin_prodi']); }
function requireAdmin(): void { 
    if (!isLoggedIn()) { header('Location: '. BASE_URL . '/login'); exit; }
    if (!isAdminOrKaprodi()) {
        if ($_SESSION['role'] === 'mahasiswa') { header('Location: '. BASE_URL . '/mhs/index'); exit; }
        if ($_SESSION['role'] === 'dosen') { header('Location: '. BASE_URL . '/dosen/index'); exit; }
        header('Location: '. BASE_URL . '/login'); exit;
    }
}
function getCurrentUser(): array {
    if (!isLoggedIn()) return [];
    return ['id'=>$_SESSION['user_id'],'nama'=>$_SESSION['nama']??'','username'=>$_SESSION['username']??'','role'=>$_SESSION['role']??'','prodi_id'=>$_SESSION['prodi_id']??null,'foto'=>$_SESSION['foto']??null];
}

// Fungsi Helper untuk Mahasiswa
function isMahasiswaLoggedIn(): bool { return isset($_SESSION['mhs_id']) && !empty($_SESSION['mhs_id']); }
function requireMahasiswaLogin(): void { if (!isMahasiswaLoggedIn()) { header('Location: '. BASE_URL . '/login'); exit; } }
function getCurrentMahasiswa(): array {
    if (!isMahasiswaLoggedIn()) return [];
    return ['id'=>$_SESSION['mhs_id'],'nama'=>$_SESSION['mhs_nama']??'','nim'=>$_SESSION['mhs_nim']??'','prodi_id'=>$_SESSION['mhs_prodi_id']??null];
}
function dbQuery(string $sql, array $p=[]): array { $s=getDB()->prepare($sql);$s->execute($p);return $s->fetchAll(); }
function dbQueryOne(string $sql, array $p=[]): ?array { $s=getDB()->prepare($sql);$s->execute($p);$r=$s->fetch();return $r?:null; }
function dbExecute(string $sql, array $p=[]): int { $s=getDB()->prepare($sql);$s->execute($p);return (int)getDB()->lastInsertId(); }
function dbCount(string $tbl, string $where='', array $p=[]): int { $sql="SELECT COUNT(*) as cnt FROM $tbl".($where?" WHERE $where":'');$r=dbQueryOne($sql,$p);return (int)($r['cnt']??0); }
function bulanRomawi(int $b): string { return ['','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$b]??'I'; }

/** Format nomor surat NPU: 006/MP/NPU/V/2026 */
function generateNomorSurat(int $prodiId, ?string $tanggal=null): string {
    $prodi = dbQueryOne("SELECT kode, prefix_surat FROM prodi WHERE id=?", [$prodiId]);
    if (!$prodi) return '';
    $ts = $tanggal ? strtotime($tanggal) : time();
    $b  = (int)date('n', $ts);
    $y  = date('Y', $ts);
    $kode = $prodi['prefix_surat'] ?: $prodi['kode'];
    $count = dbQueryOne("SELECT COUNT(*) as c FROM surat WHERE prodi_id=? AND YEAR(tanggal)=?", [$prodiId, $y])['c'] ?? 0;
    
    $num = (int)$count + 1;
    while (true) {
        $nomor = str_pad($num, 3, '0', STR_PAD_LEFT) . '/' . $kode . '/NPU/' . bulanRomawi($b) . '/' . $y;
        $cek = dbQueryOne("SELECT id FROM surat WHERE nomor_surat=? AND prodi_id=?", [$nomor, $prodiId]);
        if (!$cek) return $nomor;
        $num++;
    }
}

/** Nama hari Indonesia */
function getNamaHari(string $tanggal): string {
    $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    return $hari[(int)date('w', strtotime($tanggal))];
}

function tgl_indo(string $tanggal): string {
    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Format tanggal surat lengkap: "Sukabumi, Kamis 07 Mei 2026" */
function formatTanggalSurat(string $tanggal, string $kota='Sukabumi'): string {
    $bl  = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts  = strtotime($tanggal);
    $hari = getNamaHari($tanggal);
    return $kota . ', ' . $hari . ' ' . date('d', $ts) . ' ' . $bl[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Ganti variabel {{X}} di template isi surat */
function gantiVariabel(string $html, array $data): string {
    foreach ($data as $k => $v) {
        $html = str_replace('{{' . $k . '}}', $v, $html);
    }
    return $html;
}

/** Path gambar kop atas per prodi */
function getKopPath(string $kode, bool $url=false): ?string {
    $base = $url ? BASE_URL : BASE_PATH;
    $f    = BASE_PATH . '/uploads/kop/header_' . $kode . '.png';
    if (file_exists($f)) return $base . '/uploads/kop/header_' . $kode . '.png';
    $f2 = BASE_PATH . '/uploads/kop/header_' . $kode . '.jpg';
    return file_exists($f2) ? $base . '/uploads/kop/header_' . $kode . '.jpg' : null;
}

/** Path gambar footer per prodi */
function getFooterPath(string $kode, bool $url=false): ?string {
    $base = $url ? BASE_URL : BASE_PATH;
    foreach (['footer_'.$kode.'.jpg','footer_'.$kode.'.png','footer_default.jpg','footer_default.png'] as $fn) {
        $f = BASE_PATH . '/uploads/kop/' . $fn;
        if (file_exists($f)) return $base . '/uploads/kop/' . $fn;
    }
    return null;
}

/** Path TTD per prodi */
function getTtdUrl(string $kode): ?string {
    foreach (['ttd_'.$kode.'.png',$kode.'_ttd.png'] as $fn) {
        if (file_exists(BASE_PATH.'/uploads/ttd/'.$fn)) return BASE_URL.'/uploads/ttd/'.$fn;
    }
    return null;
}

/** Path Cap per prodi */
function getCapUrl(string $kode): ?string {
    foreach (['cap_'.$kode.'.png',$kode.'_cap.png'] as $fn) {
        if (file_exists(BASE_PATH.'/uploads/cap/'.$fn)) return BASE_URL.'/uploads/cap/'.$fn;
    }
    return null;
}
function statusBadge(string $s): string {
    $m=['Draf'=>'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700',
        'Proses'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
        'Selesai'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800',
        'Terarsip'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800'];
    $c=$m[$s]??'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700';
    return "<span class='px-2.5 py-0.5 rounded-full text-xs font-bold inline-flex items-center gap-1.5 $c'><span class='w-1.5 h-1.5 rounded-full bg-current opacity-70'></span> $s</span>";
}
function statusMhsBadge(string $s): string {
    $m=['Aktif'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800',
        'Cuti'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
        'Lulus'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
        'DO'=>'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800'];
    $c=$m[$s]??'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700';
    return "<span class='px-2.5 py-0.5 rounded-full text-xs font-bold inline-flex items-center gap-1.5 $c'><span class='w-1.5 h-1.5 rounded-full bg-current opacity-70'></span> $s</span>";
}
function prioritasBadge(string $s): string {
    $m=['Tinggi'=>'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800',
        'Sedang'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
        'Rendah'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800'];
    $c=$m[$s]??'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700';
    return "<span class='px-2.5 py-0.5 rounded-full text-xs font-bold inline-flex items-center gap-1.5 $c'><span class='w-1.5 h-1.5 rounded-full bg-current opacity-70'></span> $s</span>";
}
function formatTanggal(string $d, bool $t=false): string {
    if(empty($d)||$d==='0000-00-00') return '-';
    $bl=['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts=strtotime($d);
    $r=date('j',$ts).' '.$bl[(int)date('n',$ts)].' '.date('Y',$ts);
    if($t) $r.=' '.date('H:i',$ts);
    return $r;
}
function e(mixed $v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
function csrfToken(): string { if(empty($_SESSION['csrf_token'])){$_SESSION['csrf_token']=bin2hex(random_bytes(32));} return $_SESSION['csrf_token']; }
function logActivity(string $aksi,string $modul='',string $detail=''): void {
    if(!isLoggedIn()) return;
    dbExecute("INSERT INTO activity_log(user_id,aksi,modul,detail,ip_address)VALUES(?,?,?,?,?)",[$_SESSION['user_id'],$aksi,$modul,$detail,$_SERVER['REMOTE_ADDR']??'']);
}
function addNotifikasi(int $uid,string $pesan,string $jenis='sistem',string $link=''): void {
    dbExecute("INSERT INTO notifikasi(user_id,pesan,jenis,link)VALUES(?,?,?,?)",[$uid,$pesan,$jenis,$link]);
}
function countUnread(): int { if(!isLoggedIn()) return 0; return dbCount('notifikasi','user_id=? AND is_read=0',[$_SESSION['user_id']]); }
function getAllProdi(): array { return dbQuery("SELECT * FROM prodi ORDER BY jenjang,nama"); }
function getSetting(string $k): string { $r=dbQueryOne("SELECT value FROM settings WHERE key_name=?",[$k]); return $r['value']??''; }
function isDeadlineDekat(string $d): bool { if(empty($d)) return false; $diff=(strtotime($d)-time())/86400; return $diff>=0&&$diff<2; }
function paginate(int $total,int $pp=15,int $cur=1): array {
    $tp=max(1,ceil($total/$pp));$cur=max(1,min($cur,$tp));
    return ['total'=>$total,'per_page'=>$pp,'current'=>$cur,'total_pages'=>$tp,'offset'=>($cur-1)*$pp];
}
function renderPagination(array $pg,string $url): string {
    if($pg['total_pages']<=1) return '';
    $h='<div class="flex items-center gap-1 mt-4">';
    for($i=1;$i<=$pg['total_pages'];$i++){
        $a=$i===$pg['current']?'bg-blue-600 text-white':'bg-card text-theme-muted hover:bg-theme-border';
        $h.="<a href='{$url}&page={$i}' class='px-3 py-1 rounded text-sm $a transition'>{$i}</a>";
    }
    return $h.'</div>';
}
function getFlash(): ?array { if(isset($_SESSION['flash'])){$f=$_SESSION['flash'];unset($_SESSION['flash']);return $f;} return null; }
function svgIcon(string $name): string {
    $icons=['dashboard'=>'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
    'users'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    'document'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    'check'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
    'calendar'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    'chat'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    'chart'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    'office'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    'cog'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
    'bell'=>'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
    'logout'=>'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
    'search'=>'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0',
    'menu'=>'M4 6h16M4 12h16M4 18h16','x'=>'M6 18L18 6M6 6l12 12'];
    $p=$icons[$name]??'';
    return "<svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='$p'/></svg>";
}
