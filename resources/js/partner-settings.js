import {
  PARTNER_ACCOUNT_KEY, PARTNER_ACCOUNT_EVENT,
  readPartnerAccount, savePartnerIdentity, savePartnerPreferences,
  validateDemoPassword, enterPartnerDemo, exitPartnerDemo,
} from './partner-account-store.js';
import {
  PARTNER_SETTINGS_KEY, PARTNER_SETTINGS_EVENT,
  getPartnerAvailability, setPartnerAvailability,
} from './partner-store.js';

// Pemilik akun demo juga terlihat di footer sidebar seluruh halaman Mitra.
const sidebar = document.querySelector('[data-ps-sidebar-provider]');
if (sidebar) {
  const partnerId = Number(sidebar.dataset.psSidebarProvider);
  const renderOwner = () => {
    const name = sidebar.querySelector('[data-ps-owner-sidebar]');
    if (name) name.textContent = readPartnerAccount(partnerId).ownerName;
  };
  window.addEventListener(PARTNER_ACCOUNT_EVENT, event => {
    if (event.detail?.providerId === partnerId) renderOwner();
  });
  window.addEventListener('storage', event => {
    if (event.key === PARTNER_ACCOUNT_KEY || event.key === null) renderOwner();
  });
  renderOwner();
}

// Landing setelah logout. Ini BUKAN formulir autentikasi yang aman.
const entry = document.querySelector('#jw-partner-demo-entry');
if (entry) {
  entry.querySelector('[data-ps-enter]').addEventListener('click', () => {
    try { enterPartnerDemo(); } catch (_) { /* tanda sesi boleh tidak tersedia */ }
    window.location.assign('/mitra/dashboard');
  });
}

const root = document.querySelector('#jw-partner-account');
if (root) {
  const id = Number(root.dataset.partnerId);
  const identityForm = root.querySelector('[data-ps-identity-form]');
  const prefForm = root.querySelector('[data-ps-preferences-form]');
  const passwordForm = root.querySelector('[data-ps-password-form]');
  const toast = root.querySelector('[data-ps-toast]');
  const logoutDialog = root.querySelector('#jw-ps-logout-dialog');
  const $ = selector => root.querySelector(selector);
  const $$ = selector => [...root.querySelectorAll(selector)];
  let toastTimer;

  const message = (selector, text) => {
    const el = $(selector);
    if (!el) return;
    el.textContent = text;
    el.classList.toggle('hidden', !text);
  };

  function notify(text) {
    toast.textContent = text;
    toast.classList.remove('hidden');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.add('hidden'), 3200);
  }

  function readAndShow() {
    const current = readPartnerAccount(id);
    for (const field of ['ownerName', 'email', 'phone']) {
      identityForm.elements[field].value = current[field];
    }
    for (const [group, selector] of [
      ['notifications', '[data-ps-notification]'],
      ['privacy', '[data-ps-privacy]'],
      ['operations', '[data-ps-operation]'],
    ]) {
      for (const element of $$(selector)) {
        const key = element.dataset[
          group === 'notifications' ? 'psNotification' :
          group === 'privacy' ? 'psPrivacy' : 'psOperation'
        ];
        element.checked = current[group][key];
      }
    }
    $('[data-ps-updated]').textContent = current.updatedAt
      ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(current.updatedAt))
      : 'Belum ada';
    showAvailability();
  }

  function showAvailability() {
    const available = getPartnerAvailability(id).available;
    $('[data-ps-availability]').checked = available;
    $('[data-ps-availability-label]').textContent = available
      ? 'Menerima pesanan baru (simulasi)'
      : 'Tidak menerima pesanan baru (simulasi)';
  }

  identityForm.addEventListener('submit', event => {
    event.preventDefault();
    message('[data-ps-identity-error]', '');
    try {
      savePartnerIdentity(id, {
        ownerName: identityForm.elements.ownerName.value,
        email: identityForm.elements.email.value,
        phone: identityForm.elements.phone.value,
      });
      readAndShow();
      notify('Informasi pemilik akun demo tersimpan di browser.');
    } catch (error) {
      message('[data-ps-identity-error]', error.message);
    }
  });

  prefForm.addEventListener('submit', event => {
    event.preventDefault();
    message('[data-ps-preferences-error]', '');
    try {
      const group = selector => Object.fromEntries(
        $$(selector).map(el => [
          selector === '[data-ps-notification]' ? el.dataset.psNotification :
          selector === '[data-ps-privacy]' ? el.dataset.psPrivacy : el.dataset.psOperation,
          el.checked,
        ])
      );
      savePartnerPreferences(id, {
        notifications: group('[data-ps-notification]'),
        privacy: group('[data-ps-privacy]'),
        operations: group('[data-ps-operation]'),
      });
      readAndShow();
      notify('Preferensi Mitra berhasil disimpan.');
    } catch (error) {
      message('[data-ps-preferences-error]', error.message);
    }
  });

  $('[data-ps-availability]').addEventListener('change', event => {
    try {
      setPartnerAvailability(id, event.target.checked);
      showAvailability();
      notify(event.target.checked ? 'Mitra menerima pesanan simulasi.' : 'Pemesanan baru dijeda.');
    } catch (error) {
      showAvailability();
      notify(error.message);
    }
  });

  passwordForm.addEventListener('submit', event => {
    event.preventDefault();
    const result = $('[data-ps-password-message]');
    const newPassword = passwordForm.elements.newPassword.value;
    const confirmation = passwordForm.elements.confirmation.value;
    try {
      validateDemoPassword(newPassword, confirmation);
      result.className = 'rounded-xl bg-primary-fixed px-3 py-2.5 text-xs font-medium text-primary';
      result.textContent = 'Validasi demo berhasil. Password tidak benar-benar diubah maupun disimpan.';
    } catch (error) {
      result.className = 'rounded-xl bg-error-container px-3 py-2.5 text-xs font-medium text-on-error-container';
      result.textContent = error.message;
    } finally {
      // Jangan pernah menyimpan, mengirim, mencatat, atau mempertahankan isian password.
      passwordForm.reset();
    }
  });

  $('[data-ps-logout]').addEventListener('click', () => logoutDialog.showModal());
  $('[data-ps-cancel-logout]').addEventListener('click', () => logoutDialog.close());
  // Form POST /keluar menutup sesi Laravel; hanya redirect klien tidak cukup.
  logoutDialog.addEventListener('click', event => {
    if (event.target === logoutDialog) logoutDialog.close();
  });

  window.addEventListener('storage', event => {
    if (event.key === PARTNER_ACCOUNT_KEY || event.key === null) readAndShow();
    if (event.key === PARTNER_SETTINGS_KEY) showAvailability();
  });
  window.addEventListener(PARTNER_ACCOUNT_EVENT, event => {
    if (event.detail?.providerId === id) readAndShow();
  });
  window.addEventListener(PARTNER_SETTINGS_EVENT, showAvailability);
  readAndShow();
}
