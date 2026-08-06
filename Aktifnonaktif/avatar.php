<?php
/**
 * Avatar Proxy
 * Fetches Google profile picture server-side to bypass CORS/referrer restrictions
 */

$url = $_GET['url'] ?? '';

// Validate: only allow Google user content URLs
if (empty($url) || !preg_match('#^https://lh[0-9]+\.googleusercontent\.com/#', $url)) {
    // Return a 1x1 transparent PNG
    header('Content-Type: image/png');
    echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    exit;
}

// Cache for 24 hours
$cacheDir  = __DIR__ . '/storage/avatar_cache/';
$cacheKey  = md5($url);
$cachePath = $cacheDir . $cacheKey . '.img';

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Serve from cache if fresh (< 24h)
if (file_exists($cachePath) && (time() - filemtime($cachePath)) < 86400) {
    $mime = mime_content_type($cachePath) ?: 'image/jpeg';
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=86400');
    readfile($cachePath);
    exit;
}

// Fetch from Google
$ctx = stream_context_create([
    'http' => [
        'method'     => 'GET',
        'timeout'    => 5,
        'user_agent' => 'Mozilla/5.0 (compatible; AvatarProxy/1.0)',
        'follow_location' => true,
    ],
    'ssl' => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ],
]);

$data = @file_get_contents($url, false, $ctx);

if ($data === false || strlen($data) < 100) {
    // Fallback: transparent PNG
    header('Content-Type: image/png');
    echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    exit;
}

// Save to cache
file_put_contents($cachePath, $data);

// Determine MIME
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->buffer($data) ?: 'image/jpeg';

header('Content-Type: ' . $mime);
header('Cache-Control: public, max-age=86400');
echo $data;
exit;
