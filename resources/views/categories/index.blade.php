@extends('layouts.app')

@section('title', 'Kategori Jasa & Mitra | JasaWarga')

@php
    $categories = config('jasawarga.categories', []);
    $providers = config('jasawarga.providers', []);
    $counts = array_count_values(array_column($providers, 'category'));
@endphp

@section('content')
<div id="jw-catalog" class="min-h-screen bg-surface text-on-surface">
    <script type="application/json" data-catalog-categories-seed>@json($categories)</script>
    {{-- Header desktop: ritme visual mengikuti Home JasaWarga. --}}
    @include('components.site-navbar')
    {{-- Header mobile. --}}
    <main class="mx-auto w-full max-w-7xl px-4 pb-28 pt-5 sm:px-6 lg:px-8 lg:pb-12 lg:pt-8">
        <nav class="mb-4 hidden items-center gap-2 text-xs text-outline lg:flex" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a><span class="material-symbols-outlined text-[15px]">chevron_right</span><span class="font-semibold text-primary">Kategori Jasa</span>
        </nav>

        {{-- Judul ringkas dan berorientasi tindakan, bukan hero besar. --}}
        <section class="mb-6 overflow-hidden rounded-2xl border border-secondary-container/50 bg-gradient-to-br from-primary-fixed via-surface-container-low to-white p-5 sm:p-7 lg:mb-8 lg:p-8">
            <div class="flex flex-col justify-between gap-5 md:flex-row md:items-end">
                <div class="max-w-[670px]">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/85 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-primary"><span class="material-symbols-outlined text-[15px]">handshake</span> Layanan gotong royong</span>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-on-surface sm:text-3xl lg:text-[34px] lg:leading-tight">Temukan jasa yang tepat,<br class="hidden sm:block"> dekat dari rumah.</h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-on-surface-variant">Jelajahi jenis layanan, bandingkan penyedia jasa, dan pilih sesuai kebutuhan Anda.</p>
                </div>
                <div class="flex shrink-0 items-center gap-3 rounded-xl border border-outline-variant/30 bg-white/85 p-3 sm:px-5 sm:py-4">
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary-fixed text-primary"><span class="material-symbols-outlined text-[26px]">groups</span></span>
                    <div><p class="text-xl font-extrabold leading-none text-primary">{{ count($providers) }}</p><p class="mt-1 text-xs text-on-surface-variant">Mitra contoh ditampilkan</p></div>
                </div>
            </div>
        </section>

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900"><span><strong>Katalog layanan database sudah tersedia.</strong> Lihat layanan dari Mitra terverifikasi yang telah diterbitkan di PostgreSQL.</span><a class="shrink-0 rounded-lg bg-[#006098] px-4 py-2 text-xs font-bold text-white" href="{{ route('db-catalog.index') }}">Buka Katalog Database →</a></div>
        {{-- Satu search bar untuk seluruh ukuran layar. --}}
        <form id="jw-catalog-search-form" class="mb-7 flex items-center gap-2" role="search">
            <label for="jw-catalog-search" class="sr-only">Cari layanan atau mitra</label>
            <div class="relative min-w-0 flex-1">
                <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[22px] text-outline">search</span>
                <input id="jw-catalog-search" type="search" autocomplete="off" data-catalog-search placeholder="Cari jasa, nama mitra, atau kecamatan..." class="h-12 w-full rounded-xl border border-outline-variant/60 bg-white pl-11 pr-4 text-sm text-on-surface shadow-sm placeholder:text-outline focus:border-primary focus:ring-primary">
            </div>
            <button type="submit" class="h-12 rounded-xl bg-primary px-4 text-sm font-bold text-white transition hover:bg-primary-container sm:px-6">Cari</button>
        </form>

        {{-- Grid kategori seperti Home, dengan pilihan aktif. --}}
        <section class="mb-8" aria-labelledby="jw-category-title">
            <div class="mb-3 flex items-end justify-between gap-3">
                <div>
                    <h2 id="jw-category-title" class="text-lg font-extrabold tracking-tight">Pilih kategori layanan</h2>
                    <p class="mt-1 text-xs text-on-surface-variant sm:text-sm">Mulai dari jenis pekerjaan yang Anda butuhkan.</p>
                </div>
                <button type="button" data-catalog-category="all" class="shrink-0 text-xs font-bold text-primary hover:underline">Lihat semua</button>
            </div>
            <div data-catalog-category-grid class="grid grid-cols-4 gap-2.5 sm:grid-cols-4 sm:gap-3 lg:grid-cols-8">
                @foreach($categories as $category)
                    <button type="button" data-catalog-category="{{ $category['slug'] }}" aria-pressed="false" class="group flex min-w-0 flex-col items-center gap-2 rounded-2xl border border-outline-variant/40 bg-white px-1.5 py-4 text-center shadow-sm transition hover:border-primary/60 hover:shadow-md sm:px-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl transition group-hover:scale-105 sm:h-14 sm:w-14" style="background-color: {{ $category['bg'] }}; color: {{ $category['fg'] }};"><span class="material-symbols-outlined text-[27px]">{{ $category['icon'] }}</span></span>
                        <span class="text-[11px] font-bold leading-tight text-on-surface sm:text-xs">{{ $category['name'] }}</span>
                        <span class="hidden text-[10px] text-outline lg:block">{{ $counts[$category['slug']] ?? 0 }} mitra</span>
                    </button>
                @endforeach
                <button type="button" data-catalog-category="all" aria-pressed="true" class="group flex min-w-0 flex-col items-center gap-2 rounded-2xl border border-outline-variant/40 bg-white px-1.5 py-4 text-center shadow-sm transition hover:border-primary/60 hover:shadow-md sm:px-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-surface-container-highest text-on-surface transition group-hover:scale-105 sm:h-14 sm:w-14"><span class="material-symbols-outlined text-[27px]">grid_view</span></span>
                    <span class="text-[11px] font-bold leading-tight sm:text-xs">Semua Jasa</span>
                    <span class="hidden text-[10px] text-outline lg:block">{{ count($providers) }} mitra</span>
                </button>
            </div>
        </section>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[258px_minmax(0,1fr)]">
            <aside class="sticky top-[100px] hidden rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm lg:block" aria-label="Filter daftar mitra">
                <div class="mb-5 flex items-center gap-2 border-b border-outline-variant/40 pb-4"><span class="material-symbols-outlined text-primary">tune</span><h2 class="text-base font-extrabold">Saring pencarian</h2></div>
                @include('categories.partials.filters', ['prefix' => 'desktop'])
            </aside>

            <section aria-labelledby="jw-results-title" id="hasil-mitra" class="min-w-0 scroll-mt-28">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="jw-results-title" class="text-lg font-extrabold tracking-tight sm:text-xl">Mitra di sekitar Anda</h2>
                        <p class="mt-1 text-xs text-on-surface-variant"><span data-catalog-count aria-live="polite">{{ count($providers) }}</span> mitra ditemukan <span class="text-outline">· Data simulasi</span></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" data-catalog-open-filter class="inline-flex items-center gap-1.5 rounded-xl border border-outline-variant/60 bg-white px-3 py-2.5 text-xs font-semibold text-on-surface-variant lg:hidden"><span class="material-symbols-outlined text-[19px]">tune</span>Filter</button>
                        <label for="jw-catalog-sort" class="sr-only">Urutkan mitra</label>
                        <select id="jw-catalog-sort" data-catalog-sort class="max-w-[170px] rounded-xl border-outline-variant/60 bg-white py-2.5 text-xs font-semibold text-on-surface-variant focus:border-primary focus:ring-primary sm:max-w-none sm:text-sm">
                            <option value="relevant">Paling relevan</option>
                            <option value="nearest">Jarak terdekat</option>
                            <option value="rating">Rating tertinggi</option>
                            <option value="price">Harga terendah</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4 hidden items-center justify-between gap-3 rounded-xl border border-primary/20 bg-primary-fixed/40 px-4 py-2.5 text-xs text-on-surface-variant" data-catalog-summary>
                    <span data-catalog-summary-text></span>
                    <button type="button" data-catalog-reset class="shrink-0 font-bold text-primary hover:underline">Hapus filter</button>
                </div>

                <div class="space-y-3" data-catalog-results>
                    @foreach($providers as $index => $provider)
                        @include('categories.partials.provider-card', ['provider' => $provider, 'index' => $index])
                    @endforeach
                </div>
                <div class="hidden rounded-2xl border border-dashed border-outline-variant bg-white px-6 py-12 text-center" data-catalog-empty>
                    <span class="material-symbols-outlined text-5xl text-outline">search_off</span>
                    <h3 class="mt-2 font-extrabold">Tidak ada mitra yang cocok</h3>
                    <p class="mx-auto mt-1 max-w-sm text-sm text-on-surface-variant">Coba kata kunci lain, pilih kategori berbeda, atau atur ulang filter.</p>
                    <button type="button" data-catalog-reset class="mt-4 rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white">Tampilkan semua mitra</button>
                </div>
                <p class="mt-5 text-xs leading-relaxed text-outline">Seluruh nama, jarak, ulasan, status verifikasi, tarif, dan ketersediaan pada halaman ini merupakan <strong>data dummy</strong> untuk demonstrasi antarmuka.</p>
            </section>
        </div>
    </main>

    <footer class="hidden border-t border-outline-variant/30 bg-white lg:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-8 py-7 text-xs text-outline">
            <div><span class="font-extrabold text-primary">JasaWarga</span> · Layanan warga Bandung</div>
            <div>Prototipe frontend · Informasi mitra masih berupa data contoh</div>
            <a href="{{ route('home') }}" class="font-semibold text-primary hover:underline">Kembali ke beranda</a>
        </div>
    </footer>

    {{-- Navigasi mobile mengikuti pola Home. --}}
    @include('home.mobile.bottom-nav')

    {{-- Filter untuk mobile: memakai <dialog> agar fokus dan escape dikelola browser. --}}
    <dialog id="jw-catalog-filter-dialog" aria-labelledby="jw-catalog-filter-title" class="w-[min(94vw,470px)] max-h-[85dvh] rounded-2xl border border-outline-variant/40 bg-white p-0 text-on-surface shadow-2xl backdrop:bg-on-surface/50">
        <div class="sticky top-0 flex items-center justify-between border-b border-outline-variant/40 bg-white px-5 py-4">
            <h2 id="jw-catalog-filter-title" class="text-base font-extrabold">Filter jasa</h2>
            <button type="button" data-catalog-close-filter aria-label="Tutup filter" class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container-low"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="max-h-[calc(85dvh-140px)] overflow-y-auto px-5 py-5">
            @include('categories.partials.filters', ['prefix' => 'mobile'])
        </div>
        <div class="sticky bottom-0 border-t border-outline-variant/40 bg-white px-5 py-4">
            <button type="button" data-catalog-close-filter class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white">Lihat hasil pencarian</button>
        </div>
    </dialog>

    <dialog id="jw-catalog-info-dialog" aria-labelledby="jw-catalog-info-title" class="w-[min(92vw,460px)] rounded-2xl border border-outline-variant/40 bg-white p-0 text-on-surface shadow-2xl backdrop:bg-on-surface/50">
        <div class="flex items-center justify-between border-b border-outline-variant/40 px-5 py-4">
            <h2 id="jw-catalog-info-title" class="text-base font-extrabold">Informasi mitra</h2>
            <button type="button" data-catalog-close-info aria-label="Tutup informasi" class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container-low"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="space-y-3 px-5 py-5 text-sm leading-relaxed text-on-surface-variant" id="jw-catalog-info-body"></div>
        <div class="px-5 pb-5"><button type="button" data-catalog-close-info class="w-full rounded-xl bg-primary px-4 py-2.5 font-bold text-white">Mengerti</button></div>
    </dialog>
    <div id="jw-catalog-toast" role="status" aria-live="polite" class="pointer-events-none fixed bottom-24 left-1/2 z-[70] hidden w-[min(92vw,430px)] -translate-x-1/2 rounded-xl bg-inverse-surface px-4 py-3 text-center text-sm font-medium text-white shadow-xl lg:bottom-6"></div>
</div>
@endsection
