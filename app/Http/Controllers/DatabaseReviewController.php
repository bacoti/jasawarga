<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitServiceReviewRequest;
use App\Models\PartnerService;
use App\Models\ServiceReview;
use App\Services\Feedback\ReviewBookingVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DatabaseReviewController extends Controller
{
    public function publicIndex(Request $request): View
    {
        $star = $request->integer('bintang');
        $base = ServiceReview::published();
        if ($star >= 1 && $star <= 5) $base->where('rating', $star);
        $reviews = $base->with(['citizen:id,name','partner:id,business_name','service:id,name','reply'])
            ->latest()->paginate(10)->withQueryString();
        return view('backend-feedback.reviews.public', compact('reviews'));
    }

    public function create(Request $request): View
    {
        $services = PartnerService::published()->with('partner:id,business_name')->orderBy('name')->get();
        return view('backend-feedback.reviews.create', [
            'services' => $services,
            'selectedService' => $request->integer('layanan'),
        ]);
    }

    public function mine(Request $request): View
    {
        $reviews = ServiceReview::with(['service:id,name','partner:id,business_name','reply'])
            ->where('citizen_id', $request->user()->id)->latest()->paginate(12);
        return view('backend-feedback.reviews.mine', compact('reviews'));
    }

    public function store(SubmitServiceReviewRequest $request, ReviewBookingVerifier $verifier): RedirectResponse
    {
        $data = $request->validated();
        $service = PartnerService::withTrashed()->with('partner')
            ->findOrFail((int) $data['partner_service_id']);
        abort_if($service->partner === null, 404);
        $citizenId = $request->user()->id;
        $reference = $data['booking_reference'];
        if (ServiceReview::where('citizen_id', $citizenId)
            ->where('partner_service_id', $service->id)
            ->where('booking_reference', $reference)->exists()) {
            throw ValidationException::withMessages(['booking_reference' => 'Ulasan untuk kombinasi pesanan dan layanan ini sudah ada.']);
        }

        // Standar awal: unverified. Tidak ada bukti pemesanan dari localStorage.
        $verified = $verifier->isEligible($citizenId, $service->id, $reference);
        $photo = $request->hasFile('photo') ? $request->file('photo')->store('feedback/reviews', 'public') : null;
        try {
            DB::transaction(function () use ($data, $service, $citizenId, $reference, $verified, $photo) {
                ServiceReview::create([
                    'citizen_id' => $citizenId,
                    'partner_profile_id' => $service->partner_profile_id,
                    'partner_service_id' => $service->id,
                    'booking_reference' => $reference,
                    'verification_status' => $verified ? 'verified' : 'pending',
                    'verified_at' => $verified ? now() : null,
                    'rating' => $data['rating'],
                    'quality_rating' => $data['quality_rating'],
                    'timeliness_rating' => $data['timeliness_rating'],
                    'friendliness_rating' => $data['friendliness_rating'],
                    'comment' => $data['comment'],
                    'photo_path' => $photo,
                ]);
            });
        } catch (\Throwable $exception) {
            if ($photo) Storage::disk('public')->delete($photo);
            // Race antara dua request dengan referensi yang sama: database unique
            // constraint adalah sumber kebenaran terakhir.
            if ($exception instanceof QueryException && in_array((string) $exception->getCode(), ['23505','23000'], true)) {
                throw ValidationException::withMessages([
                    'booking_reference' => 'Kombinasi referensi pesanan dan layanan sudah digunakan.',
                ]);
            }
            throw $exception;
        }
        return redirect()->route('db-feedback.reviews.mine')->with('success', $verified
            ? 'Ulasan tersimpan dan terverifikasi dari pesanan PostgreSQL.'
            : 'Ulasan tercatat sebagai pengajuan. Belum tampil publik sampai pesanan selesai diverifikasi dari Tahap 4.');
    }

    public function destroyPending(Request $request, ServiceReview $serviceReview): RedirectResponse
    {
        $path = DB::transaction(function () use ($serviceReview, $request) {
            $review = ServiceReview::whereKey($serviceReview->id)
                ->where('citizen_id', $request->user()->id)->lockForUpdate()->firstOrFail();
            abort_unless($review->verification_status === 'pending', 403, 'Hanya pengajuan belum diverifikasi yang dapat dihapus.');
            $path = $review->photo_path;
            $review->delete();
            return $path;
        });
        if ($path) Storage::disk('public')->delete($path);
        return back()->with('success', 'Pengajuan ulasan belum diverifikasi dihapus.');
    }
}
