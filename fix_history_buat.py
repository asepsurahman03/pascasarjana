import os

file_buat = 'pages/surat_buat.php'
file_cetak = 'api/cetak_surat.php'

# 1. Update surat_buat.php
with open(file_buat, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix 1a: Redirect adding &src=buat
content = content.replace(
    'header("Location: ".BASE_URL."/api/cetak_surat.php?id=$id&mode=view");',
    'header("Location: ".BASE_URL."/api/cetak_surat.php?id=$id&mode=view&src=buat");'
)

# Fix 1b: History Query
old_query = '$historyData = dbQuery("SELECT id, jenis_surat, perihal, created_at FROM surat WHERE prodi_id = ? ORDER BY created_at DESC LIMIT ?", [$user[\'prodi_id\'], $historyLimit]);'
new_query = """if (isSuperAdmin()) {
    $historyData = dbQuery("SELECT id, jenis_surat, perihal, created_at FROM surat ORDER BY created_at DESC LIMIT ?", [$historyLimit]);
} else {
    $historyData = dbQuery("SELECT id, jenis_surat, perihal, created_at FROM surat WHERE prodi_id = ? ORDER BY created_at DESC LIMIT ?", [$user['prodi_id'], $historyLimit]);
}"""
content = content.replace(old_query, new_query)

# Fix 1c: Sidebar links target and src
content = content.replace(
    '<a href="<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $h[\'id\'] ?>&mode=view" target="_blank"',
    '<a href="<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $h[\'id\'] ?>&mode=view&src=buat" target="_self"'
)

with open(file_buat, 'w', encoding='utf-8') as f:
    f.write(content)


# 2. Update cetak_surat.php
with open(file_cetak, 'r', encoding='utf-8') as f:
    content = f.read()

# Find the back button link
old_back = '<a href="../pages/surat_keluaran.php" class="tb-btn gray"'
new_back = """<?php
        $backUrl = "../pages/surat_keluaran.php";
        if (isset($_GET['src']) && $_GET['src'] === 'buat') {
            $backUrl = "../pages/surat_buat.php";
        }
        ?>
        <a href="<?= $backUrl ?>" class="tb-btn gray\""""

content = content.replace(old_back, new_back)

with open(file_cetak, 'w', encoding='utf-8') as f:
    f.write(content)

print("surat_buat.php and cetak_surat.php updated.")
