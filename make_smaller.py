import os

# 1. Update surat_buat.php to make preview smaller
file_buat = 'pages/surat_buat.php'
with open(file_buat, 'r', encoding='utf-8') as f:
    sb_content = f.read()

# Fallback preview
sb_content = sb_content.replace(
    '''<div class="flex-1 overflow-hidden bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-slate-300 text-black document-preview cursor-pointer hover:shadow-lg hover:ring-2 hover:ring-blue-500 transition-all group relative" onclick="window.open('<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $chatData['id'] ?>&mode=view&src=buat', '_blank')" style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5;">''',
    '''<div class="flex-1 overflow-hidden bg-white rounded-xl p-4 md:p-6 shadow-sm border border-slate-300 text-black document-preview cursor-pointer hover:shadow-lg hover:ring-2 hover:ring-blue-500 transition-all group relative max-w-[800px]" onclick="window.open('<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $chatData['id'] ?>&mode=view&src=buat', '_blank')" style="font-family: 'Times New Roman', serif; font-size: 10.5pt; line-height: 1.4;">'''
)

# Chat history preview wrapper
sb_content = sb_content.replace(
    '<div class="flex flex-col gap-3 max-w-[95%]">',
    '<div class="flex flex-col gap-3 max-w-[95%] md:max-w-[800px]">'
)

sb_content = sb_content.replace(
    '''<div class="bg-white rounded-2xl p-6 md:p-8 shadow-md border border-slate-300 text-black document-preview cursor-pointer hover:shadow-lg hover:ring-2 hover:ring-blue-500 transition-all group relative overflow-hidden" onclick="window.open('<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $chatData['id'] ?>&chat_id=<?= $msg['id'] ?>&mode=view&src=buat', '_blank')" style="font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5;">''',
    '''<div class="bg-white rounded-xl p-4 md:p-6 shadow-md border border-slate-300 text-black document-preview cursor-pointer hover:shadow-lg hover:ring-2 hover:ring-blue-500 transition-all group relative overflow-hidden" onclick="window.open('<?= BASE_URL ?>/api/cetak_surat.php?id=<?= $chatData['id'] ?>&chat_id=<?= $msg['id'] ?>&mode=view&src=buat', '_blank')" style="font-family: 'Times New Roman', serif; font-size: 10.5pt; line-height: 1.4;">'''
)

with open(file_buat, 'w', encoding='utf-8') as f:
    f.write(sb_content)


# 2. Update generate_surat_ai.php for detailed AI reply
file_gen = 'api/generate_surat_ai.php'
with open(file_gen, 'r', encoding='utf-8') as f:
    gen_content = f.read()

gen_content = gen_content.replace(
    '"Balasan komunikatif ramah (bahasa Indonesia) kepada pengguna menjelaskan apa yang telah Anda draf/revisi (tanpa HTML, maks 2 kalimat)."',
    '"Balasan komunikatif dan ramah (bahasa Indonesia) kepada pengguna yang MENDETAILKAN perubahan apa saja yang telah Anda lakukan pada draf surat berdasarkan instruksi. Sebutkan poin-poin spesifiknya (tanpa format HTML, 2-3 kalimat)."'
)

with open(file_gen, 'w', encoding='utf-8') as f:
    f.write(gen_content)

print("Make preview smaller and AI reply more detailed completed.")
