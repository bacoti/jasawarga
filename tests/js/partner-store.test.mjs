import test from 'node:test';
import assert from 'node:assert/strict';
import {
  getPartnerAvailability,
  setPartnerAvailability,
  summarizePartnerOrders,
  seedPartnerDemoOrders,
  PARTNER_SETTINGS_KEY,
} from '../../resources/js/partner-store.js';
import {
  readOrders,
  clearOrders,
  changeOrderStatus,
} from '../../resources/js/orders-store.js';

const memory = new Map();
globalThis.localStorage = {
  getItem(key) { return memory.has(key) ? memory.get(key) : null; },
  setItem(key, value) { memory.set(key, String(value)); },
  removeItem(key) { memory.delete(key); },
};
globalThis.CustomEvent = class { constructor(type) { this.type = type; } };
globalThis.window = { dispatchEvent() {} };

function order({ providerId, status, price, createdAt, completedAt = createdAt, reference = 'JW-DEMO-20261107-A1B2C3' }) {
  return { providerId, status, price, createdAt, reference, service: 'Cuci AC', history: [
    { status: 'pending', at: createdAt },
    ...(status === 'completed' ? [{ status: 'completed', at: completedAt }] : []),
  ] };
}

test('status ketersediaan mitra demo disimpan terpisah per ID', () => {
  memory.delete(PARTNER_SETTINGS_KEY);
  assert.deepEqual(getPartnerAvailability(1), { available: true, updatedAt: null });
  const changed = setPartnerAvailability(1, false);
  assert.equal(changed.available, false);
  assert.equal(getPartnerAvailability(1).available, false);
  assert.equal(getPartnerAvailability(2).available, true);
  assert.throws(() => setPartnerAvailability(0, true));
});

test('kalkulasi dashboard hanya menghitung pesanan milik mitra terpilih', () => {
  const now = new Date('2026-11-07T12:00:00').getTime();
  const input = [
    order({ providerId: 1, status: 'pending', price: 75000, createdAt: now - 60000 }),
    order({ providerId: 1, status: 'accepted', price: 90000, createdAt: now - 120000 }),
    order({ providerId: 1, status: 'completed', price: 120000, createdAt: now - 180000 }),
    order({ providerId: 2, status: 'completed', price: 4000000, createdAt: now - 240000 }),
  ];
  const result = summarizePartnerOrders(input, 1, now);
  assert.equal(result.total, 3);
  assert.equal(result.pending, 1);
  assert.equal(result.active, 1);
  assert.equal(result.completed, 1);
  assert.equal(result.estimatedRevenue, 120000);
  assert.equal(result.weekRevenue, 120000);
  assert.equal(result.days.length, 7);
});

test('grafik tujuh hari mengecualikan pekerjaan selesai di luar periode', () => {
  const now = new Date('2026-11-07T12:00:00').getTime();
  const past = now - 10 * 86400000;
  const input = [
    order({ providerId: 1, status: 'completed', price: 200000, createdAt: past, completedAt: past }),
    order({ providerId: 1, status: 'completed', price: 85000, createdAt: now, completedAt: now }),
  ];
  const result = summarizePartnerOrders(input, 1, now);
  assert.equal(result.estimatedRevenue, 285000);
  assert.equal(result.weekRevenue, 85000);
  assert.equal(result.activities.some(activity => activity.status === 'completed'), true);
});

test('empat pesanan mitra demo muncul di data Pesanan Saya dengan status berbeda', () => {
  clearOrders();
  const refs = seedPartnerDemoOrders({ id: 1, name: 'Budi Teknik', service: 'Cuci AC standar', price: 75000 });
  assert.equal(refs.length, 4);
  const stored = readOrders();
  assert.equal(stored.length, 4);
  assert.equal(stored.every(item => item.providerId === 1), true);
  assert.deepEqual(new Set(stored.map(item => item.status)), new Set(['pending','accepted','working','completed']));
  const completed = stored.find(item => item.status === 'completed');
  assert.equal(completed.history.length, 5);
  assert.equal(changeOrderStatus(completed.reference, 'cancelled'), null);
  clearOrders();
});
