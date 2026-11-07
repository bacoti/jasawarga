# Patch sinkronisasi verifikasi Mitra dan penemuan layanan

- `admin/mitra` sekarang membaca `partner_profiles` PostgreSQL dan menampilkan seluruh status, termasuk approved. Pencarian mencari nama usaha, pemilik, email, atau kecamatan. Katalog dummy lama dipindah ke `/admin/mitra-demo`.
- `kategori-jasa` menampilkan Mitra PostgreSQL yang disetujui dan layanan aktif langsung di bagian teratas. Pencarian PostgreSQL terpisah dari demo, dengan filter kategori aktif.
- `home` menampilkan preview layanan PostgreSQL terbaru (hingga 4 Mitra); `profil-mitra/{slug}` menampilkan tarif, layanan, dan pemesanan aktif.
- Saat pendaftaran Mitra baru, slug publik disimpan. Migration `080001_backfill_missing_partner_slugs` melengkapi profil Mitra yang sudah ada tanpa mengubah status verifikasi.
- Syarat tampil: `verification_status=approved`, `reverification_required=false`, akun partner aktif, preferensi profil tidak disembunyikan. Untuk layanan juga perlu `is_active=true`, kategori aktif, belum diarsipkan.
- **Jalankan** `composer dump-autoload`, `php artisan optimize:clear`, `php artisan migrate`, `npm run build`, lalu `php artisan serve`. Tidak perlu mengulang seeder.
- Jika setelah disetujui statusnya berubah menjadi perlu verifikasi ulang karena mengubah area layanan, Admin harus menyetujui lagi melalui `/admin/mitra`.
- Database & browser integration harus diuji di localhost, source syntax tidak menggantikan test PostgreSQL.
