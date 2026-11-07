# Instalasi Backend Tahap 1 — JasaWarga Laravel 12 + PostgreSQL

## Persyaratan lokal

- PHP 8.2+ dengan ekstensi `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `fileinfo` dan ekstensi yang diwajibkan Laravel lainnya.
- Composer 2 dan Node.js/npm.
- PostgreSQL (server aktif), klien `psql` atau pgAdmin.
- Jalankan hanya dari folder utama project Laravel ini, **jangan ekstrak paket perubahan ke `public/`**.

## 1. Buat database lokal

Masuk `psql` sebagai superuser PostgreSQL atau gunakan pgAdmin. Pada terminal psql:

```sql
CREATE ROLE jasawarga_app LOGIN;
CREATE DATABASE jasawarga_dev OWNER jasawarga_app ENCODING 'UTF8';
```

Kemudian atur password untuk role tersebut secara interaktif dengan perintah `\password jasawarga_app` di `psql`. Gunakan nilai password itu di `.env`, **jangan** menaruhnya dalam repositori, Markdown, atau screenshot.

Jika role/database sudah ada, gunakan yang ada dan pastikan role aplikasi mendapat hak membuat tabel pada schema yang digunakan.

## 2. Siapkan konfigurasi dan dependensi

PowerShell Windows atau terminal:

```bash
composer install
npm install
```

Salin `.env.example` menjadi `.env`, lalu isi `DB_CONNECTION=pgsql`, host, port, database, username, password. Pastikan `SESSION_DRIVER=file`. Contoh yang **bukan rahasia**:

```dotenv
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=jasawarga_dev
DB_USERNAME=jasawarga_app
DB_PASSWORD=ISI_SENDIRI_DI_ENV
SESSION_DRIVER=file
```

Jalankan:

```bash
php artisan key:generate
php artisan config:clear
php artisan migrate
```

Jika muncul `could not find driver`, aktifkan ekstensi `pdo_pgsql` dan `pgsql` pada file `php.ini` PHP CLI dan restart terminal. Jika `Connection refused`, periksa PostgreSQL berjalan dan port `5432` sesuai.

## 3. Seed akun tiga role untuk **pengujian lokal saja** (opsional)

Di `.env` tetapkan `JASAWARGA_SEED_DEMO_ACCOUNTS=true` serta `JASAWARGA_DEMO_PASSWORD=` diisi password pilihanmu (minimal 10 karakter, huruf besar/kecil dan angka); **jangan bagikan password itu**. Jalankan:

```bash
php artisan config:clear
php artisan db:seed
```

Seeder hanya boleh berjalan dengan `APP_ENV=local`/`testing` dan opt-in. Ini membuat akun **lokal**:

- `admin@jasawarga.test` (role Admin)
- `warga@jasawarga.test` (role Warga)
- `mitra@jasawarga.test` (role Mitra, dashboard dummy Budi Teknik ID=1)

Setelah seed, ubah `JASAWARGA_SEED_DEMO_ACCOUNTS=false` serta kosongkan `JASAWARGA_DEMO_PASSWORD` pada `.env`, kemudian jalankan `php artisan config:clear`. Jangan pernah menjalankan seeder contoh di produksi.

## 4. Jalankan frontend dan Laravel

Terminal A:

```bash
npm run dev
```

Terminal B:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Buka:

- `http://127.0.0.1:8000/masuk` — login tiga role
- `http://127.0.0.1:8000/daftar` — daftar Warga
- `http://127.0.0.1:8000/daftar/mitra` — daftar Mitra baru
- `http://127.0.0.1:8000/mitra/portal` — portal Mitra yang login
- `http://127.0.0.1:8000/admin/warga/akun` — akun Warga database (admin saja)
- `http://127.0.0.1:8000/admin/mitra/pendaftaran` — review akun Mitra baru, hanya Admin
- `http://127.0.0.1:8000/admin/dashboard` — Admin lama dengan sesi server
- `http://127.0.0.1:8000/mitra/dashboard` — khusus akun seeded Budi Teknik

## 5. Pengujian

Untuk test fitur gunakan database terisolasi. `phpunit.xml` disetel `sqlite :memory:` untuk tes portable, jadi butuh `pdo_sqlite` pada PHP CLI. Jangan arahkan tes ke database produksi.

```bash
php artisan route:list
php artisan test
node --test tests/js/*.test.mjs
```

> Pengujian PHP Laravel belum dijalankan di lingkungan pembuat paket karena `vendor/`/Composer dan driver database tidak tersedia. Lakukan pengujian setelah instalasi di mesinmu.

## Apa yang sudah nyata vs masih demo

**Nyata pada PostgreSQL**: akun Warga/Mitra/Admin, password ter-hash, login/logout session, middleware role, profil akun Warga, pengajuan Mitra baru, persetujuan/penolakan Admin, dan log keputusan.

**Masih demo `localStorage`**: pesanan, ulasan, pengaduan, layanan, jadwal, portofolio, statistik dan preferensi dashboard. Metrik dashboard tidak boleh diperlakukan sebagai hasil database dan pengguna yang login di server tidak otomatis menjadi pemilik record demo lokal. Pastikan tidak memasukkan data pelanggan sungguhan di prototype ini.

## Keamanan sebelum produksi

Sebelum deploy: set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` HTTPS, `SESSION_SECURE_COOKIE=true`, konfigurasi cookie domain, mailer/password reset nyata, rate-limit tambahan, verifikasi email, kebijakan privasi, pengelolaan akun suspended, pengujian seluruh alur, role & policy yang lengkap, serta migrasi seluruh entitas bisnis ke DB.
