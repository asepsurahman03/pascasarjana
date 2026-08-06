<?php
/**
 * DigitalSignature Model
 */
class DigitalSignature
{
    public static function create(array $data): int
    {
        Database::query(
            "INSERT INTO digital_signature (pengunduran_id, mahasiswa_id, signature_data, signature_path, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['pengunduran_id'],
                $data['mahasiswa_id'],
                $data['signature_data'],
                $data['signature_path'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function findByPengunduranId(int $pengunduranId): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM digital_signature WHERE pengunduran_id = ? ORDER BY id DESC LIMIT 1",
            [$pengunduranId]
        );
    }

    public static function update(int $pengunduranId, array $data): void
    {
        Database::query(
            "UPDATE digital_signature SET signature_data = ?, signature_path = ? WHERE pengunduran_id = ?",
            [$data['signature_data'], $data['signature_path'] ?? null, $pengunduranId]
        );
    }

    public static function delete(int $pengunduranId): void
    {
        Database::query("DELETE FROM digital_signature WHERE pengunduran_id = ?", [$pengunduranId]);
    }
}

/**
 * ActivityLog Model
 */
class ActivityLog
{
    public static function recent(int $limit = 20): array
    {
        return Database::fetchAll(
            "SELECT al.*, u.nama as user_nama
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public static function all(int $limit = 50, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT al.*, u.nama as user_nama
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }
}

/**
 * Settings Model
 */
class Settings
{
    private static array $cache = [];

    public static function get(string $key, string $default = ''): string
    {
        if (isset(self::$cache[$key])) return self::$cache[$key];

        $result = Database::fetchOne("SELECT value FROM settings WHERE key_name = ?", [$key]);
        self::$cache[$key] = $result ? $result['value'] : $default;
        return self::$cache[$key];
    }

    public static function set(string $key, string $value): void
    {
        Database::query(
            "INSERT INTO settings (key_name, value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE value = ?",
            [$key, $value, $value]
        );
        self::$cache[$key] = $value;
    }

    public static function all(): array
    {
        return Database::fetchAll("SELECT * FROM settings ORDER BY key_name ASC");
    }

    public static function updateMany(array $data): void
    {
        foreach ($data as $key => $value) {
            self::set($key, $value);
        }
    }
}
