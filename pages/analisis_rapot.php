<?php
$pageTitle  = 'Analisis Rapot Dosen';
$breadcrumb = [['label'=>'Akademik','url'=>'#'],['label'=>'Analisis Rapot Dosen']];
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$allProdi = getAllProdi();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'generate') {
        $user = getCurrentUser();
        $dosen_id = empty($_POST['dosen_id']) ? null : $_POST['dosen_id'];
        $nama_dosen = $_POST['nama_dosen'];
        $periode = $_POST['periode'];
        $bagian_a = $_POST['bagian_a'];
        $bagian_b = $_POST['bagian_b'];
        $bagian_c = $_POST['bagian_c'];
        $created_by = $user['id'];
        $raw_feedback = $_POST['raw_feedback'] ?? null;
        $ai_analysis = $_POST['ai_analysis'] ?? null;

        $sql = "INSERT INTO analisis_rapot_dosen (dosen_id, nama_dosen, periode, bagian_a, bagian_b, bagian_c, raw_feedback, ai_analysis, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $id = dbExecute($sql, [$dosen_id, $nama_dosen, $periode, $bagian_a, $bagian_b, $bagian_c, $raw_feedback, $ai_analysis, $created_by]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Analisis Rapot Dosen berhasil dibuat.'];
        header('Location: analisis_rapot.php?step=result&id=' . $id);
        exit;
    } elseif ($_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        if (isSuperAdmin()) {
            dbExecute("DELETE FROM analisis_rapot_dosen WHERE id=?", [$id]);
        } else {
            // Check if it belongs to user or they have access. For now just delete if super admin, or let anyone delete? Let's check creator.
            $user = getCurrentUser();
            dbExecute("DELETE FROM analisis_rapot_dosen WHERE id=? AND created_by=?", [$id, $user['id']]);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data analisis berhasil dihapus.'];
        header('Location: analisis_rapot.php');
        exit;
    } elseif ($_POST['action'] === 'update') {
        $id = (int)$_POST['id'];
        $dosen_id = empty($_POST['dosen_id']) ? null : $_POST['dosen_id'];
        $nama_dosen = $_POST['nama_dosen'];
        $periode = $_POST['periode'];
        $bagian_a = $_POST['bagian_a'];
        $bagian_b = $_POST['bagian_b'];
        $bagian_c = $_POST['bagian_c'];
        $raw_feedback = $_POST['raw_feedback'] ?? null;
        $ai_analysis = $_POST['ai_analysis'] ?? null;

        $sql = "UPDATE analisis_rapot_dosen SET dosen_id=?, nama_dosen=?, periode=?, bagian_a=?, bagian_b=?, bagian_c=?, raw_feedback=?, ai_analysis=? WHERE id=?";
        dbExecute($sql, [$dosen_id, $nama_dosen, $periode, $bagian_a, $bagian_b, $bagian_c, $raw_feedback, $ai_analysis, $id]);
        
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Analisis berhasil diperbarui.'];
        header('Location: analisis_rapot.php?step=result&id=' . $id);
        exit;
    }
}

$step = $_GET['step'] ?? 'history';

if ($step === 'result' || $step === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $data = dbQueryOne("SELECT * FROM analisis_rapot_dosen WHERE id=?", [$id]);
    if (!$data) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak ditemukan.'];
        header('Location: analisis_rapot.php');
        exit;
    }
    if ($step === 'result') {
        $pageTitle = 'Laporan_Rapot_' . preg_replace('/[^a-zA-Z0-9]/', '_', $data['nama_dosen']);
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-5xl mx-auto pb-10">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">📊 Analisis Rapot Dosen</h2>
        <p class="text-slate-500 dark:text-slate-400">Buat dan kelola laporan analisis rapot dosen untuk keperluan akademik.</p>
    </div>

    <?php if ($step === 'history'): ?>
        <div class="mb-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800 dark:text-white">Riwayat Analisis Rapot</h3>
            <a href="analisis_rapot.php?step=form" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-lg font-semibold transition-all shadow hover:shadow-md text-sm">
                + Buat Analisis Baru
            </a>
        </div>
        
        <?php
        $user = getCurrentUser();
        if (isSuperAdmin()) {
            $history = dbQuery("SELECT a.*, u.nama as pembuat FROM analisis_rapot_dosen a LEFT JOIN users u ON a.created_by = u.id ORDER BY a.created_at DESC");
        } else {
            $history = dbQuery("SELECT a.*, u.nama as pembuat FROM analisis_rapot_dosen a LEFT JOIN users u ON a.created_by = u.id WHERE a.created_by=? ORDER BY a.created_at DESC", [$user['id']]);
        }
        ?>
        
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <th class="py-3 px-4 text-left font-semibold text-slate-600 dark:text-slate-300">Dosen</th>
                            <th class="py-3 px-4 text-left font-semibold text-slate-600 dark:text-slate-300">Periode</th>
                            <th class="py-3 px-4 text-left font-semibold text-slate-600 dark:text-slate-300">Pembuat</th>
                            <th class="py-3 px-4 text-left font-semibold text-slate-600 dark:text-slate-300">Tanggal</th>
                            <th class="py-3 px-4 text-right font-semibold text-slate-600 dark:text-slate-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">Belum ada riwayat analisis rapot dosen.</td>
                            </tr>
                        <?php else: foreach ($history as $h): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-3 px-4 font-medium text-slate-800 dark:text-white"><?= e($h['nama_dosen']) ?></td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-300"><?= e($h['periode']) ?></td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-300"><?= e($h['pembuat']) ?></td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-300"><?= formatTanggal($h['created_at']) ?></td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="analisis_rapot.php?step=result&id=<?= $h['id'] ?>" class="text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 p-1.5 rounded" title="Lihat"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                                        <a href="analisis_rapot.php?step=edit&id=<?= $h['id'] ?>" class="text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/30 p-1.5 rounded" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></a>
                                        <form method="POST" onsubmit="return confirm('Yakin ingin menghapus?')" class="inline-block">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                            <button type="submit" class="text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 p-1.5 rounded" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($step === 'form' || $step === 'edit'): ?>
        <?php
        $isEdit = $step === 'edit';
        $dosenList = dbQuery("SELECT id, nama FROM dosen ORDER BY nama ASC");
        ?>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-xl font-bold text-slate-800 dark:text-white"><?= $isEdit ? 'Edit Analisis Rapot' : 'Buat Analisis Rapot Baru' ?></h3>
            <a href="analisis_rapot.php" class="text-sm font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors">← Kembali ke Riwayat</a>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700/60 p-6">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'generate' ?>">
                <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $data['id'] ?>"><?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Pilih Dosen (Opsional)</label>
                        <select name="dosen_id" id="dosen_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c]" onchange="if(this.value) { document.getElementById('nama_dosen').value = this.options[this.selectedIndex].text; }">
                            <option value="">-- Pilih Dosen (Jika ada di database) --</option>
                            <?php foreach ($dosenList as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($isEdit && $data['dosen_id'] == $d['id']) ? 'selected' : '' ?>><?= e($d['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nama Dosen <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_dosen" id="nama_dosen" required value="<?= $isEdit ? e($data['nama_dosen']) : '' ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c]" placeholder="Masukkan nama dosen">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Periode <span class="text-red-500">*</span></label>
                    <input type="text" name="periode" required value="<?= $isEdit ? e($data['periode']) : 'Semester Genap Tahun Akademik ' . date('Y').'/'.(date('Y')+1) ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c]" placeholder="Contoh: Semester Gasal 2026/2027">
                </div>

                <div class="border-t border-slate-100 dark:border-slate-700/60 pt-6">
                    <h4 class="font-semibold text-slate-800 dark:text-white mb-4">✨ Analisis Sentimen Otomatis (AI)</h4>
                    <p class="text-xs text-slate-500 mb-4">Unggah file Laporan Evaluasi Dosen (PDF) atau paste "Kesan dan Pesan" mahasiswa secara manual, lalu klik tombol Analisis.</p>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Unggah File Laporan (PDF)</label>
                        <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:border-[#8c0c4c]">
                    </div>
                    
                    <textarea id="raw_feedback" name="raw_feedback" rows="4" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#8c0c4c] mb-3" placeholder="Atau paste teks manual di sini..."><?= $isEdit ? e($data['raw_feedback'] ?? '') : '' ?></textarea>
                    
                    <button type="button" id="btn-analisis" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Mulai Analisis Sentimen
                    </button>
                    
                    <input type="hidden" id="ai_analysis" name="ai_analysis" value="<?= $isEdit ? htmlspecialchars($data['ai_analysis'] ?? '', ENT_QUOTES) : '' ?>">
                    
                    <div id="ai_result_container" class="mt-4 hidden p-4 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700">
                        <!-- AI Result injected here -->
                    </div>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-700/60 pt-6">
                    <h4 class="font-semibold text-slate-800 dark:text-white mb-4">Isi Analisis Rapot Dosen</h4>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">A. Analisis Beban Kerja & Kinerja</label>
                            <textarea name="bagian_a" rows="4" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#8c0c4c]" placeholder="Tuliskan analisis alasan atau evaluasi..."><?= $isEdit ? e($data['bagian_a']) : '' ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">B. Evaluasi Pengajaran & Administrasi</label>
                            <textarea name="bagian_b" rows="5" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#8c0c4c]" placeholder="Tuliskan evaluasi pengajaran..."><?= $isEdit ? e($data['bagian_b']) : '' ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">C. Rekomendasi & Kompetensi Peningkatan</label>
                            <textarea name="bagian_c" rows="4" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-[#8c0c4c]" placeholder="Tuliskan kompetensi yang perlu ditingkatkan atau rekomendasi..."><?= $isEdit ? e($data['bagian_c']) : '' ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-700/60">
                    <button type="submit" class="px-6 py-2.5 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-xl font-semibold shadow-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Analisis
                    </button>
                </div>
            </form>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('btn-analisis');
            const rawInput = document.getElementById('raw_feedback');
            const hiddenInput = document.getElementById('ai_analysis');
            const container = document.getElementById('ai_result_container');

            if (hiddenInput && hiddenInput.value) {
                try {
                    renderAIResult(JSON.parse(hiddenInput.value));
                } catch(e) {}
            }

            if (btn) {
                btn.addEventListener('click', async function() {
                    const text = rawInput.value.trim();
                    const pdfInput = document.getElementById('pdf_file');
                    const hasPdf = pdfInput && pdfInput.files.length > 0;
                    
                    const namaDosen = document.querySelector('input[name="nama_dosen"]').value.trim() || 'Dosen Yang Bersangkutan';
                    const periodeVal = document.querySelector('input[name="periode"]').value.trim() || 'Periode Berjalan';

                    if (!text && !hasPdf) return alert('Silakan unggah file PDF atau isi teks Kesan dan Pesan terlebih dahulu.');
                    
                    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...';
                    btn.disabled = true;

                    try {
                        let formData = new FormData();
                        formData.append('nama_dosen', namaDosen);
                        formData.append('periode', periodeVal);
                        
                        if (hasPdf) {
                            formData.append('pdf_file', pdfInput.files[0]);
                        }
                        if (text) {
                            formData.append('feedback', text);
                        }

                        const res = await fetch('../api/generate_sentimen_ai.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await res.json();
                        
                        if (data.ok) {
                            hiddenInput.value = JSON.stringify(data.data);
                            renderAIResult(data.data);
                        } else {
                            alert(data.error || 'Terjadi kesalahan.');
                        }
                    } catch (err) {
                        alert('Gagal menghubungi server.');
                    }
                    
                    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Mulai Analisis Sentimen';
                    btn.disabled = false;
                });
            }

            function renderAIResult(data) {
                if (!data) return;
                
                // Auto-fill textareas if AI returns them
                if (data.bagian_a) {
                    const txtA = document.querySelector('textarea[name=\"bagian_a\"]');
                    if (txtA && !txtA.value) txtA.value = data.bagian_a;
                }
                if (data.bagian_b) {
                    const txtB = document.querySelector('textarea[name=\"bagian_b\"]');
                    if (txtB && !txtB.value) txtB.value = data.bagian_b;
                }
                if (data.bagian_c) {
                    const txtC = document.querySelector('textarea[name=\"bagian_c\"]');
                    if (txtC && !txtC.value) txtC.value = data.bagian_c;
                }

                if (!data.summary) return;
                
                container.classList.remove('hidden');
                
                let html = `
                <h5 class="font-bold text-sm mb-2 text-slate-800 dark:text-white">Rekapitulasi Sentimen</h5>
                <div class="grid grid-cols-3 gap-3 mb-4 text-center">
                    <div class="bg-emerald-50 dark:bg-emerald-900/30 p-2 rounded-lg border border-emerald-100 dark:border-emerald-800">
                        <div class="text-xs text-emerald-600 font-semibold uppercase">Positif</div>
                        <div class="font-bold text-lg text-emerald-700">${data.summary.positif}</div>
                        <div class="text-[10px] text-emerald-500">${data.summary.positif_pct}</div>
                    </div>
                    <div class="bg-amber-50 dark:bg-amber-900/30 p-2 rounded-lg border border-amber-100 dark:border-amber-800">
                        <div class="text-xs text-amber-600 font-semibold uppercase">Netral</div>
                        <div class="font-bold text-lg text-amber-700">${data.summary.netral}</div>
                        <div class="text-[10px] text-amber-500">${data.summary.netral_pct}</div>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/30 p-2 rounded-lg border border-red-100 dark:border-red-800">
                        <div class="text-xs text-red-600 font-semibold uppercase">Negatif</div>
                        <div class="font-bold text-lg text-red-700">${data.summary.negatif}</div>
                        <div class="text-[10px] text-red-500">${data.summary.negatif_pct}</div>
                    </div>
                </div>
                
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-1">Kesimpulan AI:</p>
                <p class="text-xs text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-100 dark:border-slate-700 mb-4 italic">
                    ${data.conclusion.replace(/\\n/g, '<br>')}
                </p>
                
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">* Hasil klasifikasi baris-per-baris akan terlihat saat dicetak/disimpan.</p>
                `;
                container.innerHTML = html;
            }
        });
        </script>

    <?php elseif ($step === 'result'): ?>
        <div class="mb-4 flex items-center justify-between no-print">
            <h3 class="text-xl font-bold text-slate-800 dark:text-white">Detail Analisis Rapot Dosen</h3>
            <div class="flex gap-2">
                <a href="analisis_rapot.php" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg font-semibold text-sm hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Kembali</a>
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-[#8c0c4c] hover:bg-[#a3155b] text-white rounded-lg font-semibold transition-all shadow hover:shadow-md text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> Cetak Laporan
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-none md:rounded-2xl shadow-none md:shadow-sm border-none md:border border-slate-100 dark:border-slate-700/60 p-8 md:p-12 mb-8" id="printable-area">
            
            <div class="text-center mb-8">
                <h1 class="text-xl font-bold uppercase mb-1">LAPORAN RAPOT DOSEN</h1>
                <h2 class="text-lg font-bold uppercase"><?= e($data['nama_dosen']) ?></h2>
            </div>
            
            <div class="mb-6">
                <table class="w-full text-sm">
                    <tr><td class="py-1 w-40 font-semibold align-top">Nama Dosen</td><td class="py-1 w-4 align-top">:</td><td class="py-1 font-bold"><?= e($data['nama_dosen']) ?></td></tr>
                    <tr><td class="py-1 w-40 font-semibold align-top">Periode</td><td class="py-1 w-4 align-top">:</td><td class="py-1 font-bold"><?= e($data['periode']) ?></td></tr>
                </table>
            </div>
            
            <div class="space-y-6 text-justify text-sm leading-relaxed">
                <div>
                    <h3 class="font-bold mb-2 text-base">A. Analisis Beban Kerja & Kinerja</h3>
                    <div class="pl-4 whitespace-pre-wrap"><?= e($data['bagian_a']) ?></div>
                </div>
                
                <div>
                    <h3 class="font-bold mb-2 text-base">B. Evaluasi Pengajaran & Administrasi</h3>
                    <div class="pl-4 whitespace-pre-wrap"><?= e($data['bagian_b']) ?></div>
                </div>
                
                <div>
                    <h3 class="font-bold mb-2 text-base">C. Rekomendasi & Kompetensi Peningkatan</h3>
                    <div class="pl-4 whitespace-pre-wrap"><?= e($data['bagian_c']) ?></div>
                </div>
            </div>
            
            <?php if (!empty($data['ai_analysis'])): $ai = json_decode($data['ai_analysis'], true); if ($ai): ?>
            <div class="mt-8 border-t border-black pt-6" style="page-break-before: always;">
                <h3 class="font-bold mb-4 text-base">D. Hasil Klasifikasi Sentimen Mahasiswa (Otomatis)</h3>
                
                <table class="w-full text-sm mb-6 border-collapse border border-black">
                    <thead>
                        <tr>
                            <th class="py-2 px-3 border border-black text-left w-12">No</th>
                            <th class="py-2 px-3 border border-black text-left">Kesan dan Pesan</th>
                            <th class="py-2 px-3 border border-black text-center w-32">Sentimen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ai['items'] ?? [] as $item): ?>
                        <tr>
                            <td class="py-2 px-3 border border-black align-top"><?= e($item['no']) ?></td>
                            <td class="py-2 px-3 border border-black align-top"><?= e($item['kesan']) ?></td>
                            <td class="py-2 px-3 border border-black text-center align-top"><?= e($item['sentimen']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h3 class="font-bold mb-4 text-base">E. Rekapitulasi Sentimen</h3>
                <table class="w-1/2 text-sm mb-6 border-collapse border border-black">
                    <thead>
                        <tr>
                            <th class="py-2 px-3 border border-black text-left">Sentimen</th>
                            <th class="py-2 px-3 border border-black text-center">Jumlah</th>
                            <th class="py-2 px-3 border border-black text-center">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-2 px-3 border border-black">Positif</td>
                            <td class="py-2 px-3 border border-black text-center"><?= e($ai['summary']['positif'] ?? 0) ?></td>
                            <td class="py-2 px-3 border border-black text-center"><?= e($ai['summary']['positif_pct'] ?? '0%') ?></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-3 border border-black">Netral</td>
                            <td class="py-2 px-3 border border-black text-center"><?= e($ai['summary']['netral'] ?? 0) ?></td>
                            <td class="py-2 px-3 border border-black text-center"><?= e($ai['summary']['netral_pct'] ?? '0%') ?></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-3 border border-black">Negatif</td>
                            <td class="py-2 px-3 border border-black text-center"><?= e($ai['summary']['negatif'] ?? 0) ?></td>
                            <td class="py-2 px-3 border border-black text-center"><?= e($ai['summary']['negatif_pct'] ?? '0%') ?></td>
                        </tr>
                        <tr class="font-bold">
                            <td class="py-2 px-3 border border-black">Total</td>
                            <td class="py-2 px-3 border border-black text-center"><?= e($ai['summary']['total'] ?? 0) ?></td>
                            <td class="py-2 px-3 border border-black text-center">100%</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mb-4">
                    <p class="text-sm font-bold mb-1">Kesimpulan AI:</p>
                    <div class="text-sm text-justify whitespace-pre-wrap">
                        <?= e($ai['conclusion'] ?? '') ?>
                    </div>
                </div>
            </div>
            <?php endif; endif; ?>
            
            <div class="mt-16 flex justify-end">
                <div class="text-center">
                    <p class="mb-20 text-sm">Sukabumi, <?= formatTanggal(date('Y-m-d')) ?></p>
                    <p class="font-bold border-b border-black dark:border-white inline-block px-4">Direktur Pascasarjana</p>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<style>
@media print {
    body { background: white !important; color: black !important; }
    .no-print, nav, header { display: none !important; }
    #printable-area { border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; width: 100% !important; }
    .text-white { color: black !important; }
    .dark\:bg-slate-800 { background: white !important; }
    .dark\:text-slate-300, .dark\:text-white { color: black !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
