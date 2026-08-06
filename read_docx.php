<?php
$zip = new ZipArchive;
$file = 'C:\xampp\htdocs\webdummy\Contoh Lampiran\Lampiran 1. Prof. Ts. Deden Witarsyah, S.T., M.Eng., Ph.D. Ketua Pembimbing – Form Penilaian & Revisi Seminar Proposal (1).docx';
if ($zip->open($file) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $xml = str_replace('</w:p>', "\n", $xml);
    $text = strip_tags($xml);
    file_put_contents('doc_extracted.txt', $text);
    $zip->close();
    echo "Extracted to doc_extracted.txt";
} else {
    echo "Failed to open zip";
}
