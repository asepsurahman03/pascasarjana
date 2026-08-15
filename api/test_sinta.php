<?php
$_GET = ['q' => 'ijnmt'];
ob_start();
include __DIR__ . '/../api/check_sinta.php';
$out = ob_get_clean();
// Remove headers from output
echo "By name 'ijnmt': " . $out . "\n";

$_GET = ['issn' => '2355-0082'];
ob_start();
include __DIR__ . '/../api/check_sinta.php';
$out2 = ob_get_clean();
echo "By ISSN '2355-0082': " . $out2 . "\n";

$_GET = ['q' => 'international journal of new media technology'];
ob_start();
include __DIR__ . '/../api/check_sinta.php';
$out3 = ob_get_clean();
echo "By full name: " . $out3 . "\n";
