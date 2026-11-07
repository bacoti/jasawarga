# JasaWarga — Tahap 7: Test Plan & Evidence

**Batas saat ini:** suite otomatis pada source **Tahap 1+3+5**; Tahap 2/4/6 belum disertakan. Tidak boleh mengklaim seluruh alur E2E selesai sampai integrasi dilakukan.

## Lapisan pengujian

| Lapisan | Perintah | Apa yang dibuktikan | Bukti yang perlu disimpan |
|---|---|---|---|
| Static & readiness | `php scripts/stage7_audit.php --mode=baseline` | File utama, route entrypoint, PHP syntax, status integrasi, dependency | Output/JSON audit |
| Regresi JS prototipe | `node --test tests/js/*.test.mjs` | Operasi localStorage lama tidak rusak; **tidak** membuktikan sinkronisasi database | Output TAP |
| Laravel SQLite | `php artisan test` | Autentikasi, role, CRUD, validasi dan invariants pada SQLite testing | Output test suite |
| Laravel PostgreSQL | Jalankan test dengan env PG pada DB testing terpisah | Integritas FK, transaksi, type/constraint spesifik PostgreSQL | Output CI PostgreSQL |
| Smoke HTTP (app berjalan) | `python scripts/stage7_http_smoke.py` | HTTP guest redirect, CSRF 419, halaman publik | Output smoke |
| Browser E2E | Manual/Playwright setelah semua modul digabung | Form, responsif, aksesibilitas, state, navigasi, upload dan kegagalan jaringan | Screenshot/video + checklist |
| Security review | Checklist keamanan | Session/cookie, otorisasi object-level, upload, log, formula injection | Laporan tindak lanjut |

> PHPunit biasanya menonaktifkan validasi CSRF ketika aplikasi berada di mode testing; sebab itu CSRF HTTP **419** harus diuji terhadap server development/staging **non-testing** dengan skrip smoke, bukan sekadar mengandalkan `post()` PHPUnit.

## Penerimaan fungsional per role

| ID | Skenario | Ekspektasi |
|---|---|---|
| AUTH-01 | Registrasi Warga | Password hash; role `citizen`, tidak pernah menulis `admin` dari request. |
| AUTH-02 | Registrasi Mitra | Profil `pending`, tidak otomatis menerima `demo_provider_id=1`. |
| AUTH-03 | Warga mencoba route Admin | 403; data pribadi Admin tidak tampil. |
| AUTH-04 | Mitra baru mencoba dashboard Budi Teknik | Diredirect ke portal sendiri, bukan melihat data demo. |
| AUTH-05 | Logout + akses kembali | Session invalid; route terlindungi redirect ke login. |
| PROF-01 | Warga edit alamat milik Warga lain (Tahap 2) | 403/404 dan DB tidak berubah. |
| PROF-02 | Mitra unggah portofolio (Tahap 2) | MIME/ukuran valid, file aman, owner check; file lama tak jadi orphan. |
| CAT-01 | Kategori nonaktif | Tidak muncul di katalog layanan aktif; mutasi kategori hanya Admin. |
| CAT-02 | Mitra A edit layanan Mitra B | 404/403, data DB tetap sama. |
| CAT-03 | Layanan arsip | Tidak tampil publik; ulasan/pesanan historis tetap merujuk snapshot yang benar. |
| BOOK-01 | Dua booking bersamaan pada slot sama (Tahap 4) | Satu sukses, satu ditolak secara atomik; uji concurrency PostgreSQL, bukan hanya JavaScript. |
| BOOK-02 | Harga kategori atau tarif berubah setelah booking (Tahap 4) | Pesanan lama menyimpan harga snapshot, tidak diubah retroaktif. |
| BOOK-03 | Buka tanggal cuti / kategori nonaktif (Tahap 4) | Backend menolak booking, walaupun frontend diubah paksa. |
| REVIEW-01 | Ulasan dibuat dengan nomor booking palsu | Tetap `pending`, tidak tampil publik. |
| REVIEW-02 | Review untuk pesanan selesai milik Warga yang sama (setelah merge Tahap 4) | Terverifikasi dengan bukti DB; tidak terbit karena format kode saja. |
| REVIEW-03 | Admin menyembunyikan lalu memunculkan ulasan pending | Tetap pending/tidak publik; audit moderasi tersimpan. |
| REVIEW-04 | Mitra B balas ulasan layanan Mitra A | Ditolak, konten rating/komentar asli tak berubah. |
| COM-01 | Warga B tautkan review Warga A ke pengaduannya | Ditolak, tidak mengubah DB. |
| COM-02 | Admin tangani pengaduan | Alur status valid + log Admin dan catatan alasan. |
| ANALYTICS-01 | KPI Tahap 6 | Agregasi hanya tabel PostgreSQL, jangan menjumlah dengan localStorage dummy. |
| ANALYTICS-02 | CSV laporan Tahap 6 | Tidak bocorkan PII; formula `=`, `+`, `-`, `@` pada sel die-escape. |
| NOTIF-01 | Pengiriman notifikasi Tahap 6 | Idempotent; jangan mengirim dua kali saat request retry; tidak bocorkan konten akun lain. |

## Pengujian manual UI

- Desktop 1440px, tablet 768px, mobile 390px, landscape pendek: sidebar, tabel, filter, modal, fokus keyboard, label, pesan galat, kontras.
- Submit form sekali lalu cepat submit ulang; validasi server harus mencegah duplikasi.
- Simulasi gagal koneksi, sesi berakhir, token CSRF kedaluwarsa, file lebih besar dari 2MB, kategori dinonaktifkan di tab lain.
- Tinjau semua URL `/admin/*` dan `/mitra/*` dengan tiga role dan tamu; pastikan tidak ada data operasional nyata di halaman prototype localStorage.
- Uji halaman login/daftar/logout dalam dua tab; sesi dan regenerasi token bekerja.

## Catatan uji yang harus ditulis setiap penggabungan

```text
Tanggal/jam:
Commit SHA integrasi:
Module Tahap 2/4/6 yang benar-benar di-merge:
Versi PHP/Node/PostgreSQL:
Hasil audit baseline/release:
Hasil JS (passed / skipped / failed):
Hasil Laravel SQLite:
Hasil Laravel PostgreSQL:
Hasil HTTP smoke:
Hasil UI desktop/mobile:
Temuan dan risiko residual:
Reviewer / persetujuan:
```

**Aturan:** SKIPPED/BLOCKED bukan PASSED. Skenario BOOK/ANALYTICS/NOTIF sengaja **BLOCKED** hingga Tahap 4/6 diserahkan.
