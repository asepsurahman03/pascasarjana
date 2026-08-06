import os

file_buat = 'pages/surat_buat.php'
with open(file_buat, 'r', encoding='utf-8') as f:
    sb_content = f.read()

# Replace Fallback div
target_fallback = """<div class="flex-1 overflow-hidden bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-300 text-black document-preview" style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5;">"""
replace_fallback = """<div class="flex-1 overflow-hidden bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-300 text-black document-preview cursor-pointer hover:shadow-lg hover:ring-2 hover:ring-blue-500 transition-all group relative" onclick="window.open('<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $chatData['id'] ?>&mode=view&src=buat', '_blank')" style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5;">
                            <div class="absolute inset-0 bg-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none z-10">
                                <span class="bg-blue-600 text-white px-4 py-2 rounded-full font-bold shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform">🖨️ Klik untuk Cetak</span>
                            </div>"""
sb_content = sb_content.replace(target_fallback, replace_fallback)

# Replace History div
target_history = """<div class="bg-white rounded-2xl p-6 md:p-8 shadow-md border border-slate-300 text-black document-preview" style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5;">"""
replace_history = """<div class="bg-white rounded-2xl p-6 md:p-8 shadow-md border border-slate-300 text-black document-preview cursor-pointer hover:shadow-lg hover:ring-2 hover:ring-blue-500 transition-all group relative overflow-hidden" onclick="window.open('<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $chatData['id'] ?>&chat_id=<?= $msg['id'] ?>&mode=view', '_blank')" style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5;">
                                        <div class="absolute inset-0 bg-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none z-10">
                                            <span class="bg-blue-600 text-white px-4 py-2 rounded-full font-bold shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform">🖨️ Klik untuk Cetak Versi Ini</span>
                                        </div>"""
sb_content = sb_content.replace(target_history, replace_history)

with open(file_buat, 'w', encoding='utf-8') as f:
    f.write(sb_content)

print("Make document preview clickable completed.")
