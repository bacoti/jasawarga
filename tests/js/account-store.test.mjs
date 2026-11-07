import test, { beforeEach } from 'node:test';
import assert from 'node:assert/strict';
import {
  ACCOUNT_KEY, SESSION_KEY, ensureDemoAccount, readAccounts, currentAccount,
  demoSignIn, demoSignOut, registerAccount, updateProfile, saveAddress,
  setDefaultAddress, removeAddress, updatePreferences, safeNext, deleteCurrentAccount,
} from '../../resources/js/account-store.js';

const storageMock = () => {
  const map = new Map();
  return {
    getItem: k => map.has(k) ? map.get(k) : null,
    setItem: (k, v) => map.set(k,String(v)),
    removeItem: k => map.delete(k),
    clear: () => map.clear(),
  };
};
globalThis.localStorage = storageMock();
globalThis.sessionStorage = storageMock();
globalThis.window = { dispatchEvent: () => {} };
globalThis.CustomEvent = class { constructor(name){this.type=name;} };
beforeEach(() => {localStorage.clear();sessionStorage.clear();});

test('demo account seeded and session is separate from profile records',()=>{
  ensureDemoAccount();
  assert.equal(readAccounts().length,1);
  assert.equal(currentAccount(),null);
  demoSignIn('demo@jasawarga.test');
  assert.equal(currentAccount().email,'demo@jasawarga.test');
  assert.equal(JSON.parse(sessionStorage.getItem(SESSION_KEY)).id,'jw-warga-demo');
  demoSignOut();
  assert.equal(currentAccount(),null);
  assert.equal(readAccounts().length,1);
});

test('register, edit, and preferences without storing a password',()=>{
  ensureDemoAccount();
  const account=registerAccount({name:'Warga Contoh',email:' WARGA@contoh.test ',phone:'081200000001', password:'not-persisted-secret'});
  assert.equal(currentAccount().id,account.id);
  assert.equal(account.email,'warga@contoh.test');
  const edited=updateProfile({name:'Warga Dua',phone:'081200000002'});
  assert.equal(edited.name,'Warga Dua');
  updatePreferences({notifications:false,updates:true});
  assert.equal(currentAccount().preferences.notifications,false);
  assert.equal(currentAccount().preferences.updates,true);
  assert.ok(!localStorage.getItem(ACCOUNT_KEY).includes('not-persisted-secret'));
  assert.ok(!localStorage.getItem(ACCOUNT_KEY).includes('"password"'));
  assert.equal(sessionStorage.getItem(SESSION_KEY).includes('password'),false);
});

test('address CRUD and default address selection are account-scoped',()=>{
  ensureDemoAccount();
  registerAccount({name:'Warga Contoh',email:'alamat@contoh.test',phone:'081200000001'});
  const a={label:'Rumah',recipient:'Warga Contoh',phone:'081200000001',area:'Panakkukang',street:'Jl. Simulasi No. 18',landmark:'Dekat taman'};
  saveAddress(a);
  assert.equal(currentAccount().addresses.length,1);
  assert.equal(currentAccount().addresses[0].isDefault,true);
  saveAddress({...a,label:'Kantor',street:'Jl. Kantor No. 33'});
  assert.equal(currentAccount().addresses.length,2);
  const workId=currentAccount().addresses[1].id;
  setDefaultAddress(workId);
  assert.equal(currentAccount().addresses.find(x=>x.isDefault)?.id,workId);
  saveAddress({...a,label:'Lokasi baru',isDefault:false},workId);
  assert.equal(currentAccount().addresses.find(x=>x.id===workId).label,'Lokasi baru');
  removeAddress(workId);
  assert.equal(currentAccount().addresses.length,1);
  assert.equal(currentAccount().addresses[0].isDefault,true);
});

test('validation rejects bad account/address and duplicate email',()=>{
  ensureDemoAccount();
  assert.throws(()=>registerAccount({name:'C',email:'test@contoh.test'}),/Nama/);
  assert.throws(()=>registerAccount({name:'Cukup Nama',email:'demo@jasawarga.test'}),/sudah digunakan/);
  demoSignIn('demo@jasawarga.test');
  assert.throws(()=>saveAddress({label:'Rumah',recipient:'Orang',phone:'123',area:'Panakkukang',street:'Jalan No 1'}),/Nomor WA/);
});

test('next redirect never allows cross-origin navigation',()=>{
  assert.equal(safeNext('/pesanan-saya?status=selesai'),'/pesanan-saya?status=selesai');
  for(const unsafe of ['https://evil.test','//evil.test','/\\evil.test','/ok\r\nLocation:evil',''])assert.equal(safeNext(unsafe),'/akun');
});

test('deleting a selected demo profile does not erase the other local accounts',()=>{
  ensureDemoAccount();
  registerAccount({name:'Contoh A',email:'contoha@contoh.test'});
  deleteCurrentAccount();
  assert.equal(currentAccount(),null);
  assert.equal(readAccounts().length,1);
  assert.equal(readAccounts()[0].email,'demo@jasawarga.test');
});
