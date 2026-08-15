<?php
/**
 * API Endpoint: check_sinta.php
 * Cek akreditasi SINTA jurnal:
 * 1. Cari di database lokal sinta_database.json (by ISSN & by name)
 * 2. Jika tidak ditemukan, coba fetch langsung ke sinta.kemdikbud.go.id
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ── Input ──────────────────────────────────────────────────────────────────
$issn = trim($_GET['issn'] ?? '');
$issn = strtoupper(preg_replace('/[^0-9X]/', '', $issn));
if (strlen($issn) === 8) {
    $issn = substr($issn, 0, 4) . '-' . substr($issn, 4);
}
$q = trim(strtolower($_GET['q'] ?? ''));

if (empty($issn) && empty($q)) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter issn atau q diperlukan.']);
    exit;
}

// ── Muat database lokal ────────────────────────────────────────────────────
$dbPath = __DIR__ . '/sinta_database.json';
$db = [];
if (file_exists($dbPath)) {
    $db = json_decode(file_get_contents($dbPath), true) ?? [];
}

$byIssn = $db['by_issn'] ?? [];
$byName = $db['by_name'] ?? [];

// ── 1. Cek by ISSN (paling akurat) ────────────────────────────────────────
if ($issn) {
    // Coba dengan dan tanpa dash
    $issnNoDash = str_replace('-', '', $issn);
    if (isset($byIssn[$issn])) {
        $item = $byIssn[$issn];
        echo json_encode([
            'sinta_rank'   => $item['rank'],
            'journal_name' => $item['name'],
            'issn'         => $issn,
            'source'       => 'local_db_issn',
        ]);
        exit;
    }
    // Coba semua key tanpa dash
    foreach ($byIssn as $dbIssn => $item) {
        if (str_replace('-', '', $dbIssn) === $issnNoDash) {
            echo json_encode([
                'sinta_rank'   => $item['rank'],
                'journal_name' => $item['name'],
                'issn'         => $issn,
                'source'       => 'local_db_issn',
            ]);
            exit;
        }
    }
}

// ── 2. Cek by nama jurnal ──────────────────────────────────────────────────
if ($q) {
    // Exact key match
    if (isset($byName[$q])) {
        echo json_encode([
            'sinta_rank'   => $byName[$q],
            'journal_name' => $q,
            'source'       => 'local_db_name_exact',
        ]);
        exit;
    }
    // Substring match: cek apakah query mengandung nama di db atau sebaliknya
    foreach ($byName as $dbName => $rank) {
        if (str_contains($q, $dbName) || str_contains($dbName, $q)) {
            echo json_encode([
                'sinta_rank'   => $rank,
                'journal_name' => $dbName,
                'source'       => 'local_db_name_partial',
            ]);
            exit;
        }
    }
    // Cek juga di ISSN entries by name
    foreach ($byIssn as $dbIssn => $item) {
        $dbNameLow = strtolower($item['name'] ?? '');
        if ($dbNameLow && (str_contains($q, $dbNameLow) || str_contains($dbNameLow, $q))) {
            echo json_encode([
                'sinta_rank'   => $item['rank'],
                'journal_name' => $item['name'],
                'issn'         => $dbIssn,
                'source'       => 'local_db_name_in_issn',
            ]);
            exit;
        }
    }
}

// ── 3. Fallback: Coba fetch ke sinta.kemdikbud.go.id (jika ada koneksi) ──
if (function_exists('curl_init')) {
    $query = $issn ?: $q;
    $url   = 'https://sinta.kemdikbud.go.id/journals?q=' . urlencode($query) . '&limit=10&page=1';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 SIAKAD-Pascasarjana/2.0',
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json, text/html, */*',
            'Referer: https://sinta.kemdikbud.go.id/journals',
        ],
    ]);
    $raw  = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if (!$err && $raw) {
        // Coba parse JSON
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $items = $json['data'] ?? $json['journals'] ?? (is_array($json) ? $json : []);
            foreach ((array)$items as $item) {
                if (!is_array($item)) continue;
                $rank = extractRank($item);
                if ($rank) {
                    echo json_encode([
                        'sinta_rank'   => $rank,
                        'journal_name' => $item['title'] ?? '',
                        'source'       => 'sinta_live_json',
                    ]);
                    exit;
                }
            }
        }

        // Parse HTML
        if (preg_match('/SINTA[\s-]*([1-6])/i', $raw, $m)) {
            echo json_encode([
                'sinta_rank' => 'SINTA ' . $m[1],
                'source'     => 'sinta_live_html',
            ]);
            exit;
        }
    }
}

// ── 4. Tidak ditemukan ──────────────────────────────────────────────────────
echo json_encode([
    'sinta_rank'   => null,
    'journal_name' => null,
    'issn'         => $issn,
    'source'       => 'not_found',
    'message'      => 'Jurnal tidak ditemukan. Silakan cek manual di sinta.kemdikbud.go.id',
]);

// ── Helper ──────────────────────────────────────────────────────────────────
function extractRank(array $item): ?string {
    foreach (['grade', 'sinta_grade', 'rank', 'sinta_rank', 'level', 'accreditation'] as $key) {
        $v = strtoupper(trim((string)($item[$key] ?? '')));
        if (!$v) continue;
        if (preg_match('/^S([1-6])$/', $v, $m)) return "SINTA {$m[1]}";
        if (preg_match('/SINTA[\s-]*([1-6])/i', $v, $m)) return "SINTA {$m[1]}";
        if (preg_match('/^([1-6])$/', $v, $m)) return "SINTA {$m[1]}";
    }
    return null;
}
