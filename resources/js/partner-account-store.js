/**
 * Akun MITRA DEMO JasaWarga. Hanya preferensi & kontak fiktif di localStorage.
 * Bukan autentikasi. Jangan menyimpan password atau data identitas sungguhan.
 * Ketersediaan pesanan tetap menggunakan partner-store.js (key lama).
 */
export const PARTNER_ACCOUNT_KEY = 'jasawarga:prototype-partner-account:v1';
export const PARTNER_ACCOUNT_EVENT = 'jasawarga:partner-account-updated';
export const PARTNER_DEMO_SESSION_KEY = 'jasawarga:prototype-partner-demo-session:v1';

const preferenceSchema = Object.freeze({
  notifications: ['newOrder', 'orderUpdate', 'newReview'],
  privacy: ['showBusinessPhone', 'showCoverage'],
  operations: ['showRevenue', 'scheduleReminder'],
});

function partnerId(id) {
  const parsed = Number(id);
  if (!Number.isSafeInteger(parsed) || parsed <= 0 || String(parsed) !== String(id)) {
    throw new Error('ID Mitra tidak valid.');
  }
  return String(parsed);
}

function safeObject(value) {
  return value && typeof value === 'object' && !Array.isArray(value);
}

function getMap() {
  try {
    const parsed = JSON.parse(localStorage.getItem(PARTNER_ACCOUNT_KEY) ?? '{}');
    return safeObject(parsed) ? parsed : {};
  } catch (_) {
    return {};
  }
}

export function defaultPartnerAccount(providerId) {
  partnerId(providerId);
  return {
    ownerName: 'Pengelola Mitra (Demo)',
    email: 'mitra.demo@jasawarga.test',
    phone: '',
    notifications: { newOrder: true, orderUpdate: true, newReview: true },
    privacy: { showBusinessPhone: false, showCoverage: true },
    operations: { showRevenue: true, scheduleReminder: false },
    updatedAt: null,
  };
}

function validIdentity(source) {
  const ownerName = String(source?.ownerName ?? '').trim().replace(/\s+/g, ' ');
  const email = String(source?.email ?? '').trim().toLowerCase();
  const phone = String(source?.phone ?? '').trim().replace(/[\s-]/g, '');
  if (ownerName.length < 3 || ownerName.length > 80) {
    throw new Error('Nama pemilik harus 3–80 karakter.');
  }
  if (email.length > 120 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    throw new Error('Alamat email tidak valid.');
  }
  if (phone && !/^(?:\+62|62|0)8\d{8,12}$/.test(phone)) {
    throw new Error('Nomor WhatsApp harus menggunakan format Indonesia (08 atau +628).');
  }
  return { ownerName, email, phone };
}

function readValidated(raw, providerId) {
  const fallback = defaultPartnerAccount(providerId);
  if (!safeObject(raw)) return fallback;
  try {
    const identity = validIdentity(raw);
    const candidate = { ...fallback, ...identity };
    for (const [group, keys] of Object.entries(preferenceSchema)) {
      if (!safeObject(raw[group]) || !keys.every(key => typeof raw[group][key] === 'boolean')) {
        return fallback;
      }
      candidate[group] = Object.fromEntries(keys.map(key => [key, raw[group][key]]));
    }
    candidate.updatedAt = Number.isFinite(raw.updatedAt) ? raw.updatedAt : null;
    return candidate;
  } catch (_) {
    return fallback;
  }
}

export function readPartnerAccount(providerId) {
  const id = partnerId(providerId);
  return readValidated(getMap()[id], id);
}

function commit(providerId, value) {
  const id = partnerId(providerId);
  const clean = readValidated(value, id);
  const next = { ...getMap(), [id]: clean };
  // Jika penyimpanan penuh, biarkan error dilempar. Jangan klaim berhasil.
  try {
    localStorage.setItem(PARTNER_ACCOUNT_KEY, JSON.stringify(next));
  } catch (_) {
    throw new Error('Penyimpanan browser penuh atau tidak diizinkan. Perubahan belum tersimpan.');
  }
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent(PARTNER_ACCOUNT_EVENT, { detail: { providerId: Number(id) } }));
  }
  return clean;
}

export function savePartnerIdentity(providerId, fields) {
  const cleaned = validIdentity(fields);
  const current = readPartnerAccount(providerId);
  return commit(providerId, { ...current, ...cleaned, updatedAt: Date.now() });
}

export function savePartnerPreferences(providerId, changes) {
  if (!safeObject(changes)) throw new Error('Preferensi tidak valid.');
  const current = readPartnerAccount(providerId);
  const result = { ...current, updatedAt: Date.now() };
  for (const [group, keys] of Object.entries(preferenceSchema)) {
    if (!(group in changes)) continue;
    if (!safeObject(changes[group])) throw new Error(`Preferensi ${group} tidak valid.`);
    result[group] = { ...current[group] };
    for (const [key, value] of Object.entries(changes[group])) {
      if (!keys.includes(key) || typeof value !== 'boolean') {
        throw new Error(`Pilihan ${group}.${key} tidak valid.`);
      }
      result[group][key] = value;
    }
  }
  return commit(providerId, result);
}

/** Password diperiksa di memori fungsi ini saja, tidak disimpan dan tidak diverifikasi server. */
export function validateDemoPassword(nextPassword, confirmation) {
  if (typeof nextPassword !== 'string' || nextPassword.length < 8 || nextPassword.length > 128) {
    throw new Error('Password simulasi harus 8–128 karakter.');
  }
  if (nextPassword !== confirmation) throw new Error('Konfirmasi password tidak sama.');
  return true;
}

/** Hanya menandai sedang melihat mode Mitra; BUKAN login/izin akses. */
export function enterPartnerDemo() {
  sessionStorage.setItem(PARTNER_DEMO_SESSION_KEY, 'active');
}

export function exitPartnerDemo() {
  sessionStorage.removeItem(PARTNER_DEMO_SESSION_KEY);
}

export function isPartnerDemoActive() {
  return sessionStorage.getItem(PARTNER_DEMO_SESSION_KEY) === 'active';
}
