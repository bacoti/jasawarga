<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerVerificationLog extends Model
{
    protected $fillable = ['partner_profile_id', 'admin_id', 'from_status', 'to_status', 'reason'];

    public function partnerProfile(): BelongsTo { return $this->belongsTo(PartnerProfile::class); }
    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
}
