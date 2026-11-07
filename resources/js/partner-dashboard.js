import { readOrders, ORDER_KEY } from './orders-store.js';
import { readPartnerAccount, PARTNER_ACCOUNT_KEY, PARTNER_ACCOUNT_EVENT } from './partner-account-store.js';
import {
  PARTNER_SETTINGS_KEY,
  PARTNER_SETTINGS_EVENT,
  ORDER_LABELS,
  getPartnerAvailability,
  setPartnerAvailability,
  summarizePartnerOrders,
  seedPartnerDemoOrders,
} from './partner-store.js';

const root = document.querySelector('#jw-partner');

if (root) {
  const providerId = Number(root.dataset.partnerId);
  const provider = {
    id: providerId,
    name: root.dataset.partnerName,
    service: root.dataset.partnerService,
    price: Number(root.dataset.partnerPrice),
  };
  const $$ = selector => [...root.querySelectorAll(selector)];
  const $ = selector => root.querySelector(selector);
  const IDR = new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
  });
  const dateTime = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
  });
  const viewDate = raw => {
    if (typeof raw !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw || '—';
    return new Intl.DateTimeFormat('id-ID', {
      day: 'numeric', month: 'short', year: 'numeric',
    }).format(new Date(`${raw}T12:00:00`));
  };
  const badges = {
    pending: 'bg-amber-100 text-amber-800',
    accepted: 'bg-primary-fixed text-primary',
    enroute: 'bg-primary-fixed text-primary',
    working: 'bg-primary-fixed text-primary',
    completed: 'bg-emerald-50 text-emerald-800',
    cancelled: 'bg-surface-container-low text-outline',
  };
  const chart = $('[data-partner-chart]');
  const labels = $('[data-partner-chart-labels]');
  const list = $('[data-partner-orders-list]');
  const activities = $('[data-partner-activities]');
  const dialog = $('#jw-partner-order-dialog');
  const toast = $('[data-partner-toast]');
  let toastTimeout;

  function tell(message) {
    toast.textContent = message;
    toast.classList.remove('hidden');
    window.clearTimeout(toastTimeout);
    toastTimeout = window.setTimeout(() => toast.classList.add('hidden'), 3500);
  }

  function textTo(selector, value) {
    const element = $(selector);
    if (element) element.textContent = String(value);
  }

  function renderAvailability() {
    const setting = getPartnerAvailability(providerId);
    const toggle = $('[data-partner-availability]');
    toggle.setAttribute('aria-checked', String(setting.available));
    toggle.classList.toggle('bg-primary', setting.available);
    toggle.classList.toggle('bg-outline', !setting.available);
    $('[data-partner-toggle-knob]').classList.toggle('translate-x-5', setting.available);
    $('[data-partner-toggle-knob]').classList.toggle('translate-x-0', !setting.available);
    $('[data-partner-availability-dot]').classList.toggle('bg-emerald-500', setting.available);
    $('[data-partner-availability-dot]').classList.toggle('bg-outline', !setting.available);
    textTo('[data-partner-availability-label]', setting.available ? 'Menerima pesanan' : 'Sedang tidak tersedia');
  }

  function renderChart(summary) {
    chart.replaceChildren();
    labels.replaceChildren();
    const max = Math.max(...summary.days.map(day => day.amount), 1);
    summary.days.forEach(day => {
      const height = day.amount > 0 ? Math.max(8, Math.round(day.amount / max * 100)) : 3;
      const outer = document.createElement('div');
      outer.className = 'flex h-full items-end justify-center';
      const bar = document.createElement('div');
      bar.className = `w-full max-w-[60px] rounded-t-md ${day.amount ? 'bg-primary' : 'bg-surface-container-highest'}`;
      bar.style.height = `${height}%`;
      bar.title = `${viewDate(day.key)}: ${IDR.format(day.amount)} (${day.count} pesanan selesai)`;
      outer.appendChild(bar);
      chart.appendChild(outer);

      const label = document.createElement('span');
      label.textContent = new Intl.DateTimeFormat('id-ID', { weekday: 'short' }).format(new Date(day.at));
      label.title = viewDate(day.key);
      labels.appendChild(label);
    });
    chart.setAttribute('aria-label', summary.days.map(day => `${viewDate(day.key)}: ${IDR.format(day.amount)}`).join('; '));
  }

  function renderOrders(summary) {
    list.replaceChildren();
    const recent = summary.orders.slice(0, 5);
    $('[data-partner-orders-empty]').classList.toggle('hidden', recent.length > 0);
    recent.forEach(order => {
      const fragment = $('#jw-partner-order-template').content.cloneNode(true);
      const el = (selector) => fragment.querySelector(selector);
      el('[data-partner-order-service]').textContent = order.service || 'Layanan';
      el('[data-partner-order-ref]').textContent = order.reference;
      el('[data-partner-order-date]').textContent = `${viewDate(order.date)} · ${order.time || '—'} WIB`;
      el('[data-partner-order-price]').textContent = IDR.format(order.price);
      const badge = el('[data-partner-order-status]');
      badge.textContent = ORDER_LABELS[order.status] || 'Tidak diketahui';
      badge.className = `inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold ${badges[order.status] || 'bg-surface-container-low'}`;
      el('[data-partner-view-order]').dataset.partnerViewOrder = order.reference;
      list.appendChild(fragment);
    });
  }

  function renderActivity(summary) {
    activities.replaceChildren();
    $('[data-partner-activity-empty]').classList.toggle('hidden', summary.activities.length > 0);
    summary.activities.forEach(item => {
      const fragment = $('#jw-partner-activity-template').content.cloneNode(true);
      fragment.querySelector('[data-partner-event-label]').textContent = ORDER_LABELS[item.status];
      fragment.querySelector('[data-partner-event-service]').textContent = item.service;
      fragment.querySelector('[data-partner-event-date]').textContent = dateTime.format(new Date(item.at));
      activities.appendChild(fragment);
    });
  }

  function renderRevenuePreference() {
    const visible = readPartnerAccount(providerId).operations.showRevenue;
    $('[data-partner-revenue]')?.closest('article')?.classList.toggle('hidden', !visible);
    $('[data-partner-chart]')?.closest('section')?.classList.toggle('hidden', !visible);
  }

  function render() {
    const summary = summarizePartnerOrders(readOrders(), providerId);
    textTo('[data-partner-pending]', summary.pending);
    textTo('[data-partner-active]', summary.active);
    textTo('[data-partner-completed]', summary.completed);
    textTo('[data-partner-revenue]', IDR.format(summary.estimatedRevenue));
    textTo('[data-partner-week-revenue]', IDR.format(summary.weekRevenue));
    textTo('[data-partner-total]', summary.total);
    $$('[data-partner-pending-pill]').forEach(pill => {
      pill.textContent = String(summary.pending);
      pill.classList.toggle('hidden', summary.pending === 0);
    });
    renderChart(summary);
    renderOrders(summary);
    renderActivity(summary);
    renderAvailability();
    renderRevenuePreference();
  }

  function openOrder(reference) {
    const order = readOrders().find(item => item.providerId === providerId && item.reference === reference);
    if (!order) {
      tell('Pesanan tidak ditemukan pada data mitra ini.');
      return;
    }
    textTo('[data-partner-dialog-ref]', order.reference);
    textTo('[data-partner-dialog-service]', order.service);
    textTo('[data-partner-dialog-status]', ORDER_LABELS[order.status] || '—');
    textTo('[data-partner-dialog-date]', `${viewDate(order.date)}, ${order.time || '—'} WIB`);
    textTo('[data-partner-dialog-area]', order.area || '—');
    textTo('[data-partner-dialog-price]', IDR.format(order.price));
    dialog.showModal();
  }

  root.addEventListener('click', event => {
    const button = event.target.closest('button');
    if (!button || !root.contains(button)) return;
    if (button.matches('[data-partner-seed]')) {
      try {
        const references = seedPartnerDemoOrders(provider);
        render();
        tell(`${references.length} pesanan fiktif berhasil ditambahkan untuk ${provider.name}.`);
      } catch (error) {
        tell(`Gagal menambahkan pesanan: ${error.message}`);
      }
    } else if (button.matches('[data-partner-availability]')) {
      try {
        const previous = getPartnerAvailability(providerId);
        setPartnerAvailability(providerId, !previous.available);
        renderAvailability();
        tell('Ketersediaan demo diperbarui. Belum memengaruhi form pemesanan Warga.');
      } catch (error) {
        tell(`Gagal menyimpan status: ${error.message}`);
      }
    } else if (button.matches('[data-partner-view-order]')) {
      openOrder(button.dataset.partnerViewOrder);
    } else if (button.matches('[data-partner-close-dialog]')) {
      dialog.close();
    } else if (button.matches('[data-partner-upcoming]')) {
      tell(`${button.dataset.partnerUpcoming} akan dibuat pada tahap berikutnya.`);
    }
  });

  dialog.addEventListener('click', event => {
    if (event.target === dialog) dialog.close();
  });
  window.addEventListener('jasawarga:orders-updated', render);
  window.addEventListener(PARTNER_SETTINGS_EVENT, renderAvailability);
  window.addEventListener(PARTNER_ACCOUNT_EVENT, event => {
    if (event.detail?.providerId === providerId) renderRevenuePreference();
  });
  window.addEventListener('storage', event => {
    if (event.key === ORDER_KEY || event.key === null) render();
    else if (event.key === PARTNER_SETTINGS_KEY) renderAvailability();
    else if (event.key === PARTNER_ACCOUNT_KEY) renderRevenuePreference();
  });
  render();
}
