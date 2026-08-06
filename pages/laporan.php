<?php
$pageTitle='Laporan & Analitik';$breadcrumb=[['label'=>'Laporan']];
require_once __DIR__.'/../includes/functions.php';requireAdmin();
$allProdi=getAllProdi();
$fy=(int)($_GET['tahun']??date('Y'));$fb=(int)($_GET['bulan']??date('n'));

// Stats Surat per Prodi
$suratProdi=dbQuery("SELECT p.nama,p.warna_hex,COUNT(s.id) as total,SUM(s.status='Selesai') as selesai,SUM(s.status='Proses') as proses,SUM(s.status='Draf') as draf FROM prodi p LEFT JOIN surat s ON s.prodi_id=p.id AND YEAR(s.tanggal)=? AND MONTH(s.tanggal)=? GROUP BY p.id ORDER BY total DESC",[$fy,$fb]);

// Stats Mahasiswa per Prodi
$mhsProdi=dbQuery("SELECT p.nama,p.warna_hex,COUNT(m.id) as total,SUM(m.status='Aktif') as aktif,SUM(m.status='Cuti') as cuti,SUM(m.status='Lulus') as lulus,SUM(m.status='DO') as do_mhs FROM prodi p LEFT JOIN mahasiswa m ON m.prodi_id=p.id GROUP BY p.id ORDER BY total DESC");

// Log Aktivitas Terbaru
$logs=dbQuery("SELECT a.*,u.nama as unama FROM activity_log a JOIN users u ON u.id=a.user_id WHERE YEAR(a.created_at)=? AND MONTH(a.created_at)=? ORDER BY a.created_at DESC LIMIT 30",[$fy,$fb]);

// Trend Pembuatan Surat setahun terakhir
$suratTrend=dbQuery("SELECT DATE_FORMAT(tanggal,'%Y-%m') as bln,COUNT(*) as total FROM surat WHERE YEAR(tanggal)=? GROUP BY DATE_FORMAT(tanggal,'%Y-%m') ORDER BY bln ASC",[$fy]);
$namaBulan=['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

require_once __DIR__.'/../includes/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Laporan & Analitik</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Ringkasan statistik surat, mahasiswa, dan aktivitas sistem</p>
  </div>
  <div class="flex gap-2">
    <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold transition-all text-sm shadow-sm border bg-white text-slate-600 border-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> Cetak
    </button>
  </div>
</div>

<!-- FILTER -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-5 mb-8 no-print flex flex-col md:flex-row justify-between items-center gap-4">
  <div class="flex items-center gap-3">
    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center text-lg">📅</div>
    <div>
      <h3 class="font-bold text-slate-800 dark:text-white">Filter Periode</h3>
      <p class="text-xs text-slate-500">Pilih tahun dan bulan laporan</p>
    </div>
  </div>
  
  <form method="GET" class="flex flex-wrap gap-3 items-center" id="filter-form">
    <select name="bulan" onchange="document.getElementById('filter-form').submit()" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold focus:outline-none focus:border-[#8c0c4c] transition-colors min-w-[140px]">
      <?php for($b=1;$b<=12;$b++):?>
      <option value="<?=$b?>" <?=$fb===$b?'selected':''?>><?=$namaBulan[$b]?></option>
      <?php endfor;?>
    </select>
    <select name="tahun" onchange="document.getElementById('filter-form').submit()" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold focus:outline-none focus:border-[#8c0c4c] transition-colors">
      <?php for($y=date('Y');$y>=2020;$y--):?>
      <option value="<?=$y?>" <?=$fy===$y?'selected':''?>><?=$y?></option>
      <?php endfor;?>
    </select>
  </form>
</div>

<!-- SECTION 1: SURAT -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:col-span-1 relative overflow-hidden">
    <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white mb-6">📈 Trend Surat (<?=$fy?>)</h2>
    <div class="relative w-full h-[250px]"><canvas id="chartTrend"></canvas></div>
  </div>
  
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden lg:col-span-2 flex flex-col">
    <div class="p-6 border-b border-slate-100 dark:border-slate-700/60 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center text-lg">📄</div>
      <div>
        <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white leading-none mb-1">Surat per Prodi</h2>
        <p class="text-xs font-semibold text-slate-500">Periode <?=$namaBulan[$fb]?> <?=$fy?></p>
      </div>
    </div>
    <div class="overflow-x-auto flex-1">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/50">
          <tr>
            <th class="text-left py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Program Studi</th>
            <th class="text-center py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Selesai</th>
            <th class="text-center py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Proses</th>
            <th class="text-center py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Draf</th>
            <th class="text-center py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
          <?php if(empty($suratProdi)):?>
          <tr><td colspan="5" class="py-8 text-center text-slate-400">Tidak ada data</td></tr>
          <?php else: foreach($suratProdi as $s):?>
          <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
            <td class="py-4 px-6">
              <div class="flex items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full" style="background:<?=e($s['warna_hex']??'#8c0c4c')?>"></span>
                <span class="font-bold text-slate-800 dark:text-slate-200"><?=e($s['nama'])?></span>
              </div>
            </td>
            <td class="py-4 px-4 text-center">
              <?php if($s['selesai']>0):?><span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 font-bold text-xs"><?=$s['selesai']?></span><?php else:?><span class="text-slate-300">-</span><?php endif;?>
            </td>
            <td class="py-4 px-4 text-center">
              <?php if($s['proses']>0):?><span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 font-bold text-xs"><?=$s['proses']?></span><?php else:?><span class="text-slate-300">-</span><?php endif;?>
            </td>
            <td class="py-4 px-4 text-center">
              <?php if($s['draf']>0):?><span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 font-bold text-xs"><?=$s['draf']?></span><?php else:?><span class="text-slate-300">-</span><?php endif;?>
            </td>
            <td class="py-4 px-6 text-center font-bold text-slate-800 dark:text-white text-base">
              <?=$s['total']?>
            </td>
          </tr>
          <?php endforeach; endif;?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- SECTION 2: MAHASISWA & AKTIVITAS -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden flex flex-col">
    <div class="p-6 border-b border-slate-100 dark:border-slate-700/60 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 flex items-center justify-center text-lg">🎓</div>
      <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white leading-none">Status Mahasiswa</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-900/50">
          <tr>
            <th class="text-left py-4 px-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Prodi</th>
            <th class="text-center py-4 px-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Aktif</th>
            <th class="text-center py-4 px-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Cuti</th>
            <th class="text-center py-4 px-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Lulus</th>
            <th class="text-center py-4 px-3 text-xs font-bold text-slate-500 uppercase tracking-wider">DO</th>
            <th class="text-right py-4 px-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
          <?php foreach($mhsProdi as $m): $pct=$m['total']>0?round($m['aktif']/$m['total']*100):0; ?>
          <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group">
            <td class="py-4 px-5">
              <div class="font-bold text-slate-800 dark:text-slate-200 mb-1"><?=e($m['nama'])?></div>
              <div class="flex items-center gap-2">
                <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-full h-1.5"><div class="h-1.5 rounded-full bg-indigo-500" style="width:<?=$pct?>%"></div></div>
                <span class="text-[10px] font-bold text-slate-400"><?=$pct?>% Aktif</span>
              </div>
            </td>
            <td class="py-4 px-3 text-center text-indigo-600 dark:text-indigo-400 font-semibold"><?=$m['aktif']?></td>
            <td class="py-4 px-3 text-center text-amber-500 font-semibold"><?=$m['cuti']?></td>
            <td class="py-4 px-3 text-center text-emerald-500 font-semibold"><?=$m['lulus']?></td>
            <td class="py-4 px-3 text-center text-red-500 font-semibold"><?=$m['do_mhs']?></td>
            <td class="py-4 px-5 text-right font-bold text-slate-800 dark:text-white text-base"><?=$m['total']?></td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden flex flex-col">
    <div class="p-6 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-500 flex items-center justify-center text-lg">⚡</div>
        <div>
          <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white leading-none mb-1">Aktivitas Terbaru</h2>
          <p class="text-xs font-semibold text-slate-500">Log sistem <?=$namaBulan[$fb]?> <?=$fy?></p>
        </div>
      </div>
    </div>
    
    <div class="overflow-y-auto max-h-[400px] p-2 custom-scrollbar">
      <?php if(empty($logs)):?>
        <div class="py-12 text-center text-slate-400">Tidak ada aktivitas pada bulan ini.</div>
      <?php else: ?>
        <ul class="space-y-1 relative before:absolute before:inset-0 before:ml-8 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 dark:before:via-slate-700 before:to-transparent">
          <?php foreach($logs as $idx => $l): 
            $icons = ['login'=>'🔑', 'surat'=>'📄', 'dosen'=>'👨‍🏫', 'mahasiswa'=>'🎓', 'pengaturan'=>'⚙️', 'whatsapp'=>'💬'];
            $ic = $icons[strtolower($l['modul'])] ?? '⚡';
            $bg = ['login'=>'bg-blue-500', 'surat'=>'bg-emerald-500', 'dosen'=>'bg-indigo-500', 'mahasiswa'=>'bg-amber-500', 'pengaturan'=>'bg-slate-500', 'whatsapp'=>'bg-green-500'][strtolower($l['modul'])] ?? 'bg-rose-500';
          ?>
          <li class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active px-4 py-3">
            <!-- Icon -->
            <div class="flex items-center justify-center w-8 h-8 rounded-full border-2 border-white dark:border-slate-800 <?=$bg?> text-white shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 relative z-10 text-xs">
              <?=$ic?>
            </div>
            <!-- Card -->
            <div class="w-[calc(100%-3rem)] md:w-[calc(50%-2rem)] p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700/60 shadow-sm hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between mb-1">
                <span class="font-bold text-slate-800 dark:text-slate-200 text-sm"><?=e($l['unama'])?></span>
                <span class="text-[10px] font-bold text-slate-400 bg-white dark:bg-slate-800 px-2 py-0.5 rounded-full border border-slate-200 dark:border-slate-700"><?=date('d M H:i', strtotime($l['created_at']))?></span>
              </div>
              <div class="text-xs text-slate-600 dark:text-slate-400 font-medium mb-1"><span class="text-[#8c0c4c] dark:text-[#f06ea4] font-bold uppercase tracking-wider"><?=e($l['modul'])?></span> — <?=e($l['aksi'])?></div>
              <div class="text-xs text-slate-500 truncate" title="<?=e($l['detail'])?>"><?=e($l['detail'])?></div>
            </div>
          </li>
          <?php endforeach;?>
        </ul>
      <?php endif;?>
    </div>
  </div>
</div>

<style>
@media print {
    body { background: #fff !important; color: #000 !important; }
    .no-print { display: none !important; }
    .bg-white, .dark .bg-slate-800 { background: #fff !important; box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
    .text-slate-800, .dark .text-white { color: #000 !important; }
    canvas { display: none !important; } /* Sembunyikan chart di print, gunakan data tabel */
    table { width: 100% !important; border-collapse: collapse; }
    th, td { border: 1px solid #ccc !important; }
    @page { margin: 15mm; }
}
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php
$tl=json_encode(array_column($suratTrend,'bln'));
$td=json_encode(array_column($suratTrend,'total'));
$pageScript="
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartTrend');
    if(ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: $tl,
                datasets: [{
                    label: 'Surat Terbit',
                    data: $td,
                    borderColor: '#8c0c4c',
                    backgroundColor: 'rgba(140, 12, 76, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#8c0c4c',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: { size: 13, family: 'Inter' },
                        bodyFont: { size: 14, family: 'Inter', weight: 'bold' },
                        displayColors: false,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                        ticks: { color: '#64748b', font: { family: 'Inter' } },
                        border: { display: false }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { family: 'Inter' } },
                        border: { display: false }
                    }
                }
            }
        });
    }
});
";
require_once __DIR__.'/../includes/footer.php';
?>
