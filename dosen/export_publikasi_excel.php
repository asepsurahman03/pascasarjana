<?php
/**
 * Export Publikasi Ilmiah Dosen → .xlsx
 * Tidak memuat header.php (menghindari output HTML sebelum header HTTP).
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// ─── Auth: harus login sebagai dosen ─────────────────────────────────────────
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dosen') {
    header('Location: ' . BASE_URL . '/login?portal=dosen');
    exit;
}

$dosenId  = $_SESSION['user_id'] ?? 0;
$dosenRow = dbQueryOne("SELECT * FROM dosen WHERE id = ?", [$dosenId]);
if (!$dosenRow && isset($_SESSION['username'])) {
    $dosenRow = dbQueryOne("SELECT * FROM dosen WHERE nidn = ? OR email = ?", [$_SESSION['username'], $_SESSION['username']]);
}
if (!$dosenRow) {
    $dosenRow = dbQueryOne("SELECT * FROM dosen LIMIT 1", []);
}
if (!$dosenRow) {
    http_response_code(403);
    exit('Data dosen tidak ditemukan.');
}
$dosenId   = $dosenRow['id'];
$dosenNama = $dosenRow['nama'] ?? 'Dosen';
$dosenNidn = $dosenRow['nidn'] ?? '';

// ─── Ambil semua publikasi dosen ──────────────────────────────────────────────
$pubs = dbQuery(
    "SELECT * FROM dosen_publikasi WHERE dosen_id = ? ORDER BY tahun_terbit DESC, created_at DESC",
    [$dosenId]
);

// ─── Kolom Excel ─────────────────────────────────────────────────────────────
$headers = [
    'No',
    'Judul Artikel',
    'Kategori / Indeksasi',
    'Scopus',
    'Sinta Rank',
    'Nama Jurnal / Prosiding',
    'Penulis',
    'DOI',
    'Tahun Terbit',
    'Status Publikasi',
    'Kata Kunci',
    'Link Artikel',
    'Abstrak',
    'Tanggal Ditambahkan',
];

// ─── Helper: ekstrak Scopus & Sinta dari kategori_publikasi ──────────────────
function extractScopusDosen(string $kat): string {
    if (preg_match('/scopus\s*(Q[1-4])?/i', $kat, $m)) {
        return 'Scopus' . (isset($m[1]) && $m[1] ? ' ' . strtoupper($m[1]) : '');
    }
    if (stripos($kat, 'WOS') !== false || stripos($kat, 'Web of Science') !== false) return 'WoS';
    return '';
}
function extractSintaDosen(string $kat): string {
    if (preg_match('/SINTA[\s\-]*([1-6])/i', $kat, $m)) return 'SINTA ' . $m[1];
    return '';
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function xmlEsc2(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}
function colLetterD2(int $i): string {
    $i++;
    $col = '';
    while ($i > 0) {
        $i--;
        $col = chr(65 + $i % 26) . $col;
        $i   = intdiv($i, 26);
    }
    return $col;
}
function caD(int $col, int $row): string { return colLetterD2($col) . $row; }

// ─── Shared strings pool ──────────────────────────────────────────────────────
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

foreach ($headers as $h) { $strIdx($h); }

// ─── Baris data ───────────────────────────────────────────────────────────────
$rows = [];
foreach ($pubs as $i => $p) {
    $kat = $p['kategori_publikasi'] ?? 'Lainnya';
    $rows[] = [
        $i + 1,                                       //  0  No (numeric)
        $p['judul_artikel']      ?? '',               //  1
        $kat,                                         //  2  Kategori
        extractScopusDosen($kat),                     //  3  Scopus  ← BARU
        extractSintaDosen($kat),                      //  4  Sinta   ← BARU
        $p['nama_jurnal']        ?? '',               //  5
        $p['penulis']            ?? '',               //  6
        $p['doi']                ?? '',               //  7
        (int)($p['tahun_terbit'] ?? 0) ?: '',         //  8  Tahun (numeric)
        $p['status_publikasi']   ?? '',               //  9
        $p['kata_kunci']         ?? '',               // 10
        $p['link_artikel']       ?? '',               // 11
        $p['abstrak']            ?? '',               // 12
        date('d/m/Y', strtotime($p['created_at'])),  // 13
    ];
}
foreach ($rows as $row) {
    foreach ($row as $ci => $val) {
        if ($ci !== 0 && $ci !== 8) { $strIdx((string)$val); }
    }
}

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
  <sheets><sheet name="Publikasi Dosen" sheetId="1" r:id="rId1"/></sheets>
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
$colWidths = [5, 50, 28, 14, 14, 38, 30, 12, 10, 18, 38, 38, 60, 16];
$numCols   = count($headers);
$lastCol   = colLetterD2($numCols - 1);

$ws  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
$ws .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";

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
$titleText = 'DAFTAR PUBLIKASI ILMIAH — ' . strtoupper($dosenNama) . ($dosenNidn ? ' (NIDN: ' . $dosenNidn . ')' : '');
$ws .= '      <c r="A1" s="1" t="s"><v>' . $strIdx($titleText) . '</v></c>' . "\n";
$ws .= '    </row>' . "\n";

// Row 2: Info
$ws .= '    <row r="2" ht="18" customHeight="1">' . "\n";
$infoText = 'Dicetak pada: ' . date('d F Y, H:i') . ' WIB  |  Total Publikasi: ' . count($rows);
$ws .= '      <c r="A2" s="1" t="s"><v>' . $strIdx($infoText) . '</v></c>' . "\n";
$ws .= '    </row>' . "\n";

// Row 3: blank
$ws .= '    <row r="3"/>' . "\n";

// Row 4: Headers
$ws .= '    <row r="4" ht="35" customHeight="1">' . "\n";
foreach ($headers as $ci => $h) {
    $ws .= '      <c r="' . caD($ci, 4) . '" s="2" t="s"><v>' . $strIdx($h) . '</v></c>' . "\n";
}
$ws .= '    </row>' . "\n";

// Data rows
foreach ($rows as $ri => $row) {
    $excelRow = $ri + 5;
    $ws .= '    <row r="' . $excelRow . '" ht="60" customHeight="1">' . "\n";
    foreach ($row as $ci => $val) {
        $addr = caD($ci, $excelRow);
        if ($ci === 0) {
            // No — numeric
            $ws .= '      <c r="' . $addr . '" s="4"><v>' . (int)$val . '</v></c>' . "\n";
        } elseif ($ci === 3 || $ci === 4) {
            // Scopus & Sinta — center
            $sStyle = ($ri % 2 === 0) ? 4 : 4;
            $ws .= '      <c r="' . $addr . '" s="' . $sStyle . '" t="s"><v>' . $strIdx((string)$val) . '</v></c>' . "\n";
        } elseif ($ci === 7) {
            // DOI
            $sStyle = ($ri % 2 === 0) ? 3 : 6;
            $ws .= '      <c r="' . $addr . '" s="' . $sStyle . '" t="s"><v>' . $strIdx((string)$val) . '</v></c>' . "\n";
        } elseif ($ci === 8) {
            // Tahun — numeric
            if ($val !== '') {
                $ws .= '      <c r="' . $addr . '" s="4"><v>' . (int)$val . '</v></c>' . "\n";
            } else {
                $ws .= '      <c r="' . $addr . '" s="4" t="s"><v>' . $strIdx('') . '</v></c>' . "\n";
            }
        } elseif ($ci === 9) {
            // Status Publikasi
            $sStyle = str_contains(strtolower((string)$val), 'publish') ? 5 : 3;
            $ws .= '      <c r="' . $addr . '" s="' . $sStyle . '" t="s"><v>' . $strIdx((string)$val) . '</v></c>' . "\n";
        } else {
            $sStyle = ($ri % 2 === 0) ? 3 : 6;
            $ws .= '      <c r="' . $addr . '" s="' . $sStyle . '" t="s"><v>' . $strIdx((string)$val) . '</v></c>' . "\n";
        }
    }
    $ws .= '    </row>' . "\n";
}

$ws .= '  </sheetData>' . "\n";
$ws .= '  <mergeCells count="2">' . "\n";
$ws .= '    <mergeCell ref="A1:' . $lastCol . '1"/>' . "\n";
$ws .= '    <mergeCell ref="A2:' . $lastCol . '2"/>' . "\n";
$ws .= '  </mergeCells>' . "\n";
$ws .= '</worksheet>';

// ─── Shared strings XML ───────────────────────────────────────────────────────
$ssXml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
$ssXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">' . "\n";
foreach ($strings as $s) {
    $ssXml .= '  <si><t xml:space="preserve">' . xmlEsc2($s) . '</t></si>' . "\n";
}
$ssXml .= '</sst>';

// ─── Bungkus jadi .xlsx (ZIP) ─────────────────────────────────────────────────
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_dosen_');
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
$safeName = 'Publikasi_Dosen_' . preg_replace('/[^a-zA-Z0-9]/', '_', $dosenNama) . '_' . ($dosenNidn ? preg_replace('/[^a-zA-Z0-9]/', '_', $dosenNidn) . '_' : '') . date('Ymd') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $safeName . '"');
header('Content-Length: ' . filesize($tmpFile));
header('Cache-Control: max-age=0');
readfile($tmpFile);
unlink($tmpFile);
exit;
