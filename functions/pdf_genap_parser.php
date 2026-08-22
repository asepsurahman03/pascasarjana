<?php
/**
 * functions/pdf_genap_parser.php
 * Parser PDF Laporan Rekap Kuesioner (Genap)
 *
 * Struktur PDF (per stream/halaman):
 *  - Bagian kuesioner: No, Pertanyaan (header), 22 pertanyaan, TotalJawaban, TotalSkor, Nilai, RataRata
 *  - MK name (setelah RataRata, sebelum header berikutnya) — pola: teks + kode kelas (2-4 huruf kapital)
 *  - Bagian dosen header: NamaDosen, BARKAH, Homebase, SMagisterPedagogi, NIPDosen, Periode, Genap
 *  - Bagian open question: halaman terpisah dengan komentar mahasiswa (stream akhir PDF)
 *
 * CATATAN: Nilai kuesioner tidak tersedia dari stream PDF ini (tidak di-encode sebagai BT block),
 * sehingga Nilai Kuesioner = 0 dan harus diisi dari file Excel jika tersedia.
 */

if (!defined('PERTANYAAN_KUESIONER')) {
    define('PERTANYAAN_KUESIONER', [
        1  => 'Kesiapan memberikan kuliah dan/atau praktek/praktikum',
        2  => 'Keteraturan dan ketertiban penyelenggaraan perkuliahan',
        3  => 'Kemampuan menghidupkan suasana kelas',
        4  => 'Kejelasan penyampaian materi dan jawaban terhadap pertanyaan di kelas',
        5  => 'Pemanfaatan media dan teknologi pembelajaran',
        6  => 'Keanekaragaman cara pengukuran hasil belajar',
        7  => 'Pemberian umpan balik terhadap tugas/kuis/ujian',
        8  => 'Kesesuaian materi ujian dan/atau tugas dengan tujuan mata kuliah',
        9  => 'Kesesuaian nilai yang diberikan dengan hasil belajar',
        10 => 'Kemampuan menjelaskan pokok pembahasan/topik secara cepat',
        11 => 'Kemampuan memberikan contoh relevan dari konsep yang diajarkan',
        12 => 'Kemampuan menjelaskan keterkaitan bidang/topik yang diajarkan dengan bidang/topik yang lain',
        13 => 'Kemampuan menjelaskan keterkaitan bidang/topik yang diajarkan dengan konteks',
        14 => 'Penguasaan akan isu-isu mutakhir dalam bidang yang diajarkan',
        15 => 'Penggunaan hasil-hasil penelitian untuk meningkatkan kualitas perkuliahan',
        16 => 'Pelibatan mahasiswa dalam penelitian/kajian dan/atau pengembangan/rekayasa/desain yang dilakukan oleh dosen',
        17 => 'Kemampuan menggunakan beragam teknologi komunikasi',
        18 => 'Keawibawaan sebagai pribadi dosen',
        19 => 'Kearifan dalam mengambil keputusan',
        20 => 'Menjadi contoh dalam sikap dan perilaku',
        21 => 'Satunya kata dan tindakan',
        22 => 'Kemampuan mengendalikan diri dalam berbagai situasi dan kondisi',
    ]);
}

if (!defined('ASPEK_REKOMENDASI_STANDAR')) {
    define('ASPEK_REKOMENDASI_STANDAR', [
        'Kesiapan memberikan kuliah dan/atau praktek/praktikum',
        'Kejelasan penyampaian materi dan jawaban terhadap pertanyaan di kelas',
        'Pemanfaatan media dan teknologi pembelajaran',
        'Keanekaragaman cara pengukuran hasil belajar',
        'Kesesuaian materi ujian dan/atau tugas dengan tujuan mata kuliah',
    ]);
}

/**
 * Pisahkan kata-kata CamelCase yang tergabung menjadi terpisah dengan spasi
 */
function splitJoinedWords(string $text): string {
    $result = preg_replace('/([a-z\x{c0}-\x{fe}])([A-Z\x{c0}-\x{de}])/u', '$1 $2', $text);
    $result = preg_replace('/([A-Z\x{c0}-\x{de}]{2,})([A-Z\x{c0}-\x{de}][a-z\x{df}-\x{ff}])/u', '$1 $2', $result);
    return $result;
}

/**
 * Bersihkan teks komentar dari artifact PDF encoding
 */
function cleanKomentar(string $text): string {
    $text = splitJoinedWords($text);
    $text = preg_replace('/[\x00-\x1f\x7f\x{fffe}\x{ffff}]/u', '', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

/**
 * Decode Skia PDF hex string (shift+29 encoding, UTF-16BE)
 */
function decodeSkiaHex(string $hex): string {
    static $shiftMap = null;
    if ($shiftMap === null) {
        $shiftMap = [];
        for ($b = 0; $b <= 255; $b++) {
            $shifted = $b + 29;
            if ($shifted > 126) $shifted -= 95;
            $shiftMap[$b] = chr($shifted);
        }
    }
    $result = '';
    $len = strlen($hex);
    for ($i = 0; $i + 3 < $len; $i += 4) {
        $h = hexdec(substr($hex, $i,     2));
        $l = hexdec(substr($hex, $i + 2, 2));
        if ($h === 0) {
            $result .= $shiftMap[$l] ?? '';
        } else {
            $cp = ($h << 8) | $l;
            if ($cp > 31 && $cp < 0xFFFD) $result .= mb_chr($cp, 'UTF-8');
        }
    }
    return $result;
}

/**
 * Cek apakah teks merupakan nama Mata Kuliah (diakhiri kode kelas 2-4 huruf kapital)
 * Contoh: "BahasaIndonesiadanBudayaAKA", "InovasiTeknologidalamPembelajaranMSD"
 */
function isMataKuliahName(string $cleanText): bool {
    // Pola 1: diakhiri 2-5 huruf kapital (kode kelas + inisial dosen)
    if (preg_match('/[A-Z]{2,5}[)\s]*$/', $cleanText)) return true;
    // Pola 2: diakhiri dengan kode kelas di dalam kurung e.g., (AK24A), (SK-WW), (MSD24)
    if (preg_match('/\([A-Z0-9-]+\)$/', $cleanText)) return true;
    return false;
}

/**
 * Cek apakah teks adalah komentar mahasiswa yang valid
 */
function isValidKomentar(string $text): bool {
    if (strlen($text) < 8) return false;
    // Bukan nama MK (diakhiri kode kelas)
    $clean = preg_replace('/\s+/', '', $text);
    if (isMataKuliahName($clean)) return false;
    // Harus mengandung minimal 1 huruf kecil
    if (!preg_match('/[a-z]/u', $text)) return false;
    // Tidak hanya angka atau simbol
    if (preg_match('/^[\d\s.,;:-]+$/', $text)) return false;
    // Jika tidak ada spasi dan panjang > 30 karakter = kemungkinan PDF artifact (kata tergabung)
    if (!str_contains($text, ' ') && strlen($text) > 30) return false;
    return true;
}

/**
 * Parse PDF Kuesioner Genap
 *
 * @param  string $pdfPath  Path ke file PDF
 * @return array            Array dosen dengan semua field yang dibutuhkan raport
 */
function parseGenapPDF(string $pdfPath): array {
    if (!file_exists($pdfPath)) return [];

    $raw = file_get_contents($pdfPath);

    // ── 1. Decompress semua FlateDecode streams yang mengandung BT blocks ──
    $streams = [];
    preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $raw, $sm);
    $streamIdx = 0;
    foreach ($sm[1] as $stream) {
        $dec = @gzuncompress($stream);
        if ($dec === false) $dec = @gzinflate($stream);
        if ($dec === false) continue;
        if (strpos($dec, 'BT') !== false) {
            $streams[$streamIdx++] = $dec;
        }
    }

    // ── 2. Ekstrak semua BT blocks per stream, ambil text ──
    //    Order: stream order (page order), dalam stream: Y descending (top→bottom)
    $allBlocks = [];
    foreach ($streams as $streamId => $text) {
        preg_match_all('/BT(.*?)ET/s', $text, $btBlocks);
        foreach ($btBlocks[1] as $block) {
            // Get Y from Tm matrix
            preg_match('/(-?[\d.]+)\s+(-?[\d.]+)\s+(-?[\d.]+)\s+(-?[\d.]+)\s+(-?[\d.]+)\s+(-?[\d.]+)\s+Tm/', $block, $tmm);
            $x = isset($tmm[5]) ? (float)$tmm[5] : 0;
            $y = isset($tmm[6]) ? (float)$tmm[6] : 0;

            // Decode hex text
            preg_match_all('/<([0-9A-Fa-f]+)>/', $block, $hexAll);
            $lineText = '';
            foreach ($hexAll[1] as $hex) {
                if (strlen($hex) < 2) continue;
                $lineText .= decodeSkiaHex($hex);
            }
            $lineText = trim($lineText);
            if (strlen($lineText) === 0) continue;

            $allBlocks[] = ['sid' => $streamId, 'x' => $x, 'y' => $y, 'text' => $lineText];
        }
    }

    // Sort: stream ASC, y ASC (Y kecil = visual atas, Y besar = visual bawah - urutan baca top→bottom)
    usort($allBlocks, function($a, $b) {
        if ($a['sid'] !== $b['sid']) return $a['sid'] - $b['sid'];
        if (abs($a['y'] - $b['y']) < 3) return $a['x'] - $b['x']; // same Y → sort by X (left to right)
        return $a['y'] - $b['y']; // Y ASC = top to bottom
    });

    // ── 3. Klasifikasi pola teks untuk filter ──
    // Label kolom/header tabel
    $tableLabels = ['No','Pertanyaan','Sangat','tidakbaik','Tidakbaik','Biasa','Baik','baik',
                    'TotalJawaban','TotalSkor','Nilai','Lainnya','A',
                    'Keterangan','SangatBaik','SangatTidakBaik'];

    // Prefix pertanyaan kuesioner (awal kata dari teks pertanyaan)
    $questionPrefixes = ['Kesiapan','Keteraturan','Kemampuan','Kejelasan','Pemanfaatan',
                         'keanekaragaman','Keanekaragaman','Kesesuaian','Pemberian',
                         'Penguasaan','Penggunaan','Pengguanaan','Pelibatan','Keawibawaan','Kearifan',
                         'Menjadi','Satunya','Adil','dengankonteks','denganbidangtopik',
                         'pengembanganrekayasa','perkuliahaan','kelas',
                         'Mudahbergaul','Toleransi','Mengenal','Kemampuanmenerima',
                         'Kemampuanmenyampaikan','Mudah','Toleransiterhadap'];

    // Header dokumen
    $docHeaders = ['NamaDosen','NIPDosen','Homebase','Periode','Genap','Gasal',
                   'LAPORANREKAP','NUSAPUTRA','Dicetak','JlRaya'];

    $isTableLabel = function(string $t) use ($tableLabels): bool {
        $clean = preg_replace('/\s+/', '', $t);
        foreach ($tableLabels as $l) {
            if (strcasecmp($clean, $l) === 0 || stripos($clean, $l) === 0) return true;
        }
        return false;
    };

    $isQuestion = function(string $t) use ($questionPrefixes): bool {
        foreach ($questionPrefixes as $p) {
            if (stripos($t, $p) === 0) return true;
        }
        return false;
    };

    $isDocHeader = function(string $t) use ($docHeaders): bool {
        $clean = preg_replace('/\s+/', '', $t);
        foreach ($docHeaders as $h) {
            if (stripos($clean, $h) !== false) return true;
        }
        return false;
    };

    // ── 4. State machine parse ──
    $dosenData   = [];   // keyed by uppercase name
    $currentKey  = '';
    $expectNama  = false;
    $expectProdi = false;
    $inOpenQ     = false;
    $pendingKom  = [];

    $saveKom = function() use (&$dosenData, &$pendingKom, &$currentKey) {
        if ($currentKey && !empty($pendingKom)) {
            foreach ($pendingKom as $k) {
                if (!in_array($k, $dosenData[$currentKey]['kommentars'])) {
                    $dosenData[$currentKey]['kommentars'][] = $k;
                }
            }
        }
        $pendingKom = [];
    };

    foreach ($allBlocks as $bl) {
        $raw_line = trim($bl['text']);
        if (strlen($raw_line) < 1) continue;
        $clean = preg_replace('/\s+/', '', $raw_line);

        // ── Deteksi "NamaDosen" → mulai dosen baru ──
        if (stripos($clean, 'NamaDosen') !== false) {
            $saveKom();
            $inOpenQ    = false;
            $expectNama = true;
            continue;
        }

        // ── Deteksi "Homebase" → expect prodi ──
        if (stripos($clean, 'Homebase') !== false) {
            $expectProdi = true;
            continue;
        }

        // ── Capture nama dosen ──
        if ($expectNama) {
            $nama = strtoupper(preg_replace('/\s+/', '', $raw_line));
            if (strlen($nama) < 2 || $nama === ':') continue;
            // Skip jika nama terlalu panjang (kemungkinan footer/label bukan nama dosen)
            if (strlen($nama) > 50) {
                $expectNama = false;
                continue;
            }
            // Skip jika mengandung angka (kemungkinan bukan nama)
            if (preg_match('/\d/', $nama)) {
                $expectNama = false;
                continue;
            }
            
            $expectNama = false;
            $currentKey = $nama;
            if (!isset($dosenData[$currentKey])) {
                $dosenData[$currentKey] = [
                    'nama_raw'   => $nama,
                    'prodi'      => '',
                    'mksCount'   => 0,
                    'kommentars' => [],
                ];
            }
            $inOpenQ = false;
            continue;
        }

        // ── Capture prodi ──
        if ($expectProdi) {
            $prodiRaw = trim($raw_line);
            if (strlen($prodiRaw) < 2 || $prodiRaw === ':') continue;
            $expectProdi = false;
            if ($currentKey) {
                $prodi = preg_replace('/^S(?=[A-Z])/', '', $prodiRaw); // remove leading 'S' artifact
                if (empty($dosenData[$currentKey]['prodi'])) {
                    $dosenData[$currentKey]['prodi'] = $prodi;
                }
            }
            continue;
        }

        if (!$currentKey) continue;

        // ── Skip doc headers & footer ──
        if ($isDocHeader($raw_line)) continue;

        // ── Skip label tabel kuesioner ──
        if ($isTableLabel($raw_line)) continue;

        // ── Skip pertanyaan kuesioner ──
        if ($isQuestion($raw_line)) continue;

        // ── Skip angka saja (skor) ──
        if (preg_match('/^\d+$/', $clean)) continue;

        // ── Deteksi nama Mata Kuliah → hitung jumlah MK per dosen ──
        if (isMataKuliahName($clean)) {
            continue; // bukan komentar, skip
        }

        // ── Deteksi Rata-Rata / RataRata → akhir satu MK section ──
        if (stripos($clean, 'RataRata') === 0 || stripos($clean, 'Rata-Rata') === 0) {
            if ($currentKey) {
                $dosenData[$currentKey]['mksCount']++;
                // Check if this is the 'Rata-RataTotal' row which contains the overall score
                if (stripos($clean, 'Total') !== false || stripos($raw_line, 'Total') !== false) {
                    $dosenData[$currentKey]['expect_nilai'] = true;
                }
            }
            $inOpenQ = false;
            $saveKom();
            continue;
        }

        // Capture Nilai Kuesioner
        if (!empty($dosenData[$currentKey]['expect_nilai'])) {
            if (preg_match('/^[3-5]\.\d{2}$/', $clean)) {
                // We found the average float!
                $dosenData[$currentKey]['Nilai Kuesioner'] = (float) $clean;
                $dosenData[$currentKey]['expect_nilai'] = false;
            }
        }

        // ── Deteksi Open Question trigger ──
        $openQWords = ['OpenQuestion','KesandanPesan','tuliskandisini','Kesandan','KesanPesan'];
        foreach ($openQWords as $oq) {
            if (stripos($clean, $oq) !== false) {
                $saveKom();
                $inOpenQ = true;
                continue 2;
            }
        }

        // ── Collect komentar mahasiswa ──
        if ($inOpenQ) {
            $kom = cleanKomentar($raw_line); // apply word splitting immediately
            if (isValidKomentar($kom)) {
                if (!in_array($kom, $pendingKom)) {
                    $pendingKom[] = $kom;
                }
            }
        }
    }
    $saveKom();

    // ── 5. Peta nama dosen yang diketahui (dari mapping nama all-caps ke nama proper) ──
    $knownNames = [
        'BARKAH'             => 'BARKAH',
        'WIWINWINARNIM'      => 'WIWI WINARNIM',
        'WULANWIDANINGSIH'   => 'WULAN WIDANINGSIH',
        'AGUSHENDRIYANTO'    => 'AGUS HENDRIYANTO',
        'ENTITPUSPITA'       => 'ENTIT PUSPITA',
        'UJANGSYARIPHIDAYAT' => 'UJANG SYARIP HIDAYAT',
        'DYAHLYESMAYA'       => 'DYAH LYESMAYA',
    ];

    // ── 6. Build output rows ──
    $rows = [];
    $no   = 1;
    $rekamStandar = defined('ASPEK_REKOMENDASI_STANDAR')
        ? array_values(ASPEK_REKOMENDASI_STANDAR)
        : [
            'Kesiapan memberikan kuliah dan/atau praktek/praktikum',
            'Kejelasan penyampaian materi dan jawaban terhadap pertanyaan di kelas',
            'Pemanfaatan media dan teknologi pembelajaran',
            'Keanekaragaman cara pengukuran hasil belajar',
            'Kesesuaian materi ujian dan/atau tugas dengan tujuan mata kuliah',
        ];

    foreach ($dosenData as $key => $d) {
        if (strlen($key) < 2) continue;

        // Format nama dengan spasi (CamelCase split atau dari mapping)
        $namaFmt = $knownNames[$key] ?? splitJoinedWords($key);

        // Format prodi
        $prodi = $d['prodi'] ?: 'Pascasarjana';

        // Komentar valid saja (filter ulang)
        $komUnik = array_values(array_filter(
            $d['kommentars'],
            fn($k) => isValidKomentar($k) && strlen($k) >= 10
        ));

        $rows[] = [
            'No'               => $no++,
            'Nama'             => $namaFmt,
            'Prodi'            => function_exists('formatProdiStandard') ? formatProdiStandard($prodi) : $prodi,
            'Jumlah Matkul'    => $d['mksCount'],
            'Jumlah Kelas'     => $d['mksCount'],
            'Jumlah Responden' => '',
            // Nilai diekstrak dari Rata-Rata Total
            'Nilai Kuesioner'  => $d['Nilai Kuesioner'] ?? 0,
            'Jumlah Kehadiran' => 0,
            'Konten'           => 0,
            'Jumlah Penelitian'=> 0,
            'Jumlah Pengabdian'=> 0,
            // C1. Rekomendasi Perbaikan (5 aspek standar)
            'P1' => $rekamStandar[0] ?? '',
            'P2' => $rekamStandar[1] ?? '',
            'P3' => $rekamStandar[2] ?? '',
            'P4' => $rekamStandar[3] ?? '',
            'P5' => $rekamStandar[4] ?? '',
            // D. Catatan (komentar mahasiswa - dengan word splitting)
            'K1' => isset($komUnik[0]) ? cleanKomentar($komUnik[0]) : '',
            'K2' => isset($komUnik[1]) ? cleanKomentar($komUnik[1]) : '',
            'K3' => isset($komUnik[2]) ? cleanKomentar($komUnik[2]) : '',
            'K4' => isset($komUnik[3]) ? cleanKomentar($komUnik[3]) : '',
        ];
    }

    return $rows;
}
