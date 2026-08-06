<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

try {
    // Drop existing table to rebuild with prodi_id
    dbExecute("DROP TABLE IF EXISTS dosen");

    // Create the table
    $sql = "CREATE TABLE IF NOT EXISTS dosen (
        id INT AUTO_INCREMENT PRIMARY KEY,
        prodi_id INT DEFAULT NULL,
        nidn VARCHAR(50) DEFAULT NULL,
        nama VARCHAR(150) NOT NULL,
        kualifikasi VARCHAR(50) DEFAULT NULL,
        email VARCHAR(100) DEFAULT NULL,
        jabatan VARCHAR(100) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    dbExecute($sql);
    echo "Table 'dosen' created successfully with prodi_id.<br>";

    // Import from JSON
    $jsonFile = __DIR__ . '/data_dosen.json';
    if (file_exists($jsonFile)) {
        $rawData = json_decode(file_get_contents($jsonFile), true);
        if (is_array($rawData)) {
            foreach ($rawData as $row) {
                if (isset($row['nama'])) { 
                    $prodi_id = $row['prodi_id'] ?? null;
                    $nidn = $row['nidn'] ?? '';
                    $nama = $row['nama'];
                    $kualifikasi = $row['kualifikasi'] ?? '-';
                    $email = $row['email'] ?? '-';
                    $jabatan = $row['jabatan'] ?? '-';
                    
                    dbExecute("INSERT INTO dosen (prodi_id, nidn, nama, kualifikasi, email, jabatan) VALUES (?, ?, ?, ?, ?, ?)", 
                        [$prodi_id, $nidn, $nama, $kualifikasi, $email, $jabatan]);
                }
            }
            echo "Data imported from JSON successfully.<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
