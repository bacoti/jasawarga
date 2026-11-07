<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusHistory extends Model
{
    public $timestamps = false;
    protected $fillable = ['booking_id', 'actor_user_id', 'from_status', 'to_status', 'note', 'created_at'];
    protected function casts(): array { return ['created_at' => 'datetime']; }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_user_id'); }
}
