<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_CITIZEN = 'citizen';
    public const ROLE_PARTNER = 'partner';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = ['name', 'email', 'phone', 'role', 'account_status', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function userProfile(): HasOne { return $this->hasOne(UserProfile::class); }
    public function addresses(): HasMany { return $this->hasMany(UserAddress::class); }
    public function preference(): HasOne { return $this->hasOne(UserPreference::class); }
    public function bookings(): HasMany { return $this->hasMany(Booking::class, 'user_id'); }

    public function partnerProfile(): HasOne
    {
        return $this->hasOne(PartnerProfile::class);
    }
}
