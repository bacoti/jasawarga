# JasaWarga — Checklist Keamanan Tahap 7

Ini checklist pengujian, **bukan sertifikat keamanan**. Source saat ini menggunakan session Laravel untuk role tetapi masih memiliki halaman frontend dummy `localStorage`.

## Yang sudah ada di baseline (wajib diuji dinamis)

- [x] Password `users` menggunakan cast `hashed` Laravel.
- [x] Login meregenerasi session, logout menginvalidasi session + token CSRF.
- [x] Route Admin/Mitra/Warga PostgreSQL menggunakan middleware role.
- [x] Mitra baru tidak memiliki akses ke dashboard Budi Teknik, kecuali seed demo yang eksplisit.
- [x] Ulasan tanpa bukti booking valid hanya pending; moderasi tidak mengesahkan booking.
- [x] Pengelolaan layanan dan balasan membatasi pemilik berbasis ID dari sesi server.
- [ ] Uji request CSRF 419 pada server nyata (bukan PHPUnit mode testing).
- [ ] Audit keseluruhan E2E setelah merge Tahap 2/4/6.

## Pemeriksaan sebelum publikasi

- [ ] `.env` dan kredensial **tidak** masuk Git/ZIP; `APP_DEBUG=false` di staging/production; kunci APP_KEY kuat.
- [ ] `SESSION_SECURE_COOKIE=true`, `same_site=lax/strict` sesuai kebutuhan, domain/path dan HTTPS disetel benar.
- [ ] Admin dan Mitra memakai throttling yang sesuai; pendaftaran/login di-rate-limit per alamat + identitas tanpa lockout berlebihan.
- [ ] Semua POST/PATCH/DELETE pada panel sensitif memakai `@csrf` dan server memverifikasi izin; larang GET yang mengubah state.
- [ ] Policy/owner juga diperiksa pada ID parameter yang ditebak pengguna (IDOR), termasuk file download, portofolio, alamat, pesanan dan pengaduan.
- [ ] Cek SQL injection (query terparameterisasi Eloquent/Query Builder) serta XSS (Blade `{{ }}`, jangan render konten user dengan `{!! !!}`).
- [ ] File upload disimpan dengan nama acak, validasi server tipe/ukuran, sanitasi metadata jika perlu; hindari dokumen identitas/foto sensitif pada disk publik.
- [ ] Setelah Tahap 4, booking menggunakan transaksi + constraint/locking PostgreSQL untuk bentrok, idempotency token, serta snapshot tarif.
- [ ] Setelah Tahap 4, `ReviewBookingVerifier` memeriksa kepemilikan+layanan+selesai dari database. Review pending tidak tampil di hasil pencarian publik atau statistik rating.
- [ ] Setelah Tahap 6, ekspor CSV tidak menyertakan PII yang tidak perlu dan mencegah formula injection.
- [ ] Audit notifikasi tidak memasukkan email/nomor penerima lain, deduplikasi event dan pengiriman setelah commit.
- [ ] Atur security headers CSP, HSTS (HTTPS only), X-Content-Type-Options, Referrer-Policy sesuai asset dan risiko aplikasi; uji kompatibilitas Vite/Tailwind.
- [ ] Backup + restore PostgreSQL benar-benar diuji di staging; jangan jalankan `migrate:fresh` pada data nyata.
- [ ] Jalankan dependency audit (`composer audit`, `npm audit`) dan review kerentanan, dengan lockfile versi pasti.
- [ ] Tetapkan retensi data, kebutuhan privacy policy, hak akses Admin, audit trail tidak dapat diubah oleh role biasa.

## Risiko terbuka yang diketahui

1. **Tahap 2, 4, 6 masih belum tersedia** pada paket baseline, sehingga belum ada audit terpadu terhadap profil, pemesanan, analitik DB, notifikasi, concurrency dan hak aksesnya.
2. Prototype `localStorage` masih dapat diubah pengguna. Jangan gunakan metrik/keputusan dummy untuk transaksi/klaim penanganan resmi.
3. `ReviewBookingVerifier::isEligible()` masih `false`: ulasan baru tidak dapat lolos verifikasi sebelum adapter Tahap 4.
4. Dependensi belum dikunci dengan `composer.lock` di ZIP baseline; perlu lockfile sebelum build reproducible.
5. Belum ada bukti pengujian browser dan PostgreSQL sesungguhnya di lingkungan ini.

**Gate rilis: BLOCKED** sampai risiko teratas ditutup dan bukti uji ditandatangani tim.
