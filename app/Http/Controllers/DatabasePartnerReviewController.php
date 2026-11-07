<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReplyServiceReviewRequest;
use App\Models\ServiceReview;
use App\Models\ServiceReviewReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DatabasePartnerReviewController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->partnerProfile;
        $reviews = ServiceReview::where('partner_profile_id', $profile->id)
            ->where('verification_status', 'verified')
            ->with(['citizen:id,name','service:id,name','reply'])
            ->latest()->paginate(12);
        return view('backend-feedback.reviews.partner', compact('reviews','profile'));
    }

    public function reply(ReplyServiceReviewRequest $request, ServiceReview $serviceReview): RedirectResponse
    {
        $profileId = $request->user()->partnerProfile->id;
        $data = $request->validated();
        DB::transaction(function () use ($serviceReview, $profileId, $data) {
            $review = ServiceReview::whereKey($serviceReview->id)
                ->where('partner_profile_id', $profileId)
                ->where('verification_status', 'verified')->lockForUpdate()->firstOrFail();
            $reply = ServiceReviewReply::where('service_review_id', $review->id)->first();
            if ($reply) {
                $reply->update(['body' => $data['body'], 'edited_at' => now()]);
            } else {
                ServiceReviewReply::create([
                    'service_review_id' => $review->id,
                    'partner_profile_id' => $profileId,
                    'body' => $data['body'],
                ]);
            }
        });
        return back()->with('success', 'Balasan Mitra berhasil disimpan.');
    }

    public function destroyReply(Request $request, ServiceReview $serviceReview): RedirectResponse
    {
        $profileId = $request->user()->partnerProfile->id;
        DB::transaction(function () use ($serviceReview, $profileId) {
            $review = ServiceReview::whereKey($serviceReview->id)
                ->where('partner_profile_id', $profileId)->lockForUpdate()->firstOrFail();
            ServiceReviewReply::where('service_review_id', $review->id)
                ->where('partner_profile_id', $profileId)->firstOrFail()->delete();
        });
        return back()->with('success', 'Balasan dihapus tanpa mengubah komentar Warga.');
    }
}
