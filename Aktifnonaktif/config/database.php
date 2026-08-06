<?php
/**
 * Database Connection (PDO Singleton)
 * Universitas Nusa Putra - Sistem Pengunduran Diri Mahasiswa
 */

class Database
{
    private static ?PDO $instance = null;

    // --- Database credentials ---
    private static string $host    = 'localhost';
    private static string $dbname  = 'pengunduran_diri_mahasiswa';
    private static string $user    = 'root';
    private static string $pass    = '';
    private static string $charset = 'utf8mb4';

    /** Prevent direct instantiation */
    private function __construct() {}
    private function __clone() {}

    /**
     * Get PDO singleton instance
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$host,
                self::$dbname,
                self::$charset
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, self::$user, self::$pass, $options);
            } catch (PDOException $e) {
                // Log error and show generic message
                error_log('[DB ERROR] ' . $e->getMessage());
                die(json_encode([
                    'error' => true,
                    'message' => 'Koneksi database gagal. Silakan hubungi administrator.'
                ]));
            }
        }

        return self::$instance;
    }

    /**
     * Shortcut: prepare and execute a statement
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Fetch all rows
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Get last insert ID
     */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollback(): void
    {
        self::getInstance()->rollBack();
    }
}
