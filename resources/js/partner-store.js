/**
 * Utilitas dashboard Mitra Jasa frontend-only.
 * Data pesanan dibaca dari orders-store.js bersama halaman Warga.
 * Tidak ada otorisasi Mitra, transaksi, atau sinkronisasi server.
 */
import {
  ORDER_STEPS,
  ORDER_LABELS,
  createOrderFromBooking,
  changeOrderStatus,
} from './orders-store.js';

export const PARTNER_SETTINGS_KEY = 'jasawarga:prototype-partner-settings:v1';
export const PARTNER_SETTINGS_EVENT = 'jasawarga:partner-settings-updated';
const ACTIVE = new Set(['accepted', 'enroute', 'working']);
const ID_PATTERN = /^[1-9]\d*$/;

function validId(id) {
  return Number.isSafeInteger(Number(id)) && ID_PATTERN.test(String(id));
}

function readSettings() {
  try {
    const value = JSON.parse(localStorage.getItem(PARTNER_SETTINGS_KEY) || '{}');
    return value && typeof value === 'object' && !Array.isArray(value) ? value : {};
  } catch (_) {
    return {};
  }
}

export function getPartnerAvailability(providerId) {
  if (!validId(providerId)) throw new Error('ID mitra tidak valid.');
  const found = readSettings()[String(providerId)];
  return {
    available: typeof found?.available === 'boolean' ? found.available : true,
    updatedAt: Number.isFinite(found?.updatedAt) ? found.updatedAt : null,
  };
}

export function setPartnerAvailability(providerId, available) {
  if (!validId(providerId) || typeof available !== 'boolean') {
    throw new Error('Perubahan ketersediaan tidak valid.');
  }
  const updated = { available, updatedAt: Date.now() };
  const next = { ...readSettings(), [String(providerId)]: updated };
  localStorage.setItem(PARTNER_SETTINGS_KEY, JSON.stringify(next));
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent(PARTNER_SETTINGS_EVENT));
  }
  return updated;
}

function localDateKey(timestamp) {
  const date = new Date(timestamp);
  return [
    date.getFullYear(),
    String(date.getMonth() + 1).padStart(2, '0'),
    String(date.getDate()).padStart(2, '0'),
  ].join('-');
}

function completionTimestamp(order) {
  const completed = order.history?.find(entry =>
    entry?.status === 'completed' && Number.isFinite(entry.at)
  );
  return completed?.at ?? order.createdAt;
}

/** Perhitungan hanya untuk mitra demo yang sedang dibuka; tidak menjumlah mitra lain. */
export function summarizePartnerOrders(orders, providerId, now = Date.now()) {
  if (!validId(providerId)) throw new Error('ID mitra tidak valid.');
  const ownOrders = (Array.isArray(orders) ? orders : [])
    .filter(order => Number(order?.providerId) === Number(providerId))
    .sort((a, b) => b.createdAt - a.createdAt);

  const completed = ownOrders.filter(order => order.status === 'completed');
  const days = Array.from({ length: 7 }, (_, index) => {
    const date = new Date(now);
    date.setHours(12, 0, 0, 0);
    date.setDate(date.getDate() - (6 - index));
    return { key: localDateKey(date.getTime()), at: date.getTime(), amount: 0, count: 0 };
  });
  const dayMap = new Map(days.map(day => [day.key, day]));

  completed.forEach(order => {
    const day = dayMap.get(localDateKey(completionTimestamp(order)));
    if (day) {
      day.amount += Number(order.price) || 0;
      day.count += 1;
    }
  });

  const activities = ownOrders.flatMap(order =>
    (Array.isArray(order.history) ? order.history : [])
      .filter(entry => Object.hasOwn(ORDER_LABELS, entry?.status) && Number.isFinite(entry?.at))
      .map(entry => ({
        reference: order.reference,
        service: order.service,
        status: entry.status,
        at: entry.at,
      }))
  ).sort((a, b) => b.at - a.at).slice(0, 6);

  return {
    orders: ownOrders,
    total: ownOrders.length,
    pending: ownOrders.filter(order => order.status === 'pending').length,
    active: ownOrders.filter(order => ACTIVE.has(order.status)).length,
    completed: completed.length,
    cancelled: ownOrders.filter(order => order.status === 'cancelled').length,
    estimatedRevenue: completed.reduce((sum, order) => sum + (Number(order.price) || 0), 0),
    weekRevenue: days.reduce((sum, day) => sum + day.amount, 0),
    days,
    activities,
  };
}

/** Tambah pesanan FIKTIF mitra ini ke store Warga yang sama (tidak otomatis). */
export function seedPartnerDemoOrders(provider) {
  if (!validId(provider?.id) || !provider?.name || !provider?.service ||
    !Number.isFinite(provider?.price) || provider.price < 0) {
    throw new Error('Informasi mitra demo tidak lengkap.');
  }
  const statuses = ['pending', 'accepted', 'working', 'completed'];
  const suffix = () => Array.from(crypto.getRandomValues(new Uint8Array(3)))
    .map(byte => byte.toString(16).padStart(2, '0')).join('').toUpperCase();
  const now = Date.now();
  const dateKey = localDateKey(now).replaceAll('-', '');
  const result = [];

  for (let i = 0; i < statuses.length; i += 1) {
    const date = new Date(now);
    date.setDate(date.getDate() + (i % 3) + 1);
    const order = createOrderFromBooking({
      reference: `JW-DEMO-${dateKey}-${suffix()}`,
      providerId: Number(provider.id),
      provider: String(provider.name),
      service: String(provider.service),
      serviceIndex: 0,
      price: Number(provider.price),
      date: localDateKey(date.getTime()),
      time: '11:00',
      area: 'Coblong',
      address: 'Alamat contoh (bukan alamat pelanggan asli)',
      description: `Simulasi pekerjaan mitra — data contoh ${i + 1}`,
      createdAt: now - i * 60000,
    });
    const target = statuses[i];
    const stageIndex = ORDER_STEPS.indexOf(target);
    for (let step = 1; step <= stageIndex; step += 1) {
      changeOrderStatus(order.reference, ORDER_STEPS[step]);
    }
    result.push(order.reference);
  }
  return result;
}

export { ORDER_LABELS };
