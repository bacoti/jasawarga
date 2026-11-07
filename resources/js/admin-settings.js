/** Pengaturan frontend Admin: identitas tampilan dan preferensi lokal; autentikasi memakai sesi Laravel. */
import { ADMIN_SETTINGS_KEY,ADMIN_SETTINGS_EVENT,readAdminSettings,saveAdminIdentity,saveAdminPreferences,resetAdminPreferences,validateAdminDemoPassword,enterAdminDemo,exitAdminDemo,isAdminDemoActive } from './admin-settings-store.js';

function applyAdminAppearance(){
  const onAdminPage=Boolean(document.querySelector('[id^="jw-admin-"]'));
  if(!onAdminPage)return;
  const settings=readAdminSettings();
  document.querySelectorAll('[data-admin-display-name]').forEach(element=>{element.textContent=settings.displayName;});
  document.documentElement.classList.toggle('jw-admin-reduce-motion',settings.preferences.reduceMotion);
}
applyAdminAppearance();
window.addEventListener(ADMIN_SETTINGS_EVENT,applyAdminAppearance);
window.addEventListener('storage',event=>{if(event.key===null||event.key===ADMIN_SETTINGS_KEY)applyAdminAppearance();});

const entry=document.getElementById('jw-admin-entry');
if(entry){entry.querySelector('[data-as-enter]').addEventListener('click',()=>{try{enterAdminDemo();}catch(_){}window.location.assign('/admin/dashboard');});}

const root=document.getElementById('jw-admin-settings');
if(root){
  const $=selector=>root.querySelector(selector);
  const $$=selector=>[...root.querySelectorAll(selector)];
  const identity=$('[data-as-identity-form]'),preferences=$('[data-as-preferences-form]'),passwordForm=$('[data-as-password-form]');
  const dialog=$('#jw-as-logout-dialog');
  function notice(text,error=false){const target=$('[data-as-notice]');target.textContent=text;target.className=`rounded-xl border px-4 py-3 text-sm font-semibold ${error?'border-rose-200 bg-rose-50 text-rose-800':'border-blue-200 bg-blue-50 text-blue-900'}`;target.scrollIntoView({block:'nearest',behavior:'smooth'});}
  function render(){
    const s=readAdminSettings();
    identity.elements.namedItem('displayName').value=s.displayName;
    identity.elements.namedItem('email').value=s.email;
    for(const el of $$('[data-as-pref]'))el.checked=s.preferences[el.dataset.asPref];
    for(const el of $$('[data-as-notification]'))el.checked=s.notifications[el.dataset.asNotification];
    $('[data-as-default-period]').value=s.preferences.defaultPeriod;
    $('[data-as-updated]').textContent=s.updatedAt?new Intl.DateTimeFormat('id-ID',{dateStyle:'medium',timeStyle:'short'}).format(new Date(s.updatedAt)):'Belum ada';
    $('[data-as-session]').textContent=document.documentElement.dataset.jwServerAuth === '1'?'Aktif: sesi Laravel':'Belum login Laravel';
  }
  identity.addEventListener('submit',event=>{event.preventDefault();try{saveAdminIdentity({displayName:identity.elements.namedItem('displayName').value,email:identity.elements.namedItem('email').value});render();notice('Identitas Admin demo berhasil disimpan.');}catch(error){notice(error.message,true);}});
  preferences.addEventListener('submit',event=>{
    event.preventDefault();
    try{
      const notifications=Object.fromEntries($$('[data-as-notification]').map(el=>[el.dataset.asNotification,el.checked]));
      const pref=Object.fromEntries($$('[data-as-pref]').map(el=>[el.dataset.asPref,el.checked]));
      saveAdminPreferences({notifications,preferences:{...pref,defaultPeriod:$('[data-as-default-period]').value}});
      render();notice('Preferensi berhasil disimpan. Tampilan laporan dan Dashboard akan mengikuti pengaturan.');
    }catch(error){notice(error.message,true);}
  });
  $('[data-as-reset]').addEventListener('click',()=>{if(!window.confirm('Pulihkan hanya preferensi Admin ke nilai awal? Identitas, pesanan dan tindakan Admin tetap disimpan.'))return;try{resetAdminPreferences();render();notice('Preferensi Admin dikembalikan ke bawaan.');}catch(error){notice(error.message,true);}});
  passwordForm.addEventListener('submit',event=>{
    event.preventDefault();
    const secret=passwordForm.elements.namedItem('password');const confirmation=passwordForm.elements.namedItem('confirm');
    try{validateAdminDemoPassword(secret.value,confirmation.value);notice('Simulasi cocok. Tidak ada password yang disimpan atau diubah.');}
    catch(error){notice(error.message,true);}finally{passwordForm.reset();}
  });
  $('[data-as-logout]').addEventListener('click',()=>dialog.showModal());
  $('[data-as-stay]').addEventListener('click',()=>dialog.close());
  // Logout server ditangani oleh form POST + CSRF pada dialog, bukan redirect JS.
  dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close();});
  window.addEventListener('storage',event=>{if(event.key===ADMIN_SETTINGS_KEY||event.key===null)render();});
  window.addEventListener(ADMIN_SETTINGS_EVENT,render);
  render();
}
