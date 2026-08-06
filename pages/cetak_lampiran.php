<?php
/**
 * Cetak Lampiran Tesis (On-the-fly) - Editable
 * Render 8 dokumen dengan fitur editor TinyMCE
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

// Ambil data dari database (Riwayat) atau fallback ke session
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $data = dbQueryOne("SELECT * FROM riwayat_lampiran WHERE id=?", [$id]);
    if (!$data) {
        die("Data riwayat lampiran tidak ditemukan di database.");
    }
} else {
    $data = $_SESSION['temp_lampiran_tesis'] ?? null;
    if (!$data) {
        die("Akses ditolak atau data tidak ditemukan. Harap buka melalui menu Berkas & Lampiran.");
    }
}

// Cek akses: Admin boleh semua, Mahasiswa hanya boleh datanya sendiri
$user = getCurrentUser();
$is_admin = isSuperAdmin() || (isset($user['role']) && $user['role'] === 'admin');
$is_mhs = isset($user['nim']);

if (!$is_admin && $is_mhs) {
    if ($data['nim_mhs'] !== $user['nim']) {
        die("Akses ditolak. Anda hanya dapat melihat lampiran milik Anda sendiri.");
    }
} elseif (!$is_admin && !$is_mhs) {
    die("Akses ditolak.");
}

$idxParam         = $_GET['idx'] ?? 'all';

$prodiId          = (int)$data['prodi_id'];
$konsentrasi      = trim($data['konsentrasi'] ?? 'Manajemen Pendidikan');
$namaMhs          = trim($data['nama_mhs']);
$nimMhs           = trim($data['nim_mhs']);
$judulTesis       = trim($data['judul_tesis']);
$tanggalSidang    = $data['tanggal_sidang'];
$ketuaPembimbing  = trim($data['ketua_pembimbing']);
$anggotaPembimbing= trim($data['anggota_pembimbing']);
$ketuaPenguji     = trim($data['ketua_penguji']);
$anggotaPenguji   = trim($data['anggota_penguji']);

$tanggalSurat     = date('Y-m-d');
$hariSidang       = getNamaHari($tanggalSidang);
$formattedTanggalSidang = $hariSidang . ', ' . formatTanggal($tanggalSidang);

$prodiData = dbQueryOne("SELECT p.*, p.kaprodi as nama_kaprodi, '' as nidn_kaprodi FROM prodi p WHERE p.id=?", [$prodiId]);
$kota = $prodiData['kota_surat'] ?: 'Sukabumi';
$tglSurat = formatTanggalSurat($tanggalSurat, $kota);

$kode = $prodiData['kode'];
$kopUrl = getKopPath($kode, true);
$footerUrl = getFooterPath($kode, true);
$ttdUrl = getTtdUrl($kode);
$capUrl = getCapUrl($kode);

// --- HELPER UNTUK MEMBUAT FORM PENILAIAN ---
function getTtdDosen($nama) {
    if (!$nama) return '<br><br><br>';
    $n = strtolower($nama);
    $file = '';
    if (strpos($n, 'yusuf') !== false) $file = 'Ttd Dr. Yusuf.png';
    elseif (strpos($n, 'hesri') !== false) $file = 'Ttd Dr. Hesri.png';
    elseif (strpos($n, 'dana') !== false) $file = 'Ttd Dr. Dana.png';
    elseif (strpos($n, 'slamet') !== false) $file = 'Ttd Dr. Slamet.png';
    elseif (strpos($n, 'gustian') !== false) $file = 'Gustian.png';
    elseif (strpos($n, 'koesmawan') !== false) $file = 'Ttd Dr. Koesmawan.png';
    elseif (strpos($n, 'hasan') !== false || strpos($n, 'nur') !== false) $file = 'Ttd_Dr_Nur_Hasan.png';
    elseif (strpos($n, 'kurniawan') !== false) $file = 'Ttd_Dr_Kurniawan.png';
    if ($file) {
        return '<img src="../TTD%20Dosen/TTD%20Dosen%20Manajemen/' . rawurlencode($file) . '?v=' . time() . '" style="height:85px; display:block; margin:-15px 0 -15px 0; object-fit:contain; position:relative; z-index:1; mix-blend-mode:multiply;">';
    }
    return '<br><br><br>';
}

function generateFormPenilaian($lampiranTitle, $peran, $peranEn, $namaDosen, $namaMhs, $nimMhs, $konsentrasi, $prodi, $judulTesis, $tgl) {
    $ttdHtml = getTtdDosen($namaDosen);
    $maroon = '#961D5A';
    $bdr = 'border:0.5pt solid #000;';

    return "
    <div style=\"font-family:Rockwell,'Courier New',serif; font-size:12pt; line-height:1.3; color:#000;\">

    <p style=\"margin:0; font-size:12pt;\">&nbsp;</p>
    <p style=\"text-align:center; font-size:14pt; font-weight:bold; margin:6pt 0 16pt 0;\">$lampiranTitle</p>

    <table style=\"width:93.7%; border-collapse:collapse; border:none; font-size:12pt; margin-bottom:6pt;\">
        <col style=\"width:30.9%;\"><col style=\"width:3.8%;\"><col style=\"width:65.3%;\">
        <tr>
            <td width=\"30.9%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\"><b>Nama Mahasiswa / </b><br><i>Student Name</i></td>
            <td width=\"3.8%\" style=\"vertical-align:top; padding:2pt 4pt; border:none; text-align:center; font-size:12pt;\"><b>:</b></td>
            <td width=\"65.3%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\">$namaMhs</td>
        </tr>
        <tr>
            <td width=\"30.9%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\"><b>NIM / </b><i>Student ID</i></td>
            <td width=\"3.8%\" style=\"vertical-align:top; padding:2pt 4pt; border:none; text-align:center; font-size:12pt;\"><b>:</b></td>
            <td width=\"65.3%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\">$nimMhs</td>
        </tr>
        <tr>
            <td width=\"30.9%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\"><b>Konsentrasi / </b><br><i>Concentration</i></td>
            <td width=\"3.8%\" style=\"vertical-align:top; padding:2pt 4pt; border:none; text-align:center; font-size:12pt;\"><b>:</b></td>
            <td width=\"65.3%\" class=\"editable-cell\" contenteditable=\"true\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\">$konsentrasi</td>
        </tr>
        <tr>
            <td width=\"30.9%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\"><b>Program Studi / </b><br><i>Study Program</i></td>
            <td width=\"3.8%\" style=\"vertical-align:top; padding:2pt 4pt; border:none; text-align:center; font-size:12pt;\"><b>:</b></td>
            <td width=\"65.3%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\">$prodi</td>
        </tr>
        <tr>
            <td width=\"30.9%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\"><b>Judul Tesis / </b><br><i>Thesis Title</i></td>
            <td width=\"3.8%\" style=\"vertical-align:top; padding:2pt 4pt; border:none; text-align:center; font-size:12pt;\"><b>:</b></td>
            <td width=\"65.3%\" style=\"vertical-align:top; padding:2pt 0; border:none; text-align:justify; font-size:12pt;\">$judulTesis</td>
        </tr>
        <tr>
            <td width=\"30.9%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\"><b>$peran</b><br><i>$peranEn</i></td>
            <td width=\"3.8%\" style=\"vertical-align:top; padding:2pt 4pt; border:none; text-align:center; font-size:12pt;\"><b>:</b></td>
            <td width=\"65.3%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\">$namaDosen</td>
        </tr>
        <tr>
            <td width=\"30.9%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\"><b>Hari dan Tanggal / </b><br><i>Day and Date</i></td>
            <td width=\"3.8%\" style=\"vertical-align:top; padding:2pt 4pt; border:none; text-align:center; font-size:12pt;\"><b>:</b></td>
            <td width=\"65.3%\" style=\"vertical-align:top; padding:2pt 0; border:none; font-size:12pt;\">$tgl</td>
        </tr>
    </table>

    <p style=\"font-size:12pt; margin:12pt 0 6pt 0; text-align:center;\"><b>Tabel 1. </b>Penilaian Sidang Akhir Tesis</p>

    <table align=\"center\" style=\"width:111.5%; margin-left:-5.75%; border-collapse:collapse; font-size:12pt; margin-bottom:8pt;\">
        <thead>
            <tr>
                <th width=\"39.5%\" bgcolor=\"#961D5A\" style=\"padding:5pt; text-align:center; vertical-align:middle; $bdr font-size:11pt;\"><span style=\"color:#ffffff;\">Aspek Penilaian / Assessment Criteria</span></th>
                <th width=\"7.9%\" bgcolor=\"#961D5A\" style=\"padding:5pt; text-align:center; vertical-align:middle; $bdr font-size:11pt;\"><span style=\"color:#ffffff;\">Bobot</span></th>
                <th width=\"7.9%\" bgcolor=\"#961D5A\" style=\"padding:5pt; text-align:center; vertical-align:middle; $bdr font-size:11pt;\"><span style=\"color:#ffffff;\">Skor (1&ndash;5)</span></th>
                <th width=\"9.2%\" bgcolor=\"#961D5A\" style=\"padding:5pt; text-align:center; vertical-align:middle; $bdr font-size:11pt;\"><span style=\"color:#ffffff;\">Nilai</span></th>
                <th width=\"35.4%\" bgcolor=\"#961D5A\" style=\"padding:5pt; text-align:center; vertical-align:middle; $bdr font-size:11pt;\"><span style=\"color:#ffffff;\">Catatan Perbaikan</span></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width=\"39.5%\" style=\"padding:5pt; $bdr vertical-align:middle;\">
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:2pt; font-size:11pt;\"><b>Signifikansi Latar Belakang Riset dan/atau Fokus Riset, dan Rumusan Masalah</b></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Konsistensi rumusan masalah dengan hasil penelitian yang dilaporkan</i></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Ketajaman justifikasi urgensi penelitian dalam naskah akhir</i></p>
                </td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\">10%</td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"9.2%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"35.4%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\" class=\"editable-cell\" contenteditable=\"true\">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;</td>
            </tr>
            <tr>
                <td width=\"39.5%\" style=\"padding:5pt; $bdr vertical-align:middle;\">
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:2pt; font-size:11pt;\"><b>Relevansi dan Kemutakhiran Tinjauan Pustaka</b></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Kemutakhiran dan relevansi sumber rujukan dengan topik akhir penelitian</i></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Ketepatan posisi penelitian terhadap state of the art</i></p>
                </td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\">10%</td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"9.2%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"35.4%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\" class=\"editable-cell\" contenteditable=\"true\">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;</td>
            </tr>
            <tr>
                <td width=\"39.5%\" style=\"padding:5pt; $bdr vertical-align:middle;\">
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:2pt; font-size:11pt;\"><b>Ketepatan Formulasi Kerangka Pemikiran dan Proposisi Riset/Hipotesis</b></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Konsistensi kerangka konseptual/teoritis dengan hasil penelitian</i></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Ketepatan pengujian/pembuktian hipotesis atau proposisi</i></p>
                </td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\">10%</td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"9.2%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"35.4%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\" class=\"editable-cell\" contenteditable=\"true\">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;</td>
            </tr>
            <tr>
                <td width=\"39.5%\" style=\"padding:5pt; $bdr vertical-align:middle;\">
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:2pt; font-size:11pt;\"><b>Kesesuaian Metode Riset</b></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Ketepatan penerapan metode dan prosedur penelitian</i></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Kesesuaian instrumen dan teknik analisis data dengan tujuan penelitian</i></p>
                </td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\">20%</td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"9.2%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"35.4%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\" class=\"editable-cell\" contenteditable=\"true\">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;</td>
            </tr>
            <tr>
                <td width=\"39.5%\" style=\"padding:5pt; $bdr vertical-align:middle;\">
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:2pt; font-size:11pt;\"><b>Ketajaman Analisis dan Keutuhan Pemikiran</b></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Kedalaman dan ketajaman analisis data/temuan penelitian</i></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Keutuhan dan koherensi alur berpikir dari rumusan masalah hingga simpulan</i></p>
                </td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\">20%</td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"9.2%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"35.4%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\" class=\"editable-cell\" contenteditable=\"true\">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;</td>
            </tr>
            <tr>
                <td width=\"39.5%\" style=\"padding:5pt; $bdr vertical-align:middle;\">
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:2pt; font-size:11pt;\"><b>Kemantapan dan Mutu Penyimpulan, serta Saran-Saran yang Diajukan</b></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Ketepatan simpulan dalam menjawab rumusan masalah</i></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Kebermanfaatan dan kelayakan saran/rekomendasi yang diajukan</i></p>
                </td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\">10%</td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"9.2%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"35.4%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\" class=\"editable-cell\" contenteditable=\"true\">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;</td>
            </tr>
            <tr>
                <td width=\"39.5%\" style=\"padding:5pt; $bdr vertical-align:middle;\">
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:2pt; font-size:11pt;\"><b>Kemampuan Penulisan Ilmiah</b></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Sistematika dan kejelasan penyajian naskah tesis</i></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Kebenaran tata bahasa, ejaan, dan konsistensi sitasi/daftar pustaka</i></p>
                </td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\">10%</td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"9.2%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"35.4%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\" class=\"editable-cell\" contenteditable=\"true\">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;</td>
            </tr>
            <tr>
                <td width=\"39.5%\" style=\"padding:5pt; $bdr vertical-align:middle;\">
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:2pt; font-size:11pt;\"><b>Kemampuan Komunikasi dalam Ujian Lisan</b></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Kejelasan dan ketepatan presentasi hasil penelitian</i></p>
                    <p style=\"text-align:center; margin-top:0pt; margin-bottom:0pt;\"><i style=\"font-size:9pt; color:#555555;\">&bull; Kemampuan mempertanggungjawabkan hasil penelitian dalam tanya jawab</i></p>
                </td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\">10%</td>
                <td width=\"7.9%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"9.2%\" style=\"text-align:center; vertical-align:middle; $bdr\" class=\"editable-cell\" contenteditable=\"true\"></td>
                <td width=\"35.4%\" style=\"text-align:center; vertical-align:middle; $bdr font-size:11pt;\" class=\"editable-cell\" contenteditable=\"true\">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;</td>
            </tr>
            <tr>
                <td colspan=\"3\" width=\"55.3%\" style=\"padding:10pt 5pt; $bdr text-align:center; vertical-align:middle; font-size:12pt;\"><b>Total Nilai</b></td>
                <td width=\"9.2%\" class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; vertical-align:middle; $bdr font-size:12pt; padding:10pt 5pt;\"><b>= ....</b></td>
                <td width=\"35.4%\" style=\"$bdr\"></td>
            </tr>
        </tbody>
    </table>

    <p style=\"font-size:12pt; margin:10pt 0 4pt 0;\"><b>Cara mengisi Skor: </b>Berikan skor 1&ndash;5 pada kolom Skor untuk setiap aspek.</p>
    <p style=\"font-size:12pt; margin:0 0 10pt 0;\"><b>Cara menghitung Nilai: </b>Contoh <b>Skor 4, Bobot 20% &rarr; (4&divide;5)&times;20 = Nilai 16</b></p>

    <p style=\"font-size:12pt; margin-bottom:5pt;\"><b>Catatan Revisi Tambahan:</b></p>
    <div class=\"editable-cell\" contenteditable=\"true\" style=\"margin-bottom:0pt; font-size:12pt;\">
        <p style=\"margin:0 0 0 0; word-break:break-all; letter-spacing:0; font-size:12pt;\">...........................................................................................................................................................................................................................................................................................................................................................................................................................</p>
    </div>
    <br><br>

    <table style=\"width:100%; border-collapse:collapse; border:none; margin-bottom:16pt;\">
        <tr style=\"vertical-align:top;\">
            <td style=\"width:50%; border:none; padding:0;\">
                <p style=\"font-size:11pt; font-weight:bold; margin-bottom:5pt; text-align:left;\">Tabel 2. Kriteria Penilaian Skor</p>
                <table style=\"width:71%; border-collapse:collapse; font-size:11pt; margin:0;\">
                    <col style=\"width:37.7%;\"><col style=\"width:62.3%;\">
                    <tr><th colspan=\"2\" bgcolor=\"#961D5A\" style=\"padding:2pt 4pt; text-align:center; $bdr\"><span style=\"color:#ffffff;\">Keterangan Skor</span></th></tr>
                    <tr bgcolor=\"#FAF2F5\"><td style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\">Skor 1</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">Sangat Kurang</td></tr>
                    <tr bgcolor=\"#FFFFFF\"><td style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\">Skor 2</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">Kurang</td></tr>
                    <tr bgcolor=\"#FAF2F5\"><td style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\">Skor 3</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">Cukup</td></tr>
                    <tr bgcolor=\"#FFFFFF\"><td style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\">Skor 4</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">Baik</td></tr>
                    <tr bgcolor=\"#FAF2F5\"><td style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\">Skor 5</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">Sangat Baik</td></tr>
                </table>
            </td>
            <td style=\"width:50%; border:none; padding:0;\">
                <p style=\"font-size:11pt; font-weight:bold; margin-bottom:5pt; text-align:center;\">Tabel 3. Nilai dan Huruf Mutu</p>
                <table style=\"width:92.5%; border-collapse:collapse; font-size:11pt; margin:0;\">
                    <col style=\"width:19.8%;\"><col style=\"width:26.1%;\"><col style=\"width:19.8%;\"><col style=\"width:34.3%;\">
                    <tr><th colspan=\"4\" bgcolor=\"#961D5A\" style=\"padding:2pt 4pt; text-align:center; $bdr\"><span style=\"color:#ffffff;\">Konversi Huruf Mutu</span></th></tr>
                    <tr>
                        <th bgcolor=\"#961D5A\" style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\"><span style=\"color:#ffffff;\">Huruf</span></th>
                        <th bgcolor=\"#961D5A\" style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\"><span style=\"color:#ffffff;\">Rentang</span></th>
                        <th bgcolor=\"#961D5A\" style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\"><span style=\"color:#ffffff;\">Bobot</span></th>
                        <th bgcolor=\"#961D5A\" style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\"><span style=\"color:#ffffff;\">Keterangan</span></th>
                    </tr>
                    <tr bgcolor=\"#FAF2F5\"><td style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\">A</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">90&ndash;100</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">4</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">Lulus</td></tr>
                    <tr bgcolor=\"#FFFFFF\"><td style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\">A-</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">85&ndash;89,99</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">3,67</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">Lulus</td></tr>
                    <tr bgcolor=\"#FAF2F5\"><td style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\">B+</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">80&ndash;84,99</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">3,33</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">Lulus</td></tr>
                    <tr bgcolor=\"#FFFFFF\"><td style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\">B</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">75&ndash;79,99</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">3</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">Lulus</td></tr>
                    <tr bgcolor=\"#FAF2F5\"><td style=\"padding:2pt 4pt; $bdr text-align:center; font-weight:bold;\">B-</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">70&ndash;74,99</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">2,67</td><td style=\"padding:2pt 4pt; $bdr text-align:center;\">Tidak Lulus</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div style=\"margin-top:60pt; page-break-inside:avoid;\">
    <p style=\"font-size:12pt; margin-bottom:0pt;\">$peran,</p>
    <p style=\"font-size:12pt; margin-top:48pt; margin-bottom:0;\">$namaDosen</p>
    </div>
    </div>
    ";
}

$allLampiran = [
    [
        'isi' => generateFormPenilaian('Form Penilaian &amp; Revisi Sidang Akhir Tesis<br>Ketua Pembimbing', 'Ketua Pembimbing', 'Principal Supervisor', $ketuaPembimbing, $namaMhs, $nimMhs, $konsentrasi, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)
    ],
    [
        'isi' => generateFormPenilaian('Form Penilaian &amp; Revisi Sidang Akhir Tesis<br>Anggota Pembimbing', 'Anggota Pembimbing', 'Co-Supervisor', $anggotaPembimbing, $namaMhs, $nimMhs, $konsentrasi, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)
    ],
    [
        'isi' => generateFormPenilaian('Form Penilaian &amp; Revisi Sidang Akhir Tesis<br>Ketua Penguji', 'Ketua Penguji', 'Chief Examiner', $ketuaPenguji, $namaMhs, $nimMhs, $konsentrasi, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)
    ],
    [
        'isi' => generateFormPenilaian('Form Penilaian &amp; Revisi Sidang Akhir Tesis<br>Anggota Penguji', 'Anggota Penguji', 'Examiner', $anggotaPenguji, $namaMhs, $nimMhs, $konsentrasi, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)
    ],
[ // Lampiran 5: Berita Acara
        'isi' => "
        <div style=\"font-family:'Rockwell',serif; font-size:12pt; color:#000000;\">

        <!-- Judul: 14pt, bold, center, no extra margin -->
        <p style=\"margin:0; line-height:1.15;\">&nbsp;</p>
        <h3 style=\"text-align:center; font-size:14pt; font-weight:bold; font-family:'Rockwell',serif; margin-top:0; margin-bottom:0;\">Berita Acara Penilaian Sidang Tesis</h3>
        <p style=\"margin:0; line-height:1.15;\">&nbsp;</p>
        <p style=\"margin:0; line-height:1.15;\">&nbsp;</p>

        <!-- Paragraf pembuka: 12pt, left, line-height 1x -->
        <p style=\"font-size:12pt; font-family:'Rockwell',serif; text-align:justify; margin:0 0 4pt 0; line-height:1.15;\">
            Pada hari " . $formattedTanggalSidang . ", telah dilaksanakan Sidang Tesis Program Studi " . $prodiData['nama'] . " Nusa Putra University, dengan mahasiswa sebagai berikut:
        </p>
        <p style=\"margin:0; line-height:1.15;\">&nbsp;</p>

        <!-- Tabel Biodata: padding 1.41mm T/B dan 2.29mm L/R, kolom 30.88%|3.82%|65.3% -->
        <table style=\"width:100%; border:none; border-collapse:collapse; font-family:'Rockwell',serif; font-size:12pt; margin-bottom:4pt;\">
            <tr>
                <td style=\"width:30.88%; vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222;\">Nama Mahasiswa / <i style=\"font-weight:normal;\">Student Name</i></td>
                <td style=\"width:3.82%; vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222; text-align:center;\">:</td>
                <td style=\"width:65.3%; vertical-align:top; padding:0.8mm 2mm; color:#000000;\">$namaMhs</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222;\">NIM / <i style=\"font-weight:normal;\">Student ID</i></td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222; text-align:center;\">:</td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; color:#000000;\">$nimMhs</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; font-style:italic; color:#222222;\">Konsentrasi / <span style=\"font-style:italic;\">Concentration</span></td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222; text-align:center;\">:</td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; color:#000000;\" class=\"editable-cell\" contenteditable=\"true\">$konsentrasi</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222;\">Program Studi / <i style=\"font-weight:normal;\">Study Program</i></td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222; text-align:center;\">:</td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; color:#000000;\">" . $prodiData['nama'] . "</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222;\">Judul Tesis / <i style=\"font-weight:normal;\">Thesis Title</i></td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222; text-align:center;\">:</td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; color:#000000; text-align:justify;\">$judulTesis</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222;\">Hari dan Tanggal / <i style=\"font-weight:normal;\">Day and Date</i></td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222; text-align:center;\">:</td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; color:#000000;\">$formattedTanggalSidang</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222;\">Tahun Akademik / <i style=\"font-weight:normal;\">Academic Year</i></td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; font-weight:bold; color:#222222; text-align:center;\">:</td>
                <td style=\"vertical-align:top; padding:0.8mm 2mm; color:#000000;\" class=\"editable-cell\" contenteditable=\"true\">Semester Genap 2025/2026</td>
            </tr>
        </table>

        <p style=\"margin:0; line-height:1.15;\">&nbsp;</p>
        <!-- Label Tabel 1: 12pt, bold, spacing-after 10pt -->
        <p style=\"font-size:12pt; font-family:'Rockwell',serif; font-weight:bold; text-align:center; margin:0 0 10pt 0;\">Tabel 1. <span style=\"font-weight:normal;\">Rekapitulasi Penilaian Sidang Tesis</span></p>

        <!-- Tabel Penilaian: padding 1.41mm T/B, 2.12mm L/R, header #961D5A/#FFFFFF, data #2C2C2C -->
        <table style=\"width:100%; border-collapse:collapse; font-family:'Rockwell',serif; font-size:12pt; margin-bottom:10pt;\">
            <thead>
                <tr style=\"background-color:#961D5A; color:#FFFFFF; font-weight:bold; text-align:center;\">
                    <th style=\"width:5.99%; padding:0.8mm 2mm; border:0.5pt solid #000000;\">No</th>
                    <th style=\"width:34.6%; padding:0.8mm 2mm; border:0.5pt solid #000000;\">Nama / <i>Name</i></th>
                    <th colspan=\"2\" style=\"width:20.6%; padding:0.8mm 2mm; border:0.5pt solid #000000;\">Peran / <i>Role</i></th>
                    <th style=\"width:19.4%; padding:0.8mm 2mm; border:0.5pt solid #000000;\">Tim</th>
                    <th style=\"width:19.4%; padding:0.8mm 2mm; border:0.5pt solid #000000;\">Nilai (0&ndash;100)</th>
                </tr>
            </thead>
            <tbody style=\"text-align:center; color:#2C2C2C;\">
                <tr>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">1</td>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000; color:#000000;\">$ketuaPembimbing</td>
                    <td colspan=\"2\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">Ketua Pembimbing</td>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">Pembimbing (30%)</td>
                    <td class=\"editable-cell\" contenteditable=\"true\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\"></td>
                </tr>
                <tr>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">2</td>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000; color:#000000;\">$anggotaPembimbing</td>
                    <td colspan=\"2\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">Anggota Pembimbing</td>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">Pembimbing (30%)</td>
                    <td class=\"editable-cell\" contenteditable=\"true\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\"></td>
                </tr>
                <tr>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">3</td>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000; color:#000000;\">$ketuaPenguji</td>
                    <td colspan=\"2\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">Ketua Penguji</td>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">Penguji (20%)</td>
                    <td class=\"editable-cell\" contenteditable=\"true\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\"></td>
                </tr>
                <tr>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">4</td>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000; color:#000000;\">$anggotaPenguji</td>
                    <td colspan=\"2\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">Anggota Penguji</td>
                    <td style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\">Penguji (20%)</td>
                    <td class=\"editable-cell\" contenteditable=\"true\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\"></td>
                </tr>
                <tr style=\"font-weight:bold;\">
                    <td colspan=\"5\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000; text-align:center;\">NILAI AKHIR SIDANG TESIS / <i>Final Grade</i></td>
                    <td class=\"editable-cell\" contenteditable=\"true\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\"></td>
                </tr>
                <tr style=\"font-weight:bold;\">
                    <td colspan=\"5\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000; text-align:center;\">HURUF MUTU / <i>Letter Grade</i></td>
                    <td class=\"editable-cell\" contenteditable=\"true\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000;\"></td>
                </tr>
                <tr style=\"font-weight:bold; color:#000000;\">
                    <td colspan=\"3\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000; text-align:center;\">KEPUTUSAN SIDANG / <br><i>Examination Decision</i></td>
                    <td colspan=\"3\" class=\"editable-cell\" contenteditable=\"true\" style=\"padding:0.8mm 2mm; border:0.5pt solid #000000; text-align:center;\">&#9744; LULUS &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &#9744; TIDAK LULUS</td>
                </tr>
            </tbody>
        </table>

        <!-- Label Tanda tangan: 12pt, center, spacing-after 10pt -->
        <p style=\"margin:0; line-height:1.15;\">&nbsp;</p>
        <p style=\"margin:0; line-height:1.15;\">&nbsp;</p>
        <p style=\"font-size:12pt; font-family:'Rockwell',serif; text-align:center; margin:0;\">Tanda tangan:</p>
        <p style=\"font-size:12pt; margin:0; line-height:1.15;\">&nbsp;</p>
        <p style=\"font-size:12pt; margin:0; line-height:1.15;\">&nbsp;</p>

        <!-- Tabel TTD: 3 baris, col 45.5%|2.2%|52.3%, no border, spacing-after ~10pt per nama -->
        <table style=\"width:100%; border:none; border-collapse:collapse; font-family:'Rockwell',serif; font-size:12pt; color:#000000; margin-bottom:5pt;\">
            <tr style=\"vertical-align:top;\">
                <td style=\"width:45.5%; border:none; text-align:center; vertical-align:top; padding-bottom:0;\"><b>Ketua Pembimbing,</b><br>" . getTtdDosen($ketuaPembimbing) . "$ketuaPembimbing</td>
                <td style=\"width:2.2%; border:none;\"></td>
                <td style=\"width:52.3%; border:none; text-align:center; vertical-align:top; padding-bottom:0;\"><b>Anggota Pembimbing,</b><br>" . getTtdDosen($anggotaPembimbing) . "$anggotaPembimbing</td>
            </tr>
            <tr><td colspan=\"3\" style=\"height:36pt; border:none; line-height:36pt; mso-line-height-rule:exactly;\">&nbsp;</td></tr>
            <tr style=\"vertical-align:top;\">
                <td style=\"border:none; text-align:center; vertical-align:top; padding-bottom:0;\"><b>Ketua Penguji,</b><br>" . getTtdDosen($ketuaPenguji) . "$ketuaPenguji</td>
                <td style=\"border:none;\"></td>
                <td style=\"border:none; text-align:center; vertical-align:top; padding-bottom:0;\"><b>Anggota Penguji,</b><br>" . getTtdDosen($anggotaPenguji) . "$anggotaPenguji</td>
            </tr>
            <tr><td colspan=\"3\" style=\"height:36pt; border:none; line-height:36pt; mso-line-height-rule:exactly;\">&nbsp;</td></tr>
            <tr>
                <td colspan=\"3\" style=\"border:none; text-align:center; padding-bottom:0;\"><b>Mengetahui,</b><br><b>Ketua Program Studi " . e($prodiData['nama']) . "</b><br><br>" . getTtdDosen($prodiData['nama_kaprodi']) . e($prodiData['nama_kaprodi']) . "</td>
            </tr>
        </table>

        </div>
        "
    ],
    [ // Lampiran 6: Lembar Pengesahan Revisi (sesuai DOCX)
        'isi' => "
        <div style=\"font-family:'Rockwell',serif; color:#000000;\">
        <h3 style=\"font-size:14pt; margin-top:10pt; margin-bottom:0; font-weight:bold; text-align:center;\">Lembar Pengesahan Revisi</h3>
        <p style=\"font-size:12pt; margin:0; line-height:1.15;\">&nbsp;</p>
        
        <table style=\"width:100%; border:none; border-collapse:collapse; font-size:12pt; font-family:'Rockwell',serif; color:#000000;\">
            <tr>
                <td style=\"width:22%; vertical-align:top; padding:0; border:none; line-height:1.15;\">Nama Mahasiswa</td>
                <td style=\"width:2%; vertical-align:top; padding:0; border:none; line-height:1.15;\">:</td>
                <td style=\"vertical-align:top; padding:0; border:none; line-height:1.15;\">$namaMhs</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:0; border:none; line-height:1.15;\">NIM</td>
                <td style=\"vertical-align:top; padding:0; border:none; line-height:1.15;\">:</td>
                <td style=\"vertical-align:top; padding:0; border:none; line-height:1.15;\">$nimMhs</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:0; border:none; line-height:1.15;\">Judul Tesis</td>
                <td style=\"vertical-align:top; padding:0; border:none; line-height:1.15;\">:</td>
                <td style=\"vertical-align:top; padding:0; border:none; text-align:justify; line-height:1.15;\">$judulTesis</td>
            </tr>
        </table>
        <p style=\"font-size:12pt; margin:0; line-height:1.15;\">&nbsp;</p>
        
        <p style=\"font-size:12pt; margin:0; text-align:left;\">Telah menyelesaikan revisi sesuai catatan dari pembimbing dan penguji.</p>
        <p style=\"font-size:12pt; margin:0; line-height:1.15;\">&nbsp;</p>
        
        <p style=\"font-size:12pt; margin:0; text-align:center;\">Tanda tangan:</p>
        <p style=\"font-size:12pt; margin:0; line-height:1.15;\">&nbsp;</p>
        
        <table style=\"width:100%; border:none; border-collapse:collapse; font-size:12pt; font-family:'Rockwell',serif; color:#000000;\">
            <tr style=\"vertical-align:top;\">
                <td style=\"width:47%; border:none; text-align:center; padding-bottom:12pt;\">
                    <b>Ketua Pembimbing,</b><br><br>
                    " . getTtdDosen($ketuaPembimbing) . "
                    $ketuaPembimbing
                </td>
                <td style=\"width:3%; border:none;\"></td>
                <td style=\"width:50%; border:none; text-align:center; padding-bottom:12pt;\">
                    <b>Anggota Pembimbing,</b><br><br>
                    " . getTtdDosen($anggotaPembimbing) . "
                    $anggotaPembimbing
                </td>
            </tr>
            <tr><td colspan=\"3\" style=\"height:18pt; border:none; line-height:18pt; mso-line-height-rule:exactly;\">&nbsp;</td></tr>
            <tr style=\"vertical-align:top;\">
                <td style=\"width:47%; border:none; text-align:center; padding-bottom:12pt;\">
                    <b>Ketua Penguji,</b><br><br>
                    " . getTtdDosen($ketuaPenguji) . "
                    $ketuaPenguji
                </td>
                <td style=\"width:3%; border:none;\"></td>
                <td style=\"width:50%; border:none; text-align:center; padding-bottom:12pt;\">
                    <b>Anggota Penguji,</b><br><br>
                    " . getTtdDosen($anggotaPenguji) . "
                    $anggotaPenguji
                </td>
            </tr>
            <tr><td colspan=\"3\" style=\"height:18pt; border:none; line-height:18pt; mso-line-height-rule:exactly;\">&nbsp;</td></tr>
            <tr style=\"vertical-align:top;\">
                <td colspan=\"3\" style=\"border:none; text-align:center;\">
                    <b>Mengetahui,</b><br>
                    <b>Ketua Program Studi " . e($prodiData['nama']) . "</b><br><br>
                    " . getTtdDosen($prodiData['nama_kaprodi']) . "
                    " . e($prodiData['nama_kaprodi']) . "
                </td>
            </tr>
        </table>
        
        </div>
        "
    ],
    [ // Lampiran 7: Logbook Bimbingan Tesis (sesuai DOCX)
        'isi' => "
        <div style=\"font-family:Rockwell,serif;\">
        <p style=\"font-size:10pt; margin-bottom:10px; font-weight:bold;\">LOGBOOK BIMBINGAN TESIS</p>

        <table style=\"width:100%; margin-bottom:10px; font-size:10pt; font-family:Rockwell,serif; border:none; border-collapse:collapse;\">
            <tr>
                <td style=\"width:38%; vertical-align:top; padding:4px 0; border:none; font-weight:bold;\">Nama Mahasiswa / <i>Student Name</i></td>
                <td style=\"width:2%; vertical-align:top; padding:4px 6px; border:none; font-weight:bold;\">:</td>
                <td style=\"vertical-align:top; padding:4px 0; border:none;\">$namaMhs</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:4px 0; border:none; font-weight:bold;\">NIM / <i>Student ID</i></td>
                <td style=\"vertical-align:top; padding:4px 6px; border:none; font-weight:bold;\">:</td>
                <td style=\"vertical-align:top; padding:4px 0; border:none;\">$nimMhs</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:4px 0; border:none; font-weight:bold;\">Konsentrasi / <i>Concentration</i></td>
                <td style=\"vertical-align:top; padding:4px 6px; border:none; font-weight:bold;\">:</td>
                <td style=\"vertical-align:top; padding:4px 0; border:none;\" class=\"editable-cell\" contenteditable=\"true\">$konsentrasi</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:4px 0; border:none; font-weight:bold;\">Program Studi / <br><i>Study Program</i></td>
                <td style=\"vertical-align:top; padding:4px 6px; border:none; font-weight:bold;\">:</td>
                <td style=\"vertical-align:top; padding:4px 0; border:none;\">" . $prodiData['nama'] . "</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:4px 0; border:none; font-weight:bold;\">Judul Tesis / <i>Thesis Title</i></td>
                <td style=\"vertical-align:top; padding:4px 6px; border:none; font-weight:bold;\">:</td>
                <td style=\"vertical-align:top; padding:4px 0; text-align:justify; border:none;\">$judulTesis</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:4px 0; border:none; font-weight:bold;\">Ketua Pembimbing / <i>Principal Supervisor</i></td>
                <td style=\"vertical-align:top; padding:4px 6px; border:none; font-weight:bold;\">:</td>
                <td style=\"vertical-align:top; padding:4px 0; border:none;\">$ketuaPembimbing</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:4px 0; border:none; font-weight:bold;\">Anggota Pembimbing / <i>Co-Supervisor</i></td>
                <td style=\"vertical-align:top; padding:4px 6px; border:none; font-weight:bold;\">:</td>
                <td style=\"vertical-align:top; padding:4px 0; border:none;\">$anggotaPembimbing</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:4px 0; border:none; font-weight:bold;\">Hari dan Tanggal / <i>Day and Date</i></td>
                <td style=\"vertical-align:top; padding:4px 6px; border:none; font-weight:bold;\">:</td>
                <td style=\"vertical-align:top; padding:4px 0; border:none;\">$formattedTanggalSidang</td>
            </tr>
            <tr>
                <td style=\"vertical-align:top; padding:4px 0; border:none; font-weight:bold;\">Tahun Akademik / <i>Academic Year</i></td>
                <td style=\"vertical-align:top; padding:4px 6px; border:none; font-weight:bold;\">:</td>
                <td class=\"editable-cell\" contenteditable=\"true\" style=\"vertical-align:top; padding:4px 0; border:none;\">Semester Genap 2025/2026</td>
            </tr>
        </table>

        <p style=\"margin-bottom:6px; font-size:10pt;\"><b>Tabel 1.</b> Rekap Bimbingan Tesis</p>
        <table border=\"1\" style=\"width:100%; font-size:10pt; font-family:Rockwell,serif; margin-bottom:15px; border-collapse:collapse; border:1px solid #000;\">
            <thead>
                <tr>
                    <th style=\"width:6%; padding:4px; border:1px solid #000; font-weight:bold;\">No.</th>
                    <th style=\"width:22%; padding:4px; border:1px solid #000; font-weight:bold;\">Hari / Tanggal</th>
                    <th style=\"width:52%; padding:4px; border:1px solid #000; font-weight:bold;\">Catatan &amp; Saran Pembimbing</th>
                    <th style=\"width:20%; padding:4px; border:1px solid #000; font-weight:bold;\">Paraf</th>
                </tr>
            </thead>
            <tbody><tr>
                        <td style='text-align:center; padding:6px; border:1px solid #000; vertical-align:middle;'>1</td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px; text-align:center;'></td>
                    </tr><tr>
                        <td style='text-align:center; padding:6px; border:1px solid #000; vertical-align:middle;'>2</td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px; text-align:center;'></td>
                    </tr><tr>
                        <td style='text-align:center; padding:6px; border:1px solid #000; vertical-align:middle;'>3</td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px; text-align:center;'></td>
                    </tr><tr>
                        <td style='text-align:center; padding:6px; border:1px solid #000; vertical-align:middle;'>4</td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px; text-align:center;'></td>
                    </tr><tr>
                        <td style='text-align:center; padding:6px; border:1px solid #000; vertical-align:middle;'>5</td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px; text-align:center;'></td>
                    </tr><tr>
                        <td style='text-align:center; padding:6px; border:1px solid #000; vertical-align:middle;'>6</td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px; text-align:center;'></td>
                    </tr><tr>
                        <td style='text-align:center; padding:6px; border:1px solid #000; vertical-align:middle;'>7</td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px; text-align:center;'></td>
                    </tr><tr>
                        <td style='text-align:center; padding:6px; border:1px solid #000; vertical-align:middle;'>8</td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px; text-align:center;'></td>
                    </tr><tr>
                        <td style='text-align:center; padding:6px; border:1px solid #000; vertical-align:middle;'>9</td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px; text-align:center;'></td>
                    </tr><tr>
                        <td style='text-align:center; padding:6px; border:1px solid #000; vertical-align:middle;'>10</td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px;'></td>
                        <td class='editable-cell' contenteditable='true' style='padding:2px 4px; border:1px solid #000; vertical-align:top; min-height:30px; text-align:center;'></td>
                    </tr></tbody>
        </table>

        <div style=\"font-size:10pt; margin-bottom:20px;\">
            <b>Ketentuan:</b><br>
            <ul style=\"margin-top:5px; padding-left:20px; line-height:1.6;\">
                <li>Logbook ini wajib diisi setiap kali melakukan bimbingan dengan dosen pembimbing.</li>
                <li>Mahasiswa wajib melakukan bimbingan minimal 8 (delapan) kali sebelum pelaksanaan Seminar Proposal/Sidang Tesis.</li>
                <li>Setiap sesi bimbingan harus mendapatkan paraf dari dosen pembimbing yang bersangkutan.</li>
                <li>Logbook ini menjadi salah satu syarat administratif untuk pendaftaran seminar proposal/siding tesis akhir.</li>
            </ul>
        </div>

        <table style=\"width:100%; border:none; font-size:10pt; font-family:Rockwell,serif;\">
            <tr>
                <td style=\"width:50%; border:none; vertical-align:top; padding-bottom:6px;\"><b>Ketua Pembimbing,</b><br>" . getTtdDosen($ketuaPembimbing) . "$ketuaPembimbing</td>
                <td style=\"width:50%; border:none; vertical-align:top; padding-bottom:6px;\">Sukabumi, &hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip; 2026<br><b>Anggota Pembimbing,</b><br>" . getTtdDosen($anggotaPembimbing) . "$anggotaPembimbing</td>
            </tr>
        </table>
        
        <p style=\"font-size:10pt; margin-top:20px;\">Mengetahui,<br><b>Ketua Program Studi " . e($prodiData['nama']) . "</b></p>
        <div style=\"font-size:10pt;\">" . getTtdDosen($prodiData['nama_kaprodi']) . e($prodiData['nama_kaprodi']) . "</div>
        </div>
        "
    ],
];

// Filter array berdasarkan idx
if ($idxParam !== 'all' && isset($allLampiran[$idxParam])) {
    $allLampiran = [$idxParam => $allLampiran[$idxParam]];
}

$safe_namaMhs = preg_replace('/[^a-zA-Z0-9 \\-\\.,]/', '_', $namaMhs);
$s_kp = preg_replace('/[^a-zA-Z0-9 \\-\\.,]/', '_', $ketuaPembimbing);
$s_ap = preg_replace('/[^a-zA-Z0-9 \\-\\.,]/', '_', $anggotaPembimbing);
$s_kpeng = preg_replace('/[^a-zA-Z0-9 \\-\\.,]/', '_', $ketuaPenguji);
$s_apeng = preg_replace('/[^a-zA-Z0-9 \\-\\.,]/', '_', $anggotaPenguji);

// Fallback logic for title
$safe_title = "Lampiran Sidang Tesis";
if (isset($_GET['idx']) && $_GET['idx'] !== 'all') {
    $i = (int)$_GET['idx'];
    switch ($i) {
        case 0: $safe_title = "Lampiran 1. $s_kp - Form Penilaian Ketua Pembimbing"; break;
        case 1: $safe_title = "Lampiran 2. $s_ap - Form Penilaian Anggota Pembimbing"; break;
        case 2: $safe_title = "Lampiran 3. $s_kpeng - Form Penilaian Ketua Penguji"; break;
        case 3: $safe_title = "Lampiran 4. $s_apeng - Form Penilaian Anggota Penguji"; break;
        case 4: $safe_title = "Lampiran 5. Berita Acara Penilaian Sidang Tesis"; break;
        case 5: $safe_title = "Lampiran 6. Lembar Pengesahan Revisi Tesis"; break;
        case 6: $safe_title = "Lampiran 7. Logbook Bimbingan Tesis"; break;
        default: $safe_title = "Lampiran Sidang Tesis - Dokumen " . ($i + 1); break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= $safe_title ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Rockwell', 'Courier New', serif; font-size: 10pt; color: #000; background: #ccc; }
        .toolbar { position: fixed; top: 16px; right: 16px; z-index: 999; display: flex; flex-direction: column; gap: 8px; background: rgba(0,0,0,.75); padding: 14px; border-radius: 12px; backdrop-filter: blur(6px); }
        .tb-inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm { padding: 8px 18px; border-radius: 8px; border: none; font-size: 13px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; text-align: center;}
        
        .screen-wrap { display: flex; flex-direction: column; align-items: center; padding: 32px 20px 80px; min-height: 100vh; gap: 0; }
        .pages-container { display: flex; flex-direction: column; align-items: center; gap: 24px; }
        .page { background: #fff; width: 210mm; min-height: 297mm; box-shadow: 0 4px 20px rgba(0,0,0,0.28); display: flex; flex-direction: column; position: relative; overflow: hidden; }
        
        .kop-area { background: #fff; text-align: center; padding: 8mm 20mm 2mm; }
        .kop-area img.kop-gambar { display: block; margin: 0 auto 2mm; max-height: 2.34cm; max-width: 170mm; width: auto; object-fit: contain; object-position: center; }
        .kop-prodi { font-family: 'Rockwell', 'Courier New', serif; font-size: 12pt; font-weight: bold; text-transform: uppercase; letter-spacing: .3px; line-height: 1.4; color: #740144; }
        .kop-univ { font-family: 'Rockwell', 'Courier New', serif; font-size: 12pt; font-weight: bold; text-transform: uppercase; letter-spacing: .2px; color: #740144; margin-top: 0.5mm; }
        
        .kertas-body { flex: 1; background: #fff; padding: 0mm 20.05mm 30mm 20.05mm; font-family: 'Rockwell', 'Courier New', serif; font-size: 10pt; line-height: 1.3; }
        
        .table-bordered, table[border="1"] { 
            border-collapse: separate !important; 
            border-spacing: 0 !important;
            border: 1px solid #000 !important; 
            border-bottom: none !important;
            border-right: none !important;
        }
        .table-bordered th, .table-bordered td, table[border="1"] th, table[border="1"] td { 
            border: 1px solid #000 !important; 
            border-top: none !important;
            border-left: none !important;
        }
        tr, td, th { page-break-inside: avoid !important; }
        thead { display: table-header-group !important; } /* Allow native print to repeat thead */
        
        /* Fix for html2pdf table border collapse breaking */
        .html2pdf__page-break { display: table-row !important; height: 0 !important; border: none !important; }
        
        .layout-tabel { border-collapse: collapse; border: none !important; outline: none !important; }
        .layout-tabel td { padding: 3px 0; border: none !important; outline: none !important; }
        
        .footer-area { 
            position: absolute; 
            bottom: 0; 
            left: 50%; 
            transform: translateX(-50%);
            width: 210mm;
            background: #fff; 
            text-align: center; 
            padding: 0;
            line-height: 0;
        }
        .footer-area img.footer-gambar { 
            display: block; 
            margin: 0 auto; 
            max-height: 28mm; 
            width: 100%; 
            object-fit: contain; 
            object-position: center center; 
        }

        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body > * { display: none !important; }
            body > .screen-wrap { display: block !important; }
            body, .screen-wrap { background: #fff; padding: 0; }
            .toolbar, .no-print { display: none !important; }
            .pages-container { display: block !important; gap: 0; }
            .page { display: block !important; box-shadow: none; width: 210mm; min-height: 297mm; height: auto; overflow: visible; page-break-after: always; }
            .tox-tinymce, .tox-tinymce-aux, .tox { display: none !important; }
            @page { size: A4; margin: 13.46mm 20.05mm 25.4mm 20.05mm; }
        }

        /* Editor hover effect */
        .editable-area {
            transition: all 0.2s;
            border: 2px dashed transparent;
        }
        .editable-area:hover, .editable-area:focus {
            border-color: var(--color-primary-hover);
            background: rgba(59, 130, 246, 0.02);
            outline: none;
            cursor: text;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.2/tinymce.min.js"></script>
</head>
<body>

    <!-- Tombol Kembali Kiri Atas -->
    <div style="position:fixed; top:16px; left:16px; z-index:999;" class="no-print">
        <a href="../pages/buat_lampiran_tesis?step=result<?= $id ? '&id='.$id : '' ?>" class="tb-inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm"
            style="background:#1e293b; color:#fff; border-radius:8px; padding:10px 16px; text-decoration:none; font-weight:bold; font-family:sans-serif; font-size:13px; box-shadow:0 4px 12px rgba(0,0,0,0.15); display:inline-block;">←
            Kembali ke Daftar Lampiran</a>
    </div>

<div class="toolbar no-print">
        <div style="color:#fff; font-size:12px; margin-bottom:5px; text-align:center;">💡 Anda dapat mengedit teks<br>di bawah sebelum dicetak</div>
        <button onclick="window.print()" class="tb-inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm" style="background:#22c55e;color:#fff;">🖨️ Print Dokumen</button>
        <button onclick="downloadPDFLangsung()" class="tb-inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm" style="background:#dc2626;color:#fff;" id="btn-dl-pdf">📥 Download PDF</button>
        <button onclick="downloadWordLangsung()" class="tb-inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm" style="background:#2563eb;color:#fff;" id="btn-dl-word">📄 Download Word</button>
        <button onclick="window.close()" class="tb-inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm" style="background:#64748b;color:#fff;">Tutup</button>
        
        <script>
                                    function downloadPDFLangsung() {
                var btn = document.getElementById('btn-dl-pdf');
                var oldText = btn.innerHTML;
                btn.innerHTML = '⏳ Sedang Memproses...';
                btn.style.opacity = '0.7';
                btn.disabled = true;

                if (tinymce.activeEditor) {
                    tinymce.activeEditor.fire('blur');
                }

                var element = document.getElementById('pages-container');
                var safeName = "<?= addslashes(preg_replace('/[^a-zA-Z0-9 \-\.,]/', '_', $safe_title)) ?>.pdf";

                var hiddenElements = document.querySelectorAll('.no-print, .tox, .tox-tinymce, .tox-tinymce-aux');
                var originalDisplays = [];
                hiddenElements.forEach(function(el, index) { 
                    originalDisplays[index] = el.style.display; 
                    el.style.display = 'none'; 
                });

                function restoreUI() {
                    hiddenElements.forEach(function(el, index) { el.style.display = originalDisplays[index]; });
                    btn.innerHTML = oldText; btn.style.opacity = '1'; btn.disabled = false;
                }

                var kopEl = document.querySelector('.kop-area');
                var footerEl = document.querySelector('.footer-area');
                var pxToMm = 25.4 / 96; 
                var kopHeightMm = (kopEl ? kopEl.offsetHeight : 0) * pxToMm;
                var footerHeightMm = (footerEl ? footerEl.offsetHeight : 0) * pxToMm;

                if (typeof html2canvas === 'undefined') {
                    alert('Error: library html2canvas gagal dimuat. Pastikan koneksi internet stabil.');
                    restoreUI();
                    return;
                }

                Promise.all([
                    kopEl ? html2canvas(kopEl, { scale: 2, useCORS: true, logging: false }) : Promise.resolve(null),
                    footerEl ? html2canvas(footerEl, { scale: 2, useCORS: true, logging: false }) : Promise.resolve(null)
                ]).then(function(canvases) {
                    var kopImg = canvases[0] ? canvases[0].toDataURL('image/png') : null;
                    var footerImg = canvases[1] ? canvases[1].toDataURL('image/png') : null;

                    var allKops = document.querySelectorAll('.kop-area');
                    var allFooters = document.querySelectorAll('.footer-area');
                    var origKopDisp = [];
                    var origFooterDisp = [];
                    
                    allKops.forEach(function(el, i) { origKopDisp[i] = el.style.display; el.style.display = 'none'; });
                    allFooters.forEach(function(el, i) { origFooterDisp[i] = el.style.display; el.style.display = 'none'; });

                    var pages = document.querySelectorAll('.page');
                    var originalStyles = [];
                    pages.forEach(function(p, index) {
                        originalStyles[index] = { 
                            boxShadow: p.style.boxShadow, margin: p.style.margin, border: p.style.border,
                            height: p.style.height, minHeight: p.style.minHeight, overflow: p.style.overflow, display: p.style.display
                        };
                        p.style.boxShadow = 'none'; p.style.margin = '0'; p.style.border = 'none';
                        p.style.height = 'auto'; p.style.minHeight = 'auto'; p.style.overflow = 'visible';
                        p.style.display = 'block';
                    });

                    var prevZoom = currentZoom;
                    setZoom(100);
                    
                    var originalDisplay = element.style.display;
                    var originalGap = element.style.gap;
                    element.style.display = 'block';
                    element.style.gap = '0';

                    var opt = {
                        margin:       [kopHeightMm, 0, footerHeightMm, 0],
                        filename:     safeName,
                        image:        { type: 'jpeg', quality: 1.0 },
                        html2canvas:  { scale: 2, useCORS: true, letterRendering: true, logging: false, scrollY: 0, scrollX: 0, windowWidth: document.documentElement.offsetWidth },
                        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                        pagebreak:    { mode: ['css', 'legacy'], avoid: ['tr', '.no-break'] }
                    };

                    var originalScrollY = window.scrollY;
                    window.scrollTo(0, 0);

                    html2pdf().set(opt).from(element).toPdf().get('pdf').then(function(pdf) {
                        var totalPages = pdf.internal.getNumberOfPages();
                        for (var i = 1; i <= totalPages; i++) {
                            pdf.setPage(i);
                            if (kopImg) pdf.addImage(kopImg, 'PNG', 0, 0, 210, kopHeightMm);
                            if (footerImg) pdf.addImage(footerImg, 'PNG', 0, 297 - footerHeightMm, 210, footerHeightMm);
                        }
                    }).save().then(function() {
                        window.scrollTo(0, originalScrollY);
                        setZoom(prevZoom);
                        element.style.display = originalDisplay;
                        element.style.gap = originalGap;
                        
                        pages.forEach(function(p, index) { 
                            p.style.boxShadow = originalStyles[index].boxShadow; 
                            p.style.margin = originalStyles[index].margin; 
                            p.style.border = originalStyles[index].border;
                            p.style.height = originalStyles[index].height;
                            p.style.minHeight = originalStyles[index].minHeight;
                            p.style.overflow = originalStyles[index].overflow;
                            p.style.display = originalStyles[index].display;
                        });
                        
                        allKops.forEach(function(el, i) { el.style.display = origKopDisp[i]; });
                        allFooters.forEach(function(el, i) { el.style.display = origFooterDisp[i]; });
                        restoreUI();
                    }).catch(function(err) {
                        alert('Gagal mendownload PDF. Error: ' + err);
                        window.scrollTo(0, originalScrollY);
                        setZoom(prevZoom);
                        element.style.display = originalDisplay;
                        element.style.gap = originalGap;
                        
                        pages.forEach(function(p, index) { 
                            p.style.boxShadow = originalStyles[index].boxShadow; 
                            p.style.margin = originalStyles[index].margin; 
                            p.style.border = originalStyles[index].border;
                            p.style.height = originalStyles[index].height;
                            p.style.minHeight = originalStyles[index].minHeight;
                            p.style.overflow = originalStyles[index].overflow;
                            p.style.display = originalStyles[index].display;
                        });
                        
                        allKops.forEach(function(el, i) { el.style.display = origKopDisp[i]; });
                        allFooters.forEach(function(el, i) { el.style.display = origFooterDisp[i]; });
                        restoreUI();
                    });
                }).catch(function(err) {
                    alert('Gagal memproses gambar Kop/Footer. Error: ' + err);
                    restoreUI();
                });
            }

            function downloadWordLangsung(returnBlob = false) {
                try {
                    var btn = document.getElementById('btn-dl-word');
                    var oldText = btn ? btn.innerHTML : 'Download Word';
                    if (btn) {
                        btn.innerHTML = '⏳ Sedang Memproses...';
                        btn.style.opacity = '0.7';
                        btn.disabled = true;
                    }

                    try {
                        if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
                            tinymce.activeEditor.fire('blur');
                        }
                    } catch(e) {}

                var safeName = "<?= addslashes(preg_replace('/[^a-zA-Z0-9 \\-\\.,]/', '_', $safe_title)) ?>.doc";

                // Hide non-print elements
                var hiddenElements = document.querySelectorAll('.no-print, .tox, .tox-tinymce, .tox-tinymce-aux');
                var originalDisplays = [];
                hiddenElements.forEach(function(el, index) {
                    originalDisplays[index] = el.style.display;
                    el.style.display = 'none';
                });

                function restoreUI() {
                    hiddenElements.forEach(function(el, index) { el.style.display = originalDisplays[index]; });
                    btn.innerHTML = oldText; btn.style.opacity = '1'; btn.disabled = false;
                }

                var kopGambar = document.querySelector('.kop-gambar');
                var footerGambar = document.querySelector('.footer-gambar');

                if (footerGambar) {
                    var origFooterWidth = footerGambar.style.width;
                    var origFooterObjectFit = footerGambar.style.objectFit;
                    
                    footerGambar.style.width = 'auto';
                    footerGambar.style.objectFit = 'fill';
                }

                Promise.all([
                    kopGambar ? html2canvas(kopGambar, { scale: 2, useCORS: true, logging: false }) : Promise.resolve(null),
                    footerGambar ? html2canvas(footerGambar, { scale: 2, useCORS: true, logging: false }) : Promise.resolve(null)
                ]).then(function(canvases) {
                    if (footerGambar) {
                        footerGambar.style.width = origFooterWidth || '';
                        footerGambar.style.objectFit = origFooterObjectFit || '';
                    }

                    var logoImg = canvases[0] ? canvases[0].toDataURL('image/png') : null;
                    var footerImg = canvases[1] ? canvases[1].toDataURL('image/png') : null;
                    var footerAspect = canvases[1] ? (canvases[1].width / canvases[1].height) : 1;
                    
                    // Override with native raw image to avoid html2canvas transparent padding layout bugs
                    if (footerGambar) {
                        try {
                            var rawCanvas = document.createElement('canvas');
                            rawCanvas.width = footerGambar.naturalWidth || footerGambar.width;
                            rawCanvas.height = footerGambar.naturalHeight || footerGambar.height;
                            var ctx = rawCanvas.getContext('2d');
                            ctx.drawImage(footerGambar, 0, 0);
                            footerImg = rawCanvas.toDataURL('image/png');
                            footerAspect = rawCanvas.width / rawCanvas.height;
                        } catch(e) {}
                    }
                    
                    var prodiText = document.querySelector('.kop-prodi') ? document.querySelector('.kop-prodi').innerText : '';
                    var univText = document.querySelector('.kop-univ') ? document.querySelector('.kop-univ').innerText : '';

                    var kopHeightPt = (logoImg || prodiText || univText) ? 102 : 0;
                    var footerHeightPt = 0;

                    var kopHtml = '';
                    if (logoImg || prodiText || univText) {
                        kopHtml += '<div style="mso-element:header" id="h1">';
                        if (logoImg) {
                            var logoAspect = canvases[0].width / canvases[0].height;
                            var logoHeightPt = 66.33; // exactly 2.34 cm as per Contoh Lampiran
                            var logoWidthPt = logoHeightPt * logoAspect;
                            var pxHeight = Math.round(logoHeightPt * 96 / 72);
                            var pxWidth = Math.round(logoWidthPt * 96 / 72);
                            kopHtml += '<p align="center" style="margin:0 auto 2mm;padding:0;text-align:center;"><img src="' + logoImg + '" width="' + pxWidth + '" height="' + pxHeight + '" style="width:' + logoWidthPt + 'pt;height:' + logoHeightPt + 'pt;" /></p>';
                        }
                        if (prodiText) {
                            kopHtml += '<p align="center" style="margin:0;padding:0;text-align:center;font-family:Rockwell,\'Courier New\',serif;font-size:12pt;font-weight:bold;color:#740144;">' + prodiText.toUpperCase() + '</p>';
                        }
                        if (univText) {
                            kopHtml += '<p align="center" style="margin:2pt 0 0 0;padding:0;text-align:center;font-family:Rockwell,\'Courier New\',serif;font-size:12pt;font-weight:bold;color:#740144;">' + univText.toUpperCase() + '</p>';
                        }
                        kopHtml += '</div>';
                    }

                    var footerHtml = '';
                    if (footerImg) {
                        // Dimensions from reference docx: cx=7029450 EMU = 553.5pt, offset=-457195 EMU=-36pt
                        var footerWidthPt = 553.5;
                        footerHeightPt = Math.round(footerWidthPt / footerAspect);
                        var offsetPt = -36; // negative left/right to extend beyond body margins symmetrically
                        footerHtml += '<div style="mso-element:footer" id="f1">';
                        footerHtml += '<p align="center" style="margin:0 ' + offsetPt + 'pt 0 ' + offsetPt + 'pt; padding:0; text-align:center;">';
                        footerHtml += '<img src="' + footerImg + '" width="' + Math.round(footerWidthPt * 96/72) + '" height="' + Math.round(footerHeightPt * 96/72) + '" style="width:' + footerWidthPt + 'pt;height:' + footerHeightPt + 'pt;" />';
                        footerHtml += '</p>';
                        footerHtml += '</div>';
                    }

                    var pages = document.querySelectorAll('.page');
                    var pagesHtml = '';

                    pages.forEach(function(page, idx) {
                        var bodyEl = page.querySelector('.kertas-body');
                        var bodyContent = bodyEl ? bodyEl.innerHTML : '';
                        
                        var pageHtml = '';

                        // Body content
                        pageHtml += '<div style="font-family:Rockwell,Courier New,serif;font-size:12pt;line-height:1.3;">';
                        pageHtml += bodyContent;
                        // Add hidden paragraph to absorb Word's forced paragraph if content ends with a table
                        pageHtml += '<p style="margin:0; padding:0; font-size:1pt; line-height:1pt; mso-hide:all; display:none;">&nbsp;</p>';
                        pageHtml += '</div>';

                        // Page break between pages (not after last)
                        if (idx < pages.length - 1) {
                            pageHtml += '<br clear="all" style="page-break-before:always" />';
                        }

                        pagesHtml += pageHtml;
                    });
                    
                    var marginTopPt = kopHeightPt > 0 ? (kopHeightPt + 10) : 42.55;
                    var marginBottomPt = footerHeightPt > 0 ? (footerHeightPt + 10) : 42.55;

                    // Build Word-native HTML document (Word opens HTML as .doc natively)
                    var wordHtml = '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
                    wordHtml += '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
                    wordHtml += '<!--[if gte mso 9]><xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom><w:DoNotOptimizeForBrowser/></w:WordDocument></xml><![endif]-->';
                    wordHtml += '<style>';
                    wordHtml += '@page WordSection1 { size: 595.3pt 841.9pt; margin: ' + marginTopPt + 'pt 56.85pt ' + marginBottomPt + 'pt 56.85pt; mso-header-margin: 35.4pt; mso-footer-margin: 0pt; mso-header: h1; mso-footer: f1; mso-title-page: no; mso-paper-source: 0; }';
                    wordHtml += 'div.WordSection1 { page: WordSection1; }';
                    wordHtml += 'body { font-family: Rockwell, "Courier New", serif; font-size: 12pt; color: #000; margin: 0; padding: 0; }';
                    wordHtml += 'table { border-collapse: collapse; }';
                    wordHtml += '.table-bordered, .table-bordered th, .table-bordered td { border: 0.5pt solid #000; }';
                    wordHtml += '';
                    wordHtml += 'img { max-width: 100%; height: auto; }';
                    wordHtml += 'p { margin: 0; padding: 0; mso-style-unhide: no; }';
                    wordHtml += '</style>';
                    wordHtml += '</head><body>';
                    wordHtml += '<div class="WordSection1">';
                    wordHtml += pagesHtml;
                    wordHtml += '<table id="hrdftrtbl" border="0" cellspacing="0" cellpadding="0" style="margin:0in 0in 0in 900in; width:1px; height:1pt; overflow:hidden; mso-hide:all;"><tr style="height:1pt; mso-height-rule:exactly;"><td style="padding:0; height:1pt; overflow:hidden;">';
                    wordHtml += kopHtml;
                    wordHtml += footerHtml;
                    wordHtml += '</td></tr></table>';
                    wordHtml += '</div>';
                    wordHtml += '</body></html>';

                    // Save as .doc using Word-compatible MIME type
                    var blob = new Blob(['\ufeff' + wordHtml], { type: 'application/msword' });

                    if (returnBlob) {
                        window.parent.postMessage({
                            action: 'word_blob',
                            blob: blob,
                            filename: safeName,
                            idx: new URLSearchParams(window.location.search).get('idx')
                        }, '*');
                    } else {
                        var link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = safeName;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        URL.revokeObjectURL(link.href);
                    }

                    restoreUI();
                }).catch(function(err) {
                    if (returnBlob) {
                        window.parent.postMessage({ action: 'word_blob_error', error: err.toString(), idx: new URLSearchParams(window.location.search).get('idx') }, '*');
                    } else {
                        alert('Gagal mendownload Word. Error: ' + err);
                    }
                    if (typeof restoreUI === 'function') restoreUI();
                });
                } catch(err) {
                    if (returnBlob) {
                        window.parent.postMessage({ action: 'word_blob_error', error: 'Sync Error: ' + err.toString(), idx: new URLSearchParams(window.location.search).get('idx') }, '*');
                    } else {
                        alert('Gagal mengeksekusi download: ' + err);
                    }
                }
            }
            
            window.addEventListener('message', function(event) {
                if (event.data && event.data.action === 'get_word_blob') {
                    downloadWordLangsung(true);
                }
            });
        </script>
    </div>

    <div class="screen-wrap">
        <div class="pages-container" id="pages-container">
            <?php foreach ($allLampiran as $idx => $lampiran): ?>
            <div class="page">
                <div class="kop-area">
                    <?php if ($kopUrl): ?>
                        <img src="<?= e($kopUrl) ?>?v=<?= time() ?>" alt="Kop" class="kop-gambar">
                    <?php endif; ?>
                    <div class="kop-prodi">Program Studi <?= e($prodiData['nama']) ?></div>
                    <div class="kop-univ">Universitas Nusa Putra</div>
                </div>

                <!-- Tambahkan class editable-area -->
                <div class="kertas-body editable-area" title="Klik untuk mengedit dokumen ini">
                    <?= $lampiran['isi'] ?>
                </div>

                <div class="footer-area">
                    <?php if ($footerUrl): ?>
                        <img src="<?= e($footerUrl) ?>?v=<?= time() ?>" alt="Footer NPU" class="footer-gambar">
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Indikator Zoom + Halaman (Pojok Kanan Bawah) -->
    <div id="zoom-status"
        style="position:fixed; bottom:0; right:0; background:#1e293b; color:#fff; padding:6px 14px; font-size:12px; z-index:1000; border-top-left-radius:8px; font-family:sans-serif; display:flex; align-items:center; gap:10px; cursor:pointer;"
        title="Klik untuk mereset zoom (100%)" onclick="setZoom(100)">
        <span>🔍 Zoom: <span id="zoom-val">100</span>%</span>
        <span style="color:var(--color-text-subtle);">|</span>
        <span>📄 Halaman: <span><?= count($allLampiran) ?></span></span>
    </div>

    <!-- Script inisialisasi TinyMCE dan Shortcuts -->
    <script>
        // Fitur Zoom
        let currentZoom = 100;
        function setZoom(zoomValue) {
            currentZoom = Math.max(50, Math.min(200, zoomValue)); // Batas 50% - 200%
            document.getElementById('pages-container').style.transform = `scale(${currentZoom / 100})`;
            document.getElementById('pages-container').style.transformOrigin = 'top center';
            
            let zoomEl = document.getElementById('zoom-val');
            if(zoomEl) zoomEl.innerText = currentZoom;
        }

        // Intercept Ctrl+Scroll untuk Zoom
        window.addEventListener('wheel', function (e) {
            if (e.ctrlKey) {
                e.preventDefault();
                if (e.deltaY < 0) setZoom(currentZoom + 2); // Scroll Up = Zoom In
                else setZoom(currentZoom - 2);              // Scroll Down = Zoom Out
            }
        }, { passive: false });

        function simpanPerubahan(silent = false) {
            var btn = document.getElementById('btn-dl-pdf');
            var oldText = inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm ? btn.innerHTML : '';
            var oldBg = inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm ? inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm.style.background : '';
            
            if(!silent && inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm) {
                btn.innerHTML = '✅ Tersimpan!';
                inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm.style.background = '#059669';
            }
            
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
            
            const pageId = new URLSearchParams(window.location.search).get('id') || 'default';
            const pageName = window.location.search.includes('proposal') ? 'proposal' : 'tesis';
            const storageKey = 'lampiran_data_v3_' + pageName + '_' + pageId;
            
            let payload = {};
            try {
                payload = JSON.parse(localStorage.getItem(storageKey)) || {};
            } catch(e) {}
            
            document.querySelectorAll('.editable-cell').forEach(c => {
                if (c.id) payload[c.id] = c.innerHTML;
            });
            document.querySelectorAll('.tinymce').forEach(t => {
                if (t.id) payload[t.id] = t.value;
            });
            
            localStorage.setItem(storageKey, JSON.stringify(payload));
            
            if(!silent && !inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm) {
                const toast = document.createElement('div');
                toast.innerText = 'Tersimpan!';
                toast.style.position = 'fixed';
                toast.style.bottom = '20px';
                toast.style.right = '20px';
                toast.style.background = '#4CAF50';
                toast.style.color = 'white';
                toast.style.padding = '10px 20px';
                toast.style.borderRadius = '5px';
                toast.style.zIndex = '9999';
                document.body.appendChild(toast);
                setTimeout(() => { toast.remove(); }, 2000);
            }
            
            if(!silent && inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm) {
                setTimeout(function() {
                    btn.innerHTML = oldText;
                    inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm.style.background = oldBg;
                }, 2000);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const pageId = new URLSearchParams(window.location.search).get('id') || 'default';
            const pageName = window.location.search.includes('proposal') ? 'proposal' : 'tesis';
            const storageKey = 'lampiran_data_v3_' + pageName + '_' + pageId;
            
            const savedData = localStorage.getItem(storageKey);
            if(savedData) {
                try {
                    const payload = JSON.parse(savedData);
                    
                    document.querySelectorAll('.editable-cell').forEach(c => {
                        if (c.id && payload[c.id] !== undefined && payload[c.id] !== '') {
                            c.innerHTML = payload[c.id];
                        }
                    });
                    
                    document.querySelectorAll('.tinymce').forEach(ta => {
                        if (ta.id && payload[ta.id] !== undefined && payload[ta.id] !== '') {
                            ta.value = payload[ta.id];
                            ta.innerHTML = payload[ta.id];
                        }
                    });
                } catch(e) { console.error('Error loading data', e); }
            }
            
            // Auto save on ANY keystroke for extreme reliability
            document.querySelectorAll('.editable-cell').forEach(c => {
                c.addEventListener('input', function() {
                    simpanPerubahan(true);
                });
            });
            
            // Fallback for TinyMCE
            setInterval(function() {
                simpanPerubahan(true);
            }, 3000);
        });

        // 100% reliable save when tab is closed, refreshed, or navigated away
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden') {
                simpanPerubahan(true);
            }
        });
        window.addEventListener('beforeunload', function() {
            simpanPerubahan(true);
        });
// Intercept Shortcuts Global
        window.addEventListener('keydown', function (e) {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 's' || e.key === 'S') {
                    e.preventDefault();
                    simpanPerubahan();

                        } else if (e.key === 'p' || e.key === 'P') {
                    e.preventDefault();
                    window.print();
                } else if (e.key === '=' || e.key === '+') {
                    e.preventDefault();
                    setZoom(currentZoom + 10);
                } else if (e.key === '-' || e.key === '_') {
                    e.preventDefault();
                    setZoom(currentZoom - 10);
                } else if (e.key === '0') {
                    e.preventDefault();
                    setZoom(100);
                }
            }
        });

        tinymce.init({
            selector: '.editable-area',
            inline: true,
            menubar: false,
            visual: false,
            plugins: 'lists link table charmap',
            toolbar: 'undo redo | fontfamily fontsize | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | table | removeformat',
            font_family_formats: 'Rockwell=Rockwell,Courier New,serif; Arial=arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif;',
            font_size_formats: '8pt 10pt 11pt 12pt 14pt 18pt 24pt',
            contextmenu: 'table',
            table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | tablemergecells tablesplitcells',
            setup: function (editor) {
                editor.on('keydown', function (e) {
                    if (e.keyCode === 9) { // Tab
                        if (e.shiftKey) return;
                        e.preventDefault();
                        e.stopPropagation();
                        editor.execCommand('mceInsertContent', false, '&emsp;&emsp;');
                    }
                    if (e.ctrlKey || e.metaKey) {
                        if (e.key === 's' || e.key === 'S') {
                            e.preventDefault();
                            e.stopPropagation();
                            simpanPerubahan();
                        } else if (e.key === 'p' || e.key === 'P') {
                            e.preventDefault();
                            e.stopPropagation();
                            window.print();
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
