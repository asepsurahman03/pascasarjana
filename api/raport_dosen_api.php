<?php
/**
 * api/raport_dosen_api.php
 * Backend API untuk input/edit/delete/export/import data Raport Dosen
 * Mendukung multi-periode (Gasal/Genap multi-tahun)
 */
require_once __DIR__ . '/../includes/functions.php';

// Selalu JSON untuk semua response API
header('Content-Type: application/json; charset=utf-8');

// Auth check khusus AJAX — kembalikan JSON 401, bukan redirect HTML
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesi habis. Silakan login ulang.']);
    exit;
}
if (!isAdminOrKaprodi()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses tidak diizinkan.']);
    exit;
}

$action  = $_REQUEST['action']  ?? '';
$periode = trim($_REQUEST['periode'] ?? 'Gasal 2025-2026');
if (empty($periode)) $periode = 'Gasal 2025-2026';

// ─── Helper ─────────────────────────────────────────────────────────────────

function sendJson(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function sanitizeRow(array $p): array {
    return [
        'no'               => (int)($p['no'] ?? 0),
        'nama'             => trim($p['nama'] ?? ''),
        'prodi'            => trim($p['prodi'] ?? ''),
        'jumlah_matkul'    => (int)($p['jumlah_matkul'] ?? 0),
        'jumlah_kelas'     => (int)($p['jumlah_kelas'] ?? 0),
        'jumlah_responden' => trim($p['jumlah_responden'] ?? ''),
        'nilai_kuesioner'  => (float)str_replace(',', '.', $p['nilai_kuesioner'] ?? 0),
        'jumlah_kehadiran' => (float)str_replace(',', '.', $p['jumlah_kehadiran'] ?? 0),
        'konten'           => (float)str_replace(',', '.', $p['konten'] ?? 0),
        'jumlah_penelitian'=> (int)($p['jumlah_penelitian'] ?? 0),
        'jumlah_pengabdian'=> (int)($p['jumlah_pengabdian'] ?? 0),
        'p1'               => trim($p['p1'] ?? ''),
        'p2'               => trim($p['p2'] ?? ''),
        'p3'               => trim($p['p3'] ?? ''),
        'p4'               => trim($p['p4'] ?? ''),
        'p5'               => trim($p['p5'] ?? ''),
        'k1'               => trim($p['k1'] ?? ''),
        'k2'               => trim($p['k2'] ?? ''),
        'k3'               => trim($p['k3'] ?? ''),
        'k4'               => trim($p['k4'] ?? ''),
    ];
}

// ─── DOSEN MASTER DATA ───────────────────────────────────────────────────────

// GET: semua prodi S1+S2+S3+D3 (lengkap untuk dropdown)
if ($action === 'get_all_prodis') {
    require_once __DIR__ . '/../functions/excel_raport_helper.php';
    $masterList = function_exists('getAllMasterProgramStudi') ? getAllMasterProgramStudi() : [];
    $dbProdis   = dbQuery("SELECT id, jenjang, nama, CONCAT(jenjang,' - ',nama) as label FROM prodi ORDER BY jenjang, nama");

    $combined = [];
    foreach ($masterList as $pStr) {
        $parts   = explode(' - ', $pStr, 2);
        $jenjang = count($parts) === 2 ? trim($parts[0]) : 'S1';
        $nama    = count($parts) === 2 ? trim($parts[1]) : $pStr;
        $combined[$pStr] = ['jenjang' => $jenjang, 'nama' => $nama, 'label' => $pStr];
    }
    foreach ($dbProdis as $dp) {
        $std     = function_exists('formatProdiStandard') ? formatProdiStandard($dp['label']) : $dp['label'];
        $parts   = explode(' - ', $std, 2);
        $jenjang = count($parts) === 2 ? trim($parts[0]) : $dp['jenjang'];
        $nama    = count($parts) === 2 ? trim($parts[1]) : $dp['nama'];
        $combined[$std] = ['jenjang' => $jenjang, 'nama' => $nama, 'label' => $std];
    }

    $out = array_values($combined);
    usort($out, function($a, $b) {
        if ($a['jenjang'] !== $b['jenjang']) return strcmp($a['jenjang'], $b['jenjang']);
        return strcmp($a['nama'], $b['nama']);
    });
    sendJson(['success' => true, 'rows' => $out]);
}

// GET: semua dosen (untuk autocomplete awal)
if ($action === 'get_all_dosen') {
    require_once __DIR__ . '/../functions/excel_raport_helper.php';
    $rows = dbQuery("
        SELECT d.id, d.nama, d.nidn,
               CONCAT(p.jenjang,' - ',p.nama) as prodi_label,
               p.jenjang, p.nama as prodi_nama
        FROM dosen d
        LEFT JOIN prodi p ON p.id = d.prodi_id
        ORDER BY d.nama
    ");
    foreach ($rows as &$r) {
        $r['prodi_standard'] = function_exists('formatProdiStandard')
            ? formatProdiStandard($r['prodi_label'])
            : $r['prodi_label'];
    }
    unset($r);
    sendJson(['success' => true, 'rows' => $rows]);
}

// GET: search dosen (autocomplete live)
if ($action === 'search_dosen') {
    require_once __DIR__ . '/../functions/excel_raport_helper.php';
    $q    = '%' . trim($_GET['q'] ?? '') . '%';
    $rows = dbQuery("
        SELECT d.id, d.nama, d.nidn,
               CONCAT(p.jenjang,' - ',p.nama) as prodi_label,
               p.jenjang, p.nama as prodi_nama
        FROM dosen d
        LEFT JOIN prodi p ON p.id = d.prodi_id
        WHERE d.nama LIKE ?
        ORDER BY d.nama LIMIT 20
    ", [$q]);
    foreach ($rows as &$r) {
        $r['prodi_standard'] = function_exists('formatProdiStandard')
            ? formatProdiStandard($r['prodi_label'])
            : $r['prodi_label'];
    }
    unset($r);
    sendJson(['success' => true, 'rows' => $rows]);
}

// ─── PERIODE MANAGEMENT ───────────────────────────────────────────────────────

// GET: daftar semua periode
if ($action === 'list_periode') {
    $rows = dbQuery("SELECT * FROM raport_periode ORDER BY tahun_awal DESC, semester ASC");
    // Hitung jumlah dosen per periode
    foreach ($rows as &$r) {
        $cnt = dbQueryOne("SELECT COUNT(*) as c FROM raport_dosen_data WHERE periode=?", [$r['label']]);
        $r['jumlah_dosen'] = (int)($cnt['c'] ?? 0);
    }
    unset($r);
    sendJson(['success' => true, 'rows' => $rows]);
}

// POST: tambah periode baru
if ($action === 'add_periode' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $semester = in_array($_POST['semester'] ?? '', ['Gasal', 'Genap']) ? $_POST['semester'] : 'Gasal';
    $ta       = (int)($_POST['tahun_awal'] ?? date('Y'));
    $tb       = (int)($_POST['tahun_akhir'] ?? ($ta + 1));
    $label    = "{$semester} {$ta}-{$tb}";

    $existing = dbQueryOne("SELECT id FROM raport_periode WHERE label=?", [$label]);
    if ($existing) {
        sendJson(['success' => false, 'message' => "Periode {$label} sudah ada."], 409);
    }

    $id = dbExecute("INSERT INTO raport_periode (label, semester, tahun_awal, tahun_akhir, excel_file) VALUES (?,?,?,?,?)", [
        $label, $semester, $ta, $tb, "Sistem Report Dosen {$ta} - {$tb} {$semester}.xlsx"
    ]);

    sendJson(['success' => true, 'id' => $id, 'label' => $label]);
}

// POST: hapus periode
if ($action === 'delete_periode' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $label = trim($_POST['label'] ?? '');
    if (empty($label)) sendJson(['success' => false, 'message' => 'Label periode tidak valid.'], 422);

    $cnt = dbQueryOne("SELECT COUNT(*) as c FROM raport_dosen_data WHERE periode=?", [$label]);
    if (($cnt['c'] ?? 0) > 0 && empty($_POST['force'])) {
        sendJson(['success' => false, 'message' => "Periode ini masih memiliki {$cnt['c']} data dosen. Gunakan force delete jika yakin."], 422);
    }
    dbExecute("DELETE FROM raport_dosen_data WHERE periode=?", [$label]);
    dbExecute("DELETE FROM raport_periode WHERE label=?", [$label]);
    sendJson(['success' => true, 'deleted' => $label]);
}

// ─── DATA ACTIONS ────────────────────────────────────────────────────────────

// GET: list semua dosen untuk periode
if ($action === 'list' || (empty($action) && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET')) {
    $prodi = trim($_GET['prodi'] ?? '');
    if ($prodi !== '') {
        $rows = dbQuery("SELECT * FROM raport_dosen_data WHERE periode=? AND prodi=? ORDER BY no", [$periode, $prodi]);
    } else {
        $rows = dbQuery("SELECT * FROM raport_dosen_data WHERE periode=? ORDER BY no", [$periode]);
    }
    $prodis = dbQuery("SELECT DISTINCT prodi FROM raport_dosen_data WHERE periode=? AND prodi!='' ORDER BY prodi", [$periode]);
    sendJson(['success' => true, 'rows' => $rows, 'prodis' => array_column($prodis, 'prodi')]);
}

// POST: simpan satu baris (insert/update)
if ($action === 'save_row' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $r = sanitizeRow($_POST);
    if (empty($r['nama'])) sendJson(['success' => false, 'message' => 'Nama dosen wajib diisi.'], 422);

    require_once __DIR__ . '/../functions/excel_raport_helper.php';
    if (!empty($r['prodi']) && function_exists('formatProdiStandard')) {
        $r['prodi'] = formatProdiStandard($r['prodi']);
    }

    // Auto-create periode jika belum terdaftar di raport_periode
    $periodeExist = dbQueryOne("SELECT id FROM raport_periode WHERE label=?", [$periode]);
    if (!$periodeExist) {
        $sem = (stripos($periode, 'genap') !== false) ? 'Genap' : 'Gasal';
        preg_match('/(\d{4})\s*-\s*(\d{4})/', $periode, $mYears);
        $ta  = (int)($mYears[1] ?? date('Y'));
        $tb  = (int)($mYears[2] ?? ($ta + 1));
        dbExecute("INSERT INTO raport_periode (label, semester, tahun_awal, tahun_akhir, excel_file) VALUES (?,?,?,?,?)", [
            $periode, $sem, $ta, $tb, "Sistem Report Dosen {$ta} - {$tb} {$sem}.xlsx"
        ]);
    }

    $existing = null;
    if ($r['no'] > 0) {
        $existing = dbQueryOne("SELECT id FROM raport_dosen_data WHERE periode=? AND no=?", [$periode, $r['no']]);
    }

    if ($existing) {
        dbExecute("UPDATE raport_dosen_data SET
            nama=?, prodi=?, jumlah_matkul=?, jumlah_kelas=?, jumlah_responden=?,
            nilai_kuesioner=?, jumlah_kehadiran=?, konten=?,
            jumlah_penelitian=?, jumlah_pengabdian=?,
            p1=?,p2=?,p3=?,p4=?,p5=?,k1=?,k2=?,k3=?,k4=?,
            updated_at=NOW()
            WHERE periode=? AND no=?",
            [
                $r['nama'],$r['prodi'],$r['jumlah_matkul'],$r['jumlah_kelas'],$r['jumlah_responden'],
                $r['nilai_kuesioner'],$r['jumlah_kehadiran'],$r['konten'],
                $r['jumlah_penelitian'],$r['jumlah_pengabdian'],
                $r['p1'],$r['p2'],$r['p3'],$r['p4'],$r['p5'],
                $r['k1'],$r['k2'],$r['k3'],$r['k4'],
                $periode, $r['no'],
            ]
        );
        sendJson(['success' => true, 'action' => 'updated', 'no' => $r['no']]);
    } else {
        $maxNo   = dbQueryOne("SELECT MAX(no) as m FROM raport_dosen_data WHERE periode=?", [$periode]);
        $r['no'] = (int)($maxNo['m'] ?? 0) + 1;

        dbExecute("INSERT INTO raport_dosen_data
            (periode,no,nama,prodi,jumlah_matkul,jumlah_kelas,jumlah_responden,
             nilai_kuesioner,jumlah_kehadiran,konten,jumlah_penelitian,jumlah_pengabdian,
             p1,p2,p3,p4,p5,k1,k2,k3,k4,created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [
                $periode,$r['no'],$r['nama'],$r['prodi'],$r['jumlah_matkul'],$r['jumlah_kelas'],
                $r['jumlah_responden'],$r['nilai_kuesioner'],$r['jumlah_kehadiran'],$r['konten'],
                $r['jumlah_penelitian'],$r['jumlah_pengabdian'],
                $r['p1'],$r['p2'],$r['p3'],$r['p4'],$r['p5'],
                $r['k1'],$r['k2'],$r['k3'],$r['k4'],
                $_SESSION['user_id'] ?? null,
            ]
        );
        sendJson(['success' => true, 'action' => 'inserted', 'no' => $r['no']]);
    }
}

// POST: hapus baris
if ($action === 'delete_row' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $no = (int)($_POST['no'] ?? 0);
    if (!$no) sendJson(['success' => false, 'message' => 'No tidak valid.'], 422);
    dbExecute("DELETE FROM raport_dosen_data WHERE periode=? AND no=?", [$periode, $no]);
    $remaining = dbQuery("SELECT id FROM raport_dosen_data WHERE periode=? ORDER BY no", [$periode]);
    foreach ($remaining as $i => $row) {
        dbExecute("UPDATE raport_dosen_data SET no=? WHERE id=?", [$i + 1, $row['id']]);
    }
    sendJson(['success' => true, 'action' => 'deleted']);
}

// POST: import dari Excel
if ($action === 'import_excel' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_once __DIR__ . '/../functions/excel_raport_helper.php';

    // Cek / auto-create periode
    $periodeExist = dbQueryOne("SELECT id FROM raport_periode WHERE label=?", [$periode]);
    if (!$periodeExist) {
        // Parse label: "Gasal 2025-2026"
        if (preg_match('/^(Gasal|Genap)\s+(\d{4})-(\d{4})$/i', $periode, $m)) {
            dbExecute("INSERT IGNORE INTO raport_periode (label,semester,tahun_awal,tahun_akhir) VALUES (?,?,?,?)",
                [$periode, ucfirst(strtolower($m[1])), (int)$m[2], (int)$m[3]]);
        } else {
            sendJson(['success' => false, 'message' => 'Periode tidak valid dan tidak dapat dibuat otomatis.'], 422);
        }
    }

    $f = $_FILES['file'] ?? null;
    if (!$f || $f['error'] !== UPLOAD_ERR_OK) sendJson(['success' => false, 'message' => 'Upload gagal.'], 422);

    $parsed = parseExcelRaport($f['tmp_name']);
    $rows   = $parsed['rows'] ?? [];
    if (empty($rows)) sendJson(['success' => false, 'message' => 'File tidak mengandung data.'], 422);

    if (($_POST['overwrite'] ?? '0') === '1') {
        dbExecute("DELETE FROM raport_dosen_data WHERE periode=?", [$periode]);
    }

    $imported = 0;
    $userId   = $_SESSION['user_id'] ?? null;
    foreach ($rows as $idx => $row) {
        $r = sanitizeRow($row);
        if (empty($r['nama'])) continue;
        if ($r['no'] === 0) $r['no'] = $idx + 1;
        if (function_exists('formatProdiStandard')) $r['prodi'] = formatProdiStandard($r['prodi']);

        dbExecute("INSERT INTO raport_dosen_data
            (periode,no,nama,prodi,jumlah_matkul,jumlah_kelas,jumlah_responden,
             nilai_kuesioner,jumlah_kehadiran,konten,jumlah_penelitian,jumlah_pengabdian,
             p1,p2,p3,p4,p5,k1,k2,k3,k4,created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
            nama=VALUES(nama),prodi=VALUES(prodi),
            jumlah_matkul=VALUES(jumlah_matkul),jumlah_kelas=VALUES(jumlah_kelas),
            jumlah_responden=VALUES(jumlah_responden),nilai_kuesioner=VALUES(nilai_kuesioner),
            jumlah_kehadiran=VALUES(jumlah_kehadiran),konten=VALUES(konten),
            jumlah_penelitian=VALUES(jumlah_penelitian),jumlah_pengabdian=VALUES(jumlah_pengabdian),
            p1=VALUES(p1),p2=VALUES(p2),p3=VALUES(p3),p4=VALUES(p4),p5=VALUES(p5),
            k1=VALUES(k1),k2=VALUES(k2),k3=VALUES(k3),k4=VALUES(k4)",
            [
                $periode,$r['no'],$r['nama'],$r['prodi'],$r['jumlah_matkul'],$r['jumlah_kelas'],
                $r['jumlah_responden'],$r['nilai_kuesioner'],$r['jumlah_kehadiran'],$r['konten'],
                $r['jumlah_penelitian'],$r['jumlah_pengabdian'],
                $r['p1'],$r['p2'],$r['p3'],$r['p4'],$r['p5'],
                $r['k1'],$r['k2'],$r['k3'],$r['k4'],
                $userId,
            ]
        );
        $imported++;
    }
    sendJson(['success' => true, 'imported' => $imported]);
}

// GET: Export ke Excel (100% format XLSX identik dengan master template)
if ($action === 'export_excel') {
    require_once __DIR__ . '/../functions/excel_raport_helper.php';
    $rows = dbQuery("SELECT * FROM raport_dosen_data WHERE periode=? ORDER BY no", [$periode]);
    if (empty($rows)) sendJson(['success' => false, 'message' => 'Tidak ada data untuk diexport.'], 404);

    $safeLabel = preg_replace('/[^A-Za-z0-9\-_\. ]/', '', $periode);
    $filename  = 'Sistem Report Dosen ' . $safeLabel . '.xlsx';

    // Cari template master XLSX
    $templateCandidates = [
        __DIR__ . '/../Contoh Lampiran/Laporan Raport/Sistem Report Dosen ' . $periode . '.xlsx',
        __DIR__ . '/../Contoh Lampiran/Laporan Raport/Sistem Report Dosen 2025 - 2026 Gasal.xlsx',
        __DIR__ . '/../Contoh Lampiran/Laporan Raport/Sistem Report Dosen 2025 - 2026 Genap.xlsx',
    ];
    $templatePath = null;
    foreach ($templateCandidates as $tc) {
        if (file_exists($tc)) {
            $templatePath = $tc;
            break;
        }
    }

    if ($templatePath) {
        exportExcelExactTemplate($templatePath, $rows, $filename);
    } else {
        exportExcelPhpSpreadsheet($rows, $filename, $periode);
    }
}

// ─── Export Helpers ──────────────────────────────────────────────────────────

/**
 * Export 100% identik dengan master template (5 sheet: Data Dosen, Skor Bobot, Skor, Rata-Rata, Rapot).
 * Mempertahankan seluruh rumus, layout print, dan format asli.
 */
function exportExcelExactTemplate(string $templatePath, array $rows, string $filename): void {
    $tempFile = sys_get_temp_dir() . '/' . uniqid('export_xlsx_', true) . '.xlsx';
    if (!copy($templatePath, $tempFile)) {
        exportExcelPhpSpreadsheet($rows, $filename, 'Raport Dosen');
        return;
    }

    $zip = new ZipArchive();
    if ($zip->open($tempFile) !== true) {
        exportExcelPhpSpreadsheet($rows, $filename, 'Raport Dosen');
        return;
    }

    $origSheet1 = $zip->getFromName('xl/worksheets/sheet1.xml');
    preg_match('/^(.*?<sheetData>)/s', $origSheet1, $mPre);
    preg_match('/(<\/sheetData>.*)$/s', $origSheet1, $mPost);
    
    $preamble = $mPre[1] ?? '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
    $postamble = $mPost[1] ?? '</sheetData></worksheet>';
    
    preg_match('/<row[^>]*r="1"[^>]*>.*?<\/row>/s', $origSheet1, $mRow1);
    $headerRow = $mRow1[0] ?? '';

    $sheetDataXml = $headerRow;
    foreach ($rows as $ri => $r) {
        $rowNum = $ri + 2;
        $rowXml = '<row r="' . $rowNum . '" customHeight="1" ht="20">';
        
        // A: No
        $rowXml .= '<c r="A' . $rowNum . '" s="85"><v>' . (int)$r['no'] . '</v></c>';
        
        // B: Nama
        $namaEsc = htmlspecialchars($r['nama'] ?? '', ENT_XML1, 'UTF-8');
        $rowXml .= '<c r="B' . $rowNum . '" t="inlineStr" s="86"><is><t>' . $namaEsc . '</t></is></c>';
        
        // C: Prodi
        $prodiEsc = htmlspecialchars($r['prodi'] ?? '', ENT_XML1, 'UTF-8');
        $rowXml .= '<c r="C' . $rowNum . '" t="inlineStr" s="86"><is><t>' . $prodiEsc . '</t></is></c>';
        
        // D: Jumlah Matkul
        $mk = (int)($r['jumlah_matkul'] ?? 0);
        $rowXml .= '<c r="D' . $rowNum . '" s="87">' . ($mk > 0 ? '<v>' . $mk . '</v>' : '') . '</c>';
        
        // E: Jumlah Kelas
        $kls = (int)($r['jumlah_kelas'] ?? 0);
        $rowXml .= '<c r="E' . $rowNum . '" s="87">' . ($kls > 0 ? '<v>' . $kls . '</v>' : '') . '</c>';
        
        // F: Jumlah Responden
        $respEsc = htmlspecialchars((string)($r['jumlah_responden'] ?? ''), ENT_XML1, 'UTF-8');
        $rowXml .= '<c r="F' . $rowNum . '" t="inlineStr" s="88"><is><t>' . $respEsc . '</t></is></c>';
        
        // G: Nilai Kuesioner
        $kuis = (float)($r['nilai_kuesioner'] ?? 0);
        $rowXml .= '<c r="G' . $rowNum . '" s="89">' . ($kuis > 0 ? '<v>' . $kuis . '</v>' : '') . '</c>';
        
        // H: Kehadiran
        $hadir = (float)($r['jumlah_kehadiran'] ?? 0);
        $rowXml .= '<c r="H' . $rowNum . '" s="89">' . ($hadir > 0 ? '<v>' . $hadir . '</v>' : '') . '</c>';
        
        // I: Konten
        $konten = (float)($r['konten'] ?? 0);
        $rowXml .= '<c r="I' . $rowNum . '" s="89">' . ($konten > 0 ? '<v>' . $konten . '</v>' : '') . '</c>';
        
        // J: Penelitian
        $penel = (int)($r['jumlah_penelitian'] ?? 0);
        $rowXml .= '<c r="J' . $rowNum . '" s="87">' . ($penel > 0 ? '<v>' . $penel . '</v>' : '') . '</c>';
        
        // K: Pengabdian
        $pengab = (int)($r['jumlah_pengabdian'] ?? 0);
        $rowXml .= '<c r="K' . $rowNum . '" s="87">' . ($pengab > 0 ? '<v>' . $pengab . '</v>' : '') . '</c>';
        
        // L..P: P1..P5
        for ($p = 1; $p <= 5; $p++) {
            $colLetter = chr(75 + $p);
            $pVal = htmlspecialchars($r['p'.$p] ?? '', ENT_XML1, 'UTF-8');
            $rowXml .= '<c r="' . $colLetter . $rowNum . '" t="inlineStr" s="90"><is><t>' . $pVal . '</t></is></c>';
        }
        
        // Q..T: K1..K4
        for ($k = 1; $k <= 4; $k++) {
            $colLetter = chr(80 + $k);
            $kVal = htmlspecialchars($r['k'.$k] ?? '', ENT_XML1, 'UTF-8');
            $rowXml .= '<c r="' . $colLetter . $rowNum . '" t="inlineStr" s="90"><is><t>' . $kVal . '</t></is></c>';
        }
        
        $rowXml .= '</row>';
        $sheetDataXml .= $rowXml;
    }
    
    // ── 1. UPDATE SHEET 1: Data Dosen ──────────────────────────────────────────
    $newSheet1 = $preamble . $sheetDataXml . $postamble;
    $zip->addFromString('xl/worksheets/sheet1.xml', $newSheet1);

    // ── 2. UPDATE SHEET 2: Skor Bobot ──────────────────────────────────────────
    $origSheet2 = $zip->getFromName('xl/worksheets/sheet2.xml');
    if ($origSheet2) {
        preg_match('/^(.*?<sheetData>)/s', $origSheet2, $mPre2);
        preg_match('/(<\/sheetData>.*)$/s', $origSheet2, $mPost2);
        preg_match('/<row[^>]*r="1"[^>]*>.*?<\/row>/s', $origSheet2, $mRow1_2);

        $sheet2Xml = ($mPre2[1] ?? '') . ($mRow1_2[0] ?? '');
        foreach ($rows as $ri => $r) {
            $rowNum = $ri + 2;
            $no     = (int)$r['no'];
            $kuis   = (float)($r['nilai_kuesioner'] ?? 0);
            $hadir  = (float)($r['jumlah_kehadiran'] ?? 0);
            $konten = (float)($r['konten'] ?? 0);
            $penel  = (int)($r['jumlah_penelitian'] ?? 0);
            $pengab = (int)($r['jumlah_pengabdian'] ?? 0);

            // Pre-hitung bobot & total skor (multiplier: 1, 2, 3, 4, 5)
            $wH = round($kuis * 1, 4);
            $wI = round($hadir * 2, 4);
            $wJ = round($konten * 3, 4);
            $wK = round($penel * 4, 4);
            $wL = round($pengab * 5, 4);
            $wM = round($wH + $wI + $wJ + $wK + $wL, 4);

            $rowXml = '<row r="' . $rowNum . '" customHeight="1" ht="18">';
            $rowXml .= '<c r="A' . $rowNum . '" s="4"><v>' . $no . '</v></c>';
            $rowXml .= '<c r="B' . $rowNum . '" s="4">' . ($kuis > 0 ? '<v>' . $kuis . '</v>' : '') . '</c>';
            $rowXml .= '<c r="C' . $rowNum . '" s="4">' . ($hadir > 0 ? '<v>' . $hadir . '</v>' : '') . '</c>';
            $rowXml .= '<c r="D' . $rowNum . '" s="4">' . ($konten > 0 ? '<v>' . $konten . '</v>' : '') . '</c>';
            $rowXml .= '<c r="E' . $rowNum . '" s="4">' . ($penel > 0 ? '<v>' . $penel . '</v>' : '') . '</c>';
            $rowXml .= '<c r="F' . $rowNum . '" s="4">' . ($pengab > 0 ? '<v>' . $pengab . '</v>' : '') . '</c>';
            $rowXml .= '<c r="H' . $rowNum . '"><f>B' . $rowNum . '*$H$1</f><v>' . $wH . '</v></c>';
            $rowXml .= '<c r="I' . $rowNum . '"><f>C' . $rowNum . '*$I$1</f><v>' . $wI . '</v></c>';
            $rowXml .= '<c r="J' . $rowNum . '"><f>D' . $rowNum . '*$J$1</f><v>' . $wJ . '</v></c>';
            $rowXml .= '<c r="K' . $rowNum . '"><f>E' . $rowNum . '*$K$1</f><v>' . $wK . '</v></c>';
            $rowXml .= '<c r="L' . $rowNum . '"><f>F' . $rowNum . '*$L$1</f><v>' . $wL . '</v></c>';
            $rowXml .= '<c r="M' . $rowNum . '"><f>SUM(H' . $rowNum . ':L' . $rowNum . ')</f><v>' . $wM . '</v></c>';
            $rowXml .= '</row>';
            $sheet2Xml .= $rowXml;
        }
        $sheet2Xml .= ($mPost2[1] ?? '');
        $zip->addFromString('xl/worksheets/sheet2.xml', $sheet2Xml);
    }

    // ── 3. UPDATE SHEET 3: Skor ────────────────────────────────────────────────
    $origSheet3 = $zip->getFromName('xl/worksheets/sheet3.xml');
    if ($origSheet3) {
        preg_match('/^(.*?<sheetData>)/s', $origSheet3, $mPre3);
        preg_match('/(<\/sheetData>.*)$/s', $origSheet3, $mPost3);
        preg_match('/<row[^>]*r="1"[^>]*>.*?<\/row>/s', $origSheet3, $mRow1_3);

        $sheet3Xml = ($mPre3[1] ?? '') . ($mRow1_3[0] ?? '');
        foreach ($rows as $ri => $r) {
            $rowNum = $ri + 2;
            $no     = (int)$r['no'];
            $kuis   = (float)($r['nilai_kuesioner'] ?? 0);
            $hadir  = (float)($r['jumlah_kehadiran'] ?? 0);
            $konten = (float)($r['konten'] ?? 0);
            $penel  = (int)($r['jumlah_penelitian'] ?? 0);
            $pengab = (int)($r['jumlah_pengabdian'] ?? 0);
            $totalSkor = round(($kuis * 1) + ($hadir * 2) + ($konten * 3) + ($penel * 4) + ($pengab * 5), 4);

            $rowXml = '<row r="' . $rowNum . '" customHeight="1" ht="18">';
            $rowXml .= '<c r="A' . $rowNum . '"><f>\'Skor Bobot\'!A' . $rowNum . '</f><v>' . $no . '</v></c>';
            $rowXml .= '<c r="B' . $rowNum . '"><f>\'Skor Bobot\'!M' . $rowNum . '</f><v>' . $totalSkor . '</v></c>';
            $rowXml .= '</row>';
            $sheet3Xml .= $rowXml;
        }
        $sheet3Xml .= ($mPost3[1] ?? '');
        $zip->addFromString('xl/worksheets/sheet3.xml', $sheet3Xml);
    }

    // ── 4. UPDATE SHEET 5: Rapot ───────────────────────────────────────────────
    $origSheet5 = $zip->getFromName('xl/worksheets/sheet5.xml');
    if ($origSheet5) {
        $updatedSheet5 = preg_replace('/<c r="M1"[^>]*><v>\d*<\/v><\/c>/', '<c r="M1" s="85"><v>1</v></c>', $origSheet5);
        $zip->addFromString('xl/worksheets/sheet5.xml', $updatedSheet5);
    }

    $zip->close();

    // Stream download XLSX
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tempFile));
    header('Cache-Control: max-age=0, must-revalidate');
    header('Pragma: public');
    readfile($tempFile);
    @unlink($tempFile);
    exit;
}

function exportExcelPhpSpreadsheet(array $rows, string $filename, string $periodeLabel): void {
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }

    $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $ws = $ss->getActiveSheet();
    $ws->setTitle('Data Dosen');

    $headers = ['No','Nama','Program Studi','Jumlah Mata Kuliah','Jumlah Kelas','Jumlah Responden',
        'Nilai Kuesioner Mahasiswa','Kehadiran','Kelengkapan Konten Perkuliahan','Penelitian','Pengabdian',
        'P1','P2','P3','P4','P5','K1','K2','K3','K4'];

    $headerStyle = [
        'font'      => ['bold'=>true,'color'=>['rgb'=>'FFFFFF'],'size'=>10],
        'fill'      => ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'1F4E79']],
        'borders'   => ['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        'alignment' => ['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,'wrapText'=>true],
    ];

    foreach ($headers as $ci => $h) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
        $ws->setCellValue($col.'1', $h);
        $ws->getStyle($col.'1')->applyFromArray($headerStyle);
    }
    $ws->getRowDimension(1)->setRowHeight(30);

    $dataStyle = ['borders'=>['allBorders'=>['borderStyle'=>\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,'color'=>['rgb'=>'D0D0D0']]],'alignment'=>['vertical'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];

    foreach ($rows as $ri => $row) {
        $r = $ri + 2;
        $ws->setCellValue("A$r", (int)$row['no']);
        $ws->setCellValue("B$r", $row['nama']);
        $ws->setCellValue("C$r", $row['prodi']);
        $ws->setCellValue("D$r", (int)$row['jumlah_matkul'] ?: '');
        $ws->setCellValue("E$r", (int)$row['jumlah_kelas'] ?: '');
        $ws->setCellValue("F$r", $row['jumlah_responden']);
        $ws->setCellValue("G$r", (float)$row['nilai_kuesioner'] ?: '');
        $ws->setCellValue("H$r", (float)$row['jumlah_kehadiran'] ?: '');
        $ws->setCellValue("I$r", (float)$row['konten'] ?: '');
        $ws->setCellValue("J$r", (int)$row['jumlah_penelitian'] ?: '');
        $ws->setCellValue("K$r", (int)$row['jumlah_pengabdian'] ?: '');
        for ($i = 1; $i <= 5; $i++) $ws->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(11 + $i) . $r, $row['p'.$i]);
        for ($i = 1; $i <= 4; $i++) $ws->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(16 + $i) . $r, $row['k'.$i]);
        $ws->getStyle("A$r:T$r")->applyFromArray($dataStyle);
        $bg = ($ri % 2 === 0) ? 'FFFFFF' : 'F2F6FC';
        $ws->getStyle("A$r:T$r")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
        $ws->getRowDimension($r)->setRowHeight(22);
    }

    $widths = [5,40,30,10,10,18,12,12,10,12,12,45,45,45,45,45,50,50,50,50];
    foreach ($widths as $ci => $w) {
        $ws->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1))->setWidth($w);
    }
    $ws->freezePane('A2');

    if (ob_get_level()) ob_end_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Cache-Control: max-age=0, must-revalidate');
    header('Pragma: public');
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss))->save('php://output');
    exit;
}

sendJson(['success' => false, 'message' => 'Action tidak dikenali: '.$action], 400);
