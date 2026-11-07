import test from 'node:test';
import assert from 'node:assert/strict';
import {
  ADMIN_STATUSES, summarizeAdmin, recentAdminActivity,
  filterAdminOrders, sevenDayOrders,
} from '../../resources/js/admin-overview-data.js';

const NOW = new Date('2026-11-07T12:00:00').getTime();
function makeOrder(reference, status, providerId = 1, daysAgo = 0, price = 75000) {
  const at = NOW - daysAgo * 86400000;
  return {
    reference, status, providerId, createdAt: at,
    provider: providerId === 1 ? 'Budi Teknik' : 'Joko Service',
    service: 'Cuci AC', area: 'Panakkukang', price,
    history: [{ status: 'pending', at }, ...(status === 'completed' ? [{ status: 'completed', at: at + 1500 }] : [])],
  };
}

const orders = [
  makeOrder('JW-DEMO-20261107-A1B2C3', 'pending'),
  makeOrder('JW-DEMO-20261107-A1B2C4', 'completed', 2, 1, 120000),
  makeOrder('JW-DEMO-20261107-A1B2C5', 'cancelled', 1, 9, 90000),
];

test('KPI membedakan akun lokal, katalog dummy dan hitungan layanan aktif', () => {
  const result = summarizeAdmin({
    accounts: [{ id: 'one' }],
    providers: [{ id: 1, verified: true }, { id: 2, verified: false }],
    orders, reviews: [{ providerId: 2, rating: 5 }],
    serviceCounts: [{ providerId: 1, activeCount: 3 }, { providerId: 2, activeCount: 2 }],
  }, NOW);
  assert.equal(result.citizenAccountsLocal, 1);
  assert.equal(result.catalogProvidersDemo, 2);
  assert.equal(result.catalogUnverifiedDemo, 1);
  assert.equal(result.activeServicesCatalogDemo, 5);
  assert.equal(result.ordersLocal, 3);
  assert.equal(result.pendingOrdersLocal, 1);
  assert.equal(result.completedOrdersLocal, 1);
  assert.equal(result.estimatedCompletedTariff, 120000);
  assert.equal(result.reviewsLocal, 1);
});

test('grafik 7 hari mengabaikan pesanan lama dan hanya hitung selesai', () => {
  const days = sevenDayOrders(orders, NOW);
  assert.equal(days.length, 7);
  assert.equal(days.reduce((sum, day) => sum + day.created, 0), 2);
  assert.equal(days.reduce((sum, day) => sum + day.completed, 0), 1);
  assert.equal(days[6].created, 1);
  assert.equal(days[5].completed, 1);
});

test('filter pesanan mencari kode, mitra, kategori status dengan huruf tidak sensitif', () => {
  assert.equal(filterAdminOrders(orders, { query: 'BUDI' }).length, 2);
  assert.equal(filterAdminOrders(orders, { status: 'completed' }).length, 1);
  assert.equal(filterAdminOrders(orders, { query: 'JOKO', status: 'pending' }).length, 0);
  assert.equal(filterAdminOrders(orders, { status: '__invalid__' }).length, 3);
  assert.equal(filterAdminOrders(orders, { query: 'Tidak ada' }).length, 0);
});

test('aktivitas tidak menampilkan nomor kontak atau alamat pelanggan', () => {
  const activity = recentAdminActivity({
    orders, accounts: [{ name: 'Nama Rahasia', createdAt: NOW - 100 }],
    reviews: [{ createdAt: NOW - 200, rating: 4, reference: orders[0].reference, provider: 'Budi Teknik' }],
  });
  assert.equal(activity[0].title, 'Menunggu konfirmasi');
  assert.ok(activity.some(item => item.title === 'Akun Warga lokal dibuat'));
  assert.ok(activity.some(item => item.title === 'Ulasan diberikan'));
  assert.equal(JSON.stringify(activity).includes('Nama Rahasia'), false);
  assert.equal(activity.length <= 10, true);
});

test('data kosong aman dan status penolakan terpisah dari pembatalan', () => {
  const data = summarizeAdmin({}, NOW);
  assert.equal(data.ordersLocal, 0);
  assert.equal(data.catalogProvidersDemo, 0);
  assert.equal(ADMIN_STATUSES.rejected, 'Ditolak Mitra');
  assert.notEqual(ADMIN_STATUSES.rejected, ADMIN_STATUSES.cancelled);
});
