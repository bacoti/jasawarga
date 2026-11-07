<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerPortfolio extends Model
{
    protected $fillable = ['partner_profile_id', 'title', 'description', 'image_path', 'completed_at', 'sort_order'];
    protected function casts(): array { return ['completed_at' => 'date']; }
    public function partnerProfile(): BelongsTo { return $this->belongsTo(PartnerProfile::class); }
}
