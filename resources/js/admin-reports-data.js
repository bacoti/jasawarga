/** Agregasi laporan lokal tanpa DOM/API. Semua nilai bersifat prototipe, bukan transaksi nyata. */
import { completedTime } from './admin-overview-data.js';
import { reviewStatus } from './admin-feedback-store.js';
export const REPORT_STATUS_LABELS = Object.freeze({pending:'Menunggu',accepted:'Diterima',enroute:'Perjalanan',working:'Dikerjakan',completed:'Selesai',cancelled:'Dibatalkan',rejected:'Ditolak'});
export const REPORT_PERIODS = Object.freeze(['7','30','90','all','custom']);
const asList = value => Array.isArray(value) ? value : [];
const safeTimestamp = value => typeof value === 'number' && Number.isFinite(value) && value > 0;
const money = value => Number.isFinite(Number(value)) ? Math.max(0, Number(value)) : 0;
const cleanText = value => String(value ?? '').trim().slice(0, 140);
const day = value => { const d = new Date(value); return Number.isFinite(d.getTime()) ? `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}` : null; };
export function validatePeriod(options = {}, now = Date.now()) {
  const period = String(options.period ?? '30');
  if (!REPORT_PERIODS.includes(period)) throw new Error('Periode laporan tidak dikenali.');
  if (period === 'all') return { period, from: null, to: day(now) };
  const today = new Date(now); today.setHours(12,0,0,0);
  if (period !== 'custom') {
    const start = new Date(today); start.setDate(start.getDate()-(Number(period)-1));
    return { period, from: day(start), to: day(today) };
  }
  const from = String(options.from ?? ''), to = String(options.to ?? '');
  const legal = text => /^\d{4}-\d{2}-\d{2}$/.test(text) && day(new Date(`${text}T12:00:00`)) === text;
  if (!legal(from) || !legal(to) || from > to) throw new Error('Tanggal awal/akhir laporan tidak valid.');
  const max = new Date(`${to}T12:00:00`), min = new Date(`${from}T12:00:00`);
  if ((max.getTime()-min.getTime())/86400000 > 365) throw new Error('Rentang khusus maksimal 366 hari.');
  if (to > day(now)) throw new Error('Tanggal akhir tidak boleh melewati hari ini.');
  return { period, from, to };
}

export function inPeriod(timestamp, bounds) {
  if (!safeTimestamp(timestamp)) return false;
  const key = day(timestamp);
  return Boolean(key && (!bounds.from || key >= bounds.from) && key <= bounds.to);
}

function periodDays(bounds, now = Date.now()) {
  if (bounds.period === 'all') {
    // Untuk grafik all-time: gunakan 30 hari terakhir agar tidak menghasilkan batang ribuan hari.
    return validatePeriod({period:'30'}, now);
  }
  return bounds;
}

export function buildAdminReport(data = {}, selection = {}, now = Date.now()) {
  const bounds = validatePeriod(selection, now);
  const allOrders = asList(data.orders);
  const allReviews = asList(data.reviews);
  const accounts = asList(data.accounts);
  const providers = asList(data.providers);
  const categories = asList(data.categories);
  const feedback = data.feedback && typeof data.feedback === 'object' ? data.feedback : {};
  const complaintsAll = asList(feedback.cases);
  const orders = allOrders.filter(o => inPeriod(o?.createdAt, bounds));
  const completedEvents = allOrders.filter(o => o?.status === 'completed' && inPeriod(completedTime(o), bounds));
  const reviews = allReviews.filter(r => inPeriod(r?.createdAt, bounds));
  const visibleReviews = reviews.filter(r => reviewStatus(r.reference, feedback) === 'visible');
  const complaints = complaintsAll.filter(c => inPeriod(c?.createdAt, bounds));
  const joinedAccounts = accounts.filter(a => inPeriod(a?.createdAt,bounds));
  const totalRating = visibleReviews.reduce((sum,r)=>sum+(Number(r.rating)||0),0);
  const statusCounts = Object.fromEntries(Object.keys(REPORT_STATUS_LABELS).map(status=>[status, orders.filter(o=>o?.status===status).length]));
  const completedTariff = completedEvents.reduce((sum,o)=>sum+money(o.price),0);
  const pendingComplaint = complaints.filter(c=>['open','investigating'].includes(c.status)).length;
  const providerNames = new Map(providers.map(p=>[Number(p.id),String(p.name)]));
  const categoryNames = new Map(categories.map(c=>[String(c.slug),String(c.name)]));
  const providerMap = new Map(providers.map(p=>[Number(p.id),String(p.category)]));
  const byProvider = new Map(), byCategory = new Map();
  orders.forEach(order=>{
    const id = Number(order.providerId);
    const label = providerNames.get(id) || `Mitra #${Number.isSafeInteger(id) ? id : '?'}`;
    const entry = byProvider.get(id) || {id,label,orders:0,completed:0,estimatedTariff:0};
    entry.orders += 1;
    if (order.status==='completed') {entry.completed+=1;entry.estimatedTariff+=money(order.price);}
    byProvider.set(id,entry);
    const slug=String(order.category||providerMap.get(id)||'other');
    const categoryEntry=byCategory.get(slug)||{slug,label:categoryNames.get(slug)||'Tidak diketahui',orders:0,completed:0};
    categoryEntry.orders += 1;
    if(order.status==='completed')categoryEntry.completed+=1;
    byCategory.set(slug,categoryEntry);
  });
  const ranked=(list)=>[...list].sort((a,b)=>b.orders-a.orders || String(a.label).localeCompare(String(b.label),'id-ID'));
  const chartBounds=periodDays(bounds,now), start = chartBounds.from || chartBounds.to;
  const days=[]; const cursor = new Date(`${start}T12:00:00`), end=new Date(`${chartBounds.to}T12:00:00`);
  while (cursor <= end && days.length < 366) {
    days.push({ date:day(cursor),created:0,completed:0,reviews:0 });
    cursor.setDate(cursor.getDate()+1);
  }
  const indexed = new Map(days.map(d=>[d.date,d]));
  allOrders.forEach(o=>{
    const created=indexed.get(day(o.createdAt));if(created)created.created++;
    if(o.status==='completed'){const completed=indexed.get(day(completedTime(o)));if(completed)completed.completed++;}
  });
  allReviews.forEach(r=>{const hit=indexed.get(day(r.createdAt));if(hit)hit.reviews++;});
  const audits = [
    ...asList(data.citizenAudit?.log).map(a=>({...a,source:'Warga'})),
    ...asList(data.partnerAudit?.log).map(a=>({...a,source:'Verifikasi'})),
    ...asList(feedback.audit).map(a=>({...a,source:'Ulasan/Pengaduan'})),
  ].filter(a=>inPeriod(a.at,bounds)).sort((a,b)=>b.at-a.at).slice(0,30);
  return {
    bounds, chartBounded:bounds.period==='all',
    orders, completedEvents, reviews, visibleReviews, complaints,
    metrics:{
      orders:orders.length, completed:statusCounts.completed, completedEvents:completedEvents.length,
      pending:statusCounts.pending, active:statusCounts.accepted+statusCounts.enroute+statusCounts.working,
      cancelled:statusCounts.cancelled+statusCounts.rejected,
      completionRate:orders.length ? Math.round(statusCounts.completed/orders.length*100) : 0,
      estimatedCompletedTariff:completedTariff, reviews:reviews.length, visibleReviews:visibleReviews.length,
      averageRating:visibleReviews.length ? Number((totalRating/visibleReviews.length).toFixed(1)) : 0,
      complaints:complaints.length, complaintsOpen:pendingComplaint, newAccounts:joinedAccounts.length,
      catalogProvidersDemo:providers.length, catalogCategoriesDemo:categories.length,
      servicesDemo:asList(data.serviceCounts).reduce((sum,c)=>sum+money(c.activeCount),0),
    },statusCounts,
    chart:days, topProviders:ranked(byProvider.values()).slice(0,8),topCategories:ranked(byCategory.values()).slice(0,8), audits,
  };
}

/** Escape CSV untuk Excel termasuk formula injection dan newline. Default ekspor bebas PII. */
export function safeCsvCell(value) {
  let text=cleanText(value).replace(/[\u0000-\u001f\u007f]+/g,' ');
  if (/^[\s]*[=+\-@]/.test(text)) text=`'${text}`;
  return `"${text.replace(/"/g,'""')}"`;
}
export function makeReportCsv(kind, report, { includeEstimatedTariff = true } = {}) {
  let rows;
  if(kind==='orders') {
    const headings=['Tanggal buat','Kode','Mitra (katalog demo)','Layanan','Status'];
    if(includeEstimatedTariff)headings.push('Estimasi tarif (Rp)');
    rows=[headings,...report.orders.map(o=>{const line=[day(o.createdAt)||'',o.reference,o.provider,o.service,REPORT_STATUS_LABELS[o.status]||'Tidak diketahui'];if(includeEstimatedTariff)line.push(money(o.price));return line;})];
  } else if (kind==='complaints') {
    rows=[['Nomor kasus','Tanggal dibuat','Kategori pengaduan','Prioritas','Status','Kode pesanan'],...report.complaints.map(c=>[c.id,day(c.createdAt)||'',c.topic,c.priority,c.status,c.reference||''])];
  } else if (kind==='summary') {
    rows=[['Indikator','Nilai','Sumber'],['Periode awal',report.bounds.from||'Sejak data tersedia','Filter browser'],['Periode akhir',report.bounds.to,'Filter browser'],...Object.entries(report.metrics).filter(([key])=>includeEstimatedTariff || key!=='estimatedCompletedTariff').map(([key,value])=>[key,value,key.includes('Demo')?'Katalog demo PHP':'localStorage browser'])];
  } else throw new Error('Format ekspor tidak dikenali.');
  return '\ufeff'+rows.map(row=>row.map(safeCsvCell).join(',')).join('\r\n');
}
