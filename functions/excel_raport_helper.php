<?php
/**
 * Helper: Parse XLSX file tanpa library external (ZipArchive + SimpleXML)
 * Return array dosen dari sheet "Data Dosen"
 */

function parseExcelRaport(string $filePath): array {
    if (!file_exists($filePath)) {
        return ['error' => 'File tidak ditemukan: ' . $filePath];
    }

    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        return ['error' => 'Gagal membuka file Excel'];
    }

    // Baca shared strings
    $sharedStrings = [];
    $ss = $zip->getFromName('xl/sharedStrings.xml');
    if ($ss) {
        $ssXml = @simplexml_load_string($ss);
        if ($ssXml) {
            foreach ($ssXml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } else {
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }
    }

    // Baca workbook untuk nama sheet
    $wb = $zip->getFromName('xl/workbook.xml');
    $wbXml = @simplexml_load_string($wb);

    // Baca rels untuk path sheet
    $rels = $zip->getFromName('xl/_rels/workbook.xml.rels');
    $relsXml = @simplexml_load_string($rels);
    $sheetFiles = [];
    if ($relsXml) {
        foreach ($relsXml->Relationship as $rel) {
            $a = $rel->attributes();
            $target = (string)$a['Target'];
            $id     = (string)$a['Id'];
            if (strpos($target, 'sheet') !== false) {
                $sheetFiles[$id] = 'xl/' . ltrim($target, '/');
            }
        }
    }

    // Temukan sheet "Data Dosen"
    $targetSheetFile   = null;
    $rapotSheetFile    = null;
    $rataRataSheetFile = null;

    if ($wbXml) {
        foreach ($wbXml->sheets->sheet as $sheet) {
            $attrs  = $sheet->attributes();
            $rAttrs = $sheet->attributes('r', true);
            $name   = strtolower(trim((string)$attrs['name']));
            $rId    = (string)$rAttrs['id'];

            if (strpos($name, 'data dosen') !== false || $name === 'data dosen') {
                $targetSheetFile = $sheetFiles[$rId] ?? null;
            } elseif ($name === 'rapot' || $name === 'rapot dosen' || $name === 'raport') {
                $rapotSheetFile = $sheetFiles[$rId] ?? null;
            } elseif (strpos($name, 'rata') !== false) {
                $rataRataSheetFile = $sheetFiles[$rId] ?? null;
            }
        }
    }

    if (!$targetSheetFile) {
        // Coba sheet pertama yang bukan "Skor"/"Rata"
        if ($wbXml) {
            foreach ($wbXml->sheets->sheet as $sheet) {
                $attrs  = $sheet->attributes();
                $rAttrs = $sheet->attributes('r', true);
                $name   = strtolower(trim((string)$attrs['name']));
                $rId    = (string)$rAttrs['id'];
                if (strpos($name, 'skor') === false && strpos($name, 'rata') === false && strpos($name, 'rapot') === false) {
                    $targetSheetFile = $sheetFiles[$rId] ?? null;
                    break;
                }
            }
        }
    }

    if (!$targetSheetFile) {
        $zip->close();
        return ['error' => 'Sheet "Data Dosen" tidak ditemukan'];
    }

    // Parse sheet Data Dosen
    $sheetContent = $zip->getFromName($targetSheetFile);
    $zip->close();

    if (!$sheetContent) {
        return ['error' => 'Gagal membaca sheet'];
    }

    $sheetXml = @simplexml_load_string($sheetContent);
    if (!$sheetXml) {
        return ['error' => 'Gagal parse XML sheet'];
    }

    // Ambil header dari baris 1
    $headers = [];
    $rows    = [];
    $rowIdx  = 0;

    foreach ($sheetXml->sheetData->row as $row) {
        $rowAttrs = $row->attributes();
        $rowNum   = (int)($rowAttrs['r'] ?? 0);

        $rowData = [];
        foreach ($row->c as $cell) {
            $ca  = $cell->attributes();
            $ref = (string)($ca['r'] ?? '');
            $t   = (string)($ca['t'] ?? '');
            $v   = (string)($cell->v ?? '');

            // Convert cell ref to column index
            preg_match('/([A-Z]+)(\d+)/', $ref, $m);
            $colLetter = $m[1] ?? 'A';
            $colIdx    = colLetterToIndex($colLetter);

            if ($t === 's' && isset($sharedStrings[(int)$v])) {
                $v = $sharedStrings[(int)$v];
            } elseif ($t === 'inlineStr' && isset($cell->is)) {
                $v = (string)$cell->is->t;
            }

            $rowData[$colIdx] = $v;
        }

        if ($rowNum === 1) {
            // Header row
            $headers = $rowData;
        } elseif ($rowNum > 1 && !empty($rowData)) {
            // Cek apakah row kosong total
            $vals = array_filter($rowData, fn($x) => $x !== '');
            if (empty($vals)) continue;

            // Map by header name
            $mapped = [];
            foreach ($headers as $ci => $hdr) {
                $mapped[$hdr] = $rowData[$ci] ?? '';
            }
            $rows[] = $mapped;
        }
    }

    return [
        'headers' => array_values($headers),
        'rows'    => $rows,
        'total'   => count($rows),
    ];
}

/**
 * Convert column letter to 0-based index: A=0, B=1, AA=26...
 */
function colLetterToIndex(string $col): int {
    $col = strtoupper($col);
    $idx = 0;
    for ($i = 0; $i < strlen($col); $i++) {
        $idx = $idx * 26 + (ord($col[$i]) - ord('A') + 1);
    }
    return $idx - 1;
}

/**
 * Ambil kategori berdasarkan skor
 */
function getSkorKategori(float $skor): array {
    if ($skor >= 4.58) {
        return ['label' => 'Sangat Baik', 'color' => 'emerald', 'bg' => '#d1fae5', 'text' => '#065f46'];
    } elseif ($skor >= 4.12) {
        return ['label' => 'Baik', 'color' => 'blue', 'bg' => '#dbeafe', 'text' => '#1e40af'];
    } elseif ($skor >= 3.66) {
        return ['label' => 'Cukup', 'color' => 'amber', 'bg' => '#fef3c7', 'text' => '#92400e'];
    } else {
        return ['label' => 'Kurang Baik', 'color' => 'red', 'bg' => '#fee2e2', 'text' => '#991b1b'];
    }
}

/**
 * Get kategori kehadiran
 */
function getKategoriKehadiran(float $kehadiran): string {
    if ($kehadiran >= 16) return 'Memenuhi';
    if ($kehadiran >= 14) return 'Cukup';
    return 'Belum Memenuhi';
}
