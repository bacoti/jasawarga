<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReviewReply extends Model
{
    protected $fillable = ['service_review_id','partner_profile_id','body','edited_at'];
    protected function casts(): array { return ['edited_at'=>'datetime']; }
    public function review(): BelongsTo { return $this->belongsTo(ServiceReview::class, 'service_review_id'); }
    public function partner(): BelongsTo { return $this->belongsTo(PartnerProfile::class, 'partner_profile_id'); }
}
