import test from 'node:test';
import assert from 'node:assert/strict';
import { ADMIN_FEEDBACK_KEY, readAdminFeedback, reviewStatus, changeReviewStatus,
  createComplaint, changeComplaintStatus, filterComplaints } from '../../resources/js/admin-feedback-store.js';
const memory = new Map();
globalThis.localStorage = {
  getItem: key => memory.get(key) ?? null,
  setItem: (key,value) => memory.set(key,value),
  removeItem: key => memory.delete(key),
};
const reset = () => memory.clear();
const reviews = [{reference:'JW-DEMO-20261107-123ABC',providerId:1,rating:5}];
const orders = [{reference:'JW-DEMO-20261107-123ABC',status:'completed'}];
const input = {subject:'Pelanggan kecewa',description:'Pekerjaan tiba lewat jadwal yang disepakati.',reference:orders[0].reference,topic:'schedule',priority:'high'};

test('moderasi ulasan default terlihat dan alasan wajib',()=>{
  reset();
  assert.equal(reviewStatus(reviews[0].reference),'visible');
  assert.throws(()=>changeReviewStatus(reviews[0].reference,'hidden','singkat',reviews),/10–300/);
  assert.throws(()=>changeReviewStatus('JW-DEMO-20261107-ABCDEF','hidden','Ada alasan yang valid',reviews),/tidak ditemukan/);
  changeReviewStatus(reviews[0].reference,'hidden','Konten membutuhkan pemeriksaan Admin',reviews,100);
  assert.equal(reviewStatus(reviews[0].reference),'hidden');
  assert.equal(readAdminFeedback().audit.length,1);
  assert.throws(()=>changeReviewStatus(reviews[0].reference,'hidden','Ini alasan kedua yang valid',reviews),/tidak berubah/);
  changeReviewStatus(reviews[0].reference,'visible','Sudah diperiksa dan boleh tampil',reviews,200);
  assert.equal(reviewStatus(reviews[0].reference),'visible');
});
test('pengaduan contoh bisa ditautkan ke pesanan lokal; mencegah referensi palsu',()=>{
  reset();
  assert.throws(()=>createComplaint({...input,reference:'JW-DEMO-20261107-000000'},orders,reviews,Date.now(),'ABC123'),/tidak ditemukan/);
  const result=createComplaint(input,orders,reviews,Date.now(),'AABBCC');
  assert.match(result.id,/^JW-ADU-\d{8}-AABBCC$/);
  assert.equal(readAdminFeedback().cases[0].reference,input.reference);
  assert.equal(result.status,'open');
  assert.equal(filterComplaints([result],{priority:'high'}).length,1);
});
test('alur kasus mencatat alasan dan mencegah lompatan status',()=>{
  reset();
  const result=createComplaint(input,orders,reviews,Date.now(),'C0FFEE');
  assert.throws(()=>changeComplaintStatus(result.id,'resolved','Alasan valid panjang'),/tidak diperbolehkan/);
  assert.throws(()=>changeComplaintStatus(result.id,'investigating','salah'),/10–300/);
  changeComplaintStatus(result.id,'investigating','Petugas menghubungi pihak terkait');
  changeComplaintStatus(result.id,'resolved','Kasus sudah diperiksa melalui simulasi');
  const after=readAdminFeedback();
  assert.equal(after.cases[0].status,'resolved');
  assert.equal(after.cases[0].history.length,3);
  assert.equal(after.audit.length,3);
});
test('pengaduan rusak diabaikan dan status moderasi tidak mengubah review asli',()=>{
  reset();
  memory.set(ADMIN_FEEDBACK_KEY,'invalid-json');
  assert.deepEqual(readAdminFeedback(),{reviews:{},cases:[],audit:[]});
  const inputReview={...reviews[0]};
  changeReviewStatus(reviews[0].reference,'hidden','Demi contoh moderasi tanpa mengubah rating',reviews);
  assert.deepEqual(reviews[0],inputReview);
});
