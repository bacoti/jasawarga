<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PartnerProfile;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PartnerDashboardController extends Controller
{
    public function __invoke(): View
    {
        $partner = PartnerProfile::query()->where('user_id', auth()->id())->firstOrFail();
        $query = Booking::query()->where('partner_profile_id', $partner->id);
        $active = config('jasawarga-analytics.active_booking_statuses', []);
        $bookingsAvailable = Schema::hasTable('bookings');
        $stats = [
            'total' => $bookingsAvailable ? (clone $query)->count() : 0,
            'pending' => $bookingsAvailable ? (clone $query)->where('status', Booking::PENDING_PARTNER)->count() : 0,
            'active' => $bookingsAvailable ? (clone $query)->whereIn('status', $active)->count() : 0,
            'completed' => $bookingsAvailable ? (clone $query)->where('status', Booking::COMPLETED)->count() : 0,
            'upcoming' => $bookingsAvailable ? (clone $query)->whereIn('status', $active)->where('scheduled_at', '>=', now())->count() : 0,
        ];
        $recent = $bookingsAvailable ? (clone $query)->with('warga')->latest()->limit(8)->get() : collect();
        return view('analytics.partner.dashboard', compact('partner', 'stats', 'recent'));
    }
}
