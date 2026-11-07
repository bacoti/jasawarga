import test from 'node:test';
import assert from 'node:assert/strict';
import {ADMIN_SETTINGS_KEY,ADMIN_DEMO_SESSION_KEY,readAdminSettings,defaultAdminSettings,saveAdminIdentity,saveAdminPreferences,resetAdminPreferences,validateAdminDemoPassword,enterAdminDemo,exitAdminDemo,isAdminDemoActive} from '../../resources/js/admin-settings-store.js';
const storage=new Map(),sessions=new Map();
globalThis.localStorage={getItem:k=>storage.get(k)??null,setItem:(k,v)=>storage.set(k,String(v)),removeItem:k=>storage.delete(k)};
globalThis.sessionStorage={getItem:k=>sessions.get(k)??null,setItem:(k,v)=>sessions.set(k,String(v)),removeItem:k=>sessions.delete(k)};
const reset=()=>{storage.clear();sessions.clear();};
test('data default tidak memuat password, token, atau hak akses',()=>{
 reset();const settings=readAdminSettings();assert.equal(settings.displayName,'Administrator Demo');assert.equal(settings.preferences.defaultPeriod,'30');assert.equal(settings.notifications.newOrder,true);assert.equal(JSON.stringify(settings).includes('password'),false);
});
test('identitas demo tersimpan dan divalidasi',()=>{
 reset();const settings=saveAdminIdentity({displayName:'  Admin  Makassar ',email:' ADMIN@EXAMPLE.TEST '},1234);
 assert.equal(settings.displayName,'Admin Makassar');assert.equal(settings.email,'admin@example.test');assert.equal(readAdminSettings().updatedAt,1234);
 assert.throws(()=>saveAdminIdentity({displayName:'ab',email:'admin@example.test'}),/3–70/);
 assert.throws(()=>saveAdminIdentity({displayName:'Nama Admin',email:'not-email'}),/tidak valid/);
});
test('preferensi mendukung update parsial dan menghindari key tidak dikenal',()=>{
 reset();saveAdminPreferences({preferences:{defaultPeriod:'90',showEstimatedTariff:false},notifications:{newReview:false}},2345);
 const settings=readAdminSettings();assert.equal(settings.preferences.defaultPeriod,'90');assert.equal(settings.preferences.showEstimatedTariff,false);assert.equal(settings.preferences.compactTables,false);assert.equal(settings.notifications.newReview,false);
 assert.throws(()=>saveAdminPreferences({preferences:{__protoPollute:true}}),/tidak dikenal/);
 assert.throws(()=>saveAdminPreferences({preferences:{defaultPeriod:'custom'}}),/tidak tersedia/);
 assert.throws(()=>saveAdminPreferences({notifications:{newOrder:'false'}}),/aktif\/nonaktif/);
});
test('reset preferensi tidak mereset identitas Admin',()=>{
 reset();saveAdminIdentity({displayName:'Admin Contoh',email:'contoh@test.local'});
 saveAdminPreferences({preferences:{compactTables:true}});
 resetAdminPreferences();assert.equal(readAdminSettings().displayName,'Admin Contoh');assert.deepEqual(readAdminSettings().preferences,defaultAdminSettings().preferences);
});
test('storage JSON corrupt atau palsu dipulihkan ke default',()=>{
 reset();storage.set(ADMIN_SETTINGS_KEY,'{invalid');assert.equal(readAdminSettings().displayName,'Administrator Demo');
 storage.set(ADMIN_SETTINGS_KEY,JSON.stringify({version:1,displayName:'Bad',email:'fake',notifications:{},preferences:{}}));assert.equal(readAdminSettings().email,defaultAdminSettings().email);
});
test('simulasi password tidak menyimpan credential apa pun',()=>{
 reset();assert.equal(validateAdminDemoPassword('dummy-pass-1','dummy-pass-1'),true);
 assert.throws(()=>validateAdminDemoPassword('weak','weak'),/8–128/);
 assert.throws(()=>validateAdminDemoPassword('dummy-pass-1','dummy-pass-2'),/tidak cocok/);
 assert.equal(storage.size,0);
});
test('mode Admin demo menggunakan sessionStorage, logout tidak mengubah localStorage',()=>{
 reset();saveAdminIdentity({displayName:'Admin Demo QA',email:'qa@example.test'});
 enterAdminDemo();assert.equal(isAdminDemoActive(),true);assert.equal(sessions.get(ADMIN_DEMO_SESSION_KEY),'active');
 exitAdminDemo();assert.equal(isAdminDemoActive(),false);assert.equal(readAdminSettings().displayName,'Admin Demo QA');
});
