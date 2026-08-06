<?php
/**
 * PDF Controller
 * Output 100% identik dengan PERNYATAAN PENGUNDURAN DIRI MAHASISWA.docx
 *
 * Struktur dokumen DOCX (dari atas ke bawah):
 * ─────────────────────────────────────────────────────────────────
 * 1. TABLE 0 – KOP SURAT: logo (19.2%) | judul (80.8%), tinggi 24.8mm
 * 2. IMAGE kop-footer.jpg (strip tipis berisi nama gedung + sosmed), 210mm × ~15mm
 *    → Ini adalah image yang ada DI DALAM halaman, tepat antara kop & form
 * 3. TABLE 1 – FORM DATA (9 kolom, border 1.5px)
 * 4. IMAGE kop-header.jpg (footer bawah: AQAS, eqar, KAN, BAN-PT logos), 200mm × 28.3mm
 *    → Ini adalah footer paling bawah halaman
 *
 * PAGE  : A4 (210×297mm), margin semua sisi 12.7mm
 * FONT  : Times New Roman 11pt, bold untuk label
 * BORDER: 1.5px solid #000
 * ─────────────────────────────────────────────────────────────────
 */

class PDFController
{
    public function generate(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { die('ID tidak valid'); }

        $pengajuan = PengunduranDiri::findById($id);
        if (!$pengajuan) { die('Data pengajuan tidak ditemukan'); }

        $signature = DigitalSignature::findByPengunduranId($id);

        $title = 'Pernyataan Pengunduran Diri - ' . e($pengajuan['nama_pemohon']);

        $nomorSurat = $pengajuan['nomor_surat'] ?: 'Diisi oleh SASU';

        $isBeasiswa    = ($pengajuan['status_mahasiswa'] === 'Beasiswa');
        $isNonBeasiswa = !$isBeasiswa;
        $isYa          = ($pengajuan['bersedia_mundur'] === 'YES');
        $isTidak       = !$isYa;

        // Checkbox — border 1.5px solid black, checked=hitam solid dengan centang putih
        $cb = fn(bool $checked) => $checked
            ? '<span style="display:inline-block;width:12px;height:12px;border:1.5px solid #000;background:#000;color:#fff;text-align:center;font-size:9px;font-weight:900;line-height:12px;vertical-align:middle;flex-shrink:0;">&#10003;</span>'
            : '<span style="display:inline-block;width:12px;height:12px;border:1.5px solid #000;background:#fff;vertical-align:middle;flex-shrink:0;"></span>';

        // URL gambar — PERHATIKAN URUTAN GAMBAR:
        // logo-nusaputra-docx.png = logo di kop kiri
        // kop-footer.jpg          = STRIP di antara kop & form (berisi nama gedung, sosmed)
        // kop-header.jpg          = FOOTER BAWAH (berisi logo AQAS, eqar, KAN, BAN-PT)
        $logoUrl   = APP_URL . '/assets/images/logo-nusaputra-docx.png';
        $kopStripUrl = APP_URL . '/assets/images/kop-footer.jpg';   // strip antara kop & form
        $kopFooterUrl = APP_URL . '/assets/images/kop-header.jpg';  // footer bawah halaman

        // Format Tanggal Indonesia dari tanggal_surat
        $bulanIndo = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];

        // Gunakan tanggal_surat dari form (bukan created_at)
        $tglRaw  = $pengajuan['tanggal_surat'] ?: $pengajuan['created_at'];
        $ts      = strtotime($tglRaw);
        $tglIndo = ltrim(date('d', $ts), '0') . ' ' . $bulanIndo[date('m', $ts)] . ' ' . date('Y', $ts);

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= e($title) ?></title>
<style>
/* ===================================================
   RESET
=================================================== */
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

/* ===================================================
   BODY
=================================================== */
body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 11pt;
    color: #000;
    background: #c0c0c0;
}

/* ===================================================
   TOOLBAR (hanya 1, di atas)
=================================================== */
.toolbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 9999;
    background: #1e2742;
    padding: 8px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,.5);
    font-family: Arial, sans-serif;
}
.toolbar button {
    padding: 6px 16px;
    border: none;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: Arial, sans-serif;
}
.btn-print { background: #961d5a; color: #fff; }
.btn-back  { background: #374151; color: #fff; }
.t-status  { margin-left: auto; display: flex; align-items: center; gap: 8px; }
.badge {
    padding: 3px 11px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    font-family: Arial, sans-serif;
}
.badge-Pending  { background:#FEF3C7; color:#92400E; border:1px solid #FCD34D; }
.badge-Approved { background:#D1FAE5; color:#065F46; border:1px solid #6EE7B7; }
.badge-Rejected { background:#FEE2E2; color:#991B1B; border:1px solid #FCA5A5; }
.badge-Draft    { background:#E0E7FF; color:#3730A3; border:1px solid #A5B4FC; }

/* ===================================================
   PAGE WRAPPER
=================================================== */
.page-wrap {
    padding-top: 56px;
    padding-bottom: 28px;
}

/* ===================================================
   PAGE — A4 210×297mm, flexbox column
=================================================== */
.page {
    width: 210mm;
    height: 297mm;
    margin: 14px auto;
    background: #fff;
    box-shadow: 0 4px 28px rgba(0,0,0,.28);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

/* Konten utama: padding A4 12.7mm semua sisi */
.page-content {
    flex: 1;
    min-height: 0;
    padding: 12.7mm 12.7mm 4mm 12.7mm;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 0;
}

/* Footer gambar bawah — selalu di paling bawah, centered */
.page-footer {
    flex-shrink: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: flex-end;
}

/* ===================================================
   TABEL UTAMA — border 1.5px solid black
=================================================== */
.doc-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11pt;
    font-family: 'Times New Roman', Times, serif;
    table-layout: fixed;
}
.doc-table td {
    border: 1.5px solid #000;
    padding: 3px 7px;
    vertical-align: middle;
    word-wrap: break-word;
    overflow: hidden;
}

/* ===================================================
   TABLE 0 — KOP SURAT
   Tinggi baris: 1408 twips = 24.8mm
   Col-0: 2041/10627 = 19.2% (logo)
   Col-1: 8586/10627 = 80.8% (judul)
=================================================== */
.kop-row { height: 24.8mm; }

.kop-logo-cell {
    width: 19.2%;
    text-align: center;
    vertical-align: middle;
    padding: 4px 6px;
}
.kop-title-cell {
    width: 80.8%;
    text-align: center;
    vertical-align: middle;
    padding: 4px 8px;
}
.kop-line1,
.kop-line2 {
    display: block;
    font-size: 14pt;
    font-weight: bold;
    line-height: 1.3;
    font-family: 'Times New Roman', Times, serif;
    letter-spacing: 0.5px;
}

/* ===================================================
   STRIP ANTARA KOP & FORM (kop-footer.jpg)
=================================================== */
.kop-strip-img {
    width: 100%;
    height: 15mm;
    object-fit: fill;
    display: block;
}

/* ===================================================
   TABLE 1 — FORM DATA
   9 kolom: 1556|408|427|1399|1041|482|1177|706|3431 twips
   Total: 10627 twips

   Row heights (twips → mm, 1 twip = 25.4/1440 mm):
     Row 0 NOMOR   : 567 twips = 10mm
     Row 1-4 std   : 397 twips =  7mm
     Row 5-6 chkbx : 397 twips =  7mm
     Row 7 ALASAN  : 1175 twips = 20.7mm (min)
     Row 8 PEMOHON : 340 twips =  6mm
     Row 9 MHS     : 397 twips =  7mm
     Row 10 TTD    : ~1980 twips = 35mm
     Row 11 Catatan: auto
=================================================== */

/* Row heights */
.row-nomor { height: 10mm; }
.row-std   { height: 7mm;  }
.row-sm    { height: 6mm;  }

/* ROW 0: NOMOR / TANGGAL
   NOMOR      : col1+2     = 1964 dxa = 18.48%
   Diisi SASU : col3+4+5   = 2867 dxa = 26.98%
   TANGGAL    : col6+7+8   = 2365 dxa = 22.26%
   Nilai tgl  : col9       = 3431 dxa = 32.28%
*/
.c-nomor-lbl { width: 18.48%; }
.c-nomor-val { width: 26.98%; }
.c-tgl-lbl   { width: 22.26%; }
.c-tgl-val   { width: 32.28%; }

/* Placeholder italic abu — 8pt, Arial */
.ph {
    font-style: italic;
    color: #A6A6A6;
    font-size: 8pt;
    font-family: Arial, sans-serif;
}

/* ROW 1–4: label + nilai
   Label: col1+2+3+4 = 3790 dxa = 35.66%
   Nilai: col5+6+7+8+9 = 6837 dxa = 64.34%
*/
.c-lbl { width: 35.66%; font-weight: bold; }
.c-val { width: 64.34%; }

/* ROW 5–6: MAHASISWA / BERSEDIA
   Label : col1+2+3      = 2391 dxa = 22.50%
   Tengah: col4+5+6+7    = 4099 dxa = 38.58%
   Kanan : col8+9        = 4137 dxa = 38.92%
*/
.c-mhs-lbl { width: 22.50%; font-weight: bold; }
.c-mhs-mid { width: 38.58%; padding: 3px 8px; }
.c-mhs-rgt { width: 38.92%; padding: 3px 8px; }

/* Checkbox + teks inline */
.cb-row {
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: bold;
    font-size: 11pt;
    line-height: 1;
}

/* ROW 7: ALASAN */
.c-alasan-lbl {
    width: 14.64%;
    height: 22mm;
    vertical-align: top;
    padding-top: 5px;
}
.c-alasan-val {
    width: 85.36%;
    height: 22mm;
    vertical-align: top;
    white-space: pre-wrap;
}

/* ROW 8–9: PEMOHON / PERSETUJUAN
   Kiri  6col = 5313 dxa ≈ 50%
   Kanan 3col = 5314 dxa ≈ 50%
*/
.c-half { width: 50%; text-align: center; }

/* ROW 10: TTD area */
.c-ttd {
    width: 50%;
    text-align: center;
    vertical-align: bottom;
    height: 35mm;
    padding: 5px 14px 6px;
}

/* Placeholder TTD */
.ttd-ph {
    font-style: italic;
    color: #BFBFBF;
    font-size: 8pt;
    font-family: 'Times New Roman', Times, serif;
    line-height: 1.6;
}

/* ROW 11: Catatan */
.c-catatan {
    height: 45mm;
    vertical-align: top;
}

/* ===================================================
   FOOTER IMAGE BAWAH (kop-header.jpg — AQAS, eqar, dll)
=================================================== */
.kop-footer-img {
    width: 100%;
    height: 28.3mm;
    object-fit: fill;
    display: block;
}

/* ===================================================
   PRINT — sembunyikan toolbar, hapus margin
=================================================== */
@media print {
    @page {
        size: A4;
        margin: 0;
    }
    body           { background: #fff !important; }
    .toolbar       { display: none !important; }
    .page-wrap     { padding-top: 0; padding-bottom: 0; }
    .page          {
        margin: 0;
        box-shadow: none;
        width: 210mm;
        height: 297mm;
        page-break-after: avoid;
        page-break-before: avoid;
    }
    .kop-footer-img { width: 100%; height: 28.3mm; object-fit: fill; }
}
</style>
</head>
<body>

<!-- ===== TOOLBAR (SATU) ===== -->
<div class="toolbar">
    <button class="btn-print" onclick="window.print()">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Cetak / Save PDF
    </button>
    <button class="btn-back" onclick="goBack()">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali
    </button>
    <div class="t-status">
        <span style="color:#94a3b8;font-size:12px;">Status:</span>
        <span class="badge badge-<?= e($pengajuan['status']) ?>"><?= statusLabel($pengajuan['status']) ?></span>
        <span style="color:#64748b;font-size:11px;margin-left:6px;">📄 <?= e($nomorSurat) ?></span>
    </div>
</div>

<div class="page-wrap">
<div class="page">

  <!-- ============================================================
       KONTEN UTAMA (padding 12.7mm semua sisi)
       ============================================================ -->
  <div class="page-content">

    <!-- ==========================================================
         TABLE 0 — KOP SURAT
         2 kolom: logo 19.2% | judul 80.8% — tinggi 24.8mm
         ========================================================== -->
    <table class="doc-table" style="margin-bottom: 5mm;">
      <colgroup>
        <col style="width:19.2%">
        <col style="width:80.8%">
      </colgroup>
      <tr class="kop-row">
        <!-- Logo Nusa Putra 20mm × 20mm -->
        <td class="kop-logo-cell" style="border:1.5px solid #000;">
          <img src="<?= $logoUrl ?>"
               alt="Logo Nusa Putra"
               style="width:20mm; height:20mm; object-fit:contain; display:block; margin:0 auto;"
               onerror="this.style.display='none'">
        </td>
        <!-- Judul 2 baris bold 14pt center -->
        <td class="kop-title-cell" style="border:1.5px solid #000;">
          <span class="kop-line1">PERNYATAAN</span>
          <span class="kop-line2">PENGUNDURAN DIRI MAHASISWA</span>
        </td>
      </tr>
    </table>

    <!-- ==========================================================
         TABLE 1 — FORM DATA
         9 kolom: 1556|408|427|1399|1041|482|1177|706|3431 twips
         ========================================================== -->
    <table class="doc-table" style="margin-top:0; border-top:1.5px solid #000;">
      <colgroup>
        <col style="width:14.64%"> <!-- col1: 1556 -->
        <col style="width:3.84%">  <!-- col2: 408  -->
        <col style="width:4.02%">  <!-- col3: 427  -->
        <col style="width:13.16%"> <!-- col4: 1399 -->
        <col style="width:9.80%">  <!-- col5: 1041 -->
        <col style="width:4.54%">  <!-- col6: 482  -->
        <col style="width:11.08%"> <!-- col7: 1177 -->
        <col style="width:6.64%">  <!-- col8: 706  -->
        <col style="width:32.28%"> <!-- col9: 3431 -->
      </colgroup>

      <!-- ROW 0: NOMOR | Diisi SASU | TANGGAL | nilai tanggal — 10mm -->
      <tr class="row-nomor">
        <td colspan="2" class="c-nomor-lbl">NOMOR</td>
        <td colspan="3" class="c-nomor-val">
          <span class="ph">Diisi oleh SASU</span>
        </td>
        <td colspan="3" class="c-tgl-lbl">TANGGAL</td>
        <td colspan="1" class="c-tgl-val"><?= e($tglIndo) ?></td>
      </tr>

      <!-- ROW 1: NAMA PEMOHON — 7mm -->
      <tr class="row-std">
        <td colspan="4" class="c-lbl">NAMA PEMOHON</td>
        <td colspan="5" class="c-val"><?= e($pengajuan['nama_pemohon']) ?></td>
      </tr>

      <!-- ROW 2: NIM — 7mm -->
      <tr class="row-std">
        <td colspan="4" class="c-lbl">NIM</td>
        <td colspan="5" class="c-val"><?= e($pengajuan['nim']) ?></td>
      </tr>

      <!-- ROW 3: ANGKATAN — 7mm -->
      <tr class="row-std">
        <td colspan="4" class="c-lbl">ANGKATAN</td>
        <td colspan="5" class="c-val"><?= e($pengajuan['angkatan']) ?></td>
      </tr>

      <!-- ROW 4: PROGRAM STUDI — 7mm -->
      <tr class="row-std">
        <td colspan="4" class="c-lbl">PROGRAM STUDI</td>
        <td colspan="5" class="c-val"><?= e($pengajuan['program_studi']) ?></td>
      </tr>

      <!-- ROW 5: MAHASISWA | ☐ BEASISWA | ☐ NON BEASISWA — 7mm
           Label: 3col (22.50%) | Beasiswa: 4col (38.58%) | Non Beasiswa: 2col (38.92%)
      -->
      <tr class="row-std">
        <td colspan="3" class="c-mhs-lbl">MAHASISWA</td>
        <td colspan="4" class="c-mhs-mid">
          <div class="cb-row"><?= $cb($isBeasiswa) ?>&nbsp;BEASISWA</div>
        </td>
        <td colspan="2" class="c-mhs-rgt">
          <div class="cb-row"><?= $cb($isNonBeasiswa) ?>&nbsp;NON BEASISWA</div>
        </td>
      </tr>

      <!-- ROW 6: BERSEDIA MENGUNDURKAN DIRI | ☐ YA | ☐ TIDAK — 7mm -->
      <tr class="row-std">
        <td colspan="3" class="c-mhs-lbl">BERSEDIA MENGUNDURKAN DIRI</td>
        <td colspan="4" class="c-mhs-mid">
          <div class="cb-row"><?= $cb($isYa) ?>&nbsp;YA</div>
        </td>
        <td colspan="2" class="c-mhs-rgt">
          <div class="cb-row"><?= $cb($isTidak) ?>&nbsp;TIDAK</div>
        </td>
      </tr>

      <!-- ROW 7: ALASAN — height 22mm
           Label: 1col (14.64%) | Nilai: 8col (85.36%)
      -->
      <tr>
        <td colspan="1" class="c-alasan-lbl" style="height:22mm;">ALASAN</td>
        <td colspan="8" class="c-alasan-val" style="height:22mm;"><?= nl2br(e($pengajuan['alasan'])) ?></td>
      </tr>

      <!-- ROW 8: PEMOHON | PERSETUJUAN — 6mm -->
      <tr class="row-sm">
        <td colspan="6" class="c-half">PEMOHON</td>
        <td colspan="3" class="c-half">PERSETUJUAN</td>
      </tr>

      <!-- ROW 9: MAHASISWA | KETUA PROGRAM STUDI — 7mm -->
      <tr class="row-std">
        <td colspan="6" class="c-half">MAHASISWA</td>
        <td colspan="3" class="c-half">KETUA PROGRAM STUDI</td>
      </tr>

      <!-- ROW 10: Area Tanda Tangan — ≈35mm -->
      <tr>
        <!-- Kiri: TTD Mahasiswa -->
        <td colspan="6" class="c-ttd" style="height:35mm; vertical-align:bottom; text-align:center;">
          <?php if ($signature && $pengajuan['bersedia_mundur'] === 'YES'): ?>
            <div style="height:25mm; display:flex; align-items:center; justify-content:center;">
              <img src="<?= e($signature['signature_data']) ?>"
                   alt="Tanda Tangan Mahasiswa"
                   style="max-height:24mm; max-width:100mm; object-fit:contain; transform:scale(1.3); transform-origin:center center;">
            </div>
          <?php else: ?>
            <div style="height:25mm;">&nbsp;</div>
          <?php endif; ?>
          <div class="ttd-ph">
            _____________________________<br>
            <strong><?= e($pengajuan['nama_pemohon']) ?></strong><br>
            NIM. <?= e($pengajuan['nim']) ?>
          </div>
        </td>
        <!-- Kanan: TTD Ketua Prodi -->
        <td colspan="3" class="c-ttd" style="height:35mm; vertical-align:bottom; text-align:center;">
          <?php
            $kaprodiTtdUrl = null;
            if ($pengajuan['status'] === 'Approved') {
                $kaprodiTtdUrl = getKaprodiTtdUrl($pengajuan['program_studi']);
            }
          ?>
          <?php if ($kaprodiTtdUrl): ?>
            <div style="height:25mm; display:flex; align-items:center; justify-content:center;">
              <img src="<?= $kaprodiTtdUrl ?>"
                   alt="Tanda Tangan Kaprodi"
                   style="max-height:24mm; max-width:100mm; object-fit:contain;"
                   onerror="this.style.display='none'">
            </div>
          <?php else: ?>
            <div style="height:25mm;">&nbsp;</div>
          <?php endif; ?>
          <div class="ttd-ph">
            _____________________________<br>
            <strong><?= e(getKaprodiName($pengajuan['program_studi'])) ?></strong>
          </div>
        </td>
      </tr>

      <!-- ROW 11: Catatan -->
      <tr>
        <td colspan="9" class="c-catatan">Catatan:</td>
      </tr>

    </table>

  </div><!-- /.page-content -->

  <!-- ============================================================
       FOOTER BAWAH — kop-header.jpg (AQAS, eqar, KAN, BAN-PT)
       Selalu menempel di bawah halaman via flexbox
       ============================================================ -->
  <div class="page-footer">
    <img src="<?= $kopFooterUrl ?>"
         alt="Footer Kop Surat"
         onerror="this.style.display='none'"
         style="width:200mm; max-width:100%; height:28.3mm; object-fit:fill; display:block; margin:0 auto;">
</div>

</div><!-- /.page -->
</div><!-- /.page-wrap -->

<script>
function goBack() {
    if (window.history.length > 1 && document.referrer !== "") {
        window.history.back();
    } else {
        window.close();
        setTimeout(() => { window.location.href = '<?= APP_URL ?>'; }, 100);
    }
}
// Auto-print jika ?print=1
if (new URLSearchParams(location.search).get('print') === '1') {
    setTimeout(() => window.print(), 800);
}
</script>
</body>
</html>
<?php
        $html = ob_get_clean();
        echo $html;
        exit;
    }
}
