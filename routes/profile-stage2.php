<?php

use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\ProfileController as AccountProfileController;
use App\Http\Controllers\Account\SettingsController;
use App\Http\Controllers\Partner\PortfolioController;
use App\Http\Controllers\Partner\ProfileController as PartnerProfileController;
use App\Http\Controllers\Public\PartnerProfileController as PublicPartnerProfileController;
use App\Http\Middleware\EnsureAccountRole;
use Illuminate\Support\Facades\Route;

/*
 * Tambahkan `require __DIR__.'/profile-stage2.php';` di routes/web.php.
 * Route memakai session guard Laravel yang sudah disiapkan pada Tahap 1.
 */
Route::middleware('auth')->group(function () {
    Route::middleware(EnsureAccountRole::class.':citizen')->prefix('akun')->name('account.')->group(function () {
        Route::get('/profil', [AccountProfileController::class, 'show'])->name('profile.show');
        Route::get('/profil/edit', [AccountProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [AccountProfileController::class, 'update'])->name('profile.update');

        Route::get('/alamat', [AddressController::class, 'index'])->name('addresses.index');
        Route::get('/alamat/tambah', [AddressController::class, 'create'])->name('addresses.create');
        Route::post('/alamat', [AddressController::class, 'store'])->name('addresses.store');
        Route::get('/alamat/{address}/edit', [AddressController::class, 'edit'])->whereNumber('address')->name('addresses.edit');
        Route::put('/alamat/{address}', [AddressController::class, 'update'])->whereNumber('address')->name('addresses.update');
        Route::patch('/alamat/{address}/utama', [AddressController::class, 'makePrimary'])->whereNumber('address')->name('addresses.primary');
        Route::delete('/alamat/{address}', [AddressController::class, 'destroy'])->whereNumber('address')->name('addresses.destroy');
    });

    Route::middleware(EnsureAccountRole::class.':citizen,partner')->group(function () {
        Route::get('/akun/pengaturan', [SettingsController::class, 'show'])->name('account.settings.show');
        Route::put('/akun/pengaturan/akun', [SettingsController::class, 'updateAccount'])->name('account.settings.account');
        Route::put('/akun/pengaturan/password', [SettingsController::class, 'updatePassword'])->name('account.settings.password');
        Route::put('/akun/pengaturan/preferensi', [SettingsController::class, 'updatePreferences'])->name('account.settings.preferences');
    });

    Route::middleware(EnsureAccountRole::class.':partner')->prefix('mitra/workspace')->name('partner.')->group(function () {
        Route::get('/profil', [PartnerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [PartnerProfileController::class, 'update'])->name('profile.update');
        Route::get('/portofolio', [PortfolioController::class, 'index'])->name('portfolio.index');
        Route::get('/portofolio/tambah', [PortfolioController::class, 'create'])->name('portfolio.create');
        Route::post('/portofolio', [PortfolioController::class, 'store'])->name('portfolio.store');
        Route::get('/portofolio/{portfolio}/edit', [PortfolioController::class, 'edit'])->whereNumber('portfolio')->name('portfolio.edit');
        Route::put('/portofolio/{portfolio}', [PortfolioController::class, 'update'])->whereNumber('portfolio')->name('portfolio.update');
        Route::delete('/portofolio/{portfolio}', [PortfolioController::class, 'destroy'])->whereNumber('portfolio')->name('portfolio.destroy');
    });
});

// Halaman publik hanya menampilkan Mitra disetujui dan tidak menunggu pemeriksaan ulang.
Route::get('/profil-mitra/{slug}', [PublicPartnerProfileController::class, 'show'])
    ->where('slug', '[A-Za-z0-9-]+')->name('partners.public.show');
