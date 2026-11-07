import { readCategories, ADMIN_CATEGORIES_KEY, ADMIN_CATEGORIES_EVENT } from './admin-category-store.js';
import {
  SERVICES_EVENT, readServices, saveService, setServiceActive, deleteService,
  SERVICE_UNITS, PRICE_MIN, PRICE_MAX,
} from './services-store.js';

const root = document.querySelector('#jw-partner-services');
if (root) {
  const providerId = Number(root.dataset.partnerId);
  const providerCategory = root.dataset.partnerCategory;
  const seed = JSON.parse(root.querySelector('[data-services-seed]').textContent);
  const categorySeed = JSON.parse(root.querySelector('[data-services-categories]').textContent);
  let categories = readCategories(categorySeed);
  let slugs = categories.filter(category => category.active).map(category => category.slug);
  const list = root.querySelector('[data-ps-list]');
  const search = root.querySelector('[data-ps-search]');
  const filter = root.querySelector('[data-ps-filter]');
  const categoryFilter = root.querySelector('[data-ps-category-filter]');
  const empty = root.querySelector('[data-ps-empty]');
  const dialog = root.querySelector('#jw-ps-form-dialog');
  const form = root.querySelector('#jw-ps-form');
  const error = root.querySelector('[data-ps-error]');
  const dialogTitle = root.querySelector('[data-ps-dialog-title]');
  const confirm = root.querySelector('#jw-ps-delete-dialog');
  const confirmName = root.querySelector('[data-ps-delete-name]');
  const toast = root.querySelector('[data-ps-toast]');
  const idr = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
  let editId = null;
  let deleteId = null;
  let toastTimeout;
  let current = [];

  const el = (tag, className = '', value = '') => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (value) node.textContent = String(value);
    return node;
  };

  function notify(message) {
    toast.textContent = message;
    toast.classList.remove('hidden');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => toast.classList.add('hidden'), 3100);
  }

  function refreshCategoryOptions() {
    categories = readCategories(categorySeed);
    slugs = categories.filter(item => item.active).map(item => item.slug);
    const searchValue = categoryFilter.value;
    categoryFilter.replaceChildren(new Option('Semua kategori', 'all'));
    categories.forEach(item => categoryFilter.add(new Option(`${item.name}${item.active?'':' (nonaktif)'}`, item.slug)));
    categoryFilter.value = categories.some(item => item.slug === searchValue) ? searchValue : 'all';
    const select = form.elements.namedItem('category');
    const previous = select.value;
    select.replaceChildren();
    categories.filter(item => item.active).forEach(item => select.add(new Option(item.name, item.slug)));
    if (slugs.includes(previous)) select.value = previous;
  }
  function render() {
    refreshCategoryOptions();
    current = readServices(providerId, seed, providerCategory);
    const activeCount = current.filter(item => item.active && slugs.includes(item.category)).length;
    root.querySelector('[data-ps-total]').textContent = String(current.length);
    root.querySelector('[data-ps-active]').textContent = String(activeCount);
    root.querySelector('[data-ps-inactive]').textContent = String(current.length - activeCount);
    root.querySelector('[data-ps-min]').textContent = current.some(item => item.active && slugs.includes(item.category))
      ? idr.format(Math.min(...current.filter(item => item.active && slugs.includes(item.category)).map(item => item.price))) : '—';

    const query = search.value.trim().toLocaleLowerCase('id-ID');
    const state = filter.value;
    const cat = categoryFilter.value;
    const shown = current.filter(item =>
      (state === 'all' || (state === 'active') === (item.active && slugs.includes(item.category))) &&
      (cat === 'all' || item.category === cat) &&
      (!query || `${item.name} ${item.description} ${item.category}`.toLocaleLowerCase('id-ID').includes(query))
    );
    root.querySelector('[data-ps-count]').textContent = String(shown.length);
    list.replaceChildren();
    empty.classList.toggle('hidden', shown.length > 0);

    shown.forEach(item => {
      const categoryName = categories.find(cat => cat.slug === item.category)?.name || item.category;
      const card = el('article', 'rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5');
      const heading = el('div', 'flex flex-wrap items-start justify-between gap-4');
      const main = el('div', 'min-w-0 flex-1');
      const badging = el('div', 'mb-2 flex flex-wrap items-center gap-2');
      badging.append(el('span', 'rounded-lg bg-primary-fixed px-2 py-1 text-[11px] font-bold text-primary', categoryName));
      badging.append(el('span', item.active && slugs.includes(item.category) ? 'rounded-lg bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-700' : 'rounded-lg bg-surface-container-low px-2 py-1 text-[11px] font-bold text-outline', item.active && slugs.includes(item.category) ? 'Aktif' : item.active ? 'Kategori nonaktif' : 'Nonaktif'));
      main.append(badging, el('h3', 'text-base font-extrabold text-on-surface sm:text-lg', item.name), el('p', 'mt-1 max-w-2xl text-sm leading-relaxed text-on-surface-variant', item.description));
      const price = el('div', 'shrink-0 text-left sm:text-right');
      price.append(el('p', 'text-xs text-outline', 'Tarif mulai dari'), el('p', 'text-lg font-extrabold text-primary', idr.format(item.price)), el('p', 'text-[11px] text-outline', item.unit));
      heading.append(main, price);
      const bottom = el('div', 'mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-outline-variant/30 pt-4');
      const metadata = el('div', 'flex flex-wrap gap-3 text-xs text-outline');
      metadata.append(el('span', '', `Durasi: ${item.duration}`), el('span', '', item.feeNote || 'Biaya tambahan sesuai kondisi'));
      const buttons = el('div', 'flex flex-wrap items-center gap-2');
      for (const [label, action, style] of [
        ['Edit', 'edit', 'border border-outline-variant/60 bg-white text-on-surface hover:bg-surface-container-low'],
        [item.active ? 'Nonaktifkan' : 'Aktifkan', 'toggle', item.active ? 'border border-tertiary/30 bg-white text-tertiary hover:bg-tertiary-fixed/40' : 'border border-primary/30 bg-primary-fixed text-primary'],
        ['Hapus', 'delete', 'border border-error/30 bg-white text-error hover:bg-error-container'],
      ]) {
        const button = el('button', `rounded-lg px-3 py-2 text-xs font-bold transition ${style}`, label);
        button.type = 'button';
        button.dataset.psAction = action;
        button.dataset.psId = item.id;
        buttons.append(button);
      }
      bottom.append(metadata, buttons);
      card.append(heading, bottom);
      list.append(card);
    });
  }

  function openForm(id = null) {
    editId = id;
    form.reset();
    error.classList.add('hidden');
    error.textContent = '';
    form.elements.namedItem('category').value = slugs.includes(providerCategory) ? providerCategory : (slugs[0] || '');
    if (!slugs.length) return notify('Tidak ada kategori aktif. Hubungi Admin Demo untuk mengaktifkan kategori.');
    form.elements.namedItem('unit').value = SERVICE_UNITS[0];
    dialogTitle.textContent = id ? 'Edit layanan' : 'Tambah layanan';
    if (id) {
      const item = current.find(item => item.id === id);
      if (!item) return notify('Layanan tidak ditemukan.');
      for (const field of ['name', 'description', 'price', 'category', 'duration', 'unit', 'feeNote']) {
        form.elements.namedItem(field).value = item[field] ?? '';
      }
    }
    dialog.showModal();
    form.elements.namedItem('name').focus();
  }

  root.querySelectorAll('[data-ps-create]').forEach(button => button.addEventListener('click', () => openForm()));
  for (const input of [search, filter, categoryFilter]) input.addEventListener(input === search ? 'input' : 'change', render);

  list.addEventListener('click', event => {
    const button = event.target.closest('[data-ps-action]');
    if (!button) return;
    const item = current.find(row => row.id === button.dataset.psId);
    if (!item) return render();
    if (button.dataset.psAction === 'edit') return openForm(item.id);
    if (button.dataset.psAction === 'delete') {
      deleteId = item.id;
      confirmName.textContent = item.name;
      confirm.showModal();
      return;
    }
    try {
      setServiceActive(providerId, seed, providerCategory, item.id, !item.active);
      notify(item.active ? 'Layanan dinonaktifkan. Tidak dapat dipesan baru.' : (slugs.includes(item.category) ? 'Layanan sudah aktif kembali.' : 'Status layanan aktif, tetapi kategori Admin masih nonaktif.'));
      render();
    } catch (error) { notify(error.message); }
  });

  form.addEventListener('submit', event => {
    event.preventDefault();
    error.classList.add('hidden');
    if (!form.reportValidity()) return;
    const fields = Object.fromEntries(new FormData(form));
    try {
      const price = Number(fields.price);
      if (!Number.isSafeInteger(price) || price < PRICE_MIN || price > PRICE_MAX) {
        throw new Error('Tarif harus antara Rp 10.000 dan Rp 20.000.000.');
      }
      saveService(providerId, seed, providerCategory, fields, slugs, editId);
      dialog.close();
      render();
      notify(editId ? 'Perubahan layanan tersimpan di browser ini.' : 'Layanan baru ditambahkan.');
    } catch (cause) {
      error.textContent = cause.message || 'Tidak dapat menyimpan layanan.';
      error.classList.remove('hidden');
    }
  });

  root.querySelector('[data-ps-confirm-delete]').addEventListener('click', () => {
    if (!deleteId) return;
    try {
      deleteService(providerId, seed, providerCategory, deleteId);
      confirm.close();
      deleteId = null;
      render();
      notify('Layanan dihapus. Pesanan lama tetap tersimpan.');
    } catch (error) { notify(error.message); }
  });

  root.querySelectorAll('[data-ps-close-form]').forEach(button => button.addEventListener('click', () => dialog.close()));
  root.querySelectorAll('[data-ps-close-delete]').forEach(button => button.addEventListener('click', () => confirm.close()));
  window.addEventListener('storage', event => { if ([SERVICES_KEY, ADMIN_CATEGORIES_KEY].includes(event.key)) render(); });
  window.addEventListener(SERVICES_EVENT, render);
  window.addEventListener(ADMIN_CATEGORIES_EVENT, render);
  render();
}
