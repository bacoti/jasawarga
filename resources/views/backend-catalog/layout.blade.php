@extends('layouts.app')

@section('content')
@php
    $role = auth()->user()?->role;
    $links = $role === 'admin' ? [
        ['route' => 'admin.dashboard', 'icon' => 'space_dashboard', 'label' => 'Dashboard Admin'],
        ['route' => 'admin.db-categories.index', 'icon' => 'category', 'label' => 'Kategori · Database'],
        ['route' => 'admin.categories', 'icon' => 'inventory_2', 'label' => 'Kategori · Demo'],
        ['route' => 'admin.partner-applications', 'icon' => 'verified_user', 'label' => 'Verifikasi Mitra'],
    ] : ($role === 'partner' ? [
        ['route' => 'partner.portal', 'icon' => 'storefront', 'label' => 'Portal Mitra'],
        ['route' => 'partner.db-services.index', 'icon' => 'design_services', 'label' => 'Layanan · Database'],
        ['route' => 'db-catalog.index', 'icon' => 'travel_explore', 'label' => 'Katalog Publik'],
    ] : [
        ['route' => 'home', 'icon' => 'home', 'label' => 'Beranda'],
        ['route' => 'db-catalog.index', 'icon' => 'search', 'label' => 'Katalog Database'],
        ['route' => 'categories.index', 'icon' => 'grid_view', 'label' => 'Katalog Demo'],
    ]);
@endphp
<div class="min-h-screen bg-[#F4F7FB] text-[#192c40] lg:flex">
    <aside class="hidden w-[250px] shrink-0 flex-col bg-[#142b42] text-white lg:sticky lg:top-0 lg:flex lg:h-screen" aria-label="Menu modul katalog PostgreSQL">
        <a href="{{ route('home') }}" class="flex items-center gap-3 border-b border-white/10 px-6 py-7">
            <img src="{{ asset('assets/brand/jasawarga-icon-256.png') }}" alt="Logo JasaWarga" class="h-11 w-11 rounded-xl object-cover">
            <span><strong class="block text-xl tracking-tight">JasaWarga</strong><small class="block pt-1 text-[10px] font-bold uppercase tracking-widest text-blue-200">Katalog PostgreSQL</small></span>
        </a>
        <nav class="space-y-1 px-3 py-6" aria-label="Navigasi utama">
            @foreach($links as $link)
                <a href="{{ route($link['route']) }}" @if(request()->routeIs($link['route'])) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-4 py-3 text-[13px] font-semibold {{ request()->routeIs($link['route']) ? 'bg-[#28577b] text-white' : 'text-[#c1d4e3] hover:bg-white/10 hover:text-white' }}">
                    <span class="material-symbols-outlined text-[21px]">{{ $link['icon'] }}</span>{{ $link['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="mt-auto border-t border-white/10 p-5 text-xs leading-relaxed text-[#bdd1e1]">Backend Tahap 3<br><span class="text-white">Data kategori dan layanan di PostgreSQL.</span></div>
    </aside>
    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-30 border-b border-[#dbe5ed] bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-7 lg:h-[76px] lg:px-9 lg:py-0">
                <a href="{{ route('home') }}" class="text-lg font-extrabold tracking-tight text-[#006098] lg:hidden">JasaWarga<span class="ml-1 text-[10px] font-bold text-[#68849b]">DB</span></a>
                <p class="hidden text-xs font-bold uppercase tracking-[.13em] text-[#64798a] lg:block">{{ $role === 'admin' ? 'Ruang Admin' : ($role === 'partner' ? 'Ruang Mitra' : 'Katalog Warga') }} / Layanan Jasa</p>
                <div class="flex items-center gap-3">
                    <span class="hidden rounded-full bg-[#e7f2fa] px-3 py-1.5 text-[11px] font-bold text-[#005d94] sm:inline">POSTGRESQL</span>
                    @auth
                    <span class="max-w-[140px] truncate text-xs font-semibold text-[#4e657b]">{{ auth()->user()->name }}</span>
                    <form action="{{ route('auth.logout') }}" method="POST">@csrf<button type="submit" class="rounded-lg border border-[#d8e3ed] px-3 py-2 text-xs font-bold text-[#314c65] hover:bg-[#f3f7fb]">Keluar</button></form>
                    @else
                    <a href="{{ route('account.login') }}" class="rounded-lg bg-[#006098] px-3 py-2 text-xs font-bold text-white">Masuk</a>
                    @endauth
                </div>
            </div>
            <nav class="flex gap-1 overflow-x-auto border-t border-[#e6edf3] px-3 py-2 lg:hidden" aria-label="Menu mobile katalog">
                @foreach($links as $link)<a href="{{ route($link['route']) }}" class="shrink-0 rounded-lg px-3 py-2 text-xs font-bold {{ request()->routeIs($link['route']) ? 'bg-[#e6f3fa] text-[#006098]' : 'text-[#516a7f]' }}">{{ $link['label'] }}</a>@endforeach
            </nav>
        </header>
        <main class="mx-auto max-w-7xl px-4 pb-20 pt-7 sm:px-7 lg:px-10 lg:pb-16 lg:pt-10">
            @if(session('success'))
                <p role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
            @endif
            @if($errors->any())
                <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <strong>Periksa kembali input:</strong><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('backend_catalog_content')
        </main>
    </div>
</div>
@endsection
