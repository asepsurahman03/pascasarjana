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

    // Fallback dari nama file
    if (empty($result['nama'])) {
        $base = pathinfo($filePath, PATHINFO_FILENAME);
        $result['nama'] = preg_replace('/^AK_/', '', $base);
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

    // Urutkan: paling banyak "perlu perbaikan" dulu, lalu skor terendah
    uasort($aspekData, function($x, $y) {
        $d = $y['perlu_perbaikan'] - $x['perlu_perbaikan'];
        return $d !== 0 ? $d : ($x['skor'] <=> $y['skor']);
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

    // Prioritaskan komentar yang lebih substantif dan beragam untuk K1-K4
    $komentarSubstantif = array_values(array_filter($komentar, fn($k) => strlen($k) >= 35));
    usort($komentarSubstantif, fn($a, $b) => strlen($b) - strlen($a));

    $selected = [];
    foreach ($komentarSubstantif as $k) {
        if (count($selected) >= 4) break;
        $tooSimilar = false;
        foreach ($selected as $s) {
            similar_text(strtolower(mb_substr($k, 0, 100)), strtolower(mb_substr($s, 0, 100)), $pct);
            if ($pct > 65) { $tooSimilar = true; break; }
        }
        if (!$tooSimilar) $selected[] = $k;
    }

    // Jika belum cukup 4, ambil dari sisa komentar
    if (count($selected) < 4) {
        foreach ($komentar as $k) {
            if (count($selected) >= 4) break;
            if (!in_array($k, $selected)) $selected[] = $k;
        }
    }

    // Bersihkan prefix "Kesan:", "Kritik:", "• " dll dari komentar agar teks narasi bersih
    $selected = array_map(function($k) {
        // Hapus karakter zero-width space dan non-breaking space
        $k = str_replace(["\xE2\x80\x8B", "\xC2\xA0"], ['', ' '], $k);
        // Hapus bullet/dash/hash di awal
        $k = preg_replace('/^[\x{2022}\x{2023}\x{25E6}\x{2043}\x{2219}\x{200B}\xE2\x80\x8B•\-\*\#\s]+/u', '', $k);
        // Hapus label kombinasi di awal: "Kesan dan Saran Kritik:", "dan Saran Kritik:", "Kritik / Evaluasi:", "Pesan:", dll
        $k = preg_replace('/^(?:(?:Kesan|Kritik|Saran|Komentar|Catatan|Evaluasi|Pesan|dan)\s*[\/\&dan\s]*)*(?:Kesan|Kritik|Saran|Komentar|Catatan|Evaluasi|Pesan)\s*[:;\-]?\s*/iu', '', $k);
        // Ulangi pembersihan bullet/simbol di awal jika masih ada
        $k = preg_replace('/^[\x{2022}\x{2023}\x{25E6}\x{2043}\x{2219}\x{200B}\xE2\x80\x8B•\-\*\#\s\:\;]+/u', '', $k);
        return trim($k);
    }, $selected);

    // ===== 5. Analisis Sentimen Mahasiswa dari Seluruh Komentar XLS =====
    $counts = ['positif' => 0, 'netral' => 0, 'negatif' => 0];
    foreach ($komentar as $kc) {
        $st = analyzeTextSentiment($kc);
        $counts[$st]++;
    }
    $totalKomentar = array_sum($counts);
    $posPct = $totalKomentar > 0 ? round(($counts['positif'] / $totalKomentar) * 100, 1) : 0;
    $netPct = $totalKomentar > 0 ? round(($counts['netral'] / $totalKomentar) * 100, 1) : 0;
    $negPct = $totalKomentar > 0 ? round(($counts['negatif'] / $totalKomentar) * 100, 1) : 0;

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
