<?php
$z = new ZipArchive();
$z->open('template_tagged.docx');
echo $z->getFromName('word/_rels/document.xml.rels') . "\n\n";
echo $z->getFromName('[Content_Types].xml');
