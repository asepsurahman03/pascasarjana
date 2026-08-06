<?php
require_once __DIR__."/../config/database.php";
require_once __DIR__."/../includes/functions.php";
requireLogin();

$fp=(int)($_GET["prodi_id"]??0);
$fs=$_GET["status"]??"";
$fa=(int)($_GET["angkatan"]??0);
$q=trim($_GET["q"]??"");

$w=["1=1"];$p=[];
if($fp){$w[]="m.prodi_id=?";$p[]=$fp;}
if($fs){$w[]="m.status=?";$p[]=$fs;}
if($fa){$w[]="m.angkatan=?";$p[]=$fa;}
if($q){$w[]="(m.nama LIKE ? OR m.nim LIKE ?)";$p[]="$q%";$p[]="$q%";}

$ws=implode(" AND ",$w);
$data=dbQuery("SELECT m.nim,m.nik,m.nama,m.jenis_kelamin,m.nama_ibu,m.agama,p.nama as prodi,m.angkatan,m.kelas,m.konsentrasi,m.status,m.tempat_lahir,m.tanggal_lahir,m.no_hp,m.email,m.alamat,m.dosen_pembimbing,m.judul_tesis FROM mahasiswa m LEFT JOIN prodi p ON p.id=m.prodi_id WHERE $ws ORDER BY m.angkatan DESC,m.nim ASC",$p);

header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"mahasiswa_".date("Ymd").".csv\"");
$f=fopen("php://output","w");
fputs($f,"\xEF\xBB\xBF"); // BOM UTF-8
fputcsv($f,["NIM","NIK","Nama","JK","Nama Ibu","Agama","Prodi","Angkatan","Kelas","Konsentrasi","Status","Tempat Lahir","Tgl Lahir","No HP","Email","Alamat","Pembimbing","Judul Tesis"]);
foreach($data as $r) fputcsv($f,array_values($r));
fclose($f);
exit;
