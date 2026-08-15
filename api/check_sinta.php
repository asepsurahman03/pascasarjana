<?php
/**
 * API Endpoint: check_sinta.php
 * Cek akreditasi SINTA jurnal secara real-time dari API resmi SINTA Kemdikbud
 * Menerima: GET ?issn=XXXX-XXXX atau ?q=nama+jurnal
 * Mengembalikan: JSON { sinta_rank, journal_name, issn, url, source }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ── Input sanitization ─────────────────────────────────────────────────────
$issn = trim($_GET['issn'] ?? '');
// Normalize ISSN: hapus karakter non-valid, tambahkan dash jika belum ada
$issn = strtoupper(preg_replace('/[^0-9X]/', '', $issn));
if (strlen($issn) === 8) {
    $issn = substr($issn, 0, 4) . '-' . substr($issn, 4);
}
$q = trim($_GET['q'] ?? '');

if (empty($issn) && empty($q)) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter issn atau q diperlukan.']);
    exit;
}

// ── cURL helper ────────────────────────────────────────────────────────────
function sintaFetch(string $url): ?string {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 SIAKAD-Pascasarjana/1.0',
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json, text/html, */*',
            'Accept-Language: id,en;q=0.9',
            'Referer: https://sinta.kemdikbud.go.id/journals',
        ],
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return ($result === false) ? null : $result;
}

// ── Coba SINTA JSON API ────────────────────────────────────────────────────
$queries = array_filter([$issn, $q]);

foreach ($queries as $query) {
    // Endpoint AJAX yang dipakai oleh website SINTA
    $url = 'https://sinta.kemdikbud.go.id/journals?q=' . urlencode($query) . '&limit=10&page=1';
    $raw = sintaFetch($url);

    if ($raw === null) continue;

    // Coba parse JSON dulu (beberapa endpoint SINTA memberikan JSON)
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $items = $json['data'] ?? $json['journals'] ?? $json;
        if (is_array($items) && count($items) > 0) {
            $item = bestMatch($items, $query, $issn);
            if ($item) {
                $rank = extractSintaRank($item);
                if ($rank) {
                    echo json_encode([
                        'sinta_rank'   => $rank,
                        'journal_name' => $item['title'] ?? ($item['name'] ?? ''),
                        'issn'         => $item['issn'] ?? ($item['e_issn'] ?? $issn),
                        'url'          => buildSintaUrl($item),
                        'source'       => 'sinta_json',
                    ]);
                    exit;
                }
            }
        }
    }

    // Fallback: parse HTML
    $result = parseSintaHtml($raw, $query, $issn);
    if ($result['sinta_rank'] !== null) {
        echo json_encode($result);
        exit;
    }
}

// Tidak ditemukan
echo json_encode([
    'sinta_rank'   => null,
    'journal_name' => null,
    'issn'         => $issn,
    'source'       => 'not_found',
    'message'      => 'Jurnal tidak ditemukan di database SINTA. Silakan cek manual.',
]);
exit;

// ── Helper: Best match ──────────────────────────────────────────────────────
function bestMatch(array $items, string $query, string $issn): ?array {
    $queryLow = strtolower($query);
    $issnClean = strtoupper(str_replace('-', '', $issn));

    foreach ($items as $item) {
        // Prioritas 1: ISSN exact match
        $i1 = strtoupper(str_replace('-', '', $item['issn'] ?? ''));
        $i2 = strtoupper(str_replace('-', '', $item['e_issn'] ?? ''));
        if ($issnClean && ($i1 === $issnClean || $i2 === $issnClean)) {
            return $item;
        }
    }
    foreach ($items as $item) {
        // Prioritas 2: Nama jurnal exact atau substring match
        $name = strtolower($item['title'] ?? ($item['name'] ?? ''));
        if ($name && (str_contains($name, $queryLow) || str_contains($queryLow, $name))) {
            return $item;
        }
    }
    return $items[0] ?? null;
}

// ── Helper: Ekstrak SINTA rank dari item JSON ───────────────────────────────
function extractSintaRank(array $item): ?string {
    // Berbagai nama field yang mungkin digunakan SINTA
    $candidates = [
        $item['grade']       ?? null,
        $item['sinta_grade'] ?? null,
        $item['rank']        ?? null,
        $item['sinta_rank']  ?? null,
        $item['level']       ?? null,
        $item['accreditation'] ?? null,
    ];

    foreach ($candidates as $val) {
        if ($val === null || $val === '') continue;
        $v = strtoupper(trim((string)$val));
        // Format: "S1","S2","S3","S4","S5","S6"
        if (preg_match('/^S([1-6])$/i', $v, $m)) return "SINTA {$m[1]}";
        // Format: "SINTA 1","SINTA-2","SINTA4"
        if (preg_match('/SINTA[\s-]*([1-6])/i', $v, $m)) return "SINTA {$m[1]}";
        // Format: "1","2","3","4","5","6" (angka murni peringkat)
        if (preg_match('/^([1-6])$/', $v, $m)) return "SINTA {$m[1]}";
    }
    return null;
}

// ── Helper: Build URL ke detail jurnal SINTA ───────────────────────────────
function buildSintaUrl(array $item): string {
    if (!empty($item['id'])) {
        return 'https://sinta.kemdikbud.go.id/journals/detail?id=' . $item['id'];
    }
    return 'https://sinta.kemdikbud.go.id/journals';
}

// ── Helper: Scrape HTML halaman SINTA ──────────────────────────────────────
function parseSintaHtml(string $html, string $query, string $issn): array {
    $queryLow = strtolower($query);
    $issnClean = strtoupper(str_replace('-', '', $issn));

    // Cari semua blok card jurnal
    // SINTA HTML biasanya: <div class="card-journal"> ... <div class="profile-grade">S1</div>
    if (preg_match_all('/<(?:div|article)[^>]+class="[^"]*journal[^"]*"[^>]*>(.*?)<\/(?:div|article)>/si', $html, $cards)) {
        foreach ($cards[1] as $card) {
            $text = strtolower(strip_tags($card));

            // Cek apakah ISSN atau nama jurnal cocok dengan card ini
            $cardIssn = strtoupper(preg_replace('/[^0-9X]/', '', $card));
            $matches = ($issnClean && str_contains($cardIssn, $issnClean)) ||
                       (!empty($queryLow) && str_contains($text, $queryLow));

            if ($matches) {
                // Cari pattern S1-S6 di dalam card
                if (preg_match('/\bS([1-6])\b/i', $card, $m)) {
                    return [
                        'sinta_rank'   => 'SINTA ' . $m[1],
                        'journal_name' => extractJournalNameFromCard($card),
                        'issn'         => $issn,
                        'source'       => 'html_scrape',
                    ];
                }
                if (preg_match('/SINTA[\s-]*([1-6])/i', $card, $m)) {
                    return [
                        'sinta_rank'   => 'SINTA ' . $m[1],
                        'journal_name' => extractJournalNameFromCard($card),
                        'issn'         => $issn,
                        'source'       => 'html_scrape',
                    ];
                }
            }
        }
    }

    // Fallback: cari SINTA rank di seluruh halaman (hanya jika query spesifik/ISSN)
    if ($issnClean && preg_match('/SINTA[\s-]*([1-6])/i', $html, $m)) {
        return [
            'sinta_rank'   => 'SINTA ' . $m[1],
            'journal_name' => null,
            'issn'         => $issn,
            'source'       => 'html_scrape_fallback',
        ];
    }

    return ['sinta_rank' => null, 'journal_name' => null, 'source' => 'html_not_found'];
}

// ── Helper: Ambil nama jurnal dari HTML card ────────────────────────────────
function extractJournalNameFromCard(string $card): ?string {
    if (preg_match('/<(?:h[1-6]|a)[^>]*class="[^"]*title[^"]*"[^>]*>([^<]+)</i', $card, $m)) {
        return trim(strip_tags($m[1]));
    }
    return null;
}
