<?php
$docxFile = __DIR__ . '/storage/template_tagged.docx';
$zip = new ZipArchive();
if ($zip->open($docxFile) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    
    preg_match_all('/<wp:positionV[^>]*>.*?<\/wp:positionV>/', $xml, $matches);
    echo "positionV found:\n";
    foreach ($matches[0] as $m) {
        echo $m . "\n---\n";
    }
}
