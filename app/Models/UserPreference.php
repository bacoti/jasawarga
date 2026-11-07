<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $fillable = ['user_id', 'notifications', 'privacy', 'operational', 'timezone'];
    protected function casts(): array { return ['notifications' => 'array', 'privacy' => 'array', 'operational' => 'array']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
