<?php

namespace App\Providers;

use App\Models\Booking;
use App\Observers\BookingObserver;
use Illuminate\Support\ServiceProvider;

class JasaWargaStageSixServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (class_exists(Booking::class)) {
            Booking::observe(BookingObserver::class);
        }
    }
}
