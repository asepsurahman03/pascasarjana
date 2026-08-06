<?php
$file = 'c:/xampp/htdocs/Aktifnonaktif/Contoh Output/PERNYATAAN PENGUNDURAN DIRI MAHASISWA.docx';
$zip = new ZipArchive();
if ($zip->open($file) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    
    $syms = $dom->getElementsByTagNameNS($ns, 'sym');
    foreach ($syms as $i => $sym) {
        echo "Sym $i: font=" . $sym->getAttribute('w:font') . " char=" . $sym->getAttribute('w:char') . "\n";
    }
}
