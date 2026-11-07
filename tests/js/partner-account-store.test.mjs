import test from 'node:test';
import assert from 'node:assert/strict';
import {
  PARTNER_ACCOUNT_KEY, PARTNER_ACCOUNT_EVENT, PARTNER_DEMO_SESSION_KEY,
  defaultPartnerAccount, readPartnerAccount, savePartnerIdentity, savePartnerPreferences,
  validateDemoPassword, enterPartnerDemo, exitPartnerDemo, isPartnerDemoActive,
} from '../../resources/js/partner-account-store.js';
import { getPartnerAvailability, setPartnerAvailability, PARTNER_SETTINGS_KEY } from '../../resources/js/partner-store.js';

const storage = new Map();
const sessions = new Map();
const events = [];
globalThis.localStorage = {
  getItem: key => storage.get(key) ?? null,
  setItem: (key, value) => storage.set(key, String(value)),
  removeItem: key => storage.delete(key),
};
globalThis.sessionStorage = {
  getItem: key => sessions.get(key) ?? null,
  setItem: (key, value) => sessions.set(key, String(value)),
  removeItem: key => sessions.delete(key),
};
globalThis.window = { dispatchEvent: event => events.push(event) };
globalThis.CustomEvent = class { constructor(type, init) { this.type = type; this.detail = init?.detail; } };
const reset = () => { storage.clear(); sessions.clear(); events.length = 0; };

test('default akun demo aman dan tidak disimpan otomatis', () => {
  reset();
  const account = readPartnerAccount(1);
  assert.equal(account.ownerName, 'Pengelola Mitra (Demo)');
  assert.equal(account.privacy.showBusinessPhone, false);
  assert.equal(account.operations.showRevenue, true);
  assert.equal(storage.has(PARTNER_ACCOUNT_KEY), false);
  assert.equal('verified' in defaultPartnerAccount(1), false);
  assert.equal('password' in defaultPartnerAccount(1), false);
});

test('kontak disimpan hanya pada ID Mitra target, tidak mengubah data Mitra lain', () => {
  reset();
  savePartnerIdentity(1, { ownerName: '  Pengelola   Teknik  ', email: 'CONTOH@JASAWARGA.TEST', phone: '0812 3456 7890' });
  assert.equal(readPartnerAccount(1).ownerName, 'Pengelola Teknik');
  assert.equal(readPartnerAccount(1).email, 'contoh@jasawarga.test');
  assert.equal(readPartnerAccount(1).phone, '081234567890');
  assert.equal(readPartnerAccount(2).email, 'mitra.demo@jasawarga.test');
  assert.equal(events.at(-1).type, PARTNER_ACCOUNT_EVENT);
  assert.equal(events.at(-1).detail.providerId, 1);
});

test('format nama, email, WhatsApp, dan ID wajib valid', () => {
  reset();
  const fields = { ownerName: 'Pengelola Jasa', email: 'owner@test.example', phone: '+6281234567890' };
  assert.throws(() => savePartnerIdentity(0, fields), /ID Mitra/);
  assert.throws(() => savePartnerIdentity(1, { ...fields, ownerName: 'A' }), /Nama/);
  assert.throws(() => savePartnerIdentity(1, { ...fields, email: 'invalid' }), /email/);
  assert.throws(() => savePartnerIdentity(1, { ...fields, phone: '123' }), /WhatsApp/);
  assert.equal(storage.has(PARTNER_ACCOUNT_KEY), false);
});

test('preferensi privasi dan operasional tersimpan tanpa mengubah ketersediaan lama', () => {
  reset();
  setPartnerAvailability(1, false);
  assert.equal(getPartnerAvailability(1).available, false);
  savePartnerPreferences(1, {
    privacy: { showBusinessPhone: true, showCoverage: false },
    operations: { showRevenue: false, scheduleReminder: true },
    notifications: { newOrder: false },
  });
  const account = readPartnerAccount(1);
  assert.deepEqual(account.privacy, { showBusinessPhone: true, showCoverage: false });
  assert.equal(account.operations.showRevenue, false);
  assert.equal(account.operations.scheduleReminder, true);
  assert.equal(account.notifications.newReview, true);
  assert.equal(getPartnerAvailability(1).available, false);
  assert.equal(storage.has(PARTNER_SETTINGS_KEY), true);
});

test('bukan untuk mengedit verifikasi; unknown field tidak ikut tersimpan', () => {
  reset();
  savePartnerIdentity(1, { ownerName: 'Pengelola Teknik', email: 'test@example.test', phone: '' , verified: false, password: 'VerySecret!' });
  savePartnerPreferences(1, { verified: true, privacy: { showCoverage: false } });
  const persisted = storage.get(PARTNER_ACCOUNT_KEY);
  assert.doesNotMatch(persisted, /verified|password|VerySecret/);
  assert.equal(readPartnerAccount(1).privacy.showCoverage, false);
  assert.throws(() => savePartnerPreferences(1, { privacy: { unknown: true } }), /tidak valid/);
});

test('password hanya divalidasi dalam memori fungsi dan tidak pernah ditulis ke storage', () => {
  reset();
  assert.equal(validateDemoPassword('Contoh123!', 'Contoh123!'), true);
  assert.throws(() => validateDemoPassword('1234567', '1234567'), /8–128/);
  assert.throws(() => validateDemoPassword('Contoh123!', 'Berbeda123!'), /tidak sama/);
  assert.equal(storage.has(PARTNER_ACCOUNT_KEY), false);
  assert.equal(sessions.has(PARTNER_DEMO_SESSION_KEY), false);
});

test('logout hanya menghapus penanda sesi demo, bukan pesanan/profil', () => {
  reset();
  storage.set('jasawarga:prototype-orders:v1', '[{"reference":"JW-DEMO-ABC"}]');
  savePartnerIdentity(1, { ownerName: 'Pengelola Teknik', email: 'test@example.test', phone: '' });
  enterPartnerDemo();
  assert.equal(isPartnerDemoActive(), true);
  exitPartnerDemo();
  assert.equal(isPartnerDemoActive(), false);
  assert.equal(readPartnerAccount(1).ownerName, 'Pengelola Teknik');
  assert.equal(storage.has('jasawarga:prototype-orders:v1'), true);
});

test('storage rusak tidak merender data asing sebagai pengaturan', () => {
  reset();
  storage.set(PARTNER_ACCOUNT_KEY, '{bad-json');
  assert.equal(readPartnerAccount(1).privacy.showBusinessPhone, false);
  storage.set(PARTNER_ACCOUNT_KEY, JSON.stringify({ 1: { ownerName: 'X', verified: true } }));
  assert.equal(readPartnerAccount(1).ownerName, 'Pengelola Mitra (Demo)');
});

test('gagal menyimpan karena kuota tidak boleh diklaim berhasil', () => {
  reset();
  const old = globalThis.localStorage.setItem;
  globalThis.localStorage.setItem = () => { throw new Error('QuotaExceededError'); };
  assert.throws(() => savePartnerIdentity(1, { ownerName: 'Pemilik Teknis', email: 'owner@example.test', phone: '' }), /Penyimpanan browser/);
  globalThis.localStorage.setItem = old;
  assert.equal(storage.has(PARTNER_ACCOUNT_KEY), false);
});
