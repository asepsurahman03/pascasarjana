<?php
require_once 'c:/xampp/htdocs/Aktifnonaktif/config/database.php';
require_once 'c:/xampp/htdocs/Aktifnonaktif/models/Models.php';
require_once 'c:/xampp/htdocs/Aktifnonaktif/helpers/utils.php';
$_GET['id'] = 5; // ID of some pengajuan
require_once 'c:/xampp/htdocs/Aktifnonaktif/controllers/DocxController.php';

$c = new DocxController();
ob_start();
$c->generate();
$out = ob_get_clean();
echo "Length: " . strlen($out);
