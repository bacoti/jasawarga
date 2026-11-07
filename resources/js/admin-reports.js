/** Laporan & Analitik Admin: hanya baca localStorage, tidak mengirim data ke server. */
import { readOrders, ORDER_KEY } from './orders-store.js';
import { readReviews, REVIEW_KEY } from './reviews-store.js';
import { readAccounts, ACCOUNT_KEY } from './account-store.js';
import { readAdminFeedback, ADMIN_FEEDBACK_KEY, ADMIN_FEEDBACK_EVENT } from './admin-feedback-store.js';
import { readCategories, ADMIN_CATEGORIES_KEY, ADMIN_CATEGORIES_EVENT } from './admin-category-store.js';
import { readCitizenModeration, readPartnerModeration, ADMIN_CITIZEN_KEY, ADMIN_PARTNER_KEY, ADMIN_MANAGEMENT_EVENT } from './admin-management-store.js';
import { activeServices, SERVICES_KEY } from './services-store.js';
import { readAdminSettings, ADMIN_SETTINGS_KEY, ADMIN_SETTINGS_EVENT } from './admin-settings-store.js';
import { buildAdminReport, validatePeriod, makeReportCsv, REPORT_STATUS_LABELS } from './admin-reports-data.js';

const root=document.getElementById('jw-admin-reports');
if(root){
  const $ = selector=>root.querySelector(selector);
  const $$ = selector=>[...root.querySelectorAll(selector)];
  const el=(tag,cls,text)=>{const result=document.createElement(tag);result.className=cls||'';if(text!==undefined)result.textContent=String(text);return result;};
  const formatCount = value=>Number(value||0).toLocaleString('id-ID');
  const formatMoney = value=>new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(Number(value)||0);
  const formatDate=value=>new Intl.DateTimeFormat('id-ID',{day:'2-digit',month:'short',year:'numeric'}).format(new Date(`${value}T12:00:00`));
  let seed={providers:[],profiles:{},categories:[]};
  try{seed=JSON.parse($('#jw-admin-reports-seed').textContent);}catch(_){/* Source empty. */}
  const providers=Array.isArray(seed.providers)?seed.providers:[];
  const profiles=seed.profiles&&typeof seed.profiles==='object'?seed.profiles:{};
  const categories=Array.isArray(seed.categories)?seed.categories:[];
  const form=$('[data-ar-filter-form]');
  const periodInput=$('[data-ar-period]'),fromInput=$('[data-ar-from]'),toInput=$('[data-ar-to]');
  const today=()=>{const d=new Date();return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;};
  fromInput.max=today();toInput.max=today();
  const previous=new Date();previous.setDate(previous.getDate()-29);
  fromInput.value=`${previous.getFullYear()}-${String(previous.getMonth()+1).padStart(2,'0')}-${String(previous.getDate()).padStart(2,'0')}`;
  toInput.value=today();
  let selection={period:readAdminSettings().preferences.defaultPeriod};
  periodInput.value=selection.period;
  let chartMode='created', currentReport=null;
  const storageKeys=[ORDER_KEY,REVIEW_KEY,ACCOUNT_KEY,ADMIN_FEEDBACK_KEY,ADMIN_CATEGORIES_KEY,ADMIN_CITIZEN_KEY,ADMIN_PARTNER_KEY,SERVICES_KEY,ADMIN_SETTINGS_KEY];
  function notice(message,error=false){const node=$('[data-ar-notice]');node.textContent=message;node.className=`rounded-xl border px-4 py-3 text-sm font-semibold ${error?'border-rose-200 bg-rose-50 text-rose-800':'border-blue-200 bg-blue-50 text-blue-900'}`;}
  function set(selector,text){const node=$(selector);if(node)node.textContent=String(text);}
  function toggleDateInputs(){const active=periodInput.value==='custom';fromInput.disabled=!active;toInput.disabled=!active;}
  function sourceData(){
    const effectiveCategories=readCategories(categories);
    return {orders:readOrders(),reviews:readReviews(),accounts:readAccounts(),providers,categories:effectiveCategories,
      feedback:readAdminFeedback(),citizenAudit:readCitizenModeration(),partnerAudit:readPartnerModeration(),
      serviceCounts:providers.map(p=>({id:p.id,activeCount:activeServices(p.id,profiles[String(p.id)]?.services||[],p.category,effectiveCategories).length}))};
  }
  function simpleTable(selector,items,emptySelector){
    const node=$(selector);node.replaceChildren();
    $(emptySelector).classList.toggle('hidden',items.length>0);
    const compact=readAdminSettings().preferences.compactTables;
    items.forEach(item=>{
      const row=el('tr','hover:bg-[#F7F9FC]');
      const label=el('td',`px-5 ${compact?'py-2':'py-3.5'} font-bold text-on-surface`,item.label);
      const count=el('td',`px-5 ${compact?'py-2':'py-3.5'} text-right tabular-nums`,formatCount(item.orders));
      const done=el('td',`px-5 ${compact?'py-2':'py-3.5'} text-right tabular-nums`,formatCount(item.completed));
      row.append(label,count,done);node.appendChild(row);
    });
  }
  function renderChart(report){
    const container=$('[data-ar-chart]');container.replaceChildren();
    $$('[data-ar-chart-mode]').forEach(button=>{const active=button.dataset.arChartMode===chartMode;button.setAttribute('aria-pressed',String(active));button.classList.toggle('bg-primary',active);button.classList.toggle('text-white',active);button.classList.toggle('text-on-surface-variant',!active);});
    const label={created:'Pesanan dibuat',completed:'Pekerjaan selesai',reviews:'Ulasan diberikan'}[chartMode];
    set('[data-ar-chart-note]',`${label} per hari · ${report.chart.length} hari kalender`);
    const daily=report.chart;
    // Batasi 32 batang; agregasi per 2+ hari pada rentang panjang.
    const size=Math.max(1,Math.ceil(daily.length/32));
    const blocks=[];
    for(let i=0;i<daily.length;i+=size){const items=daily.slice(i,i+size);blocks.push({from:items[0].date,to:items.at(-1).date,value:items.reduce((sum,v)=>sum+v[chartMode],0)});}
    const max=Math.max(1,...blocks.map(v=>v.value));
    blocks.forEach(block=>{
      const wrapper=el('div','flex h-full min-w-0 flex-1 flex-col justify-end');
      const bar=el('div',`w-full rounded-t-[3px] ${block.value?'bg-[#2477AC]':'bg-[#E1E8EF]'} transition-[height] duration-200`);
      bar.style.height=block.value?`${Math.max(3,(block.value/max)*100)}%`:'3px';
      wrapper.title=`${block.from}${block.to!==block.from?' s.d. '+block.to:''} · ${block.value} ${label.toLowerCase()}`;
      wrapper.setAttribute('aria-label',wrapper.title);wrapper.appendChild(bar);container.appendChild(wrapper);
    });
    set('[data-ar-axis-from]',daily.length?formatDate(daily[0].date):'—');
    set('[data-ar-axis-to]',daily.length?formatDate(daily.at(-1).date):'—');
  }
  function renderStatus(report){
    const container=$('[data-ar-status]');container.replaceChildren();
    $('[data-ar-status-empty]').classList.toggle('hidden',report.metrics.orders>0);
    Object.entries(REPORT_STATUS_LABELS).forEach(([status,label])=>{
      const count=report.statusCounts[status],percent=report.metrics.orders?Math.round(count/report.metrics.orders*100):0;
      const row=el('div','space-y-1.5');const head=el('div','flex justify-between gap-3 text-xs');
      head.append(el('span','font-semibold text-on-surface-variant',label),el('strong','tabular-nums',count));
      const track=el('div','h-[7px] overflow-hidden rounded-full bg-[#E9EEF4]');
      const fill=el('div',status==='completed'?'h-full rounded-full bg-emerald-600':status==='cancelled'||status==='rejected'?'h-full rounded-full bg-rose-500':'h-full rounded-full bg-[#2477AC]');
      fill.style.width=`${percent}%`;track.appendChild(fill);row.append(head,track);container.appendChild(row);
    });
  }
  function renderAudits(report){
    const list=$('[data-ar-audits]');list.replaceChildren();
    set('[data-ar-audit-count]',`${report.audits.length} catatan`);
    $('[data-ar-audits-empty]').classList.toggle('hidden',report.audits.length>0);
    report.audits.slice(0,8).forEach(event=>{
      const item=el('li','flex flex-wrap items-start justify-between gap-2 py-3.5');
      const body=el('div','min-w-0 flex-1');body.append(el('p','text-xs font-bold',event.source),el('p','mt-1 text-xs text-on-surface-variant',String(event.action||`${event.previous||'?'} → ${event.next||'?'}`).slice(0,120)));
      const date=el('time','text-[11px] text-outline',new Intl.DateTimeFormat('id-ID',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}).format(new Date(event.at)));
      item.append(body,date);list.appendChild(item);
    });
  }
  function render(){
    try{
      const report=buildAdminReport(sourceData(),selection);
      const options=readAdminSettings().preferences;
      currentReport=report;
      set('[data-ar-range]',`${report.bounds.from?formatDate(report.bounds.from):'Awal data lokal'} – ${formatDate(report.bounds.to)}`);
      const m=report.metrics;
      for(const [id,value] of Object.entries({orders:m.orders,completed:m.completed,reviews:m.reviews,complaints:m.complaints,accounts:m.newAccounts,rating:m.visibleReviews?`${m.averageRating.toFixed(1)} / 5`:'—',rate:`${m.completionRate}%`,tariff:options.showEstimatedTariff?formatMoney(m.estimatedCompletedTariff):'Disembunyikan'}))set(`[data-ar-kpi="${id}"]`,value);
      set('[data-ar-seed="providers"]',m.catalogProvidersDemo);
      set('[data-ar-seed="categories"]',m.catalogCategoriesDemo);
      set('[data-ar-seed="services"]',m.servicesDemo);
      set('[data-ar-detail="visible"]',m.visibleReviews);
      set('[data-ar-detail="hidden"]',m.reviews-m.visibleReviews);
      set('[data-ar-detail="complaintsOpen"]',m.complaintsOpen);
      set('[data-ar-detail="completedEvents"]',m.completedEvents);
      renderChart(report);renderStatus(report);
      simpleTable('[data-ar-providers]',report.topProviders,'[data-ar-providers-empty]');
      simpleTable('[data-ar-categories]',report.topCategories,'[data-ar-categories-empty]');
      renderAudits(report);
    }catch(error){notice(`Laporan gagal dihitung: ${error.message}`,true);}
  }
  periodInput.addEventListener('change',toggleDateInputs);
  form.addEventListener('submit',event=>{
    event.preventDefault();
    try{selection=validatePeriod({period:periodInput.value,from:fromInput.value,to:toInput.value});
      $('[data-ar-notice]').classList.add('hidden');render();}
    catch(error){notice(error.message,true);}
  });
  $$('[data-ar-chart-mode]').forEach(button=>button.addEventListener('click',()=>{chartMode=button.dataset.arChartMode;if(currentReport)renderChart(currentReport);}));
  $('[data-ar-refresh]').addEventListener('click',()=>{render();notice('Laporan diperbarui dari data browser saat ini.');});
  $$('[data-ar-export]').forEach(button=>button.addEventListener('click',()=>{
    try{
      if(!currentReport)throw new Error('Belum ada laporan yang dapat diekspor.');
      const kind=button.dataset.arExport;
      const csv=makeReportCsv(kind,currentReport,{includeEstimatedTariff:readAdminSettings().preferences.showEstimatedTariff});
      const blob=new Blob([csv],{type:'text/csv;charset=utf-8;'});
      const url=URL.createObjectURL(blob),anchor=document.createElement('a');
      anchor.href=url;anchor.download=`JasaWarga_Admin_${kind}_${currentReport.bounds.to}.csv`;
      document.body.appendChild(anchor);anchor.click();anchor.remove();window.setTimeout(()=>URL.revokeObjectURL(url),1000);
      notice('CSV laporan anonim siap diunduh. Tidak berisi kontak atau alamat pelanggan.');
    }catch(error){notice(error.message,true);}
  }));
  window.addEventListener('storage',event=>{if(event.key===null||storageKeys.includes(event.key))render();});
  ['jasawarga:orders-updated','jasawarga:reviews-updated','jasawarga:account-updated','jasawarga:services-updated',ADMIN_FEEDBACK_EVENT,ADMIN_CATEGORIES_EVENT,ADMIN_MANAGEMENT_EVENT,ADMIN_SETTINGS_EVENT].forEach(name=>window.addEventListener(name,render));
  toggleDateInputs();render();
}
