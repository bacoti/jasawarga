/** Preferensi Admin prototipe. Tidak boleh menyimpan password, hak akses, atau token. */
export const ADMIN_SETTINGS_KEY = 'jasawarga:prototype-admin-settings:v1';
export const ADMIN_SETTINGS_EVENT = 'jasawarga:admin-settings-updated';
export const ADMIN_DEMO_SESSION_KEY = 'jasawarga:prototype-admin-demo-session:v1';
export const REPORT_PERIODS = Object.freeze(['7', '30', '90', 'all', 'custom']);
const safeObject = value => value !== null && typeof value === 'object' && !Array.isArray(value);

export function defaultAdminSettings() {
  return {
    displayName: 'Administrator Demo',
    email: 'admin.demo@jasawarga.test',
    notifications: { newOrder: true, newComplaint: true, newReview: true, verification: true },
    preferences: { defaultPeriod: '30', compactTables: false, showEstimatedTariff: true, reduceMotion: false },
    updatedAt: null,
  };
}

function normalizeIdentity(source) {
  const displayName = String(source?.displayName ?? '').trim().replace(/\s+/g, ' ');
  const email = String(source?.email ?? '').trim().toLowerCase();
  if (displayName.length < 3 || displayName.length > 70) throw new Error('Nama Admin harus 3–70 karakter.');
  if (email.length > 120 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) throw new Error('Email Admin tidak valid.');
  return { displayName, email };
}

function normalizeSettings(raw) {
  const defaults = defaultAdminSettings();
  if (!safeObject(raw) || raw.version !== 1 || !safeObject(raw.notifications) || !safeObject(raw.preferences)) return defaults;
  try {
    const identity = normalizeIdentity(raw);
    const notifications = {};
    for (const key of Object.keys(defaults.notifications)) {
      if (typeof raw.notifications[key] !== 'boolean') return defaults;
      notifications[key] = raw.notifications[key];
    }
    const preferences = {};
    for (const key of ['compactTables', 'showEstimatedTariff', 'reduceMotion']) {
      if (typeof raw.preferences[key] !== 'boolean') return defaults;
      preferences[key] = raw.preferences[key];
    }
    if (!REPORT_PERIODS.includes(raw.preferences.defaultPeriod) || raw.preferences.defaultPeriod === 'custom') return defaults;
    preferences.defaultPeriod = raw.preferences.defaultPeriod;
    return { ...identity, notifications, preferences, updatedAt: Number.isFinite(raw.updatedAt) ? raw.updatedAt : null };
  } catch (_) { return defaults; }
}

export function readAdminSettings() {
  try { return normalizeSettings(JSON.parse(localStorage.getItem(ADMIN_SETTINGS_KEY) ?? 'null')); }
  catch (_) { return defaultAdminSettings(); }
}

function persist(value) {
  const data = { version: 1, ...value };
  try { localStorage.setItem(ADMIN_SETTINGS_KEY, JSON.stringify(data)); }
  catch (_) { throw new Error('Gagal menyimpan preferensi. Ruang localStorage mungkin penuh atau diblokir.'); }
  if (typeof window !== 'undefined') window.dispatchEvent(new CustomEvent(ADMIN_SETTINGS_EVENT));
  return value;
}

export function saveAdminIdentity(fields, now = Date.now()) {
  const identity = normalizeIdentity(fields);
  return persist({ ...readAdminSettings(), ...identity, updatedAt: now });
}

export function saveAdminPreferences(fields, now = Date.now()) {
  if (!safeObject(fields)) throw new Error('Preferensi tidak valid.');
  const existing = readAdminSettings();
  const next = structuredClone(existing);
  for (const group of ['notifications', 'preferences']) {
    if (!(group in fields)) continue;
    if (!safeObject(fields[group])) throw new Error(`Kelompok ${group} tidak valid.`);
    for (const [key, value] of Object.entries(fields[group])) {
      if (!(key in existing[group])) throw new Error(`Pengaturan ${group}.${key} tidak dikenal.`);
      if (key === 'defaultPeriod') {
        if (!['7', '30', '90', 'all'].includes(value)) throw new Error('Periode laporan tidak tersedia.');
      } else if (typeof value !== 'boolean') throw new Error(`Pengaturan ${key} harus aktif/nonaktif.`);
      next[group][key] = value;
    }
  }
  next.updatedAt = now;
  return persist(next);
}

export function resetAdminPreferences(now = Date.now()) {
  const current = readAdminSettings();
  const defaults = defaultAdminSettings();
  return persist({ ...current, notifications: defaults.notifications, preferences: defaults.preferences, updatedAt: now });
}

/** Simulasi validasi UI; jangan simpan, hash, atau kirim kata sandi apa pun. */
export function validateAdminDemoPassword(password, confirmation) {
  if (typeof password !== 'string' || password.length < 8 || password.length > 128) throw new Error('Password simulasi harus 8–128 karakter.');
  if (password !== confirmation) throw new Error('Konfirmasi password tidak cocok.');
  return true;
}

export function enterAdminDemo() { sessionStorage.setItem(ADMIN_DEMO_SESSION_KEY, 'active'); }
export function exitAdminDemo() { sessionStorage.removeItem(ADMIN_DEMO_SESSION_KEY); }
export function isAdminDemoActive() { return sessionStorage.getItem(ADMIN_DEMO_SESSION_KEY) === 'active'; }
