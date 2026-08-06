<?php
$data['title'] = 'Isi Formulir - ' . APP_NAME;
include BASE_PATH . '/includes/public_layout_top.php';
?>

<div x-data="formApp()" x-init="init()">

  <!-- Page Header -->
  <div class="mb-6">
    <h1 class="font-display font-bold text-2xl text-gray-800 dark:text-white mb-1">Formulir Pengunduran Diri</h1>
    <p class="text-gray-500 dark:text-gray-400 text-sm">Isi formulir dengan lengkap dan benar sesuai data resmi Anda.</p>
  </div>

  <!-- Step Progress -->
  <div class="card p-5 mb-6">
    <div class="flex items-center justify-between relative">
      <div class="absolute top-4 left-0 right-0 h-0.5 bg-gray-200 dark:bg-gray-700 mx-8 z-0"></div>
      <div class="absolute top-4 left-0 h-0.5 bg-nusa transition-all duration-500 mx-8 z-0"
           :style="`width: calc(${(step-1) / 2 * 100}% - 4rem * ${(step-1)/2})`"></div>

      <template x-for="(s, i) in steps" :key="i">
        <div class="flex flex-col items-center gap-2 relative z-10">
          <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 border-2"
               :class="step > i+1 ? 'bg-nusa border-nusa text-white' : (step === i+1 ? 'bg-white dark:bg-gray-800 border-nusa text-nusa' : 'bg-gray-100 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-400')">
            <span x-show="step > i+1">✓</span>
            <span x-show="step <= i+1" x-text="i+1"></span>
          </div>
          <span class="text-xs font-medium text-center hidden sm:block"
                :class="step === i+1 ? 'text-nusa' : 'text-gray-400'"
                x-text="s"></span>
        </div>
      </template>
    </div>
  </div>

  <form id="formPD" @submit.prevent="submitForm">
    <?= csrfField() ?>

    <!-- ========== STEP 1: DATA MAHASISWA ========== -->
    <div x-show="step===1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
      <div class="card p-6 mb-4">
        <h2 class="font-display font-bold text-lg text-gray-800 dark:text-white mb-5 flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-nusa text-white text-xs font-bold flex items-center justify-center">1</span>
          Data Mahasiswa
        </h2>

        <div class="grid md:grid-cols-2 gap-5">
          <!-- Nama Pemohon -->
          <div>
            <label class="form-label">Nama Pemohon <span class="text-nusa">*</span></label>
            <input type="text" name="nama_pemohon" x-model="formData.nama_pemohon" required
              placeholder="Nama lengkap sesuai KTP"
              class="form-input">
            <p class="text-nusa text-xs mt-1" x-show="errors.nama_pemohon" x-text="errors.nama_pemohon"></p>
          </div>

          <!-- NIM -->
          <div>
            <label class="form-label">NIM <span class="text-nusa">*</span></label>
            <input type="text" name="nim" x-model="formData.nim" required placeholder="Contoh: 2024001001"
              class="form-input">
            <p class="text-nusa text-xs mt-1" x-show="errors.nim" x-text="errors.nim"></p>
          </div>

          <!-- Angkatan -->
          <div>
            <label class="form-label">Angkatan <span class="text-nusa">*</span></label>
            <input type="number" name="angkatan" x-model="formData.angkatan" required
              min="2000" max="2030" placeholder="Contoh: 2024"
              class="form-input">
            <p class="text-nusa text-xs mt-1" x-show="errors.angkatan" x-text="errors.angkatan"></p>
          </div>

          <!-- Program Studi -->
          <div>
            <label class="form-label">Program Studi <span class="text-nusa">*</span></label>
            <select name="program_studi" x-model="formData.program_studi" required class="form-input">
              <option value="">-- Pilih Program Studi --</option>
              <?php foreach (PROGRAM_STUDI as $prodi): ?>
              <option value="<?= e($prodi) ?>" <?= ($mahasiswa['program_studi'] ?? '') === $prodi ? 'selected' : '' ?>><?= e($prodi) ?></option>
              <?php endforeach; ?>
            </select>
            <p class="text-nusa text-xs mt-1" x-show="errors.program_studi" x-text="errors.program_studi"></p>
          </div>

          <!-- Status Mahasiswa -->
          <div>
            <label class="form-label">Status Mahasiswa <span class="text-nusa">*</span></label>
            <div class="flex gap-4 mt-2">
              <label class="flex items-center gap-2 cursor-pointer group">
                <input type="radio" name="status_mahasiswa" value="Beasiswa" x-model="formData.status_mahasiswa" class="w-4 h-4 accent-nusa">
                <span class="text-gray-700 dark:text-gray-300 text-sm group-hover:text-nusa transition">Beasiswa</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer group">
                <input type="radio" name="status_mahasiswa" value="Non Beasiswa" x-model="formData.status_mahasiswa" class="w-4 h-4 accent-nusa">
                <span class="text-gray-700 dark:text-gray-300 text-sm group-hover:text-nusa transition">Non Beasiswa</span>
              </label>
            </div>
            <p class="text-nusa text-xs mt-1" x-show="errors.status_mahasiswa" x-text="errors.status_mahasiswa"></p>
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <button type="button" @click="nextStep"
          class="px-8 py-3 bg-nusa text-white font-semibold rounded-xl hover:bg-nusa-dark shadow-md shadow-nusa/30 transition-all duration-200 flex items-center gap-2">
          Lanjut
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>

    <!-- ========== STEP 2: PERNYATAAN & TANDA TANGAN ========== -->
    <div x-show="step===2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">

      <!-- Bersedia Mundur -->
      <div class="card p-6 mb-4">
        <h2 class="font-display font-bold text-lg text-gray-800 dark:text-white mb-5 flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-nusa text-white text-xs font-bold flex items-center justify-center">2</span>
          Pernyataan Pengunduran Diri
        </h2>

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-4 mb-5">
          <p class="text-amber-800 dark:text-amber-200 text-sm font-medium">⚠️ Perhatian</p>
          <p class="text-amber-700 dark:text-amber-300 text-sm mt-1">
            Dengan mengisi formulir ini, Anda menyatakan secara resmi mengundurkan diri sebagai mahasiswa Universitas Nusa Putra. Keputusan ini tidak dapat dibatalkan setelah disetujui administrator.
          </p>
        </div>

        <label class="form-label mb-3">Apakah Anda bersedia mengundurkan diri? <span class="text-nusa">*</span></label>

        <div class="grid grid-cols-2 gap-4 mb-5">
          <label class="cursor-pointer group">
            <input type="radio" name="bersedia_mundur" value="YES" x-model="formData.bersedia_mundur" class="sr-only peer">
            <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-nusa peer-checked:bg-nusa/5 transition-all duration-200 text-center hover:border-nusa/50">
              <div class="text-3xl mb-2">✅</div>
              <p class="font-semibold text-gray-800 dark:text-white peer-checked:text-nusa">YA</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Saya bersedia mengundurkan diri</p>
            </div>
          </label>

          <label class="cursor-pointer group">
            <input type="radio" name="bersedia_mundur" value="NO" x-model="formData.bersedia_mundur" class="sr-only peer">
            <div class="p-4 rounded-xl border-2 border-gray-200 dark:border-gray-600 peer-checked:border-blue-500 peer-checked:bg-blue-500/5 transition-all duration-200 text-center hover:border-blue-500/50">
              <div class="text-3xl mb-2">❌</div>
              <p class="font-semibold text-gray-800 dark:text-white">TIDAK</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Saya tidak jadi mengundurkan diri</p>
            </div>
          </label>
        </div>

        <!-- Digital Signature Canvas (only if YES) -->
        <div x-show="formData.bersedia_mundur === 'YES'"
             x-transition:enter="transition ease-out duration-400"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

          <div class="border-t border-gray-200 dark:border-gray-700 pt-5 mt-5">
            <label class="form-label mb-2">Tanda Tangan Digital <span class="text-nusa">*</span></label>
            <p class="text-gray-500 dark:text-gray-400 text-xs mb-3">Buat tanda tangan Anda menggunakan mouse atau layar sentuh di area bawah ini.</p>

            <!-- Canvas Container -->
            <div class="relative border-2 rounded-xl overflow-hidden"
                 :class="signatureEmpty ? 'border-dashed border-gray-300 dark:border-gray-600' : 'border-nusa/40 border-solid'">
              <canvas id="signatureCanvas"
                      class="w-full touch-none bg-white dark:bg-gray-50"
                      style="height:200px; cursor:crosshair"></canvas>

              <!-- Empty hint overlay -->
              <div x-show="signatureEmpty"
                   class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                <p class="text-gray-400 text-sm">Buat tanda tangan di sini</p>
              </div>
            </div>

            <!-- Signature Actions -->
            <div class="flex flex-wrap gap-2 mt-3">
              <button type="button" @click="clearSignature"
                class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus
              </button>
              <button type="button" @click="undoSignature"
                class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                Undo
              </button>
              <button type="button" @click="previewSignature"
                x-show="!signatureEmpty"
                class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 text-blue-600 dark:text-blue-400 hover:bg-blue-100 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Preview
              </button>
              <span x-show="!signatureEmpty" class="flex items-center gap-1.5 text-green-600 dark:text-green-400 text-sm font-medium px-3">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Tanda tangan tersimpan
              </span>
            </div>

            <input type="hidden" name="signature_data" :value="signatureData">
          </div>
        </div>

        <!-- Alasan -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-5 mt-5" x-show="formData.bersedia_mundur === 'YES'"
             x-transition:enter="transition ease-out duration-400"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
          <label class="form-label">
            Alasan Pengunduran Diri <span class="text-nusa">*</span>
          </label>
          <p class="text-gray-400 dark:text-gray-500 text-xs mb-2">Maksimal 1000 karakter</p>
          <textarea name="alasan" x-model="formData.alasan" rows="5"
            placeholder="Tuliskan alasan Anda mengundurkan diri secara jelas dan rinci..."
            maxlength="1000"
            class="form-input resize-none"></textarea>
          <div class="flex justify-between mt-1">
            <p class="text-nusa text-xs" x-show="errors.alasan" x-text="errors.alasan"></p>
            <p class="text-xs ml-auto" :class="formData.alasan.length > 950 ? 'text-nusa' : 'text-gray-400 dark:text-gray-500'">
              <span x-text="formData.alasan.length"></span>/1000
            </p>
          </div>
        </div>
      </div>

      <div class="flex justify-between">
        <button type="button" @click="step=1"
          class="px-6 py-3 text-gray-600 dark:text-gray-400 font-medium rounded-xl border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          Kembali
        </button>
        <button type="button" @click="nextStep"
          class="px-8 py-3 bg-nusa text-white font-semibold rounded-xl hover:bg-nusa-dark shadow-md shadow-nusa/30 transition-all duration-200 flex items-center gap-2">
          <span x-text="formData.bersedia_mundur === 'NO' ? 'Kirim' : 'Preview'"></span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>

    <!-- ========== STEP 3: PREVIEW ========== -->
    <div x-show="step===3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
      <div class="card p-6 mb-4">
        <h2 class="font-display font-bold text-lg text-gray-800 dark:text-white mb-5 flex items-center gap-2">
          <span class="w-7 h-7 rounded-lg bg-nusa text-white text-xs font-bold flex items-center justify-center">3</span>
          Preview Formulir
        </h2>

        <!-- ===== DOCUMENT PREVIEW — 100% IDENTIK DENGAN DOCX ===== -->
        <div id="doc-preview" style="background:#fff; border:1px solid #bbb; border-radius:6px; overflow:hidden; box-shadow:0 2px 16px rgba(0,0,0,.1); font-family:'Times New Roman',Times,serif; color:#000; font-size:11pt; padding:12.7mm 12.7mm 0 12.7mm; min-height:297mm; display:flex; flex-direction:column;">

          <!-- TABLE 0: KOP (2 col: logo 19.2% | judul 80.8%), height 24.8mm -->
          <table style="width:100%; border-collapse:collapse; table-layout:fixed; font-family:'Times New Roman',Times,serif; margin-top:0; margin-bottom:5mm;">
            <colgroup><col style="width:19.2%"><col style="width:80.8%"></colgroup>
            <tr style="height:24.8mm;">
              <td style="border:1.5px solid #000; padding:4px 6px; text-align:center; vertical-align:middle;">
                <img src="<?= APP_URL ?>/assets/images/logo-nusaputra-docx.png"
                     onerror="this.style.display='none'"
                     alt="Logo"
                     style="width:20mm; height:20mm; object-fit:contain; display:block; margin:0 auto;">
              </td>
              <td style="border:1.5px solid #000; padding:4px 8px; text-align:center; vertical-align:middle;">
                <span style="display:block; font-size:14pt; font-weight:bold; line-height:1.3; letter-spacing:0.5px;">PERNYATAAN</span>
                <span style="display:block; font-size:14pt; font-weight:bold; line-height:1.3; letter-spacing:0.5px;">PENGUNDURAN DIRI MAHASISWA</span>
              </td>
            </tr>
          </table>


          <!-- TABLE 1: FORM DATA — 9 kolom identik DOCX -->
          <table style="width:100%; border-collapse:collapse; table-layout:fixed; font-family:'Times New Roman',Times,serif; font-size:11pt; margin-top:0; border-top:1.5px solid #000;">
            <colgroup>
              <col style="width:14.64%"><col style="width:3.84%"><col style="width:4.02%">
              <col style="width:13.16%"><col style="width:9.80%"><col style="width:4.54%">
              <col style="width:11.08%"><col style="width:6.64%"><col style="width:32.28%">
            </colgroup>

            <!-- ROW 0: NOMOR | Diisi SASU | TANGGAL | tanggal_surat (10mm) -->
            <tr style="height:10mm;">
              <td colspan="2" style="border:1.5px solid #000;padding:3px 7px;vertical-align:middle;">NOMOR</td>
              <td colspan="3" style="border:1.5px solid #000;padding:3px 7px;vertical-align:middle;">
                <span style="font-style:italic;color:#A6A6A6;font-size:8pt;font-family:Arial,sans-serif;">Diisi oleh SASU</span>
              </td>
              <td colspan="3" style="border:1.5px solid #000;padding:3px 7px;vertical-align:middle;">TANGGAL</td>
              <td colspan="1" style="border:1.5px solid #000;padding:3px 7px;vertical-align:middle;"
                  x-text="formData.tanggal_surat ? new Date(formData.tanggal_surat + 'T00:00:00').toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}) : ''"></td>
            </tr>

            <!-- ROW 1: NAMA PEMOHON (7mm) -->
            <tr style="height:7mm;">
              <td colspan="4" style="border:1.5px solid #000;padding:3px 7px;font-weight:bold;vertical-align:middle;">NAMA PEMOHON</td>
              <td colspan="5" style="border:1.5px solid #000;padding:3px 7px;vertical-align:middle;" x-text="formData.nama_pemohon"></td>
            </tr>

            <!-- ROW 2: NIM (7mm) -->
            <tr style="height:7mm;">
              <td colspan="4" style="border:1.5px solid #000;padding:3px 7px;font-weight:bold;vertical-align:middle;">NIM</td>
              <td colspan="5" style="border:1.5px solid #000;padding:3px 7px;vertical-align:middle;" x-text="formData.nim"></td>
            </tr>

            <!-- ROW 3: ANGKATAN (7mm) -->
            <tr style="height:7mm;">
              <td colspan="4" style="border:1.5px solid #000;padding:3px 7px;font-weight:bold;vertical-align:middle;">ANGKATAN</td>
              <td colspan="5" style="border:1.5px solid #000;padding:3px 7px;vertical-align:middle;" x-text="formData.angkatan"></td>
            </tr>

            <!-- ROW 4: PROGRAM STUDI (7mm) -->
            <tr style="height:7mm;">
              <td colspan="4" style="border:1.5px solid #000;padding:3px 7px;font-weight:bold;vertical-align:middle;">PROGRAM STUDI</td>
              <td colspan="5" style="border:1.5px solid #000;padding:3px 7px;vertical-align:middle;" x-text="formData.program_studi"></td>
            </tr>

            <!-- ROW 5: MAHASISWA | □BEASISWA | □NON BEASISWA (7mm) -->
            <tr style="height:7mm;">
              <td colspan="3" style="border:1.5px solid #000;padding:3px 7px;font-weight:bold;vertical-align:middle;">MAHASISWA</td>
              <td colspan="4" style="border:1.5px solid #000;padding:3px 8px;vertical-align:middle;">
                <div style="display:flex;align-items:center;gap:5px;font-weight:bold;">
                  <div :style="formData.status_mahasiswa==='Beasiswa'
                    ? 'width:12px;height:12px;border:1.5px solid #000;background:#000;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;flex-shrink:0;line-height:1;'
                    : 'width:12px;height:12px;border:1.5px solid #000;background:#fff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;'">
                    <span x-show="formData.status_mahasiswa==='Beasiswa'" style="line-height:1;color:#fff;">✓</span>
                  </div>
                  BEASISWA
                </div>
              </td>
              <td colspan="2" style="border:1.5px solid #000;padding:3px 8px;vertical-align:middle;">
                <div style="display:flex;align-items:center;gap:5px;font-weight:bold;">
                  <div :style="formData.status_mahasiswa==='Non Beasiswa'
                    ? 'width:12px;height:12px;border:1.5px solid #000;background:#000;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;flex-shrink:0;line-height:1;'
                    : 'width:12px;height:12px;border:1.5px solid #000;background:#fff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;'">
                    <span x-show="formData.status_mahasiswa==='Non Beasiswa'" style="line-height:1;color:#fff;">✓</span>
                  </div>
                  NON BEASISWA
                </div>
              </td>
            </tr>

            <!-- ROW 6: BERSEDIA MENGUNDURKAN DIRI | □YA | □TIDAK (7mm) -->
            <tr style="height:7mm;">
              <td colspan="3" style="border:1.5px solid #000;padding:3px 7px;font-weight:bold;vertical-align:middle;">BERSEDIA MENGUNDURKAN DIRI</td>
              <td colspan="4" style="border:1.5px solid #000;padding:3px 8px;vertical-align:middle;">
                <div style="display:flex;align-items:center;gap:5px;font-weight:bold;">
                  <div :style="formData.bersedia_mundur==='YES'
                    ? 'width:12px;height:12px;border:1.5px solid #000;background:#000;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;flex-shrink:0;line-height:1;'
                    : 'width:12px;height:12px;border:1.5px solid #000;background:#fff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;'">
                    <span x-show="formData.bersedia_mundur==='YES'" style="line-height:1;color:#fff;">✓</span>
                  </div>
                  YA
                </div>
              </td>
              <td colspan="2" style="border:1.5px solid #000;padding:3px 8px;vertical-align:middle;">
                <div style="display:flex;align-items:center;gap:5px;font-weight:bold;">
                  <div :style="formData.bersedia_mundur==='NO'
                    ? 'width:12px;height:12px;border:1.5px solid #000;background:#000;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;flex-shrink:0;line-height:1;'
                    : 'width:12px;height:12px;border:1.5px solid #000;background:#fff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;'">
                    <span x-show="formData.bersedia_mundur==='NO'" style="line-height:1;color:#fff;">✓</span>
                  </div>
                  TIDAK
                </div>
              </td>
            </tr>

            <!-- ROW 7: ALASAN — height 22mm -->
            <tr>
              <td colspan="1" style="border:1.5px solid #000;padding:3px 7px;vertical-align:top;padding-top:5px;height:22mm;">ALASAN</td>
              <td colspan="8" style="border:1.5px solid #000;padding:3px 7px;vertical-align:top;height:22mm;white-space:pre-wrap;" x-text="formData.alasan"></td>
            </tr>

            <!-- ROW 8: PEMOHON | PERSETUJUAN (6mm) -->
            <tr style="height:6mm;">
              <td colspan="6" style="border:1.5px solid #000;padding:3px 7px;text-align:center;font-size:11pt;vertical-align:middle;">PEMOHON</td>
              <td colspan="3" style="border:1.5px solid #000;padding:3px 7px;text-align:center;font-size:11pt;vertical-align:middle;">PERSETUJUAN</td>
            </tr>

            <!-- ROW 9: MAHASISWA | KETUA PROGRAM STUDI (7mm) -->
            <tr style="height:7mm;">
              <td colspan="6" style="border:1.5px solid #000;padding:3px 7px;text-align:center;font-size:11pt;vertical-align:middle;">MAHASISWA</td>
              <td colspan="3" style="border:1.5px solid #000;padding:3px 7px;text-align:center;font-size:11pt;vertical-align:middle;">KETUA PROGRAM STUDI</td>
            </tr>

            <!-- ROW 10: Area TTD (35mm) -->
            <tr>
              <td colspan="6" style="border:1.5px solid #000;padding:5px 14px 6px;text-align:center;height:35mm;vertical-align:bottom;">
                <div style="height:25mm;display:flex;align-items:center;justify-content:center;">
                  <img x-show="formData.bersedia_mundur==='YES' && !signatureEmpty"
                       :src="signatureData"
                       alt="TTD Mahasiswa"
                       style="max-height:24mm;max-width:100mm;object-fit:contain;transform:scale(1.3);transform-origin:center center;">
                </div>
                <div style="font-style:italic;color:#BFBFBF;font-size:8pt;font-family:'Times New Roman',Times,serif;line-height:1.6;">
                  _____________________________<br>TTD &amp; Nama lengkap
                </div>
              </td>
              <td colspan="3" style="border:1.5px solid #000;padding:5px 14px 6px;text-align:center;height:35mm;vertical-align:bottom;">
                <div style="height:25mm;">&nbsp;</div>
                <div style="font-style:italic;color:#BFBFBF;font-size:8pt;font-family:'Times New Roman',Times,serif;line-height:1.6;">
                  _____________________________<br>TTD &amp; Nama lengkap
                </div>
              </td>
            </tr>

            <!-- ROW 11: Catatan -->
            <tr>
              <td colspan="9" style="border:1.5px solid #000;padding:4px 7px;font-size:11pt;vertical-align:top;height:45mm;">Catatan:</td>
            </tr>

          </table>

          <!-- FOOTER BAWAH: kop-header.jpg (AQAS, eqar, KAN, BAN-PT) — 200mm × 28.3mm centered -->
          <img src="<?= APP_URL ?>/assets/images/kop-header.jpg"
               onerror="this.style.display='none'"
               alt="Footer Kop Surat"
               style="width:200mm; max-width:100%; height:28.3mm; object-fit:fill; display:block; margin:auto auto 0 auto; flex-shrink:0;">

        </div><!-- /.doc-preview -->

        <!-- Auto-save notice -->
        <div class="flex items-center gap-2 mt-4 text-gray-400 text-xs">
          <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Data tersimpan otomatis di browser Anda
        </div>
      </div>

      <div class="flex justify-between">
        <button type="button" @click="step=2"
          class="px-6 py-3 text-gray-600 dark:text-gray-400 font-medium rounded-xl border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          Kembali Edit
        </button>
        <button type="button" @click="confirmSubmit"
          :disabled="submitting"
          class="px-8 py-3 bg-gradient-to-r from-nusa to-nusa-dark text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-nusa/30 transition-all duration-200 flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
          <span x-show="!submitting" class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Kirim Pengajuan
          </span>
          <span x-show="submitting" class="flex items-center gap-2">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Mengirim...
          </span>
        </button>
      </div>
    </div>

  </form>
</div>

<style type="text/tailwindcss">
.form-label { @apply block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2; }
.form-input { @apply w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-slate-50/50 hover:bg-slate-50 hover:border-slate-300 dark:bg-slate-800/50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-4 focus:ring-nusa/10 focus:border-nusa focus:bg-white transition-all duration-300 shadow-sm; }
</style>

<script>
function formApp() {
  return {
    step: 1,
    steps: ['Data Mahasiswa', 'Pernyataan & TTD', 'Preview & Kirim'],
    submitting: false,
    signatureData: '',
    signatureEmpty: true,
    signatureHistory: [],
    canvas: null,
    ctx: null,
    isDrawing: false,
    lastX: 0,
    lastY: 0,

    formData: {
      tanggal_surat: new Date().toISOString().slice(0, 10),
      nama_pemohon: '',
      nim: '',
      angkatan: '',
      program_studi: '',
      status_mahasiswa: 'Non Beasiswa',
      bersedia_mundur: '',
      alasan: '',
    },
    errors: {},

    init() {
      // Load draft from localStorage
      const draft = localStorage.getItem('pd_draft_public');
      if (draft) {
        try {
          const saved = JSON.parse(draft);
          this.formData = { ...this.formData, ...saved };
        } catch(e) {}
      }

      // Auto-save draft every 30s
      setInterval(() => this.saveDraft(), 30000);

      this.$watch('formData', () => this.saveDraft());

      // Re-init canvas whenever bersedia_mundur switches to YES
      this.$watch('formData.bersedia_mundur', (val) => {
        if (val === 'YES') {
          // Wait for Alpine x-show transition + DOM paint to complete
          setTimeout(() => this.initCanvas(), 350);
        }
      });
    },

    saveDraft() {
      localStorage.setItem('pd_draft_public', JSON.stringify(this.formData));
    },

    clearDraft() {
      localStorage.removeItem('pd_draft_public');
    },

    nextStep() {
      if (this.step === 1) {
        if (!this.validateStep1()) return;
        this.step = 2;
        // If already YES, init canvas after transition
        if (this.formData.bersedia_mundur === 'YES') {
          setTimeout(() => this.initCanvas(), 350);
        }
      } else if (this.step === 2) {
        if (!this.validateStep2()) return;
        
        if (this.formData.bersedia_mundur === 'NO') {
          this.confirmSubmit();
          return;
        }

        this.step = 3;
      }
    },

    validateStep1() {
      this.errors = {};
      if (!this.formData.tanggal_surat) this.errors.tanggal_surat = 'Tanggal wajib diisi';
      if (!this.formData.nama_pemohon) this.errors.nama_pemohon = 'Nama pemohon wajib diisi';
      if (!this.formData.nim) this.errors.nim = 'NIM wajib diisi';
      if (!this.formData.angkatan) this.errors.angkatan = 'Angkatan wajib diisi';
      if (!this.formData.program_studi) this.errors.program_studi = 'Program studi wajib dipilih';
      if (!this.formData.status_mahasiswa) this.errors.status_mahasiswa = 'Status mahasiswa wajib dipilih';
      
      const isValid = Object.keys(this.errors).length === 0;
      if (!isValid) {
        Swal.fire({ icon:'warning', title:'Data Belum Lengkap', text:'Mohon lengkapi semua field bertanda bintang (*).', confirmButtonColor:'#C1121F' });
      }
      return isValid;
    },

    validateStep2() {
      this.errors = {};
      if (!this.formData.bersedia_mundur) {
        this.errors.bersedia_mundur = 'Pilihan bersedia wajib dipilih';
        Swal.fire({ icon:'warning', title:'Perhatian', text:'Silakan pilih apakah Anda bersedia mengundurkan diri.', confirmButtonColor:'#961d5a' });
        return false;
      }
      if (this.formData.bersedia_mundur === 'YES' && this.signatureEmpty) {
        Swal.fire({ icon:'warning', title:'Tanda Tangan Diperlukan', text:'Silakan buat tanda tangan digital Anda terlebih dahulu.', confirmButtonColor:'#961d5a' });
        return false;
      }
      if (this.formData.bersedia_mundur === 'YES') {
        if (!this.formData.alasan || this.formData.alasan.trim() === '') {
          this.errors.alasan = 'Alasan wajib diisi';
          Swal.fire({ icon:'warning', title:'Alasan Diperlukan', text:'Silakan isi alasan pengunduran diri Anda.', confirmButtonColor:'#961d5a' });
          return false;
        }
      }
      if (this.formData.alasan && this.formData.alasan.length > 1000) {
        this.errors.alasan = 'Alasan maksimal 1000 karakter';
        Swal.fire({ icon:'warning', title:'Alasan Terlalu Panjang', text:'Alasan maksimal 1000 karakter.', confirmButtonColor:'#961d5a' });
        return false;
      }
      return true;
    },

    // === CANVAS SIGNATURE ===
    initCanvas() {
      const c = document.getElementById('signatureCanvas');
      if (!c) return;

      // If canvas container is not visible (0 width), skip — watcher will retry
      const w = c.parentElement ? c.parentElement.clientWidth : 0;
      if (w === 0) return;

      // Only attach listeners once
      if (!this._canvasReady) {
        this.canvas = c;
        this.ctx = this.canvas.getContext('2d');

        // Desktop events
        this.canvas.addEventListener('mousedown',  e => this.startDraw(e));
        this.canvas.addEventListener('mousemove',  e => this.draw(e));
        this.canvas.addEventListener('mouseup',    e => this.endDraw(e));
        this.canvas.addEventListener('mouseleave', e => this.endDraw(e));

        // Touch events
        this.canvas.addEventListener('touchstart', e => { e.preventDefault(); this.startDraw(e.touches[0]); }, {passive:false});
        this.canvas.addEventListener('touchmove',  e => { e.preventDefault(); this.draw(e.touches[0]); }, {passive:false});
        this.canvas.addEventListener('touchend',   e => this.endDraw(e));

        window.addEventListener('resize', () => this.resizeCanvas());
        this._canvasReady = true;
      } else {
        this.canvas = c;
        this.ctx    = this.canvas.getContext('2d');
      }

      this.resizeCanvas();
    },

    resizeCanvas() {
      if (!this.canvas) return;
      const container = this.canvas.parentElement;
      const w = container ? container.clientWidth : 600;
      const h = 200;
      const dpr = window.devicePixelRatio || 1;

      // Save existing drawing before resize
      let imgData = null;
      if (!this.signatureEmpty && this.canvas.width > 0 && this.canvas.height > 0) {
        imgData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
      }

      // Set actual pixel size
      this.canvas.width  = w * dpr;
      this.canvas.height = h * dpr;
      this.canvas.style.width  = w + 'px';
      this.canvas.style.height = h + 'px';

      this.ctx.setTransform(1, 0, 0, 1, 0, 0);
      this.ctx.scale(dpr, dpr);
      this.ctx.strokeStyle = '#1a1a2e';
      this.ctx.lineWidth   = 2.5;
      this.ctx.lineCap     = 'round';
      this.ctx.lineJoin    = 'round';
      this.ctx.lineSmooth  = true;

      // Restore drawing
      if (imgData) this.ctx.putImageData(imgData, 0, 0);
    },

    getPos(e) {
      const rect = this.canvas.getBoundingClientRect();
      return { x: (e.clientX ?? e.pageX) - rect.left, y: (e.clientY ?? e.pageY) - rect.top };
    },

    startDraw(e) {
      this.isDrawing = true;
      const pos = this.getPos(e);
      this.lastX = pos.x;
      this.lastY = pos.y;
      // Save state for undo
      this.signatureHistory.push(this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height));
    },

    draw(e) {
      if (!this.isDrawing) return;
      const pos = this.getPos(e);
      this.ctx.beginPath();
      this.ctx.moveTo(this.lastX, this.lastY);
      this.ctx.lineTo(pos.x, pos.y);
      this.ctx.stroke();
      this.lastX = pos.x;
      this.lastY = pos.y;
      this.signatureEmpty = false;
    },

    endDraw() {
      if (!this.isDrawing) return;
      this.isDrawing = false;
      if (!this.signatureEmpty) {
          this.signatureData = this.canvas.toDataURL('image/png');
      }
    },

    clearSignature() {
      if (!this.canvas) return;
      this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
      this.signatureEmpty   = true;
      this.signatureData    = '';
      this.signatureHistory = [];
    },

    undoSignature() {
      if (this.signatureHistory.length === 0) return;
      const prev = this.signatureHistory.pop();
      this.ctx.putImageData(prev, 0, 0);
      if (this.signatureHistory.length === 0) {
        this.signatureEmpty = true;
        this.signatureData  = '';
      } else {
        this.signatureData = this.canvas.toDataURL('image/png');
      }
    },

    previewSignature() {
      Swal.fire({
        title: 'Preview Tanda Tangan',
        imageUrl: this.signatureData,
        imageAlt: 'Tanda tangan digital',
        confirmButtonColor: '#C1121F',
        confirmButtonText: 'OK',
      });
    },

    // === SUBMIT ===
    confirmSubmit() {
      Swal.fire({
        title: 'Kirim Pengajuan?',
        html: `<p class="text-gray-600">Apakah Anda yakin data yang dimasukkan sudah benar?</p>
               <p class="text-red-600 text-sm mt-2 font-medium">⚠ Pengajuan yang sudah terkirim tidak dapat dibatalkan secara mandiri.</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#C1121F',
        cancelButtonColor: '#6B7280',
        confirmButtonText: '✓ Ya, Kirim Sekarang',
        cancelButtonText: 'Periksa Lagi',
        reverseButtons: true,
      }).then(result => {
        if (result.isConfirmed) this.submitForm();
      });
    },

    async submitForm() {
      this.submitting = true;
      const formEl = document.getElementById('formPD');
      const fd = new FormData(formEl);

      // Append all formData fields
      Object.entries(this.formData).forEach(([k, v]) => fd.set(k, v));
      fd.set('signature_data', this.signatureData);

      try {
        // Refresh CSRF token dulu agar tidak expired (terutama jika halaman sudah lama dibuka)
        const tokenRes = await fetch('<?= APP_URL ?>/?page=mahasiswa/csrf-token');
        const tokenJson = await tokenRes.json();
        if (tokenJson.token) {
          fd.set('csrf_token', tokenJson.token);
          const csrfInput = formEl.querySelector('input[name="csrf_token"]');
          if (csrfInput) csrfInput.value = tokenJson.token;
        }

        const res  = await fetch('<?= APP_URL ?>/?page=mahasiswa/submit', { method:'POST', body:fd });
        const json = await res.json();

        if (json.success) {
          this.clearDraft();
          Swal.fire({
            title: 'Berhasil! 🎉',
            text: 'Pengajuan pengunduran diri berhasil dikirim.',
            icon: 'success',
            confirmButtonColor: '#C1121F',
            confirmButtonText: 'Lihat Hasil',
          }).then(() => {
            window.location.href = json.redirect;
          });
        } else {
          Swal.fire({
            title: 'Gagal',
            text: json.message || 'Terjadi kesalahan. Silakan coba lagi.',
            icon: 'error',
            confirmButtonColor: '#C1121F',
          });
          if (json.errors) this.errors = json.errors;
        }
      } catch(err) {
        Swal.fire({ icon:'error', title:'Error', text:'Terjadi kesalahan koneksi.', confirmButtonColor:'#C1121F' });
      } finally {
        this.submitting = false;
      }
    },

    formatDate(d) {
      if (!d) return '—';
      if (d instanceof Date) {
        d = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
      }
      const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
      const parts = d.split('-');
      return `${parseInt(parts[2])} ${months[parseInt(parts[1])-1]} ${parts[0]}`;
    }
  };
}
</script>

<?php include BASE_PATH . '/includes/public_layout_bottom.php'; ?>
