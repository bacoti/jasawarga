import {
  ORDER_KEY, ORDER_LABELS, readOrders, nextOrderStatus,
  changeOrderStatus, rejectPartnerOrder,
} from './orders-store.js';

/** Operasional Mitra (prototipe satu browser, tanpa server). */
const root = document.querySelector('#jw-partner-orders');

if (root) {
  const providerId = Number(root.dataset.partnerId);
  const find = selector => root.querySelector(selector);
  const findAll = selector => [...root.querySelectorAll(selector)];
  const activeStatuses = new Set(['accepted', 'enroute', 'working']);
  const closedStatuses = new Set(['completed', 'rejected', 'cancelled']);
  const colors = {
    pending: 'bg-amber-100 text-amber-800',
    accepted: 'bg-primary-fixed text-primary',
    enroute: 'bg-primary-fixed text-primary',
    working: 'bg-primary-fixed text-primary',
    completed: 'bg-emerald-50 text-emerald-800',
    rejected: 'bg-rose-100 text-rose-800',
    cancelled: 'bg-surface-container-low text-on-surface-variant',
  };
  const currency = new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
  });
  const formatDay = raw => {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(raw || '')) return '—';
    return new Intl.DateTimeFormat('id-ID', {
      timeZone: 'UTC', day: 'numeric', month: 'long', year: 'numeric',
    }).format(new Date(`${raw}T12:00:00Z`));
  };
  const formatStamp = value => Number.isFinite(value)
    ? new Intl.DateTimeFormat('id-ID', {
      timeZone: 'Asia/Makassar',
      day: 'numeric', month: 'short', year: 'numeric',
      hour: '2-digit', minute: '2-digit',
    }).format(new Date(value)) + ' WITA' : '—';
  const formatSchedule = order => `${formatDay(order.date)}, ${order.time || '—'} WITA`;
  const statusText = status => ORDER_LABELS[status] || 'Status tidak dikenal';
  const ownOrders = () => readOrders().filter(o => Number(o.providerId) === providerId);
  const nextAction = order => {
    const next = nextOrderStatus(order.status);
    return next ? {
      status: next,
      label: {
        accepted: 'Terima pesanan',
        enroute: 'Mulai perjalanan',
        working: 'Mulai pengerjaan',
        completed: 'Selesaikan pekerjaan',
      }[next],
    } : null;
  };
  const view = {
    list: find('[data-mpo-list]'),
    history: find('[data-mpo-history]'),
    search: find('[data-mpo-search]'),
    date: find('[data-mpo-date]'),
    service: find('[data-mpo-service]'),
    empty: find('[data-mpo-empty]'),
    detail: find('#jw-mpo-detail'),
    confirm: find('#jw-mpo-confirm'),
    reason: find('[data-mpo-reason]'),
    reasonError: find('[data-mpo-reason-error]'),
    toast: find('[data-mpo-toast]'),
  };

  const state = { status: 'all', search: '', date: '', service: 'all' };
  let detailRef = null;
  let actionRef = null;
  let actionStatus = null;
  let toastTimer;

  function message(text) {
    view.toast.textContent = text;
    view.toast.classList.remove('hidden');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => view.toast.classList.add('hidden'), 3600);
  }

  function statusBadge(el, status) {
    el.textContent = statusText(status);
    el.className = `rounded-full px-2.5 py-1 text-[11px] font-bold ${colors[status] || 'bg-surface-container-low'}`;
  }

  function refreshServices(orders) {
    const options = new Set(orders.map(o => o.service).filter(Boolean));
    const current = state.service;
    view.service.replaceChildren();
    const all = document.createElement('option');
    all.value = 'all'; all.textContent = 'Semua layanan';
    view.service.appendChild(all);
    [...options].sort((a, b) => a.localeCompare(b, 'id')).forEach(service => {
      const option = document.createElement('option');
      option.value = service;
      option.textContent = service;
      view.service.appendChild(option);
    });
    if (current !== 'all' && !options.has(current)) state.service = 'all';
    view.service.value = state.service;
  }

  function render() {
    const orders = ownOrders();
    refreshServices(orders);
    const query = state.search.toLocaleLowerCase('id-ID');
    const visible = orders.filter(o => {
      const status = state.status === 'all' || o.status === state.status ||
        (state.status === 'active' && activeStatuses.has(o.status));
      const text = [o.reference, o.customerName, o.service, o.area, o.description]
        .join(' ').toLocaleLowerCase('id-ID');
      return status && text.includes(query) &&
        (!state.date || o.date === state.date) &&
        (state.service === 'all' || o.service === state.service);
    });
    const value = (key, count) => { find(`[data-mpo-${key}]`).textContent = String(count); };
    value('pending', orders.filter(o => o.status === 'pending').length);
    value('active', orders.filter(o => activeStatuses.has(o.status)).length);
    value('completed', orders.filter(o => o.status === 'completed').length);
    value('closed', orders.filter(o => ['cancelled', 'rejected'].includes(o.status)).length);
    value('found', visible.length);
    value('total', orders.length);

    findAll('[data-partner-pending-pill]').forEach(pill => {
      const count = orders.filter(o => o.status === 'pending').length;
      pill.textContent = String(count);
      pill.classList.toggle('hidden', !count);
    });
    findAll('[data-mpo-filter]').forEach(button => {
      const selected = button.dataset.mpoFilter === state.status;
      button.setAttribute('aria-pressed', String(selected));
      button.classList.toggle('bg-primary', selected);
      button.classList.toggle('border-primary', selected);
      button.classList.toggle('text-white', selected);
    });
    view.list.replaceChildren();
    visible.forEach(order => {
      const card = find('#jw-mpo-card-template').content.firstElementChild.cloneNode(true);
      const set = (selector, text) => { card.querySelector(selector).textContent = text; };
      set('[data-mpo-card-ref]', order.reference);
      set('[data-mpo-card-service]', order.service || 'Layanan tidak diketahui');
      set('[data-mpo-card-customer]', `${order.customerName || 'Warga demo'} · ${order.area || 'Makassar'}`);
      set('[data-mpo-card-schedule]', formatSchedule(order));
      set('[data-mpo-card-price]', currency.format(order.price));
      statusBadge(card.querySelector('[data-mpo-card-status]'), order.status);
      card.querySelector('[data-mpo-view]').dataset.mpoView = order.reference;
      const reject = card.querySelector('[data-mpo-reject]');
      const next = card.querySelector('[data-mpo-next]');
      const action = nextAction(order);
      if (order.status === 'pending') {
        reject.classList.remove('hidden');
        reject.dataset.mpoReject = order.reference;
      }
      if (action) {
        next.classList.remove('hidden');
        next.textContent = action.label;
        next.dataset.mpoNext = order.reference;
      }
      view.list.appendChild(card);
    });

    view.empty.classList.toggle('hidden', visible.length > 0);
    find('[data-mpo-empty-title]').textContent = orders.length ? 'Tidak ada hasil yang cocok' : 'Belum ada pesanan masuk';
    find('[data-mpo-empty-description]').textContent = orders.length
      ? 'Coba ubah filter status, tanggal, layanan, atau kata kunci.'
      : 'Pesanan baru untuk mitra ini akan muncul di sini. Tambahkan pesanan demo melalui Dashboard Mitra.';

    const history = orders.filter(o => closedStatuses.has(o.status)).slice(0, 6);
    view.history.replaceChildren();
    find('[data-mpo-history-empty]').classList.toggle('hidden', history.length > 0);
    history.forEach(order => {
      const row = document.createElement('button');
      row.type = 'button';
      row.dataset.mpoView = order.reference;
      row.className = 'w-full rounded-xl border border-outline-variant/40 p-3 text-left transition hover:border-primary/50';
      const service = document.createElement('p');
      service.className = 'truncate text-xs font-bold';
      service.textContent = order.service;
      const meta = document.createElement('p');
      meta.className = 'mt-1 text-[11px] text-outline';
      meta.textContent = `${statusText(order.status)} · ${formatDay(order.date)}`;
      row.append(service, meta);
      view.history.appendChild(row);
    });
    if (view.detail.open && detailRef) renderDetail(detailRef);
  }

  function renderDetail(reference) {
    const order = ownOrders().find(item => item.reference === reference);
    if (!order) { if (view.detail.open) view.detail.close(); return; }
    const fields = {
      ref: order.reference,
      customer: order.customerName || 'Warga demo (data lama)',
      phone: order.customerPhone || 'Tidak tersedia di riwayat lama',
      service: order.service,
      schedule: formatSchedule(order),
      area: order.area || '—',
      address: order.address || 'Tidak diisi',
      description: order.description || 'Tidak diisi',
      price: currency.format(order.price),
    };
    Object.entries(fields).forEach(([key, val]) => {
      find(`[data-mpo-detail-${key}]`).textContent = val;
    });
    statusBadge(find('[data-mpo-detail-status]'), order.status);
    const timeline = find('[data-mpo-detail-history]');
    timeline.replaceChildren();
    (order.history || []).forEach(entry => {
      const li = document.createElement('li');
      li.className = 'flex items-start gap-3';
      const dot = document.createElement('span');
      dot.className = 'mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-primary';
      const content = document.createElement('div');
      const title = document.createElement('p');
      title.className = 'text-xs font-semibold';
      title.textContent = statusText(entry.status);
      const time = document.createElement('p');
      time.className = 'mt-0.5 text-[11px] text-outline';
      time.textContent = formatStamp(entry.at);
      content.append(title, time);
      if (entry.reason) {
        const reason = document.createElement('p');
        reason.className = 'mt-1 text-xs text-error';
        reason.textContent = `Alasan: ${entry.reason}`;
        content.appendChild(reason);
      }
      li.append(dot, content);
      timeline.appendChild(li);
    });
    const reject = find('[data-mpo-detail-reject]');
    const next = find('[data-mpo-detail-next]');
    const action = nextAction(order);
    reject.classList.toggle('hidden', order.status !== 'pending');
    next.classList.toggle('hidden', !action);
    if (action) next.textContent = action.label;
  }

  function openDetail(reference) {
    const order = ownOrders().find(o => o.reference === reference);
    if (!order) { message('Pesanan tidak ditemukan atau bukan milik mitra ini.'); return; }
    detailRef = reference;
    renderDetail(reference);
    if (!view.detail.open) view.detail.showModal();
  }

  function openAction(reference, isReject = false) {
    const order = ownOrders().find(o => o.reference === reference);
    if (!order) { message('Pesanan tidak ditemukan pada mitra ini.'); return; }
    const next = nextAction(order);
    if ((isReject && order.status !== 'pending') || (!isReject && !next)) {
      message('Status pesanan telah berubah. Silakan periksa kembali.');
      render(); return;
    }
    actionRef = reference;
    actionStatus = isReject ? 'rejected' : next.status;
    find('#jw-mpo-confirm-title').textContent = isReject ? 'Tolak pesanan?' : `${next.label}?`;
    find('[data-mpo-confirm-summary]').textContent = isReject
      ? `Pesanan ${reference} akan ditolak dan tidak bisa diproses lagi. Tuliskan alasan yang jelas untuk Warga.`
      : `Yakin ingin mengubah status ${reference} menjadi “${statusText(next.status)}”? Tindakan ini akan terlihat pada Tracking Warga di browser yang sama.`;
    find('[data-mpo-reason-wrap]').classList.toggle('hidden', !isReject);
    view.reason.value = '';
    find('[data-mpo-reason-error]').classList.add('hidden');
    find('[data-mpo-confirm-submit]').textContent = isReject ? 'Ya, tolak pesanan' : 'Ya, lanjutkan';
    find('[data-mpo-confirm-submit]').className = isReject
      ? 'rounded-xl bg-error px-4 py-2.5 text-sm font-bold text-white'
      : 'rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white';
    if (!view.confirm.open) view.confirm.showModal();
    if (isReject) view.reason.focus();
  }

  root.addEventListener('click', event => {
    const button = event.target.closest('button');
    if (!button || !root.contains(button)) return;
    if (button.matches('[data-mpo-filter]')) {
      state.status = button.dataset.mpoFilter;
      render();
    } else if (button.matches('[data-mpo-reset]')) {
      Object.assign(state, { status: 'all', search: '', date: '', service: 'all' });
      view.search.value = ''; view.date.value = ''; view.service.value = 'all';
      render();
    } else if (button.matches('[data-mpo-view]')) {
      openDetail(button.dataset.mpoView);
    } else if (button.matches('[data-mpo-next]')) {
      openAction(button.dataset.mpoNext);
    } else if (button.matches('[data-mpo-reject]')) {
      openAction(button.dataset.mpoReject, true);
    } else if (button.matches('[data-mpo-detail-next]')) {
      if (detailRef) openAction(detailRef);
    } else if (button.matches('[data-mpo-detail-reject]')) {
      if (detailRef) openAction(detailRef, true);
    } else if (button.matches('[data-mpo-close-detail]')) {
      view.detail.close();
    } else if (button.matches('[data-mpo-close-confirm]')) {
      view.confirm.close();
    } else if (button.matches('[data-partner-upcoming]')) {
      message(`${button.dataset.partnerUpcoming} akan dikembangkan di tahap berikutnya.`);
    }
  });

  view.search.addEventListener('input', e => { state.search = e.target.value.trim(); render(); });
  view.date.addEventListener('change', e => { state.date = e.target.value; render(); });
  view.service.addEventListener('change', e => { state.service = e.target.value; render(); });

  find('#jw-mpo-confirm-form').addEventListener('submit', event => {
    event.preventDefault();
    if (!actionRef || !actionStatus) return;
    // Pembacaan ulang penting karena status bisa berubah di tab lain.
    const current = ownOrders().find(o => o.reference === actionRef);
    if (!current || (actionStatus === 'rejected'
      ? current.status !== 'pending' : nextOrderStatus(current.status) !== actionStatus)) {
      view.confirm.close(); message('Status berubah di tab lain, tindakan tidak dijalankan.'); render(); return;
    }
    try {
      const updated = actionStatus === 'rejected'
        ? rejectPartnerOrder(actionRef, view.reason.value)
        : changeOrderStatus(actionRef, actionStatus);
      if (!updated) { message('Status pesanan tidak dapat diubah.'); render(); return; }
      view.confirm.close();
      message(`Status berhasil disimpan: ${statusText(updated.status)}.`);
      render();
    } catch (error) {
      if (actionStatus === 'rejected' && /10/.test(error.message)) {
        find('[data-mpo-reason-error]').textContent = error.message;
        find('[data-mpo-reason-error]').classList.remove('hidden');
        view.reason.focus();
      } else {
        message('Tidak bisa menyimpan perubahan. Pastikan localStorage browser aktif dan tersedia.');
      }
    }
  });

  for (const dialog of [view.detail, view.confirm]) {
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
  }
  window.addEventListener('jasawarga:orders-updated', render);
  window.addEventListener('storage', event => {
    if (event.key === ORDER_KEY || event.key === null) render();
  });
  render();
}
