<?php

use App\Http\Controllers\Analytics\AdminDashboardController;
use App\Http\Controllers\Analytics\AdminReportController;
use App\Http\Controllers\Analytics\PartnerDashboardController;
use App\Http\Controllers\Analytics\WargaDashboardController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Middleware\EnsureAccountRole;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifikasi/{notification}/baca', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::middleware(EnsureAccountRole::class.':citizen')->prefix('akun')->name('account.')->group(function () {
        Route::get('/dashboard', WargaDashboardController::class)->name('analytics.dashboard');
    });

    Route::middleware(EnsureAccountRole::class.':partner')->prefix('mitra/workspace')->name('partner.')->group(function () {
        Route::get('/overview', PartnerDashboardController::class)->name('analytics.dashboard');
    });

    Route::middleware(EnsureAccountRole::class.':admin')->prefix('admin')->name('admin.analytics.')->group(function () {
        // Tautan dari patch lama tetap membawa Admin ke dashboard desain navy.
        Route::get('/overview', fn () => redirect()->route('admin.dashboard'))->name('dashboard');
        // Statistik PostgreSQL tetap tersedia tanpa mengganti tampilan dashboard utama.
        Route::get('/overview-db', AdminDashboardController::class)->name('database');
        Route::get('/laporan-db', [AdminReportController::class, 'index'])->name('reports');
        Route::get('/laporan-db/export', [AdminReportController::class, 'export'])->name('reports.export');
    });
});
