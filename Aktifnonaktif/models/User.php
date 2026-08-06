<?php
/**
 * User Model
 */
class User
{
    public static function findByEmail(string $email): ?array
    {
        return Database::fetchOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);
    }

    public static function findById(int $id): ?array
    {
        return Database::fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public static function findByGoogleId(string $googleId): ?array
    {
        return Database::fetchOne("SELECT * FROM users WHERE google_id = ? LIMIT 1", [$googleId]);
    }

    public static function create(array $data): int
    {
        Database::query(
            "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)",
            [$data['nama'], $data['email'], password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]), $data['role'] ?? 'mahasiswa']
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sets = [];
        $params = [];
        foreach ($data as $k => $v) {
            if ($k === 'password') {
                $sets[] = "password = ?";
                $params[] = password_hash($v, PASSWORD_BCRYPT, ['cost' => 12]);
            } else {
                $sets[] = "$k = ?";
                $params[] = $v;
            }
        }
        $params[] = $id;
        Database::query("UPDATE users SET " . implode(', ', $sets) . " WHERE id = ?", $params);
    }

    public static function updateLastLogin(int $id): void
    {
        Database::query("UPDATE users SET last_login = NOW() WHERE id = ?", [$id]);
    }

    public static function all(): array
    {
        return Database::fetchAll("SELECT id, nama, email, role, program_studi, avatar, ttd_path, is_active, last_login, created_at FROM users ORDER BY id DESC");
    }

    public static function findByProgramStudiAndRole(string $programStudi, string $role = 'kaprodi'): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM users WHERE program_studi = ? AND role = ? AND is_active = 1 LIMIT 1",
            [$programStudi, $role]
        );
    }

    public static function delete(int $id): void
    {
        Database::query("DELETE FROM users WHERE id = ? AND role != 'admin'", [$id]);
    }
}
