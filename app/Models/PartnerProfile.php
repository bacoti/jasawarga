<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Unified Stage 1 auth/verification + Stage 2 public profile + Stage 3 catalog + Stage 4 bookings. */
class PartnerProfile extends Model
{
    protected $fillable = [
        'user_id', 'business_name', 'business_phone', 'service_area', 'description',
        'verification_status', 'verified_at', 'verified_by', 'decision_reason', 'demo_provider_id',
        'slug', 'category_name', 'specialization', 'experience_years', 'phone', 'whatsapp',
        'city', 'address_summary', 'service_areas', 'profile_photo_path', 'cover_photo_path',
        'reverification_required',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime', 'demo_provider_id' => 'integer',
            'experience_years' => 'integer', 'service_areas' => 'array',
            'reverification_required' => 'boolean',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function verificationLogs(): HasMany { return $this->hasMany(PartnerVerificationLog::class); }
    public function portfolios(): HasMany { return $this->hasMany(PartnerPortfolio::class)->orderBy('sort_order')->orderByDesc('created_at'); }
    public function services(): HasMany { return $this->hasMany(PartnerService::class); }
    public function bookings(): HasMany { return $this->hasMany(Booking::class); }

    public function isPubliclyVisible(): bool
    {
        return $this->verification_status === 'approved'
            && ! $this->reverification_required
            && $this->user !== null
            && $this->user->role === User::ROLE_PARTNER
            && $this->user->account_status === 'active';
    }
}
