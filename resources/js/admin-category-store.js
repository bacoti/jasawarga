/** Kategori Jasa Admin — semua CRUD adalah DEMO localStorage, bukan API server. */
import { readServices } from './services-store.js';
export const ADMIN_CATEGORIES_KEY = 'jasawarga:prototype-admin-categories:v1';
export const ADMIN_CATEGORIES_EVENT = 'jasawarga:admin-categories-updated';
export const CATEGORY_ICONS = Object.freeze(['home_repair_service','ac_unit','bolt','water_drop','tv','content_cut','cleaning_services','handyman','local_shipping','yard','computer','build','chair','plumbing','format_paint','pest_control']);
export const CATEGORY_COLORS = Object.freeze([
  ['#E2E9F7','#243147'],['#CEE5FF','#006098'],['#FFDCBD','#874F00'],
  ['#DDF4E7','#176B47'],['#D9F4EF','#166A63'],['#D6E3FF','#006098'],['#D8E2FF','#465E90'],
]);
const SLUG = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
const object = item => item !== null && typeof item === 'object' && !Array.isArray(item);
const clean = value => String(value ?? '').trim().replace(/\s+/g,' ');
const max = 36;
export function slugify(value) { return clean(value).toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'').slice(0,40).replace(/-$/,''); }
function validCategory(item) {
  return object(item) && typeof item.slug === 'string' && SLUG.test(item.slug) && item.slug.length <= 40 &&
    typeof item.name === 'string' && item.name.length >= 3 && item.name.length <= 55 &&
    typeof item.description === 'string' && item.description.length >= 8 && item.description.length <= 160 &&
    CATEGORY_ICONS.includes(item.icon) && CATEGORY_COLORS.some(([bg,fg]) => bg === item.bg && fg === item.fg) &&
    typeof item.active === 'boolean' && ['seed','custom'].includes(item.origin);
}
function defaultCategories(seed) {
  return (Array.isArray(seed) ? seed : []).filter(object).map(item => ({
    slug: String(item.slug || ''), name: String(item.name || ''), description: String(item.description || ''),
    icon: CATEGORY_ICONS.includes(item.icon) ? item.icon : 'handyman',
    bg: CATEGORY_COLORS.some(([bg,fg]) => bg === item.bg && fg === item.fg) ? item.bg : CATEGORY_COLORS[0][0],
    fg: CATEGORY_COLORS.some(([bg,fg]) => bg === item.bg && fg === item.fg) ? item.fg : CATEGORY_COLORS[0][1],
    active: true, origin: 'seed',
  })).filter(validCategory);
}
/** Tanpa data override, seed PHP tetap menjadi sumber bawaan. */
export function readCategories(seed = []) {
  let state;
  try { state = JSON.parse(localStorage.getItem(ADMIN_CATEGORIES_KEY) || 'null'); }
  catch (_) { state = null; }
  if (!object(state) || state.version !== 1 || !Array.isArray(state.list)) return defaultCategories(seed);
  const seen = new Set();
  return state.list.filter(item => {
    if (!validCategory(item) || seen.has(item.slug)) return false;
    seen.add(item.slug);return true;
  }).slice(0,max).map(item => ({...item}));
}
export function isCategoryActive(slug, seed = []) {
  return readCategories(seed).some(category => category.slug === slug && category.active);
}
export function validateCategory(fields, existing = []) {
  const name = clean(fields.name);
  const description = clean(fields.description);
  const slug = clean(fields.slug || slugify(name));
  const icon = String(fields.icon || 'handyman');
  const colorIndex = Number(fields.colorIndex ?? 0);
  if (name.length < 3 || name.length > 55) throw new Error('Nama kategori harus 3–55 karakter.');
  if (!SLUG.test(slug) || slug.length > 40) throw new Error('Slug hanya boleh huruf kecil, angka, dan tanda hubung (maks. 40).');
  if (description.length < 8 || description.length > 160) throw new Error('Deskripsi harus 8–160 karakter.');
  if (!CATEGORY_ICONS.includes(icon)) throw new Error('Ikon kategori tidak tersedia.');
  if (!Number.isInteger(colorIndex) || colorIndex < 0 || colorIndex >= CATEGORY_COLORS.length) throw new Error('Warna kategori tidak valid.');
  if (existing.some(item => item.slug === slug)) throw new Error('Slug kategori sudah digunakan.');
  const [bg,fg] = CATEGORY_COLORS[colorIndex];
  return { slug,name,description,icon,bg,fg,active:true,origin:'custom' };
}
function persist(items) {
  if (!Array.isArray(items) || items.length > max || items.some(item => !validCategory(item))) throw new Error('Daftar kategori tidak valid.');
  localStorage.setItem(ADMIN_CATEGORIES_KEY, JSON.stringify({version:1,list:items}));
  if (typeof window !== 'undefined') window.dispatchEvent(new CustomEvent(ADMIN_CATEGORIES_EVENT));
  return items;
}
export function createCategory(seed, fields) {
  const list = readCategories(seed);
  if (list.length >= max) throw new Error('Maksimal 36 kategori.');
  const next = validateCategory(fields, list);
  persist([...list,next]);return next;
}
/** Slug wajib stabil agar layanan yang sudah ada tidak kehilangan relasi. */
export function editCategory(seed, slug, fields) {
  const list = readCategories(seed); const previous = list.find(item => item.slug === slug);
  if (!previous) throw new Error('Kategori tidak ditemukan.');
  const data = validateCategory({...fields, slug}, list.filter(item => item.slug !== slug));
  persist(list.map(item => item.slug === slug ? {...previous,...data,slug,active:previous.active,origin:previous.origin} : item));
  return slug;
}
export function toggleCategory(seed, slug, active) {
  if (typeof active !== 'boolean') throw new Error('Status kategori tidak valid.');
  const list = readCategories(seed);
  if (!list.some(item => item.slug === slug)) throw new Error('Kategori tidak ditemukan.');
  persist(list.map(item => item.slug === slug ? {...item,active} : item));
}
export function categoryUsage(slug, providers = [], profiles = {}) {
  const catalog = Array.isArray(providers) ? providers : [];
  const primary = catalog.filter(p => p.category === slug).length;
  const services = catalog.reduce((count, provider) => count + readServices(provider.id, profiles?.[provider.id]?.services || [], provider.category)
    .filter(service => service.category === slug).length, 0);
  return {providers:primary,services,total:primary+services};
}
export function deleteCategory(seed, slug, providers = [], profiles = {}) {
  const list = readCategories(seed);
  if (!list.some(item => item.slug === slug)) throw new Error('Kategori tidak ditemukan.');
  if (categoryUsage(slug, providers, profiles).total) throw new Error('Kategori masih digunakan Mitra atau layanan. Nonaktifkan kategori sebagai alternatif.');
  persist(list.filter(item => item.slug !== slug));
}
