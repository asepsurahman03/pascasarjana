<?php
/**
 * Google Drive Helper — Pure PHP, tanpa Composer/SDK
 * Menggunakan Service Account + JWT RS256 untuk autentikasi.
 */
class GoogleDriveHelper
{
    private string $accessToken = '';
    private int $tokenExpiry = 0;

    public function __construct(private array $serviceAccount) {}

    /** Buat instance dari JSON string (dari settings) */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (!$data || ($data['type'] ?? '') !== 'service_account') {
            throw new RuntimeException('Service account JSON tidak valid.');
        }
        return new self($data);
    }

    /** Base64Url encode (RFC 4648) */
    private static function b64u(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /** Generate JWT dan tukar dengan Access Token */
    public function getAccessToken(): string
    {
        if ($this->accessToken && time() < $this->tokenExpiry - 30) {
            return $this->accessToken;
        }

        $now  = time();
        $header  = self::b64u(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = self::b64u(json_encode([
            'iss'   => $this->serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $signingInput = "$header.$payload";
        $privateKey   = $this->serviceAccount['private_key'];
        $pkeyId       = openssl_pkey_get_private($privateKey);
        if (!$pkeyId) {
            throw new RuntimeException('Gagal memuat private key Service Account. Pastikan JSON key valid.');
        }
        openssl_sign($signingInput, $signature, $pkeyId, OPENSSL_ALGO_SHA256);
        $jwt = $signingInput . '.' . self::b64u($signature);

        // Tukar JWT → Access Token
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($resp, true);
        if ($code !== 200 || empty($data['access_token'])) {
            $msg = $data['error_description'] ?? $data['error'] ?? "HTTP $code";
            throw new RuntimeException("Gagal mendapatkan Access Token Google: $msg");
        }

        $this->accessToken  = $data['access_token'];
        $this->tokenExpiry  = time() + (int)($data['expires_in'] ?? 3600);
        return $this->accessToken;
    }

    /**
     * Upload file HTML ke Google Drive.
     * @param string $folderId  ID folder Drive target
     * @param string $filename  Nama file (dengan .html)
     * @param string $content   Konten HTML
     * @param string|null $existingFileId  Jika ada, update file yang sudah ada
     * @return array ['file_id' => ..., 'url' => ...]
     */
    public function uploadHtml(
        string $folderId,
        string $filename,
        string $content,
        ?string $existingFileId = null
    ): array {
        $token    = $this->getAccessToken();
        $mimeType = 'text/html';

        // Multipart boundary
        $boundary = '---GoogleDriveUpload' . uniqid();
        $metadata = json_encode([
            'name'    => $filename,
            'mimeType' => $mimeType,
            'parents' => $existingFileId ? [] : [$folderId],
        ]);
        $body =
            "--$boundary\r\n" .
            "Content-Type: application/json; charset=UTF-8\r\n\r\n" .
            $metadata . "\r\n" .
            "--$boundary\r\n" .
            "Content-Type: $mimeType\r\n\r\n" .
            $content . "\r\n" .
            "--$boundary--";

        $url = $existingFileId
            ? "https://www.googleapis.com/upload/drive/v3/files/$existingFileId?uploadType=multipart"
            : 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_CUSTOMREQUEST  => $existingFileId ? 'PATCH' : 'POST',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer $token",
                "Content-Type: multipart/related; boundary=$boundary",
                'Content-Length: ' . strlen($body),
            ],
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($resp, true);
        if (($code !== 200 && $code !== 201) || empty($data['id'])) {
            $msg = $data['error']['message'] ?? "HTTP $code";
            throw new RuntimeException("Gagal upload ke Drive: $msg");
        }

        $fileId = $data['id'];

        // Set izin agar bisa dibuka oleh siapapun yang punya link
        $this->setPublicReadPermission($token, $fileId);

        return [
            'file_id' => $fileId,
            'url'     => "https://drive.google.com/file/d/$fileId/view",
        ];
    }

    /** Berikan akses anyone-with-link reader */
    private function setPublicReadPermission(string $token, string $fileId): void
    {
        $ch = curl_init("https://www.googleapis.com/drive/v3/files/$fileId/permissions");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_POSTFIELDS     => json_encode(['type' => 'anyone', 'role' => 'reader']),
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer $token",
                'Content-Type: application/json',
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    /** Test koneksi — coba list file di folder target */
    public function testConnection(string $folderId): array
    {
        $token = $this->getAccessToken();
        $ch = curl_init("https://www.googleapis.com/drive/v3/files?q=" . urlencode("'$folderId' in parents") . "&pageSize=1&fields=files(id,name)");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ["Authorization: Bearer $token"],
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($resp, true);
        if ($code !== 200) {
            $msg = $data['error']['message'] ?? "HTTP $code";
            throw new RuntimeException("Akses folder Drive gagal: $msg. Pastikan folder sudah di-share ke service account email.");
        }
        return ['ok' => true, 'files_found' => count($data['files'] ?? [])];
    }
}
