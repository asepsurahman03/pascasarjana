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

?>

<div x-data="{
  step: 1,
  totalSteps: 4,
  submitted: false,
  loading: false,
  setuju: false,
  editDataDiri: false,
  editLuaran: false,
  isFetchingDoi: false,
  formData: {
    nama: '<?= e($mhsRow['nama']) ?>',
    nim: '<?= e($mhsRow['nim']) ?>',
    angkatan: '<?= e($mhsRow['angkatan']) ?>',
    email: '<?= e($mhsRow['email'] ?? '') ?>',
    hp: '<?= e($mhsRow['no_hp'] ?? '') ?>',
    prodi: '<?= e($mhsRow['nama_prodi'] ?? '') ?>',
    pembimbing1: '<?= e($mhsRow['dosen_pembimbing'] ?? '') ?>',
    pembimbing2: '',
    doi: '',
    judulArtikel: '',
    namaJurnal: '',
    statusLuaran: 'Sudah Publish / Already Published',
    kategoriLuaran: 'Scopus Q1',
    kategoriLuaranManual: '',
    linkLuaran: '',
    judulTesis: '<?= e($mhsRow['judul_tesis'] ?? '') ?>'
  },
  async fetchDoi() {
    let doi = (this.formData.doi || '').trim();
    if (!doi) {
      alert('Silakan masukkan nomor DOI terlebih dahulu.');
      return;
    }

    doi = doi.replace(/^(https?:\/\/)?(dx\.)?doi\.org\//i, '').replace(/^doi:\s*/i, '').trim();
    this.formData.doi = doi;
    this.isFetchingDoi = true;

    try {
      const [crossrefRes, openAlexRes, s2Res, dataciteRes, doiCnRes] = await Promise.allSettled([
        fetch(`https://api.crossref.org/works/${encodeURIComponent(doi)}`),
        fetch(`https://api.openalex.org/works/doi:${encodeURIComponent(doi)}?select=title,primary_location,biblio,doi`),
        fetch(`https://api.semanticscholar.org/graph/v1/paper/DOI:${encodeURIComponent(doi)}?fields=title,publicationVenue,year`),
        fetch(`https://api.datacite.org/dois/${encodeURIComponent(doi)}`),
        fetch(`https://doi.org/${encodeURIComponent(doi)}`, { headers: { Accept: 'application/vnd.citationstyles.csl+json' } })
      ]);

      const cr   = crossrefRes.status === 'fulfilled' && crossrefRes.value.ok ? await crossrefRes.value.json() : null;
      const oaw  = openAlexRes.status  === 'fulfilled' && openAlexRes.value.ok ? await openAlexRes.value.json() : null;
      const s2   = s2Res.status        === 'fulfilled' && s2Res.value.ok       ? await s2Res.value.json()       : null;
      const dc   = dataciteRes.status  === 'fulfilled' && dataciteRes.value.ok ? await dataciteRes.value.json() : null;
      const csl  = doiCnRes.status     === 'fulfilled' && doiCnRes.value.ok    ? await doiCnRes.value.json()    : null;

      const item   = cr?.message;
      const dcAttr = dc?.data?.attributes;

      if (!item && !oaw && !s2 && !dcAttr && !csl) {
        this.formData.linkLuaran = `https://doi.org/${doi}`;
        alert('⚠️ Data artikel tidak ditemukan secara otomatis di database Crossref/DataCite.\n\nSilakan lengkapi judul dan nama jurnal secara manual. Link artikel telah diisi otomatis.');
        this.isFetchingDoi = false;
        return;
      }

      // 1. Judul Artikel
      const judul = item?.title?.[0]
                 || oaw?.title
                 || s2?.title
                 || dcAttr?.titles?.[0]?.title
                 || (Array.isArray(csl?.title) ? csl.title[0] : csl?.title)
                 || '';
      if (judul) this.formData.judulArtikel = judul;

      // 2. Nama Jurnal / Prosiding
      const jurnalBase = item?.['container-title']?.[0]
                      || oaw?.primary_location?.source?.display_name
                      || s2?.publicationVenue?.name
                      || dcAttr?.container?.title
                      || csl?.['container-title']
                      || '';
      if (jurnalBase) this.formData.namaJurnal = jurnalBase;

      // 3. Link URL
      const url = item?.URL
                || (oaw?.doi ? (oaw.doi.startsWith('http') ? oaw.doi : `https://doi.org/${oaw.doi}`) : '')
                || csl?.URL
                || `https://doi.org/${doi}`;
      if (url) this.formData.linkLuaran = url;

      // 4. Deteksi Kategori Indeksasi Berpresisi Tinggi (Scopus & SINTA)
      const doiStr = doi.toLowerCase();
      const type = (item?.type || oaw?.type || csl?.type || '').toLowerCase();
      const pubName = (jurnalBase || '').toLowerCase();
      const publisher = (item?.publisher || oaw?.primary_location?.source?.host_organization_name || dcAttr?.publisher || csl?.publisher || '').toLowerCase();

      // Cek Prosiding Konferensi Internasional (IEEE, ACM, Springer, dll)
      const isConference = type.includes('proceeding') || type.includes('conference') || pubName.includes('conference') || pubName.includes('proceeding') || pubName.includes('symposium') || pubName.includes('ieee') || pubName.includes('acm') || doiStr.includes('10.1109/') || doiStr.includes('10.1145/');
      
      let kat = '';

      if (isConference) {
        kat = 'Prosiding Internasional (Scopus/IEEE)';
      } else {
        // Scopus Jurnal Bereputasi Indonesia
        const indoScopus = ['telkomnika', 'ijeecs', 'joiv', 'ijaseit', 'ijtech', 'international journal of technology', 'indonesian journal of electrical', 'ijost', 'jpii', 'biodiversitas', 'agrivita', 'iaes'];
        if (indoScopus.some(j => pubName.includes(j) || publisher.includes(j) || doiStr.includes('10.11591/'))) {
          kat = 'Scopus Q2';
        }
        // Scopus Q1 Publishers & DOI Prefixes
        else if (
          doiStr.includes('10.1016/') || doiStr.includes('10.1038/') || doiStr.includes('10.1002/') || 
          doiStr.includes('10.1371/') || doiStr.includes('10.1186/') || doiStr.includes('10.1093/') || 
          doiStr.includes('10.1017/') || doiStr.includes('10.3389/') || doiStr.includes('10.1039/') || 
          doiStr.includes('10.1021/') || ['elsevier', 'nature', 'wiley', 'cell press', 'plos', 'oxford university press', 'cambridge university press', 'frontiers in'].some(p => publisher.includes(p) || pubName.includes(p))
        ) {
          kat = 'Scopus Q1';
        }
        // Scopus Q2 Publishers & DOI Prefixes
        else if (
          doiStr.includes('10.1080/') || doiStr.includes('10.1007/') || doiStr.includes('10.3390/') || 
          doiStr.includes('10.1108/') || doiStr.includes('10.1088/') || doiStr.includes('10.1063/') || 
          doiStr.includes('10.1177/') || doiStr.includes('10.1515/') || ['springer', 'taylor & francis', 'taylor and francis', 'routledge', 'emerald', 'mdpi', 'sage', 'iop publishing', 'aip publishing', 'de gruyter'].some(p => publisher.includes(p) || pubName.includes(p))
        ) {
          kat = 'Scopus Q2';
        }
        // Scopus Q3
        else if (doiStr.includes('10.1504/') || publisher.includes('inderscience')) {
          kat = 'Scopus Q3';
        }
      }

      // 5. Cek Database SINTA Nasional Real-time via ISSN & Nama Jurnal
      const rawIssn = (
        item?.ISSN?.[0] ||
        oaw?.primary_location?.source?.issn_l ||
        (oaw?.primary_location?.source?.issn || [])[0] ||
        s2?.publicationVenue?.issn ||
        csl?.ISSN?.[0] || ''
      ).replace(/[^0-9X]/gi, '');
      const formattedIssn = rawIssn.length === 8 ? rawIssn.substring(0, 4) + '-' + rawIssn.substring(4) : rawIssn;

      if (formattedIssn || jurnalBase) {
        try {
          const sintaParams = new URLSearchParams();
          if (formattedIssn) sintaParams.set('issn', formattedIssn);
          if (jurnalBase) sintaParams.set('q', jurnalBase.toLowerCase());

          const sintaRes = await fetch('../api/check_sinta.php?' + sintaParams.toString());
          if (sintaRes.ok) {
            const sintaData = await sintaRes.json();
            if (sintaData && sintaData.sinta_rank && /^SINTA\s*[1-3]$/i.test(sintaData.sinta_rank.trim())) {
              kat = sintaData.sinta_rank.trim();
            }
          }
        } catch (sintaErr) {
          console.warn('SINTA check error:', sintaErr);
        }
      }

      // Default jika belum terdeteksi otomatis
      if (!kat) {
        kat = 'Scopus Q1';
      }

      this.formData.kategoriLuaran = kat;
      this.formData.statusLuaran = 'Sudah Publish / Already Published';

    } catch (err) {
      console.error('Error fetching DOI:', err);
      this.formData.linkLuaran = `https://doi.org/${doi}`;
      alert('Terjadi kendala saat menghubungi database DOI. Link artikel telah diisi otomatis.');
    } finally {
      this.isFetchingDoi = false;
    }
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
    presentasi: false,
    plagiarisme: false
  },
  fileNames: {},
  handleFile(key, event) {
    const files = event.target.files;
    this.berkas[key] = files && files.length > 0;
    this.fileNames[key] = files && files.length > 0 ? files[0].name : '';
  },
  triggerUpload(key) {
    const el = document.getElementById('berkas_' + key);
    if (el) el.click();
  },
  previewModal: {
    open: false,
    title: '',
    url: '',
    type: 'pdf',
    fileName: '',
    fileSize: ''
  },
  previewFile(key, label) {
    const input = document.getElementById('berkas_' + key);
    if (!input || !input.files || input.files.length === 0) {
      alert('Berkas ' + label + ' belum dipilih.');
      return;
    }
    const file = input.files[0];
    const url = URL.createObjectURL(file);
    let type = 'pdf';
    if (file.type.startsWith('image/')) {
      type = 'image';
    } else if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
      type = 'pdf';
    } else {
      type = 'other';
    }
    this.previewModal = {
      open: true,
      title: label,
      url: url,
      type: type,
      fileName: file.name,
      fileSize: (file.size / (1024 * 1024) >= 1 ? (file.size / (1024 * 1024)).toFixed(2) + ' MB' : (file.size / 1024).toFixed(1) + ' KB')
    };
  },
  closePreview() {
    this.previewModal.open = false;
    if (this.previewModal.url) {
      URL.revokeObjectURL(this.previewModal.url);
      this.previewModal.url = '';
    }
  },
  get adminCount() {
    const keys = ['persetujuan','khs','bebasPerpus','bukuSumbangan','bebasAdmin','foto','draftTesis','codeProgram','presentasi','plagiarisme'];
    return keys.filter(k => this.berkas[k]).length;
  },
  get adminPercent() {
    return Math.round((this.adminCount / 10) * 100);
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
      <button type="button" @click="step = <?= $i+1 ?>" class="flex flex-col items-center group cursor-pointer focus:outline-none transition-all hover:scale-105">
        <div :class="step > <?=$i+1?> ? 'bg-emerald-500 text-white shadow-xs' : (step === <?=$i+1?> ? 'ring-4 ring-[#8c0c4c]/25 shadow-md scale-105' : 'hover:bg-slate-300 dark:hover:bg-slate-600')" 
             class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300" 
             :style="step > <?=$i+1?> ? '' : (step === <?=$i+1?> ? 'background:#8c0c4c;color:white' : 'background:#e2e8f0;color:#64748b')">
          <span x-show="step <= <?=$i+1?>"><?= $i+1 ?></span>
          <span x-show="step > <?=$i+1?>">✓</span>
        </div>
        <div class="text-[10px] sm:text-xs mt-1.5 font-medium text-center transition-colors group-hover:text-[#8c0c4c] dark:group-hover:text-pink-400" 
             :class="step === <?=$i+1?> ? 'font-bold text-[#8c0c4c] dark:text-pink-400' : (step > <?=$i+1?> ? 'font-semibold text-slate-800 dark:text-slate-200' : 'text-slate-400')">
          <?= $s ?>
        </div>
      </button>
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
    <div class="bg-[#8c0c4c] text-white px-5 py-3 text-xs font-bold">Bagian 1 dari 3 — Data Diri & Pembimbing</div>
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

      <!-- Ketua Pembimbing -->
      <div class="border-t border-slate-100 dark:border-slate-700 pt-5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">Nama Ketua Pembimbing / Chief Supervisor Name <span class="text-red-500">*</span></label>
        <div class="relative">
          <select x-model="formData.pembimbing1" class="w-full appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all cursor-pointer">
            <option value="">-- Pilih Ketua Pembimbing --</option>
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
          <input type="text" @input="formData.pembimbing1_manual=$event.target.value" placeholder="Ketik nama ketua pembimbing..." class="w-full bg-white dark:bg-slate-900 border border-[#8c0c4c] text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#8c0c4c]/20">
        </div>
      </div>

      <!-- Anggota Pembimbing -->
      <div class="border-t border-slate-100 dark:border-slate-700 pt-5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">Nama Anggota Pembimbing / Co-Supervisor Name <span class="text-slate-400 font-normal">(opsional)</span></label>
        <div class="relative">
          <select x-model="formData.pembimbing2" class="w-full appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all cursor-pointer">
            <option value="">-- Pilih Anggota Pembimbing (jika ada) --</option>
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
          <input type="text" @input="formData.pembimbing2_manual=$event.target.value" placeholder="Ketik nama anggota pembimbing..." class="w-full bg-white dark:bg-slate-900 border border-[#8c0c4c] text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#8c0c4c]/20">
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
        <span>LUARAN ARTIKEL TESIS / THESIS ARTICLE OUTPUT</span>
    </div>
    
    <div class="p-6 space-y-6">
      
      <!-- ✨ DOI Quick-Fill Banner -->
      <div class="bg-gradient-to-b from-[#8c0c4c]/8 via-[#8c0c4c]/4 to-transparent dark:from-[#8c0c4c]/20 dark:via-[#8c0c4c]/10 dark:to-transparent border border-[#8c0c4c]/20 dark:border-[#8c0c4c]/30 rounded-2xl p-6 text-center space-y-4">
        <div class="flex flex-col items-center justify-center gap-2">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#8c0c4c] to-[#c41e73] flex items-center justify-center text-white shadow-md mb-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          </div>
          <div>
            <h4 class="text-sm font-bold text-[#8c0c4c] dark:text-[#f06ea4]">Tarik Otomatis Metadata Luaran via DOI</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-md mx-auto">Masukkan DOI artikel Anda untuk mengisi judul artikel, nama jurnal, link, dan level indeksasi secara instan.</p>
          </div>
        </div>

        <!-- DOI Input & Button Centered -->
        <div class="pt-1 max-w-xl mx-auto">
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <div class="relative flex-1">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
              </div>
              <input type="text" x-model="formData.doi" @keydown.enter.prevent="fetchDoi()" placeholder="Contoh: 10.1016/j.jbusres.2023.114132 atau link doi.org" 
                class="w-full bg-white dark:bg-slate-900 border border-[#8c0c4c]/30 dark:border-[#8c0c4c]/40 text-slate-900 dark:text-white rounded-xl pl-10 pr-4 py-2.5 text-xs placeholder-slate-400 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all shadow-xs">
            </div>
            <button type="button" @click="fetchDoi()" :disabled="isFetchingDoi"
              class="px-6 py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#c41e73] hover:from-[#a3155b] hover:to-[#d4217f] text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm hover:shadow-md disabled:opacity-50 cursor-pointer flex-shrink-0">
              <template x-if="!isFetchingDoi">
                <span class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                  Tarik Data
                </span>
              </template>
              <template x-if="isFetchingDoi">
                <span class="flex items-center gap-1.5">
                  <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  Mencari...
                </span>
              </template>
            </button>
          </div>
        </div>
      </div>

      <!-- Judul Artikel Luaran -->
      <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
          Judul Artikel / Publikasi (Paper Title) <span class="text-red-500">*</span>
        </label>
        <textarea x-model="formData.judulArtikel" rows="2" placeholder="Masukkan judul lengkap artikel ilmiah Anda..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all"></textarea>
      </div>

      <!-- Nama Jurnal / Prosiding -->
      <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
          Nama Jurnal / Penerbit / Prosiding (Journal / Proceeding Name) <span class="text-red-500">*</span>
        </label>
        <input type="text" x-model="formData.namaJurnal" placeholder="Contoh: International Journal of Electrical and Computer Engineering..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
      </div>

      <!-- Status Luaran Artikel -->
      <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
          Status Luaran Artikel / <span class="font-normal italic text-slate-500">Article Output Status</span> <span class="text-red-500">*</span>
        </label>
        <div class="space-y-2.5">
          <?php
          $statusOptions = [
            'Sudah Publish / Already Published',
            'ACC / Accepted',
            'Sedang Proses Review / Currently Under Review'
          ];
          foreach ($statusOptions as $idx => $opt):
          ?>
          <label class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition" :class="formData.statusLuaran === '<?= addslashes($opt) ?>' ? 'bg-[#8c0c4c]/5 border-[#8c0c4c] dark:border-[#8c0c4c]' : ''">
            <input type="radio" name="status_luaran_radio" value="<?= htmlspecialchars($opt) ?>" x-model="formData.statusLuaran" class="w-4 h-4 text-[#8c0c4c] focus:ring-[#8c0c4c]">
            <span class="text-sm font-medium text-slate-800 dark:text-slate-200"><?= htmlspecialchars($opt) ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Tingkat / Level Indeksasi Luaran (Scopus, SINTA, dll) -->
      <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
          Tingkat / Level Indeksasi Luaran / <span class="font-normal italic text-slate-500">Article Indexation Level</span> <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <select x-model="formData.kategoriLuaran" class="w-full appearance-none bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 pr-10 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all cursor-pointer">
            <optgroup label="⭐ Terindeks Scopus & Web of Science (WoS)">
              <option value="Scopus Q1">⭐ Scopus Q1</option>
              <option value="Scopus Q2">⭐ Scopus Q2</option>
              <option value="Scopus Q3">⭐ Scopus Q3</option>
              <option value="Scopus Q4">⭐ Scopus Q4</option>
              <option value="Scopus (Non-Q)">⭐ Scopus (Non-Q)</option>
              <option value="Jurnal Internasional Bereputasi (WoS/Scopus)">⭐ Jurnal Internasional Bereputasi (WoS/Scopus)</option>
              <option value="Prosiding Internasional (Scopus/IEEE)">⭐ Prosiding Internasional (Scopus/IEEE)</option>
            </optgroup>
            <optgroup label="🔵 Akreditasi SINTA (Nasional Bereputasi)">
              <option value="SINTA 1">🔵 SINTA 1 (Nasional Terakreditasi Peringkat 1)</option>
              <option value="SINTA 2">🔵 SINTA 2 (Nasional Terakreditasi Peringkat 2)</option>
              <option value="SINTA 3">🔵 SINTA 3 (Nasional Terakreditasi Peringkat 3)</option>
            </optgroup>
            <option value="__other__">Lainnya (isi manual)</option>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>
        <div x-show="formData.kategoriLuaran==='__other__'" x-transition class="mt-2">
          <input type="text" x-model="formData.kategoriLuaranManual" placeholder="Tuliskan level / indeksasi jurnal..." class="w-full bg-white dark:bg-slate-900 border border-[#8c0c4c] text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#8c0c4c]/20">
        </div>
      </div>

      <!-- Link Luaran Artikel -->
      <div>
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
          Link Luaran Artikel / <span class="font-normal italic text-slate-500">Article Output Link</span>
        </label>
        <input type="url" x-model="formData.linkLuaran" placeholder="https://..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition-all">
      </div>

      <!-- Upload File Jurnal / Manuskrip -->
      <div x-data="{ showTip: false }" class="border border-slate-200 dark:border-slate-700 rounded-2xl p-4.5 transition-all" :class="berkas.jurnal ? 'bg-emerald-50/70 border-emerald-300 dark:bg-emerald-950/20' : 'bg-slate-50/50 dark:bg-slate-900/40'">
        <div @click="showTip = !showTip" class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 cursor-pointer select-none group">
          <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-pink-900/30 dark:text-pink-300 text-xs font-bold flex items-center justify-center flex-shrink-0">📄</span>
            <div class="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-[#8c0c4c] transition-colors">
              Upload File Jurnal / <span class="font-normal italic text-slate-500">Upload Manuskrip</span> <span class="text-red-500">*</span>
            </div>
          </div>
          <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all self-start sm:self-auto"
               :class="showTip ? 'bg-[#8c0c4c] text-white shadow-xs' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 group-hover:opacity-90'">
            <span>📍 Publisher Jurnal</span>
            <span class="text-[9px]" x-text="showTip ? 'Tutup Panduan' : '💡 Panduan'"></span>
            <svg class="w-3 h-3 transition-transform duration-200" :class="showTip ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>

        <div x-show="showTip" 
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             style="display:none;"
             class="mb-3">
          <div class="text-[11px] text-slate-700 dark:text-slate-300 bg-amber-50/80 dark:bg-amber-950/30 p-3 rounded-xl border border-amber-200/80 dark:border-amber-800/60 leading-relaxed flex items-start gap-2.5 shadow-xs">
            <span class="text-base flex-shrink-0">💡</span>
            <div>
              <span class="font-bold text-amber-900 dark:text-amber-300">Cara Mendapatkan:</span>
              <p class="mt-0.5 text-slate-700 dark:text-slate-300">File naskah artikel publikasi ilmiah lengkap (PDF/DOCX) yang disubmit ke jurnal terindeks Scopus atau SINTA 1-3. Jika sudah terbit, sertakan naskah versi cetak/PDF dari jurnal.</p>
            </div>
          </div>
        </div>

        <div @click.stop class="pt-1">
          <input type="file" name="berkas_jurnal" id="berkas_jurnal" accept=".pdf,.doc,.docx" @change="handleFile('jurnal', $event)" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#8c0c4c]/10 file:text-[#8c0c4c] hover:file:bg-[#8c0c4c]/20 dark:file:bg-slate-700 dark:file:text-slate-300 transition cursor-pointer">
        </div>
      </div>

      <!-- Upload Bukti Pembayaran Jurnal -->
      <div x-data="{ showTip: false }" class="border border-slate-200 dark:border-slate-700 rounded-2xl p-4.5 transition-all" :class="berkas.buktiBayarJurnal ? 'bg-emerald-50/70 border-emerald-300 dark:bg-emerald-950/20' : 'bg-slate-50/50 dark:bg-slate-900/40'">
        <div @click="showTip = !showTip" class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 cursor-pointer select-none group">
          <div class="flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-pink-900/30 dark:text-pink-300 text-xs font-bold flex items-center justify-center flex-shrink-0">💳</span>
            <div class="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-[#8c0c4c] transition-colors">
              Upload Bukti Pembayaran Jurnal / <span class="font-normal italic text-slate-500">Upload Journal Payment Proof</span> <span class="text-red-500">*</span>
            </div>
          </div>
          <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold transition-all self-start sm:self-auto"
               :class="showTip ? 'bg-[#8c0c4c] text-white shadow-xs' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 group-hover:opacity-90'">
            <span>📍 Publisher Jurnal</span>
            <span class="text-[9px]" x-text="showTip ? 'Tutup Panduan' : '💡 Panduan'"></span>
            <svg class="w-3 h-3 transition-transform duration-200" :class="showTip ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>

        <div x-show="showTip" 
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             style="display:none;"
             class="mb-3">
          <div class="text-[11px] text-slate-700 dark:text-slate-300 bg-amber-50/80 dark:bg-amber-950/30 p-3 rounded-xl border border-amber-200/80 dark:border-amber-800/60 leading-relaxed flex items-start gap-2.5 shadow-xs">
            <span class="text-base flex-shrink-0">💡</span>
            <div>
              <span class="font-bold text-amber-900 dark:text-amber-300">Cara Mendapatkan:</span>
              <p class="mt-0.5 text-slate-700 dark:text-slate-300">Bukti transfer / invoice pembayaran Article Processing Charge (APC) ke rekening pengelola jurnal ilmiah atau surat bebas biaya / waiver jika gratis.</p>
            </div>
          </div>
        </div>

        <div @click.stop class="pt-1">
          <input type="file" name="berkas_bukti_bayar_jurnal" id="berkas_bukti_bayar_jurnal" accept=".pdf,.jpg,.jpeg,.png" @change="handleFile('buktiBayarJurnal', $event)" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#8c0c4c]/10 file:text-[#8c0c4c] hover:file:bg-[#8c0c4c]/20 dark:file:bg-slate-700 dark:file:text-slate-300 transition cursor-pointer">
        </div>
      </div>
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


  <!-- 🎓 Judul Tesis Card -->
  <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-7 mb-6">
    <div class="flex items-center gap-3 mb-3">
      <div class="w-10 h-10 rounded-xl bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-pink-900/30 dark:text-pink-300 flex items-center justify-center text-xl shadow-xs">
        🎓
      </div>
      <div>
        <label class="block text-sm font-bold text-slate-800 dark:text-slate-100">
          Judul Tesis Lengkap / <span class="font-normal italic text-slate-500">Thesis Title</span> <span class="text-red-500">*</span>
        </label>
        <p class="text-xs text-slate-500 dark:text-slate-400">Pastikan judul sesuai dengan naskah final yang telah disetujui para pembimbing.</p>
      </div>
    </div>
    <textarea x-model="formData.judulTesis" rows="3" placeholder="Masukkan judul tesis lengkap Anda..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:border-[#8c0c4c] focus:ring-4 focus:ring-[#8c0c4c]/15 transition-all"></textarea>
  </div>

  <!-- 📁 9 Dokumen Administrasi Grid -->
  <?php
  $lampiranUrlLink = $lampiranUrl ?? 'https://drive.google.com/drive/u/0/folders/1ZbSRYjiSc4vaPMo-oRRmGwgTe8UUCa5D';
  $admin_docs = [
    [
      'key' => 'persetujuan',
      'label' => 'Upload Persetujuan Pembimbing / Upload Supervisor Approval',
      'sublabel' => 'Supervisor Approval Letter',
      'lokasi' => 'Ketua & Anggota Pembimbing',
      'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
      'cara' => 'Mintakan persetujuan / tanda tangan resmi dari Ketua & Anggota Pembimbing setelah naskah tesis siap diuji.',
      'link' => $lampiranUrlLink,
      'link_label' => '📥 Klik disini untuk Unduh Template Form Persetujuan (Google Drive) ↗',
      'format' => '.pdf',
      'format_text' => 'PDF'
    ],
    [
      'key' => 'khs',
      'label' => 'Upload KHS Sementara Semester 1 - 3 / Upload KHS for Semesters 1-3',
      'sublabel' => 'Semester 1-3 Academic Transcript',
      'lokasi' => 'Bagian Akademik / SIAKAD',
      'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
      'cara' => 'Unduh rekap KHS semester 1 s/d 3 melalui portal SIAKAD Nusa Putra atau minta cetak transkrip nilai ke staf Akademik Pascasarjana.',
      'link' => 'https://siakad.nusaputra.ac.id',
      'link_label' => '🌐 Klik disini untuk Buka Portal SIAKAD (siakad.nusaputra.ac.id) ↗',
      'format' => '.pdf',
      'format_text' => 'PDF'
    ],
    [
      'key' => 'bebasPerpus',
      'label' => 'Upload Surat Bebas Pinjam Perpustakaan / Upload Library Clearance Letter',
      'sublabel' => 'Library Clearance Letter',
      'lokasi' => 'Perpustakaan Universitas Nusa Putra',
      'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
      'cara' => 'Kunjungi loket Perpustakaan Pusat NPU untuk memastikan tidak ada tanggungan pinjaman buku dan meminta Surat Bebas Pinjam.',
      'format' => '.pdf',
      'format_text' => 'PDF'
    ],
    [
      'key' => 'bukuSumbangan',
      'label' => 'Upload Surat Bukti Penyerahan Buku Sumbangan sebanyak 3 Buah dari Bag. Perpustakaan Universitas Nusa Putra / Upload Letter of Receipt for the Submission of 3 Donated Books',
      'sublabel' => 'Receipt of 3 Donated Books to NPU Library',
      'lokasi' => 'Perpustakaan Universitas Nusa Putra',
      'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
      'cara' => 'Serahkan 3 buah buku referensi ke Bagian Perpustakaan NPU untuk mendapatkan tanda terima resmi penyerahan buku.',
      'format' => '.pdf',
      'format_text' => 'PDF'
    ],
    [
      'key' => 'bebasAdmin',
      'label' => 'Upload Bukti Bebas Administrasi / Upload Proof of Administrative Clearance',
      'sublabel' => 'Administrative & Financial Clearance',
      'lokasi' => 'Student Administration Service Unit (SASU)',
      'badge' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
      'cara' => 'Silahkan datang langsung ke loket Student Administration Service Unit (SASU) untuk menyelesaikan administrasi perkuliahan/sidang dan memperoleh Surat Bebas Administrasi.',
      'format' => '.pdf',
      'format_text' => 'PDF'
    ],
    [
      'key' => 'foto',
      'label' => 'Upload File Foto Ukuran 4x6 Background Merah (Pria Berjas Hitam Berdasi dan Wanita Memakai Blazer Hitam) / Upload 4x6 Red Background Photo File',
      'sublabel' => 'Formal 4x6 Photo (Jas Hitam & Dasi / Blazer)',
      'lokasi' => 'Studio Foto / Mandiri',
      'badge' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
      'cara' => 'Foto formal ukuran 4x6 latar merah: Pria memakai jas hitam + kemeja putih + dasi. Wanita memakai blazer hitam + kemeja putih rapi.',
      'format' => '.jpg,.jpeg,.png',
      'format_text' => 'JPG / PNG'
    ],
    [
      'key' => 'draftTesis',
      'label' => 'Upload Draft Tesis / Upload Thesis Draft',
      'sublabel' => 'Full Thesis Manuscript (Bab 1 s/d Penutup)',
      'lokasi' => 'Mahasiswa',
      'badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
      'cara' => 'Upload naskah tesis lengkap dari Bab 1 s/d Bab 5 (Penutup) beserta daftar pustaka dan lampiran.',
      'format' => '.pdf',
      'format_text' => 'PDF'
    ],
    [
      'key' => 'codeProgram',
      'label' => 'Upload Code Program (.zip)',
      'sublabel' => 'Source Code / Project Archive',
      'lokasi' => 'Mahasiswa',
      'badge' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
      'cara' => 'Kompres seluruh source code / program / model data yang Anda kembangkan menjadi satu file berekstensi .zip.',
      'format' => '.zip',
      'format_text' => 'ZIP'
    ],
    [
      'key' => 'presentasi',
      'label' => 'Upload Presentasi Sidang / Upload Defense Presentation',
      'sublabel' => 'Thesis Defense Presentation Slides',
      'lokasi' => 'Mahasiswa',
      'badge' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300',
      'cara' => 'Siapkan file paparan PowerPoint (PPTX / PDF) yang memuat novelty riset, metodologi, hasil, dan kesimpulan.',
      'format' => '.pptx,.pdf',
      'format_text' => 'PDF / PPTX'
    ],
    [
      'key' => 'plagiarisme',
      'label' => 'Upload Hasil Cek Plagiarisme (Turnitin) / Upload Plagiarism Check Result',
      'sublabel' => 'Plagiarism Check Result / Turnitin Certificate',
      'lokasi' => 'Perpustakaan Universitas Nusa Putra',
      'badge' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
      'cara' => 'Unggah lembar hasil cek plagiarisme / similarity index naskah tesis (Turnitin) resmi dari Perpustakaan Nusa Putra (maksimal toleransi similarity 20-25%).',
      'format' => '.pdf',
      'format_text' => 'PDF'
    ],
  ];
  ?>

  <div class="space-y-4 mb-6">
    <?php foreach($admin_docs as $idx => $doc): ?>
    <div x-data="{ showTip: false }" 
         class="bg-white dark:bg-slate-800 rounded-3xl border transition-all duration-300 overflow-hidden shadow-xs hover:shadow-lg"
         :class="berkas.<?= $doc['key'] ?> ? 'border-emerald-400 dark:border-emerald-600 ring-2 ring-emerald-400/20 bg-emerald-50/20' : 'border-slate-200 dark:border-slate-700'">
      
      <!-- Card Top / Clickable Header -->
      <div @click="showTip = !showTip" class="p-5 md:p-6 cursor-pointer select-none">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          
          <!-- Number & Document Title -->
          <div class="flex items-start sm:items-center gap-3.5 flex-1">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-extrabold flex-shrink-0 shadow-xs transition-transform duration-200"
                 :class="berkas.<?= $doc['key'] ?> ? 'bg-emerald-600 text-white scale-105 shadow-emerald-200' : 'bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] text-white'">
              <template x-if="berkas.<?= $doc['key'] ?>">
                <span>✓</span>
              </template>
              <template x-if="!berkas.<?= $doc['key'] ?>">
                <span><?= $idx + 1 ?></span>
              </template>
            </div>

            <div>
              <h4 class="text-xs md:text-sm font-bold text-slate-800 dark:text-slate-100">
                <?= htmlspecialchars($doc['label']) ?> <span class="text-red-500">*</span>
              </h4>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 italic mt-0.5">
                <?= htmlspecialchars($doc['sublabel']) ?>
              </p>
            </div>
          </div>

          <!-- Badges & Action Toggle -->
          <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
            <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold <?= $doc['badge'] ?>">
              📍 <?= htmlspecialchars($doc['lokasi']) ?>
            </span>
            <span class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-mono font-bold">
              <?= $doc['format_text'] ?>
            </span>
            <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 transition-transform duration-200"
                 :class="showTip ? 'rotate-180 bg-[#8c0c4c] text-white' : ''">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </div>

        </div>

        <!-- File Upload Section on the LEFT -->
        <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-700/60 flex flex-col sm:flex-row sm:items-center justify-start gap-3.5">
          
          <!-- File Upload Input Trigger Button (Left Aligned) -->
          <div @click.stop class="relative flex-shrink-0">
            <label for="berkas_<?= $doc['key'] ?>" 
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer shadow-xs hover:shadow-md"
                   :class="berkas.<?= $doc['key'] ?> ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] hover:from-[#a3155b] hover:to-[#c41e73] text-white'">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              <span x-text="berkas.<?= $doc['key'] ?> ? 'Ganti Berkas' : 'Pilih & Upload File'"></span>
            </label>
            <input type="file" name="berkas_<?= $doc['key'] ?>" id="berkas_<?= $doc['key'] ?>" accept="<?= $doc['format'] ?>" 
                   @change="handleFile('<?= $doc['key'] ?>', $event)" class="sr-only">
          </div>

          <!-- Status message (Next to upload button) -->
          <div class="text-xs">
            <template x-if="berkas.<?= $doc['key'] ?>">
              <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1.5 rounded-xl border border-emerald-200 dark:border-emerald-800">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Berkas: <strong class="underline" x-text="fileNames.<?= $doc['key'] ?> || 'File Terpilih'"></strong></span>
              </div>
            </template>
            <template x-if="!berkas.<?= $doc['key'] ?>">
              <span class="text-slate-400 dark:text-slate-500 flex items-center gap-1.5 text-[11px]">
                <span>⚠️</span> <span>Belum ada berkas dipilih (Format: <?= $doc['format_text'] ?>)</span>
              </span>
            </template>
          </div>

        </div>
      </div>

      <!-- Expandable Animated Panduan & Action Buttons -->
      <div x-show="showTip" 
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="opacity-0 -translate-y-2"
           x-transition:enter-end="opacity-100 translate-y-0"
           x-transition:leave="transition ease-in duration-200"
           x-transition:leave-start="opacity-100 translate-y-0"
           x-transition:leave-end="opacity-0 -translate-y-2"
           style="display:none;"
           class="border-t border-slate-100 dark:border-slate-700 bg-amber-50/50 dark:bg-amber-950/20 p-5 md:p-6">
        
        <div class="flex items-start gap-3.5">
          <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 flex items-center justify-center text-base flex-shrink-0 mt-0.5">
            💡
          </div>
          <div class="space-y-3 flex-1">
            <div>
              <h5 class="text-xs font-bold text-amber-900 dark:text-amber-200 uppercase tracking-wider">Panduan & Cara Mendapatkan Dokumen Ini:</h5>
              <p class="text-xs md:text-sm text-slate-700 dark:text-slate-300 leading-relaxed mt-1">
                <?= htmlspecialchars($doc['cara']) ?>
              </p>
            </div>

            <?php if (!empty($doc['link'])): ?>
            <div class="pt-1">
              <a href="<?= htmlspecialchars($doc['link']) ?>" target="_blank" @click.stop 
                 class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] hover:from-[#a3155b] hover:to-[#c41e73] text-white text-xs font-bold shadow-xs hover:shadow-md transition-all">
                <span><?= htmlspecialchars($doc['link_label']) ?></span>
              </a>
            </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div>
    <?php endforeach; ?>
  </div>

  <!-- Motivational Footer Box -->
  <div class="p-6 rounded-3xl bg-gradient-to-r from-[#8c0c4c]/10 via-pink-500/10 to-purple-500/10 border border-[#8c0c4c]/20 text-center mb-6">
    <div class="font-display font-extrabold text-[#8c0c4c] dark:text-pink-400 text-base">✨ Semangat Menuju Sidang Tesis Pascasarjana!</div>
    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 max-w-lg mx-auto">Pastikan semua berkas telah terpilih dengan benar sebelum melangkah ke tahap konfirmasi akhir.</p>
  </div>

  <!-- Bottom Navigation Buttons -->
  <div class="flex justify-between items-center">
    <button @click="step=2" class="flex items-center gap-2 px-6 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Kembali
    </button>
    <button @click="step=4" class="flex items-center gap-2 px-8 py-3 rounded-2xl font-extrabold text-white text-sm hover:shadow-xl transition-all bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] hover:from-[#a3155b] hover:to-[#c41e73] hover:-translate-y-0.5">
      <span>Lanjut ke Konfirmasi</span> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
  </div>
</div>

<!-- ====== STEP 4: Konfirmasi (Redesigned & Premium) ====== -->
<div x-show="step===4" style="display:none" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
  <div x-show="!submitted">
    
    <!-- Top Alert / Notice -->
    <div class="bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-purple-500/10 border border-blue-200 dark:border-blue-800 rounded-3xl p-5 md:p-6 mb-6 flex items-start gap-4">
      <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-xl flex-shrink-0 shadow-md">
        🔍
      </div>
      <div>
        <h4 class="text-sm font-extrabold text-blue-900 dark:text-blue-200">Pratinjau & Konfirmasi Akhir</h4>
        <p class="text-xs text-blue-700 dark:text-blue-300 mt-0.5 leading-relaxed">
          Mohon periksa kembali seluruh ringkasan data diri, data luaran publikasi, serta kelengkapan berkas yang telah Anda lampirkan sebelum mengirim pendaftaran.
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
      
      <!-- Left Column: Data Diri & Luaran (7 cols) -->
      <div class="lg:col-span-7 space-y-6">
        
        <!-- Card 1: Data Diri & Pembimbing (Inline Editable) -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 transition-all"
             :class="editDataDiri ? 'ring-2 ring-[#8c0c4c]/30 border-[#8c0c4c]/40' : ''">
          <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-700/60">
            <div class="flex items-center gap-2.5">
              <span class="w-7 h-7 rounded-xl text-xs font-bold flex items-center justify-center transition-colors"
                    :class="editDataDiri ? 'bg-[#8c0c4c] text-white' : 'bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-pink-900/30 dark:text-pink-300'">
                1
              </span>
              <div>
                <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                  Data Mahasiswa & Pembimbing
                </h4>
                <span x-show="editDataDiri" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold block">
                  Mode Pengeditan Aktif (Ubah langsung di bawah ini)
                </span>
              </div>
            </div>
            
            <button type="button" @click="editDataDiri = !editDataDiri" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                    :class="editDataDiri ? 'bg-emerald-600 text-white shadow-xs hover:bg-emerald-700' : 'text-[#8c0c4c] dark:text-pink-400 bg-[#8c0c4c]/10 dark:bg-pink-900/30 hover:bg-[#8c0c4c]/20'">
              <template x-if="!editDataDiri">
                <span class="flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                  <span>Edit Data Diri</span>
                </span>
              </template>
              <template x-if="editDataDiri">
                <span>✓ Selesai Edit</span>
              </template>
            </button>
          </div>

          <!-- View Mode (Readonly summary) -->
          <div x-show="!editDataDiri" class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
              <span class="text-[11px] text-slate-400 font-semibold block mb-0.5">Nama Lengkap</span>
              <span class="font-bold text-slate-800 dark:text-slate-100" x-text="formData.nama"></span>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
              <span class="text-[11px] text-slate-400 font-semibold block mb-0.5">NIM / Angkatan</span>
              <span class="font-bold text-slate-800 dark:text-slate-100"><span x-text="formData.nim"></span> / <span x-text="formData.angkatan"></span></span>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
              <span class="text-[11px] text-slate-400 font-semibold block mb-0.5">Program Studi</span>
              <span class="font-bold text-slate-800 dark:text-slate-100" x-text="formData.prodi"></span>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
              <span class="text-[11px] text-slate-400 font-semibold block mb-0.5">No WhatsApp</span>
              <span class="font-bold text-slate-800 dark:text-slate-100" x-text="formData.hp || '-'"></span>
            </div>
            <div class="sm:col-span-2 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
              <span class="text-[11px] text-slate-400 font-semibold block mb-0.5">Email Mahasiswa</span>
              <span class="font-bold text-slate-800 dark:text-slate-100" x-text="formData.email || '-'"></span>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
              <span class="text-[10px] uppercase tracking-wider text-purple-600 dark:text-purple-400 font-extrabold block mb-1">Ketua Pembimbing</span>
              <span class="font-bold text-slate-800 dark:text-slate-100" x-text="formData.pembimbing1 === '__other__' ? (formData.pembimbing1_manual || '(Isi manual)') : (formData.pembimbing1 || '-')"></span>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
              <span class="text-[10px] uppercase tracking-wider text-blue-600 dark:text-blue-400 font-extrabold block mb-1">Anggota Pembimbing</span>
              <span class="font-bold text-slate-800 dark:text-slate-100" x-text="(formData.pembimbing2 === '__other2__' ? (formData.pembimbing2_manual || '(Isi manual)') : formData.pembimbing2) || '-'"></span>
            </div>
          </div>

          <!-- Edit Mode (Interactive In-Place Inputs) -->
          <div x-show="editDataDiri" style="display:none;" class="space-y-4 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Angkatan / Cohort <span class="text-red-500">*</span></label>
                <input type="text" x-model="formData.angkatan" placeholder="Contoh: 2024" 
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">No WhatsApp <span class="text-red-500">*</span></label>
                <input type="text" x-model="formData.hp" placeholder="Contoh: 08123456789" 
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
              </div>
              <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Email Mahasiswa <span class="text-red-500">*</span></label>
                <input type="email" x-model="formData.email" placeholder="email@nusaputra.ac.id" 
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
              </div>

              <!-- Ketua Pembimbing -->
              <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-purple-700 dark:text-purple-400 mb-1">Ketua Pembimbing <span class="text-red-500">*</span></label>
                <select x-model="formData.pembimbing1" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 cursor-pointer">
                  <option value="">-- Pilih Ketua Pembimbing --</option>
                  <?php foreach($dosenList as $d): ?>
                  <option value="<?= e($d['nama']) ?>"><?= e($d['nama']) ?><?= $d['nidn'] ? ' (' . e($d['nidn']) . ')' : '' ?></option>
                  <?php endforeach; ?>
                  <option value="__other__">Lainnya (isi manual)</option>
                </select>
                <div x-show="formData.pembimbing1==='__other__'" class="mt-2">
                  <input type="text" x-model="formData.pembimbing1_manual" placeholder="Ketik nama ketua pembimbing..." class="w-full bg-white dark:bg-slate-900 border border-[#8c0c4c] text-slate-800 dark:text-slate-100 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#8c0c4c]/20">
                </div>
              </div>

              <!-- Anggota Pembimbing -->
              <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-blue-700 dark:text-blue-400 mb-1">Anggota Pembimbing <span class="text-slate-400 font-normal">(opsional)</span></label>
                <select x-model="formData.pembimbing2" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 cursor-pointer">
                  <option value="">-- Pilih Anggota Pembimbing (jika ada) --</option>
                  <?php foreach($dosenList as $d): ?>
                  <option value="<?= e($d['nama']) ?>"><?= e($d['nama']) ?><?= $d['nidn'] ? ' (' . e($d['nidn']) . ')' : '' ?></option>
                  <?php endforeach; ?>
                  <option value="__other2__">Lainnya (isi manual)</option>
                </select>
                <div x-show="formData.pembimbing2==='__other2__'" class="mt-2">
                  <input type="text" x-model="formData.pembimbing2_manual" placeholder="Ketik nama anggota pembimbing..." class="w-full bg-white dark:bg-slate-900 border border-[#8c0c4c] text-slate-800 dark:text-slate-100 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#8c0c4c]/20">
                </div>
              </div>
            </div>

            <button type="button" @click="editDataDiri = false" 
                    class="w-full py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] hover:from-[#a3155b] hover:to-[#c41e73] text-white font-bold rounded-xl text-xs shadow-xs transition">
              ✓ Simpan & Selesai Ubah Data Diri
            </button>
          </div>

        </div>

        <!-- Card 2: Judul Tesis & Luaran Publikasi (Inline Editable) -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 transition-all"
             :class="editLuaran ? 'ring-2 ring-[#8c0c4c]/30 border-[#8c0c4c]/40' : ''">
          <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-700/60">
            <div class="flex items-center gap-2.5">
              <span class="w-7 h-7 rounded-xl text-xs font-bold flex items-center justify-center transition-colors"
                    :class="editLuaran ? 'bg-[#8c0c4c] text-white' : 'bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-pink-900/30 dark:text-pink-300'">
                2
              </span>
              <div>
                <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                  Judul Tesis & Luaran Publikasi
                </h4>
                <span x-show="editLuaran" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold block">
                  Mode Pengeditan Aktif (Ubah judul & luaran di bawah ini)
                </span>
              </div>
            </div>

            <button type="button" @click="editLuaran = !editLuaran" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all"
                    :class="editLuaran ? 'bg-emerald-600 text-white shadow-xs hover:bg-emerald-700' : 'text-[#8c0c4c] dark:text-pink-400 bg-[#8c0c4c]/10 dark:bg-pink-900/30 hover:bg-[#8c0c4c]/20'">
              <template x-if="!editLuaran">
                <span class="flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                  <span>Edit Judul & Luaran</span>
                </span>
              </template>
              <template x-if="editLuaran">
                <span>✓ Selesai Edit</span>
              </template>
            </button>
          </div>

          <!-- View Mode (Readonly summary) -->
          <div x-show="!editLuaran" class="space-y-3.5 text-xs">
            <!-- Judul Tesis Box -->
            <div class="p-4 rounded-2xl bg-[#8c0c4c]/5 dark:bg-pink-950/20 border border-[#8c0c4c]/20">
              <span class="text-[11px] font-bold text-[#8c0c4c] dark:text-pink-400 block mb-1 uppercase tracking-wider">🎓 Judul Tesis Terdaftar:</span>
              <p class="text-xs md:text-sm font-extrabold text-slate-800 dark:text-slate-100 leading-relaxed italic" x-text="formData.judulTesis || '(Judul belum diisi)'"></p>
            </div>

            <!-- Luaran Publikasi Detail -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
              <div class="sm:col-span-2 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
                <span class="text-[11px] text-slate-400 font-semibold block mb-0.5">Judul Artikel Ilmiah</span>
                <span class="font-bold text-slate-800 dark:text-slate-100" x-text="formData.judulArtikel || '-'"></span>
              </div>
              <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
                <span class="text-[11px] text-slate-400 font-semibold block mb-0.5">Nama Jurnal</span>
                <span class="font-bold text-slate-800 dark:text-slate-100" x-text="formData.namaJurnal || '-'"></span>
              </div>
              <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
                <span class="text-[11px] text-slate-400 font-semibold block mb-0.5">Indeksasi / Kategori</span>
                <span class="px-2 py-0.5 rounded-lg bg-pink-100 text-[#8c0c4c] dark:bg-pink-900/40 dark:text-pink-300 font-bold inline-block text-[11px]" x-text="formData.kategoriLuaran === '__other__' ? (formData.kategoriLuaranManual || 'Lainnya') : formData.kategoriLuaran"></span>
              </div>
              <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
                <span class="text-[11px] text-slate-400 font-semibold block mb-0.5">Status Publikasi</span>
                <span class="font-bold text-slate-800 dark:text-slate-100" x-text="formData.statusLuaran"></span>
              </div>
              <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
                <span class="text-[11px] text-slate-400 font-semibold block mb-0.5">Nomor DOI</span>
                <span class="font-mono font-bold text-slate-800 dark:text-slate-100 truncate block" x-text="formData.doi || '-'"></span>
              </div>
              <div x-show="formData.linkLuaran" class="sm:col-span-2 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-800">
                <span class="text-[11px] text-slate-400 font-semibold block mb-0.5">URL / Link Publikasi</span>
                <a :href="formData.linkLuaran" target="_blank" class="text-blue-600 dark:text-blue-400 font-bold underline truncate block" x-text="formData.linkLuaran"></a>
              </div>
            </div>
          </div>

          <!-- Edit Mode (Interactive In-Place Inputs) -->
          <div x-show="editLuaran" style="display:none;" class="space-y-4 text-xs">
            
            <!-- Judul Tesis Textarea -->
            <div>
              <label class="block text-[11px] font-bold text-[#8c0c4c] dark:text-pink-400 mb-1 uppercase tracking-wider">
                🎓 Judul Tesis Terdaftar <span class="text-red-500">*</span>
              </label>
              <textarea x-model="formData.judulTesis" rows="3" placeholder="Tuliskan judul naskah tesis lengkap..." 
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 transition"></textarea>
            </div>

            <!-- DOI & Fetch Button -->
            <div class="p-3.5 rounded-2xl bg-[#8c0c4c]/5 border border-[#8c0c4c]/20 space-y-2">
              <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                Nomor DOI Artikel (Tarik Otomatis)
              </label>
              <div class="flex gap-2">
                <input type="text" x-model="formData.doi" placeholder="Contoh: 10.1016/j.jbusres.2023.114132" 
                       class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#8c0c4c]/20">
                <button type="button" @click="fetchDoi()" :disabled="isFetchingDoi" 
                        class="px-4 py-2 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white font-bold rounded-xl text-xs hover:shadow-xs transition disabled:opacity-50 flex-shrink-0">
                  <span x-text="isFetchingDoi ? 'Menarik...' : 'Tarik DOI'"></span>
                </button>
              </div>
            </div>

            <!-- Judul Artikel & Nama Jurnal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
              <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Judul Artikel Ilmiah <span class="text-red-500">*</span></label>
                <input type="text" x-model="formData.judulArtikel" placeholder="Judul artikel publikasi..." 
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Nama Jurnal <span class="text-red-500">*</span></label>
                <input type="text" x-model="formData.namaJurnal" placeholder="Nama jurnal penerbit..." 
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Indeksasi / Kategori <span class="text-red-500">*</span></label>
                <select x-model="formData.kategoriLuaran" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 cursor-pointer">
                  <optgroup label="Scopus (Internasional Bereputasi)">
                    <option value="Scopus Q1">Scopus Q1</option>
                    <option value="Scopus Q2">Scopus Q2</option>
                    <option value="Scopus Q3">Scopus Q3</option>
                    <option value="Scopus Q4">Scopus Q4</option>
                    <option value="Scopus Non-Q">Scopus Non-Q / Conference</option>
                  </optgroup>
                  <optgroup label="SINTA (Nasional Terakreditasi)">
                    <option value="SINTA 1">SINTA 1</option>
                    <option value="SINTA 2">SINTA 2</option>
                    <option value="SINTA 3">SINTA 3</option>
                  </optgroup>
                  <option value="__other__">Lainnya (isi manual)</option>
                </select>
                <div x-show="formData.kategoriLuaran==='__other__'" class="mt-2">
                  <input type="text" x-model="formData.kategoriLuaranManual" placeholder="Tuliskan level / indeksasi jurnal..." class="w-full bg-white dark:bg-slate-900 border border-[#8c0c4c] text-slate-800 dark:text-slate-100 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#8c0c4c]/20">
                </div>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Status Publikasi <span class="text-red-500">*</span></label>
                <select x-model="formData.statusLuaran" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20 cursor-pointer">
                  <option value="Sudah Publish / Already Published">Sudah Publish / Already Published</option>
                  <option value="Accepted / Letter of Acceptance (LoA)">Accepted / Letter of Acceptance (LoA)</option>
                  <option value="Under Review / Review Process">Under Review / Review Process</option>
                  <option value="Submitted / Dalam Proses Submit">Submitted / Dalam Proses Submit</option>
                </select>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">URL / Link Publikasi</label>
                <input type="url" x-model="formData.linkLuaran" placeholder="https://..." 
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:border-[#8c0c4c] focus:ring-2 focus:ring-[#8c0c4c]/20">
              </div>
            </div>

            <button type="button" @click="editLuaran = false" 
                    class="w-full py-2.5 bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] hover:from-[#a3155b] hover:to-[#c41e73] text-white font-bold rounded-xl text-xs shadow-xs transition">
              ✓ Simpan & Selesai Ubah Judul & Luaran
            </button>
          </div>

        </div>

      </div>

      <!-- Right Column: Berkas Checklist & Final Action (5 cols) -->
      <div class="lg:col-span-5 space-y-6">
        
        <!-- Card 3: Status 12 Berkas Persyaratan -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
          <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-700/60">
            <div class="flex items-center gap-2.5">
              <span class="w-7 h-7 rounded-xl bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-pink-900/30 dark:text-pink-300 text-xs font-bold flex items-center justify-center">
                3
              </span>
              <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                Kelengkapan Berkas
              </h4>
            </div>
            <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-700 text-[10px] font-bold text-slate-700 dark:text-slate-300">
              12 Dokumen
            </span>
          </div>

          <?php
          $allSummaryDocs = [
            ['key'=>'jurnal', 'label'=>'File Artikel / Manuskrip Jurnal'],
            ['key'=>'buktiBayarJurnal', 'label'=>'Bukti Bayar / Invoice Jurnal'],
            ['key'=>'persetujuan', 'label'=>'Lembar Persetujuan Pembimbing'],
            ['key'=>'khs', 'label'=>'KHS Sementara Semester 1 - 3'],
            ['key'=>'bebasPerpus', 'label'=>'Surat Bebas Pinjam Perpustakaan'],
            ['key'=>'bukuSumbangan', 'label'=>'Bukti Sumbangan 3 Buku Perpustakaan'],
            ['key'=>'bebasAdmin', 'label'=>'Bukti Bebas Administrasi SASU'],
            ['key'=>'foto', 'label'=>'Pas Foto Resmi 4x6 Background Merah'],
            ['key'=>'draftTesis', 'label'=>'Draft Naskah Tesis Lengkap'],
            ['key'=>'codeProgram', 'label'=>'Code Program (.zip)'],
            ['key'=>'presentasi', 'label'=>'Slide Presentasi Sidang (PPT/PDF)'],
            ['key'=>'plagiarisme', 'label'=>'Hasil Cek Plagiarisme (Turnitin)']
          ];
          ?>

          <div class="space-y-2.5">
            <?php foreach($allSummaryDocs as $sIdx => $b): ?>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 rounded-2xl transition-all"
                 :class="berkas.<?= $b['key'] ?> ? 'bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800'">
              
              <div class="flex items-start sm:items-center gap-2.5 min-w-0 flex-1">
                <div class="w-5 h-5 rounded-full flex items-center justify-center text-xs flex-shrink-0 mt-0.5 sm:mt-0"
                     :class="berkas.<?= $b['key'] ?> ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-200 dark:bg-slate-700 text-slate-400'">
                  <template x-if="berkas.<?= $b['key'] ?>">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                  </template>
                  <template x-if="!berkas.<?= $b['key'] ?>">
                    <span class="text-[9px]"><?= $sIdx + 1 ?></span>
                  </template>
                </div>

                <div class="min-w-0 flex-1">
                  <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">
                    <?= htmlspecialchars($b['label']) ?>
                  </span>
                  <template x-if="fileNames.<?= $b['key'] ?>">
                    <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-mono truncate block" x-text="fileNames.<?= $b['key'] ?>"></span>
                  </template>
                  <template x-if="!berkas.<?= $b['key'] ?>">
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 italic block">Belum ada berkas</span>
                  </template>
                </div>
              </div>

              <!-- Action buttons per file: Lihat / Ganti / Unggah -->
              <div class="flex items-center gap-1.5 self-end sm:self-center flex-shrink-0">
                <template x-if="berkas.<?= $b['key'] ?>">
                  <div class="flex items-center gap-1">
                    <button type="button" @click="previewFile('<?= $b['key'] ?>', '<?= htmlspecialchars($b['label']) ?>')" 
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      <span>Lihat</span>
                    </button>
                    <button type="button" @click="triggerUpload('<?= $b['key'] ?>')" 
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition">
                      <span>Ubah</span>
                    </button>
                  </div>
                </template>
                <template x-if="!berkas.<?= $b['key'] ?>">
                  <button type="button" @click="triggerUpload('<?= $b['key'] ?>')" 
                          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white shadow-2xs hover:shadow-xs transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Unggah</span>
                  </button>
                </template>
              </div>

            </div>
            <?php endforeach; ?>
          </div>

        </div>

      </div>

    </div>

    <!-- Pernyataan & Checkbox Persetujuan (Unchecked by default) -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-7 mb-6 transition-all"
         :class="setuju ? 'border-[#8c0c4c]/40 ring-2 ring-[#8c0c4c]/10 bg-pink-50/20 dark:bg-pink-950/10' : ''">
      <label class="flex items-start gap-3.5 cursor-pointer select-none">
        <input type="checkbox" x-model="setuju" class="w-5 h-5 rounded-lg text-[#8c0c4c] focus:ring-[#8c0c4c]/20 border-slate-300 dark:border-slate-600 mt-0.5 flex-shrink-0 cursor-pointer">
        <div class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
          <strong class="text-slate-900 dark:text-white font-bold block mb-0.5">Pernyataan Orisinalitas & Kebenaran Berkas <span class="text-red-500">*</span></strong>
          Saya menyatakan dengan sesungguhnya bahwa seluruh data yang diisikan dan dokumen yang diunggah adalah benar, sah, dan orisinal. Apabila di kemudian hari ditemukan pemalsuan dokumen atau pelanggaran etika akademik, saya bersedia menerima sanksi sesuai ketentuan yang berlaku di Universitas Nusa Putra.
        </div>
      </label>
    </div>

    <!-- Hidden Form POST ke Database -->
    <form id="formSidang">
      <input type="hidden" name="mahasiswa_id" value="<?= $mhsId ?>">
      <input type="hidden" name="nama" :value="formData.nama">
      <input type="hidden" name="nim" :value="formData.nim">
      <input type="hidden" name="angkatan" :value="formData.angkatan">
      <input type="hidden" name="email" :value="formData.email">
      <input type="hidden" name="no_hp" :value="formData.hp">
      <input type="hidden" name="pembimbing1" :value="formData.pembimbing1 === '__other__' ? formData.pembimbing1_manual : formData.pembimbing1">
      <input type="hidden" name="pembimbing2" :value="formData.pembimbing2 === '__other2__' ? formData.pembimbing2_manual : formData.pembimbing2">
      <input type="hidden" name="doi" :value="formData.doi">
      <input type="hidden" name="judul_artikel" :value="formData.judulArtikel">
      <input type="hidden" name="nama_jurnal" :value="formData.namaJurnal">
      <input type="hidden" name="status_luaran" :value="formData.statusLuaran">
      <input type="hidden" name="kategori_luaran" :value="formData.kategoriLuaran === '__other__' ? formData.kategoriLuaranManual : formData.kategoriLuaran">
      <input type="hidden" name="link_luaran" :value="formData.linkLuaran">
      <input type="hidden" name="judul_tesis" :value="formData.judulTesis">
      <input type="hidden" name="jenis_sidang" value="<?= htmlspecialchars($jenisSidang) ?>">
    </form>

    <!-- Bottom Actions -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
      <button @click="step=3" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>Kembali ke Dokumen Administrasi</span>
      </button>

      <div class="w-full sm:w-auto flex flex-col items-end gap-1.5">
        <button :disabled="!setuju || loading" @click="
          if (!setuju) {
            alert('Silakan centang kotak persetujuan orisinalitas berkas terlebih dahulu.');
            return;
          }
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
              window.open('cetak_pendaftaran_sidang.php?judul=' + encodeURIComponent(formData.judulTesis) + '&angkatan=' + encodeURIComponent(formData.angkatan) + '&pembimbing1=' + encodeURIComponent(formData.pembimbing1 === '__other__' ? formData.pembimbing1_manual : formData.pembimbing1) + '&pembimbing2=' + encodeURIComponent(formData.pembimbing2 === '__other2__' ? formData.pembimbing2_manual : formData.pembimbing2) + '&email=' + encodeURIComponent(formData.email) + '&hp=' + encodeURIComponent(formData.hp), '_blank');
              window.location.href='pendaftaran';
          })
          .catch(err => {
              alert('Terjadi kesalahan saat mengunggah file.');
              loading = false;
          });
        " class="w-full sm:w-auto flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-2xl font-extrabold text-sm transition-all"
           :class="setuju && !loading ? 'bg-gradient-to-r from-[#8c0c4c] via-[#a3155b] to-[#750e41] hover:from-[#a3155b] hover:to-[#8c0c4c] text-white shadow-xl hover:shadow-2xl hover:-translate-y-0.5 cursor-pointer' : 'bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-400 cursor-not-allowed shadow-none opacity-80'">
          <template x-if="!loading">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
          </template>
          <template x-if="loading">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          </template>
          <span x-text="loading ? 'Sedang Mengunggah & Mengirim Data...' : 'Kirim Pendaftaran & Cetak Bukti Pendaftaran'"></span>
        </button>
        <span x-show="!setuju" class="text-[11px] text-amber-600 dark:text-amber-400 font-medium">
          ⚠️ Centang pernyataan di atas untuk mengaktifkan tombol kirim.
        </span>
      </div>
    </div>

  </div>
</div>

<!-- ====== MODAL PRATINJAU DOKUMEN (PREVIEW MODAL) ====== -->
<div x-show="previewModal.open" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display:none;"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/70 backdrop-blur-md">
  
  <div @click.away="closePreview()" 
       x-show="previewModal.open"
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 scale-95 translate-y-4"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 translate-y-4"
       class="bg-white dark:bg-slate-800 w-full max-w-4xl max-h-[90vh] rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col">
    
    <!-- Modal Header -->
    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-9 h-9 rounded-xl bg-[#8c0c4c]/10 text-[#8c0c4c] dark:bg-pink-900/30 dark:text-pink-300 flex items-center justify-center text-lg flex-shrink-0">
          📄
        </div>
        <div class="min-w-0">
          <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 truncate" x-text="previewModal.title"></h4>
          <div class="flex items-center gap-2 text-[11px] text-slate-500 font-mono">
            <span class="truncate" x-text="previewModal.fileName"></span>
            <span>•</span>
            <span x-text="previewModal.fileSize"></span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2 flex-shrink-0">
        <a :href="previewModal.url" target="_blank" 
           class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition">
          <span>Buka di Tab Baru</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
        <button type="button" @click="closePreview()" 
                class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-900/40 dark:hover:text-rose-300 flex items-center justify-center text-slate-500 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    <!-- Modal Content Body -->
    <div class="p-4 sm:p-6 overflow-y-auto flex-1 bg-slate-100 dark:bg-slate-900/60 flex items-center justify-center">
      
      <!-- Preview PDF -->
      <template x-if="previewModal.type === 'pdf'">
        <iframe :src="previewModal.url" class="w-full h-[70vh] rounded-2xl border border-slate-200 dark:border-slate-700 bg-white shadow-inner"></iframe>
      </template>

      <!-- Preview Image -->
      <template x-if="previewModal.type === 'image'">
        <div class="p-2 bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700">
          <img :src="previewModal.url" class="max-h-[68vh] max-w-full rounded-xl mx-auto object-contain">
        </div>
      </template>

      <!-- Preview Other (e.g. .ZIP, .PPTX) -->
      <template x-if="previewModal.type === 'other'">
        <div class="text-center p-8 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 max-w-md shadow-lg space-y-3">
          <div class="w-16 h-16 rounded-2xl bg-[#8c0c4c]/10 text-[#8c0c4c] mx-auto flex items-center justify-center text-3xl">
            📦
          </div>
          <h5 class="text-sm font-bold text-slate-800 dark:text-slate-100">Pratinjau Langsung Tidak Tersedia</h5>
          <p class="text-xs text-slate-500">Berkas ini berekstensi khusus (<span class="font-mono font-bold" x-text="previewModal.fileName.split('.').pop().toUpperCase()"></span>). Anda dapat mengunduh atau membukanya secara lokal untuk memeriksa isinya.</p>
          <div class="pt-2">
            <a :href="previewModal.url" :download="previewModal.fileName" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#8c0c4c] to-[#a3155b] text-white text-xs font-bold shadow-md hover:shadow-lg transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              <span>Unduh Berkas Ini</span>
            </a>
          </div>
        </div>
      </template>

    </div>

    <!-- Modal Footer -->
    <div class="px-6 py-3 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center text-xs text-slate-500">
      <span>💡 Pastikan dokumen yang diunggah jelas dan dapat dibaca oleh Dewan Penguji.</span>
      <button type="button" @click="closePreview()" class="px-4 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold transition">
        Tutup
      </button>
    </div>

  </div>
</div>

<?php require_once 'footer.php'; ?>
