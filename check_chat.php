<?php
require 'includes/functions.php';
$rows = dbQuery('SELECT s.id, s.perihal, COUNT(sc.id) as chat_count FROM surat s LEFT JOIN surat_chat sc ON sc.surat_id = s.id GROUP BY s.id ORDER BY s.created_at DESC LIMIT 10');
foreach($rows as $r) {
    echo $r['id'] . ' | ' . $r['perihal'] . ' | chat_count=' . $r['chat_count'] . PHP_EOL;
}
