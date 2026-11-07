/** Administrasi JasaWarga PROTOTIPE: perubahan lokal, bukan hak akses server. */
export const ADMIN_CITIZEN_KEY = 'jasawarga:prototype-admin-citizens:v1';
export const ADMIN_PARTNER_KEY = 'jasawarga:prototype-admin-partners:v1';
export const ADMIN_MANAGEMENT_EVENT = 'jasawarga:admin-management-updated';
const CITIZEN_ID = /^jw-warga-[a-z0-9-]+$/;
const MAX_LOG = 80;
const isObject = x => !!x && typeof x === 'object' && !Array.isArray(x);
const reasonText = value => String(value ?? '').trim().replace(/\s+/g, ' ');
function read(key) {
  try {
    const value = JSON.parse(localStorage.getItem(key) || 'null');
    return isObject(value) && isObject(value.records) && Array.isArray(value.log)
      ? { records: value.records, log: value.log.slice(0, MAX_LOG) } : { records: {}, log: [] };
  } catch (_) { return { records: {}, log: [] }; }
}
function write(key, state) {
  localStorage.setItem(key, JSON.stringify(state));
  if (typeof window !== 'undefined') window.dispatchEvent(new CustomEvent(ADMIN_MANAGEMENT_EVENT, { detail: { key } }));
}
function validateReason(value, required = true) {
  const reason = reasonText(value);
  if ((required && reason.length < 10) || reason.length > 220)
    throw new Error('Alasan tindakan harus 10–220 karakter.');
  return reason;
}
function logItem(kind, targetId, previous, next, reason, now) {
  return { id: `admin-${now}-${Math.random().toString(36).slice(2, 9)}`,
    kind, targetId: String(targetId), previous, next, reason, at: now, actor: 'Admin Demo' };
}
export function readCitizenModeration() { return read(ADMIN_CITIZEN_KEY); }
export function citizenStatus(accountId, state = readCitizenModeration()) {
  const item = state.records?.[String(accountId)];
  return item?.status === 'inactive' ? 'inactive' : 'active';
}
/** Akun Warga asli TIDAK diubah; penonaktifan hanya label untuk demo. */
export function setCitizenStatus(accountId, next, reason, accounts, now = Date.now()) {
  if (!CITIZEN_ID.test(String(accountId))) throw new Error('ID akun Warga tidak valid.');
  if (!Array.isArray(accounts) || !accounts.some(a => a.id === accountId)) throw new Error('Akun Warga tidak ditemukan.');
  if (!['active', 'inactive'].includes(next)) throw new Error('Status akun tidak tersedia.');
  const why = validateReason(reason);
  const data = readCitizenModeration();
  const previous = citizenStatus(accountId, data);
  if (previous === next) throw new Error('Status akun tidak berubah.');
  data.records[accountId] = { status: next, updatedAt: now };
  data.log.unshift(logItem('citizen', accountId, previous, next, why, now));
  data.log = data.log.slice(0, MAX_LOG);
  write(ADMIN_CITIZEN_KEY, data);
  return data.records[accountId];
}
export function readPartnerModeration() { return read(ADMIN_PARTNER_KEY); }
export function partnerVerification(provider, state = readPartnerModeration()) {
  if (!provider || !Number.isSafeInteger(Number(provider.id))) return 'pending';
  const override = state.records?.[String(provider.id)]?.status;
  return ['pending', 'verified', 'rejected'].includes(override)
    ? override : (provider.verified === true ? 'verified' : 'pending');
}
/** Hanya override status verifikasi DEMO katalog, bukan pemeriksaan berkas nyata. */
export function setPartnerVerification(providerId, next, reason, providers, now = Date.now()) {
  const id = Number(providerId);
  if (!Number.isSafeInteger(id) || id < 1) throw new Error('ID Mitra tidak valid.');
  const provider = Array.isArray(providers) ? providers.find(item => Number(item.id) === id) : null;
  if (!provider) throw new Error('Mitra tidak ditemukan pada katalog contoh.');
  if (!['pending', 'verified', 'rejected'].includes(next)) throw new Error('Status verifikasi tidak valid.');
  const why = validateReason(reason);
  const data = readPartnerModeration();
  const previous = partnerVerification(provider, data);
  if (previous === next) throw new Error('Status verifikasi tidak berubah.');
  data.records[String(id)] = { status: next, updatedAt: now };
  data.log.unshift(logItem('partner', id, previous, next, why, now));
  data.log = data.log.slice(0, MAX_LOG);
  write(ADMIN_PARTNER_KEY, data);
  return data.records[String(id)];
}
export function summaryCitizens(accounts, orders, data = readCitizenModeration(), now = Date.now()) {
  const all = Array.isArray(accounts) ? accounts : [];
  const localOrders = Array.isArray(orders) ? orders : [];
  return {
    total: all.length,
    active: all.filter(a => citizenStatus(a.id, data) === 'active').length,
    inactive: all.filter(a => citizenStatus(a.id, data) === 'inactive').length,
    new30: all.filter(a => Number.isFinite(a.createdAt) && a.createdAt > now - 30 * 86400000 && a.createdAt <= now).length,
    linkedOrders: localOrders.filter(o => all.some(a => a.id === o.accountId)).length,
    unlinkedOrders: localOrders.filter(o => !all.some(a => a.id === o.accountId)).length,
  };
}
export function summaryPartners(providers, data = readPartnerModeration()) {
  const all = Array.isArray(providers) ? providers : [];
  return { total: all.length,
    verified: all.filter(p => partnerVerification(p, data) === 'verified').length,
    pending: all.filter(p => partnerVerification(p, data) === 'pending').length,
    rejected: all.filter(p => partnerVerification(p, data) === 'rejected').length,
  };
}
export function linkedCitizenOrders(accountId, orders) {
  // Jangan cocokkan hanya berdasarkan nama, telepon, atau alamat — rawan salah atribusi.
  return (Array.isArray(orders) ? orders : []).filter(order => order.accountId === accountId);
}
export function citizenAuditEntries(state = readCitizenModeration()) {
  return state.log.filter(x => x && x.kind === 'citizen' && CITIZEN_ID.test(x.targetId || '') && Number.isFinite(x.at));
}
export function partnerAuditEntries(state = readPartnerModeration()) {
  return state.log.filter(x => x && x.kind === 'partner' && /^\d+$/.test(x.targetId || '') && Number.isFinite(x.at));
}
