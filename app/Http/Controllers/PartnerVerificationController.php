<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartnerDecisionRequest;
use App\Models\PartnerProfile;
use App\Models\PartnerVerificationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PartnerVerificationController extends Controller
{
    public function index(): View
    {
        return view('auth.admin-partner-applications', [
            'applications' => PartnerProfile::with('user')
                ->whereNull('demo_provider_id')
                ->orderByRaw("CASE verification_status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END")
                ->latest()->paginate(15),
        ]);
    }

    public function decide(PartnerDecisionRequest $request, PartnerProfile $partnerProfile): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($partnerProfile, $request, $data) {
            $profile = PartnerProfile::query()->lockForUpdate()->findOrFail($partnerProfile->id);
            abort_if($profile->demo_provider_id !== null, 403, 'Status katalog demo bukan bagian dari pendaftaran baru.');
            abort_unless(in_array($profile->verification_status, ['pending', 'rejected', 'approved'], true), 409);
            abort_if($profile->verification_status === $data['decision'] && ! $profile->reverification_required, 409, 'Status sudah sama dengan keputusan baru.');
            $previous = $profile->verification_status;
            $profile->update([
                'verification_status' => $data['decision'],
                'verified_at' => $data['decision'] === 'approved' ? now() : null,
                'verified_by' => $request->user()->id,
                'decision_reason' => trim($data['reason']),
                'reverification_required' => false,
            ]);
            PartnerVerificationLog::create([
                'partner_profile_id' => $profile->id,
                'admin_id' => $request->user()->id,
                'from_status' => $previous,
                'to_status' => $data['decision'],
                'reason' => trim($data['reason']),
            ]);
        });
        return back()->with('success', 'Keputusan verifikasi Mitra tersimpan di PostgreSQL.');
    }
}
