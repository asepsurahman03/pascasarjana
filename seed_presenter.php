<?php
require 'config/database.php';
$db = getDB();

$isi = '<p>Sehubungan dengan pelaksanaan kegiatan akademik pada Program Studi {{NAMA_PRODI}} Universitas Nusa Putra, kami bermaksud memohon izin bagi mahasiswa kami yang juga merupakan guru di sekolah yang Bapak/Ibu pimpin:</p>

<table style="width:100%;border-collapse:collapse;margin:10pt 0">
<tr style="background:#f5f5f5"><th style="border:1pt solid #000;padding:6pt 8pt;text-align:left">Nama</th><th style="border:1pt solid #000;padding:6pt 8pt">NIM</th><th style="border:1pt solid #000;padding:6pt 8pt">Program Studi</th></tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style="border:1pt solid #000;padding:6pt 8pt">{{NAMA_MAHASISWA}}</td><td class="py-3 px-4" style="border:1pt solid #000;padding:6pt 8pt">{{NIM}}</td><td class="py-3 px-4" style="border:1pt solid #000;padding:6pt 8pt">{{NAMA_PRODI}}</td></tr>
</table>

<p>Mahasiswa tersebut di atas merupakan salah satu delegasi resmi dari Universitas Nusa Putra yang telah terpilih menjadi Presenter dalam kegiatan seminar internasional:</p>

<table style="width:100%;border-collapse:collapse;margin:10pt 0">
<tr style="background:#f5f5f5"><th style="border:1pt solid #000;padding:6pt 8pt;text-align:left">Nama Kegiatan</th><th style="border:1pt solid #000;padding:6pt 8pt">Hari/Tanggal</th><th style="border:1pt solid #000;padding:6pt 8pt">Tempat</th></tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" style="border:1pt solid #000;padding:6pt 8pt">{{JENIS_KEGIATAN}}</td><td class="py-3 px-4" style="border:1pt solid #000;padding:6pt 8pt">{{TANGGAL_KEGIATAN}}</td><td class="py-3 px-4" style="border:1pt solid #000;padding:6pt 8pt">{{TEMPAT}}</td></tr>
</table>

<p>Sehubungan dengan pentingnya kegiatan tersebut bagi pengembangan akademik, kami memohon kiranya Bapak/Ibu dapat memberikan izin untuk mengikuti seluruh rangkaian kegiatan sesuai jadwal yang telah ditentukan.</p>';

$vars = '{{NAMA_MAHASISWA}},{{NIM}},{{NAMA_PRODI}},{{JENIS_KEGIATAN}},{{TANGGAL_KEGIATAN}},{{TEMPAT}}';

$db->prepare("DELETE FROM template_surat WHERE nama_template='Permohonan Izin Menjadi Presenter Seminar'")->execute();
$db->prepare("INSERT INTO template_surat(jenis_surat,nama_template,isi_template,variabel_tersedia,is_massal) VALUES(?,?,?,?,?)")
   ->execute(['Surat Permohonan','Permohonan Izin Menjadi Presenter Seminar',$isi,$vars,0]);

echo "Template ditambahkan: " . $db->lastInsertId();
?>
