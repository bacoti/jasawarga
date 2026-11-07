<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceComplaint extends Model
{
    protected $fillable = [
        'reporter_id','partner_profile_id','partner_service_id','service_review_id',
        'booking_reference','subject','description','priority','status',
        'resolution_note','handled_by','closed_at',
    ];
    protected function casts(): array { return ['closed_at'=>'datetime']; }
    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reporter_id'); }
    public function partner(): BelongsTo { return $this->belongsTo(PartnerProfile::class, 'partner_profile_id'); }
    public function service(): BelongsTo { return $this->belongsTo(PartnerService::class, 'partner_service_id')->withTrashed(); }
    public function review(): BelongsTo { return $this->belongsTo(ServiceReview::class, 'service_review_id'); }
    public function handler(): BelongsTo { return $this->belongsTo(User::class, 'handled_by'); }
    public function actions(): HasMany { return $this->hasMany(ServiceComplaintAction::class); }
}
