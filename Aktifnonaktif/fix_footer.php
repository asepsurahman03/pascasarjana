<?php
$docxFile = __DIR__ . '/storage/template_tagged.docx';
$tempFile = sys_get_temp_dir() . '/fix_temp2.docx';
copy($docxFile, $tempFile);

$zip = new ZipArchive();
if ($zip->open($tempFile) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    
    // There are 2 anchor images at the bottom of the document.
    // Image 1: rId6 = bawah.jpg (main footer - address + social media + logos), positionV from page at 9560000
    // Image 2: rId7 = Picture 11 (extra accreditation strip), positionV from page at 10153471
    
    // Remove the second anchor image (rId7/Picture 11) since rId6 bawah.jpg contains everything
    // The second <w:r> in the last paragraph contains wp:anchor with rId7
    
    // Strategy: find the w:r block containing rId7 and remove it
    // Pattern: <w:r ...><w:rPr><w:noProof/></w:rPr><w:drawing><wp:anchor...rId7...</wp:anchor></w:drawing></w:r>
    
    $pattern = '/<w:r[^>]*>\s*<w:rPr>\s*<w:noProof\/>\s*<\/w:rPr>\s*<w:drawing>\s*<wp:anchor[^>]*>(?:(?!<\/wp:anchor>).)*rId7(?:(?!<\/wp:anchor>).)*<\/wp:anchor>\s*<\/w:drawing>\s*<\/w:r>/s';
    
    $xml_new = preg_replace($pattern, '', $xml);
    
    if ($xml_new !== $xml) {
        echo "Second image (rId7) removed successfully!\n";
        $zip->addFromString('word/document.xml', $xml_new);
    } else {
        echo "Pattern not found. Trying alternative...\n";
        
        // Alternative: just find the anchor with positionV 10153471 and remove the whole <w:r>...</w:r> block containing it
        $pattern2 = '/<w:r[^>]*>(?:(?!<w:r[ >]).)*?10153471(?:(?!<\/w:r>).)*?<\/w:r>/s';
        $xml_new2 = preg_replace($pattern2, '', $xml);
        
        if ($xml_new2 !== $xml) {
            echo "Second image removed via offset pattern!\n";
            $zip->addFromString('word/document.xml', $xml_new2);
        } else {
            echo "Still not found. Debug - searching for posOffset patterns:\n";
            preg_match_all('/10153471/', $xml, $m);
            echo "Found " . count($m[0]) . " occurrences of 10153471\n";
            
            preg_match_all('/rId7/', $xml, $m2);
            echo "Found " . count($m2[0]) . " occurrences of rId7\n";
        }
    }
    
    $zip->close();
    copy($tempFile, $docxFile);
    echo "Done.";
} else {
    echo "Failed to open docx";
}
