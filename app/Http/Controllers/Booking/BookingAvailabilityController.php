<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\PartnerProfile;
use App\Services\Booking\BookingAvailabilityService;
use Illuminate\Http\Request;

class BookingAvailabilityController extends Controller
{
    public function show(Request $request, string $slug, BookingAvailabilityService $availability)
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $partner = PartnerProfile::query()->where('slug', $slug)->firstOrFail();
        abort_unless($partner->isPubliclyVisible(), 404);

        return response()->json([
            'date' => $validated['date'],
            'slots' => $availability->availableSlots($partner, (int) $validated['service_id'], $validated['date']),
        ]);
    }
}
