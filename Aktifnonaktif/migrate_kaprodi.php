<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance();
    
    // 1. Alter table
    // Modify ENUM role to include 'kaprodi'
    echo "Modifying 'users' table role ENUM...\n";
    $db->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'mahasiswa', 'kaprodi') NOT NULL DEFAULT 'mahasiswa'");
    
    // Add program_studi column if it doesn't exist
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'program_studi'");
    if ($stmt->rowCount() == 0) {
        echo "Adding 'program_studi' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN program_studi VARCHAR(100) DEFAULT NULL AFTER role");
    } else {
        echo "'program_studi' column already exists.\n";
    }

    // 2. Create Kaprodi accounts
    $kaprodiList = [
        'mif@nusaputra.ac.id' => 'S2 - Magister Informatika',
        'master.management@nusaputra.ac.id' => 'S2 - Magister Manajemen',
        'pedagogy@nusaputra.ac.id' => 'S2 - Magister Pedagogi'
    ];

    foreach ($kaprodiList as $email => $prodi) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $update = $db->prepare("UPDATE users SET role = 'kaprodi', program_studi = ? WHERE id = ?");
            $update->execute([$prodi, $user['id']]);
            echo "Updated existing user $email to kaprodi for $prodi.\n";
        } else {
            $name = 'Kaprodi ' . str_replace('S2 - ', '', $prodi);
            $insert = $db->prepare("INSERT INTO users (nama, email, password, role, program_studi) VALUES (?, ?, ?, 'kaprodi', ?)");
            $insert->execute([$name, $email, password_hash('nusaputra123', PASSWORD_DEFAULT), $prodi]);
            echo "Created new kaprodi user $email for $prodi.\n";
        }
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
