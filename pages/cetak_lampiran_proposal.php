<?php

/**

 * Cetak Lampiran Proposal (On-the-fly) - Editable

 * Render 8 dokumen dengan fitur editor TinyMCE

 */

require_once __DIR__ . '/../config/database.php';

require_once __DIR__ . '/../includes/functions.php';

requireAdmin();



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



$idxParam         = $_GET['idx'] ?? 'all';



$prodiId          = (int)$data['prodi_id'];

$namaMhs          = trim($data['nama_mhs']);

$nimMhs           = trim($data['nim_mhs']);

$konsentrasi      = trim($data['konsentrasi'] ?? '');

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

            return '<img src="../TTD%20Dosen/TTD%20Dosen%20Manajemen/' . rawurlencode($file) . '?v=' . time() . '" style="height:85px; display:block; margin: -15px auto -15px auto; object-fit:contain; position:relative; z-index:1; mix-blend-mode: multiply;">';

    }

    return '<br><br><br>';

}



function generateFormPenilaian($peran, $namaDosen, $namaMhs, $nimMhs, $konsentrasi, $prodi, $judulTesis, $tgl) {

    $sub = 'font-family:Rockwell,serif; font-style:italic; color:#555555; font-size:8.5pt;';

    return "

    <div style=\"font-family:Rockwell,serif;\">

    <h3 style=\"text-align:center; font-size:14pt; font-weight:bold; margin-bottom:10px; font-family:Rockwell,serif;\">Form Penilaian &amp; Revisi Seminar Proposal &ndash; $peran</h3>

    <table style=\"width:100%; margin-bottom:12px; border-collapse:collapse; font-family:Rockwell,serif; font-size:12pt;\">

        <tr>

            <td class='py-3 px-4' style=\"width:32%; vertical-align:middle; padding:3px 5px;\"><b>Nama Mahasiswa / </b><i>Student Name</i></td>

            <td class='py-3 px-4' style=\"width:2%; text-align:center; vertical-align:middle; font-weight:bold;\">:</td>

            <td class='py-3 px-4' style=\"vertical-align:middle; padding:3px 5px;\">$namaMhs</td>

        </tr>

        <tr>

            <td class='py-3 px-4' style=\"vertical-align:middle; padding:3px 5px;\"><b>NIM / </b><i>Student ID</i></td>

            <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; font-weight:bold;\">:</td>

            <td class='py-3 px-4' style=\"vertical-align:middle; padding:3px 5px;\">$nimMhs</td>

        </tr>

        <tr>

            <td class='py-3 px-4' style=\"vertical-align:middle; padding:3px 5px;\"><b>Program Studi / </b><i>Study Program</i></td>

            <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; font-weight:bold;\">:</td>

            <td class='py-3 px-4' style=\"vertical-align:middle; padding:3px 5px;\">$prodi</td>

        </tr>

        <tr>

            <td class='py-3 px-4' style=\"vertical-align:top; padding:3px 5px;\"><b>Judul Tesis / </b><i>Thesis Title</i></td>

            <td class='py-3 px-4' style=\"text-align:center; vertical-align:top; font-weight:bold;\">:</td>

            <td class='py-3 px-4' style=\"vertical-align:top; padding:3px 5px; text-align:justify;\">$judulTesis</td>

        </tr>

        <tr>

            <td class='py-3 px-4' style=\"vertical-align:middle; padding:3px 5px;\"><b>$peran / </b><i>Principal Supervisor</i></td>

            <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; font-weight:bold;\">:</td>

            <td class='py-3 px-4' style=\"vertical-align:middle; padding:3px 5px;\">$namaDosen</td>

        </tr>

        <tr>

            <td class='py-3 px-4' style=\"vertical-align:middle; padding:3px 5px;\"><b>Hari dan Tanggal / </b><i>Day and Date</i></td>

            <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; font-weight:bold;\">:</td>

            <td class='py-3 px-4' style=\"vertical-align:middle; padding:3px 5px;\">$tgl</td>

        </tr>

    </table>



    <p style=\"font-weight:bold; margin-bottom:4px; text-align:center; font-family:Rockwell,serif;\"><b>Tabel 1. </b>Penilaian Seminar Proposal</p>

    <table border=\"1\" style=\"width:100%; font-family:Rockwell,serif; font-size:12pt; margin-bottom:10px; border-collapse:collapse;\">

        <thead>
<tr class='border-b border-slate-200 dark:border-slate-700'>

            <tr style=\"background-color:#961d5a; text-align:center; color:#ffffff;\">

                <th style=\"padding:6px; font-weight:bold;\">Aspek Penilaian /<br><i style='font-weight:normal;'>Assessment Criteria</i></th>

                <th style=\"width:9%; padding:6px; font-weight:bold;\">Bobot</th>

                <th style=\"width:10%; padding:6px; font-weight:bold;\">Skor (1&ndash;5)</th>

                <th style=\"width:9%; padding:6px; font-weight:bold;\">Nilai</th>

                <th style=\"width:28%; padding:6px; font-weight:bold;\">Catatan Perbaikan</th>

            </tr>

        </thead>

        <tbody class='divide-y divide-slate-100 dark:divide-slate-700'>

            <tr>

                <td class='py-3 px-4' style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Signifikansi Latar Belakang Riset dan/atau Fokus Riset, dan Rumusan Masalah</b><br>

                    <span style=\"$sub\">&bull; Relevansi dan urgensi permasalahan yang diteliti</span><br>

                    <span style=\"$sub\">&bull; Ketajaman rumusan masalah / pertanyaan penelitian.</span><br>

                    <span style=\"$sub\">&bull; Keselarasan antara latar belakang dan rumusan masalah</span>

                </td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; padding:4px;\">15%</td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Relevansi dan Kemutakhiran Tinjauan Pustaka</b><br>

                    <span style=\"$sub\">&bull; Cakupan dan kemutakhiran sumber referensi</span><br>

                    <span style=\"$sub\">&bull; Relevansi teori dan penelitian terdahulu</span><br>

                    <span style=\"$sub\">&bull; Posisi penelitian terhadap state of the art</span>

                </td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; padding:4px;\">25%</td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Ketepatan Formulasi Kerangka Pemikiran dan Proposisi Riset / Hipotesis</b><br>

                    <span style=\"$sub\">&bull; Koherensi kerangka konseptual/teoritis</span><br>

                    <span style=\"$sub\">&bull; Ketepatan formulasi hipotesis atau proposisi</span>

                </td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; padding:4px;\">10%</td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Kesesuaian Metode Riset</b><br>

                    <span style=\"$sub\">&bull; Kesesuaian pendekatan/metode dengan tujuan penelitian</span><br>

                    <span style=\"$sub\">&bull; Kelayakan rancangan dan prosedur penelitian</span>

                </td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; padding:4px;\">10%</td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Kemampuan Penulisan Ilmiah</b><br>

                    <span style=\"$sub\">&bull; Sistematika dan kejelasan penyajian</span><br>

                    <span style=\"$sub\">&bull; Kebenaran tata bahasa dan ejaan</span><br>

                    <span style=\"$sub\">&bull; Konsistensi sitasi dan daftar pustaka</span>

                </td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; padding:4px;\">20%</td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Kemampuan Komunikasi dalam Ujian Lisan</b><br>

                    <span style=\"$sub\">&bull; Kejelasan dan ketepatan presentasi</span><br>

                    <span style=\"$sub\">&bull; Kemampuan menjawab pertanyaan pembahas</span>

                </td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; padding:4px;\">20%</td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr style=\"font-weight:bold; background-color:#f5f5f5;\">

                <td class='py-3 px-4' colspan=\"3\" style=\"text-align:right; padding:6px;\">Total Nilai=</td>

                <td class='py-3 px-4' style=\"text-align:center; padding:6px;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class='py-3 px-4' style=\"background-color:#f5f5f5;\"></td>

            </tr>

        </tbody>

    </table>



    <div style=\"font-size:10pt; margin-bottom:10px; font-family:Rockwell,serif;\">

        <b>Cara mengisi Skor:</b> Berikan skor 1&ndash;5 pada kolom Skor untuk setiap aspek.<br>

        <b>Cara menghitung Nilai:</b> Contoh Skor 4, Bobot 25% &rarr; (4&divide;5)&times;25 = Nilai 20

    </div>



    <div style=\"margin-bottom:10px; font-family:Rockwell,serif; font-size:10pt;\">

        <b>Catatan Revisi Tambahan:</b><br>

        <div class=\"editable-cell\" contenteditable=\"true\" style=\"width:100%; min-height:40px; border:1px solid #999; padding:5px; margin-top:4px;\"></div>

    </div>



    <p style=\"font-weight:bold; margin-bottom:4px; text-align:center; font-family:Rockwell,serif;\"><b>Tabel 2. Kriteria Penilaian Skor</b></p>

    <table border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:10px; text-align:center; border-collapse:collapse; font-family:Rockwell,serif;\">

        <tr style=\"background-color:#961d5a; color:#fff;\">

            <th colspan=\"5\" style=\"padding:5px;\">Keterangan Skor</th>

        </tr>

        <tr>

            <td class='py-3 px-4' style=\"padding:5px;\">Skor 1<br>Sangat Kurang</td>

            <td class='py-3 px-4' style=\"padding:5px;\">Skor 2<br>Kurang</td>

            <td class='py-3 px-4' style=\"padding:5px;\">Skor 3<br>Cukup</td>

            <td class='py-3 px-4' style=\"padding:5px;\">Skor 4<br>Baik</td>

            <td class='py-3 px-4' style=\"padding:5px;\">Skor 5<br>Sangat Baik</td>

        </tr>

    </table>



    <p style=\"font-weight:bold; margin-bottom:4px; text-align:center; font-family:Rockwell,serif;\"><b>Tabel 3. Nilai dan Huruf Mutu</b></p>

    <table border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:15px; text-align:center; border-collapse:collapse; font-family:Rockwell,serif;\">

        <tr style=\"background-color:#961d5a; color:#fff;\">

            <th colspan=\"4\" style=\"padding:5px;\">Konversi Huruf Mutu</th>

        </tr>

        <tr style=\"background-color:#f5f5f5;\">

            <th style=\"padding:5px;\">Huruf</th>

            <th style=\"padding:5px;\">Rentang</th>

            <th style=\"padding:5px;\">Bobot</th>

            <th style=\"padding:5px;\">Keterangan</th>

        </tr>

        <tr><td>A</td><td>90&ndash;100</td><td>4</td><td>Lulus</td></tr>

        <tr style=\"background-color:#faf2f5;\"><td>A-</td><td>85&ndash;89,99</td><td>3,67</td><td>Lulus</td></tr>

        <tr><td>B+</td><td>80&ndash;84,99</td><td>3,33</td><td>Lulus</td></tr>

        <tr style=\"background-color:#faf2f5;\"><td>B</td><td>75&ndash;79,99</td><td>3</td><td>Lulus</td></tr>

        <tr><td>B-</td><td>70&ndash;74,99</td><td>2,67</td><td>Tidak Lulus</td></tr>

    </table>



    <table style=\"width:100%; border:none; margin-top:30px; font-family:Rockwell,serif;\">

        <tr>

            <td class='py-3 px-4' style=\"width:60%; border:none;\"></td>

            <td class='py-3 px-4' style=\"width:40%; text-align:left; border:none;\">

                &nbsp;&nbsp;&nbsp;$peran,<br>" . getTtdDosen($namaDosen) . "$namaDosen

            </td>

        </tr>

    </table>

    </div>

    ";

}



$allLampiran = [

    [

        'isi' => generateFormPenilaian('Ketua Pembimbing', $ketuaPembimbing, $namaMhs, $nimMhs, $konsentrasi, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)

    ],

    [

        'isi' => generateFormPenilaian('Anggota Pembimbing', $anggotaPembimbing, $namaMhs, $nimMhs, $konsentrasi, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)

    ],

    [

        'isi' => generateFormPenilaian('Ketua Penguji', $ketuaPenguji, $namaMhs, $nimMhs, $konsentrasi, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)

    ],

    [

        'isi' => generateFormPenilaian('Anggota Penguji', $anggotaPenguji, $namaMhs, $nimMhs, $konsentrasi, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)

    ],

    [ // Lampiran 5: Berita Acara - Halaman 1 (Tabel Penilaian)

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; font-weight:bold; margin-bottom:20px; font-family:Rockwell,serif;\">Berita Acara Penilaian Seminar Proposal</h3>

        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-family:Rockwell,serif;\">

            <tr>

                <td class='py-3 px-4' style=\"width:38%; vertical-align:top; padding:6px 0; font-size:11pt;\"><b>Nama Mahasiswa /</b><br><i style=\"font-size:10pt; font-weight:normal;\">Student Name</i></td>

                <td class='py-3 px-4' style=\"width:3%; vertical-align:middle; font-weight:bold; padding:6px 6px; font-size:11pt;\">:</td>

                <td class='py-3 px-4' style=\"vertical-align:middle; padding:6px 0; font-size:11pt;\">$namaMhs</td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"vertical-align:top; padding:6px 0; font-size:11pt;\"><b>NIM /</b><br><i style=\"font-size:10pt; font-weight:normal;\">Student ID</i></td>

                <td class='py-3 px-4' style=\"vertical-align:middle; font-weight:bold; padding:6px 6px; font-size:11pt;\">:</td>

                <td class='py-3 px-4' style=\"vertical-align:middle; padding:6px 0; font-size:11pt;\">$nimMhs</td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"vertical-align:top; padding:6px 0; font-size:11pt;\"><b>Program Studi /</b><br><i style=\"font-size:10pt; font-weight:normal;\">Study Program</i></td>

                <td class='py-3 px-4' style=\"vertical-align:middle; font-weight:bold; padding:6px 6px; font-size:11pt;\">:</td>

                <td class='py-3 px-4' style=\"vertical-align:middle; padding:6px 0; font-size:11pt;\">" . $prodiData['nama'] . "</td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"vertical-align:top; padding:6px 0; font-size:11pt;\"><b>Judul Tesis /</b><br><i style=\"font-size:10pt; font-weight:normal;\">Thesis Title</i></td>

                <td class='py-3 px-4' style=\"vertical-align:top; font-weight:bold; padding:6px 6px; font-size:11pt;\">:</td>

                <td class='py-3 px-4' style=\"vertical-align:top; padding:6px 0; font-size:11pt; text-align:justify;\">$judulTesis</td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"vertical-align:top; padding:6px 0; font-size:11pt;\"><b>Hari dan Tanggal /</b><br><i style=\"font-size:10pt; font-weight:normal;\">Day and Date</i></td>

                <td class='py-3 px-4' style=\"vertical-align:middle; font-weight:bold; padding:6px 6px; font-size:11pt;\">:</td>

                <td class='py-3 px-4' style=\"vertical-align:middle; padding:6px 0; font-size:11pt;\">$formattedTanggalSidang</td>

            </tr>

        </table>



        <p style=\"font-weight:bold; margin-bottom:6px; text-align:center; font-family:Rockwell,serif; font-size:11pt;\"><b>Tabel 1.</b> Rekapitulasi Penilaian Seminar Proposal</p>

        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10.5pt; margin-bottom:18px; border-collapse:collapse; font-family:Rockwell,serif;\">

            <thead>
<tr class='border-b border-slate-200 dark:border-slate-700'>

                <tr style=\"background-color:#961d5a; color:#ffffff; text-align:center;\">

                    <th style=\"width:6%; padding:7px; font-weight:bold;\">No</th>

                    <th style=\"padding:7px; font-weight:bold;\">Nama / <i style=\"font-weight:normal;\">Name</i></th>

                    <th style=\"width:22%; padding:7px; font-weight:bold;\">Peran / <i style=\"font-weight:normal;\">Role</i></th>

                    <th style=\"width:22%; padding:7px; font-weight:bold;\">Tim</th>

                    <th style=\"width:18%; padding:7px; font-weight:bold;\">Nilai (0&ndash;100)</th>

                </tr>

            </thead>

            <tbody class='divide-y divide-slate-100 dark:divide-slate-700'>

                <tr>

                    <td class='py-3 px-4' style=\"text-align:center; padding:9px; vertical-align:middle;\">1</td>

                    <td class='py-3 px-4' style=\"padding:9px; text-align:center; vertical-align:middle;\">$ketuaPembimbing</td>

                    <td class='py-3 px-4' style=\"padding:9px; text-align:center; vertical-align:middle;\">Ketua Pembimbing</td>

                    <td class='py-3 px-4' style=\"text-align:center; padding:9px; vertical-align:middle;\">Pembimbing (60%)</td>

                    <td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; vertical-align:middle;\"></td>

                </tr>

                <tr>

                    <td class='py-3 px-4' style=\"text-align:center; padding:9px; vertical-align:middle;\">2</td>

                    <td class='py-3 px-4' style=\"padding:9px; text-align:center; vertical-align:middle;\">$anggotaPembimbing</td>

                    <td class='py-3 px-4' style=\"padding:9px; text-align:center; vertical-align:middle;\">Anggota Pembimbing</td>

                    <td class='py-3 px-4' style=\"text-align:center; padding:9px; vertical-align:middle;\">Pembimbing (60%)</td>

                    <td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; vertical-align:middle;\"></td>

                </tr>

                <tr>

                    <td class='py-3 px-4' style=\"text-align:center; padding:9px; vertical-align:middle;\">3</td>

                    <td class='py-3 px-4' style=\"padding:9px; text-align:center; vertical-align:middle;\">$ketuaPenguji</td>

                    <td class='py-3 px-4' style=\"padding:9px; text-align:center; vertical-align:middle;\">Ketua Penguji</td>

                    <td class='py-3 px-4' style=\"text-align:center; padding:9px; vertical-align:middle;\">Penguji (40%)</td>

                    <td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; vertical-align:middle;\"></td>

                </tr>

                <tr>

                    <td class='py-3 px-4' style=\"text-align:center; padding:9px; vertical-align:middle;\">4</td>

                    <td class='py-3 px-4' style=\"padding:9px; text-align:center; vertical-align:middle;\">$anggotaPenguji</td>

                    <td class='py-3 px-4' style=\"padding:9px; text-align:center; vertical-align:middle;\">Anggota Penguji</td>

                    <td class='py-3 px-4' style=\"text-align:center; padding:9px; vertical-align:middle;\">Penguji (40%)</td>

                    <td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; vertical-align:middle;\"></td>

                </tr>

                <tr style=\"font-weight:bold;\">

                    <td class='py-3 px-4' colspan=\"4\" style=\"text-align:center; padding:9px;\">NILAI AKHIR SEMINAR PROPOSAL / <i>Final Grade</i></td>

                    <td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; padding:9px;\"></td>

                </tr>

                <tr style=\"font-weight:bold;\">

                    <td class='py-3 px-4' colspan=\"4\" style=\"text-align:center; padding:9px;\">HURUF MUTU / <i>Letter Grade</i></td>

                    <td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; padding:9px;\"></td>

                </tr>

            </tbody>

        </table>

        "

    ],

    [ // Lampiran 5b: Berita Acara - Halaman 2 (Rumus + Tanda Tangan)

        'isi' => "

        <div style=\"font-family:Rockwell,serif; font-size:11pt; margin-bottom:25px; line-height:1.9;\">

            <p style=\"margin-bottom:10px;\"><b>Cara Hitung Nilai Akhir:</b> Nilai Akhir = (Rata-rata Pembimbing &times; 60%) + (Rata-rata Penguji &times; 40%)</p>

            <p><b>Konversi ke Huruf Mutu:</b><br>

            <span class=\"editable-cell\" contenteditable=\"true\" style=\"display:inline-block; min-width:80px; border-bottom:1px solid #555; padding:2px 6px;\"></span>

            &nbsp;&rarr;&nbsp; Huruf Mutu &nbsp;

            <span class=\"editable-cell\" contenteditable=\"true\" style=\"display:inline-block; min-width:30px; border-bottom:1px solid #555; padding:2px 6px;\"></span>

            &nbsp;&rarr;&nbsp; <b><span class=\"editable-cell\" contenteditable=\"true\" style=\"display:inline-block; min-width:80px; border-bottom:1px solid #555; padding:2px 6px;\">LULUS &#10003;</span></b>

            </p>

        </div>



        <div style=\"text-align:center; font-size:11pt; font-family:Rockwell,serif; margin-top:35px; margin-bottom:30px;\">Tanda tangan:</div>



        <table style=\"width:100%; border:none; text-align:center; font-size:10.5pt; font-family:Rockwell,serif;\">

            <tr>

                <td class='py-3 px-4' style=\"width:50%; border:none; padding-bottom:6px; vertical-align:top;\">Ketua Pembimbing</td>

                <td class='py-3 px-4' style=\"width:50%; border:none; padding-bottom:6px; vertical-align:top;\">Anggota Pembimbing</td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"border:none; padding-top:2px; padding-bottom:4px; vertical-align:bottom;\">" . getTtdDosen($ketuaPembimbing) . "<u>$ketuaPembimbing</u></td>

                <td class='py-3 px-4' style=\"border:none; padding-top:2px; padding-bottom:4px; vertical-align:bottom;\">" . getTtdDosen($anggotaPembimbing) . "<u>$anggotaPembimbing</u></td>

            </tr>

            <tr><td class='py-3 px-4' colspan=\"2\" style=\"border:none; height:30px;\"></td></tr>

            <tr>

                <td class='py-3 px-4' style=\"width:50%; border:none; padding-bottom:6px; vertical-align:top;\">Ketua Penguji</td>

                <td class='py-3 px-4' style=\"width:50%; border:none; padding-bottom:6px; vertical-align:top;\">Anggota Penguji</td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"border:none; padding-top:2px; padding-bottom:4px; vertical-align:bottom;\">" . getTtdDosen($ketuaPenguji) . "<u>$ketuaPenguji</u></td>

                <td class='py-3 px-4' style=\"border:none; padding-top:2px; padding-bottom:4px; vertical-align:bottom;\">" . getTtdDosen($anggotaPenguji) . "<u>$anggotaPenguji</u></td>

            </tr>

            <tr><td class='py-3 px-4' colspan=\"2\" style=\"border:none; height:30px;\"></td></tr>

            <tr>

                <td class='py-3 px-4' colspan=\"2\" style=\"border:none; text-align:center; padding-bottom:6px;\">Mengetahui,<br>Ketua Program Studi</td>

            </tr>

            <tr>

                <td class='py-3 px-4' colspan=\"2\" style=\"border:none; text-align:center; vertical-align:bottom;\">" . getTtdDosen($prodiData['nama_kaprodi']) . "<u>" . e($prodiData['nama_kaprodi']) . "</u></td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 6: Lembar Pengesahan

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; font-family:Rockwell,serif; font-weight:bold; margin-top:20px; margin-bottom:40px;\">Lembar Pengesahan Revisi</h3>

        

        <table style=\"width:100%; margin-bottom:40px; font-size:11pt; font-family:Rockwell,serif; border:none;\">

            <tr>

                <td class='py-3 px-4' style=\"width:25%; vertical-align:top; padding-bottom:5px; border:none;\">Nama Mahasiswa</td>

                <td class='py-3 px-4' style=\"width:2%; text-align:center; vertical-align:top; padding-bottom:5px; border:none;\">:</td>

                <td class='py-3 px-4' style=\"vertical-align:top; padding-bottom:5px; border:none;\">$namaMhs</td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"vertical-align:top; padding-bottom:5px; border:none;\">NIM</td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:top; padding-bottom:5px; border:none;\">:</td>

                <td class='py-3 px-4' style=\"vertical-align:top; padding-bottom:5px; border:none;\">$nimMhs</td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"vertical-align:top; border:none;\">Judul Proposal</td>

                <td class='py-3 px-4' style=\"text-align:center; vertical-align:top; border:none;\">:</td>

                <td class='py-3 px-4' style=\"vertical-align:top; text-align:justify; line-height:1.5; border:none;\">$judulTesis</td>

            </tr>

        </table>



        <p style=\"text-align:justify; font-size:11pt; font-family:Rockwell,serif; line-height:1.5; margin-bottom:50px;\">

            Telah menyelesaikan revisi sesuai catatan dari pembimbing dan penguji.

        </p>



        <div style=\"text-align:center; font-size:11pt; font-family:Rockwell,serif; margin-bottom:40px;\">Tanda tangan:</div>



        <table style=\"width:100%; border:none; text-align:center; font-size:11pt; font-family:Rockwell,serif;\">

            <tr>

                <td class='py-3 px-4' style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top;\">Ketua Pembimbing,</td>

                <td class='py-3 px-4' style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top;\">Anggota Pembimbing,</td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;\">" . getTtdDosen($ketuaPembimbing) . "($ketuaPembimbing)</td>

                <td class='py-3 px-4' style=\"border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;\">" . getTtdDosen($anggotaPembimbing) . "($anggotaPembimbing)</td>

            </tr>

            <tr><td class='py-3 px-4' colspan=\"2\" style=\"border:none; height:30px;\"></td></tr>

            <tr>

                <td class='py-3 px-4' style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top;\">Ketua Penguji,</td>

                <td class='py-3 px-4' style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top;\">Anggota Penguji,</td>

            </tr>

            <tr>

                <td class='py-3 px-4' style=\"border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;\">" . getTtdDosen($ketuaPenguji) . "($ketuaPenguji)</td>

                <td class='py-3 px-4' style=\"border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;\">" . getTtdDosen($anggotaPenguji) . "($anggotaPenguji)</td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 7: Lembar Bimbingan

        'isi' => "

        <h3 style=\"text-align:center; font-size:16pt; margin-bottom:15px;\">KARTU BIMBINGAN PROPOSAL</h3>

        

        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-size:10pt;\">

            <tr><td class='py-3 px-4' style=\"width:20%; vertical-align:top;\">Nama</td><td class='py-3 px-4' style=\"width:2%; vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">NIM</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Program Studi</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Judul Proposal</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top; text-align:justify;\">$judulTesis</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Ketua Pembimbing</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$ketuaPembimbing</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Anggota Pembimbing</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$anggotaPembimbing</td></tr>

        </table>



        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:15px;\">

            <thead>
<tr class='border-b border-slate-200 dark:border-slate-700'>

                <tr style=\"background-color:#f0f0f0; text-align:center;\">

                    <th style=\"width:5%; padding:8px;\">No</th>

                    <th style=\"width:15%; padding:8px;\">Tanggal</th>

                    <th style=\"width:50%; padding:8px;\">Materi / Catatan Bimbingan</th>

                    <th style=\"width:15%; padding:8px;\">Paraf Ketua</th>

                    <th style=\"width:15%; padding:8px;\">Paraf Anggota</th>

                </tr>

            </thead>

            <tbody class='divide-y divide-slate-100 dark:divide-slate-700'>

                " . str_repeat("<tr>

                    <td class='py-3 px-4' style='padding:15px;'></td>

                    <td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td>

                    <td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td>

                    <td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td>

                    <td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td>

                </tr>", 8) . "

            </tbody>

        </table>

        "

    ],

    [ // Lampiran 9: Formulir Pendaftaran Seminar Proposal

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; margin-bottom:20px;\">FORMULIR PENDAFTARAN SEMINAR PROPOSAL</h3>

        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-size:10pt;\">

            <tr><td class='py-3 px-4' style=\"width:35%; vertical-align:top;\">Nama Mahasiswa</td><td class='py-3 px-4' style=\"width:2%; vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">NIM</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Program Studi</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Judul Proposal</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top; text-align:justify;\">$judulTesis</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Rencana Tanggal Seminar</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$formattedTanggalSidang</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Ketua Pembimbing</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$ketuaPembimbing</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Anggota Pembimbing</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$anggotaPembimbing</td></tr>

        </table>



        <p style=\"font-weight:bold; margin-bottom:8px;\">Kelengkapan Dokumen yang Diserahkan:</p>

        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:20px;\">

            <thead>
<tr class='border-b border-slate-200 dark:border-slate-700'>

                <tr style=\"background-color:#f0f0f0; text-align:center;\">

                    <th style=\"width:5%; padding:6px;\">No</th>

                    <th style=\"padding:6px;\">Dokumen</th>

                    <th style=\"width:20%; padding:6px;\">Status</th>

                    <th style=\"width:20%; padding:6px;\">Keterangan</th>

                </tr>

            </thead>

            <tbody class='divide-y divide-slate-100 dark:divide-slate-700'>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">1</td><td class='py-3 px-4' style=\"padding:6px;\">Draft Proposal (Hardcopy)</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">2</td><td class='py-3 px-4' style=\"padding:6px;\">Lembar Persetujuan Pembimbing</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">3</td><td class='py-3 px-4' style=\"padding:6px;\">Bukti Kehadiran Seminar</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">4</td><td class='py-3 px-4' style=\"padding:6px;\">Pernyataan Orisinalitas</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">5</td><td class='py-3 px-4' style=\"padding:6px;\">Pas Foto 3x4 (2 lembar)</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">6</td><td class='py-3 px-4' style=\"padding:6px;\">Fotokopi KTM</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

            </tbody>

        </table>



        <table style=\"width:100%; border:none; text-align:center; font-size:10pt; margin-top:30px;\">

            <tr>

                <td class='py-3 px-4' style=\"width:50%; border:none;\"><b>Pemohon,</b><br><br><br>$namaMhs<br><small>$nimMhs</small></td>

                <td class='py-3 px-4' style=\"width:50%; border:none;\"><b>Ketua Program Studi</b>" . getTtdDosen($prodiData['nama_kaprodi']) . e($prodiData['nama_kaprodi']) . "</td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 10: Bukti Kehadiran Seminar

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; margin-bottom:20px;\">BUKTI KEHADIRAN SEMINAR PROPOSAL</h3>

        <p style=\"text-align:center; font-size:10pt; margin-bottom:15px;\">Program Studi " . $prodiData['nama'] . " — Universitas Nusa Putra</p>



        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-size:10pt;\">

            <tr><td class='py-3 px-4' style=\"width:35%; vertical-align:top;\">Nama Mahasiswa Peserta</td><td class='py-3 px-4' style=\"width:2%;\">:</td><td>$namaMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">NIM</td><td>:</td><td>$nimMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Program Studi</td><td>:</td><td>" . $prodiData['nama'] . "</td></tr>

        </table>



        <p style=\"font-weight:bold; margin-bottom:6px;\">Daftar Kehadiran Seminar Proposal yang Diikuti:</p>

        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:20px;\">

            <thead>
<tr class='border-b border-slate-200 dark:border-slate-700'>

                <tr style=\"background-color:#f0f0f0; text-align:center;\">

                    <th style=\"width:5%; padding:6px;\">No</th>

                    <th style=\"width:35%; padding:6px;\">Nama Penyaji / Mahasiswa</th>

                    <th style=\"width:35%; padding:6px;\">Judul Proposal</th>

                    <th style=\"width:15%; padding:6px;\">Tanggal</th>

                    <th style=\"width:10%; padding:6px;\">Paraf</th>

                </tr>

            </thead>

            <tbody class='divide-y divide-slate-100 dark:divide-slate-700'>

                " . str_repeat("<tr><td class='py-3 px-4' style='text-align:center; padding:12px;'></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>", 8) . "

            </tbody>

        </table>



        <table style=\"width:100%; border:none; text-align:center; font-size:10pt; margin-top:20px;\">

            <tr>

                <td class='py-3 px-4' style=\"border:none;\"><b>Ketua Program Studi</b>" . getTtdDosen($prodiData['nama_kaprodi']) . e($prodiData['nama_kaprodi']) . "</td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 11: Pernyataan Orisinalitas

        'isi' => "

        <h3 style=\"text-align:center; font-size:16pt; margin-top:10px; margin-bottom:30px;\">LEMBAR PERNYATAAN ORISINALITAS</h3>



        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:20px;\">

            Saya yang bertanda tangan di bawah ini:

        </p>



        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:25px; font-size:10pt;\">

            <tr><td class='py-3 px-4' style=\"width:30%; vertical-align:top;\">Nama</td><td class='py-3 px-4' style=\"width:2%; vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">NIM</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Program Studi</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Judul Proposal</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top; text-align:justify;\">$judulTesis</td></tr>

        </table>



        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:15px;\">

            Dengan ini menyatakan bahwa proposal tesis saya yang berjudul di atas adalah <b>benar-benar merupakan hasil karya saya sendiri</b> dan bukan merupakan plagiat dari karya orang lain. Sumber yang saya gunakan, baik berupa kutipan langsung maupun tidak langsung, telah saya tuliskan secara lengkap dalam daftar pustaka.

        </p>

        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:15px;\">

            Apabila di kemudian hari terbukti bahwa pernyataan ini tidak benar, saya bersedia menerima sanksi akademik yang berlaku di Universitas Nusa Putra.

        </p>



        <table style=\"width:100%; border:none; font-size:10pt; margin-top:40px;\">

            <tr>

                <td class='py-3 px-4' style=\"width:60%; border:none;\"></td>

                <td class='py-3 px-4' style=\"width:40%; border:none; text-align:center;\">

                    " . ($kota ?: 'Sukabumi') . ", " . formatTanggal($tanggalSurat) . "<br>

                    Yang Membuat Pernyataan,<br><br><br><br>

                    $namaMhs<br>

                    <small>NIM. $nimMhs</small>

                </td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 8: Persetujuan Pembimbing

        'isi' => "

        <h3 style=\"text-align:center; font-size:16pt; margin-top:10px; margin-bottom:30px;\">LEMBAR PERSETUJUAN UJIAN PROPOSAL</h3>

        

        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:30px; font-size:10pt;\">

            <tr><td class='py-3 px-4' style=\"width:25%; vertical-align:top;\">Nama</td><td class='py-3 px-4' style=\"width:2%; vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">NIM</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Program Studi</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

        </table>



        <p style=\"text-align:center; font-size:10pt; margin-bottom:10px;\">Proposal dengan judul:</p>

        <h2 style=\"text-align:center; font-size:12pt; margin-bottom:30px; line-height:1.5;\">\"$judulTesis\"</h2>

        

        <p style=\"text-align:justify; font-size:10pt; line-height:1.5; margin-bottom:40px;\">

            Telah diperiksa dan disetujui untuk diujikan dalam Seminar Proposal pada Program Studi " . $prodiData['nama'] . " Universitas Nusa Putra.

        </p>



        <p style=\"text-align:center; font-weight:bold; margin-bottom:20px;\">Menyetujui,</p>

        <table style=\"width:100%; border:none; text-align:center; font-size:10pt;\">

            <tr>

                <td class='py-3 px-4' style=\"width:50%; border:none; padding-bottom:10px;\"><b>Ketua Pembimbing</b>" . getTtdDosen($ketuaPembimbing) . "$ketuaPembimbing</td>

                <td class='py-3 px-4' style=\"width:50%; border:none; padding-bottom:10px;\"><b>Anggota Pembimbing</b>" . getTtdDosen($anggotaPembimbing) . "$anggotaPembimbing</td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 9: Formulir Pendaftaran Seminar Proposal

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; margin-bottom:20px;\">FORMULIR PENDAFTARAN SEMINAR PROPOSAL</h3>

        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-size:10pt;\">

            <tr><td class='py-3 px-4' style=\"width:35%; vertical-align:top;\">Nama Mahasiswa</td><td class='py-3 px-4' style=\"width:2%; vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">NIM</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Program Studi</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Judul Proposal</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top; text-align:justify;\">$judulTesis</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Rencana Tanggal Seminar</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$formattedTanggalSidang</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Ketua Pembimbing</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$ketuaPembimbing</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Anggota Pembimbing</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$anggotaPembimbing</td></tr>

        </table>



        <p style=\"font-weight:bold; margin-bottom:8px;\">Kelengkapan Dokumen yang Diserahkan:</p>

        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:20px;\">

            <thead>
<tr class='border-b border-slate-200 dark:border-slate-700'>

                <tr style=\"background-color:#f0f0f0; text-align:center;\">

                    <th style=\"width:5%; padding:6px;\">No</th>

                    <th style=\"padding:6px;\">Dokumen</th>

                    <th style=\"width:20%; padding:6px;\">Status</th>

                    <th style=\"width:20%; padding:6px;\">Keterangan</th>

                </tr>

            </thead>

            <tbody class='divide-y divide-slate-100 dark:divide-slate-700'>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">1</td><td class='py-3 px-4' style=\"padding:6px;\">Draft Proposal (Hardcopy)</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">2</td><td class='py-3 px-4' style=\"padding:6px;\">Lembar Persetujuan Pembimbing</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">3</td><td class='py-3 px-4' style=\"padding:6px;\">Bukti Kehadiran Seminar</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">4</td><td class='py-3 px-4' style=\"padding:6px;\">Pernyataan Orisinalitas</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">5</td><td class='py-3 px-4' style=\"padding:6px;\">Pas Foto 3x4 (2 lembar)</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr><td class='py-3 px-4' style=\"text-align:center; padding:6px;\">6</td><td class='py-3 px-4' style=\"padding:6px;\">Fotokopi KTM</td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>

            </tbody>

        </table>



        <table style=\"width:100%; border:none; text-align:center; font-size:10pt; margin-top:30px;\">

            <tr>

                <td class='py-3 px-4' style=\"width:50%; border:none;\"><b>Pemohon,</b><br><br><br>$namaMhs<br><small>$nimMhs</small></td>

                <td class='py-3 px-4' style=\"width:50%; border:none;\"><b>Ketua Program Studi</b>" . getTtdDosen($prodiData['nama_kaprodi']) . e($prodiData['nama_kaprodi']) . "</td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 10: Bukti Kehadiran Seminar

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; margin-bottom:20px;\">BUKTI KEHADIRAN SEMINAR PROPOSAL</h3>

        <p style=\"text-align:center; font-size:10pt; margin-bottom:15px;\">Program Studi " . $prodiData['nama'] . " — Universitas Nusa Putra</p>



        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-size:10pt;\">

            <tr><td class='py-3 px-4' style=\"width:35%; vertical-align:top;\">Nama Mahasiswa Peserta</td><td class='py-3 px-4' style=\"width:2%;\">:</td><td>$namaMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">NIM</td><td>:</td><td>$nimMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Program Studi</td><td>:</td><td>" . $prodiData['nama'] . "</td></tr>

        </table>



        <p style=\"font-weight:bold; margin-bottom:6px;\">Daftar Kehadiran Seminar Proposal yang Diikuti:</p>

        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:20px;\">

            <thead>
<tr class='border-b border-slate-200 dark:border-slate-700'>

                <tr style=\"background-color:#f0f0f0; text-align:center;\">

                    <th style=\"width:5%; padding:6px;\">No</th>

                    <th style=\"width:35%; padding:6px;\">Nama Penyaji / Mahasiswa</th>

                    <th style=\"width:35%; padding:6px;\">Judul Proposal</th>

                    <th style=\"width:15%; padding:6px;\">Tanggal</th>

                    <th style=\"width:10%; padding:6px;\">Paraf</th>

                </tr>

            </thead>

            <tbody class='divide-y divide-slate-100 dark:divide-slate-700'>

                " . str_repeat("<tr><td class='py-3 px-4' style='text-align:center; padding:12px;'></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td><td class='py-3 px-4' class=\"editable-cell\" contenteditable=\"true\"></td></tr>", 8) . "

            </tbody>

        </table>



        <table style=\"width:100%; border:none; text-align:center; font-size:10pt; margin-top:20px;\">

            <tr>

                <td class='py-3 px-4' style=\"border:none;\"><b>Ketua Program Studi</b>" . getTtdDosen($prodiData['nama_kaprodi']) . e($prodiData['nama_kaprodi']) . "</td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 11: Pernyataan Orisinalitas

        'isi' => "

        <h3 style=\"text-align:center; font-size:16pt; margin-top:10px; margin-bottom:30px;\">LEMBAR PERNYATAAN ORISINALITAS</h3>



        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:20px;\">

            Saya yang bertanda tangan di bawah ini:

        </p>



        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:25px; font-size:10pt;\">

            <tr><td class='py-3 px-4' style=\"width:30%; vertical-align:top;\">Nama</td><td class='py-3 px-4' style=\"width:2%; vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">NIM</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Program Studi</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

            <tr><td class='py-3 px-4' style=\"vertical-align:top;\">Judul Proposal</td><td class='py-3 px-4' style=\"vertical-align:top;\">:</td><td class='py-3 px-4' style=\"vertical-align:top; text-align:justify;\">$judulTesis</td></tr>

        </table>



        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:15px;\">

            Dengan ini menyatakan bahwa proposal tesis saya yang berjudul di atas adalah <b>benar-benar merupakan hasil karya saya sendiri</b> dan bukan merupakan plagiat dari karya orang lain. Sumber yang saya gunakan, baik berupa kutipan langsung maupun tidak langsung, telah saya tuliskan secara lengkap dalam daftar pustaka.

        </p>

        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:15px;\">

            Apabila di kemudian hari terbukti bahwa pernyataan ini tidak benar, saya bersedia menerima sanksi akademik yang berlaku di Universitas Nusa Putra.

        </p>



        <table style=\"width:100%; border:none; font-size:10pt; margin-top:40px;\">

            <tr>

                <td class='py-3 px-4' style=\"width:60%; border:none;\"></td>

                <td class='py-3 px-4' style=\"width:40%; border:none; text-align:center;\">

                    " . ($kota ?: 'Sukabumi') . ", " . formatTanggal($tanggalSurat) . "<br>

                    Yang Membuat Pernyataan,<br><br><br><br>

                    $namaMhs<br>

                    <small>NIM. $nimMhs</small>

                </td>

            </tr>

        </table>

        "

    ],

];



foreach ($allLampiran as $global_idx => &$lamp) {

    $lamp_id = $global_idx + 1;

    $cell_count = 0;

    $lamp['isi'] = preg_replace_callback('/class=\"editable-cell\"/', function($m) use (&$cell_count, $lamp_id) {

        return 'id="cell_' . $lamp_id . '_' . ($cell_count++) . '" class="editable-cell"';

    }, $lamp['isi']);

    

    $tinymce_count = 0;

    $lamp['isi'] = preg_replace_callback('/class=\"tinymce\"/', function($m) use (&$tinymce_count, $lamp_id) {

        return 'id="tinymce_' . $lamp_id . '_' . ($tinymce_count++) . '" class="tinymce"';

    }, $lamp['isi']);

}



// Filter jika hanya minta 1 lampiran

if ($idxParam !== 'all' && is_numeric($idxParam)) {

    $i = (int)$idxParam;

    // Lampiran 5 Berita Acara terdiri dari 2 halaman (index 4 dan 5)

    if ($i === 4) {

        $lampiranData = [$allLampiran[4], $allLampiran[5]];

    } elseif (isset($allLampiran[$i])) {

        $lampiranData = [$allLampiran[$i]];

    } else {

        $lampiranData = $allLampiran; // fallback

    }

} else {

    $lampiranData = $allLampiran;

}



$safe_namaMhs = preg_replace('/[^a-zA-Z0-9 \-\.,]/', '', $namaMhs);
$s_kp = preg_replace('/[^a-zA-Z0-9 \-\.,]/', '', $ketuaPembimbing);
$s_ap = preg_replace('/[^a-zA-Z0-9 \-\.,]/', '', $anggotaPembimbing);
$s_kpeng = preg_replace('/[^a-zA-Z0-9 \-\.,]/', '', $ketuaPenguji);
$s_apeng = preg_replace('/[^a-zA-Z0-9 \-\.,]/', '', $anggotaPenguji);

if ($idxParam === 'all') {
    $safe_title = "Semua Lampiran Seminar Proposal";
} else {
    $i = (int)$idxParam;
    switch ($i) {
        case 0:
            $safe_title = "Lampiran 1. $s_kp - Form Catatan Penilaian";
            break;
        case 1:
            $safe_title = "Lampiran 2. $s_ap - Form Catatan Penilaian";
            break;
        case 2:
            $safe_title = "Lampiran 3. $s_kpeng - Form Catatan Penilaian";
            break;
        case 3:
            $safe_title = "Lampiran 4. $s_apeng - Form Catatan Penilaian";
            break;
        case 4:
            $safe_title = "Lampiran 5. Berita Acara Penilaian Seminar Proposal - Hal.1";
            break;
        case 5:
            $safe_title = "Lampiran 5. Berita Acara Penilaian Seminar Proposal - Hal.2";
            break;
        case 6:
            $safe_title = "Lampiran 6. Lembar Pengesahan Tesis";
            break;
        case 7:
            $safe_title = "Lampiran 7. Lembar Persetujuan Ujian Tesis";
            break;
        case 8:
            $safe_title = "Lampiran 8. Kartu Bimbingan Tesis";
            break;
        default:
            $safe_title = "Lampiran Proposal";
            break;
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
        
        .kertas-body { flex: 1; background: #fff; padding: 0mm 21mm 32mm 21mm; font-family: 'Rockwell', 'Courier New', serif; font-size: 10pt; line-height: 1.3; }
        
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
            @page { size: A4; margin: 0; }
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
        <a href="../pages/buat_lampiran_proposal?step=result<?= $id ? '&id='.$id : '' ?>" class="tb-inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all text-sm"
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
                    wordHtml += '@page WordSection1 { size: 595.3pt 841.9pt; margin: ' + marginTopPt + 'pt 42.55pt ' + marginBottomPt + 'pt 42.55pt; mso-header-margin: 35.4pt; mso-footer-margin: 0pt; mso-header: h1; mso-footer: f1; mso-title-page: no; mso-paper-source: 0; }';
                    wordHtml += 'div.WordSection1 { page: WordSection1; }';
                    wordHtml += 'body { font-family: Rockwell, "Courier New", serif; font-size: 11pt; color: #000; margin: 0; padding: 0; }';
                    wordHtml += 'table { border-collapse: collapse; width: 100%; }';
                    wordHtml += '.table-bordered, .table-bordered th, .table-bordered td { border: 1pt solid #000; }';
                    wordHtml += 'th, td { padding: 2pt 4pt; vertical-align: top; font-family: Rockwell, "Courier New", serif; font-size: 11pt; }';
                    wordHtml += 'img { max-width: 100%; height: auto; }';
                    wordHtml += 'p { margin: 0; padding: 0; mso-style-unhide: no; }';
                    wordHtml += '</style>';
                    wordHtml += '</head><body>';
                    wordHtml += '<div class="WordSection1">';
                    wordHtml += pagesHtml;
                    wordHtml += '<table id="hrdftrtbl" border="0" cellspacing="0" cellpadding="0" style="margin: 0in 0in 0in 900in; width: 1px; height: 1px; overflow: hidden;"><tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4 py-3 px-4">';
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
