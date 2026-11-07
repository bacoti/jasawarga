import { readOrders, ORDER_KEY, ORDER_LABELS } from './orders-store.js';
import { filterAdminOrders } from './admin-overview-data.js';

const root=document.querySelector('#jw-admin-orders');
if(root){
  const $=selector=>root.querySelector(selector);
  const el=(tag,classes='',content='')=>{const node=document.createElement(tag);node.className=classes;node.textContent=String(content);return node};
  const idr=new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0});
  const dt=new Intl.DateTimeFormat('id-ID',{dateStyle:'medium',timeStyle:'short',timeZone:'Asia/Jakarta'});
  const statusClasses={pending:'bg-amber-50 text-amber-800',accepted:'bg-blue-50 text-blue-800',enroute:'bg-blue-50 text-blue-800',working:'bg-indigo-50 text-indigo-800',completed:'bg-emerald-50 text-emerald-800',cancelled:'bg-slate-100 text-slate-600',rejected:'bg-rose-50 text-rose-700'};
  let displayed=[];
  const format=v=>Number.isFinite(v)?`${dt.format(new Date(v))} WIB`:'—';
  const status=v=>ORDER_LABELS[v]||'Tidak diketahui';
  function notice(message,isError=false){const box=$('[data-notice]');box.textContent=message;box.className=`rounded-xl border px-4 py-3 text-sm font-semibold ${isError?'border-rose-200 bg-rose-50 text-rose-700':'border-blue-200 bg-blue-50 text-primary'}`;}
  function filtered(all){
    const base=filterAdminOrders(all,{query:$('[data-search]').value,status:$('[data-status]').value});
    const provider=$('[data-provider]').value;
    const period=Number($('[data-period]').value)||0;
    const since=period?Date.now()-period*86400000:0;
    return base.filter(item=>(provider==='all'||String(item.providerId)===provider)&&(!since||item.createdAt>=since));
  }
  function render(){
    const all=readOrders();
    const providers=new Map(all.map(item=>[String(item.providerId),item.provider]));
    const providerSelect=$('[data-provider]'), keep=providerSelect.value;
    providerSelect.replaceChildren(new Option('Semua Mitra','all'));
    [...providers].sort((a,b)=>a[1].localeCompare(b[1],'id-ID')).forEach(([id,name])=>providerSelect.add(new Option(name,id)));
    providerSelect.value=[...providerSelect.options].some(opt=>opt.value===keep)?keep:'all';
    const count=key=>all.filter(order=>key==='active'?['accepted','enroute','working'].includes(order.status):order.status===key).length;
    for(const [name,value] of Object.entries({total:all.length,pending:count('pending'),active:count('active'),completed:count('completed')}))$(`[data-kpi="${name}"]`).textContent=value;
    $('[data-terminal]').textContent=count('cancelled')+count('rejected');
    $('[data-tariff]').textContent=idr.format(all.filter(o=>o.status==='completed').reduce((sum,o)=>sum+o.price,0));
    const dist=$('[data-distribution]');dist.replaceChildren();
    Object.keys(ORDER_LABELS).forEach(key=>{
      const n=count(key),percent=all.length?Math.round(n/all.length*100):0;
      const wrap=el('div','space-y-1.5');const head=el('div','flex justify-between gap-2 text-xs');head.append(el('span','text-on-surface-variant',status(key)),el('strong','',`${n} · ${percent}%`));
      const track=el('div','h-2 overflow-hidden rounded-full bg-surface-container-low');const bar=el('div','h-full rounded-full bg-primary');bar.style.width=`${percent}%`;track.append(bar);wrap.append(head,track);dist.append(wrap);
    });
    displayed=filtered(all);$('[data-count]').textContent=displayed.length;
    $('[data-empty]').classList.toggle('hidden',displayed.length>0);
    const body=$('[data-rows]');body.replaceChildren();
    displayed.forEach(order=>{
      const tr=el('tr','hover:bg-surface-container-low/40');
      const reference=el('td','px-4 py-3 align-top');reference.append(el('p','text-xs font-extrabold text-primary',order.reference),el('p','mt-1 text-[11px] text-outline',format(order.createdAt)));
      const service=el('td','px-4 py-3 align-top');service.append(el('p','max-w-[210px] text-sm font-bold',order.service),el('p','mt-1 text-xs text-outline',order.provider));
      const visit=el('td','px-4 py-3 align-top text-xs text-on-surface-variant',`${order.date} · ${order.time} WIB`);
      const amount=el('td','px-4 py-3 align-top text-xs font-semibold',idr.format(order.price));
      const label=el('td','px-4 py-3 align-top');label.append(el('span',`rounded-full px-2.5 py-1 text-[11px] font-bold ${statusClasses[order.status]||'bg-slate-100'}`,status(order.status)));
      const action=el('td','px-4 py-3 align-top');const btn=el('button','rounded-lg border border-outline-variant/60 px-3 py-2 text-[11px] font-bold text-primary hover:bg-primary-fixed/35','Lihat detail');btn.type='button';btn.dataset.reference=order.reference;action.append(btn);
      tr.append(reference,service,visit,amount,label,action);body.append(tr);
    });
    $('[data-notice]').classList.add('hidden');
  }
  const dialog=$('[data-detail-dialog]');
  $('[data-rows]').addEventListener('click',event=>{
    const btn=event.target.closest('[data-reference]');if(!btn)return;
    const item=readOrders().find(order=>order.reference===btn.dataset.reference);
    if(!item){render();return notice('Pesanan sudah tidak ada dalam browser.',true)}
    $('[data-detail-heading]').textContent=item.reference;
    $('[data-detail-subhead]').textContent=`${item.service} · ${item.provider}`;
    const info=$('[data-detail-info]');info.replaceChildren();
    [ ['Status',status(item.status)],['Waktu kunjungan',`${item.date} · ${item.time} WIB`],['Area (tanpa alamat)',item.area||'—'],['Estimasi tarif',idr.format(item.price)],['Pelanggan','Identitas disembunyikan pada tampilan Admin'],['Akun Warga',item.accountId?'Tertaut ke akun lokal':'Tidak tertaut / data lama']].forEach(([key,value])=>{const row=el('div','flex flex-wrap justify-between gap-2 border-b border-outline-variant/30 pb-2 last:border-0');row.append(el('span','text-outline',key),el('strong','max-w-[300px] break-words text-right',value));info.append(row)});
    const hist=$('[data-detail-history]');hist.replaceChildren();
    const history=Array.isArray(item.history)?[...item.history]:[];
    history.sort((a,b)=>Number(a.at)-Number(b.at));
    history.forEach(step=>{const li=el('li','border-l-2 border-primary/40 py-1 pl-3');li.append(el('p','text-sm font-bold',status(step.status)),el('p','mt-1 text-xs text-outline',format(step.at)));if(step.reason)li.append(el('p','mt-1 text-xs text-on-surface-variant',step.reason));hist.append(li);});
    if(!history.length)hist.append(el('li','text-xs text-outline','Belum ada riwayat status.'));
    dialog.showModal();
  });
  $$('');
  function $$(selector){return [...root.querySelectorAll(selector)]}
  $$('[data-close]').forEach(btn=>btn.addEventListener('click',()=>dialog.close()));
  $('[data-reset]').addEventListener('click',()=>{for(const attr of ['data-search','data-status','data-provider','data-period'])$("["+attr+"]").value=attr==='data-search'?'':'all';render()});
  ['data-search','data-status','data-provider','data-period'].forEach(attr=>$('['+attr+']').addEventListener(attr==='data-search'?'input':'change',render));
  $$('[data-refresh]').forEach(btn=>btn.addEventListener('click',render));
  $('[data-export]').addEventListener('click',()=>{
    if(!displayed.length)return notice('Tidak ada pesanan sesuai filter untuk diekspor.',true);
    const csvValue=v=>'"'+String(v??'').replaceAll('"','""').replace(/^[=+\-@]/,"'$&")+'"';
    const header=['referensi','mitra','layanan','jadwal','status','estimasi_tarif'];
    const lines=[header,...displayed.map(o=>[o.reference,o.provider,o.service,`${o.date} ${o.time}`,status(o.status),o.price])];
    const csv='\uFEFF'+lines.map(line=>line.map(csvValue).join(',')).join('\r\n');
    const link=document.createElement('a');const url=URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8'}));
    link.href=url;link.download='jasawarga-pesanan-demo.csv';link.click();setTimeout(()=>URL.revokeObjectURL(url),1000);
    notice('CSV tanpa nama, nomor telepon, dan alamat berhasil dibuat dari pesanan lokal.');
  });
  window.addEventListener('storage',event=>{if(event.key===ORDER_KEY||event.key===null)render()});
  window.addEventListener('jasawarga:orders-updated',render);
  render();
}
