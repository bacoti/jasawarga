# PATCH JasaWarga v2 — Bandung, Navigasi, Peta, Seeder

Paket ini merupakan **patch relatif direktori root Laravel**, bukan salinan project lengkap. Berasal dari arsip pengguna `jasawargav2.rar`. Jangan menghapus folder `vendor`, `node_modules`, `.env`, `storage`, atau database lama.

## 1. Pemasangan aman

1. **Backup** code dan database PostgreSQL. Untuk PostgreSQL, contoh `pg_dump -Fc -f backup_jasawarga.dump jasawarga_dev` (sesuaikan kredensial).
2. Ekstrak ZIP patch di folder root Laravel (tempat file `artisan` berada). Pertahankan struktur folder dan timpa hanya file yang ada di ZIP. Jangan gunakan ekstraksi ke folder terpisah tanpa menggabungkan foldernya.
3. **Jangan timpa `.env` milikmu.** Pastikan `APP_ENV=local`, konfigurasi `DB_*` masih sesuai database yang aktif, `APP_TIMEZONE=Asia/Jakarta`.
4. Jalankan:
   ```powershell
   composer install
   npm install
   php artisan optimize:clear
   php artisan migrate
   php artisan storage:link
   npm run build
   php artisan serve
   ```
   Untuk pengembangan UI gunakan `npm run dev` di terminal terpisah, bukan `npm run build` berulang kali.
5. Refresh browser (Ctrl+F5). Favicon lama mungkin tersimpan di cache browser.

## 2. Apa yang diperbaiki

- Logo identitas dari gambar unggahan: `public/assets/brand/jasawarga-app-icon.png` + versi navbar dan favicon.
- Tombol **Lihat Profil** pada Home membuka `/mitra/{id}` milik Mitra yang sesuai, tanpa popup placeholder.
- Header/navigasi publik yang sama digunakan di Home, Kategori, Detail Mitra, Pemesanan, dan Akun Warga; tab submenu di dashboard tetap khusus role.
- Route `/pemesanan/{id}`, `/pesanan-saya*`, `/ulasan-saya`, dan `/ulasan/tulis/*` memakai middleware `auth` + `role:citizen`, sehingga guest diarahkan ke `/masuk` dan Mitra/Admin tidak dapat mengaksesnya sebagai Warga.
- Peta ilustrasi Home diganti dengan Leaflet + OpenStreetMap pada desktop dan mobile. Tile OSM dipakai dengan atribusi wajib. **Perlu koneksi internet** untuk mengunduh library Leaflet dan ubin peta. Titik Mitra sengaja **ilustratif, bukan alamat atau koordinat sungguhan**. Jangan gunakan tile server gratis untuk lalu lintas besar tanpa mematuhi kebijakan OSMF.
- Dataset demo katalog, pengaturan profil, pilihan kecamatan, dan default zona waktu pengguna baru menggunakan Kota Bandung serta `Asia/Jakarta` (WIB). Data lama tidak diubah secara paksa.
- Menu guest, citizen, partner, dan admin diperlihatkan sesuai role di navbar publik. Halaman dashboard tetap dilindungi middleware di sisi server.
- Seeder opsional PostgreSQL Bandung mencakup status Warga, Mitra, verifikasi, kategori, layanan, jam kerja/cuti, booking, penawaran, ulasan, balasan, moderasi, pengaduan, portofolio, serta notifikasi.

## 3. Seeder demo Bandung (hanya local/testing)

**Tidak otomatis dijalankan.** Isi `.env` lokal secara manual:

```dotenv
APP_ENV=local
APP_TIMEZONE=Asia/Jakarta
JASAWARGA_PRESENTATION_SEED=true
JASAWARGA_PRESENTATION_PASSWORD=ISI_PASSWORD_KUAT_MILIKMU123
```

Password di atas **contoh placeholder**, pilih password unik yang **tidak digunakan di layanan lain**. Minimal 12 karakter, huruf besar, huruf kecil, angka. Jangan unggah `.env` ke Git, jangan pakai data demo di produksi.

Jalankan:

```bash
php artisan config:clear
php artisan migrate
php artisan db:seed --class=BandungPresentationSeeder
php artisan storage:link
```

Akun demo sama-sama memakai password dari `JASAWARGA_PRESENTATION_PASSWORD`:

| Contoh | Email | Kondisi |
|---|---|---|
| Admin | `admin.bandung@demo.jasawarga.test` | Admin aktif |
| Warga | `warga.1@demo.jasawarga.test` | Warga aktif; 8 akun `warga.1` s.d. `warga.8` |
| Warga diblokir | `warga.nonaktif@demo.jasawarga.test` | Nonaktif |
| Mitra disetujui | `mitra.1@demo.jasawarga.test` | Terverifikasi dan layanan aktif |
| Mitra menunggu | `mitra.8@demo.jasawarga.test` | Verifikasi tertunda |
| Mitra ditolak | `mitra.9@demo.jasawarga.test` | Verifikasi ditolak |
| Mitra periksa ulang | `mitra.10@demo.jasawarga.test` | Perlu verifikasi ulang |
| Mitra nonaktif | `mitra.11@demo.jasawarga.test` | Akun dinonaktifkan |

Akun pada domain `.test` dan alamat/nomor telepon diisi **fiktif**. Contoh kode booking: `JWB-DEMO-0001` dan seterusnya. Seeder dirancang dapat dijalankan ulang tanpa melipatgandakan record dengan kunci demo yang sama, tetapi **tidak seharusnya dijalankan di database yang berisi data produksi**.

**Jangan gunakan `php artisan migrate:fresh` pada database berisi data yang ingin disimpan**. Migration baru hanya mengubah default kota pada kolom `partner_profiles.city`; record lama Makassar yang sudah tersimpan tidak diubah diam-diam. Bila ingin membersihkan data demo sebelumnya, lakukan pada database demo terpisah setelah backup.

## 4. Pengujian alur presentasi

| Skenario | Hasil yang diharapkan |
|---|---|
| Guest klik profil Mitra dari Home | Detail `/mitra/{id}` terbuka |
| Guest klik Pesan di Home | Dialihkan ke login Laravel (`/masuk`) |
| Guest buka `/pesanan-saya` langsung | Dialihkan ke login |
| Login sebagai Mitra/Admin, akses `/pemesanan/1` | Ditolak (403), bukan form booking Warga |
| Login sebagai Warga, akses `/pemesanan/1` | Form pemesanan demo dapat dibuka |
| Klik Pesanan Saya role Warga | Dibawa ke `/akun/pesanan` (data booking PostgreSQL) |
| Navigasi saat berpindah Home/Kategori/Profil | Header utama seragam, menu sesuai role |
| Peta Home | Tile OSM nyata, marker Bandung, link Mitra mengikuti marker dipilih |
| Admin melihat laporan | Status yang beragam dan data demonstrasi tersedia setelah seeding |

Data `localStorage` dari website yang lebih lama **tidak dihapus**, agar data asli demo lokal yang masih diperlukan tidak hilang. Karena itu, untuk melihat demo **Bandung murni**, gunakan profil browser baru / jendela privat pada localhost; tindakan ini tidak mengubah database.

## 5. Batas pengujian paket ini

- Pemeriksaan regresi JavaScript bisa dijalankan `node --test tests/js/*.test.mjs`.
- Pengujian Laravel di komputer sendiri: `php artisan test --filter=BandungNavigationAccessTest`.
- Pengujian route dan migration penuh **harus** dilakukan pada localhost dengan ekstensi PHP `mbstring`, `pdo_pgsql`, database PostgreSQL, serta dependensi Composer yang lengkap.
- Pengujian build Vite pada sistem pembuatan patch ini terbatas oleh paket binary Rollup native yang tidak tersedia di arsip untuk lingkungan Linux. Gunakan `npm install` di sistem operasi Anda sebelum menjalankan `npm run build`.
