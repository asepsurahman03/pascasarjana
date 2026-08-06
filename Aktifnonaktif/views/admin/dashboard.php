<?php
requireAdminOrKaprodi();
$data['title'] = 'Dashboard Admin - ' . APP_NAME;
include BASE_PATH . '/includes/admin_layout_top.php';

$stats          = $data['stats'];
$mahasiswaCount = $data['mahasiswaCount'];
$monthlyData    = $data['monthlyData'];
$prodiData      = $data['prodiData'];
$recentLogs     = $data['recentLogs'];
$recentPd       = $data['recentPd'];
?>

<!-- Page Title -->
<div class="mb-6">
  <h1 class="font-display font-bold text-2xl text-slate-800 dark:text-white">Dashboard</h1>
  <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Ringkasan sistem per <?= formatTanggal(date('Y-m-d'), true) ?></p>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
  <?php
  $statCards = [
    ['label'=>'Total Mahasiswa',   'value'=>$mahasiswaCount,       'color'=>'from-blue-500 to-blue-700',    'icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
    ['label'=>'Total Pengajuan',   'value'=>$stats['total'],       'color'=>'from-purple-500 to-purple-700','icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ['label'=>'Hari Ini',          'value'=>$stats['today'],       'color'=>'from-orange-400 to-orange-600','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ['label'=>'Disetujui',         'value'=>$stats['approved'],    'color'=>'from-green-500 to-green-700',  'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['label'=>'Ditolak',           'value'=>$stats['rejected'],    'color'=>'from-nusa to-nusa-dark',       'icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
  ];
  foreach ($statCards as $card):
  ?>
  <div class="stat-card bg-gradient-to-br <?= $card['color'] ?> shadow-lg">
    <div class="mb-3 opacity-80">
      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $card['icon'] ?>"/>
      </svg>
    </div>
    <p class="font-display font-bold text-3xl text-white"><?= number_format($card['value']) ?></p>
    <p class="text-white/80 text-xs mt-1 font-medium"><?= $card['label'] ?></p>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts + Recent Activity -->
<div class="grid lg:grid-cols-3 gap-6 mb-8">

  <!-- Monthly Bar Chart -->
  <div class="card p-5 lg:col-span-2">
    <div class="flex items-center justify-between mb-5">
      <h2 class="font-display font-bold text-slate-800 dark:text-white text-base">Pengajuan per Bulan</h2>
      <span class="text-xs text-slate-400">6 bulan terakhir</span>
    </div>
    <div class="relative h-60">
      <canvas id="monthlyChart"></canvas>
    </div>
  </div>

  <!-- Prodi Pie Chart -->
  <div class="card p-5">
    <h2 class="font-display font-bold text-slate-800 dark:text-white text-base mb-5">Berdasarkan Prodi</h2>
    <div class="relative h-48">
      <canvas id="prodiChart"></canvas>
    </div>
    <div class="mt-4 space-y-2">
      <?php
      $prodiColors = ['#C1121F','#3B82F6','#10B981','#F59E0B'];
      foreach ($prodiData as $i => $pd):
      ?>
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 rounded-full" style="background: <?= $prodiColors[$i % count($prodiColors)] ?>"></div>
          <span class="text-slate-600 dark:text-slate-400 truncate max-w-[140px]"><?= e($pd['program_studi']) ?></span>
        </div>
        <span class="font-bold text-slate-800 dark:text-slate-200"><?= $pd['total'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Recent Submissions + Activity Log -->
<div class="grid lg:grid-cols-2 gap-6">

  <!-- Recent Submissions -->
  <div class="card p-5">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-display font-bold text-slate-800 dark:text-white text-base">Pengajuan Terbaru</h2>
      <a href="<?= APP_URL ?>/?page=admin/pengajuan" class="text-nusa text-xs font-medium hover:underline">Lihat semua →</a>
    </div>

    <?php if (empty($recentPd)): ?>
    <div class="flex flex-col items-center py-8 text-center">
      <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      <p class="text-slate-400 text-sm">Belum ada pengajuan</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100 dark:border-slate-700">
            <th class="text-left py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Nama</th>
            <th class="text-left py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Program Studi</th>
            <th class="text-center py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
          <?php foreach ($recentPd as $p): ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
            <td class="py-2.5">
              <p class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[140px]"><?= e($p['nama_pemohon']) ?></p>
              <p class="text-slate-400 text-xs"><?= e($p['nim']) ?></p>
            </td>
            <td class="py-2.5 text-slate-500 dark:text-slate-400 text-xs max-w-[120px] truncate"><?= e($p['program_studi']) ?></td>
            <td class="py-2.5 text-center">
              <span class="badge <?= statusBadge($p['status']) ?>"><?= statusLabel($p['status']) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Activity Log -->
  <div class="card p-5">
    <h2 class="font-display font-bold text-slate-800 dark:text-white text-base mb-4">Aktivitas Terbaru</h2>
    <?php if (empty($recentLogs)): ?>
    <p class="text-slate-400 text-sm text-center py-8">Belum ada aktivitas</p>
    <?php else: ?>
    <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
      <?php foreach ($recentLogs as $log): ?>
      <div class="flex gap-3">
        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0 text-xs font-bold text-slate-500 dark:text-slate-400">
          <?= strtoupper(substr($log['user_nama'] ?? '?', 0, 1)) ?>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-slate-700 dark:text-slate-300 text-xs font-medium"><?= e($log['description']) ?></p>
          <p class="text-slate-400 dark:text-slate-500 text-xs mt-0.5"><?= timeAgo($log['created_at']) ?></p>
        </div>
        <span class="text-xs text-slate-400 flex-shrink-0 whitespace-nowrap">
          <?= e($log['action']) ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Monthly Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyData = <?= json_encode(array_column($monthlyData, 'total')) ?>;
const monthlyLabels = <?= json_encode(array_map(function($r) {
  $parts = explode('-', $r['month']);
  $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
  return $months[(int)$parts[1]-1] . ' ' . $parts[0];
}, $monthlyData)) ?>;

new Chart(monthlyCtx, {
  type: 'bar',
  data: {
    labels: monthlyLabels.length ? monthlyLabels : ['Belum ada data'],
    datasets: [{
      label: 'Pengajuan',
      data: monthlyData.length ? monthlyData : [0],
      backgroundColor: 'rgba(193,18,31,0.85)',
      borderRadius: 8,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { stepSize: 1, color: '#94a3b8' }, grid: { color: '#f1f5f9' } },
      x: { ticks: { color: '#94a3b8' }, grid: { display: false } }
    }
  }
});

// Prodi Chart
const prodiCtx = document.getElementById('prodiChart').getContext('2d');
const prodiLabels = <?= json_encode(array_column($prodiData, 'program_studi')) ?>;
const prodiValues = <?= json_encode(array_column($prodiData, 'total')) ?>;

new Chart(prodiCtx, {
  type: 'doughnut',
  data: {
    labels: prodiLabels.length ? prodiLabels : ['Belum ada data'],
    datasets: [{
      data: prodiValues.length ? prodiValues : [1],
      backgroundColor: ['#C1121F','#3B82F6','#10B981','#F59E0B'],
      borderWidth: 3,
      borderColor: '#fff',
      hoverOffset: 8,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ` ${ctx.label}: ${ctx.raw} pengajuan`
        }
      }
    },
    cutout: '65%'
  }
});
</script>

<?php include BASE_PATH . '/includes/admin_layout_bottom.php'; ?>
