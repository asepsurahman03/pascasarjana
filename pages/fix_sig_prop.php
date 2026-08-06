<?php
function processFile($filename) {
    $content = file_get_contents($filename);
    if (preg_match('/(\[ \/\/ Lampiran 7:.*?)(,?\s*\[ \/\/ Lampiran 8:)/s', $content, $m, PREG_OFFSET_CAPTURE)) {
        $start = $m[1][1];
        $end = $m[2][1];
        $lamp7 = substr($content, $start, $end - $start);
        
        // Change Ketentuan font size to 11pt
        $lamp7_new = str_replace('<div style="font-size:12pt; font-family:Rockwell,serif; margin-bottom:30px;">', '<div style="font-size:11pt; font-family:Rockwell,serif; margin-bottom:30px;">', $lamp7);
        
        $old_sig = '<table style="width:100%; border:none; text-align:center; font-size:12pt; font-family:Rockwell,serif;">
            <tr>
                <td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top;"></td>
                <td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top;">Sukabumi, ................................. 2026</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top; font-weight:bold;">Ketua Pembimbing,</td>
                <td class="py-3 px-4" style="width:50%; border:none; padding-bottom:60px; vertical-align:top; font-weight:bold;">Anggota Pembimbing,</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style="border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;">" . getTtdDosen($ketuaPembimbing) . "$ketuaPembimbing</td>
                <td class="py-3 px-4" style="border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;">" . getTtdDosen($anggotaPembimbing) . "$anggotaPembimbing</td>
            </tr>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" colspan="2" style="border:none; height:30px;"></td></tr>
            <tr>
                <td class="py-3 px-4" colspan="2" style="border:none; text-align:center; padding-bottom:60px; vertical-align:top;">Mengetahui,<br><b style="display:block; margin-top:5px;">Ketua Program Studi Magister Informatika</b></td>
            </tr>
            <tr>
                <td class="py-3 px-4" colspan="2" style="border:none; text-align:center; vertical-align:bottom;">" . getTtdDosen($prodiData[\'nama_kaprodi\']) . e($prodiData[\'nama_kaprodi\']) . "</td>
            </tr>
        </table>';

        $new_sig = '<table style="width:100%; border:none; text-align:center; font-size:12pt; font-family:Rockwell,serif;">
            <tr>
                <td class="py-3 px-4" style="width:50%; border:none; padding-bottom:5px; vertical-align:top;"></td>
                <td class="py-3 px-4" style="width:50%; border:none; padding-bottom:5px; vertical-align:top;">Sukabumi, ................................. 2026</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style="width:50%; border:none; padding-bottom:50px; vertical-align:top; font-weight:bold;">Ketua Pembimbing,</td>
                <td class="py-3 px-4" style="width:50%; border:none; padding-bottom:50px; vertical-align:top; font-weight:bold;">Anggota Pembimbing,</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style="border:none; padding-top:2px; padding-bottom:5px; vertical-align:bottom;">" . getTtdDosen($ketuaPembimbing) . "$ketuaPembimbing</td>
                <td class="py-3 px-4" style="border:none; padding-top:2px; padding-bottom:5px; vertical-align:bottom;">" . getTtdDosen($anggotaPembimbing) . "$anggotaPembimbing</td>
            </tr>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" colspan="2" style="border:none; height:10px;"></td></tr>
            <tr>
                <td class="py-3 px-4" colspan="2" style="border:none; text-align:center; padding-bottom:50px; vertical-align:top;">Mengetahui,<br><b style="display:block; margin-top:5px;">Ketua Program Studi Magister Informatika</b></td>
            </tr>
            <tr>
                <td class="py-3 px-4" colspan="2" style="border:none; text-align:center; vertical-align:bottom;">" . getTtdDosen($prodiData[\'nama_kaprodi\']) . e($prodiData[\'nama_kaprodi\']) . "</td>
            </tr>
        </table>';

        $lamp7_new = str_replace($old_sig, $new_sig, $lamp7_new);
        
        $new_content = substr_replace($content, $lamp7_new, $start, $end - $start);
        file_put_contents($filename, $new_content);
        echo "SUCCESS: $filename updated\n";
    } else {
        echo "FAILED: regex not matched for $filename\n";
    }
}

processFile('c:/xampp/htdocs/webdummy/pages/cetak_lampiran_proposal.php');
?>
