<?php
$pageTitle  = 'Raport Laporan Dosen';
$breadcrumb = [['label' => 'Akademik', 'url' => '#'], ['label' => 'Raport Laporan Dosen']];
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

require_once __DIR__ . '/../functions/excel_raport_helper.php';

// Path file Excel tetap di server
define('RAPORT_EXCEL_PATH', __DIR__ . '/../Contoh Lampiran/Laporan Raport/Sistem Report Dosen 2025 - 2026 Gasal.xlsx');
define('RAPORT_EXCEL_NAME', 'Sistem Report Dosen 2025 - 2026 Gasal.xlsx');
define('RAPORT_PERIODE', 'Gasal 2025 - 2026');

// Parse Excel
$excelData = parseExcelRaport(RAPORT_EXCEL_PATH);
$allDosen  = $excelData['rows'] ?? [];
$hasError  = isset($excelData['error']);

// Ambil daftar prodi unik dari Excel
$prodiList = array_values(array_filter(array_unique(array_column($allDosen, 'Prodi'))));
sort($prodiList);

// Filter GET
$filterProdi = $_GET['prodi'] ?? '';
$filterCari  = trim($_GET['cari'] ?? '');
$step        = $_GET['step'] ?? 'list';
$selectedIds = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

// Filter dosen
$filteredDosen = $allDosen;
if ($filterProdi) {
    $filteredDosen = array_filter($filteredDosen, fn($d) => trim($d['Prodi']) === $filterProdi);
}
if ($filterCari) {
    $filteredDosen = array_filter($filteredDosen, fn($d) => stripos($d['Nama'], $filterCari) !== false);
}
$filteredDosen = array_values($filteredDosen);

// Jika step=print, ambil dosen yang dipilih
$selectedDosen = [];
if (($step === 'print' || $step === 'preview') && !empty($selectedIds)) {
    foreach ($allDosen as $d) {
        if (in_array($d['No'], $selectedIds)) {
            $selectedDosen[] = $d;
        }
    }
    if (empty($selectedDosen) && !empty($selectedIds)) {
        // fallback by name index
        foreach ($selectedIds as $idx) {
            if (isset($allDosen[(int)$idx - 1])) {
                $selectedDosen[] = $allDosen[(int)$idx - 1];
            }
        }
    }
}

// Jika preview satu dosen dari GET no
$previewNo = $_GET['no'] ?? null;
$previewDosen = null;
if ($step === 'preview' && $previewNo !== null) {
    foreach ($allDosen as $d) {
        if ((string)$d['No'] === (string)$previewNo) {
            $previewDosen = $d;
            break;
        }
    }
    if (!$previewDosen) $step = 'list';
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($step === 'print'): ?>
<!-- ====================== PRINT VIEW ====================== -->
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Raport Laporan Dosen – <?= RAPORT_PERIODE ?></title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Times+New+Roman&display=swap');
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; background: #fff; }
  .page { width: 210mm; min-height: 297mm; padding: 20mm 25mm 20mm 30mm; margin: 0 auto; page-break-after: always; }
  .page:last-child { page-break-after: avoid; }
  .header-top { display: flex; align-items: center; border-bottom: 3px solid #000; padding-bottom: 8px; margin-bottom: 16px; }
  .logo-area { width: 70px; height: 70px; margin-right: 12px; }
  .logo-area img { width: 100%; height: 100%; object-fit: contain; }
  .logo-area-placeholder { width: 70px; height: 70px; border: 1px solid #999; display: flex; align-items: center; justify-content: center; font-size: 8pt; text-align: center; margin-right: 12px; flex-shrink: 0; }
  .header-text { text-align: center; flex: 1; }
  .header-text h1 { font-size: 13pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
  .header-text h2 { font-size: 10pt; font-weight: normal; margin-bottom: 2px; }
  .header-text p { font-size: 9pt; }
  .doc-title { text-align: center; margin: 14px 0 10px; }
  .doc-title h3 { font-size: 13pt; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000; display: inline-block; padding-bottom: 2px; }
  .doc-title p  { font-size: 11pt; margin-top: 2px; }
  .section-title { font-weight: bold; font-size: 11pt; margin: 14px 0 6px; text-transform: uppercase; }
  .id-table { width: 100%; border-collapse: collapse; font-size: 11pt; margin-bottom: 8px; }
  .id-table td { padding: 3px 6px; vertical-align: top; }
  .id-table td:first-child { width: 180px; font-weight: bold; }
  .id-table td:nth-child(2) { width: 12px; }
  .recap-table { width: 100%; border-collapse: collapse; font-size: 11pt; margin-bottom: 10px; }
  .recap-table th, .recap-table td { border: 1px solid #000; padding: 5px 8px; }
  .recap-table th { background: #f0f0f0; text-align: center; font-weight: bold; }
  .recap-table td.center { text-align: center; }
  .badge-sb { background:#d1fae5; color:#065f46; padding:1px 8px; border-radius:10px; font-size:10pt; }
  .badge-b  { background:#dbeafe; color:#1e40af; padding:1px 8px; border-radius:10px; font-size:10pt; }
  .badge-c  { background:#fef3c7; color:#92400e; padding:1px 8px; border-radius:10px; font-size:10pt; }
  .badge-kb { background:#fee2e2; color:#991b1b; padding:1px 8px; border-radius:10px; font-size:10pt; }
  .perbaikan-table { width: 100%; border-collapse: collapse; font-size: 11pt; margin-bottom: 10px; }
  .perbaikan-table th, .perbaikan-table td { border: 1px solid #000; padding: 5px 8px; }
  .perbaikan-table th { background: #f0f0f0; font-weight: bold; text-align: center; }
  .catatan-table { width: 100%; border-collapse: collapse; font-size: 11pt; margin-bottom: 14px; }
  .catatan-table th, .catatan-table td { border: 1px solid #000; padding: 5px 8px; }
  .catatan-table th { background: #f0f0f0; font-weight: bold; }
  .ttd-area { display: flex; justify-content: space-between; margin-top: 30px; font-size: 11pt; }
  .ttd-box { text-align: center; min-width: 200px; }
  .ttd-box .ttd-space { height: 60px; }
  .ttd-box .ttd-name { font-weight: bold; border-top: 1px solid #000; display: inline-block; min-width: 180px; padding-top: 4px; }
  .kriteria-box { margin-top: 14px; font-size: 10pt; border: 1px solid #ccc; padding: 8px; }
  .kriteria-box table { border-collapse: collapse; width: 100%; }
  .kriteria-box table td, .kriteria-box table th { border: 1px solid #ccc; padding: 3px 8px; font-size: 10pt; }
  @media print {
    .no-print { display: none !important; }
    body { background: white; }
  }
</style>
</head>
<body>

<div class="no-print" style="background:#1e293b;color:#fff;padding:12px 20px;display:flex;align-items:center;gap:16px;position:sticky;top:0;z-index:100;">
  <button onclick="window.print()" style="background:#8c0c4c;color:#fff;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:bold;">🖨️ Cetak Semua</button>
  <span style="font-size:13px;">Cetak Raport Laporan Dosen – <?= RAPORT_PERIODE ?> | <?= count($selectedDosen) ?> dosen dipilih</span>
  <a href="raport_dosen.php" style="color:#94a3b8;font-size:13px;text-decoration:none;">← Kembali</a>
</div>

<?php
$perbaikanLabels = [
    1 => 'Kesiapan memberikan kuliah dan/atau praktek/praktikum',
    2 => 'Keteraturan dan ketertiban penyelenggaraan perkuliahan',
    3 => 'Kemampuan menghidupkan suasana kelas',
    4 => 'Kejelasan penyampaian materi dan jawaban terhadap pertanyaan di kelas',
    5 => 'Pemanfaatan media dan teknologi pembelajaran',
];
$printDosen = $selectedDosen;
if (empty($printDosen) && !empty($allDosen)) {
    $printDosen = $filteredDosen;
}
foreach ($printDosen as $d):
    $skorKuis    = (float)($d['Nilai Kuesioner'] ?? 0);
    $kat         = getSkorKategori($skorKuis);
    $kehadiran   = (float)($d['Jumlah Kehadiran'] ?? 0);
    $katHadir    = getKategoriKehadiran($kehadiran);
    $konten      = (float)($d['Konten'] ?? 0);
    $katKonten   = getSkorKategori($konten);
    $penelitian  = (int)($d['Jumlah Penelitian'] ?? 0);
    $pengabdian  = (int)($d['Jumlah Pengabdian'] ?? 0);
    $katPenelitian  = $penelitian >= 1 ? 'Memenuhi' : 'Belum Memenuhi';
    $katPengabdian  = $pengabdian >= 1 ? 'Memenuhi' : 'Belum Memenuhi';
    
    // Badge class
    $badgeClass = match($kat['label']) {
        'Sangat Baik' => 'badge-sb',
        'Baik'        => 'badge-b',
        'Cukup'       => 'badge-c',
        default       => 'badge-kb',
    };
?>
<div class="page">
  <!-- HEADER -->
  <div class="header-top">
    <div class="logo-area-placeholder">LOGO NPU</div>
    <div class="header-text">
      <h1>Universitas Nusa Putra</h1>
      <h2>Unit Penjaminan Mutu</h2>
      <p>Jl. Raya Cibolang No. 21, Cisaat, Sukabumi, Jawa Barat 43152. Telp. (0266) 210594</p>
    </div>
  </div>

  <!-- JUDUL -->
  <div class="doc-title">
    <h3>Laporan Evaluasi Tridharma Dosen</h3>
    <p><?= htmlspecialchars(RAPORT_PERIODE) ?></p>
  </div>

  <!-- A. IDENTITAS -->
  <div class="section-title">A. Identitas Dosen</div>
  <table class="id-table">
    <tr><td>Nama Dosen</td><td>:</td><td><?= htmlspecialchars($d['Nama'] ?? '-') ?></td></tr>
    <tr><td>Program Studi</td><td>:</td><td><?= htmlspecialchars($d['Prodi'] ?? '-') ?></td></tr>
    <tr><td>Jumlah Mata Kuliah</td><td>:</td><td><?= htmlspecialchars($d['Jumlah Matkul'] ?? '0') ?></td></tr>
    <tr><td>Jumlah Kelas</td><td>:</td><td><?= htmlspecialchars($d['Jumlah Kelas'] ?? '0') ?></td></tr>
    <tr><td>Jumlah Responden</td><td>:</td><td><?= htmlspecialchars($d['Jumlah Responden'] ?? '0') ?></td></tr>
  </table>

  <!-- B. REKAPITULASI -->
  <div class="section-title">B. Rekapitulasi Penilaian</div>
  <table class="recap-table">
    <thead>
      <tr>
        <th style="width:40%">Indikator Penilaian</th>
        <th style="width:15%">Nilai</th>
        <th style="width:20%">Keterangan</th>
        <th style="width:25%">Kategori</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Kuesioner Mahasiswa</td>
        <td class="center"><?= $skorKuis > 0 ? number_format($skorKuis, 2) : '-' ?></td>
        <td class="center"><span class="<?= $badgeClass ?>"><?= $kat['label'] ?></span></td>
        <td style="font-size:9pt;"><?= $skorKuis > 0 ? 'Skor dari penilaian mahasiswa' : 'Tidak ada data' ?></td>
      </tr>
      <tr>
        <td>Kehadiran Mengajar</td>
        <td class="center"><?= $kehadiran > 0 ? $kehadiran . ' pertemuan' : '-' ?></td>
        <td class="center">
          <?php $katHC = ($kehadiran >= 16) ? 'Memenuhi' : (($kehadiran >= 14) ? 'Cukup' : 'Belum Memenuhi'); ?>
          <span class="<?= $katHC === 'Memenuhi' ? 'badge-sb' : ($katHC === 'Cukup' ? 'badge-c' : 'badge-kb') ?>">
            <?= $katHC ?>
          </span>
        </td>
        <td style="font-size:9pt;">Standar minimal 16 pertemuan</td>
      </tr>
      <tr>
        <td>Kelengkapan Konten Perkuliahan</td>
        <td class="center"><?= $konten > 0 ? number_format($konten, 2) : '-' ?></td>
        <td class="center">
          <?php $katK = getSkorKategori($konten); ?>
          <span class="<?= match($katK['label']) { 'Sangat Baik'=>'badge-sb','Baik'=>'badge-b','Cukup'=>'badge-c',default=>'badge-kb' } ?>">
            <?= $katK['label'] ?>
          </span>
        </td>
        <td style="font-size:9pt;">Kelengkapan materi di LMS</td>
      </tr>
      <tr>
        <td>Penelitian</td>
        <td class="center"><?= $penelitian ?> kegiatan</td>
        <td class="center">
          <span class="<?= $penelitian >= 1 ? 'badge-sb' : 'badge-kb' ?>">
            <?= $penelitian >= 1 ? 'Memenuhi' : 'Belum Memenuhi' ?>
          </span>
        </td>
        <td style="font-size:9pt;">Standar minimal 1 penelitian/semester</td>
      </tr>
      <tr>
        <td>Pengabdian Masyarakat</td>
        <td class="center"><?= $pengabdian ?> kegiatan</td>
        <td class="center">
          <span class="<?= $pengabdian >= 1 ? 'badge-sb' : 'badge-kb' ?>">
            <?= $pengabdian >= 1 ? 'Memenuhi' : 'Belum Memenuhi' ?>
          </span>
        </td>
        <td style="font-size:9pt;">Standar minimal 1 pengabdian/semester</td>
      </tr>
    </tbody>
  </table>

  <!-- C. ASPEK PEMBELAJARAN -->
  <div class="section-title">C. Aspek Pembelajaran – Rekomendasi Perbaikan</div>
  <table class="perbaikan-table">
    <thead>
      <tr><th style="width:8%">No</th><th>Aspek yang Perlu Ditingkatkan</th><th style="width:20%">Nilai Aspek</th></tr>
    </thead>
    <tbody>
      <?php
      $pKeys = ['P1','P2','P3','P4','P5'];
      $pItems = [];
      foreach ($pKeys as $pk) {
          $v = trim($d[$pk] ?? '');
          if ($v !== '' && $v !== '0') $pItems[] = $v;
      }
      
      // Cari aspek dengan skor rendah dari penilaian kuesioner
      $aspekList = [
          'Kesiapan memberikan kuliah dan/atau praktek/praktikum',
          'Keteraturan dan ketertiban penyelenggaraan perkuliahan',
          'Kemampuan menghidupkan suasana kelas',
          'Kejelasan penyampaian materi dan jawaban terhadap pertanyaan di kelas',
          'Pemanfaatan media dan teknologi pembelajaran',
      ];
      
      if (!empty($pItems)) {
          foreach ($pItems as $idx => $item) {
              echo "<tr><td style='text-align:center'>" . ($idx+1) . "</td><td>" . htmlspecialchars($item) . "</td><td style='text-align:center'>-</td></tr>";
          }
      } else {
          // Tampilkan placeholder jika tidak ada data spesifik
          echo "<tr><td style='text-align:center' colspan='3'><em>Tidak ada aspek yang perlu diperbaiki secara khusus berdasarkan data kuesioner</em></td></tr>";
      }
      ?>
    </tbody>
  </table>

  <!-- D. CATATAN -->
  <div class="section-title">D. Catatan</div>
  <table class="catatan-table">
    <thead>
      <tr><th style="width:8%">No</th><th>Catatan</th></tr>
    </thead>
    <tbody>
      <?php
      $kKeys = ['K1','K2','K3','K4'];
      $kItems = [];
      foreach ($kKeys as $kk) {
          $v = trim($d[$kk] ?? '');
          if ($v !== '' && $v !== '0') $kItems[] = $v;
      }
      
      // Generate catatan otomatis berdasarkan data
      $autoCatatan = [];
      if ($skorKuis > 0 && $skorKuis < 3.66) {
          $autoCatatan[] = 'Skor kuesioner mahasiswa masih di bawah standar. Perlu perhatian khusus pada kualitas pengajaran.';
      }
      if ($kehadiran > 0 && $kehadiran < 16) {
          $autoCatatan[] = 'Kehadiran mengajar belum memenuhi standar minimal 16 pertemuan per semester.';
      }
      if ($konten > 0 && $konten < 3.66) {
          $autoCatatan[] = 'Kelengkapan konten perkuliahan di LMS perlu ditingkatkan.';
      }
      if ($penelitian < 1) {
          $autoCatatan[] = 'Dosen belum melaksanakan kegiatan penelitian pada semester ini.';
      }
      if ($pengabdian < 1) {
          $autoCatatan[] = 'Dosen belum melaksanakan kegiatan pengabdian masyarakat pada semester ini.';
      }
      
      $allCatatan = array_merge($kItems, $autoCatatan);
      
      if (empty($allCatatan)) {
          echo "<tr><td style='text-align:center'>-</td><td><em>Tidak ada catatan khusus. Kinerja dosen sesuai standar yang ditetapkan.</em></td></tr>";
      } else {
          foreach ($allCatatan as $idx => $cat) {
              echo "<tr><td style='text-align:center'>" . ($idx+1) . "</td><td>" . htmlspecialchars($cat) . "</td></tr>";
          }
      }
      ?>
    </tbody>
  </table>

  <!-- KRITERIA SKOR -->
  <div class="kriteria-box">
    <strong>Catatan: Kriteria Penskoran</strong>
    <table style="margin-top:4px;">
      <tr><th>Rentang Skor</th><th>Kriteria</th></tr>
      <tr><td>3.20 – 3.65</td><td>Kurang Baik</td></tr>
      <tr><td>3.66 – 4.11</td><td>Cukup</td></tr>
      <tr><td>4.12 – 4.57</td><td>Baik</td></tr>
      <tr><td>4.58 – 5.00</td><td>Sangat Baik</td></tr>
    </table>
  </div>

  <!-- TTD -->
  <div class="ttd-area">
    <div class="ttd-box">
      <p>Sukabumi, <?= tgl_indo(date('Y-m-d')) ?></p>
      <div class="ttd-space"></div>
      <div><span class="ttd-name">Unit Penjaminan Mutu</span></div>
      <p>Universitas Nusa Putra</p>
    </div>
    <div class="ttd-box">
      <p style="margin-bottom:4px;">Mengetahui,</p>
      <p>Direktur Pascasarjana</p>
      <div class="ttd-space"></div>
      <div><span class="ttd-name">Dr. SAMSUL PAHMI, M.Pd.</span></div>
    </div>
  </div>
</div>
<?php endforeach; ?>

</body>
</html>
<?php
// Stop di sini untuk mode print
die();
endif;
?>

<?php if ($step === 'preview' && $previewDosen): ?>
<!-- ====================== PREVIEW SINGLE ====================== -->
<div class="max-w-4xl mx-auto pb-10">
  <div class="mb-6 flex items-center justify-between no-print">
    <div>
      <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-1">👁️ Preview Raport Dosen</h2>
      <p class="text-slate-500 dark:text-slate-400 text-sm"><?= htmlspecialchars($previewDosen['Nama']) ?></p>
    </div>
    <div class="flex gap-2">
      <a href="raport_dosen.php" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-semibold text-sm hover:bg-slate-200 transition-colors">← Kembali</a>
      <a href="raport_dosen.php?step=print&ids=<?= $previewDosen['No'] ?>" target="_blank"
         class="inline-flex items-center gap-2 px-4 py-2 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl font-semibold text-sm transition-all shadow">
        🖨️ Cetak Raport Ini
      </a>
    </div>
  </div>

  <?php
  $d           = $previewDosen;
  $skorKuis    = (float)($d['Nilai Kuesioner'] ?? 0);
  $kat         = getSkorKategori($skorKuis);
  $kehadiran   = (float)($d['Jumlah Kehadiran'] ?? 0);
  $konten      = (float)($d['Konten'] ?? 0);
  $katKonten   = getSkorKategori($konten);
  $penelitian  = (int)($d['Jumlah Penelitian'] ?? 0);
  $pengabdian  = (int)($d['Jumlah Pengabdian'] ?? 0);

  $colorMap = ['Sangat Baik' => 'emerald', 'Baik' => 'blue', 'Cukup' => 'amber', 'Kurang Baik' => 'red'];
  $col = $colorMap[$kat['label']] ?? 'slate';
  ?>

  <!-- Preview Card -->
  <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700/60 p-8">
    <!-- Identitas -->
    <div class="border-b border-slate-100 dark:border-slate-700/60 pb-6 mb-6">
      <h3 class="font-bold text-lg text-slate-800 dark:text-white mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-lg bg-[#8c0c4c]/10 text-[#8c0c4c] flex items-center justify-center text-sm font-bold">A</span>
        Identitas Dosen
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
        <div class="flex gap-3"><span class="text-slate-500 w-40 shrink-0">Nama Dosen</span><span class="font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($d['Nama'] ?? '-') ?></span></div>
        <div class="flex gap-3"><span class="text-slate-500 w-40 shrink-0">Program Studi</span><span class="font-semibold text-slate-700 dark:text-slate-300"><?= htmlspecialchars($d['Prodi'] ?? '-') ?></span></div>
        <div class="flex gap-3"><span class="text-slate-500 w-40 shrink-0">Jumlah Mata Kuliah</span><span class="font-semibold text-slate-700 dark:text-slate-300"><?= htmlspecialchars($d['Jumlah Matkul'] ?? '0') ?></span></div>
        <div class="flex gap-3"><span class="text-slate-500 w-40 shrink-0">Jumlah Kelas</span><span class="font-semibold text-slate-700 dark:text-slate-300"><?= htmlspecialchars($d['Jumlah Kelas'] ?? '0') ?></span></div>
        <div class="flex gap-3"><span class="text-slate-500 w-40 shrink-0">Jumlah Responden</span><span class="font-semibold text-slate-700 dark:text-slate-300"><?= htmlspecialchars($d['Jumlah Responden'] ?? '-') ?></span></div>
        <div class="flex gap-3"><span class="text-slate-500 w-40 shrink-0">Periode</span><span class="font-semibold text-slate-700 dark:text-slate-300"><?= RAPORT_PERIODE ?></span></div>
      </div>
    </div>

    <!-- Rekapitulasi -->
    <div class="border-b border-slate-100 dark:border-slate-700/60 pb-6 mb-6">
      <h3 class="font-bold text-lg text-slate-800 dark:text-white mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-lg bg-[#8c0c4c]/10 text-[#8c0c4c] flex items-center justify-center text-sm font-bold">B</span>
        Rekapitulasi Penilaian
      </h3>
      <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50">
              <th class="text-left py-2.5 px-4 font-semibold text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Indikator</th>
              <th class="text-center py-2.5 px-4 font-semibold text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Nilai</th>
              <th class="text-center py-2.5 px-4 font-semibold text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $indikators = [
              ['Kuesioner Mahasiswa', $skorKuis > 0 ? number_format($skorKuis,2) : '-', $kat['label']],
              ['Kehadiran Mengajar', $kehadiran > 0 ? $kehadiran . ' pertemuan' : '-', getKategoriKehadiran($kehadiran)],
              ['Kelengkapan Konten LMS', $konten > 0 ? number_format($konten,2) : '-', $katKonten['label']],
              ['Penelitian', $penelitian . ' kegiatan', $penelitian >= 1 ? 'Memenuhi' : 'Belum Memenuhi'],
              ['Pengabdian Masyarakat', $pengabdian . ' kegiatan', $pengabdian >= 1 ? 'Memenuhi' : 'Belum Memenuhi'],
            ];
            $colorBadge = fn($label) => match($label) {
              'Sangat Baik', 'Memenuhi' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
              'Baik' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
              'Cukup' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
              default => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
            };
            foreach ($indikators as $ind): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
              <td class="py-2.5 px-4 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200"><?= $ind[0] ?></td>
              <td class="py-2.5 px-4 border border-slate-200 dark:border-slate-700 text-center font-semibold"><?= $ind[1] ?></td>
              <td class="py-2.5 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <span class="px-3 py-1 rounded-full text-xs font-bold <?= $colorBadge($ind[2]) ?>"><?= $ind[2] ?></span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tombol Cetak -->
    <div class="flex justify-end">
      <a href="raport_dosen.php?step=print&ids=<?= $previewDosen['No'] ?>" target="_blank"
         class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl font-semibold shadow hover:shadow-md transition-all">
        🖨️ Cetak Raport Lengkap
      </a>
    </div>
  </div>
</div>
<?php else: ?>
<!-- ====================== LIST VIEW ====================== -->
<div class="pb-10">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
    <div>
      <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">📋 Raport Laporan Dosen</h1>
      <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Generate surat raport dosen otomatis dari data kuesioner dan evaluasi Tridharma</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="px-4 py-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm">
        <span class="text-emerald-700 dark:text-emerald-400 font-bold">📁 <?= RAPORT_EXCEL_NAME ?></span>
        <br><span class="text-emerald-600 text-xs"><?= count($allDosen) ?> dosen · <?= RAPORT_PERIODE ?></span>
      </div>
    </div>
  </div>

  <?php if ($hasError): ?>
  <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-2xl p-6 mb-6">
    <p class="text-red-700 dark:text-red-400 font-semibold">❌ <?= htmlspecialchars($excelData['error']) ?></p>
  </div>
  <?php endif; ?>

  <!-- Stats Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <?php
    $totalDosen   = count($allDosen);
    $dosenSB      = count(array_filter($allDosen, fn($d) => (float)($d['Nilai Kuesioner']??0) >= 4.58));
    $dosenBaik    = count(array_filter($allDosen, function($d) { $s=(float)($d['Nilai Kuesioner']??0); return $s >= 4.12 && $s < 4.58; }));
    $dosenCukup   = count(array_filter($allDosen, function($d) { $s=(float)($d['Nilai Kuesioner']??0); return $s >= 3.66 && $s < 4.12; }));
    $dosenKurang  = count(array_filter($allDosen, function($d) { $s=(float)($d['Nilai Kuesioner']??0); return $s > 0 && $s < 3.66; }));
    $stats = [
      ['label' => 'Total Dosen', 'value' => $totalDosen, 'icon' => '👨‍🏫', 'bg' => 'bg-indigo-50 dark:bg-indigo-900/30', 'text' => 'text-indigo-700 dark:text-indigo-300'],
      ['label' => 'Sangat Baik', 'value' => $dosenSB, 'icon' => '⭐', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-300'],
      ['label' => 'Baik', 'value' => $dosenBaik, 'icon' => '✅', 'bg' => 'bg-blue-50 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300'],
      ['label' => 'Perlu Perhatian', 'value' => $dosenCukup + $dosenKurang, 'icon' => '⚠️', 'bg' => 'bg-amber-50 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-300'],
    ];
    ?>
    <?php foreach ($stats as $s): ?>
    <div class="<?= $s['bg'] ?> rounded-2xl p-4 border border-white/60 dark:border-white/5">
      <div class="text-2xl mb-1"><?= $s['icon'] ?></div>
      <div class="text-2xl font-bold <?= $s['text'] ?>"><?= $s['value'] ?></div>
      <div class="text-xs font-semibold <?= $s['text'] ?> opacity-80"><?= $s['label'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Filter + Aksi -->
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-5 mb-6">
    <form method="GET" id="filter-form" class="flex flex-wrap gap-3 items-end">
      <input type="hidden" name="step" value="list">
      
      <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Cari Nama Dosen</label>
        <div class="relative">
          <input type="text" name="cari" value="<?= htmlspecialchars($filterCari) ?>" placeholder="Nama dosen..."
            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 pl-9 text-sm focus:outline-none focus:border-[#8c0c4c] transition-colors">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
      </div>

      <div class="min-w-[200px]">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Filter Prodi</label>
        <select name="prodi" onchange="document.getElementById('filter-form').submit()"
          class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c]">
          <option value="">Semua Prodi</option>
          <?php foreach ($prodiList as $p): ?>
          <option value="<?= htmlspecialchars($p) ?>" <?= $filterProdi === $p ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="px-5 py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-sm font-semibold hover:bg-slate-700 transition-colors">
        🔍 Cari
      </button>

      <?php if ($filterProdi || $filterCari): ?>
      <a href="raport_dosen.php" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">
        ✕ Reset
      </a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Toolbar Batch -->
  <div class="bg-gradient-to-r from-[#8c0c4c] to-[#6d0039] rounded-2xl p-4 mb-4 flex items-center justify-between" id="batch-toolbar" style="display:none!important">
    <div class="flex items-center gap-3 text-white">
      <span class="text-2xl">🖨️</span>
      <div>
        <div class="font-bold text-base" id="selected-count-text">0 dosen dipilih</div>
        <div class="text-xs opacity-80">Pilih dosen dari tabel untuk generate raport</div>
      </div>
    </div>
    <div class="flex gap-2">
      <button onclick="printSelected()" class="px-5 py-2 bg-white text-[#8c0c4c] rounded-xl text-sm font-bold hover:bg-slate-100 transition-colors shadow">
        🖨️ Cetak Raport Terpilih
      </button>
      <button onclick="printAll()" class="px-5 py-2 bg-white/20 text-white border border-white/30 rounded-xl text-sm font-semibold hover:bg-white/30 transition-colors">
        📄 Cetak Semua (<?= count($filteredDosen) ?>)
      </button>
    </div>
  </div>

  <!-- Button Cetak Semua (always visible) -->
  <div class="flex justify-between items-center mb-3">
    <p class="text-sm text-slate-500 dark:text-slate-400">
      Menampilkan <strong class="text-slate-700 dark:text-slate-200"><?= count($filteredDosen) ?></strong> dari <strong><?= $totalDosen ?></strong> dosen
    </p>
    <div class="flex gap-2">
      <button onclick="selectAll()" id="btn-select-all" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">
        ☑️ Pilih Semua
      </button>
      <button onclick="printAll()" class="inline-flex items-center gap-2 px-5 py-2 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl text-sm font-semibold transition-all shadow">
        🖨️ Cetak Semua (<?= count($filteredDosen) ?>)
      </button>
    </div>
  </div>

  <!-- Table -->
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm" id="dosen-table">
        <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
          <tr>
            <th class="py-3.5 px-4 text-left w-10">
              <input type="checkbox" id="check-all" onchange="toggleAll(this)" class="rounded cursor-pointer w-4 h-4 accent-[#8c0c4c]">
            </th>
            <th class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
            <th class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Dosen</th>
            <th class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Program Studi</th>
            <th class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">MK/Kelas</th>
            <th class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Responden</th>
            <th class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Skor Kuesioner</th>
            <th class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Kehadiran</th>
            <th class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Penelitian</th>
            <th class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Pengabdian</th>
            <th class="py-3.5 px-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60" id="dosen-tbody">
          <?php if (empty($filteredDosen)): ?>
          <tr>
            <td colspan="11" class="py-12 text-center text-slate-400">
              <div class="text-4xl mb-2">🔍</div>
              <div class="font-medium">Tidak ada data dosen yang ditemukan</div>
            </td>
          </tr>
          <?php else: foreach ($filteredDosen as $idx => $d):
            $skor  = (float)($d['Nilai Kuesioner'] ?? 0);
            $kat   = getSkorKategori($skor);
            $hadir = (float)($d['Jumlah Kehadiran'] ?? 0);
            $katHC = $hadir >= 16 ? 'Memenuhi' : ($hadir >= 14 ? 'Cukup' : ($hadir > 0 ? 'Kurang' : '-'));
            $pen   = (int)($d['Jumlah Penelitian'] ?? 0);
            $peng  = (int)($d['Jumlah Pengabdian'] ?? 0);
            
            $badgeColors = [
              'Sangat Baik' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
              'Baik'        => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
              'Cukup'       => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
              'Kurang Baik' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
            ];
            $bc = $badgeColors[$kat['label']] ?? 'bg-slate-100 text-slate-600';
          ?>
          <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group" data-no="<?= htmlspecialchars($d['No']) ?>">
            <td class="py-3.5 px-4">
              <input type="checkbox" name="dosen_check" value="<?= htmlspecialchars($d['No']) ?>"
                class="dosen-checkbox rounded cursor-pointer w-4 h-4 accent-[#8c0c4c]"
                onchange="updateBatchToolbar()">
            </td>
            <td class="py-3.5 px-4 text-slate-400 font-mono text-xs"><?= $d['No'] ?></td>
            <td class="py-3.5 px-4">
              <div class="font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($d['Nama'] ?? '-') ?></div>
            </td>
            <td class="py-3.5 px-4">
              <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-medium">
                <?= htmlspecialchars($d['Prodi'] ?? '-') ?>
              </span>
            </td>
            <td class="py-3.5 px-4 text-center">
              <span class="font-semibold text-slate-700 dark:text-slate-300"><?= $d['Jumlah Matkul'] ?: '-' ?></span>
              <span class="text-slate-400">/</span>
              <span class="text-slate-600 dark:text-slate-400"><?= $d['Jumlah Kelas'] ?: '-' ?></span>
            </td>
            <td class="py-3.5 px-4 text-center text-slate-600 dark:text-slate-300 text-xs"><?= htmlspecialchars($d['Jumlah Responden'] ?? '-') ?></td>
            <td class="py-3.5 px-4 text-center">
              <?php if ($skor > 0): ?>
              <div class="flex flex-col items-center gap-1">
                <span class="font-bold text-slate-800 dark:text-white"><?= number_format($skor, 2) ?></span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $bc ?>"><?= $kat['label'] ?></span>
              </div>
              <?php else: ?>
              <span class="text-slate-300 text-xs">-</span>
              <?php endif; ?>
            </td>
            <td class="py-3.5 px-4 text-center">
              <?php if ($hadir > 0): ?>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $hadir >= 16 ? 'bg-emerald-100 text-emerald-700' : ($hadir >= 14 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') ?>">
                <?= $hadir ?> pertemuan
              </span>
              <?php else: ?>
              <span class="text-slate-300 text-xs">-</span>
              <?php endif; ?>
            </td>
            <td class="py-3.5 px-4 text-center">
              <span class="<?= $pen >= 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' ?> font-semibold"><?= $pen ?></span>
            </td>
            <td class="py-3.5 px-4 text-center">
              <span class="<?= $peng >= 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' ?> font-semibold"><?= $peng ?></span>
            </td>
            <td class="py-3.5 px-4 text-right">
              <div class="flex items-center justify-end gap-1.5">
                <a href="raport_dosen.php?step=preview&no=<?= $d['No'] ?>"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 transition-colors">
                   👁️ Preview
                </a>
                <a href="raport_dosen.php?step=print&ids=<?= $d['No'] ?>" target="_blank"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#8c0c4c]/10 text-[#8c0c4c] hover:bg-[#8c0c4c]/20 transition-colors">
                   🖨️ Cetak
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
// ===== Checkbox / Batch =====
function updateBatchToolbar() {
  const checks = document.querySelectorAll('.dosen-checkbox:checked');
  const toolbar = document.getElementById('batch-toolbar');
  const countText = document.getElementById('selected-count-text');
  if (toolbar) {
    if (checks.length > 0) {
      toolbar.style.display = 'flex';
      toolbar.style.removeProperty('display');
      toolbar.style.cssText = '';
    } else {
      toolbar.style.cssText = 'display:none!important';
    }
  }
  if (countText) {
    countText.textContent = checks.length + ' dosen dipilih';
  }
}

function toggleAll(chk) {
  document.querySelectorAll('.dosen-checkbox').forEach(c => c.checked = chk.checked);
  updateBatchToolbar();
}

function selectAll() {
  const allCheck = document.getElementById('check-all');
  if (allCheck) { allCheck.checked = true; toggleAll(allCheck); }
}

function printSelected() {
  const checks = document.querySelectorAll('.dosen-checkbox:checked');
  if (checks.length === 0) {
    alert('Pilih minimal satu dosen terlebih dahulu.');
    return;
  }
  const ids = Array.from(checks).map(c => c.value).join(',');
  window.open('raport_dosen.php?step=print&ids=' + encodeURIComponent(ids), '_blank');
}

function printAll() {
  // Ambil semua no dari tbody
  const rows = document.querySelectorAll('#dosen-tbody tr[data-no]');
  if (rows.length === 0) {
    alert('Tidak ada data untuk dicetak.');
    return;
  }
  const ids = Array.from(rows).map(r => r.getAttribute('data-no')).join(',');
  window.open('raport_dosen.php?step=print&ids=' + encodeURIComponent(ids), '_blank');
}

// Update batch toolbar on load
document.addEventListener('DOMContentLoaded', function() {
  updateBatchToolbar();
  
  // Sync check-all state
  const allChecks = document.querySelectorAll('.dosen-checkbox');
  allChecks.forEach(c => c.addEventListener('change', function() {
    const allCheck = document.getElementById('check-all');
    if (allCheck) {
      allCheck.checked = document.querySelectorAll('.dosen-checkbox:checked').length === allChecks.length;
      allCheck.indeterminate = document.querySelectorAll('.dosen-checkbox:checked').length > 0 &&
                               document.querySelectorAll('.dosen-checkbox:checked').length < allChecks.length;
    }
  }));
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
