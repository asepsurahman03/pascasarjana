<?php
$pageTitle = 'Manajemen Sidang & Seminar';
require_once __DIR__.'/../includes/header.php';

// Stats from DB or static (adjust based on actual DB)
$pendaftaran = dbQuery("
    SELECT ps.*,m.nama as mhs_nama,m.nim,p.nama as pnama,p.warna_hex
    FROM pendaftaran_sidang ps
    LEFT JOIN mahasiswa m ON m.id=ps.mahasiswa_id
    LEFT JOIN prodi p ON p.id=m.prodi_id
    ORDER BY ps.created_at DESC
    LIMIT 50
") ?? [];

$statMenunggu = dbCount('pendaftaran_sidang',"status='Menunggu Review'");
$statPolling = dbCount('pendaftaran_sidang',"status='Polling Jadwal'");
$statDitetapkan = dbCount('pendaftaran_sidang',"status='Jadwal Ditetapkan'");
$statSelesai = dbCount('pendaftaran_sidang',"status='Selesai' AND MONTH(updated_at)=MONTH(CURDATE())");

$statusStyles = [
    'Menunggu Review'   => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300',
    'Verifikasi Berkas' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
    'Polling Jadwal'    => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'Jadwal Ditetapkan' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
    'Selesai'           => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
];

// Filter
$fs = $_GET['status'] ?? '';
$q  = trim($_GET['q'] ?? '');
if ($fs || $q) {
    $filtered = array_filter($pendaftaran, function($p) use ($fs, $q) {
        $matchStatus = !$fs || $p['status'] === $fs;
        $matchSearch = !$q || stripos($p['mhs_nama']??'', $q)!==false || stripos($p['nim']??'',$q)!==false;
        return $matchStatus && $matchSearch;
    });
    $pendaftaran = array_values($filtered);
}
?>

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Sidang & Seminar</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Monitor dan kelola pendaftaran sidang mahasiswa</p>
  </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  <?php
  $statCards = [
      ['label'=>'Menunggu Review','val'=>$statMenunggu,'gradient'=>'from-slate-500 to-slate-700','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
      ['label'=>'Polling Jadwal','val'=>$statPolling,'gradient'=>'from-blue-500 to-indigo-700','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
      ['label'=>'Jadwal Ditetapkan','val'=>$statDitetapkan,'gradient'=>'from-emerald-500 to-teal-700','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
      ['label'=>'Selesai Bulan Ini','val'=>$statSelesai,'gradient'=>'from-[#8c0c4c] to-[#a3155b]','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
  ];
  foreach($statCards as $s): ?>
  <div class="group bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all hover:-translate-y-0.5">
    <div class="flex items-center gap-4">
      <div class="w-11 h-11 rounded-2xl bg-gradient-to-br <?=$s['gradient']?> flex items-center justify-center shrink-0 shadow-sm">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?=$s['icon']?>"/></svg>
      </div>
      <div>
        <div class="text-2xl font-bold font-display text-slate-800 dark:text-white"><?=$s['val']?></div>
        <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5"><?=$s['label']?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filter Bar -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-2xl shadow-sm p-5 mb-6">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Cari Mahasiswa</label>
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div>
        <input type="text" name="q" value="<?=e($q)?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" placeholder="Nama atau NIM...">
      </div>
    </div>
    <div class="min-w-[180px]">
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
      <select name="status" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        <option value="">Semua Status</option>
        <?php foreach(['Menunggu Review','Verifikasi Berkas','Polling Jadwal','Jadwal Ditetapkan','Selesai'] as $st):?>
        <option value="<?=$st?>" <?=$fs===$st?'selected':''?>><?=$st?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold text-sm shadow-md">Filter</button>
      <a href="sidang" class="inline-flex items-center px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-xl font-semibold text-sm">Reset</a>
    </div>
  </form>
</div>

<!-- Table -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden">
  <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700">
    <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white">Daftar Pendaftaran Sidang / Seminar</h2>
    <p class="text-xs text-slate-500 mt-0.5">Menampilkan <strong class="text-slate-700 dark:text-slate-300"><?=count($pendaftaran)?></strong> pendaftaran</p>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/50">
        <tr>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Mahasiswa</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Jenis Kegiatan</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Kelengkapan Berkas</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Didaftarkan</th>
          <th class="text-right py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60" x-data="{modalOpen:false,selectedItem:null}">
        <?php if(empty($pendaftaran)): ?>
        <tr><td colspan="6" class="py-20">
          <div class="flex flex-col items-center justify-center text-slate-400">
            <div class="w-20 h-20 bg-slate-50 dark:bg-slate-700/30 rounded-full flex items-center justify-center mb-4">
              <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="font-semibold">Belum ada pendaftaran sidang</p>
            <p class="text-sm mt-1">Mahasiswa belum mendaftarkan diri</p>
          </div>
        </td></tr>
        <?php else: foreach($pendaftaran as $p):
          $pct = isset($p['berkas_ok'],$p['berkas_total']) && $p['berkas_total']>0 ? round(($p['berkas_ok']/$p['berkas_total'])*100) : 0;
          $bOk = $p['berkas_ok'] ?? 0; $bTot = $p['berkas_total'] ?? 0;
          $statusCls = $statusStyles[$p['status']??'Menunggu Review'] ?? 'bg-slate-100 text-slate-500';
        ?>
        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group <?=($p['urgent']??false)?'border-l-[3px] border-l-red-400':''?>">
          <td class="py-4 px-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#8c0c4c] to-[#c2527a] text-white font-bold text-sm flex items-center justify-center shrink-0">
                <?=strtoupper(substr($p['mhs_nama']??'?',0,1))?>
              </div>
              <div>
                <div class="font-semibold text-slate-800 dark:text-white group-hover:text-[#8c0c4c] transition-colors"><?=e($p['mhs_nama']??'-')?></div>
                <div class="text-xs text-slate-400 flex items-center gap-1.5 mt-0.5">
                  <span class="font-mono"><?=e($p['nim']??'')?></span>
                  <?php if(!empty($p['pnama'])): ?><span>·</span><span style="color:<?=e($p['warna_hex']??'#888')?>"><?=e($p['pnama'])?></span><?php endif;?>
                </div>
              </div>
            </div>
          </td>
          <td class="py-4 px-6">
            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-200">
              <?=e($p['jenis']??$p['jenis_sidang']??'-')?>
            </span>
          </td>
          <td class="py-4 px-6">
            <?php if($bTot>0): ?>
            <div class="flex items-center gap-2">
              <div class="w-24 bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                <div class="h-2 rounded-full transition-all <?=$pct>=100?'bg-emerald-500':'bg-amber-500'?>" style="width:<?=$pct?>%"></div>
              </div>
              <span class="text-xs font-bold <?=$pct>=100?'text-emerald-600 dark:text-emerald-400':'text-amber-600 dark:text-amber-400'?>"><?=$bOk?>/<?=$bTot?></span>
            </div>
            <?php else: ?><span class="text-xs text-slate-400">-</span><?php endif;?>
          </td>
          <td class="py-4 px-6">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold <?=$statusCls?>">
              <?=e($p['status']??'-')?>
            </span>
            <?php if($p['urgent']??false):?><span class="ml-1 text-xs">⚠️</span><?php endif;?>
          </td>
          <td class="py-4 px-6 text-xs text-slate-500 dark:text-slate-400">
            <?=isset($p['created_at'])?formatTanggal($p['created_at']):($p['tgl_daftar']??'-')?>
          </td>
          <td class="py-4 px-6">
            <div class="flex justify-end items-center gap-1.5">
              <button @click="selectedItem=<?=htmlspecialchars(json_encode($p),ENT_QUOTES)?>;modalOpen=true" class="px-3 py-1.5 text-xs font-bold bg-[#8c0c4c]/10 text-[#8c0c4c] dark:text-[#f06ea4] hover:bg-[#8c0c4c]/20 rounded-lg transition-colors">Detail</button>
              <?php if(($p['status']??'') === 'Verifikasi Berkas'): ?>
              <button class="px-3 py-1.5 text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-200 rounded-lg transition">✓ Setujui</button>
              <?php elseif(($p['status']??'') === 'Polling Jadwal'): ?>
              <a href="../dosen/vote_jadwal" target="_blank" class="px-3 py-1.5 text-xs font-bold bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 hover:bg-blue-200 rounded-lg transition">Lihat Vote</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
