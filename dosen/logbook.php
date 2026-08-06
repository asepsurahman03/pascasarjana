<?php
$pageTitle = 'Approve Logbook';
require_once 'header.php';

$entries = [
  ['mhs'=>'Budi Hermawan','nim'=>'2023MIF002','no'=>11,'tgl'=>'15 Jul 2025',
   'materi'=>'Finalisasi penulisan bab 4 dan 5 — analisis data dan kesimpulan penelitian',
   'catatan_mhs'=>'Sudah revisi sesuai arahan dosen di sesi sebelumnya. Draft final bab 5 dilampirkan.',
   'file'=>true,'status'=>'Menunggu','id'=>'log_budi_11'],
];
$approved = [
  ['mhs'=>'Ahmad Rizki Pratama','nim'=>'2023MIF001','no'=>8,'tgl'=>'08 Jul 2025',
   'materi'=>'Finalisasi bab 4 analisis hasil dan pembahasan','status'=>'Disetujui','feedback'=>'Baik. Tambahkan perbandingan dengan baseline lebih detail.'],
  ['mhs'=>'Ahmad Rizki Pratama','nim'=>'2023MIF001','no'=>7,'tgl'=>'24 Jun 2025',
   'materi'=>'Revisi bab 3 metodologi penelitian','status'=>'Disetujui','feedback'=>'Oke, metodologi sudah komprehensif. Lanjut ke bab 4.'],
  ['mhs'=>'Citra Dewi Santika','nim'=>'2022MIF001','no'=>5,'tgl'=>'01 Jul 2025',
   'materi'=>'Pembahasan literatur review untuk bab 2','status'=>'Revisi','feedback'=>'Tambahkan 3 referensi terbaru (< 5 tahun). Perkuat argumen pada sub-bab 2.3.'],
];
?>

<!-- Notif Banner jika ada pending -->
<?php if(count($entries)): ?>
<div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-4 mb-6 flex items-start gap-3">
  <span class="text-2xl">⏳</span>
  <div>
    <div class="font-semibold text-amber-800 dark:text-amber-300 text-sm">Ada <?= count($entries) ?> entri logbook menunggu persetujuan Anda</div>
    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">Mahasiswa menunggu konfirmasi dari Anda sebelum dapat mendaftar sidang.</p>
  </div>
</div>
<?php endif; ?>

<!-- Pending Approval -->
<?php if(!empty($entries)): ?>
<div class="mb-6">
  <h2 class="font-display font-bold text-slate-800 dark:text-white text-sm mb-3 flex items-center gap-2">
    <span class="w-2 h-2 rounded-full bg-amber-400"></span> Menunggu Persetujuan
  </h2>
  <?php foreach($entries as $e): ?>
  <div class="bg-white dark:bg-slate-800 rounded-2xl border-2 border-amber-200 dark:border-amber-800 shadow-sm overflow-hidden" x-data="{feedback:''}">
    <div class="bg-amber-50 dark:bg-amber-900/20 px-5 py-3 flex items-center justify-between border-b border-amber-200 dark:border-amber-800">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-amber-500 text-white font-bold text-sm flex items-center justify-center"><?= strtoupper(substr($e['mhs'],0,1)) ?></div>
        <div>
          <span class="font-semibold text-sm text-slate-800 dark:text-white"><?= $e['mhs'] ?></span>
          <span class="text-xs text-slate-500 dark:text-slate-400 ml-2"><?= $e['nim'] ?></span>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xs text-slate-500">Sesi #<?= $e['no'] ?> · <?= $e['tgl'] ?></span>
        <?php if($e['file']): ?><span class="text-xs bg-nusa/10 text-nusa px-2 py-0.5 rounded-full font-semibold border border-nusa/20">📄 Ada Lampiran</span><?php endif; ?>
      </div>
    </div>
    <div class="p-5">
      <div class="mb-4">
        <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold mb-1">Materi Bimbingan:</div>
        <p class="text-sm text-slate-800 dark:text-white"><?= $e['materi'] ?></p>
      </div>
      <div class="mb-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/40 border border-slate-200 dark:border-slate-700">
        <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold mb-1">Catatan Mahasiswa:</div>
        <p class="text-xs text-slate-700 dark:text-slate-200 italic"><?= $e['catatan_mhs'] ?></p>
      </div>
      <div class="mb-4">
        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Feedback / Catatan Dosen (opsional)</label>
        <textarea x-model="feedback" rows="2" placeholder="Tulis arahan, pujian, atau permintaan revisi..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa transition-colors"></textarea>
      </div>
      <div class="flex items-center gap-3">
        <button onclick="this.closest('.bg-white').remove();alert('Logbook disetujui! Notifikasi dikirim ke mahasiswa.')" class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-white text-sm transition hover:shadow-lg hover:-translate-y-0.5" style="background:linear-gradient(135deg,#059669,#10b981)">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Setujui Logbook
        </button>
        <button onclick="this.closest('.bg-white').remove();alert('Logbook dikembalikan untuk revisi.')" class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          Minta Revisi
        </button>
        <?php if($e['file']): ?>
        <button class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
          📄 Lihat Lampiran
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Riwayat Logbook -->
<div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
  <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
    <h3 class="font-display font-bold text-slate-800 dark:text-white text-sm flex items-center gap-2">
      <span class="w-2 h-2 rounded-full bg-slate-400"></span> Riwayat Logbook Diproses
    </h3>
    <select class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none">
      <option>Semua Mahasiswa</option>
      <option>Ahmad Rizki Pratama</option>
      <option>Budi Hermawan</option>
      <option>Citra Dewi Santika</option>
    </select>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30">
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Mahasiswa</th>
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Sesi</th>
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tanggal</th>
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Materi</th>
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
          <th class="text-left py-3 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Feedback</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
        <?php foreach($approved as $a): ?>
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
          <td class="py-3 px-4">
            <div class="font-semibold text-xs text-slate-800 dark:text-white"><?= $a['mhs'] ?></div>
            <div class="text-xs text-slate-400"><?= $a['nim'] ?></div>
          </td>
          <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-bold text-xs">#<?= $a['no'] ?></td>
          <td class="py-3 px-4 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap"><?= $a['tgl'] ?></td>
          <td class="py-3 px-4 text-xs text-slate-600 dark:text-slate-300 max-w-xs"><?= $a['materi'] ?></td>
          <td class="py-3 px-4">
            <span class="px-2 py-0.5 rounded-full text-xs font-bold border <?= $a['status']==='Disetujui'?'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800':'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800' ?>">
              <?= $a['status'] ?>
            </span>
          </td>
          <td class="py-3 px-4 text-xs text-slate-500 dark:text-slate-400 italic max-w-xs"><?= $a['feedback'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'footer.php'; ?>
