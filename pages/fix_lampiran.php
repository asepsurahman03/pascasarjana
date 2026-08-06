<?php
function processFile($filename) {
    $content = file_get_contents($filename);
    if (preg_match('/(\[ \/\/ Lampiran 7:.*?)(,?\s*\[ \/\/ Lampiran 8:)/s', $content, $m, PREG_OFFSET_CAPTURE)) {
        $start = $m[1][1];
        $end = $m[2][1];
        $lamp7 = substr($content, $start, $end - $start);
        
        $lamp7_new = str_replace('font-size:11pt', 'font-size:12pt', $lamp7);
        $lamp7_new = str_replace('<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top;">Sukabumi', '<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:5px; vertical-align:top;">Sukabumi', $lamp7_new);
        $lamp7_new = str_replace('<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top;"></td>', '<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:5px; vertical-align:top;"></td>', $lamp7_new);
        $lamp7_new = str_replace('<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top; font-weight:bold;">Ketua Pembimbing,</td>', '<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:50px; vertical-align:top; font-weight:bold;">Ketua Pembimbing,</td>', $lamp7_new);
        $lamp7_new = str_replace('<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top; font-weight:bold;">Anggota Pembimbing,</td>', '<td class="py-3 px-4" style="width:50%; border:none; padding-bottom:50px; vertical-align:top; font-weight:bold;">Anggota Pembimbing,</td>', $lamp7_new);
        $lamp7_new = str_replace('<td class="py-3 px-4" colspan="2" style="border:none; height:30px;"></td>', '<td class="py-3 px-4" colspan="2" style="border:none; height:10px;"></td>', $lamp7_new);
        $lamp7_new = str_replace('<td class="py-3 px-4" colspan="2" style="border:none; text-align:center; padding-bottom:60px; vertical-align:top;">Mengetahui', '<td class="py-3 px-4" colspan="2" style="border:none; text-align:center; padding-bottom:50px; vertical-align:top;">Mengetahui', $lamp7_new);
        
        $new_content = substr_replace($content, $lamp7_new, $start, $end - $start);
        file_put_contents($filename, $new_content);
        echo "SUCCESS: $filename updated\n";
    } else {
        echo "FAILED: regex not matched for $filename\n";
    }
}

processFile('c:/xampp/htdocs/webdummy/pages/cetak_lampiran.php');
processFile('c:/xampp/htdocs/webdummy/pages/cetak_lampiran_proposal.php');
?>
