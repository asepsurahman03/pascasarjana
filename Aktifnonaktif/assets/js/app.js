/**
 * app.js — Global JavaScript Utilities
 * Universitas Nusa Putra - Sistem Pengunduran Diri Mahasiswa
 */

'use strict';

// ============================================================
// UTILITY: Safe fetch with error handling
// ============================================================
async function apiFetch(url, options = {}) {
    const defaults = {
        method:  'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    };
    const config = { ...defaults, ...options };

    try {
        const res = await fetch(url, config);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return await res.json();
    } catch (err) {
        console.error('[apiFetch]', err);
        throw err;
    }
}

// ============================================================
// TOAST NOTIFICATION (using SweetAlert2)
// ============================================================
const Toast = typeof Swal !== 'undefined' ? Swal.mixin({
    toast:              true,
    position:           'top-end',
    showConfirmButton:  false,
    timer:              3500,
    timerProgressBar:   true,
    showCloseButton:    true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
}) : null;

function showToast(type, message) {
    if (!Toast) { alert(message); return; }
    Toast.fire({ icon: type, title: message });
}
function toastSuccess(msg) { showToast('success', msg); }
function toastError(msg)   { showToast('error',   msg); }
function toastWarning(msg) { showToast('warning', msg); }
function toastInfo(msg)    { showToast('info',    msg); }

// ============================================================
// CONFIRM DIALOG
// ============================================================
async function confirmDialog(options = {}) {
    if (typeof Swal === 'undefined') return window.confirm(options.text || 'Konfirmasi?');

    const result = await Swal.fire({
        title:             options.title             || 'Konfirmasi',
        html:              options.html              || options.text || 'Apakah Anda yakin?',
        icon:              options.icon              || 'question',
        showCancelButton:  true,
        confirmButtonColor: options.confirmColor     || '#C1121F',
        cancelButtonColor: '#6B7280',
        confirmButtonText: options.confirmText       || 'Ya',
        cancelButtonText:  options.cancelText        || 'Batal',
        reverseButtons:    options.reverseButtons    !== false,
    });

    return result.isConfirmed;
}

// ============================================================
// LOADING OVERLAY
// ============================================================
let loadingEl = null;

function showLoading(message = 'Memproses...') {
    if (loadingEl) return;
    loadingEl = document.createElement('div');
    loadingEl.id = 'global-loading';
    loadingEl.innerHTML = `
        <div style="position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
            <div style="background:#fff;border-radius:1rem;padding:2rem 2.5rem;text-align:center;box-shadow:0 25px 50px rgba(0,0,0,0.25);">
                <div style="width:3rem;height:3rem;border:3px solid #f1f5f9;border-top-color:#C1121F;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto 1rem;"></div>
                <p style="color:#374151;font-weight:600;font-size:.9rem;font-family:Inter,sans-serif;">${message}</p>
            </div>
        </div>
        <style>@keyframes spin{to{transform:rotate(360deg)}}</style>
    `;
    document.body.appendChild(loadingEl);
}

function hideLoading() {
    if (loadingEl) {
        loadingEl.remove();
        loadingEl = null;
    }
}

// ============================================================
// SKELETON LOADER
// ============================================================
function showSkeletons(container, count = 3) {
    const el = typeof container === 'string' ? document.querySelector(container) : container;
    if (!el) return;

    let html = '';
    for (let i = 0; i < count; i++) {
        html += `
            <div class="card p-4 mb-3 animate-skeleton" style="opacity:${1 - i * 0.15}">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:40px;height:40px;border-radius:50%;background:#e2e8f0;flex-shrink:0;"></div>
                    <div style="flex:1;">
                        <div style="height:12px;background:#e2e8f0;border-radius:4px;margin-bottom:8px;width:60%;"></div>
                        <div style="height:10px;background:#e2e8f0;border-radius:4px;width:40%;"></div>
                    </div>
                </div>
            </div>
        `;
    }
    el.innerHTML = html;
}

// ============================================================
// FORM: Auto-save to localStorage
// ============================================================
function autoSaveForm(formId, storageKey) {
    const form = document.getElementById(formId);
    if (!form) return;

    // Restore saved values
    try {
        const saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
        Object.entries(saved).forEach(([name, value]) => {
            const el = form.elements[name];
            if (!el) return;
            if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = el.value === value;
            } else {
                el.value = value;
            }
        });
    } catch(e) {}

    // Save on input
    form.addEventListener('input', () => {
        const data = {};
        new FormData(form).forEach((val, key) => {
            if (key !== 'csrf_token' && key !== 'signature_data') {
                data[key] = val;
            }
        });
        localStorage.setItem(storageKey, JSON.stringify(data));
    });

    return {
        clear: () => localStorage.removeItem(storageKey),
    };
}

// ============================================================
// FORM: Client-side validation helpers
// ============================================================
function validateField(input) {
    const value   = input.value.trim();
    const min     = input.dataset.min ? parseInt(input.dataset.min) : null;
    const max     = input.dataset.max ? parseInt(input.dataset.max) : null;
    const pattern = input.dataset.pattern || null;

    input.classList.remove('is-invalid', 'is-valid');

    if (input.required && !value) {
        setFieldError(input, 'Field ini wajib diisi');
        return false;
    }
    if (min && value.length < min) {
        setFieldError(input, `Minimal ${min} karakter`);
        return false;
    }
    if (max && value.length > max) {
        setFieldError(input, `Maksimal ${max} karakter`);
        return false;
    }
    if (pattern && !new RegExp(pattern).test(value)) {
        setFieldError(input, input.dataset.patternMsg || 'Format tidak valid');
        return false;
    }
    if (input.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        setFieldError(input, 'Format email tidak valid');
        return false;
    }

    input.classList.add('is-valid');
    clearFieldError(input);
    return true;
}

function setFieldError(input, message) {
    input.classList.add('is-invalid');
    let fb = input.nextElementSibling;
    if (!fb || !fb.classList.contains('invalid-feedback')) {
        fb = document.createElement('p');
        fb.className = 'invalid-feedback text-nusa text-xs mt-1';
        input.insertAdjacentElement('afterend', fb);
    }
    fb.textContent = message;
}

function clearFieldError(input) {
    input.classList.remove('is-invalid');
    const fb = input.nextElementSibling;
    if (fb && fb.classList.contains('invalid-feedback')) fb.remove();
}

// ============================================================
// CHARACTER COUNTER
// ============================================================
function initCharCounter(textareaId, counterId, min = 0, max = 1000) {
    const ta   = document.getElementById(textareaId);
    const ctr  = document.getElementById(counterId);
    if (!ta || !ctr) return;

    const update = () => {
        const len = ta.value.length;
        ctr.textContent = `${len}/${max}`;
        ctr.style.color = len < min ? '#EF4444' : (len > max * 0.9 ? '#F59E0B' : '#94A3B8');
    };

    ta.addEventListener('input', update);
    update();
}

// ============================================================
// AJAX DELETE with confirmation
// ============================================================
async function deleteWithConfirm(url, data = {}, reloadOnSuccess = true) {
    const confirmed = await confirmDialog({
        title: 'Hapus Data?',
        text:  'Data yang dihapus tidak dapat dikembalikan!',
        icon:  'warning',
        confirmText: 'Hapus!',
    });

    if (!confirmed) return false;

    showLoading('Menghapus...');
    try {
        const fd = new FormData();
        if (typeof CSRF_TOKEN !== 'undefined') {
            fd.append(typeof CSRF_TOKEN_NAME !== 'undefined' ? CSRF_TOKEN_NAME : 'csrf_token', CSRF_TOKEN);
        }
        Object.entries(data).forEach(([k, v]) => fd.append(k, v));

        const res  = await fetch(url, { method: 'POST', body: fd });
        const json = await res.json();

        hideLoading();

        if (json.success) {
            toastSuccess(json.message || 'Data berhasil dihapus');
            if (reloadOnSuccess) setTimeout(() => location.reload(), 1000);
            return true;
        } else {
            toastError(json.message || 'Gagal menghapus data');
            return false;
        }
    } catch(err) {
        hideLoading();
        toastError('Terjadi kesalahan koneksi');
        return false;
    }
}

// ============================================================
// REALTIME SEARCH (debounce)
// ============================================================
function initRealtimeSearch(inputId, formId, delay = 400) {
    const input = document.getElementById(inputId);
    const form  = document.getElementById(formId);
    if (!input || !form) return;

    let timer;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => form.submit(), delay);
    });
}

// ============================================================
// TABLE ROW HIGHLIGHT
// ============================================================
function initTableHighlight(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    table.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('click', () => {
            table.querySelectorAll('tbody tr').forEach(r => r.classList.remove('bg-nusa/5'));
            row.classList.add('bg-nusa/5');
        });
    });
}

// ============================================================
// SCROLL ANIMATIONS (IntersectionObserver)
// ============================================================
function initScrollAnimations() {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fadeInUp');
                entry.target.style.opacity = '1';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('[data-animate]').forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });
}

// ============================================================
// DARK MODE TOGGLE
// ============================================================
function initDarkMode() {
    const saved = localStorage.getItem('darkMode') === 'true';
    if (saved) document.documentElement.classList.add('dark');
}

// ============================================================
// NUMBER FORMAT
// ============================================================
function formatNumber(n) {
    return new Intl.NumberFormat('id-ID').format(n);
}

// ============================================================
// COPY TO CLIPBOARD
// ============================================================
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        toastSuccess('Disalin ke clipboard!');
    } catch {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        ta.remove();
        toastSuccess('Disalin!');
    }
}

// ============================================================
// DOM READY INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    // Scroll animations
    initScrollAnimations();

    // Dark mode
    initDarkMode();

    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('[data-auto-dismiss]').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, parseInt(el.dataset.autoDismiss) || 5000);
    });

    // Confirm on delete buttons
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', async e => {
            const confirmed = await confirmDialog({
                title: btn.dataset.confirmTitle || 'Konfirmasi',
                text:  btn.dataset.confirm,
            });
            if (!confirmed) e.preventDefault();
        });
    });

    // Print buttons
    document.querySelectorAll('[data-print]').forEach(btn => {
        btn.addEventListener('click', () => window.print());
    });

    // Console branding
    console.log(
        '%c🎓 Universitas Nusa Putra%c\nSistem Pengunduran Diri Mahasiswa',
        'color:#C1121F;font-weight:900;font-size:16px;font-family:Poppins,sans-serif;',
        'color:#64748B;font-size:12px;'
    );
});
