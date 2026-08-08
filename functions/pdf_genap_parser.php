<?php
/**
 * functions/pdf_genap_parser.php
 * Membaca data dosen dari PDF Laporan Rekap Kuesioner (Genap) - Versi 4
 * Fix: UTF-16BE decode = 2 bytes per char, byte[0]=high (skip 0x00), byte[1]=Skia-shifted
 */

function parseGenapPDF(string $pdfPath): array {
    if (!file_exists($pdfPath)) return [];

    $raw = file_get_contents($pdfPath);

    // ===== 1. Decompress FlateDecode streams with BT blocks =====
    $decodedTexts = [];
    preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $raw, $streamMatches);
    foreach ($streamMatches[1] as $stream) {
        $dec = @gzuncompress($stream);
        if ($dec === false) $dec = @gzinflate($stream);
        if ($dec === false) continue;
        if (strpos($dec, 'BT') !== false) $decodedTexts[] = $dec;
    }

    // ===== 2. Decode Skia PDF shift-29 (single byte) =====
    // Skia encodes: stored_byte = original_byte - 29 (mod 95 in printable range)
    $shiftChar = function(int $byte): string {
        if ($byte >= 32 && $byte <= 126) {
            $shifted = $byte + 29;
            if ($shifted > 126) $shifted = $shifted - 95;
            return chr($shifted);
        }
        return '';
    };

    // Decode a hex string from Skia PDF:
    // Hex is stored as UTF-16BE where high byte is 0x00 and low byte is the shifted char
    $decodeHex = function(string $hex) use ($shiftChar): string {
        $result = '';
        $len = strlen($hex);
        // Process 4 hex chars at a time (= 2 bytes = 1 UTF-16BE char)
        for ($i = 0; $i + 3 < $len; $i += 4) {
            $highByte = hexdec(substr($hex, $i, 2));
            $lowByte  = hexdec(substr($hex, $i + 2, 2));
            if ($highByte === 0) {
                // Normal ASCII char encoded with Skia shift
                $result .= $shiftChar($lowByte);
            } else {
                // High byte non-zero → real Unicode char (UTF-16BE)
                $cp = ($highByte << 8) | $lowByte;
                if ($cp > 31 && $cp < 0xFFFD) {
                    $result .= mb_chr($cp, 'UTF-8');
                }
            }
        }
        return $result;
    };

    // ===== 3. Extract unique text lines from BT...ET blocks =====
    $allLines = [];
    foreach ($decodedTexts as $text) {
        preg_match_all('/BT(.*?)ET/s', $text, $btBlocks);
        foreach ($btBlocks[1] as $block) {
            // Extract all hex strings from this BT block
            preg_match_all('/<([0-9A-Fa-f]+)>/', $block, $hexAll);
            $lineText = '';
            foreach ($hexAll[1] as $hex) {
                if (strlen($hex) < 2) continue;
                $lineText .= $decodeHex($hex);
            }
            $lineText = trim($lineText);
            if (strlen($lineText) > 1) $allLines[] = $lineText;
        }
    }

    // Deduplicate consecutive identical lines (Skia sometimes draws text multiple times)
    $lines = [];
    $lastLine = null;
    foreach ($allLines as $l) {
        if ($l !== $lastLine) {
            $lines[] = $l;
            $lastLine = $l;
        }
    }

    // ===== 4. Parse structure: identify dosen and collect komentar =====
    $dosenData   = [];
    $currentKey  = '';
    $inOpenQ     = false;
    $pendingKom  = [];
    $expectNama  = false;
    $expectProdi = false;

    // Helper to save pending komentar to current dosen
    $saveKom = function() use (&$dosenData, &$pendingKom, &$currentKey) {
        if ($currentKey && !empty($pendingKom)) {
            $dosenData[$currentKey]['kommentars'] = array_merge(
                $dosenData[$currentKey]['kommentars'] ?? [],
                $pendingKom
            );
        }
        $pendingKom = [];
    };

    // Keywords (decoded, no spaces)
    $openQWords = ['OpenQuestion', 'KesandanPesan', 'tuliskandisini'];
    $stopWords  = ['NIPDosen', 'NamaDosen', 'LAPORANREKAP', 'Dicetak'];
    $skipWords  = [
        'TotalJawaban','TotalSkor','Nilai','RataRata','Pertanyaan',
        'Sangatbaik','Sangattidakbaik','Tidakbaik','tidakbaik',
        'Biasa','Baik','baik','Lainnya','No',
        'OpenQuestion','KesandanPesan','tuliskandisini',
    ];

    foreach ($lines as $line) {
        $line  = trim($line);
        if (strlen($line) < 2) continue;
        $clean = preg_replace('/\s+/', '', $line);

        // ── Deteksi "NamaDosen" ──
        if (stripos($clean, 'NamaDosen') !== false) {
            $saveKom();
            $inOpenQ    = false;
            $expectNama = true;
            continue;
        }

        // ── Deteksi "Homebase" ──
        if (stripos($clean, 'Homebase') !== false) {
            $expectProdi = true;
            continue;
        }

        // ── Capture nama dosen (baris setelah "NamaDosen") ──
        if ($expectNama) {
            $expectNama  = false;
            $nama = strtoupper(trim($line));
            $currentKey  = $nama;
            if (!isset($dosenData[$currentKey])) {
                $dosenData[$currentKey] = [
                    'nama'       => $nama,
                    'prodi'      => '',
                    'nilais'     => [],
                    'mk_count'   => 0,
                    'kommentars' => [],
                ];
            }
            continue;
        }

        // ── Capture prodi (baris setelah "Homebase") ──
        if ($expectProdi && $currentKey) {
            $dosenData[$currentKey]['prodi'] = trim($line);
            $expectProdi = false;
            continue;
        }

        // ── Rata-Rata nilai ──
        if (preg_match('/RataRata(\d+[.,]\d{2})/', $clean, $m) && $currentKey) {
            $nilai = (float)str_replace(',', '.', $m[1]);
            if ($nilai > 0) {
                $dosenData[$currentKey]['nilais'][]  = $nilai;
                $dosenData[$currentKey]['mk_count']++;
            }
            $inOpenQ = false;
            continue;
        }

        // ── Deteksi Open Question trigger ──
        foreach ($openQWords as $kw) {
            if (stripos($clean, $kw) !== false) {
                $inOpenQ = true;
                continue 2;
            }
        }

        // ── Deteksi Stop trigger ──
        foreach ($stopWords as $kw) {
            if (stripos($clean, $kw) !== false) {
                $inOpenQ = false;
                continue 2;
            }
        }

        // ── Collect komentar ──
        if ($inOpenQ && $currentKey) {
            // Skip header/table rows
            $skip = false;
            foreach ($skipWords as $sw) {
                if (stripos($clean, $sw) !== false && strlen($clean) <= strlen($sw) + 5) {
                    $skip = true; break;
                }
            }
            if (preg_match('/^\d+$/', $clean)) $skip = true;
            if ($skip || strlen($line) < 5) continue;
            $pendingKom[] = $line;
        }
    }
    $saveKom();

    // ===== 5. Build rows =====
    $rows = [];
    $no   = 1;
    foreach ($dosenData as $d) {
        if (empty($d['nama'])) continue;
        $avgNilai = count($d['nilais']) > 0
            ? round(array_sum($d['nilais']) / count($d['nilais']), 2) : 0;

        $komUnik = array_values(array_filter(
            array_unique($d['kommentars'] ?? []),
            fn($k) => strlen(trim($k)) >= 5
        ));

        $rows[] = [
            'No'               => $no++,
            'Nama'             => $d['nama'],
            'Prodi'            => $d['prodi'] ?: 'S2 Magister Pedagogi',
            'Jumlah Matkul'    => $d['mk_count'],
            'Jumlah Kelas'     => $d['mk_count'],
            'Jumlah Responden' => '',
            'Nilai Kuesioner'  => $avgNilai,
            'Jumlah Kehadiran' => 0,
            'Konten'           => 0,
            'Jumlah Penelitian'=> 0,
            'Jumlah Pengabdian'=> 0,
            // Rekomendasi Perbaikan (ambil komentar ke-5 dst, atau generik)
            'P1' => $komUnik[4] ?? ($komUnik[0] ?? 'Terus pertahankan kualitas pengajaran.'),
            'P2' => $komUnik[5] ?? 'Tingkatkan interaksi aktif dengan mahasiswa.',
            'P3' => $komUnik[6] ?? 'Berikan umpan balik yang lebih spesifik pada tugas.',
            'P4' => $komUnik[7] ?? 'Gunakan media pembelajaran yang lebih variatif.',
            'P5' => $komUnik[8] ?? 'Perbanyak diskusi dan studi kasus relevan.',
            // Catatan Mahasiswa
            'K1' => $komUnik[0] ?? '',
            'K2' => $komUnik[1] ?? '',
            'K3' => $komUnik[2] ?? '',
            'K4' => $komUnik[3] ?? '',
        ];
    }

    return $rows;
}
