<?php ?>
  </main>
  
  <footer class="mt-auto px-4 lg:px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-center text-xs text-slate-500 dark:text-slate-400">
    &copy; <?= date('Y') ?> Pascasarjana Universitas Nusa Putra. All rights reserved.
  </footer>
</div>

<div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-2"></div>

<?php if(!empty($pageScript)): ?><script><?= $pageScript ?></script><?php endif; ?>

<script>
// Modal helpers
function openModal(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Search logic if search input exists
const si=document.getElementById('global-search'),sr=document.getElementById('search-results');
let st;
if (si) {
  si.addEventListener('input',()=>{
    clearTimeout(st);
    const q=si.value.trim();
    if(q.length<2){sr.classList.add('hidden');return;}
    st=setTimeout(()=>{
      fetch(`<?=BASE_URL?>/api/search.php?q=${encodeURIComponent(q)}`)
      .then(r=>r.json())
      .then(d=>{
        if(!d.length){sr.classList.add('hidden');return;}
        sr.innerHTML=d.map(i=>`<a href="${i.url}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm border-b border-slate-100 dark:border-slate-700 last:border-0"><span class="text-blue-500 font-medium text-xs">${i.type}</span><span class="text-slate-700 dark:text-slate-300">${i.label}</span></a>`).join('');
        sr.classList.remove('hidden');
      }).catch(()=>{})
    },350);
  });
}
document.addEventListener('click',()=>sr?.classList.add('hidden'));

// Global keyboard shortcuts
document.addEventListener('keydown',e=>{
  if(e.ctrlKey&&e.key==='n'){
    e.preventDefault();
    window.location.href='<?=BASE_URL?>/pages/surat?action=new';
  }
});
</script>
</body>
</html>
