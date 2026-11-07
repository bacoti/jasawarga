<?php

namespace App\Services\Feedback;

use App\Models\Booking;
use Illuminate\Support\Facades\Schema;

/** Ground verified reviews solely in Stage 4 bookings, never in localStorage codes. */
class ReviewBookingVerifier
{
    public function isEligible(int $citizenId, int $partnerServiceId, string $bookingReference): bool
    {
        if ($citizenId < 1 || $partnerServiceId < 1 || $bookingReference === ''
            || str_starts_with($bookingReference, 'JW-DEMO-') || ! Schema::hasTable('bookings')) {
            return false;
        }
        return Booking::query()
            ->where('booking_code', $bookingReference)
            ->where('user_id', $citizenId)
            ->where('service_id', $partnerServiceId)
            ->where('status', Booking::COMPLETED)
            ->whereNotNull('completed_at')
            ->whereNull('cancelled_at')
            ->exists();
    }
}
