<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\SubmitBookingOfferRequest;
use App\Models\Booking;
use App\Models\PartnerProfile;
use App\Services\Booking\BookingWorkflowService;
use Illuminate\Http\Request;

class PartnerBookingController extends Controller
{
    public function index(Request $request)
    {
        $partner = $this->partnerFor($request);
        $bookings = Booking::query()->with('warga')
            ->where('partner_profile_id', $partner->id)
            ->latest('scheduled_at')->paginate(15);
        return view('booking.partner.index', compact('bookings'));
    }

    public function show(Request $request, Booking $booking)
    {
        $partner = $this->partnerFor($request);
        abort_unless($booking->partner_profile_id === $partner->id, 404);
        $booking->load(['warga', 'offers', 'statusHistories.actor']);
        return view('booking.partner.show', compact('booking'));
    }

    public function respond(Request $request, Booking $booking, BookingWorkflowService $workflow)
    {
        $partner = $this->partnerFor($request);
        abort_unless($booking->partner_profile_id === $partner->id, 404);
        $data = $request->validate(['decision' => ['required', 'in:accept,reject']]);
        $workflow->partnerRespond($booking, $request->user(), $data['decision'] === 'accept');
        return back()->with('success', $data['decision'] === 'accept' ? 'Booking diterima.' : 'Booking ditolak.');
    }

    public function submitOffer(SubmitBookingOfferRequest $request, Booking $booking, BookingWorkflowService $workflow)
    {
        $partner = $this->partnerFor($request);
        abort_unless($booking->partner_profile_id === $partner->id, 404);
        $workflow->submitFinalOffer($booking, $request->user(), (float) $request->validated('amount'), $request->validated('note'));
        return back()->with('success', 'Penawaran harga final berhasil dikirim ke Warga.');
    }

    public function start(Request $request, Booking $booking, BookingWorkflowService $workflow)
    {
        $partner = $this->partnerFor($request);
        abort_unless($booking->partner_profile_id === $partner->id, 404);
        $workflow->startWork($booking, $request->user());
        return back()->with('success', 'Pekerjaan dimulai.');
    }

    public function requestCompletion(Request $request, Booking $booking, BookingWorkflowService $workflow)
    {
        $partner = $this->partnerFor($request);
        abort_unless($booking->partner_profile_id === $partner->id, 404);
        $workflow->requestCompletion($booking, $request->user());
        return back()->with('success', 'Pengajuan penyelesaian dikirim ke Warga.');
    }

    private function partnerFor(Request $request): PartnerProfile
    {
        return PartnerProfile::query()->where('user_id', $request->user()->id)->firstOrFail();
    }
}
