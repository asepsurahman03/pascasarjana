<?php
$pageTitle  = 'Raport Laporan Dosen';
$breadcrumb = [['label' => 'Akademik', 'url' => '#'], ['label' => 'Raport Laporan Dosen']];
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
require_once __DIR__ . '/../functions/excel_raport_helper.php';

// ======================================================
// MULTI-PERIODE: Gasal & Genap
// ======================================================
$periodeParam = $_GET['periode'] ?? 'gasal';
$periodeParam = in_array($periodeParam, ['gasal','genap']) ? $periodeParam : 'gasal';

$periodeConfig = [
    'gasal' => [
        'label'    => 'Gasal 2025 - 2026',
        'excel'    => 'Sistem Report Dosen 2025 - 2026 Gasal.xlsx',
        'path'     => __DIR__ . '/../Contoh Lampiran/Laporan Raport/Sistem Report Dosen 2025 - 2026 Gasal.xlsx',
        'pdf'      => null,
    ],
    'genap' => [
        'label'    => 'Genap 2025 - 2026',
        'excel'    => 'Sistem Report Dosen 2025 - 2026 Genap.xlsx',
        'path'     => __DIR__ . '/../Contoh Lampiran/Laporan Raport/Sistem Report Dosen 2025 - 2026 Genap.xlsx',
        'pdf'      => __DIR__ . '/../Contoh Lampiran/Laporan Raport/Laporan Rekap Kuesioner 2025 - 2026 Genap.pdf',
    ],
];

$cfg = $periodeConfig[$periodeParam];

define('RAPORT_EXCEL_PATH', $cfg['path']);
define('RAPORT_EXCEL_NAME', $cfg['excel']);
define('RAPORT_PERIODE',    $cfg['label']);
define('RAPORT_PERIODE_KEY', $periodeParam);

// Parse data – Excel jika ada, fallback ke PDF parser untuk Genap
$excelData = [];
$allDosen  = [];
$hasError  = false;

if (file_exists(RAPORT_EXCEL_PATH)) {
    $excelData = parseExcelRaport(RAPORT_EXCEL_PATH);
    $allDosen  = $excelData['rows'] ?? [];
    $hasError  = isset($excelData['error']);
} elseif ($periodeParam === 'genap' && $cfg['pdf'] && file_exists($cfg['pdf'])) {
    // Fallback: gunakan data yang sudah di-parse dari PDF
    require_once __DIR__ . '/../functions/pdf_genap_parser.php';
    $allDosen = parseGenapPDF($cfg['pdf']);
    if (empty($allDosen)) {
        $hasError = true;
        $excelData['error'] = 'File Excel Genap belum tersedia. Data PDF belum dapat di-parse otomatis.';
    }
} else {
    $hasError = true;
    $excelData['error'] = 'File Excel ' . RAPORT_EXCEL_NAME . ' tidak ditemukan di server.';
}

// Daftar prodi unik
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
  /* ================================================================
     TEMPLATE SURAT RAPORT DOSEN - SESUAI EXCEL SHEET 'Rapot'
     ================================================================ */
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Times New Roman',Times,serif; font-size:9pt; color:#000; background:#fff; }

  @page { size: A4 portrait; }

  /* Halaman A4 portrait */
  .page {
    width: 210mm;
    padding: 10mm 10mm 10mm 15mm;
    margin: 0 auto;
    page-break-after: always;
    font-size: 9pt;
    position: relative;
  }
  .page:last-child { page-break-after: avoid; }

  /* ===== HEADER ===== */
  .hdr {
    text-align: center;
    padding-bottom: 5px;
    border-bottom: 2.5pt double #000;
    margin-bottom: 5px;
    line-height: 1.3;
  }
  .hdr .r5 { font-weight: bold; font-size: 9pt; text-transform: uppercase; }
  .hdr .r6 { font-weight: bold; font-size: 9pt; }
  .hdr .r7 { font-weight: bold; font-size: 9pt; }
  .hdr .r8 { font-size: 9pt; }

  /* Section headers */
  .sec-a, .sec-b, .sec-c, .sec-d { font-weight: bold; font-size: 9pt; margin: 4px 0 2px; }
  .subsec { font-weight: bold; font-size: 9pt; margin: 2px 0 2px; }

  /* A. IDENTITAS */
  .tbl-id { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 4px; }
  .tbl-id td { padding: 1px 3px; vertical-align: top; font-size: 9pt; }
  .tbl-id td.lbl { width: 33%; }

  /* B. REKAPITULASI */
  .tbl-rekap { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 4px; }
  .tbl-rekap th { border: 1px solid #000; padding: 3px 5px; font-weight: bold; text-align: center; font-size: 9pt; }
  .tbl-rekap td { border: 1px solid #000; padding: 2px 5px; font-size: 9pt; vertical-align: middle; }
  .tbl-rekap td.nilai { text-align: right; width: 13%; }
  .tbl-rekap td.ket   { width: 42%; }
  .tbl-rekap td.ind   { width: 45%; }

  /* C. ASPEK */
  .aspek-tbl { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 3px; }
  .aspek-tbl td { padding: 2px 3px; font-size: 9pt; vertical-align: top; border-bottom: 0.5pt solid #ccc; }
  .aspek-tbl td.no-col  { width: 22px; text-align: center; }

  /* D. CATATAN */
  .cat-tbl { width: 100%; border-collapse: collapse; font-size: 9pt; margin-bottom: 3px; }
  .cat-tbl td { padding: 2px 3px; font-size: 9pt; vertical-align: top; border-bottom: 0.5pt solid #ccc; }
  .cat-tbl td.dash-col { width: 22px; font-weight: bold; text-align: center; }
  /* Row 34 dipaskan agar proporsional */
  .cat-tbl tr:nth-child(2) td { min-height: 25pt; height: 25pt; }

  /* FOOTER */
  .footer-wrap { margin-top: 6px; display: flex; align-items: flex-start; }
  .footer-left  { flex: 0 0 42%; font-size: 9pt; }
  .footer-right { flex: 1; font-size: 9pt; }
  .footer-left .ttd-wrap { margin-top: 5px; text-align: center; width: 170px; }
  .footer-left .ttd-name { font-weight: bold; font-size: 9pt; border-top: 1px solid #000;
                            padding-top: 2px; display: inline-block; width: 100%; text-align: left; }
  .footer-right .kr-hd { font-size: 9pt; margin-bottom: 2px; }
  .tbl-kr { border-collapse: collapse; width: 100%; font-size: 9pt; }
  .tbl-kr th { border: 1px solid #000; padding: 2px 5px; font-weight: bold; text-align: center; font-size: 9pt; }
  .tbl-kr td { border: 1px solid #000; padding: 2px 5px; font-size: 9pt; }
  .tbl-kr td.skor { text-align: right; width: 22%; }
  .tbl-kr td.sd   { text-align: left; width: 33%; }
  .tbl-kr td.ket  { text-align: left; }

  @media print {
    .no-print { display: none !important; }
    body { background: white; margin: 0; padding: 0; }
    .page { padding: 5mm 10mm 10mm 15mm !important; width: 100%; box-shadow: none; margin: 0; }
  }
</style>
</head>
<body>

<div class="no-print" style="background:#1e293b;color:#fff;padding:10px 20px;display:flex;align-items:center;gap:16px;position:sticky;top:0;z-index:100;font-family:sans-serif;">
  <button onclick="window.print()" style="background:#8c0c4c;color:#fff;border:none;padding:8px 20px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:bold;">&#128424; Cetak / Print PDF</button>
  <span style="font-size:13px;">Raport Laporan Dosen &ndash; <?= RAPORT_PERIODE ?> | <?= count($selectedDosen) ?> dosen</span>
  <a href="raport_dosen.php?periode=<?= RAPORT_PERIODE_KEY ?>" style="color:#94a3b8;font-size:13px;text-decoration:none;margin-left:auto;">&larr; Kembali ke Daftar</a>
</div>

<?php
/**
 * FORMULA KETERANGAN - persis dari Excel sheet Rapot:
 * D18: IF(C18>=4.58,"Sangat Baik",IF(AND(C18>=4.12,C18<4.8),"Baik",IF(AND(C18>=3.66,C18<4.12),"Cukup","Kurang Baik")))
 * D19: IF(C19<14,"Belum Memenuhi","Sudah Memenuhi")
 * D20: IF(C20>=4.58,"Sangat Baik",IF(AND(C20>=4.12,C20<4.57),"Baik",IF(AND(C20>=3.66,C20<4.12),"Cukup","Kurang ")))
 * D21: IF(C21>=1,"Memenuhi","Belum Memenuhi")
 * D22: IF(C22>=1,"Memenuhi","Belum Memenuhi")
 */
function getKetKuis(float $s): string {
    if ($s == 0) return '';
    if ($s >= 4.58) return 'Sangat Baik';
    if ($s >= 4.12 && $s < 4.8)  return 'Baik';
    if ($s >= 3.66 && $s < 4.12) return 'Cukup';
    return 'Kurang Baik';
}
function getKetHadir(float $h): string {
    if ($h == 0) return '';
    return $h < 14 ? 'Belum Memenuhi' : 'Sudah Memenuhi';
}
function getKetKonten(float $k): string {
    if ($k == 0) return '';
    if ($k >= 4.58) return 'Sangat Baik';
    if ($k >= 4.12 && $k < 4.57) return 'Baik';
    if ($k >= 3.66 && $k < 4.12) return 'Cukup';
    return 'Kurang';
}

$printDosen = $selectedDosen;
if (empty($printDosen)) $printDosen = $filteredDosen;

foreach ($printDosen as $d):
    $nama     = $d['Nama'] ?? '-';
    $prodi    = $d['Prodi'] ?? '-';
    $jmlMK    = $d['Jumlah Matkul'] ?? 0;
    $jmlKelas = $d['Jumlah Kelas'] ?? 0;
    $jmlResp  = $d['Jumlah Responden'] ?? 0;
    $sKuis    = (float)($d['Nilai Kuesioner'] ?? 0);
    $sHadir   = (float)($d['Jumlah Kehadiran'] ?? 0);
    $sKonten  = (float)($d['Konten'] ?? 0);
    $jPenel   = (int)($d['Jumlah Penelitian'] ?? 0);
    $jPengab  = (int)($d['Jumlah Pengabdian'] ?? 0);

    $vKuis   = $sKuis   > 0 ? number_format($sKuis,   2) : '0';
    $vHadir  = $sHadir  > 0 ? (int)$sHadir               : '0';
    $vKonten = $sKonten > 0 ? number_format($sKonten, 2) : '0';
    $vPenel  = $jPenel;
    $vPengab = $jPengab;

    $kKuis   = getKetKuis($sKuis);
    $kHadir  = getKetHadir($sHadir);
    $kKonten = getKetKonten($sKonten);
    $kPenel  = $jPenel  >= 1 ? 'Memenuhi' : 'Belum Memenuhi';
    $kPengab = $jPengab >= 1 ? 'Memenuhi' : 'Belum Memenuhi';

    $perbaikan = [];
    foreach (['P1','P2','P3','P4','P5'] as $pk) {
        $v = trim($d[$pk] ?? '');
        $perbaikan[] = ($v !== '' && $v !== '0') ? $v : '';
    }
    while (count($perbaikan) < 5) $perbaikan[] = '';

    $catatan = [];
    foreach (['K1','K2','K3','K4'] as $kk) {
        $v = trim($d[$kk] ?? '');
        $catatan[] = ($v !== '' && $v !== '0') ? $v : '';
    }
    while (count($catatan) < 4) $catatan[] = '';
?>
<div class="page">

  <!-- ROW 5-8: Header (A5:H5 merged, A6:H6, A7:H7, A8:H8) centered -->
  <div class="hdr">
    <div class="r5">LAPORAN EVALUASI TRIDHARMA DOSEN</div>
    <div class="r6"><?= htmlspecialchars(RAPORT_PERIODE) ?></div>
    <div class="r7">NUSA PUTRA UNIVERSITY</div>
    <div class="r8">Jl. Raya Cibolang No. 21, Cibolang Kaler, Cisaat, Cibolang Kaler, Cisaat, Sukabumi, Jawa Barat 43152. Telp. (0266) 210594</div>
  </div>

  <!-- ROW 10: A. IDENTITAS DOSEN (A10:H10 merged, BOLD) -->
  <div class="sec-a">A. IDENTITAS DOSEN</div>

  <!-- ROW 11-15: B=label, C=value (no colon, no border) -->
  <table class="tbl-id">
    <tr><td class="lbl">NAMA DOSEN</td><td><?= htmlspecialchars($nama) ?></td></tr>
    <tr><td class="lbl">PROGRAM STUDI</td><td><?= htmlspecialchars($prodi) ?></td></tr>
    <tr><td class="lbl">JUMLAH MATA KULIAH</td><td><?= (string)$jmlMK ?></td></tr>
    <tr><td class="lbl">JUMLAH KELAS</td><td><?= (string)$jmlKelas ?></td></tr>
    <tr><td class="lbl">JUMLAH RESPONDEN</td><td><?= (string)$jmlResp ?></td></tr>
  </table>

  <!-- ROW 16: B. REKAPITULASI PENILAIAN (A16:H16 merged, BOLD) -->
  <div class="sec-b">B. REKAPITULASI PENILAIAN</div>

  <!-- ROW 17: header | ROW 18-22: data, nilai align=right (sesuai Excel C align=right) -->
  <table class="tbl-rekap">
    <thead>
      <tr>
        <th class="ind">Indikator Penilaian</th>
        <th style="width:13%">Nilai</th>
        <th class="ket">Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <tr><td class="ind">Kuesioner Mahasiswa</td><td class="nilai"><?= $vKuis ?></td><td class="ket"><?= $kKuis ?></td></tr>
      <tr><td class="ind">Kehadiran</td><td class="nilai"><?= $vHadir ?></td><td class="ket"><?= $kHadir ?></td></tr>
      <tr><td class="ind">Kelengkapan Konten Perkuliahan</td><td class="nilai"><?= $vKonten ?></td><td class="ket"><?= $kKonten ?></td></tr>
      <tr><td class="ind">Penelitian</td><td class="nilai"><?= $vPenel ?></td><td class="ket"><?= $kPenel ?></td></tr>
      <tr><td class="ind">Pengabdian</td><td class="nilai"><?= $vPengab ?></td><td class="ket"><?= $kPengab ?></td></tr>
    </tbody>
  </table>

  <!-- ROW 24: C. ASPEK PEMBELAJARAN (A24, BOLD) -->
  <div class="sec-c">C. ASPEK PEMBELAJARAN</div>

  <!-- ROW 25: C1. REKOMENDASI PERBAIKAN (A25, BOLD) -->
  <div class="subsec">C1. REKOMENDASI PERBAIKAN</div>

  <!-- ROW 26-30: A=nomor, B=isi -->
  <table class="aspek-tbl">
    <?php for ($i = 0; $i < 5; $i++): ?>
    <tr>
      <td class="no-col"><?= $i + 1 ?></td>
      <td class="isi-col"><?= htmlspecialchars($perbaikan[$i]) ?></td>
    </tr>
    <?php endfor; ?>
  </table>

  <!-- ROW 32: D. CATATAN (A32, BOLD) -->
  <div class="sec-d">D. CATATAN</div>

  <!-- ROW 33-36: A="-"(BOLD,center), B:D merged=isi -->
  <table class="cat-tbl">
    <?php for ($i = 0; $i < 4; $i++): ?>
    <tr>
      <td class="dash-col">-</td>
      <td class="cat-isi"><?= htmlspecialchars($catatan[$i]) ?></td>
    </tr>
    <?php endfor; ?>
  </table>

  <!-- ROW 39-46: FOOTER sesuai Excel -->
  <div class="footer-wrap">
    <!-- Kiri (col B): B39=UPM, B40=UNIVERSITAS, B46=nama TTD -->
    <div class="footer-left">
      <div class="upm">UNIT PENJAMINAN MUTU</div>
      <div class="univ">UNIVERSITAS NUSA PUTRA</div>
      <div class="ttd-wrap">
        <img src="../assets/img/ttd_samsulpahmi.png" style="height:55px; object-fit:contain; margin-bottom:5px; margin-left:-20px;" alt="TTD">
        <div class="ttd-name">Dr. SAMSUL PAHMI, M.Pd.</div>
      </div>
    </div>
    <!-- Kanan (col C-F): C40=label, tabel kriteria -->
    <div class="footer-right">
      <div class="kr-hd">CATATAN: KRITERIA PENSKORAN</div>
      <table class="tbl-kr">
        <thead>
          <tr>
            <th colspan="2">RENTANG SKOR</th>
            <th>KRITERIA</th>
          </tr>
        </thead>
        <tbody>
          <tr><td class="skor">3.2</td><td class="sd">s/d 3.65</td><td class="ket">Kurang Baik</td></tr>
          <tr><td class="skor">3.66</td><td class="sd">s/d 4.11</td><td class="ket">Cukup</td></tr>
          <tr><td class="skor">4.12</td><td class="sd">s/d 4.57</td><td class="ket">Baik</td></tr>
          <tr><td class="skor">4.58</td><td class="sd">s/d 5.00</td><td class="ket">Sangat Baik</td></tr>
        </tbody>
      </table>
    </div>
  </div>

</div>
<?php endforeach; ?>

</body>
</html>
<?php
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
  <!-- Header + Periode Switcher -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">📋 Raport Laporan Dosen</h1>
      <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Generate surat raport dosen otomatis dari data kuesioner &amp; evaluasi Tridharma</p>
    </div>
    <div class="flex items-center gap-3">
      <!-- Periode Switcher -->
      <div class="flex bg-slate-100 dark:bg-slate-800 rounded-xl p-1 gap-1">
        <a href="raport_dosen.php?periode=gasal"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition-all <?= RAPORT_PERIODE_KEY === 'gasal' ? 'bg-[#8c0c4c] text-white shadow' : 'text-slate-600 dark:text-slate-400 hover:bg-white/60 dark:hover:bg-slate-700' ?>">
          🍂 Gasal 2025-2026
        </a>
        <a href="raport_dosen.php?periode=genap"
           class="px-4 py-2 rounded-lg text-sm font-semibold transition-all <?= RAPORT_PERIODE_KEY === 'genap' ? 'bg-[#8c0c4c] text-white shadow' : 'text-slate-600 dark:text-slate-400 hover:bg-white/60 dark:hover:bg-slate-700' ?>">
          🌸 Genap 2025-2026
        </a>
      </div>
      <!-- Info File -->
      <div class="px-4 py-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm">
        <span class="text-emerald-700 dark:text-emerald-400 font-bold">📁 <?= RAPORT_EXCEL_NAME ?></span>
        <br><span class="text-emerald-600 text-xs"><?= count($allDosen) ?> dosen · <?= RAPORT_PERIODE ?></span>
      </div>
    </div>
  </div>

  <?php if ($hasError): ?>
  <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-2xl p-6 mb-6">
    <p class="text-amber-700 dark:text-amber-400 font-semibold">⚠️ <?= htmlspecialchars($excelData['error'] ?? 'Data tidak tersedia') ?></p>
    <?php if (RAPORT_PERIODE_KEY === 'genap'): ?>
    <p class="text-amber-600 dark:text-amber-500 text-sm mt-2">
      💡 <strong>Genap:</strong> Letakkan file <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">Sistem Report Dosen 2025 - 2026 Genap.xlsx</code>
      di folder <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">Contoh Lampiran/Laporan Raport/</code> untuk menggunakan data Excel penuh.
      Sistem akan otomatis membaca dari PDF kuesioner sebagai sumber data sementara.
    </p>
    <?php endif; ?>
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
      <input type="hidden" name="periode" value="<?= RAPORT_PERIODE_KEY ?>">
      
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
      <a href="raport_dosen.php?periode=<?= RAPORT_PERIODE_KEY ?>" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">
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
                <a href="raport_dosen.php?step=preview&no=<?= $d['No'] ?>&periode=<?= RAPORT_PERIODE_KEY ?>"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 transition-colors">
                   👁️ Preview
                </a>
                <a href="raport_dosen.php?step=print&ids=<?= $d['No'] ?>&periode=<?= RAPORT_PERIODE_KEY ?>" target="_blank"
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
