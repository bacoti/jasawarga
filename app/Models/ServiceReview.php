<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceReview extends Model
{
    protected $fillable = [
        'citizen_id','partner_profile_id','partner_service_id','booking_reference',
        'verification_status','verified_at','rating','quality_rating','timeliness_rating',
        'friendliness_rating','comment','photo_path','moderation_status',
        'moderation_reason','moderated_by','moderated_at',
    ];

    protected function casts(): array
    {
        return ['verified_at'=>'datetime','moderated_at'=>'datetime',
            'rating'=>'integer','quality_rating'=>'integer',
            'timeliness_rating'=>'integer','friendliness_rating'=>'integer'];
    }

    public function citizen(): BelongsTo { return $this->belongsTo(User::class, 'citizen_id'); }
    public function partner(): BelongsTo { return $this->belongsTo(PartnerProfile::class, 'partner_profile_id'); }
    public function service(): BelongsTo { return $this->belongsTo(PartnerService::class, 'partner_service_id')->withTrashed(); }
    public function reply(): HasOne { return $this->hasOne(ServiceReviewReply::class); }
    public function moderationLogs(): HasMany { return $this->hasMany(ServiceReviewModerationLog::class); }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('verification_status', 'verified')
            ->where('moderation_status', 'visible')
            ->whereHas('partner', fn (Builder $q) => $q
                ->where('verification_status', 'approved')->where('reverification_required', false)
                ->whereHas('user', fn (Builder $users) => $users
                    ->where('account_status', 'active')->where('role', User::ROLE_PARTNER)));
    }
}
