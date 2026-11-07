@auth
@php($unreadNotifications = \Illuminate\Support\Facades\Schema::hasTable('jw_platform_notifications') ? \App\Models\PlatformNotification::where('user_id', auth()->id())->unread()->count() : 0)
<a href="{{ route('notifications.index') }}" class="relative inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-[#16324F]" aria-label="Notifikasi">Notifikasi @if($unreadNotifications)<span class="rounded-full bg-amber-500 px-1.5 py-0.5 text-[10px] text-white">{{ $unreadNotifications }}</span>@endif</a>
@endauth
