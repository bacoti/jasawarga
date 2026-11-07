@extends('layouts.app')
@section('content')
@php
    $role = auth()->user()?->role;
    $links = [
        ['route'=>'db-feedback.reviews.public','label'=>'Ulasan publik','icon'=>'star'],
    ];
    if ($role === 'citizen') {
        $links = array_merge($links, [
            ['route'=>'db-feedback.reviews.create','label'=>'Tulis ulasan','icon'=>'rate_review'],
            ['route'=>'db-feedback.reviews.mine','label'=>'Ulasan saya','icon'=>'reviews'],
            ['route'=>'db-feedback.complaints.mine','label'=>'Pengaduan saya','icon'=>'support_agent'],
        ]);
    }
    if ($role === 'partner') $links[] = ['route'=>'partner.db-feedback.reviews.index','label'=>'Balasan Mitra','icon'=>'forum'];
    if ($role === 'admin') {
        $links[] = ['route'=>'admin.db-feedback.reviews.index','label'=>'Moderasi ulasan','icon'=>'verified_user'];
        $links[] = ['route'=>'admin.db-feedback.complaints.index','label'=>'Penanganan aduan','icon'=>'assignment_turned_in'];
    }
@endphp
<div class="min-h-screen bg-[#f3f6f9] text-[#23354a] lg:flex">
    <aside class="hidden w-[252px] shrink-0 flex-col bg-[#142e44] text-white lg:sticky lg:top-0 lg:flex lg:h-screen">
        <a href="{{ route('home') }}" class="flex items-center gap-3 border-b border-white/10 px-6 py-7">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#1685b4]"><span class="material-symbols-outlined">handyman</span></span>
            <span><strong class="block text-xl">JasaWarga</strong><small class="text-[11px] text-blue-200">Feedback PostgreSQL</small></span>
        </a>
        <nav class="space-y-1 px-3 py-6" aria-label="Navigasi modul ulasan dan pengaduan">
            @foreach($links as $link)
                <a href="{{ route($link['route']) }}" @if(request()->routeIs($link['route'])) aria-current="page" @endif class="flex items-center gap-3 rounded-lg px-4 py-3 text-[13px] font-semibold {{ request()->routeIs($link['route']) ? 'bg-[#275675] text-white' : 'text-[#c2d6e5] hover:bg-white/10 hover:text-white' }}"><span class="material-symbols-outlined text-[20px]">{{ $link['icon'] }}</span>{{ $link['label'] }}</a>
            @endforeach
            <a class="mt-4 flex items-center gap-3 rounded-lg px-4 py-3 text-[13px] text-[#bed5e6] hover:bg-white/10" href="{{ route('db-catalog.index') }}"><span class="material-symbols-outlined text-[20px]">inventory_2</span>Katalog database</a>
        </nav>
        <div class="mt-auto border-t border-white/10 p-5 text-xs leading-5 text-[#b7cede]">Backend Tahap 5<br>Ulasan dan pengaduan tersimpan di PostgreSQL. Tidak bercampur dengan data demo browser.</div>
    </aside>
    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-30 border-b border-[#d9e4ed] bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-2 px-4 py-3 sm:px-7 lg:h-[72px] lg:px-9">
                <a class="font-extrabold tracking-tight text-[#006098] lg:hidden" href="{{ route('home') }}">JasaWarga <span class="text-xs text-[#597d99]">DB</span></a>
                <div class="hidden text-xs font-bold uppercase tracking-[.14em] text-[#617e94] lg:block">Ulasan & Pengaduan / PostgreSQL</div>
                <div class="flex items-center gap-3 text-xs"><span class="hidden rounded-full bg-[#e9f3f8] px-3 py-1.5 font-bold text-[#086c9d] sm:inline">DATA DATABASE</span>
                    @auth <span class="max-w-[120px] truncate font-bold">{{ auth()->user()->name }}</span><form action="{{ route('auth.logout') }}" method="POST">@csrf<button class="rounded-lg border border-[#d4e2eb] px-3 py-2 font-bold" type="submit">Keluar</button></form>
                    @else <a class="rounded-lg bg-[#006098] px-3 py-2 font-bold text-white" href="{{ route('account.login') }}">Masuk</a> @endauth
                </div>
            </div>
            <nav class="flex gap-1 overflow-x-auto border-t border-[#e8edf1] px-3 py-2 lg:hidden" aria-label="Navigasi mobile feedback">
                @foreach($links as $link)<a class="shrink-0 rounded-lg px-3 py-2 text-xs font-bold {{ request()->routeIs($link['route']) ? 'bg-[#e4f1f8] text-[#006098]' : 'text-[#566e81]' }}" href="{{ route($link['route']) }}">{{ $link['label'] }}</a>@endforeach
            </nav>
        </header>
        <main class="mx-auto max-w-7xl px-4 pb-24 pt-8 sm:px-7 lg:px-10 lg:pt-10">
            @if($errors->any())<div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-800"><strong>Input belum sesuai:</strong><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('feedback_content')
        </main>
    </div>
</div>
@endsection
