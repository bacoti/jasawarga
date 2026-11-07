<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard JasaWarga')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F4F7FB] text-[#172B45] antialiased">
<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <a href="{{ auth()->user()->role === 'admin' ? route('admin.analytics.dashboard') : (auth()->user()->role === 'partner' ? route('partner.analytics.dashboard') : route('account.analytics.dashboard')) }}" class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-[#16324F] text-sm font-black text-white">JW</span>
            <span><strong class="block text-sm tracking-tight">JasaWarga</strong><small class="block text-[10px] text-slate-500">Dashboard {{ ucfirst(auth()->user()->role) }}</small></span>
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('notifications.index') }}" class="relative rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold hover:bg-slate-50" aria-label="Notifikasi">Notifikasi
                @php($unread = \Illuminate\Support\Facades\Schema::hasTable('jw_platform_notifications') ? \App\Models\PlatformNotification::where('user_id', auth()->id())->unread()->count() : 0)
                @if($unread > 0)<span class="ml-1 rounded-full bg-amber-500 px-1.5 py-0.5 text-[10px] text-white">{{ $unread }}</span>@endif
            </a>
            <span class="hidden text-right sm:block"><strong class="block text-xs">{{ auth()->user()->name }}</strong><small class="text-[10px] capitalize text-slate-500">{{ auth()->user()->role }}</small></span>
            <form method="POST" action="{{ route('auth.logout') }}">@csrf<button class="rounded-xl bg-[#16324F] px-3 py-2 text-xs font-bold text-white">Keluar</button></form>
        </div>
    </div>
    <nav class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 pb-3 text-xs sm:px-6" aria-label="Navigasi dashboard">
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.analytics.dashboard') }}" class="whitespace-nowrap rounded-full px-3 py-2 font-semibold hover:bg-slate-100">Overview</a>
            <a href="{{ route('admin.analytics.reports') }}" class="whitespace-nowrap rounded-full px-3 py-2 font-semibold hover:bg-slate-100">Laporan</a>
            <a href="{{ route('admin.bookings.index') }}" class="whitespace-nowrap rounded-full px-3 py-2 font-semibold hover:bg-slate-100">Monitoring Pesanan</a>
        @elseif(auth()->user()->role === 'partner')
            <a href="{{ route('partner.analytics.dashboard') }}" class="whitespace-nowrap rounded-full px-3 py-2 font-semibold hover:bg-slate-100">Overview</a>
            <a href="{{ url('/mitra/workspace/pesanan') }}" class="whitespace-nowrap rounded-full px-3 py-2 font-semibold hover:bg-slate-100">Pesanan Masuk</a>
            <a href="{{ url('/mitra/workspace/jadwal') }}" class="whitespace-nowrap rounded-full px-3 py-2 font-semibold hover:bg-slate-100">Jadwal</a>
        @else
            <a href="{{ route('account.analytics.dashboard') }}" class="whitespace-nowrap rounded-full px-3 py-2 font-semibold hover:bg-slate-100">Overview</a>
            <a href="{{ url('/akun/pesanan') }}" class="whitespace-nowrap rounded-full px-3 py-2 font-semibold hover:bg-slate-100">Pesanan Saya</a>
            <a href="{{ route('account.profile.show') }}" class="whitespace-nowrap rounded-full px-3 py-2 font-semibold hover:bg-slate-100">Profil Saya</a>
        @endif
    </nav>
</header>
<main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-9">
    @if(session('success'))<div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>@endif
    @yield('content')
</main>
<footer class="border-t border-slate-200 bg-white py-5 text-center text-xs text-slate-500">JasaWarga · Dashboard internal. Status pekerjaan bukan bukti pelunasan pembayaran.</footer>
</body></html>
