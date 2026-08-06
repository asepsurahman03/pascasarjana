<?php
/**
 * Mahasiswa Model
 */
class Mahasiswa
{
    public static function findByNimAndTanggalLahir(string $nim, string $tanggalLahir): ?array
    {
        return Database::fetchOne(
            "SELECT m.*, u.email, u.password, u.role, u.is_active
             FROM mahasiswa m
             LEFT JOIN users u ON u.id = m.user_id
             WHERE m.nim = ? AND m.tanggal_lahir = ? AND m.is_active = 1",
            [$nim, $tanggalLahir]
        );
    }

    public static function findByUserId(int $userId): ?array
    {
        return Database::fetchOne("SELECT * FROM mahasiswa WHERE user_id = ?", [$userId]);
    }

    public static function findById(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT m.*, u.email FROM mahasiswa m LEFT JOIN users u ON u.id = m.user_id WHERE m.id = ?",
            [$id]
        );
    }

    public static function findByNim(string $nim): ?array
    {
        return Database::fetchOne("SELECT * FROM mahasiswa WHERE nim = ?", [$nim]);
    }

    public static function create(array $data): int
    {
        Database::query(
            "INSERT INTO mahasiswa (user_id, nim, nama, email, tanggal_lahir, angkatan, program_studi, status_beasiswa, no_hp, alamat)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['user_id'] ?? null,
                $data['nim'],
                $data['nama'],
                $data['email'] ?? null,
                $data['tanggal_lahir'],
                $data['angkatan'],
                $data['program_studi'],
                $data['status_beasiswa'] ?? 'Non Beasiswa',
                $data['no_hp'] ?? null,
                $data['alamat'] ?? null,
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sets   = [];
        $params = [];
        $allowed = ['nim','nama','email','tanggal_lahir','angkatan','program_studi','status_beasiswa','no_hp','alamat','foto','is_active'];
        foreach ($data as $k => $v) {
            if (in_array($k, $allowed)) {
                $sets[]   = "$k = ?";
                $params[] = $v;
            }
        }
        if (empty($sets)) return;
        $params[] = $id;
        Database::query("UPDATE mahasiswa SET " . implode(', ', $sets) . " WHERE id = ?", $params);
    }

    public static function all(array $filters = [], int $limit = 10, int $offset = 0): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = "(m.nim LIKE ? OR m.nama LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['program_studi'])) {
            $where[]  = "m.program_studi = ?";
            $params[] = $filters['program_studi'];
        }
        if (!empty($filters['angkatan'])) {
            $where[]  = "m.angkatan = ?";
            $params[] = $filters['angkatan'];
        }

        $sql = "SELECT m.*, u.email FROM mahasiswa m LEFT JOIN users u ON u.id = m.user_id
                WHERE " . implode(' AND ', $where) . " ORDER BY m.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return Database::fetchAll($sql, $params);
    }

    public static function count(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = "(nim LIKE ? OR nama LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['program_studi'])) {
            $where[]  = "program_studi = ?";
            $params[] = $filters['program_studi'];
        }
        if (!empty($filters['angkatan'])) {
            $where[]  = "angkatan = ?";
            $params[] = $filters['angkatan'];
        }

        $result = Database::fetchOne(
            "SELECT COUNT(*) as total FROM mahasiswa WHERE " . implode(' AND ', $where),
            $params
        );
        return (int)($result['total'] ?? 0);
    }

    public static function delete(int $id): void
    {
        Database::query("DELETE FROM mahasiswa WHERE id = ?", [$id]);
    }
}
