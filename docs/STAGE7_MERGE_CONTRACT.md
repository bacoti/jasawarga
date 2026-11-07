# JasaWarga — Tahap 7: Kontrak Integrasi Tim

**Baseline aktual:** Tahap 1 + 3 + 5. Tahap 2, 4, dan 6 **belum disertakan** dalam source ini. Dokumen ini adalah kontrak serah-terima, bukan klaim seluruh modul sudah digabung.

## Prinsip merge yang harus dipatuhi

1. Buat branch integrasi dari source terbaru tim, jangan mengekstrak ZIP **project lengkap Tahap 1+3+5+7** di atas pekerjaan Tahap 2/4/6.
2. Gunakan **ZIP Files Only Tahap 7**; seluruh file baru dimasukkan satu-per-satu. `README.md` digabung manual, **jangan overwrite**.
3. Pertahankan file `routes/stage3_catalog.php` dan `routes/stage5_feedback.php`; pertahankan `require` yang memuat keduanya pada `routes/web.php`.
4. Tidak mengubah ID tabel, kolom, atau kontrak controller teman tanpa membaca migration sebenarnya. Semua dokumen Tahap 7 memakai nama **konseptual** untuk Tahap 2/4/6, bukan nama tabel yang sudah dipastikan.
5. Jalankan migration pada **database staging hasil backup**, lalu uji ulang role, kepemilikan, dan kontrak akses.
6. Tidak mengimpor `jasawarga:prototype-*` dari localStorage secara otomatis, bahkan jika pengguna memakai email/nomor yang sama.
7. Periksa konflik route path dan nama route memakai `php artisan route:list`; jangan menghapus route tanpa memperbarui Blade link dan tes.

## Serah-terima minimal tiap modul

| Modul | Keluaran yang perlu diserahkan | Kontrak terhadap Tahap 7 |
|---|---|---|
| Tahap 1 | `users`, autentikasi session, role, profil Mitra awal | Tidak memperbolehkan peran dari `localStorage`; semua mutasi memerlukan sesi + role. |
| Tahap 2 | Migration profil Warga, alamat dan portofolio Mitra + policy/owner | Form profil hanya mengubah data pemilik, Admin boleh baca sesuai kebijakan; file upload dibatasi dan dibersihkan saat dihapus. |
| Tahap 3 | `service_categories`, `partner_services`, kategori publik dan CRUD pemilik | Layanan milik Mitra terverifikasi; kategori nonaktif dan layanan arsip tidak boleh dipesan. |
| Tahap 4 | Model booking, foreign key Warga/Mitra/Layanan, slot, status dan idempotensi | Bukti pesanan **harus** membuktikan pemilik Warga, ID layanan, status selesai; hindari dua booking untuk slot yang sama memakai transaksi/lock/constraint server. |
| Tahap 5 | `service_reviews`, balasan, moderasi, pengaduan, log | Review `pending` tidak publik; balasan hanya Mitra pemilik; pengaduan hanya reporter/Admin; moderasi bukan bukti booking. |
| Tahap 6 | Agregator dashboard, laporan, notifikasi, job/queue jika ada | Statistik dari PostgreSQL, bukan katalog dummy; ekspor cegah formula injection, otorisasi role; notifikasi dijalankan setelah commit/transaksi yang relevan. |

## Titik integrasi wajib Tahap 4 → Tahap 5

File yang perlu **diimplementasikan setelah schema Tahap 4 diterima**: `app/Services/Feedback/ReviewBookingVerifier.php`.

Kontrak fungsi yang saat ini tersedia:

```php
public function isEligible(int $citizenId, int $partnerServiceId, string $bookingReference): bool
```

Hanya boleh `true` bila sebuah record **PostgreSQL** memenuhi seluruh ini secara bersamaan:
- referensi booking unik ditemukan, bukan ID localStorage/demo;
- `citizen_id` sama dengan pengguna yang mengajukan review;
- `partner_service_id` sama dengan layanan pada review;
- booking memiliki status selesai yang valid sesuai state machine Tahap 4, bukan `cancelled`/`rejected`/`pending`;
- perubahan/rollback booking yang berpengaruh pada validitas ditangani sesuai kebijakan tim.

Contoh **pseudocode** (bukan kode final, karena tabel/kolom Tahap 4 belum diterima):

```text
booking = query_booking_by_reference(bookingReference)
return booking exists
    AND booking.citizen_id === citizenId
    AND booking.partner_service_id === partnerServiceId
    AND booking.status === official_completed_status
```

Default `false` saat ini **sengaja**: tidak bisa menganggap bukti dari browser/Warga sebagai valid. Jangan menggantinya dengan `return true` atau pemeriksaan format kode saja.

### Adapter wajib memiliki tes PostgreSQL

- Warga A + booking selesai milik A + layanan X → eligible.
- Warga B + nomor booking A → ineligible.
- Booking A layanan Y, review untuk X → ineligible.
- Booking A pending/working/rejected/cancelled → ineligible.
- Booking tidak ada / kode demo lokal → ineligible.
- Dua permintaan review yang bersamaan untuk pesanan yang sama → satu review maksimum (unique DB).
- Klik moderasi Admin tidak mengubah status verifikasi.

## Setelah merge

1. Perbarui `docs/STAGE7_TEAM_HANDOFF.json`: ubah `status` menjadi `integrated` **hanya setelah merge commit**, isikan `merge_commit` dan `test_evidence` (referensi workflow atau laporan), bukan teks placeholder.
2. Jalankan `php scripts/stage7_audit.php --mode=baseline` untuk pembangunan harian.
3. Jalankan suite Laravel ke SQLite dan PostgreSQL; jalankan smoke HTTP setelah `php artisan serve`.
4. Jalankan skenario E2E dari `docs/STAGE7_TEST_PLAN.md` dan simpan bukti hasilnya.
5. Gunakan `--mode=release` hanya menjelang deployment; hasil PASS dari skrip bukan pengganti review kode, pengujian browser, backup database, dan pemeriksaan produksi.
