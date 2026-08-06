import os
import re

file_path = 'pages/surat_buat.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add Data Fetching Logic
php_fetch_logic = """
require_once __DIR__ . '/../includes/header.php';

// === AMBIL RIWAYAT SURAT ALA CHATGPT ===
$user = getCurrentUser();
$historyLimit = 30;
$historyData = dbQuery("SELECT id, jenis_surat, perihal, created_at FROM surat WHERE prodi_id = ? ORDER BY created_at DESC LIMIT ?", [$user['prodi_id'], $historyLimit]);

// Helper untuk mengelompokkan waktu
$historyGrouped = [
    'Hari Ini' => [],
    'Kemarin' => [],
    '7 Hari Terakhir' => [],
    'Bulan Ini' => [],
    'Lebih Lama' => []
];

$today = new DateTime('today');
$yesterday = new DateTime('yesterday');
$last7days = (new DateTime('today'))->modify('-7 days');
$thisMonth = (new DateTime('today'))->modify('first day of this month');

foreach ($historyData as $row) {
    $date = new DateTime($row['created_at']);
    $date->setTime(0, 0, 0); // normalize ke tengah malam

    if ($date == $today) {
        $historyGrouped['Hari Ini'][] = $row;
    } elseif ($date == $yesterday) {
        $historyGrouped['Kemarin'][] = $row;
    } elseif ($date >= $last7days) {
        $historyGrouped['7 Hari Terakhir'][] = $row;
    } elseif ($date >= $thisMonth) {
        $historyGrouped['Bulan Ini'][] = $row;
    } else {
        $historyGrouped['Lebih Lama'][] = $row;
    }
}
// ======================================

$sbData = json_encode([
"""

content = content.replace(
    "require_once __DIR__ . '/../includes/header.php';\n\n$sbData = json_encode([",
    php_fetch_logic
)

# 2. Add Layout Wrapper and Sidebar
html_wrapper_start = """
<style>
/* Kustom scrollbar untuk sidebar */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
</style>

<div class="flex h-[calc(100vh-90px)] -mt-4 -mx-4 lg:-mx-6 lg:-mt-6 border-t border-slate-700">
    <!-- Panel Kiri: History Surat -->
    <div class="w-64 md:w-72 bg-slate-900 border-r border-slate-700 flex flex-col flex-shrink-0 overflow-hidden">
        <div class="p-4">
            <a href="surat_buat.php" class="flex items-center gap-3 w-full px-4 py-3 bg-slate-800 hover:bg-slate-700 transition rounded-xl text-white font-medium border border-slate-600 shadow-sm group">
                <span class="text-xl leading-none group-hover:text-blue-400 transition">+</span> Buat Surat Baru
            </a>
        </div>
        <div class="flex-1 overflow-y-auto px-3 pb-4 space-y-6 custom-scrollbar">
            <?php foreach ($historyGrouped as $groupName => $items): ?>
                <?php if (count($items) > 0): ?>
                    <div>
                        <h4 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 px-2"><?= $groupName ?></h4>
                        <div class="space-y-1">
                            <?php foreach ($items as $h): ?>
                                <a href="<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $h['id'] ?>&mode=view" target="_blank" class="block w-full text-left px-3 py-2.5 rounded-lg hover:bg-slate-800 transition text-sm text-slate-300 group">
                                    <div class="flex items-center gap-3">
                                        <span class="text-slate-500 group-hover:text-blue-400 transition">📄</span>
                                        <span class="truncate flex-1"><?= e($h['perihal'] ?: $h['jenis_surat']) ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Panel Kanan: Area AI -->
    <div class="flex-1 overflow-y-auto relative bg-slate-800/20 custom-scrollbar flex flex-col items-center justify-center p-6">
"""

# Replace the start of the form area
content = content.replace(
    "<form method=\"POST\" id=\"form-surat\">",
    html_wrapper_start + "\n<form method=\"POST\" id=\"form-surat\" class=\"w-full max-w-4xl\">"
)

# 3. Close the wrapper at the end
content = content.replace(
    "<?php require_once __DIR__ . '/../includes/footer.php'; ?>",
    "    </div>\n</div>\n\n<?php require_once __DIR__ . '/../includes/footer.php'; ?>"
)

# 4. Modify the hero box height
content = content.replace(
    "<div class=\"flex flex-col items-center justify-center min-h-[60vh] px-4\">",
    "<div class=\"flex flex-col items-center justify-center w-full\">"
)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("surat_buat.php has been updated with ChatGPT-style layout.")
