<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = ['user_id', 'phone', 'birth_date', 'gender', 'avatar_path', 'bio'];
    protected function casts(): array { return ['birth_date' => 'date']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
