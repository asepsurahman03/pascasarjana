<?php
/**
 * Google OAuth 2.0 Helper
 * Pure PHP — no Composer required.
 */
class GoogleOAuth
{
    private const AUTH_URL  = "https://accounts.google.com/o/oauth2/v2/auth";
    private const TOKEN_URL = "https://oauth2.googleapis.com/token";
    private const INFO_URL  = "https://www.googleapis.com/oauth2/v3/userinfo";

    public static function getAuthUrl(): string
    {
        $state = bin2hex(random_bytes(16));
        $_SESSION["google_oauth_state"] = $state;

        $params = http_build_query([
            "client_id"     => GOOGLE_CLIENT_ID,
            "redirect_uri"  => GOOGLE_REDIRECT_URI,
            "response_type" => "code",
            "scope"         => "openid email profile",
            "access_type"   => "online",
            "prompt"        => "select_account",
            "state"         => $state,
        ]);

        return self::AUTH_URL . "?" . $params;
    }

    public static function getTokenFromCode(string $code): ?array
    {
        $payload = http_build_query([
            "code"          => $code,
            "client_id"     => GOOGLE_CLIENT_ID,
            "client_secret" => GOOGLE_CLIENT_SECRET,
            "redirect_uri"  => GOOGLE_REDIRECT_URI,
            "grant_type"    => "authorization_code",
        ]);

        $ch = curl_init(self::TOKEN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local testing

        $raw = curl_exec($ch);
        curl_close($ch);

        if (!$raw) return null;

        $data = json_decode($raw, true);
        return isset($data["access_token"]) ? $data : null;
    }

    public static function getUserInfo(string $accessToken): ?array
    {
        $ch = curl_init(self::INFO_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $raw = curl_exec($ch);
        curl_close($ch);

        if (!$raw) return null;

        $data = json_decode($raw, true);
        return isset($data["sub"]) ? $data : null;
    }
}
