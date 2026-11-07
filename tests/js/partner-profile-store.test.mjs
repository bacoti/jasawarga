import test from 'node:test';
import assert from 'node:assert/strict';
import {
  PROFILE_KEY, MAX_PROJECTS, seedPartnerProfile, readPartnerProfile,
  hasPartnerProfileOverride, savePartnerProfile, savePartnerProject,
  removePartnerProject, validateProfileFields,
} from '../../resources/js/partner-profile-store.js';

const records = new Map();
globalThis.localStorage = {
  getItem: key => records.get(key) ?? null,
  setItem: (key, value) => records.set(key, String(value)),
  removeItem: key => records.delete(key),
};
globalThis.window = { dispatchEvent() {} };
globalThis.CustomEvent = class { constructor(type, params) { this.type = type; this.detail = params?.detail; } };
const provider = { id: 1, name: 'Budi Teknik', area: 'Coblong', service: 'Cuci AC', verified: true };
const source = {
  headline: 'Teknisi AC andalan untuk perawatan rumah tangga.',
  about: 'Mitra servis AC di Bandung yang mengutamakan komunikasi terbuka dan pemeriksaan kondisi unit terlebih dahulu.',
  experience: '6 tahun', coverage: ['Coblong', 'Sukajadi'],
  projects: [
    { title: 'Perawatan AC ruang tamu', note: 'Contoh pekerjaan · Coblong', icon: 'ac_unit', color: '#cee5ff' },
  ],
};
const form = {
  name: 'Budi Teknik Premium', headline: 'Teknisi AC berpengalaman untuk rumah dan kantor kecil.',
  about: 'Pembersihan dan perawatan AC rumah serta kantor, dengan jadwal yang disepakati bersama pelanggan terlebih dahulu.',
  specialties: 'Servis AC, Cuci AC', experienceYears: 8, phone: '081234567890',
  coverage: ['Coblong', 'Antapani'], avatarDataUrl: '', coverDataUrl: '',
};
const reset = () => records.clear();

test('seed profil menggunakan konfigurasi PHP tanpa mengklaim status verifikasi lokal', () => {
  reset();
  const seed = seedPartnerProfile(provider, source);
  assert.equal(seed.name, 'Budi Teknik');
  assert.deepEqual(seed.coverage, ['Coblong', 'Sukajadi']);
  assert.equal(seed.projects.length, 1);
  assert.equal(seed.projects[0].isDemo, true);
  assert.equal('verified' in seed, false);
  assert.equal(records.has(PROFILE_KEY), false);
});

test('edit profil tersimpan per mitra tanpa mengubah status verified', () => {
  reset();
  const saved = savePartnerProfile(1, provider, source, { ...form, verified: false });
  assert.equal(saved.name, 'Budi Teknik Premium');
  assert.equal(saved.experienceYears, 8);
  assert.equal(saved.phone, '081234567890');
  assert.equal('verified' in saved, false);
  assert.equal(hasPartnerProfileOverride(1), true);
  assert.equal(readPartnerProfile(2, { ...provider, id: 2 }, source).name, provider.name);
  assert.equal(readPartnerProfile(1, provider, source).name, 'Budi Teknik Premium');
});

test('validasi nama, spesialisasi, kota jangkauan, kontak dan foto', () => {
  reset();
  assert.throws(() => validateProfileFields({ ...form, name: 'A' }), /Nama usaha/);
  assert.throws(() => validateProfileFields({ ...form, specialties: '' }), /spesialisasi/i);
  assert.throws(() => validateProfileFields({ ...form, coverage: ['Bandung'] }), /Bandung/);
  assert.throws(() => validateProfileFields({ ...form, phone: '123456' }), /WhatsApp/);
  assert.throws(() => validateProfileFields({ ...form, coverDataUrl: 'javascript:alert(1)' }), /Foto/);
});

test('CRUD portofolio lokal termasuk edit entri ilustrasi bawaan', () => {
  reset();
  let data = savePartnerProject(1, provider, source, {
    title: 'Cuci AC perkantoran', description: 'Membersihkan evaporator dan mengecek aliran udara unit dalam.',
    area: 'Coblong', imageDataUrl: '',
  });
  assert.equal(data.projects.length, 2);
  const id = data.projects[0].id;
  assert.match(id, /^project-/);
  data = savePartnerProject(1, provider, source, {
    title: 'Cuci AC kantor kecil', description: 'Pemeriksaan fungsi pendingin, saluran pembuangan, dan filter.',
    area: 'Antapani', imageDataUrl: '',
  }, id);
  assert.equal(data.projects[0].title, 'Cuci AC kantor kecil');
  data = removePartnerProject(1, provider, source, id);
  assert.equal(data.projects.length, 1);
  data = savePartnerProject(1, provider, source, {
    title: 'Perawatan AC rumah', description: 'Pembersihan rutin filter unit dan pemeriksaan aliran udara.',
    area: 'Sukajadi', imageDataUrl: '',
  }, 'demo-0');
  assert.equal(data.projects[0].isDemo, false);
  assert.equal(data.projects[0].id, 'demo-0');
});

test('batas sembilan portofolio dan entri tidak ditemukan', () => {
  reset();
  assert.equal(MAX_PROJECTS, 9);
  for (let i = 0; i < MAX_PROJECTS - 1; i++) {
    savePartnerProject(1, provider, source, {
      title: `Contoh pekerjaan ${i}`, description: 'Pembersihan dan perawatan rutin unit pendingin ruangan.',
      area: 'Coblong', imageDataUrl: '',
    });
  }
  assert.throws(() => savePartnerProject(1, provider, source, {
    title: 'Melebihi batas', description: 'Pembersihan dan perawatan rutin unit pendingin ruangan.',
    area: 'Coblong', imageDataUrl: '',
  }), /Maksimal/);
  assert.throws(() => removePartnerProject(1, provider, source, 'project-hilang'), /tidak ditemukan/);
});

test('kegagalan quota tidak boleh diklaim berhasil dan data lama tidak berubah', () => {
  reset();
  const old = localStorage.setItem;
  localStorage.setItem = () => { throw new Error('Quota exceeded'); };
  assert.throws(() => savePartnerProfile(1, provider, source, form), /Penyimpanan browser penuh/);
  localStorage.setItem = old;
  assert.equal(readPartnerProfile(1, provider, source).name, 'Budi Teknik');
});

test('data localStorage rusak tidak dirender sebagai konten profil', () => {
  reset();
  records.set(PROFILE_KEY, '{rusak');
  assert.equal(hasPartnerProfileOverride(1), false);
  assert.equal(readPartnerProfile(1, provider, source).name, 'Budi Teknik');
  records.set(PROFILE_KEY, JSON.stringify({ 1: { ...seedPartnerProfile(provider, source), avatarDataUrl: 'data:text/html;base64,SGk=' } }));
  assert.equal(hasPartnerProfileOverride(1), false);
});
