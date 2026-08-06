<?php
// Script to fix table row padding in Lampiran 7 for both files

function processFile($filename) {
    $content = file_get_contents($filename);
    if (preg_match('/(\[ \/\/ Lampiran 7:.*?)(,?\s*\[ \/\/ Lampiran 8:)/s', $content, $m, PREG_OFFSET_CAPTURE)) {
        $start = $m[1][1];
        $end = $m[2][1];
        $lamp7 = substr($content, $start, $end - $start);
        
        // Fix table rows - old padding:8px or padding:15px => padding:25px with height
        $lamp7_new = preg_replace(
            "/<td class="py-3 px-4" style='text-align:center; padding:(8px|15px);'>(\\\$i)<\/td>\s*\n\s*<td class="py-3 px-4" class='editable-cell' contenteditable='true'><\/td>\s*\n\s*<td class="py-3 px-4" class='editable-cell' contenteditable='true'><\/td>\s*\n\s*<td class="py-3 px-4" class='editable-cell' contenteditable='true'><\/td>/",
            "<td class="py-3 px-4" style='text-align:center; padding:25px; height:40px;'>\$2</td>\n                        <td class="py-3 px-4" class='editable-cell' contenteditable='true' style='padding:5px; height:40px;'></td>\n                        <td class="py-3 px-4" class='editable-cell' contenteditable='true' style='padding:5px; height:40px;'></td>\n                        <td class="py-3 px-4" class='editable-cell' contenteditable='true' style='padding:5px; height:40px;'></td>",
            $lamp7
        );
        
        if ($lamp7_new === null) {
            echo "preg_replace error\n";
            return;
        }

        // Also remove the <br> between the two tables
        $lamp7_new = str_replace(
            "</table>\n        \n        <br>\n        <table",
            "</table>\n        <table",
            $lamp7_new
        );
        
        $new_content = substr_replace($content, $lamp7_new, $start, $end - $start);
        file_put_contents($filename, $new_content);
        echo "SUCCESS: $filename updated\n";
        
        // Check if anything actually changed
        if ($lamp7 === $lamp7_new) {
            echo "  WARNING: No changes were made to lamp7 block!\n";
        }
    } else {
        echo "FAILED: Lampiran 7 block not found in $filename\n";
    }
}

processFile('c:/xampp/htdocs/webdummy/pages/cetak_lampiran.php');
processFile('c:/xampp/htdocs/webdummy/pages/cetak_lampiran_proposal.php');
?>
