import {
  ACCOUNT_EVENT, readAccounts, ensureDemoAccount, currentAccount,
  demoSignIn, demoSignOut, registerAccount, updateProfile,
  saveAddress, removeAddress, setDefaultAddress, updatePreferences,
  deleteCurrentAccount, safeNext,
} from './account-store.js';
import { readOrders } from './orders-store.js';
import { readReviews } from './reviews-store.js';

// Frontend prototype. All account operations are local; no credential verification.
function initials(name) {
  return String(name||'JW').split(/\s+/).filter(Boolean).slice(0,2)
    .map(p=>p[0].toUpperCase()).join('')||'JW';
}
function avatarTo(node,account){
  if(!node)return;
  node.replaceChildren();
  if(account?.avatar){
    const img=document.createElement('img');img.src=account.avatar;
    img.alt='Foto profil simulasi';img.className='h-full w-full object-cover';node.append(img);
  }else node.textContent=initials(account?.name);
}
function refreshAccountNav(){
  if(document.documentElement.dataset.jwServerAuth === '1'){
    const html=document.documentElement;
    const name=html.dataset.jwServerName || 'Akun';
    const email=html.dataset.jwServerEmail || '';
    const role=html.dataset.jwServerRole;
    document.querySelectorAll('[data-jw-account-name]').forEach(el=>el.textContent=name);
    document.querySelectorAll('[data-jw-account-email]').forEach(el=>el.textContent=email);
    document.querySelectorAll('[data-jw-account-avatar]').forEach(el=>avatarTo(el,{name}));
    const path=role==='admin'?'/admin/dashboard':role==='partner'?'/mitra/portal':'/akun';
    document.querySelectorAll('[data-jw-account-link]').forEach(el=>el.href=path);
    return;
  }
  const a=currentAccount();
  document.querySelectorAll('[data-jw-account-name]').forEach(el=>el.textContent=a?.name||'Masuk / Daftar');
  document.querySelectorAll('[data-jw-account-email]').forEach(el=>el.textContent=a?.email||'Belum masuk');
  document.querySelectorAll('[data-jw-account-avatar]').forEach(el=>avatarTo(el,a));
  document.querySelectorAll('[data-jw-account-link]').forEach(el=>el.href=a?'/akun':'/masuk');
}
try {if(document.documentElement.dataset.jwServerAuth !== '1')ensureDemoAccount();refreshAccountNav();}catch(_){ /* Storage disabled; forms will show errors. */ }
window.addEventListener(ACCOUNT_EVENT,refreshAccountNav);
window.addEventListener('storage',refreshAccountNav);

const root=document.querySelector('#jw-account');
if(root){
  const page=root.dataset.accountPage;
  const loginUrl=root.dataset.loginUrl||'/masuk';
  const profileUrl=root.dataset.profileUrl||'/akun';
  let toastTimer;
  function toast(message){
    const el=root.querySelector('[data-account-toast]');if(!el)return;
    el.textContent=message;el.classList.remove('hidden');
    window.clearTimeout(toastTimer);toastTimer=window.setTimeout(()=>el.classList.add('hidden'),3300);
  }
  function error(message,target='[data-account-error]'){
    const el=root.querySelector(target);if(!el){toast(message);return;}
    el.textContent=message;el.classList.remove('hidden');el.focus?.();
  }
  function clearError(target='[data-account-error]'){
    const el=root.querySelector(target);if(el){el.textContent='';el.classList.add('hidden');}
  }
  function redirectToLogin(){
    const next=encodeURIComponent(window.location.pathname+window.location.search);
    window.location.replace(`${loginUrl}?next=${next}`);
  }
  const requiresAccount=['profile','edit','addresses','settings'].includes(page);
  const account=(()=>{try{return currentAccount();}catch(_){return null;}})();
  if(requiresAccount&&!account){redirectToLogin();}
  // Ensure no privileged content is displayed while the local-only redirect is pending.
  if(requiresAccount&&!account){root.querySelector('main')?.classList.add('hidden');}

  function showPassword(){
    root.querySelectorAll('[data-account-show-password]').forEach(button=>button.addEventListener('click',()=>{
      const input=button.parentElement.querySelector('input');
      const show=input.type==='password';input.type=show?'text':'password';
      button.setAttribute('aria-label',show?'Sembunyikan kata sandi':'Tampilkan kata sandi');
      button.querySelector('.material-symbols-outlined').textContent=show?'visibility_off':'visibility';
    }));
  }
  showPassword();

  if(page==='login'){
    const form=root.querySelector('[data-account-login-form]');
    const next=safeNext(new URLSearchParams(location.search).get('next'));
    if(account){/* Allow user to switch demo accounts by explicitly logging in. */}
    form?.addEventListener('submit',event=>{
      event.preventDefault();clearError();
      if(!form.reportValidity())return;
      const email=form.elements.namedItem('email').value;
      const pwd=form.elements.namedItem('password');
      if(pwd.value.length<6){error('Kata sandi demo minimal 6 karakter.');return;}
      try {demoSignIn(email);pwd.value='';window.location.assign(next);}
      catch(err){pwd.value='';error(err.message);}
    });
  }
  if(page==='register'){
    const form=root.querySelector('[data-account-register-form]');
    form?.addEventListener('submit',event=>{
      event.preventDefault();clearError();if(!form.reportValidity())return;
      const password=form.elements.namedItem('password');
      const confirm=form.elements.namedItem('confirm');
      if(password.value!==confirm.value){error('Kata sandi simulasi dan konfirmasi tidak cocok.');return;}
      if(password.value.length<8){error('Kata sandi simulasi minimal 8 karakter.');return;}
      try {
        registerAccount({name:form.elements.namedItem('name').value,email:form.elements.namedItem('email').value,phone:form.elements.namedItem('phone').value});
        password.value='';confirm.value='';window.location.assign(profileUrl);
      }catch(err){password.value='';confirm.value='';error(err.message);}
    });
  }
  if(page==='forgot'){
    const form=root.querySelector('[data-account-forgot-form]');
    form?.addEventListener('submit',event=>{
      event.preventDefault();clearError();if(!form.reportValidity())return;
      form.querySelector('input').value='';
      root.querySelector('[data-account-forgot-result]')?.classList.remove('hidden');
    });
  }
  if(page==='logout'){
    try {demoSignOut();}catch(_){}
  }
  if(account&&page==='profile'){
    root.querySelectorAll('[data-account-profile-name]').forEach(el=>el.textContent=account.name);
    root.querySelectorAll('[data-account-profile-email]').forEach(el=>el.textContent=account.email);
    root.querySelector('[data-account-profile-phone]').textContent=account.phone||'Belum ditambahkan';
    const defaultAddress=account.addresses.find(x=>x.isDefault)||account.addresses[0];
    root.querySelector('[data-account-profile-address]').textContent=defaultAddress?`${defaultAddress.label} · ${defaultAddress.area}`:'Belum ada alamat';
    root.querySelector('[data-account-address-count]').textContent=String(account.addresses.length);
    root.querySelector('[data-account-orders]').textContent=String(readOrders().length);
    root.querySelector('[data-account-reviews]').textContent=String(readReviews().length);
    avatarTo(root.querySelector('[data-account-avatar-large]'),account);
  }
  if(account&&page==='edit'){
    const form=root.querySelector('[data-account-edit-form]');
    const avatarInput=form.elements.namedItem('avatar');
    let pendingAvatar=undefined;
    form.elements.namedItem('name').value=account.name;
    form.elements.namedItem('email').value=account.email;
    form.elements.namedItem('phone').value=account.phone||'';
    avatarTo(root.querySelector('[data-account-avatar-preview]'),account);
    async function compressAvatar(file){
      if(!['image/jpeg','image/png','image/webp'].includes(file.type)||file.size>2*1024*1024)throw new Error('Gunakan JPG/PNG/WebP maksimum 2 MB.');
      const blobUrl=URL.createObjectURL(file);
      try {
        const image=await new Promise((resolve,reject)=>{
          const img=new Image();img.onload=()=>resolve(img);img.onerror=()=>reject(new Error('Foto tidak dapat dibuka.'));img.src=blobUrl;
        });
        const max=240,ratio=Math.min(1,max/Math.max(image.naturalWidth,image.naturalHeight));
        const canvas=document.createElement('canvas');canvas.width=Math.max(1,Math.round(image.naturalWidth*ratio));canvas.height=Math.max(1,Math.round(image.naturalHeight*ratio));
        canvas.getContext('2d').drawImage(image,0,0,canvas.width,canvas.height);
        for(const quality of [0.78,0.62,0.45]){
          const result=canvas.toDataURL('image/jpeg',quality);
          if(result.length<=130000)return result;
        }
        throw new Error('Foto belum dapat dikompresi. Pilih gambar lebih kecil.');
      }finally{URL.revokeObjectURL(blobUrl);}
    }
    avatarInput.addEventListener('change',async()=>{
      clearError();const file=avatarInput.files?.[0];if(!file)return;
      try {pendingAvatar=await compressAvatar(file);avatarTo(root.querySelector('[data-account-avatar-preview]'),{...account,avatar:pendingAvatar});}
      catch(err){avatarInput.value='';error(err.message);}
    });
    root.querySelector('[data-account-remove-avatar]').addEventListener('click',()=>{
      pendingAvatar='';avatarInput.value='';avatarTo(root.querySelector('[data-account-avatar-preview]'),{...account,avatar:''});
    });
    form.addEventListener('submit',event=>{
      event.preventDefault();clearError();if(!form.reportValidity())return;
      try {updateProfile({name:form.elements.namedItem('name').value,phone:form.elements.namedItem('phone').value,avatar:pendingAvatar});window.location.assign(profileUrl);}
      catch(err){error(err.message);}
    });
  }
  if(account&&page==='addresses'){
    const list=root.querySelector('[data-account-address-list]');
    const empty=root.querySelector('[data-account-address-empty]');
    const template=root.querySelector('#jw-address-card');
    const dialog=root.querySelector('#jw-address-dialog');
    const form=root.querySelector('[data-account-address-form]');
    const deleteDialog=root.querySelector('#jw-address-delete-dialog');
    let deleting=null;
    const set=(node,key,value)=>{node.querySelector(`[data-address-${key}]`).textContent=value||'—';};
    function render(){
      const addresses=currentAccount()?.addresses||[];
      list.replaceChildren();
      empty.classList.toggle('hidden',addresses.length!==0);
      addresses.forEach(a=>{
        const card=template.content.firstElementChild.cloneNode(true);card.dataset.id=a.id;
        set(card,'label',a.label);set(card,'recipient',a.recipient);set(card,'phone',a.phone);
        set(card,'street',a.street);set(card,'area',a.area);set(card,'landmark',a.landmark||'');
        card.querySelector('[data-address-default]').classList.toggle('hidden',!a.isDefault);
        card.querySelector('[data-address-default-action]').classList.toggle('hidden',!!a.isDefault);
        list.appendChild(card);
      });
    }
    function openAddress(a=null){
      form.reset();clearError('[data-address-error]');
      form.elements.namedItem('addressId').value=a?.id||'';
      root.querySelector('[data-address-dialog-title]').textContent=a?'Edit alamat':'Tambah alamat';
      if(a){for(const field of ['label','recipient','phone','area','street','landmark'])form.elements.namedItem(field).value=a[field]||'';form.elements.namedItem('isDefault').checked=!!a.isDefault;}
      else {form.elements.namedItem('recipient').value=currentAccount().name;form.elements.namedItem('phone').value=currentAccount().phone||'';}
      dialog.showModal();
    }
    root.querySelector('[data-account-new-address]').addEventListener('click',()=>openAddress());
    root.querySelectorAll('[data-address-close]').forEach(button=>button.addEventListener('click',()=>dialog.close()));
    form.addEventListener('submit',event=>{
      event.preventDefault();clearError('[data-address-error]');if(!form.reportValidity())return;
      const data=Object.fromEntries(new FormData(form));data.isDefault=form.elements.namedItem('isDefault').checked;
      try {saveAddress(data,data.addressId||null);dialog.close();render();toast('Alamat simulasi berhasil disimpan.');}
      catch(err){error(err.message,'[data-address-error]');}
    });
    list.addEventListener('click',event=>{
      const button=event.target.closest('button');if(!button)return;
      const id=button.closest('[data-id]')?.dataset.id;
      const a=currentAccount()?.addresses.find(x=>x.id===id);if(!a)return;
      try {
        if(button.matches('[data-address-edit]'))openAddress(a);
        if(button.matches('[data-address-default-action]')){setDefaultAddress(id);render();toast('Alamat utama diperbarui.');}
        if(button.matches('[data-address-delete]')){deleting=id;deleteDialog.showModal();}
      }catch(err){error(err.message);}
    });
    root.querySelector('[data-address-cancel-delete]').addEventListener('click',()=>deleteDialog.close());
    root.querySelector('[data-address-confirm-delete]').addEventListener('click',()=>{
      if(!deleting)return;
      try {removeAddress(deleting);render();toast('Alamat dihapus.');}catch(err){error(err.message);}
      deleting=null;deleteDialog.close();
    });
    for(const d of [dialog,deleteDialog])d.addEventListener('click',event=>{if(event.target===d)d.close();});
    render();
  }
  if(account&&page==='settings'){
    const form=root.querySelector('[data-account-settings-form]');
    form.elements.namedItem('notifications').checked=account.preferences?.notifications??true;
    form.elements.namedItem('updates').checked=account.preferences?.updates??false;
    form.addEventListener('submit',event=>{
      event.preventDefault();
      try {updatePreferences({notifications:form.elements.namedItem('notifications').checked,updates:form.elements.namedItem('updates').checked});toast('Preferensi simulasi berhasil disimpan.');}
      catch(err){toast(err.message);}
    });
    const dialog=root.querySelector('#jw-account-delete-dialog');
    root.querySelector('[data-account-clear-profile]').addEventListener('click',()=>dialog.showModal());
    root.querySelector('[data-account-cancel-delete]').addEventListener('click',()=>dialog.close());
    root.querySelector('[data-account-confirm-delete]').addEventListener('click',()=>{
      try {deleteCurrentAccount();window.location.assign('/daftar');}
      catch(err){dialog.close();toast(err.message);}
    });
    dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close();});
  }
}

// Optional prefill ONLY when a user deliberately goes to the booking form. All values remain editable.
const booking=document.querySelector('#jw-booking');
if(booking){
  let a;try{a=currentAccount();}catch(_){a=null;}
  if(a){
    const form=booking.querySelector('form');
    if(form){
      const fill=(name,value)=>{const el=form.elements.namedItem(name);if(el&&!el.value)el.value=value||'';};
      const preferred=a.addresses.find(x=>x.isDefault)||a.addresses[0];
      fill('contact_name',preferred?.recipient||a.name);fill('phone',preferred?.phone||a.phone);
      if(preferred){
        const area=form.elements.namedItem('area');
        if(area&&!area.value&&[...area.options].some(o=>o.value===preferred.area))area.value=preferred.area;
        fill('address',preferred.street);fill('landmark',preferred.landmark);}
    }
  }
}
