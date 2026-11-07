<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingPrototypeController;
use App\Http\Controllers\CitizenAccountController;
use App\Http\Controllers\PartnerPortalController;
use App\Http\Controllers\PartnerVerificationController;
use App\Http\Controllers\RegisteredCitizenController;
use App\Http\Controllers\Public\DiscoveryController;
use Illuminate\Support\Facades\Route;

// Publik: kategori dan profil jasa dummy tetap dapat diakses oleh siapa pun.
Route::get('/', [DiscoveryController::class, 'home'])->name('home');
Route::get('/kategori-jasa', [DiscoveryController::class, 'index'])->name('categories.index');

Route::get('/masuk', [AuthController::class, 'showLogin'])->name('account.login');
Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('auth.login');
Route::get('/daftar', [AuthController::class, 'showCitizenRegister'])->name('account.register');
Route::post('/daftar', [AuthController::class, 'registerCitizen'])->middleware('throttle:6,1')->name('auth.register.citizen');
Route::get('/daftar/mitra', [AuthController::class, 'showPartnerRegister'])->name('partner.register');
Route::post('/daftar/mitra', [AuthController::class, 'registerPartner'])->middleware('throttle:6,1')->name('auth.register.partner');
Route::view('/lupa-password', 'auth.password-help')->name('account.forgot');
Route::get('/admin/masuk', fn () => redirect()->route('account.login', ['role' => 'admin']))->name('admin.demo.entry');
Route::get('/mitra/masuk', fn () => redirect()->route('account.login', ['role' => 'partner']))->name('partner.demo.entry');

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('auth.logout');
});

Route::middleware(['auth', 'role:citizen'])->group(function () {
    Route::get('/akun', [CitizenAccountController::class, 'profile'])->name('account.profile');
    Route::get('/akun/edit', [CitizenAccountController::class, 'edit'])->name('account.edit');
    Route::patch('/akun/edit', [CitizenAccountController::class, 'update'])->name('account.update');
    Route::redirect('/akun/alamat-legacy', '/akun/alamat')->name('account.addresses');
    Route::redirect('/akun/pengaturan-legacy', '/akun/pengaturan')->name('account.settings');
    Route::view('/keluar', 'auth.logout-confirm')->name('account.logout');
});

// Aplikasi baru belum memakai transaksi PostgreSQL. Pesanan/ulasan berikut tetap demo lokal.
Route::get('/mitra/{id}', function (string $id) {
    $provider = collect(config('jasawarga.providers', []))->firstWhere('id', (int) $id);
    abort_if($provider === null, 404);

    $profile = config("jasawarga_profiles.{$id}");
    abort_if($profile === null, 404);

    $category = collect(config('jasawarga.categories', []))
        ->firstWhere('slug', $provider['category']);

    return view('providers.show', compact('provider', 'profile', 'category'));
})->whereNumber('id')->name('providers.show');

// Semua rute pembelian dan riwayat pesanan lokal hanya untuk Warga login.
Route::middleware(['auth', 'role:citizen'])->group(function () {
Route::get('/pemesanan/berhasil', [BookingPrototypeController::class, 'success'])
    ->name('bookings.success');
Route::get('/pemesanan/{id}', [BookingPrototypeController::class, 'create'])
    ->whereNumber('id')->name('bookings.create');


// Ulasan demo disimpan di localStorage; tidak ada POST/API server.
Route::view('/ulasan-saya', 'reviews.index')->name('reviews.index');
Route::get('/ulasan/tulis/{reference}', function (string $reference) {
    return view('reviews.create', compact('reference'));
})->where('reference', 'JW-DEMO-[A-Z0-9-]+')->name('reviews.create');

// Halaman Pesanan Saya: data hanya berasal dari localStorage browser.
Route::view('/pesanan-saya', 'orders.index')->name('orders.index');
Route::get('/pesanan-saya/{reference}', function (string $reference) {
    return view('orders.show', compact('reference'));
})->where('reference', 'JW-DEMO-[A-Z0-9-]+')->name('orders.show');


});

// Panel Admin hanya dapat diakses role admin dari sesi Laravel.
Route::middleware(['auth', 'role:admin'])->group(function () {

// Dashboard Admin lama: sumber data simulasi browser, tetapi route terlindungi.
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard', [
        'providers' => config('jasawarga.providers', []),
        'profiles' => config('jasawarga_profiles', []),
        'categories' => config('jasawarga.categories', []),
    ]);
})->name('admin.dashboard');

// Admin Manajemen Warga dan Mitra: UI legacy localStorage, akses role Admin.
Route::view('/admin/warga', 'admin.citizens.index')->name('admin.citizens');
// Ini daftar Mitra PostgreSQL: termasuk yang sudah disetujui.
Route::get('/admin/mitra', [PartnerVerificationController::class, 'index'])->name('admin.partners');
// Katalog demo lama tetap dapat diakses terpisah bila dibutuhkan saat presentasi.
Route::get('/admin/mitra-demo', function () {
    return view('admin.partners.demo', [
        'providers' => config('jasawarga.providers', []),
        'profiles' => config('jasawarga_profiles', []),
        'categories' => config('jasawarga.categories', []),
    ]);
})->name('admin.partners.demo');

// Modul operasional Admin: CRUD simulasi localStorage, route butuh login Admin.
Route::get('/admin/kategori', function () {
    return view('admin.categories.index', [
        'categories' => config('jasawarga.categories', []),
        'providers' => config('jasawarga.providers', []),
        'profiles' => config('jasawarga_profiles', []),
    ]);
})->name('admin.categories');
Route::view('/admin/pesanan', 'admin.orders.index')->name('admin.orders');
Route::view('/admin/ulasan-pengaduan', 'admin.reviews.index')->name('admin.reviews');

// Analitik & preferensi Admin: data lokal, akses role Admin.
Route::get('/admin/laporan', function () {
    return view('admin.reports.index', [
        'providers' => config('jasawarga.providers', []),
        'profiles' => config('jasawarga_profiles', []),
        'categories' => config('jasawarga.categories', []),
    ]);
})->name('admin.reports');
Route::view('/admin/pengaturan', 'admin.settings.index')->name('admin.settings');
    Route::get('/admin/warga/akun', [RegisteredCitizenController::class, 'index'])->name('admin.citizen-accounts');
    Route::get('/admin/mitra/pendaftaran', [PartnerVerificationController::class, 'index'])
        ->name('admin.partner-applications');
    Route::post('/admin/mitra/pendaftaran/{partnerProfile}/keputusan', [PartnerVerificationController::class, 'decide'])
        ->middleware('throttle:20,1')->name('admin.partner-applications.decide');
});

// Mitra yang mendaftar mempunyai portal sendiri, belum memakai profil katalog Budi Teknik.
Route::middleware(['auth', 'role:partner'])->group(function () {
    Route::get('/mitra/portal', [PartnerPortalController::class, 'index'])->name('partner.portal');
});

// Panel operasional Mitra legacy khusus akun seed demo yang terhubung ke katalog ID=1.
Route::middleware(['auth', 'role:partner', 'partner.approved', 'partner.demo'])->group(function () {
// Dashboard Overview Mitra: hanya akun seed demo yang punya ID katalog 1.
Route::get('/mitra/dashboard', function () {
    $provider = collect(config('jasawarga.providers', []))->firstWhere('id', 1);
    abort_if($provider === null, 404);
    $profile = config('jasawarga_profiles.1');
    abort_if($profile === null, 404);

    return view('partner.dashboard', compact('provider', 'profile'));
})->name('partner.dashboard');

// Kelola Pesanan Masuk Mitra #1: data hanya localStorage, route terlindungi.
Route::get('/mitra/pesanan', function () {
    $provider = collect(config('jasawarga.providers', []))->firstWhere('id', 1);
    abort_if($provider === null, 404);

    return view('partner.orders.index', compact('provider'));
})->name('partner.orders.index');

// Kelola Layanan & Tarif Mitra #1 - seluruh perubahan disimpan di browser.
Route::get('/mitra/layanan', function () {
    $provider = collect(config('jasawarga.providers', []))->firstWhere('id', 1);
    abort_if($provider === null, 404);
    $profile = config('jasawarga_profiles.1');
    abort_if(!is_array($profile) || empty($profile['services']), 404);
    $categories = config('jasawarga.categories', []);

    return view('partner.services.index', compact('provider', 'profile', 'categories'));
})->name('partner.services.index');

// Kelola jadwal Mitra #1. Semua pengaturan hanya disimpan di browser.
Route::get('/mitra/jadwal', function () {
    $provider = collect(config('jasawarga.providers', []))->firstWhere('id', 1);
    abort_if($provider === null, 404);
    $profile = config('jasawarga_profiles.1');
    abort_if(!is_array($profile), 404);

    return view('partner.schedule.index', compact('provider', 'profile'));
})->name('partner.schedule.index');

// Kelola Profil & Portofolio Mitra #1 — demo satu browser, tanpa backend upload.
Route::get('/mitra/profil', function () {
    $provider = collect(config('jasawarga.providers', []))->firstWhere('id', 1);
    abort_if($provider === null, 404);
    $profile = config('jasawarga_profiles.1');
    abort_if(!is_array($profile), 404);

    $areas = config('jasawarga-profile.bandung_districts', []);
    return view('partner.profile.index', compact('provider', 'profile', 'areas'));
})->name('partner.profile.index');

// Ulasan yang diterima Mitra demo #1 — data dan balasan dari localStorage browser.
Route::get('/mitra/ulasan', function () {
    $provider = collect(config('jasawarga.providers', []))->firstWhere('id', 1);
    abort_if($provider === null, 404);

    return view('partner.reviews.index', compact('provider'));
})->name('partner.reviews.index');

// Pengaturan Akun Mitra demo: preferensi lokal; autentikasi dari Laravel.
Route::get('/mitra/pengaturan', function () {
    $provider = collect(config('jasawarga.providers', []))->firstWhere('id', 1);
    abort_if($provider === null, 404);
    return view('partner.settings.index', compact('provider'));
})->name('partner.settings.index');

// Semua rute operasional dummy hanya untuk demo_provider_id=1.
});

// Backend Tahap 3: kategori dan layanan PostgreSQL (terpisah dari katalog dummy).
require __DIR__.'/stage3_catalog.php';

// Backend Tahap 5, terpisah dari Tahap 4.
require __DIR__.'/stage5_feedback.php';

// Tahap 2/4/6: backend terpisah; tidak menimpa route demo legacy.
require __DIR__.'/profile-stage2.php';
require __DIR__.'/booking-stage4.php';
require __DIR__.'/analytics-stage6.php';
