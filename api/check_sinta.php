<?php
/**
 * API Endpoint: check_sinta.php
 * Cek akreditasi SINTA jurnal secara real-time dari API resmi SINTA Kemdikbud
 * Menerima: GET ?issn=XXXX-XXXX atau ?q=nama+jurnal
 * Mengembalikan: JSON { sinta_rank, journal_name, issn, url }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ── Input sanitization ─────────────────────────────────────────────────────
$issn = preg_replace('/[^0-9X-]/', '', strtoupper(trim($_GET['issn'] ?? '')));
$q    = trim($_GET['q'] ?? '');

if (empty($issn) && empty($q)) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter issn atau q diperlukan.']);
    exit;
}

// Tentukan query yang dipakai
$query = $issn ?: $q;

// ── Fetch dari SINTA API ───────────────────────────────────────────────────
$url = 'https://sinta.kemdikbud.go.id/ajax/journals?q=' . urlencode($query) . '&rows=5&page=1';

$ctx = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'timeout' => 10,
        'header'  => implode("\r\n", [
            'Accept: application/json, text/html, */*',
            'User-Agent: Mozilla/5.0 SIAKAD-Pascasarjana/1.0',
            'Referer: https://sinta.kemdikbud.go.id/journals',
        ]),
        'ignore_errors' => true,
    ],
    'ssl' => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ],
]);

$raw = @file_get_contents($url, false, $ctx);

if ($raw === false) {
    // Fallback: scrape halaman pencarian HTML
    $scrapeUrl = 'https://sinta.kemdikbud.go.id/journals?q=' . urlencode($query) . '&lang=id';
    $raw = @file_get_contents($scrapeUrl, false, $ctx);

    if ($raw === false) {
        echo json_encode(['sinta_rank' => null, 'error' => 'Tidak dapat terhubung ke server SINTA.']);
        exit;
    }

    // Parse HTML hasil pencarian
    $result = parseSintaHtml($raw, $query);
    echo json_encode($result);
    exit;
}

// ── Parse JSON response dari SINTA AJAX ───────────────────────────────────
$data = json_decode($raw, true);

if (!empty($data) && isset($data['data']) && count($data['data']) > 0) {
    $item = bestMatch($data['data'], $query, $issn);
    if ($item) {
        $sintaRank = getSintaRank($item);
        echo json_encode([
            'sinta_rank'   => $sintaRank,
            'journal_name' => $item['title'] ?? ($item['name'] ?? ''),
            'issn'         => $item['issn'] ?? ($item['e_issn'] ?? ''),
            'url'          => 'https://sinta.kemdikbud.go.id/journals/detail?id=' . ($item['id'] ?? ''),
            'source'       => 'sinta_api',
        ]);
        exit;
    }
}

// Fallback: data belum tersedia via JSON, coba scrape HTML
$scrapeUrl = 'https://sinta.kemdikbud.go.id/journals?q=' . urlencode($query) . '&lang=id';
$raw2 = @file_get_contents($scrapeUrl, false, $ctx);
if ($raw2 !== false) {
    $result = parseSintaHtml($raw2, $query);
    echo json_encode($result);
    exit;
}

echo json_encode(['sinta_rank' => null, 'journal_name' => null, 'issn' => $issn, 'source' => 'not_found']);
exit;

// ── Helper functions ───────────────────────────────────────────────────────

/**
 * Pilih item terbaik dari daftar berdasarkan ISSN exact match atau nama terdekat
 */
function bestMatch(array $items, string $query, string $issn): ?array {
    $queryLow = strtolower($query);
    // Prioritas 1: ISSN exact match
    if ($issn) {
        foreach ($items as $item) {
            $i1 = strtoupper(trim($item['issn'] ?? ''));
            $i2 = strtoupper(trim($item['e_issn'] ?? ''));
            if ($i1 === $issn || $i2 === $issn) {
                return $item;
            }
        }
    }
    // Prioritas 2: Nama jurnal mengandung query
    foreach ($items as $item) {
        $name = strtolower($item['title'] ?? ($item['name'] ?? ''));
        if (str_contains($name, $queryLow) || str_contains($queryLow, $name)) {
            return $item;
        }
    }
    // Fallback: ambil item pertama
    return $items[0] ?? null;
}

/**
 * Ekstrak ranking SINTA dari data item JSON
 */
function getSintaRank(array $item): ?string {
    // Coba field 'grade', 'sinta_score', 'rank_sinta', 'sinta_grade', dll.
    $rank = $item['grade'] ?? ($item['sinta_grade'] ?? ($item['rank'] ?? ($item['sinta_rank'] ?? null)));
    if ($rank === null) return null;

    $r = strtoupper(trim((string)$rank));
    // Normalize: "S1","S2","S3","S4","S5","S6","SINTA 1",dst.
    if (preg_match('/(\d)/', $r, $m)) {
        return 'SINTA ' . $m[1];
    }
    return null;
}

/**
 * Scrape HTML halaman jurnal SINTA untuk ambil peringkat
 */
function parseSintaHtml(string $html, string $query): array {
    $queryLow = strtolower($query);

    // Cari pola: "SINTA 1", "SINTA 2", dst. di dalam blok card jurnal
    // Format khas: <span class="...">S1</span> atau "SINTA 1" di near judul jurnal
    if (preg_match_all('/<[^>]+class="[^"]*journal[^>]*>(.+?)<\/[^>]+>/si', $html, $cards)) {
        foreach ($cards[1] as $card) {
            $text = strtolower(strip_tags($card));
            if (str_contains($text, $queryLow)) {
                if (preg_match('/\bS([1-6])\b/i', $card, $m)) {
                    return [
                        'sinta_rank'   => 'SINTA ' . $m[1],
                        'journal_name' => null,
                        'source'       => 'html_scrape',
                    ];
                }
            }
        }
    }

    // Fallback: cari pola SINTA di seluruh halaman di dekat nama jurnal
    if (preg_match_all('/\bS([1-6])\b/i', $html, $m)) {
        // Ambil yang pertama jika ada
        return [
            'sinta_rank'   => 'SINTA ' . $m[1][0],
            'journal_name' => null,
            'source'       => 'html_scrape_fallback',
        ];
    }

    return ['sinta_rank' => null, 'journal_name' => null, 'source' => 'html_not_found'];
}
