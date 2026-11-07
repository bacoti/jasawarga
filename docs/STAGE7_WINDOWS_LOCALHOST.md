# Pengujian Tahap 7 — Windows / Localhost

Buka **PowerShell** dari root project yang sudah digabung dengan pekerjaan tim. Pastikan PHP 8.2+, Composer 2, Node.js 20+, PostgreSQL dan ekstensi `pdo_pgsql` serta `pdo_sqlite` tersedia.

```powershell
php -v
php -m | Select-String 'pdo_pgsql|pdo_sqlite|pgsql'
composer --version
node --version
```

Jangan menggunakan `.env` milik rekan atau akun database sungguhan. Buat `.env` lokal sendiri, arahkan ke database pengembangan, lalu jalankan:

```powershell
composer install
npm install
php artisan key:generate
php artisan migrate
```

Untuk tes otomatis menggunakan SQLite memory sesuai `phpunit.xml`:

```powershell
php scripts/stage7_audit.php --mode=baseline
node --test tests/js/*.test.mjs
php artisan test
# atau jalankan bersama:
.\scripts\stage7_check.ps1 -Mode baseline
```

Untuk pengujian PostgreSQL, gunakan **database testing khusus** yang boleh dihapus, jangan database development/production. Sesuaikan environment proses yang menjalankan PHPUnit (override konfigurasi PHPUnit bila diperlukan) dan pastikan `DB_CONNECTION=pgsql` benar-benar terbaca. Contoh pada PowerShell:

```powershell
$env:DB_CONNECTION='pgsql'
$env:DB_HOST='127.0.0.1'
$env:DB_PORT='5432'
$env:DB_DATABASE='jasawarga_test'
$env:DB_USERNAME='jasawarga_test'
$env:DB_PASSWORD='ISI_PASSWORD_TEST_SENDIRI'
php artisan test
Remove-Item Env:DB_PASSWORD
```

`RefreshDatabase` dapat membuat dan menjatuhkan tabel pada database tersebut; **jangan gunakan database yang memiliki data penting**.

Jalankan aplikasi:

```powershell
# Terminal 1
npm run dev
# Terminal 2
php artisan serve
# Terminal 3, setelah halaman dapat diakses
py scripts\stage7_http_smoke.py --base-url http://127.0.0.1:8000 --include-db
```

Skrip HTTP tidak memakai akun/password dan akan memeriksa CSRF 419 tanpa token. Jika menggunakan reverse proxy, redirect HTTPS, atau path lain, atur base URL dengan benar. Untuk menjalankan hanya route yang tidak mengakses DB, hilangkan `--include-db`.

Sebelum rilis:

```powershell
php scripts/stage7_audit.php --mode=release
```

Mode release **sengaja gagal** selama status Tahap 2/4/6 belum dibuktikan, verifier booking masih dummy, dependensi belum terpasang, atau ekstensi DB tidak tersedia. Jangan mengubah status menjadi `integrated` sekadar agar pemeriksa menjadi hijau; isi commit dan bukti hasil uji.
