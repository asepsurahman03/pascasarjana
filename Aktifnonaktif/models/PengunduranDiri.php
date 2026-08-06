<?php
/**
 * PengunduranDiri Model
 */
class PengunduranDiri
{
    public static function create(array $data): int
    {
        Database::query(
            "INSERT INTO pengunduran_diri
             (nomor_surat, mahasiswa_id, tanggal_surat, nama_pemohon, nim, angkatan, program_studi,
              status_mahasiswa, bersedia_mundur, alasan, status, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['nomor_surat'] ?? null,
                $data['mahasiswa_id'],
                $data['tanggal_surat'],
                $data['nama_pemohon'],
                $data['nim'],
                $data['angkatan'],
                $data['program_studi'],
                $data['status_mahasiswa'],
                $data['bersedia_mundur'],
                $data['alasan'],
                $data['status'] ?? 'Pending',
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['nomor_surat','tanggal_surat','nama_pemohon','nim','angkatan','program_studi',
                    'status_mahasiswa','bersedia_mundur','alasan','status','catatan_admin',
                    'approved_by','approved_at'];
        $sets   = [];
        $params = [];
        foreach ($data as $k => $v) {
            if (in_array($k, $allowed)) {
                $sets[]   = "$k = ?";
                $params[] = $v;
            }
        }
        if (empty($sets)) return;
        $params[] = $id;
        Database::query("UPDATE pengunduran_diri SET " . implode(', ', $sets) . " WHERE id = ?", $params);
    }

    public static function findById(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT pd.*, m.foto, m.no_hp, m.alamat,
                    u.nama as approved_by_nama
             FROM pengunduran_diri pd
             LEFT JOIN mahasiswa m ON m.id = pd.mahasiswa_id
             LEFT JOIN users u ON u.id = pd.approved_by
             WHERE pd.id = ?",
            [$id]
        );
    }

    public static function findByMahasiswaId(int $mahasiswaId): array
    {
        return Database::fetchAll(
            "SELECT * FROM pengunduran_diri WHERE mahasiswa_id = ? ORDER BY created_at DESC",
            [$mahasiswaId]
        );
    }

    public static function all(array $filters = [], int $limit = 10, int $offset = 0): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = "(pd.nim LIKE ? OR pd.nama_pemohon LIKE ? OR pd.nomor_surat LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[]  = "pd.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['program_studi'])) {
            $where[]  = "pd.program_studi = ?";
            $params[] = $filters['program_studi'];
        }
        if (!empty($filters['angkatan'])) {
            $where[]  = "pd.angkatan = ?";
            $params[] = $filters['angkatan'];
        }
        if (!empty($filters['tanggal_dari'])) {
            $where[]  = "pd.tanggal_surat >= ?";
            $params[] = $filters['tanggal_dari'];
        }
        if (!empty($filters['tanggal_sampai'])) {
            $where[]  = "pd.tanggal_surat <= ?";
            $params[] = $filters['tanggal_sampai'];
        }

        $sql = "SELECT pd.*, m.foto
                FROM pengunduran_diri pd
                LEFT JOIN mahasiswa m ON m.id = pd.mahasiswa_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY pd.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return Database::fetchAll($sql, $params);
    }

    public static function count(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[]  = "(nim LIKE ? OR nama_pemohon LIKE ? OR nomor_surat LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[]  = "status = ?";
            $params[] = $filters['status'];
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
            "SELECT COUNT(*) as total FROM pengunduran_diri WHERE " . implode(' AND ', $where),
            $params
        );
        return (int)($result['total'] ?? 0);
    }

    public static function statistics(?string $prodi = null): array
    {
        $where = $prodi ? "WHERE program_studi = ?" : "";
        $params = $prodi ? [$prodi] : [];
        
        $total    = Database::fetchOne("SELECT COUNT(*) as c FROM pengunduran_diri $where", $params)['c'] ?? 0;
        
        $whereToday = $prodi ? "WHERE DATE(created_at) = CURDATE() AND program_studi = ?" : "WHERE DATE(created_at) = CURDATE()";
        $today    = Database::fetchOne("SELECT COUNT(*) as c FROM pengunduran_diri $whereToday", $params)['c'] ?? 0;
        
        $whereApp = $prodi ? "WHERE status = 'Approved' AND program_studi = ?" : "WHERE status = 'Approved'";
        $approved = Database::fetchOne("SELECT COUNT(*) as c FROM pengunduran_diri $whereApp", $params)['c'] ?? 0;
        
        $whereRej = $prodi ? "WHERE status = 'Rejected' AND program_studi = ?" : "WHERE status = 'Rejected'";
        $rejected = Database::fetchOne("SELECT COUNT(*) as c FROM pengunduran_diri $whereRej", $params)['c'] ?? 0;
        
        $wherePen = $prodi ? "WHERE status = 'Pending' AND program_studi = ?" : "WHERE status = 'Pending'";
        $pending  = Database::fetchOne("SELECT COUNT(*) as c FROM pengunduran_diri $wherePen", $params)['c'] ?? 0;

        return compact('total', 'today', 'approved', 'rejected', 'pending');
    }

    public static function monthlyChart(?string $prodi = null): array
    {
        $where = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
        $params = [];
        if ($prodi) {
            $where .= " AND program_studi = ?";
            $params[] = $prodi;
        }

        $rows = Database::fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total
             FROM pengunduran_diri
             $where
             GROUP BY month ORDER BY month ASC",
            $params
        );
        return $rows;
    }

    public static function prodiChart(?string $prodi = null): array
    {
        $where = $prodi ? "WHERE program_studi = ?" : "";
        $params = $prodi ? [$prodi] : [];
        return Database::fetchAll(
            "SELECT program_studi, COUNT(*) as total FROM pengunduran_diri $where GROUP BY program_studi",
            $params
        );
    }

    public static function delete(int $id): void
    {
        Database::query("DELETE FROM pengunduran_diri WHERE id = ?", [$id]);
    }

    public static function updateNomor(int $id, string $nomor): void
    {
        Database::query("UPDATE pengunduran_diri SET nomor_surat = ? WHERE id = ?", [$nomor, $id]);
    }
}
