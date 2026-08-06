import os

def update_tesis():
    with open('pages/buat_lampiran_tesis.php', 'r', encoding='utf-8') as f:
        content = f.read()
        
    # Update INSERT
    content = content.replace(
        "INSERT INTO riwayat_lampiran (prodi_id, nama_mhs",
        "INSERT INTO riwayat_lampiran (jenis, prodi_id, nama_mhs"
    )
    content = content.replace(
        "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        "VALUES ('tesis', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    )
    
    # Update SELECT for isSuperAdmin
    content = content.replace(
        "SELECT r.*, p.nama as nama_prodi FROM riwayat_lampiran r JOIN prodi p ON r.prodi_id = p.id ORDER BY",
        "SELECT r.*, p.nama as nama_prodi FROM riwayat_lampiran r JOIN prodi p ON r.prodi_id = p.id WHERE r.jenis = 'tesis' ORDER BY"
    )
    # Update SELECT for Admin
    content = content.replace(
        "WHERE r.prodi_id = ? ORDER BY",
        "WHERE r.prodi_id = ? AND r.jenis = 'tesis' ORDER BY"
    )

    with open('pages/buat_lampiran_tesis.php', 'w', encoding='utf-8') as f:
        f.write(content)

def create_proposal():
    with open('pages/buat_lampiran_tesis.php', 'r', encoding='utf-8') as f:
        content = f.read()

    # Replacements for Proposal
    content = content.replace('Buat Lampiran Tesis', 'Buat Lampiran Proposal')
    content = content.replace('Riwayat Lampiran Tesis', 'Riwayat Lampiran Proposal')
    content = content.replace('Lampiran Tesis', 'Lampiran Proposal')
    content = content.replace('Tesis & Sidang', 'Proposal & Seminar')
    content = content.replace('Judul Tesis', 'Judul Proposal')
    content = content.replace('Tanggal Sidang', 'Tanggal Seminar')
    content = content.replace('tanggal_sidang', 'tanggal_sidang') # DB column remains same
    content = content.replace('judul_tesis', 'judul_tesis') # DB column remains same
    content = content.replace('Sidang Tesis', 'Seminar Proposal')
    content = content.replace('buat_lampiran_tesis.php', 'buat_lampiran_proposal.php')
    content = content.replace('cetak_lampiran.php', 'cetak_lampiran_proposal.php')
    
    # DB Values replacement (we already added 'tesis' above, so change to 'proposal')
    content = content.replace("VALUES ('tesis',", "VALUES ('proposal',")
    content = content.replace("r.jenis = 'tesis'", "r.jenis = 'proposal'")

    # Ensure $pageTitle and breadcrumb are correct
    with open('pages/buat_lampiran_proposal.php', 'w', encoding='utf-8') as f:
        f.write(content)

def duplicate_cetak():
    with open('pages/cetak_lampiran.php', 'r', encoding='utf-8') as f:
        content = f.read()

    # Change "Tesis" to "Proposal" in titles and texts
    content = content.replace('Lampiran Tesis', 'Lampiran Proposal')
    content = content.replace('Sidang Tesis', 'Seminar Proposal')
    content = content.replace('Judul Tesis', 'Judul Proposal')
    # Keep variable names same
    content = content.replace('LEMBAR PENGESAHAN TESIS', 'LEMBAR PENGESAHAN PROPOSAL')
    content = content.replace('PENGESAHAN TESIS', 'PENGESAHAN PROPOSAL')
    content = content.replace('UJIAN TESIS', 'SEMINAR PROPOSAL')
    content = content.replace('BIMBINGAN TESIS', 'BIMBINGAN PROPOSAL')
    content = content.replace('Tesis dengan judul', 'Proposal dengan judul')
    content = content.replace('Konsultasi Tesis', 'Konsultasi Proposal')
    
    with open('pages/cetak_lampiran_proposal.php', 'w', encoding='utf-8') as f:
        f.write(content)

update_tesis()
create_proposal()
duplicate_cetak()
print("Duplication and replacement complete.")
