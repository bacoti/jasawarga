/**
 * JasaWarga • prototype jadwal Mitra (localStorage, bukan server).
 * Slot adalah interval 60 menit untuk waktu kedatangan. Pesanan pending/aktif
 * mengunci slot itu; ini BUKAN kalkulasi durasi pekerjaan riil.
 */
import { getPartnerAvailability } from './partner-store.js';

export const SCHEDULE_KEY = 'jasawarga:prototype-partner-schedules:v1';
export const SCHEDULE_EVENT = 'jasawarga:partner-schedule-updated';
export const SLOT_MINUTES = 60;
export const RESERVED_STATUSES = new Set(['pending', 'accepted', 'enroute', 'working']);
const MAX_HOLIDAYS = 80;
const MAX_SLOTS = 16;
const datePattern = /^\d{4}-(0[1-9]|1[0-2])-([0-2]\d|3[01])$/;
const timePattern = /^([01]\d|2[0-3]):([0-5]\d)$/;

export function makassarToday(now = new Date()) {
  const parts = new Intl.DateTimeFormat('en-GB', {
    timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit',
  }).formatToParts(now);
  const part = type => parts.find(item => item.type === type)?.value;
  return `${part('year')}-${part('month')}-${part('day')}`;
}

export function addDays(iso, days) {
  const date = new Date(`${iso}T00:00:00.000Z`);
  date.setUTCDate(date.getUTCDate() + days);
  return date.toISOString().slice(0, 10);
}

export function validIsoDate(raw) {
  if (typeof raw !== 'string' || !datePattern.test(raw)) return false;
  const date = new Date(`${raw}T00:00:00.000Z`);
  return !Number.isNaN(date.getTime()) && date.toISOString().slice(0, 10) === raw;
}

export function weekday(iso) {
  return new Date(`${iso}T00:00:00.000Z`).getUTCDay();
}

export function timeMinutes(time) {
  if (typeof time !== 'string' || !timePattern.test(time)) return NaN;
  const [h, m] = time.split(':').map(Number);
  return h * 60 + m;
}

export function defaultSchedule({ allowSunday = false } = {}) {
  return {
    version: 1,
    workDays: allowSunday ? [0, 1, 2, 3, 4, 5, 6] : [1, 2, 3, 4, 5, 6],
    open: '08:00', close: '17:00',
    breakStart: '12:00', breakEnd: '13:00',
    slots: ['09:00', '11:00', '13:00', '15:00'],
    holidays: [],
  };
}

/** Validasi ketat juga saat membaca storage yang mungkin rusak atau dimodifikasi. */
export function validateSchedule(input) {
  if (!input || typeof input !== 'object' || Array.isArray(input)) {
    throw new Error('Data jadwal tidak valid.');
  }
  if (!Array.isArray(input.workDays) || !input.workDays.length ||
      input.workDays.length > 7 ||
      new Set(input.workDays).size !== input.workDays.length ||
      !input.workDays.every(day => Number.isInteger(day) && day >= 0 && day <= 6)) {
    throw new Error('Pilih setidaknya satu hari kerja yang valid.');
  }
  const open = timeMinutes(input.open);
  const close = timeMinutes(input.close);
  if (!Number.isFinite(open) || !Number.isFinite(close) || close - open < 120 ||
      open < 5 * 60 || close > 23 * 60) {
    throw new Error('Jam kerja harus antara 05.00–23.00 dengan durasi minimal 2 jam.');
  }
  const useBreak = Boolean(input.breakStart || input.breakEnd);
  const breakStart = useBreak ? timeMinutes(input.breakStart) : null;
  const breakEnd = useBreak ? timeMinutes(input.breakEnd) : null;
  if (useBreak && (!Number.isFinite(breakStart) || !Number.isFinite(breakEnd) ||
      breakStart < open || breakEnd > close || breakEnd <= breakStart)) {
    throw new Error('Waktu istirahat harus di dalam jam kerja dan berurutan.');
  }
  if (!Array.isArray(input.slots) || input.slots.length < 1 || input.slots.length > MAX_SLOTS) {
    throw new Error(`Pilih 1–${MAX_SLOTS} slot kedatangan.`);
  }
  const slots = [...input.slots].sort((a, b) => timeMinutes(a) - timeMinutes(b));
  if (new Set(slots).size !== slots.length) throw new Error('Slot kedatangan tidak boleh duplikat.');
  for (let i = 0; i < slots.length; i += 1) {
    const start = timeMinutes(slots[i]);
    if (!Number.isFinite(start) || start < open || start + SLOT_MINUTES > close) {
      throw new Error(`Slot ${slots[i]} harus berada dalam jam kerja (${SLOT_MINUTES} menit).`);
    }
    if (useBreak && start < breakEnd && start + SLOT_MINUTES > breakStart) {
      throw new Error(`Slot ${slots[i]} berbenturan dengan jam istirahat.`);
    }
    if (i && start - timeMinutes(slots[i - 1]) < SLOT_MINUTES) {
      throw new Error('Jarak antar slot minimal 60 menit.');
    }
  }
  if (!Array.isArray(input.holidays) || input.holidays.length > MAX_HOLIDAYS) {
    throw new Error('Tanggal libur terlalu banyak.');
  }
  const holidayDates = new Set();
  const holidays = input.holidays.map(holiday => {
    const date = holiday?.date;
    const label = holiday?.label;
    if (!validIsoDate(date) || typeof label !== 'string' || label.length > 100 || holidayDates.has(date)) {
      throw new Error('Tanggal atau keterangan libur tidak valid / duplikat.');
    }
    holidayDates.add(date);
    return { date, label: label.trim() || 'Libur mitra' };
  }).sort((a, b) => a.date.localeCompare(b.date));
  return {
    version: 1,
    workDays: [...input.workDays].sort((a, b) => a - b),
    open: input.open, close: input.close,
    breakStart: useBreak ? input.breakStart : '',
    breakEnd: useBreak ? input.breakEnd : '',
    slots,
    holidays,
  };
}

function scheduleRecord() {
  try {
    const parsed = JSON.parse(localStorage.getItem(SCHEDULE_KEY) || '{}');
    return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
  } catch (_) { return {}; }
}

export function getSchedule(providerId, options = {}) {
  if (!Number.isSafeInteger(Number(providerId)) || Number(providerId) <= 0) {
    throw new Error('ID mitra tidak valid.');
  }
  const record = scheduleRecord()[String(providerId)];
  if (record) {
    try { return validateSchedule(record); } catch (_) { /* fallback */ }
  }
  return defaultSchedule(options);
}

export function bookedOrders(orders, providerId, date) {
  return (Array.isArray(orders) ? orders : []).filter(order =>
    Number(order?.providerId) === Number(providerId) &&
    RESERVED_STATUSES.has(order?.status) &&
    (!date || order.date === date) &&
    validIsoDate(order.date) && Number.isFinite(timeMinutes(order.time))
  );
}

export function dateClosedReason(date, schedule, { today = makassarToday(), horizonDays = 30 } = {}) {
  if (!validIsoDate(date)) return 'Pilih tanggal yang valid.';
  if (date <= today || date > addDays(today, horizonDays)) {
    return `Pemesanan tersedia mulai besok hingga ${horizonDays} hari ke depan.`;
  }
  if (!schedule.workDays.includes(weekday(date))) return 'Hari ini bukan hari operasional mitra.';
  const holiday = schedule.holidays.find(item => item.date === date);
  if (holiday) return `Mitra libur: ${holiday.label}.`;
  return '';
}

export function slotCheck({ providerId, date, time, schedule, orders = [], available = true,
  today = makassarToday(), horizonDays = 30, excludeReference = null } = {}) {
  if (!available) return { ok: false, reason: 'Mitra sedang tidak menerima pesanan baru.' };
  const reason = dateClosedReason(date, schedule, { today, horizonDays });
  if (reason) return { ok: false, reason };
  if (!schedule.slots.includes(time)) return { ok: false, reason: 'Jam ini tidak termasuk slot yang dibuka mitra.' };
  const start = timeMinutes(time);
  const busy = bookedOrders(orders, providerId, date).find(order => {
    if (excludeReference && order.reference === excludeReference) return false;
    const bookedStart = timeMinutes(order.time);
    return start < bookedStart + SLOT_MINUTES && bookedStart < start + SLOT_MINUTES;
  });
  if (busy) return { ok: false, reason: 'Slot telah direservasi pesanan lain. Pilih jam berbeda.' };
  return { ok: true, reason: '' };
}

export function availableSlots({ providerId, date, schedule, orders = [], available = true,
  today = makassarToday(), horizonDays = 30 } = {}) {
  return schedule.slots.map(time => ({
    time, ...slotCheck({ providerId, date, time, schedule, orders, available, today, horizonDays }),
  }));
}

/** Jangan menonaktifkan slot / hari dengan pesanan yang masih mengunci slot. */
export function conflictingReservations(providerId, proposedSchedule, orders = []) {
  const current = bookedOrders(orders, providerId);
  return current.filter(order => {
    const result = slotCheck({ providerId, date: order.date, time: order.time,
      schedule: proposedSchedule, orders: [], available: true,
      today: addDays(order.date, -1), horizonDays: 1 });
    return !result.ok;
  });
}

export function saveSchedule(providerId, proposed, orders = []) {
  if (!Number.isSafeInteger(Number(providerId)) || Number(providerId) <= 0) {
    throw new Error('ID mitra tidak valid.');
  }
  const normalized = validateSchedule(proposed);
  const blocking = conflictingReservations(providerId, normalized, orders);
  if (blocking.length) {
    throw new Error(`Perubahan bentrok dengan ${blocking.length} pesanan aktif/menunggu (contoh: ${blocking[0].date} ${blocking[0].time}). Selesaikan atau batalkan pesanan tersebut lebih dahulu.`);
  }
  const next = { ...scheduleRecord(), [String(providerId)]: normalized };
  localStorage.setItem(SCHEDULE_KEY, JSON.stringify(next));
  if (typeof window !== 'undefined') window.dispatchEvent(new CustomEvent(SCHEDULE_EVENT, { detail: { providerId: Number(providerId) } }));
  return normalized;
}

/** Selalu verifikasi dari penyimpanan terbaru tepat sebelum konfirmasi demo. */
export function currentSlotCheck(providerId, date, time, orders, options = {}) {
  return slotCheck({ providerId, date, time, orders,
    schedule: getSchedule(providerId, options),
    available: getPartnerAvailability(providerId).available });
}
