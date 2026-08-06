import os
import re

# 1. Update generate_surat_ai.php
file_gen = 'api/generate_surat_ai.php'
with open(file_gen, 'r', encoding='utf-8') as f:
    gen_content = f.read()

gen_content = gen_content.replace(
    '  "perihal": "Ringkasan perihal surat (singkat, padat, maks 5-7 kata)",\n',
    '  "perihal": "Ringkasan perihal surat (singkat, padat, maks 5-7 kata)",\n  "ai_reply": "Balasan komunikatif ramah (bahasa Indonesia) kepada pengguna menjelaskan apa yang telah Anda draf/revisi (tanpa HTML, maks 2 kalimat).",\n'
)

gen_content = gen_content.replace(
    "'perihal'     => $parsed['perihal'] ?? '',",
    "'perihal'     => $parsed['perihal'] ?? '',\n    'ai_reply'    => $parsed['ai_reply'] ?? 'Baik, draf telah saya selesaikan sesuai instruksi Anda.',"
)
with open(file_gen, 'w', encoding='utf-8') as f:
    f.write(gen_content)


# 2. Update update_surat_ajax.php
file_ajax = 'api/update_surat_ajax.php'
with open(file_ajax, 'r', encoding='utf-8') as f:
    ajax_content = f.read()

ajax_target = """$prompt = trim($data['prompt'] ?? '');
if ($prompt) {
    dbExecute("INSERT INTO surat_chat (surat_id, role, content) VALUES (?, 'user', ?)", [$id, $prompt]);
}
if ($isi_surat) {
    dbExecute("INSERT INTO surat_chat (surat_id, role, content) VALUES (?, 'assistant', ?)", [$id, $isi_surat]);
}
dbExecute("UPDATE surat SET isi_surat=? WHERE id=?", [$isi_surat, $id]);"""

ajax_replacement = """$prompt = trim($data['prompt'] ?? '');
$ai_reply = trim($data['ai_reply'] ?? 'Revisi telah diterapkan.');
if ($prompt) {
    dbExecute("INSERT INTO surat_chat (surat_id, role, content) VALUES (?, 'user', ?)", [$id, $prompt]);
}
if ($isi_surat) {
    dbExecute("INSERT INTO surat_chat (surat_id, role, content, ai_reply) VALUES (?, 'assistant', ?, ?)", [$id, $isi_surat, $ai_reply]);
}
dbExecute("UPDATE surat SET isi_surat=? WHERE id=?", [$isi_surat, $id]);"""

ajax_content = ajax_content.replace(ajax_target, ajax_replacement)
with open(file_ajax, 'w', encoding='utf-8') as f:
    f.write(ajax_content)


# 3. Update surat_buat.php
file_buat = 'pages/surat_buat.php'
with open(file_buat, 'r', encoding='utf-8') as f:
    sb_content = f.read()

# Add hidden input
sb_content = sb_content.replace(
    '<input type="hidden" name="prompt_awal" id="inp-prompt-awal" value="">',
    '<input type="hidden" name="prompt_awal" id="inp-prompt-awal" value="">\n    <input type="hidden" name="ai_reply" id="inp-ai-reply" value="">'
)

# Populate hidden input in generateSuratAI
sb_content = sb_content.replace(
    "document.getElementById('inp-prompt-awal').value = prompt;",
    "document.getElementById('inp-prompt-awal').value = prompt;\n                document.getElementById('inp-ai-reply').value = d.ai_reply || 'Draf surat awal berhasil dibuat.';"
)

# POST handler
sb_content = sb_content.replace(
    'dbExecute("INSERT INTO surat_chat (surat_id, role, content) VALUES (?, \'assistant\', ?)", [$id, $isiSurat]);',
    """$aiReply = trim($_POST['ai_reply'] ?? 'Draf surat awal berhasil dibuat.');
        dbExecute("INSERT INTO surat_chat (surat_id, role, content, ai_reply) VALUES (?, 'assistant', ?, ?)", [$id, $isiSurat, $aiReply]);"""
)

# Fix revisiSuratAI payload
sb_content = sb_content.replace(
    "body: JSON.stringify({ id: id, prompt: prompt, isi_surat: d.html })",
    "body: JSON.stringify({ id: id, prompt: prompt, isi_surat: d.html, ai_reply: d.ai_reply })"
)

# Update Chat UI bubble
old_bubble = """                            <div class="flex gap-4 items-start w-full">
                                <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center flex-shrink-0 text-white shadow-lg">✨</div>
                                <div class="flex flex-col gap-2 max-w-[95%]">
                                    <div class="bg-white rounded-2xl rounded-tl-sm p-6 md:p-8 shadow-sm border border-slate-300 text-black document-preview" style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5;">
                                        <?= $msg['content'] ?>
                                    </div>"""

new_bubble = """                            <div class="flex gap-4 items-start w-full">
                                <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center flex-shrink-0 text-white shadow-lg mt-1">✨</div>
                                <div class="flex flex-col gap-3 max-w-[95%]">
                                    <?php if (!empty($msg['ai_reply'])): ?>
                                        <div class="bg-slate-800 text-white rounded-2xl rounded-tl-sm px-5 py-3 shadow-md border border-slate-700 w-fit max-w-full text-sm leading-relaxed">
                                            <?= nl2br(e($msg['ai_reply'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="bg-white rounded-2xl p-6 md:p-8 shadow-md border border-slate-300 text-black document-preview" style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5;">
                                        <?= $msg['content'] ?>
                                    </div>"""

sb_content = sb_content.replace(old_bubble, new_bubble)

with open(file_buat, 'w', encoding='utf-8') as f:
    f.write(sb_content)

print("AI Reply feature and chat fix implemented successfully.")
