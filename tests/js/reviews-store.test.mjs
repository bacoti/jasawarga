import test from 'node:test';
import assert from 'node:assert/strict';
import { createOrderFromBooking, changeOrderStatus, clearOrders } from '../../resources/js/orders-store.js';
import { saveReview, getReview, readReviews, reviewsForProvider, clearReviews, REVIEW_KEY } from '../../resources/js/reviews-store.js';

const memory = new Map();
globalThis.localStorage = {
  getItem(key) { return memory.get(key) ?? null; },
  setItem(key, value) { memory.set(key, String(value)); },
  removeItem(key) { memory.delete(key); },
};
globalThis.window = { dispatchEvent() {} };
globalThis.CustomEvent = class { constructor(name) { this.type = name; } };
const code = (suffix) => `JW-DEMO-20261107-${suffix}`;
function newOrder(suffix, providerId) {
  return createOrderFromBooking({
    reference: code(suffix), providerId, provider: `Mitra ${providerId}`,
    service: 'Layanan contoh', serviceIndex: 0, price: 75000,
    date: '2026-11-09', time: '11:00', area: 'Makassar',
    address: 'Alamat simulasi', description: 'Deskripsi simulasi',
    createdAt: Date.now(),
  });
}
const payload = reference => ({ reference, rating: 5, details: {
  quality: 5, punctuality: 4, friendliness: 5,
}, comment: 'Pelayanan cepat dan pekerjaan cukup rapi untuk kebutuhan saya.', photos: [] });

function finish(reference) {
  for (const status of ['accepted', 'enroute', 'working', 'completed']) {
    assert.equal(changeOrderStatus(reference, status)?.status, status);
  }
}

test('hanya pesanan selesai dapat diulas', () => {
  clearOrders(); clearReviews();
  newOrder('A1B2C3', 1);
  assert.throws(() => saveReview(payload(code('A1B2C3'))), /Hanya pesanan/);
  finish(code('A1B2C3'));
  const saved = saveReview(payload(code('A1B2C3')));
  assert.equal(saved.providerId, 1);
  assert.equal(getReview(saved.reference)?.rating, 5);
  assert.equal(reviewsForProvider(1).length, 1);
});

test('satu ulasan saja per pesanan dan rating valid', () => {
  assert.throws(() => saveReview(payload(code('A1B2C3'))), /sudah memiliki/);
  newOrder('D4E5F6', 2);
  finish(code('D4E5F6'));
  assert.throws(() => saveReview({ ...payload(code('D4E5F6')), rating: 9 }), /Rating harus/);
  assert.throws(() => saveReview({ ...payload(code('D4E5F6')), comment: '<script>x</script>' }), /Rating harus/);
  assert.equal(readReviews().length, 1);
});

test('dua pesanan selesai dari mitra berbeda tersimpan, tidak tercampur', () => {
  const b = saveReview({ ...payload(code('D4E5F6')), rating: 4 });
  assert.equal(b.providerId, 2);
  assert.equal(reviewsForProvider(1).length, 1);
  assert.equal(reviewsForProvider(2).length, 1);
  assert.equal(readReviews().length, 2);
});

test('validasi foto, kerusakan data, dan hapus ulasan', () => {
  newOrder('112233', 1);
  finish(code('112233'));
  assert.throws(() => saveReview({ ...payload(code('112233')), photos: ['data:text/html;base64,YQ=='] }), /Rating harus/);
  assert.equal(getReview(code('112233')), null);
  localStorage.setItem(REVIEW_KEY, '{broken');
  assert.deepEqual(readReviews(), []);
  clearReviews();
  assert.equal(localStorage.getItem(REVIEW_KEY), null);
});
