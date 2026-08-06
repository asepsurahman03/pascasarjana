<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['error'=>'Unauthorized']); exit; }

$id      = (int)($_GET['prodi_id'] ?? 0);
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
if (!$id) { echo json_encode(['error'=>'Invalid']); exit; }

$prodi = dbQueryOne("SELECT * FROM prodi WHERE id=?", [$id]);
if (!$prodi) { echo json_encode(['error'=>'Not found']); exit; }

$kode  = $prodi['kode'];
$nomor = generateNomorSurat($id, $tanggal);

echo json_encode([
    'id'            => $prodi['id'],
    'kode'          => $kode,
    'prefix'        => $prodi['prefix_surat'] ?: $kode,
    'nama'          => $prodi['nama'],
    'kota'          => $prodi['kota_surat'] ?: 'Sukabumi',
    'nama_kaprodi'  => $prodi['kaprodi'] ?: '',
    'gelar_kaprodi' => '',
    'nidn_kaprodi'  => $prodi['nidn_kaprodi'] ?: '',
    'ttd_url'       => getTtdUrl($kode),
    'cap_url'       => getCapUrl($kode),
    'kop_url'       => getKopPath($kode, true),
    'footer_url'    => getFooterPath($kode, true),
    'nomor_surat'   => $nomor,
    'tgl_formatted' => formatTanggalSurat($tanggal, $prodi['kota_surat'] ?: 'Sukabumi'),
]);
