<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceComplaintAction extends Model
{
    protected $fillable = [
        'service_complaint_id','admin_id','from_status','to_status',
        'from_priority','to_priority','note',
    ];
    public function complaint(): BelongsTo { return $this->belongsTo(ServiceComplaint::class, 'service_complaint_id'); }
    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
}
