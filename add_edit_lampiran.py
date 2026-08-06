import os

def process_file(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Add Edit button to table
    edit_btn = '<a href="?step=form&edit_id=<?= $r[\'id\'] ?>" class="text-green-400 hover:text-green-300">✏️ Edit</a>\n                                        <a href="'
    content = content.replace('<a href="buat_lampiran', edit_btn + 'buat_lampiran', 1)

    # 2. Add PHP logic for editData and UPDATE action
    # Around line 41, before $step = $_GET['step'] ?? 'history';
    php_update_logic = """
    } elseif ($_POST['action'] === 'update') {
        $id = (int)$_POST['id'];
        $user = getCurrentUser();
        $prodi_id = $_POST['prodi_id'];
        $nama_mhs = $_POST['nama_mhs'];
        $nim_mhs = $_POST['nim_mhs'];
        $judul_tesis = $_POST['judul_tesis'];
        $tanggal_sidang = $_POST['tanggal_sidang'];
        $ketua_pembimbing = $_POST['ketua_pembimbing'];
        $anggota_pembimbing = $_POST['anggota_pembimbing'];
        $ketua_penguji = $_POST['ketua_penguji'];
        $anggota_penguji = $_POST['anggota_penguji'];

        // check permission
        $row = dbQueryOne("SELECT prodi_id FROM riwayat_lampiran WHERE id=?", [$id]);
        if ($row && (isSuperAdmin() || $row['prodi_id'] == $user['prodi_id'])) {
            $sql = "UPDATE riwayat_lampiran SET prodi_id=?, nama_mhs=?, nim_mhs=?, judul_tesis=?, tanggal_sidang=?, ketua_pembimbing=?, anggota_pembimbing=?, ketua_penguji=?, anggota_penguji=? WHERE id=?";
            dbExecute($sql, [$prodi_id, $nama_mhs, $nim_mhs, $judul_tesis, $tanggal_sidang, $ketua_pembimbing, $anggota_pembimbing, $ketua_penguji, $anggota_penguji, $id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Lampiran berhasil diperbarui.'];
            header('Location: ?step=result&id=' . $id);
            exit;
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akses ditolak.'];
            header('Location: ?');
            exit;
        }
    }
"""
    content = content.replace("    }\n}\n\n$step = $_GET['step']", "    }" + php_update_logic + "}\n\n$step = $_GET['step']")

    # 3. Add $editData fetch in form step
    form_logic = """
    <?php if ($step === 'form'): ?>
        <?php
        $editData = [];
        $edit_id = (int)($_GET['edit_id'] ?? 0);
        if ($edit_id > 0) {
            $editData = dbQueryOne("SELECT * FROM riwayat_lampiran WHERE id=?", [$edit_id]);
        }
        ?>
        <div class="mb-4">
"""
    content = content.replace("    <?php if ($step === 'form'): ?>\n        <div class=\"mb-4\">", form_logic)

    # 4. Modify form inputs to have values
    content = content.replace('<input type="hidden" name="action" value="generate">', '<input type="hidden" name="action" value="<?= $edit_id ? \'update\' : \'generate\' ?>">\n            <?php if($edit_id): ?><input type="hidden" name="id" value="<?= $edit_id ?>"><?php endif; ?>')
    
    # Text inputs
    fields = ['nama_mhs', 'nim_mhs', 'tanggal_sidang', 'ketua_pembimbing', 'anggota_pembimbing', 'ketua_penguji', 'anggota_penguji']
    for field in fields:
        content = content.replace(f'name="{field}" required class="form-input"', f'name="{field}" value="<?= e($editData[\'{field}\'] ?? \'\') ?>" required class="form-input"')
    
    # Textarea (judul_tesis)
    content = content.replace(
        '<textarea name="judul_tesis" required class="form-input" rows="3" placeholder="Masukkan judul tesis secara lengkap..."></textarea>',
        '<textarea name="judul_tesis" required class="form-input" rows="3" placeholder="Masukkan judul tesis secara lengkap..."><?= e($editData[\'judul_tesis\'] ?? \'\') ?></textarea>'
    )
    content = content.replace(
        '<textarea name="judul_tesis" required class="form-input" rows="3" placeholder="Masukkan judul proposal secara lengkap..."></textarea>',
        '<textarea name="judul_tesis" required class="form-input" rows="3" placeholder="Masukkan judul proposal secara lengkap..."><?= e($editData[\'judul_tesis\'] ?? \'\') ?></textarea>'
    )

    # Select prodi
    # We already have: <?= ($user['prodi_id'] == $p['id']) ? 'selected' : '' ?>
    # Need to change to: <?= (($edit_id ? $editData['prodi_id'] : $user['prodi_id']) == $p['id']) ? 'selected' : '' ?>
    content = content.replace(
        "<?= ($user['prodi_id'] == $p['id']) ? 'selected' : '' ?>",
        "<?= (($edit_id ? $editData['prodi_id'] : $user['prodi_id']) == $p['id']) ? 'selected' : '' ?>"
    )

    # Change button text if editing
    content = content.replace(
        "✨ Buat & Simpan Dokumen",
        "<?= $edit_id ? '💾 Simpan Perubahan' : '✨ Buat & Simpan Dokumen' ?>"
    )

    # Fix button classes
    content = content.replace('<a href="?step=form&edit_id=<?= $r[\'id\'] ?>" class="text-green-400 hover:text-green-300">✏️ Edit</a>', '<a href="?step=form&edit_id=<?= $r[\'id\'] ?>" class="text-green-400 hover:text-green-300 mr-2">✏️ Edit</a>')

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

process_file('pages/buat_lampiran_tesis.php')
process_file('pages/buat_lampiran_proposal.php')
print("Edit functionality added successfully.")
