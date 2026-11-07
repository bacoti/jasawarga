<?php

// Backend Tahap 3 sengaja diisolasi dari route yang mungkin diedit pada Tahap 2.
// Route lama /kategori-jasa, /admin/kategori, dan /mitra/layanan tetap mode demo.

use App\Http\Controllers\DatabaseCategoryController;
use App\Http\Controllers\DatabasePartnerServiceController;
use App\Http\Controllers\DatabasePublicCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/katalog-jasa', [DatabasePublicCatalogController::class, 'index'])
    ->name('db-catalog.index');
Route::get('/katalog-jasa/{partnerService}', [DatabasePublicCatalogController::class, 'show'])
    ->whereNumber('partnerService')->name('db-catalog.show');

Route::middleware(['auth', 'role:admin'])->prefix('admin/kategori-db')->name('admin.db-categories.')->group(function () {
    Route::get('/', [DatabaseCategoryController::class, 'index'])->name('index');
    Route::post('/', [DatabaseCategoryController::class, 'store'])->name('store');
    Route::patch('/{serviceCategory}', [DatabaseCategoryController::class, 'update'])->name('update');
    Route::patch('/{serviceCategory}/status', [DatabaseCategoryController::class, 'status'])->name('status');
    Route::delete('/{serviceCategory}', [DatabaseCategoryController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'role:partner', 'partner.approved'])
    ->prefix('mitra/layanan-db')->name('partner.db-services.')->group(function () {
        Route::get('/', [DatabasePartnerServiceController::class, 'index'])->name('index');
        Route::post('/', [DatabasePartnerServiceController::class, 'store'])->name('store');
        Route::patch('/{partnerService}', [DatabasePartnerServiceController::class, 'update'])->name('update');
        Route::patch('/{partnerService}/status', [DatabasePartnerServiceController::class, 'status'])->name('status');
        Route::delete('/{partnerService}', [DatabasePartnerServiceController::class, 'destroy'])->name('destroy');
    });
