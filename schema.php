<?php
require 'includes/functions.php';
try {
    $tables = dbQuery('SHOW TABLES');
    foreach ($tables as $t) {
        $table = array_values($t)[0];
        echo "Table: $table\n";
        $schema = dbQuery("SHOW CREATE TABLE $table");
        print_r($schema[0]);
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
