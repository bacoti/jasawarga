<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerScheduleBlock extends Model
{
    protected $fillable = ['partner_profile_id', 'starts_at', 'ends_at', 'reason'];
    protected function casts(): array { return ['starts_at' => 'datetime', 'ends_at' => 'datetime']; }
    public function partnerProfile(): BelongsTo { return $this->belongsTo(PartnerProfile::class); }
}
