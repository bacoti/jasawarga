# JasaWarga — PROJECT TERPADU Tahap 1–7

**Versi sinkronisasi tim.** Source Tahap 1/3/5/7 dan ZIP Tahap 2/4/6 telah digabung ke **satu project Laravel 12 + PostgreSQL**. Sebelum menjalankan migration, **wajib baca [`docs/INTEGRASI_SEMUA_TAHAP.md`](docs/INTEGRASI_SEMUA_TAHAP.md)** untuk konflik yang diperbaiki, setup, peta URL, dan pengujian yang belum dilakukan. Status saat ini: source merge + lint/JS/static test lulus; **uji database dan visual masih diperlukan**. Jangan samakan data browser demo dengan data PostgreSQL.

---

# JasaWarga — Backend Tahap 1 (Laravel 12 + PostgreSQL)

**Pembaruan 7 November 2026:** Authentication Laravel Session tiga role (Warga/Mitra/Admin), registrasi Warga & Mitra, verifikasi admin untuk pendaftaran baru, migrasi PostgreSQL, dan seeder demo opsional. **Dashboard lama masih menggunakan localStorage sebagai data simulasi, dan Mitra baru tidak pernah melihat data Budi Teknik.**

- **ERD:** [`docs/ERD_Tahap_1.md`](docs/ERD_Tahap_1.md)
- **Petunjuk instalasi PostgreSQL & semua route:** [`docs/SETUP_POSTGRESQL.md`](docs/SETUP_POSTGRESQL.md)
- **Login:** `/masuk` | **Daftar Warga:** `/daftar` | **Daftar Mitra:** `/daftar/mitra`
- **Portal Mitra baru:** `/mitra/portal` | **Akun Warga DB:** `/admin/warga/akun` | **Verifikasi admin DB:** `/admin/mitra/pendaftaran`
- **Logout sungguhan:** form `POST /keluar` dengan CSRF, bukan GET redirect JavaScript.

> **Perubahan akses:** seluruh `/admin` memerlukan role `admin`; seluruh dashboard lama `/mitra/*` memerlukan role `partner` yang `approved` dengan `demo_provider_id=1`. `/akun/*` menggunakan session Warga. Halaman publik tetap dapat dikunjungi. Catatan versi frontend-only lama di bawah adalah riwayat development, bukan status autentikasi terbaru.

---

# JasaWarga — Laravel (Home + Kategori + Profil + Pemesanan + Pesanan Saya)

Project prototipe **frontend Laravel Blade responsif**, yang memulai dari dua desain HTML Home dan berkembang menjadi katalog, profil mitra, form pemesanan, konfirmasi dan riwayat pesanan simulasi.

## Persyaratan

- PHP **8.2+**, dengan ekstensi standar Laravel (contoh: mbstring, pdo, xml, openssl, fileinfo)
- Composer 2
- Node.js 20+ dan NPM
- Internet saat menginstal dependensi dan menampilkan foto/font eksternal dari HTML asal

Versi framework: **Laravel 12** (dipilih agar kompatibel dengan PHP 8.2+). Tidak membutuhkan PostgreSQL/MySQL ataupun migrasi.

## Menjalankan di Windows / macOS / Linux

Ekstrak ZIP, buka terminal di folder `JasaWarga-Laravel-Home`, kemudian:

```bash
composer install
```

Salin `.env.example` ke `.env`:

```bash
# Windows CMD:
copy .env.example .env
# macOS/Linux (pilih salah satu, jangan keduanya):
# cp .env.example .env
```

Generate app key:

```bash
php artisan key:generate
npm install
```

**Terminal 1:**
```bash
npm run dev
```

**Terminal 2:**
```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`. Untuk aset produksi, jalankan `npm run build` lalu `php artisan serve`. Jika Anda ingin menguji halaman: `php artisan test`.

**Catatan:** Jangan membuka `resources/views/*.blade.php` langsung sebagai file HTML. Jalankan dari server Laravel agar Vite dan Blade diproses.

## Struktur utama

```text
app/Providers/AppServiceProvider.php     Bootstrap aplikasi Laravel
bootstrap/app.php                       Konfigurasi Laravel
routes/web.php                          Route halaman Home, kategori, profil, booking, orders
resources/views/layouts/app.blade.php   HTML head, fonts, Vite
resources/views/home/index.blade.php    Responsive switch desktop/mobile
resources/views/home/desktop/           Header, konten, footer dari HTML desktop
resources/views/home/mobile/            Header, konten, bottom-nav dari HTML mobile
resources/views/components/             Dialog dan toast prototype
resources/css/app.css                   Tailwind + CSS asli dari kedua HTML
resources/js/home.js                    Interaksi demo pencarian/filter/dialog
resources/js/app.js                     JS entrypoint Vite
tailwind.config.cjs                     Token warna, font, ukuran dari HTML asli
public/assets/images/                  Fallback SVG bila foto eksternal gagal
```

## Perilaku prototipe

- **Desktop ≥ 1024 px** memuat tata letak desktop asli, **mobile/tablet < 1024 px** memakai komposisi mobile.
- Pencarian dan filter kategori/kartu mitra aktif di browser menggunakan data statis dari desain.
- Lokasi, notifikasi, bantuan dan peta masih bersifat demo. Kategori, profil mitra, pemesanan simulasi, dan Pesanan Saya sudah memiliki navigasi dan interaksi browser; **bukan transaksi sungguhan, GPS, ataupun data live**.
- Data nama, jarak, badge verifikasi, tarif, keterangan status, jumlah mitra, serta visual peta semuanya adalah **konten mockup dari HTML asal**; bukan data yang divalidasi di lapangan.
- URL foto eksternal `lh3.googleusercontent.com` tetap dipertahankan agar tampilan semirip mungkin dengan desain; jika gagal dimuat, tampil SVG fallback lokal. Google Fonts/Material Symbols juga dimuat melalui URL publik.
- Tidak ada login, database, controller bisnis, pembayaran, pemesanan real-time, API, atau pekerjaan backend lain.

## Catatan teknis penting

Dua desain sebenarnya memiliki **komposisi berbeda**, bukan sekadar scaling satu layout. Agar hasil konversi tidak kehilangan bagian, Blade memakai komponen desktop dan mobile terpisah di dalam *satu* route Home, dipilih menggunakan breakpoint CSS. Konten tetap memakai sistem warna dan tipografi desain asal. Tidak memakai CDN Tailwind untuk produksi; kelas dikompilasi lewat Vite saat `npm install` + `npm run dev/build`.

Project ZIP tidak menyertakan folder `vendor/`, `node_modules/`, atau `public/build/` karena semuanya merupakan hasil instalasi/build lokal. `composer.lock` dan `package-lock.json` akan dibuat di perangkat Anda saat dependensi dipasang.

## Halaman Kategori Jasa (tahap 2)

- URL: `http://127.0.0.1:8000/kategori-jasa`.
- Halaman Blade: `resources/views/categories/index.blade.php`.
- Card mitra dan panel filter: `resources/views/categories/partials/`.
- Data dummy kategori dan mitra: `config/jasawarga.php` (silakan ubah di sini).
- JavaScript katalog: `resources/js/categories.js`, diimpor dari `resources/js/app.js`.
- Semua kategori pada Home mengarah ke katalog. Contoh: `/kategori-jasa?kategori=ac`.
- Pencarian, urutan harga/rating/jarak, filter harga/garansi/ketersediaan serta modal profil adalah interaksi **frontend saja**. Data contoh tidak boleh dianggap sebagai mitra atau tarif riil.
- Tidak perlu migrasi, database, atau perintah build tambahan di luar perintah awal.
- Jika `config/jasawarga.php` tidak berubah setelah diedit pada instalasi dengan config cache, jalankan `php artisan optimize:clear`.

## Halaman Detail Profil Mitra (tahap 3)

- URL dinamis: `/mitra/{id}`, misalnya `http://127.0.0.1:8000/mitra/1`.
- Route: `routes/web.php` (`providers.show`). ID di luar daftar akan menghasilkan 404.
- Tampilan utama: `resources/views/providers/show.blade.php`.
- Data layanan, portofolio ilustratif, ulasan, dan area layanan: `config/jasawarga_profiles.php` (8 mitra, berpasangan dengan `config/jasawarga.php`).
- JavaScript untuk pilihan layanan, estimasi harga, tombol bagikan, dan modal pemesanan simulasi: `resources/js/provider-detail.js`.
- Kartu katalog di `resources/views/categories/partials/provider-card.blade.php` sekarang menggunakan tautan langsung ke `/mitra/{id}`. Tombol Pesan menuju bagian layanan pada profil tersebut.
- Tidak ada database/API, transaksi, verifikasi sungguhan, jadwal langsung, atau foto portofolio asli. Seluruh data bersifat fiktif dan telah ditandai sebagai demo.
- Pengujian opsional setelah dependencies terpasang: `php artisan test --filter=ProviderPageTest`.
- Bila data `config/` diubah dan belum terlihat, jalankan `php artisan optimize:clear`.

## Form Pemesanan Jasa (tahap 4)

- Detail Mitra → **Pesan Jasa** → `/pemesanan/{id}?layanan={index}` → `/pemesanan/berhasil`.
- `app/Http/Controllers/BookingPrototypeController.php` hanya menampilkan halaman dari data konfigurasi. Tidak ada endpoint POST, database, pembayaran, atau pengiriman ke mitra.
- `resources/views/bookings/create.blade.php`: formulir pemesanan untuk desktop dan mobile, tanggal di zona waktu **Asia/Makassar (WITA)**, pilihan empat slot simulasi 09.00/11.00/13.00/15.00, dan ringkasan tarif reaktif.
- `resources/views/bookings/success.blade.php`: ringkasan pemesanan simulasi, bukan konfirmasi pesanan aktif.
- `resources/js/booking.js`: validasi frontend (nama, WhatsApp Indonesia, area, alamat, tanggal, jam, deskripsi, persetujuan), perhitungan harga awal, dan perpindahan ringkasan melalui `sessionStorage` tab browser dengan masa berlaku tampilan 30 menit. WhatsApp **hanya disimpan dalam bentuk tersamarkan**. Tidak ada data yang dikirim ke server lewat form.
- Materi, tenaga tambahan, dan harga sebenarnya di lapangan **belum dihitung**. Angka di UI murni dummy.
- Sebagai prototipe, mekanisme ini tidak layak dijadikan pemesanan sungguhan; implementasi produksi perlu backend, validasi server, pengamanan data pribadi, penyimpanan transaksional, dan konfirmasi ketersediaan mitra.

Uji halaman: `php artisan test --filter=BookingPrototypePageTest` (setelah `composer install`).


## Halaman Pesanan Saya dan Tracking (tahap 5)

- URL daftar: `http://127.0.0.1:8000/pesanan-saya`
- URL detail contoh: `http://127.0.0.1:8000/pesanan-saya/JW-DEMO-20261107-A1B2C3` (hanya muncul jika kode tersimpan di browser).
- View baru: `resources/views/orders/index.blade.php`, `resources/views/orders/show.blade.php`.
- JavaScript baru: `resources/js/orders-store.js`, `resources/js/orders.js`.
- Kode form booking sebelumnya masih aktif. Saat simulasi dikonfirmasi, ringkasan tab tetap disimpan ke `sessionStorage`, sedangkan data riwayat baru ditulis ke `localStorage`.
- Filter status: Semua, Menunggu, Diproses, Selesai, Dibatalkan. Pencarian berdasarkan kode/mitra/layanan/area.
- Tombol `Tambahkan 3 pesanan contoh` memungkinkan demonstrasi langsung, tanpa data pribadi.
- Pada halaman detail, tombol `Simulasikan tahap berikutnya` bergerak: Menunggu → Diterima → Perjalanan → Dikerjakan → Selesai. Pembatalan hanya jika status Menunggu. Tombol `Pesan lagi` mengisi mitra dan indeks layanan ke form booking.
- Tombol `Beri ulasan` sekarang menuju halaman Rating & Ulasan untuk pesanan berstatus Selesai; bila sudah diulas, tombol berubah menjadi `Lihat ulasan`.
- Riwayat masih tersimpan setelah refresh, tetapi hanya pada browser/origin yang sama. Membersihkan data situs menghapus riwayat. **Tidak ada sinkronisasi antarperangkat, server, mitra, ataupun pembayaran.**
- **Privasi:** field `address` dan `description` dari form pemesanan dipertahankan di `localStorage` (tidak terenkripsi). Gunakan data **fiktif** saat demonstrasi, terutama pada perangkat bersama. Nama & nomor WA tidak dimasukkan ke daftar riwayat lokal; nomor WA hanya dimask dalam ringkasan `sessionStorage`.
- Hapus seluruh riwayat menggunakan tombol `Hapus seluruh riwayat demo`; `Hapus ringkasan sesi` pada halaman sukses tidak menghapus daftar.

### Pemeriksaan opsional

```bash
node --test tests/js/orders-store.test.mjs
php artisan test --filter OrderHistoryPageTest
```

Pengujian JavaScript di atas tidak perlu npm install; pengujian Laravel membutuhkan Composer sudah terpasang.


## Rating & Ulasan Pelanggan (tahap 6)

- Form: `/ulasan/tulis/{kode-pesanan}` (misalnya `/ulasan/tulis/JW-DEMO-20261107-A1B2C3`). Harus ada pesanan dengan kode sama dan status `completed` di `localStorage` browser ini.
- Riwayat ulasan: `/ulasan-saya`. Dapat dibuka langsung dari navigasi dan pesanan selesai.
- `resources/js/reviews-store.js`: menyimpan maksimal 30 ulasan lokal, satu ulasan per kode pesanan. Setiap ulasan berisi rating 1–5, tiga rating detail (kualitas, ketepatan waktu, keramahan), teks 20–800 karakter, dan maksimal 2 foto JPEG kompresi.
- `resources/js/reviews.js`: validasi frontend, tampilan bintang keyboard-accessible, konversi gambar menjadi JPEG kecil melalui canvas, daftar ulasan, penghapusan seluruh ulasan lokal, dan integrasi ulasan pada profil mitra.
- Batas input foto: JPG/PNG/WebP maksimal 2 MB per file. Gambar yang dipilih diubah menjadi thumbnail JPEG sebelum disimpan ke localStorage, **bukan diunggah ke server**. Penyimpanan dapat gagal jika kuota browser penuh; kondisi ini ditampilkan sebagai error dan tidak diklaim berhasil.
- `resources/views/providers/show.blade.php`: menampilkan **Ulasan dari simulasi Anda** di bagian terpisah. Angka rating/ulasan dummy awal **tidak dicampur** dengan ulasan lokal.
- Data browser tidak diautentikasi; pengguna bisa mengubah `localStorage`. Larangan ulasan ganda hanya diterapkan pada browser yang sama untuk keperluan prototype, **bukan jaminan keamanan produksi**. Data tidak sinkron antar perangkat.
- Untuk mencoba cepat: buka `/pesanan-saya` → **Tambahkan 3 pesanan contoh** → pilih pesanan contoh status Selesai → **Beri rating & ulasan**, atau gerakkan pesanan lain sampai Selesai dengan tombol simulasi.
- Agar tidak menyimpan foto atau informasi sensitif di perangkat bersama, gunakan gambar dan teks fiktif. Gunakan tombol **Hapus semua ulasan demo** di halaman Ulasan Saya untuk membersihkannya.

Uji JavaScript lokal (tanpa npm install):

```bash
node --test tests/js/*.test.mjs
```

Uji route Blade setelah Composer terpasang:

```bash
php artisan test --filter=ReviewPrototypePageTest
```

## Fitur tambahan: Akun Warga (frontend prototype)

Fitur ini menambahkan delapan halaman **tanpa database ataupun sistem autentikasi server**:

| URL | Nama route | Halaman |
| --- | --- | --- |
| `/masuk` | `account.login` | Login demo |
| `/daftar` | `account.register` | Registrasi demo |
| `/lupa-password` | `account.forgot` | Simulasi pemulihan password (tanpa email dikirim) |
| `/akun` | `account.profile` | Dashboard profil Warga |
| `/akun/edit` | `account.edit` | Edit nama, WA, dan foto kecil |
| `/akun/alamat` | `account.addresses` | CRUD alamat, pilih alamat utama |
| `/akun/pengaturan` | `account.settings` | Preferensi demo dan penghapusan akun |
| `/keluar` | `account.logout` | Tutup sesi simulasi |

**Akun bawaan**: `demo@jasawarga.test` (password demo apa pun minimal 6 karakter). Ini **bukan autentikasi sungguhan**: password tidak diverifikasi terhadap akun, tidak dikirim ke server, dan **tidak pernah disimpan** di `localStorage` / `sessionStorage`. Jangan menggunakan password sebenarnya.

- Profil akun, foto profil terkompresi, alamat, dan preferensi tersimpan di `localStorage` dengan kunci `jasawarga:prototype-accounts:v1`.
- Hanya penanda akun yang aktif tersimpan di `sessionStorage` dengan kunci `jasawarga:prototype-session:v1`; klik Logout akan menghapus sesi tersebut, tetapi tidak menghapus profil tersimpan.
- Alamat favorit secara otomatis mengisi form pemesanan jika akun demo sedang aktif. Seluruh isian tetap dapat diubah. Form hanya mengisi kecamatan jika ada pada daftar jangkauan mitra.
- Data pesanan (`jasawarga:prototype-orders:v1`) dan ulasan (`jasawarga:prototype-reviews:v1`) dari fitur lama **tetap bersifat umum pada browser ini dan tidak dipisahkan berdasarkan akun**. Jika berganti akun demo, riwayat tersebut tetap terlihat pada browser yang sama. Tidak ada klaim keamanan atau privasi antar akun.
- Fitur 'lupa password' hanya menampilkan pemberitahuan; tidak ada pesan email atau reset sungguhan.
- Untuk mencoba, jalankan `php artisan serve` dan `npm run dev` di dua terminal, lalu buka `http://127.0.0.1:8000/masuk`.
- Pengujian JavaScript: `node --test tests/js/*.test.mjs`; tes khusus akun: `node --test tests/js/account-store.test.mjs`.

**Catatan penting**: Gunakan email, foto, alamat, serta nomor telepon fiktif. `localStorage` bukan tempat yang aman untuk informasi sensitif. Tidak ada backend, session Laravel login, database, hash password, verifikasi email, atau proteksi route server untuk akun. Ini prototipe demonstrasi visual/interaksi, bukan sistem siap produksi.

## Dashboard Overview Mitra Jasa (tahap 9)

- URL: `http://127.0.0.1:8000/mitra/dashboard` (`partner.dashboard`).
- Saat ini hanya tersedia untuk **mitra demo Budi Teknik (ID 1)**. Identitas ditetapkan melalui `config/jasawarga.php` dan `config/jasawarga_profiles.php`, **bukan melalui autentikasi**. Jangan gunakan untuk data pelanggan nyata.
- Blade: `resources/views/partner/dashboard.blade.php` dan dua partial untuk sidebar desktop serta navigasi mobile.
- JavaScript: `resources/js/partner-store.js` (ringkasan metrik, ketersediaan, pembangkit pesanan fiktif) dan `resources/js/partner-dashboard.js` (tampilan/interaksi), dimuat lewat `resources/js/app.js`.
- Statistik hanya menghitung pesanan dengan `providerId = 1` dari `jasawarga:prototype-orders:v1` di `localStorage`, sama dengan Pesanan Saya. Perubahan status pada halaman Warga tampak di dashboard setelah event atau refresh, dan dapat tersinkron pada tab lain lewat event `storage` selama satu origin browser.
- **Estimasi tarif pekerjaan selesai** hanyalah penjumlahan harga contoh pada pesanan status `completed`, **bukan pendapatan diterima atau pembayaran terverifikasi**. Grafik menunjukkan 7 hari terakhir menurut tanggal selesai di riwayat status, menggunakan tanggal lokal browser.
- Tombol **Tambahkan 4 pesanan demo** menambahkan empat data dummy khusus mitra ID 1 dengan status berbeda. Data ini juga muncul di Pesanan Saya. Penambahan berulang membuat contoh baru.
- Status **Menerima pesanan / Sedang tidak tersedia** disimpan di `jasawarga:prototype-partner-settings:v1` pada browser ini. Status tersebut **memblokir slot pemesanan Warga pada prototype satu browser** setelah modul jadwal dipasang, tetapi bukan pengamanan backend.
- Menu **Kelola Pesanan**, **Layanan & Tarif**, dan **Jadwal** sekarang sudah berfungsi. **Pengaturan Mitra** masih placeholder. Profil Publik dan kembali ke Warga sudah aktif.
- Pengujian tambahan: `node --test tests/js/partner-store.test.mjs`, serta `php artisan test --filter=PartnerDashboardPageTest` setelah Composer dan dependensi tersedia.
- Tidak memerlukan database, migrations, role permission, library chart tambahan, atau perubahan config Tailwind.

## Kelola Pesanan Masuk Mitra (frontend prototype)

- URL: `http://127.0.0.1:8000/mitra/pesanan`
- Mitra demo yang ditampilkan: **Budi Teknik**, ID `1`.
- Pengelolaan pesanan Mitra membaca `jasawarga:prototype-orders:v1` dari `localStorage`, sama seperti Pesanan Saya milik Warga.
- Filter status/tanggal/jenis layanan, pencarian kode/area/nama, riwayat, detail pelanggan demo, terima, tolak dengan alasan (10–300 karakter), serta kelanjutan status hingga Selesai.
- **Penolakan Mitra (`rejected`) berbeda dari pembatalan Warga (`cancelled`)**. Alasan disimpan di histori pesanan, lalu tampil pada tracking Warga pada browser/origin yang sama.
- Data pesanan lama tetap terbaca. Untuk pesanan baru, nomor WhatsApp di store hanya berupa versi tersamarkan; isian pelanggan dan alamat **harus data fiktif**.
- Status pekerjaan kini hanya dinaikkan melalui panel Mitra; Warga tetap dapat membatalkan pesanan yang masih Menunggu Konfirmasi.
- Tanpa login Mitra, database, notifikasi sungguhan, locking transaksi, atau sinkronisasi antarperangkat. **Jangan gunakan untuk produksi atau data nyata**.

Untuk menguji modul JS: `node --test tests/js/*.test.mjs`; untuk Laravel (bila dependensi Composer telah terpasang): `php artisan test --filter=PartnerOrdersPageTest`.

## Kelola Layanan & Tarif Mitra (frontend prototype)

- **URL dashboard:** `http://127.0.0.1:8000/mitra/layanan` (`partner.services.index`). Hanya dashboard mitra demo **Budi Teknik (ID 1)** yang bisa mengubah layanannya melalui antarmuka. Belum ada login Mitra sebenarnya.
- **Buat, edit, aktif/nonaktif, hapus layanan**; tiap layanan memuat nama, kategori, deskripsi, durasi, tarif Rp 10.000–20.000.000, satuan, dan catatan biaya tambahan. Maksimal 40 layanan per mitra. Pencarian dan filter status/kategori disediakan.
- **Penyimpanan:** `localStorage` key `jasawarga:prototype-services:v1`, disimpan terpisah berdasarkan ID mitra. Data awal berasal dari `config/jasawarga_profiles.php` melalui Blade (tanpa hardcode ulang ke JavaScript); perubahan tidak menimpa data default PHP. Hapus semua layanan tetap menghasilkan daftar kosong, bukan muncul kembali otomatis.
- **Integrasi Warga:** halaman `/mitra/{id}` menampilkan layanan aktif dari storage yang sama; tombol Pesan mengirim ID permanen ke `/pemesanan/{id}?layanan=base-0` atau `custom-...`. Link `?layanan=0` lama tetap kompatibel. Form pemesanan membaca pilihan aktif serta nominal terbaru, mengecek ulang status layanan dan harga **tepat sebelum submit**, serta menolak pilihan lama yang dinonaktifkan/dihapus. Pengguna harus memilih layanan aktif lainnya; tidak dilakukan pemilihan layanan lain secara diam-diam saat link tidak valid.
- **Pesan Lagi:** riwayat baru menyimpan `serviceId` stabil selain `serviceIndex` lama. Harga/teks layanan pada pesanan lama tetap menjadi snapshot, sehingga perubahan etalase tidak mengubah pesanan yang sudah dibuat.
- **Satu browser/origin:** UI dapat melihat pembaruan dari tab lain via event `storage`. Tidak ada sinkronisasi backend, database, kontrol akses, atau jaminan inventori real-time. Gunakan data fiktif.
- **File penting:** `resources/views/partner/services/index.blade.php`, `resources/js/services-store.js`, `resources/js/partner-services.js`; pembaruan pada `provider-detail.js`, `booking.js`, `orders-store.js`, `orders.js`, dua Blade Warga, sidebar/nav Mitra, `routes/web.php`, dan `app.js`.
- **Jalankan:** `php artisan optimize:clear`, `npm run dev`, dan pada terminal lain `php artisan serve`; lalu buka `/mitra/layanan` → ubah layanan → lihat `/mitra/1#layanan` → coba `/pemesanan/1`.
- **Uji JS:** `node --test tests/js/*.test.mjs`; **uji Laravel:** `php artisan test --filter=PartnerServicesPageTest` (bergantung pada Composer/vendor).

**Batasan:** Pengguna yang membuka developer tools dapat mengubah `localStorage`. Validasi ini mencegah operasi pada prototipe UI, bukan pengamanan transaksi produksi. Untuk implementasi sebenarnya, data dan validasi tarif/status harus berada di server dan dibatasi berdasarkan otorisasi Mitra.


## Kelola Jadwal & Ketersediaan Mitra (frontend prototype)

- URL: `http://127.0.0.1:8000/mitra/jadwal` (route `partner.schedule.index`). Mitra simulasi: Budi Teknik (ID 1).
- Mengatur hari kerja Senin–Minggu, jam mulai/selesai, waktu istirahat opsional, serta slot waktu kedatangan. Setiap slot dianggap sebagai **jendela 60 menit** dan harus berjarak minimal 60 menit dari slot lainnya. Durasi layanan riil tidak dihitung sebagai durasi okupansi — ini **simulasi**, bukan mesin penjadwalan produksi.
- Jadwal awal mengikuti menu booking lama: **Senin–Sabtu 08.00–17.00 WITA**, istirahat 12.00–13.00, slot **09.00, 11.00, 13.00, 15.00**. Mitra yang data awalnya menyatakan buka setiap hari tetap memperoleh default Minggu aktif.
- Mendukung tanggal libur/cuti (tanggal + keterangan), kalender dengan penanda reservasi/libur, daftar pekerjaan mendatang, serta status **Menerima/Jeda Pemesanan** yang sinkron dengan toggle Dashboard Overview Mitra.
- Data disimpan di `localStorage` key `jasawarga:prototype-partner-schedules:v1` per ID Mitra. Status menerima pesanan tetap memakai key lama `jasawarga:prototype-partner-settings:v1`; riwayat booking tetap memakai `jasawarga:prototype-orders:v1` (tidak ada migrasi maupun penghapusan data lama).
- **Integrasi Form Pemesanan Warga**: daftar jam dinamis berdasarkan tanggal, aturan jadwal, hari libur, status tersedia, serta pesanan tersimpan. Pesanan `pending`, `accepted`, `enroute`, `working` mengunci interval 60 menit berdasarkan `date/time` dengan ID Mitra yang sama. `completed`, `cancelled`, `rejected` tidak menguncinya. Cek ulang ketersediaan dilakukan saat konfirmasi pemesanan, bukan hanya saat menggambar opsi.
- Perubahan jadwal yang menghilangkan slot milik pesanan masih menunggu/aktif ditolak, sehingga tidak diam-diam merusak booking yang sudah dicatat. Status jeda penerimaan **tidak membatalkan pesanan yang sudah ada**.
- Tombol Jadwal di sidebar desktop dan navbar bawah mobile sudah aktif; Dashboard Overview memiliki tautan ke pengaturan jadwal. Semua tanggal/jam kedatangan pemesanan memakai **Asia/Makassar (WITA)**; kalender browser tidak dipakai untuk menggeser tanggal booking.
- **Batasan keamanan dan teknis**: Perhitungan bentrok hanya berlaku pada satu origin `localStorage` di satu browser. Tidak ada lock transaksi atomik, sinkronisasi lintas perangkat, login Mitra yang diverifikasi server, atau kalender real-time. Jangan gunakan untuk menjadwalkan pekerjaan sungguhan. Gunakan data fiktif.
- Jalankan: `php artisan optimize:clear`, `npm run dev`, dan pada terminal lain `php artisan serve`. Coba `/mitra/jadwal` → tutup hari atau nonaktifkan slot → buka `/pemesanan/1` pada browser yang sama.
- Pengujian JS keseluruhan: `node --test tests/js/*.test.mjs`; khusus jadwal: `node --test tests/js/schedule-store.test.mjs`. Pengujian Laravel (jika dependensi sudah terpasang): `php artisan test --filter=PartnerSchedulePageTest`.

## Kelola Profil & Portofolio Mitra (frontend prototype)

- URL: `http://127.0.0.1:8000/mitra/profil` (route `partner.profile.index`), menggunakan **Budi Teknik (ID 1)** sebagai Mitra demo. Navigasi sidebar dan nav bawah sudah terintegrasi; tampilan publik ada di `/mitra/1`.
- Edit nama usaha, tagline, deskripsi, pengalaman dalam tahun, WhatsApp opsional, maksimal lima spesialisasi dan satu atau beberapa kecamatan di Kota Makassar. Data awal diperoleh dari `config/jasawarga.php` dan `config/jasawarga_profiles.php`.
- Upload foto profil dan cover (JPG, PNG atau WebP, ukuran file awal maksimal 2 MB). File **tidak diunggah ke server**; Canvas mengubahnya menjadi JPEG berukuran lebih kecil sebelum disimpan ke browser. Simpan profil agar perubahan diterapkan.
- Kelola maksimal **9 portofolio**: tambah, edit, hapus dengan konfirmasi, judul, deskripsi, kecamatan pengerjaan dan foto opsional. Tiga entri bawaan tetap dilabeli **ilustrasi** sampai diedit atau dihapus.
- Data disimpan per ID mitra di `localStorage` key `jasawarga:prototype-partner-profiles:v1`. Data tidak memengaruhi transaksi historis, layanan/tarif, jadwal atau rating/ulasan yang sudah tersimpan. Komunikasi antar tab satu browser melalui event `storage`.
- **Status verifikasi read-only**, berasal dari `config/jasawarga.php` dan tidak ditulis ke `localStorage`. Badge verifikasi pada profil Warga tetap mengikuti data server demo, tidak pernah berubah akibat edit profil.
- Pada halaman publik `/mitra/1` perubahan nama, tagline, pengalaman, deskripsi, spesialisasi, coverage, avatar, cover dan portofolio dirender ulang **hanya saat ada override valid di browser**. Halaman mitra lain mempertahankan seed PHP masing-masing.
- Tidak ada backend upload, akun Mitra sungguhan, moderasi konten, jaminan persistensi antar perangkat, atau perlindungan privasi foto/kontak di localStorage. Gunakan data/foto fiktif saja. Browser dapat menolak simpan jika kuota localStorage penuh; pesan error akan muncul.
- Untuk menjalankan: `php artisan optimize:clear` lalu `npm run dev`, di terminal kedua `php artisan serve`. Uji `/mitra/profil` → edit/simpan → lihat `/mitra/1#tentang` dan `/mitra/1#portofolio`.
- Pengujian data JS: `node --test tests/js/partner-profile-store.test.mjs`; semua JS: `node --test tests/js/*.test.mjs`. Pengujian route/Blade ketika Composer tersedia: `php artisan test --filter=PartnerProfilePageTest`.

## Kelola Ulasan Pelanggan Mitra — Tahap Berikutnya

- Halaman baru: `/mitra/ulasan` (mitra demo Budi Teknik, ID 1). Tersedia dari sidebar desktop, navigasi bawah mobile, serta tombol pada Dashboard Overview.
- Statistik rating lokal, distribusi 1–5 bintang, rata-rata kualitas/ketepatan/keramahan, daftar ulasan, filter bintang/tanggal/layanan/balasan, pencarian, urutkan, dan pratinjau foto ulasan.
- Buat/edit/hapus **balasan Mitra** 10–500 karakter. Balasan dipisahkan dari `jasawarga:prototype-reviews:v1` dan hanya diizinkan untuk ulasan lokal dengan `providerId` yang sama. Key balasan: `jasawarga:prototype-partner-review-replies:v1`.
- Balasan tampil di `/mitra/1#ulasan-lokal`, tepat di bawah ulasan Warga dan tetap menggunakan `textContent` agar HTML dari browser tidak dirender sebagai markup.
- Tombol **Tambah 3 ulasan contoh** membuat pesanan berstatus selesai untuk Budi Teknik dan ulasan fiktif (tanpa foto). Dapat dicoba tanpa mengisi form Warga secara manual.
- Uji unit JavaScript: `node --test tests/js/*.test.mjs`; tes route Laravel jika dependency sudah terpasang: `php artisan test --filter=PartnerReviewsPageTest`.
- **Batasan:** semua data hanya tersimpan di browser yang sama. Tidak ada login/otorisasi Mitra server, pengiriman pesan nyata, moderasi ulasan, API, atau sinkronisasi antarperangkat. Gunakan data fiktif, bukan informasi pelanggan asli.

## Pengaturan Akun Mitra — Tahap frontend-only

- **URL:** `/mitra/pengaturan` (`partner.settings.index`), tersedia dari sidebar dan navigasi mobile semua halaman Mitra. Demo Mitra ID **1 (Budi Teknik)**. Setelah tombol logout, halaman `/mitra/masuk` (`partner.demo.entry`) menyediakan tombol untuk kembali ke dashboard.
- **Informasi pemilik akun:** nama, email, WhatsApp (opsional). Data ini **terpisah** dari nama dan kontak usaha pada `/mitra/profil`, sehingga identitas pengelola tidak dipublikasikan otomatis.
- **Preferensi notifikasi:** pesanan baru, perubahan status, ulasan pelanggan — **hanya disimpan** dan belum menjalankan pengiriman push/email/WhatsApp.
- **Privasi:** pengaturan apakah nomor kontak usaha dari profil dan daftar area jangkauan ditampilkan di `/mitra/1` pada browser yang sama. Status verifikasi tetap diambil dari `config/jasawarga.php` dan **read-only**.
- **Preferensi operasional:** tampilkan/sembunyikan grafik dan kartu estimasi tarif pada Dashboard Overview. Preferensi pengingat jadwal belum memicu notifikasi sungguhan. Tombol **Menerima pesanan** menggunakan key ketersediaan `jasawarga:prototype-partner-settings:v1` yang sudah digunakan Dashboard/Jadwal/Form Pemesanan.
- **Password:** form hanya memeriksa panjang dan konfirmasi. Password **tidak dikirim, tidak disimpan, tidak diverifikasi, tidak berubah**. Jangan gunakan password sesungguhnya saat demonstrasi.
- **Logout simulasi:** hanya menghapus penanda `sessionStorage` `jasawarga:prototype-partner-demo-session:v1`, kemudian membuka `/mitra/masuk`. Tidak ada middleware autentikasi: dashboard tetap bisa diakses langsung dan jangan menganggap fitur ini sebagai keamanan akun.
- **Penyimpanan akun:** `localStorage` key `jasawarga:prototype-partner-account:v1`, berdasarkan ID Mitra. Tidak menghapus/mengubah pesanan, layanan, jadwal, profil, atau ulasan yang sudah ada. Pengaturan hanya sinkron antar-tab pada **origin dan browser yang sama**.
- **File utama:** `resources/views/partner/settings/index.blade.php`, `resources/views/partner/settings/entry.blade.php`, `resources/js/partner-account-store.js`, `resources/js/partner-settings.js`. Perubahan pada route, sidebar/nav, dashboard, dan profil publik.
- **Jalankan:** `php artisan optimize:clear`, `npm run dev`, dan pada terminal lain `php artisan serve`, lalu buka `http://127.0.0.1:8000/mitra/pengaturan`.
- **Uji data JavaScript:** `node --test tests/js/partner-account-store.test.mjs`; semua pengujian: `node --test tests/js/*.test.mjs`. Uji Laravel jika dependensi Composer sudah ada: `php artisan test --filter=PartnerSettingsPageTest`.

**Batasan:** Ini prototype bukan aplikasi siap produksi. Jangan gunakan data pribadi atau password sungguhan: `localStorage` tidak cocok untuk menyimpan data rahasia. Keamanan riil harus memakai autentikasi Laravel di server, session/CSRF, validasi dan otorisasi berbasis role, password hash, serta database.

## Dashboard Admin Overview — Prototipe Frontend

- **URL:** `http://127.0.0.1:8000/admin/dashboard` (`admin.dashboard`). Dapat diakses dari menu `Demo Admin` di Home desktop, halaman Profil Warga, atau tautan di sidebar Mitra. **Tidak dilindungi autentikasi server** dan hanya cocok untuk demonstrasi.
- **Struktur:** `resources/views/admin/dashboard.blade.php`, `resources/views/admin/partials/sidebar.blade.php`, `resources/views/admin/partials/mobile-nav.blade.php`; CSS menggunakan utilitas Tailwind yang sudah ada, tanpa library grafik tambahan. JavaScript di `resources/js/admin-dashboard.js` dan fungsi kalkulasi murni `resources/js/admin-overview-data.js`.
- **KPI Data browser:** akun Warga (dari `readAccounts()`), pesanan (dari `readOrders()`), status menunggu/aktif/selesai, ulasan (dari `readReviews()`), dan **estimasi tarif** untuk pekerjaan selesai. Semua terbatas pada localStorage browser/origin yang sama. Estimasi tarif **bukan uang masuk maupun pendapatan nyata**.
- **KPI Data contoh katalog:** jumlah mitra dan badge verifikasi dari `config/jasawarga.php`. Jumlah layanan aktif berasal dari daftar bawaan `config/jasawarga_profiles.php` digabungkan dengan override `jasawarga:prototype-services:v1` pada browser. Tidak ada pendaftaran Mitra sungguhan.
- **Grafik:** jumlah pesanan dibuat dan selesai per hari selama 7 hari kalender lokal terakhir; tombol `Dibuat / Selesai`. Klik `Tambahkan 3 pesanan demo` jika browser belum memiliki riwayat; tombol ini sengaja menulis data contoh yang juga terlihat di halaman Pesanan Saya dan Dashboard Mitra.
- **Tabel pesanan terbaru:** pencarian kode, mitra, layanan, kecamatan; filter status; delapan hasil terbaru; klik kode untuk membuka detail Pesanan Saya. Tidak menampilkan nomor WhatsApp atau alamat pelanggan pada Dashboard Admin.
- **Verifikasi mitra:** memperlihatkan profil katalog yang memiliki `verified=false` sebagai **contoh belum diverifikasi**, **bukan permohonan verifikasi atau dokumen yang masuk**. Tampilan baca-saja; tombol `Lihat` hanya menampilkan penjelasan. Persetujuan/penolakan harus menunggu implementasi backend yang aman.
- **Aktivitas platform:** berdasarkan histori status pesanan, ulasan lokal, dan waktu pembuatan akun lokal yang dapat dibaca browser ini. Nama akun Warga, nomor kontak, dan alamat tidak dicantumkan pada timeline.
- **Navigasi selanjutnya:** Warga, Mitra & Verifikasi, Kategori, Ulasan & Pengaduan, Laporan, serta Pengaturan adalah placeholder yang menjelaskan bahwa modul tersebut akan dikembangkan berikutnya; bukan tombol tidak berfungsi tanpa penjelasan. Tautan yang memang tersedia (pesanan, aktivitas, Home, Dashboard Mitra) sudah aktif.
- **Update antar-tab:** event `storage` dan event custom dari store terkait. Data yang berasal dari browser/perangkat berbeda **tidak tersinkron**.
- **Pengujian:** `node --test tests/js/*.test.mjs` dan khusus Admin `node --test tests/js/admin-overview-data.test.mjs`; bila Composer telah terpasang, `php artisan test --filter=AdminDashboardPageTest`.
- **Jalankan:** `php artisan optimize:clear`, `npm run dev`, dan di terminal kedua `php artisan serve`.

**Batasan:** ini sekadar frontend prototype, bukan panel Admin produksi. Tidak ada autentikasi, otorisasi, persetujuan verifikasi sungguhan, akses data lintas pengguna, database, atau laporan pembayaran. Gunakan data sepenuhnya fiktif dan jangan menggunakannya untuk proses operasional nyata.

---

## Modul Admin Manajemen Warga & Manajemen Mitra/Verifikasi (prototipe)

Modul lanjutan Dashboard Admin Overview:

| Halaman | Route | Fungsi |
|---|---|---|
| Manajemen Warga | `/admin/warga` | Statistik akun Warga lokal, cari dan filter, detail terbatas, pesanan yang benar-benar tertaut dengan `accountId`, simulasi aktif/nonaktif, log dan alasan wajib |
| Mitra & Verifikasi | `/admin/mitra` | Daftar 8 profil katalog dummy, statistik status, cari/filter kategori dan verifikasi, ringkasan layanan/pesanan, simulasi persetujuan/penolakan, catatan alasan dan log |
| Dashboard Admin | `/admin/dashboard` | Tautan ke kedua modul, pembaruan KPI verifikasi dan status akun, serta aktivitas administratif demo |

### Penyimpanan dan integrasi

- Data akun dasar tetap pada `jasawarga:prototype-accounts:v1` dan pesanan tetap di `jasawarga:prototype-orders:v1`.
- Status akun dan log Admin tersimpan **terpisah** di `jasawarga:prototype-admin-citizens:v1`. Status `inactive` hanya penanda tampilan, **bukan** pemblokiran akses akun atau login sungguhan.
- Status verifikasi mitra dan log disimpan terpisah di `jasawarga:prototype-admin-partners:v1`. Bila tidak ada override lokal, nilai awal `verified` berasal dari `config/jasawarga.php` (data contoh). Status `pending`, `verified`, `rejected` semuanya **simulasi**; Admin belum memeriksa dokumen apa pun. Mitra tetap tidak dapat mengubah verifikasi lewat form edit profil sendiri.
- Perubahan status verifikasi terlihat di Dashboard Admin, kategori, profil publik Mitra, dan tampilan read-only pengelolaan profil Mitra dalam browser/origin yang sama.
- Pesanan **baru** yang dibuat saat sesi Warga demo aktif menyimpan `accountId`, kemudian ditautkan dengan ID akun yang valid. Pesanan lama tanpa `accountId` **tidak** ditebak menggunakan nama, nomor kontak, atau alamat. Detail Warga hanya memperlihatkan kecamatan dan nomor tersamarkan; tidak memamerkan alamat lengkap.
- Tindakan simpan wajib menyertakan alasan 10–220 karakter. Log mempertahankan hingga 80 perubahan per modul.
- Semua route Admin dapat dikunjungi tanpa login server: ini hanya **prototipe antarmuka**, tidak boleh dipakai untuk pengelolaan identitas, dokumen sensitif, atau otorisasi sungguhan.

### Install pembaruan

Dari direktori utama project:

```bash
php artisan optimize:clear
npm install
npm run dev
php artisan serve
```

Jalankan pengujian JavaScript (Node 20+):

```bash
node --test tests/js/*.test.mjs
```

Fitur ini **belum menggunakan PostgreSQL, backend Admin, session auth, kebijakan akses, audit server, atau penyimpanan lintas perangkat**. Jika ingin melanjutkan ke aplikasi nyata, buat model Warga/Mitra/Verifikasi dan tabel relasional di database, endpoint terproteksi middleware Admin, validasi akses, pemeriksaan berkas privat, dan log audit di server.

---

## Admin: Manajemen Kategori, Monitoring Pesanan, Ulasan & Pengaduan (frontend demo)

Tiga halaman baru memperluas Dashboard Admin sebelumnya. Ketiganya **hanya menggunakan localStorage browser pada origin yang sama**, bukan API/server, tidak membutuhkan migration dan tidak memberikan hak akses Admin sungguhan.

| Halaman | Route | Kemampuan |
| --- | --- | --- |
| Manajemen Kategori Jasa | `/admin/kategori` | Daftar, tambah, edit nama/deskripsi/ikon/palet (slug tetap), aktif/nonaktif, hapus hanya jika tidak digunakan; pencarian/filter; statistik |
| Monitoring Pesanan | `/admin/pesanan` | KPI, distribusi status, filter mitra/status/7–30 hari, detail linimasa dan CSV ringkas **tanpa identitas pengguna**; baca-saja |
| Ulasan & Pengaduan | `/admin/ulasan-pengaduan` | Review lokal Warga, filter, moderasi tampil/sembunyi beralasan; buat kasus pengaduan fiktif, alur status, riwayat tindakan |

**Sumber data yang berbeda:**
- Kategori awal dan delapan Mitra berasal dari `config/jasawarga.php` sebagai katalog *dummy*. Perubahan kategori Admin tersimpan di `jasawarga:prototype-admin-categories:v1`.
- Layanan Mitra memiliki override dari `jasawarga:prototype-services:v1`. Kategori nonaktif tidak tersedia untuk layanan baru. Form Pemesanan dan Profil Publik menghapus layanan dari pilihan jika kategorinya dinonaktifkan. Pesanan lama dan snapshot tarif tidak dimodifikasi.
- Monitoring menggunakan `jasawarga:prototype-orders:v1` (maksimal 50 riwayat, hanya pada browser ini), termasuk status yang diperbarui oleh alur Warga/Mitra. Admin tidak bisa mengubah status pesanan pada modul Monitoring.
- Ulasan asli menggunakan `jasawarga:prototype-reviews:v1`; moderasi dan pengaduan fiktif disimpan terpisah di `jasawarga:prototype-admin-feedback:v1`. Ulasan yang disembunyikan Admin hanya disembunyikan dari **daftar ulasan lokal di profil publik Mitra**. Riwayat ulasan Warga dan manajemen ulasan Mitra tetap mencatat ulasan aslinya. Rating seed katalog PHP tidak termasuk ulasan yang bisa dimoderasi.
- Pengaduan baru hanya dapat **dicatat oleh Admin Demo** melalui form kasus; **belum ada formulir pengaduan Warga** maupun pelacakan sengketa oleh pelanggan. ID pesanan yang ditautkan wajib sudah ada di browser. Komentar/rating asli tidak dihapus atau ditimpa saat moderasi.
- Seluruh perubahan status dan alasan tersimpan hanya lokal, serta ditampilkan pada aktivitas Dashboard Admin.

### Cara menggunakan

1. Tempel ZIP perubahan ke root project yang sudah ada (atau gunakan ZIP lengkap). Jangan hapus `.env`/`vendor`/`node_modules` lokal pada projectmu.
2. Jalankan `php artisan optimize:clear`, `npm install` (jika diperlukan), `npm run dev`, dan `php artisan serve` pada terminal terpisah.
3. Buka `/admin/kategori`, nonaktifkan kategori misalnya `ac`, lalu buka `/kategori-jasa`, `/mitra/1`, dan `/pemesanan/1` di browser yang sama. Layanan pada kategori tersebut tidak dapat dipesan baru; aktifkan kembali setelah menguji.
4. Buat atau tambahkan pesanan contoh dari `/admin/dashboard`, lalu pantau di `/admin/pesanan`.
5. Tambahkan ulasan contoh dari `/mitra/ulasan`, lalu coba moderasi di `/admin/ulasan-pengaduan`; profil publik Mitra dapat dicek di `/mitra/1#ulasan-lokal`.
6. Pada tab Pengaduan, catat kasus **fiktif**, pilih kode pesanan lokal jika ada, lalu ubah status dengan alasan 10–300 karakter.

### Perintah pemeriksaan

```bash
node --test tests/js/*.test.mjs
node --check resources/js/admin-categories.js
node --check resources/js/admin-orders.js
node --check resources/js/admin-feedback.js
php -l routes/web.php
# Setelah composer install:
php artisan test --filter=AdminOperationsPagesTest
```

**Batasan keamanan dan produksi:** seluruh route `/admin/*` masih terbuka tanpa middleware autentikasi; siapa saja yang membuka browser yang sama dapat memanipulasi data lokal. File CSV hanya ringkasan ilustrasi. Tidak ada moderasi server yang enforceable, pencatatan kasus/aduan resmi, otorisasi, backup, audit anti-manipulasi, maupun pengiriman notifikasi. Jangan memasukkan data pribadi atau kasus nyata hingga backend, validasi server, peran akses, dan perlindungan data selesai dibangun.

---

## Admin: Laporan & Analitik + Pengaturan Akun (frontend demo)

Dua modul terakhir melengkapi navigasi Admin. Project tetap **Laravel Blade + Tailwind CSS + JS + localStorage tanpa database**. Tidak ada route POST, endpoint privat, autentikasi atau hak akses sungguhan. Semua angka lokal adalah **ilustrasi perilaku di satu browser**, bukan laporan transaksi platform nyata.

### Route baru

| Halaman | Route | Nama Laravel |
| --- | --- | --- |
| Laporan & Analitik | `/admin/laporan` | `admin.reports` |
| Pengaturan Admin | `/admin/pengaturan` | `admin.settings` |
| Masuk mode Admin Demo | `/admin/masuk` | `admin.demo.entry` |

### Laporan & Analitik

- Filter 7 / 30 / 90 hari, seluruh **data yang masih tersimpan** di browser, atau tanggal khusus (maks. 366 hari, tidak melewati hari ini).
- KPI pesanan dibuat, pesanan selesai (status saat ini dari pesanan yang dibuat dalam periode), ulasan lokal, pengaduan, akun Warga baru, rerata rating ulasan yang **tampil** setelah moderasi, proporsi status selesai, dan estimasi tarif pekerjaan yang diselesaikan pada rentang tanggal tersebut.
- Grafik tren pesanan dibuat / diselesaikan / ulasan menurut tanggal kalender browser. Mode Semua Data menampilkan **30 hari terakhir di grafik** tetapi KPI/tabel menghitung semua data lokal yang masih disimpan.
- Distribusi status, peringkat Mitra & kategori menurut jumlah pesanan lokal, panel aktivitas tindakan Admin terbaru.
- Ekspor tiga jenis CSV: ringkasan, pesanan, serta status pengaduan. Tidak mengekspor nama pelanggan, telepon, email, alamat, deskripsi pengaduan atau foto. Formula CSV di-*escape* agar lebih aman saat dibuka di spreadsheet. Saat opsi estimasi tarif dimatikan, angka tarif juga tidak diekspor.
- Sumber **katalog demo** (Mitra, kategori, layanan aktif) dihitung dari `config/jasawarga.php`, `config/jasawarga_profiles.php`, dan override `localStorage`, ditampilkan di panel terpisah tanpa klaim pendaftaran sungguhan. Kategori yang baru ditambahkan Admin di browser dapat masuk hitungan katalog saat ini.
- Sumber **data browser**: pesanan (`jasawarga:prototype-orders:v1`), ulasan (`jasawarga:prototype-reviews:v1`), akun (`jasawarga:prototype-accounts:v1`), pengaduan/moderasi (`jasawarga:prototype-admin-feedback:v1`), serta audit pengelolaan Warga/Mitra (`jasawarga:prototype-admin-citizens:v1`, `jasawarga:prototype-admin-partners:v1`). Batas lama tetap berlaku: order hanya menyimpan hingga **50**, review hanya **30**, pengaduan maksimum **60**. Karena itu **Semua Data bukan histori server tanpa batas**.
- Tidak ada laporan pembayaran: nominal `price` pada pesanan adalah **tarif estimasi/snapshot**, bukan hasil penagihan, profit, atau pendapatan resmi.

**Perbedaan dua metrik selesai:** “Selesai (status saat ini)” menghitung pesanan yang **dibuat** pada periode dan kini memiliki status selesai; “Selesai dalam periode” menghitung **peristiwa perubahan status menjadi selesai** dalam periode (meski pesanan dibuat sebelumnya). Estimasi tarif selesai memakai peristiwa kedua. Persentase selesai dihitung sebagai jumlah status selesai dari pesanan yang dibuat periode tersebut dibagi jumlah pesanan dibuat pada periode tersebut.

### Pengaturan Admin

- Edit nama tampilan dan email **fiktif**; nama Admin disinkronkan dengan footer sidebar pada halaman Admin.
- Preferensi notifikasi pesanan baru, pengaduan, ulasan, serta verifikasi **hanya disimpan, belum ada pengiriman push, email atau WA**.
- Preferensi laporan: periode default, kepadatan tabel laporan, tampil/sembunyikan estimasi tarif di Dashboard & Laporan/CSV, dan pengurangan animasi.
- Validasi password contoh (8–128 karakter) **hanya di memori saat tombol diklik**; tidak ada password yang disimpan, dikirim, dibandingkan dengan hash, atau digunakan sebagai kredensial.
- Logout demo hanya menghapus penanda `sessionStorage` dan membuka `/admin/masuk`. Tombol masuk lagi hanya membuat penanda `sessionStorage`; route tetap dapat diakses tanpa login. Ini **bukan mekanisme keamanan**.
- Menyetel ulang preferensi tidak menghapus identitas demo, keputusan verifikasi, pengaduan, ulasan, layanan, kategori, maupun pesanan. Data Admin disimpan pada `jasawarga:prototype-admin-settings:v1`.

### Install dan pemeriksaan

Salin ZIP file baru/perubahan ke folder root project (jangan menimpa `.env` lokal), lalu jalankan:

```bash
php artisan optimize:clear
npm install
npm run dev
# terminal kedua
php artisan serve
```

Kunjungi `/admin/laporan`, pilih periode, coba ekspor CSV dan lihat data; buka `/admin/pengaturan` untuk mengubah preferensi dan cek hasilnya di halaman laporan/Dashboard. Uji JavaScript (Node 20+):

```bash
node --test tests/js/*.test.mjs
php -l routes/web.php
# Hanya setelah composer install
php artisan test --filter=AdminReportsSettingsPagesTest
```

**Untuk produksi wajib menambahkan autentikasi Laravel, middleware role Admin, kontrol akses berbasis policy, API/migration PostgreSQL, agregasi server, pengelolaan sesi, audit anti-manipulasi, sanitasi unggahan, keamanan ekspor, dan pengujian UI serta E2E.** Tidak aman menggunakan data atau kredensial sungguhan pada prototype ini.

---

## Backend Tahap 3 — Kategori & Layanan Jasa (PostgreSQL)

Tahap ini dibuat **di atas kode Tahap 1** karena Tahap 2 sedang dikerjakan rekan yang berbeda. Jangan overwrite pekerjaan Tahap 2 dengan ZIP project lengkap Tahap 3. Baca **`docs/INTEGRASI_TAHAP2_TAHAP3.md`** untuk panduan cherry-pick/merge.

- Route Admin: **`/admin/kategori-db`** (CRUD kategori, status, hapus aman, pencarian).
- Route Mitra approved: **`/mitra/layanan-db`** (CRUD layanan sendiri, harga/durasi/status, arsip soft-delete).
- Katalog publik database: **`/katalog-jasa`** dan **`/katalog-jasa/{id}`** (hanya Mitra approved dan layanan/kategori aktif).
- Model/tabel: `ServiceCategory` → `service_categories`; `PartnerService` → `partner_services`.
- Seeder opsional: `php artisan db:seed --class=ServiceCategorySeeder` untuk taksonomi kategori awal (idempotent; **tidak** impor mitra/layanan dummy).
- Migrations: `php artisan migrate` setelah backup database dan memastikan struktur Tahap 1/Tahap 2 kompatibel.
- Uji: `php artisan test --filter=DatabaseCatalogStage3Test` dan `node --test tests/js/*.test.mjs` (bergantung instalasi PHP Composer/ext SQLite untuk test).
- Tampilan baru memakai Tailwind dan form server-rendered, plus `backend-catalog.js` untuk konfirmasi arsip/hapus dan bantuan slug.

**Tidak termasuk:** migrasi data `localStorage`, sinkronisasi katalog dummy, booking/jadwal PostgreSQL, pembayaran, atau penggabungan source Tahap 2 yang belum diterima. Katalog demo lama tetap dapat diakses, tidak menjadi katalog produksi.


## Backend Tahap 5 — Ulasan & Pengaduan PostgreSQL (modul terpisah)

Baca `docs/ERD_TAHAP5.md` dan `docs/MERGE_TAHAP4_TAHAP5.md`. Modul ini dipasang lewat
`require __DIR__.'/stage5_feedback.php';` pada akhir `routes/web.php`.

- Publik: `/ulasan-db` (hanya ulasan verified + visible dari Mitra approved).
- Warga login: `/ulasan-db/tulis`, `/ulasan-db/saya`, `/pengaduan-db`.
- Mitra approved: `/mitra/ulasan-db` (balas/edit/hapus balasan milik usahanya).
- Admin: `/admin/ulasan-db` (moderasi & cek bukti), `/admin/pengaduan-db` (status, prioritas, audit).
- Review baru punya `verification_status=pending` dan **tidak tampil publik** hingga
  `ReviewBookingVerifier::isEligible()` dihubungkan ke **pesanan PostgreSQL selesai milik warga dan layanan yang sama**.
- Verifier default SELALU `false`. **Jangan menganggap referensi dari Warga, seeder, atau localStorage sebagai bukti**.
- Moderasi `visible` tidak bisa membuat review pending muncul publik. Log moderasi dan log aduan ada di database.
- Pengaduan dapat dibuat tanpa pesanan dan referensi opsional adalah klaim belum diverifikasi.
- Foto ulasan (opsional) pada `public` disk: `php artisan storage:link`. Beri tahu pengguna agar tak mengunggah dokumen sensitif.
- Tidak ada migrasi maupun model yang bergantung pada tabel pesanan Tahap 4; gabungkan lewat adapter verifier setelah tabel nyata diketahui.
- Review dan pengaduan demo di `localStorage` lama **tidak diimpor**.
- Jalankan `php artisan migrate` setelah gabung, lalu `php artisan test --filter=Stage5FeedbackTest`.

Layanan untuk ditinjau berasal dari `partner_services` Tahap 3, bukan katalog browser. Tanpa integrasi booking Tahap 4, pengajuan review tetap pending, sedangkan alur pengaduan dan moderasi bisa diuji melalui PostgreSQL.

---

## Backend Tahap 7 — Testing, Security Review, dan Persiapan Merge Akhir

Tahap 7 menambahkan **tooling QA** tanpa mengganti controller/migration/route Tahap 2, 4 dan 6 yang sedang dikerjakan rekan. Package lengkap saat ini berisi **Tahap 1 + 3 + 5 + 7**, **bukan** full-integrated release.

- `php scripts/stage7_audit.php --mode=baseline` memeriksa sumber, sintaks PHP, manifest, dan gap integrasi. Mode `--json` tersedia untuk CI. Mode `--mode=release` **sengaja gagal** jika belum ada bukti merge, verifier Tahap 4 belum dihubungkan, atau dependency belum lengkap.
- `node --test tests/js/*.test.mjs` menjalankan regresi JavaScript lama beserta tes audit baru.
- `php artisan test --filter=Stage7` dan `php artisan test` memerlukan `composer install` serta ekstensi database. PHPUnit default SQLite in-memory; uji PostgreSQL gunakan database tes terpisah.
- `python scripts/stage7_http_smoke.py --base-url http://127.0.0.1:8000 --include-db` menguji HTTP guest, redirect dan CSRF 419 **ketika server berjalan**. PHPUnit test mode bukan bukti CSRF aktif.
- `bash scripts/stage7_check.sh baseline` atau `.\scripts\stage7_check.ps1 -Mode baseline` untuk runner lokal.
- `.github/workflows/stage7-ci.yml` menyediakan contoh quality gates SQLite + PostgreSQL di GitHub Actions (baru berjalan setelah commit/push ke repository).
- `docs/STAGE7_TEAM_HANDOFF.json` melacak status Tahap 2/4/6 tanpa menganggap belum diterimanya source sebagai selesai.

Baca `docs/STAGE7_MERGE_CONTRACT.md`, `docs/STAGE7_TEST_PLAN.md`, `docs/STAGE7_SECURITY_CHECKLIST.md`, `docs/STAGE7_WINDOWS_LOCALHOST.md`, dan `docs/STAGE7_RELEASE_HANDOFF.md` sebelum menyatukan branch tim. **Jangan overwrite README atau file Tahap 2/4/6 hasil kerja rekan**; lakukan merge selektif.
