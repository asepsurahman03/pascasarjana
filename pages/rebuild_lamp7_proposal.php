<?php
// Rebuild cetak_lampiran_proposal.php Lampiran 7 block completely
$file = 'c:/xampp/htdocs/webdummy/pages/cetak_lampiran_proposal.php';
$content = file_get_contents($file);

if (!preg_match('/\[ \/\/ Lampiran 7: Logbook Bimbingan/s', $content, $m7, PREG_OFFSET_CAPTURE)) {
    die("ERROR: Lampiran 7 not found\n");
}
if (!preg_match('/\[ \/\/ Lampiran 8:/s', $content, $m8, PREG_OFFSET_CAPTURE)) {
    die("ERROR: Lampiran 8 not found\n");
}

$start = $m7[0][1];
$end = $m8[0][1];
echo "Lampiran 7: $start, Lampiran 8: $end\n";

$dq = '\\"';

$newLamp7 = '[ // Lampiran 7: Logbook Bimbingan
        \'isi\' => "
        <h3 style=' . $dq . 'text-align:center; font-size:14pt; margin-bottom:20px; font-weight:bold; font-family:Rockwell,serif;' . $dq . '>LOGBOOK BIMBINGAN PROPOSAL TESIS</h3>
        
        <table style=' . $dq . 'width:100%; margin-bottom:20px; font-size:12pt; font-family:Rockwell,serif; border:none;' . $dq . '>
            <tr>
                <td class="py-3 px-4" style=' . $dq . 'width:30%; vertical-align:top; font-weight:bold; padding-bottom:10px; border:none;' . $dq . '>Nama Mahasiswa /<br><i style=' . $dq . 'font-weight:normal;' . $dq . '>Student Name</i></td>
                <td class="py-3 px-4" style=' . $dq . 'width:2%; text-align:center; vertical-align:top; padding-bottom:10px; border:none;' . $dq . '>:</td>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; padding-bottom:10px; border:none;' . $dq . '>$namaMhs</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; font-weight:bold; padding-bottom:10px; border:none;' . $dq . '>NIM / <i style=' . $dq . 'font-weight:normal;' . $dq . '>Student ID</i></td>
                <td class="py-3 px-4" style=' . $dq . 'text-align:center; vertical-align:top; padding-bottom:10px; border:none;' . $dq . '>:</td>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; padding-bottom:10px; border:none;' . $dq . '>$nimMhs</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; font-weight:bold; padding-bottom:10px; border:none;' . $dq . '>Program Studi /<br><i style=' . $dq . 'font-weight:normal;' . $dq . '>Study Program</i></td>
                <td class="py-3 px-4" style=' . $dq . 'text-align:center; vertical-align:top; padding-bottom:10px; border:none;' . $dq . '>:</td>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; padding-bottom:10px; border:none;' . $dq . '>" . $prodiData[\'nama\'] . "</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; font-weight:bold; padding-bottom:10px; border:none;' . $dq . '>Judul Proposal Tesis /<br><i style=' . $dq . 'font-weight:normal;' . $dq . '>Thesis Proposal Title</i></td>
                <td class="py-3 px-4" style=' . $dq . 'text-align:center; vertical-align:top; padding-bottom:10px; border:none;' . $dq . '>:</td>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; padding-bottom:10px; text-align:justify; line-height:1.2; border:none;' . $dq . '>$judulTesis</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; font-weight:bold; padding-bottom:10px; border:none;' . $dq . '>Ketua Pembimbing /<br><i style=' . $dq . 'font-weight:normal;' . $dq . '>Principal Supervisor</i></td>
                <td class="py-3 px-4" style=' . $dq . 'text-align:center; vertical-align:top; padding-bottom:10px; border:none;' . $dq . '>:</td>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; padding-bottom:10px; border:none;' . $dq . '>$ketuaPembimbing</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; font-weight:bold; padding-bottom:15px; border:none;' . $dq . '>Anggota Pembimbing<br>/ <i style=' . $dq . 'font-weight:normal;' . $dq . '>Co-Supervisor</i></td>
                <td class="py-3 px-4" style=' . $dq . 'text-align:center; vertical-align:top; padding-bottom:15px; border:none;' . $dq . '>:</td>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; padding-bottom:15px; border:none;' . $dq . '>$anggotaPembimbing</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; font-weight:bold; padding-bottom:10px; border:none;' . $dq . '>Hari dan Tanggal /<br><i style=' . $dq . 'font-weight:normal;' . $dq . '>Day and Date</i></td>
                <td class="py-3 px-4" style=' . $dq . 'text-align:center; vertical-align:top; padding-bottom:10px; border:none;' . $dq . '>:</td>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; padding-bottom:10px; border:none;' . $dq . ' class=' . $dq . 'editable-cell' . $dq . ' contenteditable=' . $dq . 'true' . $dq . '>Rabu, 13 Mei 2026</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; font-weight:bold; padding-bottom:10px; border:none;' . $dq . '>Tahun Akademik /<br><i style=' . $dq . 'font-weight:normal;' . $dq . '>Academic Year</i></td>
                <td class="py-3 px-4" style=' . $dq . 'text-align:center; vertical-align:top; padding-bottom:10px; border:none;' . $dq . '>:</td>
                <td class="py-3 px-4" style=' . $dq . 'vertical-align:top; padding-bottom:10px; border:none;' . $dq . ' class=' . $dq . 'editable-cell' . $dq . ' contenteditable=' . $dq . 'true' . $dq . '>Semester Genap 2025/2026</td>
            </tr>
        </table>

        <table style=' . $dq . 'width:100%; font-size:12pt; font-family:Rockwell,serif; margin-bottom:0; border-collapse:collapse;' . $dq . ' border=' . $dq . '1' . $dq . '>
            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">
                <tr style=' . $dq . 'background-color:#99295f; color:white; font-weight:bold; text-align:center;' . $dq . '>
                    <th style=' . $dq . 'width:6%; padding:8px;' . $dq . '>No.</th>
                    <th style=' . $dq . 'width:22%; padding:8px;' . $dq . '>Hari / Tanggal</th>
                    <th style=' . $dq . 'width:52%; padding:8px;' . $dq . '>Catatan &amp; Saran Pembimbing</th>
                    <th style=' . $dq . 'width:20%; padding:8px;' . $dq . '>Paraf</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                " . implode("", array_map(function($i) {
                    return "<tr>
                        <td class="py-3 px-4" style=\'text-align:center; padding:25px;\'>$i</td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px;\'></td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px;\'></td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px;\'></td>
                    </tr>";
                }, range(1, 6))) . "
            </tbody>
        </table>
        <table style=' . $dq . 'width:100%; font-size:12pt; font-family:Rockwell,serif; margin-bottom:15px; border-collapse:collapse;' . $dq . ' border=' . $dq . '1' . $dq . '>
            <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">
                <tr style=' . $dq . 'background-color:#99295f; color:white; font-weight:bold; text-align:center;' . $dq . '>
                    <th style=' . $dq . 'width:6%; padding:8px;' . $dq . '>No.</th>
                    <th style=' . $dq . 'width:22%; padding:8px;' . $dq . '>Hari / Tanggal</th>
                    <th style=' . $dq . 'width:52%; padding:8px;' . $dq . '>Catatan &amp; Saran Pembimbing</th>
                    <th style=' . $dq . 'width:20%; padding:8px;' . $dq . '>Paraf</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                " . implode("", array_map(function($i) {
                    return "<tr>
                        <td class="py-3 px-4" style=\'text-align:center; padding:25px;\'>$i</td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px;\'></td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px;\'></td>
                        <td class="py-3 px-4" class=\'editable-cell\' contenteditable=\'true\' style=\'padding:5px;\'></td>
                    </tr>";
                }, range(7, 10))) . "
            </tbody>
        </table>

        <div style=' . $dq . 'font-size:11pt; font-family:Rockwell,serif; margin-bottom:30px;' . $dq . '>
            <p style=' . $dq . 'font-weight:bold; margin-bottom:8px;' . $dq . '>Ketentuan:</p>
            <ul style=' . $dq . 'margin-top:0; padding-left:20px; line-height:1.5;' . $dq . '>
                <li>Logbook ini wajib diisi setiap kali melakukan bimbingan dengan dosen pembimbing.</li>
                <li>Mahasiswa wajib melakukan bimbingan minimal 8 (delapan) kali sebelum pelaksanaan Seminar Proposal/Sidang Tesis.</li>
                <li>Setiap sesi bimbingan harus mendapatkan paraf dari dosen pembimbing yang bersangkutan.</li>
                <li>Logbook ini menjadi salah satu syarat administratif untuk pendaftaran seminar proposal/sidang tesis akhir.</li>
            </ul>
        </div>

        <table style=' . $dq . 'width:100%; border:none; text-align:center; font-size:12pt; font-family:Rockwell,serif;' . $dq . '>
            <tr>
                <td class="py-3 px-4" style=' . $dq . 'width:50%; border:none; padding-bottom:5px; vertical-align:top;' . $dq . '></td>
                <td class="py-3 px-4" style=' . $dq . 'width:50%; border:none; padding-bottom:5px; vertical-align:top;' . $dq . '>Sukabumi, ................................. 2026</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style=' . $dq . 'width:50%; border:none; padding-bottom:50px; vertical-align:top; font-weight:bold;' . $dq . '>Ketua Pembimbing,</td>
                <td class="py-3 px-4" style=' . $dq . 'width:50%; border:none; padding-bottom:50px; vertical-align:top; font-weight:bold;' . $dq . '>Anggota Pembimbing,</td>
            </tr>
            <tr>
                <td class="py-3 px-4" style=' . $dq . 'border:none; padding-top:2px; padding-bottom:5px; vertical-align:bottom;' . $dq . '>" . getTtdDosen($ketuaPembimbing) . "$ketuaPembimbing</td>
                <td class="py-3 px-4" style=' . $dq . 'border:none; padding-top:2px; padding-bottom:5px; vertical-align:bottom;' . $dq . '>" . getTtdDosen($anggotaPembimbing) . "$anggotaPembimbing</td>
            </tr>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" colspan=' . $dq . '2' . $dq . ' style=' . $dq . 'border:none; height:10px;' . $dq . '></td></tr>
            <tr>
                <td class="py-3 px-4" colspan=' . $dq . '2' . $dq . ' style=' . $dq . 'border:none; text-align:center; padding-bottom:50px; vertical-align:top;' . $dq . '>Mengetahui,<br><b style=' . $dq . 'display:block; margin-top:5px;' . $dq . '>Ketua Program Studi Magister Informatika</b></td>
            </tr>
            <tr>
                <td class="py-3 px-4" colspan=' . $dq . '2' . $dq . ' style=' . $dq . 'border:none; text-align:center; vertical-align:bottom;' . $dq . '>" . getTtdDosen($prodiData[\'nama_kaprodi\']) . e($prodiData[\'nama_kaprodi\']) . "</td>
            </tr>
        </table>
        "
    ],
    ';

$newContent = substr($content, 0, $start) . $newLamp7 . substr($content, $end);
file_put_contents($file, $newContent);
echo "SUCCESS: Written to $file\n";

exec('"C:\\xampp\\php\\php.exe" -l ' . escapeshellarg($file), $out, $ret);
echo implode("\n", $out) . "\n";
echo "Exit code: $ret\n";
?>
