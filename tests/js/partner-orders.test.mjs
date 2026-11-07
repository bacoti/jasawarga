import test from 'node:test';
import assert from 'node:assert/strict';
import {
  ORDER_LABELS, ORDER_STEPS, createOrderFromBooking,
  changeOrderStatus, getOrder, readOrders, clearOrders, rejectPartnerOrder,
  nextOrderStatus,
} from '../../resources/js/orders-store.js';
import { summarizePartnerOrders } from '../../resources/js/partner-store.js';

const memory = new Map();
globalThis.localStorage = {
  getItem: key => memory.get(key) ?? null,
  setItem: (key, value) => memory.set(key, String(value)),
  removeItem: key => memory.delete(key),
};
globalThis.window = { dispatchEvent() {} };
globalThis.CustomEvent = class { constructor(type) { this.type = type; } };
const sample = (suffix, providerId = 1) => ({
  reference: `JW-DEMO-20261107-${suffix}`,
  providerId, provider: providerId === 1 ? 'Budi Teknik' : 'Mitra lain',
  name: 'Pelanggan Demo', phone: '0812****7890',
  service: 'Cuci AC standar', serviceIndex: 0, price: 75000,
  date: '2026-11-08', time: '11:00', area: 'Panakkukang',
  address: 'Alamat fiktif', description: 'Cuci AC simulasi', createdAt: Date.now(),
});

test('menerima pesanan: hanya transisi berurutan sampai selesai', () => {
  clearOrders();
  const ref = sample('00AA11').reference;
  createOrderFromBooking(sample('00AA11'));
  assert.equal(changeOrderStatus(ref, 'working'), null);
  assert.equal(nextOrderStatus('pending'), 'accepted');
  for (const status of ORDER_STEPS.slice(1)) assert.equal(changeOrderStatus(ref, status)?.status, status);
  assert.equal(getOrder(ref).history.length, 5);
  assert.equal(changeOrderStatus(ref, 'accepted'), null);
  assert.equal(rejectPartnerOrder(ref, 'Jadwal tidak tersedia.'), null);
});

test('menolak pesanan: wajib alasan 10–300 karakter dan hanya status menunggu', () => {
  clearOrders();
  const ref = sample('00AA12').reference;
  createOrderFromBooking(sample('00AA12'));
  assert.throws(() => rejectPartnerOrder(ref, 'Tidak'), /10–300/);
  assert.throws(() => rejectPartnerOrder(ref, 'A'.repeat(301)), /10–300/);
  assert.equal(getOrder(ref).status, 'pending');
  const record = rejectPartnerOrder(ref, 'Jadwal pada hari itu sudah penuh.');
  assert.equal(record.status, 'rejected');
  assert.equal(getOrder(ref).rejectionReason, 'Jadwal pada hari itu sudah penuh.');
  assert.equal(getOrder(ref).history.at(-1).actor, 'mitra');
  assert.equal(getOrder(ref).history.at(-1).reason, 'Jadwal pada hari itu sudah penuh.');
  assert.equal(ORDER_LABELS.rejected, 'Ditolak mitra');
  assert.equal(rejectPartnerOrder(ref, 'Alasan penolakan kedua'), null);
  assert.equal(changeOrderStatus(ref, 'accepted'), null);
});

test('pesanan batal dari Warga tidak dapat diterima oleh Mitra', () => {
  clearOrders();
  const ref = sample('00AA13').reference;
  createOrderFromBooking(sample('00AA13'));
  assert.equal(changeOrderStatus(ref, 'cancelled')?.status, 'cancelled');
  assert.equal(changeOrderStatus(ref, 'accepted'), null);
  assert.equal(rejectPartnerOrder(ref, 'Jadwal tidak dapat dipenuhi.'), null);
});

test('hanya pesanan mitra terpilih yang memengaruhi statistik mitra', () => {
  clearOrders();
  createOrderFromBooking(sample('00AA14', 1));
  createOrderFromBooking(sample('00AA15', 2));
  rejectPartnerOrder(sample('00AA14').reference, 'Jadwal kerja tidak memungkinkan.');
  const stats = summarizePartnerOrders(readOrders(), 1);
  assert.equal(stats.total, 1);
  assert.equal(stats.pending, 0);
  assert.equal(stats.cancelled, 0);
  assert.equal(readOrders().find(o => o.providerId === 2).status, 'pending');
});

test('metadata pelanggan yang disimpan untuk mitra hanya nomor yang sudah disamarkan', () => {
  clearOrders();
  const record = createOrderFromBooking(sample('00AA16'));
  assert.equal(record.customerName, 'Pelanggan Demo');
  assert.equal(record.customerPhone, '0812****7890');
  assert.equal(readOrders()[0].customerPhone, '0812****7890');
});
