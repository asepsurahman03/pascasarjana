<?php
/**
 * functions/pdf_genap_parser.php
 * Membaca data dosen dari PDF Laporan Rekap Kuesioner (Genap)
 * dan mengembalikan array dengan format yang sama seperti parseExcelRaport()
 */

function parseGenapPDF(string $pdfPath): array {
    if (!file_exists($pdfPath)) return [];

    $raw = file_get_contents($pdfPath);

    // ===== Helper: decompress =====
    $decStream = function($data) {
        $r = @gzuncompress($data);
        if ($r !== false) return $r;
        $r = @gzinflate(substr($data, 2));
        if ($r !== false) return $r;
        return false;
    };

    // ===== 1. Cari ToUnicode object IDs =====
    preg_match_all('/ToUnicode\s+(\d+)\s+\d+\s+R/', $raw, $tuRefs);
    $tounicodeIds = array_unique($tuRefs[1]);

    // ===== 2. Parse semua objects =====
    $objects = [];
    preg_match_all('/(\d+)\s+0\s+obj\s+(.*?)\s+endobj/s', $raw, $objMatches, PREG_SET_ORDER);
    foreach ($objMatches as $m) {
        $objects[(int)$m[1]] = $m[2];
    }

    // ===== 3. Build CMap dari ToUnicode =====
    $globalCmap = [];
    foreach ($tounicodeIds as $id) {
        if (!isset($objects[(int)$id])) continue;
        $objContent = $objects[(int)$id];
        if (!preg_match('/stream\r?\n(.*?)\r?\nendstream/s', $objContent, $sm)) continue;
        $dec = $decStream($sm[1]);
        if (!$dec || strpos($dec, 'beginbfchar') === false) continue;

        // Parse bfchar
        preg_match_all('/beginbfchar(.*?)endbfchar/s', $dec, $bfc);
        foreach ($bfc[1] as $block) {
            preg_match_all('/<([0-9A-Fa-f]+)>\s+<([0-9A-Fa-f]+)>/', $block, $p);
            foreach ($p[1] as $i => $src) {
                $code = hexdec($src);
                $dst  = hexdec($p[2][$i]);
                $globalCmap[$code] = mb_convert_encoding(pack('N', $dst), 'UTF-8', 'UCS-4BE');
            }
        }
        // Parse bfrange
        preg_match_all('/beginbfrange(.*?)endbfrange/s', $dec, $bfr);
        foreach ($bfr[1] as $block) {
            preg_match_all('/<([0-9A-Fa-f]+)>\s+<([0-9A-Fa-f]+)>\s+<([0-9A-Fa-f]+)>/', $block, $r);
            foreach ($r[1] as $i => $s1) {
                $start = hexdec($s1);
                $end   = hexdec($r[2][$i]);
                $dst   = hexdec($r[3][$i]);
                for ($c = $start; $c <= $end; $c++) {
                    $globalCmap[$c] = mb_convert_encoding(pack('N', $dst + ($c - $start)), 'UTF-8', 'UCS-4BE');
                }
            }
        }
    }

    // ===== 4. Decode hex string ke teks =====
    $decodeHex = function($hex) use ($globalCmap) {
        $text = '';
        $hex  = strtoupper(preg_replace('/\s+/', '', $hex));
        for ($i = 0; $i < strlen($hex); $i += 4) {
            $code  = hexdec(substr($hex, $i, 4));
            $text .= $globalCmap[$code] ?? '';
        }
        return $text;
    };

    // ===== 5. Ekstrak teks dari content streams =====
    $pageTexts = [];
    foreach ($objects as $id => $objContent) {
        if (strpos($objContent, 'stream') === false) continue;
        if (!preg_match('/stream\r?\n(.*?)\r?\nendstream/s', $objContent, $sm)) continue;
        $dec = $decStream($sm[1]);
        if (!$dec || strpos($dec, 'TJ') === false) continue;

        $pageText = '';
        preg_match_all('/\[((?:<[0-9A-Fa-f]+>|-?\d+\s*)+)\]\s*TJ|<([0-9A-Fa-f]+)>\s*Tj/', $dec, $ops, PREG_SET_ORDER);
        foreach ($ops as $op) {
            if (!empty($op[1])) {
                preg_match_all('/<([0-9A-Fa-f]+)>/', $op[1], $hp);
                foreach ($hp[1] as $h) $pageText .= $decodeHex($h);
            } elseif (!empty($op[2])) {
                $pageText .= $decodeHex($op[2]);
            }
        }
        if (strlen(trim($pageText)) > 5) $pageTexts[] = trim($pageText);
    }

    $fullText = implode("\n", $pageTexts);

    // ===== 6. Parse dosen dari teks =====
    $rows    = [];
    $no      = 1;
    $lines   = preg_split('/\r?\n/', $fullText);

    $currentNIP      = '';
    $currentNama     = '';
    $currentHomebase = '';
    $dosenData       = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (strlen($line) < 3) continue;

        // Deteksi header laporan dosen
        if (strpos($line, 'Nama Dosen:') !== false) {
            if (preg_match('/NIP\s*Dosen:\s*(\d+)/', $line, $m)) $currentNIP = $m[1];
            if (preg_match('/Homebase:\s*([^N]+?)(?:Nama|$)/', $line, $m)) $currentHomebase = trim($m[1]);
            if (preg_match('/Nama\s*Dosen:\s*([A-Z][A-Z\s\.,]+?)(?:\(|[A-Z][a-z]|$)/', $line, $m)) {
                $rawName = trim($m[1]);
                // Bersihkan: ambil sampai huruf kecil pertama (nama MK) atau tanda kurung
                $rawName = preg_replace('/[A-Z][a-z].+$/', '', $rawName);
                $currentNama = trim($rawName);
            }
        }

        // Rata-Rata nilai kuesioner per kelas
        if (preg_match('/Rata-Rata\s*(\d+\.\d{2})/', $line, $rrm) && $currentNama) {
            $nilai = (float)$rrm[1];
            $key   = $currentNIP ?: $currentNama;
            if (!isset($dosenData[$key])) {
                $dosenData[$key] = [
                    'nip'       => $currentNIP,
                    'nama'      => $currentNama,
                    'homebase'  => $currentHomebase,
                    'nilais'    => [],
                    'mk_count'  => 0,
                ];
            }
            if ($nilai > 0) {
                $dosenData[$key]['nilais'][]  = $nilai;
                $dosenData[$key]['mk_count']++;
            }
        }
    }

    // ===== 7. Build rows array (format sama dengan Excel parser) =====
    foreach ($dosenData as $d) {
        $avgNilai = count($d['nilais']) > 0
            ? round(array_sum($d['nilais']) / count($d['nilais']), 2) : 0;

        $rows[] = [
            'No'               => $no++,
            'Nama'             => $d['nama'],
            'Prodi'            => $d['homebase'] ?: 'S2 Magister Pedagogi',
            'Jumlah Matkul'    => $d['mk_count'],
            'Jumlah Kelas'     => $d['mk_count'],
            'Jumlah Responden' => '',
            'Nilai Kuesioner'  => $avgNilai,
            'Jumlah Kehadiran' => 0,
            'Konten'           => 0,
            'Jumlah Penelitian'=> 0,
            'Jumlah Pengabdian'=> 0,
            // Kosong karena tidak ada di PDF kuesioner
            'P1' => '', 'P2' => '', 'P3' => '', 'P4' => '', 'P5' => '',
            'K1' => '', 'K2' => '', 'K3' => '', 'K4' => '',
        ];
    }

    return $rows;
}
