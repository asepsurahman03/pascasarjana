<?php
$pageTitle = 'Notifikasi';
require_once 'header.php';

$notifs = [
  ['type'=>'success','icon'=>'✅','judul'=>'Logbook Sesi #8 Disetujui',
   'pesan'=>'Dr. Ahmad Fauzi menyetujui logbook bimbingan Anda pada sesi ke-8. Feedback: "Baik. Tambahkan perbandingan dengan baseline lebih detail."',
   'waktu'=>'2 jam lalu','tgl'=>'08 Jul 2025','baru'=>true,'link'=>'logbook.php'],
  ['type'=>'info','icon'=>'📅','judul'=>'Jadwal Sidang Ditetapkan',
   'pesan'=>'Jadwal Sidang Tesis Anda telah ditetapkan pada Senin, 21 Juli 2025 pukul 09.00–11.00 WIB di Ruang Seminar Lt.3. Tim penguji: Dr. Ahmad Fauzi, Dr. Rizal Maulana, Dr. Siti Rahayu.',
   'waktu'=>'Kemarin','tgl'=>'07 Jul 2025','baru'=>true,'link'=>'jadwal.php'],
  ['type'=>'warning','icon'=>'⚠️','judul'=>'Berkas Tidak Lengkap — Segera Upload',
   'pesan'=>'Rekap Logbook Bimbingan Anda belum diunggah. Minimum 8 sesi bimbingan diperlukan. Saat ini baru 7 sesi.',
   'waktu'=>'2 hari lalu','tgl'=>'06 Jul 2025','baru'=>false,'link'=>'daftar_sidang.php'],
  ['type'=>'info','icon'=>'🔬','judul'=>'Penelitian Baru Tersedia',
   'pesan'=>'Dr. Siti Rahayu membuka slot penelitian baru: "Analisis Sentimen Multibahasa Menggunakan LLM". Tersedia 3 slot mahasiswa.',
   'waktu'=>'3 hari lalu','tgl'=>'05 Jul 2025','baru'=>false,'link'=>'penelitian.php'],
  ['type'=>'info','icon'=>'📢','judul'=>'Pengumuman Wisuda Semester Genap 2024/2025',
   'pesan'=>'Pendaftaran wisuda semester genap 2024/2025 telah dibuka. Batas akhir pendaftaran: 15 Agustus 2025. Hubungi Staff Pascasarjana untuk informasi lebih lanjut.',
   'waktu'=>'1 minggu lalu','tgl'=>'10 Jun 2025','baru'=>false,'link'=>'#'],
  ['type'=>'success','icon'=>'🎉','judul'=>'Pendaftaran Sidang Diterima',
   'pesan'=>'Pendaftaran Sidang Tesis Anda telah diterima sistem. Admin akan memverifikasi berkas dalam 1–2 hari kerja.',
   'waktu'=>'1 minggu lalu','tgl'=>'12 Jul 2025','baru'=>false,'link'=>'status_sidang.php'],
];
$jumlah_baru = count(array_filter($notifs, fn($n) => $n['baru']));

$typeCl = [
  'success' => ['border'=>'border-emerald-200 dark:border-emerald-800','bg'=>'bg-emerald-50 dark:bg-emerald-900/20','dot'=>'bg-emerald-500'],
  'warning' => ['border'=>'border-amber-200 dark:border-amber-800',  'bg'=>'bg-amber-50 dark:bg-amber-900/20',  'dot'=>'bg-amber-500'],
  'error'   => ['border'=>'border-red-200 dark:border-red-800',      'bg'=>'bg-red-50 dark:bg-red-900/20',      'dot'=>'bg-red-500'],
  'info'    => ['border'=>'border-blue-200 dark:border-blue-800',    'bg'=>'bg-blue-50 dark:bg-blue-900/20',    'dot'=>'bg-blue-500'],
];
?>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
  <div>
    <h2 class="font-display font-bold text-slate-800 dark:text-white text-xl">🔔 Notifikasi</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
      <?php if($jumlah_baru): ?>
        <span class="font-semibold text-nusa"><?= $jumlah_baru ?> notifikasi baru</span> · <?= count($notifs) ?> total
      <?php else: ?>
        Semua notifikasi sudah dibaca
      <?php endif; ?>
    </p>
  </div>
  <button onclick="document.querySelectorAll('.notif-dot').forEach(d=>d.remove())" class="text-xs font-semibold text-nusa hover:underline transition px-3 py-1.5 rounded-lg hover:bg-nusa/10">
    Tandai Semua Dibaca
  </button>
</div>

<!-- List Notifikasi -->
<div class="space-y-3">
  <?php foreach($notifs as $n):
    $cl = $typeCl[$n['type']];
  ?>
  <a href="<?= $n['link'] ?>" class="block group">
    <div class="bg-white dark:bg-slate-800 rounded-2xl border <?= $n['baru'] ? $cl['border'].' '.$cl['bg'] : 'border-slate-200 dark:border-slate-700' ?> p-4 hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 relative overflow-hidden">
      <!-- Unread left bar -->
      <?php if($n['baru']): ?>
      <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl <?= $cl['dot'] ?>"></div>
      <?php endif; ?>
      <div class="flex items-start gap-4 pl-<?= $n['baru'] ? '2' : '0' ?>">
        <!-- Icon -->
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl flex-shrink-0 <?= $n['baru'] ? $cl['bg'].' border '.$cl['border'] : 'bg-slate-100 dark:bg-slate-700' ?>">
          <?= $n['icon'] ?>
        </div>
        <!-- Content -->
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-2">
            <h4 class="font-display font-bold text-sm text-slate-800 dark:text-white group-hover:text-nusa transition-colors leading-tight"><?= $n['judul'] ?></h4>
            <div class="flex items-center gap-2 flex-shrink-0">
              <?php if($n['baru']): ?>
              <span class="notif-dot w-2 h-2 rounded-full <?= $cl['dot'] ?> flex-shrink-0 mt-1"></span>
              <?php endif; ?>
              <span class="text-xs text-slate-400 whitespace-nowrap"><?= $n['waktu'] ?></span>
            </div>
          </div>
          <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed line-clamp-2"><?= $n['pesan'] ?></p>
          <div class="flex items-center justify-between mt-2">
            <span class="text-xs text-slate-400"><?= $n['tgl'] ?></span>
            <span class="text-xs font-semibold text-nusa opacity-0 group-hover:opacity-100 transition-opacity">Lihat detail →</span>
          </div>
        </div>
      </div>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<!-- Empty state jika kosong -->
<?php if(empty($notifs)): ?>
<div class="text-center py-16">
  <div class="text-6xl mb-4">🔔</div>
  <h3 class="font-display font-bold text-slate-700 dark:text-slate-200 text-lg">Tidak ada notifikasi</h3>
  <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Semua aktivitas Anda akan muncul di sini.</p>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
