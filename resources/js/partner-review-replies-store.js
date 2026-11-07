/**
 * Balasan ulasan Mitra — prototype frontend-only, terpisah dari review asli Warga.
 * Data dapat diubah oleh siapa saja yang memiliki akses ke browser; bukan sistem autentikasi.
 */
import { getReview } from './reviews-store.js';

export const PARTNER_REPLIES_KEY = 'jasawarga:prototype-partner-review-replies:v1';
export const PARTNER_REPLIES_EVENT = 'jasawarga:partner-review-replies-updated';
const MAX_RECORDS = 30;
const REFERENCE_PATTERN = /^JW-DEMO-[0-9]{8}-[A-F0-9]{6}$/;

const validId = value => Number.isSafeInteger(Number(value)) && Number(value) > 0;
const validTime = value => Number.isFinite(value) && value > 0;

function isReply(record) {
  return record && typeof record === 'object' && !Array.isArray(record) &&
    record.version === 1 && validId(record.providerId) &&
    REFERENCE_PATTERN.test(record.reference || '') &&
    typeof record.text === 'string' && record.text === record.text.trim() &&
    record.text.length >= 10 && record.text.length <= 500 &&
    validTime(record.createdAt) && validTime(record.updatedAt) &&
    record.updatedAt >= record.createdAt;
}

function matchesReview(record) {
  const review = getReview(record.reference);
  return review && review.providerId === Number(record.providerId);
}

export function readPartnerReplies() {
  let parsed;
  try { parsed = JSON.parse(localStorage.getItem(PARTNER_REPLIES_KEY) || '[]'); }
  catch (_) { return []; }
  if (!Array.isArray(parsed)) return [];
  const seen = new Set();
  return parsed.filter(record => {
    if (!isReply(record) || !matchesReview(record) || seen.has(record.reference)) return false;
    seen.add(record.reference);
    return true;
  }).slice(0, MAX_RECORDS);
}

export function getPartnerReply(reference, providerId) {
  if (!validId(providerId) || !REFERENCE_PATTERN.test(String(reference))) return null;
  return readPartnerReplies().find(item => item.reference === reference && item.providerId === Number(providerId)) || null;
}

export function repliesForProvider(providerId) {
  if (!validId(providerId)) return [];
  return readPartnerReplies().filter(item => item.providerId === Number(providerId));
}

function emitChange() {
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent(PARTNER_REPLIES_EVENT));
  }
}

/** Simpan balasan pertama atau ubah balasan mitra yang sudah ada. */
export function savePartnerReply(providerId, reference, text) {
  if (!validId(providerId)) throw new Error('Identitas Mitra tidak valid.');
  const review = getReview(reference);
  if (!review || review.providerId !== Number(providerId)) {
    throw new Error('Ulasan tidak ditemukan untuk Mitra ini.');
  }
  const normalized = String(text ?? '').trim();
  if (normalized.length < 10 || normalized.length > 500) {
    throw new Error('Balasan harus berisi 10–500 karakter.');
  }
  const previous = getPartnerReply(reference, providerId);
  const now = Date.now();
  const reply = {
    version: 1,
    providerId: Number(providerId),
    reference,
    text: normalized,
    createdAt: previous?.createdAt || now,
    updatedAt: Math.max(now, previous?.createdAt || 0),
  };
  const other = readPartnerReplies().filter(item => item.reference !== reference);
  localStorage.setItem(PARTNER_REPLIES_KEY, JSON.stringify([reply, ...other].slice(0, MAX_RECORDS)));
  emitChange();
  return reply;
}

export function deletePartnerReply(providerId, reference) {
  const previous = getPartnerReply(reference, providerId);
  if (!previous) return false;
  const next = readPartnerReplies().filter(item => item.reference !== reference);
  localStorage.setItem(PARTNER_REPLIES_KEY, JSON.stringify(next));
  emitChange();
  return true;
}
