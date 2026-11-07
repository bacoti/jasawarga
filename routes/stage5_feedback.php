<?php

use App\Http\Controllers\DatabaseAdminComplaintController;
use App\Http\Controllers\DatabaseAdminReviewController;
use App\Http\Controllers\DatabaseComplaintController;
use App\Http\Controllers\DatabasePartnerReviewController;
use App\Http\Controllers\DatabaseReviewController;
use Illuminate\Support\Facades\Route;

// Terpisah dari route Tahap 4 dan dari prototype localStorage.
// Route publik hanya menampilkan ulasan yang terverifikasi DAN tidak disembunyikan.
Route::get('/ulasan-db', [DatabaseReviewController::class, 'publicIndex'])->name('db-feedback.reviews.public');

Route::middleware(['auth','role:citizen'])->group(function () {
    Route::get('/ulasan-db/saya', [DatabaseReviewController::class, 'mine'])->name('db-feedback.reviews.mine');
    Route::get('/ulasan-db/tulis', [DatabaseReviewController::class, 'create'])->name('db-feedback.reviews.create');
    Route::post('/ulasan-db', [DatabaseReviewController::class, 'store'])->middleware('throttle:5,1')->name('db-feedback.reviews.store');
    Route::delete('/ulasan-db/{serviceReview}/pengajuan', [DatabaseReviewController::class, 'destroyPending'])->name('db-feedback.reviews.destroy-pending');
    Route::get('/pengaduan-db', [DatabaseComplaintController::class, 'index'])->name('db-feedback.complaints.mine');
    Route::post('/pengaduan-db', [DatabaseComplaintController::class, 'store'])->middleware('throttle:5,1')->name('db-feedback.complaints.store');
});

Route::middleware(['auth','role:partner','partner.approved'])->group(function () {
    Route::get('/mitra/ulasan-db', [DatabasePartnerReviewController::class, 'index'])->name('partner.db-feedback.reviews.index');
    Route::post('/mitra/ulasan-db/{serviceReview}/balasan', [DatabasePartnerReviewController::class, 'reply'])->middleware('throttle:10,1')->name('partner.db-feedback.reviews.reply');
    Route::delete('/mitra/ulasan-db/{serviceReview}/balasan', [DatabasePartnerReviewController::class, 'destroyReply'])->name('partner.db-feedback.reviews.destroy-reply');
});

Route::middleware(['auth','role:admin'])->group(function () {
    Route::get('/admin/ulasan-db', [DatabaseAdminReviewController::class, 'index'])->name('admin.db-feedback.reviews.index');
    Route::post('/admin/ulasan-db/{serviceReview}/cek', [DatabaseAdminReviewController::class, 'recheck'])->middleware('throttle:20,1')->name('admin.db-feedback.reviews.recheck');
    Route::patch('/admin/ulasan-db/{serviceReview}/moderasi', [DatabaseAdminReviewController::class, 'moderate'])->name('admin.db-feedback.reviews.moderate');
    Route::get('/admin/pengaduan-db', [DatabaseAdminComplaintController::class, 'index'])->name('admin.db-feedback.complaints.index');
    Route::patch('/admin/pengaduan-db/{serviceComplaint}', [DatabaseAdminComplaintController::class, 'update'])->name('admin.db-feedback.complaints.update');
});
