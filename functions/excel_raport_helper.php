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

            // Fix floating point precision untuk kolom numerik
            $numericCols = ['Nilai Kuesioner', 'Jumlah Kehadiran', 'Konten'];
            foreach ($numericCols as $col) {
                if (isset($mapped[$col]) && is_numeric($mapped[$col]) && $mapped[$col] !== '') {
                    $mapped[$col] = round((float)$mapped[$col], 3);
                }
            }
            $intCols = ['Jumlah Matkul', 'Jumlah Kelas', 'Jumlah Penelitian', 'Jumlah Pengabdian'];
            foreach ($intCols as $col) {
                if (isset($mapped[$col]) && is_numeric($mapped[$col]) && $mapped[$col] !== '') {
                    $mapped[$col] = (int)$mapped[$col];
                }
            }

            // Format nama Prodi ke format resmi berjenjang (e.g. S1 - Akuntansi, S2 - Magister Informatika)
            if (isset($mapped['Prodi'])) {
                $mapped['Prodi'] = formatProdiStandard($mapped['Prodi']);
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
 * Parse semua data multi-sheet dari Excel untuk wizard
 * Return: [ 'data_dosen'=>[], 'skor_bobot'=>[], 'skor'=>[], 'rata_rata'=>[] ]
 */
function parseExcelAllSheets(string $filePath): array {
    if (!file_exists($filePath)) {
        return ['error' => 'File tidak ditemukan'];
    }
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        return ['error' => 'Gagal membuka file Excel'];
    }

    // Shared strings
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
                    foreach ($si->r as $r) { $text .= (string)$r->t; }
                    $sharedStrings[] = $text;
                }
            }
        }
    }

    // Sheet paths
    $wb = $zip->getFromName('xl/workbook.xml');
    $wbXml = @simplexml_load_string($wb);
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

    $sheetMap = []; // name_lc => path
    if ($wbXml) {
        foreach ($wbXml->sheets->sheet as $sheet) {
            $attrs  = $sheet->attributes();
            $rAttrs = $sheet->attributes('r', true);
            $name   = strtolower(trim((string)$attrs['name']));
            $rId    = (string)$rAttrs['id'];
            if (isset($sheetFiles[$rId])) {
                $sheetMap[$name] = $sheetFiles[$rId];
            }
        }
    }

    // Helper: read sheet XML to associative array by row
    $readSheet = function(string $path) use ($zip, $sharedStrings) {
        $xml = $zip->getFromName($path);
        if (!$xml) return [];
        $sxe = @simplexml_load_string($xml);
        if (!$sxe) return [];
        $rows = [];
        foreach ($sxe->sheetData->row as $row) {
            $ra = $row->attributes();
            $rowNum = (int)($ra['r'] ?? 0);
            $cells = [];
            foreach ($row->c as $cell) {
                $ca  = $cell->attributes();
                $ref = (string)($ca['r'] ?? '');
                $t   = (string)($ca['t'] ?? '');
                $v   = (string)($cell->v ?? '');
                preg_match('/([A-Z]+)(\d+)/', $ref, $m);
                $colLetter = $m[1] ?? 'A';
                if ($t === 's' && isset($sharedStrings[(int)$v])) {
                    $v = $sharedStrings[(int)$v];
                } elseif ($t === 'inlineStr' && isset($cell->is)) {
                    $v = (string)$cell->is->t;
                }
                $cells[$colLetter] = $v;
            }
            if (!empty(array_filter($cells, fn($x) => $x !== ''))) {
                $rows[$rowNum] = $cells;
            }
        }
        return $rows;
    };

    // Find sheets by keyword
    $findSheet = function(string $keyword) use ($sheetMap) {
        foreach ($sheetMap as $name => $path) {
            if (strpos($name, $keyword) !== false) return $path;
        }
        return null;
    };

    $result = [];

    // --- Sheet: Rata-Rata (kriteria kuesioner) ---
    $rataPath = $findSheet('rata');
    if ($rataPath) {
        $rataRows = $readSheet($rataPath);
        $kriteria = [];
        // Row 1 = header, row 2+ = data. Column U = nama kriteria, F = No urut, G = rata-rata
        foreach ($rataRows as $rowNum => $cells) {
            if ($rowNum === 1) continue;
            $no  = trim($cells['F'] ?? '');
            $avg = trim($cells['G'] ?? '');
            $nama = trim($cells['U'] ?? '');
            if ($no !== '' && $nama !== '') {
                // Skip error values (formulas not computed)
                $avgVal = (strpos($avg, '#') === false && $avg !== '') ? (float)$avg : null;
                $kriteria[] = [
                    'no'   => $no,
                    'nama' => $nama,
                    'rata' => $avgVal,
                ];
            }
        }
        $result['rata_rata'] = $kriteria;
    } else {
        $result['rata_rata'] = [];
    }

    // --- Sheet: Skor Bobot ---
    $skorBobotPath = $findSheet('bobot') ?? $findSheet('skor bobot');
    if ($skorBobotPath) {
        $sbRows = $readSheet($skorBobotPath);
        $result['skor_bobot'] = $sbRows;
    } else {
        $result['skor_bobot'] = [];
    }

    // --- Sheet: Skor ---
    $skorPath = null;
    foreach ($sheetMap as $name => $path) {
        if ($name === 'skor') { $skorPath = $path; break; }
    }
    if ($skorPath) {
        $sRows = $readSheet($skorPath);
        $result['skor'] = $sRows;
    } else {
        $result['skor'] = [];
    }

    // Build static kriteria list if empty (from Rata-Rata sheet shared strings)
    if (empty($result['rata_rata'])) {
        $result['rata_rata'] = [
            ['no'=>'1','nama'=>'Kesiapan memberikan kuliah dan/atau praktek/praktikum','rata'=>null],
            ['no'=>'2','nama'=>'Keteraturan dan ketertiban penyelengaraan perkuliahan','rata'=>null],
            ['no'=>'3','nama'=>'Kemampuan menghidupkan suasana kelas','rata'=>null],
            ['no'=>'4','nama'=>'Kejelasan penyampaian materi dan jawaban terhadap pertanyaan di kelas','rata'=>null],
            ['no'=>'5','nama'=>'Pemanfaatan media dan teknologi pembelajaran','rata'=>null],
            ['no'=>'6','nama'=>'Keanekaragaman cara pengukuran hasil belajar','rata'=>null],
            ['no'=>'7','nama'=>'Pemberian umpan balik terhadap tugas','rata'=>null],
            ['no'=>'8','nama'=>'Kesesuaian materi ujian dan/atau tugas dengan tujuan mata kuliah','rata'=>null],
            ['no'=>'9','nama'=>'Kesesuaian nilai yang diberikan dengan hasil belajar','rata'=>null],
            ['no'=>'10','nama'=>'Kemampuan menjelaskan pokok pembahasan/topik secara cepat','rata'=>null],
            ['no'=>'11','nama'=>'Kemampuan memberikan contoh relevan dari konsep yang diajarkan','rata'=>null],
            ['no'=>'12','nama'=>'Kemampuan menjelaskan keterkaitan bidang/topik yang di ajarkan dengan bidang/topik yang lain','rata'=>null],
            ['no'=>'13','nama'=>'Kemampuan menjelaskan keterkaitan bidang/topik yang diajarkan dengan konteks','rata'=>null],
            ['no'=>'14','nama'=>'Penguasaan akan isu-isu mutakhir dalam bidang yang diajarkan','rata'=>null],
            ['no'=>'15','nama'=>'Penggunaan hasil-hasil penelitian untuk meningkatkan kualitas perkuliahaan','rata'=>null],
            ['no'=>'16','nama'=>'Pelibatan mahasiswa dalam penelitian/kajian dan/atau pengembangan/rekayasa/desain yang dilakukan oleh dosen','rata'=>null],
            ['no'=>'17','nama'=>'Kemampuan menggunakan beragam teknologi komunikasi','rata'=>null],
            ['no'=>'18','nama'=>'Keawibawaan sebagai pribadi dosen','rata'=>null],
            ['no'=>'19','nama'=>'Kearifan dalam mengambil keputusan','rata'=>null],
            ['no'=>'20','nama'=>'Menjadi contoh dalam sikap dan perilaku','rata'=>null],
            ['no'=>'21','nama'=>'Satunya kata dan tindakan','rata'=>null],
            ['no'=>'22','nama'=>'Kemampuan mengendalikan diri dalam berbagai situasi dan kondisi','rata'=>null],
            ['no'=>'23','nama'=>'Adil dalam memperlakukan mahasiswa','rata'=>null],
            ['no'=>'24','nama'=>'Kemampuan menyampaikan pendapat','rata'=>null],
            ['no'=>'25','nama'=>'Kemampuan menerima kritik, saran dan pendapat mahasiswa','rata'=>null],
            ['no'=>'26','nama'=>'Mengenal dengan baik mahasiswa yang mengikuti kuliahnya','rata'=>null],
            ['no'=>'27','nama'=>'Mudah bergaul di kalangan mahasiswa','rata'=>null],
            ['no'=>'28','nama'=>'Toleransi terhadap keberagaman mahasiswa','rata'=>null],
        ];
    }

    $zip->close();
    return $result;
}

/**
 * Format nama Program Studi ke format resmi berjenjang (e.g. S1 - Akuntansi, S2 - Magister Informatika, S3 - Doktor Ilmu Komputer, D3 - Keperawatan).
 */
function formatProdiStandard(?string $prodiRaw): string {
    $p = trim((string)$prodiRaw);
    if ($p === '' || $p === '-') return '-';

    // Jika sudah memiliki prefix jenjang (S1 - , S2 - , S3 - , D3 - , D4 - ), return langsung
    if (preg_match('/^(?:S1|S2|S3|D3|D4)\s*-\s*/i', $p)) {
        return $p;
    }

    $map = [
        // S2 / Magister
        'magister informatika'            => 'S2 - Magister Informatika',
        'magister hukum'                  => 'S2 - Magister Hukum',
        's2 hukum'                        => 'S2 - Magister Hukum',
        'magister pedagogi'               => 'S2 - Magister Pedagogi',
        's2 pedagogi'                     => 'S2 - Magister Pedagogi',
        'pedagogi'                        => 'S2 - Magister Pedagogi',
        'magister manajemen'              => 'S2 - Magister Manajemen',
        's2 manajemen'                    => 'S2 - Magister Manajemen',
        // S3 / Doktor
        'doktor ilmu komputer'            => 'S3 - Doktor Ilmu Komputer',
        'ilmu komputer'                   => 'S3 - Doktor Ilmu Komputer',
        // D3 / Diploma
        'keperawatan'                     => 'D3 - Keperawatan',
        // S1 / Sarjana
        'administrasi kesehatan'          => 'S1 - Administrasi Kesehatan',
        'teknik informatika'              => 'S1 - Teknik Informatika',
        'sistem informasi'                => 'S1 - Sistem Informasi',
        'teknik elektro'                  => 'S1 - Teknik Elektro',
        'teknik mesin'                    => 'S1 - Teknik Mesin',
        'teknik sipil'                    => 'S1 - Teknik Sipil',
        'desain komunikasi visual'        => 'S1 - Desain Komunikasi Visual',
        'dkv'                             => 'S1 - Desain Komunikasi Visual',
        'akuntansi'                       => 'S1 - Akuntansi',
        'manajemen'                       => 'S1 - Manajemen',
        'hukum'                           => 'S1 - Hukum',
        'ilmu hukum'                      => 'S1 - Hukum',
        'pgsd'                            => 'S1 - Pendidikan Guru Sekolah Dasar',
        'pendidikan guru sekolah dasar'   => 'S1 - Pendidikan Guru Sekolah Dasar',
        'gizi'                            => 'S1 - Gizi',
        'bioteknologi'                    => 'S1 - Bioteknologi',
        'teknologi pangan'                => 'S1 - Teknologi Pangan',
    ];

    $norm = strtolower($p);
    foreach ($map as $key => $formatted) {
        if ($norm === $key || strpos($norm, $key) !== false) {
            return $formatted;
        }
    }

    if (stripos($p, 'universitas') !== false || stripos($p, 'institut') !== false || stripos($p, 'politeknik') !== false) {
        return $p;
    }

    return 'S1 - ' . $p;
}

/**
 * Master Program Studi lengkap Universitas Nusa Putra (D3, S1, S2, S3).
 */
function getAllMasterProgramStudi(): array {
    return [
        'D3 - Keperawatan',
        'S1 - Administrasi Kesehatan',
        'S1 - Akuntansi',
        'S1 - Bioteknologi',
        'S1 - Desain Komunikasi Visual',
        'S1 - Gizi',
        'S1 - Hukum',
        'S1 - Manajemen',
        'S1 - Pendidikan Guru Sekolah Dasar',
        'S1 - Sistem Informasi',
        'S1 - Teknik Elektro',
        'S1 - Teknik Informatika',
        'S1 - Teknik Mesin',
        'S1 - Teknik Sipil',
        'S1 - Teknologi Pangan',
        'S2 - Magister Hukum',
        'S2 - Magister Informatika',
        'S2 - Magister Manajemen',
        'S2 - Magister Pedagogi',
        'S3 - Doktor Ilmu Komputer',
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

/**
 * Ambil atau hitung analisis sentimen mahasiswa untuk dosen
 */
function getDosenSentimen(array $d): array {
    if (!empty($d['sentimen']) && is_array($d['sentimen'])) {
        return $d['sentimen'];
    }

    // Ambil komentar K1-K4 jika ada
    $komentar = [];
    foreach (['K1','K2','K3','K4'] as $kKey) {
        $kVal = trim($d[$kKey] ?? '');
        if ($kVal !== '' && $kVal !== '0') {
            $komentar[] = $kVal;
        }
    }

    $skor = (float)($d['Nilai Kuesioner'] ?? 0);

    if (!empty($komentar)) {
        $pos = 0; $net = 0; $neg = 0;
        foreach ($komentar as $kc) {
            $st = function_exists('analyzeTextSentiment') ? analyzeTextSentiment($kc) : 'positif';
            if ($st === 'positif') $pos++;
            elseif ($st === 'negatif') $neg++;
            else $net++;
        }
        $total = $pos + $net + $neg;
        $posPct = $total > 0 ? round(($pos / $total) * 100, 1) : 0;
        $netPct = $total > 0 ? round(($net / $total) * 100, 1) : 0;
        $negPct = $total > 0 ? round(($neg / $total) * 100, 1) : 0;

        $kesimpulan = function_exists('generateKesimpulanSentimen')
            ? generateKesimpulanSentimen($posPct, $netPct, $negPct, $skor)
            : "Berdasarkan hasil kuesioner dan analisis sentimen, respon mahasiswa didominasi penilaian POSITIF ({$posPct}%).";

        return [
            'total'        => $total,
            'positif'      => $pos,
            'netral'       => $net,
            'negatif'      => $neg,
            'positif_pct'  => $posPct,
            'netral_pct'   => $netPct,
            'negatif_pct'  => $negPct,
            'kesimpulan'   => $kesimpulan,
        ];
    }

    // Estimasi proporsional dari skor kuesioner
    if ($skor >= 4.58) {
        $posPct = 85.0; $netPct = 12.0; $negPct = 3.0;
    } elseif ($skor >= 4.12) {
        $posPct = 76.0; $netPct = 21.0; $negPct = 3.0;
    } elseif ($skor >= 3.66) {
        $posPct = 60.0; $netPct = 28.0; $negPct = 12.0;
    } elseif ($skor > 0) {
        $posPct = 40.0; $netPct = 35.0; $negPct = 25.0;
    } else {
        $posPct = 0; $netPct = 0; $negPct = 0;
    }

    $rawResp = (string)($d['Jumlah Responden'] ?? '');
    preg_match('/(\d+)/', $rawResp, $mResp);
    $totalResp = (int)($mResp[1] ?? 0);
    if ($totalResp === 0) $totalResp = 20;

    $posCount = round($totalResp * $posPct / 100);
    $netCount = round($totalResp * $netPct / 100);
    $negCount = max(0, $totalResp - $posCount - $netCount);

    $kesimpulan = function_exists('generateKesimpulanSentimen')
        ? generateKesimpulanSentimen($posPct, $netPct, $negPct, $skor)
        : "Hasil evaluasi Tridharma dosen menunjukkan kinerja yang telah memenuhi standar institusi.";

    return [
        'total'        => $totalResp,
        'positif'      => $posCount,
        'netral'       => $netCount,
        'negatif'      => $negCount,
        'positif_pct'  => $posPct,
        'netral_pct'   => $netPct,
        'negatif_pct'  => $negPct,
        'kesimpulan'   => $kesimpulan,
    ];
}
