import {
  ORDER_STEPS, ORDER_LABELS, ORDER_KEY,
  readOrders, getOrder, createSampleOrders, changeOrderStatus,
  nextOrderStatus, clearOrders,
} from './orders-store.js';
import { getReview } from './reviews-store.js';

/** UI daftar & detail tracking pesanan. Semua aktivitas simulasi lokal browser. */
const money = new Intl.NumberFormat('id-ID', {
  style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
});
const activeStatuses = new Set(['accepted', 'enroute', 'working']);
const statusColors = {
  pending: 'bg-amber-50 text-amber-800',
  accepted: 'bg-primary-fixed text-primary',
  enroute: 'bg-primary-fixed text-primary',
  working: 'bg-primary-fixed text-primary',
  completed: 'bg-emerald-50 text-emerald-800',
  cancelled: 'bg-error-container text-on-error-container',
  rejected: 'bg-rose-100 text-rose-800',
};

function dateLabel(iso) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(iso || '')) return '—';
  const d = new Date(`${iso}T00:00:00Z`);
  if (Number.isNaN(d.getTime())) return '—';
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC',
  }).format(d);
}

function timestampLabel(stamp) {
  const d = new Date(stamp);
  if (Number.isNaN(d.getTime())) return '—';
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta',
  }).format(d) + ' WIB';
}

function formatSchedule(order) {
  return `${dateLabel(order.date)}, ${String(order.time || '').replace(':', '.')} WIB`;
}

function setStatusBadge(node, status) {
  node.textContent = ORDER_LABELS[status] || 'Status tidak diketahui';
  node.className = `rounded-full px-2.5 py-1 text-[11px] font-bold ${statusColors[status] || 'bg-surface-container-low'}`;
}

function repeatUrl(base, order) {
  const url = new URL(`${base}/${order.providerId}`, window.location.origin);
  url.searchParams.set('layanan', order.serviceId || String(order.serviceIndex || 0));
  return url.pathname + url.search;
}

function toastFactory(root, selector) {
  let timer;
  return (text) => {
    const el = root.querySelector(selector);
    if (!el) return;
    el.textContent = text;
    el.classList.remove('hidden');
    clearTimeout(timer);
    timer = setTimeout(() => el.classList.add('hidden'), 3000);
  };
}

const listRoot = document.querySelector('#jw-orders');
if (listRoot) {
  const list = listRoot.querySelector('[data-orders-list]');
  const empty = listRoot.querySelector('[data-orders-empty]');
  const search = listRoot.querySelector('[data-orders-search]');
  const filters = [...listRoot.querySelectorAll('[data-orders-filter]')];
  const cardTemplate = listRoot.querySelector('#jw-order-card-template');
  const dialog = listRoot.querySelector('#jw-orders-confirm-clear');
  const alert = listRoot.querySelector('[data-orders-alert]');
  const notify = toastFactory(listRoot, '[data-orders-toast]');
  let filter = 'all';

  function matchesStatus(order) {
    return filter === 'all' || order.status === filter ||
      (filter === 'active' && activeStatuses.has(order.status));
  }

  function render() {
    const orders = readOrders();
    const query = search.value.trim().toLocaleLowerCase('id-ID');
    const matches = orders.filter(order => matchesStatus(order) &&
      [order.reference, order.provider, order.service, order.area]
        .join(' ').toLocaleLowerCase('id-ID').includes(query));

    const counts = {
      total: orders.length,
      pending: orders.filter(order => order.status === 'pending').length,
      active: orders.filter(order => activeStatuses.has(order.status)).length,
      completed: orders.filter(order => order.status === 'completed').length,
    };
    for (const [key, val] of Object.entries(counts)) {
      listRoot.querySelector(`[data-orders-${key}]`).textContent = String(val);
    }
    listRoot.querySelector('[data-orders-found]').textContent = String(matches.length);

    filters.forEach(button => {
      const active = button.dataset.ordersFilter === filter;
      button.setAttribute('aria-pressed', String(active));
      button.classList.toggle('bg-primary', active);
      button.classList.toggle('text-white', active);
      button.classList.toggle('border-primary', active);
    });

    list.replaceChildren();
    const fragment = document.createDocumentFragment();
    matches.forEach(order => {
      const card = cardTemplate.content.firstElementChild.cloneNode(true);
      const set = (attr, value) => { card.querySelector(`[${attr}]`).textContent = String(value); };
      set('data-card-reference', order.reference);
      set('data-card-provider', order.provider);
      set('data-card-service', order.service);
      set('data-card-date', formatSchedule(order));
      set('data-card-price', money.format(order.price));
      setStatusBadge(card.querySelector('[data-card-status]'), order.status);
      card.querySelector('[data-card-detail]').href = `${listRoot.dataset.detailUrl}/${encodeURIComponent(order.reference)}`;
      card.querySelector('[data-card-repeat]').href = repeatUrl(listRoot.dataset.bookingBase, order);
      const review = card.querySelector('[data-card-review]');
      if (order.status === 'completed') {
        const existing = getReview(order.reference);
        review.classList.remove('hidden'); review.classList.add('inline-flex');
        review.href = existing
          ? `${listRoot.dataset.reviewsUrl}?ref=${encodeURIComponent(order.reference)}`
          : `${listRoot.dataset.reviewCreateBase}/${encodeURIComponent(order.reference)}`;
        review.querySelector('[data-card-review-label]').textContent = existing ? 'Lihat ulasan' : 'Beri ulasan';
      }
      fragment.appendChild(card);
    });
    list.appendChild(fragment);

    empty.classList.toggle('hidden', matches.length > 0);
    listRoot.querySelector('[data-orders-empty-title]').textContent = orders.length === 0
      ? 'Belum ada pesanan simulasi' : 'Pesanan tidak ditemukan';
    listRoot.querySelector('[data-orders-empty-description]').textContent = orders.length === 0
      ? 'Buat pesanan lewat Kategori Jasa atau tambahkan tiga pesanan contoh untuk mencoba tracking.'
      : 'Tidak ada pesanan sesuai filter atau pencarian. Coba status lain atau kosongkan kata kunci.';
    listRoot.querySelector('[data-orders-empty-icon]').textContent = orders.length === 0 ? 'inbox' : 'search_off';
  }

  search.addEventListener('input', render);
  filters.forEach(button => button.addEventListener('click', () => {
    filter = button.dataset.ordersFilter;
    render();
  }));

  listRoot.querySelector('[data-orders-seed]').addEventListener('click', () => {
    try {
      createSampleOrders();
      filter = 'all'; search.value = '';
      alert.classList.add('hidden');
      render();
      notify('Tiga pesanan ilustratif berhasil ditambahkan.');
    } catch (_) {
      alert.textContent = 'Tidak bisa menyimpan data contoh. Periksa apakah localStorage browser aktif atau ruang penyimpanan penuh.';
      alert.classList.remove('hidden');
    }
  });
  listRoot.querySelector('[data-orders-clear]').addEventListener('click', () => dialog.showModal());
  listRoot.querySelector('[data-orders-close-clear]').addEventListener('click', () => dialog.close());
  listRoot.querySelector('[data-orders-confirm-clear]').addEventListener('click', () => {
    try {
      clearOrders(); dialog.close(); render(); notify('Riwayat demo telah dihapus dari browser.');
    } catch (_) {
      dialog.close();
      alert.textContent = 'Browser tidak mengizinkan penghapusan penyimpanan lokal.';
      alert.classList.remove('hidden');
    }
  });
  dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
  window.addEventListener('storage', event => { if (event.key === ORDER_KEY) render(); });
  window.addEventListener('jasawarga:orders-updated', render);
  window.addEventListener('jasawarga:reviews-updated', render);
  render();
}

const detailRoot = document.querySelector('#jw-order-detail');
if (detailRoot) {
  const reference = detailRoot.dataset.reference;
  const content = detailRoot.querySelector('[data-order-content]');
  const notFound = detailRoot.querySelector('[data-order-not-found]');
  const cancelDialog = detailRoot.querySelector('#jw-order-cancel-dialog');
  const nextButton = detailRoot.querySelector('[data-order-advance]');
  const cancelButton = detailRoot.querySelector('[data-order-cancel]');
  const reviewButton = detailRoot.querySelector('[data-order-review]');
  const alert = detailRoot.querySelector('[data-order-alert]');
  const notify = toastFactory(detailRoot, '[data-order-toast]');

  function timeline(order) {
    const steps = ['cancelled', 'rejected'].includes(order.status)
      ? [...ORDER_STEPS.slice(0, 1), order.status]
      : ORDER_STEPS;
    const reachedIndex = ORDER_STEPS.indexOf(order.status);
    const container = detailRoot.querySelector('[data-order-timeline]');
    container.replaceChildren();

    steps.forEach((step, index) => {
      const reached = ['cancelled', 'rejected'].includes(order.status) || index <= reachedIndex;
      const current = order.status === step;
      const history = order.history.find(entry => entry.status === step);
      const li = document.createElement('li');
      li.className = 'flex items-start gap-3';
      const rail = document.createElement('div');
      rail.className = 'flex w-8 shrink-0 flex-col items-center';
      const dot = document.createElement('span');
      dot.className = `flex h-8 w-8 items-center justify-center rounded-full text-xs font-extrabold ${
        current ? (['cancelled', 'rejected'].includes(step) ? 'bg-error text-white' : 'bg-primary text-white')
          : reached ? 'bg-emerald-50 text-emerald-700' : 'bg-surface-container-low text-outline'
      }`;
      dot.textContent = reached ? '✓' : String(index + 1);
      rail.appendChild(dot);
      if (index !== steps.length - 1) {
        const line = document.createElement('span');
        line.className = `my-1 h-9 w-0.5 rounded-full ${reached && !current ? 'bg-emerald-500' : 'bg-outline-variant/50'}`;
        rail.appendChild(line);
      }
      const info = document.createElement('div');
      info.className = 'min-w-0 flex-1 pt-1';
      const name = document.createElement('p');
      name.className = `text-sm ${current ? 'font-extrabold text-primary' : reached ? 'font-semibold text-on-surface' : 'text-outline'}`;
      name.textContent = ORDER_LABELS[step];
      const time = document.createElement('p');
      time.className = 'mt-1 text-xs text-outline';
      time.textContent = history ? `${timestampLabel(history.at)} · Simulasi${history.reason ? ` · Alasan: ${history.reason}` : ''}` : 'Belum dicapai';
      info.append(name, time);
      li.append(rail, info);
      container.appendChild(li);
    });
  }

  function renderDetail() {
    const order = getOrder(reference);
    notFound.classList.toggle('hidden', Boolean(order));
    content.classList.toggle('hidden', !order);
    if (!order) return;
    const values = {
      reference: order.reference,
      created: timestampLabel(order.createdAt),
      provider: order.provider,
      service: order.service,
      schedule: formatSchedule(order),
      area: `${order.area}, Bandung`,
      address: order.address || 'Tidak diisi',
      description: order.description || 'Tidak diisi',
      price: money.format(order.price),
      total: money.format(order.price),
    };
    Object.entries(values).forEach(([key, value]) => {
      detailRoot.querySelectorAll(`[data-order-value="${key}"]`).forEach(el => { el.textContent = value; });
    });
    setStatusBadge(detailRoot.querySelector('[data-order-status]'), order.status);
    const stage = ORDER_STEPS.indexOf(order.status);
    detailRoot.querySelector('[data-order-progress]').textContent = order.status === 'cancelled'
      ? 'Pesanan dibatalkan' : order.status === 'rejected' ? 'Pesanan ditolak mitra' : `Tahap ${stage + 1} dari ${ORDER_STEPS.length}`;
    detailRoot.querySelector('[data-order-cancelled-note]').classList.toggle('hidden', !['cancelled', 'rejected'].includes(order.status));
    detailRoot.querySelector('[data-order-cancelled-note]').textContent = order.status === 'rejected'
      ? `Pesanan ditolak oleh Mitra. Alasan: ${order.rejectionReason || 'Tidak disebutkan'}`
      : 'Pesanan simulasi ini telah dibatalkan. Tahapan berikutnya tidak dapat dijalankan.';
    // Status pekerjaan diproses melalui panel Mitra, bukan oleh Warga.
    nextButton?.classList.add('hidden');
    cancelButton.classList.toggle('hidden', order.status !== 'pending');
    reviewButton.classList.toggle('hidden', order.status !== 'completed');
    reviewButton.classList.toggle('block', order.status === 'completed');
    if (order.status === 'completed') {
      const existing = getReview(order.reference);
      reviewButton.href = existing
        ? `${detailRoot.dataset.reviewsUrl}?ref=${encodeURIComponent(order.reference)}`
        : `${detailRoot.dataset.reviewCreateBase}/${encodeURIComponent(order.reference)}`;
      reviewButton.textContent = existing ? 'Lihat ulasan saya' : 'Beri rating & ulasan';
    }
    detailRoot.querySelector('[data-order-repeat]').href = repeatUrl(detailRoot.dataset.bookingBase, order);
    timeline(order);
  }

  function updateStatus(newStatus) {
    try {
      const updated = changeOrderStatus(reference, newStatus);
      if (!updated) { notify('Status tidak bisa diubah dari tahap saat ini.'); return; }
      alert.classList.add('hidden');
      renderDetail();
      notify(`Status simulasi: ${ORDER_LABELS[updated.status]}.`);
    } catch (_) {
      alert.textContent = 'Perubahan tidak tersimpan. Pastikan penyimpanan browser diizinkan.';
      alert.classList.remove('hidden');
    }
  }

  // Tidak ada tombol perubahan tahap dari sisi Warga.
  cancelButton.addEventListener('click', () => cancelDialog.showModal());
  detailRoot.querySelector('[data-order-keep]').addEventListener('click', () => cancelDialog.close());
  detailRoot.querySelector('[data-order-confirm-cancel]').addEventListener('click', () => {
    cancelDialog.close(); updateStatus('cancelled');
  });
  cancelDialog.addEventListener('click', e => { if (e.target === cancelDialog) cancelDialog.close(); });
  window.addEventListener('storage', e => {
    if (e.key === ORDER_KEY || e.key === 'jasawarga:prototype-reviews:v1') renderDetail();
  });
  window.addEventListener('jasawarga:orders-updated', renderDetail);
  renderDetail();
}
