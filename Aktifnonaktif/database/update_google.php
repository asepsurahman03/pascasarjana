<?php
require_once dirname(__DIR__) . "/config/database.php";

try {
    Database::query("ALTER TABLE users ADD COLUMN IF NOT EXISTS google_id VARCHAR(100) NULL DEFAULT NULL AFTER email");
    Database::query("ALTER TABLE users ADD COLUMN IF NOT EXISTS avatar VARCHAR(500) NULL DEFAULT NULL AFTER google_id");
    Database::query("ALTER TABLE users ADD COLUMN IF NOT EXISTS auth_provider ENUM('local','google') NOT NULL DEFAULT 'local' AFTER avatar");
    echo "Database updated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
