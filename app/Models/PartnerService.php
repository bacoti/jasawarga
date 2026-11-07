<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'partner_profile_id', 'service_category_id', 'name', 'description',
        'base_price', 'duration_minutes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer', 'duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(PartnerProfile::class, 'partner_profile_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_active', true)
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->whereHas('partner', fn ($q) => $q->where('verification_status', 'approved')
                ->where('reverification_required', false))
            ->whereHas('partner.user', fn ($q) => $q->where('role', User::ROLE_PARTNER)
                ->where('account_status', 'active'));
    }
}
