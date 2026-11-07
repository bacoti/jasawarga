<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PlatformNotification extends Model
{
    protected $table = 'jw_platform_notifications';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['user_id', 'type', 'title', 'message', 'url', 'data', 'read_at'];

    protected function casts(): array
    {
        return ['data' => 'array', 'read_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $notification): void {
            $notification->id ??= (string) Str::uuid();
        });
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }
}
