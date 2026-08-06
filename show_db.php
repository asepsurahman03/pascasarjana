<?php
require 'includes/functions.php';
echo dbQuery('SHOW CREATE TABLE surat')[0]['Create Table'] . "\n\n";
echo dbQuery('SHOW CREATE TABLE surat_chat')[0]['Create Table'] . "\n";
