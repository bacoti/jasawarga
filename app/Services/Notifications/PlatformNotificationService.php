<?php

namespace App\Services\Notifications;

use App\Models\PlatformNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlatformNotificationService
{
    public function notify(User $user, string $type, string $title, string $message, ?string $url = null, array $data = [], string $preferenceKey = 'order_updates'): ?PlatformNotification
    {
        if (! config('jasawarga-analytics.notifications.enabled', true) || ! Schema::hasTable('jw_platform_notifications')) {
            return null;
        }

        if (config('jasawarga-analytics.notifications.respect_user_preferences', true)
            && $user->role !== 'admin'
            && ! $this->preferenceEnabled((int) $user->id, $preferenceKey)) {
            return null;
        }

        return PlatformNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'data' => $data,
        ]);
    }

    public function notifyAdmins(string $type, string $title, string $message, ?string $url = null, array $data = []): int
    {
        if (! config('jasawarga-analytics.notifications.admin_new_booking', true)
            || ! Schema::hasTable('users')
            || ! Schema::hasTable('jw_platform_notifications')) {
            return 0;
        }

        $count = 0;
        User::query()->where('role', 'admin')->select('id', 'role')->chunkById(100, function ($admins) use ($type, $title, $message, $url, $data, &$count): void {
            foreach ($admins as $admin) {
                $this->notify($admin, $type, $title, $message, $url, $data);
                $count++;
            }
        });

        return $count;
    }

    private function preferenceEnabled(int $userId, string $key): bool
    {
        if (! Schema::hasTable('user_preferences')) {
            return true;
        }

        $raw = DB::table('user_preferences')->where('user_id', $userId)->value('notifications');
        if ($raw === null) {
            return true;
        }

        $preferences = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (! is_array($preferences)) {
            return true;
        }

        return (bool) ($preferences[$key] ?? true);
    }
}
