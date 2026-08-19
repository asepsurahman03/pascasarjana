<?php
/**
 * Export Excel Admin — Semua Publikasi Dosen (.xlsx)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$filterStatus   = $_GET['status']   ?? '';
$filterTahun    = $_GET['tahun']    ?? '';
$filterKategori = $_GET['kategori'] ?? '';
$searchQ        = trim($_GET['q']   ?? '');

$params = []; $where = ['1=1'];
if ($filterStatus)   { $where[] = 'dp.status_publikasi = ?'; $params[] = $filterStatus; }
if ($filterTahun)    { $where[] = 'dp.tahun_terbit = ?';     $params[] = (int)$filterTahun; }
if ($filterKategori) { $where[] = 'dp.kategori_publikasi LIKE ?'; $params[] = "%$filterKategori%"; }
if ($searchQ)        { $where[] = '(dp.judul_artikel LIKE ? OR dp.nama_jurnal LIKE ? OR dp.kategori_publikasi LIKE ? OR d.nama LIKE ?)'; $params[] = "%$searchQ%"; $params[] = "%$searchQ%"; $params[] = "%$searchQ%"; $params[] = "%$searchQ%"; }

$sql = "SELECT dp.*, d.nama AS dosen_nama, d.nidn
        FROM dosen_publikasi dp
        LEFT JOIN dosen d ON dp.dosen_id = d.id
        WHERE ".implode(' AND ',$where)."
        ORDER BY dp.tahun_terbit DESC, dp.created_at DESC";
$pubs = dbQuery($sql, $params);

$headers = ['No','NIDN','Nama Dosen','Judul Artikel','Kategori / Indeksasi','Scopus','Sinta Rank','Nama Jurnal','Penulis','DOI','Tahun Terbit','Status Publikasi','Kata Kunci','Link Artikel','Abstrak','Tanggal Input'];

function extractScopusD(string $kat): string {
    if (preg_match('/scopus\s*(Q[1-4])?/i', $kat, $m)) {
        return 'Scopus' . (isset($m[1]) && $m[1] ? ' ' . strtoupper($m[1]) : '');
    }
    if (stripos($kat, 'WOS') !== false || stripos($kat, 'Web of Science') !== false) return 'WoS';
    return '';
}
function extractSintaD(string $kat): string {
    if (preg_match('/SINTA[\s\-]*([1-6])/i', $kat, $m)) return 'SINTA ' . $m[1];
    return '';
}

function xmlEscD(string $s): string { return htmlspecialchars($s, ENT_QUOTES|ENT_XML1, 'UTF-8'); }
function colLetD(int $i): string { $i++; $c=''; while($i>0){$i--;$c=chr(65+$i%26).$c;$i=intdiv($i,26);} return $c; }
function caD2(int $col, int $row): string { return colLetD($col).$row; }

$strings = [];
$strIdx = function(string $s) use (&$strings): int {
    $s=trim($s); $k=array_search($s,$strings,true);
    if($k===false){$strings[]=$s;$k=count($strings)-1;} return $k;
};
foreach ($headers as $h) { $strIdx($h); }

$rows = [];
foreach ($pubs as $i => $p) {
    $kat = $p['kategori_publikasi'] ?? 'Lainnya';
    $rows[] = [
        $i + 1,                                         //  0  No (numeric)
        $p['nidn']               ?? '',                 //  1
        $p['dosen_nama']         ?? '',                 //  2
        $p['judul_artikel']      ?? '',                 //  3
        $kat,                                           //  4  Kategori
        extractScopusD($kat),                           //  5  Scopus  ← BARU
        extractSintaD($kat),                            //  6  Sinta   ← BARU
        $p['nama_jurnal']        ?? '',                 //  7
        $p['penulis']            ?? '',                 //  8
        $p['doi']                ?? '',                 //  9
        (int)($p['tahun_terbit'] ?? 0) ?: '',           // 10  Tahun (numeric)
        $p['status_publikasi']   ?? '',                 // 11
        $p['kata_kunci']         ?? '',                 // 12
        $p['link_artikel']       ?? '',                 // 13
        $p['abstrak']            ?? '',                 // 14
        date('d/m/Y', strtotime($p['created_at'])),    // 15
    ];
}
foreach ($rows as $row) {
    foreach ($row as $ci => $val) {
        if ($ci !== 0 && $ci !== 10) { $strIdx((string)$val); }
    }
}

$contentTypes='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
$rels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
$wbRels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
$workbook='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Publikasi Dosen" sheetId="1" r:id="rId1"/></sheets></workbook>';
$styles='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="4"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font><font><b/><sz val="13"/><name val="Calibri"/><color rgb="FFFFFFFF"/></font><font><sz val="10"/><name val="Calibri"/></font></fonts>
  <fills count="5"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF8C0C4C"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFFDF0F7"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE8F5E9"/></patternFill></fill></fills>
  <borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFD1D5DB"/></left><right style="thin"><color rgb="FFD1D5DB"/></right><top style="thin"><color rgb="FFD1D5DB"/></top><bottom style="thin"><color rgb="FFD1D5DB"/></bottom></border></borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="7">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="top"/></xf>
    <xf numFmtId="0" fontId="3" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="top"/></xf>
    <xf numFmtId="0" fontId="3" fillId="0" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>
  </cellXfs>
</styleSheet>';

$colWidths=[4,14,35,50,25,14,14,35,38,28,10,18,35,38,60,14];
$numCols=count($headers); $lastCol=colLetD($numCols-1);
$ws='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
$ws.='<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'."\n";
$ws.='  <sheetViews><sheetView tabSelected="1" workbookViewId="0"><pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'."\n";
$ws.='  <cols>'."\n";
foreach($colWidths as $ci=>$w){$ws.='    <col min="'.($ci+1).'" max="'.($ci+1).'" width="'.$w.'" customWidth="1"/>'."\n";}
$ws.='  </cols>'."\n";
$ws.='  <sheetData>'."\n";
$ws.='    <row r="1" ht="30" customHeight="1"><c r="A1" s="1" t="s"><v>'.$strIdx('REKAP PUBLIKASI ILMIAH DOSEN — PASCASARJANA NPU — '.date('d F Y')).'</v></c></row>'."\n";
$ws.='    <row r="2" ht="16" customHeight="1"><c r="A2" s="1" t="s"><v>'.$strIdx('Total: '.count($rows).' publikasi  |  Dicetak: '.date('d/m/Y H:i').' WIB').'</v></c></row>'."\n";
$ws.='    <row r="3"/>'."\n";
$ws.='    <row r="4" ht="35" customHeight="1">'."\n";
foreach($headers as $ci=>$h){$ws.='      <c r="'.caD2($ci,4).'" s="2" t="s"><v>'.$strIdx($h).'</v></c>'."\n";}
$ws.='    </row>'."\n";
foreach($rows as $ri=>$row){
    $er=$ri+5;
    $ws.='    <row r="'.$er.'" ht="55" customHeight="1">'."\n";
    foreach($row as $ci=>$val){
        $addr=caD2($ci,$er);
        if($ci===0){ $ws.='      <c r="'.$addr.'" s="4"><v>'.(int)$val.'</v></c>'."\n"; }
        elseif($ci===5||$ci===6){ $sStyle=($ri%2===0)?4:4; $ws.='      <c r="'.$addr.'" s="'.$sStyle.'" t="s"><v>'.$strIdx((string)$val).'</v></c>'."\n"; }
        elseif($ci===10){ if($val!==''){$ws.='      <c r="'.$addr.'" s="4"><v>'.(int)$val.'</v></c>'."\n";}else{$ws.='      <c r="'.$addr.'" s="4" t="s"><v>'.$strIdx('').'</v></c>'."\n";} }
        elseif($ci===11){ $sStyle=str_contains(strtolower((string)$val),'publish')?5:3; $ws.='      <c r="'.$addr.'" s="'.$sStyle.'" t="s"><v>'.$strIdx((string)$val).'</v></c>'."\n"; }
        else{ $sStyle=($ri%2===0)?3:6; $ws.='      <c r="'.$addr.'" s="'.$sStyle.'" t="s"><v>'.$strIdx((string)$val).'</v></c>'."\n"; }
    }
    $ws.='    </row>'."\n";
}
$ws.='  </sheetData>'."\n";
$ws.='  <mergeCells count="2"><mergeCell ref="A1:'.$lastCol.'1"/><mergeCell ref="A2:'.$lastCol.'2"/></mergeCells>'."\n";
$ws.='</worksheet>';
$ssXml='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
$ssXml.='<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($strings).'" uniqueCount="'.count($strings).'">'."\n";
foreach($strings as $s){$ssXml.='  <si><t xml:space="preserve">'.xmlEscD($s).'</t></si>'."\n";}
$ssXml.='</sst>';

$tmp=tempnam(sys_get_temp_dir(),'xlsx_admin_dosen_');
$zip=new ZipArchive(); $zip->open($tmp,ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml',$contentTypes);
$zip->addFromString('_rels/.rels',$rels);
$zip->addFromString('xl/workbook.xml',$workbook);
$zip->addFromString('xl/_rels/workbook.xml.rels',$wbRels);
$zip->addFromString('xl/styles.xml',$styles);
$zip->addFromString('xl/sharedStrings.xml',$ssXml);
$zip->addFromString('xl/worksheets/sheet1.xml',$ws);
$zip->close();

$fname='Rekap_Publikasi_Dosen_'.date('Ymd').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$fname.'"');
header('Content-Length: '.filesize($tmp));
header('Cache-Control: max-age=0');
readfile($tmp); unlink($tmp); exit;
