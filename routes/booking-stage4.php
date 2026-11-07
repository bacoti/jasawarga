<?php

use App\Http\Controllers\Booking\AdminBookingController;
use App\Http\Controllers\Booking\BookingAvailabilityController;
use App\Http\Controllers\Booking\PartnerBookingController;
use App\Http\Controllers\Booking\PartnerScheduleController;
use App\Http\Controllers\Booking\WargaBookingController;
use App\Http\Middleware\EnsureAccountRole;
use Illuminate\Support\Facades\Route;

// Slot publik tidak membuat transaksi. Hanya POST booking oleh Warga yang membuat record.
Route::get('/profil-mitra/{slug}/slot-tersedia', [BookingAvailabilityController::class, 'show'])
    ->where('slug', '[A-Za-z0-9-]+')->name('bookings.availability');

Route::middleware(['auth', EnsureAccountRole::class.':citizen'])->group(function () {
    Route::get('/profil-mitra/{slug}/booking', [WargaBookingController::class, 'create'])
        ->where('slug', '[A-Za-z0-9-]+')->name('bookings.database.create');
    Route::post('/profil-mitra/{slug}/booking', [WargaBookingController::class, 'store'])
        ->where('slug', '[A-Za-z0-9-]+')->name('bookings.store');

    Route::prefix('akun/pesanan')->name('account.bookings.')->group(function () {
        Route::get('/', [WargaBookingController::class, 'index'])->name('index');
        Route::get('/{booking:booking_code}', [WargaBookingController::class, 'show'])->name('show');
        Route::post('/{booking:booking_code}/batal', [WargaBookingController::class, 'cancel'])->name('cancel');
        Route::post('/{booking:booking_code}/penawaran/{offer}/respons', [WargaBookingController::class, 'respondToOffer'])->whereNumber('offer')->name('offer.respond');
        Route::post('/{booking:booking_code}/konfirmasi-selesai', [WargaBookingController::class, 'confirmCompletion'])->name('complete.confirm');
    });
});

Route::middleware(['auth', EnsureAccountRole::class.':partner'])->prefix('mitra/workspace')->name('partner.')->group(function () {
    Route::get('/pesanan', [PartnerBookingController::class, 'index'])->name('bookings.index');
    Route::get('/pesanan/{booking:booking_code}', [PartnerBookingController::class, 'show'])->name('bookings.show');
    Route::post('/pesanan/{booking:booking_code}/respons', [PartnerBookingController::class, 'respond'])->name('bookings.respond');
    Route::post('/pesanan/{booking:booking_code}/penawaran', [PartnerBookingController::class, 'submitOffer'])->name('bookings.offer');
    Route::post('/pesanan/{booking:booking_code}/mulai', [PartnerBookingController::class, 'start'])->name('bookings.start');
    Route::post('/pesanan/{booking:booking_code}/ajukan-selesai', [PartnerBookingController::class, 'requestCompletion'])->name('bookings.complete.request');

    Route::get('/jadwal', [PartnerScheduleController::class, 'index'])->name('schedule.index');
    Route::put('/jadwal/jam-operasional', [PartnerScheduleController::class, 'update'])->name('schedule.update');
    Route::post('/jadwal/blokir', [PartnerScheduleController::class, 'storeBlock'])->name('schedule.blocks.store');
    Route::delete('/jadwal/blokir/{block}', [PartnerScheduleController::class, 'destroyBlock'])->whereNumber('block')->name('schedule.blocks.destroy');
});

Route::middleware(['auth', EnsureAccountRole::class.':admin'])->prefix('admin/pesanan-db')->name('admin.bookings.')->group(function () {
    Route::get('/', [AdminBookingController::class, 'index'])->name('index');
    Route::get('/{booking:booking_code}', [AdminBookingController::class, 'show'])->name('show');
});
