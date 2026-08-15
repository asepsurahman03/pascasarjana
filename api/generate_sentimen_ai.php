<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$feedbackText = '';
$nama_dosen = 'Dosen Yang Bersangkutan';
$periode = 'Periode Berjalan';

// Cek apakah ada file PDF yang diunggah
if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
    require_once __DIR__ . '/../vendor/autoload.php';
    try {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($_FILES['pdf_file']['tmp_name']);
        $feedbackText = $pdf->getText();
        $nama_dosen = trim($_POST['nama_dosen'] ?? $nama_dosen);
        $periode = trim($_POST['periode'] ?? $periode);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Gagal membaca PDF: ' . $e->getMessage()]);
        exit;
    }
} elseif (isset($_POST['feedback'])) {
    $feedbackText = trim($_POST['feedback']);
    $nama_dosen = trim($_POST['nama_dosen'] ?? $nama_dosen);
    $periode = trim($_POST['periode'] ?? $periode);
} else {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data) {
        $feedbackText = trim($data['feedback'] ?? '');
        $nama_dosen = trim($data['nama_dosen'] ?? $nama_dosen);
        $periode = trim($data['periode'] ?? $periode);
    }
}

// Bersihkan input teks dari karakter UTF-8 non-standar/invalid agar tidak merusak json_encode
$feedbackText = mb_convert_encoding($feedbackText, 'UTF-8', 'UTF-8');
$feedbackText = @iconv('UTF-8', 'UTF-8//IGNORE', $feedbackText);
$nama_dosen = mb_convert_encoding($nama_dosen, 'UTF-8', 'UTF-8');
$nama_dosen = @iconv('UTF-8', 'UTF-8//IGNORE', $nama_dosen);
$periode = mb_convert_encoding($periode, 'UTF-8', 'UTF-8');
$periode = @iconv('UTF-8', 'UTF-8//IGNORE', $periode);

// Fungsi untuk memotong teks PDF agar hanya mengambil bagian milik dosen yang dipilih
function extractLecturerText(string $fullText, string $lecturerName): string {
    $lines = explode("\n", $fullText);
    $startIndex = -1;
    $endIndex = -1;
    
    $cleanLecturerName = strtolower(trim($lecturerName));
    if (empty($cleanLecturerName) || $cleanLecturerName === 'dosen yang bersangkutan') {
        return $fullText;
    }
    
    foreach ($lines as $i => $line) {
        if (str_contains($line, 'Nama Dosen :') || str_contains($line, 'Nama Dosen:')) {
            if (str_contains(strtolower($line), $cleanLecturerName)) {
                $startIndex = $i;
            } elseif ($startIndex !== -1) {
                $endIndex = $i;
                break;
            }
        }
    }
    
    // Jika tidak ditemukan dengan Nama Dosen :, coba cari kecocokan nama langsung di baris
    if ($startIndex === -1) {
        foreach ($lines as $i => $line) {
            if (str_contains(strtolower($line), $cleanLecturerName)) {
                $startIndex = $i;
                break;
            }
        }
    }
    
    if ($startIndex !== -1) {
        $sliceEnd = ($endIndex !== -1) ? $endIndex : count($lines);
        $slicedLines = array_slice($lines, $startIndex, $sliceEnd - $startIndex);
        return implode("\n", $slicedLines);
    }
    
    return $fullText;
}

$feedbackText = extractLecturerText($feedbackText, $nama_dosen);

if (empty($feedbackText)) {
    echo json_encode(['error' => 'Tidak ada teks yang dapat dianalisis. Pastikan PDF berisi teks (bukan scan gambar) atau isi form teks manual.']);
    exit;
}

$geminiKey = trim(getSetting('gemini_api_key'));
$groqKey   = trim(getSetting('groq_api_key'));

// Deteksi key mana yang bertipe Groq (gsk_) dan mana yang Gemini
$actualGroqKey   = str_starts_with($groqKey, 'gsk_') ? $groqKey : (str_starts_with($geminiKey, 'gsk_') ? $geminiKey : '');
$actualGeminiKey = (!empty($geminiKey) && !str_starts_with($geminiKey, 'gsk_')) ? $geminiKey : ((!empty($groqKey) && !str_starts_with($groqKey, 'gsk_')) ? $groqKey : '');

if (empty($actualGroqKey) && empty($actualGeminiKey)) {
    echo json_encode(['error' => 'API Key belum diatur. Silakan masukkan Groq API Key (gsk_...) atau Gemini API Key di menu Pengaturan.']);
    exit;
}

$sysMsg = "Anda adalah AI Ahli Analisis Sentimen dan Evaluasi Akademik untuk dosen.
Tugas Anda adalah menganalisis teks (ekstraksi mentah dari PDF Laporan Evaluasi Dosen / LAPORAN REKAP KUESIONER). Fokuslah pada data dan komentar mahasiswa terhadap dosen bernama $nama_dosen pada periode $periode, lalu buat ringkasan menyeluruh.

=== STRUKTUR DATA INPUT (DIAMBIL DARI DATASET ACUAN: LAPORAN REKAP KUESIONER.PDF) ===
1. METADATA: Cari baris seperti 'LAPORAN REKAP KUESIONER', 'Periode', 'NIP Dosen', 'Nama Dosen', dan 'Homebase'.
2. DATA KUANTITATIF (TABEL PERTANYAAN):
   - Baris pertanyaan kuesioner biasanya memiliki deretan angka di bagian akhir. Contoh:
     '1 Kesiapan memberikan kuliah dan/atau praktek/praktikum  0 0 5 10 3'
     Artinya:
     - Sangat tidak baik (Skor 1): 0 respon
     - Tidak baik (Skor 2): 0 respon
     - Biasa (Skor 3): 5 respon
     - Baik (Skor 4): 10 respon
     - Sangat baik (Skor 5): 3 respon
     - Total responden = 18. Mayoritas menilai Baik (10) dan Sangat Baik (3). Rata-rata berkisar di angka 3.89.
   - Gunakan data angka ini untuk menganalisis kinerja pengajaran secara kuantitatif.
3. DATA KUALITATIF (KRITIK & SARAN):
   - Komentar dari mahasiswa diawali dengan label 'Kritik: ...' dan/atau 'Saran: ...'.
   - Contoh:
     'Kritik: ketika menyampaikan di zoom itu terlalu cepet dan kurang jelas Saran : tetep selalu mempertahankan sikapp ramahh'
   - AI wajib mengekstrak kritik dan saran ini ke dalam daftar 'items' untuk diklasifikasikan sentimennya.

TUGAS ANDA:
1. Buat paragraf evaluasi untuk 'Analisis Beban Kerja & Kinerja' (Bagian A). Bahas bagaimana kesiapan dosen, keteraturan kuliah, dan alokasi waktu mengajar berdasarkan data angka kuesioner.
2. Buat paragraf evaluasi untuk 'Evaluasi Pengajaran & Administrasi' (Bagian B). Bahas kemampuan menjelaskan materi, kejelasan penyampaian, penggunaan teknologi, keanekaragaman cara pengukuran hasil belajar, dan pemberian nilai/umpan balik tugas.
3. Buat paragraf evaluasi untuk 'Rekomendasi & Kompetensi Peningkatan' (Bagian C). Berikan rekomendasi konkret berdasarkan keluhan mahasiswa (misalnya jika tempo mengajar terlalu cepat, manajemen waktu zoom, atau komunikasi jadwal).
4. Klasifikasikan setiap komentar/pesan mahasiswa menjadi Positif, Netral, atau Negatif. Tampilkan MINIMAL 4 KOMENTAR mahasiswa yang paling relevan (atau tampilkan semua jika jumlah komentar kurang dari 4) di dalam list 'items'.
5. Buat rekapitulasi jumlah sentimen dan persentase.
6. Buat Surat Kesimpulan Evaluasi Dosen (3-4 paragraf) yang sangat formal dan akademis untuk dilaporkan kepada Kaprodi / Dekan.

FORMAT OUTPUT WAJIB BERUPA JSON persis seperti ini:
{
  \"bagian_a\": \"Paragraf analisis beban kerja & kinerja secara formal...\",
  \"bagian_b\": \"Paragraf analisis evaluasi pengajaran & administrasi secara formal...\",
  \"bagian_c\": \"Paragraf rekomendasi & kompetensi peningkatan secara formal...\",
  \"items\": [
    { \"no\": 1, \"kesan\": \"Kritik: ketika menyampaikan di zoom itu terlalu cepet... Saran : tetep selalu mempertahankan sikapp ramahh\", \"sentimen\": \"Negatif\" },
    { \"no\": 2, \"kesan\": \"Kritik: ... Saran: ...\", \"sentimen\": \"Positif\" },
    { \"no\": 3, \"kesan\": \"Kritik: ... Saran: ...\", \"sentimen\": \"Netral\" },
    { \"no\": 4, \"kesan\": \"Kritik: ... Saran: ...\", \"sentimen\": \"Positif\" }
  ],
  \"summary\": {
    \"positif\": 10,
    \"netral\": 2,
    \"negatif\": 1,
    \"total\": 13,
    \"positif_pct\": \"76.92%\",
    \"netral_pct\": \"15.38%\",
    \"negatif_pct\": \"7.69%\"
  },
  \"conclusion\": \"Berdasarkan hasil analisis sentimen dari evaluasi mahasiswa terhadap Saudara/i $nama_dosen, dengan ini disimpulkan bahwa... (buat naskah surat resmi yang komprehensif, ditujukan kepada dosen ybs dari prodi)\"
}";

// Batasi panjang teks untuk menghindari error "Request too large" (TPM limit) di Groq/Gemini.
// Menggunakan pemotongan "middle-out" agar tabel di awal dan komentar di akhir dokumen tetap terjaga.
$maxCharLimit = 22000;
if (mb_strlen($feedbackText) > $maxCharLimit) {
    $keepStart = 14000;
    $keepEnd = 7000;
    $feedbackText = mb_substr($feedbackText, 0, $keepStart) . 
                    "\n\n[... BEBERAPA BAGIAN TENGAH TEKS DIPOTONG UNTUK MENGHEMAT BATAS TOKEN ...]\n\n" . 
                    mb_substr($feedbackText, -$keepEnd);
}

$userMsg = "Berikut adalah daftar Kesan dan Pesan dari mahasiswa:\n" . $feedbackText;

$resultJson = null;
$lastError = '';

// ============================================================
// 1. Coba GROQ API jika key tersedia
// ============================================================
if (!empty($actualGroqKey)) {
    $groqModels = ['llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'gemma2-9b-it'];
    foreach ($groqModels as $model) {
        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        $postFields = json_encode([
            'model'       => $model,
            'messages'    => [
                ['role' => 'system', 'content' => $sysMsg],
                ['role' => 'user', 'content' => $userMsg]
            ],
            'temperature' => 0.2,
            'max_tokens'  => 3500,
            'response_format' => ['type' => 'json_object']
        ], JSON_INVALID_UTF8_SUBSTITUTE);

        if ($postFields === false) {
            $lastError = "JSON Encode Error: " . json_last_error_msg();
            curl_close($ch);
            break;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer ' . $actualGroqKey],
            CURLOPT_POSTFIELDS     => $postFields,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);

        if ($cerr) {
            $lastError = "Groq curl error: " . $cerr;
            continue;
        }
        
        $d = json_decode($resp, true);
        if ($code === 200 && !empty($d['choices'][0]['message']['content'])) {
            $resultJson = $d['choices'][0]['message']['content'];
            break;
        }
        
        $msg = $d['error']['message'] ?? "HTTP $code";
        $lastError = "Groq Error ($code) via $model: $msg";
    }
}

// ============================================================
// 2. Fallback ke GEMINI API jika Groq gagal atau key Gemini diisi
// ============================================================
if ($resultJson === null && !empty($actualGeminiKey)) {
    $geminiModels = ['gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-1.5-pro'];
    foreach ($geminiModels as $gmodel) {
        $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/{$gmodel}:generateContent?key={$actualGeminiKey}");
        $postFields = json_encode([
            'contents'         => [['parts' => [['text' => $sysMsg . "\n\n" . $userMsg]]]],
            'generationConfig' => [
                'temperature'      => 0.2, 
                'maxOutputTokens'  => 3000,
                'responseMimeType' => 'application/json'
            ],
        ], JSON_INVALID_UTF8_SUBSTITUTE);

        if ($postFields === false) {
            $lastError = "JSON Encode Error: " . json_last_error_msg();
            curl_close($ch);
            break;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 35,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $postFields,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);
        
        if ($cerr) {
            $lastError = "Gemini curl error: " . $cerr;
            continue;
        }
        
        $d = json_decode($resp, true);
        if ($code === 200 && !empty($d['candidates'][0]['content']['parts'][0]['text'])) {
            $resultJson = $d['candidates'][0]['content']['parts'][0]['text'];
            break;
        }
        
        $msg = $d['error']['message'] ?? "HTTP $code";
        $lastError = "Gemini Error ($code) via $gmodel: $msg";
    }
}

if ($resultJson === null) {
    echo json_encode(['error' => "Gagal menganalisis dengan AI. Detail error: " . $lastError]);
    exit;
}

// Ekstrak JSON secara kokoh dengan mencari kurung kurawal pertama dan terakhir
$firstBrace = strpos($resultJson, '{');
$lastBrace  = strrpos($resultJson, '}');
$parsed     = null;

if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
    $jsonStr = substr($resultJson, $firstBrace, $lastBrace - $firstBrace + 1);
    $parsed  = json_decode(trim($jsonStr), true);
}

if (!$parsed) {
    // Fallback jika pemotongan gagal, coba bersihkan markdown manual
    $cleanJson = preg_replace('/```json\s*/i', '', $resultJson);
    $cleanJson = preg_replace('/```\s*/',      '', $cleanJson);
    $parsed    = json_decode(trim($cleanJson), true);
}

if (!$parsed) {
    echo json_encode(['error' => 'AI tidak mengembalikan format JSON yang valid.', 'raw' => $resultJson]);
    exit;
}

echo json_encode([
    'ok' => true,
    'data' => $parsed
]);
