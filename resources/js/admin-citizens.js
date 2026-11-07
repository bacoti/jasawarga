import { readAccounts, ACCOUNT_KEY } from './account-store.js';
import { readOrders, ORDER_KEY } from './orders-store.js';
import {
 ADMIN_CITIZEN_KEY, ADMIN_MANAGEMENT_EVENT, readCitizenModeration,
 citizenStatus, setCitizenStatus, summaryCitizens, linkedCitizenOrders, citizenAuditEntries,
} from './admin-management-store.js';
const root = document.querySelector('#jw-admin-citizens');
if (root) {
  const $ = q => root.querySelector(q);
  const currency = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });
  const dateFmt = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' });
  const dateTime = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
  const fmt = x => Number.isFinite(x) ? dateFmt.format(x) : '—';
  const item = (tag, className = '', value) => {
    const el = document.createElement(tag); el.className = className;
    if (value !== undefined) el.textContent = String(value);
    return el;
  };
  let filter = { q: '', status: 'all', range: 'all' };
  let selectedId = null;
  let pending = null;
  const dialog = $('#jw-admin-citizen-detail');
  const actionDialog = $('#jw-admin-citizen-action');
  function values() {
    return { accounts: readAccounts(), orders: readOrders(), moderation: readCitizenModeration() };
  }
  function notify(msg, isError = false) {
    const banner = $('[data-citizen-notice]');
    banner.textContent = msg; banner.classList.remove('hidden');
    banner.classList.toggle('bg-rose-50', isError);
    banner.classList.toggle('text-rose-800', isError);
    banner.classList.toggle('bg-blue-50', !isError);
    banner.classList.toggle('text-primary', !isError);
  }
  function renderList() {
    const { accounts, orders, moderation } = values();
    const stats = summaryCitizens(accounts, orders, moderation);
    Object.entries(stats).forEach(([name, val]) => {
      const el = $(`[data-citizen-kpi="${name}"]`); if (el) el.textContent = val.toLocaleString('id-ID');
    });
    let list = accounts.filter(a => {
      const s = citizenStatus(a.id, moderation);
      if (filter.status !== 'all' && s !== filter.status) return false;
      if (filter.range === '30' && !(Number(a.createdAt) > Date.now() - 30 * 86400000)) return false;
      const q = filter.q.toLocaleLowerCase('id-ID').trim();
      return !q || [a.name, a.email, a.id].some(x => String(x || '').toLocaleLowerCase('id-ID').includes(q));
    }).sort((a, b) => Number(b.createdAt) - Number(a.createdAt));
    const listEl = $('[data-citizen-table]');listEl.replaceChildren();
    $('[data-citizen-count]').textContent = list.length;
    $('[data-citizen-empty]').classList.toggle('hidden', list.length > 0);
    list.forEach(a => {
      const ordersFor = linkedCitizenOrders(a.id, orders);
      const row = item('tr', 'border-b border-outline-variant/35 last:border-0 hover:bg-surface-container-low/60');
      const identity = item('td', 'px-4 py-4 align-top');
      const title = item('p', 'font-bold text-on-surface', a.name);
      identity.append(title, item('p', 'mt-1 text-xs text-outline', a.email));
      const state = citizenStatus(a.id, moderation);
      const badge = item('span', `inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ${state === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-800'}`, state === 'active' ? 'Aktif (demo)' : 'Nonaktif (demo)');
      const status = item('td', 'px-4 py-4 align-top');status.append(badge);
      const history = item('td', 'px-4 py-4 align-top text-xs text-on-surface-variant', `${ordersFor.length} tertaut`);
      const registered = item('td', 'px-4 py-4 align-top text-xs text-on-surface-variant', fmt(a.createdAt));
      const action = item('td', 'px-4 py-4 align-top');
      const button = item('button', 'rounded-lg border border-outline-variant/60 px-3 py-2 text-xs font-bold text-primary hover:bg-primary-fixed/30', 'Lihat detail');
      button.type = 'button';button.addEventListener('click', () => showDetail(a.id));action.append(button);
      row.append(identity, status, history, registered, action);listEl.append(row);
    });
    const log = $('[data-citizen-logs]');log.replaceChildren();
    citizenAuditEntries(moderation).slice(0, 8).forEach(a => {
      const n = accounts.find(x => x.id === a.targetId)?.name || 'Akun dihapus';
      const entry = item('li', 'border-b border-outline-variant/30 py-3 last:border-0');
      entry.append(item('p', 'text-xs font-bold text-on-surface', `${n} · ${a.next === 'active' ? 'Diaktifkan' : 'Dinonaktifkan'}`));
      entry.append(item('p', 'mt-1 text-xs text-on-surface-variant', a.reason));
      entry.append(item('p', 'mt-1 text-[11px] text-outline', dateTime.format(a.at)));log.append(entry);
    });
    $('[data-citizen-logs-empty]').classList.toggle('hidden', citizenAuditEntries(moderation).length > 0);
  }
  function showDetail(id) {
    const { accounts, orders, moderation } = values();
    const a = accounts.find(x => x.id === id);if (!a) {notify('Akun sudah tidak tersedia.',true);return;}
    selectedId = id;
    $('[data-citizen-detail-title]').textContent = a.name;
    $('[data-citizen-detail-email]').textContent = a.email;
    $('[data-citizen-detail-status]').textContent = citizenStatus(id, moderation) === 'active' ? 'Aktif (demo)' : 'Nonaktif (demo)';
    $('[data-citizen-detail-date]').textContent = fmt(a.createdAt);
    $('[data-citizen-detail-phone]').textContent = a.phone ? `${a.phone.slice(0, 4)}••••${a.phone.slice(-3)}` : 'Tidak diisi';
    const address = $('[data-citizen-addresses]');address.replaceChildren();
    (Array.isArray(a.addresses) ? a.addresses : []).forEach(x => {
      address.append(item('li','rounded-lg bg-surface-container-low p-3 text-xs text-on-surface-variant',`${x.label || 'Alamat'} · ${x.area || 'Area tidak tersedia'} (detail alamat disembunyikan)`));
    });
    $('[data-citizen-addresses-empty]').classList.toggle('hidden', !!a.addresses?.length);
    const bookings = $('[data-citizen-orders]');bookings.replaceChildren();
    linkedCitizenOrders(id, orders).slice(0, 8).forEach(o => {
      const li = item('li', 'flex justify-between gap-3 border-b border-outline-variant/30 py-2 text-xs');
      const inner = item('div');inner.append(item('p', 'font-bold text-on-surface', o.reference),item('p','text-outline',o.service));
      li.append(inner, item('span','font-bold text-on-surface',currency.format(o.price)));bookings.append(li);
    });
    $('[data-citizen-orders-empty]').classList.toggle('hidden', linkedCitizenOrders(id, orders).length > 0);
    const status = citizenStatus(id,moderation);
    const button = $('[data-citizen-change]');button.textContent = status === 'active' ? 'Nonaktifkan akun (demo)' : 'Aktifkan akun (demo)';
    button.dataset.next = status === 'active' ? 'inactive' : 'active';
    if (!dialog.open) dialog.showModal();
  }
  $('[data-citizen-search]').addEventListener('input', e => {filter.q=e.target.value;renderList();});
  $('[data-citizen-status]').addEventListener('change', e => {filter.status=e.target.value;renderList();});
  $('[data-citizen-range]').addEventListener('change', e => {filter.range=e.target.value;renderList();});
  $('[data-citizen-reset]').addEventListener('click', () => {
    filter={q:'',status:'all',range:'all'};
    $('[data-citizen-search]').value='';$('[data-citizen-status]').value='all';$('[data-citizen-range]').value='all';renderList();
  });
  $('[data-citizen-change]').addEventListener('click', e => {
    if (!selectedId) return;
    pending = { id: selectedId, next: e.target.dataset.next };
    $('[data-citizen-action-label]').textContent = pending.next === 'inactive' ? 'Nonaktifkan akun Warga' : 'Aktifkan akun Warga';
    $('[data-citizen-reason]').value='';$('[data-citizen-action-error]').textContent='';
    dialog.close();actionDialog.showModal();
  });
  $('[data-citizen-action-form]').addEventListener('submit', e => {
    e.preventDefault();if (!pending)return;
    try {
      setCitizenStatus(pending.id,pending.next,$('[data-citizen-reason]').value,readAccounts());
      actionDialog.close();notify('Status akun diperbarui dalam mode demo. Ini bukan pemblokiran akses sungguhan.');renderList();
    }catch(error){$('[data-citizen-action-error]').textContent=error.message;}
  });
  root.querySelectorAll('[data-admin-close]').forEach(b => b.addEventListener('click', () => b.closest('dialog')?.close()));
  window.addEventListener('storage', e => {if ([ACCOUNT_KEY,ORDER_KEY,ADMIN_CITIZEN_KEY,null].includes(e.key))renderList();});
  window.addEventListener(ADMIN_MANAGEMENT_EVENT,renderList);
  window.addEventListener('jasawarga:account-updated',renderList);
  window.addEventListener('jasawarga:orders-updated',renderList);
  root.querySelectorAll('[data-admin-soon]').forEach(button => button.addEventListener('click', () => notify(`${button.dataset.adminSoon || 'Modul'} belum tersedia di prototipe ini. Silakan gunakan menu Manajemen Warga atau Mitra.`)));
  renderList();
}
