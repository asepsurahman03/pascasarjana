  </main>
  <!-- Footer -->
  <footer class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400 dark:text-slate-600">
    SIAKAD Pascasarjana © <?= date('Y') ?> Universitas Nusa Putra — Portal Mahasiswa
  </footer>
</div>

<?php if (!empty($pageScript)): ?><script><?= $pageScript ?></script><?php endif; ?>

<script>
function openModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) { el.classList.add('hidden'); document.body.style.overflow = 'auto'; }
}
</script>
</body>
</html>
