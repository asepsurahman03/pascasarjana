import os
import re

files = [
    r'C:\xampp\htdocs\webdummy\pages\cetak_lampiran_proposal.php',
    r'C:\xampp\htdocs\webdummy\pages\cetak_lampiran.php'
]

for fpath in files:
    with open(fpath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # 1. Add html2canvas script
    if 'html2canvas.min.js' not in content:
        content = content.replace(
            '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>',
            '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>\n    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>'
        )

    # 2. Fix the promise catch and restore buttons if it fails
    # Replace the Promise.all logic to include a catch block
    
    # Let's find the Promise.all block
    promise_match = re.search(r'Promise\.all\(\[\s*kopEl.*?\).catch\(function\(err\)', content, re.DOTALL)
    
    # Wait, the previous code had:
    #                 }).catch(function(err) {
    #                     alert('Gagal mendownload PDF. Error: ' + err);
    # This was attached to html2pdf().save().then(...).catch(...)
    # The Promise.all().then() does NOT have a catch.
    
    # Let's just replace the whole downloadPDFLangsung again to be safe and clean.
    new_js = """            function downloadPDFLangsung() {
                var btn = document.getElementById('btn-dl-pdf');
                var oldText = btn.innerHTML;
                btn.innerHTML = '⏳ Sedang Memproses...';
                btn.style.opacity = '0.7';
                btn.disabled = true;

                if (tinymce.activeEditor) {
                    tinymce.activeEditor.fire('blur');
                }

                var element = document.getElementById('pages-container');
                var safeName = "<?= addslashes(preg_replace('/[^a-zA-Z0-9 \\-\\.,]/', '_', $safe_title)) ?>.pdf";

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
                            height: p.style.height, minHeight: p.style.minHeight, overflow: p.style.overflow
                        };
                        p.style.boxShadow = 'none'; p.style.margin = '0'; p.style.border = 'none';
                        p.style.height = 'auto'; p.style.minHeight = 'auto'; p.style.overflow = 'visible';
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
                        pagebreak:    { mode: ['css', 'legacy'] }
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
                        
                        allKops.forEach(function(el, i) { el.style.display = origKopDisp[i]; });
                        allFooters.forEach(function(el, i) { el.style.display = origFooterDisp[i]; });
                        restoreUI();
                    });
                }).catch(function(err) {
                    alert('Gagal memproses gambar Kop/Footer. Error: ' + err);
                    restoreUI();
                });
            }"""

    match = re.search(r'function downloadPDFLangsung\(\)\s*\{.*?\n            \}', content, re.DOTALL)
    if match:
        new_content = content.replace(match.group(0), new_js)
        with open(fpath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Updated JS in {fpath}")
    else:
        print(f"Could not find JS function in {fpath}")
