# JasaWarga TERPADU — Hasil Sinkronisasi Tahap 1–7

**Tanggal integrasi:** 7 November 2026  
**Basis source:** project Tahap 1 + 3 + 5 + 7; modul ZIP Tahap 2, 4, 6 dari anggota tim.  
**Target:** Laravel 12, PHP 8.2+, PostgreSQL, Blade, Tailwind, JavaScript.

## Status dan arti "sinkronisasi"

Source untuk tujuh tahap **telah digabung dan konflik yang teridentifikasi sudah diperbaiki pada level kode**. Ini **bukan jaminan aplikasi telah lulus uji database atau siap produksi**. Pemeriksaan PHP, JavaScript, dan kontrak source yang tersedia sudah dijalankan. Karena Composer/vendor, PostgreSQL, dan ekstensi PHP basis data tidak tersedia di lingkungan integrasi, `php artisan migrate`, PHPUnit/Pest, route:list, dan pengujian browser E2E **belum bisa diverifikasi di sini**. Jalankan semua langkah penerimaan di localhost sebelum digunakan.

## Ringkasan konflik yang benar-benar diperbaiki

| Konflik sebelum merge | Penyelesaian |
|---|---|
| Migration Tahap 2 `users` dan `partner_profiles` memakai timestamp/nama yang bertabrakan dengan Tahap 1 | Migration penambah role Tahap 2 tidak dipakai; `users.role` Tahap 1 dipertahankan. Migration create `partner_profiles` Tahap 2 diganti migration **ALTER** `010004_extend_partner_profiles_for_stage2`; kode sumber lama disimpan di `docs/original_modules/stage2/`. Semua migration Tahap 2/4/6 mendapat prefix unik 010/040/060. |
| Tahap 2/4/6 memakai role `warga`, `mitra`, sedangkan Tahap 1 menyimpan `citizen`, `partner` | FormRequest, middleware, view, route, dan query laporan disesuaikan ke `citizen`/`partner`; label bahasa Indonesia tetap. |
| `PartnerProfile` dari Tahap 2 menimpa kolom/model verifikasi Tahap 1 | Model digabung, termasuk relasi portofolio, layanan, pesanan, log verifikasi, `slug`, `service_areas`, dan `reverification_required`. Keputusan Admin tetap berasal dari Tahap 1. |
| Tahap 4 mencari tabel `services`, `price`, `price_type`, Tahap 3 memakai `partner_services`, `base_price` | `ServiceCatalog` sekarang membaca model `PartnerService::published()`, memakai harga `base_price`, dan memvalidasi kategori aktif, Mitra aktif/terverifikasi serta kepemilikan. **Saat ini semua tarif dianggap harga tetap (`fixed`)**; fitur harga estimasi menunggu perluasan data katalog. |
| Form Tahap 4 mengambil field konfigurasi katalog lama | Form booking disederhanakan agar membaca objek hasil adapter (`id`, `name`, `duration_minutes`). |
| Tahap 5 `ReviewBookingVerifier` selalu `false` | Kini membuktikan kode pesanan berdasarkan `bookings.booking_code`, pemilik Warga, ID layanan, status `completed`, `completed_at` terisi, dan tidak dibatalkan. Referensi localStorage demo tetap ditolak. |
| Tahap 6 membaca `categories`, `services`, `reviews`, `reports` | Pemetaan menjadi `service_categories`, `partner_services`, `service_reviews`, `service_complaints`. Rata-rata rating hanya dari ulasan yang `verified` dan `visible`. |
| `PartnerVerificationController` tak bisa menyetujui pemeriksaan ulang karena status awal masih `approved` | Setelah Mitra mengubah area pelayanan dan `reverification_required=true`, Admin bisa menyetujui ulang; flag kembali `false`. |
| Route Stage 2 `/akun/alamat` dan `/akun/pengaturan` menimpa placeholder Tahap 1 | Endpoint data nyata Stage 2 dipasang; nama route navigasi legacy (`account.addresses`, `account.settings`) masih mengarah ke alamat baru lewat redirect. |
| Route Tahap 4 `bookings.create` bertabrakan dengan booking demo | Dibedakan menjadi `bookings.database.create` untuk URL `/profil-mitra/{slug}/booking`. |
| Route Admin Tahap 4 `/admin/pesanan` bertabrakan dengan monitoring dummy | Dialihkan menjadi **`/admin/pesanan-db`** dengan route name `admin.bookings.*`. |
| Route Tahap 6 `/admin/laporan` bertabrakan dengan laporan localStorage | Dialihkan menjadi **`/admin/laporan-db`** dengan route name `admin.analytics.reports.*`. |
| Tahap 6 observer/scheduler belum didaftarkan | Provider observer dimasukkan ke `bootstrap/providers.php`, dan scheduler Tahap 4 dimuat oleh `routes/console.php`. |
| Layout modul memakai route `logout` yang tidak ada dalam Tahap 1 | Semua form logout memakai POST `auth.logout` dan `@csrf`. |
| Katalog database tidak memiliki tindakan booking | Detail layanan database dihubungkan ke form booking database; profil Mitra publik menyediakan tautan booking bila layanannya aktif. |

## Peta modul setelah merge

| Tahap | Menu / URL database utama |
|---|---|
| 1 — Login dan verifikasi | `/masuk`, `/daftar`, `/daftar/mitra`, `/admin/mitra/pendaftaran` |
| 2 — Profil dan alamat | `/akun/profil`, `/akun/alamat`, `/akun/pengaturan`, `/mitra/workspace/profil`, `/mitra/workspace/portofolio`, `/profil-mitra/{slug}` |
| 3 — Kategori/layanan | `/admin/kategori-db`, `/mitra/layanan-db`, `/katalog-jasa` |
| 4 — Booking/jadwal | `/profil-mitra/{slug}/booking`, `/akun/pesanan`, `/mitra/workspace/pesanan`, `/mitra/workspace/jadwal`, `/admin/pesanan-db` |
| 5 — Ulasan/pengaduan | `/ulasan-db`, `/ulasan-db/saya`, `/pengaduan-db`, `/mitra/ulasan-db`, `/admin/ulasan-db`, `/admin/pengaduan-db` |
| 6 — Dashboard/notifikasi | `/admin/overview`, `/admin/laporan-db`, `/mitra/workspace/overview`, `/akun/dashboard`, `/notifikasi` |
| 7 — QA | `scripts/stage7_audit.php`, `scripts/merged_contract_check.py`, `tests/Feature/Stage7IntegratedModulesTest.php` |

**Halaman prototipe demo masih dipertahankan**. Demo dan database sengaja berbeda; pesanan `localStorage` lama **tidak diimpor** sebagai transaksi resmi. Kode demo tidak boleh dipakai membuktikan review.

## Setup localhost dari arsip ZIP terpadu

1. Ekstrak arsip ke direktori kerja, kemudian `cd JasaWarga-Terpadu-Tahap1-7`. Arsip tidak memuat `vendor`, `node_modules`, maupun file `.env` pribadi.
2. Pastikan PHP CLI mempunyai `pdo_pgsql`, PostgreSQL aktif, Composer dan Node/npm tersedia.
3. Buat database kosong, contoh `jasawarga_dev`, serta user dengan izin create/alter table pada schema database itu.
4. Copy `.env.example` menjadi `.env` (Windows PowerShell `Copy-Item .env.example .env`), isi `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT=5432`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Isi password di `.env` lokal **jangan masukkan ke Git**.
5. Jalankan perintah di bawah:

```bash
composer install
npm install
php artisan key:generate
php artisan optimize:clear
php artisan migrate
php artisan storage:link
php artisan route:list
npm run build
php artisan test
node --test tests/js/*.test.mjs
php scripts/stage7_audit.php --mode=baseline
python scripts/merged_contract_check.py
```

Untuk development, jalankan `npm run dev` di satu terminal dan `php artisan serve` di terminal lain. Seeder kategori database opsional: `php artisan db:seed --class=ServiceCategorySeeder`. Akun dummy hanya jika secara sadar mengaktifkan seeder demo lokal, bukan untuk produksi.

**Jika database Tahap 1/3/5 sudah pernah dimigrasikan:** wajib backup data dulu; jalankan `php artisan migrate:status` dan pastikan migration yang baru ialah Tahap 2/4/6 + alter Tahap 2. Jangan gunakan `migrate:fresh` pada database yang berisi data penting. Jika teman sudah menerapkan migration Tahap 2/4/6 versi ZIP standalone di database yang sama, **jangan langsung jalankan migration terpadu**, karena Laravel mungkin mencatat versi/nama migration lama sementara tabelnya sudah ada; rekonsiliasi migration dan data perlu dilakukan manual setelah backup.

## Skenario pengujian penerimaan antarroll

1. Login Admin; buat kategori DB; daftar Mitra baru; Admin setujui; Mitra lengkapi profil dan area layanan; Mitra buat layanan DB.
2. Mitra atur jam kerja dan blok cuti; daftar Warga; Warga isi alamat; buka profil publik Mitra; buat booking DB dari kategori aktif.
3. Mitra konfirmasi booking; Warga menerima jika perlu; Mitra mulai pengerjaan; Mitra ajukan selesai; Warga konfirmasi selesai. Periksa `booking_status_histories`.
4. Warga menulis ulasan dengan **kode booking database** yang selesai dan layanan cocok; cek otomatis terverifikasi. Admin moderasi; Mitra balas. Coba kode demo dan pesanan milik Warga lain: harus ditolak sebagai bukti.
5. Warga buat pengaduan; Admin tangani dan catat log; cek dashboard dan statistik DB; notifikasi hanya dapat dibaca pemilik akun.
6. Dua Warga memilih slot Mitra yang sama dalam waktu hampir bersamaan: hanya satu booking aktif boleh dibuat. Test lintas-transaction terbaik memakai PostgreSQL sungguhan, bukan hanya SQLite.
7. Periksa 403/404 untuk akses alamat Warga lain, edit layanan Mitra lain, ulasan Mitra lain, notifikasi akun lain, dan seluruh route Admin oleh non-Admin.

## Batasan yang masih harus diuji / diselesaikan

- **Belum ada bukti uji migrate / Laravel Feature tests / browser end-to-end pada PostgreSQL di lingkungan ini.** Jalankan sesudah Composer terpasang, terutama migrasi alter dan query dashboard.
- **Penawaran harga estimasi belum terhubung ke model Tahap 3:** katalog saat ini menetapkan harga tetap. Jangan menjanjikan harga survei/negosiasi sebelum skema layanan diperluas.
- Pengaduan dapat mencantumkan kode referensi sebagai klaim pelapor; kepemilikannya **belum divalidasi otomatis** oleh modul pengaduan dan harus diperiksa Admin. Verifikasi ketat hanya diterapkan pada **ulasan**.
- Perubahan area layanan Mitra yang telah disetujui membuat profil tidak tampil sampai pemeriksaan ulang Admin.
- Data `localStorage` dan PostgreSQL adalah **dua sumber berbeda**. Modul demo masih berjalan secara lokal; fitur database pada path `-db`/`workspace` merupakan source resmi tahap backend.
- Fitur pembayaran / payment gateway, notifikasi push dan layanan produksi tambahan belum termasuk.
- `APP_DEBUG=true` hanya untuk localhost; dalam produksi nonaktifkan debug, siapkan HTTPS, izin dan review keamanan menyeluruh.

## Status validasi paket dari lingkungan integrasi

Lihat `docs/SINKRONISASI_VALIDASI.json` untuk angka terverifikasi. Tidak ada klaim status deploy produksi sampai semua pengujian di atas lulus.
