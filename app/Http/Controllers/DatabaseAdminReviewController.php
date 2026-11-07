<?php

namespace App\Http\Controllers;

use App\Models\ServiceReview;
use App\Models\ServiceReviewModerationLog;
use App\Services\Feedback\ReviewBookingVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DatabaseAdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('verifikasi');
        $moderation = $request->query('moderasi');
        $query = ServiceReview::with(['citizen:id,name','partner:id,business_name','service:id,name','reply','moderationLogs.admin:id,name']);
        if (in_array($status, ['pending','verified'], true)) $query->where('verification_status', $status);
        if (in_array($moderation, ['visible','hidden'], true)) $query->where('moderation_status', $moderation);
        $reviews = $query->latest()->paginate(15)->withQueryString();
        return view('backend-feedback.reviews.admin', compact('reviews'));
    }

    public function recheck(Request $request, ServiceReview $serviceReview, ReviewBookingVerifier $verifier): RedirectResponse
    {
        DB::transaction(function () use ($serviceReview, $verifier) {
            $review = ServiceReview::whereKey($serviceReview->id)->lockForUpdate()->firstOrFail();
            if ($review->verification_status === 'verified') return;
            if (!$verifier->isEligible($review->citizen_id, $review->partner_service_id, $review->booking_reference)) {
                throw ValidationException::withMessages(['verifikasi' => 'Belum ditemukan bukti pesanan selesai milik Warga pada Tahap 4. Status tidak diubah.']);
            }
            $review->update(['verification_status' => 'verified', 'verified_at' => now()]);
        });
        return back()->with('success', 'Pesanan berhasil diverifikasi. Ulasan mengikuti status moderasinya.');
    }

    public function moderate(Request $request, ServiceReview $serviceReview): RedirectResponse
    {
        $data = $request->validate([
            'moderation_status' => ['required','in:visible,hidden'],
            'reason' => ['required','string','min:10','max:500'],
        ]);
        DB::transaction(function () use ($serviceReview, $request, $data) {
            $review = ServiceReview::whereKey($serviceReview->id)->lockForUpdate()->firstOrFail();
            if ($review->moderation_status === $data['moderation_status']) {
                throw ValidationException::withMessages(['moderation_status' => 'Status moderasi belum berubah.']);
            }
            ServiceReviewModerationLog::create([
                'service_review_id' => $review->id,
                'admin_id' => $request->user()->id,
                'from_status' => $review->moderation_status,
                'to_status' => $data['moderation_status'],
                'reason' => $data['reason'],
            ]);
            $review->update([
                'moderation_status' => $data['moderation_status'],
                'moderation_reason' => $data['reason'],
                'moderated_by' => $request->user()->id,
                'moderated_at' => now(),
            ]);
        });
        return back()->with('success', 'Keputusan moderasi tersimpan dan dicatat dalam log.');
    }
}
