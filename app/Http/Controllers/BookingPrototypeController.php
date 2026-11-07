<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman prototipe saja. Tidak menerima POST dan tidak menyimpan pesanan.
 */
class BookingPrototypeController
{
    public function create(Request $request, int $id): View
    {
        $provider = collect(config('jasawarga.providers', []))
            ->firstWhere('id', $id);

        abort_if($provider === null, 404);

        $profile = config("jasawarga_profiles.{$id}");
        abort_if(! is_array($profile) || empty($profile['services']), 404);

        $index = filter_var($request->query('layanan', 0), FILTER_VALIDATE_INT);
        $selectedIndex = $index !== false && array_key_exists($index, $profile['services'])
            ? $index : 0;

        $category = collect(config('jasawarga.categories', []))
            ->firstWhere('slug', $provider['category']);

        // Aturan Minggu dipakai hanya untuk default awal schedule-store.js.
        // Slot final diambil dari localStorage melalui booking.js.
        $allowSunday = str_starts_with($profile['hours'] ?? '', 'Setiap hari');

        return view('bookings.create', compact(
            'provider', 'profile', 'category', 'selectedIndex', 'allowSunday'
        ));
    }

    public function success(): View
    {
        // Ringkasan dipindahkan antarhalaman melalui sessionStorage di browser,
        // bukan dari database, form POST, ataupun session server Laravel.
        return view('bookings.success');
    }
}
