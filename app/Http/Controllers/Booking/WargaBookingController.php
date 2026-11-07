<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\Booking;
use App\Models\BookingOffer;
use App\Models\PartnerProfile;
use App\Models\UserAddress;
use App\Services\Booking\BookingWorkflowService;
use App\Services\Booking\ServiceCatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WargaBookingController extends Controller
{
    public function create(string $slug, ServiceCatalog $catalog)
    {
        $partner = PartnerProfile::query()->where('slug', $slug)->firstOrFail();
        abort_unless($partner->isPubliclyVisible(), 404);
        $services = $catalog->activeForPartner($partner->id);
        $addresses = UserAddress::query()->where('user_id', auth()->id())->orderByDesc('is_primary')->get();
        return view('booking.warga.create', compact('partner', 'services', 'addresses'));
    }

    public function store(StoreBookingRequest $request, string $slug, BookingWorkflowService $workflow)
    {
        $partner = PartnerProfile::query()->where('slug', $slug)->firstOrFail();
        $booking = $workflow->create($request->user(), $partner, $request->validated());
        return redirect()->route('account.bookings.show', $booking->booking_code)
            ->with('success', 'Booking berhasil dikirim. Slot ditahan sementara menunggu respons Mitra.');
    }

    public function index(Request $request)
    {
        $bookings = Booking::query()->with('partnerProfile')
            ->where('user_id', $request->user()->id)
            ->latest('created_at')->paginate(12);
        return view('booking.warga.index', compact('bookings'));
    }

    public function show(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        $booking->load(['partnerProfile', 'offers.creator', 'statusHistories.actor']);
        return view('booking.warga.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking, BookingWorkflowService $workflow)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $workflow->cancelByWarga($booking, $request->user(), $data['reason']);
        return back()->with('success', 'Booking berhasil dibatalkan.');
    }

    public function respondToOffer(Request $request, Booking $booking, BookingOffer $offer, BookingWorkflowService $workflow)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        $data = $request->validate(['decision' => ['required', 'in:approve,reject']]);
        $workflow->wargaRespondToOffer($booking, $offer, $request->user(), $data['decision'] === 'approve');
        return back()->with('success', $data['decision'] === 'approve' ? 'Harga final disetujui.' : 'Penawaran ditolak; inspeksi dicatat selesai tanpa perbaikan.');
    }

    public function confirmCompletion(Request $request, Booking $booking, BookingWorkflowService $workflow)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        $workflow->confirmCompletion($booking, $request->user());
        return back()->with('success', 'Pekerjaan berhasil dikonfirmasi selesai.');
    }
}
