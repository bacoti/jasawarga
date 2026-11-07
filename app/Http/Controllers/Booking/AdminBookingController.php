<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::query()->with(['warga', 'partnerProfile']);
        if ($request->filled('status')) $query->where('status', $request->string('status')->toString());
        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(function ($q) use ($term) {
                $q->where('booking_code', 'like', $term)->orWhere('service_name_snapshot', 'like', $term);
            });
        }
        $bookings = $query->latest('created_at')->paginate(20)->withQueryString();
        return view('booking.admin.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['warga', 'partnerProfile', 'offers.creator', 'statusHistories.actor']);
        return view('booking.admin.show', compact('booking'));
    }
}
