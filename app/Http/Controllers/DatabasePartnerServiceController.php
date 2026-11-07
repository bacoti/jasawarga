<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePartnerServiceRequest;
use App\Models\PartnerService;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DatabasePartnerServiceController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->partnerProfile;
        $services = PartnerService::with('category')
            ->where('partner_profile_id', $profile->id)
            ->latest()->paginate(12);

        return view('backend-catalog.partner.services', [
            'profile' => $profile,
            'services' => $services,
            'categories' => ServiceCategory::where('is_active', true)
                ->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(SavePartnerServiceRequest $request): RedirectResponse
    {
        $values = $request->validated();
        $profile = $request->user()->partnerProfile;
        DB::transaction(function () use ($values, $profile) {
            $this->ensureActiveCategory((int) $values['service_category_id']);
            PartnerService::create([
                ...$values,
                'partner_profile_id' => $profile->id, // selalu ditentukan server
            ]);
        });
        return redirect()->route('partner.db-services.index')->with('success', 'Layanan database berhasil ditambahkan.');
    }

    public function update(SavePartnerServiceRequest $request, PartnerService $partnerService): RedirectResponse
    {
        $profile = $request->user()->partnerProfile;
        $values = $request->validated();
        DB::transaction(function () use ($profile, $partnerService, $values) {
            $service = $this->owned($profile->id, $partnerService->id);
            $this->ensureActiveCategory((int) $values['service_category_id']);
            $service->update($values); // tidak menerima partner_profile_id dari request
        });
        return redirect()->route('partner.db-services.index')->with('success', 'Layanan database diperbarui.');
    }

    public function status(Request $request, PartnerService $partnerService): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $profile = $request->user()->partnerProfile;
        DB::transaction(function () use ($profile, $partnerService, $data) {
            $service = $this->owned($profile->id, $partnerService->id);
            if ((bool) $data['is_active']) $this->ensureActiveCategory($service->service_category_id);
            $service->update(['is_active' => (bool) $data['is_active']]);
        });
        return back()->with('success', 'Ketersediaan layanan database diperbarui.');
    }

    public function destroy(Request $request, PartnerService $partnerService): RedirectResponse
    {
        $profile = $request->user()->partnerProfile;
        DB::transaction(function () use ($profile, $partnerService) {
            $this->owned($profile->id, $partnerService->id)->delete(); // soft delete
        });
        return redirect()->route('partner.db-services.index')->with('success', 'Layanan diarsipkan dan tidak muncul di katalog.');
    }

    private function owned(int $profileId, int $serviceId): PartnerService
    {
        return PartnerService::where('partner_profile_id', $profileId)
            ->lockForUpdate()->findOrFail($serviceId);
    }

    private function ensureActiveCategory(int $categoryId): void
    {
        $category = ServiceCategory::query()->lockForUpdate()->find($categoryId);
        if (!$category || !$category->is_active) {
            throw ValidationException::withMessages([
                'service_category_id' => 'Kategori sedang tidak aktif atau tidak tersedia.',
            ]);
        }
    }
}
