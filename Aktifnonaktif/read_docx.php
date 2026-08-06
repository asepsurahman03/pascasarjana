<?php
/**
 * Deep DOCX Inspector — mengekstrak SEMUA detail formatting
 */
$file = 'c:/xampp/htdocs/Aktifnonaktif/Contoh Output/PERNYATAAN PENGUNDURAN DIRI MAHASISWA.docx';
$zip = new ZipArchive();
if ($zip->open($file) !== TRUE) die('FAILED to open');

$xml = $zip->getFromName('word/document.xml');
$zip->close();

$dom = new DOMDocument();
$dom->loadXML($xml);
$ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

function getAttr($node, $name) {
    foreach (['w:'.$name, $name] as $attr) {
        $val = $node->getAttribute($attr);
        if ($val !== '') return $val;
    }
    return '';
}

function getCellText($cell, $ns) {
    $text = '';
    $paras = $cell->getElementsByTagNameNS($ns, 'p');
    foreach ($paras as $p) {
        $line = '';
        $runs = $p->getElementsByTagNameNS($ns, 'r');
        foreach ($runs as $r) {
            $ts = $r->getElementsByTagNameNS($ns, 't');
            foreach ($ts as $t) { $line .= $t->nodeValue; }
        }
        $text .= $line . "\n";
    }
    return trim($text);
}

function getCellProps($cell, $ns) {
    $props = [];
    $tcPr = $cell->getElementsByTagNameNS($ns, 'tcPr')->item(0);
    if (!$tcPr) return $props;
    
    $tcW = $tcPr->getElementsByTagNameNS($ns, 'tcW')->item(0);
    if ($tcW) {
        $props['width'] = $tcW->getAttribute('w:w');
        $props['wtype'] = $tcW->getAttribute('w:type');
    }
    
    $gridSpan = $tcPr->getElementsByTagNameNS($ns, 'gridSpan')->item(0);
    if ($gridSpan) $props['span'] = $gridSpan->getAttribute('w:val');
    
    $shd = $tcPr->getElementsByTagNameNS($ns, 'shd')->item(0);
    if ($shd) {
        $props['fill'] = $shd->getAttribute('w:fill');
        $props['color'] = $shd->getAttribute('w:color');
    }
    
    $vAlign = $tcPr->getElementsByTagNameNS($ns, 'vAlign')->item(0);
    if ($vAlign) $props['vAlign'] = $vAlign->getAttribute('w:val');
    
    return $props;
}

function getParaProps($para, $ns) {
    $props = [];
    $pPr = $para->getElementsByTagNameNS($ns, 'pPr')->item(0);
    if (!$pPr) return $props;
    
    $jc = $pPr->getElementsByTagNameNS($ns, 'jc')->item(0);
    if ($jc) $props['align'] = $jc->getAttribute('w:val');
    
    $ind = $pPr->getElementsByTagNameNS($ns, 'ind')->item(0);
    if ($ind) $props['indent'] = $ind->getAttribute('w:left');
    
    $spacing = $pPr->getElementsByTagNameNS($ns, 'spacing')->item(0);
    if ($spacing) {
        $props['spacingBefore'] = $spacing->getAttribute('w:before');
        $props['spacingAfter'] = $spacing->getAttribute('w:after');
    }
    
    // Font/size in run properties
    $rPr = $pPr->getElementsByTagNameNS($ns, 'rPr')->item(0);
    if ($rPr) {
        $sz = $rPr->getElementsByTagNameNS($ns, 'sz')->item(0);
        if ($sz) $props['fontSize'] = $sz->getAttribute('w:val') . ' (half-points = ' . ($sz->getAttribute('w:val')/2) . 'pt)';
        $b = $rPr->getElementsByTagNameNS($ns, 'b')->item(0);
        if ($b) $props['bold'] = true;
    }
    
    return $props;
}

function getRunStyle($run, $ns) {
    $style = [];
    $rPr = $run->getElementsByTagNameNS($ns, 'rPr')->item(0);
    if (!$rPr) return $style;
    
    $sz = $rPr->getElementsByTagNameNS($ns, 'sz')->item(0);
    if ($sz) $style['size'] = $sz->getAttribute('w:val')/2 . 'pt';
    
    $b = $rPr->getElementsByTagNameNS($ns, 'b')->item(0);
    if ($b) $style['bold'] = true;
    
    $i = $rPr->getElementsByTagNameNS($ns, 'i')->item(0);
    if ($i) $style['italic'] = true;
    
    $color = $rPr->getElementsByTagNameNS($ns, 'color')->item(0);
    if ($color) $style['color'] = $color->getAttribute('w:val');
    
    $font = $rPr->getElementsByTagNameNS($ns, 'rFonts')->item(0);
    if ($font) $style['font'] = $font->getAttribute('w:ascii');
    
    return $style;
}

echo "=== DOCX DEEP ANALYSIS ===\n\n";
echo "Page width (body): looking in settings...\n";

// Page margins from sectPr
$sectPrs = $dom->getElementsByTagNameNS($ns, 'sectPr');
foreach ($sectPrs as $sp) {
    $pgSz = $sp->getElementsByTagNameNS($ns, 'pgSz')->item(0);
    $pgMar = $sp->getElementsByTagNameNS($ns, 'pgMar')->item(0);
    if ($pgSz) {
        echo "Page size: w=" . $pgSz->getAttribute('w:w') . " h=" . $pgSz->getAttribute('w:h') . " (twips, /20 = pt)\n";
    }
    if ($pgMar) {
        echo "Margins: top=" . $pgMar->getAttribute('w:top') . " bottom=" . $pgMar->getAttribute('w:bottom')
             . " left=" . $pgMar->getAttribute('w:left') . " right=" . $pgMar->getAttribute('w:right') . " (twips)\n";
    }
}

echo "\n";

$tables = $dom->getElementsByTagNameNS($ns, 'tbl');
foreach ($tables as $tIdx => $tbl) {
    echo "╔══════════════════════════════════════════╗\n";
    echo "║ TABLE $tIdx\n";
    echo "╚══════════════════════════════════════════╝\n";
    
    // Table grid columns
    $tblGrid = $tbl->getElementsByTagNameNS($ns, 'tblGrid')->item(0);
    if ($tblGrid) {
        $gridCols = $tblGrid->getElementsByTagNameNS($ns, 'gridCol');
        $gridWidths = [];
        foreach ($gridCols as $gc) {
            $gridWidths[] = $gc->getAttribute('w:w');
        }
        echo "Grid columns (" . count($gridWidths) . " cols): " . implode(', ', $gridWidths) . "\n";
        echo "Total width: " . array_sum($gridWidths) . " twips\n\n";
    }
    
    $rows = $tbl->getElementsByTagNameNS($ns, 'tr');
    $rowIdx = 0;
    foreach ($rows as $row) {
        // Skip rows inside nested tables
        if ($row->parentNode !== $tbl) continue;
        
        echo "  ┌─ ROW $rowIdx ─────────────────────────────\n";
        
        $cells = $row->childNodes;
        $cellIdx = 0;
        foreach ($cells as $cell) {
            if ($cell->localName !== 'tc') continue;
            
            $text = getCellText($cell, $ns);
            $cProps = getCellProps($cell, $ns);
            
            // Get first paragraph alignment
            $firstPara = $cell->getElementsByTagNameNS($ns, 'p')->item(0);
            $pProps = $firstPara ? getParaProps($firstPara, $ns) : [];
            
            // Get first run style
            $firstRun = $cell->getElementsByTagNameNS($ns, 'r')->item(0);
            $rStyle = $firstRun ? getRunStyle($firstRun, $ns) : [];
            
            echo "  │  CELL $cellIdx:\n";
            echo "  │    Text   : " . json_encode($text, JSON_UNESCAPED_UNICODE) . "\n";
            echo "  │    Width  : " . ($cProps['width'] ?? '?') . " twips";
            if (!empty($cProps['span'])) echo " | gridSpan=" . $cProps['span'];
            echo "\n";
            if (!empty($cProps['fill']) && $cProps['fill'] !== 'auto') {
                echo "  │    Fill   : #" . $cProps['fill'] . "\n";
            }
            if (!empty($pProps['align'])) echo "  │    Align  : " . $pProps['align'] . "\n";
            if (!empty($rStyle)) echo "  │    Style  : " . json_encode($rStyle) . "\n";
            if (!empty($pProps['spacingBefore'])) echo "  │    SpacingBefore: " . $pProps['spacingBefore'] . "\n";
            
            $cellIdx++;
        }
        echo "  └──────────────────────────────────────\n\n";
        $rowIdx++;
    }
}
