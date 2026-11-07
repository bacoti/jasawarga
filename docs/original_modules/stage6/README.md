# JasaWarga — Tahap 6: Dashboard, Analitik & Notifikasi Internal

Modul ini menambahkan dashboard database-driven untuk Admin, Mitra, dan Warga; laporan Admin dengan filter tanggal dan ekspor CSV; serta inbox notifikasi internal. UI menggunakan Blade + Tailwind/Vite, data dibaca dari PostgreSQL melalui Eloquent/Query Builder. Modul ini **bukan project Laravel lengkap** dan tidak menggantikan Tahap 1–5.

## Cakupan

- Admin overview: jumlah akun berdasarkan role, profil Mitra dan status verifikasi, booking, status pesanan, katalog kategori/layanan, ulasan dan laporan masalah bila tabel Tahap 5 tersedia, tren harian, booking terbaru, top Mitra dan layanan.
- Laporan Admin: filter 7/30/90 hari atau tanggal khusus maksimal 366 hari, distribusi status, layanan/Mitra teratas, dan ekspor CSV.
- Dashboard Mitra: total pesanan, permintaan menunggu respons, pesanan aktif, jadwal mendatang, dan pesanan terbaru milik Mitra login.
- Dashboard Warga: semua pesanan, aktif, selesai, menunggu persetujuan harga, dan aktivitas terbaru milik Warga login.
- Notifikasi internal: booking baru memberi notifikasi ke Mitra dan Admin; perubahan status booking memberi notifikasi ke Warga dan Mitra; inbox, tandai dibaca, tandai semua dibaca, unread badge.
- Preferensi `user_preferences.notifications.order_updates` dihormati bila tersedia; default aktif jika tidak ada data preferensi.
- Tidak ada email, push, WhatsApp API, payment gateway, atau scheduler notifikasi eksternal.

## Prasyarat dan dependensi skema

- Laravel 12/13, PHP yang sesuai, session auth, PostgreSQL.
- Tahap 1: `users` dengan `id`, `name`, `role`.
- Tahap 2: `partner_profiles` dengan `user_id`, `business_name`, `verification_status`, `PartnerProfile`, `EnsureAccountRole`; `user_preferences` opsional untuk preferensi notifikasi.
- Tahap 3: `services` dan `categories` (konfigurasi nama tabel/kolom aktif tersedia di `config/jasawarga-analytics.php`).
- Tahap 4: `bookings` dengan `booking_code`, `user_id`, `partner_profile_id`, `service_name_snapshot`, `status`, `created_at`, `scheduled_at`, serta `Booking` dan relasi `warga`, `partnerProfile`.
- Tahap 5: `reviews` (kolom `rating`, `created_at`) dan `reports` (kolom `status`, `created_at`) bersifat opsional. Modul tidak membuat tabel duplikat untuk keduanya.

Jika nama tabel katalog berbeda, sesuaikan `JASAWARGA_SERVICES_TABLE`, `JASAWARGA_CATEGORIES_TABLE`, dan kolom aktif di `.env` atau config.

## Instalasi

1. Backup project Laravel.
2. Ekstrak ZIP ke root project; file modul hanya menambahkan path baru.
3. Tambahkan `require __DIR__.'/analytics-stage6.php';` pada bagian bawah `routes/web.php`.
4. Daftarkan `App\Providers\JasaWargaStageSixServiceProvider::class` pada array `bootstrap/providers.php` (Laravel 11/12/13). Untuk versi lebih lama, daftarkan pada `config/app.php`.
5. Jalankan:

```bash
php artisan optimize:clear
php artisan migrate
php artisan route:list --name=analytics
php artisan route:list --name=notifications
php artisan test --filter=AnalyticsStageSixTest
```

## Route baru

| Path | Role | Nama |
|---|---|---|
| `/admin/overview` | Admin | `admin.analytics.dashboard` |
| `/admin/laporan` | Admin | `admin.analytics.reports` |
| `/admin/laporan/export` | Admin | `admin.analytics.reports.export` |
| `/mitra/workspace/overview` | Mitra | `partner.analytics.dashboard` |
| `/akun/dashboard` | Warga | `account.analytics.dashboard` |
| `/notifikasi` | Semua akun login | `notifications.index` |
| `/notifikasi/{notification}/baca` | Pemilik notifikasi | `notifications.read` |
| `/notifikasi/baca-semua` | Semua akun login | `notifications.read-all` |

## Interpretasi metrik

- Pesanan dihitung dari booking website. Klik tautan WhatsApp tidak membuat record booking dan tidak masuk metrik.
- KPI selesai dihitung berdasarkan `completed_at` pada periode terpilih (fallback ke `created_at` hanya jika kolom selesai belum tersedia); distribusi status tetap mengelompokkan booking yang dibuat pada periode berdasarkan status saat ini.
- `completed` adalah status pekerjaan, bukan bukti pembayaran/pelunasan. `agreed_price` hanya nilai kesepakatan pada booking; jangan menyebutnya pendapatan platform.
- Grafik harian dibatasi pada maksimal 90 hari terakhir; KPI tetap mengikuti seluruh rentang yang dipilih (maksimal 366 hari).
- Jumlah akun menggunakan `users.role`; jumlah Mitra profil memakai `partner_profiles`. Kedua angka bisa berbeda bila akun Mitra belum punya profil.
- Ulasan/pengaduan hanya tampil jika tabel Tahap 5 sudah ada. Jika tidak ada, UI menampilkan “Belum tersedia”, bukan nol. Jika tabel ulasan memiliki kolom moderasi `status`, rata-rata rating sengaja tidak dihitung sampai `JASAWARGA_REVIEWS_VISIBLE_STATUS` diatur sesuai status ulasan publik yang benar.
- Data daftar booking terbaru di dashboard Admin hanya ditampilkan kepada role Admin. Dashboard Warga dan Mitra memfilter berdasarkan pemilik/profil login.
- CSV tidak menyertakan nama, nomor telepon, email, alamat, catatan booking, atau identitas akun. Formula CSV diawali karakter berbahaya di-escape.

## Notifikasi

Observer Booking mengirim notifikasi database internal ketika booking dibuat atau status berubah. Daftarkan service provider agar observer aktif. Notifikasi mengikuti `order_updates` pada `user_preferences` untuk Warga/Mitra. Admin menerima notifikasi booking baru. Tidak ada email/push/WhatsApp yang dikirim.

Untuk menampilkan badge notifikasi pada layout yang sudah ada, tambahkan komponen:

```blade
<x-notification-bell />
```

## Batasan dan pemeriksaan

Source project Laravel utama tidak tersedia pada sesi pembuatan modul. Karena itu migration dan file PHP dapat diperiksa sintaksnya, tetapi `artisan migrate`, `route:list`, Feature Test PHPUnit, serta browser end-to-end harus dijalankan setelah modul digabungkan ke project Tahap 1–5. Jangan menganggap dashboard telah berjalan di database produksi sebelum pemeriksaan itu dilakukan.
