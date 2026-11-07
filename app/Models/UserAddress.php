<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAddress extends Model
{
    protected $fillable = ['user_id', 'label', 'recipient_name', 'phone', 'province', 'city', 'district', 'subdistrict', 'postal_code', 'address_detail', 'latitude', 'longitude', 'is_primary'];
    protected function casts(): array { return ['is_primary' => 'boolean', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
