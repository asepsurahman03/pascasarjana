<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pageTitle  = 'Buku Agenda Surat';
$breadcrumb = [['label'=>'Surat Keluaran','url'=>BASE_URL.'/pages/surat_keluaran.php'],['label'=>'Buku Agenda']];

$allProdi  = getAllProdi();
$namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

$fp  = (int)($_GET['prodi_id'] ?? 0);
$fb  = (int)($_GET['bulan']    ?? date('n'));
$fy  = (int)($_GET['tahun']    ?? date('Y'));

$w = ['1=1']; $p = [];
if ($fp) { $w[] = 's.prodi_id=?'; $p[] = $fp; }
$w[] = 'MONTH(s.tanggal)=?'; $p[] = $fb;
$w[] = 'YEAR(s.tanggal)=?';  $p[] = $fy;

$ws   = implode(' AND ', $w);
$list = dbQuery("
    SELECT s.*, p.nama as pnama, p.kode as pkode, p.prefix_surat, p.warna_hex
    FROM surat s JOIN prodi p ON p.id=s.prodi_id
    WHERE $ws
    ORDER BY s.tanggal ASC, s.nomor_surat ASC", $p);

$tahunList = dbQuery("SELECT DISTINCT YEAR(tanggal) as tahun FROM surat ORDER BY tahun DESC");

// Nama kaprodi prodi terpilih (untuk tanda tangan cetak)
$prodiCetak = $fp ? dbQueryOne("SELECT * FROM prodi WHERE id=?",[$fp]) : null;

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Page Header (Screen Only) -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 no-print">
  <div>
    <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Buku Agenda Surat</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Laporan surat masuk dan keluar per periode</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="surat_keluaran" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold transition-all text-sm shadow-sm border bg-white text-slate-600 border-slate-200 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Kembali
    </a>
    <button onclick="cetakAgenda()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white rounded-xl font-semibold shadow-md hover:shadow-lg transition-all text-sm">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> Cetak Agenda
    </button>
  </div>
</div>

<!-- Filter Bar (tersembunyi saat cetak) -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-5 mb-6 no-print">
  <form method="GET" class="flex flex-wrap gap-4 items-end" id="filter-form">
    <div class="flex-1 min-w-[200px]">
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Program Studi</label>
      <select name="prodi_id" onchange="document.getElementById('filter-form').submit()" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] transition-colors">
        <option value="">Semua Prodi (Gabungan)</option>
        <?php foreach($allProdi as $pr): ?>
        <option value="<?=$pr['id']?>" <?=$fp==$pr['id']?'selected':''?>><?=e($pr['nama'])?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="w-full sm:w-auto min-w-[150px]">
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Bulan</label>
      <select name="bulan" onchange="document.getElementById('filter-form').submit()" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] transition-colors">
        <?php for($b=1;$b<=12;$b++): ?>
        <option value="<?=$b?>" <?=$fb==$b?'selected':''?>><?=$namaBulan[$b]?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="w-full sm:w-auto min-w-[120px]">
      <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tahun</label>
      <select name="tahun" onchange="document.getElementById('filter-form').submit()" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] transition-colors">
        <?php foreach($tahunList as $ty): ?>
        <option value="<?=$ty['tahun']?>" <?=$fy==$ty['tahun']?'selected':''?>><?=$ty['tahun']?></option>
        <?php endforeach; ?>
        <?php if(empty($tahunList)): ?>
        <option value="<?=date('Y')?>" selected><?=date('Y')?></option>
        <?php endif; ?>
      </select>
    </div>
  </form>
</div>

<!-- ===== BUKU AGENDA (tampil di screen & print) ===== -->
<div id="agenda-print-area">

  <!-- Header Cetak -->
  <div class="print-header hidden" style="display:none">
    <div style="text-align:center;margin-bottom:24px;font-family:'Times New Roman',serif">
      <h2 style="font-size:16pt;font-weight:bold;text-transform:uppercase;margin:0 0 5px 0">BUKU AGENDA SURAT KELUARAN</h2>
      <h3 style="font-size:14pt;font-weight:bold;margin:0 0 5px 0">Program Studi <?=$fp&&$prodiCetak?e($prodiCetak['nama']):'Pascasarjana'?></h3>
      <p style="font-size:12pt;margin:0 0 10px 0">Universitas Nusa Putra</p>
      <p style="font-size:11pt;margin:0">Bulan: <?=$namaBulan[$fb]?> <?=$fy?></p>
      <hr style="border-top:3px solid #000;border-bottom:1px solid #000;height:2px;padding:0;margin:15px 0;">
    </div>
  </div>

  <?php if (empty($list)): ?>
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-16 text-center no-print">
    <div class="text-6xl mb-4 opacity-50">📒</div>
    <h3 class="text-xl font-bold text-slate-700 dark:text-slate-300 mb-1">Buku Agenda Kosong</h3>
    <p class="text-slate-500 dark:text-slate-400">Belum ada surat yang diterbitkan pada bulan <?=$namaBulan[$fb]?> <?=$fy?></p>
  </div>
  <?php else: ?>

  <!-- Tabel Buku Agenda -->
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden mb-8 agenda-container">
    <div class="p-5 border-b border-slate-100 dark:border-slate-700/60 no-print">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-lg">
          📒
        </div>
        <div>
          <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white leading-none mb-1">Agenda — <?=$namaBulan[$fb]?> <?=$fy?></h2>
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Menampilkan <?=count($list)?> surat</p>
        </div>
      </div>
    </div>
    
    <div class="overflow-x-auto">
      <table class="w-full text-sm agenda-table">
        <thead class="bg-slate-50 dark:bg-slate-900/50">
          <tr>
            <th class="text-center py-4 px-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-12 border-b border-slate-200 dark:border-slate-700">No</th>
            <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Tanggal & Nomor</th>
            <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Jenis Surat</th>
            <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Ditujukan Kepada</th>
            <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 min-w-[200px]">Perihal</th>
            <th class="text-left py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 w-28">Status</th>
            <th class="text-center py-4 px-4 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 print-col w-24">Paraf</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
        <?php $no = 1; foreach ($list as $s):
          $ts = strtotime($s['tanggal']);
          $stBg = ['Draf'=>'#94a3b8','Proses'=>'#f59e0b','Selesai'=>'#10b981'][$s['status']] ?? '#94a3b8';
        ?>
        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
          <td class="py-4 px-3 text-center font-medium text-slate-400"><?=$no++?></td>
          <td class="py-4 px-4">
            <div class="font-bold text-slate-800 dark:text-white mb-1 whitespace-nowrap"><?=date('d/m/Y',$ts)?></div>
            <div class="font-mono text-sm font-bold text-[#8c0c4c] dark:text-[#f06ea4]"><?=e($s['nomor_surat'])?></div>
          </td>
          <td class="py-4 px-4">
            <div class="font-bold text-slate-800 dark:text-slate-200"><?=e($s['jenis_surat'])?></div>
            <div class="mt-1">
              <span class="text-xs px-2 py-0.5 rounded-full font-bold" style="background:<?=e($s['warna_hex'])?>15;color:<?=e($s['warna_hex'])?>; border: 1px solid <?=e($s['warna_hex'])?>40">
                <?=e($s['prefix_surat']?:$s['pkode'])?>
              </span>
            </div>
          </td>
          <td class="py-4 px-4">
            <div class="font-bold text-slate-800 dark:text-slate-200"><?=e($s['nama_penerima'])?></div>
            <?php if(!empty($s['nim_nidn'])): ?>
            <div class="text-slate-400 dark:text-slate-500 font-mono text-xs mt-0.5"><?=e($s['nim_nidn'])?></div>
            <?php endif; ?>
          </td>
          <td class="py-4 px-4 text-slate-600 dark:text-slate-300">
            <?=e($s['perihal'])?>
          </td>
          <td class="py-4 px-4 align-top">
            <span class="text-[11px] font-bold px-2 py-1 rounded-md uppercase tracking-wider whitespace-nowrap" style="background:<?=$stBg?>15;color:<?=$stBg?>; border:1px solid <?=$stBg?>40">
              <?=$s['status']?>
            </span>
          </td>
          <td class="py-4 px-4 print-col" style="border-left:1px dashed #e2e8f0; height:60px;">&nbsp;</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- TTD Buku Agenda (saat cetak) -->
  <?php if ($prodiCetak || !$fp): ?>
  <div class="print-ttd-block" style="display:none;margin-top:60px;font-family:'Times New Roman',serif;font-size:12pt;page-break-inside:avoid;">
    <div style="float:right; text-align:center; width:300px">
      <p style="margin:0 0 5px 0"><?=e($prodiCetak['kota_surat']??'Sukabumi')?>, ....................................</p>
      <p style="margin:0 0 70px 0">Ketua Program Studi</p>
      <p style="font-weight:bold;text-decoration:underline;margin:0"><?=e($prodiCetak['nama_kaprodi']??'________________________')?></p>
      <?php if(!empty($prodiCetak['nidn_kaprodi'])): ?><p style="margin:5px 0 0 0">NIDN. <?=e($prodiCetak['nidn_kaprodi'])?></p><?php endif; ?>
    </div>
    <div style="clear:both;"></div>
  </div>
  <?php endif; ?>

</div>

<!-- Print Styles -->
<style>
@media print {
    body { background: #fff !important; color: #000 !important; font-family: 'Times New Roman', serif !important; }
    .no-print { display: none !important }
    .print-col { display: table-cell !important }
    .print-header { display: block !important }
    .print-ttd-block { display: block !important }
    .sidebar, .main-content > header, .topbar { display: none !important }
    .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    
    .agenda-container { 
        box-shadow: none !important; 
        border: none !important; 
        border-radius: 0 !important;
        background: #fff !important; 
        margin-bottom: 0 !important;
    }
    .agenda-table { 
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 11pt !important;
    }
    .agenda-table th, .agenda-table td { 
        border: 1px solid #000 !important; 
        padding: 6px 10px !important; 
        color: #000 !important; 
        background: transparent !important;
    }
    .agenda-table th { 
        background: #f0f0f0 !important; 
        font-weight: bold !important; 
        text-transform: uppercase;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .agenda-table span {
        border: none !important;
        background: transparent !important;
        color: #000 !important;
        padding: 0 !important;
    }
    @page { size: A4 landscape; margin: 15mm }
}
@media screen {
    .print-col { display: none }
    .print-header { display: none }
    .print-ttd-block { display: none }
}
</style>

<script>
function cetakAgenda() {
    // Tampilkan elemen khusus print sebelum memanggil dialog print
    document.querySelector('.print-header').style.display = 'block';
    if(document.querySelector('.print-ttd-block')) {
        document.querySelector('.print-ttd-block').style.display = 'block';
    }
    
    window.print();
    
    // Sembunyikan kembali setelah dialog print ditutup (dengan sedikit delay)
    setTimeout(() => {
        document.querySelector('.print-header').style.display = 'none';
        if(document.querySelector('.print-ttd-block')) {
            document.querySelector('.print-ttd-block').style.display = 'none';
        }
    }, 500);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
