import zipfile
import os
import shutil

docx_file = r'C:\xampp\htdocs\webdummy\Contoh Lampiran\Lampiran 1. Prof. Ts. Deden Witarsyah, S.T., M.Eng., Ph.D. Ketua Pembimbing – Form Penilaian & Revisi Seminar Proposal (1).docx'
extract_dir = r'C:\xampp\htdocs\webdummy\temp_docx'

os.makedirs(extract_dir, exist_ok=True)
with zipfile.ZipFile(docx_file, 'r') as zip_ref:
    zip_ref.extractall(extract_dir)

print("Extracted files:")
for root, dirs, files in os.walk(extract_dir):
    for f in files:
        if 'media' in root:
            print(os.path.join(root, f))
