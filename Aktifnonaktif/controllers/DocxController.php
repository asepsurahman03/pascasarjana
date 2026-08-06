<?php
/**
 * DocxController
 * Generates the EXACT DOCX file by replacing tags in a pre-processed template copy.
 */

class DocxController
{
    public function generate(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            die('ID tidak valid');
        }

        $pengajuan = PengunduranDiri::findById($id);
        if (!$pengajuan) {
            die('Data pengajuan tidak ditemukan');
        }

        // Prepare data
        $nomorSurat = $pengajuan['nomor_surat'] ?: 'Diisi oleh SASU';
        $tanggal    = formatTanggal($pengajuan['tanggal_surat'], false);
        $nama       = $pengajuan['nama_pemohon'];
        $nim        = $pengajuan['nim'];
        $angkatan   = $pengajuan['angkatan'];
        $prodi      = $pengajuan['program_studi'];
        $alasan     = $pengajuan['alasan'];

        // Checkboxes using Unicode Ballot Box
        // U+2611 ☑ Ballot Box with Check
        // U+2610 ☐ Ballot Box
        $cb_beasiswa = ($pengajuan['status_mahasiswa'] === 'Beasiswa') ? '☑ BEASISWA' : '☐ BEASISWA';
        $cb_non      = ($pengajuan['status_mahasiswa'] !== 'Beasiswa') ? '☑ NON BEASISWA' : '☐ NON BEASISWA';
        $cb_ya       = ($pengajuan['bersedia_mundur'] === 'YES') ? '☑ YA' : '☐ YA';
        $cb_tidak    = ($pengajuan['bersedia_mundur'] !== 'YES') ? '☑ TIDAK' : '☐ TIDAK';

        $ttd_mhs = "_____________________________\n" . $nama;

        // Paths
        $templatePath = BASE_PATH . '/storage/template_tagged.docx';
        if (!file_exists($templatePath)) {
            die('Template DOCX tidak ditemukan. Jalankan tag_template.php terlebih dahulu.');
        }

        $tempFile = sys_get_temp_dir() . '/pengajuan_' . $id . '_' . time() . '.docx';
        copy($templatePath, $tempFile);

        $zip = new ZipArchive();
        if ($zip->open($tempFile) === TRUE) {
            $xml = $zip->getFromName('word/document.xml');

            // Insert signature if available
            $signatureXml = htmlspecialchars($ttd_mhs);
            if ($pengajuan['bersedia_mundur'] === 'YES') {
                $signature = DigitalSignature::findByPengunduranId($id);
                if ($signature && !empty($signature['signature_data'])) {
                    $b64 = $signature['signature_data'];
                    if (strpos($b64, 'base64,') !== false) {
                        $b64 = explode('base64,', $b64)[1];
                    }
                    $imgData = base64_decode($b64);
                    if ($imgData) {
                        // 1. Add image to media folder
                        $zip->addFromString('word/media/sig.png', $imgData);
                        
                        // 2. Add relationship
                        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
                        $relStr = '<Relationship Id="rIdSig" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/sig.png"/></Relationships>';
                        $relsXml = str_replace('</Relationships>', $relStr, $relsXml);
                        $zip->addFromString('word/_rels/document.xml.rels', $relsXml);
                        
                        // 3. Create drawing XML (approx 45mm x 25mm -> 1600000 x 890000 EMUs)
                        $signatureXml = '<w:drawing>
                          <wp:inline distT="0" distB="0" distL="0" distR="0">
                            <wp:extent cx="1600000" cy="890000"/>
                            <wp:docPr id="999" name="Signature"/>
                            <wp:cNvGraphicFramePr/>
                            <a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
                              <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
                                <pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
                                  <pic:nvPicPr><pic:cNvPr id="999" name="Signature"/><pic:cNvPicPr/></pic:nvPicPr>
                                  <pic:blipFill><a:blip r:embed="rIdSig"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>
                                  <pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="1600000" cy="890000"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>
                                </pic:pic>
                              </a:graphicData>
                            </a:graphic>
                          </wp:inline>
                        </w:drawing>';
                        // Add the placeholder text below the image
                        $textOnlyTtd = htmlspecialchars($ttd_mhs);
                        $textOnlyTtd = str_replace("\n", '</w:t><w:br/><w:t>', $textOnlyTtd);
                        $signatureXml .= '<w:br/><w:t>' . $textOnlyTtd . '</w:t>';
                    }
                }
            }

            // Replace text
            $xml = str_replace('${TANGGAL_SURAT}', htmlspecialchars($tanggal), $xml);
            $xml = str_replace('${NAMA_PEMOHON}', htmlspecialchars($nama), $xml);
            $xml = str_replace('${NIM}', htmlspecialchars($nim), $xml);
            $xml = str_replace('${ANGKATAN}', htmlspecialchars($angkatan), $xml);
            $xml = str_replace('${PROGRAM_STUDI}', htmlspecialchars($prodi), $xml);
            $xml = str_replace('${ALASAN}', htmlspecialchars($alasan), $xml);
            
            $xml = str_replace('${CB_BEASISWA}', htmlspecialchars($cb_beasiswa), $xml);
            $xml = str_replace('${CB_NON}', htmlspecialchars($cb_non), $xml);
            $xml = str_replace('${CB_YA}', htmlspecialchars($cb_ya), $xml);
            $xml = str_replace('${CB_TIDAK}', htmlspecialchars($cb_tidak), $xml);
            
            // Replace signature tag with XML directly (no htmlspecialchars since it contains raw XML)
            $xml = str_replace('<w:t>${TTD_MHS}</w:t>', $signatureXml, $xml);
            $xml = str_replace('<w:t xml:space="preserve">${TTD_MHS}</w:t>', $signatureXml, $xml);
            
            // Text-only fallback for TTD_MHS, converting \n to <w:br/>
            $textOnlyTtd = htmlspecialchars($ttd_mhs);
            $textOnlyTtd = str_replace("\n", '</w:t><w:br/><w:t>', $textOnlyTtd);
            $xml = str_replace(htmlspecialchars('${TTD_MHS}'), $textOnlyTtd, $xml);
            $xml = str_replace('${TTD_MHS}', $textOnlyTtd, $xml);

            $zip->addFromString('word/document.xml', $xml);
            $zip->close();
        } else {
            die('Gagal memproses file DOCX');
        }

        // Output file
        $filename = 'Pernyataan_Pengunduran_Diri_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nama) . '.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tempFile));
        header('Cache-Control: max-age=0');
        
        readfile($tempFile);
        unlink($tempFile);
        exit;
    }
}
