<?php
/**
 * Export Publikasi Ilmiah Mahasiswa → .xlsx
 * Tidak memuat header.php (menghindari output HTML sebelum header HTTP).
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// ─── Auth: harus login sebagai mahasiswa ──────────────────────────────────────
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ' . BASE_URL . '/login?portal=mahasiswa');
    exit;
}

$username = $_SESSION['username'] ?? '';
$mhsRow   = dbQueryOne(
    "SELECT m.*, p.nama AS prodi FROM mahasiswa m LEFT JOIN prodi p ON m.prodi_id = p.id WHERE m.nim = ?",
    [$username]
);
if (!$mhsRow) {
    http_response_code(403);
    exit('Akses ditolak: data mahasiswa tidak ditemukan.');
}

$mhs = [
    'id'    => $mhsRow['id'],
    'nama'  => $mhsRow['nama'],
    'nim'   => $mhsRow['nim'],
    'prodi' => $mhsRow['prodi'] ?? '-',
];
$mhsId = $mhs['id'];

// ─── Ambil semua publikasi ────────────────────────────────────────────────────
$pubs = dbQuery(
    "SELECT * FROM mahasiswa_publikasi WHERE mahasiswa_id = ? ORDER BY tahun_terbit DESC, created_at DESC",
    [$mhsId]
);

// ─── Kolom Excel ─────────────────────────────────────────────────────────────
$headers = [
    'No',
    'Judul Artikel',
    'Kategori / Indeksasi',
    'Nama Jurnal / Prosiding',
    'Penulis / Rekan Penulis',
    'Dosen Pembimbing',
    'DOI',
    'Tahun Terbit',
    'Volume',
    'Nomor Terbit',
    'Halaman',
    'Status Publikasi',
    'Kata Kunci',
    'Link Artikel',
    'Abstrak',
    'Tanggal Ditambahkan',
];

// ─── Helper: escape XML chars ─────────────────────────────────────────────────
function xmlEsc(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

// ─── Shared-strings pool ──────────────────────────────────────────────────────
$strings = [];
$strIdx  = function (string $s) use (&$strings): int {
    $s   = trim($s);
    $key = array_search($s, $strings, true);
    if ($key === false) {
        $strings[] = $s;
        $key       = count($strings) - 1;
    }
    return $key;
};

// Pre-load header strings
foreach ($headers as $h) {
    $strIdx($h);
}

// ─── Buat baris data ──────────────────────────────────────────────────────────
$rows = [];
foreach ($pubs as $i => $p) {
    $penulisList = !empty($p['rekan_penulis'])
        ? array_map('trim', explode(',', $p['rekan_penulis']))
        : [$mhs['nama']];
    $penulisStr  = implode(', ', $penulisList);

    $rows[] = [
        $i + 1,                                       // 0  No (numeric)
        $p['judul_artikel']      ?? '',               // 1
        $p['kategori_publikasi'] ?? 'Lainnya',        // 2
        $p['nama_jurnal']        ?? '',               // 3
        $penulisStr,                                  // 4
        $p['dosen_pendamping']   ?? '',               // 5
        $p['doi']                ?? '',               // 6
        (int)($p['tahun_terbit'] ?? 0) ?: '',         // 7  Tahun (numeric)
        $p['volume']             ?? '',               // 8
        $p['nomor_terbit']       ?? '',               // 9
        $p['halaman']            ?? '',               // 10
        $p['status_publikasi']   ?? '',               // 11
        $p['kata_kunci']         ?? '',               // 12
        $p['link_artikel']       ?? '',               // 13
        $p['abstrak']            ?? '',               // 14
        date('d/m/Y', strtotime($p['created_at'])),  // 15
    ];
}

// Pre-load all string cells
foreach ($rows as $row) {
    foreach ($row as $ci => $val) {
        if ($ci !== 0 && $ci !== 7) {   // skip numeric cols
            $strIdx((string)$val);
        }
    }
}

// ─── Helper: column letter ────────────────────────────────────────────────────
function colLetter(int $i): string {
    $i++;
    $col = '';
    while ($i > 0) {
        $i--;
        $col = chr(65 + $i % 26) . $col;
        $i   = intdiv($i, 26);
    }
    return $col;
}
function ca(int $col, int $row): string { return colLetter($col) . $row; }

// ─── XML parts ────────────────────────────────────────────────────────────────
$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml"          ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml"     ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
  <Override PartName="/xl/styles.xml"            ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';

$rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';

$wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"    Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"        Target="styles.xml"/>
</Relationships>';

$workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets><sheet name="Publikasi Mahasiswa" sheetId="1" r:id="rId1"/></sheets>
</workbook>';

$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="4">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><sz val="11"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font>
    <font><b/><sz val="13"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font>
    <font><sz val="10"/><name val="Calibri"/></font>
  </fonts>
  <fills count="5">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF8C0C4C"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFDF0F7"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFE8F5E9"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left   style="thin"><color rgb="FFD1D5DB"/></left>
      <right  style="thin"><color rgb="FFD1D5DB"/></right>
      <top    style="thin"><color rgb="FFD1D5DB"/></top>
      <bottom style="thin"><color rgb="FFD1D5DB"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="7">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center" wrapText="1"/>
    </xf>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center" wrapText="1"/>
    </xf>
    <xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment vertical="top" wrapText="1"/>
    </xf>
    <xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="top"/>
    </xf>
    <xf numFmtId="0" fontId="3" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="top"/>
    </xf>
    <xf numFmtId="0" fontId="3" fillId="0" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment vertical="top" wrapText="1"/>
    </xf>
  </cellXfs>
</styleSheet>';

// ─── Worksheet XML ────────────────────────────────────────────────────────────
$colWidths = [5, 45, 35, 35, 30, 28, 12, 10, 12, 12, 18, 35, 38, 60, 16];
$numCols   = count($headers);

$ws  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
$ws .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";

// Freeze pane must come before sheetData
$lastCol = colLetter($numCols - 1);

$ws .= '  <sheetViews><sheetView tabSelected="1" workbookViewId="0">';
$ws .= '<pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/>';
$ws .= '</sheetView></sheetViews>' . "\n";

$ws .= '  <cols>' . "\n";
foreach ($colWidths as $ci => $w) {
    $ws .= '    <col min="' . ($ci + 1) . '" max="' . ($ci + 1) . '" width="' . $w . '" customWidth="1"/>' . "\n";
}
$ws .= '  </cols>' . "\n";

$ws .= '  <sheetData>' . "\n";

// Row 1: Title
$ws .= '    <row r="1" ht="30" customHeight="1">' . "\n";
$titleText = 'DAFTAR PUBLIKASI ILMIAH — ' . strtoupper($mhs['nama']) . ' (' . $mhs['nim'] . ') — ' . strtoupper($mhs['prodi']);
$ws .= '      <c r="A1" s="1" t="s"><v>' . $strIdx($titleText) . '</v></c>' . "\n";
$ws .= '    </row>' . "\n";

// Row 2: Info
$ws .= '    <row r="2" ht="18" customHeight="1">' . "\n";
$infoText = 'Dicetak pada: ' . date('d F Y, H:i') . ' WIB  |  Total Publikasi: ' . count($rows);
$ws .= '      <c r="A2" s="1" t="s"><v>' . $strIdx($infoText) . '</v></c>' . "\n";
$ws .= '    </row>' . "\n";

// Row 3: blank
$ws .= '    <row r="3"/>' . "\n";

// Row 4: Header
$ws .= '    <row r="4" ht="35" customHeight="1">' . "\n";
foreach ($headers as $ci => $h) {
    $ws .= '      <c r="' . ca($ci, 4) . '" s="2" t="s"><v>' . $strIdx($h) . '</v></c>' . "\n";
}
$ws .= '    </row>' . "\n";

// Data rows (start row 5)
foreach ($rows as $ri => $row) {
    $excelRow = $ri + 5;
    $ws .= '    <row r="' . $excelRow . '" ht="60" customHeight="1">' . "\n";
    foreach ($row as $ci => $val) {
        $addr = ca($ci, $excelRow);
        if ($ci === 0) {
            // No. — numeric, centered
            $ws .= '      <c r="' . $addr . '" s="4"><v>' . (int)$val . '</v></c>' . "\n";
        } elseif ($ci === 6) {
            // Tahun — numeric or blank
            if ($val !== '') {
                $ws .= '      <c r="' . $addr . '" s="4"><v>' . (int)$val . '</v></c>' . "\n";
            } else {
                $ws .= '      <c r="' . $addr . '" s="4" t="s"><v>' . $strIdx('') . '</v></c>' . "\n";
            }
        } elseif ($ci === 10) {
            // Status — green fill if published
            $sStyle = str_contains(strtolower((string)$val), 'publish') ? 5 : 3;
            $ws .= '      <c r="' . $addr . '" s="' . $sStyle . '" t="s"><v>' . $strIdx((string)$val) . '</v></c>' . "\n";
        } else {
            // Normal text — alternate row bg
            $sStyle = ($ri % 2 === 0) ? 3 : 6;
            $ws .= '      <c r="' . $addr . '" s="' . $sStyle . '" t="s"><v>' . $strIdx((string)$val) . '</v></c>' . "\n";
        }
    }
    $ws .= '    </row>' . "\n";
}

$ws .= '  </sheetData>' . "\n";

// Merged cells for title rows
$ws .= '  <mergeCells count="2">' . "\n";
$ws .= '    <mergeCell ref="A1:' . $lastCol . '1"/>' . "\n";
$ws .= '    <mergeCell ref="A2:' . $lastCol . '2"/>' . "\n";
$ws .= '  </mergeCells>' . "\n";
$ws .= '</worksheet>';

// ─── Shared strings XML ───────────────────────────────────────────────────────
$ssXml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
$ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">' . "\n";
foreach ($strings as $s) {
    $ssXml .= '  <si><t xml:space="preserve">' . xmlEsc($s) . '</t></si>' . "\n";
}
$ssXml .= '</sst>';

// ─── Bungkus jadi .xlsx (ZIP) ─────────────────────────────────────────────────
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_mhs_');
$zip     = new ZipArchive();
$zip->open($tmpFile, ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml',        $contentTypes);
$zip->addFromString('_rels/.rels',                $rels);
$zip->addFromString('xl/workbook.xml',            $workbook);
$zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
$zip->addFromString('xl/styles.xml',              $styles);
$zip->addFromString('xl/sharedStrings.xml',       $ssXml);
$zip->addFromString('xl/worksheets/sheet1.xml',   $ws);
$zip->close();

// ─── Kirim file ke browser ────────────────────────────────────────────────────
$safeName = 'Publikasi_' . preg_replace('/[^a-zA-Z0-9]/', '_', $mhs['nama']) . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $mhs['nim']) . '_' . date('Ymd') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $safeName . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: max-age=0');
readfile($tmpFile);
unlink($tmpFile);
exit;
