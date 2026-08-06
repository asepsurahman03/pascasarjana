  </main>
  <footer class="px-4 lg:px-6 py-3 border-t border-slate-200 dark:border-slate-700 text-center">
    <p class="text-slate-400 text-xs">
      © <?= date('Y') ?> <span class="font-semibold text-nusa">Universitas Nusa Putra</span> · Administrator Panel · v<?= APP_VERSION ?>
    </p>
  </footer>
</div>

<script>
// Global CSRF token for AJAX
const CSRF_TOKEN_NAME = '<?= CSRF_TOKEN_NAME ?>';
const CSRF_TOKEN = '<?= csrfToken() ?>';
const BASE_URL   = '<?= APP_URL ?>';

// Toast notification helper
function showToast(type, message) {
  const Toast = Swal.mixin({
    toast: true, position: 'top-end', showConfirmButton: false,
    timer: type === 'success' ? 5000 : 4000, timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });
  Toast.fire({ icon: type, title: message });
}

// Generic confirm + AJAX action
async function ajaxAction(url, data, successMsg, errorMsg) {
  const fd = new FormData();
  fd.append(CSRF_TOKEN_NAME, CSRF_TOKEN);
  Object.entries(data).forEach(([k,v]) => fd.append(k, v));

  try {
    const res  = await fetch(url, { method: 'POST', body: fd });
    const text = await res.text();
    let json;
    try {
      json = JSON.parse(text);
    } catch (e) {
      console.error("Non-JSON response:", text);
      showToast('error', 'Terjadi kesalahan sistem. Cek console log.');
      return false;
    }
    
    if (json.success) {
      showToast('success', json.message || successMsg);
      return true;
    } else {
      showToast('error', json.message || errorMsg);
      return false;
    }
  } catch(e) {
    showToast('error', 'Terjadi kesalahan koneksi.');
    return false;
  }
}
</script>
</body>
</html>
