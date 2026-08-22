<?php
$pageTitle  = 'Raport Laporan Dosen';
$breadcrumb = [['label' => 'Akademik', 'url' => '#'], ['label' => 'Raport Laporan Dosen']];
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
require_once __DIR__ . '/../functions/excel_raport_helper.php';
require_once __DIR__ . '/../functions/pdf_genap_parser.php';
require_once __DIR__ . '/../functions/docx_raport_generator.php';
require_once __DIR__ . '/../functions/ak_xls_parser.php';

/**
 * 5 ASPEK KUESIONER STANDAR - sesuai PDF referensi MSD_Agus Hendriyanto.pdf
 * Digunakan sebagai C1. Rekomendasi Perbaikan jika kolom P1-P5 di Excel kosong
 */
define('ASPEK_KUESIONER_STANDAR', [
    'Kesiapan memberikan kuliah dan/atau praktek/praktikum',
    'Kejelasan penyampaian materi dan jawaban terhadap pertanyaan di kelas',
    'Pemanfaatan media dan teknologi pembelajaran',
    'Keanekaragaman cara pengukuran hasil belajar',
    'Kesesuaian materi ujian dan/atau tugas dengan tujuan mata kuliah',
]);

// ======================================================
// MULTI-PERIODE: Dinamis dari tabel raport_periode
// ======================================================

// Load semua periode dari DB
$allPeriodeDB = dbQuery("SELECT * FROM raport_periode ORDER BY tahun_awal DESC, semester ASC");

// Buat lookup by label
$periodeByLabel = [];
foreach ($allPeriodeDB as $p) {
    $periodeByLabel[$p['label']] = $p;
}

// Default: periode pertama (terbaru)
$defaultPeriode = !empty($allPeriodeDB) ? $allPeriodeDB[0]['label'] : 'Gasal 2025-2026';

// Resolve periodeParam dari GET
$periodeParam = trim($_GET['periode'] ?? '');

// Backward compat: 'gasal' → 'Gasal 2025-2026', 'genap' → 'Genap 2025-2026'
$backwardMap = ['gasal' => 'Gasal 2025-2026', 'genap' => 'Genap 2025-2026'];
if (isset($backwardMap[$periodeParam])) {
    $periodeParam = $backwardMap[$periodeParam];
}

// Validasi: harus ada di DB
if (empty($periodeParam) || !isset($periodeByLabel[$periodeParam])) {
    $periodeParam = $defaultPeriode;
}

$cfg        = $periodeByLabel[$periodeParam] ?? [];
$excelName  = $cfg['excel_file'] ?? '';
$excelPath  = !empty($excelName)
    ? __DIR__ . '/../Contoh Lampiran/Laporan Raport/' . $excelName
    : '';
// PDF hanya untuk Genap 2025-2026 legacy
$pdfPath    = ($cfg['semester'] === 'Genap' && ($cfg['tahun_awal'] ?? 0) === 2025)
    ? __DIR__ . '/../Contoh Lampiran/Laporan Raport/Laporan Rekap Kuesioner 2025 - 2026 Genap.pdf'
    : null;

define('RAPORT_EXCEL_PATH', $excelPath);
define('RAPORT_EXCEL_NAME', $excelName);
define('RAPORT_PERIODE',    $periodeParam);
define('RAPORT_PERIODE_KEY', $periodeParam);

// ======================================================
// SUMBER DATA: Database (utama) atau Excel (fallback)
// ======================================================
$excelData   = [];
$allDosen    = [];
$hasError    = false;
$dataFromDB  = false;

// Cek apakah ada data di database untuk periode ini
$dbCount = dbQueryOne("SELECT COUNT(*) as c FROM raport_dosen_data WHERE periode=?", [$periodeParam]);
$useDB   = ($dbCount['c'] ?? 0) > 0;

if ($useDB) {
    // ── Baca dari DATABASE ─────────────────────────────────────────────────
    $dbRows = dbQuery("SELECT * FROM raport_dosen_data WHERE periode=? ORDER BY no", [$periodeParam]);
    foreach ($dbRows as $dbRow) {
        $allDosen[] = [
            'No'                => $dbRow['no'],
            'Nama'              => $dbRow['nama'],
            'Prodi'             => $dbRow['prodi'],
            'Jumlah Matkul'     => $dbRow['jumlah_matkul'],
            'Jumlah Kelas'      => $dbRow['jumlah_kelas'],
            'Jumlah Responden'  => $dbRow['jumlah_responden'],
            'Nilai Kuesioner'   => $dbRow['nilai_kuesioner'],
            'Jumlah Kehadiran'  => $dbRow['jumlah_kehadiran'],
            'Konten'            => $dbRow['konten'],
            'Jumlah Penelitian' => $dbRow['jumlah_penelitian'],
            'Jumlah Pengabdian' => $dbRow['jumlah_pengabdian'],
            'P1'                => $dbRow['p1'],
            'P2'                => $dbRow['p2'],
            'P3'                => $dbRow['p3'],
            'P4'                => $dbRow['p4'],
            'P5'                => $dbRow['p5'],
            'K1'                => $dbRow['k1'],
            'K2'                => $dbRow['k2'],
            'K3'                => $dbRow['k3'],
            'K4'                => $dbRow['k4'],
        ];
    }
    $dataFromDB = true;
    $hasError   = false;

} else {
    // ── Fallback ke FILE EXCEL ─────────────────────────────────────────────
    if (file_exists(RAPORT_EXCEL_PATH)) {
        $excelData = parseExcelRaport(RAPORT_EXCEL_PATH);
        $allDosen  = $excelData['rows'] ?? [];
        $hasError  = isset($excelData['error']);
    }

    // Untuk Genap: merge data dari PDF jika tersedia
    if (($cfg['semester'] ?? '') === 'Genap' && $pdfPath && file_exists($pdfPath)) {
        require_once __DIR__ . '/../functions/pdf_genap_parser.php';
        $pdfDosen = parseGenapPDF($pdfPath);
        if (!empty($pdfDosen)) {
            if (empty($allDosen)) {
                $allDosen = $pdfDosen;
            } else {
                foreach ($allDosen as &$exRow) {
                    $nama = strtoupper(preg_replace('/\s+/', '', $exRow['Nama'] ?? ''));
                    foreach ($pdfDosen as $pdfRow) {
                        $pdfNama = strtoupper(preg_replace('/\s+/', '', $pdfRow['Nama'] ?? ''));
                        if ($nama === $pdfNama) {
                            if ($pdfRow['Nilai Kuesioner'] > 0) $exRow['Nilai Kuesioner'] = $pdfRow['Nilai Kuesioner'];
                            if ($pdfRow['Jumlah Matkul'] > 0) $exRow['Jumlah Matkul'] = $pdfRow['Jumlah Matkul'];
                            if ($pdfRow['Jumlah Kelas'] > 0) $exRow['Jumlah Kelas'] = $pdfRow['Jumlah Kelas'];
                            for ($i = 1; $i <= 10; $i++) {
                                if (!empty($pdfRow['K'.$i])) $exRow['K'.$i] = $pdfRow['K'.$i];
                            }
                            break;
                        }
                    }
                }
                unset($exRow);
            }
            $hasError = false;
            unset($excelData['error']);
        } else {
            if (empty($allDosen)) {
                $hasError = true;
                $excelData['error'] = 'Gagal mem-parsing PDF Genap, dan Excel Genap kosong.';
            }
        }
    } else {
        if (empty($allDosen)) {
            $hasError = true;
            $excelData['error'] = 'File Excel ' . RAPORT_EXCEL_NAME . ' tidak ditemukan. Silakan <a href="input_raport_dosen.php?periode=' . $periodeParam . '" class="underline text-blue-600">input data via web</a>.';
        }
    }
}

// GASAL: Enrich data dosen semua Prodi dari XLS SIAKAD
// Hanya berlaku untuk periode Gasal 2025-2026 (folder rekap ada)
// ======================================================
if (($cfg['semester'] ?? '') === 'Gasal' && !empty($allDosen)) {
    $rekapPath = __DIR__ . '/../Contoh Lampiran/Laporan Raport/Semester Gasal 2025-2026/Rekap Koesioner';
    if (is_dir($rekapPath)) {
        $allProdiXLS = parseAllFakultasDosen($rekapPath);

        // Mapping: keyword dalam kolom Prodi (lowercase) => key di $allProdiXLS
        // Urutan penting: keyword lebih spesifik harus lebih dulu
        $prodiKeywordMap = [
            // FBHP — S2 lebih spesifik duluan
            's2 hukum'              => 's2 hukum',
            's2 manajemen'          => 's2 manajemen',
            's2 pedagogi'           => 's2 pedagogi',
            'magister manajemen'    => 's2 manajemen',
            'magister pedagogi'     => 's2 pedagogi',
            'magister hukum'        => 's2 hukum',
            'akuntansi'             => 'akuntansi',
            'manajemen'             => 'manajemen',
            'hukum'                 => 'hukum',
            'pgsd'                  => 'pgsd',
            'pedagogi'              => 's2 pedagogi',
            // FECD — Magister lebih spesifik duluan
            'magister informatika'  => 'magister informatika',
            'desain komunikasi'     => 'desain komunikasi visual',
            'dkv'                   => 'desain komunikasi visual',
            'komunikasi visual'     => 'desain komunikasi visual',
            'sistem informasi'      => 'sistem informasi',
            'teknik elektro'        => 'teknik elektro',
            'teknik informatika'    => 'teknik informatika',
            'teknik mesin'          => 'teknik mesin',
            'teknik sipil'          => 'teknik sipil',
        ];

        $prodiKeyToStandard = [
            'akuntansi'               => 'S1 - Akuntansi',
            'manajemen'               => 'S1 - Manajemen',
            'hukum'                   => 'S1 - Hukum',
            'pgsd'                    => 'S1 - Pendidikan Guru Sekolah Dasar',
            's2 hukum'                => 'S2 - Magister Hukum',
            's2 manajemen'            => 'S2 - Magister Manajemen',
            's2 pedagogi'             => 'S2 - Magister Pedagogi',
            'desain komunikasi visual'=> 'S1 - Desain Komunikasi Visual',
            'sistem informasi'        => 'S1 - Sistem Informasi',
            'teknik elektro'          => 'S1 - Teknik Elektro',
            'teknik informatika'      => 'S1 - Teknik Informatika',
            'teknik mesin'            => 'S1 - Teknik Mesin',
            'teknik sipil'            => 'S1 - Teknik Sipil',
            'magister informatika'    => 'S2 - Magister Informatika',
        ];

        // Pre-index flat lookup agar pencocokan 307 dosen berjalan instan O(1)
        $flatXlsDosen = [];
        foreach ($allProdiXLS as $pKey => $dosenMap) {
            foreach ($dosenMap as $normKey => $dosenData) {
                $flatXlsDosen[$normKey] = [
                    'prodiKey' => $pKey,
                    'data'     => $dosenData,
                ];
            }
        }

        foreach ($allDosen as &$exRow) {
            $nama     = $exRow['Nama'] ?? '';
            $normNama = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $nama));

            $prodiRaw = strtolower(trim($exRow['Prodi'] ?? ''));
            $prodiKey = null;

            // Cari key prodi awal dari kolom Excel
            foreach ($prodiKeywordMap as $keyword => $key) {
                if (strpos($prodiRaw, $keyword) !== false) {
                    $prodiKey = $key;
                    break;
                }
            }

            $xlsMatch = null;
            $matchedProdiKey = null;

            // 1. Cek exact normalized match di flat lookup (instan)
            if (isset($flatXlsDosen[$normNama])) {
                $xlsMatch = $flatXlsDosen[$normNama]['data'];
                $matchedProdiKey = $flatXlsDosen[$normNama]['prodiKey'];
            }

            // 2. Coba cari di folder prodi yang sesuai kolom Excel dulu
            if (!$xlsMatch && $prodiKey !== null && !empty($allProdiXLS[$prodiKey])) {
                $xlsMatch = matchProdiDosen($nama, $allProdiXLS[$prodiKey]);
                if ($xlsMatch) $matchedProdiKey = $prodiKey;
            }

            // 3. Jika belum ditemukan, cari fuzzy di seluruh folder prodi XLS lainnya
            if (!$xlsMatch) {
                foreach ($allProdiXLS as $pKey => $dosenMap) {
                    if ($pKey === $prodiKey) continue;
                    $candMatch = matchProdiDosen($nama, $dosenMap);
                    if ($candMatch) {
                        $xlsMatch = $candMatch;
                        $matchedProdiKey = $pKey;
                        break;
                    }
                }
            }

            if (!$xlsMatch) continue;

            // Koreksi nama Program Studi dosen sesuai prodi file kuesionernya
            if ($matchedProdiKey && isset($prodiKeyToStandard[$matchedProdiKey])) {
                $exRow['Prodi'] = $prodiKeyToStandard[$matchedProdiKey];
            }

            // Override nilai kuesioner & responden dari XLS jika Excel kosong atau 0
            if ((float)($exRow['Nilai Kuesioner'] ?? 0) == 0 && $xlsMatch['nilai_kuesioner'] > 0) {
                $exRow['Nilai Kuesioner'] = $xlsMatch['nilai_kuesioner'];
            }
            if (empty(trim($exRow['Jumlah Responden'] ?? '')) && !empty($xlsMatch['jumlah_responden'])) {
                $exRow['Jumlah Responden'] = $xlsMatch['jumlah_responden'];
            }

            // Enrich Rekomendasi (P1-P5): dari aspek paling banyak Biasa/Buruk
            // Hanya isi jika Excel masih kosong
            foreach (['P1','P2','P3','P4','P5'] as $pk) {
                if (empty(trim($exRow[$pk] ?? ''))) {
                    $exRow[$pk] = $xlsMatch[$pk] ?? '';
                }
            }

            // Enrich Catatan Mahasiswa (K1-K4): dari komentar di file XLS
            // Hanya isi jika Excel masih kosong
            foreach (['K1','K2','K3','K4'] as $kk) {
                if (empty(trim($exRow[$kk] ?? ''))) {
                    $exRow[$kk] = $xlsMatch[$kk] ?? '';
                }
            }

            // Simpan data Analisis Sentimen dari seluruh komentar XLS
            if (!empty($xlsMatch['sentimen'])) {
                $exRow['sentimen'] = $xlsMatch['sentimen'];
            }
        }
        unset($exRow);
    }
}


// Format semua nama Prodi ke format resmi berjenjang (e.g. S1 - Akuntansi, S2 - Magister Informatika)
foreach ($allDosen as &$dRef) {
    if (isset($dRef['Prodi'])) {
        $dRef['Prodi'] = formatProdiStandard($dRef['Prodi']);
    }
}
unset($dRef);

// Daftar prodi unik — gabungkan master prodi lengkap (D3, S1, S2, S3) dengan prodi yang ada di data
$masterProdis = function_exists('getAllMasterProgramStudi') ? getAllMasterProgramStudi() : [];
$dataProdis   = array_map('trim', array_column($allDosen, 'Prodi'));
$prodiList    = array_values(array_filter(array_unique(array_merge($masterProdis, $dataProdis))));
sort($prodiList);

// Filter GET
$filterProdi = trim($_GET['prodi'] ?? '');  // trim: Excel kadang simpan prodi dengan trailing space
$filterCari  = trim($_GET['cari'] ?? '');
$step        = $_GET['step'] ?? 'list';
$selectedIds = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

// WIZARD STEPS: Data Dosen → Skor Bobot → Skor → Rata-Rata → Cetak Raport
$wizardSteps = [
    'list'       => ['label' => 'Data Dosen',   'icon' => '1', 'sheet' => 'Sheet: Data Dosen',   'no' => 1],
    'skor_bobot' => ['label' => 'Skor Bobot',    'icon' => '2', 'sheet' => 'Sheet: Skor Bobot',    'no' => 2],
    'skor'       => ['label' => 'Skor',          'icon' => '3', 'sheet' => 'Sheet: Skor',           'no' => 3],
    'rata_rata'  => ['label' => 'Rata-Rata',     'icon' => '4', 'sheet' => 'Sheet: Rata-Rata',      'no' => 4],
    'print'      => ['label' => 'Cetak Raport',  'icon' => '5', 'sheet' => 'Sheet: Rapot',          'no' => 5],
];
$validSteps = array_keys($wizardSteps);
if (!in_array($step, array_merge($validSteps, ['preview','word']))) $step = 'list';

// Load multi-sheet data for wizard (skor_bobot, skor, rata_rata)
$allSheetsData = [];
if (in_array($step, ['skor_bobot','skor','rata_rata'])) {
    $allSheetsData = parseExcelAllSheets(RAPORT_EXCEL_PATH);
}

// Handle Session Overrides untuk Input Manual
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['raport_overrides'])) {
    $_SESSION['raport_overrides'] = [];
}

// Handle POST: Simpan Input Manual atau Reset
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    $actNo = trim($_POST['dosen_no'] ?? '');
    if ($actNo !== '') {
        if ($_POST['action'] === 'save_manual') {
            $_SESSION['raport_overrides'][$actNo] = [
                'Jumlah Matkul'     => trim($_POST['jml_mk'] ?? ''),
                'Jumlah Kelas'      => trim($_POST['jml_kelas'] ?? ''),
                'Jumlah Responden'  => trim($_POST['jml_resp'] ?? ''),
                'Nilai Kuesioner'   => (float)str_replace(',', '.', trim($_POST['nilai_kuis'] ?? '0')),
                'Jumlah Kehadiran'  => (float)str_replace(',', '.', trim($_POST['kehadiran'] ?? '0')),
                'Konten'            => (float)str_replace(',', '.', trim($_POST['konten'] ?? '0')),
                'Jumlah Penelitian' => (int)trim($_POST['penelitian'] ?? '0'),
                'Jumlah Pengabdian' => (int)trim($_POST['pengabdian'] ?? '0'),
                'P1'                => trim($_POST['p1'] ?? ''),
                'P2'                => trim($_POST['p2'] ?? ''),
                'P3'                => trim($_POST['p3'] ?? ''),
                'P4'                => trim($_POST['p4'] ?? ''),
                'P5'                => trim($_POST['p5'] ?? ''),
                'K1'                => trim($_POST['k1'] ?? ''),
                'K2'                => trim($_POST['k2'] ?? ''),
                'K3'                => trim($_POST['k3'] ?? ''),
                'K4'                => trim($_POST['k4'] ?? ''),
                'is_custom'         => true,
            ];
            $redirectStep = $_POST['current_step'] ?? 'skor_bobot';
            header('Location: raport_dosen.php?step=' . urlencode($redirectStep) . '&ids=' . urlencode($actNo) . '&periode=' . urlencode(RAPORT_PERIODE_KEY) . '&msg=saved');
            exit;
        } elseif ($_POST['action'] === 'reset_manual') {
            unset($_SESSION['raport_overrides'][$actNo]);
            $redirectStep = $_POST['current_step'] ?? 'skor_bobot';
            header('Location: raport_dosen.php?step=' . urlencode($redirectStep) . '&ids=' . urlencode($actNo) . '&periode=' . urlencode(RAPORT_PERIODE_KEY) . '&msg=reset');
            exit;
        }
    }
}

// Aplikasikan overrides ke data dosen jika ada
foreach ($allDosen as &$dRef) {
    $dNo = (string)($dRef['No'] ?? '');
    if (isset($_SESSION['raport_overrides'][$dNo])) {
        $ov = $_SESSION['raport_overrides'][$dNo];
        foreach ($ov as $k => $v) {
            $dRef[$k] = $v;
        }
    }
}
unset($dRef);

// Filter dosen — mendukung pencocokan nama prodi berformat standar maupun nama mentah
$filteredDosen = $allDosen;
if ($filterProdi) {
    $normFilter = strtolower(trim($filterProdi));
    $fmtFilter  = formatProdiStandard($filterProdi);
    $filteredDosen = array_filter($filteredDosen, function($d) use ($filterProdi, $fmtFilter, $normFilter) {
        $p = trim($d['Prodi'] ?? '');
        return $p === $filterProdi || $p === $fmtFilter || strtolower($p) === $normFilter;
    });
}
if ($filterCari) {
    $filteredDosen = array_filter($filteredDosen, fn($d) => stripos($d['Nama'], $filterCari) !== false);
}
$filteredDosen = array_values($filteredDosen);

// Jika step=print/word/wizard, ambil dosen yang dipilih (satu dosen)
$selectedDosen = [];
if (in_array($step, ['print','preview','word','skor_bobot','skor','rata_rata']) && !empty($selectedIds)) {
    // Cast semua ke string untuk perbandingan yang aman (Excel No bisa berupa string)
    $selectedIdsStr = array_map('strval', $selectedIds);
    foreach ($allDosen as $d) {
        if (in_array((string)($d['No'] ?? ''), $selectedIdsStr)) {
            $selectedDosen[] = $d;
        }
    }
    if (empty($selectedDosen) && !empty($selectedIds)) {
        // fallback by array index (0-based, No dimulai dari 1)
        foreach ($selectedIds as $idx) {
            if (isset($allDosen[(int)$idx - 1])) {
                $selectedDosen[] = $allDosen[(int)$idx - 1];
            }
        }
    }
}
// Untuk wizard: ambil dosen terpilih (satu)
$wizardDosen = !empty($selectedDosen) ? $selectedDosen[0] : null;

// ── Handler step=word: generate & download DOCX ──
if ($step === 'word') {
    // Tentukan dosen yang akan di-export
    $wordDosen = $selectedDosen;
    if (empty($wordDosen)) {
        // Jika tidak ada ID dipilih, export semua yang ada data
        $wordDosen = array_values(array_filter($filteredDosen, function($d) {
            return (
                (float)($d['Nilai Kuesioner']  ?? 0) > 0 ||
                (float)($d['Jumlah Kehadiran'] ?? 0) > 0 ||
                (float)($d['Konten']           ?? 0) > 0 ||
                (int)($d['Jumlah Penelitian']  ?? 0) > 0 ||
                (int)($d['Jumlah Pengabdian']  ?? 0) > 0
            ) && !empty(trim($d['Nama'] ?? ''));
        }));
        if (empty($wordDosen)) $wordDosen = $filteredDosen;
    }

    // Periode uppercase sesuai Excel
    $periodeDocx = strtoupper(RAPORT_PERIODE);
    $periodeDocx = preg_replace('/\s*-\s*/', '-', $periodeDocx);

    try {
        $docxContent = generateRaportDocx($wordDosen, $periodeDocx);

        // Nama file: satu dosen = nama dosen, banyak = 'Raport_Dosen_[Periode]'
        if (count($wordDosen) === 1) {
            $namaFile = 'Raport_' . preg_replace('/[^A-Za-z0-9_]+/', '_', trim($wordDosen[0]['Nama'] ?? 'Dosen'));
        } else {
            $namaFile = 'Raport_Dosen_' . preg_replace('/[^A-Za-z0-9_]+/', '_', RAPORT_PERIODE);
        }
        $namaFile .= '.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $namaFile . '"');
        header('Content-Length: ' . strlen($docxContent));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        echo $docxContent;
    } catch (Exception $e) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<p style="color:red;font-family:sans-serif">Gagal membuat DOCX: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    exit;
}

// ── HELPER FUNCTIONS – keterangan/kategori (dibutuhkan di semua step) ──
if (!function_exists('getKetKuis')) {
    function getKetKuis(float $s): string {
        if ($s == 0) return '';
        if ($s >= 4.58) return 'Sangat Baik';
        if ($s >= 4.12 && $s < 4.8) return 'Baik';
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
}

/**
 * Cari file TTD berdasarkan nama dosen.
 * Mengembalikan array ['src' => '../TTD Dosen/...'] atau null jika tidak ada.
 */
if (!function_exists('getTtdRaportDosen')) {
    function getTtdRaportDosen(string $namaDosen): ?array {
        // Normalisasi nama: lowercase, hapus gelar & spasi
        $norm = strtolower($namaDosen);

        // Mapping keyword => path relatif (dari folder pages/)
        $mapping = [
            'pahmi'     => '../TTD Dosen/TTD Pak Pahmi.png',
            'samsul'    => '../TTD Dosen/TTD Pak Pahmi.png',
            'dana'      => '../TTD Dosen/TTD Dosen Manajemen/Ttd Dr. Dana.png',
            'hesri'     => '../TTD Dosen/TTD Dosen Manajemen/Ttd Dr. Hesri.png',
            'koesmawan' => '../TTD Dosen/TTD Dosen Manajemen/Ttd Dr. Koesmawan.png',
            'slamet'    => '../TTD Dosen/TTD Dosen Manajemen/Ttd Dr. Slamet.png',
            'yusuf'     => '../TTD Dosen/TTD Dosen Manajemen/Ttd Dr. Yusuf.png',
            'gustian'   => '../TTD Dosen/TTD Dosen Manajemen/Ttd. Dr. Gustian.png',
            'kurniawan' => '../TTD Dosen/TTD Dosen Manajemen/Ttd_Dr_Kurniawan.png',
            'nurhasan'  => '../TTD Dosen/TTD Dosen Manajemen/Ttd_Dr_Nur_Hasan.png',
            'nur hasan' => '../TTD Dosen/TTD Dosen Manajemen/Ttd_Dr_Nur_Hasan.png',
            'hasan'     => '../TTD Dosen/TTD Dosen Manajemen/Ttd_Dr_Nur_Hasan.png',
        ];

        foreach ($mapping as $keyword => $relPath) {
            if (strpos($norm, $keyword) !== false) {
                $absPath = __DIR__ . '/' . $relPath;
                if (file_exists($absPath)) {
                    return ['src' => $relPath];
                }
            }
        }
        return null;
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


// ── Early-exit untuk print/word mode: HARUS sebelum header.php ──
// Jika step=print, output halaman print murni (tanpa admin nav) lalu exit.
if ($step === 'print') {
    // Pass through ke blok print di bawah (tidak include header dulu)
    // Header dihandle inline di dalam blok print
} else {
    require_once __DIR__ . '/../includes/header.php';
}

// Helper: buat URL wizard dengan IDs terpilih
if (!function_exists('wizardUrl')) {
    function wizardUrl(string $step, string $periode, string $ids = ''): string {
        $u = 'raport_dosen.php?step=' . urlencode($step) . '&periode=' . urlencode($periode);
        if ($ids !== '') $u .= '&ids=' . urlencode($ids);
        return $u;
    }
}
?>

<?php if ($step === 'print'): ?>
<!-- ====================== PRINT VIEW ====================== -->
<?php
$printDosen = $selectedDosen;
if (empty($printDosen)) {
    // Saat cetak semua: skip dosen yang semua data numeriknya kosong
    // (baris placeholder/template di Excel yang belum diisi)
    $filteredForPrint = array_filter($filteredDosen, function($d) {
        $hasNumerik = (
            (float)($d['Nilai Kuesioner']   ?? 0) > 0 ||
            (float)($d['Jumlah Kehadiran']  ?? 0) > 0 ||
            (float)($d['Konten']            ?? 0) > 0 ||
            (int)($d['Jumlah Penelitian']  ?? 0) > 0 ||
            (int)($d['Jumlah Pengabdian']  ?? 0) > 0
        );
        return !empty(trim($d['Nama'] ?? '')) && $hasNumerik;
    });
    // Jika ada yang lolos filter, gunakan itu; kalau semuanya kosong, tetap print semua
    $printDosen = !empty($filteredForPrint) ? array_values($filteredForPrint) : $filteredDosen;
}

// Set Judul Dokumen (digunakan browser sebagai nama file default saat 'Simpan sebagai PDF' / Save to PDF)
if (count($printDosen) === 1) {
    $namaDosenPrint = trim($printDosen[0]['Nama'] ?? 'Dosen');
    $pagePrintTitle = 'Raport_' . preg_replace('/[^A-Za-z0-9_]+/', '_', $namaDosenPrint) . '_' . preg_replace('/[^A-Za-z0-9_]+/', '_', RAPORT_PERIODE);
} else {
    $pagePrintTitle = 'Raport_Dosen_' . preg_replace('/[^A-Za-z0-9_]+/', '_', RAPORT_PERIODE);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($pagePrintTitle) ?></title>
<style>
  /* ================================================================
     TEMPLATE SURAT RAPORT DOSEN - SESUAI EXCEL SHEET 'Rapot'
     Print Area Excel: Rapot!$A$2:$I$48
     Row structure: 5-8=Header, 10=SecA, 11-15=Identitas,
     16=SecB, 17-22=Rekap, [23=kosong], 24=SecC, 25=SubC1,
     26-30=Perbaikan, [31=kosong], 32=SecD, 33-36=Catatan,
     [37-38=kosong], 39-46=Footer
     ================================================================ */
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Times New Roman',Times,serif; font-size:9pt; line-height:1.15; color:#000; }

  @page {
    size: A4 portrait;
    /* Excel: margin 0.75in left/right, 1in top/bottom, scale=85% */
    margin: 19mm 14mm 19mm 19mm;
  }

  /* Screen: background abu untuk menampilkan shadow A4 */
  body { background: #e2e8f0; }

  /* Halaman A4 portrait */
  .page {
    width: 210mm;
    min-height: 297mm;
    padding: 19mm 14mm 19mm 19mm;
    margin: 0 auto 10mm;
    page-break-after: always;
    page-break-inside: avoid;
    font-size: 9pt;
    position: relative;
    background: #fff;
    box-shadow: 0 2px 16px rgba(0,0,0,0.12);
  }
  .page:last-child { page-break-after: auto; }

  /* ===== HEADER (Row 5-8) — Excel: A5:H5 merged, center, BOLD ===== */
  .hdr {
    text-align: center;
    padding-bottom: 3px;
    border-bottom: 2pt double #000;
    margin-bottom: 5px;
    line-height: 1.4;
  }
  /* Row 5: LAPORAN EVALUASI TRIDHARMA DOSEN — BOLD sz=9 */
  .hdr .r5 { font-weight: bold; font-size: 9pt; }
  /* Row 6: GASAL 2025-2026 — BOLD sz=9 */
  .hdr .r6 { font-weight: bold; font-size: 9pt; }
  /* Row 7: NUSA PUTRA UNIVERSITY — BOLD sz=9 */
  .hdr .r7 { font-weight: bold; font-size: 9pt; }
  /* Row 8: alamat — sz=9, not bold, border-bottom double (handled by .hdr) */
  .hdr .r8 { font-size: 9pt; }

  /* Section headers — Excel: A10, A16, A24 merged A:H, BOLD sz=9 */
  .sec-a, .sec-b, .sec-c, .sec-d {
    font-weight: bold;
    font-size: 9pt;
    line-height: 16px;
  }
  /* Row gap sesuai Excel: row 23=kosong sebelum C, row 31=kosong sebelum D */
  .sec-b { margin-top: 2px; }
  .sec-c { margin-top: 5px; }
  .sec-d { margin-top: 5px; }
  .subsec { font-weight: bold; font-size: 9pt; line-height: 16px; }

  /* ===== A. IDENTITAS (Row 11-15) =====
     Excel: col A kosong (~4%), B=label (36.5 wide ~34%), C=value rest
     Tidak ada border, tidak ada titik dua */
  .tbl-id { width: 100%; border-collapse: collapse; font-size: 9pt; }
  .tbl-id tr { height: 16px; }
  .tbl-id td { padding: 0 2px; vertical-align: top; font-size: 9pt; line-height: 16px; }
  .tbl-id td.id-spc { width: 4%; }   /* col A kosong */
  .tbl-id td.lbl    { width: 34%; }  /* col B: label */
  .tbl-id td.val    { width: 62%; }  /* col C-H: value */

  /* ===== B. REKAPITULASI (Row 17-22) =====
     Excel: col A kosong, tabel mulai dari B; thin borders; Nilai right-align */
  .rekap-wrap { padding-left: 4%; }
  .tbl-rekap { width: 96%; border-collapse: collapse; font-size: 9pt; }
  .tbl-rekap th {
    border: 0.5pt solid #000;
    padding: 1px 4px;
    font-weight: bold;
    text-align: center;
    font-size: 9pt;
    background: #fff;
    height: 16px;
    line-height: 14px;
  }
  .tbl-rekap td {
    border: 0.5pt solid #000;
    padding: 0px 4px;
    font-size: 9pt;
    vertical-align: middle;
    line-height: 14px;
    height: 16px;
  }
  /* Excel: B=Indikator(36.5), C=Nilai(7.5 right-align), D=Keterangan(22.5) */
  .tbl-rekap .th-ind { width: 58%; text-align: center; }
  .tbl-rekap .th-nil { width: 12%; text-align: center; }
  .tbl-rekap .th-ket { width: 30%; text-align: center; }
  .tbl-rekap td.ind  { width: 58%; text-align: left; }
  .tbl-rekap td.nil  { width: 12%; text-align: right; }
  .tbl-rekap td.ket  { width: 30%; text-align: left; }

  /* ===== C. ASPEK (Row 26-30) =====
     Excel: A=nomor(left), B=teks; border-bottom tipis */
  .aspek-tbl { width: 100%; border-collapse: collapse; font-size: 9pt; }
  .aspek-tbl tr { height: 16px; }
  .aspek-tbl td {
    padding: 1px 2px;
    font-size: 9pt;
    vertical-align: top;
    border-bottom: 0.5pt solid #ccc;
    line-height: 15px;
  }
  .aspek-tbl td.no-col { width: 22px; text-align: left; padding-left: 0; }
  .aspek-tbl td.isi-col {
    text-align: justify;
    text-justify: inter-word;
  }

  /* ===== D. CATATAN (Row 33-36) =====
     Excel: A="-"(centered), B:D merged=teks; border-bottom; baris kosong tetap terlihat */
  .cat-tbl { width: 100%; border-collapse: collapse; font-size: 9pt; }
  .cat-tbl tr { min-height: 18px; }
  .cat-tbl td {
    padding: 2px 3px;
    font-size: 9pt;
    vertical-align: top;
    border-bottom: 0.5pt solid #999;
    height: 18px;
    line-height: 15px;
  }
  .cat-tbl td.cat-isi {
    text-align: justify;
    text-justify: inter-word;
  }
  .cat-row-empty td.cat-isi { color: transparent; }
  .cat-tbl td.dash-col {
    width: 22px;
    text-align: center;
    font-weight: bold;
    color: #000;
    vertical-align: top;
  }

  /* ===== E. KESIMPULAN & SENTIMEN (Row 37-38) ===== */
  .sec-e {
    font-weight: bold;
    font-size: 9pt;
    line-height: 16px;
    margin-top: 5px;
  }
  .tbl-kesimpulan { width: 100%; border-collapse: collapse; font-size: 9pt; margin-top: 2px; }
  .tbl-kesimpulan td {
    padding: 1px 2px;
    font-size: 9pt;
    vertical-align: top;
    line-height: 14px;
  }
  .tbl-kesimpulan td.lbl-kes {
    width: 26%;
    font-weight: bold;
  }
  .tbl-kesimpulan td.sep-kes {
    width: 2%;
    text-align: center;
  }
  .tbl-kesimpulan td.val-kes {
    width: 72%;
  }
  .tbl-kesimpulan td.val-just {
    text-align: justify;
    text-justify: inter-word;
  }
  .st-tag {
    display: inline-block;
    padding: 0 4px;
    border-radius: 3px;
    font-size: 8.5pt;
  }
  .st-pos { background: #dcfce7; color: #15803d; }
  .st-net { background: #f1f5f9; color: #475569; }
  .st-neg { background: #fee2e2; color: #b91c1c; }

  /* ===== FOOTER (Row 39-46) ===== */
  .footer-wrap {
    margin-top: 14px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
  }
  .footer-left {
    flex: 0 0 46%;
    font-size: 9pt;
    text-align: left;
    padding-left: 4%; /* indent kolom B sesuai Excel */
  }
  .footer-left .ttd-block {
    display: inline-block;
    text-align: left;
  }
  .footer-left .upm  { font-size: 9pt; line-height: 1.3; white-space: nowrap; }
  .footer-left .univ { font-size: 9pt; line-height: 1.3; margin-bottom: 2px; white-space: nowrap; }
  .footer-left .ttd-wrap {
    height: 56px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center; /* TTD tepat 100% di tengah blok tanda tangan */
  }
  .footer-left .ttd-wrap img {
    height: 56px;
    object-fit: contain;
  }
  /* Excel B46: Dr. Samsul Pahmi, S.Pd., M.Pd. — BOLD, garis pas selebar nama */
  .footer-left .ttd-name {
    font-weight: bold;
    font-size: 9pt;
    display: block;
    width: 100%;
    text-align: left;
    border-top: 1pt solid #000;
    padding-top: 2px;
    white-space: nowrap;
  }
  .footer-right { flex: 1; font-size: 9pt; }
  .footer-right .kr-hd { font-size: 9pt; margin-bottom: 2px; font-weight: normal; }

  /* ===== TABEL KRITERIA PENSKORAN =====
     Excel: C41:D41 merged="RENTANG SKOR", E41:F41 merged="KRITERIA"
     Data: C=angka, D="s/d X", E=kriteria — 3 kolom */
  .tbl-kr { border-collapse: collapse; font-size: 9pt; }
  .tbl-kr th {
    border: 0.5pt solid #000;
    padding: 1px 4px;
    font-weight: bold;
    text-align: center;
    font-size: 9pt;
    background: #fff;
    height: 16px;
    line-height: 14px;
  }
  .tbl-kr td {
    border: 0.5pt solid #000;
    padding: 1px 5px;
    font-size: 9pt;
    height: 16px;
    line-height: 14px;
    white-space: nowrap;
  }
  .tbl-kr td.skor { text-align: right; width: 38px; min-width: 38px; }
  .tbl-kr td.sd   { text-align: left;  width: 60px; min-width: 60px; }
  .tbl-kr td.ket  { text-align: left;  min-width: 65px; }

  @media print {
    body { background: #fff !important; }
    .no-print { display: none !important; }
    html, body { margin: 0 !important; padding: 0 !important; }
    .page {
      margin: 0 !important;
      box-shadow: none !important;
      width: 100% !important;
      min-height: auto !important;
      page-break-after: always;
      page-break-inside: avoid;
    }
    .page:last-child { page-break-after: auto; }
    /* Baris kosong D.Catatan tetap tampil sebagai garis kosong saat print */
    .cat-row-empty { display: table-row !important; }
    .cat-row-empty td.cat-isi { color: transparent !important; }
    /* Pastikan warna tercetak */
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>
</head>
<body>

<div class="no-print" style="background:#1e293b;color:#fff;padding:10px 20px;display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:100;font-family:sans-serif;">
  <button onclick="window.print()" style="background:#8c0c4c;color:#fff;border:none;padding:8px 18px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:bold;">Cetak / Print PDF</button>
  <?php
    // Buat URL word download dengan parameter yang sama
    $wordUrl = 'raport_dosen.php?step=word&periode=' . urlencode(RAPORT_PERIODE_KEY);
    if (!empty($selectedIds)) $wordUrl .= '&ids=' . urlencode(implode(',', $selectedIds));
  ?>
  <a href="<?= htmlspecialchars($wordUrl) ?>" style="background:#1d4ed8;color:#fff;border:none;padding:8px 18px;border-radius:8px;cursor:pointer;font-size:13px;font-weight:bold;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
    Download Word (.docx)
  </a>
  <span style="font-size:13px;">Raport Laporan Dosen &ndash; <?= RAPORT_PERIODE ?> | <?= count($printDosen) ?> dosen</span>
  <a href="raport_dosen.php?periode=<?= RAPORT_PERIODE_KEY ?>" style="color:#94a3b8;font-size:13px;text-decoration:none;margin-left:auto;">&larr; Kembali ke Daftar</a>
</div>


<?php
// Fungsi getKetKuis/getKetHadir/getKetKonten sudah didefinisikan di atas (global scope)

foreach ($printDosen as $d):
    $nama     = $d['Nama'] ?? '-';
    $prodi    = $d['Prodi'] ?? '-';
    $jmlMK    = $d['Jumlah Matkul'] ?? 0;
    $jmlKelas = $d['Jumlah Kelas'] ?? 0;

    // Jumlah Responden: tampilkan format lengkap sesuai Excel (misal "131 dari 312")
    // Excel menyimpan format "X dari Y" - tampilkan apa adanya
    $rawResp  = trim($d['Jumlah Responden'] ?? '');
    $jmlResp  = $rawResp;

    $sKuis    = round((float)($d['Nilai Kuesioner'] ?? 0), 3);
    $sHadir   = round((float)($d['Jumlah Kehadiran'] ?? 0), 1);
    $sKonten  = round((float)($d['Konten'] ?? 0), 3);
    $jPenel   = (int)($d['Jumlah Penelitian'] ?? 0);
    $jPengab  = (int)($d['Jumlah Pengabdian'] ?? 0);

    // Cek apakah field di Excel benar-benar kosong (string '') atau nol aktual
    $kuisRaw   = trim((string)($d['Nilai Kuesioner']  ?? ''));
    $hadirRaw  = trim((string)($d['Jumlah Kehadiran'] ?? ''));
    $kontenRaw = trim((string)($d['Konten']           ?? ''));
    $penelRaw  = trim((string)($d['Jumlah Penelitian'] ?? ''));
    $pengabRaw = trim((string)($d['Jumlah Pengabdian'] ?? ''));
    $mkRaw     = trim((string)($d['Jumlah Matkul']    ?? ''));
    $kelasRaw  = trim((string)($d['Jumlah Kelas']     ?? ''));

    // Format nilai: gunakan '-' untuk data kosong, angka untuk data terisi
    $vKuis   = ($kuisRaw !== '' && $sKuis > 0)
        ? number_format($sKuis, 2, ',', '.')
        : ($kuisRaw !== '' ? '0' : '-');
    // Kehadiran: tampilkan desimal jika ada (misal 14,2 bukan 14)
    // Gunakan 1 desimal jika ada pecahan, else integer
    $sHadirRounded = round($sHadir, 1);
    $vHadir  = ($hadirRaw !== '' && $sHadirRounded > 0)
        ? ($sHadirRounded == floor($sHadirRounded) ? (string)(int)$sHadirRounded : number_format($sHadirRounded, 1, ',', '.'))
        : ($hadirRaw !== '' ? '0' : '-');
    // Konten: tampilkan 3 desimal jika ada pecahan signifikan, else 2
    $vKonten = ($kontenRaw !== '' && $sKonten > 0)
        ? rtrim(rtrim(number_format($sKonten, 3, ',', '.'), '0'), ',')
        : ($kontenRaw !== '' ? '0' : '-');
    $vPenel  = ($penelRaw !== '') ? (string)$jPenel : '-';
    $vPengab = ($pengabRaw !== '') ? (string)$jPengab : '-';
    // Matkul & Kelas
    $vJmlMK    = ($mkRaw !== '')    ? (string)$jmlMK    : '-';
    $vJmlKelas = ($kelasRaw !== '') ? (string)$jmlKelas : '-';

    $kKuis   = getKetKuis($sKuis);
    $kHadir  = getKetHadir($sHadir);
    $kKonten = getKetKonten($sKonten);
    $kPenel  = ($penelRaw !== '') ? ($jPenel  >= 1 ? 'Memenuhi' : 'Belum Memenuhi') : '';
    $kPengab = ($pengabRaw !== '') ? ($jPengab >= 1 ? 'Memenuhi' : 'Belum Memenuhi') : '';

    // C. ASPEK PEMBELAJARAN - Rekomendasi Perbaikan (P1-P5)
    // Jika data di Excel kosong, gunakan 5 pertanyaan kuesioner standar
    $perbaikan = [];
    foreach (['P1','P2','P3','P4','P5'] as $pk) {
        $v = trim($d[$pk] ?? '');
        $perbaikan[] = ($v !== '' && $v !== '0') ? $v : '';
    }
    $allPerbaikanKosong = empty(array_filter($perbaikan, fn($x) => $x !== ''));
    if ($allPerbaikanKosong) {
        // Gunakan 5 aspek kuesioner standar sebagai rekomendasi perbaikan
        $perbaikan = array_values(ASPEK_KUESIONER_STANDAR);
    }
    while (count($perbaikan) < 5) $perbaikan[] = '';

    // D. CATATAN - Komentar Mahasiswa (K1-K4)
    $catatan = [];
    foreach (['K1','K2','K3','K4'] as $kk) {
        $v = trim($d[$kk] ?? '');
        $catatan[] = ($v !== '' && $v !== '0') ? $v : '';
    }
    while (count($catatan) < 4) $catatan[] = '';

    // E. ANALISIS SENTIMEN & KESIMPULAN MAHASISWA
    $sentimen = getDosenSentimen($d);

    // Periode uppercase sesuai Excel: "GASAL 2025-2026" (tanpa spasi di sekitar -)
    $periodeLabel = strtoupper(RAPORT_PERIODE);
    $periodeLabel = preg_replace('/\s*-\s*/', '-', $periodeLabel);
?>
<div class="page">

  <!-- ROW 5-8: Header — A5:H5 merged, center, BOLD sz=9 -->
  <div class="hdr">
    <div class="r5">LAPORAN EVALUASI TRIDHARMA DOSEN</div>
    <div class="r6"><?= htmlspecialchars($periodeLabel) ?></div>
    <div class="r7">NUSA PUTRA UNIVERSITY</div>
    <div class="r8">Jl. Raya Cibolang No. 21, Cibolang Kaler, Cisaat, Cibolang Kaler, Cisaat, Sukabumi, Jawa Barat 43152. Telp. (0266) 210594</div>
  </div>

  <!-- ROW 10: A. IDENTITAS DOSEN — A10:H10 merged, BOLD -->
  <div class="sec-a">A. IDENTITAS DOSEN</div>

  <!-- ROW 11-15: Excel col A kosong, B=label (34%), C=value (62%) — no border, no colon -->
  <table class="tbl-id">
    <tr><td class="id-spc"></td><td class="lbl">NAMA DOSEN</td><td class="val"><?= htmlspecialchars($nama) ?></td></tr>
    <tr><td class="id-spc"></td><td class="lbl">PROGRAM STUDI</td><td class="val"><?= htmlspecialchars($prodi) ?></td></tr>
    <tr><td class="id-spc"></td><td class="lbl">JUMLAH MATA KULIAH</td><td class="val"><?= htmlspecialchars($vJmlMK) ?></td></tr>
    <tr><td class="id-spc"></td><td class="lbl">JUMLAH KELAS</td><td class="val"><?= htmlspecialchars($vJmlKelas) ?></td></tr>
    <tr><td class="id-spc"></td><td class="lbl">JUMLAH RESPONDEN</td><td class="val"><?= ($jmlResp !== '' ? htmlspecialchars($jmlResp) : '-') ?></td></tr>
  </table>

  <!-- ROW 16: B. REKAPITULASI PENILAIAN — A16:H16 merged, BOLD -->
  <div class="sec-b">B. REKAPITULASI PENILAIAN</div>

  <!-- ROW 17-22: indent col A (~4%), tabel B-D dengan thin borders; Nilai right-align -->
  <div class="rekap-wrap">
    <table class="tbl-rekap">
      <thead>
        <tr>
          <th class="th-ind">Indikator Penilaian</th>
          <th class="th-nil">Nilai</th>
          <th class="th-ket">Keterangan</th>
        </tr>
      </thead>
      <tbody>
        <tr><td class="ind">Kuesioner Mahasiswa</td><td class="nil"><?= $vKuis ?></td><td class="ket"><?= $kKuis ?></td></tr>
        <tr><td class="ind">Kehadiran</td><td class="nil"><?= $vHadir ?></td><td class="ket"><?= $kHadir ?></td></tr>
        <tr><td class="ind">Kelengkapan Konten Perkuliahan</td><td class="nil"><?= $vKonten ?></td><td class="ket"><?= $kKonten ?></td></tr>
        <tr><td class="ind">Penelitian</td><td class="nil"><?= $vPenel ?></td><td class="ket"><?= $kPenel ?></td></tr>
        <tr><td class="ind">Pengabdian</td><td class="nil"><?= $vPengab ?></td><td class="ket"><?= $kPengab ?></td></tr>
      </tbody>
    </table>
  </div>

  <!-- ROW 24: C. ASPEK PEMBELAJARAN — A24, BOLD -->
  <div class="sec-c">C. ASPEK PEMBELAJARAN</div>

  <!-- ROW 25: C1. REKOMENDASI PERBAIKAN — A25, BOLD -->
  <div class="subsec">C1. REKOMENDASI PERBAIKAN</div>

  <!-- ROW 26-30: A=nomor(left), B=teks; border-bottom tipis per baris -->
  <table class="aspek-tbl">
    <?php for ($i = 0; $i < 5; $i++): ?>
    <tr>
      <td class="no-col"><?= $i + 1 ?></td>
      <td class="isi-col"><?= htmlspecialchars($perbaikan[$i]) ?></td>
    </tr>
    <?php endfor; ?>
  </table>

  <!-- ROW 32: D. CATATAN — A32, BOLD -->
  <div class="sec-d">D. CATATAN</div>

  <!-- ROW 33-36: A="-"(centered), B:D merged=isi; border-bottom; baris kosong tetap tampil -->
  <table class="cat-tbl">
    <?php for ($i = 0; $i < 4; $i++):
          $isEmpty = (trim($catatan[$i]) === '');
    ?>
    <tr<?= $isEmpty ? ' class="cat-row-empty"' : '' ?>>
      <td class="dash-col">-</td>
      <td class="cat-isi"><?= $isEmpty ? '&nbsp;' : htmlspecialchars($catatan[$i]) ?></td>
    </tr>
    <?php endfor; ?>
  </table>

  <!-- ROW: E. KESIMPULAN & ANALISIS SENTIMEN MAHASISWA -->
  <div class="sec-e">E. KESIMPULAN &amp; ANALISIS SENTIMEN MAHASISWA</div>
  <table class="tbl-kesimpulan">
    <tr>
      <td class="lbl-kes">Hasil Sentimen Mahasiswa</td>
      <td class="sep-kes">:</td>
      <td class="val-kes">
        <span class="st-tag st-pos">Positif: <strong><?= $sentimen['positif_pct'] ?>%</strong> (<?= $sentimen['positif'] ?> respon)</span> &nbsp;&bull;&nbsp;
        <span class="st-tag st-net">Netral: <strong><?= $sentimen['netral_pct'] ?>%</strong> (<?= $sentimen['netral'] ?> respon)</span> &nbsp;&bull;&nbsp;
        <span class="st-tag st-neg">Negatif: <strong><?= $sentimen['negatif_pct'] ?>%</strong> (<?= $sentimen['negatif'] ?> respon)</span>
      </td>
    </tr>
    <tr>
      <td class="lbl-kes">Kesimpulan Evaluasi</td>
      <td class="sep-kes">:</td>
      <td class="val-kes val-just"><?= htmlspecialchars($sentimen['kesimpulan']) ?></td>
    </tr>
  </table>

  <!-- ROW 39-46: FOOTER sesuai Excel -->
  <div class="footer-wrap">
  <!-- Kiri: B39=UPM, B40=UNIVERSITAS, [TTD image], B46=nama BOLD -->
    <div class="footer-left">
      <div class="ttd-block">
        <div class="upm">UNIT PENJAMINAN MUTU</div>
        <div class="univ">UNIVERSITAS NUSA PUTRA</div>
        <?php
          // TTD footer selalu Dr. Samsul Pahmi sebagai Kepala UPM penandatangan
          $ttdPahmiInfo = getTtdRaportDosen('pahmi');
        ?>
        <?php if ($ttdPahmiInfo): ?>
        <div class="ttd-wrap">
          <img src="<?= htmlspecialchars($ttdPahmiInfo['src']) ?>" alt="TTD Dr. Samsul Pahmi">
        </div>
        <?php else: ?>
        <div class="ttd-wrap" style="height:60px;"></div>
        <?php endif; ?>
        <!-- Excel B46: BOLD, border-top (garis tanda tangan) -->
        <div class="ttd-name">Dr. SAMSUL PAHMI, S.Pd, M.Pd</div>
      </div>
    </div>

    <!-- Kanan: C40="CATATAN: KRITERIA PENSKORAN", tabel 3 kolom -->
    <!-- Excel: C41:D41 merged="RENTANG SKOR", E41:F41 merged="KRITERIA" -->
    <div class="footer-right">
      <div class="kr-hd">CATATAN: KRITERIA PENSKORAN</div>
      <table class="tbl-kr">
        <thead>
          <tr>
            <th colspan="2" style="text-align:center;">RENTANG SKOR</th>
            <th style="text-align:center;">KRITERIA</th>
          </tr>
        </thead>
        <tbody>
          <tr><td class="skor">3,20</td><td class="sd">s/d 3,65</td><td class="ket">Kurang Baik</td></tr>
          <tr><td class="skor">3,66</td><td class="sd">s/d 4,11</td><td class="ket">Cukup</td></tr>
          <tr><td class="skor">4,12</td><td class="sd">s/d 4,57</td><td class="ket">Baik</td></tr>
          <tr><td class="skor">4,58</td><td class="sd">s/d 5,00</td><td class="ket">Sangat Baik</td></tr>
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
      <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-1">Preview Raport Dosen</h2>
      <p class="text-slate-500 dark:text-slate-400 text-sm"><?= htmlspecialchars($previewDosen['Nama']) ?></p>
    </div>
    <div class="flex gap-2">
      <a href="raport_dosen.php" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-semibold text-sm hover:bg-slate-200 transition-colors">← Kembali</a>
      <a href="raport_dosen.php?step=print&ids=<?= $previewDosen['No'] ?>&periode=<?= RAPORT_PERIODE_KEY ?>" target="_blank"
         class="inline-flex items-center gap-2 px-4 py-2 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl font-semibold text-sm transition-all shadow">
        Cetak Raport Ini
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

    <!-- Analisis Sentimen & Kesimpulan -->
    <?php $prevSentimen = getDosenSentimen($previewDosen); ?>
    <div class="border-b border-slate-100 dark:border-slate-700/60 pb-6 mb-6">
      <h3 class="font-bold text-lg text-slate-800 dark:text-white mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-lg bg-[#8c0c4c]/10 text-[#8c0c4c] flex items-center justify-center text-sm font-bold">E</span>
        Kesimpulan &amp; Analisis Sentimen Mahasiswa
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
        <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl border border-emerald-100 dark:border-emerald-800 text-center">
          <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase">Sentimen Positif</div>
          <div class="text-xl font-bold text-emerald-700 dark:text-emerald-300"><?= $prevSentimen['positif_pct'] ?>%</div>
          <div class="text-[11px] text-emerald-600 dark:text-emerald-400"><?= $prevSentimen['positif'] ?> respon</div>
        </div>
        <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600 text-center">
          <div class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">Sentimen Netral</div>
          <div class="text-xl font-bold text-slate-700 dark:text-slate-300"><?= $prevSentimen['netral_pct'] ?>%</div>
          <div class="text-[11px] text-slate-500 dark:text-slate-400"><?= $prevSentimen['netral'] ?> respon</div>
        </div>
        <div class="p-3 bg-red-50 dark:bg-red-900/30 rounded-2xl border border-red-100 dark:border-red-800 text-center">
          <div class="text-xs font-bold text-red-600 dark:text-red-400 uppercase">Sentimen Negatif</div>
          <div class="text-xl font-bold text-red-700 dark:text-red-300"><?= $prevSentimen['negatif_pct'] ?>%</div>
          <div class="text-[11px] text-red-600 dark:text-red-400"><?= $prevSentimen['negatif'] ?> respon</div>
        </div>
      </div>
      <div class="p-4 bg-slate-50 dark:bg-slate-900/40 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs leading-relaxed text-slate-700 dark:text-slate-300 text-justify">
        <strong>Kesimpulan Evaluasi:</strong> <?= htmlspecialchars($prevSentimen['kesimpulan']) ?>
      </div>
    </div>

    <!-- Tombol Cetak -->
    <div class="flex justify-end">
      <a href="raport_dosen.php?step=print&ids=<?= $previewDosen['No'] ?>&periode=<?= RAPORT_PERIODE_KEY ?>" target="_blank"
         class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl font-semibold shadow hover:shadow-md transition-all">
        Cetak Raport Lengkap
      </a>
    </div>
  </div>
</div>
<?php elseif (in_array($step, ['skor_bobot','skor','rata_rata']) && $wizardDosen): ?>
<!-- ====================== WIZARD STEPS (2-4) ====================== -->
<?php
  $idsParam = implode(',', $selectedIds);
  $prevStepMap = ['skor_bobot'=>'list','skor'=>'skor_bobot','rata_rata'=>'skor'];
  $nextStepMap = ['skor_bobot'=>'skor','skor'=>'rata_rata','rata_rata'=>'print'];
  $currentStep = $wizardSteps[$step];
  $prevStep = $prevStepMap[$step];
  $nextStep = $nextStepMap[$step];
?>
<div class="pb-12">
  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
  <div class="mb-4 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-700 flex items-center justify-between text-emerald-800 dark:text-emerald-200">
    <div class="flex items-center gap-3">
      <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
      <span class="text-sm font-semibold">Data perubahan manual berhasil disimpan dan diterapkan pada raport dosen ini.</span>
    </div>
    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
  </div>
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'reset'): ?>
  <div class="mb-4 p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-700 flex items-center justify-between text-blue-800 dark:text-blue-200">
    <div class="flex items-center gap-3">
      <span class="w-2 h-2 rounded-full bg-blue-500"></span>
      <span class="text-sm font-semibold">Data telah dikembalikan ke data asli dari file Excel.</span>
    </div>
    <button onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-700 font-bold">&times;</button>
  </div>
  <?php endif; ?>

  <!-- ======= WIZARD PROGRESS BAR ======= -->
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between gap-1 mb-4">
      <?php foreach ($wizardSteps as $sKey => $sInfo): ?>
      <?php
        $isDone    = $sInfo['no'] < $currentStep['no'];
        $isCurrent = ($sKey === $step);
        $isNext    = $sInfo['no'] > $currentStep['no'];
        $stepColor = $isDone ? 'bg-emerald-500 text-white shadow-sm' : ($isCurrent ? 'bg-[#8c0c4c] text-white shadow-lg ring-4 ring-[#8c0c4c]/20' : 'bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-500 hover:bg-slate-200');
        $lineColor = $isDone ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-700';
        $stepUrl   = ($sKey === 'list')
          ? 'raport_dosen.php?periode=' . urlencode(RAPORT_PERIODE_KEY)
          : (($sKey === 'print')
              ? 'raport_dosen.php?step=print&ids=' . urlencode($idsParam) . '&periode=' . urlencode(RAPORT_PERIODE_KEY)
              : wizardUrl($sKey, RAPORT_PERIODE_KEY, $idsParam));
      ?>
      <div class="flex flex-col items-center flex-1 relative">
        <?php if ($sKey !== 'list'): ?>
        <div class="w-full h-0.5 <?= $lineColor ?> mb-4 -mt-4 relative top-4"></div>
        <?php endif; ?>
        <a href="<?= $stepUrl ?>"
           target="<?= $sKey === 'print' ? '_blank' : '_self' ?>"
           class="group flex flex-col items-center z-10 relative cursor-pointer no-underline text-center">
          <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold <?= $stepColor ?> transition-all group-hover:scale-110">
            <?= $isDone ? '✓' : $sInfo['icon'] ?>
          </div>
          <div class="mt-1.5 text-center">
            <div class="text-[11px] font-bold transition-colors <?= $isCurrent ? 'text-[#8c0c4c] dark:text-pink-400' : ($isDone ? 'text-emerald-600 dark:text-emerald-400 group-hover:text-emerald-700' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-white') ?>">
              <?= $sInfo['label'] ?>
            </div>
            <div class="text-[9px] text-slate-400 dark:text-slate-500 group-hover:text-slate-600"><?= $sInfo['sheet'] ?></div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Dosen selector bar (Pilih Dosen Interaktif) -->
    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="w-8 h-8 rounded-xl bg-[#8c0c4c]/10 text-[#8c0c4c] flex items-center justify-center font-bold text-xs shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div class="flex-1 min-w-[260px]">
          <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Pilih / Ganti Dosen</label>
          <select onchange="window.location.href='raport_dosen.php?step=<?= $step ?>&periode=<?= RAPORT_PERIODE_KEY ?>&ids=' + this.value"
            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-semibold text-slate-800 dark:text-slate-200 focus:outline-none focus:border-[#8c0c4c]">
            <?php foreach ($allDosen as $ad): ?>
            <?php
              $adNo = (string)($ad['No'] ?? '');
              $isSel = ($adNo === (string)($wizardDosen['No'] ?? ''));
              $isMod = isset($_SESSION['raport_overrides'][$adNo]);
            ?>
            <option value="<?= htmlspecialchars($adNo) ?>" <?= $isSel ? 'selected' : '' ?>>
              <?= htmlspecialchars($ad['No'] . '. ' . $ad['Nama'] . ' (' . ($ad['Prodi'] ?: 'Tanpa Prodi') . ')' . ($isMod ? ' [Diedit]' : '')) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if (isset($_SESSION['raport_overrides'][(string)($wizardDosen['No'] ?? '')])): ?>
        <span class="px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 text-[10px] font-bold shrink-0">
          Mode Kustom
        </span>
        <?php endif; ?>
      </div>

      <div class="flex items-center gap-2 w-full md:w-auto justify-end">
        <a href="<?= wizardUrl($prevStep, RAPORT_PERIODE_KEY, $idsParam) ?>" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-semibold text-xs hover:bg-slate-200 transition-colors">← Kembali</a>
        <?php if ($nextStep === 'print'): ?>
        <a href="raport_dosen.php?step=print&ids=<?= urlencode($idsParam) ?>&periode=<?= RAPORT_PERIODE_KEY ?>" target="_blank"
           class="px-5 py-2 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl font-semibold text-xs transition-all shadow flex items-center gap-1.5">
          <span>Cetak Raport A4</span> <span>→</span>
        </a>
        <?php else: ?>
        <a href="<?= wizardUrl($nextStep, RAPORT_PERIODE_KEY, $idsParam) ?>" class="px-5 py-2 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl font-semibold text-xs transition-all shadow flex items-center gap-1.5">
          <span>Lanjut ke <?= $wizardSteps[$nextStep]['label'] ?></span> <span>→</span>
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ======= STEP CONTENT ======= -->
  <?php if ($step === 'skor_bobot'): ?>
  <!-- STEP 2: SKOR BOBOT & INPUT / EDIT MANUAL -->
  <?php
    $skK = round((float)($wizardDosen['Nilai Kuesioner'] ?? 0), 2);
    $skH = (float)($wizardDosen['Jumlah Kehadiran'] ?? 0);
    $skKo = round((float)($wizardDosen['Konten'] ?? 0), 2);
    $jPenel = (int)($wizardDosen['Jumlah Penelitian'] ?? 0);
    $jPengab = (int)($wizardDosen['Jumlah Pengabdian'] ?? 0);
    $dNo = (string)($wizardDosen['No'] ?? '');
    $isOverridden = isset($_SESSION['raport_overrides'][$dNo]);
  ?>
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
      <div>
        <h2 class="font-bold text-lg text-slate-800 dark:text-white">Skor Bobot &amp; Input Penilaian Tridharma</h2>
        <p class="text-xs text-slate-500">Sesuaikan atau input manual data indikator penilaian</p>
      </div>
      <?php if ($isOverridden): ?>
      <form method="POST" onsubmit="return confirm('Kembalikan semua nilai dosen ini ke data asli Excel?')">
        <input type="hidden" name="action" value="reset_manual">
        <input type="hidden" name="dosen_no" value="<?= htmlspecialchars($dNo) ?>">
        <input type="hidden" name="current_step" value="skor_bobot">
        <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-colors">
          Reset ke Data Excel
        </button>
      </form>
      <?php endif; ?>
    </div>

    <!-- FORM INPUT / EDIT NILAI MANUAL -->
    <form method="POST" class="p-6">
      <input type="hidden" name="action" value="save_manual">
      <input type="hidden" name="dosen_no" value="<?= htmlspecialchars($dNo) ?>">
      <input type="hidden" name="current_step" value="skor_bobot">

      <!-- Keep P & K hidden on this step if not modified -->
      <input type="hidden" name="p1" value="<?= htmlspecialchars($wizardDosen['P1'] ?? '') ?>">
      <input type="hidden" name="p2" value="<?= htmlspecialchars($wizardDosen['P2'] ?? '') ?>">
      <input type="hidden" name="p3" value="<?= htmlspecialchars($wizardDosen['P3'] ?? '') ?>">
      <input type="hidden" name="p4" value="<?= htmlspecialchars($wizardDosen['P4'] ?? '') ?>">
      <input type="hidden" name="p5" value="<?= htmlspecialchars($wizardDosen['P5'] ?? '') ?>">
      <input type="hidden" name="k1" value="<?= htmlspecialchars($wizardDosen['K1'] ?? '') ?>">
      <input type="hidden" name="k2" value="<?= htmlspecialchars($wizardDosen['K2'] ?? '') ?>">
      <input type="hidden" name="k3" value="<?= htmlspecialchars($wizardDosen['K3'] ?? '') ?>">
      <input type="hidden" name="k4" value="<?= htmlspecialchars($wizardDosen['K4'] ?? '') ?>">

      <!-- Section Identitas Mengajar -->
      <div class="mb-6 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
        <h4 class="text-xs uppercase font-bold text-slate-500 mb-3">
          Informasi Mengajar &amp; Responden
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Jumlah Mata Kuliah</label>
            <input type="number" name="jml_mk" value="<?= htmlspecialchars($wizardDosen['Jumlah Matkul'] ?? '0') ?>" min="0" max="20"
              class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-[#8c0c4c]">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Jumlah Kelas</label>
            <input type="number" name="jml_kelas" value="<?= htmlspecialchars($wizardDosen['Jumlah Kelas'] ?? '0') ?>" min="0" max="50"
              class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-[#8c0c4c]">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Jumlah Responden (cth: 131 dari 312)</label>
            <input type="text" name="jml_resp" value="<?= htmlspecialchars($wizardDosen['Jumlah Responden'] ?? '') ?>" placeholder="X dari Y"
              class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-[#8c0c4c]">
          </div>
        </div>
      </div>

      <!-- Section Input 5 Indikator Tridharma -->
      <h3 class="font-bold text-slate-800 dark:text-white mb-3 flex items-center justify-between">
        <span>Nilai 5 Indikator Tridharma</span>
        <span class="text-xs text-slate-400 font-normal">Formula aktif otomatis saat nilai diubah</span>
      </h3>

      <div class="overflow-x-auto mb-6">
        <table class="w-full text-sm border-collapse">
          <thead>
            <tr class="bg-slate-100 dark:bg-slate-900">
              <th class="text-left py-3 px-4 font-bold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Indikator Penilaian</th>
              <th class="text-center py-3 px-4 font-bold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 w-36">Input Nilai</th>
              <th class="text-center py-3 px-4 font-bold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 w-24">Bobot</th>
              <th class="text-center py-3 px-4 font-bold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 w-44">Predikat / Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <!-- 1. Kuesioner Mahasiswa -->
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700">
                <div class="font-bold text-slate-800 dark:text-white">1. Kuesioner Mahasiswa</div>
                <div class="text-[11px] text-slate-500">Rentang skor: 3.20 - 5.00</div>
              </td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <input type="number" step="0.01" min="0" max="5.00" name="nilai_kuis" id="input-kuis"
                  value="<?= $skK > 0 ? $skK : '' ?>" placeholder="0.00" oninput="updateLiveKeterangan()"
                  class="w-28 text-center bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl py-1.5 font-bold text-slate-800 dark:text-white focus:border-[#8c0c4c] focus:outline-none">
              </td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center text-slate-500 font-semibold">40%</td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <span id="badge-kuis" class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700"><?= getKetKuis($skK) ?: '–' ?></span>
              </td>
            </tr>

            <!-- 2. Kehadiran Mengajar -->
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700">
                <div class="font-bold text-slate-800 dark:text-white">2. Kehadiran Mengajar</div>
                <div class="text-[11px] text-slate-500">Target: minimal 14 pertemuan</div>
              </td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <input type="number" step="0.1" min="0" max="20" name="kehadiran" id="input-hadir"
                  value="<?= $skH > 0 ? $skH : '' ?>" placeholder="0" oninput="updateLiveKeterangan()"
                  class="w-28 text-center bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl py-1.5 font-bold text-slate-800 dark:text-white focus:border-[#8c0c4c] focus:outline-none">
              </td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center text-slate-500 font-semibold">20%</td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <span id="badge-hadir" class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700"><?= getKetHadir($skH) ?: '–' ?></span>
              </td>
            </tr>

            <!-- 3. Kelengkapan Konten LMS -->
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700">
                <div class="font-bold text-slate-800 dark:text-white">3. Kelengkapan Konten LMS</div>
                <div class="text-[11px] text-slate-500">Rentang skor: 3.20 - 5.00</div>
              </td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <input type="number" step="0.01" min="0" max="5.00" name="konten" id="input-konten"
                  value="<?= $skKo > 0 ? $skKo : '' ?>" placeholder="0.00" oninput="updateLiveKeterangan()"
                  class="w-28 text-center bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl py-1.5 font-bold text-slate-800 dark:text-white focus:border-[#8c0c4c] focus:outline-none">
              </td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center text-slate-500 font-semibold">20%</td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <span id="badge-konten" class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700"><?= getKetKonten($skKo) ?: '–' ?></span>
              </td>
            </tr>

            <!-- 4. Penelitian -->
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700">
                <div class="font-bold text-slate-800 dark:text-white">4. Penelitian</div>
                <div class="text-[11px] text-slate-500">Minimal 1 publikasi / penelitian</div>
              </td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <input type="number" min="0" max="20" name="penelitian" id="input-penel"
                  value="<?= $jPenel ?>" placeholder="0" oninput="updateLiveKeterangan()"
                  class="w-28 text-center bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl py-1.5 font-bold text-slate-800 dark:text-white focus:border-[#8c0c4c] focus:outline-none">
              </td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center text-slate-500 font-semibold">10%</td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <span id="badge-penel" class="px-3 py-1 rounded-full text-xs font-bold <?= $jPenel >= 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>"><?= $jPenel >= 1 ? 'Memenuhi' : 'Belum Memenuhi' ?></span>
              </td>
            </tr>

            <!-- 5. Pengabdian -->
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700">
                <div class="font-bold text-slate-800 dark:text-white">5. Pengabdian Kepada Masyarakat</div>
                <div class="text-[11px] text-slate-500">Minimal 1 kegiatan pengabdian</div>
              </td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <input type="number" min="0" max="20" name="pengabdian" id="input-pengab"
                  value="<?= $jPengab ?>" placeholder="0" oninput="updateLiveKeterangan()"
                  class="w-28 text-center bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl py-1.5 font-bold text-slate-800 dark:text-white focus:border-[#8c0c4c] focus:outline-none">
              </td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center text-slate-500 font-semibold">10%</td>
              <td class="py-3 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <span id="badge-pengab" class="px-3 py-1 rounded-full text-xs font-bold <?= $jPengab >= 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>"><?= $jPengab >= 1 ? 'Memenuhi' : 'Belum Memenuhi' ?></span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between gap-4 pt-4 border-t border-slate-100 dark:border-slate-700">
        <p class="text-xs text-slate-500">
          Klik <strong>Simpan Perubahan</strong> untuk mengaplikasikan nilai ini ke Raport dan cetakan PDF.
        </p>
        <button type="submit" class="px-6 py-2.5 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl font-bold text-xs shadow-md transition-all">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>

  <?php elseif ($step === 'skor'): ?>
  <!-- STEP 3: SKOR & PILIHAN REKOMENDASI (P1-P5) + CATATAN MAHASISWA (K1-K4) -->
  <?php
    $dNo = (string)($wizardDosen['No'] ?? '');
    $rataKriteria = $allSheetsData['rata_rata'] ?? [];
    $skK = round((float)($wizardDosen['Nilai Kuesioner'] ?? 0), 2);
    $katK = getSkorKategori($skK);

    $pValues = [
      $wizardDosen['P1'] ?? '',
      $wizardDosen['P2'] ?? '',
      $wizardDosen['P3'] ?? '',
      $wizardDosen['P4'] ?? '',
      $wizardDosen['P5'] ?? '',
    ];
    // Fallback default rekomendasi jika kosong
    $defaultP = ASPEK_KUESIONER_STANDAR;
    for ($i = 0; $i < 5; $i++) {
      if (empty(trim($pValues[$i]))) {
        $pValues[$i] = $defaultP[$i] ?? '';
      }
    }
    $kValues = [
      $wizardDosen['K1'] ?? '',
      $wizardDosen['K2'] ?? '',
      $wizardDosen['K3'] ?? '',
      $wizardDosen['K4'] ?? '',
    ];
  ?>
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
      <div>
        <h2 class="font-bold text-lg text-slate-800 dark:text-white">Skor &amp; Pilihan Rekomendasi Kuesioner</h2>
        <p class="text-xs text-slate-500">Pilih rekomendasi perbaikan (P1–P5) dari 28 aspek kuesioner atau ketik catatan mahasiswa</p>
      </div>
      <span class="px-3 py-1 rounded-xl bg-blue-100 text-blue-700 font-bold text-xs">
        Skor: <?= $skK > 0 ? number_format($skK, 2) : '–' ?> (<?= $katK['label'] ?>)
      </span>
    </div>

    <!-- FORM PILIHAN & EDIT REKOMENDASI -->
    <form method="POST" class="p-6">
      <input type="hidden" name="action" value="save_manual">
      <input type="hidden" name="dosen_no" value="<?= htmlspecialchars($dNo) ?>">
      <input type="hidden" name="current_step" value="skor">

      <!-- Keep numerical fields -->
      <input type="hidden" name="jml_mk" value="<?= htmlspecialchars($wizardDosen['Jumlah Matkul'] ?? '0') ?>">
      <input type="hidden" name="jml_kelas" value="<?= htmlspecialchars($wizardDosen['Jumlah Kelas'] ?? '0') ?>">
      <input type="hidden" name="jml_resp" value="<?= htmlspecialchars($wizardDosen['Jumlah Responden'] ?? '') ?>">
      <input type="hidden" name="nilai_kuis" value="<?= htmlspecialchars($wizardDosen['Nilai Kuesioner'] ?? '0') ?>">
      <input type="hidden" name="kehadiran" value="<?= htmlspecialchars($wizardDosen['Jumlah Kehadiran'] ?? '0') ?>">
      <input type="hidden" name="konten" value="<?= htmlspecialchars($wizardDosen['Konten'] ?? '0') ?>">
      <input type="hidden" name="penelitian" value="<?= htmlspecialchars($wizardDosen['Jumlah Penelitian'] ?? '0') ?>">
      <input type="hidden" name="pengabdian" value="<?= htmlspecialchars($wizardDosen['Jumlah Pengabdian'] ?? '0') ?>">

      <!-- 5 ASPEK REKOMENDASI PERBAIKAN DENGAN DROPDOWN 28 KRITERIA -->
      <div class="mb-8">
        <h3 class="font-bold text-slate-800 dark:text-white mb-2">
          C1. Rekomendasi Perbaikan (P1 – P5)
        </h3>
        <p class="text-xs text-slate-500 mb-4">
          Pilih aspek dari <strong>dropdown 28 kriteria kuesioner</strong> atau isi manual:
        </p>

        <div class="space-y-3">
          <?php for ($pi = 1; $pi <= 5; $pi++): ?>
          <?php $currentP = $pValues[$pi - 1]; ?>
          <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-900/10 border border-amber-200/70 dark:border-amber-800/60">
            <div class="flex items-center gap-2 mb-2">
              <span class="w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center text-xs font-bold shrink-0"><?= $pi ?></span>
              <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Pilih Aspek Rekomendasi <?= $pi ?>:</label>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <!-- Dropdown 28 Kriteria -->
              <select onchange="document.getElementById('input-p<?= $pi ?>').value = this.value"
                class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-[#8c0c4c]">
                <option value="">-- Pilih dari 28 Kriteria Kuesioner --</option>
                <?php foreach ($rataKriteria as $rk): ?>
                <option value="<?= htmlspecialchars($rk['nama']) ?>" <?= trim($currentP) === trim($rk['nama']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($rk['no'] . '. ' . $rk['nama']) ?>
                </option>
                <?php endforeach; ?>
              </select>

              <!-- Text Input Langsung (bisa diketik kustom) -->
              <input type="text" name="p<?= $pi ?>" id="input-p<?= $pi ?>" value="<?= htmlspecialchars($currentP) ?>" placeholder="Teks aspek rekomendasi..."
                class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 font-semibold focus:outline-none focus:border-[#8c0c4c]">
            </div>
          </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- 4 CATATAN / KOMENTAR MAHASISWA (K1 - K4) -->
      <div class="mb-6">
        <h3 class="font-bold text-slate-800 dark:text-white mb-2">
          D. Catatan &amp; Komentar Mahasiswa (K1 – K4)
        </h3>
        <p class="text-xs text-slate-500 mb-4">
          Tulis atau edit catatan/kesan mahasiswa untuk dosen ini (akan tercetak pada Bagian D Raport):
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <?php for ($ki = 1; $ki <= 4; $ki++): ?>
          <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700">
            <label class="block text-xs font-bold text-slate-500 mb-1">Catatan <?= $ki ?> (K<?= $ki ?>):</label>
            <textarea name="k<?= $ki ?>" rows="2" placeholder="Tulis catatan mahasiswa di sini..."
              class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:border-[#8c0c4c]"><?= htmlspecialchars($kValues[$ki - 1]) ?></textarea>
          </div>
          <?php endfor; ?>
        </div>
      </div>

      <div class="flex items-center justify-between gap-4 pt-4 border-t border-slate-100 dark:border-slate-700">
        <p class="text-xs text-slate-500">
          Rekomendasi &amp; catatan yang dipilih akan tercetak pada raport resmi.
        </p>
        <button type="submit" class="px-6 py-2.5 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl font-bold text-xs shadow-md transition-all">
          Simpan Rekomendasi &amp; Catatan
        </button>
      </div>
    </form>
  </div>


  <?php elseif ($step === 'rata_rata'): ?>
  <!-- STEP 4: RATA-RATA -->
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
      <h2 class="font-bold text-lg text-slate-800 dark:text-white">Rata-Rata Kriteria Kuesioner</h2>
      <p class="text-sm text-slate-500">28 kriteria aspek penilaian kuesioner</p>
    </div>
    <div class="p-6">
      <?php
        $rataData = $allSheetsData['rata_rata'] ?? [];
        // Skor rata-rata dosen untuk referensi
        $skorRef = round((float)($wizardDosen['Nilai Kuesioner'] ?? 0), 2);
      ?>
      <!-- Info skor -->
      <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800 mb-6">
        <div class="font-bold text-emerald-800 dark:text-emerald-200">Nilai Kuesioner: <span class="text-2xl font-black"><?= $skorRef > 0 ? number_format($skorRef, 2) : '–' ?></span></div>
        <div class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">Nilai ini merupakan rata-rata tertimbang dari 28 kriteria di bawah ini</div>
      </div>
      <!-- Tabel 28 Kriteria -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50">
              <th class="text-center py-3 px-4 font-semibold text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 w-12">No</th>
              <th class="text-left py-3 px-4 font-semibold text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Kriteria Penilaian</th>
              <th class="text-center py-3 px-4 font-semibold text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 w-24">Rata-Rata</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rataData as $kr): ?>
            <?php
              $rataVal = $kr['rata'];
              $katRata = $rataVal !== null ? getSkorKategori($rataVal) : null;
              $bgRow = '';
              if ($rataVal !== null) {
                  $bgRow = $rataVal >= 4.58 ? 'bg-emerald-50/50 dark:bg-emerald-900/10' :
                           ($rataVal >= 4.12 ? 'bg-blue-50/50 dark:bg-blue-900/10' :
                           ($rataVal >= 3.66 ? 'bg-amber-50/50 dark:bg-amber-900/10' : 'bg-red-50/50 dark:bg-red-900/10'));
              }
            ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 <?= $bgRow ?>">
              <td class="py-2.5 px-4 border border-slate-200 dark:border-slate-700 text-center text-slate-500 font-mono"><?= htmlspecialchars($kr['no']) ?></td>
              <td class="py-2.5 px-4 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200"><?= htmlspecialchars($kr['nama']) ?></td>
              <td class="py-2.5 px-4 border border-slate-200 dark:border-slate-700 text-center">
                <?php if ($rataVal !== null): ?>
                <span class="px-2 py-0.5 rounded text-xs font-bold <?= ['Sangat Baik'=>'bg-emerald-100 text-emerald-700','Baik'=>'bg-blue-100 text-blue-700','Cukup'=>'bg-amber-100 text-amber-700','Kurang Baik'=>'bg-red-100 text-red-700'][$katRata['label']] ?? 'bg-slate-100 text-slate-600' ?>"><?= number_format($rataVal, 2) ?></span>
                <?php else: ?>
                <span class="text-slate-300 text-xs">–</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="mt-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-xs text-slate-500">
        <strong>Catatan:</strong> Nilai rata-rata dihitung dari data kuesioner mahasiswa sesuai Sheet "Rata-Rata" di file Excel.
        Tanda (–) berarti data belum tersedia atau belum dihitung untuk dosen ini.
      </div>
    </div>
  </div>
  <?php endif; // end wizard step content ?>
</div>

<?php else: ?>
<!-- ====================== LIST VIEW ====================== -->
<div class="pb-10">
  <!-- Header + Periode Switcher -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
    <div>
      <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Raport Laporan Dosen</h1>
      <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Generate surat raport dosen dari data kuesioner &amp; evaluasi Tridharma</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <!-- Periode Switcher — dinamis dari DB -->
      <?php
        $showAll   = count($allPeriodeDB) <= 6;
        $displayed = $showAll ? $allPeriodeDB : array_slice($allPeriodeDB, 0, 6);
      ?>
      <div class="flex flex-wrap gap-1">
        <?php foreach ($displayed as $ap):
          $isActive = ($ap['label'] === RAPORT_PERIODE_KEY);
          $dosenCnt = (int)(dbQueryOne("SELECT COUNT(*) as c FROM raport_dosen_data WHERE periode=?", [$ap['label']])['c'] ?? 0);
        ?>
        <a href="raport_dosen.php?periode=<?= urlencode($ap['label']) ?>"
           class="flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all <?= $isActive ? 'bg-[#8c0c4c] text-white shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' ?>">
          <?= e($ap['label']) ?>
          <?php if ($dosenCnt > 0): ?>
          <span class="px-1 py-0.5 rounded-full text-[10px] font-bold <?= $isActive ? 'bg-white/30 text-white' : 'bg-slate-300 dark:bg-slate-600 text-slate-600 dark:text-slate-200' ?>"><?= $dosenCnt ?></span>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
        <?php if (!$showAll): ?>
        <div class="relative group">
          <button class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400">+<?= count($allPeriodeDB)-6 ?> lainnya ▾</button>
          <div class="absolute top-full right-0 mt-1 hidden group-hover:block bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-100 dark:border-slate-700 py-1 z-20 min-w-40">
            <?php foreach (array_slice($allPeriodeDB, 6) as $ap2): ?>
            <a href="raport_dosen.php?periode=<?= urlencode($ap2['label']) ?>"
               class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 <?= $ap2['label']===RAPORT_PERIODE_KEY?'text-[#8c0c4c] font-bold':'' ?>">
              <?= e($ap2['label']) ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <a href="input_raport_dosen.php" class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl text-xs font-semibold border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition-colors">+ Periode</a>
      </div>
      <div class="flex items-center gap-2">
        <a href="input_raport_dosen.php?periode=<?= urlencode(RAPORT_PERIODE_KEY) ?>"
           class="flex items-center gap-1.5 px-3 py-2 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl text-sm font-semibold transition-colors shadow-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          Input Data
        </a>
        <?php if ($dataFromDB): ?>
        <div class="px-3 py-2 bg-blue-50 dark:bg-blue-900/30 rounded-xl border border-blue-200 dark:border-blue-800 text-sm">
          <span class="text-blue-700 dark:text-blue-400 font-bold">📊 Database</span>
          <br><span class="text-blue-600 text-xs"><?= count($allDosen) ?> dosen</span>
        </div>
        <?php else: ?>
        <div class="px-3 py-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl border border-emerald-200 dark:border-emerald-800 text-sm">
          <span class="text-emerald-700 dark:text-emerald-400 font-bold">📁 Excel</span>
          <br><span class="text-emerald-600 text-xs"><?= count($allDosen) ?> dosen</span>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- ======= WIZARD FLOW INDICATOR (Step 1 aktif) ======= -->
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/60 shadow-sm p-5 mb-6">
    <div class="flex items-center justify-between gap-1">
      <?php foreach ($wizardSteps as $sKey => $sInfo): ?>
      <?php
        $isListActive = ($sKey === 'list');
        $circleClass  = $isListActive
          ? 'bg-[#8c0c4c] text-white shadow-lg ring-4 ring-[#8c0c4c]/20'
          : 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600';
        $lineClass = 'bg-slate-200 dark:bg-slate-700';
        $lblClass  = $isListActive ? 'text-[#8c0c4c] dark:text-pink-400 font-bold' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-white';
        $stepUrl   = ($sKey === 'list')
          ? 'raport_dosen.php?periode=' . urlencode(RAPORT_PERIODE_KEY)
          : (($sKey === 'print')
              ? 'raport_dosen.php?step=print&periode=' . urlencode(RAPORT_PERIODE_KEY)
              : 'raport_dosen.php?step=' . urlencode($sKey) . '&periode=' . urlencode(RAPORT_PERIODE_KEY));
      ?>
      <div class="flex flex-col items-center flex-1 relative">
        <?php if ($sKey !== 'list'): ?>
        <div class="w-full h-0.5 <?= $lineClass ?> mb-4 -mt-4 relative top-4"></div>
        <?php endif; ?>
        <a href="<?= $stepUrl ?>"
           target="<?= $sKey === 'print' ? '_blank' : '_self' ?>"
           class="group flex flex-col items-center z-10 relative cursor-pointer no-underline text-center">
          <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold <?= $circleClass ?> transition-all group-hover:scale-110">
            <?= $sInfo['icon'] ?>
          </div>
          <div class="mt-1.5 text-center">
            <div class="text-[11px] <?= $lblClass ?> transition-colors"><?= $sInfo['label'] ?></div>
            <div class="text-[9px] text-slate-400 dark:text-slate-500 group-hover:text-slate-600"><?= $sInfo['sheet'] ?></div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="text-xs text-slate-500 dark:text-slate-400 text-center mt-3">
      <strong>Langkah 1 dari 5:</strong> Pilih satu dosen lalu klik <strong>"Lihat Raport"</strong> untuk memulai alur tahapan raport sesuai sheet Excel
    </p>
  </div>

  <?php if ($hasError): ?>
  <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-2xl p-6 mb-6">
    <p class="text-amber-700 dark:text-amber-400 font-semibold"><?= $excelData['error'] ?? 'Data tidak tersedia' ?></p>
    <?php if (RAPORT_PERIODE_KEY === 'genap'): ?>
    <p class="text-amber-600 dark:text-amber-500 text-sm mt-2">
      <strong>Genap:</strong> Letakkan file <code class="bg-amber-100 dark:bg-amber-900 px-1 rounded">Sistem Report Dosen 2025 - 2026 Genap.xlsx</code>
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
      ['label' => 'Total Dosen', 'value' => $totalDosen, 'bg' => 'bg-indigo-50 dark:bg-indigo-900/30', 'text' => 'text-indigo-700 dark:text-indigo-300'],
      ['label' => 'Sangat Baik', 'value' => $dosenSB, 'bg' => 'bg-emerald-50 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-300'],
      ['label' => 'Baik', 'value' => $dosenBaik, 'bg' => 'bg-blue-50 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300'],
      ['label' => 'Perlu Perhatian', 'value' => $dosenCukup + $dosenKurang, 'bg' => 'bg-amber-50 dark:bg-amber-900/30', 'text' => 'text-amber-700 dark:text-amber-300'],
    ];
    ?>
    <?php foreach ($stats as $s): ?>
    <div class="<?= $s['bg'] ?> rounded-2xl p-4 border border-white/60 dark:border-white/5">
      <div class="text-2xl font-bold <?= $s['text'] ?>"><?= $s['value'] ?></div>
      <div class="text-xs font-semibold <?= $s['text'] ?> opacity-80 mt-1"><?= $s['label'] ?></div>
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

      <div class="min-w-[240px]">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5">Filter Prodi</label>
        <select name="prodi" onchange="document.getElementById('filter-form').submit()"
          class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c]">
          <option value="">Semua Prodi</option>
          <?php foreach ($prodiList as $p): ?>
          <option value="<?= htmlspecialchars($p) ?>" <?= ($filterProdi === $p || formatProdiStandard($filterProdi) === $p) ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="px-5 py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-sm font-semibold hover:bg-slate-700 transition-colors">
        Cari
      </button>

      <?php if ($filterProdi || $filterCari): ?>
      <a href="raport_dosen.php?periode=<?= RAPORT_PERIODE_KEY ?>" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">
        Reset
      </a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Toolbar Batch -->
  <div class="bg-gradient-to-r from-[#8c0c4c] to-[#6d0039] rounded-2xl p-4 mb-4 flex items-center justify-between" id="batch-toolbar" style="display:none!important">
    <div class="flex items-center gap-3 text-white">
      <div>
        <div class="font-bold text-base" id="selected-count-text">0 dosen dipilih</div>
        <div class="text-xs opacity-80">Pilih dosen dari tabel untuk generate raport</div>
      </div>
    </div>
    <div class="flex gap-2">
      <button onclick="printSelected()" class="px-5 py-2 bg-white text-[#8c0c4c] rounded-xl text-sm font-bold hover:bg-slate-100 transition-colors shadow">
        Cetak Terpilih
      </button>
      <button onclick="wordSelected()" class="px-5 py-2 bg-blue-600 text-white border border-blue-400 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
        Word Terpilih
      </button>
      <button onclick="printAll()" class="px-5 py-2 bg-white/20 text-white border border-white/30 rounded-xl text-sm font-semibold hover:bg-white/30 transition-colors">
        Cetak Semua (<?= count($filteredDosen) ?>)
      </button>
      <button onclick="wordAll()" class="px-5 py-2 bg-blue-800/70 text-white border border-blue-300/30 rounded-xl text-sm font-semibold hover:bg-blue-800 transition-colors">
        Word Semua
      </button>
    </div>
  </div>

  <!-- Button Cetak Semua (always visible) -->
  <div class="flex justify-between items-center mb-3">
    <p class="text-sm text-slate-500 dark:text-slate-400">
      Menampilkan <strong class="text-slate-700 dark:text-slate-200"><?= count($filteredDosen) ?></strong> dari <strong><?= $totalDosen ?></strong> dosen
    </p>
    <div class="flex gap-2 flex-wrap">
      <button onclick="selectAll()" id="btn-select-all" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">
        Pilih Semua
      </button>
      <button onclick="printAll()" class="inline-flex items-center gap-2 px-5 py-2 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl text-sm font-semibold transition-all shadow">
        Cetak Semua (<?= count($filteredDosen) ?>)
      </button>
      <button onclick="wordAll()" class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-all shadow">
        Word Semua (<?= count($filteredDosen) ?>)
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
              <div class="font-medium text-sm">Tidak ada data dosen yang ditemukan</div>
            </td>
          </tr>
          <?php else: foreach ($filteredDosen as $idx => $d):
            $skor  = (float)($d['Nilai Kuesioner'] ?? 0);
            $kat   = getSkorKategori($skor);
            $hadir = (float)($d['Jumlah Kehadiran'] ?? 0);
            $katHC = $hadir >= 16 ? 'Memenuhi' : ($hadir >= 14 ? 'Cukup' : ($hadir > 0 ? 'Kurang' : '-'));
            $pen   = (int)($d['Jumlah Penelitian'] ?? 0);
            $peng  = (int)($d['Jumlah Pengabdian'] ?? 0);

            // Tandai baris yang tidak punya data numerik (baris placeholder Excel)
            $dataLengkap = (
                $skor > 0 || $hadir > 0 ||
                (float)($d['Konten'] ?? 0) > 0 ||
                $pen > 0 || $peng > 0
            );

            $badgeColors = [
              'Sangat Baik' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
              'Baik'        => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
              'Cukup'       => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
              'Kurang Baik' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
            ];
            $bc = $badgeColors[$kat['label']] ?? 'bg-slate-100 text-slate-600';
          ?>
          <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors group<?= !$dataLengkap ? ' opacity-60' : '' ?>" data-no="<?= htmlspecialchars($d['No']) ?>">
            <td class="py-3.5 px-4">
              <input type="checkbox" name="dosen_check" value="<?= htmlspecialchars($d['No']) ?>"
                class="dosen-checkbox rounded cursor-pointer w-4 h-4 accent-[#8c0c4c]"
                onchange="updateBatchToolbar()">
            </td>
            <td class="py-3.5 px-4 text-slate-400 font-mono text-xs"><?= $d['No'] ?></td>
            <td class="py-3.5 px-4">
              <div class="font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($d['Nama'] ?? '-') ?></div>
              <?php if (!$dataLengkap): ?>
              <div class="text-[10px] text-amber-600 font-semibold mt-0.5">Data belum lengkap</div>
              <?php endif; ?>
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
              <div class="flex items-center justify-end gap-1.5 flex-wrap">
                <!-- Lihat Raport → mulai wizard dari step skor_bobot -->
                <a href="raport_dosen.php?step=skor_bobot&ids=<?= $d['No'] ?>&periode=<?= RAPORT_PERIODE_KEY ?>"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#8c0c4c] text-white hover:bg-[#a3155b] transition-colors shadow-sm">
                   Lihat Raport
                </a>
                <a href="raport_dosen.php?step=print&ids=<?= $d['No'] ?>&periode=<?= RAPORT_PERIODE_KEY ?>" target="_blank"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-300 transition-colors"
                   title="Langsung cetak tanpa wizard">
                   Cetak
                </a>
                <a href="raport_dosen.php?step=word&ids=<?= $d['No'] ?>&periode=<?= RAPORT_PERIODE_KEY ?>"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-600/10 text-blue-700 hover:bg-blue-600/20 dark:bg-blue-600/20 dark:text-blue-300 transition-colors">
                   Word
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
// ===== Live Formula / Keterangan Calculator (Mirip Formula Excel) =====
function updateLiveKeterangan() {
  // 1. Kuesioner
  const kInput = document.getElementById('input-kuis');
  const kBadge = document.getElementById('badge-kuis');
  if (kInput && kBadge) {
    const k = parseFloat(kInput.value) || 0;
    if (k >= 4.58) {
      kBadge.textContent = 'Sangat Baik';
      kBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    } else if (k >= 4.12 && k < 4.8) {
      kBadge.textContent = 'Baik';
      kBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';
    } else if (k >= 3.66 && k < 4.12) {
      kBadge.textContent = 'Cukup';
      kBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
    } else if (k > 0) {
      kBadge.textContent = 'Kurang Baik';
      kBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
    } else {
      kBadge.textContent = '–';
      kBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500';
    }
  }

  // 2. Kehadiran
  const hInput = document.getElementById('input-hadir');
  const hBadge = document.getElementById('badge-hadir');
  if (hInput && hBadge) {
    const h = parseFloat(hInput.value) || 0;
    if (h >= 14) {
      hBadge.textContent = 'Sudah Memenuhi';
      hBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';
    } else if (h > 0) {
      hBadge.textContent = 'Belum Memenuhi';
      hBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
    } else {
      hBadge.textContent = '–';
      hBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500';
    }
  }

  // 3. Konten LMS
  const koInput = document.getElementById('input-konten');
  const koBadge = document.getElementById('badge-konten');
  if (koInput && koBadge) {
    const ko = parseFloat(koInput.value) || 0;
    if (ko >= 4.58) {
      koBadge.textContent = 'Sangat Baik';
      koBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    } else if (ko >= 4.12 && ko < 4.57) {
      koBadge.textContent = 'Baik';
      koBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';
    } else if (ko >= 3.66 && ko < 4.12) {
      koBadge.textContent = 'Cukup';
      koBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
    } else if (ko > 0) {
      koBadge.textContent = 'Kurang';
      koBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
    } else {
      koBadge.textContent = '–';
      koBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500';
    }
  }

  // 4. Penelitian
  const pInput = document.getElementById('input-penel');
  const pBadge = document.getElementById('badge-penel');
  if (pInput && pBadge) {
    const p = parseInt(pInput.value, 10) || 0;
    if (p >= 1) {
      pBadge.textContent = 'Memenuhi';
      pBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    } else {
      pBadge.textContent = 'Belum Memenuhi';
      pBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
    }
  }

  // 5. Pengabdian
  const pgInput = document.getElementById('input-pengab');
  const pgBadge = document.getElementById('badge-pengab');
  if (pgInput && pgBadge) {
    const pg = parseInt(pgInput.value, 10) || 0;
    if (pg >= 1) {
      pgBadge.textContent = 'Memenuhi';
      pgBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    } else {
      pgBadge.textContent = 'Belum Memenuhi';
      pgBadge.className = 'px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
    }
  }
}

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

function getCheckedIds() {
  return Array.from(document.querySelectorAll('.dosen-checkbox:checked')).map(c => c.value).join(',');
}
function getAllIds() {
  return Array.from(document.querySelectorAll('#dosen-tbody tr[data-no]')).map(r => r.getAttribute('data-no')).join(',');
}

function printSelected() {
  const ids = getCheckedIds();
  if (!ids) { alert('Pilih minimal satu dosen terlebih dahulu.'); return; }
  window.open('raport_dosen.php?step=print&ids=' + encodeURIComponent(ids) + '&periode=<?= RAPORT_PERIODE_KEY ?>', '_blank');
}
function printAll() {
  const ids = getAllIds();
  if (!ids) { alert('Tidak ada data untuk dicetak.'); return; }
  window.open('raport_dosen.php?step=print&ids=' + encodeURIComponent(ids) + '&periode=<?= RAPORT_PERIODE_KEY ?>', '_blank');
}
function wordSelected() {
  const ids = getCheckedIds();
  if (!ids) { alert('Pilih minimal satu dosen terlebih dahulu.'); return; }
  window.location.href = 'raport_dosen.php?step=word&ids=' + encodeURIComponent(ids) + '&periode=<?= RAPORT_PERIODE_KEY ?>';
}
function wordAll() {
  const ids = getAllIds();
  if (!ids) { alert('Tidak ada data untuk didownload.'); return; }
  window.location.href = 'raport_dosen.php?step=word&ids=' + encodeURIComponent(ids) + '&periode=<?= RAPORT_PERIODE_KEY ?>';
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
