<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class WargaDashboardController extends Controller
{
    public function __invoke(): View
    {
        $query = Booking::query()->where('user_id', auth()->id());
        $active = config('jasawarga-analytics.active_booking_statuses', []);
        $available = Schema::hasTable('bookings');
        $stats = [
            'total' => $available ? (clone $query)->count() : 0,
            'active' => $available ? (clone $query)->whereIn('status', $active)->count() : 0,
            'completed' => $available ? (clone $query)->where('status', Booking::COMPLETED)->count() : 0,
            'pending_price' => $available ? (clone $query)->where('status', Booking::PENDING_PRICE_APPROVAL)->count() : 0,
        ];
        $recent = $available ? (clone $query)->with('partnerProfile')->latest()->limit(8)->get() : collect();
        return view('analytics.warga.dashboard', compact('stats', 'recent'));
    }
}
