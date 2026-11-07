import { ADMIN_CATEGORIES_KEY, ADMIN_CATEGORIES_EVENT, CATEGORY_ICONS, CATEGORY_COLORS,
  readCategories, slugify, createCategory, editCategory, toggleCategory, categoryUsage, deleteCategory,
} from './admin-category-store.js';
import { SERVICES_KEY, SERVICES_EVENT } from './services-store.js';

const root = document.querySelector('#jw-admin-categories');
if (root) {
  const $ = selector => root.querySelector(selector);
  const $$ = selector => [...root.querySelectorAll(selector)];
  const seed = JSON.parse($('[data-category-seed]').textContent);
  const seeded = seed.categories || [], providers = seed.providers || [], profiles = seed.profiles || {};
  const formDialog = $('[data-form-dialog]'), form = $('[data-form]');
  const confirmDialog = $('[data-confirm-dialog]');
  let editing = null, removing = null, noticeTimer;
  const el = (tag,classes='',value='') => { const node = document.createElement(tag);node.className=classes;node.textContent=String(value);return node; };
  const notify = (message,fail=false) => {
    const box = $('[data-notice]');box.textContent=message;
    box.className=`rounded-xl border px-4 py-3 text-sm font-semibold ${fail?'border-rose-200 bg-rose-50 text-rose-800':'border-blue-200 bg-blue-50 text-primary'}`;
    clearTimeout(noticeTimer);noticeTimer=setTimeout(()=>box.classList.add('hidden'),5000);
  };
  CATEGORY_ICONS.forEach(icon=>{ const option=new Option(icon.replaceAll('_',' '),icon);form.elements.namedItem('icon').add(option); });
  CATEGORY_COLORS.forEach((pair,index)=>{ const option=new Option(`Palet ${index+1} · ${pair[0]}`,String(index));form.elements.namedItem('colorIndex').add(option); });
  function getColorIndex(item) { return Math.max(0,CATEGORY_COLORS.findIndex(pair=>pair[0]===item.bg&&pair[1]===item.fg)); }
  function render() {
    const categories = readCategories(seeded);
    const query = $('[data-search]').value.toLocaleLowerCase('id-ID').trim();
    const filter = $('[data-filter]').value;
    const used = categories.reduce((sum,cat)=>sum+categoryUsage(cat.slug,providers,profiles).services,0);
    for (const [key,n] of Object.entries({total:categories.length,active:categories.filter(x=>x.active).length,inactive:categories.filter(x=>!x.active).length,services:used})) $(`[data-kpi="${key}"]`).textContent=n;
    const visible = categories.filter(item => (filter==='all'||(filter==='active')===item.active) && (!query || `${item.name} ${item.slug} ${item.description}`.toLocaleLowerCase('id-ID').includes(query)));
    $('[data-count]').textContent=visible.length;
    const rows = $('[data-rows]');rows.replaceChildren();
    $('[data-empty]').classList.toggle('hidden',visible.length>0);
    visible.forEach(item=>{
      const usage=categoryUsage(item.slug,providers,profiles);
      const tr=el('tr','hover:bg-surface-container-low/40');
      const ident=el('td','px-4 py-4');
      const flex=el('div','flex items-center gap-3');
      const icon=el('span','material-symbols-outlined flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[25px]',item.icon);
      icon.style.backgroundColor=item.bg;icon.style.color=item.fg;
      const desc=el('div','min-w-0');desc.append(el('p','font-extrabold',item.name),el('p','mt-1 max-w-[230px] text-xs text-outline',item.description));
      flex.append(icon,desc);ident.append(flex);
      const slug=el('td','px-4 py-4 text-xs font-semibold text-on-surface-variant',item.slug);
      const usageCell=el('td','px-4 py-4 text-xs text-on-surface-variant',`${usage.providers} Mitra · ${usage.services} layanan`);
      const status=el('td','px-4 py-4');status.append(el('span',`rounded-full px-2.5 py-1 text-[11px] font-bold ${item.active?'bg-emerald-50 text-emerald-800':'bg-slate-100 text-slate-600'}`,item.active?'Aktif':'Nonaktif'));
      const actions=el('td','px-4 py-4 text-right');const wrap=el('div','flex flex-wrap justify-end gap-1.5');
      [['Edit','edit'],[item.active?'Nonaktifkan':'Aktifkan','toggle'],['Hapus','delete']].forEach(([label,act])=>{
        const btn=el('button',`rounded-lg border border-outline-variant/60 px-2.5 py-2 text-[11px] font-bold ${act==='delete'?'text-rose-700':'text-primary'} hover:bg-surface-container-low`,label);
        btn.type='button';btn.dataset.action=act;btn.dataset.slug=item.slug;
        if (act==='delete'&&usage.total>0) {btn.disabled=true;btn.title='Masih digunakan Mitra atau layanan';btn.classList.add('cursor-not-allowed','opacity-40');}
        wrap.append(btn);
      });actions.append(wrap);tr.append(ident,slug,usageCell,status,actions);rows.append(tr);
    });
  }
  function openForm(slug=null) {
    editing=slug;form.reset();$('[data-form-error]').classList.add('hidden');
    const item=readCategories(seeded).find(item=>item.slug===slug);
    $('[data-form-title]').textContent=item?'Edit kategori':'Tambah kategori';
    for (const field of ['name','slug','description','icon']) form.elements.namedItem(field).value=item?.[field]||'';
    form.elements.namedItem('colorIndex').value=String(item?getColorIndex(item):0);
    form.elements.namedItem('slug').readOnly=Boolean(item);
    form.elements.namedItem('slug').classList.toggle('bg-surface-container-low',Boolean(item));
    if (!item) form.elements.namedItem('icon').value='handyman';
    formDialog.showModal();form.elements.namedItem('name').focus();
  }
  $('[data-add]').addEventListener('click',()=>openForm());
  $('[data-search]').addEventListener('input',render);
  $('[data-filter]').addEventListener('change',render);
  $('[data-reset]').addEventListener('click',()=>{ $('[data-search]').value='';$('[data-filter]').value='all';render(); });
  form.elements.namedItem('name').addEventListener('input',()=>{
    if (!editing) form.elements.namedItem('slug').value=slugify(form.elements.namedItem('name').value);
  });
  $('[data-rows]').addEventListener('click',event=>{
    const button=event.target.closest('[data-action]');if(!button)return;
    const slug=button.dataset.slug;const item=readCategories(seeded).find(item=>item.slug===slug);
    if(!item)return render();
    if(button.dataset.action==='edit')return openForm(slug);
    if(button.dataset.action==='toggle') {
      try{toggleCategory(seeded,slug,!item.active);render();notify(item.active?'Kategori dinonaktifkan. Layanan terkait tidak tersedia untuk booking baru.':'Kategori diaktifkan kembali.');}catch(error){notify(error.message,true)}return;
    }
    const usage=categoryUsage(slug,providers,profiles);
    if(usage.total)return notify('Kategori masih digunakan. Nonaktifkan sebagai alternatif.',true);
    removing=slug;$('[data-confirm-text]').textContent=`Kategori “${item.name}” akan dihapus dari katalog lokal. Tindakan ini tidak bisa dibatalkan.`;confirmDialog.showModal();
  });
  $('[data-confirm-delete]').addEventListener('click',()=>{
    try{deleteCategory(seeded,removing,providers,profiles);confirmDialog.close();removing=null;render();notify('Kategori berhasil dihapus dari browser ini.');}catch(error){notify(error.message,true)}
  });
  form.addEventListener('submit',event=>{
    event.preventDefault();if(!form.reportValidity())return;
    try{const fields=Object.fromEntries(new FormData(form));editing?editCategory(seeded,editing,fields):createCategory(seeded,fields);formDialog.close();render();notify(editing?'Kategori berhasil diperbarui.':'Kategori baru berhasil ditambahkan.');}
    catch(error){$('[data-form-error]').textContent=error.message;$('[data-form-error]').classList.remove('hidden');}
  });
  $$('[data-close]').forEach(button=>button.addEventListener('click',()=>button.closest('dialog').close()));
  $$('[data-refresh]').forEach(button=>button.addEventListener('click',render));
  [ADMIN_CATEGORIES_EVENT,SERVICES_EVENT].forEach(ev=>window.addEventListener(ev,render));
  window.addEventListener('storage',event=>{if([ADMIN_CATEGORIES_KEY,SERVICES_KEY,null].includes(event.key))render()});
  render();
}
