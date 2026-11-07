import test from 'node:test';
import assert from 'node:assert/strict';
import {
  createOrderFromBooking, changeOrderStatus, clearOrders,
} from '../../resources/js/orders-store.js';
import {
  saveReview, clearReviews, getReview, reviewsForProvider,
} from '../../resources/js/reviews-store.js';
import {
  PARTNER_REPLIES_KEY, readPartnerReplies, getPartnerReply,
  repliesForProvider, savePartnerReply, deletePartnerReply,
} from '../../resources/js/partner-review-replies-store.js';

const memory = new Map();
globalThis.localStorage = {
  getItem(key) { return memory.get(key) ?? null; },
  setItem(key, value) { memory.set(key, String(value)); },
  removeItem(key) { memory.delete(key); },
};
globalThis.window = { dispatchEvent() {} };
globalThis.CustomEvent = class { constructor(type) { this.type = type; } };
const ref = suffix => `JW-DEMO-20261107-${suffix}`;

function reset() {
  clearOrders(); clearReviews(); localStorage.removeItem(PARTNER_REPLIES_KEY);
}
function reviewFor(providerId, suffix) {
  const reference = ref(suffix);
  createOrderFromBooking({
    reference, providerId, provider: `Mitra ${providerId}`, service: 'Cuci AC demo',
    price: 75000, date: '2026-11-10', time: '11:00', area: 'Makassar',
    address: 'Alamat fiktif', description: 'Pekerjaan uji.', createdAt: Date.now(),
  });
  for (const status of ['accepted', 'enroute', 'working', 'completed']) {
    assert.equal(changeOrderStatus(reference, status)?.status, status);
  }
  saveReview({
    reference, rating: 5,
    details: { quality: 5, punctuality: 4, friendliness: 5 },
    comment: 'Pekerjaan demo diselesaikan dengan hasil sangat memuaskan.', photos: [],
  });
  return reference;
}

test('balasan hanya pada ulasan milik mitra yang tepat', () => {
  reset();
  const a = reviewFor(1, 'A1B2C3');
  const b = reviewFor(2, 'D4E5F6');
  assert.throws(() => savePartnerReply(2, a, 'Terima kasih atas sarannya.'), /tidak ditemukan/);
  assert.throws(() => savePartnerReply(1, ref('111111'), 'Terima kasih atas sarannya.'), /tidak ditemukan/);
  const saved = savePartnerReply(1, a, 'Terima kasih telah mempercayai layanan kami.');
  assert.equal(saved.providerId, 1);
  assert.equal(repliesForProvider(1).length, 1);
  assert.equal(getPartnerReply(a, 2), null);
  assert.equal(getPartnerReply(b, 1), null);
});

test('balasan dapat diedit, tanggal pertama tetap dan satu per ulasan', () => {
  const a = ref('A1B2C3');
  const old = getPartnerReply(a, 1);
  const next = savePartnerReply(1, a, 'Terima kasih, kami senang dapat membantu pekerjaan Anda.');
  assert.equal(next.createdAt, old.createdAt);
  assert.equal(readPartnerReplies().length, 1);
  assert.match(getPartnerReply(a, 1).text, /senang/);
});

test('validasi isi 10–500 karakter serta tidak mengubah rating Warga', () => {
  const a = ref('A1B2C3');
  assert.throws(() => savePartnerReply(1, a, 'singkat'), /10–500/);
  assert.throws(() => savePartnerReply(1, a, 'x'.repeat(501)), /10–500/);
  const original = getReview(a);
  savePartnerReply(1, a, '<script>alert(1)</script> Teks hanya boleh dirender dengan textContent.');
  assert.deepEqual(getReview(a), original);
  assert.equal(reviewsForProvider(1).length, 1);
});

test('hapus balasan mempertahankan ulasan pelanggan', () => {
  const a = ref('A1B2C3');
  assert.equal(deletePartnerReply(2, a), false);
  assert.equal(deletePartnerReply(1, a), true);
  assert.equal(deletePartnerReply(1, a), false);
  assert.equal(getPartnerReply(a, 1), null);
  assert.equal(getReview(a)?.rating, 5);
});

test('data rusak dan balasan tanpa ulasan tidak ditampilkan', () => {
  const b = ref('D4E5F6');
  savePartnerReply(2, b, 'Terima kasih atas penilaian dan masukan yang diberikan.');
  assert.equal(repliesForProvider(2).length, 1);
  localStorage.setItem(PARTNER_REPLIES_KEY, '{malformed');
  assert.deepEqual(readPartnerReplies(), []);
  savePartnerReply(2, b, 'Balasan valid kembali setelah data sebelumnya rusak.');
  clearReviews();
  assert.deepEqual(readPartnerReplies(), []);
});

test('penyimpanan gagal tidak menyatakan balasan berhasil', () => {
  reset();
  const a = reviewFor(1, 'ABCDEF');
  const setter = localStorage.setItem;
  localStorage.setItem = () => { throw new Error('QuotaExceededError'); };
  assert.throws(() => savePartnerReply(1, a, 'Terima kasih atas penilaiannya.'), /QuotaExceededError/);
  localStorage.setItem = setter;
  assert.equal(getPartnerReply(a, 1), null);
});
