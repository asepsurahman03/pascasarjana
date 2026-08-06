<?php
$file = 'c:/xampp/htdocs/Aktifnonaktif/Contoh Output/PERNYATAAN PENGUNDURAN DIRI MAHASISWA.docx';
$zip = new ZipArchive();
if ($zip->open($file) === TRUE) {
    echo "Media files in docx:\n";
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (strpos($name, 'word/media/') === 0) {
            echo "- " . $name . "\n";
        }
    }
    $zip->close();
}
