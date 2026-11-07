/** Dashboard Admin — DEMO. Tidak ada autentikasi atau mutasi data server. */
import { readOrders, createSampleOrders, ORDER_KEY } from './orders-store.js';
import { readReviews, REVIEW_KEY } from './reviews-store.js';
import { readAdminFeedback, ADMIN_FEEDBACK_KEY, ADMIN_FEEDBACK_EVENT } from './admin-feedback-store.js';
import { readCategories, ADMIN_CATEGORIES_KEY, ADMIN_CATEGORIES_EVENT } from './admin-category-store.js';
import { readAccounts, ACCOUNT_KEY } from './account-store.js';
import { activeServices, SERVICES_KEY } from './services-store.js';
import {
  readCitizenModeration, citizenStatus, readPartnerModeration, partnerVerification,
  ADMIN_CITIZEN_KEY, ADMIN_PARTNER_KEY, ADMIN_MANAGEMENT_EVENT,
  citizenAuditEntries, partnerAuditEntries, summaryCitizens,
} from './admin-management-store.js';
import {
  ADMIN_STATUSES, summarizeAdmin, recentAdminActivity, filterAdminOrders,
} from './admin-overview-data.js';
import { readAdminSettings, ADMIN_SETTINGS_KEY, ADMIN_SETTINGS_EVENT } from './admin-settings-store.js';

const root = document.getElementById('jw-admin-dashboard');
if (root) {
  const $$ = selector => [...root.querySelectorAll(selector)];
  const $ = selector => root.querySelector(selector);
  let seed = { providers: [], profiles: {}, categories: [] };
  try {
    seed = JSON.parse($('#jw-admin-seed').textContent);
  } catch (_) {
    // HTML tetap tampil; dashboard menunjukkan sumber data kosong.
  }
  const providers = Array.isArray(seed.providers) ? seed.providers : [];
  const profiles = seed.profiles && typeof seed.profiles === 'object' ? seed.profiles : {};
  const categories = Array.isArray(seed.categories) ? seed.categories : [];
  const currency = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
  const dateTime = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
  });
  const shortDate = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short' });
  const weekday = new Intl.DateTimeFormat('id-ID', { weekday: 'short' });
  let state = { query: '', status: 'all', chartMode: 'created' };
  const infoDialog = $('#jw-admin-info-dialog');

  function el(tag, classes = '', content = null) {
    const element = document.createElement(tag);
    if (classes) element.className = classes;
    if (content !== null) element.textContent = String(content);
    return element;
  }
  function set(selector, value) {
    const element = $(selector);
    if (element) element.textContent = String(value);
  }
  const formatTime = value => Number.isFinite(value) ? dateTime.format(value) : '—';
  const badgeClasses = {
    pending: 'bg-amber-50 text-amber-800',
    accepted: 'bg-blue-50 text-blue-800',
    enroute: 'bg-blue-50 text-blue-800',
    working: 'bg-blue-50 text-blue-800',
    completed: 'bg-emerald-50 text-emerald-800',
    cancelled: 'bg-slate-100 text-slate-600',
    rejected: 'bg-rose-50 text-rose-700',
  };

  function sources() {
    const accounts = readAccounts();
    const orders = readOrders();
    const reviews = readReviews();
    const feedback = readAdminFeedback();
    const effectiveCategories = readCategories(categories);
    const citizenModeration = readCitizenModeration();
    const partnerModeration = readPartnerModeration();
    const effectiveProviders = providers.map(provider => ({ ...provider, verified: partnerVerification(provider, partnerModeration) === 'verified' }));
    const serviceCounts = providers.map(provider => ({
      providerId: provider.id,
      activeCount: activeServices(
        provider.id,
        profiles[String(provider.id)]?.services || [],
        provider.category, effectiveCategories,
      ).length,
    }));
    return { accounts, orders, reviews, feedback, effectiveCategories, providers: effectiveProviders, serviceCounts, citizenModeration, partnerModeration };
  }

  function renderStats(data) {
    const summary = summarizeAdmin(data);
    const fields = {
      'citizens': summary.citizenAccountsLocal,
      'providers': summary.catalogProvidersDemo,
      'orders': summary.ordersLocal,
      'services': summary.activeServicesCatalogDemo,
      'waiting': summary.pendingOrdersLocal,
      'active': summary.activeOrdersLocal,
      'completed': summary.completedOrdersLocal,
      'reviews': summary.reviewsLocal,
      'unverified': providers.filter(p => partnerVerification(p, data.partnerModeration) === 'pending').length,
    };
    Object.entries(fields).forEach(([name, value]) => set(`[data-admin-kpi="${name}"]`, value.toLocaleString('id-ID')));
    const citizens = summaryCitizens(data.accounts, data.orders, data.citizenModeration);
    set('[data-admin-citizens-inactive]', citizens.inactive);
    set('[data-admin-partners-rejected]', providers.filter(p => partnerVerification(p, data.partnerModeration) === 'rejected').length);
    set('[data-admin-estimated-tariff]', readAdminSettings().preferences.showEstimatedTariff ? currency.format(summary.estimatedCompletedTariff) : 'Disembunyikan');
    set('[data-admin-chart-created]', summary.chart.reduce((sum, day) => sum + day.created, 0));
    set('[data-admin-chart-completed]', summary.chart.reduce((sum, day) => sum + day.completed, 0));
    return summary;
  }

  function renderChart(summary) {
    const container = $('[data-admin-chart]');
    container.replaceChildren();
    const mode = state.chartMode;
    const max = Math.max(1, ...summary.chart.map(day => day[mode]));
    summary.chart.forEach(day => {
      const column = el('div', 'flex h-full min-w-0 flex-col items-center justify-end gap-2');
      const track = el('div', 'flex h-[168px] w-full max-w-[65px] items-end overflow-hidden rounded-t-md bg-surface-container-low/40');
      const bar = el('div', day[mode] ? 'w-full rounded-t-md bg-primary transition-all' : 'w-full rounded-t-md bg-outline-variant/70 transition-all');
      bar.style.height = day[mode] ? `${Math.max(9, day[mode] / max * 100)}%` : '3px';
      const date = new Date(`${day.date}T12:00:00`);
      const metric = mode === 'created' ? 'pesanan dibuat' : 'pesanan selesai';
      column.setAttribute('title', `${shortDate.format(date)}: ${day[mode]} ${metric}`);
      column.setAttribute('aria-label', `${shortDate.format(date)}: ${day[mode]} ${metric}`);
      track.appendChild(bar);
      column.appendChild(track);
      column.appendChild(el('span', 'text-[10px] font-medium text-outline sm:text-xs', weekday.format(date)));
      container.appendChild(column);
    });
    $$('[data-admin-chart-mode]').forEach(button => {
      const selected = button.dataset.adminChartMode === mode;
      button.setAttribute('aria-pressed', String(selected));
      button.classList.toggle('bg-primary', selected);
      button.classList.toggle('text-white', selected);
      button.classList.toggle('text-on-surface-variant', !selected);
      button.classList.toggle('bg-white', !selected);
    });
  }

  function renderOrders(orders) {
    const list = $('[data-admin-orders-list]');
    list.replaceChildren();
    const visible = filterAdminOrders(orders, state);
    set('[data-admin-orders-matched]', visible.length);
    $('[data-admin-orders-empty]').classList.toggle('hidden', visible.length > 0);
    visible.slice(0, 8).forEach(order => {
      const row = el('tr', 'border-b border-outline-variant/25 last:border-0 hover:bg-surface-container-low/40');
      const reference = el('td', 'px-4 py-3.5 align-top');
      const link = el('a', 'text-xs font-bold text-primary hover:underline', order.reference);
      link.href = `/pesanan-saya/${encodeURIComponent(order.reference)}`;
      link.setAttribute('aria-label', `Lihat detail pesanan ${order.reference}`);
      reference.appendChild(link);
      reference.appendChild(el('p', 'mt-1 text-[11px] text-outline', formatTime(order.createdAt)));
      const service = el('td', 'px-4 py-3.5 align-top');
      service.appendChild(el('p', 'max-w-[230px] truncate text-sm font-semibold text-on-surface', order.service));
      service.appendChild(el('p', 'mt-1 text-xs text-outline', order.provider));
      const price = el('td', 'px-4 py-3.5 align-top text-sm font-semibold text-on-surface', currency.format(order.price));
      const status = el('td', 'px-4 py-3.5 align-top');
      status.appendChild(el('span', `inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-bold ${badgeClasses[order.status] || 'bg-slate-100 text-slate-600'}`, ADMIN_STATUSES[order.status] || 'Tidak diketahui'));
      row.append(reference, service, price, status);
      list.appendChild(row);
    });
    set('[data-admin-orders-limit]', Math.min(visible.length, 8));
  }

  function renderVerification(data) {
    const list = $('[data-admin-verification-list]');
    list.replaceChildren();
    const unverified = providers.filter(provider => partnerVerification(provider, data.partnerModeration) === 'pending');
    $('[data-admin-verification-empty]').classList.toggle('hidden', unverified.length > 0);
    unverified.slice(0, 5).forEach(provider => {
      const item = el('li', 'flex items-center gap-3 border-b border-outline-variant/30 py-3 last:border-0');
      const initials = el('span', 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-fixed text-xs font-extrabold text-primary', provider.initials || 'JW');
      const body = el('div', 'min-w-0 flex-1');
      body.appendChild(el('p', 'truncate text-sm font-bold text-on-surface', provider.name));
      body.appendChild(el('p', 'mt-0.5 text-xs text-outline', `${provider.area} · ${categories.find(cat => cat.slug === provider.category)?.name || 'Layanan'}`));
      const action = el('button', 'shrink-0 rounded-lg border border-outline-variant/60 px-2.5 py-2 text-xs font-bold text-primary hover:bg-surface-container-low', 'Lihat');
      action.type = 'button';
      action.addEventListener('click', () => window.location.assign('/admin/mitra')); 
      item.append(initials, body, action);
      list.appendChild(item);
    });
  }

  function renderActivity(data) {
    const list = $('[data-admin-activity-list]');
    list.replaceChildren();
    const activity = recentAdminActivity(data);
    const adminEvents = [
      ...citizenAuditEntries(data.citizenModeration),
      ...partnerAuditEntries(data.partnerModeration),
    ].map(event => ({
      type: 'admin', at: event.at,
      title: event.kind === 'citizen' ? 'Perubahan status Warga (demo)' : 'Keputusan verifikasi Mitra (demo)',
      detail: `${event.previous} → ${event.next} · alasan tercatat`,
    }));
    activity.push(...adminEvents);
    data.feedback.audit.forEach(entry => activity.push({type:'admin',at:entry.at,title:'Tindakan ulasan/pengaduan (demo)',detail:`${entry.action} · alasan tercatat`}));
    activity.sort((a, b) => b.at - a.at);

    $('[data-admin-activity-empty]').classList.toggle('hidden', activity.length > 0);
    const styles = {
      order: ['receipt_long', 'bg-blue-50 text-primary'],
      review: ['star', 'bg-amber-50 text-amber-700'],
      account: ['person_add', 'bg-emerald-50 text-emerald-700'],
      admin: ['admin_panel_settings', 'bg-slate-100 text-primary'],
    };
    activity.slice(0, 7).forEach(event => {
      const item = el('li', 'flex items-start gap-3 py-3 border-b border-outline-variant/30 last:border-0');
      const [iconName, style] = styles[event.type] || styles.order;
      const iconWrap = el('span', `flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${style}`);
      iconWrap.appendChild(el('span', 'material-symbols-outlined text-[18px]', iconName));
      const content = el('div', 'min-w-0 flex-1');
      content.appendChild(el('p', 'text-sm font-semibold text-on-surface', event.title));
      content.appendChild(el('p', 'mt-0.5 truncate text-xs text-on-surface-variant', event.detail));
      content.appendChild(el('p', 'mt-1 text-[11px] text-outline', formatTime(event.at)));
      item.append(iconWrap, content);
      list.appendChild(item);
    });
  }

  function showInfo(title, message) {
    $('#jw-admin-info-title').textContent = title;
    $('#jw-admin-info-body').textContent = message;
    if (!infoDialog.open) infoDialog.showModal();
  }

  function render() {
    try {
      const data = sources();
      const summary = renderStats(data);
      set('[data-admin-categories-active]', data.effectiveCategories.filter(c => c.active).length);
      set('[data-admin-feedback-open]', data.feedback.cases.filter(c => ['open','investigating'].includes(c.status)).length);
      renderChart(summary);
      renderOrders(data.orders);
      renderVerification(data);
      renderActivity(data);
      $('[data-admin-storage-warning]').classList.add('hidden');
    } catch (error) {
      $('[data-admin-storage-warning]').classList.remove('hidden');
      $('[data-admin-storage-warning]').textContent = `Penyimpanan browser tidak dapat dibaca: ${error.message}`;
    }
  }

  $$('[data-admin-chart-mode]').forEach(button => button.addEventListener('click', () => {
    state.chartMode = button.dataset.adminChartMode;
    render();
  }));
  $('[data-admin-search]').addEventListener('input', event => {
    state.query = event.target.value;
    renderOrders(readOrders());
  });
  $('[data-admin-status]').addEventListener('change', event => {
    state.status = event.target.value;
    renderOrders(readOrders());
  });
  $('[data-admin-reset]').addEventListener('click', () => {
    state.query = '';
    state.status = 'all';
    $('[data-admin-search]').value = '';
    $('[data-admin-status]').value = 'all';
    renderOrders(readOrders());
  });
  $('[data-admin-global-search]').addEventListener('submit', event => {
    event.preventDefault();
    const query = $('[data-admin-global-input]').value;
    state.query = query;
    $('[data-admin-search]').value = query;
    renderOrders(readOrders());
    $('#jw-admin-orders').scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
  $$('[data-admin-soon]').forEach(button => button.addEventListener('click', () => {
    showInfo(button.dataset.adminSoon || 'Modul Admin', 'Halaman pengelolaan lengkap akan dibuat pada tahap berikutnya. Dashboard ini hanya menyediakan ringkasan baca-saja dari data lokal dan contoh katalog.');
  }));
  $$('[data-admin-info-close]').forEach(button => button.addEventListener('click', () => infoDialog.close()));
  infoDialog.addEventListener('click', event => { if (event.target === infoDialog) infoDialog.close(); });
  $('[data-admin-seed]').addEventListener('click', () => {
    if (!window.confirm('Tambahkan tiga pesanan DEMO ke browser ini? Data akan terlihat juga pada Pesanan Saya dan Dashboard Mitra.')) return;
    try {
      createSampleOrders();
      render();
      showInfo('Data contoh ditambahkan', 'Tiga pesanan fiktif berhasil disimpan di localStorage browser ini. Buka halaman Pesanan Saya untuk melihatnya.');
    } catch (error) {
      showInfo('Gagal menambahkan contoh', error.message);
    }
  });
  $$('[data-admin-refresh]').forEach(button => button.addEventListener('click', render));
  window.addEventListener('storage', event => {
    if ([ORDER_KEY, REVIEW_KEY, ACCOUNT_KEY, SERVICES_KEY, ADMIN_CITIZEN_KEY, ADMIN_PARTNER_KEY, ADMIN_CATEGORIES_KEY, ADMIN_FEEDBACK_KEY, ADMIN_SETTINGS_KEY].includes(event.key) || event.key === null) render();
  });
  ['jasawarga:orders-updated', 'jasawarga:reviews-updated', 'jasawarga:account-updated', 'jasawarga:services-updated', ADMIN_MANAGEMENT_EVENT, ADMIN_CATEGORIES_EVENT, ADMIN_FEEDBACK_EVENT, ADMIN_SETTINGS_EVENT]
    .forEach(name => window.addEventListener(name, render));
  render();
}
