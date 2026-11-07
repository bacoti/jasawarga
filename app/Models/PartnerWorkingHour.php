<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerWorkingHour extends Model
{
    protected $fillable = ['partner_profile_id', 'day_of_week', 'start_time', 'end_time', 'is_active'];
    protected function casts(): array { return ['day_of_week' => 'integer', 'is_active' => 'boolean']; }
    public function partnerProfile(): BelongsTo { return $this->belongsTo(PartnerProfile::class); }
}
