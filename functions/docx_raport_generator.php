<?php
/**
 * Generator DOCX native (ZipArchive + OOXML) untuk Raport Laporan Dosen
 * Sesuai layout Excel sheet "Rapot" - tanpa library external
 * Versi 2: embed TTD image + perbaikan tabel kriteria + nama benar
 */

function docxEsc(string $s): string {
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function docxRun(string $text, bool $bold = false, string $size = '18'): string {
    $boldTag = $bold ? '<w:b/><w:bCs/>' : '';
    return '<w:r>'
        . '<w:rPr>'
        .   '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>'
        .   $boldTag
        .   '<w:sz w:val="' . $size . '"/>'
        .   '<w:szCs w:val="' . $size . '"/>'
        .   '<w:color w:val="000000"/>'
        . '</w:rPr>'
        . '<w:t xml:space="preserve">' . docxEsc($text) . '</w:t>'
        . '</w:r>';
}

function docxPara(string $runsXml, string $align = 'left', int $spacingBefore = 0, int $spacingAfter = 0): string {
    $alignTag = '';
    if ($align === 'justify' || $align === 'both') {
        $alignTag = '<w:jc w:val="both"/>';
    } elseif ($align !== 'left') {
        $alignTag = '<w:jc w:val="' . $align . '"/>';
    }
    return '<w:p>'
        . '<w:pPr>'
        .   '<w:pStyle w:val="Normal"/>'
        .   $alignTag
        .   '<w:spacing w:before="' . $spacingBefore . '" w:after="' . $spacingAfter . '" w:line="240" w:lineRule="auto"/>'
        . '</w:pPr>'
        . $runsXml
        . '</w:p>';
}

function docxBorderAll(): array { return ['top'=>'single','left'=>'single','bottom'=>'single','right'=>'single']; }
function docxBorderNone(): array { return ['top'=>'none','left'=>'none','bottom'=>'none','right'=>'none']; }
function docxBorderBottom(): array { return ['top'=>'none','left'=>'none','bottom'=>'single','right'=>'none']; }

function docxCell(string $innerXml, int $width = 1000, array $borders = [], int $gridSpan = 1, string $vAlign = 'top', int $padLeft = 60, int $padRight = 60, int $padTop = 20, int $padBottom = 20): string {
    $borderXml = '';
    if (!empty($borders)) {
        $bparts = '';
        foreach (['top','left','bottom','right'] as $side) {
            $style = $borders[$side] ?? 'none';
            if ($style === 'single') {
                $bparts .= '<w:' . $side . ' w:val="single" w:sz="4" w:space="0" w:color="000000"/>';
            } elseif ($style === 'double') {
                $bparts .= '<w:' . $side . ' w:val="double" w:sz="6" w:space="0" w:color="000000"/>';
            } else {
                $bparts .= '<w:' . $side . ' w:val="none" w:sz="0" w:space="0" w:color="auto"/>';
            }
        }
        $borderXml = '<w:tcBorders>' . $bparts . '</w:tcBorders>';
    }
    $spanXml = $gridSpan > 1 ? '<w:gridSpan w:val="' . $gridSpan . '"/>' : '';
    $marXml = '<w:tcMar>'
        . '<w:top w:w="' . $padTop . '" w:type="dxa"/>'
        . '<w:left w:w="' . $padLeft . '" w:type="dxa"/>'
        . '<w:bottom w:w="' . $padBottom . '" w:type="dxa"/>'
        . '<w:right w:w="' . $padRight . '" w:type="dxa"/>'
        . '</w:tcMar>';
    return '<w:tc>'
        . '<w:tcPr>'
        .   '<w:tcW w:w="' . $width . '" w:type="dxa"/>'
        .   $spanXml
        .   $borderXml
        .   $marXml
        .   '<w:vAlign w:val="' . $vAlign . '"/>'
        . '</w:tcPr>'
        . $innerXml
        . '</w:tc>';
}

function docxRow(array $cells, int $height = 280): string {
    $hXml = $height > 0 ? '<w:trPr><w:trHeight w:val="' . $height . '" w:hRule="atLeast"/></w:trPr>' : '';
    return '<w:tr>' . $hXml . implode('', $cells) . '</w:tr>';
}

function docxTable(array $rows, int $totalWidth = 10035): string {
    return '<w:tbl>'
        . '<w:tblPr>'
        .   '<w:tblW w:w="' . $totalWidth . '" w:type="dxa"/>'
        .   '<w:tblLayout w:type="fixed"/>'
        .   '<w:tblCellMar>'
        .     '<w:top w:w="20" w:type="dxa"/>'
        .     '<w:left w:w="60" w:type="dxa"/>'
        .     '<w:bottom w:w="20" w:type="dxa"/>'
        .     '<w:right w:w="60" w:type="dxa"/>'
        .   '</w:tblCellMar>'
        .   '<w:tblBorders>'
        .     '<w:insideH w:val="none"/>'
        .     '<w:insideV w:val="none"/>'
        .   '</w:tblBorders>'
        .   '<w:tblLook w:val="0000"/>'
        . '</w:tblPr>'
        . implode('', $rows)
        . '</w:tbl>';
}

/**
 * Buat XML untuk inline image (w:drawing) di dokumen Word
 * @param string $rId     relationship ID (mis. "rId2")
 * @param int    $widthEmu  lebar dalam EMU (1 cm = 360000 EMU; 1 px@96dpi = 9144 EMU)
 * @param int    $heightEmu tinggi dalam EMU
 * @param string $name    nama deskriptif
 */
function docxInlineImage(string $rId, int $widthEmu, int $heightEmu, string $name = 'image'): string {
    static $imgIdx = 0;
    $imgIdx++;
    return '<w:r><w:rPr/>'
        . '<w:drawing>'
        .   '<wp:inline distT="0" distB="0" distL="0" distR="0"'
        .     ' xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing">'
        .     '<wp:extent cx="' . $widthEmu . '" cy="' . $heightEmu . '"/>'
        .     '<wp:effectExtent l="0" t="0" r="0" b="0"/>'
        .     '<wp:docPr id="' . $imgIdx . '" name="' . htmlspecialchars($name, ENT_XML1) . '"/>'
        .     '<wp:cNvGraphicFramePr>'
        .       '<a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/>'
        .     '</wp:cNvGraphicFramePr>'
        .     '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
        .       '<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
        .         '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
        .           '<pic:nvPicPr>'
        .             '<pic:cNvPr id="0" name="' . htmlspecialchars($name, ENT_XML1) . '"/>'
        .             '<pic:cNvPicPr/>'
        .           '</pic:nvPicPr>'
        .           '<pic:blipFill>'
        .             '<a:blip r:embed="' . $rId . '"'
        .               ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>'
        .             '<a:stretch><a:fillRect/></a:stretch>'
        .           '</pic:blipFill>'
        .           '<pic:spPr>'
        .             '<a:xfrm>'
        .               '<a:off x="0" y="0"/>'
        .               '<a:ext cx="' . $widthEmu . '" cy="' . $heightEmu . '"/>'
        .             '</a:xfrm>'
        .             '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
        .           '</pic:spPr>'
        .         '</pic:pic>'
        .       '</a:graphicData>'
        .     '</a:graphic>'
        .   '</wp:inline>'
        . '</w:drawing>'
        . '</w:r>';
}

function generateRaportDocx(array $dosenList, string $periodeLabel): string {
    $TW   = 10035; // total usable width twips for A4 with 19mm left/top/bottom, 14mm right
    $colA = 400;   // indent ~4%
    $colB = 3200;  // label B ~32%
    $colC = $TW - $colA - $colB; // value rest ~6435

    // Cek apakah TTD image tersedia
    $ttdPath = __DIR__ . '/../TTD Dosen/ttd_pak_pahmi.png';
    $hasTTD  = file_exists($ttdPath);
    $ttdData = $hasTTD ? file_get_contents($ttdPath) : null;
    $ttdRId  = 'rId2'; // relationship ID untuk gambar

    // Ukuran TTD di dokumen: skala ke tinggi ~1.8 cm (648000 EMU)
    $ttdHeightEmu = 648000;  // ~1.8cm tinggi
    $ttdWidthEmu  = (int)($ttdHeightEmu * 158 / 182); // proportional

    $body = '';

    foreach ($dosenList as $idx => $d) {
        $nama     = trim($d['Nama'] ?? '-') ?: '-';
        $prodi    = trim($d['Prodi'] ?? '-') ?: '-';
        $responden = trim($d['Jumlah Responden'] ?? '-') ?: '-';

        $sKuis   = round((float)($d['Nilai Kuesioner'] ?? 0), 3);
        $sHadir  = round((float)($d['Jumlah Kehadiran'] ?? 0), 1);
        $sKonten = round((float)($d['Konten'] ?? 0), 3);
        $jPenel  = (int)($d['Jumlah Penelitian'] ?? 0);
        $jPengab = (int)($d['Jumlah Pengabdian'] ?? 0);

        $kuisRaw   = trim((string)($d['Nilai Kuesioner']  ?? ''));
        $hadirRaw  = trim((string)($d['Jumlah Kehadiran'] ?? ''));
        $kontenRaw = trim((string)($d['Konten']           ?? ''));
        $penelRaw  = trim((string)($d['Jumlah Penelitian'] ?? ''));
        $pengabRaw = trim((string)($d['Jumlah Pengabdian'] ?? ''));
        $mkRaw     = trim((string)($d['Jumlah Matkul']    ?? ''));
        $kelasRaw  = trim((string)($d['Jumlah Kelas']     ?? ''));

        $vKuis   = ($kuisRaw !== '' && $sKuis > 0) ? number_format($sKuis, 2, ',', '.') : ($kuisRaw !== '' ? '0' : '-');
        $sHadirR = round($sHadir, 1);
        $vHadir  = ($hadirRaw !== '' && $sHadirR > 0)
            ? ($sHadirR == floor($sHadirR) ? (string)(int)$sHadirR : number_format($sHadirR, 1, ',', '.'))
            : ($hadirRaw !== '' ? '0' : '-');
        $vKonten = ($kontenRaw !== '' && $sKonten > 0)
            ? rtrim(rtrim(number_format($sKonten, 3, ',', '.'), '0'), ',')
            : ($kontenRaw !== '' ? '0' : '-');
        $vPenel  = ($penelRaw !== '') ? (string)$jPenel  : '-';
        $vPengab = ($pengabRaw !== '') ? (string)$jPengab : '-';
        $vJmlMK    = ($mkRaw !== '') ? (string)(int)($d['Jumlah Matkul']??0) : '-';
        $vJmlKelas = ($kelasRaw !== '') ? (string)(int)($d['Jumlah Kelas']??0) : '-';

        $kKuis = '';
        if ($sKuis > 0) {
            if ($sKuis >= 4.58) $kKuis = 'Sangat Baik';
            elseif ($sKuis >= 4.12) $kKuis = 'Baik';
            elseif ($sKuis >= 3.66) $kKuis = 'Cukup';
            else $kKuis = 'Kurang Baik';
        }
        $kHadir  = $sHadir > 0 ? ($sHadir < 14 ? 'Belum Memenuhi' : 'Sudah Memenuhi') : '';
        $kKonten = '';
        if ($sKonten > 0) {
            if ($sKonten >= 4.58) $kKonten = 'Sangat Baik';
            elseif ($sKonten >= 4.12) $kKonten = 'Baik';
            elseif ($sKonten >= 3.66) $kKonten = 'Cukup';
            else $kKonten = 'Kurang';
        }
        $kPenel  = ($penelRaw !== '') ? ($jPenel  >= 1 ? 'Memenuhi' : 'Belum Memenuhi') : '';
        $kPengab = ($pengabRaw !== '') ? ($jPengab >= 1 ? 'Memenuhi' : 'Belum Memenuhi') : '';

        $perbaikan = [];
        foreach (['P1','P2','P3','P4','P5'] as $pk) {
            $v = trim($d[$pk] ?? '');
            $perbaikan[] = ($v !== '' && $v !== '0') ? $v : '';
        }
        if (empty(array_filter($perbaikan, fn($x) => $x !== ''))) {
            $perbaikan = [
                'Kesiapan memberikan kuliah dan/atau praktek/praktikum',
                'Kejelasan penyampaian materi dan jawaban terhadap pertanyaan di kelas',
                'Pemanfaatan media dan teknologi pembelajaran',
                'Keanekaragaman cara pengukuran hasil belajar',
                'Kesesuaian materi ujian dan/atau tugas dengan tujuan mata kuliah',
            ];
        }
        while (count($perbaikan) < 5) $perbaikan[] = '';

        $catatan = [];
        foreach (['K1','K2','K3','K4'] as $kk) {
            $v = trim($d[$kk] ?? '');
            $catatan[] = ($v !== '' && $v !== '0') ? $v : '';
        }
        while (count($catatan) < 4) $catatan[] = '';

        // PAGE BREAK
        if ($idx > 0) {
            $body .= '<w:p><w:pPr><w:pageBreakBefore/><w:spacing w:before="0" w:after="0"/></w:pPr></w:p>';
        }

        // HEADER
        $body .= docxPara(docxRun('LAPORAN EVALUASI TRIDHARMA DOSEN', true), 'center', 0, 0);
        $body .= docxPara(docxRun($periodeLabel, true), 'center', 0, 0);
        $body .= docxPara(docxRun('NUSA PUTRA UNIVERSITY', true), 'center', 0, 0);
        $body .= '<w:p>'
            . '<w:pPr>'
            .   '<w:jc w:val="center"/>'
            .   '<w:spacing w:before="0" w:after="40" w:line="240" w:lineRule="auto"/>'
            .   '<w:pBdr><w:bottom w:val="double" w:sz="6" w:space="1" w:color="000000"/></w:pBdr>'
            . '</w:pPr>'
            . docxRun('Jl. Raya Cibolang No. 21, Cibolang Kaler, Cisaat, Cibolang Kaler, Cisaat, Sukabumi, Jawa Barat 43152. Telp. (0266) 210594')
            . '</w:p>';

        // A. IDENTITAS
        $body .= docxPara(docxRun('A. IDENTITAS DOSEN', true), 'left', 40, 0);
        $idRows = [
            ['NAMA DOSEN', $nama], ['PROGRAM STUDI', $prodi],
            ['JUMLAH MATA KULIAH', $vJmlMK], ['JUMLAH KELAS', $vJmlKelas],
            ['JUMLAH RESPONDEN', $responden],
        ];
        $idTableRows = [];
        foreach ($idRows as [$lbl, $val]) {
            $idTableRows[] = docxRow([
                docxCell(docxPara(''), $colA, docxBorderNone()),
                docxCell(docxPara(docxRun($lbl)), $colB, docxBorderNone()),
                docxCell(docxPara(docxRun($val)), $colC, docxBorderNone()),
            ], 220);
        }
        $body .= docxTable($idTableRows, $TW);

        // B. REKAPITULASI
        $body .= docxPara(docxRun('B. REKAPITULASI PENILAIAN', true), 'left', 40, 0);
        $wInd = 5588;          // Indikator ~58%
        $wNil = 1200;          // Nilai ~12%
        $wKet = $TW - $colA - $wInd - $wNil; // Keterangan ~30% (~2847)
        $rekapRows = [];
        $rekapRows[] = docxRow([
            docxCell(docxPara(''), $colA, docxBorderNone()),
            docxCell(docxPara(docxRun('Indikator Penilaian', true), 'center'), $wInd, docxBorderAll(), 1, 'center'),
            docxCell(docxPara(docxRun('Nilai', true), 'center'), $wNil, docxBorderAll(), 1, 'center'),
            docxCell(docxPara(docxRun('Keterangan', true), 'center'), $wKet, docxBorderAll(), 1, 'center'),
        ], 260);
        $rekapData2 = [
            ['Kuesioner Mahasiswa',$vKuis,$kKuis],['Kehadiran',$vHadir,$kHadir],
            ['Kelengkapan Konten Perkuliahan',$vKonten,$kKonten],
            ['Penelitian',$vPenel,$kPenel],['Pengabdian',$vPengab,$kPengab],
        ];
        foreach ($rekapData2 as [$ind,$nil,$ket]) {
            $rekapRows[] = docxRow([
                docxCell(docxPara(''), $colA, docxBorderNone()),
                docxCell(docxPara(docxRun($ind)), $wInd, docxBorderAll()),
                docxCell(docxPara(docxRun($nil), 'right'), $wNil, docxBorderAll()),
                docxCell(docxPara(docxRun($ket)), $wKet, docxBorderAll()),
            ], 240);
        }
        $body .= docxTable($rekapRows, $TW);

        // C. ASPEK
        $body .= docxPara(docxRun('C. ASPEK PEMBELAJARAN', true), 'left', 40, 0);
        $body .= docxPara(docxRun('C1. REKOMENDASI PERBAIKAN', true), 'left', 0, 0);
        $wNo = 300; $wIsiCol = $TW - $wNo;
        $aspekRows = [];
        for ($i = 0; $i < 5; $i++) {
            $aspekRows[] = docxRow([
                docxCell(docxPara(docxRun((string)($i+1))), $wNo, docxBorderBottom()),
                docxCell(docxPara(docxRun($perbaikan[$i]), 'both'), $wIsiCol, docxBorderBottom()),
            ], 240);
        }
        $body .= docxTable($aspekRows, $TW);

        // D. CATATAN
        $body .= docxPara(docxRun('D. CATATAN', true), 'left', 40, 0);
        $wDash = 280; $wCat = $TW - $wDash;
        $catRows = [];
        for ($i = 0; $i < 4; $i++) {
            $catText = $catatan[$i] !== '' ? $catatan[$i] : ' ';
            $catRows[] = docxRow([
                docxCell(docxPara(docxRun('-', true), 'center'), $wDash, docxBorderBottom()),
                docxCell(docxPara(docxRun($catText), 'both'), $wCat, docxBorderBottom()),
            ], 260);
        }
        $body .= docxTable($catRows, $TW);

        // E. KESIMPULAN & ANALISIS SENTIMEN MAHASISWA
        $sentimen = getDosenSentimen($d);
        $body .= docxPara(docxRun('E. KESIMPULAN & ANALISIS SENTIMEN MAHASISWA', true), 'left', 40, 0);
        $wE1 = 2500;
        $wE2 = 200;
        $wE3 = $TW - $wE1 - $wE2;
        $eRows = [];
        $sentimenText = "Positif: {$sentimen['positif_pct']}% ({$sentimen['positif']} respon)  |  Netral: {$sentimen['netral_pct']}% ({$sentimen['netral']} respon)  |  Negatif: {$sentimen['negatif_pct']}% ({$sentimen['negatif']} respon)";
        $eRows[] = docxRow([
            docxCell(docxPara(docxRun('Hasil Sentimen Mahasiswa', true)), $wE1, docxBorderNone()),
            docxCell(docxPara(docxRun(':')), $wE2, docxBorderNone()),
            docxCell(docxPara(docxRun($sentimenText, true)), $wE3, docxBorderNone()),
        ], 220);
        $eRows[] = docxRow([
            docxCell(docxPara(docxRun('Kesimpulan Evaluasi', true)), $wE1, docxBorderNone()),
            docxCell(docxPara(docxRun(':')), $wE2, docxBorderNone()),
            docxCell(docxPara(docxRun($sentimen['kesimpulan']), 'both'), $wE3, docxBorderNone()),
        ], 240);
        $body .= docxTable($eRows, $TW);

        // FOOTER
        $wFL = 4400;              // kiri
        $wFR = $TW - $wFL;        // kanan (~5635)
        $wSkor = 900;
        $wSd   = 1550;
        $wKt   = $wFR - $wSkor - $wSd; // ~3185

        if ($hasTTD) {
            $ttdPara = '<w:p>'
                . '<w:pPr><w:ind w:left="650"/><w:spacing w:before="20" w:after="0" w:line="240" w:lineRule="auto"/></w:pPr>'
                . docxInlineImage($ttdRId, $ttdWidthEmu, $ttdHeightEmu, 'TTD_Pahmi')
                . '</w:p>';
        } else {
            $ttdPara = docxPara('', 'center', 0, 0)
                . docxPara('', 'center', 0, 0);
        }

        $leftXml = docxPara(docxRun('UNIT PENJAMINAN MUTU'), 'left', 60, 0)
            . docxPara(docxRun('UNIVERSITAS NUSA PUTRA'), 'left', 0, 0)
            . $ttdPara
            . '<w:p>'
            .   '<w:pPr><w:jc w:val="left"/><w:spacing w:before="0" w:after="0"/>'
            .   '<w:pBdr><w:top w:val="single" w:sz="6" w:space="1" w:color="000000"/></w:pBdr></w:pPr>'
            .   docxRun('Dr. Samsul Pahmi, S.Pd., M.Pd.', true)
            . '</w:p>';

        $krRows = [];
        $krRows[] = docxRow([
            docxCell(docxPara(docxRun('RENTANG SKOR', true), 'center'), $wSkor + $wSd, docxBorderAll(), 2, 'center'),
            docxCell(docxPara(docxRun('KRITERIA', true), 'center'), $wKt, docxBorderAll(), 1, 'center'),
        ], 240);
        foreach([
            ['3,20', 's/d 3,65', 'Kurang Baik'],
            ['3,66', 's/d 4,11', 'Cukup'],
            ['4,12', 's/d 4,57', 'Baik'],
            ['4,58', 's/d 5,00', 'Sangat Baik'],
        ] as [$sk, $sd, $kt]) {
            $krRows[] = docxRow([
                docxCell(docxPara(docxRun($sk), 'right'), $wSkor, docxBorderAll()),
                docxCell(docxPara(docxRun($sd)), $wSd, docxBorderAll()),
                docxCell(docxPara(docxRun($kt)), $wKt, docxBorderAll()),
            ], 220);
        }
        $krTable = docxTable($krRows, $wFR);
        $rightXml = docxPara(docxRun('CATATAN: KRITERIA PENSKORAN'), 'left', 60, 0) . $krTable;

        $body .= docxTable([
            docxRow([
                docxCell($leftXml, $wFL, docxBorderNone(), 1, 'top'),
                docxCell($rightXml, $wFR, docxBorderNone(), 1, 'top'),
            ], 0),
        ], $TW);
    }

    // ===== Assemble DOCX =====
    $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas"'
        . ' xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
        . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'
        . ' xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"'
        . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
        . ' xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
        . '<w:body>' . $body
        . '<w:sectPr>'
        .   '<w:pgSz w:w="11906" w:h="16838"/>'
        .   '<w:pgMar w:top="1077" w:right="794" w:bottom="1077" w:left="1077" w:header="0" w:footer="0" w:gutter="0"/>'
        . '</w:sectPr>'
        . '</w:body></w:document>';

    $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
        . '<w:docDefaults>'
        .   '<w:rPrDefault><w:rPr>'
        .     '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>'
        .     '<w:sz w:val="18"/><w:szCs w:val="18"/>'
        .   '</w:rPr></w:rPrDefault>'
        .   '<w:pPrDefault><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/></w:pPr></w:pPrDefault>'
        . '</w:docDefaults>'
        . '<w:style w:type="paragraph" w:default="1" w:styleId="Normal">'
        .   '<w:name w:val="Normal"/>'
        .   '<w:pPr><w:spacing w:after="0"/></w:pPr>'
        . '</w:style>'
        . '</w:styles>';

    // Content Types — tambahkan PNG jika ada TTD
    $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . ($hasTTD ? '<Default Extension="png" ContentType="image/png"/>' : '')
        . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
        . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
        . '</Types>';

    $relsRoot = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
        . '</Relationships>';

    // Word rels — tambahkan image relationship jika ada TTD
    $wordRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
        . ($hasTTD ? '<Relationship Id="' . $ttdRId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/ttd.png"/>' : '')
        . '</Relationships>';

    $tmpFile = sys_get_temp_dir() . '/raport_' . uniqid() . '.docx';
    $zip = new ZipArchive();
    if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('Gagal membuat DOCX');
    }
    $zip->addFromString('[Content_Types].xml', $contentTypes);
    $zip->addFromString('_rels/.rels', $relsRoot);
    $zip->addFromString('word/document.xml', $documentXml);
    $zip->addFromString('word/styles.xml', $stylesXml);
    $zip->addFromString('word/_rels/document.xml.rels', $wordRels);

    // Embed TTD image
    if ($hasTTD && $ttdData) {
        $zip->addFromString('word/media/ttd.png', $ttdData);
    }

    $zip->close();

    $content = file_get_contents($tmpFile);
    unlink($tmpFile);
    return $content;
}
