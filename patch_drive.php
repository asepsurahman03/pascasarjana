<?php
$file = 'pages/cetak_lampiran.php';
$content = file_get_contents($file);

// 1. Add the button
$btnSearch = '<button onclick="downloadPDFLangsung()" class="tb-btn" style="background:#dc2626;color:#fff;" id="btn-dl-pdf">📥 Download PDF</button>';
$btnAdd = $btnSearch . "\n        <button onclick=\"uploadToGoogleDrive()\" class=\"tb-btn\" style=\"background:#eab308;color:#fff;\" id=\"btn-upload-drive\">☁️ Upload ke Drive</button>";

$content = str_replace($btnSearch, $btnAdd, $content);

// 2. Add the JS function uploadToGoogleDrive() right before downloadWordLangsung()
$jsSearch = 'function downloadWordLangsung() {';

$jsAdd = <<<'JS'
            function uploadToGoogleDrive() {
                var btn = document.getElementById('btn-upload-drive');
                var oldText = btn.innerHTML;
                btn.innerHTML = '⏳ Uploading...';
                btn.style.opacity = '0.7';
                btn.disabled = true;

                if (tinymce.activeEditor) {
                    tinymce.activeEditor.fire('blur');
                }

                var element = document.getElementById('pages-container');
                var safeName = "<?= addslashes(preg_replace('/[^a-zA-Z0-9 \-\.,]/', '_', $safe_title)) ?>.pdf";
                var riwayatId = "<?= (int)$id ?>";

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
                    alert('Error: library html2canvas gagal dimuat.');
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
                    }).output('blob').then(function(pdfBlob) {
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
                        
                        // Send blob to server
                        var formData = new FormData();
                        formData.append('pdf', pdfBlob, safeName);
                        formData.append('filename', safeName);
                        formData.append('id', riwayatId);

                        fetch('../api/upload_pdf_to_drive.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            restoreUI();
                            if(data.success) {
                                alert('Berhasil diupload ke Google Drive!');
                                window.open(data.drive_url, '_blank');
                            } else {
                                alert('Error: ' + (data.error || 'Unknown error'));
                            }
                        })
                        .catch(err => {
                            alert('Gagal upload: ' + err);
                            restoreUI();
                        });

                    }).catch(function(err) {
                        alert('Gagal generate PDF. Error: ' + err);
                        restoreUI();
                    });
                }).catch(function(err) {
                    alert('Gagal memproses gambar Kop/Footer. Error: ' + err);
                    restoreUI();
                });
            }

            function downloadWordLangsung() {
JS;

$content = str_replace($jsSearch, $jsAdd, $content);

file_put_contents($file, $content);
echo "Successfully patched cetak_lampiran.php\n";
