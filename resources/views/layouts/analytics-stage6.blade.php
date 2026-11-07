<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard JasaWarga')</title>
    @include('components.font-imports')
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F4F7FB] text-[#172B45] antialiased">
@include('components.site-navbar')
<div class="border-b border-slate-200 bg-white">
  <nav class="mx-auto flex max-w-7xl items-center gap-2 overflow-x-auto px-4 py-2.5 text-xs sm:px-6" aria-label="Navigasi dashboard {{ auth()->user()?->role }}">
    @if(auth()->user()?->role === 'admin')
      <a href="{{ route('admin.analytics.database') }}" class="shrink-0 rounded-xl px-3 py-2 font-semibold hover:bg-slate-100">Overview Admin</a>
      <a href="{{ route('admin.analytics.reports') }}" class="shrink-0 rounded-xl px-3 py-2 font-semibold hover:bg-slate-100">Laporan</a>
      <a href="{{ route('admin.bookings.index') }}" class="shrink-0 rounded-xl px-3 py-2 font-semibold hover:bg-slate-100">Monitoring Pesanan</a>
    @elseif(auth()->user()?->role === 'partner')
      <a href="{{ route('partner.analytics.dashboard') }}" class="shrink-0 rounded-xl px-3 py-2 font-semibold hover:bg-slate-100">Overview Mitra</a>
      <a href="{{ route('partner.bookings.index') }}" class="shrink-0 rounded-xl px-3 py-2 font-semibold hover:bg-slate-100">Pesanan Masuk</a>
      <a href="{{ route('partner.schedule.index') }}" class="shrink-0 rounded-xl px-3 py-2 font-semibold hover:bg-slate-100">Jadwal</a>
    @else
      <a href="{{ route('account.analytics.dashboard') }}" class="shrink-0 rounded-xl px-3 py-2 font-semibold hover:bg-slate-100">Overview Warga</a>
      <a href="{{ route('account.bookings.index') }}" class="shrink-0 rounded-xl px-3 py-2 font-semibold hover:bg-slate-100">Pesanan Saya</a>
      <a href="{{ route('account.profile.show') }}" class="shrink-0 rounded-xl px-3 py-2 font-semibold hover:bg-slate-100">Akun Saya</a>
    @endif
    <a href="{{ route('notifications.index') }}" class="ml-auto shrink-0 rounded-xl border border-slate-200 px-3 py-2 font-semibold hover:bg-slate-100">Notifikasi</a>
    <form method="POST" action="{{ route('auth.logout') }}" class="shrink-0">@csrf<button class="rounded-xl bg-[#0C3065] px-3 py-2 font-semibold text-white">Keluar</button></form>
  </nav>
</div>
<main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-9">
    @include('components.auth-flash-toast')
    @if($errors->any())<div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>@endif
    @yield('content')
</main>
<footer class="border-t border-slate-200 bg-white py-5 text-center text-xs text-slate-500">JasaWarga · Dashboard internal. Status pekerjaan bukan bukti pelunasan pembayaran.</footer>
</body></html>
