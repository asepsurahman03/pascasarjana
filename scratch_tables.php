<?php
require 'includes/functions.php';
try {
    dbExecute("CREATE TABLE IF NOT EXISTS pendaftaran_sidang ( 
        id int(11) NOT NULL AUTO_INCREMENT, 
        mahasiswa_id int(11) NOT NULL, 
        jenis_sidang varchar(100) NOT NULL, 
        berkas_ok int(11) DEFAULT 0, 
        berkas_total int(11) DEFAULT 0, 
        status varchar(50) DEFAULT 'Menunggu Review', 
        urgent tinyint(1) DEFAULT 0, 
        created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, 
        updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
        PRIMARY KEY (id), 
        KEY mahasiswa_id (mahasiswa_id), 
        CONSTRAINT pendaftaran_sidang_ibfk_1 FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa (id) ON DELETE CASCADE 
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table pendaftaran_sidang created successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
