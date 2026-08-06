<?php
$source = 'c:/xampp/htdocs/Aktifnonaktif/Contoh Output/PERNYATAAN PENGUNDURAN DIRI MAHASISWA.docx';
$target = 'c:/xampp/htdocs/Aktifnonaktif/storage/template_tagged.docx';

if (!is_dir(dirname($target))) {
    mkdir(dirname($target), 0777, true);
}
copy($source, $target);

$zip = new ZipArchive();
if ($zip->open($target) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    
    $tables = $dom->getElementsByTagNameNS($ns, 'tbl');
    $tbl1 = $tables->item(1); // Table 1
    
    // Helper to insert text into cell
    $setCell = function($rowIdx, $colIdx, $text) use ($tbl1, $ns, $dom) {
        $rows = $tbl1->getElementsByTagNameNS($ns, 'tr');
        $row = $rows->item($rowIdx);
        if (!$row) return;
        
        $cells = [];
        foreach ($row->childNodes as $n) {
            if ($n->localName === 'tc') $cells[] = $n;
        }
        $cell = $cells[$colIdx] ?? null;
        if (!$cell) return;
        
        // Keep first paragraph and clear its runs, remove other paragraphs
        $paragraphs = [];
        foreach ($cell->childNodes as $n) {
            if ($n->localName === 'p') $paragraphs[] = $n;
        }
        if (count($paragraphs) === 0) return;
        
        $p = $paragraphs[0];
        
        // Remove all runs in the first paragraph
        $runs = [];
        foreach ($p->childNodes as $n) {
            if ($n->localName === 'r') $runs[] = $n;
        }
        foreach ($runs as $r) {
            $p->removeChild($r);
        }
        
        // Remove all other paragraphs
        for ($i = 1; $i < count($paragraphs); $i++) {
            $cell->removeChild($paragraphs[$i]);
        }
        
        // Add new run with text
        $newR = $dom->createElementNS($ns, 'w:r');
        // Add run properties (11pt size)
        $rPr = $dom->createElementNS($ns, 'w:rPr');
        $sz = $dom->createElementNS($ns, 'w:sz');
        $sz->setAttribute('w:val', '22'); // 11pt
        $szCs = $dom->createElementNS($ns, 'w:szCs');
        $szCs->setAttribute('w:val', '22');
        $rPr->appendChild($sz);
        $rPr->appendChild($szCs);
        $newR->appendChild($rPr);
        
        $newT = $dom->createElementNS($ns, 'w:t', $text);
        $newR->appendChild($newT);
        $p->appendChild($newR);
    };
    
    // Tagging
    $setCell(0, 3, '${TANGGAL_SURAT}');
    $setCell(1, 1, '${NAMA_PEMOHON}');
    $setCell(2, 1, '${NIM}');
    $setCell(3, 1, '${ANGKATAN}');
    $setCell(4, 1, '${PROGRAM_STUDI}');
    $setCell(7, 1, '${ALASAN}');
    
    // For checkboxes, we will just replace the "BEASISWA" and "NON BEASISWA" text with ${CB_BEASISWA} and ${CB_NON}
    $setCell(5, 1, '${CB_BEASISWA}');
    $setCell(5, 2, '${CB_NON}');
    $setCell(6, 1, '${CB_YA}');
    $setCell(6, 2, '${CB_TIDAK}');
    
    // For signature
    $setCell(10, 0, '${TTD_MHS}');
    
    $newXml = $dom->saveXML();
    $zip->addFromString('word/document.xml', $newXml);
    $zip->close();
    echo "Successfully tagged template!\n";
} else {
    echo "Failed to open zip.\n";
}
