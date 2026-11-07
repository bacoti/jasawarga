<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\PlatformNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = PlatformNotification::query()->where('user_id', $request->user()->id)->latest()->paginate(20);
        $unreadCount = PlatformNotification::query()->where('user_id', $request->user()->id)->unread()->count();
        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(Request $request, PlatformNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 404);
        $notification->forceFill(['read_at' => $notification->read_at ?? now()])->save();
        return $notification->url ? redirect($this->safeInternalUrl($notification->url)) : back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        PlatformNotification::query()->where('user_id', $request->user()->id)->unread()->update(['read_at' => now()]);
        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }

    private function safeInternalUrl(?string $url): string
    {
        if (! $url || ! str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return route('notifications.index');
        }
        return url($url);
    }
}
