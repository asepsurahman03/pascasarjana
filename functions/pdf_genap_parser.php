<?php
/**
 * functions/pdf_genap_parser.php
 * Membaca data dosen dari PDF Laporan Rekap Kuesioner (Genap)
 * dan mengembalikan array dengan format yang sama seperti parseExcelRaport()
 * Versi 2: Juga mengekstrak komentar Open Question (Kesan & Pesan) mahasiswa ke K1-K4
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

        preg_match_all('/beginbfchar(.*?)endbfchar/s', $dec, $bfc);
        foreach ($bfc[1] as $block) {
            preg_match_all('/<([0-9A-Fa-f]+)>\s+<([0-9A-Fa-f]+)>/', $block, $p);
            foreach ($p[1] as $i => $src) {
                $code = hexdec($src);
                $dst  = hexdec($p[2][$i]);
                $globalCmap[$code] = mb_convert_encoding(pack('N', $dst), 'UTF-8', 'UCS-4BE');
            }
        }
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
    $lines   = preg_split('/\r?\n/', $fullText);

    $currentNIP      = '';
    $currentNama     = '';
    $currentHomebase = '';
    $currentKey      = '';
    $dosenData       = [];
    $inOpenQuestion  = false;
    $komentar        = [];

    // Kata kunci yang menandakan awal Open Question
    $openQKeywords = ['Open Question', 'Kesan dan Pesan', 'Kesan dan pesan', 'tuliskan di sini'];
    // Kata kunci yang menandakan akhir Open Question / header baru
    $stopKeywords  = ['NIP Dosen', 'Nama Dosen', 'LAPORAN', 'Dicetak'];
    $skipPrefixes  = ['Pertanyaan', 'Total Jawaban', 'Total Skor', 'Nilai', 'Rata-Rata',
                      'Sangat', 'Tidak baik', 'Biasa', 'Baik', 'Lainnya', 'tuliskan'];

    $saveKomentar = function() use (&$dosenData, &$komentar, &$currentKey) {
        if ($currentKey && !empty($komentar) && isset($dosenData[$currentKey])) {
            $dosenData[$currentKey]['kommentars'] = array_merge(
                $dosenData[$currentKey]['kommentars'] ?? [],
                $komentar
            );
            $komentar = [];
        }
    };

    foreach ($lines as $line) {
        $line = trim($line);
        if (strlen($line) < 2) continue;

        // Deteksi dosen baru
        if (strpos($line, 'Nama Dosen:') !== false) {
            $saveKomentar();
            $inOpenQuestion = false;

            if (preg_match('/NIP\s*Dosen:\s*(\d+)/', $line, $m)) $currentNIP = $m[1];
            if (preg_match('/Homebase:\s*([^N]+?)(?:Nama|$)/', $line, $m)) $currentHomebase = trim($m[1]);
            if (preg_match('/Nama\s*Dosen:\s*([A-Z][A-Z\s\.,]+?)(?:\(|[A-Z][a-z]|$)/', $line, $m)) {
                $rawName = trim($m[1]);
                $rawName = preg_replace('/[A-Z][a-z].+$/', '', $rawName);
                $currentNama = trim($rawName);
            }

            $currentKey = $currentNIP ?: $currentNama;
            if ($currentKey && !isset($dosenData[$currentKey])) {
                $dosenData[$currentKey] = [
                    'nip'        => $currentNIP,
                    'nama'       => $currentNama,
                    'homebase'   => $currentHomebase,
                    'nilais'     => [],
                    'mk_count'   => 0,
                    'kommentars' => [],
                ];
            }
        }

        // Rata-Rata nilai kuesioner
        if (preg_match('/Rata-Rata\s+(\d+\.\d{2})/', $line, $rrm) && $currentKey) {
            $nilai = (float)$rrm[1];
            if ($nilai > 0 && isset($dosenData[$currentKey])) {
                $dosenData[$currentKey]['nilais'][]  = $nilai;
                $dosenData[$currentKey]['mk_count']++;
            }
            $inOpenQuestion = false;
        }

        // Deteksi awal Open Question
        foreach ($openQKeywords as $kw) {
            if (strpos($line, $kw) !== false) {
                $inOpenQuestion = true;
                continue 2;
            }
        }

        // Kumpulkan komentar jika dalam Open Question
        if ($inOpenQuestion && $currentKey) {
            // Stop jika menemukan header baru
            $shouldStop = false;
            foreach ($stopKeywords as $sk) {
                if (strpos($line, $sk) !== false) { $shouldStop = true; break; }
            }
            if ($shouldStop) { $inOpenQuestion = false; continue; }

            // Skip baris yang merupakan elemen tabel/header
            $shouldSkip = false;
            foreach ($skipPrefixes as $sp) {
                if (stripos($line, $sp) === 0) { $shouldSkip = true; break; }
            }
            if ($shouldSkip || strlen($line) < 5) continue;

            $komentar[] = $line;
        }
    }

    // Simpan komentar dosen terakhir
    $saveKomentar();

    // ===== 7. Build rows array =====
    $no = 1;
    foreach ($dosenData as $d) {
        $avgNilai = count($d['nilais']) > 0
            ? round(array_sum($d['nilais']) / count($d['nilais']), 2) : 0;

        // Ambil max 4 komentar unik untuk K1-K4
        $komUnik = array_values(array_unique(array_filter(
            $d['kommentars'] ?? [],
            fn($k) => strlen(trim($k)) > 4
        )));

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
            'P1' => '', 'P2' => '', 'P3' => '', 'P4' => '', 'P5' => '',
            // Komentar mahasiswa dari Open Question
            'K1' => $komUnik[0] ?? '',
            'K2' => $komUnik[1] ?? '',
            'K3' => $komUnik[2] ?? '',
            'K4' => $komUnik[3] ?? '',
        ];
    }

    return $rows ?? [];
}
