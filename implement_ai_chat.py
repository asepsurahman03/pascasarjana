import os

# 1. CREATE update_surat_ajax.php
ajax_content = """<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$isi_surat = $data['isi_surat'] ?? '';

if (!$id) {
    echo json_encode(['error' => 'ID Surat tidak valid']);
    exit;
}

$user = getCurrentUser();
$surat = dbQueryOne("SELECT * FROM surat WHERE id=?", [$id]);
if (!$surat) {
    echo json_encode(['error' => 'Surat tidak ditemukan']);
    exit;
}

if (!isSuperAdmin() && $surat['prodi_id'] != $user['prodi_id']) {
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

dbExecute("UPDATE surat SET isi_surat=? WHERE id=?", [$isi_surat, $id]);
echo json_encode(['ok' => true]);
"""
with open('api/update_surat_ajax.php', 'w', encoding='utf-8') as f:
    f.write(ajax_content)

# 2. UPDATE generate_surat_ai.php
with open('api/generate_surat_ai.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the userMsg assignment
old_user_msg = '$userMsg = "Elaborasikan dan kembangkan instruksi berikut menjadi surat formal yang sangat detail, kaya makna, dan berwibawa:\\n\\"$prompt\\"";'
new_user_msg = """$previous_html = trim($data['previous_html'] ?? '');
if (!empty($previous_html)) {
    $userMsg = "Ini adalah draf surat saat ini (HTML):\\n\\n" . $previous_html . "\\n\\nTOLONG REVISI DRAF DI ATAS BERDASARKAN INSTRUKSI BERIKUT:\\n\\\"" . $prompt . "\\\"\\n\\nKeluarkan KESELURUHAN draf yang sudah direvisi secara utuh (bukan cuma bagian yang diubah). Tetap pertahankan format HTML dan styling tabel layout seperti aslinya.";
} else {
    $userMsg = "Elaborasikan dan kembangkan instruksi berikut menjadi surat formal yang sangat detail, kaya makna, dan berwibawa:\\n\\\"$prompt\\\"";
}"""
content = content.replace(old_user_msg, new_user_msg)

with open('api/generate_surat_ai.php', 'w', encoding='utf-8') as f:
    f.write(content)


# 3. UPDATE surat_buat.php
with open('pages/surat_buat.php', 'r', encoding='utf-8') as f:
    sb_content = f.read()

# Fix history links
sb_content = sb_content.replace(
    '<a href="<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $h[\'id\'] ?>&mode=view&src=buat" target="_self"',
    '<a href="surat_buat.php?id=<?= $h[\'id\'] ?>"'
)

# Insert PHP logic for $chatData
logic_str = """$user = getCurrentUser();
$historyLimit = 30;"""

new_logic_str = """$user = getCurrentUser();

$chatId = (int)($_GET['id'] ?? 0);
$chatData = null;
if ($chatId) {
    if (isSuperAdmin()) {
        $chatData = dbQueryOne("SELECT * FROM surat WHERE id=?", [$chatId]);
    } else {
        $chatData = dbQueryOne("SELECT * FROM surat WHERE id=? AND prodi_id=?", [$chatId, $user['prodi_id']]);
    }
}

$historyLimit = 30;"""
sb_content = sb_content.replace(logic_str, new_logic_str)

# Redirect after generation: from cetak_surat to surat_buat?id=
sb_content = sb_content.replace(
    "header(\"Location: \".BASE_URL.\"/api/cetak_surat.php?id=$id&mode=view&src=buat\");",
    "header(\"Location: surat_buat.php?id=$id\");"
)

# Split Right Panel UI
start_marker = "<!-- Tampilan AI Hero -->"
end_marker = "    <script>"

chat_ui = """<!-- Tampilan AI Hero / Chat Revision -->
    <?php if ($chatData): ?>
        <div class="flex flex-col h-full w-full max-w-4xl pt-4">
            <!-- Header Chat -->
            <div class="flex justify-between items-center mb-4 bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-700">
                <div>
                    <h2 class="text-lg font-bold text-white"><?= e($chatData['perihal'] ?: 'Surat AI') ?></h2>
                    <p class="text-xs text-slate-400">Dibuat pada: <?= formatTanggal($chatData['tanggal']) ?></p>
                </div>
                <a href="<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $chatData['id'] ?>&mode=view&src=buat" target="_blank" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-bold rounded-xl transition flex items-center gap-2">
                    🖨️ Finalisasi & Cetak
                </a>
            </div>

            <!-- Preview Surat -->
            <div class="flex-1 overflow-y-auto bg-white rounded-2xl p-8 mb-4 shadow-xl border border-slate-300 relative text-black" style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5;" id="chat-preview-container">
                <div id="chat-preview">
                    <?= $chatData['isi_surat'] ?>
                </div>
                <!-- Loading Overlay -->
                <div id="rev-status" class="hidden absolute inset-0 bg-white/80 backdrop-blur-sm flex flex-col items-center justify-center z-10 rounded-2xl">
                    <div class="inline-block animate-spin w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full mb-3"></div>
                    <p class="text-blue-600 font-bold animate-pulse">AI sedang merevisi surat Anda...</p>
                </div>
            </div>

            <!-- Input Prompt Bawah -->
            <div class="bg-slate-800 p-3 rounded-2xl shadow-lg border border-slate-600 flex gap-3 items-end">
                <textarea id="ai-rev-prompt" class="flex-1 bg-slate-900 text-white placeholder-gray-500 border border-slate-700 rounded-xl p-3 text-sm focus:outline-none focus:border-blue-500 resize-none max-h-32" rows="2" placeholder="Ketik instruksi revisi... (Contoh: Ganti paragraf kedua jadi lebih ramah, atau ubah hari pelaksanaannya)"></textarea>
                <button type="button" onclick="revisiSuratAI(<?= $chatData['id'] ?>)" id="btn-rev" class="h-11 px-5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-500 transition flex items-center justify-center">
                    Kirim 🚀
                </button>
            </div>
            <p id="rev-error" class="text-red-400 text-sm mt-2 hidden text-center"></p>
        </div>
    <?php else: ?>
        <div class="flex flex-col items-center justify-center w-full my-auto">
            <div class="w-full max-w-5xl p-8 rounded-3xl shadow-2xl relative overflow-hidden" style="background: linear-gradient(145deg, #1e293b, #0f172a); border: 1px solid #334155;">
                <div class="absolute -top-32 -left-32 w-64 h-64 bg-blue-500 rounded-full mix-blend-screen filter blur-3xl opacity-20"></div>
                <div class="absolute -bottom-32 -right-32 w-64 h-64 bg-purple-500 rounded-full mix-blend-screen filter blur-3xl opacity-20"></div>

                <div class="relative z-10 text-center mb-6">
                    <div class="text-4xl mb-4">✨</div>
                    <h1 class="text-2xl font-bold text-white mb-2" style="font-family: 'Outfit', sans-serif;">Buat Surat Menggunakan AI</h1>
                    <p class="text-gray-400 text-sm">Ceritakan surat apa yang ingin Anda buat, sistem cerdas kami akan menyusun draf resminya secara instan.</p>
                </div>

                <div class="relative z-10">
                    <textarea id="ai-prompt" class="w-full bg-slate-800/80 text-white placeholder-gray-500 border border-slate-600 rounded-2xl p-4 text-base focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/30 transition-all resize-y min-h-[120px] shadow-inner" placeholder="Contoh: Buatkan surat undangan resmi ke wakil rektor 1 terkait rapat visitasi akreditasi prodi Magister Manajemen besok pagi jam 09.00 di ruang rapat utama..."></textarea>
                    
                    <button type="button" onclick="generateSuratAI()" id="btn-ai" class="mt-5 w-full py-3 rounded-xl font-bold text-base flex items-center justify-center gap-2 transition-all hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-blue-500/25 cursor-pointer" style="background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: #ffffff;">
                        <span id="btn-ai-text">🪄 Buatkan Surat Sekarang</span>
                    </button>

                    <div id="ai-status" class="hidden mt-5 text-center">
                        <div class="inline-block animate-spin w-6 h-6 border-4 border-blue-500 border-t-transparent rounded-full mb-2"></div>
                        <p class="text-blue-400 font-medium text-sm animate-pulse">AI sedang berpikir dan merangkai struktur surat...</p>
                    </div>
                    
                    <p id="ai-error" class="text-red-400 text-center mt-4 hidden bg-red-500/10 p-3 rounded-xl border border-red-500/20 text-sm"></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
"""

# Isolate the hero block and replace
import re
sb_content = re.sub(
    r'<!-- Tampilan AI Hero -->.*?<script>',
    chat_ui + '\n    <script>',
    sb_content,
    flags=re.DOTALL
)

# Insert revisiSuratAI javascript function
js_logic = """
    function revisiSuratAI(id) {
        var promptEl = document.getElementById('ai-rev-prompt');
        var prompt = promptEl.value.trim();
        var errEl = document.getElementById('rev-error');
        var btn = document.getElementById('btn-rev');
        var status = document.getElementById('rev-status');
        var prevHtml = document.getElementById('chat-preview').innerHTML;

        errEl.classList.add('hidden');
        if (!prompt) return;

        btn.disabled = true;
        promptEl.disabled = true;
        status.classList.remove('hidden');

        // Panggil AI
        fetch(_aiBaseUrl + '/api/generate_surat_ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt: prompt, previous_html: prevHtml })
        })
        .then(r => r.json())
        .then(d => {
            if(d.ok) {
                // Update DB diam-diam
                fetch(_aiBaseUrl + '/api/update_surat_ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, isi_surat: d.html })
                })
                .then(r2 => r2.json())
                .then(d2 => {
                    if(d2.ok) {
                        document.getElementById('chat-preview').innerHTML = d.html;
                        promptEl.value = '';
                    } else {
                        errEl.textContent = 'Gagal menyimpan revisi: ' + d2.error;
                        errEl.classList.remove('hidden');
                    }
                }).finally(() => { resetRevState(btn, promptEl, status); });
            } else {
                errEl.textContent = 'Gagal merevisi: ' + d.error;
                errEl.classList.remove('hidden');
                resetRevState(btn, promptEl, status);
            }
        })
        .catch(e => {
            errEl.textContent = 'Error koneksi: ' + e.message;
            errEl.classList.remove('hidden');
            resetRevState(btn, promptEl, status);
        });
    }

    function resetRevState(btn, input, status) {
        btn.disabled = false;
        input.disabled = false;
        status.classList.add('hidden');
        input.focus();
    }

    // Bind Enter key for revision
    var revInput = document.getElementById('ai-rev-prompt');
    if (revInput) {
        revInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                var id = <?php echo $chatId; ?>;
                if(id > 0) revisiSuratAI(id);
            }
        });
    }
"""

sb_content = sb_content.replace(
    'document.getElementById(\'ai-prompt\').addEventListener(\'keydown\'',
    js_logic + '\n    document.getElementById(\'ai-prompt\')?.addEventListener(\'keydown\''
)

with open('pages/surat_buat.php', 'w', encoding='utf-8') as f:
    f.write(sb_content)

print("AI Chat flow implemented successfully.")
