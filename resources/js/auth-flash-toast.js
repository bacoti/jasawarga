// Flash notifikasi server Laravel: tepat 3 detik dari saat halaman selesai siap.
// Hanya pesan success yang sudah di-escape oleh Blade yang ditampilkan.
function initializeAuthFlashToast() {
  const toast = document.querySelector('[data-jw-auth-flash]');
  if (!toast || toast.dataset.jwInitialized === 'true') return;
  toast.dataset.jwInitialized = 'true';

  const delay = Number(toast.getAttribute('data-jw-duration')) || 3000;
  let closed = false;
  function dismiss() {
    if (closed) return;
    closed = true;
    toast.classList.add('jw-auth-flash--leaving');
    toast.setAttribute('aria-hidden', 'true');
    window.setTimeout(() => toast.remove(), 200);
  }

  toast.querySelector('[data-jw-auth-flash-close]')?.addEventListener('click', dismiss);
  window.setTimeout(dismiss, delay);
}

if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAuthFlashToast, { once: true });
  } else {
    initializeAuthFlashToast();
  }
}
