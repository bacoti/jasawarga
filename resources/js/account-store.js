/** JasaWarga frontend-only account state. NOT authentication. NEVER store passwords. */
export const ACCOUNT_KEY = 'jasawarga:prototype-accounts:v1';
export const SESSION_KEY = 'jasawarga:prototype-session:v1';
export const ACCOUNT_EVENT = 'jasawarga:account-updated';
const DEMO_EMAIL = 'demo@jasawarga.test';
const MAX_ACCOUNTS = 8;
const MAX_ADDRESSES = 8;
const emailRE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const phoneRE = /^08[0-9]{8,11}$/;
const pureString = x => typeof x === 'string' ? x.trim() : '';
const id = prefix => `${prefix}-${Array.from(crypto.getRandomValues(new Uint8Array(5))).map(x=>x.toString(16).padStart(2,'0')).join('')}`;
const notify = () => { if (typeof window !== 'undefined') window.dispatchEvent(new CustomEvent(ACCOUNT_EVENT)); };
const store = () => { if (typeof localStorage === 'undefined') throw new Error('Penyimpanan browser tidak tersedia.'); return localStorage; };
const sessions = () => { if (typeof sessionStorage === 'undefined') throw new Error('Sesi browser tidak tersedia.'); return sessionStorage; };
const prefsDefault = () => ({notifications:true, updates:false});
const demo = () => ({id:'jw-warga-demo', name:'Budi Santoso (Demo)', email:DEMO_EMAIL, phone:'081200000000', avatar:'', addresses:[{id:'alamat-demo',label:'Rumah (Contoh)',recipient:'Budi Santoso',phone:'081200000000',area:'Panakkukang',street:'Jl. Contoh No. 12, Makassar',landmark:'Dekat balai warga',isDefault:true}],preferences:prefsDefault(),createdAt:Date.now()});
export const normalizeEmail = value => pureString(value).toLowerCase();
function validAccount(a) {
  return a && typeof a==='object' && /^jw-warga-[a-z0-9-]+$/.test(a.id||'') &&
    pureString(a.name).length>=2 && pureString(a.name).length<=80 && emailRE.test(a.email||'') &&
    Array.isArray(a.addresses) && a.addresses.length<=MAX_ADDRESSES &&
    typeof a.avatar==='string' && a.avatar.length<=140000;
}
export function readAccounts() {
  let raw;
  try { raw=JSON.parse(store().getItem(ACCOUNT_KEY)||'null'); } catch (_) { raw=null; }
  if (!Array.isArray(raw)) return [];
  const emails=new Set();
  return raw.filter(a=>{if(!validAccount(a)||emails.has(a.email))return false;emails.add(a.email);return true}).slice(0,MAX_ACCOUNTS);
}
function saveAccounts(list) {
  store().setItem(ACCOUNT_KEY,JSON.stringify(list)); notify();
}
export function ensureDemoAccount() {
  const current=readAccounts();
  if (current.some(a=>a.email===DEMO_EMAIL)) return;
  // Demo is seeded only when no accounts have ever been stored.
  if (store().getItem(ACCOUNT_KEY)!==null) return;
  saveAccounts([demo()]);
}
export function currentAccount() {
  let accountId;
  try { accountId=JSON.parse(sessions().getItem(SESSION_KEY)||'null')?.id; } catch (_) {return null;}
  return readAccounts().find(a=>a.id===accountId)||null;
}
export function isSignedIn() {return !!currentAccount();}
export function demoSignIn(email) {
  const record=readAccounts().find(a=>a.email===normalizeEmail(email));
  if(!record) throw new Error('Email belum terdaftar di browser ini. Silakan registrasi atau gunakan akun demo.');
  sessions().setItem(SESSION_KEY,JSON.stringify({id:record.id,at:Date.now()}));notify();return record;
}
export function demoSignOut() {sessions().removeItem(SESSION_KEY);notify();}
export function registerAccount(input) {
  const name=pureString(input.name),email=normalizeEmail(input.email),phone=pureString(input.phone).replace(/[^0-9]/g,'');
  if(name.length<2||name.length>80)throw new Error('Nama harus 2–80 karakter.');
  if(!emailRE.test(email)||email.length>120)throw new Error('Alamat email tidak valid.');
  if(phone && !phoneRE.test(phone))throw new Error('Nomor WhatsApp harus 08 diikuti 8–11 digit.');
  const list=readAccounts();
  if(list.some(a=>a.email===email))throw new Error('Email sudah digunakan dalam browser ini.');
  if(list.length>=MAX_ACCOUNTS)throw new Error('Batas 8 akun demo pada browser ini tercapai.');
  const account={id:id('jw-warga'),name,email,phone,avatar:'',addresses:[],preferences:prefsDefault(),createdAt:Date.now()};
  saveAccounts([...list,account]);
  sessions().setItem(SESSION_KEY,JSON.stringify({id:account.id,at:Date.now()}));notify();return account;
}
function requireAccount() {const a=currentAccount();if(!a) throw new Error('Silakan masuk ke akun simulasi terlebih dahulu.');return a;}
function replaceAccount(next) {const list=readAccounts();const idx=list.findIndex(a=>a.id===next.id);if(idx<0)throw new Error('Akun tidak ditemukan.');list[idx]=next;saveAccounts(list);return next;}
export function updateProfile(input) {
  const a=requireAccount(),name=pureString(input.name),phone=pureString(input.phone).replace(/[^0-9]/g,'');
  if(name.length<2||name.length>80)throw new Error('Nama harus 2–80 karakter.');
  if(phone&&!phoneRE.test(phone))throw new Error('Format nomor WhatsApp tidak valid.');
  const avatar=input.avatar===undefined?a.avatar:input.avatar;
  if(typeof avatar!=='string'||(avatar!==''&&(!/^data:image\/jpeg;base64,[A-Za-z0-9+/=]+$/.test(avatar)||avatar.length>140000)))throw new Error('Ukuran foto profil terlalu besar.');
  return replaceAccount({...a,name,phone,avatar});
}
function checkAddress(data) {
  const a={label:pureString(data.label),recipient:pureString(data.recipient),phone:pureString(data.phone).replace(/[^0-9]/g,''),area:pureString(data.area),street:pureString(data.street),landmark:pureString(data.landmark)};
  if(!a.label||a.label.length>35)throw new Error('Label alamat wajib, maksimal 35 karakter.');
  if(a.recipient.length<2||a.recipient.length>80)throw new Error('Nama penerima harus 2–80 karakter.');
  if(!phoneRE.test(a.phone))throw new Error('Nomor WA penerima tidak valid.');
  if(!['Panakkukang','Rappocini','Tamalate','Biringkanaya','Manggala','Makassar','Ujung Pandang'].includes(a.area))throw new Error('Pilih kecamatan yang tersedia.');
  if(a.street.length<10||a.street.length>240)throw new Error('Alamat harus 10–240 karakter.');
  if(a.landmark.length>150)throw new Error('Patokan maksimal 150 karakter.');
  return a;
}
export function saveAddress(data,addressId=null) {
  const account=requireAccount(),values=checkAddress(data);let addresses=[...account.addresses];
  if(addressId){const i=addresses.findIndex(a=>a.id===addressId);if(i<0)throw new Error('Alamat tidak ditemukan.');addresses[i]={...addresses[i],...values};}
  else {if(addresses.length>=MAX_ADDRESSES)throw new Error('Maksimal 8 alamat tersimpan.');addresses.push({...values,id:id('addr'),isDefault:addresses.length===0});}
  if(data.isDefault){addresses=addresses.map(a=>({...a,isDefault:a.id===(addressId||addresses.at(-1).id)}));}
  return replaceAccount({...account,addresses});
}
export function removeAddress(addressId) {
  const a=requireAccount();let addresses=a.addresses.filter(x=>x.id!==addressId);
  if(addresses.length===a.addresses.length)throw new Error('Alamat tidak ditemukan.');
  if(addresses.length&&!addresses.some(x=>x.isDefault))addresses[0]={...addresses[0],isDefault:true};
  return replaceAccount({...a,addresses});
}
export function setDefaultAddress(addressId) {
  const a=requireAccount();if(!a.addresses.some(x=>x.id===addressId))throw new Error('Alamat tidak ditemukan.');
  return replaceAccount({...a,addresses:a.addresses.map(x=>({...x,isDefault:x.id===addressId}))});
}
export function updatePreferences(p) {
  const a=requireAccount();return replaceAccount({...a,preferences:{notifications:!!p.notifications,updates:!!p.updates}});
}
export function safeNext(value,fallback='/akun') {
  if(typeof value!=='string'||!value.startsWith('/')||value.startsWith('//')||value.includes('\\')||/[\r\n]/.test(value))return fallback;
  try {const url=new URL(value,'http://example.test');if(url.origin!=='http://example.test')return fallback;return `${url.pathname}${url.search}${url.hash}`;}catch(_){return fallback;}
}

export function deleteCurrentAccount() {
  const a=requireAccount(); const next=readAccounts().filter(x=>x.id!==a.id);
  saveAccounts(next);demoSignOut();return true;
}
