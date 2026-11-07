/**
 * Profil dan portofolio Mitra — penyimpanan MOCK satu browser.
 * Data publik tetap menggunakan seed PHP bila tidak ada perubahan lokal.
 * Badge verified TIDAK pernah menjadi bagian dari state yang bisa diedit.
 */
export const PROFILE_KEY = 'jasawarga:prototype-partner-profiles:v1';
export const PROFILE_EVENT = 'jasawarga:partner-profile-updated';
export const BANDUNG_AREAS = Object.freeze([
  'Andir',
  'Antapani',
  'Arcamanik',
  'Astanaanyar',
  'Babakan Ciparay',
  'Bandung Kidul',
  'Bandung Kulon',
  'Bandung Wetan',
  'Batununggal',
  'Bojongloa Kaler',
  'Bojongloa Kidul',
  'Buahbatu',
  'Cibeunying Kaler',
  'Cibeunying Kidul',
  'Cibiru',
  'Cicendo',
  'Cidadap',
  'Cinambo',
  'Coblong',
  'Gedebage',
  'Kiaracondong',
  'Lengkong',
  'Mandalajati',
  'Panyileukan',
  'Rancasari',
  'Regol',
  'Sukajadi',
  'Sukasari',
  'Sumur Bandung',
  'Ujungberung'
]);
// Kompatibilitas internal dengan modul lama; opsi pengguna tetap daftar kecamatan Bandung.
export const MAKASSAR_AREAS = BANDUNG_AREAS;
export const MAX_PROJECTS = 9;
export const MAX_IMAGE_CHARS = 440000; // base64 data URL per gambar, hindari melampaui quota

function validProviderId(value) {
  const number = Number(value);
  if (!Number.isSafeInteger(number) || number < 1) throw new Error('ID mitra tidak valid.');
  return number;
}
const asText = value => String(value ?? '').trim();
const safeImage = value => typeof value === 'string' && (
  value === '' || (/^data:image\/jpeg;base64,[A-Za-z0-9+/=]+$/.test(value) && value.length <= MAX_IMAGE_CHARS)
);
function readMap() {
  try {
    const parsed = JSON.parse(localStorage.getItem(PROFILE_KEY) || '{}');
    return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
  } catch (_) { return {}; }
}
const unique = array => [...new Set(array)];

export function seedPartnerProfile(provider, profile) {
  const years = parseInt(String(profile?.experience || '0'), 10);
  const coverage = Array.isArray(profile?.coverage)
    ? unique(profile.coverage.filter(area => BANDUNG_AREAS.includes(area))) : [];
  const fallbackArea = BANDUNG_AREAS.includes(provider?.area) ? provider.area : 'Coblong';
  return {
    name: asText(provider?.name || 'Mitra JasaWarga'),
    headline: asText(profile?.headline || 'Penyedia layanan lokal Bandung.'),
    about: asText(profile?.about || 'Informasi usaha mitra.'),
    specialties: unique([asText(provider?.service || 'Layanan jasa')]).slice(0, 5),
    experienceYears: Number.isFinite(years) ? Math.max(0, Math.min(50, years)) : 0,
    phone: '',
    coverage: coverage.length ? coverage : [fallbackArea],
    avatarDataUrl: '',
    coverDataUrl: '',
    projects: (Array.isArray(profile?.projects) ? profile.projects : []).slice(0, MAX_PROJECTS)
      .map((item, i) => ({
        id: `demo-${i}`, title: asText(item.title || `Contoh proyek ${i + 1}`),
        description: asText(item.note || 'Ilustrasi portofolio bawaan'),
        area: fallbackArea, imageDataUrl: '', isDemo: true,
        icon: asText(item.icon || 'construction'),
        color: /^#[0-9a-fA-F]{6}$/.test(item.color || '') ? item.color : '#CEE5FF',
      })),
  };
}
function isProject(value) {
  return value && typeof value === 'object' &&
    typeof value.id === 'string' && /^(demo-\d+|project-[a-zA-Z0-9-]+)$/.test(value.id) &&
    typeof value.title === 'string' && value.title.trim().length >= 3 && value.title.length <= 90 &&
    typeof value.description === 'string' && value.description.length <= 360 &&
    BANDUNG_AREAS.includes(value.area) && safeImage(value.imageDataUrl) &&
    typeof value.isDemo === 'boolean' &&
    typeof value.icon === 'string' && /^[-a-z_]{2,40}$/.test(value.icon) &&
    typeof value.color === 'string' && /^#[0-9a-fA-F]{6}$/.test(value.color);
}
function isProfile(value) {
  return value && typeof value === 'object' &&
    typeof value.name === 'string' && value.name.trim().length >= 3 && value.name.length <= 90 &&
    typeof value.headline === 'string' && value.headline.trim().length >= 10 && value.headline.length <= 170 &&
    typeof value.about === 'string' && value.about.trim().length >= 30 && value.about.length <= 1400 &&
    Number.isSafeInteger(value.experienceYears) && value.experienceYears >= 0 && value.experienceYears <= 50 &&
    typeof value.phone === 'string' && (value.phone === '' || /^(?:\+62|62|0)8\d{8,12}$/.test(value.phone)) &&
    Array.isArray(value.specialties) && value.specialties.length >= 1 && value.specialties.length <= 5 &&
    value.specialties.every(item => typeof item === 'string' && item.trim().length >= 2 && item.length <= 45) &&
    Array.isArray(value.coverage) && value.coverage.length >= 1 && value.coverage.length <= 15 &&
    value.coverage.every(area => BANDUNG_AREAS.includes(area)) &&
    safeImage(value.avatarDataUrl) && safeImage(value.coverDataUrl) &&
    Array.isArray(value.projects) && value.projects.length <= MAX_PROJECTS &&
    value.projects.every(isProject) &&
    new Set(value.projects.map(item => item.id)).size === value.projects.length;
}
/** true bila browser memiliki override profil yang utuh dan aman. */
export function hasPartnerProfileOverride(providerId) {
  validProviderId(providerId);
  return Boolean(isProfile(readMap()[String(providerId)]));
}
/** Tanpa override: seed, dengan override: hanya field whitelist yang tervalidasi. */
export function readPartnerProfile(providerId, provider, profile) {
  validProviderId(providerId);
  const base = seedPartnerProfile(provider, profile);
  const override = readMap()[String(providerId)];
  if (!isProfile(override)) return base;
  const allowed = Object.keys(base);
  return Object.fromEntries(allowed.map(key => [key, structuredClone(override[key])]));
}
function write(providerId, next) {
  validProviderId(providerId);
  if (!isProfile(next)) throw new Error('Data profil atau portofolio tidak valid.');
  const map = readMap();
  map[String(providerId)] = next;
  try {
    localStorage.setItem(PROFILE_KEY, JSON.stringify(map));
  } catch (_) {
    throw new Error('Penyimpanan browser penuh. Kurangi ukuran atau jumlah foto, lalu coba lagi.');
  }
  if (typeof window !== 'undefined' && typeof CustomEvent !== 'undefined') {
    window.dispatchEvent(new CustomEvent(PROFILE_EVENT, { detail: { providerId: Number(providerId) } }));
  }
  return structuredClone(next);
}

export function validateProfileFields(input) {
  const specialties = unique(String(input.specialties ?? '').split(',').map(asText).filter(Boolean));
  const coverage = unique(Array.isArray(input.coverage) ? input.coverage : []);
  const value = {
    name: asText(input.name), headline: asText(input.headline),
    about: asText(input.about), specialties,
    experienceYears: Number(input.experienceYears),
    phone: asText(input.phone).replace(/[\s()-]/g, ''), coverage,
    avatarDataUrl: input.avatarDataUrl ?? '', coverDataUrl: input.coverDataUrl ?? '',
  };
  if (value.name.length < 3 || value.name.length > 90) throw new Error('Nama usaha harus 3–90 karakter.');
  if (value.headline.length < 10 || value.headline.length > 170) throw new Error('Tagline harus 10–170 karakter.');
  if (value.about.length < 30 || value.about.length > 1400) throw new Error('Deskripsi usaha harus 30–1400 karakter.');
  if (!Number.isSafeInteger(value.experienceYears) || value.experienceYears < 0 || value.experienceYears > 50) throw new Error('Pengalaman harus 0–50 tahun.');
  if (value.phone && !/^(?:\+62|62|0)8\d{8,12}$/.test(value.phone)) throw new Error('Nomor WhatsApp Indonesia tidak valid.');
  if (!specialties.length || specialties.length > 5 || specialties.some(s => s.length < 2 || s.length > 45)) throw new Error('Isi 1–5 spesialisasi, dipisahkan koma (2–45 karakter per item).');
  if (!coverage.length || coverage.length > 30 || coverage.some(s => !BANDUNG_AREAS.includes(s))) throw new Error('Pilih minimal satu kecamatan di Bandung.');
  if (!safeImage(value.avatarDataUrl) || !safeImage(value.coverDataUrl)) throw new Error('Foto profil atau cover tidak sesuai batas ukuran.');
  return value;
}
export function savePartnerProfile(providerId, provider, seed, form) {
  const current = readPartnerProfile(providerId, provider, seed);
  const fields = validateProfileFields(form);
  return write(providerId, { ...current, ...fields }); // verified TIDAK ada di schema
}
export function savePartnerProject(providerId, provider, seed, input, editId = '') {
  const current = readPartnerProfile(providerId, provider, seed);
  const title = asText(input.title);
  const description = asText(input.description);
  const area = asText(input.area);
  const imageDataUrl = input.imageDataUrl ?? '';
  if (title.length < 3 || title.length > 90) throw new Error('Judul portofolio harus 3–90 karakter.');
  if (description.length < 15 || description.length > 360) throw new Error('Deskripsi portofolio harus 15–360 karakter.');
  if (!BANDUNG_AREAS.includes(area)) throw new Error('Pilih kecamatan pekerjaan yang valid.');
  if (!safeImage(imageDataUrl)) throw new Error('Foto pekerjaan tidak valid atau terlalu besar.');
  const index = current.projects.findIndex(item => item.id === editId);
  if (editId && index < 0) throw new Error('Portofolio yang diedit tidak ditemukan.');
  if (index < 0 && current.projects.length >= MAX_PROJECTS) throw new Error(`Maksimal ${MAX_PROJECTS} portofolio.`);
  const item = {
    id: index >= 0 ? editId : `project-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 9)}`,
    title, description, area, imageDataUrl,
    isDemo: false, icon: 'construction', color: '#CEE5FF',
  };
  if (index >= 0) current.projects[index] = item;
  else current.projects.unshift(item);
  return write(providerId, current);
}
export function removePartnerProject(providerId, provider, seed, projectId) {
  const current = readPartnerProfile(providerId, provider, seed);
  const before = current.projects.length;
  current.projects = current.projects.filter(item => item.id !== projectId);
  if (current.projects.length === before) throw new Error('Portofolio tidak ditemukan.');
  return write(providerId, current);
}
/** URL gambar harus dihasilkan oleh kompresor frontend, tidak menerima URL internet. */
export function isSafeProfileImage(url) { return safeImage(url) && url !== ''; }
