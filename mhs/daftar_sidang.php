<?php
require_once __DIR__ . '/../includes/functions.php'; // harus sebelum header.php agar session bisa dimulai

// Jenis pendaftaran dari parameter URL
$jenisMap = [
    'proposal'    => 'Seminar Proposal Tesis',
    'tesis'       => 'Sidang Tesis',
    'kualifikasi' => 'Ujian Kualifikasi',
    'iamp'        => 'Program IAMP',
    'capstone'    => 'Capstone Project',
    'kolokium'    => 'Kolokium',
];
$jenisKey  = $_GET['jenis'] ?? 'tesis';
$jenisSidang = $jenisMap[$jenisKey] ?? 'Sidang Tesis';
$pageTitle = 'Pendaftaran ' . $jenisSidang;
require_once 'header.php';

// Ambil data mahasiswa dari header
$mhsId = $mhs['id'];
$mhsRow = dbQueryOne("
    SELECT m.*, p.nama AS nama_prodi
    FROM mahasiswa m
    LEFT JOIN prodi p ON m.prodi_id = p.id
    WHERE m.id = ?", [$mhsId]);

// Fallback ke data header jika tidak ditemukan
if (!$mhsRow) {

    $mhsRow = [
        'nama'       => $mhs['nama'] ?? 'Ahmad Rizki Pratama',
        'nim'        => $mhs['nim']  ?? '2023MIF001',
        'angkatan'   => $mhs['angkatan'] ?? 2023,
        'email'      => $mhs['email'] ?? '-',
        'no_hp'      => $mhs['no_hp'] ?? '-',
        'nama_prodi' => $mhs['prodi'] ?? 'Magister Informatika',
        'judul_tesis'=> $mhs['judul_tesis'] ?? '',
        'dosen_pembimbing' => $mhs['pembimbing'] ?? '',
        'tempat_lahir' => '-', 'tanggal_lahir' => '0000-00-00',
        'alamat' => '-', 'konsentrasi' => '-',
    ];
}

// Ambil daftar dosen dari database sesuai dengan jurusan/prodi mahasiswa
$prodiId = $mhsRow['prodi_id'] ?? 0;
if ($prodiId) {
    $dosenList = dbQuery("SELECT id, nama, nidn FROM dosen WHERE status='Aktif' AND prodi_id=? ORDER BY nama", [$prodiId]);
} else {
    $dosenList = dbQuery("SELECT id, nama, nidn FROM dosen WHERE status='Aktif' ORDER BY nama");
}

// Cek apakah mahasiswa sudah ada pendaftaran aktif
$existing = dbQueryOne(
    "SELECT * FROM pendaftaran_sidang WHERE mahasiswa_id=? AND status IN ('Pending','Diverifikasi') ORDER BY id DESC LIMIT 1",
    [$mhsId]
);

// Hitung jumlah publikasi mahasiswa di portofolio
$pubCount = dbQueryOne("SELECT count(*) as total FROM mahasiswa_publikasi WHERE mahasiswa_id=?", [$mhsId])['total'] ?? 0;
?>

<div x-data="{
  step: 1,
  totalSteps: 4,
  submitted: false,
  loading: false,
  formData: {
    nama: '<?= e($mhsRow['nama']) ?>',
    nim: '<?= e($mhsRow['nim']) ?>',
    angkatan: '<?= e($mhsRow['angkatan']) ?>',
    email: '<?= e($mhsRow['email'] ?? '') ?>',
    hp: '<?= e($mhsRow['no_hp'] ?? '') ?>',
    prodi: '<?= e($mhsRow['nama_prodi'] ?? '') ?>',
    pembimbing1: '<?= e($mhsRow['dosen_pembimbing'] ?? '') ?>',
    pembimbing2: '',
    pembimbing2: '',
    judulTesis: '<?= e($mhsRow['judul_tesis'] ?? '') ?>'
  },
  berkas: {
    jurnal: false,
    buktiBayarJurnal: false,
    persetujuan: false,
    khs: false,
    bebasPerpus: false,
    bukuSumbangan: false,
    bebasAdmin: false,
    foto: false,
    draftTesis: false,
    codeProgram: false,
    presentasi: false
  }
}">

<?php if ($existing): ?>
<div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5 mb-6 flex items-start gap-4">
  <div class="text-2xl">⚠️</div>
  <div>
    <div class="font-bold text-amber-800 dark:text-amber-300 text-sm mb-1">Pendaftaran Sudah Ada</div>
    <p class="text-xs text-amber-700 dark:text-amber-400">Anda sudah memiliki pendaftaran sidang dengan status <strong><?= e($existing['status']) ?></strong> yang dikirim pada <?= date('d F Y', strtotime($existing['created_at'])) ?>. Harap tunggu verifikasi dari admin.</p>
  </div>
</div>
<?php endif; ?>

<!-- Step Indicator -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-6">
  <h2 class="font-display font-bold text-slate-800 dark:text-white text-base mb-1">🎓 Formulir Pendaftaran <?= htmlspecialchars($jenisSidang) ?></h2>
  <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Program Studi <?= e($mhsRow['nama_prodi'] ?? 'Pascasarjana') ?> / Semester Genap Tahun Akademik 2025/2026</p>
  <div class="flex items-center">
    <?php $steps = ['Data Diri & Pembimbing', 'Luaran Artikel Tesis', 'Dokumen Administrasi', 'Konfirmasi']; ?>
    <?php foreach($steps as $i=>$s): ?>
    <div class="flex items-center <?= $i<count($steps)-1?'flex-1':'' ?>">
      <div class="flex flex-col items-center">
        <div :class="step > <?=$i+1?> ? 'bg-emerald-500 text-white' : (step === <?=$i+1?> ? 'ring-4 ring-[#8c0c4c]/20' : '')" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300" :style="step > <?=$i+1?> ? '' : (step === <?=$i+1?> ? 'background:#8c0c4c;color:white' : 'background:#e2e8f0;color:#94a3b8')">
          <span x-show="step <= <?=$i+1?>"><?= $i+1 ?></span>
          <span x-show="step > <?=$i+1?>">✓</span>
        </div>
        <div class="text-[10px] sm:text-xs mt-1.5 font-medium text-center" :class="step >= <?=$i+1?> ? 'text-slate-800 dark:text-white' : 'text-slate-400'"><?= $s ?></div>
      </div>
      <?php if($i<count($steps)-1): ?>
      <div class="flex-1 h-0.5 mx-2 mb-5 transition-all duration-500" :style="step > <?=$i+1?> ? 'background:#10b981' : 'background:#e2e8f0'"></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ====== STEP 1: Data Diri & Pembimbing ====== -->
<div x-show="step===1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm mb-4 overflow-hidden">
    <div class="bg-[#8c0c4c] text-white px-5 py-3 text-xs font-bold">Bagian 1 dari 3 — Data Diri</div>
    <div class="p-6 space-y-5">
      <div class="grid md:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">Nama Lengkap / Full Name <span class="text-red-500">*</span></label>
          <input type="text" x-model="formData.nama" readonly class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl px-4 py-2.5 text-sm cursor-not-allowed opacity-75">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">NIM / Student ID Number <span class="text-red-500">*</span></label>
          <input type="text" x-model="formData.nim" readonly class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl px-4 py-2.5 text-sm cursor-not-allowed opacity-75">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">Angkatan / Cohort <span class="text-red-500">*</span></label>
          <input type="text" x-model="formData.angkatan" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all" placeholder="Contoh: 2024">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">Program Studi</label>
          <input type="text" x-model="formData.prodi" readonly class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl px-4 py-2.5 text-sm cursor-not-allowed opacity-75">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">Email <span class="text-red-500">*</span></label>
          <input type="email" x-model="formData.email" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">Nomor WhatsApp / WhatsApp Number <span class="text-red-500">*</span></label>
          <input type="text" x-model="formData.hp" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
        </div>
      </div>

      <!-- Pembimbing I -->
      <div class="border-t border-slate-100 dark:border-slate-700 pt-5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">Nama Pembimbing I / Supervisor I Name <span class="text-red-500">*</span></label>
        <div class="relative">
          <select x-model="formData.pembimbing1" class="w-full appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all cursor-pointer">
            <option value="">-- Pilih Dosen Pembimbing I --</option>
            <?php foreach($dosenList as $d): ?>
            <option value="<?= e($d['nama']) ?>"><?= e($d['nama']) ?><?= $d['nidn'] ? ' (' . e($d['nidn']) . ')' : '' ?></option>
            <?php endforeach; ?>
            <option value="__other__">Lainnya (isi manual)</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>
        <div x-show="formData.pembimbing1==='__other__'" x-transition class="mt-2">
          <input type="text" @input="formData.pembimbing1_manual=$event.target.value" placeholder="Ketik nama dosen pembimbing..." class="w-full bg-white dark:bg-slate-900 border border-[#8c0c4c] text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#8c0c4c]/20">
        </div>
      </div>

      <!-- Pembimbing II -->
      <div class="border-t border-slate-100 dark:border-slate-700 pt-5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">Nama Pembimbing II / Supervisor II Name <span class="text-slate-400 font-normal">(opsional)</span></label>
        <div class="relative">
          <select x-model="formData.pembimbing2" class="w-full appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all cursor-pointer">
            <option value="">-- Pilih Dosen Pembimbing II (jika ada) --</option>
            <?php foreach($dosenList as $d): ?>
            <option value="<?= e($d['nama']) ?>"><?= e($d['nama']) ?><?= $d['nidn'] ? ' (' . e($d['nidn']) . ')' : '' ?></option>
            <?php endforeach; ?>
            <option value="__other2__">Lainnya (isi manual)</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>
        <div x-show="formData.pembimbing2==='__other2__'" x-transition class="mt-2">
          <input type="text" @input="formData.pembimbing2_manual=$event.target.value" placeholder="Ketik nama dosen pembimbing..." class="w-full bg-white dark:bg-slate-900 border border-[#8c0c4c] text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#8c0c4c]/20">
        </div>
      </div>
    </div>
  </div>
  <div class="flex justify-end">
    <button @click="step=2" class="flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-white text-sm transition-all hover:shadow-lg bg-gradient-to-br from-[#8c0c4c] to-[#a3155b]">
      Selanjutnya <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
  </div>
</div>

<!-- ====== STEP 2: Luaran Artikel ====== -->
<div x-show="step===2" style="display:none" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm mb-4 overflow-hidden">
    <div class="bg-[#8c0c4c] text-white px-5 py-3 text-xs font-bold flex justify-between items-center">
        <span>Bagian 2 dari 3</span>
        <span>LUARAN ARTIKEL / PUBLIKASI</span>
    </div>
    
    <div class="p-8 space-y-8 bg-slate-50/50 dark:bg-slate-900/20 text-center">
        <?php if ($pubCount > 0): ?>
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Portofolio Tersedia</h3>
            <p class="text-slate-600 dark:text-slate-400 max-w-md mx-auto mb-6">Sistem mendeteksi Anda memiliki <strong class="text-slate-800 dark:text-slate-200"><?= $pubCount ?> publikasi</strong> terdaftar di portofolio. Publikasi ini akan otomatis dilampirkan sebagai dokumen pendukung syarat pendaftaran sidang Anda.</p>
            <a href="penelitian" target="_blank" class="text-sm font-semibold text-[#8c0c4c] hover:text-[#a3155b] underline underline-offset-4">Lihat Portofolio Anda</a>
        <?php else: ?>
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 text-amber-600 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Portofolio Kosong</h3>
            <p class="text-slate-600 dark:text-slate-400 max-w-md mx-auto mb-6">Anda belum memiliki publikasi atau artikel yang terdaftar di portofolio Anda. Beberapa jenis sidang mewajibkan adanya luaran publikasi.</p>
            <a href="penelitian" class="inline-flex items-center gap-2 bg-[#8c0c4c] hover:bg-[#a3155b] text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-md transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Isi Portofolio Sekarang
            </a>
            <p class="text-xs text-slate-400 mt-4">* Anda bisa melanjutkan pendaftaran jika prodi Anda tidak mensyaratkan publikasi.</p>
        <?php endif; ?>
    </div>
  </div>
  <div class="flex justify-between">
    <button @click="step=1" class="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Kembali
    </button>
    <button @click="step=3" class="flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-white text-sm hover:shadow-lg transition bg-gradient-to-br from-[#8c0c4c] to-[#a3155b]">
      Selanjutnya <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
  </div>
</div>

<!-- ====== STEP 3: Dokumen Administrasi ====== -->
<div x-show="step===3" style="display:none" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm mb-4 overflow-hidden">
    <div class="bg-[#8c0c4c] text-white px-5 py-3 text-xs font-bold">Bagian 3 dari 3</div>
    <div class="p-6 space-y-4">
      <h3 class="font-display font-bold text-slate-800 dark:text-white uppercase tracking-wide text-sm">DOKUMEN ADMINISTRASI / <span class="font-normal italic">ADMINISTRATIVE DOCUMENTS</span></h3>

      <div>
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">Judul Tesis / Thesis Title <span class="text-red-500">*</span></label>
        <textarea x-model="formData.judulTesis" rows="3" placeholder="Masukkan judul tesis lengkap..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all"></textarea>
      </div>

      <?php
      $admin_docs = [
        ['key'=>'persetujuan',     'label'=>'Upload Persetujuan Pembimbing / Upload Supervisor Approval'],
        ['key'=>'khs',             'label'=>'Upload KHS Sementara Semester 1 - 3 / Upload KHS for Semesters 1-3'],
        ['key'=>'bebasPerpus',     'label'=>'Upload Surat Bebas Pinjam Perpustakaan / Upload Library Clearance Letter'],
        ['key'=>'bukuSumbangan',   'label'=>'Upload Surat Bukti Penyerahan Buku Sumbangan (3 Buah) dari Bag. Perpustakaan Universitas Nusa Putra / Upload Letter of Receipt for 3 Donated Books from the Library'],
        ['key'=>'bebasAdmin',      'label'=>'Upload Bukti Bebas Administrasi / Upload Proof of Administrative Clearance'],
        ['key'=>'foto',            'label'=>'Upload File Foto Background Merah (Pria Berjas Hitam Berdasi & Wanita Memakai Blazer Hitam) / Upload Red Background Photo File'],
        ['key'=>'draftTesis',      'label'=>'Upload Draft Tesis / Upload Thesis Draft'],
        ['key'=>'codeProgram',     'label'=>'Upload Code Program (.zip)'],
        ['key'=>'presentasi',      'label'=>'Upload Presentasi Sidang / Upload Defense Presentation'],
      ];
      foreach($admin_docs as $doc): ?>
      <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4" :class="berkas.<?= $doc['key'] ?> ? 'bg-emerald-50 border-emerald-300 dark:bg-emerald-900/10' : ''">
        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-3"><?= $doc['label'] ?> <span class="text-red-500">*</span></label>
        <input type="file" name="berkas_<?= $doc['key'] ?>" id="berkas_<?= $doc['key'] ?>" accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png" @change="berkas.<?= $doc['key'] ?> = $event.target.files.length > 0" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-slate-700 dark:file:text-slate-300 transition">
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="flex justify-between">
    <button @click="step=2" class="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Kembali
    </button>
    <button @click="step=4" class="flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-white text-sm hover:shadow-lg transition bg-gradient-to-br from-[#8c0c4c] to-[#a3155b]">
      Selanjutnya <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
  </div>
</div>

<!-- ====== STEP 4: Konfirmasi ====== -->
<div x-show="step===4" style="display:none" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
  <div x-show="!submitted">
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-4">
      <h3 class="font-display font-bold text-slate-800 dark:text-white mb-4">Konfirmasi Pendaftaran</h3>
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wide mb-3">Ringkasan Data Diri</div>
          <div class="space-y-2">
            <div class="flex gap-2"><span class="text-xs text-slate-500 w-32 flex-shrink-0">Nama Lengkap</span><span class="text-xs font-semibold text-slate-800 dark:text-white">: <span x-text="formData.nama"></span></span></div>
            <div class="flex gap-2"><span class="text-xs text-slate-500 w-32 flex-shrink-0">NIM</span><span class="text-xs font-semibold text-slate-800 dark:text-white">: <span x-text="formData.nim"></span></span></div>
            <div class="flex gap-2"><span class="text-xs text-slate-500 w-32 flex-shrink-0">Angkatan</span><span class="text-xs font-semibold text-slate-800 dark:text-white">: <span x-text="formData.angkatan"></span></span></div>
            <div class="flex gap-2"><span class="text-xs text-slate-500 w-32 flex-shrink-0">Program Studi</span><span class="text-xs font-semibold text-slate-800 dark:text-white">: <span x-text="formData.prodi"></span></span></div>
            <div class="flex gap-2"><span class="text-xs text-slate-500 w-32 flex-shrink-0">Email</span><span class="text-xs font-semibold text-slate-800 dark:text-white">: <span x-text="formData.email"></span></span></div>
            <div class="flex gap-2"><span class="text-xs text-slate-500 w-32 flex-shrink-0">No WhatsApp</span><span class="text-xs font-semibold text-slate-800 dark:text-white">: <span x-text="formData.hp"></span></span></div>
            <div class="flex gap-2"><span class="text-xs text-slate-500 w-32 flex-shrink-0">Pembimbing I</span><span class="text-xs font-semibold text-slate-800 dark:text-white">: <span x-text="formData.pembimbing1 === '__other__' ? formData.pembimbing1_manual : formData.pembimbing1"></span></span></div>
            <div class="flex gap-2"><span class="text-xs text-slate-500 w-32 flex-shrink-0">Pembimbing II</span><span class="text-xs font-semibold text-slate-800 dark:text-white">: <span x-text="(formData.pembimbing2 === '__other2__' ? formData.pembimbing2_manual : formData.pembimbing2) || '-'"></span></span></div>
            <div class="flex gap-2"><span class="text-xs text-slate-500 w-32 flex-shrink-0">Jml Publikasi</span><span class="text-xs font-semibold text-slate-800 dark:text-white">: <span x-text="luaranList.length + ' Artikel'"></span></span></div>
          </div>
          <div class="mt-4">
            <div class="text-xs text-slate-500 font-semibold mb-1">Judul Tesis:</div>
            <p class="text-sm text-slate-800 dark:text-white italic leading-relaxed" x-text="formData.judulTesis"></p>
          </div>
        </div>
        <div>
          <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wide mb-3">Status Berkas Administratif</div>
          <div class="space-y-1.5">
            <?php
            $allBerkas = array_map(fn($d)=>['key'=>$d['key'],'label'=>substr($d['label'],0,50).'...'], $admin_docs);
            foreach($allBerkas as $b): ?>
            <div class="flex items-center gap-2">
              <div x-show="berkas.<?= $b['key'] ?>" class="w-4 h-4 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0"><svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div>
              <div x-show="!berkas.<?= $b['key'] ?>" class="w-4 h-4 rounded-full bg-slate-200 dark:bg-slate-700 flex-shrink-0"></div>
              <span class="text-xs text-slate-700 dark:text-slate-200"><?= htmlspecialchars($b['label']) ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-4 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
            <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed font-medium">
              🎓 Pastikan semua data dan berkas administratif sudah sesuai. Setelah disubmit, Anda tidak dapat mengubah data sampai diverifikasi.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Form POST ke database -->
    <form id="formSidang">
      <input type="hidden" name="mahasiswa_id" value="<?= $mhsId ?>">
      <input type="hidden" name="nama" :value="formData.nama">
      <input type="hidden" name="nim" :value="formData.nim">
      <input type="hidden" name="angkatan" :value="formData.angkatan">
      <input type="hidden" name="email" :value="formData.email">
      <input type="hidden" name="no_hp" :value="formData.hp">
      <input type="hidden" name="pembimbing1" :value="formData.pembimbing1 === '__other__' ? formData.pembimbing1_manual : formData.pembimbing1">
      <input type="hidden" name="pembimbing2" :value="formData.pembimbing2 === '__other2__' ? formData.pembimbing2_manual : formData.pembimbing2">
      <input type="hidden" name="judul_tesis" :value="formData.judulTesis">
      <input type="hidden" name="jenis_sidang" value="<?= htmlspecialchars($jenisSidang) ?>">
    </form>

    <div class="flex justify-between">
      <button @click="step=3" class="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Kembali
      </button>
      <button :disabled="loading" @click="
        loading = true;
        let fd = new FormData(document.getElementById('formSidang'));
        
        // Append all files
        document.querySelectorAll('input[type=file]').forEach(input => {
            if(input.files.length > 0) fd.append(input.name, input.files[0]);
        });
        
        fetch('aksi_daftar_sidang.php', {
            method: 'POST',
            body: fd
        })
        .then(res => res.text())
        .then(text => {
            // Setelah berhasil submit data, buka cetak PDF di tab baru
            window.open('cetak_pendaftaran_sidang.php?judul=' + encodeURIComponent(formData.judulTesis) + '&angkatan=' + encodeURIComponent(formData.angkatan) + '&pembimbing1=' + encodeURIComponent(formData.pembimbing1) + '&pembimbing2=' + encodeURIComponent(formData.pembimbing2) + '&email=' + encodeURIComponent(formData.email) + '&hp=' + encodeURIComponent(formData.hp), '_blank');
            window.location.href='status_sidang';
        })
        .catch(err => {
            alert('Terjadi kesalahan saat mengunggah file.');
            loading = false;
        });
      " class="flex items-center gap-2 px-8 py-3 rounded-xl font-bold text-white text-sm shadow-lg hover:shadow-xl transition hover:-translate-y-0.5 bg-gradient-to-br from-emerald-500 to-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
        <span x-show="!loading">Kirim Pendaftaran</span>
        <span x-show="loading">Mengirim...</span>
      </button>
    </div>
  </div>
</div>

</div>

<?php require_once 'footer.php'; ?>
