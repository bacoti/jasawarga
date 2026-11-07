/**
 * Etalase layanan mitra - prototype localStorage (TIDAK aman untuk data produksi).
 * Sumber bawaan: config/jasawarga_profiles.php, di-embed dalam Blade sebagai seed.
 * Key tetap per provider. ID stabil menjaga tautan pemesanan dan "Pesan lagi".
 */
export const SERVICES_KEY = 'jasawarga:prototype-services:v1';
export const SERVICES_EVENT = 'jasawarga:services-updated';
export const PRICE_MIN = 10000;
export const PRICE_MAX = 20000000;
export const SERVICE_UNITS = ['per pekerjaan', 'per unit', 'per kunjungan', 'per jam'];
const MAX_SERVICES = 40;

function assertId(providerId) {
  if (!Number.isSafeInteger(Number(providerId)) || Number(providerId) < 1) {
    throw new Error('ID mitra tidak valid.');
  }
}

function storageMap() {
  try {
    const parsed = JSON.parse(localStorage.getItem(SERVICES_KEY) || '{}');
    return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
  } catch (_) {
    return {};
  }
}

export function seedServices(seed, providerCategory = 'ac') {
  return (Array.isArray(seed) ? seed : []).map((item, index) => ({
    id: `base-${index}`,
    name: String(item.name || ''),
    description: String(item.description || ''),
    price: Number(item.price) || PRICE_MIN,
    duration: String(item.duration || '1 jam'),
    category: providerCategory,
    unit: 'per pekerjaan',
    feeNote: 'Biaya material dan kebutuhan tambahan dikonfirmasi saat survei.',
    active: true,
    origin: 'default',
  }));
}

function validRecord(item) {
  return item && typeof item === 'object' && typeof item.id === 'string' &&
    /^(base-\d+|custom-[a-z0-9-]+)$/.test(item.id) &&
    typeof item.name === 'string' && item.name.trim().length >= 3 &&
    typeof item.description === 'string' &&
    Number.isSafeInteger(item.price) && item.price >= PRICE_MIN && item.price <= PRICE_MAX &&
    typeof item.category === 'string' && /^[a-z0-9-]{1,40}$/.test(item.category) &&
    typeof item.duration === 'string' && typeof item.unit === 'string' &&
    typeof item.active === 'boolean';
}

/** Baca data lokal jika ada; tanpa override, gunakan layanan bawaan PHP. */
export function readServices(providerId, seed = [], providerCategory = 'ac') {
  assertId(providerId);
  const override = storageMap()[String(providerId)];
  if (!Array.isArray(override)) return seedServices(seed, providerCategory);
  // [] valid: mitra dapat menghapus seluruh layanan. Jangan kembalikan seed.
  return override.filter(validRecord).slice(0, MAX_SERVICES).map(item => ({ ...item }));
}

export function activeServices(providerId, seed = [], providerCategory = 'ac', categorySeed = null) {
  const items = readServices(providerId, seed, providerCategory).filter(item => item.active);
  // Jika seed kategori tersedia, sembunyikan juga layanan di kategori yang dinonaktifkan Admin.
  // Import dinamis tidak diperlukan: categorySeed dibaca dari PHP pada halaman publik.
  if (!Array.isArray(categorySeed)) return items;
  let override;
  try { override = JSON.parse(localStorage.getItem('jasawarga:prototype-admin-categories:v1') || 'null'); }
  catch (_) { override = null; }
  const categories = override?.version === 1 && Array.isArray(override.list) ? override.list : categorySeed.map(item => ({ ...item, active: true }));
  const active = new Set(categories.filter(item => item && item.active === true).map(item => item.slug));
  return items.filter(item => active.has(item.category));
}

function writeServices(providerId, items) {
  assertId(providerId);
  if (!Array.isArray(items) || items.length > MAX_SERVICES ||
      items.some(item => !validRecord(item)) ||
      new Set(items.map(item => item.id)).size !== items.length) {
    throw new Error('Daftar layanan tidak valid atau melebihi batas 40.');
  }
  const next = { ...storageMap(), [String(providerId)]: items };
  // Jika browser menolak penyimpanan, lempar error agar UI tidak mengklaim berhasil.
  localStorage.setItem(SERVICES_KEY, JSON.stringify(next));
  if (typeof window !== 'undefined') window.dispatchEvent(new CustomEvent(SERVICES_EVENT, { detail: { providerId: Number(providerId) } }));
  return items;
}

export function validateServiceInput(input, categorySlugs = []) {
  const clean = {
    name: String(input.name || '').trim(),
    description: String(input.description || '').trim(),
    category: String(input.category || '').trim(),
    duration: String(input.duration || '').trim(),
    unit: String(input.unit || ''),
    price: Number(input.price),
    feeNote: String(input.feeNote || '').trim(),
  };
  if (clean.name.length < 3 || clean.name.length > 80) throw new Error('Nama layanan harus 3–80 karakter.');
  if (clean.description.length < 15 || clean.description.length > 360) throw new Error('Deskripsi harus 15–360 karakter.');
  if (!categorySlugs.includes(clean.category)) throw new Error('Kategori belum dipilih atau tidak dikenal.');
  if (clean.duration.length < 2 || clean.duration.length > 60) throw new Error('Durasi harus 2–60 karakter.');
  if (!SERVICE_UNITS.includes(clean.unit)) throw new Error('Satuan tarif tidak valid.');
  if (!Number.isSafeInteger(clean.price) || clean.price < PRICE_MIN || clean.price > PRICE_MAX) {
    throw new Error('Tarif harus antara Rp 10.000 dan Rp 20.000.000.');
  }
  if (clean.feeNote.length > 200) throw new Error('Catatan biaya maksimal 200 karakter.');
  return clean;
}

/** Beri ID yang tidak bergantung pada urutan array. */
export function saveService(providerId, seed, providerCategory, fields, categorySlugs, editId = null) {
  const services = readServices(providerId, seed, providerCategory);
  const data = validateServiceInput(fields, categorySlugs);
  const index = editId === null ? -1 : services.findIndex(item => item.id === editId);
  if (editId !== null && index < 0) throw new Error('Layanan sudah tidak ditemukan. Segarkan daftar.');
  if (index < 0 && services.length >= MAX_SERVICES) throw new Error('Maksimal 40 layanan per mitra.');
  if (index >= 0) {
    services[index] = { ...services[index], ...data };
  } else {
    const id = `custom-${crypto.randomUUID()}`;
    services.push({ id, ...data, active: true, origin: 'custom' });
  }
  return writeServices(providerId, services);
}

export function setServiceActive(providerId, seed, providerCategory, id, active) {
  if (typeof active !== 'boolean') throw new Error('Status layanan tidak valid.');
  const services = readServices(providerId, seed, providerCategory);
  const target = services.find(item => item.id === id);
  if (!target) throw new Error('Layanan tidak ditemukan.');
  target.active = active;
  return writeServices(providerId, services);
}

export function deleteService(providerId, seed, providerCategory, id) {
  const services = readServices(providerId, seed, providerCategory);
  const after = services.filter(item => item.id !== id);
  if (after.length === services.length) throw new Error('Layanan tidak ditemukan.');
  return writeServices(providerId, after);
}

/** Konversi link lama ?layanan=0 menjadi ID permanen base-0. */
export function normalizeServiceId(requested) {
  if (requested === null || requested === undefined || requested === '') return '';
  const value = String(requested);
  if (/^\d+$/.test(value)) return `base-${Number(value)}`;
  return value;
}
