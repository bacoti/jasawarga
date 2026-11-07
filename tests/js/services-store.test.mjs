import test from 'node:test';
import assert from 'node:assert/strict';
import {
  SERVICES_KEY, seedServices, readServices, activeServices, saveService,
  setServiceActive, deleteService, normalizeServiceId,
} from '../../resources/js/services-store.js';

const data = new Map();
globalThis.localStorage = {
  getItem: key => data.get(key) ?? null,
  setItem: (key, value) => data.set(key, String(value)),
  removeItem: key => data.delete(key),
};
globalThis.window = { dispatchEvent() {} };
globalThis.CustomEvent = class { constructor(type, options) { this.type = type; this.detail = options?.detail; } };
const seed = [
  { name: 'Cuci AC standar', description: 'Membersihkan komponen AC standar', price: 75000, duration: '45–60 menit' },
  { name: 'Pengisian freon', description: 'Layanan isi ulang gas refrigeran', price: 120000, duration: '1 jam' },
];
const form = {
  name: 'Servis AC Premium', description: 'Pembersihan intensif dan pengecekan mesin AC',
  category: 'ac', price: 175000, duration: '90 menit', unit: 'per unit',
  feeNote: 'Suku cadang dibayar terpisah.',
};

function reset() { data.clear(); }

test('seed diberikan per mitra tanpa menulis localStorage', () => {
  reset();
  assert.equal(readServices(1, seed, 'ac').length, 2);
  assert.equal(readServices(1, seed, 'ac')[0].id, 'base-0');
  assert.equal(data.has(SERVICES_KEY), false);
  assert.equal(normalizeServiceId('0'), 'base-0');
  assert.equal(normalizeServiceId('custom-1234'), 'custom-1234');
});

test('CRUD layanan: tambah, edit, nonaktif, aktifkan, hapus', () => {
  reset();
  let values = saveService(1, seed, 'ac', form, ['ac', 'pipa']);
  assert.equal(values.length, 3);
  const customId = values[2].id;
  assert.match(customId, /^custom-/);
  values = saveService(1, seed, 'ac', { ...form, price: 190000, name: 'Servis AC Intensif' }, ['ac'], customId);
  assert.equal(values[2].id, customId);
  assert.equal(values[2].price, 190000);
  values = setServiceActive(1, seed, 'ac', customId, false);
  assert.equal(activeServices(1, seed, 'ac').length, 2);
  assert.equal(values[2].active, false);
  values = setServiceActive(1, seed, 'ac', customId, true);
  assert.equal(activeServices(1, seed, 'ac').length, 3);
  values = deleteService(1, seed, 'ac', customId);
  assert.equal(values.length, 2);
  assert.equal(activeServices(1, seed, 'ac').length, 2);
});

test('layanan bawaan dapat dihapus seluruhnya tanpa seed otomatis muncul kembali', () => {
  reset();
  deleteService(1, seed, 'ac', 'base-0');
  deleteService(1, seed, 'ac', 'base-1');
  assert.deepEqual(readServices(1, seed, 'ac'), []);
  assert.deepEqual(activeServices(1, seed, 'ac'), []);
});

test('perubahan satu mitra tidak memengaruhi seed mitra lain', () => {
  reset();
  setServiceActive(1, seed, 'ac', 'base-0', false);
  assert.equal(activeServices(1, seed, 'ac').length, 1);
  assert.equal(activeServices(2, seed, 'ac').length, 2);
});

test('validasi nominal, kategori, deskripsi, dan unit', () => {
  reset();
  assert.throws(() => saveService(1, seed, 'ac', { ...form, price: 9000 }, ['ac']), /Tarif/);
  assert.throws(() => saveService(1, seed, 'ac', { ...form, category: 'rahasia' }, ['ac']), /Kategori/);
  assert.throws(() => saveService(1, seed, 'ac', { ...form, description: 'singkat' }, ['ac']), /Deskripsi/);
  assert.throws(() => saveService(1, seed, 'ac', { ...form, unit: 'per kilometer' }, ['ac']), /Satuan/);
});

test('gagal simpan tidak mengubah state dan error diteruskan', () => {
  reset();
  const oldSetItem = localStorage.setItem;
  localStorage.setItem = () => { throw new Error('Quota exceeded'); };
  assert.throws(() => setServiceActive(1, seed, 'ac', 'base-0', false), /Quota/);
  localStorage.setItem = oldSetItem;
  assert.equal(readServices(1, seed, 'ac')[0].active, true);
});
