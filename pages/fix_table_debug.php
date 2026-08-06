<?php
// Rebuild cetak_lampiran.php Lampiran 7 table block completely
$file = 'c:/xampp/htdocs/webdummy/pages/cetak_lampiran.php';
$content = file_get_contents($file);

// Find Lampiran 7 start and end markers
if (!preg_match('/(\[ \/\/ Lampiran 7:.*?)(,?\s*\[ \/\/ Lampiran 8:)/s', $content, $m, PREG_OFFSET_CAPTURE)) {
    die("ERROR: Lampiran 7 not found\n");
}

$start = $m[1][1];
$end = $m[2][1];
$lamp7 = substr($content, $start, $end - $start);

// Replace the corrupted tbody section (rows 1-6 table) with a clean version
// First, find the table blocks using regex
$pattern = '/(<table style=\\"width:100%; font-size:12pt;.*?<tbody class="divide-y divide-slate-100 dark:divide-slate-700">.*?)(range\(1, 6\))(.*?<\/tbody>\s*<\/table>)/s';
if (!preg_match($pattern, $lamp7)) {
    echo "Pattern 1-6 not matched directly, trying simpler approach\n";
}

// Replace the entire two-table block with clean version
$old_tables = '/<table style=\\"width:100%; font-size:12pt; font-family:Rockwell,serif; margin-bottom:15px; border-collapse:collapse;\\" border=\\"1\\">\s*<thead>
<tr class="border-b border-slate-200 dark:border-slate-700">.*?<\/thead>\s*<tbody class="divide-y divide-slate-100 dark:divide-slate-700">.*?range\(1, 6\).*?<\/tbody>\s*<\/table>.*?<table style=\\"width:100%; font-size:12pt; font-family:Rockwell,serif; margin-bottom:15px; border-collapse:collapse;\\" border=\\"1\\">\s*<thead>
<tr class="border-b border-slate-200 dark:border-slate-700">.*?<\/thead>\s*<tbody class="divide-y divide-slate-100 dark:divide-slate-700">.*?range\(7, 10\).*?<\/tbody>\s*<\/table>/s';

$new_tables = '<table style=\\"width:100%; font-size:12pt; font-family:Rockwell,serif; margin-bottom:15px; border-collapse:collapse;\\" border=\\"1\\">
            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">
                <tr style=\\"background-color:#99295f; color:white; font-weight:bold; text-align:center;\\">
                    <th style=\\"width:6%; padding:8px;\\">No.</th>
                    <th style=\\"width:22%; padding:8px;\\">Hari / Tanggal</th>
                    <th style=\\"width:52%; padding:8px;\\">Catatan &amp; Saran Pembimbing</th>
                    <th style=\\"width:20%; padding:8px;\\">Paraf</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                \\" . implode(\\"\\", array_map(function($i) {
                    return \\"<tr>
                        <td class="py-3 px-4" style=\'text-align:center; padding:25px; height:40px;\'>$i</td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px; height:40px;\'></td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px; height:40px;\'></td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px; height:40px;\'></td>
                    </tr>\\";
                }, range(1, 6))) . \\"
            </tbody>
        </table>
        <table style=\\"width:100%; font-size:12pt; font-family:Rockwell,serif; margin-bottom:15px; border-collapse:collapse;\\" border=\\"1\\">
            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">
                <tr style=\\"background-color:#99295f; color:white; font-weight:bold; text-align:center;\\">
                    <th style=\\"width:6%; padding:8px;\\">No.</th>
                    <th style=\\"width:22%; padding:8px;\\">Hari / Tanggal</th>
                    <th style=\\"width:52%; padding:8px;\\">Catatan &amp; Saran Pembimbing</th>
                    <th style=\\"width:20%; padding:8px;\\">Paraf</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                \\" . implode(\\"\\", array_map(function($i) {
                    return \\"<tr>
                        <td class="py-3 px-4" style=\'text-align:center; padding:25px; height:40px;\'>$i</td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px; height:40px;\'></td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px; height:40px;\'></td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px; height:40px;\'></td>
                    </tr>\\";
                }, range(7, 10))) . \\"
            </tbody>
        </table>';

$lamp7_new = preg_replace($old_tables, $new_tables, $lamp7);
if ($lamp7_new === null) {
    die("preg_replace error: " . preg_last_error_msg() . "\n");
}
if ($lamp7_new === $lamp7) {
    echo "WARNING: No change made! Pattern not matched.\n";
    echo "Let me show the current tables block:\n";
    preg_match('/<table style=\\"width:100%; font-size:12pt.*?<\/table>/s', $lamp7, $tm);
    echo htmlspecialchars(substr($lamp7, strpos($lamp7, '<table'), 500)) . "\n";
} else {
    $new_content = substr_replace($content, $lamp7_new, $start, $end - $start);
    file_put_contents($file, $new_content);
    echo "SUCCESS: cetak_lampiran.php updated\n";
}
?>
