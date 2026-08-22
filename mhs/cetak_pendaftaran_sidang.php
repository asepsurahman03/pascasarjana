<?php
ob_start(); // Buffer output agar session bisa dimulai sebelum ada output HTML
// Ambil data dari URL parameter (dikirim saat submit form pendaftaran)
// Fallback ke dummy data jika tidak ada parameter
require_once __DIR__ . '/../includes/functions.php';


$nama        = trim($_GET['nama']        ?? '');
$nim         = trim($_GET['nim']         ?? '');
$angkatan    = trim($_GET['angkatan']    ?? '');
$pembimbing1 = trim($_GET['pembimbing1'] ?? '');
$pembimbing2 = trim($_GET['pembimbing2'] ?? '');
$emailParam  = trim($_GET['email']       ?? '');
$hp          = trim($_GET['hp']          ?? '');
$judul       = trim($_GET['judul']       ?? '');
$jenis       = trim($_GET['jenis']       ?? 'Sidang Tesis');

// Jika ada mahasiswa_id, ambil dari database (prioritas)
$mhsId = (int)($_GET['mhs_id'] ?? 0);
if ($mhsId) {
    $dbMhs = dbQueryOne("SELECT m.*, p.nama AS nama_prodi FROM mahasiswa m LEFT JOIN prodi p ON m.prodi_id=p.id WHERE m.id=?", [$mhsId]);
    if ($dbMhs) {
        $nama        = $dbMhs['nama'];
        $nim         = $dbMhs['nim'];
        $angkatan    = $dbMhs['angkatan'];
        $emailParam  = $dbMhs['email'];
        $hp          = $dbMhs['no_hp'] ?? '';
        $namaProdi   = $dbMhs['nama_prodi'];
        $ttl         = ($dbMhs['tempat_lahir'] ?? '') . ', ' . (!empty($dbMhs['tanggal_lahir']) ? date('d F Y', strtotime($dbMhs['tanggal_lahir'])) : '-');
        $alamat      = $dbMhs['alamat'] ?? '-';
        $konsentrasi = $dbMhs['konsentrasi'] ?? '-';
        if (!$pembimbing1) $pembimbing1 = $dbMhs['dosen_pembimbing'] ?? '-';
        if (!$judul) $judul = $dbMhs['judul_tesis'] ?? '-';
    }
}

$mhs = [
    'nama'        => $nama        ?: 'Ahmad Rizki Pratama',
    'nim'         => $nim         ?: '2023MIF001',
    'angkatan'    => $angkatan    ?: '2023',
    'ttl'         => $ttl         ?? 'Jakarta, 15 Agustus 1995',
    'prodi'       => $namaProdi   ?? 'Magister Informatika',
    'konsentrasi' => $konsentrasi ?? 'Rekayasa Perangkat Lunak',
    'alamat'      => $alamat      ?? 'Jl. Raya Cisaat No. 123, Sukabumi',
    'alamat_ortu' => '-',
    'hp'          => $hp          ?: '081234567890',
    'hp_ortu'     => '-',
    'email'       => $emailParam  ?: 'mahasiswa@mhs.nusaputra.ac.id',
    'pembimbing1' => $pembimbing1 ?: 'Dr. Ahmad Fauzi, M.Kom',
    'pembimbing2' => $pembimbing2 ?: '-',
    'semester'    => '4 (Genap 2025/2026)',
];

$tanggal = date('d F Y');

// Header and Footer Images Base64 or URLs
$logo_url = '../assets/images/LOGO-UNIVERSITAS-NUSA-PUTRA.png'; 
$logo_fallback = 'https://nusaputra.ac.id/wp-content/uploads/2019/07/logo-nusa-putra-ep.png';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cetak Pendaftaran Sidang</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>
  @page { margin: 0; size: A4 portrait; }
  body { background: #525659; font-family: "Times New Roman", Times, serif; color: #000; margin: 0; padding: 20px 0; display: flex; flex-direction: column; align-items: center; }
  .a4-page {
    width: 210mm;
    min-height: 297mm;
    background: white;
    margin-bottom: 20px;
    padding: 25mm 25mm 25mm 25mm; /* Standard Word margins */
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
    position: relative;
    box-sizing: border-box;
  }
  @media print {
    body { background: white; padding: 0; display: block; }
    .a4-page { margin: 0; box-shadow: none; border: none; page-break-after: always; padding: 25mm; }
    #loading { display: none !important; }
  }
  
  .header-logo { display: block; margin: 0 auto 15px auto; height: 95px; object-fit: contain; }
  .footer-img { position: absolute; bottom: 15mm; left: 25mm; right: 25mm; width: calc(100% - 50mm); object-fit: contain; height: 35px; border-top: 1px solid #000; padding-top: 5px; text-align: center; font-size: 7.5pt; color: #333; font-family: Arial, sans-serif; }
  
  .title-bold { text-align: center; font-weight: bold; font-size: 13pt; margin-bottom: 20px; line-height: 1.3; }
  
  .table-bordered { width: 100%; border-collapse: collapse; font-size: 11pt; }
  .table-bordered td, .table-bordered th { border: 1px solid #000; padding: 8px 10px; vertical-align: top; }
  
  .table-form { font-size: 12pt; width: 100%; border-collapse: collapse; }
  .table-form td { vertical-align: top; padding: 4px 0; }
  
  .dotted-line { border-bottom: 1px dotted #000; width: 100%; display: inline-block; }
  
  .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.08; width: 350px; z-index: 0; pointer-events: none; }
  .content-z { position: relative; z-index: 10; }
</style>
</head>
<body>

<div id="pdf-content">

<!-- HALAMAN 1 -->
<div class="a4-page">
  <img src="<?= $logo_fallback ?>" onerror="this.src='<?= $logo_url ?>'" class="header-logo" alt="Logo Nusa Putra">
  
  <div class="title-bold" style="font-size: 13.5pt;">
    FORMULIR PENDAFTARAN<br>
    SIDANG TESIS<br>
    TAHUN AKADEMIK 2025/2026
  </div>

  <div style="font-size: 12pt; margin-bottom: 8px; text-transform: uppercase;">
    PROGRAM STUDI S2 <strong><?= $mhs['prodi'] ?></strong> <span style="display:inline-block; width:150px; border-bottom:1px solid #000; margin-bottom:2px;"></span>
  </div>

  <table class="table-bordered" style="margin-bottom: 30px;">
    <tr>
      <td style="width: 35%;">Nama</td>
      <td style="width: 40%; font-weight: bold;"><?= $mhs['nama'] ?></td>
      <td style="width: 25%; border-bottom: none;">NIM:<br><span style="font-weight:normal; font-size:10.5pt;"><?= $mhs['nim'] ?></span></td>
    </tr>
    <tr>
      <td>Tempat Tanggal Lahir</td>
      <td><?= $mhs['ttl'] ?></td>
      <td rowspan="5" style="text-align: center; vertical-align: middle; border-top: none; font-size: 10pt; color: #555;">Pas Foto<br><strong>4 x 6</strong></td>
    </tr>
    <tr>
      <td>Program Studi</td>
      <td><?= $mhs['prodi'] ?></td>
    </tr>
    <tr>
      <td>Angkatan</td>
      <td><?= $mhs['angkatan'] ?></td>
    </tr>
    <tr>
      <td>Konsentrasi</td>
      <td><?= $mhs['konsentrasi'] ?></td>
    </tr>
    <tr>
      <td style="height: 50px;">Alamat Tinggal</td>
      <td><?= $mhs['alamat'] ?></td>
    </tr>
    <tr>
      <td style="height: 50px;">Alamat Tinggal Orang Tua</td>
      <td><?= $mhs['alamat_ortu'] ?></td>
    </tr>
    <tr>
      <td>No Tlp/HP</td>
      <td><?= $mhs['hp'] ?></td>
      <td>No HP Orang Tua<br><span style="font-weight:normal; font-size:10.5pt;"><?= $mhs['hp_ortu'] ?></span></td>
    </tr>
    <tr>
      <td>Alamat Email</td>
      <td colspan="2"><?= $mhs['email'] ?></td>
    </tr>
    <tr>
      <td>Ketua Pembimbing</td>
      <td colspan="2"><?= $mhs['pembimbing1'] ?></td>
    </tr>
    <tr>
      <td>Anggota Pembimbing</td>
      <td colspan="2"><?= $mhs['pembimbing2'] ?></td>
    </tr>
    <tr>
      <td style="height: 80px;">Judul Tugas Akhir/Skripsi</td>
      <td colspan="2" style="font-style: italic;"><?= htmlspecialchars($judul) ?></td>
    </tr>
  </table>

  <div style="display: flex; justify-content: flex-end; font-size: 12pt; margin-top: 40px;">
    <div style="width: 220px;">
      Sukabumi, <?= $tanggal ?><br>
      <div style="height: 70px;"></div>
      <div class="dotted-line"></div>
    </div>
  </div>

  <div class="footer-img">
    <strong style="color: #961d5a;">NUSA PUTRA BUILDING</strong><br>
    Jl. Raya Cibatuh Cisaat No.21, Cibolang Kaler, Kec. Cisaat, Kabupaten Sukabumi, Jawa Barat 43152 &#9742; +62 266-210594 &#128423; +62 266-237287
  </div>
</div>

<!-- HALAMAN 2 -->
<div class="a4-page">
  <img src="<?= $logo_fallback ?>" onerror="this.src='<?= $logo_url ?>'" class="header-logo" alt="Logo Nusa Putra">
  
  <div class="title-bold" style="margin-top: 30px; font-size: 13.5pt;">
    LEMBAR PERSYARATAN SIDANG
  </div>

  <table class="table-form" style="margin-bottom: 20px;">
    <tr>
      <td style="width: 140px;">Nama</td>
      <td style="width: 15px;">:</td>
      <td style="font-weight: bold;"><?= $mhs['nama'] ?></td>
    </tr>
    <tr>
      <td>NIM</td>
      <td>:</td>
      <td><?= $mhs['nim'] ?></td>
    </tr>
    <tr>
      <td>Angkatan</td>
      <td>:</td>
      <td><?= $mhs['angkatan'] ?></td>
    </tr>
    <tr>
      <td>Program Studi</td>
      <td>:</td>
      <td><?= $mhs['prodi'] ?></td>
    </tr>
  </table>

  <table class="table-bordered" style="font-size: 11.5pt;">
    <thead>
      <tr>
        <th style="width: 50px; text-align: center; padding: 4px;">No</th>
        <th style="text-align: center; padding: 4px;">Persyaratan Sidang</th>
        <th style="width: 80px; text-align: center; padding: 4px;">Cek</th>
      </tr>
    </thead>
    <tbody>
      <tr><td style="text-align: center;">1</td><td>Formulir Pendaftaran Sidang</td><td></td></tr>
      <tr><td style="text-align: center;">2</td><td>Surat Lembar Persetujuan Sidang Tesis</td><td></td></tr>
      <tr><td style="text-align: center;">3</td><td>Draft Tesis*</td><td></td></tr>
      <tr><td style="text-align: center;">4</td><td>KHS sampai Semester Terakhir</td><td></td></tr>
      <tr><td style="text-align: center;">5</td><td>Surat Keterangan Bebas Administrasi</td><td></td></tr>
      <tr><td style="text-align: center;">6</td><td>Surat Keterangan Pemenuhan Luaran Syarat Lulus</td><td></td></tr>
      <tr><td style="text-align: center;">7</td><td>Surat Bukti Penyerahan Buku Sumbangan sebanyak 3 Buah dari Bag. Perpustakaan Universitas Nusa Putra</td><td></td></tr>
      <tr><td style="text-align: center;">8</td><td>Surat Bebas Pinjam Buku dari Bag. Perpustakaan</td><td></td></tr>
      <tr><td style="text-align: center;">9</td><td>Mengumpulkan File Foto Background Merah (Pria Berjas Hitam Berdasi dan Wanita Memakai Blazer Hitam)</td><td></td></tr>
      <tr><td style="text-align: center;">10</td><td>Fotocopy Ijazah Sarjana yang dilegalisir/</td><td></td></tr>
    </tbody>
  </table>

  <div style="margin-top: 30px; font-size: 11.5pt;">
    Sukabumi, ....................................................
  </div>

  <div style="display: flex; justify-content: space-between; margin-top: 20px; font-size: 11.5pt;">
    <div style="width: 250px;">
      Staff Admin Akademik
      <div style="margin-top: 70px;" class="dotted-line"></div>
    </div>
    <div style="width: 250px;">
      Mahasiswa
      <div style="margin-top: 70px;" class="dotted-line"></div>
    </div>
  </div>

  <div style="margin-top: 40px; font-size: 10.5pt; line-height: 1.6;">
    Keterangan:<br>
    Seluruh persyaratan sidang dikumpulkan dalam bentuk <i>hardfile</i> dan <i>softfile</i>.<br>
    <i>Softfile</i> dikirimkan ke email <a href="mailto:sidang@nusaputra.ac.id" style="color: blue; text-decoration: underline;">sidang@nusaputra.ac.id</a><br>
    * <i>Hardfile</i> draft tesis dikumpulkan sebanyak 3 rangkap<br>
    * <i>Softfile</i> draft tesis dikumpulkan dalam bentuk file PDF dengan format nama file: Draft_Nama_NIM_Prodi.
  </div>

  <div class="footer-img">
    <strong style="color: #961d5a;">NUSA PUTRA BUILDING</strong><br>
    Jl. Raya Cibatuh Cisaat No.21, Cibolang Kaler, Kec. Cisaat, Kabupaten Sukabumi, Jawa Barat 43152 &#9742; +62 266-210594 &#128423; +62 266-237287
  </div>
</div>

<!-- HALAMAN 3 -->
<div class="a4-page">
  <img src="<?= $logo_fallback ?>" onerror="this.src='<?= $logo_url ?>'" class="header-logo" alt="Logo Nusa Putra">
  
  <div class="title-bold" style="margin-top: 30px; font-size: 13.5pt; line-height: 1.6;">
    LEMBAR PERSETUJUAN<br>
    PELAKSANAAN SIDANG TESIS
  </div>

  <table class="table-form" style="margin-top: 40px; margin-left: 10px;">
    <tr>
      <td style="width: 30px;">1.</td>
      <td style="width: 170px;">Nama Mahasiswa</td>
      <td style="width: 15px;">:</td>
      <td style="font-weight: bold;"><?= $mhs['nama'] ?></td>
    </tr>
    <tr>
      <td>2.</td>
      <td>NIM</td>
      <td>:</td>
      <td><?= $mhs['nim'] ?></td>
    </tr>
    <tr>
      <td>3.</td>
      <td>Angkatan</td>
      <td>:</td>
      <td><?= $mhs['angkatan'] ?></td>
    </tr>
    <tr>
      <td>4.</td>
      <td>Program Studi</td>
      <td>:</td>
      <td><?= $mhs['prodi'] ?></td>
    </tr>
    <tr>
      <td>4.</td>
      <td>Alamat Rumah</td>
      <td>:</td>
      <td><?= $mhs['alamat'] ?></td>
    </tr>
    <tr>
      <td>5.</td>
      <td>Telepon (HP)/email</td>
      <td>:</td>
      <td><?= $mhs['hp'] ?> / <span style="color: blue; text-decoration: underline;"><?= $mhs['email'] ?></span></td>
    </tr>
    <tr>
      <td>6.</td>
      <td>Judul Penelitian</td>
      <td>:</td>
      <td style="font-style: italic;"><?= htmlspecialchars($judul) ?></td>
    </tr>
  </table>

  <div style="display: flex; justify-content: space-between; margin-top: 70px; padding: 0 10px; font-size: 12pt;">
    <div style="width: 250px;">
      Ketua Pembimbing,
      <div style="margin-top: 80px;">
        <span style="font-weight: bold;"><?= $mhs['pembimbing1'] ?></span><br>
        NIDN. ........................................
      </div>
    </div>
    <div style="width: 250px;">
      Anggota Pembimbing,
      <div style="margin-top: 80px;">
        <span style="font-weight: bold;"><?= $mhs['pembimbing2'] ?></span><br>
        NIDN. ........................................
      </div>
    </div>
  </div>

  <div style="text-align: center; margin-top: 60px; font-size: 12pt;">
    Menyetujui,<br><br>
    Ketua Program Studi<br>
    Universitas Nusa Putra<br>
    <div style="margin-top: 80px; display: inline-block; width: 350px; border-bottom: 2px solid #000;"></div><br>
    <div style="text-align: left; width: 350px; margin: 0 auto; font-size: 11.5pt; padding-top: 5px;">
      NIDN : 
    </div>
  </div>

  <div class="footer-img">
    <strong style="color: #961d5a;">NUSA PUTRA BUILDING</strong><br>
    Jl. Raya Cibatuh Cisaat No.21, Cibolang Kaler, Kec. Cisaat, Kabupaten Sukabumi, Jawa Barat 43152 &#9742; +62 266-210594 &#128423; +62 266-237287
  </div>
</div>

<!-- HALAMAN 4 -->
<div class="a4-page">
  <img src="<?= $logo_fallback ?>" onerror="this.src='<?= $logo_url ?>'" class="watermark" alt="Watermark">
  
  <div class="content-z">
    <img src="<?= $logo_fallback ?>" onerror="this.src='<?= $logo_url ?>'" class="header-logo" alt="Logo Nusa Putra">
    
    <div style="text-align: center; font-size: 14pt; margin-top: 15px;">
      <span style="font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 2px;">SURAT KETERANGAN</span><br>
      <div style="margin-top: 8px; font-size: 12pt;">Nomor: ..............................................................</div>
    </div>

    <div style="margin-top: 40px; font-size: 12pt;">
      Yang bertanda tangan dibawah ini:
    </div>

    <table class="table-form" style="margin-top: 15px; margin-left: 0;">
      <tr>
        <td style="width: 150px;">Nama</td>
        <td style="width: 15px;">:</td>
        <td>....................................................................................</td>
      </tr>
      <tr>
        <td>NIDN</td>
        <td>:</td>
        <td>....................................................................................</td>
      </tr>
      <tr>
        <td>Jabatan</td>
        <td>:</td>
        <td>Ketua Program Studi <?= $mhs['prodi'] ?></td>
      </tr>
    </table>

    <div style="margin-top: 30px; font-size: 12pt;">
      Dengan ini menerangkan bahwa:
    </div>

    <table class="table-form" style="margin-top: 15px; margin-left: 0;">
      <tr>
        <td style="width: 150px;">Nama</td>
        <td style="width: 15px;">:</td>
        <td style="font-weight: bold;"><?= $mhs['nama'] ?></td>
      </tr>
      <tr>
        <td>TTL</td>
        <td>:</td>
        <td><?= $mhs['ttl'] ?></td>
      </tr>
      <tr>
        <td>NIM</td>
        <td>:</td>
        <td><?= $mhs['nim'] ?></td>
      </tr>
      <tr>
        <td>Angkatan</td>
        <td>:</td>
        <td><?= $mhs['angkatan'] ?></td>
      </tr>
      <tr>
        <td>Program Studi</td>
        <td>:</td>
        <td><?= $mhs['prodi'] ?></td>
      </tr>
      <tr>
        <td>Semester</td>
        <td>:</td>
        <td><?= $mhs['semester'] ?></td>
      </tr>
    </table>

    <div style="margin-top: 40px; font-size: 12pt; text-align: justify; line-height: 1.6;">
      telah <b>memenuhi luaran Syarat Lulus</b> berdasarkan hasil penilaian dan verifikasi dari program studi serta bukti pendukung yang telah diserahkan oleh mahasiswa.
    </div>

    <div style="margin-top: 40px; font-size: 11pt; font-style: italic; line-height: 1.4;">
      Catatan :<br>
      Harap melampirkan bukti pendukung (Contoh: Artikel Jurnal, link dll) saat menyerahkan surat ini.
    </div>

    <div style="display: flex; justify-content: flex-end; margin-top: 70px; font-size: 12pt;">
      <div style="width: 250px;">
        Sukabumi, <?= $tanggal ?><br>
        Ketua Program Studi<br>
        <div style="margin-top: 80px; border-bottom: 1px solid #000; width: 100%;"></div>
      </div>
    </div>
  </div>

  <div class="footer-img">
    <strong style="color: #961d5a;">NUSA PUTRA BUILDING</strong><br>
    Jl. Raya Cibatuh Cisaat No.21, Cibolang Kaler, Kec. Cisaat, Kabupaten Sukabumi, Jawa Barat 43152 &#9742; +62 266-210594 &#128423; +62 266-237287
  </div>
</div>

</div>

<!-- Loading overlay -->
<div id="loading" class="fixed inset-0 bg-slate-900/80 flex flex-col items-center justify-center z-50 text-white">
  <svg class="animate-spin -ml-1 mr-3 h-10 w-10 text-white mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
  </svg>
  <div class="font-bold text-xl">Menyiapkan Dokumen PDF...</div>
  <div class="text-slate-300 mt-2 text-sm">Mohon tunggu sebentar</div>
</div>

<?php
$emailBody = "Yth. Admin Administrasi Sidang Pascasarjana,\nUniversitas Nusa Putra\n\n";
$emailBody .= "Melalui email ini, saya bermaksud mengajukan pendaftaran Sidang Tesis. Berikut adalah data diri saya:\n\n";
$emailBody .= "- Nama Lengkap : " . $mhs['nama'] . "\n";
$emailBody .= "- NIM : " . $mhs['nim'] . "\n";
$emailBody .= "- Program Studi : " . $mhs['prodi'] . "\n";
$emailBody .= "- Angkatan : " . $mhs['angkatan'] . "\n";
$emailBody .= "- No. WhatsApp : " . $mhs['hp'] . "\n\n";
$emailBody .= "Adapun detail tesis saya adalah sebagai berikut:\n";
$emailBody .= "- Judul Tesis : " . $judul . "\n";
$emailBody .= "- Ketua Pembimbing : " . $mhs['pembimbing1'] . "\n";
if ($mhs['pembimbing2'] && $mhs['pembimbing2'] !== '-') {
    $emailBody .= "- Anggota Pembimbing : " . $mhs['pembimbing2'] . "\n\n";
} else {
    $emailBody .= "\n";
}
$emailBody .= "Bersama email ini, saya akan melampirkan berkas-berkas persyaratan pendaftaran sidang yang dibutuhkan, meliputi:\n";
$emailBody .= "1. Formulir Pendaftaran Sidang (File PDF hasil unduhan)\n";
$emailBody .= "2. File Jurnal / Manuskrip (Bukti Luaran/ACC/Sudah Publish)\n";
$emailBody .= "3. Bukti Pembayaran Jurnal & Sidang\n";
$emailBody .= "4. (Dan berkas kelengkapan administrasi lainnya sesuai ketentuan yang telah diceklis di sistem)\n\n";
$emailBody .= "Demikian pendaftaran ini saya sampaikan. Atas perhatian dan bantuannya, saya ucapkan terima kasih.\n\n";
$emailBody .= "Hormat saya,\n\n" . $mhs['nama'];
?>
<script>
window.onload = function() {
  const element = document.getElementById('pdf-content');
  const opt = {
    margin:       0,
    filename:     'Pendaftaran_Sidang_<?= str_replace(" ", "_", $mhs["nama"]) ?>.pdf',
    image:        { type: 'jpeg', quality: 0.98 },
    html2canvas:  { scale: 2, useCORS: true, logging: false },
    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
  };

  html2pdf().set(opt).from(element).save().then(() => {
    document.getElementById('loading').innerHTML = `
      <div class="text-emerald-400 text-6xl mb-4">✅</div>
      <div class="font-bold text-2xl text-white">PDF Berhasil Diunduh!</div>
      
      <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4 mt-4 max-w-md mx-auto relative overflow-hidden">
        <h4 class="font-bold text-blue-400 flex items-center justify-center gap-2 mb-2 text-lg">
          <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Informasi
        </h4>
        <p class="text-blue-100/90 text-sm text-center leading-relaxed">
          Wajib mengirimkan file PDF yang sudah didownload barusan ke email pendaftaran.
        </p>
      </div>

      <a href="mailto:sidang@nusaputra.ac.id,pascasarjana@nusaputra.ac.id?subject=Pendaftaran Sidang Tesis - <?= rawurlencode($mhs['nama']) ?> - <?= rawurlencode($mhs['nim']) ?>&body=<?= rawurlencode($emailBody) ?>" 
         class="mt-6 inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-white shadow-lg bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] hover:shadow-xl transition hover:-translate-y-0.5">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Buka Email (Gmail / Outlook)
      </a>
      <button onclick="window.close()" class="mt-5 text-sm text-slate-400 hover:text-white underline transition">Tutup Jendela Ini</button>
    `;
  }).catch(err => {
    console.error(err);
    document.getElementById('loading').innerHTML = '<div class="text-red-400 font-bold text-xl">Gagal membuat PDF. Silakan coba lagi.</div>';
  });
};
</script>
</body>
</html>
