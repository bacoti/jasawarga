<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingOffer extends Model
{
    protected $fillable = [
        'booking_id', 'created_by_user_id', 'amount', 'note', 'status',
        'responded_at',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'responded_at' => 'datetime'];
    }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
