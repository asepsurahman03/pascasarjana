<?php
$content = file_get_contents('c:/xampp/htdocs/webdummy/pages/cetak_lampiran.php');
if (preg_match('/(\[ \/\/ Lampiran 7: Persetujuan Pembimbing.*?\r?\n\s*\],?\r?\n)(\s*\[ \/\/ Lampiran 8: Lembar Bimbingan.*?\r?\n\s*\],?\r?\n)(\s*\[ \/\/ Lampiran 9)/s', $content, $m)) {
    $lamp7 = $m[1];
    $lamp8 = $m[2];
    
    // Change comments
    $lamp7_new = str_replace('Lampiran 7: Persetujuan Pembimbing', 'Lampiran 8: Persetujuan Pembimbing', $lamp7);
    $lamp8_new = str_replace('Lampiran 8: Lembar Bimbingan', 'Lampiran 7: Logbook Bimbingan', $lamp8);
    
    // Fix padding in Logbook signature
    $lamp8_new = str_replace('<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top;">Sukabumi', '<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:10px; vertical-align:top;">Sukabumi', $lamp8_new);
    $lamp8_new = str_replace('<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top;"></td>', '<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:10px; vertical-align:top;"></td>', $lamp8_new);
    
    // Reassemble
    $new_content = str_replace($m[1] . $m[2], $lamp8_new . $lamp7_new, $content);
    file_put_contents('c:/xampp/htdocs/webdummy/pages/cetak_lampiran.php', $new_content);
    echo "SUCCESS: cetak_lampiran.php updated\n";
} else {
    echo "FAILED: regex not matched for cetak_lampiran.php\n";
}

$content2 = file_get_contents('c:/xampp/htdocs/webdummy/pages/cetak_lampiran_proposal.php');
if (preg_match('/(\[ \/\/ Lampiran 7: Persetujuan Pembimbing.*?\r?\n\s*\],?\r?\n)(\s*\[ \/\/ Lampiran 8: Lembar Bimbingan.*?\r?\n\s*\],?\r?\n)(\s*\[ \/\/ Lampiran 9)/s', $content2, $m2)) {
    $lamp7 = $m2[1];
    $lamp8 = $m2[2];
    
    // Change comments
    $lamp7_new = str_replace('Lampiran 7: Persetujuan Pembimbing', 'Lampiran 8: Persetujuan Pembimbing', $lamp7);
    $lamp8_new = str_replace('Lampiran 8: Lembar Bimbingan', 'Lampiran 7: Logbook Bimbingan', $lamp8);
    
    // Fix padding in Logbook signature
    $lamp8_new = str_replace('<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top;">Sukabumi', '<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:10px; vertical-align:top;">Sukabumi', $lamp8_new);
    $lamp8_new = str_replace('<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top;"></td>', '<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:10px; vertical-align:top;"></td>', $lamp8_new);
    
    // Reassemble
    $new_content2 = str_replace($m2[1] . $m2[2], $lamp8_new . $lamp7_new, $content2);
    file_put_contents('c:/xampp/htdocs/webdummy/pages/cetak_lampiran_proposal.php', $new_content2);
    echo "SUCCESS: cetak_lampiran_proposal.php updated\n";
} else {
    echo "FAILED: regex not matched for cetak_lampiran_proposal.php\n";
}
?>
