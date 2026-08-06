<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pageTitle  = 'Buat Surat';
$breadcrumb = [['label'=>'Surat Keluaran','url'=>BASE_URL.'/pages/surat_keluaran.php'],['label'=>'Buat Surat']];

$allProdi  = getAllProdi();
$templates = dbQuery("SELECT * FROM template_surat ORDER BY jenis_surat");
$dupId     = (int)($_GET['dup'] ?? 0);
$suratDup  = $dupId ? dbQueryOne("SELECT * FROM surat WHERE id=?",[$dupId]) : null;
$jenisDef  = trim($_GET['jenis'] ?? ($suratDup['jenis_surat'] ?? ''));  // pre-select dari URL ?jenis=
$isiDefault = $suratDup['isi_surat'] ?? '';
if (!$isiDefault && $jenisDef) {
    foreach ($templates as $t) {
        if ($t['jenis_surat'] === $jenisDef) {
            $isiDefault = $t['isi_template'];
            break;
        }
    }
}
$tahunAkad = getSetting('tahun_akademik') ?: date('Y').'/'.(date('Y')+1);
$sessKey   = bin2hex(random_bytes(16)); // untuk autosave surat baru

$penerimaOpts = [
    'individual'        => 'Penerima Individual',
    'dosen_univ'        => 'Bapak/Ibu Dosen Pascasarjana Universitas Nusa Putra',
    'dosen_prodi'       => 'Bapak/Ibu Dosen Program Studi [Prodi]',
    'dosen_pembimbing'  => 'Bapak/Ibu Dosen Pembimbing Tesis/Disertasi',
    'dosen_penguji'     => 'Bapak/Ibu Dosen Penguji',
    'mhs_univ'          => 'Seluruh Mahasiswa Pascasarjana Universitas Nusa Putra',
    'mhs_prodi'         => 'Mahasiswa Program Studi [Prodi]',
    'mhs_angkatan'      => 'Mahasiswa Angkatan [Tahun]',
    'panitia'           => 'Panitia [Nama Kegiatan]',
    'yang_bersangkutan' => 'Yang Bersangkutan',
    'custom'            => 'Kepada Yth. [Custom]',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prodiId       = (int)$_POST['prodi_id'];
    $jenis         = trim($_POST['jenis_surat']);
    $jenisPenerima = $_POST['jenis_penerima'] ?? 'individual';
    $namaPenerima  = trim($_POST['nama_penerima']);
    $nimNidn       = trim($_POST['nim_nidn'] ?? '');
    $perihal       = trim($_POST['perihal']);
    $isiSurat      = $_POST['isi_surat'] ?? '';
    $tanggal       = $_POST['tanggal'] ?: date('Y-m-d');
    $status        = $_POST['status'] ?? 'Draf';
    $lampiran      = trim($_POST['lampiran'] ?? '-');
    $nomorOverride = trim($_POST['nomor_override'] ?? '');
    $hari          = getNamaHari($tanggal);

    // Validasi nomor duplikat
    $nomor = $nomorOverride ?: generateNomorSurat($prodiId, $tanggal);
    $cekDup = dbQueryOne("SELECT id FROM surat WHERE nomor_surat=? AND prodi_id=?", [$nomor, $prodiId]);
    if ($cekDup && !($nomorOverride && $_POST['force_nomor'])) {
        $_SESSION['flash'] = ['type'=>'error','message'=>"Nomor surat $nomor sudah digunakan. Gunakan override."];
        header('Location: surat_buat'); exit;
    }

    // Dapatkan kota dari prodi
    $prodiData = dbQueryOne("SELECT kota_surat FROM prodi WHERE id=?",[$prodiId]);
    $kota = $prodiData['kota_surat'] ?? 'Sukabumi';

    $id = dbExecute(
        "INSERT INTO surat(nomor_surat,jenis_surat,prodi_id,nama_penerima,nim_nidn,perihal,lampiran,tanggal,hari,kota,isi_surat,status,jenis_penerima,created_by)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [$nomor,$jenis,$prodiId,$namaPenerima,$nimNidn,$perihal,$lampiran,$tanggal,$hari,$kota,$isiSurat,$status,$jenisPenerima,$_SESSION['user_id']]
    );

    $prompt_awal = trim($_POST['prompt_awal'] ?? '');
    if ($prompt_awal) {
        dbExecute("INSERT INTO surat_chat (surat_id, role, content) VALUES (?, 'user', ?)", [$id, $prompt_awal]);
    }
    if ($isiSurat) {
        $aiReply = trim($_POST['ai_reply'] ?? 'Draf surat awal berhasil dibuat.');
        dbExecute("INSERT INTO surat_chat (surat_id, role, content, ai_reply) VALUES (?, 'assistant', ?, ?)", [$id, $isiSurat, $aiReply]);
    }

    logActivity('Buat Surat','surat',$nomor);
    $_SESSION['flash'] = ['type'=>'success','message'=>"Surat $nomor dibuat."];

    if ($_POST['submit_action'] === 'preview') {
        header("Location: surat_buat.php?id=$id");
    } else {
        header('Location: '. BASE_URL . '/pages/surat_keluaran');
    }
    exit;
}


require_once __DIR__ . '/../includes/header.php';
?>
<style>
/* Remove padding from main wrapper for full-screen chat interface */
.min-h-screen.pt-2 { padding-top: 0 !important; }
@media (min-width: 768px) {
    .min-h-screen.md\:pt-4 { padding-top: 0 !important; }
}
main { padding: 0 !important; max-width: 100% !important; }

/* Kustom scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
</style>
<?php
// === AMBIL RIWAYAT SURAT ALA CHATGPT ===
$user = getCurrentUser();

$chatId = (int)($_GET['id'] ?? 0);
$chatData = null;
$chatHistory = [];
if ($chatId) {
    if (isSuperAdmin()) {
        $chatData = dbQueryOne("SELECT * FROM surat WHERE id=?", [$chatId]);
    } else {
        $chatData = dbQueryOne("SELECT * FROM surat WHERE id=? AND prodi_id=?", [$chatId, $user['prodi_id']]);
    }
    if ($chatData) {
        $chatHistory = dbQuery("SELECT * FROM surat_chat WHERE surat_id=? ORDER BY created_at ASC", [$chatId]);
        // Jika surat lama belum punya history chat, buat satu entry otomatis dari isi_surat
        if (empty($chatHistory) && !empty($chatData['isi_surat'])) {
            $autoReply = 'Berikut adalah draf surat yang telah dibuat.';
            dbExecute("INSERT INTO surat_chat (surat_id, role, content, ai_reply) VALUES (?, 'assistant', ?, ?)",
                [$chatId, $chatData['isi_surat'], $autoReply]);
            $chatHistory = dbQuery("SELECT * FROM surat_chat WHERE surat_id=? ORDER BY created_at ASC", [$chatId]);
        }
    }
}

$historyLimit = 30;
if (isSuperAdmin() || empty($user['prodi_id'])) {
    // Super admin ATAU user tanpa prodi_id: tampilkan semua surat
    $historyData = dbQuery("SELECT id, jenis_surat, perihal, created_at, is_pinned FROM surat ORDER BY is_pinned DESC, created_at DESC LIMIT ?", [$historyLimit]);
} else {
    $historyData = dbQuery("SELECT id, jenis_surat, perihal, created_at, is_pinned FROM surat WHERE prodi_id = ? ORDER BY is_pinned DESC, created_at DESC LIMIT ?", [$user['prodi_id'], $historyLimit]);
}

// Helper untuk mengelompokkan waktu
$historyGrouped = [
    '📌 Dipin' => [],
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
    if (!empty($row['is_pinned'])) {
        $historyGrouped['📌 Dipin'][] = $row;
        continue; // skip date check
    }

    $date = new DateTime($row['created_at']);
    $date->setTime(0, 0, 0); // normalize ke tengah malam

    if ($date == $today) {
        $historyGrouped['Hari Ini'][] = $row;
    } elseif ($date == $yesterday) {
        $historyGrouped['Kemarin'][] = $row;
    } elseif ($date > $last7days) {
        $historyGrouped['7 Hari Terakhir'][] = $row;
    } elseif ($date >= $thisMonth) {
        $historyGrouped['Bulan Ini'][] = $row;
    } else {
        $historyGrouped['Lebih Lama'][] = $row;
    }
}
// ======================================

$sbData = json_encode([

    'prodis'  => array_map(fn($p) => ['id'=>$p['id'], 'nama'=>$p['nama']], $allProdi),
    'baseUrl' => BASE_URL,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<script>window._sb=<?= $sbData ?>;</script>


<style>
/* ======== CHAT LAYOUT ======== */
/* Wrapper memenuhi sisa tinggi <main> */
#surat-buat-wrapper {
    display: flex;
    width: 100%;
    /* Tinggi = viewport dikurangi topbar (~56px) dan dikurangi padding main (24px atas) */
    height: calc(100vh - 110px); min-height: 500px;
    overflow: hidden;
    background-color: transparent;
    
}

/* Panel kiri: riwayat surat */
#chat-history-sidebar {
    width: 260px;
    min-width: 260px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: width 0.3s ease, min-width 0.3s ease, transform 0.3s ease;
}

/* Sidebar collapsed state */
#chat-history-sidebar.sidebar-collapsed {
    width: 52px;
    min-width: 52px;
}
#chat-history-sidebar.sidebar-collapsed .sidebar-content,
#chat-history-sidebar.sidebar-collapsed .sidebar-search,
#chat-history-sidebar.sidebar-collapsed .sidebar-footer,
#chat-history-sidebar.sidebar-collapsed .sidebar-new-chat {
    display: none !important;
}
#chat-history-sidebar.sidebar-collapsed .sidebar-title {
    display: none;
}
#chat-history-sidebar.sidebar-collapsed .sidebar-header {
    justify-content: center;
    padding: 10px 8px;
}

/* Search highlight */
.hist-item-row.search-hidden { display: none !important; }
.search-group-hidden { display: none !important; }
.search-highlight { background: rgba(140,12,76,0.15); border-radius: 2px; font-weight: 600; }

/* Panel kanan: area AI */
#surat-buat-right {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background-color: transparent;
}

/* Responsive: layar kecil */
@media (max-width: 768px) {
    #surat-buat-wrapper {
        /* mobile: full width */
    }
    #chat-history-sidebar {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        transform: translateX(-100%);
        z-index: 50;
        box-shadow: 4px 0 24px rgba(0,0,0,.5);
    }
    #chat-history-sidebar.sidebar-open {
        transform: translateX(0);
    }
    #chat-sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.5);
        z-index: 45;
    }
    #chat-sidebar-overlay.visible {
        display: block;
    }
}

/* Kustom scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }

/* Chat bubble animations */
@keyframes blink-cursor {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}
.typing-cursor {
    display: inline-block;
    width: 2px;
    height: 1em;
    background: #6b1040;
    margin-left: 2px;
    vertical-align: text-bottom;
    animation: blink-cursor 0.7s infinite;
}
@keyframes bubble-in {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
.bubble-animate { animation: bubble-in 0.35s ease-out forwards; }

/* Typing dots animation (ChatGPT-style) */
@keyframes typing-dot {
    0%, 60%, 100% { transform: translateY(0); opacity: .4; }
    30%           { transform: translateY(-6px); opacity: 1; }
}
.typing-dots span {
    display: inline-block;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #8c0c4c;
    margin: 0 2px;
    animation: typing-dot 1.1s infinite;
}
.typing-dots span:nth-child(2) { animation-delay: .15s; }
.typing-dots span:nth-child(3) { animation-delay: .30s; }

/* Bubble action buttons (copy, regen) */
.bubble-actions { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
.bubble-action-btn {
    font-size:0.72rem; padding:4px 10px; border-radius:8px; border:1px solid #e2e8f0;
    background:#fff; color:#64748b; cursor:pointer; transition: all .15s;
    display:flex; align-items:center; gap:4px;
}
.dark .bubble-action-btn { background:#1e293b; border-color:#334155; color:#94a3b8; }
.bubble-action-btn:hover { background:#f1f5f9; color:#8c0c4c; border-color:#8c0c4c; }
.dark .bubble-action-btn:hover { background:#334155; color:#f06ea4; border-color:#f06ea4; }

/* Timestamp */
.bubble-ts { font-size:0.65rem; color:#94a3b8; margin-top:2px; }


/* Rename input di sidebar */
.hist-rename-input {
    flex:1; font-size:13px; padding:4px 8px; border-radius:6px;
    border:1px solid #8c0c4c; background:#fff; color:#1e293b; outline:none;
}
.dark .hist-rename-input { background:#1e293b; color:#f1f5f9; }
</style>

<div id="surat-buat-wrapper">
    <!-- Overlay untuk mobile -->
    <div id="chat-sidebar-overlay" onclick="toggleChatSidebar()"></div>
    
    <!-- Panel Kiri: History Surat -->
    <div id="chat-history-sidebar" class="bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800">
        <!-- Sidebar Header: ChatGPT-style -->
        <div class="sidebar-header flex items-center justify-between gap-2 px-3 py-3 border-b border-slate-100 dark:border-slate-800 shrink-0">
            <!-- New Chat / Compose button (Unified with text) -->
            <a href="surat_buat" class="sidebar-new-chat flex items-center justify-start gap-2 px-3 py-1.5 bg-[#8c0c4c] hover:bg-[#6b1040] text-white rounded-lg transition shadow-sm group" title="Buat Surat Baru">
                <svg class="w-4 h-4 opacity-90 group-hover:opacity-100 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span class="text-sm font-semibold">Buat Surat Baru</span>
            </a>

            <!-- Collapse/Minimize button -->
            <button onclick="collapseSidebar()" id="btn-collapse-sidebar" class="hidden md:flex p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition" title="Sembunyikan Sidebar">
                <svg id="icon-collapse" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
            
            <!-- Mobile close button -->
            <button onclick="toggleChatSidebar()" class="md:hidden p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition" title="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="sidebar-search px-3 py-2 border-b border-slate-100 dark:border-slate-800 shrink-0">
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
                <input type="text" id="sidebar-search" oninput="filterHistory(this.value)" placeholder="Cari surat..." class="w-full pl-7 pr-3 py-1.5 text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 placeholder-slate-400 rounded-lg border border-transparent focus:border-[#8c0c4c] focus:outline-none transition">
            </div>
        </div>

        <div style="flex:1; overflow-y:auto; padding: 8px 12px 16px;" class="sidebar-content custom-scrollbar">
            <?php foreach ($historyGrouped as $groupName => $items): ?>
                <?php if (count($items) > 0): ?>
                    <div class="hist-group" style="margin-bottom: 16px;">
                        <h4 style="font-size:10px; font-weight:700; color:var(--color-text-subtle); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px; padding:0 6px;"><?= $groupName ?></h4>
                        <div>
                            <?php foreach ($items as $h): ?>
                                <?php $isActive = ($chatId === (int)$h['id']); ?>
                                <div style="display:flex; align-items:center; gap:4px; width:100%;" class="hist-item-row" data-title="<?= e($h['perihal'] ?: $h['jenis_surat']) ?>" data-surat-id="<?= $h['id'] ?>">
                                    <div id="hist-label-<?= $h['id'] ?>" style="flex:1; display:block; padding:8px 10px; border-radius:8px; font-size:13px; text-decoration:none; overflow:hidden; transition:background .15s; cursor:pointer; <?= $isActive ? 'background:rgba(140,12,76,0.1); border:1px solid rgba(140,12,76,0.3); color:#fff;' : 'color:var(--color-td-text); border:1px solid transparent;' ?>"
                                         onclick="navigateHistory(<?= $h['id'] ?>, this, event)"
                                         ondblclick="startRename(<?= $h['id'] ?>, this)"
                                         title="Double-click untuk ganti nama"
                                         onmouseover="if(<?= $isActive ? 'false' : 'true' ?>)this.style.background='rgba(30,41,59,0.8)'"
                                         onmouseout="if(<?= $isActive ? 'false' : 'true' ?>)this.style.background='transparent'">
                                        <div style="display:flex; align-items:center; gap:8px;">
                                            <span style="color:<?= $isActive ? '#8c0c4c' : '#64748b' ?>; flex-shrink:0;">📄</span>
                                            <span id="hist-title-<?= $h['id'] ?>" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1;"><?= e($h['perihal'] ?: $h['jenis_surat']) ?></span>
                                            <?php if ($isActive): ?>
                                                <span style="width:8px; height:8px; border-radius:50%; background:#8c0c4c; flex-shrink:0;"></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div style="position:relative; flex-shrink:0;">
                                        <button type="button" onclick="toggleHistoryMenu(<?= $h['id'] ?>, event)" style="padding:6px; background:none; border:none; cursor:pointer; color:var(--color-text-subtle); border-radius:6px; font-size:16px; font-weight:bold; display:flex; align-items:center; justify-content:center; width:24px; height:24px;" title="Menu History">
                                            ⋮
                                        </button>
                                        <div id="hist-menu-<?= $h['id'] ?>" class="hist-dropdown bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-1 z-50 min-w-[110px] shadow-lg" style="display:none; position:absolute; right:0; top:100%;">
                                            <button type="button" onclick="togglePin(<?= $h['id'] ?>)" class="w-full text-left px-3 py-1.5 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 rounded text-xs flex gap-1.5 items-center">
                                                <?= !empty($h['is_pinned']) ? '📍 Lepas Pin' : '📌 Pin Surat' ?>
                                            </button>
                                            <button type="button" onclick="deleteHistory(<?= $h['id'] ?>)" class="w-full text-left px-3 py-1.5 text-red-600 dark:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded text-xs flex gap-1.5 items-center">
                                                🗑️ Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <!-- No search results -->
            <div id="search-no-result" style="display:none;" class="text-center py-8 text-slate-400 dark:text-slate-500">
                <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35"/></svg>
                <p class="text-xs">Tidak ada surat ditemukan</p>
            </div>
        </div>

    </div>

    <!-- Panel Kanan: Area AI -->
    <div id="surat-buat-right">

<form method="POST" id="form-surat" style="width:100%; height:100%; display:flex; flex-direction:column; overflow:hidden;">
    <!-- SEMUA HIDDEN INPUTS -->
    <input type="hidden" name="prodi_id" id="sel-prodi" value="<?= $allProdi[0]['id'] ?? '' ?>">
    <input type="hidden" name="jenis_surat" id="sel-jenis" value="Lainnya">
    <input type="hidden" name="tanggal" id="inp-tanggal" value="<?=date('Y-m-d')?>">
    <!-- Override UI di bawah -->
    <input type="hidden" name="lampiran" value="-">
    <input type="hidden" name="status" value="Draf">
    <input type="hidden" name="perihal" id="inp-perihal" value="Surat AI">
    <input type="hidden" name="jenis_penerima" id="sel-jenis-penerima" value="custom">
    <input type="hidden" name="nama_penerima" id="hidden-nama" value="Yth. Bapak/Ibu">
    <input type="hidden" name="nim_nidn" id="inp-nim" value="">
    <input type="hidden" name="isi_surat" id="editor-surat" value="">
    <input type="hidden" name="prompt_awal" id="inp-prompt-awal" value="">
    <input type="hidden" name="ai_reply" id="inp-ai-reply" value="">

    <!-- Tampilan AI Hero / Chat Revision -->
    <?php if ($chatData): ?>
        <div style="display:flex; flex-direction:column; height:100%; width:100%; padding:16px; box-sizing:border-box;">
            <!-- Header Chat -->
            <div class="flex justify-between items-center mb-4 bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shrink-0">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleChatSidebar()" class="md:hidden p-2 bg-slate-700 rounded-lg text-slate-800 dark:text-white hover:bg-slate-600 transition" title="Buka History">
                        ☰
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white"><?= e($chatData['perihal'] ?: 'Surat AI') ?></h2>
                        <p class="text-xs text-slate-400">Dibuat pada: <?= formatTanggal($chatData['tanggal']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <?php
                    $driveUrl  = $chatData['drive_url'] ?? '';
                    $driveId   = $chatData['drive_file_id'] ?? '';
                    ?>
                    <?php if ($driveUrl): ?>
                    <a href="<?= e($driveUrl) ?>" target="_blank"
                       class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M4.433 22l4-6.928H22l-4 6.928H4.433zM2 17.072L6 10.144 8 13.608 4 20.536 2 17.072zM8.433 3l4 6.928H2l4-6.928H8.433z"/></svg>
                        ✅ Buka di Drive
                    </a>
                    <?php else: ?>
                    <button type="button" id="btn-drive" onclick="uploadToDrive(<?= $chatData['id'] ?>)"
                            class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M4.433 22l4-6.928H22l-4 6.928H4.433zM2 17.072L6 10.144 8 13.608 4 20.536 2 17.072zM8.433 3l4 6.928H2l4-6.928H8.433z"/></svg>
                        <span id="btn-drive-text">☁️ Simpan ke Drive</span>
                    </button>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/api/cetak_surat?id=<?= $chatData['id'] ?>&mode=view&src=buat" target="_blank"
                       class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-bold rounded-xl transition flex items-center gap-2">
                        🖨️ Finalisasi & Cetak
                    </a>
                </div>
            </div>

            <!-- Preview Surat / Chat History -->
            <div class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 rounded-2xl p-5 mb-3 border border-slate-200 dark:border-slate-700 flex flex-col gap-6 custom-scrollbar" id="chat-preview-container">
                <?php if (empty($chatHistory)): ?>
                    <!-- Fallback kalau chat history kosong tapi dokumen ada -->
                    <div class="flex gap-4 items-start w-full">
                        <div class="w-8 h-8 rounded-full bg-[#8c0c4c] flex items-center justify-center flex-shrink-0">🤖</div>
                        <div class="flex-1 overflow-hidden bg-white rounded-xl p-3 md:p-5 shadow-sm border border-slate-300 text-black document-preview cursor-pointer hover:shadow-lg hover:ring-2 hover:ring-[#8c0c4c] transition-all group relative max-w-[600px]" onclick="window.open('<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $chatData['id'] ?>&mode=view&src=buat', '_blank')" style="font-family: 'Times New Roman', serif; font-size: 9pt; line-height: 1.4;">
                            <div class="absolute inset-0 bg-[#b8277a]/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none z-10">
                                <span class="bg-[#8c0c4c] text-white px-4 py-2 rounded-full font-bold shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform">🖨️ Klik untuk Cetak</span>
                            </div>
                            <div id="chat-preview-content"><?= $chatData['isi_surat'] ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($chatHistory as $msg): ?>
                        <?php if ($msg['role'] === 'user'): ?>
                            <?php
                            static $histIdx = 0;
                            $thisIdx = $histIdx;
                            $histIdx++;
                            ?>
                            <div class="flex gap-4 items-start justify-end w-full group/msg" data-bubble-idx="<?= $thisIdx ?>">
                                <button type="button"
                                    onclick="openUndoModal(<?= $thisIdx ?>, this)"
                                    data-hist-index="<?= $thisIdx ?>"
                                    data-prompt="<?= e($msg['content']) ?>"
                                    class="opacity-0 group-hover/msg:opacity-100 transition mt-2 p-1.5 bg-white dark:bg-slate-800 rounded-full text-amber-500 hover:text-amber-600 dark:hover:text-amber-400 border border-slate-200 dark:border-slate-700 shadow-sm"
                                    title="Undo — Batalkan perubahan dari prompt ini">
                                    ↩️
                                </button>
                                <div class="bg-[#8c0c4c] text-white rounded-2xl rounded-tr-sm px-5 py-3 shadow-sm border border-[#8c0c4c] max-w-[80%] text-sm">
                                    <?= nl2br(e($msg['content'])) ?>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center flex-shrink-0 text-slate-800 dark:text-white">👤</div>
                            </div>

                        <?php
                             static $isLastAssistant = false;
                             $isLastAssistant = ($loop_index === count($chatHistory) - 1);
                             $msgTs = !empty($msg['created_at']) ? date('H:i', strtotime($msg['created_at'])) : '';
                             $isLast = ($msg === end($chatHistory));
                        ?>
                        <?php else: ?>
                            <div class="flex gap-4 items-start w-full">
                                <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center flex-shrink-0 text-white shadow-lg mt-1">✨</div>
                                <div class="flex flex-col gap-3 max-w-[95%] md:max-w-[800px]">
                                    <?php if (!empty($msg['ai_reply'])): ?>
                                        <div class="bg-white dark:bg-slate-800 text-slate-800 dark:text-white rounded-2xl rounded-tl-sm px-5 py-3 shadow-md border border-slate-200 dark:border-slate-700 w-fit max-w-full text-sm leading-relaxed">
                                            <?= nl2br(e($msg['ai_reply'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="bg-white rounded-xl p-3 md:p-5 shadow-md border border-slate-300 text-black document-preview cursor-pointer hover:shadow-lg hover:ring-2 hover:ring-[#8c0c4c] transition-all group relative overflow-hidden max-w-[600px]" onclick="window.open('<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $chatData['id'] ?>&chat_id=<?= $msg['id'] ?>&mode=view&src=buat', '_blank')" style="font-family: 'Times New Roman', serif; font-size: 9pt; line-height: 1.4;">
                                        <div class="absolute inset-0 bg-[#b8277a]/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none z-10">
                                            <span class="bg-[#8c0c4c] text-white px-4 py-2 rounded-full font-bold shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform">🖨️ Klik untuk Cetak Versi Ini</span>
                                        </div>
                                        <?= $msg['content'] ?>
                                    </div>
                                    <div class="bubble-actions">
                                        <a href="<?= BASE_URL ?>/api/cetak_surat?id=<?= $chatData['id'] ?>&chat_id=<?= $msg['id'] ?>&mode=view&src=buat" target="_blank" class="bubble-action-btn">🖨️ Cetak Versi Ini</a>
                                        <button type="button" class="bubble-action-btn" onclick="copyBubbleText(this)" data-html="<?= htmlspecialchars($msg['content'], ENT_QUOTES) ?>">📋 Salin Teks</button>
                                        <?php if ($msg === end($chatHistory)): ?>
                                        <button type="button" class="bubble-action-btn" onclick="regenerateLast()">🔄 Coba Lagi</button>
                                        <?php endif; ?>
                                        <?php if ($msgTs): ?><span class="bubble-ts"><?= $msgTs ?></span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Loading Overlay (di bawah) -->
                <div id="rev-status" class="hidden flex gap-4 items-start w-full mt-2">
                    <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center flex-shrink-0 text-white">✨</div>
                    <div class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-300 rounded-2xl rounded-tl-sm px-5 py-4 shadow-sm border border-slate-200 dark:border-slate-700 max-w-[80%] text-sm flex items-center gap-3">
                        <div class="typing-dots"><span></span><span></span><span></span></div>
                        <span>AI sedang merangkai revisi...</span>
                    </div>
                </div>
            </div>

            <!-- Input Prompt Bawah -->
            <div class="bg-white dark:bg-slate-800 p-3 rounded-2xl border border-slate-200 dark:border-slate-700 flex gap-3 items-end shrink-0">
                <div class="flex-1 flex flex-col gap-1">
                    <textarea id="ai-rev-prompt"
                        class="w-full bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white placeholder-gray-500 border border-slate-200 dark:border-slate-700 rounded-xl p-3 text-sm focus:outline-none focus:border-[#8c0c4c] resize-none overflow-hidden"
                        rows="1"
                        style="max-height:160px;"
                        placeholder="Ketik instruksi revisi... (Enter untuk kirim, Shift+Enter untuk baris baru)"></textarea>
                    <p class="text-xs text-slate-400 pl-1">Enter = Kirim &nbsp;·&nbsp; Shift+Enter = Baris Baru</p>
                </div>
                <button type="button" onclick="revisiSuratAI(<?= $chatData['id'] ?>)" id="btn-rev" class="h-11 px-5 rounded-xl font-bold text-white bg-[#8c0c4c] hover:bg-[#b8277a] transition flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
            <p id="rev-error" class="text-red-400 text-sm mt-2 hidden text-center"></p>
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; align-items:center; justify-content:flex-start; width:100%; padding-top:16px; padding-bottom:24px; box-sizing:border-box; overflow-y:auto;">
            <div class="w-full max-w-5xl p-8 rounded-3xl shadow-2xl relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="absolute -top-32 -left-32 w-64 h-64 bg-[#b8277a] rounded-full mix-blend-screen filter blur-3xl opacity-20"></div>
                <div class="absolute -bottom-32 -right-32 w-64 h-64 bg-purple-500 rounded-full mix-blend-screen filter blur-3xl opacity-20"></div>

                <div class="relative z-10 text-center mb-6">
                    <button type="button" onclick="toggleChatSidebar()" class="md:hidden absolute -top-4 -left-4 p-2 bg-slate-800 rounded-lg text-slate-400 hover:text-slate-800 dark:text-white shadow-lg border border-slate-700 transition">
                        ☰ Histori
                    </button>
                    <div class="text-4xl mb-4">✨</div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white mb-2" style="font-family: 'Outfit', sans-serif;">Buat Surat Menggunakan AI</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">Ceritakan surat apa yang ingin Anda buat, sistem cerdas kami akan menyusun draf resminya secara instan.</p>
                </div>

                <div class="relative z-10">

                    <textarea id="ai-prompt"
                        class="w-full bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white placeholder-gray-500 border border-slate-300 dark:border-slate-700 rounded-2xl p-4 text-base focus:outline-none focus:border-[#8c0c4c] focus:ring-4 focus:ring-[#8c0c4c]/30 transition-all resize-none shadow-inner overflow-hidden"
                        rows="4"
                        style="min-height:100px; max-height:220px;"
                        placeholder="Atau ketik manual... (Contoh: Buatkan surat undangan rapat koordinasi prodi Magister Manajemen besok pagi jam 09.00)"></textarea>
                    <p class="text-xs text-slate-400 mt-1 text-right">Ctrl+Enter = Kirim &nbsp;·&nbsp; Enter = Baris Baru</p>

                    <!-- Pengaturan Override Nomor -->
                    <div class="relative z-10 text-left bg-slate-50/50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 mt-4 mb-4">
                        <label class="flex items-center gap-2 cursor-pointer mb-1">
                            <input type="checkbox" id="chk-override" class="w-4 h-4 text-[#8c0c4c] bg-gray-100 border-gray-300 rounded focus:ring-[#8c0c4c]" onchange="document.getElementById('override-container').style.display = this.checked ? 'flex' : 'none';">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Gunakan Override Nomor Surat (Opsional)</span>
                        </label>
                        <div id="override-container" style="display:none;" class="flex-col gap-3 mt-3">
                            <input type="text" name="nomor_override" id="inp-nomor" class="w-full bg-white dark:bg-slate-900 text-slate-800 dark:text-white placeholder-gray-400 border border-slate-300 dark:border-slate-600 rounded-lg p-2.5 text-sm focus:outline-none focus:border-[#8c0c4c]" placeholder="Contoh: 003/MIF/NPU/VIII/2026">
                            <label class="flex items-center gap-2 cursor-pointer bg-red-50 dark:bg-red-900/20 p-2 rounded-lg border border-red-100 dark:border-red-800">
                                <input type="checkbox" name="force_nomor" id="force-nomor" value="1" class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-600">
                                <span class="text-xs font-semibold text-red-700 dark:text-red-400">Paksa gunakan nomor ini (Abaikan validasi duplikat)</span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="button" onclick="generateSuratAI()" id="btn-ai" class="mt-4 w-full py-3 rounded-xl font-bold text-base flex items-center justify-center gap-2 transition-all hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-[#8c0c4c]/25 cursor-pointer" style="background: linear-gradient(135deg, #6b1040, #8b5cf6); color: #ffffff;">
                        <span id="btn-ai-text">🪄 Buatkan Surat Sekarang</span>
                    </button>

                    <div id="ai-status" class="hidden mt-5 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <div class="typing-dots"><span></span><span></span><span></span></div>
                            <p class="text-[#f06ea4] font-medium text-sm">AI sedang berpikir dan merangkai struktur surat...</p>
                        </div>
                    </div>
                    
                    <p id="ai-error" class="text-red-400 text-center mt-4 hidden bg-red-500/10 p-3 rounded-xl border border-red-500/20 text-sm"></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

<!-- ===== UNDO MODAL ===== -->
<div id="undo-modal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.55); backdrop-filter:blur(4px); align-items:center; justify-content:center;" onclick="closeUndoModal(event)">
    <div style="background:#1e293b; border:1px solid #334155; border-radius:20px; padding:28px 32px; max-width:420px; width:90%; box-shadow:0 25px 60px rgba(0,0,0,.5); animation: undoModalIn .25s ease;" onclick="event.stopPropagation()">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="font-size:2.5rem; margin-bottom:10px;">↩️</div>
            <h3 style="font-size:1.1rem; font-weight:700; color:#f1f5f9; margin:0 0 8px;">Batalkan Perubahan?</h3>
            <p style="font-size:0.85rem; color:#94a3b8; line-height:1.5; margin:0;">
                Prompt dan semua revisi <strong style="color:#fbbf24;">setelah</strong> ini akan dihapus permanen.<br>
                Surat akan dikembalikan ke versi sebelumnya.
            </p>
            <div id="undo-prompt-preview" style="margin-top:14px; background:#0f172a; border:1px solid #334155; border-radius:10px; padding:10px 14px; font-size:0.8rem; color:#cbd5e1; text-align:left; max-height:80px; overflow:hidden;"></div>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="closeUndoModal()" style="flex:1; padding:10px; border-radius:10px; border:1px solid #334155; background:transparent; color:#94a3b8; font-size:0.9rem; font-weight:600; cursor:pointer;">
                Batal
            </button>
            <button id="undo-confirm-btn" onclick="confirmUndo()" style="flex:1; padding:10px; border-radius:10px; border:none; background:linear-gradient(135deg,#b45309,#f59e0b); color:#fff; font-size:0.9rem; font-weight:700; cursor:pointer;">
                ↩️ Ya, Undo
            </button>
        </div>
    </div>
</div>
<style>
@keyframes undoModalIn {
    from { opacity:0; transform:scale(.92) translateY(16px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
</style>


<script>
    var _aiBaseUrl = window._sb.baseUrl;
    

    function toggleChatSidebar() {
        var sidebar = document.getElementById('chat-history-sidebar');
        var overlay = document.getElementById('chat-sidebar-overlay');
        sidebar.classList.toggle('sidebar-open');
        overlay.classList.toggle('visible');
    }

    /* ---- Collapse / Expand Sidebar ---- */
    var _sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === '1';
    function applySidebarCollapse() {
        var sidebar = document.getElementById('chat-history-sidebar');
        var icon    = document.getElementById('icon-collapse');
        if (_sidebarCollapsed) {
            sidebar.classList.add('sidebar-collapsed');
            // Flip icon to face right (expand direction)
            if (icon) icon.style.transform = 'scaleX(-1)';
        } else {
            sidebar.classList.remove('sidebar-collapsed');
            if (icon) icon.style.transform = 'scaleX(1)';
        }
    }
    function collapseSidebar() {
        _sidebarCollapsed = !_sidebarCollapsed;
        localStorage.setItem('sidebarCollapsed', _sidebarCollapsed ? '1' : '0');
        applySidebarCollapse();
    }

    /* ---- Live Search / Filter History ---- */
    function filterHistory(query) {
        var q = query.toLowerCase().trim();
        var groups = document.querySelectorAll('.hist-group');
        groups.forEach(function(group) {
            var items = group.querySelectorAll('.hist-item-row');
            var anyVisible = false;
            items.forEach(function(row) {
                var text = (row.dataset.title || '').toLowerCase();
                if (!q || text.includes(q)) {
                    row.classList.remove('search-hidden');
                    anyVisible = true;
                } else {
                    row.classList.add('search-hidden');
                }
            });
            group.classList.toggle('search-group-hidden', !anyVisible);
        });
        // Show "no results" if all hidden
        var noResult = document.getElementById('search-no-result');
        var allHidden = document.querySelectorAll('.hist-group:not(.search-group-hidden)').length === 0;
        if (noResult) noResult.style.display = (q && allHidden) ? 'block' : 'none';
    }

    /* Apply on load */
    document.addEventListener('DOMContentLoaded', function() {
        applySidebarCollapse();
    });


    function togglePin(id) {
        fetch(_aiBaseUrl + '/api/toggle_pin_surat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                window.location.reload();
            } else {
                alert(res.error || 'Gagal menyematkan surat');
            }
        })
        .catch(e => alert('Terjadi kesalahan jaringan'));
    }

    function editPrompt(btn) {
        // Legacy compatibility — buka modal undo
        var idx = parseInt(btn.getAttribute('data-hist-index') || '-1', 10);
        if (idx >= 0) { openUndoModal(idx, btn); return; }
        var promptText = btn.getAttribute('data-prompt');
        var input = document.getElementById('ai-rev-prompt') || document.getElementById('ai-prompt');
        if (input) { input.value = promptText; input.focus(); }
    }

    /* ============================================================
       UNDO MODAL — ChatGPT-style revert
    ============================================================ */
    var _undoPendingIdx    = -1;
    var _undoPendingPrompt = '';

    function openUndoModal(histIdx, btn) {
        _undoPendingIdx    = histIdx;
        _undoPendingPrompt = btn ? (btn.getAttribute('data-prompt') || '') : '';
        var preview = document.getElementById('undo-prompt-preview');
        if (preview) preview.textContent = '"' + _undoPendingPrompt + '"';
        var modal = document.getElementById('undo-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeUndoModal(e) {
        // Jika dipanggil dari onclick overlay, hanya tutup jika klik tepat di overlay
        if (e && e.currentTarget && e.target !== e.currentTarget) return;
        var modal = document.getElementById('undo-modal');
        if (modal) { modal.style.display = 'none'; document.body.style.overflow = ''; }
        _undoPendingIdx = -1;
    }

    function confirmUndo() {
        if (_undoPendingIdx < 0) return;
        var idx = _undoPendingIdx;
        var confirmBtn = document.getElementById('undo-confirm-btn');
        if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.textContent = '⏳ Memproses...'; }

        // Tutup modal
        var modal = document.getElementById('undo-modal');
        if (modal) { modal.style.display = 'none'; document.body.style.overflow = ''; }

        fetch(_baseUrl + '/api/undo_chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ surat_id: _suratId, from_hist_index: idx })
        })
        .then(r => r.json())
        .then(d => {
            if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = '↩️ Ya, Undo'; }
            if (d.ok) {
                // 1. Potong _chatHistory
                _chatHistory = _chatHistory.slice(0, idx);
                // 2. Revert _latestHtml
                _latestHtml = d.prev_html || '';
                // 3. Hapus DOM bubbles dengan data-bubble-idx >= idx
                var container = document.getElementById('chat-preview-container');
                if (container) {
                    // Hapus semua elemen dengan data-bubble-idx >= idx
                    container.querySelectorAll('[data-bubble-idx]').forEach(function(el) {
                        if (parseInt(el.getAttribute('data-bubble-idx'), 10) >= idx) el.remove();
                    });
                    // Hapus semua dynamic bubbles (bubble-animate tanpa data-bubble-idx) yang ditambah setelah page load
                    container.querySelectorAll('.bubble-animate:not([data-bubble-idx])').forEach(function(el) {
                        el.remove();
                    });
                    // Flash efek
                    container.style.transition = 'opacity .25s';
                    container.style.opacity = '0.4';
                    setTimeout(function() { container.style.opacity = '1'; }, 350);
                }
                showUndoToast();
                _undoPendingIdx = -1;
            } else {
                alert('Gagal undo: ' + (d.error || 'Unknown error'));
            }
        })
        .catch(function(e) {
            if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = '↩️ Ya, Undo'; }
            alert('Error koneksi: ' + e.message);
        });
    }

    function showUndoToast() {
        var t = document.createElement('div');
        t.style.cssText = 'position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(10px);background:#1e293b;border:1px solid #f59e0b;color:#fbbf24;padding:10px 22px;border-radius:40px;font-size:0.85rem;font-weight:600;z-index:9999;box-shadow:0 8px 32px rgba(0,0,0,.4);opacity:0;transition:opacity .3s, transform .3s;';
        t.textContent = '↩️ Revisi berhasil di-undo!';
        document.body.appendChild(t);
        requestAnimationFrame(function() {
            t.style.opacity = '1';
            t.style.transform = 'translateX(-50%) translateY(0)';
        });
        setTimeout(function() {
            t.style.opacity = '0';
            t.style.transform = 'translateX(-50%) translateY(10px)';
            setTimeout(function() { t.remove(); }, 350);
        }, 2500);
    }



    function generateSuratAI() {
        var prompt = document.getElementById('ai-prompt').value.trim();
        var errEl  = document.getElementById('ai-error');
        errEl.classList.add('hidden');
        if (!prompt) {
            errEl.textContent = '⚠️ Harap ketikkan penjelasan singkat tentang surat yang ingin dibuat.';
            errEl.classList.remove('hidden');
            return;
        }
        var btn    = document.getElementById('btn-ai');
        var btnTxt = document.getElementById('btn-ai-text');
        var status = document.getElementById('ai-status');
        btn.disabled = true;
        btnTxt.textContent = '⏳ Menghubungi Server AI...';
        status.classList.remove('hidden');

        fetch(_aiBaseUrl + '/api/generate_surat_ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt: prompt })
        })
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(d) {
            if (d.ok) {
                document.getElementById('editor-surat').value = d.html;
                if (d.perihal) document.getElementById('inp-perihal').value = d.perihal;
                if (d.penerima) document.getElementById('hidden-nama').value = d.penerima;
                if (d.jenis_surat) document.getElementById('sel-jenis').value = d.jenis_surat;

                var selProdiValue = document.getElementById('sel-prodi').value;
                if (d.prodi && d.prodi.trim() !== '') {
                    var searchStr = d.prodi.toLowerCase();
                    var prodis = window._sb.prodis || [];
                    for (var i = 0; i < prodis.length; i++) {
                        var optText = prodis[i].nama.toLowerCase();
                        if (optText.indexOf(searchStr) !== -1 || searchStr.indexOf(optText) !== -1 ||
                            (searchStr.includes('manajemen') && optText.includes('manajemen')) ||
                            (searchStr.includes('hukum') && optText.includes('hukum')) ||
                            (searchStr.includes('informatika') && optText.includes('informatika')) ||
                            (searchStr.includes('pedagogi') && optText.includes('pedagogi')) ||
                            (searchStr.includes('komputer') && optText.includes('komputer'))
                        ) {
                            selProdiValue = prodis[i].id;
                            break;
                        }
                    }
                }
                document.getElementById('sel-prodi').value = selProdiValue;
                document.getElementById('ai-prompt').value = '';
                document.getElementById('inp-prompt-awal').value = prompt;
                document.getElementById('inp-ai-reply').value = d.ai_reply || 'Draf surat awal berhasil dibuat.';
                
                btnTxt.textContent = '✅ Surat Berhasil Dibuat! Mengarahkan...';
                
                setTimeout(function() {
                    var form = document.getElementById('form-surat');
                    var btnSubmit = document.createElement('input');
                    btnSubmit.type = 'hidden';
                    btnSubmit.name = 'submit_action';
                    btnSubmit.value = 'preview'; 
                    form.appendChild(btnSubmit);
                    form.submit();
                }, 800);
                
            } else {
                errEl.textContent = '❌ Gagal: ' + (d.error || 'Terjadi kesalahan.');
                errEl.classList.remove('hidden');
                btn.disabled = false;
                btnTxt.textContent = '🪄 Coba Generate Lagi';
                status.classList.add('hidden');
            }
        })
        .catch(function(e) {
            errEl.textContent = '❌ Koneksi terputus: ' + e.message;
            errEl.classList.remove('hidden');
            btn.disabled = false;
            btnTxt.textContent = '🪄 Coba Generate Lagi';
            status.classList.add('hidden');
        });
    }
    
    
    var _latestHtml = <?= json_encode($chatData ? ($chatData['isi_surat'] ?? '') : '') ?>;
    var _baseUrl    = <?= json_encode(BASE_URL) ?>;
    var _suratId    = <?= $chatData ? (int)$chatData['id'] : 0 ?>;

    // === Full conversation history (ChatGPT-style) ===
    // Di-populate dari riwayat PHP saat halaman dimuat
    var _chatHistory = [];
    <?php if (!empty($chatHistory)): ?>
    (function() {
        var phpHistory = <?= json_encode(array_map(function($h) {
            return [
                'role'    => $h['role'],
                // user bubble: content, assistant bubble: gabungkan ai_reply + html content
                'content' => $h['role'] === 'user'
                    ? $h['content']
                    : ((!empty($h['ai_reply']) ? $h['ai_reply'] . "\n\n" : '') . ($h['content'] ?? ''))
            ];
        }, $chatHistory), JSON_UNESCAPED_UNICODE) ?>;
        _chatHistory = phpHistory;
    })();
    <?php endif; ?>


    /* -------- Helper: scroll chat ke bawah -------- */
    function scrollChat() {
        var c = document.getElementById('chat-preview-container');
        if (c) c.scrollTop = c.scrollHeight;
    }

    /* -------- Tambah gelembung USER ke chat (dengan tombol undo) -------- */
    function appendUserBubble(text) {
        var container = document.getElementById('chat-preview-container');
        var statusEl  = document.getElementById('rev-status');
        // _chatHistory sudah di-push sebelum pemanggilan ini, jadi index = length - 1
        var histIdx = _chatHistory.length - 1;
        var div = document.createElement('div');
        div.className = 'flex gap-4 items-start justify-end w-full bubble-animate group/msg';
        div.setAttribute('data-bubble-idx', histIdx);
        var escapedText = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        div.innerHTML =
            '<button type="button" onclick="openUndoModal(' + histIdx + ', this)" data-hist-index="' + histIdx + '" data-prompt="' + escapedText + '" class="opacity-0 group-hover/msg:opacity-100 transition mt-2 p-1.5 bg-white dark:bg-slate-800 rounded-full text-amber-500 hover:text-amber-600 border border-slate-200 dark:border-slate-700 shadow-sm" title="Undo — Batalkan perubahan dari prompt ini">↩️</button>' +
            '<div class="bg-[#8c0c4c] text-white rounded-2xl rounded-tr-sm px-5 py-3 shadow-sm border border-[#8c0c4c] max-w-[80%] text-sm">' +
                escapedText.replace(/\n/g,'<br>') +
            '</div>' +
            '<div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center flex-shrink-0 text-slate-800 dark:text-white">👤</div>';
        container.insertBefore(div, statusEl);
        scrollChat();
    }



    /* -------- Tambah gelembung AI kosong + jalankan typewriter -------- */
    function appendAiBubble(chatId, aiReply, htmlContent) {
        var container = document.getElementById('chat-preview-container');
        var statusEl  = document.getElementById('rev-status');
        var uid = 'ai-bubble-' + Date.now();

        var outerDiv = document.createElement('div');
        outerDiv.className = 'flex gap-4 items-start w-full bubble-animate';
        outerDiv.innerHTML =
            '<div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center flex-shrink-0 text-white shadow-lg mt-1">✨</div>' +
            '<div class="flex flex-col gap-3 max-w-[95%] md:max-w-[800px]">' +
                (aiReply ? '<div class="bg-white dark:bg-slate-800 text-slate-800 dark:text-white rounded-2xl rounded-tl-sm px-5 py-3 shadow-md border border-slate-200 dark:border-slate-700 w-fit max-w-full text-sm leading-relaxed ai-reply-box"></div>' : '') +
                '<div id="' + uid + '" class="bg-white rounded-xl p-3 md:p-5 shadow-md border border-slate-300 text-black cursor-pointer hover:shadow-lg hover:ring-2 hover:ring-[#8c0c4c] transition-all group relative overflow-hidden max-w-[600px]" onclick="window.open(\'' + _baseUrl + '/api/cetak_surat.php?id=' + _suratId + '&chat_id=' + chatId + '&mode=view&src=buat\', \'_blank\')" style="font-family:\'Times New Roman\',serif;font-size:9pt;line-height:1.4;">' +
                    '<div class="absolute inset-0 bg-[#b8277a]/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none z-10">' +
                        '<span class="bg-[#8c0c4c] text-white px-4 py-2 rounded-full font-bold shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform">🖨️ Klik untuk Cetak Versi Ini</span>' +
                    '</div>' +
                    '<div class="doc-type-target"></div>' +
                '</div>' +
                '<div class="bubble-actions">' +
                    '<a href="' + _baseUrl + '/api/cetak_surat?id=' + _suratId + '&chat_id=' + chatId + '&mode=view&src=buat" target="_blank" class="bubble-action-btn">🖨️ Cetak Versi Ini</a>' +
                    '<button type="button" class="bubble-action-btn" onclick="copyBubbleText(this)" data-html="" id="copy-btn-' + uid + '">📋 Salin Teks</button>' +
                    '<button type="button" class="bubble-action-btn" id="regen-btn-' + uid + '" onclick="regenerateLast()">🔄 Coba Lagi</button>' +
                    '<span class="bubble-ts">' + (new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})) + '</span>' +
                '</div>' +
            '</div>';

        container.insertBefore(outerDiv, statusEl);
        scrollChat();

        /* Typewriter untuk ai_reply */
        if (aiReply) {
            var replyBox = outerDiv.querySelector('.ai-reply-box');
            typewriterText(replyBox, aiReply, 18, function() {});
        }

        /* Reveal HTML untuk dokumen surat */
        var docTarget = outerDiv.querySelector('.doc-type-target');
        revealDocHTML(docTarget, htmlContent, function() {});
    }

    /* -------- Typewriter teks biasa -------- */
    function typewriterText(el, text, speed, onDone) {
        el.textContent = '';
        var i = 0;
        var cursor = document.createElement('span');
        cursor.className = 'typing-cursor';
        el.appendChild(cursor);
        var t = setInterval(function() {
            if (i >= text.length) {
                clearInterval(t);
                cursor.remove();
                if (onDone) onDone();
                return;
            }
            el.insertBefore(document.createTextNode(text[i]), cursor);
            i++;
            scrollChat();
        }, speed);
    }

    /* -------- Reveal dokumen HTML dengan efek baris per baris -------- */
    function revealDocHTML(el, html, onDone) {
        /* Tampilkan HTML langsung, lalu animasikan baris per baris dari atas ke bawah */
        el.innerHTML = html;

        /* Ambil semua elemen anak langsung (paragraf, tabel, dll) */
        var children = Array.from(el.children);
        if (children.length === 0) {
            if (onDone) onDone();
            return;
        }

        /* Sembunyikan semua anak dulu */
        children.forEach(function(c) {
            c.style.opacity = '0';
            c.style.transform = 'translateY(8px)';
            c.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
        });

        /* Tampilkan satu per satu dengan jeda */
        var delay = 80;
        children.forEach(function(c, idx) {
            setTimeout(function() {
                c.style.opacity = '1';
                c.style.transform = 'translateY(0)';
                scrollChat();
                if (idx === children.length - 1 && onDone) {
                    setTimeout(onDone, 300);
                }
            }, idx * delay);
        });
    }

    /* -------- Fungsi utama revisi (ChatGPT-style, full history) -------- */
    function revisiSuratAI(id) {
        var promptEl = document.getElementById('ai-rev-prompt');
        var prompt   = promptEl.value.trim();
        var errEl    = document.getElementById('rev-error');
        var btn      = document.getElementById('btn-rev');
        var status   = document.getElementById('rev-status');

        errEl.classList.add('hidden');
        if (!prompt) return;

        /* 1. Tambahkan prompt user ke history */
        _chatHistory.push({ role: 'user', content: prompt });

        /* 2. Tampilkan gelembung user langsung */
        appendUserBubble(prompt);
        promptEl.value = '';
        btn.disabled = true;
        promptEl.disabled = true;
        status.classList.remove('hidden');
        scrollChat();

        /* 3. Panggil AI dengan FULL chat history */
        fetch(_baseUrl + '/api/generate_surat_ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                prompt: prompt,
                chat_history: _chatHistory.slice(0, -1), // kirim history tanpa prompt terakhir (sudah di-handle server)
                previous_html: _latestHtml               // fallback
            })
        })
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                /* 4. Simpan ke DB */
                fetch(_baseUrl + '/api/update_surat_ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, prompt: prompt, isi_surat: d.html, ai_reply: d.ai_reply })
                })
                .then(r2 => r2.json())
                .then(d2 => {
                    status.classList.add('hidden');
                    if (d2.ok) {
                        /* 5. Update latest html dan tambahkan ke history */
                        _latestHtml = d.html;
                        var aiContent = (d.ai_reply ? d.ai_reply + '\n\n' : '') + d.html;
                        _chatHistory.push({ role: 'assistant', content: aiContent });

                        /* 6. Tampilkan gelembung AI dengan animasi */
                        appendAiBubble(d2.chat_id || 0, d.ai_reply || '', d.html);
                        resetRevState(btn, promptEl, null);
                    } else {
                        // Rollback history jika gagal simpan
                        _chatHistory.pop();
                        _chatHistory.pop();
                        errEl.textContent = 'Gagal menyimpan revisi: ' + (d2.error || '');
                        errEl.classList.remove('hidden');
                        resetRevState(btn, promptEl, null);
                    }
                });
            } else {
                // Rollback history jika AI error
                _chatHistory.pop();
                status.classList.add('hidden');
                errEl.textContent = '❌ Gagal merevisi: ' + (d.error || '');
                errEl.classList.remove('hidden');
                resetRevState(btn, promptEl, null);
            }
        })
        .catch(e => {
            _chatHistory.pop();
            status.classList.add('hidden');
            errEl.textContent = 'Error koneksi: ' + e.message;
            errEl.classList.remove('hidden');
            resetRevState(btn, promptEl, null);
        });
    }

    function resetRevState(btn, input, status) {
        btn.disabled = false;
        input.disabled = false;
        if (status) status.classList.add('hidden');
        input.focus();
    }

    /* ---- Auto-resize textarea (keduanya) ---- */
    function autoResizeTA(el) {
        if (!el) return;
        el.style.height = 'auto';
        var h = Math.min(el.scrollHeight, parseInt(el.style.maxHeight) || 220);
        el.style.height = h + 'px';
    }
    function autoResizeInput() { autoResizeTA(document.getElementById('ai-rev-prompt')); }

    function scrollToBottom() {
        var container = document.getElementById('chat-preview-container');
        if (container) container.scrollTop = container.scrollHeight;
    }

    /* ---- Fill prompt from suggestion card ---- */
    function fillPrompt(text) {
        var ta = document.getElementById('ai-prompt');
        if (!ta) return;
        ta.value = text;
        ta.focus();
        autoResizeTA(ta);
        ta.setSelectionRange(text.length, text.length);
    }

    /* ---- Copy bubble text to clipboard ---- */
    function copyBubbleText(btn) {
        var html = btn.getAttribute('data-html') || '';
        // Strip HTML tags to get plain text
        var tmp = document.createElement('div');
        tmp.innerHTML = html;
        var text = tmp.innerText || tmp.textContent || html;
        navigator.clipboard.writeText(text).then(function() {
            var orig = btn.textContent;
            btn.textContent = '✅ Disalin!';
            btn.style.color = '#16a34a';
            setTimeout(function() { btn.textContent = orig; btn.style.color = ''; }, 1800);
        }).catch(function() {
            // Fallback for older browsers
            var ta2 = document.createElement('textarea');
            ta2.value = text; document.body.appendChild(ta2);
            ta2.select(); document.execCommand('copy'); ta2.remove();
            btn.textContent = '✅ Disalin!';
            setTimeout(function() { btn.textContent = '📋 Salin Teks'; }, 1800);
        });
    }

    /* ---- Regenerate: kirim ulang prompt terakhir ---- */
    var _lastPrompt = '';
    function regenerateLast() {
        if (!_lastPrompt) return;
        // Undo satu turn terakhir dulu (hapus bubble AI terakhir dari DOM, kembalikan history)
        if (_chatHistory.length >= 2) {
            _chatHistory.pop(); // hapus assistant
        }
        var promptEl = document.getElementById('ai-rev-prompt');
        if (promptEl) promptEl.value = _lastPrompt;
        revisiSuratAI(<?= $chatId ?: 0 ?>);
    }

    /* ---- Rename inline sidebar ---- */
    function navigateHistory(id, el, e) {
        // Hanya navigasi jika bukan rename mode
        if (el.querySelector('.hist-rename-input')) return;
        window.location.href = 'surat_buat?id=' + id;
    }

    function startRename(id, el) {
        // Jangan double-rename
        if (el.querySelector('.hist-rename-input')) return;
        var titleEl = document.getElementById('hist-title-' + id);
        var currentText = titleEl ? titleEl.textContent.trim() : '';
        var input = document.createElement('input');
        input.type = 'text';
        input.value = currentText;
        input.className = 'hist-rename-input';
        input.style.cssText = 'flex:1;font-size:13px;padding:4px 8px;border-radius:6px;border:1px solid #8c0c4c;background:#fff;color:#1e293b;outline:none;width:100%;';
        // Replace title span with input
        var inner = el.querySelector('div');
        if (titleEl) titleEl.replaceWith(input);
        input.focus();
        input.select();
        function commitRename() {
            var newName = input.value.trim();
            if (!newName) newName = currentText;
            fetch(_baseUrl + '/api/rename_surat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, perihal: newName })
            }).then(r => r.json()).then(function(d) {
                var span = document.createElement('span');
                span.id = 'hist-title-' + id;
                span.style.cssText = 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;';
                span.textContent = d.ok ? d.perihal : currentText;
                input.replaceWith(span);
                // Update data-title for search
                var row = el.closest('.hist-item-row');
                if (row) row.dataset.title = span.textContent;
                // Update page title if this is the active surat
                var h2 = document.querySelector('#surat-buat-right h2');
                if (h2 && h2.textContent.trim() === currentText) h2.textContent = span.textContent;
            }).catch(function() {
                var span = document.createElement('span');
                span.id = 'hist-title-' + id;
                span.style.cssText = 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;';
                span.textContent = currentText;
                input.replaceWith(span);
            });
        }
        input.addEventListener('blur', commitRename);
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
            if (e.key === 'Escape') { input.value = currentText; input.blur(); }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        /* Auto-resize kedua textarea */
        var revPrompt = document.getElementById('ai-rev-prompt');
        if (revPrompt) {
            revPrompt.addEventListener('input', function() { autoResizeTA(revPrompt); });
            revPrompt.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    var id = <?php echo $chatId ?: 0; ?>;
                    if(id > 0) revisiSuratAI(id);
                }
            });
        }
        var aiPrompt = document.getElementById('ai-prompt');
        if (aiPrompt) {
            aiPrompt.addEventListener('input', function() { autoResizeTA(aiPrompt); });
            aiPrompt.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault(); generateSuratAI();
                }
            });
        }
        scrollToBottom();
    });

    /* Patch revisiSuratAI to track _lastPrompt for regenerate */
    var _origRevisi = revisiSuratAI;
    revisiSuratAI = function(id) {
        var promptEl = document.getElementById('ai-rev-prompt');
        if (promptEl && promptEl.value.trim()) _lastPrompt = promptEl.value.trim();
        _origRevisi(id);
    };
    
    // Reset padding main agar tidak ada jarak
    (function() {
        var mainEl = document.querySelector('.main-content > main');
        if (mainEl) {
            mainEl.style.padding = '0';
            mainEl.style.overflow = 'hidden';
        }
    })();

    function toggleHistoryMenu(id, e) {
        e.stopPropagation();
        var menus = document.querySelectorAll('.hist-dropdown');
        menus.forEach(function(m) {
            if (m.id !== 'hist-menu-' + id) m.style.display = 'none';
        });
        var menu = document.getElementById('hist-menu-' + id);
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    }

    document.addEventListener('click', function() {
        var menus = document.querySelectorAll('.hist-dropdown');
        menus.forEach(function(m) { m.style.display = 'none'; });
    });

    function deleteHistory(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus history surat ini?')) return;
        var fd = new FormData();
        fd.append('id', id);
        fetch('../api/delete_surat.php', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if(data.ok) {
                if (id == <?= $chatId ?>) {
                    window.location.href='surat_buat';
                } else {
                    location.reload();
                }
            } else {
                alert('Gagal menghapus: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(e => {
            alert('Terjadi kesalahan jaringan.');
            console.error(e);
        });
    }
    /* ---- Upload to Google Drive ---- */
    function uploadToDrive(id) {
        var btn  = document.getElementById('btn-drive');
        var txt  = document.getElementById('btn-drive-text');
        if (!btn) return;
        btn.disabled = true;
        txt.textContent = '⏳ Mengunggah...';
        fetch(_baseUrl + '/api/upload_to_drive.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                // Ubah tombol jadi link hijau
                btn.outerHTML = '<a href="' + d.url + '" target="_blank" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5">'
                    + '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M4.433 22l4-6.928H22l-4 6.928H4.433zM2 17.072L6 10.144 8 13.608 4 20.536 2 17.072zM8.433 3l4 6.928H2l4-6.928H8.433z"/></svg>'
                    + '\u2705 Buka di Drive</a>';
                // Toast
                var t = document.createElement('div');
                t.style.cssText='position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:#16a34a;color:#fff;padding:10px 22px;border-radius:14px;font-size:14px;font-weight:600;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.25);';
                t.textContent='✅ Surat berhasil diunggah ke Google Drive!';
                document.body.appendChild(t);
                setTimeout(function(){ t.style.opacity='0'; setTimeout(function(){ t.remove(); },400); }, 3000);
            } else {
                alert('Gagal upload ke Drive:\n' + (d.error || 'Terjadi kesalahan.'));
                btn.disabled = false;
                txt.textContent = '☁️ Simpan ke Drive';
            }
        })
        .catch(e => {
            alert('Error jaringan: ' + e.message);
            btn.disabled = false;
            txt.textContent = '☁️ Simpan ke Drive';
        });
    }
    </script>
</form>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
