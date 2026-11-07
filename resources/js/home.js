import { readCategories, ADMIN_CATEGORIES_KEY, ADMIN_CATEGORIES_EVENT } from './admin-category-store.js';
const experiences = [...document.querySelectorAll('.jw-experience')];
const seedElement = document.getElementById('jw-home-categories-seed');
const homeCategorySeed = seedElement ? JSON.parse(seedElement.textContent) : null;
const toast = document.querySelector('#jw-toast');
const dialog = document.querySelector('#jw-dialog');
const dialogTitle = document.querySelector('#jw-dialog-title');
const dialogBody = document.querySelector('#jw-dialog-body');
const locationOptions = ['Coblong, Bandung', 'Sukajadi, Bandung', 'Lengkong, Bandung', 'Antapani, Bandung', 'Astanaanyar, Bandung'];
const state = { location: locationOptions[0] };
let toastTimer;

const labels = { ac: 'Servis AC', pipa: 'Air & Pipa', listrik: 'Listrik', bangunan: 'Bangunan', elektronik: 'Elektronik', jahit: 'Jahit', kebersihan: 'Kebersihan', all: 'Semua' };
const demoNotice = 'Ini masih prototipe frontend. Pemesanan dan data real-time belum dihubungkan ke backend.';

function showToast(message) {
  if (!toast) return;
  window.clearTimeout(toastTimer);
  toast.textContent = message;
  toast.classList.remove('hidden');
  toastTimer = window.setTimeout(() => toast.classList.add('hidden'), 4200);
}

function openDialog(title, content) {
  if (!dialog || !dialogBody) return;
  dialogTitle.textContent = title;
  dialogBody.replaceChildren();
  if (typeof content === 'string') {
    const paragraph = document.createElement('p');
    paragraph.textContent = content;
    dialogBody.appendChild(paragraph);
  } else {
    dialogBody.appendChild(content);
  }
  if (!dialog.open) dialog.showModal();
}

function locationPicker() {
  const wrapper = document.createElement('div');
  wrapper.className = 'grid gap-2';
  const note = document.createElement('p');
  note.className = 'mb-2 text-on-surface-variant';
  note.textContent = 'Pilih lokasi tampilan. Daftar mitra dan jarak masih berupa data contoh dari desain.';
  wrapper.appendChild(note);
  locationOptions.forEach((location) => {
    const choice = document.createElement('button');
    choice.type = 'button';
    choice.className = 'w-full rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-3 text-left font-semibold hover:border-primary focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary';
    choice.textContent = location + (location === state.location ? ' ✓' : '');
    choice.addEventListener('click', () => {
      state.location = location;
      document.querySelectorAll('[data-jw-location-label]').forEach(el => { el.textContent = location; });
      dialog.close();
      showToast(`Lokasi tampilan: ${location}. Data mitra masih contoh.`);
    });
    wrapper.appendChild(choice);
  });
  openDialog('Pilih lokasi', wrapper);
}

function initExperience(view) {
  const local = { query: '', category: 'all', filter: 'all' };
  const input = view.querySelector('[data-jw-search]');
  const cards = [...view.querySelectorAll('.jw-provider-card')];
  const empty = view.querySelector('.jw-empty-state');
  let activeCategories = homeCategorySeed ? new Set(readCategories(homeCategorySeed).filter(category => category.active).map(category => category.slug)) : null;

  function updateCards() {
    let shown = 0;
    for (const card of cards) {
      const categoryMatch = (local.category === 'all' || card.dataset.jwCategory === local.category) && (!activeCategories || activeCategories.has(card.dataset.jwCategory));
      const queryMatch = !local.query || card.textContent.toLocaleLowerCase('id').includes(local.query);
      const distance = Number(card.dataset.jwDistance || 100);
      const rating = Number(card.dataset.jwRating || 0);
      const filterMatch = local.filter === 'all' ||
        (local.filter === 'nearby' && distance < 3) ||
        (local.filter === 'rating' && rating >= 4.8) ||
        (local.filter === 'warranty' && card.dataset.jwWarranty === 'true') ||
        (local.filter === 'available' && card.dataset.jwAvailable === 'true');
      const display = categoryMatch && queryMatch && filterMatch;
      card.classList.toggle('jw-is-hidden', !display);
      if (display) shown++;
    }
    if (empty) {
      empty.classList.toggle('hidden', shown > 0);
      empty.classList.toggle('jw-is-hidden', shown > 0);
    }
    view.querySelectorAll('[data-jw-action="category"]').forEach(btn => {
      if (btn.dataset.jwCategory !== 'all') btn.style.display = activeCategories && !activeCategories.has(btn.dataset.jwCategory) ? 'none' : '';
      const active = btn.dataset.jwCategory === local.category;
      btn.setAttribute('aria-pressed', String(active));
      btn.classList.toggle('jw-selected-category', active && local.category !== 'all');
    });
    view.querySelectorAll('[data-jw-action="filter-pill"]').forEach(btn => {
      const active = btn.dataset.jwFilter === local.filter;
      btn.setAttribute('aria-pressed', String(active));
      btn.classList.toggle('jw-active-pill', active);
    });
    return shown;
  }

  function scrollTo(target) {
    const el = view.querySelector(target);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  function search() {
    local.query = (input?.value || '').trim().toLocaleLowerCase('id');
    const found = updateCards();
    scrollTo('[data-jw-list-target]');
    showToast(`${found} mitra contoh ditemukan${local.query ? ` untuk “${input.value.trim()}”` : ''}.`);
  }
  input?.addEventListener('input', () => {
    local.query = input.value.trim().toLocaleLowerCase('id');
    updateCards();
  });
  input?.addEventListener('keydown', event => {
    if (event.key === 'Enter') { event.preventDefault(); search(); }
  });

  view.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-jw-action]');
    if (!trigger || !view.contains(trigger)) return;
    const action = trigger.dataset.jwAction;
    if (trigger.tagName === 'A' && action !== 'categories' && action !== 'help') event.preventDefault();
    switch (action) {
      case 'search': search(); break;
      case 'category': {
        local.category = trigger.dataset.jwCategory || 'all';
        local.filter = 'all';
        const found = updateCards();
        showToast(`${labels[local.category] || 'Layanan'}: ${found} mitra contoh.`);
        scrollTo('[data-jw-list-target]');
        break;
      }
      case 'filter-pill':
        local.filter = trigger.dataset.jwFilter || 'all';
        updateCards();
        break;
      case 'location': locationPicker(); break;
      case 'notifications': openDialog('Kabar warga', 'Notifikasi baru akan ditampilkan ketika sistem backend terhubung.'); break;
      case 'profile': { const id = Number(trigger.dataset.jwProviderId || trigger.closest('[data-jw-provider-id]')?.dataset.jwProviderId); if (Number.isInteger(id) && id > 0) window.location.assign(`/mitra/${id}`); break; }
      case 'order': { const id = Number(trigger.dataset.jwProviderId || trigger.closest('[data-jw-provider-id]')?.dataset.jwProviderId); if (Number.isInteger(id) && id > 0) window.location.assign(`/pemesanan/${id}`); else window.location.assign('/kategori-jasa'); break; }
      case 'orders': window.location.assign('/akun/pesanan'); break;
      case 'quick-order': { const role = document.documentElement.dataset.jwServerRole; window.location.assign(role === 'citizen' ? '/kategori-jasa' : role === 'partner' ? '/mitra/portal' : role === 'admin' ? '/admin/overview' : '/masuk'); break; }
      case 'rates': openDialog('Standar tarif', 'Harga yang terlihat pada Home merupakan data contoh. Tarif resmi akan ditampilkan setelah sumber data terintegrasi.'); break;
      case 'filter-help':
        scrollTo('[data-jw-list-target]');
        showToast('Pilih chip Semua, Terdekat, Rating, Garansi, atau Tersedia Hari Ini untuk menyaring mitra.');
        break;
      case 'categories': event.preventDefault(); scrollTo('#kategori-layanan'); break;
      case 'list': scrollTo('[data-jw-list-target]'); break;
      case 'map': scrollTo('[data-jw-map-target]'); window.dispatchEvent(new Event('jasawarga:map-focus')); break;
      case 'zoom': window.dispatchEvent(new CustomEvent('jasawarga:map-zoom',{detail:trigger.dataset.zoomDirection==='out'?-1:1})); break;
      case 'my-location': window.dispatchEvent(new Event('jasawarga:map-center')); break;
      case 'radius': window.dispatchEvent(new Event('jasawarga:map-focus')); break;
      case 'help': event.preventDefault(); openDialog('Bantuan Warga', 'Halaman bantuan lengkap akan dibuat pada tahap berikutnya.'); break;
      case 'contact': openDialog('Kontak JasaWarga', 'Hubungi layanan warga melalui kanal resmi setelah diintegrasikan.'); break;
      case 'home': window.scrollTo({ top: 0, behavior: 'smooth' }); break;
      case 'account': window.location.assign('/akun'); break;
      case 'link-preview': showToast('Tautan ini belum memiliki halaman tujuan pada prototipe Home.'); break;
      default: break;
    }
  });
  function refreshCategories() {
    if (!homeCategorySeed) return;
    activeCategories = new Set(readCategories(homeCategorySeed).filter(category => category.active).map(category => category.slug));
    if (!activeCategories.has(local.category)) local.category = 'all';
    updateCards();
  }
  window.addEventListener(ADMIN_CATEGORIES_EVENT, refreshCategories);
  window.addEventListener('storage', event => {
    if (event.key === ADMIN_CATEGORIES_KEY || event.key === null) refreshCategories();
  });
  updateCards();
}

experiences.forEach(initExperience);

document.addEventListener('click', (event) => {
  if (event.target.closest('[data-jw-action="close-dialog"]')) dialog?.close();
});
dialog?.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });

document.querySelectorAll('img[data-fallback-url]').forEach(img => {
  img.addEventListener('error', () => {
    if (img.src !== new URL(img.dataset.fallbackUrl, location.href).href) {
      img.src = img.dataset.fallbackUrl;
    }
  });
});
