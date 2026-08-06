<?php
$file = 'c:/xampp/htdocs/Aktifnonaktif/Contoh Output/PERNYATAAN PENGUNDURAN DIRI MAHASISWA.docx';
$zip = new ZipArchive();
if ($zip->open($file) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    
    $tables = $dom->getElementsByTagNameNS($ns, 'tbl');
    $tbl0 = $tables->item(0);
    $tblPr = $tbl0->getElementsByTagNameNS($ns, 'tblPr')->item(0);
    if ($tblPr) {
        $borders = $tblPr->getElementsByTagNameNS($ns, 'tblBorders')->item(0);
        if ($borders) {
            echo "TABLE 0 Borders:\n";
            foreach (['top', 'left', 'bottom', 'right', 'insideH', 'insideV'] as $b) {
                $node = $borders->getElementsByTagNameNS($ns, $b)->item(0);
                if ($node) {
                    echo "- $b: val=" . $node->getAttribute('w:val') . " sz=" . $node->getAttribute('w:sz') . "\n";
                }
            }
        } else {
            echo "TABLE 0 has no tblBorders tag.\n";
        }
    }
}
