import test from 'node:test';
import assert from 'node:assert/strict';
import {
  createOrderFromBooking, createSampleOrders, readOrders, getOrder,
  changeOrderStatus, nextOrderStatus, clearOrders, ORDER_KEY,
} from '../../resources/js/orders-store.js';

const memory = new Map();
globalThis.localStorage = {
  getItem(key) { return memory.has(key) ? memory.get(key) : null; },
  setItem(key, value) { memory.set(key, String(value)); },
  removeItem(key) { memory.delete(key); },
};
globalThis.window = { dispatchEvent() {} };
globalThis.CustomEvent = class { constructor(name) { this.type = name; } };

function booking(code, providerId) {
  return {
    reference: `JW-DEMO-20261107-${code}`,
    providerId, provider: `Mitra ${providerId}`, serviceIndex: 1,
    service: 'Servis contoh', price: 75000,
    date: '2026-11-08', time: '11:00', area: 'Panakkukang',
    address: 'Jalan Contoh No. 1', description: 'Simulasi data dummy',
    createdAt: Date.now(),
  };
}

test('menyimpan beberapa pesanan dan tidak membuat duplikat', () => {
  clearOrders();
  const first = createOrderFromBooking(booking('A1B2C3', 1));
  createOrderFromBooking(booking('D4E5F6', 2));
  createOrderFromBooking(booking('A1B2C3', 1));
  assert.equal(readOrders().length, 2);
  assert.equal(first.status, 'pending');
  assert.equal(getOrder(first.reference).history[0].status, 'pending');
});

test('urutan tahap diperiksa dan tidak dapat melompati status', () => {
  const ref = 'JW-DEMO-20261107-A1B2C3';
  assert.equal(changeOrderStatus(ref, 'completed'), null);
  assert.equal(nextOrderStatus('pending'), 'accepted');
  for (const next of ['accepted', 'enroute', 'working', 'completed']) {
    assert.equal(changeOrderStatus(ref, next).status, next);
  }
  assert.equal(getOrder(ref).history.length, 5);
  assert.equal(changeOrderStatus(ref, 'cancelled'), null);
  assert.equal(nextOrderStatus('completed'), null);
});

test('pembatalan hanya boleh ketika menunggu', () => {
  const ref = 'JW-DEMO-20261107-D4E5F6';
  assert.equal(changeOrderStatus(ref, 'cancelled').status, 'cancelled');
  assert.equal(changeOrderStatus(ref, 'accepted'), null);
  assert.equal(getOrder(ref).history.at(-1).status, 'cancelled');
});

test('tombol data contoh menambah tiga pesanan dan hapus riwayat bersih', () => {
  const samples = createSampleOrders();
  assert.equal(samples.length, 3);
  assert.equal(readOrders().length, 5);
  assert.equal(samples.some(o => o.status === 'completed'), true);
  clearOrders();
  assert.equal(readOrders().length, 0);
  assert.equal(localStorage.getItem(ORDER_KEY), null);
});
