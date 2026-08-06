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



function generateFormPenilaian($peran, $namaDosen, $namaMhs, $nimMhs, $prodi, $judulTesis, $tgl) {

    $sub = 'font-family:Rockwell,serif; font-style:italic; color:#555555; font-size:8.5pt;';

    return "

    <div style=\"font-family:Rockwell,serif;\">

    <h3 style=\"text-align:center; font-size:14pt; font-weight:bold; margin-bottom:10px; font-family:Rockwell,serif;\">Form Penilaian &amp; Revisi Seminar Proposal &ndash; $peran</h3>

    <table style=\"width:100%; margin-bottom:12px; border-collapse:collapse; font-family:Rockwell,serif; font-size:12pt;\">

        <tr>

            <td class="py-3 px-4" style=\"width:32%; vertical-align:middle; padding:3px 5px;\"><b>Nama Mahasiswa / </b><i>Student Name</i></td>

            <td class="py-3 px-4" style=\"width:2%; text-align:center; vertical-align:middle; font-weight:bold;\">:</td>

            <td class="py-3 px-4" style=\"vertical-align:middle; padding:3px 5px;\">$namaMhs</td>

        </tr>

        <tr>

            <td class="py-3 px-4" style=\"vertical-align:middle; padding:3px 5px;\"><b>NIM / </b><i>Student ID</i></td>

            <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; font-weight:bold;\">:</td>

            <td class="py-3 px-4" style=\"vertical-align:middle; padding:3px 5px;\">$nimMhs</td>

        </tr>

        <tr>

            <td class="py-3 px-4" style=\"vertical-align:middle; padding:3px 5px;\"><b>Program Studi / </b><i>Study Program</i></td>

            <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; font-weight:bold;\">:</td>

            <td class="py-3 px-4" style=\"vertical-align:middle; padding:3px 5px;\">$prodi</td>

        </tr>

        <tr>

            <td class="py-3 px-4" style=\"vertical-align:top; padding:3px 5px;\"><b>Judul Tesis / </b><i>Thesis Title</i></td>

            <td class="py-3 px-4" style=\"text-align:center; vertical-align:top; font-weight:bold;\">:</td>

            <td class="py-3 px-4" style=\"vertical-align:top; padding:3px 5px; text-align:justify;\">$judulTesis</td>

        </tr>

        <tr>

            <td class="py-3 px-4" style=\"vertical-align:middle; padding:3px 5px;\"><b>$peran / </b><i>Principal Supervisor</i></td>

            <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; font-weight:bold;\">:</td>

            <td class="py-3 px-4" style=\"vertical-align:middle; padding:3px 5px;\">$namaDosen</td>

        </tr>

        <tr>

            <td class="py-3 px-4" style=\"vertical-align:middle; padding:3px 5px;\"><b>Hari dan Tanggal / </b><i>Day and Date</i></td>

            <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; font-weight:bold;\">:</td>

            <td class="py-3 px-4" style=\"vertical-align:middle; padding:3px 5px;\">$tgl</td>

        </tr>

    </table>



    <p style=\"font-weight:bold; margin-bottom:4px; text-align:center; font-family:Rockwell,serif;\"><b>Tabel 1. </b>Penilaian Seminar Proposal</p>

    <table border=\"1\" style=\"width:100%; font-family:Rockwell,serif; font-size:12pt; margin-bottom:10px; border-collapse:collapse;\">

        <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">

            <tr style=\"background-color:#961d5a; text-align:center; color:#ffffff;\">

                <th style=\"padding:6px; font-weight:bold;\">Aspek Penilaian /<br><i style='font-weight:normal;'>Assessment Criteria</i></th>

                <th style=\"width:9%; padding:6px; font-weight:bold;\">Bobot</th>

                <th style=\"width:10%; padding:6px; font-weight:bold;\">Skor (1&ndash;5)</th>

                <th style=\"width:9%; padding:6px; font-weight:bold;\">Nilai</th>

                <th style=\"width:28%; padding:6px; font-weight:bold;\">Catatan Perbaikan</th>

            </tr>

        </thead>

        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">

            <tr>

                <td class="py-3 px-4" style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Signifikansi Latar Belakang Riset dan/atau Fokus Riset, dan Rumusan Masalah</b><br>

                    <span style=\"$sub\">&bull; Relevansi dan urgensi permasalahan yang diteliti</span><br>

                    <span style=\"$sub\">&bull; Ketajaman rumusan masalah / pertanyaan penelitian.</span><br>

                    <span style=\"$sub\">&bull; Keselarasan antara latar belakang dan rumusan masalah</span>

                </td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; padding:4px;\">15%</td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Relevansi dan Kemutakhiran Tinjauan Pustaka</b><br>

                    <span style=\"$sub\">&bull; Cakupan dan kemutakhiran sumber referensi</span><br>

                    <span style=\"$sub\">&bull; Relevansi teori dan penelitian terdahulu</span><br>

                    <span style=\"$sub\">&bull; Posisi penelitian terhadap state of the art</span>

                </td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; padding:4px;\">25%</td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Ketepatan Formulasi Kerangka Pemikiran dan Proposisi Riset / Hipotesis</b><br>

                    <span style=\"$sub\">&bull; Koherensi kerangka konseptual/teoritis</span><br>

                    <span style=\"$sub\">&bull; Ketepatan formulasi hipotesis atau proposisi</span>

                </td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; padding:4px;\">10%</td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Kesesuaian Metode Riset</b><br>

                    <span style=\"$sub\">&bull; Kesesuaian pendekatan/metode dengan tujuan penelitian</span><br>

                    <span style=\"$sub\">&bull; Kelayakan rancangan dan prosedur penelitian</span>

                </td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; padding:4px;\">10%</td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Kemampuan Penulisan Ilmiah</b><br>

                    <span style=\"$sub\">&bull; Sistematika dan kejelasan penyajian</span><br>

                    <span style=\"$sub\">&bull; Kebenaran tata bahasa dan ejaan</span><br>

                    <span style=\"$sub\">&bull; Konsistensi sitasi dan daftar pustaka</span>

                </td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; padding:4px;\">20%</td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"padding:6px; text-align:center; vertical-align:middle;\">

                    <b>Kemampuan Komunikasi dalam Ujian Lisan</b><br>

                    <span style=\"$sub\">&bull; Kejelasan dan ketepatan presentasi</span><br>

                    <span style=\"$sub\">&bull; Kemampuan menjawab pertanyaan pembahas</span>

                </td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; padding:4px;\">20%</td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle; border-color:#99295f;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:middle;\" class=\"editable-cell\" contenteditable=\"true\"></td>

            </tr>

            <tr style=\"font-weight:bold; background-color:#f5f5f5;\">

                <td class="py-3 px-4" colspan=\"3\" style=\"text-align:right; padding:6px;\">Total Nilai=</td>

                <td class="py-3 px-4" style=\"text-align:center; padding:6px;\" class=\"editable-cell\" contenteditable=\"true\"></td>

                <td class="py-3 px-4" style=\"background-color:#f5f5f5;\"></td>

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

            <td class="py-3 px-4" style=\"padding:5px;\">Skor 1<br>Sangat Kurang</td>

            <td class="py-3 px-4" style=\"padding:5px;\">Skor 2<br>Kurang</td>

            <td class="py-3 px-4" style=\"padding:5px;\">Skor 3<br>Cukup</td>

            <td class="py-3 px-4" style=\"padding:5px;\">Skor 4<br>Baik</td>

            <td class="py-3 px-4" style=\"padding:5px;\">Skor 5<br>Sangat Baik</td>

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

        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4 py-3 px-4">A</td><td class="py-3 px-4 py-3 px-4">90&ndash;100</td><td class="py-3 px-4 py-3 px-4">4</td><td class="py-3 px-4 py-3 px-4">Lulus</td></tr>

        <tr style=\"background-color:#faf2f5;\"><td class="py-3 px-4 py-3 px-4">A-</td><td class="py-3 px-4 py-3 px-4">85&ndash;89,99</td><td class="py-3 px-4 py-3 px-4">3,67</td><td class="py-3 px-4 py-3 px-4">Lulus</td></tr>

        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4 py-3 px-4">B+</td><td class="py-3 px-4 py-3 px-4">80&ndash;84,99</td><td class="py-3 px-4 py-3 px-4">3,33</td><td class="py-3 px-4 py-3 px-4">Lulus</td></tr>

        <tr style=\"background-color:#faf2f5;\"><td class="py-3 px-4 py-3 px-4">B</td><td class="py-3 px-4 py-3 px-4">75&ndash;79,99</td><td class="py-3 px-4 py-3 px-4">3</td><td class="py-3 px-4 py-3 px-4">Lulus</td></tr>

        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4 py-3 px-4">B-</td><td class="py-3 px-4 py-3 px-4">70&ndash;74,99</td><td class="py-3 px-4 py-3 px-4">2,67</td><td class="py-3 px-4 py-3 px-4">Tidak Lulus</td></tr>

    </table>



    <table style=\"width:100%; border:none; margin-top:30px; font-family:Rockwell,serif;\">

        <tr>

            <td class="py-3 px-4" style=\"width:60%; border:none;\"></td>

            <td class="py-3 px-4" style=\"width:40%; text-align:left; border:none;\">

                &nbsp;&nbsp;&nbsp;$peran,<br>" . getTtdDosen($namaDosen) . "$namaDosen

            </td>

        </tr>

    </table>

    </div>

    ";

}



$allLampiran = [

    [

        'isi' => generateFormPenilaian('Ketua Pembimbing', $ketuaPembimbing, $namaMhs, $nimMhs, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)

    ],

    [

        'isi' => generateFormPenilaian('Anggota Pembimbing', $anggotaPembimbing, $namaMhs, $nimMhs, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)

    ],

    [

        'isi' => generateFormPenilaian('Ketua Penguji', $ketuaPenguji, $namaMhs, $nimMhs, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)

    ],

    [

        'isi' => generateFormPenilaian('Anggota Penguji', $anggotaPenguji, $namaMhs, $nimMhs, $prodiData['nama'], $judulTesis, $formattedTanggalSidang)

    ],

    [ // Lampiran 5: Berita Acara - Halaman 1 (Tabel Penilaian)

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; font-weight:bold; margin-bottom:20px; font-family:Rockwell,serif;\">Berita Acara Penilaian Seminar Proposal</h3>

        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-family:Rockwell,serif;\">

            <tr>

                <td class="py-3 px-4" style=\"width:38%; vertical-align:top; padding:6px 0; font-size:11pt;\"><b>Nama Mahasiswa /</b><br><i style=\"font-size:10pt; font-weight:normal;\">Student Name</i></td>

                <td class="py-3 px-4" style=\"width:3%; vertical-align:middle; font-weight:bold; padding:6px 6px; font-size:11pt;\">:</td>

                <td class="py-3 px-4" style=\"vertical-align:middle; padding:6px 0; font-size:11pt;\">$namaMhs</td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"vertical-align:top; padding:6px 0; font-size:11pt;\"><b>NIM /</b><br><i style=\"font-size:10pt; font-weight:normal;\">Student ID</i></td>

                <td class="py-3 px-4" style=\"vertical-align:middle; font-weight:bold; padding:6px 6px; font-size:11pt;\">:</td>

                <td class="py-3 px-4" style=\"vertical-align:middle; padding:6px 0; font-size:11pt;\">$nimMhs</td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"vertical-align:top; padding:6px 0; font-size:11pt;\"><b>Program Studi /</b><br><i style=\"font-size:10pt; font-weight:normal;\">Study Program</i></td>

                <td class="py-3 px-4" style=\"vertical-align:middle; font-weight:bold; padding:6px 6px; font-size:11pt;\">:</td>

                <td class="py-3 px-4" style=\"vertical-align:middle; padding:6px 0; font-size:11pt;\">" . $prodiData['nama'] . "</td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"vertical-align:top; padding:6px 0; font-size:11pt;\"><b>Judul Tesis /</b><br><i style=\"font-size:10pt; font-weight:normal;\">Thesis Title</i></td>

                <td class="py-3 px-4" style=\"vertical-align:top; font-weight:bold; padding:6px 6px; font-size:11pt;\">:</td>

                <td class="py-3 px-4" style=\"vertical-align:top; padding:6px 0; font-size:11pt; text-align:justify;\">$judulTesis</td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"vertical-align:top; padding:6px 0; font-size:11pt;\"><b>Hari dan Tanggal /</b><br><i style=\"font-size:10pt; font-weight:normal;\">Day and Date</i></td>

                <td class="py-3 px-4" style=\"vertical-align:middle; font-weight:bold; padding:6px 6px; font-size:11pt;\">:</td>

                <td class="py-3 px-4" style=\"vertical-align:middle; padding:6px 0; font-size:11pt;\">$formattedTanggalSidang</td>

            </tr>

        </table>



        <p style=\"font-weight:bold; margin-bottom:6px; text-align:center; font-family:Rockwell,serif; font-size:11pt;\"><b>Tabel 1.</b> Rekapitulasi Penilaian Seminar Proposal</p>

        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10.5pt; margin-bottom:18px; border-collapse:collapse; font-family:Rockwell,serif;\">

            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">

                <tr style=\"background-color:#961d5a; color:#ffffff; text-align:center;\">

                    <th style=\"width:6%; padding:7px; font-weight:bold;\">No</th>

                    <th style=\"padding:7px; font-weight:bold;\">Nama / <i style=\"font-weight:normal;\">Name</i></th>

                    <th style=\"width:22%; padding:7px; font-weight:bold;\">Peran / <i style=\"font-weight:normal;\">Role</i></th>

                    <th style=\"width:22%; padding:7px; font-weight:bold;\">Tim</th>

                    <th style=\"width:18%; padding:7px; font-weight:bold;\">Nilai (0&ndash;100)</th>

                </tr>

            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">

                <tr>

                    <td class="py-3 px-4" style=\"text-align:center; padding:9px; vertical-align:middle;\">1</td>

                    <td class="py-3 px-4" style=\"padding:9px; text-align:center; vertical-align:middle;\">$ketuaPembimbing</td>

                    <td class="py-3 px-4" style=\"padding:9px; text-align:center; vertical-align:middle;\">Ketua Pembimbing</td>

                    <td class="py-3 px-4" style=\"text-align:center; padding:9px; vertical-align:middle;\">Pembimbing (60%)</td>

                    <td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; vertical-align:middle;\"></td>

                </tr>

                <tr>

                    <td class="py-3 px-4" style=\"text-align:center; padding:9px; vertical-align:middle;\">2</td>

                    <td class="py-3 px-4" style=\"padding:9px; text-align:center; vertical-align:middle;\">$anggotaPembimbing</td>

                    <td class="py-3 px-4" style=\"padding:9px; text-align:center; vertical-align:middle;\">Anggota Pembimbing</td>

                    <td class="py-3 px-4" style=\"text-align:center; padding:9px; vertical-align:middle;\">Pembimbing (60%)</td>

                    <td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; vertical-align:middle;\"></td>

                </tr>

                <tr>

                    <td class="py-3 px-4" style=\"text-align:center; padding:9px; vertical-align:middle;\">3</td>

                    <td class="py-3 px-4" style=\"padding:9px; text-align:center; vertical-align:middle;\">$ketuaPenguji</td>

                    <td class="py-3 px-4" style=\"padding:9px; text-align:center; vertical-align:middle;\">Ketua Penguji</td>

                    <td class="py-3 px-4" style=\"text-align:center; padding:9px; vertical-align:middle;\">Penguji (40%)</td>

                    <td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; vertical-align:middle;\"></td>

                </tr>

                <tr>

                    <td class="py-3 px-4" style=\"text-align:center; padding:9px; vertical-align:middle;\">4</td>

                    <td class="py-3 px-4" style=\"padding:9px; text-align:center; vertical-align:middle;\">$anggotaPenguji</td>

                    <td class="py-3 px-4" style=\"padding:9px; text-align:center; vertical-align:middle;\">Anggota Penguji</td>

                    <td class="py-3 px-4" style=\"text-align:center; padding:9px; vertical-align:middle;\">Penguji (40%)</td>

                    <td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; vertical-align:middle;\"></td>

                </tr>

                <tr style=\"font-weight:bold;\">

                    <td class="py-3 px-4" colspan=\"4\" style=\"text-align:center; padding:9px;\">NILAI AKHIR SEMINAR PROPOSAL / <i>Final Grade</i></td>

                    <td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; padding:9px;\"></td>

                </tr>

                <tr style=\"font-weight:bold;\">

                    <td class="py-3 px-4" colspan=\"4\" style=\"text-align:center; padding:9px;\">HURUF MUTU / <i>Letter Grade</i></td>

                    <td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\" style=\"text-align:center; padding:9px;\"></td>

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

                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:6px; vertical-align:top;\">Ketua Pembimbing</td>

                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:6px; vertical-align:top;\">Anggota Pembimbing</td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"border:none; padding-top:2px; padding-bottom:4px; vertical-align:bottom;\">" . getTtdDosen($ketuaPembimbing) . "<u>$ketuaPembimbing</u></td>

                <td class="py-3 px-4" style=\"border:none; padding-top:2px; padding-bottom:4px; vertical-align:bottom;\">" . getTtdDosen($anggotaPembimbing) . "<u>$anggotaPembimbing</u></td>

            </tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" colspan=\"2\" style=\"border:none; height:30px;\"></td></tr>

            <tr>

                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:6px; vertical-align:top;\">Ketua Penguji</td>

                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:6px; vertical-align:top;\">Anggota Penguji</td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"border:none; padding-top:2px; padding-bottom:4px; vertical-align:bottom;\">" . getTtdDosen($ketuaPenguji) . "<u>$ketuaPenguji</u></td>

                <td class="py-3 px-4" style=\"border:none; padding-top:2px; padding-bottom:4px; vertical-align:bottom;\">" . getTtdDosen($anggotaPenguji) . "<u>$anggotaPenguji</u></td>

            </tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" colspan=\"2\" style=\"border:none; height:30px;\"></td></tr>

            <tr>

                <td class="py-3 px-4" colspan=\"2\" style=\"border:none; text-align:center; padding-bottom:6px;\">Mengetahui,<br>Ketua Program Studi</td>

            </tr>

            <tr>

                <td class="py-3 px-4" colspan=\"2\" style=\"border:none; text-align:center; vertical-align:bottom;\">" . getTtdDosen($prodiData['nama_kaprodi']) . "<u>" . e($prodiData['nama_kaprodi']) . "</u></td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 6: Lembar Pengesahan

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; font-family:Rockwell,serif; font-weight:bold; margin-top:20px; margin-bottom:40px;\">Lembar Pengesahan Revisi</h3>

        

        <table style=\"width:100%; margin-bottom:40px; font-size:11pt; font-family:Rockwell,serif; border:none;\">

            <tr>

                <td class="py-3 px-4" style=\"width:25%; vertical-align:top; padding-bottom:5px; border:none;\">Nama Mahasiswa</td>

                <td class="py-3 px-4" style=\"width:2%; text-align:center; vertical-align:top; padding-bottom:5px; border:none;\">:</td>

                <td class="py-3 px-4" style=\"vertical-align:top; padding-bottom:5px; border:none;\">$namaMhs</td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"vertical-align:top; padding-bottom:5px; border:none;\">NIM</td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:top; padding-bottom:5px; border:none;\">:</td>

                <td class="py-3 px-4" style=\"vertical-align:top; padding-bottom:5px; border:none;\">$nimMhs</td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"vertical-align:top; border:none;\">Judul Proposal</td>

                <td class="py-3 px-4" style=\"text-align:center; vertical-align:top; border:none;\">:</td>

                <td class="py-3 px-4" style=\"vertical-align:top; text-align:justify; line-height:1.5; border:none;\">$judulTesis</td>

            </tr>

        </table>



        <p style=\"text-align:justify; font-size:11pt; font-family:Rockwell,serif; line-height:1.5; margin-bottom:50px;\">

            Telah menyelesaikan revisi sesuai catatan dari pembimbing dan penguji.

        </p>



        <div style=\"text-align:center; font-size:11pt; font-family:Rockwell,serif; margin-bottom:40px;\">Tanda tangan:</div>



        <table style=\"width:100%; border:none; text-align:center; font-size:11pt; font-family:Rockwell,serif;\">

            <tr>

                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top;\">Ketua Pembimbing,</td>

                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top;\">Anggota Pembimbing,</td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;\">" . getTtdDosen($ketuaPembimbing) . "($ketuaPembimbing)</td>

                <td class="py-3 px-4" style=\"border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;\">" . getTtdDosen($anggotaPembimbing) . "($anggotaPembimbing)</td>

            </tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" colspan=\"2\" style=\"border:none; height:30px;\"></td></tr>

            <tr>

                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top;\">Ketua Penguji,</td>

                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top;\">Anggota Penguji,</td>

            </tr>

            <tr>

                <td class="py-3 px-4" style=\"border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;\">" . getTtdDosen($ketuaPenguji) . "($ketuaPenguji)</td>

                <td class="py-3 px-4" style=\"border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;\">" . getTtdDosen($anggotaPenguji) . "($anggotaPenguji)</td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 7: Lembar Bimbingan

        'isi' => "

        <h3 style=\"text-align:center; font-size:16pt; margin-bottom:15px;\">KARTU BIMBINGAN PROPOSAL</h3>

        

        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-size:10pt;\">

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"width:20%; vertical-align:top;\">Nama</td><td class="py-3 px-4" style=\"width:2%; vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">NIM</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Program Studi</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Judul Proposal</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top; text-align:justify;\">$judulTesis</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Ketua Pembimbing</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$ketuaPembimbing</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Anggota Pembimbing</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$anggotaPembimbing</td></tr>

        </table>



        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:15px;\">

            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">

                <tr style=\"background-color:#f0f0f0; text-align:center;\">

                    <th style=\"width:5%; padding:8px;\">No</th>

                    <th style=\"width:15%; padding:8px;\">Tanggal</th>

                    <th style=\"width:50%; padding:8px;\">Materi / Catatan Bimbingan</th>

                    <th style=\"width:15%; padding:8px;\">Paraf Ketua</th>

                    <th style=\"width:15%; padding:8px;\">Paraf Anggota</th>

                </tr>

            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">

                " . str_repeat("<tr>

                    <td class="py-3 px-4" style='padding:15px;'></td>

                    <td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td>

                    <td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td>

                    <td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td>

                    <td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td>

                </tr>", 8) . "

            </tbody>

        </table>

        "

    ],

    [ // Lampiran 9: Formulir Pendaftaran Seminar Proposal

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; margin-bottom:20px;\">FORMULIR PENDAFTARAN SEMINAR PROPOSAL</h3>

        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-size:10pt;\">

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"width:35%; vertical-align:top;\">Nama Mahasiswa</td><td class="py-3 px-4" style=\"width:2%; vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">NIM</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Program Studi</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Judul Proposal</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top; text-align:justify;\">$judulTesis</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Rencana Tanggal Seminar</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$formattedTanggalSidang</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Ketua Pembimbing</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$ketuaPembimbing</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Anggota Pembimbing</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$anggotaPembimbing</td></tr>

        </table>



        <p style=\"font-weight:bold; margin-bottom:8px;\">Kelengkapan Dokumen yang Diserahkan:</p>

        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:20px;\">

            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">

                <tr style=\"background-color:#f0f0f0; text-align:center;\">

                    <th style=\"width:5%; padding:6px;\">No</th>

                    <th style=\"padding:6px;\">Dokumen</th>

                    <th style=\"width:20%; padding:6px;\">Status</th>

                    <th style=\"width:20%; padding:6px;\">Keterangan</th>

                </tr>

            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">1</td><td class="py-3 px-4" style=\"padding:6px;\">Draft Proposal (Hardcopy)</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">2</td><td class="py-3 px-4" style=\"padding:6px;\">Lembar Persetujuan Pembimbing</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">3</td><td class="py-3 px-4" style=\"padding:6px;\">Bukti Kehadiran Seminar</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">4</td><td class="py-3 px-4" style=\"padding:6px;\">Pernyataan Orisinalitas</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">5</td><td class="py-3 px-4" style=\"padding:6px;\">Pas Foto 3x4 (2 lembar)</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">6</td><td class="py-3 px-4" style=\"padding:6px;\">Fotokopi KTM</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

            </tbody>

        </table>



        <table style=\"width:100%; border:none; text-align:center; font-size:10pt; margin-top:30px;\">

            <tr>

                <td class="py-3 px-4" style=\"width:50%; border:none;\"><b>Pemohon,</b><br><br><br>$namaMhs<br><small>$nimMhs</small></td>

                <td class="py-3 px-4" style=\"width:50%; border:none;\"><b>Ketua Program Studi</b>" . getTtdDosen($prodiData['nama_kaprodi']) . e($prodiData['nama_kaprodi']) . "</td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 10: Bukti Kehadiran Seminar

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; margin-bottom:20px;\">BUKTI KEHADIRAN SEMINAR PROPOSAL</h3>

        <p style=\"text-align:center; font-size:10pt; margin-bottom:15px;\">Program Studi " . $prodiData['nama'] . " — Universitas Nusa Putra</p>



        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-size:10pt;\">

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"width:35%; vertical-align:top;\">Nama Mahasiswa Peserta</td><td class="py-3 px-4" style=\"width:2%;\">:</td><td class="py-3 px-4 py-3 px-4">$namaMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">NIM</td><td class="py-3 px-4 py-3 px-4">:</td><td class="py-3 px-4 py-3 px-4">$nimMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Program Studi</td><td class="py-3 px-4 py-3 px-4">:</td><td class="py-3 px-4 py-3 px-4">" . $prodiData['nama'] . "</td></tr>

        </table>



        <p style=\"font-weight:bold; margin-bottom:6px;\">Daftar Kehadiran Seminar Proposal yang Diikuti:</p>

        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:20px;\">

            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">

                <tr style=\"background-color:#f0f0f0; text-align:center;\">

                    <th style=\"width:5%; padding:6px;\">No</th>

                    <th style=\"width:35%; padding:6px;\">Nama Penyaji / Mahasiswa</th>

                    <th style=\"width:35%; padding:6px;\">Judul Proposal</th>

                    <th style=\"width:15%; padding:6px;\">Tanggal</th>

                    <th style=\"width:10%; padding:6px;\">Paraf</th>

                </tr>

            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">

                " . str_repeat("<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style='text-align:center; padding:12px;'></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>", 8) . "

            </tbody>

        </table>



        <table style=\"width:100%; border:none; text-align:center; font-size:10pt; margin-top:20px;\">

            <tr>

                <td class="py-3 px-4" style=\"border:none;\"><b>Ketua Program Studi</b>" . getTtdDosen($prodiData['nama_kaprodi']) . e($prodiData['nama_kaprodi']) . "</td>

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

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"width:30%; vertical-align:top;\">Nama</td><td class="py-3 px-4" style=\"width:2%; vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">NIM</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Program Studi</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Judul Proposal</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top; text-align:justify;\">$judulTesis</td></tr>

        </table>



        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:15px;\">

            Dengan ini menyatakan bahwa proposal tesis saya yang berjudul di atas adalah <b>benar-benar merupakan hasil karya saya sendiri</b> dan bukan merupakan plagiat dari karya orang lain. Sumber yang saya gunakan, baik berupa kutipan langsung maupun tidak langsung, telah saya tuliskan secara lengkap dalam daftar pustaka.

        </p>

        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:15px;\">

            Apabila di kemudian hari terbukti bahwa pernyataan ini tidak benar, saya bersedia menerima sanksi akademik yang berlaku di Universitas Nusa Putra.

        </p>



        <table style=\"width:100%; border:none; font-size:10pt; margin-top:40px;\">

            <tr>

                <td class="py-3 px-4" style=\"width:60%; border:none;\"></td>

                <td class="py-3 px-4" style=\"width:40%; border:none; text-align:center;\">

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



if ($idxParam === 'all') {

    $safe_title = "Semua Lampiran Seminar Proposal ($safe_namaMhs)";

} else {

    $i = (int)$idxParam;

    switch ($i) {

        case 0:

            $safe_title = "Lampiran 1. ($ketuaPembimbing) Form Catatan Penilaian ($safe_namaMhs)";

            break;

        case 1:

            $safe_title = "Lampiran 2. ($anggotaPembimbing) Form Catatan Penilaian ($safe_namaMhs)";

            break;

        case 2:

            $safe_title = "Lampiran 3. ($ketuaPenguji) Form Catatan Penilaian ($safe_namaMhs)";

            break;

        case 3:

            $safe_title = "Lampiran 4. ($anggotaPenguji) Form Catatan Penilaian ($safe_namaMhs)";

            break;

        case 4:

            $safe_title = "Lampiran 5. Berita Acara Penilaian Seminar Proposal - Hal.1 ($safe_namaMhs)";

            break;

        case 5:

            $safe_title = "Lampiran 5. Berita Acara Penilaian Seminar Proposal - Hal.2 ($safe_namaMhs)";

            break;

        case 6:

            $safe_title = "Lampiran 6. Lembar Pengesahan Tesis ($safe_namaMhs)";

            break;

        case 7:

            $safe_title = "Lampiran 7. Lembar Persetujuan Ujian Tesis ($safe_namaMhs)";

            break;

        case 8:

            $safe_title = "Lampiran 8. Kartu Bimbingan Tesis ($safe_namaMhs)";

            break;

        default:

            $safe_ti
            Dengan ini menyatakan bahwa proposal tesis saya yang berjudul di atas adalah <b>benar-benar merupakan hasil karya saya sendiri</b> dan bukan merupakan plagiat dari karya orang lain. Sumber yang saya gunakan, baik berupa kutipan langsung maupun tidak langsung, telah saya tuliskan secara lengkap dalam daftar pustaka.
        </p>
        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:15px;\">
            Apabila di kemudian hari terbukti bahwa pernyataan ini tidak benar, saya bersedia menerima sanksi akademik yang berlaku di Universitas Nusa Putra.
        </p>

        <table style=\"width:100%; border:none; font-size:10pt; margin-top:40px;\">
            <tr>
                <td class="py-3 px-4" style=\"width:60%; border:none;\"></td>
                <td class="py-3 px-4" style=\"width:40%; border:none; text-align:center;\">
                    " . ($kota ?: 'Sukabumi') . ", " . formatTanggal($tanggalSurat) . "<br>
                    Yang Membuat Pernyataan,<br><br><br><br>
                    $namaMhs<br>
                    <small>NIM. $nimMhs</small>
                </td>
            <table style="width:100%; font-size:12pt; font-family:Rockwell,serif; margin-bottom:15px; border-collapse:collapse;" border="1">
            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">
                <tr style="background-color:#99295f; color:white; font-weight:bold; text-align:center;">
                    <th style="width:6%; padding:8px;">No.</th>
                    <th style="width:22%; padding:8px;">Hari / Tanggal</th>
                    <th style="width:52%; padding:8px;">Catatan &amp; Saran Pembimbing</th>
                    <th style="width:20%; padding:8px;">Paraf</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                " . implode("", array_map(function($i) {
                    return "<tr>
                        <td class="py-3 px-4" style='text-align:center; padding:10px;'>$i</td>
                        <td class="py-3 px-4" class='editable-cell' contenteditable='true'></td>
                        <td class="py-3 px-4" class='editable-cell' contenteditable='true'></td>
                        <td class="py-3 px-4" class='editable-cell' contenteditable='true'></td>
                    </tr>";
                }, range(1, 6))) . "
            </tbody>
        </table>
        
        <!-- Pemisah halaman manual -->
        <div style="page-break-after:always;"></div>
        <br><br><br>
        <table style="width:100%; font-size:12pt; font-family:Rockwell,serif; margin-bottom:15px; border-collapse:collapse;" border="1">
            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">
                <tr style="background-color:#99295f; color:white; font-weight:bold; text-align:center;">
                    <th style="width:6%; padding:8px;">No.</th>
                    <th style="width:22%; padding:8px;">Hari / Tanggal</th>
                    <th style="width:52%; padding:8px;">Catatan &amp; Saran Pembimbing</th>
                    <th style="width:20%; padding:8px;">Paraf</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                " . implode("", array_map(function($i) {
                    return "<tr>
                        <td class="py-3 px-4" style='text-align:center; padding:10px;'>$i</td>
                        <td class="py-3 px-4" class='editable-cell' contenteditable='true'></td>
                        <td class="py-3 px-4" class='editable-cell' contenteditable='true'></td>
                        <td class="py-3 px-4" class='editable-cell' contenteditable='true'></td>
                    </tr>";
                }, range(7, 10))) . "
            </tbody>
        </table>

        <div style=\"font-size:12pt; font-family:Rockwell,serif; margin-bottom:30px;\">
            <p style=\"font-weight:bold; margin-bottom:8px;\">Ketentuan:</p>
            <ul style=\"margin-top:0; padding-left:20px; line-height:1.5;\">
                <li>Logbook ini wajib diisi setiap kali melakukan bimbingan dengan dosen pembimbing.</li>
                <li>Mahasiswa wajib melakukan bimbingan minimal 8 (delapan) kali sebelum pelaksanaan Seminar Proposal/Sidang Tesis.</li>
                <li>Setiap sesi bimbingan harus mendapatkan paraf dari dosen pembimbing yang bersangkutan.</li>
                <li>Logbook ini menjadi salah satu syarat administratif untuk pendaftaran seminar proposal/sidang tesis akhir.</li>
            </ul>
        </div>

        <table style=\"width:100%; border:none; text-align:center; font-size:12pt; font-family:Rockwell,serif;\">
            <tr>
                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top;\"></td>
                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top;\">Sukabumi, ................................. 2026</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top; font-weight:bold;\">Ketua Pembimbing,</td>
                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:60px; vertical-align:top; font-weight:bold;\">Anggota Pembimbing,</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style=\"border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;\">" . getTtdDosen($ketuaPembimbing) . "$ketuaPembimbing</td>
                <td class="py-3 px-4" style=\"border:none; padding-top:2px; padding-bottom:15px; vertical-align:bottom;\">" . getTtdDosen($anggotaPembimbing) . "$anggotaPembimbing</td>
            </tr>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" colspan=\"2\" style=\"border:none; height:30px;\"></td></tr>
            <tr>
                <td class="py-3 px-4" colspan=\"2\" style=\"border:none; text-align:center; padding-bottom:60px; vertical-align:top;\">Mengetahui,<br><b style=\"display:block; margin-top:5px;\">Ketua Program Studi Magister Informatika</b></td>
            </tr>
            <tr>
                <td class="py-3 px-4" colspan=\"2\" style=\"border:none; text-align:center; vertical-align:bottom;\">" . getTtdDosen($prodiData['nama_kaprodi']) . e($prodiData['nama_kaprodi']) . "</td>
            </tr>
        </table>
        "
    ],
    [ // Lampiran 8: Persetujuan Pembimbing

        'isi' => "

        <h3 style=\"text-align:center; font-size:16pt; margin-top:10px; margin-bottom:30px;\">LEMBAR PERSETUJUAN UJIAN PROPOSAL</h3>

        

        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:30px; font-size:10pt;\">

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"width:25%; vertical-align:top;\">Nama</td><td class="py-3 px-4" style=\"width:2%; vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">NIM</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Program Studi</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

        </table>



        <p style=\"text-align:center; font-size:10pt; margin-bottom:10px;\">Proposal dengan judul:</p>

        <h2 style=\"text-align:center; font-size:12pt; margin-bottom:30px; line-height:1.5;\">\"$judulTesis\"</h2>

        

        <p style=\"text-align:justify; font-size:10pt; line-height:1.5; margin-bottom:40px;\">

            Telah diperiksa dan disetujui untuk diujikan dalam Seminar Proposal pada Program Studi " . $prodiData['nama'] . " Universitas Nusa Putra.

        </p>



        <p style=\"text-align:center; font-weight:bold; margin-bottom:20px;\">Menyetujui,</p>

        <table style=\"width:100%; border:none; text-align:center; font-size:10pt;\">

            <tr>

                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:10px;\"><b>Ketua Pembimbing</b>" . getTtdDosen($ketuaPembimbing) . "$ketuaPembimbing</td>

                <td class="py-3 px-4" style=\"width:50%; border:none; padding-bottom:10px;\"><b>Anggota Pembimbing</b>" . getTtdDosen($anggotaPembimbing) . "$anggotaPembimbing</td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 9: Formulir Pendaftaran Seminar Proposal

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; margin-bottom:20px;\">FORMULIR PENDAFTARAN SEMINAR PROPOSAL</h3>

        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-size:10pt;\">

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"width:35%; vertical-align:top;\">Nama Mahasiswa</td><td class="py-3 px-4" style=\"width:2%; vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">NIM</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Program Studi</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Judul Proposal</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top; text-align:justify;\">$judulTesis</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Rencana Tanggal Seminar</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$formattedTanggalSidang</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Ketua Pembimbing</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$ketuaPembimbing</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Anggota Pembimbing</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$anggotaPembimbing</td></tr>

        </table>



        <p style=\"font-weight:bold; margin-bottom:8px;\">Kelengkapan Dokumen yang Diserahkan:</p>

        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:20px;\">

            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">

                <tr style=\"background-color:#f0f0f0; text-align:center;\">

                    <th style=\"width:5%; padding:6px;\">No</th>

                    <th style=\"padding:6px;\">Dokumen</th>

                    <th style=\"width:20%; padding:6px;\">Status</th>

                    <th style=\"width:20%; padding:6px;\">Keterangan</th>

                </tr>

            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">1</td><td class="py-3 px-4" style=\"padding:6px;\">Draft Proposal (Hardcopy)</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">2</td><td class="py-3 px-4" style=\"padding:6px;\">Lembar Persetujuan Pembimbing</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">3</td><td class="py-3 px-4" style=\"padding:6px;\">Bukti Kehadiran Seminar</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">4</td><td class="py-3 px-4" style=\"padding:6px;\">Pernyataan Orisinalitas</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">5</td><td class="py-3 px-4" style=\"padding:6px;\">Pas Foto 3x4 (2 lembar)</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"text-align:center; padding:6px;\">6</td><td class="py-3 px-4" style=\"padding:6px;\">Fotokopi KTM</td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>

            </tbody>

        </table>



        <table style=\"width:100%; border:none; text-align:center; font-size:10pt; margin-top:30px;\">

            <tr>

                <td class="py-3 px-4" style=\"width:50%; border:none;\"><b>Pemohon,</b><br><br><br>$namaMhs<br><small>$nimMhs</small></td>

                <td class="py-3 px-4" style=\"width:50%; border:none;\"><b>Ketua Program Studi</b>" . getTtdDosen($prodiData['nama_kaprodi']) . e($prodiData['nama_kaprodi']) . "</td>

            </tr>

        </table>

        "

    ],

    [ // Lampiran 10: Bukti Kehadiran Seminar

        'isi' => "

        <h3 style=\"text-align:center; font-size:14pt; margin-bottom:20px;\">BUKTI KEHADIRAN SEMINAR PROPOSAL</h3>

        <p style=\"text-align:center; font-size:10pt; margin-bottom:15px;\">Program Studi " . $prodiData['nama'] . " — Universitas Nusa Putra</p>



        <table class=\"layout-tabel\" style=\"width:100%; margin-bottom:20px; font-size:10pt;\">

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"width:35%; vertical-align:top;\">Nama Mahasiswa Peserta</td><td class="py-3 px-4" style=\"width:2%;\">:</td><td class="py-3 px-4 py-3 px-4">$namaMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">NIM</td><td class="py-3 px-4 py-3 px-4">:</td><td class="py-3 px-4 py-3 px-4">$nimMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Program Studi</td><td class="py-3 px-4 py-3 px-4">:</td><td class="py-3 px-4 py-3 px-4">" . $prodiData['nama'] . "</td></tr>

        </table>



        <p style=\"font-weight:bold; margin-bottom:6px;\">Daftar Kehadiran Seminar Proposal yang Diikuti:</p>

        <table class=\"table-bordered\" border=\"1\" style=\"width:100%; font-size:10pt; margin-bottom:20px;\">

            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">

                <tr style=\"background-color:#f0f0f0; text-align:center;\">

                    <th style=\"width:5%; padding:6px;\">No</th>

                    <th style=\"width:35%; padding:6px;\">Nama Penyaji / Mahasiswa</th>

                    <th style=\"width:35%; padding:6px;\">Judul Proposal</th>

                    <th style=\"width:15%; padding:6px;\">Tanggal</th>

                    <th style=\"width:10%; padding:6px;\">Paraf</th>

                </tr>

            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">

                " . str_repeat("<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style='text-align:center; padding:12px;'></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td><td class="py-3 px-4" class=\"editable-cell\" contenteditable=\"true\"></td></tr>", 8) . "

            </tbody>

        </table>



        <table style=\"width:100%; border:none; text-align:center; font-size:10pt; margin-top:20px;\">

            <tr>

                <td class="py-3 px-4" style=\"border:none;\"><b>Ketua Program Studi</b>" . getTtdDosen($prodiData['nama_kaprodi']) . e($prodiData['nama_kaprodi']) . "</td>

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

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"width:30%; vertical-align:top;\">Nama</td><td class="py-3 px-4" style=\"width:2%; vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$namaMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">NIM</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">$nimMhs</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Program Studi</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top;\">" . $prodiData['nama'] . "</td></tr>

            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style=\"vertical-align:top;\">Judul Proposal</td><td class="py-3 px-4" style=\"vertical-align:top;\">:</td><td class="py-3 px-4" style=\"vertical-align:top; text-align:justify;\">$judulTesis</td></tr>

        </table>



        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:15px;\">

            Dengan ini menyatakan bahwa proposal tesis saya yang berjudul di atas adalah <b>benar-benar merupakan hasil karya saya sendiri</b> dan bukan merupakan plagiat dari karya orang lain. Sumber yang saya gunakan, baik berupa kutipan langsung maupun tidak langsung, telah saya tuliskan secara lengkap dalam daftar pustaka.

        </p>

        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:15px;\">

            Apabila di kemudian hari terbukti bahwa pernyataan ini tidak benar, saya bersedia menerima sanksi akademik yang berlaku di Universitas Nusa Putra.

        </p>



        <table style=\"width:100%; border:none; font-size:10pt; margin-top:40px;\">

            <tr>

                <td class="py-3 px-4" style=\"width:60%; border:none;\"></td>

                <td class="py-3 px-4" style=\"width:40%; border:none; text-align:center;\">

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



if ($idxParam === 'all') {

    $safe_title = "Semua Lampiran Seminar Proposal ($safe_namaMhs)";

} else {

    $i = (int)$idxParam;

    switch ($i) {

        case 0:

            $safe_title = "Lampiran 1. ($ketuaPembimbing) Form Catatan Penilaian ($safe_namaMhs)";

            break;

        case 1:

            $safe_title = "Lampiran 2. ($anggotaPembimbing) Form Catatan Penilaian ($safe_namaMhs)";

            break;

        case 2:

            $safe_title = "Lampiran 3. ($ketuaPenguji) Form Catatan Penilaian ($safe_namaMhs)";

            break;

        case 3:

            $safe_title = "Lampiran 4. ($anggotaPenguji) Form Catatan Penilaian ($safe_namaMhs)";

            break;

        case 4:

            $safe_title = "Lampiran 5. Berita Acara Penilaian Seminar Proposal - Hal.1 ($safe_namaMhs)";

            break;

        case 5:

            $safe_title = "Lampiran 5. Berita Acara Penilaian Seminar Proposal - Hal.2 ($safe_namaMhs)";

            break;

        case 6:

            $safe_title = "Lampiran 6. Lembar Pengesahan Tesis ($safe_namaMhs)";

            break;

        case 7:

            $safe_title = "Lampiran 7. Lembar Persetujuan Ujian Tesis ($safe_namaMhs)";

            break;

        case 8:

            $safe_title = "Lampiran 8. Kartu Bimbingan Tesis ($safe_namaMhs)";

            break;

        default:

            $safe_ti
            Dengan ini menyatakan bahwa proposal tesis saya yang berjudul di atas adalah <b>benar-benar merupakan hasil karya saya sendiri</b> dan bukan merupakan plagiat dari karya orang lain. Sumber yang saya gunakan, baik berupa kutipan langsung maupun tidak langsung, telah saya tuliskan secara lengkap dalam daftar pustaka.
        </p>
        <p style=\"text-align:justify; font-size:10pt; line-height:1.8; margin-bottom:15px;\">
            Apabila di kemudian hari terbukti bahwa pernyataan ini tidak benar, saya bersedia menerima sanksi akademik yang berlaku di Universitas Nusa Putra.
        </p>

        <table style=\"width:100%; border:none; font-size:10pt; margin-top:40px;\">
            <tr>
                <td class="py-3 px-4" style=\"width:60%; border:none;\"></td>
                <td class="py-3 px-4" style=\"width:40%; border:none; text-align:center;\">
                    " . ($kota ?: 'Sukabumi') . ", " . formatTanggal($tanggalSurat) . "<br>
                    Yang Membuat Pernyataan,<br><br><br><br>
                    $namaMhs<br>
                    <small>NIM. $nimMhs</small>
                </td>
            </tr>
