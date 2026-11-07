import test from 'node:test';
import assert from 'node:assert/strict';
import {
  defaultSchedule, validateSchedule, getSchedule, saveSchedule, slotCheck,
  availableSlots, conflictingReservations, dateClosedReason, makassarToday,
  currentSlotCheck, SCHEDULE_KEY,
} from '../../resources/js/schedule-store.js';
import { setPartnerAvailability, PARTNER_SETTINGS_KEY } from '../../resources/js/partner-store.js';
import { changeOrderStatus, createOrderFromBooking, readOrders, clearOrders } from '../../resources/js/orders-store.js';

const data = new Map();
globalThis.localStorage = {
  getItem(key) { return data.get(key) ?? null; },
  setItem(key, value) { data.set(key, String(value)); },
  removeItem(key) { data.delete(key); },
};
globalThis.CustomEvent = class { constructor(type) { this.type = type; } };
globalThis.window = { dispatchEvent() {} };
const today = '2026-11-07';
const tomorrow = '2026-11-09'; // Monday
const booking = ({ reference = 'JW-DEMO-20261107-A0B1C2', providerId = 1, date = tomorrow, time = '09:00', status = 'pending' } = {}) =>
  ({ reference, providerId, date, time, status });
const reset = () => data.clear();

test('default jadwal mempertahankan 09/11/13/15, Senin–Sabtu dan istirahat', () => {
  reset();
  assert.deepEqual(getSchedule(1).slots, ['09:00', '11:00', '13:00', '15:00']);
  assert.equal(getSchedule(1).workDays.includes(0), false);
  assert.equal(getSchedule(2, { allowSunday: true }).workDays.includes(0), true);
  assert.equal(dateClosedReason('2026-11-08', getSchedule(1), { today }), 'Hari ini bukan hari operasional mitra.');
});

test('jam kerja, istirahat, slot bertumpuk, tanggal invalid ditolak', () => {
  const base = defaultSchedule();
  assert.throws(() => validateSchedule({ ...base, workDays: [] }), /hari kerja/);
  assert.throws(() => validateSchedule({ ...base, open: '18:00', close: '17:00' }), /Jam kerja/);
  assert.throws(() => validateSchedule({ ...base, slots: ['09:00','09:30'] }), /Jarak antar slot/);
  assert.throws(() => validateSchedule({ ...base, slots: ['12:00'] }), /istirahat/);
  assert.throws(() => validateSchedule({ ...base, slots: ['16:30'] }), /jam kerja/);
  assert.throws(() => validateSchedule({ ...base, holidays: [{date:'2026-02-30',label:'Oops'}] }), /tidak valid/);
  assert.equal(validateSchedule(base).slots.length, 4);
});

test('slot penuh oleh pesanan pending/accepted/working, bebas setelah cancelled/rejected/completed', () => {
  const schedule = defaultSchedule();
  for (const status of ['pending', 'accepted', 'enroute', 'working']) {
    assert.equal(slotCheck({ providerId:1, date:tomorrow, time:'09:00', schedule, orders:[booking({status})], today }).ok, false);
  }
  for (const status of ['completed', 'cancelled', 'rejected']) {
    assert.equal(slotCheck({ providerId:1, date:tomorrow, time:'09:00', schedule, orders:[booking({status})], today }).ok, true);
  }
  assert.equal(slotCheck({ providerId:1, date:tomorrow, time:'09:00', schedule, orders:[booking({providerId:2})], today }).ok, true);
  assert.equal(availableSlots({ providerId:1, date:tomorrow, schedule, orders:[booking()], today }).filter(s=>s.ok).length, 3);
});

test('slot diblokir jika interval 60 menit tumpang tindih, termasuk pesanan legacy', () => {
  const schedule = defaultSchedule();
  assert.equal(slotCheck({ providerId:1,date:tomorrow,time:'09:00',schedule,orders:[booking({time:'09:30'})],today }).ok, false);
  assert.equal(slotCheck({ providerId:1,date:tomorrow,time:'11:00',schedule,orders:[booking({time:'09:30'})],today }).ok, true);
});

test('hari libur dan tidak tersedia mengunci semua slot baru', () => {
  const schedule = validateSchedule({ ...defaultSchedule(), holidays:[{date:tomorrow,label:'Cuti keluarga'}] });
  assert.match(dateClosedReason(tomorrow, schedule, {today}), /Cuti keluarga/);
  assert.equal(slotCheck({providerId:1,date:tomorrow,time:'09:00',schedule,orders:[],today}).ok, false);
  assert.equal(slotCheck({providerId:1,date:tomorrow,time:'09:00',schedule:defaultSchedule(),available:false,today}).ok, false);
});

test('perubahan jadwal ditolak jika menghilangkan slot pesanan aktif', () => {
  reset();
  const old = defaultSchedule();
  saveSchedule(1, old);
  assert.equal(conflictingReservations(1, { ...old, workDays:[2,3,4,5,6] }, [booking()]).length, 1);
  assert.throws(() => saveSchedule(1, { ...old, holidays:[{date:tomorrow,label:'Cuti'}] }, [booking()]), /bentrok/);
  assert.deepEqual(getSchedule(1).holidays, []);
  assert.deepEqual(saveSchedule(2, { ...old, holidays:[{date:tomorrow,label:'Cuti'}] }, [booking()]).holidays, [{date:tomorrow,label:'Cuti'}]);
});

test('status penerimaan dari Dashboard Mitra berlaku pada cek booking', () => {
  reset();
  saveSchedule(1, defaultSchedule());
  assert.equal(currentSlotCheck(1,tomorrow,'11:00',[], {allowSunday:false}).ok, true);
  setPartnerAvailability(1,false);
  assert.equal(currentSlotCheck(1,tomorrow,'11:00',[], {allowSunday:false}).ok, false);
  assert.equal(currentSlotCheck(2,tomorrow,'11:00',[], {allowSunday:false}).ok, true);
});

test('integrasi dengan orders-store: simpan pesanan, slot terkunci, setelah batal terbuka', () => {
  reset();
  saveSchedule(1, defaultSchedule());
  const created = createOrderFromBooking({
    reference:'JW-DEMO-20261107-A0B1C2',providerId:1, provider:'Budi Teknik',
    service:'Cuci AC',price:75000,date:tomorrow,time:'09:00',createdAt:Date.now(),
  });
  assert.equal(created.status,'pending');
  assert.equal(currentSlotCheck(1,tomorrow,'09:00',readOrders()).ok, false);
  changeOrderStatus(created.reference,'cancelled');
  assert.equal(currentSlotCheck(1,tomorrow,'09:00',readOrders()).ok, true);
  clearOrders();
});

test('waktu lokal Makassar tidak salah hari ketika waktu UTC masih hari sebelumnya', () => {
  assert.equal(makassarToday(new Date('2026-11-07T17:30:00Z')), '2026-11-08');
});

test('gagal menulis localStorage tidak mengklaim jadwal disimpan', () => {
  reset();
  const prev = localStorage.setItem;
  localStorage.setItem = () => { throw new Error('Quota exceeded'); };
  assert.throws(() => saveSchedule(1,defaultSchedule()), /Quota exceeded/);
  localStorage.setItem = prev;
  assert.equal(data.has(SCHEDULE_KEY), false);
});
