/**
 * Ulasan lokal prototipe JasaWarga. Tidak ada autentikasi maupun pengiriman data.
 * Simpan foto versi terkompresi (data URL JPEG), bukan gambar asli.
 */
import { getOrder } from './orders-store.js';

export const REVIEW_KEY = 'jasawarga:prototype-reviews:v1';
export const REVIEW_EVENT = 'jasawarga:reviews-updated';
export const REVIEW_LIMIT = 30;
const REF_PATTERN = /^JW-DEMO-[0-9]{8}-[A-F0-9]{6}$/;
const PHOTO_PATTERN = /^data:image\/jpeg;base64,[a-zA-Z0-9+/=]+$/;
const validScore = value => Number.isInteger(value) && value >= 1 && value <= 5;

function validReview(record) {
  return record !== null && typeof record === 'object' && record.version === 1 &&
    REF_PATTERN.test(record.reference || '') &&
    Number.isSafeInteger(record.providerId) && record.providerId > 0 &&
    typeof record.provider === 'string' && record.provider.length > 0 && record.provider.length <= 120 &&
    typeof record.service === 'string' && record.service.length > 0 && record.service.length <= 150 &&
    validScore(record.rating) &&
    ['quality', 'punctuality', 'friendliness'].every(key => validScore(record.details?.[key])) &&
    typeof record.comment === 'string' && record.comment.trim().length >= 20 && record.comment.length <= 800 &&
    Number.isFinite(record.createdAt) && record.createdAt > 0 &&
    Array.isArray(record.photos) && record.photos.length <= 2 &&
    record.photos.every(photo => typeof photo === 'string' && photo.length <= 180000 && PHOTO_PATTERN.test(photo));
}

export function readReviews() {
  let parsed;
  try { parsed = JSON.parse(localStorage.getItem(REVIEW_KEY) || '[]'); }
  catch (_) { return []; }
  if (!Array.isArray(parsed)) return [];
  const used = new Set();
  return parsed.filter(review => {
    if (!validReview(review) || used.has(review.reference)) return false;
    used.add(review.reference);
    return true;
  }).slice(0, REVIEW_LIMIT).sort((a, b) => b.createdAt - a.createdAt);
}

export function getReview(reference) {
  return readReviews().find(review => review.reference === reference) || null;
}

export function reviewsForProvider(providerId) {
  return readReviews().filter(review => review.providerId === Number(providerId));
}

export function saveReview({ reference, rating, details, comment, photos = [] }) {
  const order = getOrder(reference);
  if (!order) throw new Error('Pesanan tidak ditemukan di browser ini.');
  if (order.status !== 'completed') throw new Error('Hanya pesanan berstatus Selesai yang dapat diulas.');
  if (getReview(reference)) throw new Error('Pesanan ini sudah memiliki satu ulasan.');

  const payload = {
    version: 1, reference: order.reference,
    providerId: order.providerId, provider: order.provider, service: order.service,
    rating: Number(rating),
    details: {
      quality: Number(details?.quality),
      punctuality: Number(details?.punctuality),
      friendliness: Number(details?.friendliness),
    },
    comment: String(comment || '').trim(),
    photos: Array.isArray(photos) ? [...photos] : [],
    createdAt: Date.now(),
  };
  if (!validReview(payload)) {
    throw new Error('Rating harus 1–5, ulasan 20–800 karakter, dan foto maksimal 2 gambar kecil.');
  }

  const next = [payload, ...readReviews()].slice(0, REVIEW_LIMIT);
  // Jika browser kehabisan kuota, akan throw; jangan mengklaim data tersimpan.
  localStorage.setItem(REVIEW_KEY, JSON.stringify(next));
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent(REVIEW_EVENT));
  }
  return payload;
}

export function clearReviews() {
  localStorage.removeItem(REVIEW_KEY);
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent(REVIEW_EVENT));
  }
}
