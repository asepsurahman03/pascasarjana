<?php
$pageTitle = 'Input Nilai Sidang';
require_once 'header.php';

$pending_nilai = [
  ['mhs'=>'Budi Hermawan','nim'=>'2023MIF002','prodi'=>'Magister Informatika',
   'jenis'=>'Sidang Tesis','tgl_sidang'=>'21 Jul 2025','peran'=>'Ketua Penguji',
   'judul'=>'Sistem Rekomendasi Berbasis Collaborative Filtering untuk E-Commerce',
   'nilai_input'=>false],
];
$riwayat_nilai = [
  ['mhs'=>'Hana Safitri','nim'=>'2023MP001','jenis'=>'Seminar Proposal','tgl'=>'05 Jun 2025',
   'peran'=>'Pembimbing','presentasi'=>85,'penguasaan'=>88,'metodologi'=>82,'penulisan'=>80,
   'final'=>'B+','status'=>'Selesai'],
  ['mhs'=>'Irwan Kusuma','nim'=>'2022DIK001','jenis'=>'Ujian Komprehensif','tgl'=>'28 Mei 2025',
   'peran'=>'Penguji','presentasi'=>90,'penguasaan'=>92,'metodologi'=>89,'penulisan'=>87,
   'final'=>'A','status'=>'Selesai'],
];
?>

<!-- Nilai Pending -->
<?php if(!empty($pending_nilai)): ?>
<div class="mb-6">
  <h2 class="font-display font-bold text-slate-800 dark:text-white text-sm mb-3 flex items-center gap-2">
    <span class="w-2 h-2 rounded-full bg-amber-400"></span> Belum Diinput
  </h2>
  <?php foreach($pending_nilai as $p): ?>
  <div class="bg-white dark:bg-slate-800 rounded-2xl border-2 border-amber-200 dark:border-amber-800 shadow-sm overflow-hidden" x-data="{
    presentasi:0, penguasaan:0, metodologi:0, penulisan:0,
    get rerata(){ return ((+this.presentasi + +this.penguasaan + +this.metodologi + +this.penulisan)/4).toFixed(1); },
    get grade(){
      let r = ((+this.presentasi + +this.penguasaan + +this.metodologi + +this.penulisan)/4);
      if(r>=90) return 'A'; if(r>=85) return 'B+'; if(r>=80) return 'B'; if(r>=75) return 'C+';
      if(r>=70) return 'C'; return 'D';
    }
  }">
    <div class="bg-amber-50 dark:bg-amber-900/20 px-5 py-3 border-b border-amber-200 dark:border-amber-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <div>
        <span class="font-semibold text-slate-800 dark:text-white text-sm"><?= $p['mhs'] ?></span>
        <span class="text-xs text-slate-500 ml-2"><?= $p['nim'] ?></span>
        <span class="ml-2 text-xs font-bold px-2 py-0.5 rounded-full bg-nusa/10 text-nusa border border-nusa/20"><?= $p['peran'] ?></span>
      </div>
      <div class="text-xs text-slate-500 dark:text-slate-400"><?= $p['jenis'] ?> · <?= $p['tgl_sidang'] ?></div>
    </div>
    <div class="p-5">
      <p class="text-xs text-slate-500 dark:text-slate-400 italic mb-5">"<?= $p['judul'] ?>"</p>
      <!-- Komponen Nilai -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <?php
        $komponen = [
          ['key'=>'presentasi','label'=>'Presentasi & Komunikasi','bobot'=>'25%'],
          ['key'=>'penguasaan','label'=>'Penguasaan Materi','bobot'=>'30%'],
          ['key'=>'metodologi','label'=>'Metodologi Penelitian','bobot'=>'25%'],
          ['key'=>'penulisan', 'label'=>'Penulisan & Tata Tulis','bobot'=>'20%'],
        ];
        foreach($komponen as $k): ?>
        <div>
          <div class="flex justify-between items-center mb-1.5">
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-200"><?= $k['label'] ?></label>
            <span class="text-xs text-slate-400"><?= $k['bobot'] ?></span>
          </div>
          <div class="flex items-center gap-3">
            <input type="number" x-model="<?= $k['key'] ?>" min="0" max="100" placeholder="0–100"
              class="w-24 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm text-center font-bold focus:outline-none focus:border-nusa transition-colors">
            <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-full h-2">
              <div class="h-2 rounded-full bg-nusa transition-all" :style="`width:${Math.min(<?= $k['key'] ?>,100)}%`"></div>
            </div>
            <span class="text-xs font-bold w-6 text-right" :class="<?= $k['key'] ?> >= 80 ? 'text-emerald-600 dark:text-emerald-400' : (<?= $k['key'] ?> >= 70 ? 'text-amber-600 dark:text-amber-400' : 'text-red-500')" x-text="<?= $k['key'] ?>"></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <!-- Preview Nilai Final -->
      <div class="flex items-center gap-6 p-4 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-200 dark:border-slate-700 mb-4">
        <div class="text-center">
          <div class="font-display font-black text-4xl" :class="rerata >= 80 ? 'text-emerald-600 dark:text-emerald-400' : (rerata >= 70 ? 'text-amber-500' : 'text-red-500')" x-text="rerata">—</div>
          <div class="text-xs text-slate-400 mt-0.5">Nilai Angka</div>
        </div>
        <div class="text-slate-200 dark:text-slate-600 text-3xl">/</div>
        <div class="text-center">
          <div class="font-display font-black text-4xl text-nusa" x-text="grade || '—'">—</div>
          <div class="text-xs text-slate-400 mt-0.5">Nilai Huruf</div>
        </div>
        <div class="flex-1">
          <div class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            A: ≥90 &nbsp;·&nbsp; B+: ≥85 &nbsp;·&nbsp; B: ≥80<br>
            C+: ≥75 &nbsp;·&nbsp; C: ≥70 &nbsp;·&nbsp; D: &lt;70
          </div>
        </div>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Catatan Penguji (wajib diisi)</label>
        <textarea rows="2" placeholder="Komentar singkat tentang kualitas penelitian dan rekomendasi perbaikan..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa transition-colors mb-3"></textarea>
        <button @click="alert('Nilai berhasil disimpan! Mahasiswa akan mendapat notifikasi.')" class="flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-white text-sm transition hover:shadow-lg hover:-translate-y-0.5" style="background:linear-gradient(135deg,#961d5a,#6b1040)">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Simpan & Kirim Nilai
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Riwayat Nilai -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
  <div class="p-5 border-b border-slate-100 dark:border-slate-700">
    <h3 class="font-display font-bold text-slate-800 dark:text-white text-sm">📊 Riwayat Nilai yang Sudah Diinput</h3>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30">
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Mahasiswa</th>
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Kegiatan</th>
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Peran</th>
          <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Presentasi</th>
          <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Penguasaan</th>
          <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Metodologi</th>
          <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Penulisan</th>
          <th class="text-center py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Final</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php foreach($riwayat_nilai as $r): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
          <td class="py-3 px-4"><div class="font-semibold text-xs text-slate-800 dark:text-white"><?= $r['mhs'] ?></div><div class="text-xs text-slate-400"><?= $r['nim'] ?></div></td>
          <td class="py-3 px-4"><div class="text-xs text-slate-700 dark:text-slate-200"><?= $r['jenis'] ?></div><div class="text-xs text-slate-400"><?= $r['tgl'] ?></div></td>
          <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-300"><?= $r['peran'] ?></td>
          <td class="py-3 px-4 text-center text-xs font-semibold text-slate-700 dark:text-slate-200"><?= $r['presentasi'] ?></td>
          <td class="py-3 px-4 text-center text-xs font-semibold text-slate-700 dark:text-slate-200"><?= $r['penguasaan'] ?></td>
          <td class="py-3 px-4 text-center text-xs font-semibold text-slate-700 dark:text-slate-200"><?= $r['metodologi'] ?></td>
          <td class="py-3 px-4 text-center text-xs font-semibold text-slate-700 dark:text-slate-200"><?= $r['penulisan'] ?></td>
          <td class="py-3 px-4 text-center">
            <span class="font-display font-black text-4xl <?= $r['final']==='A'?'text-emerald-600 dark:text-emerald-400':($r['final']==='B+'?'text-nusa':'text-slate-600 dark:text-slate-300') ?>"><?= $r['final'] ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'footer.php'; ?>
