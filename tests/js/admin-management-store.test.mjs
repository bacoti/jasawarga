import test from 'node:test';
import assert from 'node:assert/strict';
import {
  ADMIN_CITIZEN_KEY, ADMIN_PARTNER_KEY, citizenStatus, partnerVerification,
  readCitizenModeration, readPartnerModeration, setCitizenStatus, setPartnerVerification,
  summaryCitizens, summaryPartners, citizenAuditEntries, partnerAuditEntries, linkedCitizenOrders,
} from '../../resources/js/admin-management-store.js';

const memory = new Map();
globalThis.localStorage = {
  getItem: key => memory.get(key) ?? null,
  setItem: (key, value) => memory.set(key, value),
  removeItem: key => memory.delete(key),
};
const accounts = [
  {id:'jw-warga-demo', name:'Warga Demo', createdAt:Date.now() - 1000},
  {id:'jw-warga-abcd1234', name:'Warga B', createdAt:Date.now() - 50 * 86400000},
];
const providers = [
  {id:1,name:'Budi Teknik',verified:true},
  {id:8,name:'AC Sejuk Bersama',verified:false},
];
const reset = () => memory.clear();
test('status default Warga aktif dan penghitung tidak menebak pemilik pesanan lama', () => {
  reset();
  assert.equal(citizenStatus(accounts[0].id), 'active');
  const orders = [{reference:'A',accountId:'jw-warga-demo'},{reference:'B',customerName:'Warga Demo'},{reference:'C',accountId:'jw-warga-tidak-ada'}];
  assert.equal(linkedCitizenOrders(accounts[0].id,orders).length,1);
  assert.deepEqual(summaryCitizens(accounts,orders).linkedOrders,1);
  assert.equal(summaryCitizens(accounts,orders).unlinkedOrders,2);
  assert.equal(summaryCitizens(accounts,orders).new30,1);
});
test('ubah status Warga membutuhkan ID valid, alasan, dan transisi nyata', () => {
  reset();
  assert.throws(()=>setCitizenStatus(accounts[0].id,'inactive','kurang',accounts),/10–220/);
  assert.throws(()=>setCitizenStatus('jw-warga-fiktif','inactive','Sepuluh karakter alasan',accounts),/tidak ditemukan/);
  setCitizenStatus(accounts[0].id,'inactive','Tindakan administrasi demo saja',accounts,12345);
  assert.equal(citizenStatus(accounts[0].id),'inactive');
  assert.equal(summaryCitizens(accounts,[]).inactive,1);
  assert.equal(citizenAuditEntries()[0].at,12345);
  assert.throws(()=>setCitizenStatus(accounts[0].id,'inactive','Tidak boleh status sama',accounts),/tidak berubah/);
  setCitizenStatus(accounts[0].id,'active','Mengembalikan status semula',accounts,12346);
  assert.equal(citizenAuditEntries().length,2);
  assert.equal(citizenStatus(accounts[0].id),'active');
});
test('verifikasi Mitra mengikuti katalog seed hingga ada override lokal', () => {
  reset();
  assert.equal(partnerVerification(providers[0]), 'verified');
  assert.equal(partnerVerification(providers[1]), 'pending');
  assert.deepEqual(summaryPartners(providers),{total:2,verified:1,pending:1,rejected:0});
});
test('setujui dan tolak Mitra demo mengubah status dan mencatat alasan', () => {
  reset();
  assert.throws(()=>setPartnerVerification(8,'verified','singkat',providers),/10–220/);
  assert.throws(()=>setPartnerVerification(99,'verified','Contoh tindakan yang lengkap',providers),/tidak ditemukan/);
  setPartnerVerification(8,'verified','Mitra contoh ditandai disetujui',providers,20000);
  assert.equal(partnerVerification(providers[1]),'verified');
  assert.equal(summaryPartners(providers).verified,2);
  setPartnerVerification(8,'rejected','Skenario penolakan data demo',providers,20001);
  assert.equal(partnerVerification(providers[1]),'rejected');
  assert.equal(summaryPartners(providers).rejected,1);
  assert.equal(partnerAuditEntries().length,2);
  assert.equal(partnerAuditEntries()[0].previous,'verified');
  assert.throws(()=>setPartnerVerification(8,'rejected','Statusnya sudah ditolak lagi',providers),/tidak berubah/);
});
test('payload penyimpanan rusak diabaikan aman; status diperbaiki setelah edit', () => {
  reset();memory.set(ADMIN_PARTNER_KEY,'{invalid');memory.set(ADMIN_CITIZEN_KEY,'not-json');
  assert.deepEqual(readCitizenModeration(),{records:{},log:[]});
  assert.deepEqual(readPartnerModeration(),{records:{},log:[]});
  assert.equal(partnerVerification(providers[1]),'pending');
  assert.equal(citizenStatus(accounts[0].id),'active');
  setPartnerVerification(8,'rejected','Simulasi dengan data diperbaiki',providers);
  assert.equal(partnerVerification(providers[1]),'rejected');
});
