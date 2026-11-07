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
    public function index(\Illuminate\Http\Request $request): View
    {
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $status = (string) $request->query('status', 'all');
        if (! in_array($status, ['all', 'pending', 'approved', 'rejected', 'reverification'], true)) {
            $status = 'all';
        }

        $query = PartnerProfile::query()->whereNull('demo_provider_id');
        $stats = [
            'all' => (clone $query)->count(),
            'approved' => (clone $query)->where('verification_status', 'approved')->where('reverification_required', false)->count(),
            'pending' => (clone $query)->where('verification_status', 'pending')->count(),
            'rejected' => (clone $query)->where('verification_status', 'rejected')->count(),
            'reverification' => (clone $query)->where('reverification_required', true)->count(),
        ];
        $applications = $query->with('user')
            ->withCount(['services as published_services_count' => fn ($q) => $q->published()])
            ->when($search !== '', function ($q) use ($search) {
                $term = '%'.mb_strtolower($search).'%';
                $q->where(fn ($p) => $p->whereRaw('LOWER(business_name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(service_area) LIKE ?', [$term])
                    ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$term])));
            })
            ->when($status === 'approved', fn ($q) => $q->where('verification_status', 'approved')->where('reverification_required', false))
            ->when($status === 'pending', fn ($q) => $q->where('verification_status', 'pending'))
            ->when($status === 'rejected', fn ($q) => $q->where('verification_status', 'rejected'))
            ->when($status === 'reverification', fn ($q) => $q->where('reverification_required', true))
            ->orderByRaw("CASE verification_status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->latest('id')->paginate(12)->withQueryString();

        return view('admin.partners.index', compact('applications', 'search', 'status', 'stats'));
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
