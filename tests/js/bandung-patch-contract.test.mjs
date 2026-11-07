import assert from 'node:assert/strict';
import fs from 'node:fs';
import test from 'node:test';

const read = name => fs.readFileSync(new URL(`../../${name}`, import.meta.url), 'utf8');

test('pemesanan dan pesanan demo dibatasi oleh session dan role citizen', () => {
  const routes = read('routes/web.php');
  const start = routes.indexOf("Route::middleware(['auth', 'role:citizen'])->group(function () {", routes.indexOf("->name('providers.show')"));
  assert.ok(start > 0);
  const tail = routes.slice(start, routes.indexOf('// Panel Admin', start));
  for (const part of ["'/pemesanan/{id}'", "'/pesanan-saya'", "'/ulasan-saya'"]) {
    assert.ok(tail.includes(part), `Missing protected route ${part}`);
  }
});

test('Home action profil menuju id mitra dan pemesanan ke form yang dilindungi', () => {
  const code = read('resources/js/home.js');
  assert.match(code, /window\.location\.assign\(`\/mitra\/\$\{id\}`\)/);
  assert.match(code, /window\.location\.assign\(`\/pemesanan\/\$\{id\}`\)/);
  assert.doesNotMatch(code, /function providerProfile\(/);
});

test('peta menggunakan ubin OpenStreetMap dengan atribusi', () => {
  const code = read('resources/js/home-map.js');
  assert.ok(code.includes('tile.openstreetmap.org'));
  assert.ok(code.includes('OpenStreetMap contributors'));
  assert.match(read('config/jasawarga.php'), /Bandung/);
});

test('navigasi publik bersumber dari komponen dan disaring menurut role', () => {
  const header = read('resources/views/components/site-navbar.blade.php');
  assert.ok(header.includes('role:citizen') === false);
  assert.ok(header.includes('$jwCitizen') && header.includes('$jwPartner') && header.includes('$jwAdmin'));
  assert.ok(read('resources/views/home/desktop/header.blade.php').includes("components.site-navbar"));
  assert.ok(read('resources/views/home/mobile/header.blade.php').includes("components.site-navbar"));
  assert.ok(read('resources/views/layouts/profile-stage2.blade.php').includes("components.site-navbar"));
  assert.ok(read('resources/views/layouts/analytics-stage6.blade.php').includes("components.site-navbar"));
});

test('teks area publik dan cakupan mitra demo tidak membawa kecamatan luar Bandung', () => {
  for (const file of [
    'config/jasawarga_profiles.php',
    'resources/views/home/desktop/content.blade.php',
    'resources/views/accounts/addresses.blade.php',
    'resources/views/partner/profile/index.blade.php',
    'resources/views/partner/reviews/index.blade.php',
    'resources/views/partner/services/index.blade.php',
  ]) {
    assert.doesNotMatch(read(file), /\b(Makassar|Mariso|Ujung Pandang)\b/i, file);
  }
  assert.match(read('resources/views/accounts/addresses.blade.php'), /bandung_districts/);
  assert.match(read('database/migrations/2026_11_07_070002_update_user_timezone_default_to_jakarta.php'), /Asia\/Jakarta/);
});
