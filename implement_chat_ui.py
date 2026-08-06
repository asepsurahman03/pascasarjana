import os
import re

# 1. Update surat_buat.php POST Handler
file_buat = 'pages/surat_buat.php'
with open(file_buat, 'r', encoding='utf-8') as f:
    sb_content = f.read()

# Add logic to insert into surat_chat
post_handler_target = """    $id = dbExecute(
        "INSERT INTO surat(nomor_surat,jenis_surat,prodi_id,nama_penerima,nim_nidn,perihal,lampiran,tanggal,hari,kota,isi_surat,status,jenis_penerima,created_by)VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        [$nomor,$jenis,$prodiId,$namaPenerima,$nimNidn,$perihal,$lampiran,$tanggal,$hari,$kota,$isiSurat,$status,$jenisPenerima,$_SESSION['user_id']]
    );"""

post_handler_replacement = post_handler_target + """

    $prompt_awal = trim($_POST['prompt_awal'] ?? '');
    if ($prompt_awal) {
        dbExecute("INSERT INTO surat_chat (surat_id, role, content) VALUES (?, 'user', ?)", [$id, $prompt_awal]);
    }
    if ($isiSurat) {
        dbExecute("INSERT INTO surat_chat (surat_id, role, content) VALUES (?, 'assistant', ?)", [$id, $isiSurat]);
    }"""

sb_content = sb_content.replace(post_handler_target, post_handler_replacement)

# Update form hidden inputs
sb_content = sb_content.replace(
    '<input type="hidden" name="isi_surat" id="editor-surat" value="">',
    '<input type="hidden" name="isi_surat" id="editor-surat" value="">\n    <input type="hidden" name="prompt_awal" id="inp-prompt-awal" value="">'
)

# Update JS to fill prompt_awal
sb_content = sb_content.replace(
    "document.getElementById('ai-prompt').value = '';",
    "document.getElementById('ai-prompt').value = '';\n                document.getElementById('inp-prompt-awal').value = prompt;"
)

# 2. Update Revision Mode UI to show Chat Bubbles
chat_ui_start = "<!-- Header Chat -->"
chat_ui_end = "<!-- Input Prompt Bawah -->"

# We will fetch chat history
chat_data_logic = """$chatId = (int)($_GET['id'] ?? 0);
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
    }
}"""
sb_content = re.sub(r'\$chatId = \(int\)\(\$_GET\[\'id\'\] \?\? 0\);\s*\$chatData = null;\s*if \(\$chatId\) \{.*?\n\}', chat_data_logic, sb_content, flags=re.DOTALL)

# Build the chat bubbles
new_chat_preview = """<!-- Preview Surat / Chat History -->
            <div class="flex-1 overflow-y-auto bg-slate-900 rounded-2xl p-4 md:p-6 mb-4 shadow-xl border border-slate-700 relative flex flex-col gap-6 custom-scrollbar" id="chat-preview-container">
                <?php if (empty($chatHistory)): ?>
                    <!-- Fallback kalau chat history kosong tapi dokumen ada -->
                    <div class="flex gap-4 items-start w-full">
                        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">🤖</div>
                        <div class="flex-1 overflow-hidden bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-300 text-black document-preview" style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5;">
                            <div id="chat-preview-content"><?= $chatData['isi_surat'] ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($chatHistory as $msg): ?>
                        <?php if ($msg['role'] === 'user'): ?>
                            <div class="flex gap-4 items-start justify-end w-full">
                                <div class="bg-blue-600 text-white rounded-2xl rounded-tr-sm px-5 py-3 shadow-sm border border-blue-500 max-w-[80%] text-sm">
                                    <?= nl2br(e($msg['content'])) ?>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center flex-shrink-0 text-white">👤</div>
                            </div>
                        <?php else: ?>
                            <div class="flex gap-4 items-start w-full">
                                <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center flex-shrink-0 text-white shadow-lg">✨</div>
                                <div class="flex flex-col gap-2 max-w-[95%]">
                                    <div class="bg-white rounded-2xl rounded-tl-sm p-6 md:p-8 shadow-sm border border-slate-300 text-black document-preview" style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5;">
                                        <?= $msg['content'] ?>
                                    </div>
                                    <div class="flex justify-start">
                                        <a href="<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $chatData['id'] ?>&chat_id=<?= $msg['id'] ?>&mode=view" target="_blank" class="text-xs flex items-center gap-1 text-slate-400 hover:text-blue-400 transition bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700">
                                            🖨️ Cetak Versi Ini
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Loading Overlay (di bawah) -->
                <div id="rev-status" class="hidden flex gap-4 items-start w-full mt-2">
                    <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center flex-shrink-0 text-white animate-pulse">✨</div>
                    <div class="bg-slate-800 text-slate-300 rounded-2xl rounded-tl-sm px-5 py-4 shadow-sm border border-slate-700 max-w-[80%] text-sm flex items-center gap-3">
                        <div class="inline-block animate-spin w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                        <span>AI sedang memproses revisi Anda...</span>
                    </div>
                </div>
            </div>"""

sb_content = re.sub(
    r'<!-- Preview Surat -->.*?<!-- Input Prompt Bawah -->',
    new_chat_preview + '\n\n            <!-- Input Prompt Bawah -->',
    sb_content,
    flags=re.DOTALL
)

# Fix Javascript for revision (reload page on success)
js_rev_target = """                .then(d2 => {
                    if(d2.ok) {
                        document.getElementById('chat-preview').innerHTML = d.html;
                        promptEl.value = '';
                    } else {"""
js_rev_replacement = """                .then(d2 => {
                    if(d2.ok) {
                        window.location.reload();
                    } else {"""
sb_content = sb_content.replace(js_rev_target, js_rev_replacement)

# We need the previous_html to be the LAST assistant content.
# But `revisiSuratAI()` uses `document.getElementById('chat-preview').innerHTML`.
# Since we changed `#chat-preview` structure, we need to pass the latest content from PHP, or fetch it.
# The easiest is to inject it as a hidden JS variable.
latest_html_logic = """    <script>
    var _latestHtml = <?= json_encode($chatData['isi_surat'] ?? '') ?>;
"""
sb_content = sb_content.replace('    <script>\n    function revisiSuratAI(id)', latest_html_logic + '    function revisiSuratAI(id)')

# Replace prevHtml in revisiSuratAI
sb_content = sb_content.replace(
    "var prevHtml = document.getElementById('chat-preview').innerHTML;",
    "var prevHtml = _latestHtml;"
)

with open(file_buat, 'w', encoding='utf-8') as f:
    f.write(sb_content)


# 3. Update update_surat_ajax.php
ajax_file = 'api/update_surat_ajax.php'
with open(ajax_file, 'r', encoding='utf-8') as f:
    ajax_content = f.read()

ajax_content = ajax_content.replace(
    'dbExecute("UPDATE surat SET isi_surat=? WHERE id=?", [$isi_surat, $id]);',
    """$prompt = trim($data['prompt'] ?? '');
if ($prompt) {
    dbExecute("INSERT INTO surat_chat (surat_id, role, content) VALUES (?, 'user', ?)", [$id, $prompt]);
}
if ($isi_surat) {
    dbExecute("INSERT INTO surat_chat (surat_id, role, content) VALUES (?, 'assistant', ?)", [$id, $isi_surat]);
}
dbExecute("UPDATE surat SET isi_surat=? WHERE id=?", [$isi_surat, $id]);"""
)

with open(ajax_file, 'w', encoding='utf-8') as f:
    f.write(ajax_content)


# 4. Update cetak_surat.php
cetak_file = 'api/cetak_surat.php'
with open(cetak_file, 'r', encoding='utf-8') as f:
    cetak_content = f.read()

cetak_logic = """if (!$surat) {
    echo "Surat tidak ditemukan.";
    exit;
}

// === OVERRIDE CHAT ID ===
$chat_id = (int)($_GET['chat_id'] ?? 0);
if ($chat_id > 0) {
    $chatData = dbQueryOne("SELECT content FROM surat_chat WHERE id=? AND surat_id=? AND role='assistant'", [$chat_id, $id]);
    if ($chatData) {
        $surat['isi_surat'] = $chatData['content'];
    }
}
// ========================

// Update nomor otomatis jika mode draft
"""
cetak_content = cetak_content.replace(
    "if (!$surat) {\n    echo \"Surat tidak ditemukan.\";\n    exit;\n}\n\n// Update nomor otomatis jika mode draft",
    cetak_logic
)

with open(cetak_file, 'w', encoding='utf-8') as f:
    f.write(cetak_content)

print("Chat UI architecture implemented.")
