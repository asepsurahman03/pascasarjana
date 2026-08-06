<?php
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../includes/functions.php';
requireLogin();
$id=(int)($_GET['id']??0); if(!$id) die('ID tidak valid');
$s=dbQueryOne("SELECT s.*,p.nama as prodi_nama,p.kode as prodi_kode,p.kota_surat,p.kaprodi as nama_kaprodi,p.nidn_kaprodi FROM surat s JOIN prodi p ON p.id=s.prodi_id WHERE s.id=?",[$id]);
if(!$s) die('Tidak ditemukan');

$kode=$s['prodi_kode']; $kota=$s['kota_surat']?:'Sukabumi';
$bl=['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$ts=strtotime($s['tanggal']);
$tgl=$kota.', '.date('d',$ts).' '.$bl[(int)date('n',$ts)].' '.date('Y',$ts);

$base=BASE_PATH;
function ff(string $b,array $ns):?string{foreach($ns as $n){if(file_exists("$b/$n"))return "$b/$n";}return null;}
$hF=ff($base,["uploads/kop/header_{$kode}.png","uploads/kop/header_{$kode}.jpg"]);
$fF=ff($base,["uploads/kop/footer_{$kode}.jpg","uploads/kop/footer_{$kode}.png"]);
$tF=ff($base,["uploads/ttd/ttd_{$kode}.png"]);
$cF=ff($base,["uploads/cap/cap_{$kode}.png"]);

$imgReg=[];$seq=1;
function ri(?string $p,array &$r,int &$seq):?string{
    if(!$p||!file_exists($p))return null;
    $ext=strtolower(pathinfo($p,PATHINFO_EXTENSION))==='jpg'?'jpg':'png';
    $rid='rId'.$seq++;$r[]=['rid'=>$rid,'path'=>$p,'ext'=>$ext,'idx'=>count($r)+1];return $rid;
}
$rH=ri($hF,$imgReg,$seq);$rF=ri($fF,$imgReg,$seq);
$rT=ri($tF,$imgReg,$seq);$rC=ri($cF,$imgReg,$seq);
$rST='rId'.$seq++;$rSY='rId'.$seq++;$rHR='rId'.$seq++;$rFR='rId'.$seq++;

function xe(string $s):string{return htmlspecialchars($s,ENT_XML1,'UTF-8');}
function rpr(bool $b=false,bool $u=false,int $sz=24,string $f='Rockwell'):string{
    $x="<w:rFonts w:ascii=\"$f\" w:hAnsi=\"$f\" w:cs=\"$f\"/><w:sz w:val=\"$sz\"/><w:szCs w:val=\"$sz\"/>";
    if($b)$x.='<w:b/><w:bCs/>';if($u)$x.='<w:u w:val="single"/>';return "<w:rPr>$x</w:rPr>";
}
function ppr(string $jc='',int $iL=0,int $hang=0):string{
    $x='<w:spacing w:line="276" w:lineRule="auto" w:before="0" w:after="0"/>';
    if($jc)$x.="<w:jc w:val=\"$jc\"/>";
    if($iL)$x.="<w:ind w:left=\"$iL\"".($hang?" w:hanging=\"$hang\"":'').'/>';
    return "<w:pPr>$x</w:pPr>";
}
function wp(string $t,string $jc='',bool $b=false,bool $u=false,int $sz=24,int $iL=0,int $hang=0):string{
    if($t==='')return '<w:p>'.ppr($jc,$iL,$hang).'</w:p>';
    return '<w:p>'.ppr($jc,$iL,$hang).'<w:r>'.rpr($b,$u,$sz).'<w:t xml:space="preserve">'.xe($t).'</w:t></w:r></w:p>';
}

// Inline image
$iid=0;
function inl(string $rid,int $cx,int $cy):string{
    global $iid;$iid++;
    return "<w:r><w:rPr><w:noProof/></w:rPr><w:drawing><wp:inline xmlns:wp=\"http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing\"><wp:extent cx=\"$cx\" cy=\"$cy\"/><wp:effectExtent l=\"0\" t=\"0\" r=\"0\" b=\"0\"/><wp:docPr id=\"$iid\" name=\"i$iid\"/><wp:cNvGraphicFramePr/><a:graphic xmlns:a=\"http://schemas.openxmlformats.org/drawingml/2006/main\"><a:graphicData uri=\"http://schemas.openxmlformats.org/drawingml/2006/picture\"><pic:pic xmlns:pic=\"http://schemas.openxmlformats.org/drawingml/2006/picture\"><pic:nvPicPr><pic:cNvPr id=\"0\" name=\"i$iid\"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed=\"$rid\"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x=\"0\" y=\"0\"/><a:ext cx=\"$cx\" cy=\"$cy\"/></a:xfrm><a:prstGeom prst=\"rect\"><a:avLst/></a:prstGeom><a:noFill/><a:ln><a:noFill/></a:ln></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r>";
}

// Anchored (floating) image — for overlapping TTD+Cap
function anchorImg(string $rid,int $cx,int $cy,int $posX,int $posY,int $zOrder,bool $behind=false):string{
    global $iid;$iid++;
    $beh=$behind?'1':'0';
    $picXml="<pic:pic xmlns:pic=\"http://schemas.openxmlformats.org/drawingml/2006/picture\"><pic:nvPicPr><pic:cNvPr id=\"0\" name=\"i$iid\"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed=\"$rid\"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x=\"0\" y=\"0\"/><a:ext cx=\"$cx\" cy=\"$cy\"/></a:xfrm><a:prstGeom prst=\"rect\"><a:avLst/></a:prstGeom><a:noFill/><a:ln><a:noFill/></a:ln></pic:spPr></pic:pic>";
    return "<w:r><w:rPr><w:noProof/></w:rPr><w:drawing><wp:anchor xmlns:wp=\"http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing\" distT=\"0\" distB=\"0\" distL=\"0\" distR=\"0\" simplePos=\"0\" relativeHeight=\"$zOrder\" behindDoc=\"$beh\" locked=\"0\" layoutInCell=\"1\" allowOverlap=\"1\"><wp:simplePos x=\"0\" y=\"0\"/><wp:positionH relativeFrom=\"character\"><wp:posOffset>$posX</wp:posOffset></wp:positionH><wp:positionV relativeFrom=\"paragraph\"><wp:posOffset>$posY</wp:posOffset></wp:positionV><wp:extent cx=\"$cx\" cy=\"$cy\"/><wp:effectExtent l=\"0\" t=\"0\" r=\"0\" b=\"0\"/><wp:wrapNone/><wp:docPr id=\"$iid\" name=\"i$iid\"/><wp:cNvGraphicFramePr/><a:graphic xmlns:a=\"http://schemas.openxmlformats.org/drawingml/2006/main\"><a:graphicData uri=\"http://schemas.openxmlformats.org/drawingml/2006/picture\">$picXml</a:graphicData></a:graphic></wp:anchor></w:drawing></w:r>";
}

// Scale image to target height keeping aspect ratio, return [cx,cy] in EMU
function scaleEmu(?string $path,int $targetCyEmu):array{
    if(!$path||!file_exists($path))return[0,0];
    [$pw,$ph]=getimagesize($path);
    if($ph<=0)return[0,0];
    $dpi=220;
    $origCx=(int)round($pw*914400/$dpi);
    $origCy=(int)round($ph*914400/$dpi);
    if($origCy<=0)return[$origCx,$origCy];
    $ratio=$targetCyEmu/$origCy;
    return[(int)($origCx*$ratio),$targetCyEmu];
}

// Sizes
[$hCx,$hCy]=scaleEmu($hF,972000);   // header: max 27mm
// Footer: scale ke lebar penuh halaman A4 (11906 twips = 7,558,560 EMU)
// Gunakan lebar halaman penuh agar footer span full-width seperti contoh
[$fCx,$fCy]=scaleEmu($fF,1008000);  // footer: target 28mm tinggi
$fullPageW=7558560; // A4 width dalam EMU
if($fCx>0&&$fCy>0){
    // Scale berdasarkan lebar penuh halaman
    [$fCxFull,$fCyFull]=scaleEmu($fF,(int)round($fCy*($fullPageW/$fCx)));
    $fCx=$fullPageW;
    // Hitung tinggi proporsional
    if($fF&&file_exists($fF)){[$pw,$ph]=getimagesize($fF);if($pw>0)$fCy=(int)round($fullPageW*$ph/$pw);}
}
[$tCx,$tCy]=scaleEmu($tF,1116000);  // ttd: 31mm
[$cCx,$cCy]=scaleEmu($cF,1152000);  // cap: 32mm

// ISI SURAT
$rawIsi=!empty($s['html_kustom'])?$s['html_kustom']:($s['isi_surat']??'');
$isiXml='';
if($rawIsi){
    $dom=new DOMDocument();
    @$dom->loadHTML('<html><head><meta charset="utf-8"></head><body>'.$rawIsi.'</body></html>');
    foreach($dom->getElementsByTagName('body')->item(0)->childNodes as $node){
        $txt=trim($node->textContent);
        $isiXml.=$txt?wp($txt,'both'):'<w:p>'.ppr('both').'</w:p>';
    }
}
if(!$isiXml)$isiXml=wp('(Isi surat belum diisi)','both');

// META SURAT (tab-based 2 col)
$metaXml='';
foreach([['Nomor',$s['nomor_surat'],true],['Lampiran',$s['lampiran']??'-',false],['Perihal',$s['perihal']??'',true]] as [$lbl,$val,$b]){
    $metaXml.='<w:p>'.ppr()
        .'<w:r>'.rpr().'<w:t xml:space="preserve">'.xe($lbl).'</w:t></w:r>'
        .'<w:r>'.rpr().'<w:tab/></w:r>'
        .'<w:r>'.rpr().'<w:t xml:space="preserve">: </w:t></w:r>'
        .'<w:r>'.rpr($b).'<w:t xml:space="preserve">'.xe($val).'</w:t></w:r>'
        .'</w:p>';
}

// TTD BLOCK — Cap di KIRI, TTD overlapping di kanan cap (sesuai contoh surat resmi)
$nama=$s['nama_kaprodi']?:'________________________';
$nidn=$s['nidn_kaprodi']??'';
$nb='<w:tblBorders><w:top w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:left w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:bottom w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:right w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:insideH w:val="none" w:sz="0" w:space="0" w:color="auto"/><w:insideV w:val="none" w:sz="0" w:space="0" w:color="auto"/></w:tblBorders>';

// Build TTD+Cap overlapping paragraph
// Cap di kiri (z=1), TTD anchor mulai di 40% dari lebar cap → overlap kanan cap (z=2)
$ttdImgPar='';
if($rT||$rC){
    $ttdImgPar='<w:p>'.ppr();
    // Offset negatif agar cap & tanda tangan sedikit terangkat ke atas (-250000 EMU ~= -20pt)
    $upOffset = -250000;
    if($rC)$ttdImgPar.=anchorImg($rC,$cCx,$cCy,0,$upOffset,1,false);
    if($rT)$ttdImgPar.=anchorImg($rT,$tCx,$tCy,(int)($cCx*0.42),$upOffset,2,false);
    $ttdImgPar.='</w:p>';
}
// Tambahkan 2x Enter (paragraf kosong) agar posisi nama di bawah
$ttdImgPar .= wp('') . wp('');

// TTD BLOCK — Tabel 1 kolom rata kanan (right-aligned) agar batas kanan sejajar sempurna dengan margin teks
$ttdBlock='<w:tbl>'
    .'<w:tblPr><w:jc w:val="right"/><w:tblW w:w="0" w:type="auto"/>'.$nb.'</w:tblPr>'
    .'<w:tblGrid><w:gridCol w:w="5500"/></w:tblGrid>'
    .'<w:tr>'
        .'<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/>'.$nb.'</w:tcPr>'
            .wp('Mengetahui,')
            .wp('Ketua Program Studi '.xe($s['prodi_nama']),'',true)
            .$ttdImgPar
            .wp($nama,'',true,true)
            .($nidn?wp('NIDN. '.$nidn,''):'')
        .'</w:tc>'
    .'</w:tr>'
.'</w:tbl>';

// BODY
$body=wp($tgl,'right')
    .'<w:p>'.ppr().'</w:p>'
    .$metaXml
    .'<w:p>'.ppr().'</w:p>'
    .wp('Kepada Yth.')
    .wp($s['nama_penerima']??'','',true)
    .wp('di tempat')
    .'<w:p>'.ppr().'</w:p>'
    .wp('Dengan hormat,')
    .'<w:p>'.ppr().'</w:p>'
    .$isiXml
    .'<w:p>'.ppr().'</w:p>'
    .wp('Demikian permohonan ini kami sampaikan. Atas perhatian dan kesediaan Bapak/Ibu, kami ucapkan terima kasih.','both')
    .'<w:p>'.ppr().'</w:p>'
    .$ttdBlock;

// HEADER XML (logo + prodi + univ + garis separator)
$hdrBody='';
if($rH){
    $hdrBody.='<w:p><w:pPr><w:pStyle w:val="Header"/><w:spacing w:after="0"/><w:jc w:val="center"/></w:pPr>'.inl($rH,$hCx,$hCy).'</w:p>';
}
$hdrBody.='<w:p><w:pPr><w:pStyle w:val="Header"/><w:spacing w:after="0"/><w:jc w:val="center"/></w:pPr>'
    .'<w:r><w:rPr><w:rFonts w:ascii="Rockwell" w:hAnsi="Rockwell" w:cs="Rockwell"/><w:b/><w:bCs/><w:color w:val="000000"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>'
    .'<w:t>PROGRAM STUDI '.xe(strtoupper($s['prodi_nama'])).'</w:t></w:r></w:p>';
// Baris UNIVERSITAS NUSA PUTRA
$hdrBody.='<w:p><w:pPr><w:pStyle w:val="Header"/><w:spacing w:before="0" w:after="0"/><w:jc w:val="center"/></w:pPr>'
    .'<w:r><w:rPr><w:rFonts w:ascii="Rockwell" w:hAnsi="Rockwell" w:cs="Rockwell"/><w:b/><w:bCs/><w:color w:val="000000"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>'
    .'<w:t>UNIVERSITAS NUSA PUTRA</w:t></w:r></w:p>';

// FOOTER XML — full-width, di bawah halaman, centered
$ftrBody='';
if($rF){
    // Gunakan indentasi negatif agar footer span full-width (melampaui margin kiri-kanan)
    $negL=1140*914400/1440; // offset kiri margin dalam EMU
    $negR=1195*914400/1440; // offset kanan margin dalam EMU
    $ftrBody='<w:p><w:pPr><w:pStyle w:val="Footer"/><w:jc w:val="center"/>'
        .'<w:spacing w:before="0" w:after="0"/>'
        .'<w:ind w:left="-1140" w:right="-1195"/>'
        .'</w:pPr>'.inl($rF,$fCx,$fCy).'</w:p>';
}else{
    $ftrBody='<w:p><w:pPr><w:pStyle w:val="Footer"/><w:jc w:val="center"/></w:pPr></w:p>';
}

$NSdoc='xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"';
$hdrXml='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:hdr '.$NSdoc.'>'.$hdrBody.'</w:hdr>';
$ftrXml='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:ftr '.$NSdoc.'>'.$ftrBody.'</w:ftr>';

// DOCUMENT XML
$docXml='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<w:document '.$NSdoc.'><w:body>'.$body
    .'<w:sectPr>'
    ."<w:headerReference w:type=\"default\" r:id=\"$rHR\"/>"
    ."<w:footerReference w:type=\"default\" r:id=\"$rFR\"/>"
    .'<w:pgSz w:w="11906" w:h="16838"/>'
    .'<w:pgMar w:top="0" w:right="1195" w:bottom="700" w:left="1140" w:header="288" w:footer="0" w:gutter="0"/>'
    .'</w:sectPr></w:body></w:document>';

// RELS helpers
$RNS='xmlns="http://schemas.openxmlformats.org/package/2006/relationships"';
function imgRel(array $imgs,string $rid):string{
    foreach($imgs as $img)if($img['rid']===$rid)return "<Relationship Id=\"$rid\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/image\" Target=\"media/img{$img['idx']}.{$img['ext']}\"/>";
    return '';
}
$hdrRels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships '.$RNS.'>'.($rH?imgRel($imgReg,$rH):'').'</Relationships>';
$ftrRels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships '.$RNS.'>'.($rF?imgRel($imgReg,$rF):'').'</Relationships>';

$docRels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships '.$RNS.'>'
    ."<Relationship Id=\"$rST\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings\" Target=\"settings.xml\"/>"
    ."<Relationship Id=\"$rSY\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles\" Target=\"styles.xml\"/>"
    ."<Relationship Id=\"$rHR\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/header\" Target=\"header1.xml\"/>"
    ."<Relationship Id=\"$rFR\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer\" Target=\"footer1.xml\"/>";
foreach($imgReg as $img)$docRels.=imgRel($imgReg,$img['rid']);
$docRels.='</Relationships>';

$rootRels='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships '.$RNS.'>'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
    .'</Relationships>';

$styles='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Rockwell" w:hAnsi="Rockwell" w:cs="Rockwell"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:rPrDefault></w:docDefaults>
<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/>
<w:pPr><w:spacing w:before="0" w:after="0" w:line="276" w:lineRule="auto"/></w:pPr>
<w:rPr><w:rFonts w:ascii="Rockwell" w:hAnsi="Rockwell"/><w:sz w:val="24"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="Header"><w:name w:val="header"/>
<w:basedOn w:val="Normal"/><w:pPr><w:jc w:val="center"/><w:spacing w:before="0" w:after="0"/></w:pPr></w:style>
<w:style w:type="paragraph" w:styleId="Footer"><w:name w:val="footer"/>
<w:basedOn w:val="Normal"/><w:pPr><w:jc w:val="center"/><w:spacing w:before="0" w:after="0"/></w:pPr></w:style>
</w:styles>';

$settings='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    .'<w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
    .'<w:defaultTabStop w:val="1440"/></w:settings>';

$ct='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Default Extension="png" ContentType="image/png"/>
<Default Extension="jpg" ContentType="image/jpeg"/>
<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
<Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/>
<Override PartName="/word/header1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"/>
<Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/>
</Types>';

$tmp=tempnam(sys_get_temp_dir(),'docx_');
$zip=new ZipArchive();
$zip->open($tmp,ZipArchive::CREATE|ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml',$ct);
$zip->addFromString('_rels/.rels',$rootRels);
$zip->addFromString('word/document.xml',$docXml);
$zip->addFromString('word/styles.xml',$styles);
$zip->addFromString('word/settings.xml',$settings);
$zip->addFromString('word/header1.xml',$hdrXml);
$zip->addFromString('word/footer1.xml',$ftrXml);
$zip->addFromString('word/_rels/document.xml.rels',$docRels);
$zip->addFromString('word/_rels/header1.xml.rels',$hdrRels);
$zip->addFromString('word/_rels/footer1.xml.rels',$ftrRels);
foreach($imgReg as $img)$zip->addFile($img['path'],"word/media/img{$img['idx']}.{$img['ext']}");
$zip->close();

$parts = explode('/', $s['nomor_surat']);
$nomor_saja = $parts[0] ?? $s['nomor_surat'];
$jenis = !empty($s['perihal']) ? $s['perihal'] : ($s['jenis_surat'] ?? '');
$penerima = !empty($s['nama_penerima']) ? $s['nama_penerima'] : '';

$safe_title = trim($nomor_saja) . ' Surat ' . trim($jenis);
if ($penerima) {
    $safe_title .= ' - ' . trim($penerima);
}
$safe_title = preg_replace('/[^A-Za-z0-9 \-]/', '_', $safe_title);
$fname = $safe_title . '.docx';
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="'.$fname.'"');
header('Content-Length: '.filesize($tmp));
readfile($tmp);unlink($tmp);exit;
