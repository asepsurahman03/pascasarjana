<?php
$file = 'c:/xampp/htdocs/Aktifnonaktif/Contoh Output/PERNYATAAN PENGUNDURAN DIRI MAHASISWA.docx';
$zip = new ZipArchive();
if ($zip->open($file) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    
    $rows = $dom->getElementsByTagNameNS($ns, 'tr');
    $row5 = $rows->item(6); // Table 1, Row 5 (actually 6th row of document? Table 0 has 1 row, Table 1 has 12 rows. Total rows = 13. Index 6 should be Table 1 Row 5.)
    if ($row5) {
        $cells = $row5->getElementsByTagNameNS($ns, 'tc');
        $cell1 = $cells->item(1); // "BEASISWA" cell
        if ($cell1) {
            echo "Cell XML:\n" . $dom->saveXML($cell1) . "\n";
        }
    }
}
