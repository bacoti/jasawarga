# Penggabungan Backend Tahap 2 dan Tahap 3 — JasaWarga

## Keadaan awal

Tahap 3 ini dibangun di atas ZIP **Backend Tahap 1**, karena Tahap 2 sedang dikerjakan oleh rekan lain dan **belum tersedia dalam source**. ZIP lengkap Tahap 3 berarti **Tahap 1 + Tahap 3**, BUKAN Tahap 1 + 2 + 3. Jangan mengganti project rekan yang sudah memuat Tahap 2 dengan ZIP lengkap ini.

## Langkah merge aman (disarankan)

1. Pastikan rekan menyelesaikan Tahap 2, lakukan `git commit` untuk versi gabungan Tahap 1 + Tahap 2.
2. **Jangan langsung ekstrak semua file overwrite.** Dari ZIP perubahan Tahap 3, salin file yang **baru** terlebih dahulu:
   - `app/Models/ServiceCategory.php`, `app/Models/PartnerService.php`;
   - `app/Http/Controllers/Database*Controller.php`, `app/Http/Requests/Save*Request.php`;
   - `database/migrations/*030001*`, `*030002*`;
   - `database/seeders/ServiceCategorySeeder.php`;
   - `routes/stage3_catalog.php`;
   - `resources/views/backend-catalog/**`, `resources/js/backend-catalog.js`;
   - `tests/Feature/DatabaseCatalogStage3Test.php`;
   - `docs/ERD_Tahap_3.md` dan dokumen ini.
3. `app/Http/Controllers/Controller.php` adalah file **baru** di source Tahap 1 yang dipakai sebagai dasar; bila Tahap 2 sudah menambahkannya, **pertahankan versi mereka** jika telah benar.
4. Pada `routes/web.php` versi gabungan Tahap 2, tambahkan **tepat satu baris** setelah definisi route lain (di luar grup):

   ```php
   require __DIR__.'/stage3_catalog.php';
   ```

   Jangan mengganti seluruh `routes/web.php` dengan yang dari ZIP Tahap 3.
5. Pada `resources/js/app.js` versi gabungan, tambahkan **satu import**:

   ```js
   import './backend-catalog.js';
   ```

   Jangan mengganti seluruh `app.js` dengan yang dari ZIP Tahap 3.
6. Link menu opsional yang dapat ditambahkan manual atau di-merge pada file Tahap 2:
   - Admin sidebar: `route('admin.db-categories.index')`
   - Admin mobile nav: `route('admin.db-categories.index')`
   - Portal Mitra disetujui: `route('partner.db-services.index')`
   - Katalog Warga lama: `route('db-catalog.index')`
7. Pastikan Tahap 2 masih mempunyai entitas `App\Models\User`, `App\Models\PartnerProfile` dan relasi:
   - `User::partnerProfile()`
   - `PartnerProfile::user()`
   - `partner_profiles.user_id`, `verification_status`, `business_name`, `service_area`.
   Bila nama kolom di Tahap 2 berubah, sesuaikan model/controller Tahap 3 **sebelum migrasi**.
8. Jalankan `php artisan optimize:clear`, `php artisan migrate:status`, lalu `php artisan migrate`. Lakukan backup PostgreSQL terlebih dulu pada environment data penting.
9. Opsional: `php artisan db:seed --class=ServiceCategorySeeder` untuk menambahkan **label kategori awal saja**. Perintah ini **tidak** membuat Mitra, layanan, pesanan atau mengimpor localStorage.
10. Jalankan `php artisan route:list --path=admin/kategori-db`, `php artisan route:list --path=mitra/layanan-db`, `php artisan test --filter=DatabaseCatalogStage3Test` (jika dependensi pengujian tersedia).

## File bersama yang berbeda dari Tahap 1

| File | Perubahan Tahap 3 | Tindakan merge |
|---|---|---|
| `routes/web.php` | 1 baris `require` | Tambahkan ke versi Tahap 2 |
| `resources/js/app.js` | 1 baris `import` | Tambahkan ke versi Tahap 2 |
| `resources/views/admin/partials/sidebar.blade.php` | Link kategori DB | Tempel hanya satu link |
| `resources/views/admin/partials/mobile-nav.blade.php` | Link kategori DB | Tempel satu item navigasi |
| `resources/views/auth/partner-portal.blade.php` | Tombol layanan DB untuk Mitra approved | Tambahkan tombol pada UI Tahap 2 |
| `resources/views/categories/index.blade.php` | Tautan katalog DB | Tempel panel CTA |
| `README.md` | Dokumentasi Tahap 3 | Gabungkan per bagian |

## Route produksi baru (tidak bentrok dengan prototype)

| Metode | Route | Akses |
|---|---|---|
| GET | `/katalog-jasa` | Publik |
| GET | `/katalog-jasa/{partnerService}` | Publik; hanya layanan publishable |
| GET/POST | `/admin/kategori-db` | Admin authenticated |
| PATCH | `/admin/kategori-db/{serviceCategory}` | Admin authenticated |
| PATCH | `/admin/kategori-db/{serviceCategory}/status` | Admin authenticated |
| DELETE | `/admin/kategori-db/{serviceCategory}` | Admin authenticated |
| GET/POST | `/mitra/layanan-db` | Mitra approved |
| PATCH | `/mitra/layanan-db/{partnerService}` | Pemilik Mitra approved |
| PATCH | `/mitra/layanan-db/{partnerService}/status` | Pemilik Mitra approved |
| DELETE | `/mitra/layanan-db/{partnerService}` | Pemilik Mitra approved |

## Batas pemisahan data

`/admin/kategori`, `/mitra/layanan`, `/kategori-jasa`, `/pemesanan/{id}` dan halaman Warga/Mitra demo lama masih menggunakan data PHP/localStorage. Route **`-db`** dan `/katalog-jasa` adalah jalur PostgreSQL. Browser lokal **tidak** menjadi basis data kebenaran untuk harga atau kategori database. Jangan menggunakan ID service database sebagai indeks layanan dummy.

## Keamanan yang perlu dipastikan saat penggabungan

- Route Tahap 3 berada dalam grup middleware `web` karena di-*require* oleh `routes/web.php`, sehingga Form POST/PATCH/DELETE mendapat proteksi CSRF Laravel.
- Login/role menggunakan middleware Tahap 1. Persetujuan Mitra dicek setiap request mutasi.
- Controller Mitra melakukan query ulang layanan dengan `partner_profile_id` dari sesi login untuk mencegah akses layanan milik Mitra lain.
- Field `partner_profile_id` dari request tidak pernah dipakai.
- Form Request dan database membatasi input kategori, harga, durasi dan status.
- `base_price` hanya harga awal, tidak boleh disebut pemasukan platform.
