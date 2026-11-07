import { activeServices } from './services-store.js';
import { readCategories, ADMIN_CATEGORIES_KEY, ADMIN_CATEGORIES_EVENT } from './admin-category-store.js';
import { readOrders, ORDER_KEY } from './orders-store.js';
import { readPartnerProfile, hasPartnerProfileOverride } from './partner-profile-store.js';
import {
 ADMIN_PARTNER_KEY, ADMIN_MANAGEMENT_EVENT, readPartnerModeration,
 partnerVerification, setPartnerVerification, summaryPartners, partnerAuditEntries,
} from './admin-management-store.js';
const root = document.querySelector('#jw-admin-partners');
if (root) {
  const $ = q => root.querySelector(q);
  const seed = JSON.parse($('#jw-admin-partners-seed').textContent);
  const providers = Array.isArray(seed.providers) ? seed.providers : [];
  const profiles = seed.profiles || {};
  const categorySeed = Array.isArray(seed.categories) ? seed.categories : [];
  let categories = readCategories(categorySeed);
  const statusNames = {verified:'Terverifikasi (demo)',pending:'Belum diverifikasi',rejected:'Ditolak (demo)'};
  const statusStyle = {verified:'bg-emerald-50 text-emerald-800',pending:'bg-amber-50 text-amber-800',rejected:'bg-rose-50 text-rose-800'};
  const currency = new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0});
  const dateTime = new Intl.DateTimeFormat('id-ID',{dateStyle:'medium',timeStyle:'short'});
  let filter = {q:'',status:'all',category:'all'};
  let selectedId = null, pending=null;
  const detail = $('#jw-admin-partner-detail'), action = $('#jw-admin-partner-action');
  const el = (tag,cl='',value) => {const node=document.createElement(tag);node.className=cl;if(value!==undefined)node.textContent=String(value);return node;};
  function notify(text,isError=false){const notice=$('[data-partner-notice]');notice.textContent=text;notice.className=`rounded-xl px-4 py-3 text-sm ${isError?'bg-rose-50 text-rose-800':'bg-blue-50 text-primary'}`;}
  function currentName(provider) {
    if (!hasPartnerProfileOverride(provider.id)) return provider.name;
    return readPartnerProfile(provider.id,provider,profiles[String(provider.id)]).name;
  }
  function showDetail(id){
    const p=providers.find(x=>Number(x.id)===id);if(!p)return;
    selectedId=id;
    const state=readPartnerModeration(), status=partnerVerification(p,state);
    $('[data-partner-detail-title]').textContent=currentName(p);
    $('[data-partner-detail-meta]').textContent=`${categories.find(c=>c.slug===p.category)?.name||'Layanan'} · ${p.area}, Bandung`;
    $('[data-partner-detail-status]').textContent=statusNames[status];
    $('[data-partner-detail-services]').textContent=activeServices(id,profiles[String(id)]?.services||[],p.category,readCategories(categorySeed)).length;
    const orders=readOrders().filter(o=>o.providerId===id);
    $('[data-partner-detail-orders]').textContent=orders.length;
    $('[data-partner-detail-tariff]').textContent=currency.format(p.price);
    $('[data-partner-detail-link]').href=`/mitra/${encodeURIComponent(id)}`;
    const buttons=$('[data-partner-detail-actions]');buttons.replaceChildren();
    [['verified','Setujui (demo)'],['rejected','Tolak (demo)'],['pending','Atur belum diverifikasi']].filter(x=>x[0]!==status).forEach(([next,label])=>{
      const b=el('button','rounded-xl border border-outline-variant/60 px-3 py-2.5 text-xs font-bold text-primary hover:bg-primary-fixed/30',label);
      b.type='button';b.addEventListener('click',()=>{
        pending={id,next};
        $('[data-partner-action-label]').textContent=label;
        $('[data-partner-reason]').value='';$('[data-partner-action-error]').textContent='';
        detail.close();action.showModal();
      });buttons.append(b);
    });
    if(!detail.open)detail.showModal();
  }
  function render(){
    categories=readCategories(categorySeed);
    const state=readPartnerModeration();
    const stats=summaryPartners(providers,state);
    Object.entries(stats).forEach(([k,v])=>{const a=$(`[data-partner-kpi="${k}"]`);if(a)a.textContent=v.toLocaleString('id-ID');});
    let visible=providers.filter(p=>{
      const status=partnerVerification(p,state);
      if(filter.status!=='all'&&status!==filter.status)return false;
      if(filter.category!=='all'&&filter.category!==p.category)return false;
      const q=filter.q.toLocaleLowerCase('id-ID').trim();
      return !q||[currentName(p),p.name,p.area,p.service].some(s=>String(s||'').toLocaleLowerCase('id-ID').includes(q));
    });
    $('[data-partner-count]').textContent=visible.length;
    $('[data-partner-empty]').classList.toggle('hidden',visible.length>0);
    const list=$('[data-partner-table]');list.replaceChildren();
    visible.forEach(p=>{
      const status=partnerVerification(p,state);
      const row=el('tr','border-b border-outline-variant/35 last:border-0 hover:bg-surface-container-low/60');
      const about=el('td','px-4 py-4');about.append(el('p','font-bold text-on-surface',currentName(p)),el('p','mt-1 text-xs text-outline',`${p.area} · ID ${p.id} (katalog contoh)`));
      const cat=el('td','px-4 py-4 text-xs text-on-surface-variant',categories.find(c=>c.slug===p.category)?.name||p.category);
      const service=el('td','px-4 py-4 text-xs text-on-surface-variant',activeServices(p.id,profiles[String(p.id)]?.services||[],p.category,readCategories(categorySeed)).length);
      const badge=el('td','px-4 py-4');badge.append(el('span',`inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ${statusStyle[status]}`,statusNames[status]));
      const action=el('td','px-4 py-4');const button=el('button','rounded-lg border border-outline-variant/60 px-3 py-2 text-xs font-bold text-primary hover:bg-primary-fixed/30','Periksa');button.type='button';button.addEventListener('click',()=>showDetail(p.id));action.append(button);
      row.append(about,cat,service,badge,action);list.append(row);
    });
    const logs=$('[data-partner-logs]');logs.replaceChildren();
    partnerAuditEntries(state).slice(0,8).forEach(event=>{
      const name=providers.find(p=>String(p.id)===event.targetId)?.name||'Mitra katalog';
      const li=el('li','border-b border-outline-variant/30 py-3 last:border-0');
      li.append(el('p','text-xs font-bold text-on-surface',`${name} · ${statusNames[event.next]||event.next}`),el('p','mt-1 text-xs text-on-surface-variant',event.reason),el('p','mt-1 text-[11px] text-outline',dateTime.format(event.at)));
      logs.append(li);
    });
    $('[data-partner-logs-empty]').classList.toggle('hidden',partnerAuditEntries(state).length>0);
  }
  $('[data-partner-search]').addEventListener('input',e=>{filter.q=e.target.value;render();});
  $('[data-partner-status]').addEventListener('change',e=>{filter.status=e.target.value;render();});
  $('[data-partner-category]').addEventListener('change',e=>{filter.category=e.target.value;render();});
  $('[data-partner-reset]').addEventListener('click',()=>{
    filter={q:'',status:'all',category:'all'};
    $('[data-partner-search]').value='';$('[data-partner-status]').value='all';$('[data-partner-category]').value='all';render();
  });
  $('[data-partner-action-form]').addEventListener('submit',e=>{
    e.preventDefault();if(!pending)return;
    try{setPartnerVerification(pending.id,pending.next,$('[data-partner-reason]').value,providers);action.close();notify('Status katalog demo diperbarui; belum ada pemeriksaan dokumen atau verifikasi nyata.');render();}
    catch(err){$('[data-partner-action-error]').textContent=err.message;}
  });
  root.querySelectorAll('[data-admin-close]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog')?.close()));
  window.addEventListener('storage',e=>{if([ADMIN_PARTNER_KEY,ORDER_KEY,'jasawarga:prototype-partner-profiles:v1','jasawarga:prototype-services:v1',ADMIN_CATEGORIES_KEY,null].includes(e.key))render();});
  window.addEventListener(ADMIN_MANAGEMENT_EVENT,render);
  window.addEventListener(ADMIN_CATEGORIES_EVENT,render);
  root.querySelectorAll('[data-admin-soon]').forEach(button => button.addEventListener('click', () => notify(`${button.dataset.adminSoon || 'Modul'} belum tersedia di prototipe ini. Silakan gunakan menu Manajemen Warga atau Mitra.`)));
  render();
}
