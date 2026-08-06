<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data   = json_decode(file_get_contents('php://input'), true);
$prompt = trim($data['prompt'] ?? '');

if (empty($prompt)) {
    echo json_encode(['error' => 'Prompt tidak boleh kosong.']);
    exit;
}

$geminiKey = getSetting('gemini_api_key');
$groqKey   = getSetting('groq_api_key');

$apiKey = '';
$isGroq = false;

// Deteksi tipe key secara cerdas: untuk surat, prefer Groq (starts_with gsk_) karena cepat
if (!empty($groqKey) && str_starts_with($groqKey, 'gsk_')) {
    $apiKey = $groqKey;
    $isGroq = true;
} elseif (!empty($geminiKey) && str_starts_with($geminiKey, 'gsk_')) {
    $apiKey = $geminiKey;
    $isGroq = true;
} elseif (!empty($geminiKey) && !str_starts_with($geminiKey, 'gsk_')) {
    $apiKey = $geminiKey;
    $isGroq = false;
} elseif (!empty($groqKey) && !str_starts_with($groqKey, 'gsk_')) {
    $apiKey = $groqKey;
    $isGroq = false;
} else {
    echo json_encode(['error' => 'API Key belum diatur. Silakan isi di pengaturan.']);
    exit;
}

$systemPrompt = getSetting('ai_system_prompt') ?:
    'Anda adalah Asisten Penulis Surat Resmi AHLI untuk Program Studi Pascasarjana Universitas Nusa Putra.';

$variabelSurat = [
    '{{NOMOR_SURAT}}','{{TANGGAL_SURAT}}','{{KOTA}}','{{NAMA_PRODI}}','{{NAMA_KAPRODI}}',
    '{{GELAR_KAPRODI}}','{{NIDN_KAPRODI}}','{{NAMA_PENERIMA}}','{{NIM}}','{{NAMA_MAHASISWA}}',
    '{{TAHUN_AKADEMIK}}','{{ANGKATAN}}','{{KEPERLUAN}}','{{MATA_KULIAH}}','{{SKS}}',
    '{{SEMESTER}}','{{JUDUL_PENELITIAN}}','{{LOKASI_PENELITIAN}}','{{TANGGAL_MULAI}}',
    '{{TANGGAL_SELESAI}}','{{JENIS_KEGIATAN}}','{{TANGGAL_KEGIATAN}}','{{WAKTU}}','{{TEMPAT}}',
    '{{NAMA_PRESENTER}}','{{JUDUL_TESIS}}','{{IPK}}','{{PREDIKAT}}','{{TANGGAL_YUDISIUM}}',
    '{{TUJUAN_REKOMENDASI}}','{{PRESTASI}}','{{NO_IJAZAH}}','{{PERIHAL}}','{{ISI_PEMBERITAHUAN}}',
    '{{TANGGAL_PELAKSANAAN}}','{{NAMA_INSTANSI}}','{{ALAMAT_INSTANSI}}','{{ISI_PERMOHONAN}}',
    '{{PESERTA}}','{{HASIL_KEGIATAN}}'
];

// Tambahkan konteks waktu saat ini agar AI bisa menghitung "3 hari dari sekarang"
$currentDate = date('d F Y');
$sysMsg  = $systemPrompt . "\n\n(INFO SISTEM: Hari ini adalah tanggal " . $currentDate . ")\n\nGunakan placeholder berikut jika sesuai: " . implode(', ', $variabelSurat) . "\n\n" .
"=== IDENTITAS & KEPINTARAN AI ===\n" .
"Anda adalah editor dan penulis surat resmi tingkat tinggi yang SANGAT CERDAS dan PEKA terhadap instruksi, baik yang spesifik maupun yang samar/umum.\n" .
"Ketika menerima instruksi REVISI yang AMBIGU atau UMUM (seperti 'perbaiki', 'buat lebih baik', 'update redaksinya', 'tingkatkan', dll), Anda HARUS menafsirkan maksudnya secara cerdas dan melakukan perbaikan nyata yang dapat dirasakan pada output, bukan hanya mengulang surat yang sama.\n\n" .
"=== KEMAMPUAN INTERPRETASI CERDAS ===\n" .
"Jika instruksi revisi bersifat umum, lakukan SEMUA atau SEBAGIAN dari tindakan berikut sesuai konteks:\n" .
"• 'perbaiki redaksi / kalimat / bahasa / tulisan' → Perbaiki diksi, ganti kata-kata biasa dengan sinonim akademis yang lebih kuat, susun ulang kalimat agar lebih mengalir, hilangkan kata mubazir\n" .
"• 'buat lebih formal / resmi / akademis' → Tingkatkan register bahasa ke level birokrasi/akademis tertinggi, hindari bahasa sehari-hari\n" .
"• 'buat lebih singkat / padat' → Pangkas kalimat berulang, satukan paragraf yang membahas hal sama, buang basa-basi berlebihan\n" .
"• 'buat lebih lengkap / detail / elaborasi' → Tambahkan alasan/latar belakang yang kuat, rincian kegiatan, dampak yang diharapkan\n" .
"• 'buat lebih sopan / ramah / hangat' → Tambahkan ungkapan penghargaan, gunakan sapaan hormat yang lebih personal\n" .
"• 'buat lebih tegas / lugas' → Langsung ke inti, gunakan kalimat aktif, hindari ungkapan ragu-ragu\n" .
"• 'sesuaikan / update / refresh / tingkatkan' → Kombinasi: perbaiki diksi + perkuat argumen + rapikan struktur\n\n" .
"=== ATURAN WAJIB PENULISAN ===\n" .
"1. JANGAN cantumkan 'Kepada Yth', 'Dengan hormat', 'Hormat kami', nama Kaprodi, atau NIDN — sudah ada otomatis di sistem.\n" .
"2. LANGSUNG MULAI dengan kalimat isi surat ('Berdasarkan...', 'Sehubungan dengan...', 'Melalui surat ini...').\n" .
"3. Akhiri dengan kalimat penutup resmi: 'Demikian surat ini kami sampaikan. Atas perhatian dan kerja sama yang baik, kami ucapkan terima kasih.'\n" .
"4. KUALITAS: Detail, elegan, diksi akademis-profesional, paragraf runtut (Pendahuluan kuat → Isi elaboratif → Penutup tegas).\n" .
"5. STYLE HTML: Gunakan <p style=\"text-align: justify; margin-bottom: 8px;\"> untuk setiap paragraf.\n" .
"6. PANTANGAN: JANGAN gunakan kata 'Anda' atau 'Kamu'. Selalu gunakan 'Bapak/Ibu', 'Saudara/i', atau kalimat pasif.\n\n" .
"=== FORMAT OUTPUT (WAJIB JSON) ===\n" .
"Kembalikan SELALU dalam format JSON berikut:\n" .
"{\n" .
"  \"perihal\": \"Ringkasan perihal surat (singkat, padat, maks 5-7 kata)\",\n" .
"  \"jenis_surat\": \"Kategorikan: (Surat Undangan, Surat Tugas, Surat Keterangan, Surat Pemberitahuan, Surat Rekomendasi, Lainnya)\",\n" .
"  \"penerima\": \"Nama target penerima surat (asumsikan dari konteks jika tidak eksplisit)\",\n" .
"  \"prodi\": \"Nama prodi yang disebutkan. Kosongkan jika tidak ada.\",\n" .
"  \"ai_reply\": \"Penjelasan singkat apa yang Anda lakukan/ubah dalam bahasa Indonesia yang natural dan ramah (1-2 kalimat). Contoh: 'Saya telah memperbaiki diksi dan memperkuat argumentasi pada seluruh paragraf.' atau 'Waktu pelaksanaan telah diperbarui menjadi pukul 12.00 WIB.'\",\n" .
"  \"html\": \"Isi draf surat formal dalam format HTML mentah (gunakan tag p, strong, em, ul, table jika perlu).\"\n" .
"}";

// === MULTI-TURN CONVERSATION (ChatGPT-style) ===
$chatHistory   = $data['chat_history'] ?? [];
$previous_html = trim($data['previous_html'] ?? '');

// ============================================================
// SMART INTENT DETECTION — memperkaya instruksi revisi ambigu
// ============================================================
function enrichRevisionPrompt(string $userPrompt): string {
    $p = mb_strtolower(trim($userPrompt));

    $intents = [
        // Redaksi / bahasa
        ['keywords' => ['redaksi','kalimat','bahasa','tulisan','kata','diksi','phrasing'],
         'enrichment' => "Perbaiki diksi, gunakan kata-kata akademis yang lebih kuat dan elegan, susun ulang kalimat agar mengalir lebih baik, hilangkan kata mubazir/repetisi."],
        // Formal
        ['keywords' => ['formal','resmi','akademis','profesional','berwibawa'],
         'enrichment' => "Tingkatkan register bahasa ke level birokrasi/akademis tertinggi, hindari ekspresi informal, gunakan ungkapan yang berwibawa."],
        // Singkat/padat
        ['keywords' => ['singkat','padat','ringkas','pendek','efisien'],
         'enrichment' => "Pangkas pengulangan dan basa-basi, padatkan kalimat menjadi lebih efisien tanpa kehilangan substansi."],
        // Detail/lengkap
        ['keywords' => ['detail','lengkap','elaborasi','panjang','komprehensif','kembangkan'],
         'enrichment' => "Tambahkan latar belakang yang kuat, elaborasi rincian kegiatan/tujuan, dan perkuat setiap paragraf dengan argumen yang lebih menyeluruh."],
        // Sopan/ramah
        ['keywords' => ['sopan','ramah','halus','santun','hormat'],
         'enrichment' => "Tambahkan ungkapan penghargaan yang tulus, gunakan sapaan yang lebih personal dan hangat, perhalus diksi."],
        // Tegas/lugas
        ['keywords' => ['tegas','lugas','langsung','to the point','clear','jelas'],
         'enrichment' => "Gunakan kalimat aktif yang tegas, langsung ke inti pesan, hindari ungkapan yang terkesan ragu-ragu."],
        // Umum: perbaiki/update/sesuaikan
        ['keywords' => ['perbaiki','sesuaikan','update','tingkatkan','improve','refresh','lebih baik','bagus','sempurnakan','revisi','ulas','poles'],
         'enrichment' => "Lakukan perbaikan menyeluruh: perbaiki diksi dan gaya bahasa, perkuat argumentasi, rapikan alur paragraf, dan pastikan setiap kalimat memberi nilai tambah yang nyata."],
    ];

    $matched = [];
    foreach ($intents as $intent) {
        foreach ($intent['keywords'] as $kw) {
            if (str_contains($p, $kw)) {
                $matched[] = $intent['enrichment'];
                break;
            }
        }
    }

    if (empty($matched)) {
        return $userPrompt; // instruksi sudah spesifik, tidak perlu enrichment
    }

    $enriched = $userPrompt . "\n\n[PANDUAN EKSEKUSI OTOMATIS — WAJIB DIIKUTI]:\n";
    foreach (array_unique($matched) as $m) {
        $enriched .= "• " . $m . "\n";
    }
    $enriched .= "\nTerapkan seluruh panduan di atas secara nyata pada seluruh isi surat. Pastikan perubahan terasa jelas dan signifikan, bukan sekadar mengulang versi sebelumnya.";
    return $enriched;
}

// Bangun messages multi-turn untuk Groq
$messages = [['role' => 'system', 'content' => $sysMsg]];

if (!empty($chatHistory)) {
    foreach ($chatHistory as $turn) {
        $role    = ($turn['role'] === 'user') ? 'user' : 'assistant';
        $content = trim($turn['content'] ?? '');
        if ($content === '') continue;
        $messages[] = ['role' => $role, 'content' => $content];
    }
    $enrichedPrompt = enrichRevisionPrompt($prompt);
    $enrichedPrompt = enrichRevisionPrompt($prompt);
    $messages[] = ['role' => 'user', 'content' =>
        "REVISI DRAF SURAT TERAKHIR BERDASARKAN INSTRUKSI BERIKUT:\n\"$enrichedPrompt\"\n\n" .
        "PENTING: Anda HARUS MENGUBAH isi teks surat sesuai instruksi. JANGAN HANYA MENGCOPAS SURAT SEBELUMNYA.\n" .
        "Keluarkan KESELURUHAN draf yang sudah direvisi secara utuh. Pertahankan struktur HTML-nya, tetapi UBAH ISINYA agar lebih baik dan sesuai instruksi. " .
        "Isi field 'ai_reply' dengan penjelasan tindakan apa saja yang Anda lakukan pada revisi ini."
    ];
} elseif (!empty($previous_html)) {
    $enrichedPrompt = enrichRevisionPrompt($prompt);
    $messages[] = ['role' => 'user', 'content' =>
        "Ini adalah draf surat saat ini (HTML):\n\n" . $previous_html .
        "\n\nTOLONG REVISI DRAF DI ATAS BERDASARKAN INSTRUKSI BERIKUT:\n\"$enrichedPrompt\"\n\n" .
        "PENTING: Anda HARUS MENGUBAH isi teks surat sesuai instruksi. JANGAN HANYA MENGCOPAS SURAT SEBELUMNYA.\n" .
        "Keluarkan KESELURUHAN draf yang sudah direvisi secara utuh. Isi field 'ai_reply' dengan penjelasan tindakan apa saja yang Anda lakukan."
    ];
} else {
    $messages[] = ['role' => 'user', 'content' =>
        "Buat surat formal baru yang SANGAT DETAIL, kaya makna, elegan, dan berwibawa berdasarkan instruksi berikut:\n\"$prompt\"\n\n" .
        "Kembangkan konteks dari instruksi di atas menjadi surat yang komprehensif dengan pendahuluan kuat, isi elaboratif, dan penutup yang tegas."
    ];
}

// userMsg hanya untuk Gemini fallback (single-turn)
$userMsg = end($messages)['content'];

$resultJson = null;
$isGroq = str_starts_with($apiKey, 'gsk_');
$lastError = '';

if ($isGroq) {
    // ============================================================
    // Coba Groq API
    // ============================================================
    $groqModels = ['llama-3.3-70b-versatile', 'llama3-70b-8192', 'llama-3.1-8b-instant', 'gemma2-9b-it'];
    foreach ($groqModels as $model) {
        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        $postFields = json_encode([
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => 0.7,
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
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
            CURLOPT_POSTFIELDS     => $postFields,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);

        if ($cerr) {
            $lastError = "Curl error: " . $cerr;
            continue;
        }
        
        $d = json_decode($resp, true);
        if ($code === 200 && !empty($d['choices'][0]['message']['content'])) {
            $resultJson = $d['choices'][0]['message']['content'];
            break;
        }
        
        $msg = $d['error']['message'] ?? "HTTP $code";
        $lastError = "Groq API Error ($code) via $model: $msg";
        
        if ($code === 404 || $code === 503 || $code === 429 || $code === 413) continue;
        break; // Jika error fatal seperti 400, tidak perlu coba model lain
    }
} else {
    // ============================================================
    // Coba Gemini API
    // ============================================================
    $geminiModels = ['gemini-2.5-flash', 'gemini-3.5-flash', 'gemini-flash-latest', 'gemini-1.5-flash', 'gemini-pro-latest'];
    foreach ($geminiModels as $gmodel) {
        $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/{$gmodel}:generateContent?key={$apiKey}");
        $postFields = json_encode([
            'contents'         => [['parts' => [['text' => $sysMsg . "\n\n" . $userMsg]]]],
            'generationConfig' => [
                'temperature' => 0.7, 
                'maxOutputTokens' => 2000,
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
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $postFields,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $cerr = curl_error($ch);
        curl_close($ch);
        
        if ($cerr) {
            $lastError = "Curl error: " . $cerr;
            continue;
        }
        
        $d = json_decode($resp, true);
        if ($code === 200 && !empty($d['candidates'][0]['content']['parts'][0]['text'])) {
            $resultJson = $d['candidates'][0]['content']['parts'][0]['text'];
            break;
        }
        
        $msg = $d['error']['message'] ?? "HTTP $code";
        $lastError = "Gemini API Error ($code) via $gmodel: $msg";
        
        if ($code === 404) continue;
        break;
    }
}

if ($resultJson === null) {
    $serviceName = $isGroq ? "Groq" : "Gemini";
    echo json_encode(['error' => "Gagal menggunakan API $serviceName. Detail error: " . $lastError]);
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

$html = $parsed['html'] ?? '';
$html = preg_replace('/```html\s*/i', '', $html);
$html = preg_replace('/```\s*/',      '', $html);

// Paksa semua tabel dari AI menjadi tabel layout transparan
$html = preg_replace('/<table\b[^>]*>/i', '<table class="layout-tabel" style="border: none !important; border-style: none !important; outline: none !important;">', $html);
$html = preg_replace('/<tr\b[^>]*>/i', '<tr style="border: none !important;">', $html);
$html = preg_replace('/<td\b[^>]*>/i', '<td style="border: none !important; border-style: none !important; outline: none !important;">', $html);

// Hapus bagian "Kepada Yth" jika AI tetap membuatnya (sudah ada di template)
$html = preg_replace('/<p[^>]*>\s*Kepada\s+Yth[^<]*(<br\s*\/?>\s*[^<]*)*<\/p>/i', '', $html);
$html = preg_replace('/<p[^>]*>\s*Yth\.?[^<]*(<br\s*\/?>\s*[^<]*)*<\/p>/i', '', $html);
$html = preg_replace('/<p[^>]*>\s*(Kepada|Yth)[^<]*/i', '', $html);
// Hapus "Dengan hormat" juga karena template sudah menampilkannya
$html = preg_replace('/<p[^>]*>\s*Dengan\s+hormat[^<]*<\/p>/i', '', $html);
// Hapus salam pembuka double jika lolos
$html = preg_replace('/(<p[^>]*>\s*Dengan\s+hormat[^<]*<\/p>\s*){2,}/i', '', $html);

// Filter darurat untuk membersihkan blok tanda tangan (Hormat kami...) jika AI membandel
$html = preg_replace('/(<p[^>]*>\s*Hormat\s+kami,?\s*<\/p>[\s\S]*)$/i', '', $html);
$html = preg_replace('/(Hormat\s+kami,?<br[^>]*>[\s\S]*)$/i', '', $html);

echo json_encode([
    'ok'          => true, 
    'html'        => trim($html),
    'perihal'     => $parsed['perihal'] ?? '',
    'ai_reply'    => $parsed['ai_reply'] ?? 'Baik, draf telah saya selesaikan sesuai instruksi Anda.',
    'jenis_surat' => $parsed['jenis_surat'] ?? 'Lainnya',
    'penerima'    => $parsed['penerima'] ?? '',
    'prodi'       => $parsed['prodi'] ?? ''
]);
