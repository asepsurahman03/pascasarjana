  </main>

  <!-- FOOTER -->
  <footer class="px-4 lg:px-6 py-3 border-t border-gray-100 dark:border-gray-700 text-center">
    <p class="text-gray-400 dark:text-gray-500 text-xs">
      © <?= date('Y') ?> <span class="font-semibold text-nusa">Universitas Nusa Putra</span> · Sistem Pengunduran Diri Mahasiswa · v<?= APP_VERSION ?>
    </p>
  </footer>
</div>

<!-- SESSION TIMEOUT CHECK -->
<script>
(function(){
  const timeout = <?= SESSION_TIMEOUT * 1000 ?>;
  const warningAt = timeout - 60000; // warn 1 min before
  let warned = false;

  setTimeout(function(){
    if(!warned){
      warned = true;
      Swal.fire({
        title: 'Sesi Hampir Berakhir',
        text: 'Sesi Anda akan berakhir dalam 1 menit. Simpan pekerjaan Anda.',
        icon: 'warning',
        timer: 60000,
        timerProgressBar: true,
        showCancelButton: true,
        confirmButtonText: 'Perpanjang Sesi',
        cancelButtonText: 'Logout',
        confirmButtonColor: '#C1121F',
      }).then(result => {
        if(result.dismiss === Swal.DismissReason.cancel){
          window.location.href = '<?= APP_URL ?>/?page=logout';
        } else {
          // Ping server to keep session alive
          fetch('<?= APP_URL ?>/?page=mahasiswa/dashboard', {method:'HEAD'});
        }
      });
    }
  }, warningAt > 0 ? warningAt : 1000);
})();
</script>

</body>
</html>
