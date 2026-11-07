import { readReviews, REVIEW_KEY, REVIEW_EVENT } from './reviews-store.js';
import { readOrders, ORDER_KEY } from './orders-store.js';
import { getPartnerReply, PARTNER_REPLIES_KEY } from './partner-review-replies-store.js';
import { ADMIN_FEEDBACK_KEY, ADMIN_FEEDBACK_EVENT, CASE_LABELS, CASE_TOPICS,
  readAdminFeedback, reviewStatus, changeReviewStatus, createComplaint, changeComplaintStatus, filterComplaints,
} from './admin-feedback-store.js';

const root=document.querySelector('#jw-admin-reviews');
if(root){
  const $=selector=>root.querySelector(selector);
  const $$=selector=>[...root.querySelectorAll(selector)];
  const el=(tag,classes='',text='')=>{const node=document.createElement(tag);node.className=classes;node.textContent=String(text);return node;};
  const fmt=new Intl.DateTimeFormat('id-ID',{dateStyle:'medium',timeStyle:'short',timeZone:'Asia/Jakarta'});
  const time=n=>Number.isFinite(n)?`${fmt.format(new Date(n))} WIB`:'—';
  const caseDialog=$('[data-case-form-dialog]'),caseForm=$('[data-case-form]');
  const actionDialog=$('[data-action-dialog]'),actionForm=$('[data-action-form]');
  let action=null,noticeTimer;
  function notice(message,failed=false){const box=$('[data-notice]');box.textContent=message;box.className=`rounded-xl border px-4 py-3 text-sm font-semibold ${failed?'border-rose-200 bg-rose-50 text-rose-800':'border-blue-200 bg-blue-50 text-primary'}`;clearTimeout(noticeTimer);noticeTimer=setTimeout(()=>box.classList.add('hidden'),6000);}
  const badge=(text,klass='bg-primary-fixed text-primary')=>el('span',`inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ${klass}`,text);
  function render(){
    const reviews=readReviews();const orders=readOrders();const state=readAdminFeedback();
    const hidden=reviews.filter(r=>reviewStatus(r.reference,state)==='hidden').length;
    for(const [key,value] of Object.entries({reviews:reviews.length,hidden,open:state.cases.filter(c=>['open','investigating'].includes(c.status)).length,resolved:state.cases.filter(c=>['resolved','closed'].includes(c.status)).length}))$(`[data-kpi="${key}"]`).textContent=value;
    renderReviews(reviews,state);renderCases(state);renderAudit(state);updateReferenceOptions(orders,reviews);
  }
  function renderReviews(reviews,state){
    const query=$('[data-review-search]').value.trim().toLocaleLowerCase('id-ID');
    const status=$('[data-review-filter]').value;
    const list=reviews.filter(r=>(status==='all'||reviewStatus(r.reference,state)===status)&&(!query||[r.reference,r.provider,r.service,r.comment].some(v=>String(v).toLocaleLowerCase('id-ID').includes(query))));
    const mount=$('[data-review-list]');mount.replaceChildren();$('[data-review-empty]').classList.toggle('hidden',list.length>0);
    list.forEach(review=>{
      const isHidden=reviewStatus(review.reference,state)==='hidden';
      const article=el('article','space-y-3 p-4 sm:p-5');
      const top=el('div','flex flex-wrap items-start justify-between gap-3');
      const title=el('div','min-w-0');
      title.append(el('p','text-sm font-extrabold',review.provider),el('p','mt-1 text-xs text-outline',`${review.service} · ${review.reference}`),el('p','mt-1 text-[11px] text-outline',time(review.createdAt)));
      const chips=el('div','flex flex-wrap items-center gap-2');chips.append(badge(`${review.rating}/5 ★`,'bg-amber-50 text-amber-800'),badge(isHidden?'Disembunyikan':'Ditampilkan',isHidden?'bg-rose-50 text-rose-800':'bg-emerald-50 text-emerald-800'));
      top.append(title,chips);
      const body=el('p','whitespace-pre-wrap break-words text-sm leading-relaxed text-on-surface-variant',review.comment);
      const aspects=el('p','text-xs text-outline',`Kualitas ${review.details.quality}/5 · Tepat waktu ${review.details.punctuality}/5 · Keramahan ${review.details.friendliness}/5`);
      const bottom=el('div','flex flex-wrap items-center justify-between gap-3');
      const text=el('span','text-xs text-on-surface-variant',getPartnerReply(review.reference,review.providerId)?'Mitra sudah membalas':'Belum ada balasan Mitra');
      const button=el('button',`rounded-xl border px-3 py-2 text-xs font-bold ${isHidden?'border-primary/40 text-primary':'border-rose-200 text-rose-700'}`,isHidden?'Tampilkan ulang':'Sembunyikan ulasan');
      button.type='button';button.dataset.review=review.reference;button.dataset.target=isHidden?'visible':'hidden';bottom.append(text,button);
      article.append(top,body,aspects);
      if(review.photos.length){const photos=el('div','flex flex-wrap gap-2');review.photos.forEach((src,i)=>{const photo=document.createElement('img');photo.src=src;photo.alt=`Foto ulasan ${i+1} dari ${review.provider}`;photo.className='h-16 w-16 rounded-lg border border-outline-variant/40 object-cover';photo.loading='lazy';photos.append(photo)});article.append(photos)}
      article.append(bottom);mount.append(article);
    });
  }
  function renderCases(state){
    const cases=filterComplaints(state.cases,{query:$('[data-case-search]').value,status:$('[data-case-status]').value,priority:$('[data-case-priority]').value});
    $('[data-case-count]').textContent=cases.length;
    const mount=$('[data-case-list]');mount.replaceChildren();$('[data-case-empty]').classList.toggle('hidden',cases.length>0);
    cases.forEach(item=>{
      const article=el('article','space-y-3 p-4 sm:p-5');
      const head=el('div','flex flex-wrap items-start justify-between gap-3');
      const ident=el('div','min-w-0');ident.append(el('p','text-[11px] font-bold text-primary',item.id),el('h4','mt-1 text-sm font-extrabold',item.subject),el('p','mt-1 text-xs text-outline',`${CASE_TOPICS[item.topic]} · ${time(item.createdAt)}${item.reference?' · '+item.reference:''}`));
      const labels=el('div','flex flex-wrap gap-2');labels.append(badge(CASE_LABELS[item.status],['closed','resolved'].includes(item.status)?'bg-emerald-50 text-emerald-800':'bg-blue-50 text-primary'),badge(item.priority==='high'?'Prioritas tinggi':item.priority==='normal'?'Normal':'Rendah',item.priority==='high'?'bg-rose-50 text-rose-800':'bg-slate-100 text-slate-700'));
      head.append(ident,labels);article.append(head,el('p','whitespace-pre-wrap break-words text-sm text-on-surface-variant',item.description));
      const footer=el('div','flex flex-wrap items-center justify-between gap-3');
      footer.append(el('span','text-[11px] text-outline',`${item.history.length} catatan · Terakhir ${time(item.updatedAt)}`));
      const buttons=el('div','flex flex-wrap gap-2');
      const allowed={open:[['investigating','Mulai tangani'],['closed','Tutup']],investigating:[['resolved','Tandai selesai'],['closed','Tutup']],resolved:[['investigating','Buka penanganan'],['closed','Tutup']],closed:[['open','Buka kembali']]};
      (allowed[item.status]||[]).forEach(([target,label])=>{const b=el('button','rounded-lg border border-outline-variant/60 px-3 py-2 text-[11px] font-bold text-primary hover:bg-primary-fixed/35',label);b.type='button';b.dataset.caseId=item.id;b.dataset.caseTarget=target;buttons.append(b)});
      footer.append(buttons);article.append(footer);
      const details=document.createElement('details');details.className='rounded-lg bg-surface-container-low px-3 py-2 text-xs';const sum=el('summary','cursor-pointer font-bold','Lihat kronologi penanganan');details.append(sum);
      const history=el('ol','mt-3 space-y-2');item.history.forEach(log=>{history.append(el('li','break-words border-l-2 border-primary/30 pl-3 text-on-surface-variant',`${time(log.at)} · ${log.action} · ${log.reason}`))});details.append(history);article.append(details);
      mount.append(article);
    });
  }
  function renderAudit(state){
    const audit=$('[data-audit]');audit.replaceChildren();const visible=state.audit.slice(0,12);
    $('[data-audit-empty]').classList.toggle('hidden',visible.length>0);
    visible.forEach(item=>{const li=el('li','rounded-xl bg-surface-container-low p-3');li.append(el('p','text-xs font-extrabold',item.action.replace('review:','Ulasan · ').replace('case:','Kasus · ')),el('p','mt-1 break-all text-[11px] text-outline',item.target||'—'),el('p','mt-1 text-xs text-on-surface-variant',item.reason),el('p','mt-1 text-[11px] text-outline',time(item.at)));audit.append(li)});
  }
  function updateReferenceOptions(orders,reviews){
    const dropdown=caseForm.elements.namedItem('reference');const previous=dropdown.value;
    dropdown.replaceChildren(new Option('Tanpa tautan pesanan',''));
    const refs=new Map([...orders,...reviews].filter(x=>x.reference).map(x=>[x.reference,x.reference]));
    refs.forEach(ref=>dropdown.add(new Option(ref,ref)));
    dropdown.value=[...dropdown.options].some(option=>option.value===previous)?previous:'';
  }
  function openAction(kind,id,target){
    action={kind,id,target};actionForm.reset();$('[data-action-error]').classList.add('hidden');
    const labels=kind==='review'?['Moderasi ulasan',target==='hidden'?'Ulasan akan disembunyikan dari profil publik Mitra. Komentar Warga tetap tersimpan dan terlihat di riwayat pemilik.':'Ulasan akan ditampilkan kembali di profil publik Mitra.']
      :['Perbarui status kasus',`Status akan diubah menjadi “${CASE_LABELS[target]}”. Catatan wajib tercatat pada kronologi.`];
    $('[data-action-title]').textContent=labels[0];$('[data-action-desc]').textContent=labels[1];actionDialog.showModal();$('[data-action-reason]').focus();
  }
  $('[data-review-list]').addEventListener('click',event=>{const b=event.target.closest('[data-review]');if(b)openAction('review',b.dataset.review,b.dataset.target)});
  $('[data-case-list]').addEventListener('click',event=>{const b=event.target.closest('[data-case-id]');if(b)openAction('case',b.dataset.caseId,b.dataset.caseTarget)});
  actionForm.addEventListener('submit',event=>{
    event.preventDefault();if(!actionForm.reportValidity()||!action)return;
    try{const reason=$('[data-action-reason]').value;
      if(action.kind==='review')changeReviewStatus(action.id,action.target,reason,readReviews());
      else changeComplaintStatus(action.id,action.target,reason);
      actionDialog.close();render();notice('Tindakan Admin demo tersimpan.');}
    catch(error){$('[data-action-error]').textContent=error.message;$('[data-action-error]').classList.remove('hidden');}
  });
  $$('[data-add-case]').forEach(button=>button.addEventListener('click',()=>{caseForm.reset();$('[data-case-form-error]').classList.add('hidden');updateReferenceOptions(readOrders(),readReviews());caseDialog.showModal()}));
  caseForm.addEventListener('submit',event=>{
    event.preventDefault();if(!caseForm.reportValidity())return;
    try{createComplaint(Object.fromEntries(new FormData(caseForm)),readOrders(),readReviews());caseDialog.close();switchTab('cases');render();notice('Kasus fiktif tersimpan di browser ini.');}
    catch(error){$('[data-case-form-error]').textContent=error.message;$('[data-case-form-error]').classList.remove('hidden');}
  });
  function switchTab(name){$$('[data-tab]').forEach(btn=>{const active=btn.dataset.tab===name;btn.setAttribute('aria-selected',String(active));btn.className=`rounded-t-xl px-5 py-3 text-xs font-bold ${active?'bg-primary text-white':'bg-white text-on-surface-variant'}`;});$$('[data-pane]').forEach(p=>p.classList.toggle('hidden',p.dataset.pane!==name))}
  $$('[data-tab]').forEach(btn=>btn.addEventListener('click',()=>switchTab(btn.dataset.tab)));
  $('[data-review-search]').addEventListener('input',render);$('[data-review-filter]').addEventListener('change',render);
  $('[data-review-reset]').addEventListener('click',()=>{$('[data-review-search]').value='';$('[data-review-filter]').value='all';render()});
  $('[data-case-search]').addEventListener('input',render);$('[data-case-status]').addEventListener('change',render);$('[data-case-priority]').addEventListener('change',render);
  $$('[data-refresh]').forEach(button=>button.addEventListener('click',render));
  $$('[data-close]').forEach(btn=>btn.addEventListener('click',()=>btn.closest('dialog').close()));
  window.addEventListener('storage',event=>{if([ADMIN_FEEDBACK_KEY,REVIEW_KEY,ORDER_KEY,PARTNER_REPLIES_KEY,null].includes(event.key))render()});
  [ADMIN_FEEDBACK_EVENT,REVIEW_EVENT,'jasawarga:orders-updated'].forEach(ev=>window.addEventListener(ev,render));
  render();
}
