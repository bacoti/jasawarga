# Merge aman Backend Tahap 4 + Tahap 5

## Prinsip

Paket **project lengkap** Tahap 5 berasal dari **Tahap 1 + Tahap 3 + Tahap 5**. Paket ini **TIDAK MEMUAT** kode Tahap 2 atau Tahap 4 milik temanmu. **Jangan menimpa project tim dengan ZIP project lengkap.**

### 1. Lakukan merge di branch

```bash
git switch -c feature/backend-tahap5-feedback
# salin file BARU dari ZIP perubahan sesuai direktori
```

File baru Tahap 5 memakai namespace tersendiri:

- `routes/stage5_feedback.php`
- `app/Http/Controllers/Database*ReviewController.php`, `Database*ComplaintController.php`
- `app/Http/Requests/SubmitServiceReviewRequest.php`, `ReplyServiceReviewRequest.php`, `SubmitServiceComplaintRequest.php`
- `app/Services/Feedback/ReviewBookingVerifier.php`
- `app/Models/ServiceReview*.php`, `ServiceComplaint*.php`
- `database/migrations/2026_11_07_05000*.php`
- `resources/views/backend-feedback/**`
- `tests/Feature/Stage5FeedbackTest.php`
- `docs/ERD_TAHAP5.md`

**File bersama** hanya `routes/web.php` dan `README.md`. Jangan overwrite versi temanmu. Tambahkan tepat satu baris ini di akhir `routes/web.php`:

```php
require __DIR__.'/stage5_feedback.php';
```

Lalu gabungkan bagian README Tahap 5 secara manual. Frontend lama dan `app.js` **tidak perlu diubah**.

### 2. Kontrak penghubung ke Tahap 4

File `app/Services/Feedback/ReviewBookingVerifier.php` berisi:

```php
public function isEligible(int $citizenId, int $partnerServiceId, string $bookingReference): bool
```

Default `return false` adalah keputusan keamanan disengaja. Tim Tahap 4 harus menulis query **menggunakan nama model/tabel yang benar-benar mereka buat** dan memastikan semuanya sekaligus:

1. Pesanan PostgreSQL ada untuk `bookingReference` (bukan dummy `localStorage`).
2. Pemilik pesanan = `citizenId` dari Laravel session.
3. Layanan pada pesanan = `partnerServiceId` dari Tahap 3.
4. Status selesai (`completed`/padanannya), bukan pending, rejected atau cancelled.
5. Jika pembatalan/retur/refund membatalkan kelayakan, tambahkan ke pemeriksaan.

**Jangan** menganggap keberadaan kode referensi sebagai verifikasi. Jangan langsung menyetel `verified_at` melalui UI Admin tanpa query tersebut. Adapter bisa diubah di kelas verifier di atas tanpa mengubah controller Tahap 5.

### 3. Pasang dan validasi

```bash
composer install
php artisan optimize:clear
php artisan migrate
php artisan storage:link
php artisan route:list --path=ulasan-db
php artisan route:list --path=pengaduan-db
php artisan test --filter=Stage5FeedbackTest
npm run dev
php artisan serve
```

Periksa konflik nama migrasi 050001–050005 dengan branch lain; jika ada, atur timestamp setelah mengonfirmasi urutan FK (users, partner_profiles, partner_services harus ada dahulu). Jangan gunakan `migrate:fresh` pada database berisi data tim.

### 4. Skenario QA integrasi

- Warga dapat mengajukan ulasan dari layanan DB; sebelum adapter, status **pending** dan tidak publik.
- Warga B tidak dapat menghapus pengajuan Warga A atau menautkan ulasan A ke pengaduannya.
- Admin tidak bisa mengubah pending jadi verified tanpa bukti pesanan selesai.
- Setelah adapter, Warga yang memiliki **completed booking** bisa menghasilkan ulasan verified; warga dengan pesanan batal/akun lain tidak bisa.
- Mitra A tidak bisa membalas ulasan Mitra B; balasan muncul publik jika ulasannya dipublikasikan.
- Admin dapat hide/show dengan alasan dan audit tanpa mengubah rating Warga.
- Pengaduan tidak dapat diakses Warga lain; Admin wajib menambahkan catatan untuk perubahan status dan prioritas.
- Coba unggah foto valid <=2 MB dan foto >2 MB; foto valid disimpan pada disk publik.

### 5. Fitur yang sengaja belum dijanjikan

- Belum ada notifikasi nyata, laporan agregat Tahap 6, payment/refund verification, dan portal pengaduan Mitra.
- Belum ada endpoint publik JSON untuk aplikasi mobile. Modul ini menggunakan Laravel Session + Blade + CSRF.
- File review lama di browser tidak dianggap transaksi nyata.
