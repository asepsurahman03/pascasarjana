<?php
/**
 * Cetak Surat — Format NPU (Dokumen Resmi)
 * - Semua background putih (#fff)
 * - Nomor/Lampiran/Perihal di KIRI
 * - Blok TTD: natural flow, gambar menempel teks atas-bawah
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$mode = $_GET['mode'] ?? 'view';
if (!$id)
    die('ID tidak valid');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_content') {
        $html = $_POST['html_kustom'] ?? '';
        dbExecute("UPDATE surat SET html_kustom = ? WHERE id = ?", [$html, $id]);
        header('Content-Type: application/json');
        die(json_encode(['ok' => true]));
    }
    if ($_POST['action'] === 'reset_content') {
        dbExecute("UPDATE surat SET html_kustom = NULL WHERE id = ?", [$id]);
        header('Content-Type: application/json');
        die(json_encode(['ok' => true]));
    }
}

$surat = dbQueryOne("
    SELECT s.*,
           p.nama as prodi_nama, p.kode as prodi_kode, p.prefix_surat,
           p.kota_surat, p.kaprodi as nama_kaprodi, '' as gelar_kaprodi, p.nidn_kaprodi,
           p.foto_ttd, p.foto_cap, p.foto_header, p.foto_footer
    FROM surat s JOIN prodi p ON p.id = s.prodi_id
    WHERE s.id = ?", [$id]);

if (!$surat)
    die('Surat tidak ditemukan');

$kode = $surat['prodi_kode'];
$kota = $surat['kota_surat'] ?: 'Sukabumi';
$tglSurat = formatTanggalSurat($surat['tanggal'], $kota);
$kopUrl = getKopPath($kode, true);
$footerUrl = getFooterPath($kode, true);
$ttdUrl = getTtdUrl($kode);
$capUrl = getCapUrl($kode);
$namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tglTtd = date('d') . ' ' . $namaBulan[(int) date('n')] . ' ' . date('Y');

logActivity('Cetak Surat', 'surat', $surat['nomor_surat']);
?>
<?php
$parts = explode('/', $surat['nomor_surat']);
$nomor_saja = $parts[0] ?? $surat['nomor_surat'];
$jenis = !empty($surat['perihal']) ? $surat['perihal'] : $surat['jenis_surat'];
$penerima = !empty($surat['nama_penerima']) ? $surat['nama_penerima'] : '';

$safe_title = trim($nomor_saja) . ' Surat ' . trim($jenis);
if ($penerima) {
    $safe_title .= ' - ' . trim($penerima);
}
// Bersihkan karakter aneh/newline agar nama file PDF tidak kacau
$safe_title = preg_replace('/[\r\n\t]+/', ' ', $safe_title);
$safe_title = trim(preg_replace('/[^a-zA-Z0-9 \-\.,]/', '', $safe_title));
if (empty($safe_title)) $safe_title = 'Surat_Dokumen';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= $safe_title ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Rockwell', 'Courier New', serif;
            font-size: 12pt;
            color: #000;
            background: #ccc;
        }

        /* ── TOOLBAR ── */
        .toolbar {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: rgba(0, 0, 0, .75);
            padding: 14px;
            border-radius: 12px;
            backdrop-filter: blur(6px);
        }

        .tb-btn {
            padding: 8px 18px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .tb-btn.green {
            background: #22c55e;
            color:#fff;
        }

        .tb-btn.gray {
            background: #64748b;
            color: #fff;
        }

        /* ── WRAPPER ── */
        .screen-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 32px 20px 80px;
            min-height: 100vh;
            gap: 0;
        }

        /* ── CONTAINER SEMUA HALAMAN ── */
        .pages-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }

        /* ── SATU DOKUMEN A4 (mengalir bebas) ── */
        .page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.28);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Footer selalu di bawah halaman 1 */
        #page-1 .footer-area {
            margin-top: auto;
        }

        /* Nomor halaman label (di luar kertas, atas) */
        .page-label {
            font-size: 11px;
            color:var(--color-text-muted);
            font-family: sans-serif;
            text-align: center;
            margin-bottom: 6px;
        }

        /* Area isi yang bisa di-edit */
        .page-body {
            flex: 1;
            outline: none;
            position: relative;
        }

        /* ══════════════════════════════════
   KOP / HEADER — putih, center
   ══════════════════════════════════ */
        .kop-area {
            background: #fff;
            text-align: center;
            padding: 8mm 20mm 4mm;
        }

        .kop-area img.kop-gambar {
            display: block;
            margin: 0 auto 2mm;
            max-height: 26mm;
            /* Sedikit diperbesar */
            max-width: 170mm;
            width: auto;
            object-fit: contain;
            object-position: center;
        }

        .kop-logo-ph {
            width: 45px;
            height: 45px;
            border: 1px dashed #aaa;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 8pt;
            margin: 0 auto 2mm;
        }

        .kop-prodi {
            font-family: 'Rockwell', 'Courier New', serif;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .3px;
            line-height: 1.4;
            color: #000;
        }

        .kop-univ {
            font-family: 'Rockwell', 'Courier New', serif;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .2px;
            color: #000;
            margin-top: 0.5mm;
        }

        /* ══════════════════════════════════
   BODY — putih
   ══════════════════════════════════ */
        .kertas-body {
            flex: 1;
            background: #fff;
            padding: 0mm 21mm 4mm 21mm;
            font-family: 'Rockwell', 'Courier New', serif;
            font-size: 12pt;
            line-height: 1.15;
            overflow: visible;
            /* Garis batas halaman visual tiap 297mm */
            background-image: repeating-linear-gradient(
                to bottom,
                transparent,
                transparent calc(297mm - 1px),
                #cbd5e1 calc(297mm - 1px),
                #cbd5e1 297mm
            );
            background-attachment: local;
        }
        
        /* Halaman tambahan — dari pagination engine */
        .paginated-page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.28);
            padding: 12mm 21mm 22mm 21mm;
            font-family: 'Rockwell', 'Courier New', serif;
            font-size: 12pt;
            line-height: 1.15;
            box-sizing: border-box;
            overflow: hidden;
            page-break-before: always;
        }
        /* Celah abu-abu di antara halaman */
        .page-gap {
            width: 210mm;
            height: 24px;
            background: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #1e293b;
            font-family: sans-serif;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .kota-tgl {
            text-align: right;
            font-size: 12pt;
            margin-bottom: 5mm;
        }

        /* Nomor, Lampiran, Perihal — KIRI */
        .meta-surat {
            margin-bottom: 6mm;
        }

        .meta-surat table,
        .meta-surat td {
            border: none !important;
            outline: none !important;
        }

        .meta-surat table {
            border-collapse: collapse;
        }

        .meta-surat td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 12pt;
        }

        .meta-surat .col-label {
            width: 85px;
        }

        .meta-surat .col-sep {
            width: 16px;
            text-align: center;
        }

        .kepada {
            margin: 5mm 0 4mm;
            font-size: 12pt;
        }

        .kepada-nama {
            font-weight: bold;
        }

        .salam {
            margin-bottom: 4mm;
            font-size: 12pt;
        }

        .isi-surat {
            font-size: 12pt;
            font-family: 'Rockwell', 'Courier New', serif;
            line-height: 1.15;
        }

        .isi-surat p {
            text-align: justify;
            margin: 0 0 5pt 0;
        }

        .isi-surat table {
            width: 100%;
            border-collapse: collapse;
            margin: 8pt 0;
        }

        .isi-surat td,
        .isi-surat th {
            padding: 5pt 8pt;
            border: 1pt solid #000;
            font-size: 11pt;
        }

        .isi-surat th {
            font-weight: bold;
            background: #f5f5f5;
        }

        .penutup {
            margin-top: 4mm;
            font-size: 12pt;
            text-align: justify;
        }

        /* ══════════════════════════════════
   BLOK TANDA TANGAN — sesuai dokumen asli
   ══════════════════════════════════ */
        .ttd-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 8mm;
        }

        /* Box TTD — rata KIRI seperti dokumen asli */
        .ttd-box {
            min-width: 70mm;
            width: max-content;
            /* Agar otomatis selebar teks terpanjang (misal nama prodi yang panjang) */
            max-width: 100%;
            text-align: left;
            font-family: 'Rockwell', 'Courier New', serif;
            font-size: 11pt;
            line-height: 1.4;
        }

        /* "Mengetahui," — normal, rata kiri */
        .ttd-label {
            display: block;
            font-size: 11pt;
            font-weight: normal;
            position: relative;
            z-index: 5;
        }

        /* "Ketua Program Studi Magister Pedagogi" — bold, satu baris penuh */
        .ttd-prodi {
            display: block;
            font-size: 11pt;
            font-weight: bold;
            white-space: nowrap;
            /* ← satu baris, tidak wrap */
            margin-bottom: 0;
            position: relative;
            z-index: 5;
        }

        /* Wrapper gambar TTD+Cap — tinggi kecil agar teks atas & bawah merapat */
        .ttd-gambar {
            position: relative;
            display: block;
            width: 70mm;
            height: 18mm;
            /* Jarak vertikal antara teks atas dan bawah */
            overflow: visible;
            margin: 0;
        }

        /* Gambar TTD — absolute, ukuran besar overlap teks */
        .ttd-gambar img.i-ttd {
            position: absolute;
            top: -6mm;
            /* Naik sedikit menabrak teks prodi di atasnya */
            left: 2mm;
            height: 31mm;
            /* Sedikit dikecilkan dari 36mm */
            width: auto;
            max-width: 65mm;
            object-fit: contain;
            z-index: 2;
            mix-blend-mode: multiply;
            /* Efek tinta asli (background putih jadi transparan) */
        }

        /* Cap — absolute, di kiri overlap dengan teks dan TTD */
        .ttd-gambar img.i-cap {
            position: absolute;
            top: -6mm;
            /* Naik menabrak teks prodi */
            left: -8mm;
            /* Digeser ke kanan agar menempel dengan TTD */
            height: 32mm;
            width: auto;
            max-width: 35mm;
            object-fit: contain;
            opacity: 0.85;
            transform: rotate(-8deg);
            z-index: 1;
            pointer-events: none;
            mix-blend-mode: multiply;
        }

        /* Placeholder jika tidak ada TTD/Cap */
        .ttd-ph {
            height: 18mm;
            width: 65mm;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #bbb;
            font-size: 9pt;
        }

        /* Nama Kaprodi — bold underline, menempel ke gambar */
        .ttd-nama {
            display: block;
            font-weight: bold;
            font-size: 12pt;
            text-decoration: underline;
            margin-top: 0;
            line-height: 1.4;
            position: relative;
            z-index: 5;
        }

        /* Gelar sudah masuk ke nama, tidak perlu baris terpisah */
        .ttd-nidn {
            display: block;
            font-size: 11pt;
            font-weight: normal;
            margin-top: 0;
            position: relative;
            z-index: 5;
        }

        /* ══════════════════════════════════
   FOOTER — putih
   ══════════════════════════════════ */
        .footer-area {
            background: #fff;
            margin-top: auto;
        }

        .footer-area img.footer-gambar {
            width: 100%;
            display: block;
            max-height: 28mm;
            object-fit: contain;
            object-position: center;
        }

        .footer-ph {
            padding: 5px 20mm;
            text-align: center;
            font-size: 8pt;
            color: #555;
            font-family: 'Rockwell', 'Courier New', serif;
            line-height: 1.6;
            border-top: 1px solid #ccc;
        }

        /* ── PRINT ── */
        @media print {

            /* Sembunyikan semua elemen injeksi dari ekstensi browser dll */
            body>* {
                display: none !important;
            }

            /* Pastikan HANYA dokumen surat yang dirender */
            body>.screen-wrap {
                display: block !important;
            }

            body,
            .screen-wrap {
                background: #fff;
                padding: 0;
            }

            .toolbar {
                display: none !important;
            }

            .kertas {
                box-shadow: none;
                width: 100%;
                min-height: 297mm;
                display: flex;
                flex-direction: column;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }

        /* Style tambahan untuk Editor */
        #editor-isi {
            transition: all 0.2s;
            border: 2px dashed transparent;
            padding: 10px;
            margin: -10px;
        }

        #editor-isi:hover,
        #editor-isi:focus {
            border-color: var(--color-primary-hover);
            background: rgba(59, 130, 246, 0.05);
            outline: none;
            border-radius: 8px;
            cursor: text;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>

<body>

    <!-- Tombol Kembali Kiri Atas -->
    <div style="position:fixed; top:16px; left:16px; z-index:999;" class="no-print">
        <a href="../pages/surat_keluaran" class="tb-btn gray"
            style="background:#1e293b; color:#fff; border-radius:8px; padding:10px 16px; text-decoration:none; font-weight:bold; font-family:sans-serif; font-size:13px; box-shadow:0 4px 12px rgba(0,0,0,0.15); display:inline-block;">←
            Kembali</a>
    </div>

    <!-- Toolbar Kanan -->
    <div class="toolbar no-print">
        <button id="btn-save-surat" onclick="simpanIsiSurat()" class="tb-btn"
            style="background:var(--color-primary-hover);color:#fff;display:none;">💾 Simpan Perubahan</button>
        <button onclick="downloadPDFLangsung()" class="tb-btn" style="background:#dc2626;color:#fff;" id="btn-dl-pdf">📥 Download PDF Langsung</button>
        <script>
            function downloadPDFLangsung() {
                var btn = document.getElementById('btn-dl-pdf');
                var oldText = btn.innerHTML;
                btn.innerHTML = '⏳ Sedang Memproses...';
                btn.style.opacity = '0.7';
                btn.disabled = true;

                var element = document.getElementById('pages-container');
                var safeName = "<?= addslashes(preg_replace('/[^a-zA-Z0-9 \-\.,]/', '_', $safe_title)) ?>.pdf";

                // Sembunyikan elemen-elemen yang tidak boleh masuk ke PDF
                var hiddenElements = document.querySelectorAll('.page-label, .page-gap, .live-page-gap, .no-print');
                var originalDisplays = [];
                hiddenElements.forEach(function(el, index) {
                    originalDisplays[index] = el.style.display;
                    el.style.display = 'none';
                });

                var pages = document.querySelectorAll('.page, .extra-page');
                var originalStyles = [];
                pages.forEach(function(p, index) {
                    originalStyles[index] = {
                        boxShadow: p.style.boxShadow,
                        margin: p.style.margin,
                        border: p.style.border,
                        height: p.style.height
                    };
                    p.style.boxShadow = 'none';
                    p.style.margin = '0';
                    p.style.border = 'none';
                    p.style.height = '296.5mm';
                });

                var opt = {
                    margin:       0,
                    filename:     safeName,
                    image:        { type: 'jpeg', quality: 1.0 },
                    html2canvas:  { scale: 2, useCORS: true, letterRendering: true, logging: false, scrollY: 0, scrollX: 0 },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };

                var originalScrollY = window.scrollY;
                window.scrollTo(0, 0);

                html2pdf().set(opt).from(element).save().then(function() {
                    window.scrollTo(0, originalScrollY);
                    pages.forEach(function(p, index) {
                        p.style.boxShadow = originalStyles[index].boxShadow;
                        p.style.margin = originalStyles[index].margin;
                        p.style.border = originalStyles[index].border;
                        p.style.height = originalStyles[index].height;
                    });
                    hiddenElements.forEach(function(el, index) {
                        el.style.display = originalDisplays[index];
                    });
                    btn.innerHTML = oldText;
                    btn.style.opacity = '1';
                    btn.disabled = false;
                }).catch(function(err) {
                    alert('Gagal mendownload PDF. Error: ' + err);
                    hiddenElements.forEach(function(el, index) {
                        el.style.display = originalDisplays[index];
                    });
                    btn.innerHTML = oldText;
                    btn.style.opacity = '1';
                    btn.disabled = false;
                });
            }
        </script>
        <a href="export_word?id=<?= $id ?>" class="tb-btn"
            style="background:#2563eb;color:#fff;text-decoration:none;">📄 Download File Docx</a>
    </div>

    <div class="screen-wrap">
        <!-- Container semua halaman -->
        <div class="pages-container" id="pages-container">

            <!-- HALAMAN 1 (berisi kop, header surat, isi surat) -->
            <div class="page-label no-print">Halaman 1</div>
            <div class="page" id="page-1">
                <!-- KOP -->
                <div class="kop-area">
                    <?php if ($kopUrl): ?>
                        <img src="<?= e($kopUrl) ?>?v=<?= time() ?>" alt="Kop" class="kop-gambar">
                    <?php else: ?>
                        <div class="kop-logo-ph">Logo</div>
                    <?php endif; ?>
                    <div class="kop-prodi">Program Studi <?= e($surat['prodi_nama']) ?></div>
                    <div class="kop-univ">Universitas Nusa Putra</div>
                </div>

                <!-- AREA KONTEN UTAMA (dapat diedit) -->
                <div class="kertas-body" id="editor-full" title="Klik di mana saja untuk mengedit">
                    <?php if (!empty($surat['html_kustom'])): ?>
                        <?= $surat['html_kustom'] ?>
                    <?php else: ?>

                        <div class="kota-tgl"><?= e($tglSurat) ?></div>

                        <div class="meta-surat">
                            <table>
                                <tr><td class="col-label">Nomor</td><td class="col-sep">:</td><td><strong><?= e($surat['nomor_surat']) ?></strong></td></tr>
                                <tr><td class="col-label">Lampiran</td><td class="col-sep">:</td><td><?= e($surat['lampiran'] ?? '-') ?></td></tr>
                                <tr><td class="col-label">Perihal</td><td class="col-sep">:</td><td><strong><?= e($surat['perihal']) ?></strong></td></tr>
                            </table>
                        </div>

                        <div class="kepada">
                            <div>Kepada Yth.</div>
                            <div class="kepada-nama"><?= nl2br(e($surat['nama_penerima'])) ?></div>
                            <div>di tempat</div>
                        </div>

                        <div class="salam">Dengan hormat,</div>

                        <div class="isi-surat">
                            <?= $surat['isi_surat'] ?: '<p><em>(Isi surat belum diisi)</em></p>' ?>
                        </div>



                        <div class="ttd-wrap">
                            <div class="ttd-box">
                                <span class="ttd-label">Mengetahui,</span>
                                <span class="ttd-prodi">Ketua Program Studi <?= e($surat['prodi_nama']) ?></span>
                                <div class="ttd-gambar">
                                    <?php if ($ttdUrl): ?><img class="i-ttd" src="<?= e($ttdUrl) ?>?v=<?= time() ?>" alt="TTD"><?php endif; ?>
                                    <?php if ($capUrl): ?><img class="i-cap" src="<?= e($capUrl) ?>?v=<?= time() ?>" alt="Cap"><?php endif; ?>
                                    <?php if (!$ttdUrl && !$capUrl): ?><div class="ttd-ph">(TTD &amp; Cap)</div><?php endif; ?>
                                </div>
                                <span class="ttd-nama"><?= e($surat['nama_kaprodi'] ?: '________________________') ?></span>
                                <?php if (!empty($surat['nidn_kaprodi'])): ?>
                                    <span class="ttd-nidn">NIDN. <?= e($surat['nidn_kaprodi']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php endif; ?>
                </div><!-- /kertas-body -->

                <!-- FOOTER Halaman 1 -->
                <div class="footer-area">
                    <?php if ($footerUrl): ?>
                        <img src="<?= e($footerUrl) ?>?v=<?= time() ?>" alt="Footer NPU" class="footer-gambar">
                    <?php else: ?>
                        <div class="footer-ph">
                            Universitas Nusa Putra — Jl. Cibolang Kaler No.21, Sukabumi 43152<br>
                            Telp. (0266) 222 206 &nbsp;|&nbsp; www.nusaputra.ac.id
                        </div>
                    <?php endif; ?>
                </div>
            </div><!-- /page-1 -->

        </div><!-- /pages-container -->
    </div><!-- /screen-wrap -->

    <!-- Indikator Zoom + Halaman (Pojok Kanan Bawah) -->
    <div id="zoom-status"
        style="position:fixed; bottom:0; right:0; background:#1e293b; color:#fff; padding:6px 14px; font-size:12px; z-index:1000; border-top-left-radius:8px; font-family:sans-serif; display:flex; align-items:center; gap:10px;"
        title="Klik untuk mereset zoom (100%)" onclick="setZoom(100)">
        <span>🔍 Zoom: <span id="zoom-val">100</span>%</span>
        <span style="color:var(--color-text-subtle);">|</span>
        <span>📄 Halaman: <span id="page-count">1</span></span>
    </div>

    <?php if ($mode === 'print'): ?>
        <script>setTimeout(function () { window.print(); }, 700);</script>
    <?php endif; ?>

    <!-- Fitur Editor Langsung (TinyMCE Inline) -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.2/tinymce.min.js"></script>
    <script>
        let contentChanged = false;
        tinymce.init({
            selector: '#editor-full',
            inline: true,
            menubar: true,
            plugins: 'advlist lists link table image charmap searchreplace visualblocks code fullscreen insertdatetime wordcount',
            toolbar: 'undo redo | fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | removeformat',
            font_family_formats: 'Rockwell=Rockwell,Courier New,serif; Arial=arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif;',
            font_size_formats: '8pt 10pt 11pt 12pt 14pt 18pt 24pt',
            contextmenu: 'link image table',
            table_toolbar: 'tableprops tabledelete | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | tablemergecells tablesplitcells',
            setup: function (editor) {
                // Shortcut Ctrl+S di dalam editor
                editor.addShortcut('meta+s', 'Simpan Perubahan', function() {
                    simpanIsiSurat();
                });

                // Mengizinkan Tab untuk indentasi
                editor.on('keydown', function (e) {
                    if (e.keyCode === 9) { // Tab
                        if (e.shiftKey) return;
                        e.preventDefault();
                        e.stopPropagation();

                        if (editor.selection.isCollapsed()) {
                            // Jika tidak ada teks yang diblok (hanya kursor) -> sisipkan jarak
                            editor.execCommand('mceInsertContent', false, '&emsp;&emsp;');
                        } else {
                            // Jika ada teks yang diblok -> indentasi blok paragrafnya
                            editor.execCommand('Indent');
                        }
                        return false;
                    }
                });

                editor.on('change keyup', function () {
                    contentChanged = true;
                    const btn = document.getElementById('btn-save-surat');
                    if (btn) {
                        btn.style.display = 'inline-block';
                        btn.innerText = '💾 Simpan';
                        btn.disabled = false;
                    }
                    // Update page counter setiap konten berubah
                    setTimeout(updatePageCount, 100);
                });
            },
            init_instance_callback: function(ed) {
                // Update page counter setelah editor selesai load
                setTimeout(updatePageCount, 800);
            }
        });

        function simpanIsiSurat() {
            if (!contentChanged) return;
            const btn = document.getElementById('btn-save-surat');
            btn.innerText = 'Menyimpan...';
            btn.disabled = true;

            const html = tinymce.get('editor-full').getContent();
            const formData = new FormData();
            formData.append('action', 'save_content');
            formData.append('html_kustom', html);

            fetch('cetak_surat.php?id=<?= $id ?>', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(res => {
                    if (res.ok) {
                        btn.innerText = '✅ Tersimpan';
                        contentChanged = false;
                        setTimeout(() => { btn.style.display = 'none'; }, 2000);
                    } else {
                        btn.innerText = '❌ Gagal';
                        btn.disabled = false;
                    }
                })
                .catch(e => {
                    btn.innerText = '❌ Error';
                    btn.disabled = false;
                });
        }

        function resetSurat() {
            if (!confirm('Anda yakin ingin mereset surat ini ke template aslinya? Semua editan bebas Anda akan hilang.')) return;
            const formData = new FormData();
            formData.append('action', 'reset_content');
            fetch('cetak_surat.php?id=<?= $id ?>', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(res => {
                    if (res.ok) location.reload();
                });
        }

        let currentZoom = 100;

        function setZoom(val) {
            if (val < 20) val = 20;
            if (val > 250) val = 250;
            currentZoom = val;
            document.getElementById('zoom-val').innerText = currentZoom;

            var scale = currentZoom / 100;
            // Apply zoom to the pages container
            var container = document.getElementById('pages-container');
            if (container) {
                container.style.transform = 'scale(' + scale + ')';
                container.style.transformOrigin = 'top center';
            }
        }

        // ══════════════════════════════════════════════
        // DOM-SPLIT PAGINATION ENGINE
        // ══════════════════════════════════════════════
        const MM      = 3.7795;
        const A4_PX   = Math.round(297 * MM);      // ~1122px
        const A4_W    = Math.round(210 * MM);      // ~794px
        const MARGIN  = Math.round(21 * MM);       // ~79px margin kiri/kanan
        const CONTENT_W = A4_W - 2 * MARGIN;      // lebar area teks
        let isPreviewMode = false;

        function updatePageCount() {
            const editor = document.getElementById('editor-full');
            if (!editor) return;
            const page1   = document.getElementById('page-1');
            const kop     = page1 ? page1.querySelector('.kop-area')    : null;
            const footer  = page1 ? page1.querySelector('.footer-area') : null;
            const kopH    = kop    ? kop.offsetHeight    : 0;
            const footerH = footer ? footer.offsetHeight : 0;
            const availH1 = A4_PX - kopH - footerH;
            const contentH = editor.scrollHeight;
            let totalPages = 1;
            if (contentH > availH1) {
                totalPages = 1 + Math.ceil((contentH - availH1) / A4_PX);
            }
            const pageCountEl = document.getElementById('page-count');
            if (pageCountEl) pageCountEl.textContent = totalPages;
        }

        // ----- TOGGLE MODE -----
        function togglePreviewMode() {
            isPreviewMode ? enterEditMode() : enterPreviewMode();
        }

        function enterEditMode() {
            isPreviewMode = false;
            const btn = document.getElementById('btn-toggle-mode');
            if (btn) { btn.textContent = '📄 Mode Pratinjau'; btn.style.background = '#7c3aed'; }

            // Hapus halaman hasil paginasi
            document.querySelectorAll('.paginated-page, .page-gap').forEach(el => el.remove());

            // Sembunyikan div pratinjau halaman 1
            const p1prev = document.getElementById('page-1-preview');
            if (p1prev) p1prev.style.display = 'none';

            // Kembalikan page-1 ke mode edit (min-height, tidak fixed)
            const page1 = document.getElementById('page-1');
            page1.style.height   = '';
            page1.style.overflow = '';

            // Tampilkan kembali TinyMCE editor
            const editorEl = document.getElementById('editor-full');
            if (editorEl) editorEl.style.display = '';
        }

        function enterPreviewMode() {
            isPreviewMode = true;
            const btn = document.getElementById('btn-toggle-mode');
            if (btn) { btn.textContent = '✏️ Mode Edit'; btn.style.background = '#f59e0b'; }

            // Kunci page-1 ke ukuran A4 persis agar footer tidak bergeser
            const page1 = document.getElementById('page-1');
            page1.style.height   = '297mm';
            page1.style.overflow = 'hidden';

            // Ambil konten dari TinyMCE (tidak menyentuh DOM-nya)
            const ed   = (typeof tinymce !== 'undefined') ? tinymce.get('editor-full') : null;
            const html = ed ? ed.getContent() : document.getElementById('editor-full').innerHTML;

            // Sembunyikan TinyMCE (kontennya tetap aman)
            const editorEl = document.getElementById('editor-full');
            if (editorEl) editorEl.style.display = 'none';

            renderPaginatedContent(html);
        }

        function renderPaginatedContent(html) {
            const page1     = document.getElementById('page-1');
            const container = document.getElementById('pages-container');

            // Ukur tinggi kop dan footer dari DOM yang sebenarnya
            const kop     = page1.querySelector('.kop-area');
            const footer  = page1.querySelector('.footer-area');
            const kopH    = kop    ? kop.getBoundingClientRect().height    : 0;
            const footerH = footer ? footer.getBoundingClientRect().height : 0;

            // Tinggi area konten yang tersedia di halaman 1
            const page1BodyH = A4_PX - kopH - footerH - Math.round(4 * MM);
            // Halaman berikutnya: padding atas 10mm + bawah 22mm = 32mm
            const pageNBodyH = A4_PX - Math.round(32 * MM);

            // ---- 1. Measurer: pasang di body, tepat di atas viewport agar rendering akurat ----
            const measurer = document.createElement('div');
            measurer.style.cssText = [
                'position:fixed',
                'top:0',
                'left:-9999px',
                'width:' + Math.floor(CONTENT_W) + 'px',
                "font-family:'Rockwell','Courier New',serif",
                'font-size:12pt',
                'line-height:1.15',
                'visibility:hidden',
                'pointer-events:none',
                'z-index:-9999',
                'background:#fff'
            ].join(';');
            measurer.innerHTML = html;
            document.body.appendChild(measurer);

            // Paksa browser hitung layout sebelum kita baca tinggi elemen
            void measurer.offsetHeight;

            // ---- 2. Ukur setiap child element dan pecah ke halaman ----
            const children = Array.from(measurer.children); // hanya element nodes

            const pages  = [];
            let curPage  = [];
            let curH     = 0;
            let maxH     = page1BodyH;

            const flushPage = () => {
                if (curPage.length > 0) pages.push(curPage);
                curPage = []; curH = 0; maxH = pageNBodyH;
            };

            for (const child of children) {
                // getBoundingClientRect lebih akurat dari offsetHeight
                const rect   = child.getBoundingClientRect();
                const childH = rect.height > 0 ? rect.height : (child.offsetHeight || 20);

                if (curH + childH > maxH && curPage.length > 0) flushPage();

                curPage.push(child.cloneNode(true));
                curH += childH;

                // Satu elemen penuh 1 halaman -> paksa page baru
                if (curH >= maxH) flushPage();
            }
            if (curPage.length > 0) pages.push(curPage);

            document.body.removeChild(measurer);

            // ---- 3. Render konten halaman 1 ke div TERPISAH (TinyMCE tidak disentuh) ----
            let p1prev = document.getElementById('page-1-preview');
            if (!p1prev) {
                p1prev = document.createElement('div');
                p1prev.id = 'page-1-preview';
                const editorEl = document.getElementById('editor-full');
                editorEl.parentNode.insertBefore(p1prev, editorEl);
            }
            p1prev.style.cssText = [
                'padding:0 21mm 4mm 21mm',
                "font-family:'Rockwell','Courier New',serif",
                'font-size:12pt', 'line-height:1.15',
                'background:#fff', 'flex:1', 'overflow:hidden',
                'box-sizing:border-box'
            ].join(';');
            p1prev.innerHTML = '';
            (pages[0] || []).forEach(n => p1prev.appendChild(n.cloneNode(true)));

            // ---- 4. Render halaman ke-2, ke-3, dst. ----
            container.querySelectorAll('.paginated-page, .page-gap').forEach(el => el.remove());

            for (let i = 1; i < pages.length; i++) {
                // Celah antar halaman
                const gap = document.createElement('div');
                gap.className = 'page-gap no-print';
                gap.textContent = '▼  Halaman ' + (i + 1) + '  ▼';
                container.appendChild(gap);

                // Halaman baru A4
                const newPage = document.createElement('div');
                newPage.className = 'paginated-page';
                pages[i].forEach(n => newPage.appendChild(n.cloneNode(true)));
                container.appendChild(newPage);
            }

            // Perbarui counter halaman di status bar
            const pageCountEl = document.getElementById('page-count');
            if (pageCountEl) pageCountEl.textContent = Math.max(1, pages.length);
        }

        // Pantau perubahan konten editor (mode edit)
        document.addEventListener('DOMContentLoaded', function() {
            const editorEl = document.getElementById('editor-full');
            if (!editorEl) return;
            const ro = new ResizeObserver(() => updatePageCount());
            ro.observe(editorEl);
            editorEl.addEventListener('keyup', () => updatePageCount());
            setTimeout(updatePageCount, 600);
        });

        // Intercept Ctrl+Scroll untuk Zoom
        window.addEventListener('wheel', function (e) {
            if (e.ctrlKey) {
                e.preventDefault();
                if (e.deltaY < 0) setZoom(currentZoom + 2); // Scroll Up = Zoom In (Sangat Halus)
                else setZoom(currentZoom - 2);              // Scroll Down = Zoom Out (Sangat Halus)
            }
        }, { passive: false });

        // Intercept Ctrl+ dan Ctrl- untuk Zoom, dan Ctrl+S untuk Simpan
        window.addEventListener('keydown', function (e) {
            if (e.ctrlKey || e.metaKey) {
                if (e.key === 's' || e.key === 'S') {
                    e.preventDefault();
                    simpanIsiSurat();
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

        function exportToWord() {
            var wrapper = document.querySelector('.kertas').cloneNode(true);

            // Resolve absolute URLs
            var imgs = wrapper.querySelectorAll('img');
            for (var i = 0; i < imgs.length; i++) { imgs[i].src = imgs[i].src; }

            var kopArea = wrapper.querySelector('.kop-area');
            var footerArea = wrapper.querySelector('.footer-area');
            var kertasBody = wrapper.querySelector('.kertas-body');

            // Ubah blok TTD menjadi Tabel agar Word membaca perataan kanan (Right Align) dengan benar
            var ttdBox = wrapper.querySelector('.ttd-box');
            var ttdWrap = wrapper.querySelector('.ttd-wrap');
            if (ttdBox && ttdWrap) {
                var newTtd = document.createElement('table');
                newTtd.setAttribute('width', '100%');
                newTtd.setAttribute('border', '0');
                newTtd.innerHTML = `<tr>
            <td width="60%"></td>
            <td width="40%" style="text-align:left; font-family:'Rockwell',serif; font-size:11pt;">
                ${ttdBox.innerHTML}
            </td>
        </tr>`;
                ttdWrap.parentNode.replaceChild(newTtd, ttdWrap);
            }

            var kopImg = kopArea && kopArea.querySelector('img') ? `<img src="${kopArea.querySelector('img').src}" style="width:100%;">` : '';
            var footImg = footerArea && footerArea.querySelector('img') ? `<img src="${footerArea.querySelector('img').src}" style="width:100%;">` : '';

            var html = `
    <html xmlns:v="urn:schemas-microsoft-com:vml"
          xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:w="urn:schemas-microsoft-com:office:word"
          xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
          xmlns="http://www.w3.org/TR/REC-html40">
    <head>
    <meta charset="utf-8">
    <title>Export to Word</title>
    <style>
        @page WordSection1 {
            size: 595.3pt 841.9pt; /* A4 */
            margin: 2cm 2cm 2cm 2cm; /* Margin standar NPU */
            mso-header-margin: 35.4pt;
            mso-footer-margin: 35.4pt;
            mso-header: h1;
            mso-footer: f1;
            mso-paper-source: 0;
        }
        div.WordSection1 { page: WordSection1; font-family: "Rockwell", "Courier New", serif; font-size: 12pt; line-height: 1.15; }
        p { margin: 0 0 5pt 0; text-align: justify; }
        table { border-collapse: collapse; }
        td { vertical-align: top; padding: 2px; }
        .meta-surat table, .meta-surat td { border: none !important; }
        .meta-surat .col-label { width: 80px; }
        .meta-surat .col-sep { width: 15px; text-align: center; }
        .kepada { margin: 15px 0 10px; }
        .kepada-nama { font-weight: bold; }
        .salam { margin-bottom: 10px; }
        .penutup { margin-top: 15px; text-align: justify; }
        .ttd-prodi { font-weight: bold; }
    </style>
    </head>
    <body>
    <div class="WordSection1">
        ${kertasBody ? kertasBody.innerHTML : ''}
    </div>
    
    <!-- Word Header (Kop Surat) -->
    <div style="mso-element:header" id="h1">
        <p class="MsoHeader" style="margin:0; text-align:center;">${kopImg}</p>
    </div>
    
    <!-- Word Footer -->
    <div style="mso-element:footer" id="f1">
        <p class="MsoFooter" style="margin:0; text-align:center;">${footImg}</p>
    </div>
    </body>
    </html>
    `;

            var blob = new Blob(['\\ufeff', html], { type: 'application/msword' });
            var url = URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.href = url;

            var filename = 'Surat_<?= preg_replace("/[^A-Za-z0-9]/", "_", $surat["nomor_surat"]) ?>.doc';
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>

</body>

</html>