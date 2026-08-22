<?php
/**
 * Parser XLS Akuntansi (HTML format dari SIAKAD Nusa Putra)
 * Digunakan untuk raport dosen Gasal - data prodi Akuntansi
 * 
 * File XLS adalah HTML yang diekspor dari SIAKAD, berisi:
 * - Laporan rekap kuesioner per dosen (semua kelas)
 * - 28 aspek penilaian dengan skor 1-5 (STB/TB/Biasa/Baik/SB)
 * - Komentar / kesan mahasiswa
 */

/**
 * Parse satu file XLS Akuntansi, return array data dosen
 */
function parseAkuntansiDosenXLS(string $filePath): array {
    if (!file_exists($filePath)) return [];

    $html = file_get_contents($filePath);
    if (empty($html)) return [];

    $result = [
        'nama'              => '',
        'jumlah_responden'  => '',
        'nilai_kuesioner'   => 0,
        'P1' => '', 'P2' => '', 'P3' => '', 'P4' => '', 'P5' => '',
        'K1' => '', 'K2' => '', 'K3' => '', 'K4' => '',
    ];

    // ===== 1. Nama Dosen =====
    // Pattern HTML: <td>Nama Dosen</td><td>:</td><td>DEA ARME TIARA HARAHAP</td>
    preg_match('/<td[^>]*>\s*Nama Dosen\s*<\/td>\s*<td[^>]*>:\s*<\/td>\s*<td[^>]*>\s*(.*?)\s*<\/td>/si', $html, $mNama);
    $result['nama'] = strip_tags(html_entity_decode(trim($mNama[1] ?? ''), ENT_QUOTES, 'UTF-8'));

    // Fallback dari nama file (hapus prefix prodi apa pun: AK_, MN_, HK_, PG_, MH_, MM_, MP_)
    if (empty($result['nama'])) {
        $base = pathinfo($filePath, PATHINFO_FILENAME);
        $result['nama'] = preg_replace('/^[A-Z]{2}_/', '', $base);
    }

    // ===== 2. Nilai Kuesioner & Jumlah Responden =====
    // Rata-rata per kelas: <td colspan='2'> Rata-Rata</td><td colspan="5"> 4.71 </td>
    preg_match_all('/<td[^>]*colspan[^>]*>\s*Rata-Rata\s*<\/td>\s*<td[^>]*colspan[^>]*>\s*([\d\.]+)\s*<\/td>/si', $html, $mRata);

    // Total jawaban per kelas: <td colspan='2'> Total Jawaban</td> lalu 5 angka
    preg_match_all('/<td[^>]*colspan[^>]*>\s*Total Jawaban\s*<\/td>\s*((?:<td[^>]*>\s*\d+\s*<\/td>\s*){5})/si', $html, $mTJ);

    $rataArr = array_map('floatval', $mRata[1] ?? []);
    $respArr  = [];
    foreach ($mTJ[1] ?? [] as $row) {
        preg_match_all('/<td[^>]*>\s*(\d+)\s*<\/td>/si', $row, $nums);
        $totalJawab = array_sum($nums[1] ?? []);
        // Total Jawaban = jumlah_responden × 28 pertanyaan
        $respArr[] = $totalJawab > 0 ? round($totalJawab / 28) : 0;
    }

    // Rata-rata tertimbang nilai kuesioner
    $totalResp  = array_sum($respArr);
    $weightSum  = 0;
    for ($i = 0; $i < count($rataArr); $i++) {
        $w = $respArr[$i] ?? 1;
        $weightSum += $rataArr[$i] * $w;
    }
    if ($totalResp > 0) {
        $result['nilai_kuesioner'] = round($weightSum / $totalResp, 3);
    } elseif (count($rataArr) > 0) {
        $result['nilai_kuesioner'] = round(array_sum($rataArr) / count($rataArr), 3);
    }
    $result['jumlah_responden'] = $totalResp > 0 ? (string)$totalResp : (count($rataArr) > 0 ? (string)count($rataArr) : '');

    // ===== 3. 28 Aspek → Rekomendasi Perbaikan (P1-P5) =====
    // Tiap baris aspek: <td>No</td><td>Nama Aspek</td><td>STB</td><td>TB</td><td>Biasa</td><td>Baik</td><td>SB</td>
    preg_match_all(
        '/<tr[^>]*>\s*<td[^>]*>\s*(\d+)\s*<\/td>\s*<td[^>]*>\s*(.*?)\s*<\/td>\s*' .
        '<td[^>]*>\s*(\d+)\s*<\/td>\s*<td[^>]*>\s*(\d+)\s*<\/td>\s*' .
        '<td[^>]*>\s*(\d+)\s*<\/td>\s*<td[^>]*>\s*(\d+)\s*<\/td>\s*<td[^>]*>\s*(\d+)\s*<\/td>/si',
        $html, $mAspek, PREG_SET_ORDER
    );

    $aspekData = []; // [no => [nama, biasa_count, buruk_count, skor_weighted, total_resp]]
    foreach ($mAspek as $m) {
        $no  = (int)$m[1];
        $nama = strip_tags(html_entity_decode(trim($m[2]), ENT_QUOTES, 'UTF-8'));
        // Hilangkan huruf kapital awal yang salah (keanekaragaman → Keanekaragaman)
        if (ctype_lower($nama[0] ?? '')) {
            $nama = ucfirst($nama);
        }
        $stb = (int)$m[3];
        $tb  = (int)$m[4];
        $bia = (int)$m[5];
        $bk  = (int)$m[6];
        $sb  = (int)$m[7];
        $tot = $stb + $tb + $bia + $bk + $sb;

        if ($no < 1 || $no > 28 || empty($nama) || $tot === 0) continue;

        $skor = ($stb*1 + $tb*2 + $bia*3 + $bk*4 + $sb*5) / $tot;

        if (!isset($aspekData[$no])) {
            $aspekData[$no] = [
                'no'          => $no,
                'nama'        => $nama,
                'buruk'       => 0, // STB + TB
                'biasa'       => 0, // Biasa saja (nilai 3)
                'skor_w'      => 0,
                'total'       => 0,
            ];
        }
        $aspekData[$no]['buruk'] += ($stb + $tb);
        $aspekData[$no]['biasa'] += $bia;
        $aspekData[$no]['skor_w'] += $skor * $tot;
        $aspekData[$no]['total'] += $tot;
    }

    // Hitung skor akhir & bobot perbaikan per aspek
    foreach ($aspekData as $no => &$a) {
        $a['skor']             = $a['total'] > 0 ? round($a['skor_w'] / $a['total'], 4) : 0;
        $a['perlu_perbaikan']  = $a['buruk'] + $a['biasa']; // responden skor ≤ 3
    }
    unset($a);

    // Urutkan: paling banyak "perlu perbaikan" (buruk+biasa) dulu,
    // tiebreaker 1: paling banyak "buruk" saja (STB+TB = respon paling negatif),
    // tiebreaker 2: skor rata-rata terendah.
    // Urutan ini memastikan aspek yang paling banyak dikeluhkan mahasiswa
    // (paling banyak rekomendasi perbaikan) muncul di P1 dst.
    uasort($aspekData, function($x, $y) {
        // Tiebreaker primer: total perlu perbaikan (buruk + biasa)
        $d = $y['perlu_perbaikan'] - $x['perlu_perbaikan'];
        if ($d !== 0) return $d;
        // Tiebreaker sekunder: yang lebih banyak jawaban buruk (STB+TB) muncul duluan
        $db = $y['buruk'] - $x['buruk'];
        if ($db !== 0) return $db;
        // Tiebreaker tersier: skor rata-rata paling rendah muncul duluan
        return $x['skor'] <=> $y['skor'];
    });

    $rekArr = array_values(array_column($aspekData, 'nama'));
    $result['P1'] = $rekArr[0] ?? '';
    $result['P2'] = $rekArr[1] ?? '';
    $result['P3'] = $rekArr[2] ?? '';
    $result['P4'] = $rekArr[3] ?? '';
    $result['P5'] = $rekArr[4] ?? '';

    // ===== 4. Komentar Mahasiswa → Catatan (K1-K4) =====
    $cleanHtml = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
    $cleanHtml = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $cleanHtml);

    // Cari baris dengan konten teks komentar mahasiswa dari seluruh kelas di XLS
    preg_match_all(
        '/<tr[^>]*>\s*(?:<td[^>]*>(?:\s*\d+\s*|#\s*|&bull;\s*|\x{2022}\s*)<\/td>\s*)?\s*<td[^>]*>\s*([^<]{15,})\s*<\/td>\s*<\/tr>/siu',
        $cleanHtml, $mCom
    );

    // Kata-kata yang mengindikasikan bukan komentar mahasiswa (header, aspek, dll)
    static $skipPatterns = [
        '/^(Kesiapan|Keteraturan|Kemampuan|Kejelasan|Pemanfaatan|[Kk]eanekaragaman|Pemberian|Kesesuaian|Penguasaan|Pelibatan|Keawibawaan|Kearifan|Menjadi contoh|Satunya kata|Adil dalam|Mudah bergaul|Toleransi)/u',
        '/^(Total|Nilai|Rata-Rata|Dicetak|siakad|LAPORAN|Laporan|NIP Dosen|Homebase|Nama Dosen|Periode)/u',
        '/^(No\b|Pertanyaan|Sangat tidak|Tidak baik|Biasa|Baik|Sangat baik)/iu',
        '/font-family|border|padding|margin|display:|position:/i',
    ];

    $komentar = [];
    foreach ($mCom[1] as $c) {
        $c = html_entity_decode(trim($c), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $c = preg_replace('/\s+/', ' ', $c);
        if (strlen($c) < 15) continue;

        // Cek apakah alpha count cukup
        $alphaLen = strlen(preg_replace('/[^a-zA-Z\x{0080}-\x{FFFF}]/u', '', $c));
        if ($alphaLen < 8) continue;

        // Skip berdasarkan pola
        $skip = false;
        foreach ($skipPatterns as $pat) {
            if (preg_match($pat, ltrim($c))) { $skip = true; break; }
        }
        if ($skip) continue;

        // Skip class codes only lines (AK23I, MN25F)
        if (preg_match('/^[A-Z]{2}\d{2}[A-Z]\s*$/u', $c)) continue;
        // Skip mat kuliah + kode kelas
        if (preg_match('/\([A-Z]{2}\d{2}[A-Z]\)$/u', trim($c))) continue;
        $komentar[] = $c;
    }

    $komentar = array_values(array_unique($komentar));

    // ===== 4+5. SATU PASS: Hitung Sentimen & Pilih K1-K4 Sekaligus =====
    // Strategi: analisis sentimen SEMUA komentar dulu dalam satu loop, lalu:
    //   - K1-K4 dipilih dari komentar NEGATIF terlebih dahulu (terburuk di atas)
    //   - jika komentar negatif < 4, lengkapi dari komentar netral
    //   - jika masih kurang, lengkapi dari komentar positif
    // Ini memastikan Section D (Catatan) SELALU konsisten dengan Section E (Sentimen):
    // jika sentimen menunjukkan ada komentar negatif, catatan PASTI menampilkannya.
    $counts   = ['positif' => 0, 'netral' => 0, 'negatif' => 0];
    $kelompok = ['negatif' => [], 'netral' => [], 'positif' => []];

    foreach ($komentar as $kc) {
        $st       = analyzeTextSentiment($kc);
        $negScore = getKomentarNegScore($kc);
        $counts[$st]++;
        $kelompok[$st][] = ['teks' => $kc, 'neg_score' => $negScore];
    }

    // Urutkan kelompok negatif: skor negatif tertinggi dulu, tiebreaker panjang teks
    usort($kelompok['negatif'], function($a, $b) {
        if ($b['neg_score'] !== $a['neg_score']) return $b['neg_score'] <=> $a['neg_score'];
        return strlen($b['teks']) <=> strlen($a['teks']);
    });
    // Urutkan netral & positif: teks lebih panjang (lebih substantif) dulu
    usort($kelompok['netral'],  fn($a, $b) => strlen($b['teks']) <=> strlen($a['teks']));
    usort($kelompok['positif'], fn($a, $b) => strlen($b['teks']) <=> strlen($a['teks']));

    // Helper: tambahkan komentar dari pool ke selected, skip yang terlalu mirip
    $selectPool = function(array $pool, array &$selected, int $max, float $simThr = 65.0): void {
        foreach ($pool as $ks) {
            if (count($selected) >= $max) break;
            $k = $ks['teks'];
            $tooSimilar = false;
            foreach ($selected as $s) {
                similar_text(strtolower(mb_substr($k, 0, 100)), strtolower(mb_substr($s, 0, 100)), $pct);
                if ($pct > $simThr) { $tooSimilar = true; break; }
            }
            if (!$tooSimilar) $selected[] = $k;
        }
    };

    $selected = [];
    $selectPool($kelompok['negatif'], $selected, 4); // prioritas utama: komentar negatif
    $selectPool($kelompok['netral'],  $selected, 4); // pelengkap: komentar netral
    $selectPool($kelompok['positif'], $selected, 4); // fallback: komentar positif

    // Hitung statistik sentimen dari seluruh komentar
    $totalKomentar = array_sum($counts);
    $posPct = $totalKomentar > 0 ? round(($counts['positif'] / $totalKomentar) * 100, 1) : 0;
    $netPct = $totalKomentar > 0 ? round(($counts['netral']  / $totalKomentar) * 100, 1) : 0;
    $negPct = $totalKomentar > 0 ? round(($counts['negatif'] / $totalKomentar) * 100, 1) : 0;

    // Bersihkan prefix "Kesan:", "Kritik:", "• " dll dari komentar yang terpilih
    $cleanKomentar = function(string $k): string {
        $k = str_replace(["\xE2\x80\x8B", "\xC2\xA0"], ['', ' '], $k);
        $k = preg_replace('/^[\x{2022}\x{2023}\x{25E6}\x{2043}\x{2219}\x{200B}\xE2\x80\x8B•\-\*\#\s]+/u', '', $k);
        $k = preg_replace('/^(?:(?:Kesan|Kritik|Saran|Komentar|Catatan|Evaluasi|Pesan|dan)\s*[\/\&dan\s]*)*(?:Kesan|Kritik|Saran|Komentar|Catatan|Evaluasi|Pesan)\s*[:;\-]?\s*/iu', '', $k);
        $k = preg_replace('/^[\x{2022}\x{2023}\x{25E6}\x{2043}\x{2219}\x{200B}\xE2\x80\x8B•\-\*\#\s\:\;]+/u', '', $k);
        return trim($k);
    };
    $selected = array_map($cleanKomentar, $selected);

    $result['sentimen'] = [
        'total'        => $totalKomentar,
        'positif'      => $counts['positif'],
        'netral'       => $counts['netral'],
        'negatif'      => $counts['negatif'],
        'positif_pct'  => $posPct,
        'netral_pct'   => $netPct,
        'negatif_pct'  => $negPct,
        'kesimpulan'   => generateKesimpulanSentimen($posPct, $netPct, $negPct, $result['nilai_kuesioner']),
    ];

    $result['K1'] = $selected[0] ?? '';
    $result['K2'] = $selected[1] ?? '';
    $result['K3'] = $selected[2] ?? '';
    $result['K4'] = $selected[3] ?? '';

    return $result;
}

/**
 * Analisis sentimen teks bahasa Indonesia berbasis NLP kontekstual
 */
function analyzeTextSentiment(string $text): string {
    $text = strtolower($text);
    
    // Normalisasi negasi kontekstual: "tidak membosankan" -> bermakna positif, bukan negatif
    $text = str_replace(
        ['tidak membosankan', 'nggak membosankan', 'gak membosankan', 'tidak ngebosenin', 'gak bosenin', 'tidak membingungkan', 'tidak sulit', 'tidak susah', 'tidak ada masalah', 'tidak ada kendala'],
        ['sangat menarik', 'sangat menarik', 'sangat menarik', 'sangat menarik', 'sangat menarik', 'sangat jelas', 'sangat mudah', 'sangat mudah', 'sangat lancar', 'sangat lancar'],
        $text
    );
    
    // Respon netral eksplisit / konfirmasi umum
    if (preg_match('/^(tidak ada|ga ada|gada|nggak ada|belum ada|cukup|biasa|standar|aman|oke|ok|none|nihil|tdk ada)\.?$/i', trim($text))) {
        return 'netral';
    }

    // Frase positif berbobot kuat (bobot 3)
    $strongPosWords = [
        'luar biasa', 'the best', 'sangat baik', 'terbaik', 'sangat jelas', 'sangat membantu',
        'sangat menyenangkan', 'sangat menginspirasi', 'sangat puas', 'sangat interaktif',
        'sangat ramah', 'sangat sabar', 'sangat komunikatif', 'sangat bermanfaat', 'sangat mudah dipahami'
    ];
    
    // Kata / frase positif reguler (bobot 1.5)
    $posWords = [
        'terima kasih', 'terimakasih', 'makasih', 'thank you', 'thanks', 'bersyukur', 'bermanfaat',
        'bagus', 'jelas', 'mantap', 'ramah', 'interaktif', 'profesional', 'keren', 'memotivasi',
        'puas', 'sabar', 'komunikatif', 'terstruktur', 'asik', 'seru', 'hebat', 'apresiasi',
        'senang', 'suka', 'baik', 'mudah dipahami', 'mudah dimengerti', 'good', 'great', 'nice',
        'friendly', 'helpful', 'menarik', 'menyenangkan', 'menginspirasi', 'disiplin', 'tepat waktu',
        'runtut', 'rapi', 'sukses', 'sehat selalu', 'berkah', 'semangat'
    ];
    
    // Frase negatif berbobot kuat (bobot 3)
    $strongNegWords = [
        'tidak jelas', 'sulit dipahami', 'susah dipahami', 'sangat cepat', 'terlalu cepat',
        'membingungkan', 'membosankan', 'sering tidak masuk', 'tidak pernah masuk', 'sering telat',
        'kecewa', 'tidak adil', 'dipersulit', 'sangat membosankan', 'tugas terlalu banyak',
        'terlalu padat', 'kurang jelas', 'kurang paham', 'sulit dihubungi', 'susah dihubungi'
    ];
    
    // Kata / frase negatif reguler (bobot 1.5)
    $negWords = [
        'buruk', 'kurang', 'sulit', 'susah', 'bingung', 'rumit', 'keluhan', 'berat', 'monoton',
        'ngantuk', 'lambat', 'telat', 'jarang hadir', 'tidak hadir', 'tidak masuk', 'kurang detail',
        'kurang interaktif', 'kurang konsisten', 'kecepatan', 'terburu-buru', 'tidak sesuai',
        'keberatan', 'kendala'
    ];

    $posScore = 0;
    foreach ($strongPosWords as $w) {
        if (strpos($text, $w) !== false) $posScore += 3;
    }
    foreach ($posWords as $w) {
        if (strpos($text, $w) !== false) $posScore += 1.5;
    }
    
    $negScore = 0;
    foreach ($strongNegWords as $w) {
        if (strpos($text, $w) !== false) $negScore += 3;
    }
    foreach ($negWords as $w) {
        if (strpos($text, $w) !== false) $negScore += 1.5;
    }
    
    if ($posScore > $negScore + 0.5) return 'positif';
    if ($negScore > $posScore + 0.5) return 'negatif';
    return 'netral';
}

/**
 * Hitung skor negatif numerik dari sebuah komentar mahasiswa.
 * Digunakan untuk mengurutkan catatan K1-K4 dari komentar TERBURUK.
 * Semakin tinggi nilai return, semakin negatif/kritis komentar tersebut.
 * Konsisten dengan kata kunci di analyzeTextSentiment().
 */
function getKomentarNegScore(string $text): float {
    $text = strtolower($text);

    // Normalisasi negasi kontekstual (frasa ini sebenarnya positif)
    $text = str_replace(
        ['tidak membosankan', 'nggak membosankan', 'gak membosankan', 'tidak ngebosenin', 'gak bosenin',
         'tidak membingungkan', 'tidak sulit', 'tidak susah', 'tidak ada masalah', 'tidak ada kendala'],
        ['sangat menarik', 'sangat menarik', 'sangat menarik', 'sangat menarik', 'sangat menarik',
         'sangat jelas', 'sangat mudah', 'sangat mudah', 'sangat lancar', 'sangat lancar'],
        $text
    );

    // Frase negatif berbobot kuat (bobot 3)
    $strongNegWords = [
        'tidak jelas', 'sulit dipahami', 'susah dipahami', 'sangat cepat', 'terlalu cepat',
        'membingungkan', 'membosankan', 'sering tidak masuk', 'tidak pernah masuk', 'sering telat',
        'kecewa', 'tidak adil', 'dipersulit', 'sangat membosankan', 'tugas terlalu banyak',
        'terlalu padat', 'kurang jelas', 'kurang paham', 'sulit dihubungi', 'susah dihubungi',
    ];

    // Kata / frase negatif reguler (bobot 1.5)
    $negWords = [
        'buruk', 'kurang', 'sulit', 'susah', 'bingung', 'rumit', 'keluhan', 'berat', 'monoton',
        'ngantuk', 'lambat', 'telat', 'jarang hadir', 'tidak hadir', 'tidak masuk', 'kurang detail',
        'kurang interaktif', 'kurang konsisten', 'kecepatan', 'terburu-buru', 'tidak sesuai',
        'keberatan', 'kendala', 'lambat respon', 'tidak responsif', 'tidak tepat waktu',
        'perlu diperbaiki', 'harus diperbaiki', 'mohon diperbaiki', 'tolong diperbaiki',
        'kurang memuaskan', 'belum memuaskan', 'perlu ditingkatkan', 'harus ditingkatkan',
    ];

    // Frase positif kuat yang mengurangi skor negatif (bobot -2)
    $strongPosWords = [
        'luar biasa', 'the best', 'sangat baik', 'terbaik', 'sangat jelas', 'sangat membantu',
        'sangat menyenangkan', 'sangat menginspirasi', 'sangat puas', 'sangat ramah',
    ];

    $negScore = 0.0;
    foreach ($strongNegWords as $w) {
        if (strpos($text, $w) !== false) $negScore += 3.0;
    }
    foreach ($negWords as $w) {
        if (strpos($text, $w) !== false) $negScore += 1.5;
    }
    // Kurangi skor jika ada elemen positif kuat (komentar campuran)
    foreach ($strongPosWords as $w) {
        if (strpos($text, $w) !== false) $negScore -= 2.0;
    }

    return max(0.0, $negScore);
}

/**
 * Buat narasi kesimpulan evaluasi & sentimen
 */
function generateKesimpulanSentimen(float $posPct, float $netPct, float $negPct, float $skor = 0): string {
    if ($posPct >= 70) {
        return "Berdasarkan hasil kuesioner dan analisis sentimen, mayoritas mahasiswa memberikan respon SANGAT POSITIF ({$posPct}%) terhadap proses pembelajaran dan pengajaran dosen. Dosen diharapkan mempertahankan kinerja yang baik serta menindaklanjuti rekomendasi perbaikan pada Bagian C untuk peningkatan mutu berkelanjutan.";
    } elseif ($posPct >= 50) {
        return "Berdasarkan hasil kuesioner dan analisis sentimen, respon mahasiswa didominasi penilaian POSITIF ({$posPct}%) dan NETRAL ({$netPct}%). Dosen disarankan untuk memperhatikan catatan mahasiswa dan rekomendasi perbaikan pada Bagian C guna meningkatkan efektivitas perkuliahan.";
    } elseif ($posPct > 0 || $negPct > 0) {
        return "Berdasarkan hasil kuesioner dan analisis sentimen, terdapat beberapa aspek yang memerlukan evaluasi lebih lanjut dengan proporsi masukan netral ({$netPct}%) dan masukan perbaikan ({$negPct}%). Dosen diharapkan mengoptimalkan metode pengajaran sesuai rekomendasi pada Bagian C.";
    } else {
        return "Hasil evaluasi Tridharma dosen menunjukkan kinerja yang telah memenuhi standar institusi. Rekomendasi perbaikan pada Bagian C dapat dijadikan acuan dalam penyusunan rencana pembelajaran semester berikutnya.";
    }
}

/**
 * Baca semua dosen dari folder Akuntansi XLS
 * Return: array keyed by nama_normalized => data_dosen
 */
function parseAllAkuntansiDosen(string $folderPath): array {
    $result = [];
    $files = glob(rtrim($folderPath, '/\\') . '/AK_*.xls');
    if (empty($files)) return $result;

    foreach ($files as $f) {
        $data = parseAkuntansiDosenXLS($f);
        if (!empty($data['nama'])) {
            // Key: nama dinormalisasi untuk matching
            $key = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['nama']));
            $result[$key] = $data;
        }
    }
    return $result;
}

/**
 * Mapping prefix file XLS ke nama prodi standar.
 * FBHP: Akuntansi, Manajemen, Hukum, PGSD, S2 Hukum, S2 Manajemen, S2 Pedagogi
 * FECD: DKV, Sistem Informasi, Teknik Elektro, Teknik Informatika, Teknik Mesin, Teknik Sipil, Magister Informatika
 */
function getProdiPrefixMap(): array {
    return [
        // FBHP
        'AK'  => 'Akuntansi',
        'MN'  => 'Manajemen',
        'HK'  => 'Hukum',
        'PG'  => 'PGSD',
        'MH'  => 'S2 Hukum',
        'MM'  => 'S2 Manajemen',
        'MP'  => 'S2 Pedagogi',
        // FECD
        'DKV' => 'Desain Komunikasi Visual',
        'KV'  => 'Desain Komunikasi Visual',
        'SI'  => 'Sistem Informasi',
        'TE'  => 'Teknik Elektro',
        'TI'  => 'Teknik Informatika',
        'TM'  => 'Teknik Mesin',
        'TS'  => 'Teknik Sipil',
        'MI'  => 'Magister Informatika',
    ];
}

/**
 * Baca semua dosen dari satu folder prodi (generic).
 * Scan semua *.xls dalam $folderPath dan return array keyed by nama_normalized.
 *
 * @param  string $folderPath  Path absolut ke folder prodi (misal .../FBHP/Akuntansi)
 * @return array               [ 'NORMNAMA' => data_dosen ]
 */
function parseProdiFolder(string $folderPath): array {
    $result = [];
    $files  = glob(rtrim($folderPath, '/\\') . DIRECTORY_SEPARATOR . '*.xls');
    if (empty($files)) return $result;

    foreach ($files as $f) {
        $data = parseAkuntansiDosenXLS($f);
        if (!empty($data['nama'])) {
            $key = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['nama']));
            $result[$key] = $data;
        }
    }
    return $result;
}

/**
 * Baca semua dosen dari satu folder fakultas (misal FBHP atau FECD).
 * Scan setiap subfolder dan kembalikan data per prodi key.
 *
 * @param  string $fakultasPath  Path absolut ke folder fakultas
 * @param  array  $folderToProdi Map nama subfolder => prodi key (lowercase)
 * @return array                 [ 'prodi_key' => [ 'NORMNAMA' => data ], ... ]
 */
function parseAllProdiDosen(string $fakultasPath, array $folderToProdi = []): array {
    // Default: FBHP mapping (backward-compatible)
    if (empty($folderToProdi)) {
        $folderToProdi = [
            'Akuntansi'    => 'akuntansi',
            'Manajemen'    => 'manajemen',
            'Hukum'        => 'hukum',
            'PGSD'         => 'pgsd',
            'S2 Hukum'     => 's2 hukum',
            'S2 Manajemen' => 's2 manajemen',
            'S2 Pedagogi'  => 's2 pedagogi',
        ];
    }

    $result = [];
    $base   = rtrim($fakultasPath, '/\\');

    foreach ($folderToProdi as $folderName => $prodiKey) {
        $folderPath = $base . DIRECTORY_SEPARATOR . $folderName;
        if (!is_dir($folderPath)) continue;
        $prodiData = parseProdiFolder($folderPath);
        if (!empty($prodiData)) {
            // Merge ke key yang sama jika sudah ada (misal DKV dari FECD)
            $result[$prodiKey] = array_merge($result[$prodiKey] ?? [], $prodiData);
        }
    }

    return $result;
}

/**
 * Baca semua dosen dari SEMUA folder fakultas (FBHP + FECD) sekaligus.
 * Param $rekapPath adalah path ke folder "Rekap Koesioner" yang berisi subfolder per fakultas.
 *
 * @param  string $rekapPath  Contoh: __DIR__ . '/../Contoh Lampiran/.../Rekap Koesioner'
 * @return array              [ 'prodi_key' => [ 'NORMNAMA' => data ], ... ]
 */
function parseAllFakultasDosen(string $rekapPath, bool $useCache = true): array {
    $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'raport_rekap_koesioner_gasal_' . md5($rekapPath) . '.json';

    // Cek cache jika masih valid (< 24 jam)
    if ($useCache && file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400)) {
        $cached = @json_decode(@file_get_contents($cacheFile), true);
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }
    }

    $rekapBase = rtrim($rekapPath, '/\\');

    // Peta: nama subfolder fakultas => peta (nama subfolder prodi => prodi key)
    $fakultasMap = [
        'FBHP' => [
            'Akuntansi'    => 'akuntansi',
            'Manajemen'    => 'manajemen',
            'Hukum'        => 'hukum',
            'PGSD'         => 'pgsd',
            'S2 Hukum'     => 's2 hukum',
            'S2 Manajemen' => 's2 manajemen',
            'S2 Pedagogi'  => 's2 pedagogi',
        ],
        'FECD' => [
            'DKV'                 => 'desain komunikasi visual',
            'Sistem Informasi'    => 'sistem informasi',
            'Teknik Elektro'      => 'teknik elektro',
            'Teknik Informatika'  => 'teknik informatika',
            'Teknik Mesin'        => 'teknik mesin',
            'Teknik Sipil'        => 'teknik sipil',
            'Magister Informatika'=> 'magister informatika',
        ],
    ];

    $result = [];
    foreach ($fakultasMap as $fakultasFolder => $folderToProdi) {
        $fakultasPath = $rekapBase . DIRECTORY_SEPARATOR . $fakultasFolder;
        if (!is_dir($fakultasPath)) continue;
        $prodiData = parseAllProdiDosen($fakultasPath, $folderToProdi);
        foreach ($prodiData as $prodiKey => $data) {
            $result[$prodiKey] = array_merge($result[$prodiKey] ?? [], $data);
        }
    }

    // Simpan ke cache untuk percepatan load
    if ($useCache && !empty($result)) {
        @file_put_contents($cacheFile, json_encode($result));
    }

    return $result;
}

/**
 * Cocokkan nama dosen Excel ke data prodi XLS.
 * Sama persis dengan matchAkuntansiDosen() tetapi generic untuk semua prodi.
 *
 * @param  string $namaExcel  Nama dari kolom Excel
 * @param  array  $prodiData  Hasil parseAllProdiDosen()[$prodiKey]
 * @return array|null
 */
function matchProdiDosen(string $namaExcel, array $prodiData): ?array {
    return matchAkuntansiDosen($namaExcel, $prodiData);
}

/**
 * Match nama dosen dari Excel ke data Akuntansi XLS
 * Return: data dari XLS atau null jika tidak cocok
 */
function matchAkuntansiDosen(string $namaExcel, array $akData): ?array {
    if (empty($akData)) return null;

    $normalize = function(string $s): string {
        // Normalisasi: uppercase, hapus non-alphanumeric, hilangkan gelar akademik
        $s = strtoupper($s);
        // Hapus gelar: M.Ak, M.Acc, SE, MM, MBA, S.Ak, dst
        $s = preg_replace('/\b(?:S[E]|MM|MBA|S\.?AK|M\.?AK|M\.?ACC|M\.?SI|M\.?STAT|M\.?TR\.?E|SE\.?M\.?AK|M\.?AK\.?|S\.?AKUN)\b\.?/i', '', $s);
        // Hapus non-alphanumeric
        $s = preg_replace('/[^A-Z0-9]/', '', $s);
        return $s;
    };

    $keyExcel = $normalize($namaExcel);

    // Exact match (dengan normalisasi)
    foreach ($akData as $key => $data) {
        $keyAK = $normalize($data['nama']);
        if ($keyExcel === $keyAK) return $data;
    }

    // Partial match: cek substring
    foreach ($akData as $key => $data) {
        $keyAK = $normalize($data['nama']);
        if (strpos($keyAK, $keyExcel) !== false || strpos($keyExcel, $keyAK) !== false) {
            return $data;
        }
    }

    // Similarity match dengan threshold lebih rendah
    $bestMatch = null;
    $bestPct   = 0;
    foreach ($akData as $key => $data) {
        $keyAK = $normalize($data['nama']);
        similar_text($keyExcel, $keyAK, $pct);
        if ($pct > $bestPct) {
            $bestPct   = $pct;
            $bestMatch = $data;
        }
    }
    if ($bestPct >= 72) return $bestMatch;

    // First-word match: bandingkan nama pertama (nama depan)
    $firstWordExcel = preg_replace('/[^A-Z].*/', '', $keyExcel);
    if (strlen($firstWordExcel) >= 4) {
        foreach ($akData as $key => $data) {
            $keyAK = $normalize($data['nama']);
            $firstWordAK = preg_replace('/[^A-Z].*/', '', $keyAK);
            if ($firstWordExcel === $firstWordAK) {
                // Cocok nama depan, cek juga ada kesamaan sisanya
                similar_text($keyExcel, $keyAK, $pct2);
                if ($pct2 >= 55) return $data;
            }
        }
    }

    return null;
}
