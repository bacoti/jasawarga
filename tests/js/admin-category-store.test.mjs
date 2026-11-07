import test from 'node:test';
import assert from 'node:assert/strict';
import { ADMIN_CATEGORIES_KEY, readCategories, createCategory, editCategory, toggleCategory, deleteCategory,
  slugify, categoryUsage, isCategoryActive } from '../../resources/js/admin-category-store.js';
import { activeServices } from '../../resources/js/services-store.js';

const memory = new Map();
globalThis.localStorage = {
  getItem: key => memory.get(key) ?? null,
  setItem: (key,value) => memory.set(key,value),
  removeItem: key => memory.delete(key),
};
const seed = [
  {slug:'ac',name:'Servis AC',description:'Servis AC rumah',icon:'ac_unit',bg:'#FFDCBD',fg:'#874F00'},
  {slug:'taman',name:'Taman Rumah',description:'Rawat taman rumah',icon:'yard',bg:'#DDF4E7',fg:'#176B47'},
];
const providers = [{id:1,category:'ac'}];
const profiles = {'1':{services:[{name:'Cuci AC',description:'Layanan cuci AC',price:75000,duration:'1 jam'}]}};
const category = {name:'Perawatan Komputer',slug:'perawatan-komputer',description:'Servis dan perawatan perangkat komputer',icon:'computer',colorIndex:'1'};
const reset = () => memory.clear();

test('kategori seed tampil tanpa override, slug normalisasi stabil',()=>{
  reset();
  assert.equal(readCategories(seed).length,2);
  assert.equal(slugify('  Perawatan  Komputer & Printer!  '),'perawatan-komputer-printer');
  assert.equal(isCategoryActive('ac',seed),true);
});
test('tambah dan edit menjaga slug; tidak mengubah relasi layanan lama',()=>{
  reset();
  createCategory(seed,category);
  assert.equal(readCategories(seed).length,3);
  assert.throws(()=>createCategory(seed,category),/sudah digunakan/);
  editCategory(seed,'perawatan-komputer',{...category,name:'Perawatan PC',slug:'ubah-slug'});
  const result=readCategories(seed).find(item=>item.slug==='perawatan-komputer');
  assert.equal(result.name,'Perawatan PC');
  assert.equal(result.slug,'perawatan-komputer');
  assert.equal(result.origin,'custom');
});
test('kategori nonaktif membuat layanan tidak bisa dipesan baru',()=>{
  reset();
  const services=profiles['1'].services;
  assert.equal(activeServices(1,services,'ac',seed).length,1);
  toggleCategory(seed,'ac',false);
  assert.equal(isCategoryActive('ac',seed),false);
  assert.equal(activeServices(1,services,'ac',seed).length,0);
  assert.equal(activeServices(1,services,'ac').length,1); // pemanggil lama tetap kompatibel
  toggleCategory(seed,'ac',true);
  assert.equal(activeServices(1,services,'ac',seed).length,1);
});
test('kategori yang digunakan Mitra atau layanan tidak boleh dihapus',()=>{
  reset();
  assert.deepEqual(categoryUsage('ac',providers,profiles),{providers:1,services:1,total:2});
  assert.throws(()=>deleteCategory(seed,'ac',providers,profiles),/masih digunakan/);
  createCategory(seed,category);
  deleteCategory(seed,'perawatan-komputer',providers,profiles);
  assert.equal(readCategories(seed).some(x=>x.slug==='perawatan-komputer'),false);
});
test('validasi input menolak data salah dan data tersimpan rusak ditangani',()=>{
  reset();
  assert.throws(()=>createCategory(seed,{...category,icon:'<script>'}),/Ikon/);
  assert.throws(()=>createCategory(seed,{...category,description:'pendek'}),/Deskripsi/);
  memory.set(ADMIN_CATEGORIES_KEY,'{broken');
  assert.equal(readCategories(seed).length,2);
});
