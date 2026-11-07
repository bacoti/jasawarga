<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PartnerProfile;
use App\Models\UserPreference;
use Illuminate\View\View;

class PartnerProfileController extends Controller
{
    public function show(string $slug): View
    {
        $profile = PartnerProfile::where('slug', $slug)
            ->where('verification_status', 'approved')
            ->where('reverification_required', false)
            ->whereHas('user', fn ($query) => $query->where('role', 'partner')->where('account_status', 'active'))
            ->with(['portfolios', 'user'])
            ->firstOrFail();

        $preferences = UserPreference::where('user_id', $profile->user_id)->first();
        $privacy = $preferences?->privacy ?? ['profile_visible' => true, 'show_phone' => false];
        abort_unless(($privacy['profile_visible'] ?? true) === true, 404);

        return view('public.partners.show', compact('profile', 'privacy'));
    }
}
