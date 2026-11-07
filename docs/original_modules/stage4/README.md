# JasaWarga — Tahap 4: Booking, Penjadwalan & Manajemen Pesanan

Paket ini adalah **modul Laravel siap integrasi**, bukan project Laravel lengkap. Source project Laravel utama tidak tersedia pada sesi ini; yang tersedia hanya prototipe UI HTML dan modul Tahap 2. Karena itu, file tidak dapat langsung digabungkan atau diuji terhadap konfigurasi aplikasi utama.

## Cakupan

- Warga membuat booking dari profil Mitra yang telah disetujui.
- Validasi jadwal minimal **36 jam** dari waktu server.
- Jadwal kerja mingguan Mitra (Minggu–Sabtu) dan blokir tanggal/jam libur.
- Pemeriksaan bentrok berdasarkan durasi layanan; proses pembuatan booking mengunci baris profil Mitra (`lockForUpdate`) agar dua request bersamaan tidak mengambil slot yang sama.
- Booking baru berstatus `pending_partner_confirmation` dan slot ditahan sampai Mitra merespons.
- Deadline respons Mitra **24 jam**; command scheduler mengubah booking yang tidak direspons menjadi `expired`.
- Mitra menerima/menolak booking.
- Untuk harga estimasi: Mitra menerima jadwal inspeksi, mengajukan harga final, lalu Warga menyetujui atau menolak.
- Penolakan harga setelah inspeksi dicatat sebagai `inspection_completed_no_repair`, bukan pembatalan otomatis.
- Mitra memulai pekerjaan dan mengajukan penyelesaian; Warga mengonfirmasi pekerjaan selesai.
- Pembatalan oleh Warga sesuai status yang diizinkan.
- Riwayat perubahan status disimpan.
- Dashboard pesanan Warga, Mitra, dan pemantauan Admin.

## Prasyarat integrasi

1. Laravel 12/13 dengan PHP yang kompatibel, autentikasi session, dan database PostgreSQL (rancangan utama JasaWarga).
2. Tahap 1: model `App\Models\User`, tabel `users`, login, dan role server-side (`warga`, `mitra`, `admin`).
3. Tahap 2: model/tabel `PartnerProfile`, `UserAddress`, middleware `EnsureAccountRole`, dan layout Blade `layouts.profile-stage2`.
4. Tahap 3: tabel katalog `services` sudah tersedia. Secara default modul mengharapkan kolom:

   | Kolom | Tipe / nilai |
   |---|---|
   | `id` | primary key |
   | `partner_profile_id` | foreign key ke `partner_profiles.id` |
   | `name` | nama layanan |
   | `price_type` | `fixed` atau `estimate` |
   | `price` | nominal harga (boleh null untuk estimasi) |
   | `duration_minutes` | durasi layanan dalam menit |
   | `is_active` | boolean |

   Jika nama tabel/kolom Tahap 3 berbeda, ubah pemetaan pada `config/jasawarga-booking.php`. Jangan menambah tabel `services` duplikat bila Tahap 3 sudah memilikinya.

## Integrasi file

1. Backup source project Laravel.
2. Ekstrak isi ZIP ke root project Laravel; file di dalam paket hanya file tambahan.
3. Tambahkan di bagian bawah `routes/web.php`:

   ```php
   require __DIR__.'/booking-stage4.php';
   ```

4. Tambahkan di bagian bawah `routes/console.php`:

   ```php
   require __DIR__.'/console-stage4.php';
   ```

5. Salin konfigurasi `config/jasawarga-booking.php` dan pastikan pemetaan layanan sesuai Tahap 3.
6. Jalankan:

   ```bash
   php artisan optimize:clear
   php artisan migrate
   php artisan storage:link
   php artisan route:list --name=bookings
   php artisan test --filter=BookingStageFourTest
   ```

7. Pastikan scheduler berjalan pada server produksi (`php artisan schedule:run` setiap menit melalui cron) atau jalankan lokal untuk uji:

   ```bash
   php artisan schedule:work
   ```

   Command `jasawarga:expire-bookings` dapat dijalankan manual untuk menguji kedaluwarsa.

## Migration yang ditambahkan

- `partner_working_hours`
- `partner_schedule_blocks`
- `bookings`
- `booking_offers`
- `booking_status_histories`

`bookings.service_id` disimpan sebagai ID referensi tanpa foreign key pada paket ini karena migration Tahap 3 tidak tersedia untuk diverifikasi. Integritas layanan dijaga melalui validasi aplikasi dan snapshot nama/harga/durasi pada saat booking. Setelah nama tabel dan migration Tahap 3 dipastikan, tambahkan foreign key `service_id` ke tabel layanan yang benar.

## Status booking

| Status | Makna |
|---|---|
| `pending_partner_confirmation` | Menunggu respons Mitra; slot ditahan; deadline 24 jam |
| `inspection_scheduled` | Mitra menerima booking harga estimasi; menunggu inspeksi/penawaran |
| `pending_price_approval` | Penawaran harga final menunggu keputusan Warga |
| `confirmed` | Jadwal dan harga telah disepakati |
| `in_progress` | Pekerjaan sedang berlangsung |
| `completion_pending` | Mitra mengajukan pekerjaan selesai; menunggu konfirmasi Warga |
| `completed` | Warga mengonfirmasi pekerjaan selesai |
| `inspection_completed_no_repair` | Warga menolak penawaran setelah inspeksi; inspeksi tercatat selesai tanpa perbaikan |
| `rejected` | Mitra menolak booking |
| `expired` | Mitra tidak merespons sampai deadline 24 jam |
| `cancelled` | Booking dibatalkan Warga sebelum pekerjaan berjalan |

## Route utama

| Role | Path | Nama route |
|---|---|---|
| Publik | `GET /profil-mitra/{slug}/slot-tersedia` | `bookings.availability` |
| Warga | `GET /profil-mitra/{slug}/booking` | `bookings.create` |
| Warga | `POST /profil-mitra/{slug}/booking` | `bookings.store` |
| Warga | `GET /akun/pesanan` | `account.bookings.index` |
| Warga | `GET /akun/pesanan/{booking:booking_code}` | `account.bookings.show` |
| Mitra | `GET /mitra/workspace/pesanan` | `partner.bookings.index` |
| Mitra | `GET /mitra/workspace/pesanan/{booking:booking_code}` | `partner.bookings.show` |
| Mitra | `GET /mitra/workspace/jadwal` | `partner.schedule.index` |
| Admin | `GET /admin/pesanan` | `admin.bookings.index` |

## Keamanan dan konsistensi

- Role berasal dari akun terautentikasi, bukan dari request body.
- Mitra hanya dapat melihat/mengelola booking milik profilnya.
- Warga hanya dapat melihat booking miliknya.
- Hanya Mitra pemilik yang dapat menerima/menolak dan mengajukan harga.
- Hanya Warga pemilik yang dapat menyetujui/menolak penawaran dan mengonfirmasi penyelesaian.
- Harga, nama layanan, dan durasi disalin sebagai snapshot saat booking untuk menjaga riwayat transaksi jika katalog berubah.
- Booking memakai transaksi database dan row lock pada `partner_profiles` saat validasi slot.
- Status transition dicatat pada `booking_status_histories`.
- Jalur WhatsApp tidak membuat record booking; hanya form website yang menghasilkan transaksi tercatat.

## Batasan yang perlu diketahui

- Paket ini belum terhubung ke source Laravel utama karena source lengkap tidak tersedia.
- Integrasi katalog layanan bergantung pada skema Tahap 3; pemetaan default disediakan di config.
- Tidak mengirim WhatsApp/SMS/email atau notifikasi push; ini dapat menjadi tahap berikutnya.
- Belum ada gateway pembayaran/deposit.
- Belum ada kalender Google/Outlook.
- Jam kerja default tidak otomatis di-seed: Mitra harus mengatur jam operasional sebelum menerima booking.
- Pengujian database/Feature Test perlu dijalankan di project Laravel yang memiliki dependency Composer dan koneksi database. Pada sesi pembuatan paket, hanya pemeriksaan sintaks PHP dan struktur arsip yang dapat dijalankan.

## Cara uji manual

1. Login sebagai Mitra, buka `/mitra/workspace/jadwal`, atur jam kerja untuk hari-hari operasional.
2. Login sebagai Warga, buka profil Mitra yang approved, pilih layanan aktif, alamat, tanggal dan jam minimal 36 jam ke depan.
3. Kirim booking; status harus `pending_partner_confirmation`.
4. Login sebagai Mitra dan terima booking.
5. Untuk layanan `fixed`, status menjadi `confirmed`. Untuk `estimate`, status menjadi `inspection_scheduled`.
6. Pada layanan estimasi, Mitra kirim penawaran final; Warga setujui atau tolak.
7. Mitra mulai pekerjaan, ajukan penyelesaian, dan Warga konfirmasi.
8. Uji kedaluwarsa dengan mengubah `partner_response_deadline` ke waktu lampau lalu jalankan `php artisan jasawarga:expire-bookings`.
