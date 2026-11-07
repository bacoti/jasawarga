<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReviewModerationLog extends Model
{
    protected $fillable = ['service_review_id','admin_id','from_status','to_status','reason'];
    public function review(): BelongsTo { return $this->belongsTo(ServiceReview::class, 'service_review_id'); }
    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
}
