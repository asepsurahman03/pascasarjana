<?php
$pageTitle='Kirim WhatsApp';$breadcrumb=[['label'=>'WhatsApp']];
require_once __DIR__.'/../includes/functions.php';requireAdmin();
$allProdi=getAllProdi();
$templates=[
    'Info Jadwal'=>"Yth. Mahasiswa Pascasarjana,\n\nMenginformasikan jadwal *[KEGIATAN]*:\n📅 [TANGGAL]\n⏰ [WAKTU]\n📍 [TEMPAT]\n\nSalam,\nAdmin Pascasarjana NPU",
    'Pengingat Tugas'=>"Halo [NAMA],\n\nIni pengingat bahwa tugas *[TUGAS]* deadline pada *[DEADLINE]*.\n\nSalam,\nAdmin Pascasarjana NPU",
    'Undangan Seminar'=>"Yth. [NAMA],\n\nDiundang menghadiri:\n*SEMINAR [JUDUL]*\n📅 [TANGGAL] | ⏰ [WAKTU]\n📍 [TEMPAT]\n\nSalam,\nAdmin Pascasarjana NPU",
    'Pengumuman Umum'=>"Yth. Mahasiswa Pascasarjana NPU,\n\n[ISI_PENGUMUMAN]\n\nSalam,\nAdmin Pascasarjana NPU"
];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $a=$_POST['action']??'';
    if($a==='send'){
        $tuj=trim($_POST['tujuan']);
        $pes=trim($_POST['pesan']);
        $jns=$_POST['jenis']??'individu';
        dbExecute("INSERT INTO whatsapp_log(tujuan,jenis_tujuan,pesan,status,waktu_kirim,created_by)VALUES(?,?,?,'Terkirim',NOW(),?)",[$tuj,$jns,$pes,$_SESSION['user_id']]);
        logActivity('Kirim WA','whatsapp',"Ke: $tuj");
        $_SESSION['flash']=['type'=>'success','message'=>'Pesan berhasil dikirim (simulasi).'];
        header('Location: whatsapp');exit;
    }
}
$logs=dbQuery("SELECT w.*,u.nama as unama FROM whatsapp_log w JOIN users u ON u.id=w.created_by ORDER BY w.created_at DESC LIMIT 20");
require_once __DIR__.'/../includes/header.php';
$tj=json_encode($templates,JSON_UNESCAPED_UNICODE);
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
  <div>
    <h1 class="font-display font-bold text-2xl md:text-3xl text-slate-800 dark:text-white">Broadcast WhatsApp</h1>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Kirim pesan informasi ke mahasiswa dan dosen Pascasarjana</p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
  <!-- FORM KIRIM -->
  <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-6 lg:col-span-2 relative overflow-hidden">
    <div class="absolute -right-16 -top-16 w-48 h-48 bg-emerald-500 rounded-full mix-blend-multiply filter blur-3xl opacity-10"></div>
    <div class="absolute -left-16 -bottom-16 w-48 h-48 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-10"></div>
    
    <div class="flex items-center gap-3 mb-6 relative z-10">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center text-2xl shadow-inner">
        💬
      </div>
      <div>
        <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white">Form Kirim Pesan</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400">Pesan akan langsung dikirimkan ke nomor tujuan.</p>
      </div>
    </div>
    
    <form method="POST" class="space-y-5 relative z-10">
      <input type="hidden" name="action" value="send">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nomor Tujuan</label>
          <div class="relative">
            <span class="absolute left-4 top-3 text-slate-400 text-sm">📱</span>
            <input type="text" name="tujuan" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm font-mono focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" placeholder="Contoh: 6281234567890" required>
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jenis Tujuan</label>
          <select name="jenis" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
            <option value="individu">Individu / Personal</option>
            <option value="grup">Grup Prodi / Mahasiswa</option>
          </select>
        </div>
      </div>
      
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Gunakan Template Pesan</label>
        <select id="tpl-sel" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all" onchange="applyTpl(this.value)">
          <option value="">-- Buat pesan dari awal --</option>
          <?php foreach(array_keys($templates) as $t):?>
          <option value="<?=e($t)?>"><?=e($t)?></option>
          <?php endforeach;?>
        </select>
      </div>
      
      <div>
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Isi Pesan <span class="text-red-500">*</span></label>
        <textarea name="pesan" id="wa-pesan" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-xl p-4 text-sm focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all font-mono leading-relaxed" rows="8" placeholder="Ketik pesan Anda di sini. Gunakan *tebal*, _miring_, atau ~coret~ untuk format teks WhatsApp..." required></textarea>
        <p class="text-xs text-slate-400 mt-2">Tips: Anda dapat mengganti variabel seperti [NAMA], [TANGGAL] secara manual sebelum dikirim.</p>
      </div>
      
      <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-xl font-bold shadow-md hover:shadow-lg transition-all text-sm group">
          <span>Kirim Pesan</span>
          <svg class="w-4 h-4 transform group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
        </button>
        <button type="reset" class="px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all text-sm">
          Reset
        </button>
      </div>
    </form>
  </div>
  
  <!-- PANEL KANAN -->
  <div class="space-y-6">
    <!-- Grup Prodi -->
    <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm p-5">
      <div class="flex items-center gap-3 mb-4 pb-4 border-b border-slate-100 dark:border-slate-700/60">
        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center text-lg">
          👥
        </div>
        <h3 class="font-display font-bold text-slate-800 dark:text-white">Grup Program Studi</h3>
      </div>
      
      <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
        <?php foreach($allProdi as $p):?>
        <div class="flex items-center justify-between p-3 rounded-2xl border border-slate-100 dark:border-slate-700/60 hover:bg-slate-50 dark:hover:bg-slate-700/30 hover:border-slate-300 dark:hover:border-slate-600 transition-all group">
          <div class="flex items-center gap-3">
            <span class="w-2 h-2 rounded-full" style="background:<?=e($p['warna_hex']??'#3b82f6')?>"></span>
            <div>
              <div class="text-sm font-bold text-slate-800 dark:text-slate-200"><?=e($p['nama'])?></div>
              <div class="text-xs text-slate-500 font-mono mt-0.5"><?=e($p['no_wa_grup']??'Belum dikonfigurasi')?></div>
            </div>
          </div>
          <?php if($p['no_wa_grup']):?>
          <button onclick="document.querySelector('[name=tujuan]').value='<?=e($p['no_wa_grup'])?>';document.querySelector('[name=jenis]').value='grup';" class="opacity-0 group-hover:opacity-100 transition-opacity text-xs font-bold text-blue-500 bg-blue-50 dark:bg-blue-900/40 px-3 py-1.5 rounded-lg hover:bg-blue-500 hover:text-white">
            Pilih
          </button>
          <?php endif;?>
        </div>
        <?php endforeach;?>
      </div>
    </div>
    
    <!-- Info -->
    <div class="bg-gradient-to-br from-[#8c0c4c] to-[#a3155b] rounded-3xl p-6 text-white shadow-lg relative overflow-hidden">
      <div class="absolute right-0 top-0 opacity-10 text-9xl leading-none transform translate-x-4 -translate-y-4">💬</div>
      <h3 class="font-bold text-lg mb-2 relative z-10">Terkoneksi API</h3>
      <p class="text-sm opacity-90 leading-relaxed relative z-10">Pesan yang dikirim melalui portal ini akan otomatis di-forward oleh server bot resmi Pascasarjana NPU (Simulation Mode).</p>
    </div>
  </div>
</div>

<!-- RIWAYAT -->
<div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 rounded-3xl shadow-sm overflow-hidden mb-8">
  <div class="p-5 border-b border-slate-100 dark:border-slate-700/60 flex justify-between items-center">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-500 flex items-center justify-center text-lg">🕒</div>
      <h2 class="font-display font-bold text-lg text-slate-800 dark:text-white">Riwayat Pengiriman</h2>
    </div>
  </div>
  
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-900/50">
        <tr>
          <th class="text-left py-4 px-5 text-xs font-bold text-slate-500 uppercase tracking-wider w-40 border-b border-slate-100 dark:border-slate-700/60">Tujuan</th>
          <th class="text-left py-4 px-5 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 dark:border-slate-700/60">Pesan</th>
          <th class="text-left py-4 px-5 text-xs font-bold text-slate-500 uppercase tracking-wider w-24 border-b border-slate-100 dark:border-slate-700/60">Status</th>
          <th class="text-left py-4 px-5 text-xs font-bold text-slate-500 uppercase tracking-wider w-40 border-b border-slate-100 dark:border-slate-700/60">Waktu & Pengirim</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
        <?php if(empty($logs)):?>
        <tr>
          <td colspan="4" class="py-12 text-center">
            <div class="text-4xl mb-3 opacity-30">📨</div>
            <p class="text-slate-500 font-medium">Belum ada riwayat pengiriman pesan.</p>
          </td>
        </tr>
        <?php else: foreach($logs as $w): 
          $wc=['Terkirim'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400','Gagal'=>'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400','Pending'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400'][$w['status']]??'bg-slate-100 text-slate-700';
        ?>
        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
          <td class="py-4 px-5">
            <div class="font-mono text-sm font-bold text-slate-800 dark:text-slate-200"><?=e($w['tujuan'])?></div>
            <div class="text-[10px] font-bold text-slate-400 uppercase mt-1"><?=$w['jenis_tujuan']?></div>
          </td>
          <td class="py-4 px-5">
            <div class="text-slate-600 dark:text-slate-300 line-clamp-2 pr-4 leading-relaxed"><?=e($w['pesan'])?></div>
          </td>
          <td class="py-4 px-5">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide <?=$wc?>">
              <?=$w['status']?>
            </span>
          </td>
          <td class="py-4 px-5">
            <div class="font-semibold text-slate-800 dark:text-slate-200"><?=$w['waktu_kirim']?formatTanggal($w['waktu_kirim'],true):'-'?></div>
            <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              <?=e($w['unama'])?>
            </div>
          </td>
        </tr>
        <?php endforeach; endif;?>
      </tbody>
    </table>
  </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
const tpls = <?=$tj?>;
function applyTpl(n){
    const t=tpls[n];
    if(t) {
        document.getElementById('wa-pesan').value=t;
        document.getElementById('tpl-sel').value=n;
        // Animasi highlight
        const el = document.getElementById('wa-pesan');
        el.classList.add('ring-4', 'ring-emerald-500/50');
        setTimeout(()=>el.classList.remove('ring-4', 'ring-emerald-500/50'), 500);
    } else {
        document.getElementById('wa-pesan').value='';
    }
}
</script>

<?php require_once __DIR__.'/../includes/footer.php'; ?>
