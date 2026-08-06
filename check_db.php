<?php
$p = new PDO('mysql:host=localhost;dbname=pascasarjana_unp;charset=utf8','root','');
$tables = $p->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "=== SEMUA TABEL ===\n" . implode("\n", $tables) . "\n";

// Cek tabel users jika ada
if (in_array('users', $tables)) {
    $cols = $p->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
    echo "\n=== STRUKTUR TABEL USERS ===\n";
    foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']} | Null:{$c['Null']}\n";
    $sample = $p->query("SELECT * FROM users LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nSample users:\n";
    foreach ($sample as $r) echo "  " . implode(' | ', array_map(fn($k,$v) => "$k=$v", array_keys($r), $r)) . "\n";
}

// Cek tabel login atau akun
foreach (['login','akun','account','auth'] as $tbl) {
    if (in_array($tbl, $tables)) {
        echo "\n=== TABEL: $tbl ===\n";
        $cols = $p->query("DESCRIBE $tbl")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) echo "  {$c['Field']} | {$c['Type']}\n";
    }
}

// Cek fungsi login
echo "\n=== SAMPLE MAHASISWA ===\n";
$mhs = $p->query("SELECT id, nim, nama, prodi_id, email, status FROM mahasiswa LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($mhs as $r) {
    echo "  nim={$r['nim']} | nama={$r['nama']} | prodi={$r['prodi_id']} | email={$r['email']}\n";
}
