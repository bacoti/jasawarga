<?php

namespace App\Services\Booking;

use App\Models\PartnerService;
use Illuminate\Validation\ValidationException;

/** Adapter to Stage 3 Eloquent catalog. Stage 3 currently supports fixed prices only. */
class ServiceCatalog
{
    public function activeForPartner(int $partnerProfileId)
    {
        return PartnerService::published()->where('partner_profile_id', $partnerProfileId)
            ->orderBy('name')->get()
            ->map(fn (PartnerService $service) => (object) [
                'id' => $service->id,
                'name' => $service->name,
                'price_type' => 'fixed',
                'price' => (float) $service->base_price,
                'duration_minutes' => $service->duration_minutes,
            ]);
    }

    public function findActiveForPartner(int $partnerProfileId, int $serviceId): array
    {
        $service = PartnerService::published()->where('partner_profile_id', $partnerProfileId)
            ->whereKey($serviceId)->first();
        if (! $service) {
            throw ValidationException::withMessages(['service_id' => 'Layanan atau kategorinya tidak tersedia untuk pemesanan.']);
        }
        if ($service->duration_minutes < 1 || $service->duration_minutes > 1440 || $service->base_price <= 0) {
            throw ValidationException::withMessages(['service_id' => 'Harga atau durasi layanan belum valid.']);
        }
        return [
            'id' => $service->id, 'name' => $service->name,
            'price_type' => 'fixed', 'price' => (float) $service->base_price,
            'duration_minutes' => $service->duration_minutes,
        ];
    }
}
