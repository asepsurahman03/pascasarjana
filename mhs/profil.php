<?php
$pageTitle = 'Profil Saya';
require_once 'header.php';

$mhs = [
  'nama'        => 'Ahmad Rizki Pratama',
  'nim'         => '2023MIF001',
  'email'       => 'ahmad.rizki@student.nusaputra.ac.id',
  'phone'       => '08123456789',
  'prodi'       => 'Magister Informatika (S2)',
  'angkatan'    => '2023',
  'semester'    => '4',
  'ipk'         => '3.82',
  'status'      => 'Aktif',
  'pembimbing'  => 'Dr. Ahmad Fauzi, M.Kom',
  'kopromotor'  => '—',
  'asal'        => 'Bandung, Jawa Barat',
  'foto'        => null,
];
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- Kolom Kiri: Avatar + Info Cepat -->
  <div class="space-y-4">
    <!-- Profile Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
      <div class="h-20 w-full bg-gradient-to-br from-nusa to-nusa-dark"></div>
      <div class="px-5 pb-5 -mt-8">
        <!-- Avatar Upload -->
        <div class="relative w-16 h-16 mb-3" x-data="{hover:false}" @mouseenter="hover=true" @mouseleave="hover=false">
          <div class="w-16 h-16 rounded-2xl border-4 border-white dark:border-slate-800 bg-gradient-to-br from-nusa to-nusa-dark text-white font-display font-black text-2xl flex items-center justify-center shadow-lg">
            <?= strtoupper(substr($mhs['nama'],0,1)) ?>
          </div>
          <div x-show="hover" class="absolute inset-0 rounded-2xl bg-black/40 flex items-center justify-center cursor-pointer">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
        </div>
        <h2 class="font-display font-bold text-slate-800 dark:text-white text-base leading-tight"><?= $mhs['nama'] ?></h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5"><?= $mhs['nim'] ?></p>
        <span class="mt-2 inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">● <?= $mhs['status'] ?></span>
      </div>
    </div>

    <!-- Akademik Stats -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
      <h3 class="font-display font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wide mb-3">Ringkasan Akademik</h3>
      <div class="space-y-3">
        <?php
        $stats_akd = [
          ['l'=>'IPK','v'=>$mhs['ipk'],'sub'=>'Dari skala 4.00'],
          ['l'=>'Semester','v'=>$mhs['semester'],'sub'=>'Dari 6 semester'],
          ['l'=>'Sesi Bimbingan','v'=>'7','sub'=>'Min. 8 untuk sidang'],
          ['l'=>'SKS Ditempuh','v'=>'36','sub'=>'Dari 42 SKS total'],
        ];
        foreach($stats_akd as $s): ?>
        <div class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0">
          <div>
            <div class="text-xs text-slate-500 dark:text-slate-400"><?= $s['l'] ?></div>
            <div class="text-xs text-slate-400"><?= $s['sub'] ?></div>
          </div>
          <div class="font-display font-bold text-slate-800 dark:text-white text-lg"><?= $s['v'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Kolom Kanan: Form Edit Profil -->
  <div class="lg:col-span-2 space-y-4">
    <!-- Data Pribadi -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6" x-data="{editing:false}">
      <div class="flex items-center justify-between mb-5">
        <h3 class="font-display font-bold text-slate-800 dark:text-white text-sm">Data Pribadi</h3>
        <button @click="editing=!editing" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition"
          :class="editing ? 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' : 'bg-nusa/10 text-nusa hover:bg-nusa/20'">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          <span x-text="editing ? 'Batal' : 'Edit Profil'"></span>
        </button>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?php
        $fields = [
          ['label'=>'Nama Lengkap','val'=>$mhs['nama'],'editable'=>true,'key'=>'nama'],
          ['label'=>'NIM','val'=>$mhs['nim'],'editable'=>false,'key'=>'nim'],
          ['label'=>'Email','val'=>$mhs['email'],'editable'=>true,'key'=>'email'],
          ['label'=>'No. HP / WhatsApp','val'=>$mhs['phone'],'editable'=>true,'key'=>'phone'],
          ['label'=>'Program Studi','val'=>$mhs['prodi'],'editable'=>false,'key'=>'prodi'],
          ['label'=>'Angkatan','val'=>$mhs['angkatan'],'editable'=>false,'key'=>'angkatan'],
          ['label'=>'Dosen Pembimbing','val'=>$mhs['pembimbing'],'editable'=>false,'key'=>'pembimbing'],
          ['label'=>'Asal Kota','val'=>$mhs['asal'],'editable'=>true,'key'=>'asal'],
        ];
        foreach($fields as $f): ?>
        <div>
          <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1"><?= $f['label'] ?></label>
          <template x-if="editing && <?= $f['editable']?'true':'false' ?>">
            <input type="text" value="<?= htmlspecialchars($f['val']) ?>" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa transition-colors">
          </template>
          <template x-if="!editing || <?= $f['editable']?'false':'true' ?>">
            <div class="px-3 py-2 rounded-lg text-sm font-medium <?= $f['editable']?'text-slate-800 dark:text-slate-200':'text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 opacity-70' ?>"><?= htmlspecialchars($f['val']) ?></div>
          </template>
        </div>
        <?php endforeach; ?>
      </div>
      <div x-show="editing" x-transition class="mt-4 flex gap-2">
        <button @click="editing=false;alert('Profil berhasil disimpan!')" class="px-5 py-2 text-sm font-bold text-white rounded-lg transition hover:shadow-md" style="background:linear-gradient(135deg,#961d5a,#6b1040)">💾 Simpan Perubahan</button>
        <button @click="editing=false" class="px-4 py-2 text-sm border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition">Batal</button>
      </div>
    </div>

    <!-- Ganti Password -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6" x-data="{open:false}">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="font-display font-bold text-slate-800 dark:text-white text-sm">Keamanan Akun</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Password terakhir diubah: 3 bulan lalu</p>
        </div>
        <button @click="open=!open" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600">
          🔒 Ganti Password
        </button>
      </div>
      <div x-show="open" x-transition class="mt-4 grid gap-3">
        <div>
          <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Password Saat Ini</label>
          <input type="password" placeholder="••••••••" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa transition-colors">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Password Baru</label>
            <input type="password" placeholder="Min. 8 karakter" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa transition-colors">
          </div>
          <div>
            <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Konfirmasi Password</label>
            <input type="password" placeholder="Ulangi password" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-nusa transition-colors">
          </div>
        </div>
        <div class="flex gap-2">
          <button @click="open=false;alert('Password berhasil diubah!')" class="px-5 py-2 text-sm font-bold text-white rounded-lg hover:shadow-md transition" style="background:linear-gradient(135deg,#961d5a,#6b1040)">Simpan Password</button>
          <button @click="open=false" class="px-4 py-2 text-sm border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition">Batal</button>
        </div>
      </div>
    </div>

    <!-- Preferensi Notifikasi -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
      <h3 class="font-display font-bold text-slate-800 dark:text-white text-sm mb-4">Preferensi Notifikasi</h3>
      <div class="space-y-3">
        <?php
        $prefs = [
          ['label'=>'Email Notifikasi Jadwal Sidang','desc'=>'Terima email saat jadwal sidang ditetapkan','on'=>true],
          ['label'=>'Email Persetujuan Logbook','desc'=>'Terima email saat dosen menyetujui logbook Anda','on'=>true],
          ['label'=>'Reminder Batas Waktu Berkas','desc'=>'Pengingat 3 hari sebelum batas upload berkas','on'=>true],
          ['label'=>'Notifikasi Penelitian Baru','desc'=>'Info saat ada topik penelitian dosen yang tersedia','on'=>false],
        ];
        foreach($prefs as $pr): ?>
        <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-slate-700 last:border-0">
          <div>
            <div class="text-sm font-semibold text-slate-800 dark:text-white"><?= $pr['label'] ?></div>
            <div class="text-xs text-slate-500 dark:text-slate-400"><?= $pr['desc'] ?></div>
          </div>
          <button x-data="{on:<?= $pr['on']?'true':'false' ?>}" @click="on=!on"
            :class="on ? 'bg-nusa' : 'bg-slate-200 dark:bg-slate-700'"
            class="relative w-11 h-6 rounded-full transition-colors duration-200 flex-shrink-0 ml-4">
            <div :class="on ? 'translate-x-5' : 'translate-x-0.5'" class="w-5 h-5 rounded-full bg-white shadow transition-transform duration-200 absolute top-0.5"></div>
          </button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>
