<?php
$pageTitle  = 'Buat Lampiran Tesis';
$breadcrumb = [['label'=>'Berkas & Lampiran','url'=>'#'],['label'=>'Riwayat Lampiran Tesis']];
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$allProdi = getAllProdi();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'generate') {
        $user = getCurrentUser();
        $prodi_id = $_POST['prodi_id'];
        $nama_mhs = $_POST['nama_mhs'];
        $nim_mhs = $_POST['nim_mhs'];
        $konsentrasi = $_POST['konsentrasi'] ?? '';
        $judul_tesis = $_POST['judul_tesis'];
        $tanggal_sidang = $_POST['tanggal_sidang'];
        $ketua_pembimbing = $_POST['ketua_pembimbing'] === 'lainnya' ? $_POST['ketua_pembimbing_manual'] : $_POST['ketua_pembimbing'];
        $anggota_pembimbing = $_POST['anggota_pembimbing'] === 'lainnya' ? $_POST['anggota_pembimbing_manual'] : $_POST['anggota_pembimbing'];
        $ketua_penguji = $_POST['ketua_penguji'] === 'lainnya' ? $_POST['ketua_penguji_manual'] : $_POST['ketua_penguji'];
        $anggota_penguji = $_POST['anggota_penguji'] === 'lainnya' ? $_POST['anggota_penguji_manual'] : $_POST['anggota_penguji'];
        $created_by = $user['id'];

        $sql = "INSERT INTO riwayat_lampiran (jenis, prodi_id, nama_mhs, nim_mhs, konsentrasi, judul_tesis, tanggal_sidang, ketua_pembimbing, anggota_pembimbing, ketua_penguji, anggota_penguji, created_by) VALUES ('tesis', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $id = dbExecute($sql, [$prodi_id, $nama_mhs, $nim_mhs, $konsentrasi, $judul_tesis, $tanggal_sidang, $ketua_pembimbing, $anggota_pembimbing, $ketua_penguji, $anggota_penguji, $created_by]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lampiran berhasil dibuat dan disimpan ke riwayat.'];
        header('Location: buat_lampiran_tesis?step=result&id=' . $id);
        exit;
    } elseif ($_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $user = getCurrentUser();
        if (isSuperAdmin()) {
            dbExecute("DELETE FROM riwayat_lampiran WHERE id=?", [$id]);
        } else {
            dbExecute("DELETE FROM riwayat_lampiran WHERE id=? AND prodi_id=?", [$id, $user['prodi_id']]);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Riwayat lampiran berhasil dihapus.'];
        header('Location: buat_lampiran_tesis');
        exit;
    } elseif ($_POST['action'] === 'update') {
        $id = (int)$_POST['id'];
        $user = getCurrentUser();
        $prodi_id = $_POST['prodi_id'];
        $nama_mhs = $_POST['nama_mhs'];
        $nim_mhs = $_POST['nim_mhs'];
        $konsentrasi = $_POST['konsentrasi'] ?? '';
        $judul_tesis = $_POST['judul_tesis'];
        $tanggal_sidang = $_POST['tanggal_sidang'];
        $ketua_pembimbing = $_POST['ketua_pembimbing'] === 'lainnya' ? $_POST['ketua_pembimbing_manual'] : $_POST['ketua_pembimbing'];
        $anggota_pembimbing = $_POST['anggota_pembimbing'] === 'lainnya' ? $_POST['anggota_pembimbing_manual'] : $_POST['anggota_pembimbing'];
        $ketua_penguji = $_POST['ketua_penguji'] === 'lainnya' ? $_POST['ketua_penguji_manual'] : $_POST['ketua_penguji'];
        $anggota_penguji = $_POST['anggota_penguji'] === 'lainnya' ? $_POST['anggota_penguji_manual'] : $_POST['anggota_penguji'];

        // check permission
        $row = dbQueryOne("SELECT prodi_id FROM riwayat_lampiran WHERE id=?", [$id]);
        if ($row && (isSuperAdmin() || $row['prodi_id'] == $user['prodi_id'])) {
            $sql = "UPDATE riwayat_lampiran SET prodi_id=?, nama_mhs=?, nim_mhs=?, konsentrasi=?, judul_tesis=?, tanggal_sidang=?, ketua_pembimbing=?, anggota_pembimbing=?, ketua_penguji=?, anggota_penguji=? WHERE id=?";
            dbExecute($sql, [$prodi_id, $nama_mhs, $nim_mhs, $konsentrasi, $judul_tesis, $tanggal_sidang, $ketua_pembimbing, $anggota_pembimbing, $ketua_penguji, $anggota_penguji, $id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lampiran berhasil diperbarui.'];
            header('Location: ?step=result&id=' . $id);
            exit;
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak.'];
            header('Location: ?');
            exit;
        }
    }
}

$step = $_GET['step'] ?? 'history';

if ($step === 'result') {
    $id = (int)($_GET['id'] ?? 0);
    $data = dbQueryOne("SELECT * FROM riwayat_lampiran WHERE id=?", [$id]);
    if (!$data) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak ditemukan.'];
        header('Location: buat_lampiran_tesis');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-5xl mx-auto pb-10">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">📚 Berkas & Lampiran Tesis</h2>
        <p class="text-slate-500 dark:text-slate-400">Buat dokumen lampiran secara otomatis dan lihat riwayat dokumen yang sudah dibuat.</p>
    </div>

    <?php if ($step === 'history'): ?>
        <div class="mb-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-slate-800 dark:text-white">Riwayat Lampiran Tesis</h3>
            <a href="buat_lampiran_tesis?step=form" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-nusa hover:bg-nusa-dark text-white rounded-lg font-semibold transition-all shadow hover:shadow-md text-sm font-bold shadow-lg shadow-blue-500/30">+ Buat Lampiran Baru</a>
        </div>
        
        <?php
        $user = getCurrentUser();
        if (isSuperAdmin()) {
            $riwayat = dbQuery("SELECT r.*, p.nama as nama_prodi FROM riwayat_lampiran r JOIN prodi p ON r.prodi_id = p.id WHERE r.jenis = 'tesis' ORDER BY r.created_at DESC");
        } else {
            $riwayat = dbQuery("SELECT r.*, p.nama as nama_prodi FROM riwayat_lampiran r JOIN prodi p ON r.prodi_id = p.id WHERE r.prodi_id = ? AND r.jenis = 'tesis' ORDER BY r.created_at DESC", [$user['prodi_id']]);
        }
        ?>

        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
<tr class="border-b border-slate-200 dark:border-slate-700">
                        <tr class="bg-slate-50 dark:bg-slate-900-bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm text-slate-500 dark:text-slate-400 text-sm border-b border-slate-200 dark:border-slate-700">
                            <th class="p-4 font-medium">Mahasiswa</th>
                            <th class="p-4 font-medium">Judul Tesis</th>
                            <th class="p-4 font-medium">Tanggal Sidang</th>
                            <th class="p-4 font-medium">Prodi</th>
                            <th class="p-4 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-700">
                        <?php if (empty($riwayat)): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition"><td class="py-3 px-4" colspan="5" class="p-8 text-center text-slate-400 dark:text-slate-500">Belum ada riwayat lampiran tesis yang dibuat.</td></tr>
                        <?php else: ?>
                            <?php foreach ($riwayat as $r): ?>
                                <tr class="hover:bg-slate-50 dark:bg-slate-900-bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm/50 transition">
                                    <td class="py-3 px-4 p-4">
                                        <div class="font-semibold text-slate-800 dark:text-white"><?= e($r['nama_mhs']) ?></div>
                                        <div class="text-xs text-slate-400 dark:text-slate-500"><?= e($r['nim_mhs']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 p-4 text-slate-800 dark:text-white max-w-xs truncate" title="<?= e($r['judul_tesis']) ?>">
                                        <?= e($r['judul_tesis']) ?>
                                    </td>
                                    <td class="py-3 px-4 p-4 text-slate-500 dark:text-slate-400">
                                        <?= formatTanggal($r['tanggal_sidang']) ?>
                                    </td>
                                    <td class="py-3 px-4 p-4 text-slate-500 dark:text-slate-400">
                                        <?= e($r['nama_prodi']) ?>
                                    </td>
                                    <td class="py-3 px-4 p-4 text-right space-x-3 whitespace-nowrap">
                                        <a href="buat_lampiran_tesis?step=result&id=<?= $r['id'] ?>" class="text-blue-400 hover:text-blue-300">👁️ Lihat/Cetak</a>
                                        <a href="?step=form&edit_id=<?= $r['id'] ?>" class="text-green-400 hover:text-green-300" title="Edit">✏️</a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat dokumen ini?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="text-red-400 hover:text-red-300" title="Hapus">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>


    <?php if ($step === 'form'): ?>
        <?php
        $editData = [];
        $edit_id = (int)($_GET['edit_id'] ?? 0);
        if ($edit_id > 0) {
            $editData = dbQueryOne("SELECT * FROM riwayat_lampiran WHERE id=?", [$edit_id]);
        }
        $activeDosen = dbQuery("SELECT nama, prodi_id FROM dosen WHERE status='Aktif' ORDER BY nama ASC");
        $activeDosenNames = array_column($activeDosen, 'nama');
        
        $kp_val = $editData['ketua_pembimbing'] ?? '';
        $is_kp_lain = $kp_val && !in_array($kp_val, $activeDosenNames);
        
        $ap_val = $editData['anggota_pembimbing'] ?? '';
        $is_ap_lain = $ap_val && !in_array($ap_val, $activeDosenNames);
        
        $kpenguji_val = $editData['ketua_penguji'] ?? '';
        $is_kpenguji_lain = $kpenguji_val && !in_array($kpenguji_val, $activeDosenNames);
        
        $apenguji_val = $editData['anggota_penguji'] ?? '';
        $is_apenguji_lain = $apenguji_val && !in_array($apenguji_val, $activeDosenNames);
        ?>
        <div class="mb-4">

            <a href="buat_lampiran_tesis" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg font-semibold transition-all text-sm text-xs inline-flex items-center gap-1">← Kembali ke Riwayat</a>
        </div>
        <form method="POST" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-6 space-y-6">
            <input type="hidden" name="action" value="<?= $edit_id ? 'update' : 'generate' ?>">
            <?php if($edit_id): ?><input type="hidden" name="id" value="<?= $edit_id ?>"><?php endif; ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Mahasiswa -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-blue-400 border-b border-slate-200 dark:border-slate-700 pb-2">1. Data Mahasiswa</h3>
                    
                    <div>
                        <label class="block text-sm text-slate-800 dark:text-white mb-1">Nama Mahasiswa <span class="text-red-400">*</span></label>
                        <input type="text" name="nama_mhs" value="<?= e($editData['nama_mhs'] ?? '') ?>" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors" placeholder="Contoh: Asep Surahman Sulaeman">
                    </div>
                    
                    <div>
                        <label class="block text-sm text-slate-800 dark:text-white mb-1">NIM <span class="text-red-400">*</span></label>
                        <input type="text" name="nim_mhs" value="<?= e($editData['nim_mhs'] ?? '') ?>" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors" placeholder="Contoh: 2023MIF001">
                    </div>
                    
                    <div>
                        <label class="block text-sm text-slate-800 dark:text-white mb-1">Konsentrasi</label>
                        <input type="text" name="konsentrasi" value="<?= e($editData['konsentrasi'] ?? '') ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors" placeholder="Contoh: Keuangan, Pendidikan Agama Islam, dll">
                    </div>

                    <div>
                        <label class="block text-sm text-slate-800 dark:text-white mb-1">Program Studi <span class="text-red-400">*</span></label>
                        <select name="prodi_id" id="prodi_id" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors" onchange="filterDosenByProdi()">
                            <?php foreach($allProdi as $p): ?>
                                <option value="<?=$p['id']?>" <?= (($edit_id ? $editData['prodi_id'] : $user['prodi_id']) == $p['id']) ? 'selected' : '' ?>><?=e($p['nama'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Tesis & Sidang -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-green-400 border-b border-slate-200 dark:border-slate-700 pb-2">2. Tesis & Sidang</h3>
                    
                    <div>
                        <label class="block text-sm text-slate-800 dark:text-white mb-1">Judul Tesis <span class="text-red-400">*</span></label>
                        <textarea name="judul_tesis" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors" rows="3" placeholder="Masukkan judul tesis secara lengkap..."><?= e($editData['judul_tesis'] ?? '') ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-slate-800 dark:text-white mb-1">Tanggal Sidang <span class="text-red-400">*</span></label>
                        <input type="date" name="tanggal_sidang" value="<?= e($editData['tanggal_sidang'] ?? '') ?>" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors">
                    </div>
                </div>

                <!-- Tim Pembimbing -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-purple-400 border-b border-slate-200 dark:border-slate-700 pb-2">3. Tim Pembimbing</h3>
                    
                    <div>
                        <label class="block text-sm text-slate-800 dark:text-white mb-1">Ketua Pembimbing <span class="text-red-400">*</span></label>
                        <select name="ketua_pembimbing" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors" onchange="toggleManualInput(this, 'ketua_pembimbing_manual')">
                            <option value="">-- Pilih Ketua Pembimbing --</option>
                            <?php foreach($activeDosen as $d): ?>
                                <option value="<?= e($d['nama']) ?>" data-prodi="<?= $d['prodi_id'] ?>" <?= (!$is_kp_lain && e($kp_val) === e($d['nama'])) ? 'selected' : '' ?>><?= e($d['nama']) ?></option>
                            <?php endforeach; ?>
                            <option value="lainnya" data-prodi="all" <?= $is_kp_lain ? 'selected' : '' ?>>Lainnya (Ketik Manual)</option>
                        </select>
                        <input type="text" name="ketua_pembimbing_manual" value="<?= $is_kp_lain ? e($kp_val) : '' ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors mt-2 <?= $is_kp_lain ? '' : 'hidden' ?>" placeholder="Ketik nama beserta gelar..." <?= $is_kp_lain ? 'required' : '' ?>>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-slate-800 dark:text-white mb-1">Anggota Pembimbing <span class="text-red-400">*</span></label>
                        <select name="anggota_pembimbing" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors" onchange="toggleManualInput(this, 'anggota_pembimbing_manual')">
                            <option value="">-- Pilih Anggota Pembimbing --</option>
                            <?php foreach($activeDosen as $d): ?>
                                <option value="<?= e($d['nama']) ?>" data-prodi="<?= $d['prodi_id'] ?>" <?= (!$is_ap_lain && e($ap_val) === e($d['nama'])) ? 'selected' : '' ?>><?= e($d['nama']) ?></option>
                            <?php endforeach; ?>
                            <option value="lainnya" data-prodi="all" <?= $is_ap_lain ? 'selected' : '' ?>>Lainnya (Ketik Manual)</option>
                        </select>
                        <input type="text" name="anggota_pembimbing_manual" value="<?= $is_ap_lain ? e($ap_val) : '' ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors mt-2 <?= $is_ap_lain ? '' : 'hidden' ?>" placeholder="Ketik nama beserta gelar..." <?= $is_ap_lain ? 'required' : '' ?>>
                    </div>
                </div>

                <!-- Tim Penguji -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-orange-400 border-b border-slate-200 dark:border-slate-700 pb-2">4. Tim Penguji</h3>
                    
                    <div>
                        <label class="block text-sm text-slate-800 dark:text-white mb-1">Ketua Penguji <span class="text-red-400">*</span></label>
                        <select name="ketua_penguji" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors" onchange="toggleManualInput(this, 'ketua_penguji_manual')">
                            <option value="">-- Pilih Ketua Penguji --</option>
                            <?php foreach($activeDosen as $d): ?>
                                <option value="<?= e($d['nama']) ?>" data-prodi="<?= $d['prodi_id'] ?>" <?= (!$is_kpenguji_lain && e($kpenguji_val) === e($d['nama'])) ? 'selected' : '' ?>><?= e($d['nama']) ?></option>
                            <?php endforeach; ?>
                            <option value="lainnya" data-prodi="all" <?= $is_kpenguji_lain ? 'selected' : '' ?>>Lainnya (Ketik Manual)</option>
                        </select>
                        <input type="text" name="ketua_penguji_manual" value="<?= $is_kpenguji_lain ? e($kpenguji_val) : '' ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors mt-2 <?= $is_kpenguji_lain ? '' : 'hidden' ?>" placeholder="Ketik nama beserta gelar..." <?= $is_kpenguji_lain ? 'required' : '' ?>>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-slate-800 dark:text-white mb-1">Anggota Penguji <span class="text-red-400">*</span></label>
                        <select name="anggota_penguji" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors" onchange="toggleManualInput(this, 'anggota_penguji_manual')">
                            <option value="">-- Pilih Anggota Penguji --</option>
                            <?php foreach($activeDosen as $d): ?>
                                <option value="<?= e($d['nama']) ?>" data-prodi="<?= $d['prodi_id'] ?>" <?= (!$is_apenguji_lain && e($apenguji_val) === e($d['nama'])) ? 'selected' : '' ?>><?= e($d['nama']) ?></option>
                            <?php endforeach; ?>
                            <option value="lainnya" data-prodi="all" <?= $is_apenguji_lain ? 'selected' : '' ?>>Lainnya (Ketik Manual)</option>
                        </select>
                        <input type="text" name="anggota_penguji_manual" value="<?= $is_apenguji_lain ? e($apenguji_val) : '' ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa focus:ring-1 focus:ring-nusa transition-colors mt-2 <?= $is_apenguji_lain ? '' : 'hidden' ?>" placeholder="Ketik nama beserta gelar..." <?= $is_apenguji_lain ? 'required' : '' ?>>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t border-slate-200 dark:border-slate-700 pt-6 flex justify-end gap-3">
                <button type="reset" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg font-semibold transition-all text-sm py-2 px-6">Reset</button>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-nusa hover:bg-nusa-dark text-white rounded-lg font-semibold transition-all shadow hover:shadow-md text-sm py-2 px-8 font-bold flex items-center gap-2" style="background: linear-gradient(135deg, var(--color-primary-hover), #8b5cf6); color: white; border: none; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);">
                    <?= $edit_id ? '💾 Simpan Perubahan' : '✨ Buat & Simpan Dokumen' ?>
                </button>
            </div>
        </form>

        <script>
        function toggleManualInput(selectObj, manualInputName) {
            var manualInput = document.querySelector('input[name="' + manualInputName + '"]');
            if (selectObj.value === 'lainnya') {
                manualInput.classList.remove('hidden');
                manualInput.required = true;
            } else {
                manualInput.classList.add('hidden');
                manualInput.required = false;
            }
        }

        function filterDosenByProdi() {
            var prodiId = document.getElementById('prodi_id').value;
            var selects = ['ketua_pembimbing', 'anggota_pembimbing', 'ketua_penguji', 'anggota_penguji'];
            
            selects.forEach(function(name) {
                var selectElement = document.querySelector('select[name="' + name + '"]');
                if(!selectElement) return;
                var options = selectElement.querySelectorAll('option');
                
                // Track if currently selected option is still valid
                var selectedValueValid = false;
                var currentValue = selectElement.value;

                options.forEach(function(opt) {
                    if (opt.value === "") return; // Skip placeholder
                    var optProdi = opt.getAttribute('data-prodi');
                    if (prodiId === "" || optProdi === prodiId || optProdi === "all") {
                        opt.style.display = '';
                        opt.disabled = false;
                        if(opt.value === currentValue) selectedValueValid = true;
                    } else {
                        opt.style.display = 'none';
                        opt.disabled = true;
                    }
                });

                if(!selectedValueValid && currentValue !== "") {
                    selectElement.value = "";
                }
            });
        }
        
        // Run on load
        document.addEventListener('DOMContentLoaded', function() {
            filterDosenByProdi();
        });
        </script>
    <?php endif; ?>

    <?php if ($step === 'result'): ?>
        <div class="mb-4">
            <a href="buat_lampiran_tesis" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg font-semibold transition-all text-sm text-xs inline-flex items-center gap-1">← Kembali ke Riwayat</a>
        </div>
        
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-6 border border-blue-500/30 bg-blue-900/10 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center text-2xl">🎓</div>
                <div>
                    <h3 class="text-lg font-bold text-blue-400"><?= e($data['nama_mhs']) ?> <span class="text-sm font-normal text-slate-500 dark:text-slate-400">(<?= e($data['nim_mhs']) ?>)</span></h3>
                    <p class="text-sm text-slate-800 dark:text-white mt-1 line-clamp-1">"<?= e($data['judul_tesis']) ?>"</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php 
            $lampirans = [
                ['id'=>0, 'icon'=>'👨‍🏫', 'title'=>'Lampiran 1', 'desc'=>'Ketua Pembimbing', 'target'=>$data['ketua_pembimbing']],
                ['id'=>1, 'icon'=>'👨‍🏫', 'title'=>'Lampiran 2', 'desc'=>'Anggota Pembimbing', 'target'=>$data['anggota_pembimbing']],
                ['id'=>2, 'icon'=>'🕵️‍♂️', 'title'=>'Lampiran 3', 'desc'=>'Ketua Penguji', 'target'=>$data['ketua_penguji']],
                ['id'=>3, 'icon'=>'🕵️‍♂️', 'title'=>'Lampiran 4', 'desc'=>'Anggota Penguji', 'target'=>$data['anggota_penguji']],
                ['id'=>4, 'icon'=>'📋', 'title'=>'Lampiran 5', 'desc'=>'Berita Acara Sidang', 'target'=>'Rekapitulasi Nilai Akhir'],
                ['id'=>5, 'icon'=>'📜', 'title'=>'Lampiran 6', 'desc'=>'Lembar Pengesahan', 'target'=>'Persetujuan Tim Penguji'],
                ['id'=>6, 'icon'=>'📝', 'title'=>'Lampiran 7', 'desc'=>'Logbook Bimbingan Tesis', 'target'=>'Log Konsultasi Tesis'],
                ['id'=>7, 'icon'=>'📝', 'title'=>'Lampiran 8', 'desc'=>'Persetujuan Pembimbing', 'target'=>'Persetujuan Ujian Tesis'],
                ['id'=>8, 'icon'=>'📋', 'title'=>'Lampiran 9', 'desc'=>'Formulir Pendaftaran Sidang', 'target'=>'Kelengkapan Pendaftaran Sidang Tesis'],
                ['id'=>9, 'icon'=>'✅', 'title'=>'Lampiran 10', 'desc'=>'Bukti Kehadiran Seminar', 'target'=>'Daftar Kehadiran Seminar/Sidang Tesis'],
                ['id'=>10, 'icon'=>'🔏', 'title'=>'Lampiran 11', 'desc'=>'Pernyataan Orisinalitas', 'target'=>'Surat Pernyataan Keaslian Karya Ilmiah'],
            ];

            foreach($lampirans as $l): ?>
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-5 hover:border-blue-500/50 transition flex flex-col h-full group">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" class="cb-lampiran w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 cursor-pointer" value="<?= $l['id'] ?>">
                        <div class="text-3xl bg-slate-800 p-3 rounded-xl group-hover:scale-110 transition"><?= $l['icon'] ?></div>
                    </div>
                    <span class="text-xs font-bold px-2 py-1 bg-slate-800 text-slate-500 dark:text-slate-400 rounded"><?= $l['title'] ?></span>
                </div>
                <h4 class="font-semibold text-slate-800 dark:text-white mb-1"><?= $l['desc'] ?></h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 flex-1 line-clamp-2"><?= e($l['target']) ?></p>
                
                <a href="cetak_lampiran?id=<?= $data['id'] ?>&idx=<?= $l['id'] ?>" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg font-semibold transition-all text-sm border-blue-500/30 text-blue-400 hover:bg-blue-500 hover:text-white justify-center text-sm py-2">
                    👁️ Lihat & Cetak
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-6 flex flex-wrap justify-center gap-4">
            <button type="button" onclick="document.querySelectorAll('.cb-lampiran').forEach(cb => cb.checked = !cb.checked)" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg font-semibold transition-all text-sm py-3 px-6">
                ☑️ Pilih Semua / Batal Pilih
            </button>
            <button type="button" id="btn-dl-zip" onclick="downloadZipTerpilih()" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition-all shadow hover:shadow-md text-sm py-3 px-6 shadow-lg shadow-green-500/30">
                📦 Download ZIP Terpilih
            </button>
            <a href="cetak_lampiran?id=<?= $data['id'] ?>&idx=all" target="_blank" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-nusa hover:bg-nusa-dark text-white rounded-lg font-semibold transition-all shadow hover:shadow-md text-sm py-3 px-6 shadow-lg shadow-blue-500/30">
                🖨️ Cetak Semua
            </a>
        </div>
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
        <script>
        var zipQueue = [];
        var zipInstance = null;
        var downloadZipBtn = null;
        var currentIframe = null;

        function downloadZipTerpilih() {
            try {
                if (typeof JSZip === 'undefined') {
                    alert('JSZip library belum dimuat. Periksa koneksi internet Anda atau nonaktifkan pemblokir iklan.');
                    return;
                }
                
                var checkboxes = document.querySelectorAll('.cb-lampiran:checked');
                if (checkboxes.length === 0) {
                    alert('Pilih minimal satu lampiran untuk di-download.');
                    return;
                }
                
                downloadZipBtn = document.getElementById('btn-dl-zip');
                var oldText = downloadZipBtn.innerHTML;
                downloadZipBtn.innerHTML = '⏳ Memproses...';
                downloadZipBtn.disabled = true;
                
                zipInstance = new JSZip();
                zipQueue = [];
                checkboxes.forEach(function(cb) {
                    zipQueue.push(cb.value);
                });
                
                processNextZip(oldText);
            } catch(e) {
                alert('Error pada tombol ZIP: ' + e);
                if (downloadZipBtn) {
                    downloadZipBtn.innerHTML = '📦 Download ZIP Terpilih';
                    downloadZipBtn.disabled = false;
                }
            }
        }

        function processNextZip(oldText) {
            if (zipQueue.length === 0) {
                downloadZipBtn.innerHTML = 'Memproses ZIP...';
                zipInstance.generateAsync({type:"blob"}).then(function(content) {
                    saveAs(content, "Lampiran_Tesis_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $data['nama_mhs']) ?>.zip");
                    downloadZipBtn.innerHTML = oldText;
                    downloadZipBtn.disabled = false;
                    if (currentIframe) {
                        currentIframe.remove();
                        currentIframe = null;
                    }
                }).catch(function(err) {
                    alert('Gagal membuat ZIP: ' + err);
                    downloadZipBtn.innerHTML = oldText;
                    downloadZipBtn.disabled = false;
                });
                return;
            }
            
            var idx = zipQueue.shift();
            var id = <?= $data['id'] ?>;
            
            if (currentIframe) {
                currentIframe.remove();
            }
            
            currentIframe = document.createElement('iframe');
            currentIframe.style.position = 'absolute';
            currentIframe.style.left = '-9999px';
            currentIframe.style.width = '800px';
            currentIframe.style.height = '600px';
            
            var iframeTimeout = setTimeout(function() {
                alert('Timeout saat memproses lampiran ke-' + (idx) + '. Melewati...');
                processNextZip(oldText);
            }, 15000); // 15 detik timeout per file
            
            currentIframe.onload = function() {
                setTimeout(function() {
                    try {
                        currentIframe.contentWindow.postMessage({action: 'get_word_blob'}, '*');
                    } catch(e) {
                        clearTimeout(iframeTimeout);
                        alert('Gagal postMessage ke iframe: ' + e);
                        processNextZip(oldText);
                    }
                }, 500); // Beri jeda sedikit setelah iframe load
            };
            
            document.body.appendChild(currentIframe);
            currentIframe.dataset.timeoutId = iframeTimeout;
            currentIframe.src = 'cetak_lampiran?id=' + id + '&idx=' + idx;
        }

        window.addEventListener('message', function(e) {
            if (e.data && e.data.action === 'word_blob') {
                if (currentIframe && currentIframe.dataset.timeoutId) clearTimeout(currentIframe.dataset.timeoutId);
                zipInstance.file(e.data.filename, e.data.blob);
                processNextZip(downloadZipBtn ? downloadZipBtn.getAttribute('data-original-text') || '📦 Download ZIP Terpilih' : '📦 Download ZIP Terpilih');
            } else if (e.data && e.data.action === 'word_blob_error') {
                if (currentIframe && currentIframe.dataset.timeoutId) clearTimeout(currentIframe.dataset.timeoutId);
                alert('Error dari iframe: ' + e.data.error);
                processNextZip(downloadZipBtn ? downloadZipBtn.getAttribute('data-original-text') || '📦 Download ZIP Terpilih' : '📦 Download ZIP Terpilih');
            }
        });
        </script>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
