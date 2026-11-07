/**
 * Analitik Admin JasaWarga: agregasi data lokal browser dan katalog dummy PHP.
 * Fungsi murni agar bisa diuji tanpa DOM maupun localStorage.
 * Tidak menghitung transaksi nyata, akun lintas perangkat, atau pengajuan server.
 */
export const ADMIN_STATUSES = Object.freeze({
  pending: 'Menunggu konfirmasi',
  accepted: 'Diterima mitra',
  enroute: 'Dalam perjalanan',
  working: 'Sedang dikerjakan',
  completed: 'Selesai',
  cancelled: 'Dibatalkan Warga',
  rejected: 'Ditolak Mitra',
});

const safeList = value => Array.isArray(value) ? value : [];
const safeCount = value => Number.isFinite(Number(value)) ? Math.max(0, Number(value)) : 0;
const dateKey = value => {
  const date = new Date(value);
  if (!Number.isFinite(date.getTime())) return null;
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
};

export function completedTime(order) {
  if (order?.status !== 'completed') return null;
  const event = safeList(order.history).findLast(entry =>
    entry?.status === 'completed' && Number.isFinite(entry.at));
  return event?.at ?? order.createdAt ?? null;
}

/** Bar per hari kalender lokal, 7 hari termasuk hari ini. */
export function sevenDayOrders(orders, now = Date.now()) {
  const days = Array.from({ length: 7 }, (_, index) => {
    const date = new Date(now);
    date.setHours(12, 0, 0, 0);
    date.setDate(date.getDate() - (6 - index));
    return { date: dateKey(date.getTime()), timestamp: date.getTime(), created: 0, completed: 0 };
  });
  const byDate = new Map(days.map(day => [day.date, day]));
  safeList(orders).forEach(order => {
    const created = byDate.get(dateKey(order.createdAt));
    if (created) created.created += 1;
    const finished = byDate.get(dateKey(completedTime(order)));
    if (finished && order.status === 'completed') finished.completed += 1;
  });
  return days;
}

export function summarizeAdmin({ accounts = [], providers = [], orders = [], reviews = [], serviceCounts = [] } = {}, now = Date.now()) {
  const list = safeList(orders);
  const catalog = safeList(providers);
  const reviewsList = safeList(reviews);
  const completed = list.filter(order => order.status === 'completed');
  return {
    // Browser = hanya data lokal milik origin/browser yang sama.
    citizenAccountsLocal: safeList(accounts).length,
    ordersLocal: list.length,
    pendingOrdersLocal: list.filter(order => order.status === 'pending').length,
    activeOrdersLocal: list.filter(order => ['accepted', 'enroute', 'working'].includes(order.status)).length,
    completedOrdersLocal: completed.length,
    reviewsLocal: reviewsList.length,
    estimatedCompletedTariff: completed.reduce((sum, order) => sum + safeCount(order.price), 0),
    // Katalog = seed demonstrasi PHP + override layanan di browser, BUKAN mitra terdaftar nyata.
    catalogProvidersDemo: catalog.length,
    catalogVerifiedDemo: catalog.filter(provider => provider.verified === true).length,
    catalogUnverifiedDemo: catalog.filter(provider => provider.verified !== true).length,
    activeServicesCatalogDemo: safeList(serviceCounts).reduce((sum, item) => sum + safeCount(item.activeCount), 0),
    chart: sevenDayOrders(list, now),
  };
}

/** Maksimal 10 catatan aktivitas, tanpa alamat pelanggan / nomor WhatsApp. */
export function recentAdminActivity({ orders = [], reviews = [], accounts = [] } = {}) {
  const activities = [];
  safeList(orders).forEach(order => {
    safeList(order.history).forEach(entry => {
      if (!Object.hasOwn(ADMIN_STATUSES, entry?.status) || !Number.isFinite(entry.at)) return;
      activities.push({
        type: 'order', at: entry.at, reference: String(order.reference || ''),
        title: ADMIN_STATUSES[entry.status],
        detail: String(order.provider || 'Mitra demo').slice(0, 90),
      });
    });
  });
  safeList(reviews).forEach(review => {
    if (!Number.isFinite(review.createdAt)) return;
    activities.push({
      type: 'review', at: review.createdAt, reference: String(review.reference || ''),
      title: 'Ulasan diberikan',
      detail: `${Number(review.rating) || 0}/5 · ${String(review.provider || 'Mitra').slice(0, 80)}`,
    });
  });
  safeList(accounts).forEach(account => {
    if (!Number.isFinite(account.createdAt)) return;
    activities.push({
      type: 'account', at: account.createdAt, reference: '',
      title: 'Akun Warga lokal dibuat',
      detail: 'Tersimpan di browser ini (identitas disembunyikan)',
    });
  });
  return activities.sort((a, b) => b.at - a.at).slice(0, 10);
}

export function filterAdminOrders(orders, { query = '', status = 'all' } = {}) {
  const needle = String(query).trim().toLocaleLowerCase('id-ID');
  const statusFilter = Object.hasOwn(ADMIN_STATUSES, status) ? status : 'all';
  return safeList(orders).filter(order => {
    if (statusFilter !== 'all' && order.status !== statusFilter) return false;
    if (!needle) return true;
    return [order.reference, order.provider, order.service, order.area]
      .some(value => String(value || '').toLocaleLowerCase('id-ID').includes(needle));
  }).sort((a, b) => Number(b.createdAt) - Number(a.createdAt));
}
