<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitServiceComplaintRequest;
use App\Models\PartnerService;
use App\Models\ServiceComplaint;
use App\Models\ServiceReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DatabaseComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $complaints = ServiceComplaint::where('reporter_id', $request->user()->id)
            ->with(['service:id,name','actions.admin:id,name'])
            ->latest()->paginate(12);
        $services = PartnerService::published()->orderBy('name')->get(['id','name']);
        $reviews = ServiceReview::where('citizen_id', $request->user()->id)
            ->latest()->limit(40)->get(['id','booking_reference']);
        return view('backend-feedback.complaints.index', compact('complaints','services','reviews'));
    }

    public function store(SubmitServiceComplaintRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $review = isset($data['service_review_id'])
            ? ServiceReview::where('citizen_id', $request->user()->id)
                ->findOrFail($data['service_review_id']) : null;
        $service = isset($data['partner_service_id'])
            ? PartnerService::withTrashed()->findOrFail($data['partner_service_id']) : null;
        if ($review && $service && $review->partner_service_id !== $service->id) {
            throw ValidationException::withMessages(['partner_service_id' => 'Layanan tidak cocok dengan ulasan milik Warga.']);
        }
        if ($review && !empty($data['booking_reference']) && $review->booking_reference !== $data['booking_reference']) {
            throw ValidationException::withMessages(['booking_reference' => 'Kode pesanan berbeda dari ulasan yang dipilih.']);
        }
        $serviceId = $review?->partner_service_id ?? $service?->id;
        $partnerId = $review?->partner_profile_id ?? $service?->partner_profile_id;
        ServiceComplaint::create([
            'reporter_id' => $request->user()->id,
            'partner_profile_id' => $partnerId,
            'partner_service_id' => $serviceId,
            'service_review_id' => $review?->id,
            'booking_reference' => $review?->booking_reference ?? ($data['booking_reference'] ?? null),
            'subject' => $data['subject'],
            'description' => $data['description'],
        ]);
        return redirect()->route('db-feedback.complaints.mine')
            ->with('success', 'Pengaduan tersimpan di PostgreSQL. Jika ada referensi pesanan, Admin masih perlu mengecek kepemilikannya di Monitoring Pesanan.');
    }
}
