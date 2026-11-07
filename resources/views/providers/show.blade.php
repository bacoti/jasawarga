@extends('layouts.app')

@section('title', $provider['name'] . ' | Detail Mitra JasaWarga')

@section('content')
<div id="jw-provider-detail" class="min-h-screen bg-surface text-on-surface" data-provider-name="{{ $provider['name'] }}" data-provider-id="{{ $provider['id'] }}" data-provider-category="{{ $provider['category'] }}">
    <script type="application/json" data-provider-categories-seed>@json(config('jasawarga.categories', []))</script>
    <script type="application/json" data-provider-services-seed>{!! json_encode($profile['services'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script type="application/json" data-provider-profile-seed>{!! json_encode(['provider' => $provider, 'profile' => $profile], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    {{-- Header desktop; mengikuti warna dan navigasi halaman Kategori Jasa. --}}
    <header class="sticky top-0 z-40 hidden border-b border-outline-variant/40 bg-white/95 backdrop-blur lg:block">
        <div class="mx-auto flex h-[76px] max-w-7xl items-center justify-between px-8">
            <div class="flex items-center gap-9">
                <a href="{{ route('home') }}" class="flex flex-col leading-none" aria-label="Beranda JasaWarga">
                    <span class="text-[22px] font-extrabold tracking-tight text-primary">JasaWarga</span>
                    <span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.11em] text-outline">Layanan Warga Makassar</span>
                </a>
                <nav class="flex items-center gap-7 text-sm" aria-label="Navigasi utama">
                    <a href="{{ route('home') }}" class="text-on-surface-variant hover:text-primary">Beranda</a>
                    <a href="{{ route('categories.index') }}" class="font-semibold text-primary">Kategori Jasa</a>
                    <span class="text-on-surface-variant" aria-current="page">Detail Mitra</span><a href="{{ route('account.profile') }}" data-jw-account-link class="text-on-surface-variant hover:text-primary">Akun Warga</a><a href="{{ route('orders.index') }}" class="text-on-surface-variant hover:text-primary">Pesanan Saya</a>
                </nav>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-container-low px-3 py-2 text-xs font-semibold text-on-surface-variant">
                <span class="material-symbols-outlined text-[18px] text-primary">location_on</span> Makassar
            </span>
        </div>
    </header>

    {{-- Header mobile. --}}
    <header class="sticky top-0 z-40 flex items-center justify-between border-b border-outline-variant/40 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('categories.index', ['kategori' => $provider['category']]) }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-container-low" aria-label="Kembali ke Kategori Jasa">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div class="min-w-0">
                <p class="truncate text-base font-extrabold">Detail Mitra</p>
                <p class="truncate text-xs text-outline" data-public-name>{{ $provider['name'] }}</p>
            </div>
        </div>
        <button type="button" data-provider-share class="flex h-10 w-10 items-center justify-center rounded-xl border border-outline-variant/60" aria-label="Bagikan profil">
            <span class="material-symbols-outlined text-primary">share</span>
        </button>
    </header>

    <main class="mx-auto max-w-7xl px-4 pb-28 pt-5 sm:px-6 lg:px-8 lg:pb-16 lg:pt-8">
        <nav class="mb-5 hidden items-center gap-2 text-xs text-outline lg:flex" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('categories.index', ['kategori' => $provider['category']]) }}" class="hover:text-primary">{{ $category['name'] ?? 'Kategori Jasa' }}</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span data-public-name class="font-semibold text-primary">{{ $provider['name'] }}</span>
        </nav>

        {{-- Identitas mitra. --}}
        <section class="relative overflow-hidden rounded-2xl border border-outline-variant/40 bg-white shadow-sm" aria-labelledby="provider-name">
            <div class="relative h-24 overflow-hidden bg-gradient-to-r from-primary-fixed via-surface-container-high to-surface-container-low sm:h-32">
                <img data-public-cover-img alt="Sampul usaha mitra" class="hidden h-full w-full object-cover">
                <span data-public-cover-fallback class="material-symbols-outlined absolute right-5 top-0 select-none text-[110px] text-primary/10 sm:text-[140px]">{{ $category['icon'] ?? 'handyman' }}</span>
            </div>
            <div class="relative p-4 pt-0 sm:p-7 sm:pt-0">
                <div class="-mt-9 flex flex-wrap items-end justify-between gap-3 sm:-mt-12">
                    <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-2xl border-4 border-white text-2xl font-black shadow-sm sm:h-24 sm:w-24" style="background-color: {{ $provider['bg'] }}">
                        <img data-public-avatar-img alt="Foto profil mitra" class="hidden h-full w-full object-cover">
                        <span data-public-avatar-fallback aria-hidden="true">{{ $provider['initials'] }}</span>
                    </div>
                    <button type="button" data-provider-share class="mb-1 hidden items-center gap-2 rounded-xl border border-outline-variant/60 bg-white px-4 py-2 text-sm font-semibold hover:bg-surface-container-low sm:inline-flex">
                        <span class="material-symbols-outlined text-[18px]">share</span> Bagikan profil
                    </button>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <h1 id="provider-name" data-public-name class="text-[23px] font-extrabold tracking-tight sm:text-3xl">{{ $provider['name'] }}</h1>
                    <span data-public-verification class="inline-flex items-center gap-1 rounded-full bg-secondary-fixed px-2.5 py-1 text-[11px] font-bold text-on-secondary-fixed" data-catalog-verified="{{ $provider['verified'] ? '1' : '0' }}">{{ $provider['verified'] ? 'Terverifikasi (data contoh)' : 'Belum diverifikasi (data contoh)' }}</span>
                </div>
                <p class="mt-1 text-sm font-semibold text-primary sm:text-base">{{ $category['name'] ?? 'Layanan Jasa' }} · <span data-public-headline>{{ $profile['headline'] }}</span></p>
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-on-surface-variant sm:text-sm">
                    <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[18px] text-tertiary">star</span><strong class="text-on-surface">{{ number_format($provider['rating'], 1) }}</strong> ({{ $provider['reviews'] }} ulasan contoh)</span>
                    <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[18px] text-primary">location_on</span><span data-public-primary-area>{{ $provider['area'] }}</span>, Makassar · {{ number_format($provider['distance'], 1) }} km (contoh)</span>
                    <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[18px] {{ $provider['available'] ? 'text-emerald-700' : 'text-outline' }}">schedule</span>{{ $provider['available'] ? 'Tersedia hari ini (simulasi)' : 'Jadwal terbatas (simulasi)' }}</span>
                </div>
                <div class="mt-5 grid grid-cols-3 divide-x divide-outline-variant/40 rounded-xl bg-surface-container-low/80 py-3 text-center">
                    <div class="px-2"><p data-public-experience class="text-sm font-extrabold sm:text-lg">{{ $profile['experience'] }}</p><p class="text-[11px] text-outline">Pengalaman</p></div>
                    <div class="px-2"><p class="text-sm font-extrabold sm:text-lg">{{ $profile['completed'] }}</p><p class="text-[11px] text-outline">Pekerjaan</p></div>
                    <div class="px-2"><p class="text-sm font-extrabold sm:text-lg">{{ $profile['response'] }}</p><p class="text-[11px] text-outline">Estimasi respons</p></div>
                </div>
            </div>
        </section>

        {{-- Tab berupa anchor native; seluruh bagian tetap bisa dibaca tanpa JS. --}}
        <nav class="no-scrollbar mt-5 flex gap-2 overflow-x-auto pb-2 text-xs font-bold sm:text-sm" aria-label="Bagian profil mitra">
            <a href="#layanan" class="shrink-0 rounded-full bg-inverse-surface px-4 py-2 text-white">Layanan & Tarif</a>
            <a href="#portofolio" class="shrink-0 rounded-full border border-outline-variant/60 bg-white px-4 py-2 text-on-surface-variant hover:text-primary">Portofolio</a>
            <a href="#ulasan" class="shrink-0 rounded-full border border-outline-variant/60 bg-white px-4 py-2 text-on-surface-variant hover:text-primary">Ulasan</a>
            <a href="#ulasan-lokal" class="shrink-0 rounded-full border border-outline-variant/60 bg-white px-4 py-2 text-on-surface-variant hover:text-primary">Ulasan Saya</a>
            <a href="#tentang" class="shrink-0 rounded-full border border-outline-variant/60 bg-white px-4 py-2 text-on-surface-variant hover:text-primary">Tentang Mitra</a>
        </nav>

        <div class="mt-4 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_330px]">
            <div class="min-w-0 space-y-6">
                {{-- Pilihan layanan. --}}
                <section id="layanan" class="scroll-mt-28 rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="layanan-title">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <div><h2 id="layanan-title" class="text-lg font-extrabold sm:text-xl">Layanan & Tarif</h2><p class="mt-1 text-xs text-on-surface-variant">Pilih satu layanan untuk melihat estimasi biaya.</p></div>
                        <span class="hidden rounded-full bg-primary-fixed px-3 py-1 text-[11px] font-bold text-primary sm:inline-block"><span data-provider-service-count>{{ count($profile['services']) }}</span> pilihan</span>
                    </div>
                    <p data-provider-service-empty class="mb-3 hidden rounded-xl bg-surface-container-low px-4 py-5 text-sm text-on-surface-variant">Belum ada layanan aktif yang bisa dipesan dari mitra ini.</p>
                    <div class="space-y-3" data-provider-service-list>
                        @foreach($profile['services'] as $index => $service)
                            <label class="block cursor-pointer" for="provider-service-{{ $index }}">
                                <input id="provider-service-{{ $index }}" class="peer sr-only" type="radio" name="provider-service" value="{{ $index }}" data-provider-service data-service-name="{{ $service['name'] }}" data-service-price="{{ $service['price'] }}" @checked($index === 0)>
                                <span class="block rounded-xl border border-outline-variant/60 p-3.5 transition hover:border-primary/60 peer-checked:border-primary peer-checked:bg-primary-fixed/30 peer-focus-visible:ring-2 peer-focus-visible:ring-primary sm:p-4">
                                    <span class="flex items-start justify-between gap-3">
                                        <span class="min-w-0"><span class="block text-sm font-bold text-on-surface sm:text-base">{{ $service['name'] }}</span><span class="mt-1 block text-xs leading-relaxed text-on-surface-variant">{{ $service['description'] }}</span></span>
                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-outline-variant/80 bg-white text-xs text-white peer-checked:bg-primary">✓</span>
                                    </span>
                                    <span class="mt-3 flex flex-wrap items-center justify-between gap-2"><span class="inline-flex items-center gap-1 text-[11px] text-outline"><span class="material-symbols-outlined text-[16px]">schedule</span>{{ $service['duration'] }}</span><strong class="text-sm text-primary sm:text-base">Rp {{ number_format($service['price'], 0, ',', '.') }}</strong></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-4 flex items-start gap-2 rounded-xl bg-surface-container-low px-3 py-3 text-xs leading-relaxed text-on-surface-variant"><span class="material-symbols-outlined text-[18px] text-primary">info</span>Tarif dan durasi hanyalah contoh. Harga akhir perlu dikonfirmasi setelah pengecekan kebutuhan. Pemesanan nyata belum tersedia.</p>
                </section>

                {{-- Portofolio visual ilustratif: tidak mengklaim sebagai foto hasil asli. --}}
                <section id="portofolio" class="scroll-mt-28 rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="portfolio-title">
                    <h2 id="portfolio-title" class="text-lg font-extrabold sm:text-xl">Portofolio Pekerjaan</h2>
                    <p class="mt-1 text-xs text-on-surface-variant">Portofolio bawaan merupakan ilustrasi. Foto tambahan tersimpan hanya pada browser ini.</p>
                    <div data-public-projects class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach($profile['projects'] as $project)
                            <article class="overflow-hidden rounded-xl border border-outline-variant/40">
                                <div class="flex aspect-[4/3] items-center justify-center" style="background-color: {{ $project['color'] }}">
                                    <span class="material-symbols-outlined text-[64px] text-primary/70" aria-hidden="true">{{ $project['icon'] }}</span>
                                </div>
                                <div class="p-3"><h3 class="text-xs font-bold leading-snug sm:text-sm">{{ $project['title'] }}</h3><p class="mt-1 text-[11px] text-outline">{{ $project['note'] }}</p></div>
                            </article>
                        @endforeach
                    </div>
                </section>

                {{-- Ulasan contoh. --}}
                <section id="ulasan" class="scroll-mt-28 rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="reviews-title">
                    <div class="flex items-end justify-between gap-3">
                        <div><h2 id="reviews-title" class="text-lg font-extrabold sm:text-xl">Ulasan Warga</h2><p class="mt-1 text-xs text-on-surface-variant">Semua ulasan berikut merupakan contoh fiktif.</p></div>
                        <div class="text-right"><p class="text-2xl font-extrabold text-on-surface">{{ number_format($provider['rating'], 1) }} <span class="align-middle text-xl text-tertiary">★</span></p><p class="text-[11px] text-outline">Rating simulasi</p></div>
                    </div>
                    <div class="mt-3 divide-y divide-outline-variant/40">
                        @foreach($profile['reviews'] as $index => $review)
                            <article class="py-4 {{ $index >= 2 ? 'hidden' : '' }}" @if($index >= 2) data-provider-extra-review @endif>
                                <div class="flex items-center justify-between gap-3"><div class="flex items-center gap-2"><span class="flex h-9 w-9 items-center justify-center rounded-full bg-secondary-fixed text-xs font-extrabold text-secondary">{{ mb_substr($review['name'], 0, 1) }}</span><div><p class="text-sm font-bold">{{ $review['name'] }}</p><p class="text-[11px] text-outline">{{ $review['date'] }}</p></div></div><span class="text-xs tracking-wide text-tertiary" aria-label="{{ $review['rating'] }} dari 5 bintang">{{ str_repeat('★', $review['rating']) }}{{ str_repeat('☆', 5 - $review['rating']) }}</span></div>
                                <p class="mt-2 text-sm leading-relaxed text-on-surface-variant">{{ $review['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                    @if(count($profile['reviews']) > 2)
                        <button type="button" data-provider-more-reviews aria-expanded="false" class="mt-1 text-sm font-bold text-primary hover:underline">Lihat semua ulasan contoh</button>
                    @endif
                </section>

                {{-- Ulasan hasil input pengguna di browser (terpisah dari ulasan dummy). --}}
                <section id="ulasan-lokal" class="scroll-mt-28 rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="reviews-local-title">
                    <div class="flex flex-wrap items-end justify-between gap-3"><div><span class="inline-flex items-center gap-1.5 rounded-full bg-primary-fixed px-2.5 py-1 text-[10px] font-bold text-primary"><span class="material-symbols-outlined text-[15px]">devices</span> Tersimpan di browser</span><h2 id="reviews-local-title" class="mt-3 text-lg font-extrabold sm:text-xl">Ulasan dari simulasi Anda</h2><p class="mt-1 text-xs leading-relaxed text-on-surface-variant">Ulasan yang ditambahkan melalui pesanan selesai pada perangkat ini. Tidak dipublikasikan dan tidak memengaruhi rating contoh di atas.</p></div><p data-provider-local-review-summary class="text-xs font-extrabold text-primary">Belum ada ulasan lokal</p></div>
                    <div data-provider-local-review-list class="mt-3"></div>
                    <p data-provider-local-review-empty class="mt-4 rounded-xl bg-surface-container-low px-4 py-5 text-center text-xs text-outline">Belum ada ulasan simulasi untuk mitra ini di browser Anda.</p>
                    <a href="{{ route('reviews.index') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline">Riwayat Ulasan Saya <span class="material-symbols-outlined text-[18px]">arrow_forward</span></a>
                </section>

                <section id="tentang" class="scroll-mt-28 rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="about-title">
                    <h2 id="about-title" class="text-lg font-extrabold sm:text-xl">Tentang Mitra</h2>
                    <p data-public-about class="mt-3 text-sm leading-7 text-on-surface-variant">{{ $profile['about'] }}</p>
                    <div class="mt-4"><p class="text-sm font-bold">Spesialisasi</p><div data-public-specialties class="mt-2 flex flex-wrap gap-2"><span class="rounded-full bg-primary-fixed px-2.5 py-1 text-xs font-semibold text-primary">{{ $provider['service'] }}</span></div></div>
                    <p data-public-contact-wrap class="mt-4 hidden text-sm text-on-surface-variant"><span class="material-symbols-outlined mr-1 align-middle text-[17px] text-primary">call</span>Kontak usaha: <span data-public-contact></span> <span class="text-xs text-outline">(data simulasi)</span></p>
                    <div class="mt-5 grid gap-4 border-t border-outline-variant/40 pt-5 sm:grid-cols-2">
                        <div><p class="flex items-center gap-1.5 text-sm font-bold"><span class="material-symbols-outlined text-[18px] text-primary">schedule</span>Jam layanan</p><p class="mt-1 text-sm text-on-surface-variant">{{ $profile['hours'] }}</p></div>
                        <div data-public-coverage-wrap><p class="flex items-center gap-1.5 text-sm font-bold"><span class="material-symbols-outlined text-[18px] text-primary">location_on</span>Area jangkauan</p><div data-public-coverage class="mt-2 flex flex-wrap gap-2">@foreach($profile['coverage'] as $area)<span class="rounded-full bg-surface-container-low px-2.5 py-1 text-xs text-on-surface-variant">{{ $area }}</span>@endforeach</div></div>
                    </div>
                </section>
            </div>

            {{-- Ringkasan dan CTA desktop. --}}
            <aside class="sticky top-[98px] hidden rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm lg:block" aria-label="Ringkasan pemesanan">
                <p class="text-sm font-bold text-on-surface">Ringkasan pilihan Anda</p>
                <p class="mt-1 text-xs text-outline">Estimasi harga awal (data dummy)</p>
                <p class="mt-4 text-xs text-on-surface-variant" data-provider-selected-name>{{ $profile['services'][0]['name'] }}</p>
                <p class="mt-1 text-3xl font-extrabold tracking-tight text-primary" data-provider-price>Rp {{ number_format($profile['services'][0]['price'], 0, ',', '.') }}</p>
                <div class="my-5 border-t border-outline-variant/40"></div>
                <div class="space-y-3 text-xs text-on-surface-variant"><p class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-primary">location_on</span> Area <span data-public-primary-area>{{ $provider['area'] }}</span>, Makassar</p><p class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-primary">verified_user</span> {{ $provider['warranty'] ? 'Garansi contoh tersedia' : 'Garansi bergantung layanan' }}</p><p class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-primary">payments</span> Tidak ada pembayaran pada prototipe</p></div>
                <a href="{{ route('bookings.create', $provider['id']) }}" data-provider-book class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white transition hover:bg-primary-container"><span class="material-symbols-outlined text-[19px]">calendar_month</span> Pesan Jasa</a>
                <p class="mt-3 text-center text-[11px] leading-relaxed text-outline">Lanjut ke form pemesanan simulasi. Tidak membuat transaksi sungguhan.</p>
            </aside>
        </div>
    </main>

    {{-- Sticky action khusus mobile. --}}
    <div class="fixed bottom-0 left-0 right-0 z-40 border-t border-outline-variant/40 bg-white/95 px-4 py-3 shadow-[0_-4px_18px_rgba(14,28,49,.07)] backdrop-blur lg:hidden" style="padding-bottom: max(12px, env(safe-area-inset-bottom));">
        <div class="mx-auto flex max-w-lg items-center justify-between gap-4"><div class="min-w-0"><p class="text-[11px] text-outline">Mulai dari · contoh</p><p class="truncate text-lg font-extrabold text-primary" data-provider-price>Rp {{ number_format($profile['services'][0]['price'], 0, ',', '.') }}</p></div><a href="{{ route('bookings.create', $provider['id']) }}" data-provider-book class="flex shrink-0 items-center gap-1.5 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white"><span class="material-symbols-outlined text-[20px]">calendar_month</span> Pesan Jasa</a></div>
    </div>

    <div id="jw-provider-toast" role="status" aria-live="polite" class="pointer-events-none fixed bottom-24 left-1/2 z-[70] hidden w-[min(92vw,400px)] -translate-x-1/2 rounded-xl bg-inverse-surface px-4 py-3 text-center text-sm text-white shadow-xl lg:bottom-6"></div>
</div>
@endsection
