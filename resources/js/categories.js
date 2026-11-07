/* Katalog jasa JasaWarga: interaksi frontend saja, tanpa API/backend. */
import { readCategories, ADMIN_CATEGORIES_KEY, ADMIN_CATEGORIES_EVENT } from './admin-category-store.js';
import { readPartnerModeration, partnerVerification, ADMIN_PARTNER_KEY, ADMIN_MANAGEMENT_EVENT } from './admin-management-store.js';

// Tautkan tombol kategori di Home lama tanpa mengganti HTML Home yang panjang.
if (document.querySelector('.jw-experience')) {
  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-jw-action]');
    if (!button || !button.closest('.jw-experience')) return;
    const action = button.dataset.jwAction;
    if (action !== 'categories' && action !== 'category') return;

    event.preventDefault();
    event.stopPropagation();
    const target = new URL('/kategori-jasa', window.location.origin);
    if (action === 'category' && button.dataset.jwCategory && button.dataset.jwCategory !== 'all') {
      target.searchParams.set('kategori', button.dataset.jwCategory);
    }
    window.location.assign(target.toString());
  }, true);
}

const catalog = document.querySelector('#jw-catalog');
if (catalog) {
  const cardsContainer = catalog.querySelector('[data-catalog-results]');
  const categorySeed = JSON.parse(catalog.querySelector('[data-catalog-categories-seed]').textContent);
  const categoryGrid = catalog.querySelector('[data-catalog-category-grid]');
  const allCategoryTile = categoryGrid.querySelector('[data-catalog-category="all"]');
  function renderCategoryTiles() {
    const items = readCategories(categorySeed).filter(category => category.active);
    categoryGrid.replaceChildren();
    items.forEach(category => {
      const button = document.createElement('button');
      button.type = 'button'; button.dataset.catalogCategory = category.slug;
      button.setAttribute('aria-pressed', 'false');
      button.className = 'group flex min-w-0 flex-col items-center gap-2 rounded-2xl border border-outline-variant/40 bg-white px-1.5 py-4 text-center shadow-sm transition hover:border-primary/60 hover:shadow-md sm:px-3';
      const glyph = document.createElement('span');
      glyph.className = 'flex h-12 w-12 items-center justify-center rounded-xl transition group-hover:scale-105 sm:h-14 sm:w-14';
      glyph.style.backgroundColor = category.bg; glyph.style.color = category.fg;
      const icon = document.createElement('span');icon.className = 'material-symbols-outlined text-[27px]';icon.textContent = category.icon;glyph.append(icon);
      const title = document.createElement('span');title.className = 'text-[11px] font-bold leading-tight text-on-surface sm:text-xs';title.textContent = category.name;
      const count = document.createElement('span');count.className = 'hidden text-[10px] text-outline lg:block';
      count.textContent = `${cards.filter(card => card.dataset.category === category.slug).length} mitra demo`;
      button.append(glyph,title,count);categoryGrid.append(button);
    });
    categoryGrid.append(allCategoryTile);
    return new Set(items.map(category => category.slug));
  }

  const cards = [...catalog.querySelectorAll('.jw-catalog-card')];
  function renderVerification() {
    const moderation = readPartnerModeration();
    cards.forEach(card => {
      const badge = card.querySelector('[data-card-verification]');
      if (!badge) return;
      const status = partnerVerification({id: Number(card.dataset.providerId), verified: card.dataset.seedVerified === '1'},moderation);
      badge.textContent = ({verified: 'Terverifikasi (demo)',pending: 'Belum diverifikasi (demo)',rejected: 'Ditolak (demo)'})[status];
      badge.className = `inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${status === 'verified' ? 'bg-secondary-fixed text-on-secondary-fixed' : status === 'rejected' ? 'bg-rose-50 text-rose-800' : 'bg-amber-50 text-amber-800'}`;
    });
  }
  window.addEventListener(ADMIN_MANAGEMENT_EVENT, renderVerification);
  window.addEventListener('storage', event => { if (event.key === ADMIN_PARTNER_KEY || event.key === null) renderVerification(); });
  renderVerification();
  let categories = new Set(['all',...renderCategoryTiles()]);
  const params = new URLSearchParams(window.location.search);
  const initialCategory = params.get('kategori') || 'all';
  const state = {
    category: categories.has(initialCategory) ? initialCategory : 'all',
    search: params.get('q') || '',
    maxPrice: 0,
    available: false,
    warranty: false,
    sort: 'relevant',
  };
  const search = catalog.querySelector('[data-catalog-search]');
  const sort = catalog.querySelector('[data-catalog-sort]');
  const count = catalog.querySelector('[data-catalog-count]');
  const empty = catalog.querySelector('[data-catalog-empty]');
  const summary = catalog.querySelector('[data-catalog-summary]');
  const summaryText = catalog.querySelector('[data-catalog-summary-text]');
  const filterDialog = catalog.querySelector('#jw-catalog-filter-dialog');
  const infoDialog = catalog.querySelector('#jw-catalog-info-dialog');
  const infoTitle = catalog.querySelector('#jw-catalog-info-title');
  const infoBody = catalog.querySelector('#jw-catalog-info-body');
  const toast = catalog.querySelector('#jw-catalog-toast');
  let toastTimer;

  function notify(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.classList.remove('hidden');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.add('hidden'), 3000);
  }

  function showInfo(title, message) {
    infoTitle.textContent = title;
    infoBody.replaceChildren();
    const paragraph = document.createElement('p');
    paragraph.textContent = message;
    infoBody.appendChild(paragraph);
    if (!infoDialog.open) infoDialog.showModal();
  }

  function syncControls() {
    catalog.querySelectorAll('[data-catalog-category]').forEach((button) => {
      const active = button.dataset.catalogCategory === state.category;
      button.setAttribute('aria-pressed', String(active));
      button.classList.toggle('ring-2', active);
      button.classList.toggle('ring-primary', active);
      button.classList.toggle('bg-primary-fixed/40', active);
    });
    catalog.querySelectorAll('[data-catalog-price]').forEach(el => { el.value = String(state.maxPrice); });
    catalog.querySelectorAll('[data-catalog-available]').forEach(el => { el.checked = state.available; });
    catalog.querySelectorAll('[data-catalog-warranty]').forEach(el => { el.checked = state.warranty; });
    search.value = state.search;
    sort.value = state.sort;
  }

  function updateUrl() {
    const url = new URL(window.location.href);
    if (state.category !== 'all') url.searchParams.set('kategori', state.category);
    else url.searchParams.delete('kategori');
    if (state.search.trim()) url.searchParams.set('q', state.search.trim());
    else url.searchParams.delete('q');
    window.history.replaceState(null, '', url);
  }

  function redraw() {
    const normalizedQuery = state.search.trim().toLocaleLowerCase('id-ID');
    let visible = 0;
    cards.forEach(card => {
      const found = (state.category === 'all' || card.dataset.category === state.category)
        && categories.has(card.dataset.category)
        && (!normalizedQuery || card.dataset.search.includes(normalizedQuery))
        && (!state.maxPrice || Number(card.dataset.price) <= state.maxPrice)
        && (!state.available || card.dataset.available === '1')
        && (!state.warranty || card.dataset.warranty === '1');
      card.classList.toggle('hidden', !found);
      if (found) visible++;
    });

    const sorting = {
      relevant: (a, b) => Number(a.dataset.originalOrder) - Number(b.dataset.originalOrder),
      nearest: (a, b) => Number(a.dataset.distance) - Number(b.dataset.distance),
      rating: (a, b) => Number(b.dataset.rating) - Number(a.dataset.rating),
      price: (a, b) => Number(a.dataset.price) - Number(b.dataset.price),
    };
    cards.sort((a, b) => sorting[state.sort](a, b) || Number(a.dataset.originalOrder) - Number(b.dataset.originalOrder));
    cards.forEach(card => cardsContainer.appendChild(card));

    count.textContent = String(visible);
    empty.classList.toggle('hidden', visible !== 0);
    const active = state.category !== 'all' || !!state.search.trim() || state.maxPrice > 0 || state.available || state.warranty;
    summary.classList.toggle('hidden', !active);
    const applied = [];
    if (state.category !== 'all') {
      const categoryButton = [...catalog.querySelectorAll('[data-catalog-category]')].find(button => button.dataset.catalogCategory === state.category);
      applied.push(categoryButton?.textContent.trim().replace(/\s+/g, ' ') || state.category);
    }
    if (state.search.trim()) applied.push(`“${state.search.trim()}”`);
    if (state.maxPrice) applied.push(`Maks. Rp ${state.maxPrice.toLocaleString('id-ID')}`);
    if (state.available) applied.push('Tersedia hari ini');
    if (state.warranty) applied.push('Garansi');
    summaryText.textContent = applied.join(' · ');
    syncControls();
    updateUrl();
  }

  function refreshCategories() {
    categories = new Set(['all', ...renderCategoryTiles()]);
    if (!categories.has(state.category)) state.category = 'all';
    redraw();
  }
  window.addEventListener(ADMIN_CATEGORIES_EVENT, refreshCategories);
  window.addEventListener('storage', event => { if (event.key === ADMIN_CATEGORIES_KEY || event.key === null) refreshCategories(); });

  function reset() {
    Object.assign(state, { category: 'all', search: '', maxPrice: 0, available: false, warranty: false, sort: 'relevant' });
    redraw();
  }

  catalog.addEventListener('input', (event) => {
    if (event.target.matches('[data-catalog-search]')) {
      state.search = event.target.value;
      redraw();
    }
  });
  catalog.addEventListener('change', (event) => {
    const target = event.target;
    if (target.matches('[data-catalog-price]')) state.maxPrice = Number(target.value);
    else if (target.matches('[data-catalog-available]')) state.available = target.checked;
    else if (target.matches('[data-catalog-warranty]')) state.warranty = target.checked;
    else if (target.matches('[data-catalog-sort]')) state.sort = target.value;
    else return;
    redraw();
  });
  catalog.querySelector('#jw-catalog-search-form').addEventListener('submit', event => {
    event.preventDefault();
    state.search = search.value.trim();
    if (state.search) {
      // Pencarian utama juga mencari Mitra/layanan PostgreSQL, bukan hanya kartu dummy.
      const url = new URL('/kategori-jasa', window.location.origin);
      url.searchParams.set('q', state.search);
      url.searchParams.set('db_q', state.search);
      window.location.assign(`${url.pathname}${url.search}#mitra-postgresql`);
      return;
    }
    redraw();
    catalog.querySelector('#mitra-postgresql').scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  catalog.addEventListener('click', (event) => {
    const trigger = event.target.closest('button');
    if (!trigger || !catalog.contains(trigger)) return;
    if (trigger.matches('[data-catalog-category]')) {
      state.category = trigger.dataset.catalogCategory;
      redraw();
    } else if (trigger.matches('[data-catalog-reset]')) {
      reset();
      notify('Filter telah diatur ulang.');
    } else if (trigger.matches('[data-catalog-open-filter]')) {
      if (!filterDialog.open) filterDialog.showModal();
    } else if (trigger.matches('[data-catalog-close-filter]')) {
      filterDialog.close();
    } else if (trigger.matches('[data-catalog-close-info]')) {
      infoDialog.close();
    } else if (trigger.hasAttribute('data-catalog-info')) {
      showInfo(trigger.dataset.catalogInfo, 'Fitur ini direncanakan untuk tahap berikutnya. Saat ini proyek masih berfokus pada prototipe frontend halaman Home dan Kategori Jasa.');
    } else if (trigger.hasAttribute('data-catalog-profile') || trigger.hasAttribute('data-catalog-order')) {
      const card = trigger.closest('.jw-catalog-card');
      const name = card?.querySelector('h3')?.textContent.trim() || 'Mitra';
      const isOrder = trigger.hasAttribute('data-catalog-order');
      showInfo(isOrder ? 'Pemesanan jasa' : `Profil ${name}`, isOrder
        ? `Simulasi: Anda memilih ${name}. Form pemesanan belum dibuat; tidak ada transaksi yang diproses.`
        : `${name} merupakan mitra contoh. Halaman detail mitra, portofolio, dan ulasan lengkap akan dikembangkan pada tahap selanjutnya.`);
    }
  });

  filterDialog.addEventListener('click', event => { if (event.target === filterDialog) filterDialog.close(); });
  infoDialog.addEventListener('click', event => { if (event.target === infoDialog) infoDialog.close(); });
  redraw();
}
