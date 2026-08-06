<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'pascasarjana_unp');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('BASE_URL', 'http://localhost/Pascasarjana');
define('BASE_PATH', dirname(__DIR__));

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            die(json_encode(['error' => 'Koneksi database gagal.']));
        }
    }
    return $pdo;
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime'=>86400,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
date_default_timezone_set('Asia/Jakarta');
