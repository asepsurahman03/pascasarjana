<?php
$pageTitle = 'Vote Jadwal Sidang';
require_once 'header.php';

// Data simulasi
$sidang = [
  'mahasiswa' => 'Budi Hermawan',
  'nim'       => '2023MIF002',
  'jenis'     => 'Sidang Tesis (S2)',
  'judul'     => 'Sistem Rekomendasi Berbasis Collaborative Filtering untuk E-Commerce',
  'prodi'     => 'Magister Informatika',
];
$dosen_penguji = 'Dr. Ahmad Fauzi, M.Kom';
$batas_vote = '18 Juli 2025, pukul 23:59 WIB';

$slots = [
  ['tgl'=>'Senin, 21 Jul 2025',  'jam'=>'09.00–11.00', 'votes'=>3, 'voted'=>false],
  ['tgl'=>'Senin, 21 Jul 2025',  'jam'=>'13.00–15.00', 'votes'=>1, 'voted'=>false],
  ['tgl'=>'Selasa, 22 Jul 2025', 'jam'=>'09.00–11.00', 'votes'=>4, 'voted'=>false],
  ['tgl'=>'Rabu, 23 Jul 2025',   'jam'=>'09.00–11.00', 'votes'=>2, 'voted'=>false],
  ['tgl'=>'Kamis, 24 Jul 2025',  'jam'=>'13.00–15.00', 'votes'=>0, 'voted'=>false],
];
$total_penguji = 4;
?>

<div class="w-full max-w-3xl mx-auto" x-data="{ selected: [], submitted: false }">
  <!-- Card Utama -->
  <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    
    <!-- Banner -->
    <div class="px-6 py-5 text-white" style="background:linear-gradient(135deg,#961d5a,#6b1040)">
      <div class="flex items-center gap-2 mb-1">
        <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded-full font-bold">📋 Undangan Vote Jadwal</span>
      </div>
      <h1 class="font-display font-bold text-xl mt-2">Pilih Kesediaan Waktu Sidang</h1>
      <p class="text-white/70 text-sm mt-1">Batas waktu voting: <span class="font-bold text-white"><?= $batas_vote ?></span></p>
    </div>

    <div class="p-6">
      <!-- Info Sidang -->
      <div class="bg-slate-50 dark:bg-slate-700/40 rounded-xl p-4 mb-6 space-y-2 border border-slate-100 dark:border-slate-700">
        <div class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wide mb-2">Detail Sidang</div>
        <?php
        $info = [
          ['k'=>'Mahasiswa', 'v'=>$sidang['mahasiswa'].' ('.$sidang['nim'].')'],
          ['k'=>'Jenis',     'v'=>$sidang['jenis']],
          ['k'=>'Prodi',     'v'=>$sidang['prodi']],
        ];
        foreach($info as $r): ?>
        <div class="flex gap-2 text-sm">
          <span class="text-slate-500 dark:text-slate-400 w-24 flex-shrink-0"><?= $r['k'] ?></span>
          <span class="font-semibold text-slate-800 dark:text-white">: <?= $r['v'] ?></span>
        </div>
        <?php endforeach; ?>
        <div class="pt-2 border-t border-slate-200 dark:border-slate-600 mt-2">
          <div class="text-xs text-slate-500 dark:text-slate-400 mb-0.5">Judul Penelitian:</div>
          <p class="text-sm text-slate-800 dark:text-white italic font-medium">"<?= $sidang['judul'] ?>"</p>
        </div>
      </div>

      <!-- Pilih Slot -->
      <div x-show="!submitted">
        <h3 class="font-display font-bold text-slate-800 dark:text-white text-sm mb-1">Pilih Slot yang Anda BISA Hadiri</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Anda dapat memilih lebih dari satu slot. <?= $total_penguji ?> dosen penguji diundang. Slot dengan suara terbanyak yang akan dipilih.</p>

        <div class="space-y-3 mb-6">
          <?php foreach($slots as $i=>$s): ?>
            <label class="block cursor-pointer group">
              <input type="checkbox" class="peer sr-only" value="<?= $i ?>" x-model="selected">
              <div class="flex items-center justify-between p-4 rounded-xl border-2 transition-all peer-checked:border-nusa peer-checked:bg-nusa/5 border-slate-200 dark:border-slate-700 hover:border-nusa/40">
                <div class="flex items-center gap-3">
                  <div class="w-5 h-5 rounded-md border-2 transition-all flex items-center justify-center flex-shrink-0" :class="selected.includes('<?= $i ?>') ? 'bg-nusa border-nusa' : 'border-slate-300 dark:border-slate-600'">
                    <svg class="w-3 h-3 text-white" x-show="selected.includes('<?= $i ?>')" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  </div>
                  <div>
                    <div class="font-semibold text-sm text-slate-800 dark:text-white"><?= $s['tgl'] ?></div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Pukul <?= $s['jam'] ?> WIB</div>
                  </div>
                </div>
                <!-- Vote bar -->
                <div class="flex items-center gap-2 flex-shrink-0">
                  <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-slate-600 dark:text-slate-300"><?= $s['votes'] ?>/<?= $total_penguji ?> dosen</div>
                    <div class="w-24 bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 mt-1">
                      <div class="h-1.5 rounded-full bg-nusa transition-all" style="width:<?= $s['votes']>0?round(($s['votes']/$total_penguji)*100):0 ?>%"></div>
                    </div>
                  </div>
                  <?php if($s['votes'] === max(array_column($slots,'votes')) && $s['votes']>0): ?>
                  <span class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-bold px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">🏆 Terbanyak</span>
                  <?php endif; ?>
                </div>
              </div>
            </label>
          <?php endforeach; ?>
        </div>

        <!-- Catatan -->
        <div class="mb-6">
          <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">Catatan Tambahan (Opsional)</label>
          <textarea rows="2" placeholder="Misal: Slot Selasa lebih disukai karena agenda kampus..." class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa transition-colors"></textarea>
        </div>

        <!-- Submit -->
        <button @click="if(selected.length > 0) submitted = true" :disabled="selected.length === 0" :class="selected.length > 0 ? 'opacity-100 hover:shadow-xl hover:-translate-y-0.5' : 'opacity-40 cursor-not-allowed'" class="w-full py-3.5 rounded-xl font-bold text-white text-sm transition-all" style="background:linear-gradient(135deg,#961d5a,#6b1040)">
          <span x-text="selected.length > 0 ? '✅ Kirim Vote Saya (' + selected.length + ' slot dipilih)' : 'Pilih minimal 1 slot terlebih dahulu'"></span>
        </button>
      </div>

      <!-- Sukses State -->
      <div x-show="submitted" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="text-center py-8">
        <div class="w-20 h-20 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-5xl mx-auto mb-4">✅</div>
        <h2 class="font-display font-bold text-xl text-slate-800 dark:text-white mb-2">Vote Berhasil!</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Terima kasih, <span class="font-semibold text-slate-700 dark:text-slate-200"><?= $dosen_penguji ?></span>. Pilihan Anda telah tercatat.</p>
        <div class="bg-nusa/10 rounded-xl p-4 text-sm text-nusa text-left border border-nusa/20">
          <p>Admin akan menetapkan jadwal final setelah semua penguji memberikan suara. Anda akan mendapat email konfirmasi jadwal sidang.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
