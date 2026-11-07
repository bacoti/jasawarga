/** Moderasi ulasan + pengaduan Admin: simulasi lokal, komentar asli Warga tidak diubah. */
export const ADMIN_FEEDBACK_KEY = 'jasawarga:prototype-admin-feedback:v1';
export const ADMIN_FEEDBACK_EVENT = 'jasawarga:admin-feedback-updated';
const REFERENCE = /^JW-DEMO-\d{8}-[A-F0-9]{6}$/;
const CASE_ID = /^JW-ADU-\d{8}-[A-F0-9]{6}$/;
const STATUS = ['open','investigating','resolved','closed'];
export const CASE_LABELS = Object.freeze({open:'Baru',investigating:'Ditangani',resolved:'Selesai ditangani',closed:'Ditutup'});
export const CASE_TOPICS = Object.freeze({service:'Kualitas layanan',schedule:'Jadwal',payment:'Tarif',conduct:'Perilaku',other:'Lainnya'});
const isObj = v => v !== null && typeof v === 'object' && !Array.isArray(v);
const norm = text => String(text ?? '').trim().replace(/\s+/g,' ');
const validLog = x => isObj(x) && Number.isFinite(x.at) && typeof x.action === 'string' && typeof x.reason === 'string';
const validCase = v => isObj(v) && CASE_ID.test(v.id || '') &&
  (!v.reference || REFERENCE.test(v.reference)) &&
  typeof v.subject === 'string' && v.subject.length >= 5 && v.subject.length <= 100 &&
  typeof v.description === 'string' && v.description.length >= 15 && v.description.length <= 600 &&
  Object.hasOwn(CASE_TOPICS,v.topic) && ['low','normal','high'].includes(v.priority) && STATUS.includes(v.status) &&
  Number.isFinite(v.createdAt) && Number.isFinite(v.updatedAt) && Array.isArray(v.history) && v.history.every(validLog);
const empty = () => ({reviews:{},cases:[],audit:[]});
export function readAdminFeedback() {
  let raw;try { raw = JSON.parse(localStorage.getItem(ADMIN_FEEDBACK_KEY) || 'null'); } catch (_) { return empty(); }
  if (!isObj(raw) || raw.version !== 1) return empty();
  const reviews = {};
  for (const [ref,value] of Object.entries(isObj(raw.reviews) ? raw.reviews : {})) {
    if (REFERENCE.test(ref) && isObj(value) && ['visible','hidden'].includes(value.status) && Number.isFinite(value.at)) reviews[ref] = {...value};
  }
  const ids = new Set();
  const cases = (Array.isArray(raw.cases) ? raw.cases : []).filter(item => {
    if (!validCase(item) || ids.has(item.id)) return false; ids.add(item.id);return true;
  }).slice(0,60);
  const audit = (Array.isArray(raw.audit) ? raw.audit : []).filter(validLog).slice(0,120);
  return {reviews,cases,audit};
}
export function reviewStatus(reference, state = readAdminFeedback()) { return state.reviews?.[reference]?.status === 'hidden' ? 'hidden' : 'visible'; }
export function changeReviewStatus(reference, target, reason, reviews, now = Date.now()) {
  if (!REFERENCE.test(String(reference)) || !Array.isArray(reviews) || !reviews.some(item => item.reference === reference)) throw new Error('Ulasan lokal tidak ditemukan.');
  if (!['visible','hidden'].includes(target)) throw new Error('Status moderasi tidak valid.');
  const why = norm(reason);
  if (why.length < 10 || why.length > 300) throw new Error('Alasan moderasi harus 10–300 karakter.');
  const state = readAdminFeedback();
  const previous = reviewStatus(reference,state);
  if (previous === target) throw new Error('Status ulasan tidak berubah.');
  state.reviews[reference] = {status:target,reason:why,at:now};
  state.audit.unshift({at:now,action:`review:${previous}->${target}`,reason:why,target:reference,actor:'Admin Demo'});
  persist(state);return state.reviews[reference];
}
function persist(state) {
  localStorage.setItem(ADMIN_FEEDBACK_KEY, JSON.stringify({version:1,reviews:state.reviews,cases:state.cases.slice(0,60),audit:state.audit.slice(0,120)}));
  if (typeof window !== 'undefined') window.dispatchEvent(new CustomEvent(ADMIN_FEEDBACK_EVENT));
}
/** Pengaduan awal hanya dibuat dari Dashboard Admin, bukan pengajuan Warga sungguhan. */
export function createComplaint(fields, orders = [], reviews = [], now = Date.now(), token = '') {
  const state = readAdminFeedback();
  if (state.cases.length >= 60) throw new Error('Maksimal 60 pengaduan demo.');
  const reference = norm(fields.reference).toUpperCase();
  if (reference && (!REFERENCE.test(reference) || ![...orders,...reviews].some(item => item.reference === reference))) throw new Error('Kode pesanan/ulasan tidak ditemukan di browser ini.');
  const subject = norm(fields.subject), description = norm(fields.description);
  if (subject.length < 5 || subject.length > 100) throw new Error('Judul pengaduan harus 5–100 karakter.');
  if (description.length < 15 || description.length > 600) throw new Error('Deskripsi pengaduan harus 15–600 karakter.');
  if (!Object.hasOwn(CASE_TOPICS,fields.topic)) throw new Error('Jenis pengaduan tidak valid.');
  if (!['low','normal','high'].includes(fields.priority)) throw new Error('Prioritas tidak valid.');
  const hash = /^[A-F0-9]{6}$/.test(token) ? token : Array.from(crypto.getRandomValues(new Uint8Array(3))).map(b => b.toString(16).padStart(2,'0')).join('').toUpperCase();
  const day = new Date(now).toISOString().slice(0,10).replaceAll('-','');
  const result = {id:`JW-ADU-${day}-${hash}`,reference,subject,description,topic:fields.topic,
    priority:fields.priority,status:'open',createdAt:now,updatedAt:now,history:[{at:now,action:'created',reason:'Dicatat oleh Admin Demo'}]};
  if (!validCase(result)) throw new Error('Pengaduan tidak valid.');
  if (state.cases.some(item=>item.id===result.id)) throw new Error('Nomor kasus bentrok, coba lagi.');
  state.cases.unshift(result);
  state.audit.unshift({at:now,action:'case:created',reason:'Pengaduan demo dicatat',target:result.id,actor:'Admin Demo'});
  persist(state);return result;
}
export function changeComplaintStatus(id,target,reason,now=Date.now()) {
  if (!CASE_ID.test(String(id)) || !STATUS.includes(target)) throw new Error('ID kasus/status tidak valid.');
  const why = norm(reason);
  if (why.length < 10 || why.length > 300) throw new Error('Catatan penanganan harus 10–300 karakter.');
  const state = readAdminFeedback();const item = state.cases.find(item => item.id === id);
  if (!item) throw new Error('Kasus tidak ditemukan.');
  const allowed = {open:['investigating','closed'],investigating:['resolved','closed'],resolved:['investigating','closed'],closed:['open']};
  if (!allowed[item.status].includes(target)) throw new Error('Perubahan status kasus tidak diperbolehkan.');
  const previous = item.status;item.status=target;item.updatedAt=now;
  item.history.push({at:now,action:`${previous}->${target}`,reason:why});
  state.audit.unshift({at:now,action:`case:${previous}->${target}`,reason:why,target:id,actor:'Admin Demo'});
  persist(state);return item;
}
export function filterComplaints(cases, {query='',status='all',priority='all'}={}) {
  const needle = norm(query).toLocaleLowerCase('id-ID');
  return (Array.isArray(cases) ? cases : []).filter(item =>
    (status === 'all' || item.status === status) && (priority === 'all' || item.priority === priority) &&
    (!needle || [item.id,item.reference,item.subject,item.description].some(v=>String(v||'').toLocaleLowerCase('id-ID').includes(needle)))
  ).sort((a,b)=>b.createdAt-a.createdAt);
}
