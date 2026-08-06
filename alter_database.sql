-- Perbaikan Struktur Database: Sinkronisasi Skema yang Hilang

USE pascasarjana_unp;

-- 1. Tambah kolom ke template_surat
ALTER TABLE template_surat 
ADD COLUMN IF NOT EXISTS nama_template VARCHAR(150) AFTER jenis_surat,
ADD COLUMN IF NOT EXISTS variabel_tersedia TEXT AFTER header_html,
ADD COLUMN IF NOT EXISTS is_massal TINYINT(1) DEFAULT 0 AFTER variabel_tersedia;

-- 2. Tambah kolom ke surat
ALTER TABLE surat
ADD COLUMN IF NOT EXISTS jenis_penerima VARCHAR(50) DEFAULT 'individual' AFTER nama_penerima,
ADD COLUMN IF NOT EXISTS hari VARCHAR(20) AFTER tanggal,
ADD COLUMN IF NOT EXISTS kota VARCHAR(50) DEFAULT 'Sukabumi' AFTER hari,
ADD COLUMN IF NOT EXISTS lampiran VARCHAR(100) DEFAULT '-' AFTER status,
ADD COLUMN IF NOT EXISTS updated_by INT NULL AFTER created_by;

-- 3. Update FK constraint untuk updated_by
ALTER TABLE surat
ADD CONSTRAINT fk_surat_updated_by FOREIGN KEY IF NOT EXISTS (updated_by) REFERENCES users(id);
