<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    public const PENDING_PARTNER = 'pending_partner_confirmation';
    public const INSPECTION_SCHEDULED = 'inspection_scheduled';
    public const PENDING_PRICE_APPROVAL = 'pending_price_approval';
    public const CONFIRMED = 'confirmed';
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETION_PENDING = 'completion_pending';
    public const COMPLETED = 'completed';
    public const INSPECTION_NO_REPAIR = 'inspection_completed_no_repair';
    public const REJECTED = 'rejected';
    public const EXPIRED = 'expired';
    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'booking_code', 'user_id', 'partner_profile_id', 'service_id',
        'service_name_snapshot', 'price_type_snapshot', 'initial_price_snapshot',
        'agreed_price', 'duration_minutes', 'scheduled_at', 'user_address_id',
        'address_snapshot', 'notes', 'status', 'partner_response_deadline',
        'partner_accepted_at', 'partner_rejected_at', 'expired_at', 'confirmed_at',
        'started_at', 'completion_requested_at', 'completed_at', 'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'initial_price_snapshot' => 'decimal:2',
            'agreed_price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'scheduled_at' => 'datetime',
            'address_snapshot' => 'array',
            'partner_response_deadline' => 'datetime',
            'partner_accepted_at' => 'datetime',
            'partner_rejected_at' => 'datetime',
            'expired_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'started_at' => 'datetime',
            'completion_requested_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function warga(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function partnerProfile(): BelongsTo { return $this->belongsTo(PartnerProfile::class); }
    public function offers(): HasMany { return $this->hasMany(BookingOffer::class)->latest(); }
    public function statusHistories(): HasMany { return $this->hasMany(BookingStatusHistory::class)->latest(); }

    public function endAt(): \Illuminate\Support\Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    public function isActiveForSlot(): bool
    {
        return in_array($this->status, config('jasawarga-booking.active_booking_statuses', []), true);
    }
}
