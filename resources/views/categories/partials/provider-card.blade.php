@php
    $categoryName = collect($categories)->firstWhere('slug', $provider['category'])['name'] ?? 'Layanan';
@endphp
<article
    class="jw-catalog-card rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm transition-shadow hover:shadow-md sm:p-5"
    data-provider-id="{{ $provider['id'] }}"
    data-seed-verified="{{ $provider['verified'] ? '1' : '0' }}"
    data-category="{{ $provider['category'] }}"
    data-search="{{ mb_strtolower($provider['name'].' '.$provider['service'].' '.$provider['area'].' '.$categoryName, 'UTF-8') }}"
    data-rating="{{ $provider['rating'] }}"
    data-distance="{{ $provider['distance'] }}"
    data-price="{{ $provider['price'] }}"
    data-available="{{ $provider['available'] ? '1' : '0' }}"
    data-warranty="{{ $provider['warranty'] ? '1' : '0' }}"
    data-original-order="{{ $index }}"
>
    <div class="flex items-start gap-3 sm:gap-4">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-base font-extrabold text-on-surface sm:h-[72px] sm:w-[72px] sm:text-lg"
             style="background-color: {{ $provider['bg'] }}" aria-hidden="true">
            {{ $provider['initials'] }}
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-base font-bold leading-tight text-on-surface sm:text-lg">{{ $provider['name'] }}</h3>
                <span data-card-verification class="inline-flex items-center gap-1 rounded-full bg-secondary-fixed px-2 py-0.5 text-[10px] font-bold text-on-secondary-fixed">{{ $provider['verified'] ? 'Terverifikasi (demo)' : 'Belum diverifikasi (demo)' }}</span>
            </div>
            <p class="mt-1 text-sm font-medium text-primary">{{ $provider['service'] }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-on-surface-variant">
                <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-tertiary">star</span><strong class="text-on-surface">{{ number_format($provider['rating'], 1) }}</strong> ({{ $provider['reviews'] }} ulasan)</span>
                <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">location_on</span>{{ $provider['area'] }} · {{ number_format($provider['distance'], 1) }} km</span>
            </div>
            <div class="mt-2 flex flex-wrap gap-1.5">
                <span class="rounded-md px-2 py-1 text-[11px] font-medium {{ $provider['available'] ? 'bg-emerald-50 text-emerald-700' : 'bg-surface-container-low text-outline' }}">
                    {{ $provider['available'] ? 'Tersedia hari ini' : 'Jadwal terbatas' }}
                </span>
                @if($provider['warranty'])
                    <span class="rounded-md bg-primary-fixed/70 px-2 py-1 text-[11px] font-medium text-primary">Garansi layanan</span>
                @endif
            </div>
        </div>
    </div>
    <div class="mt-4 flex items-end justify-between gap-3 border-t border-outline-variant/30 pt-3">
        <div>
            <p class="text-xs text-outline">Tarif mulai dari</p>
            <p class="text-lg font-extrabold tracking-tight text-primary">Rp {{ number_format($provider['price'], 0, ',', '.') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('providers.show', $provider['id']) }}" class="rounded-xl border border-outline-variant/70 bg-white px-3 py-2 text-xs font-semibold text-on-surface transition hover:bg-surface-container-low sm:px-4 sm:text-sm">Lihat Profil</a>
            <a href="{{ route('bookings.create', $provider['id']) }}" class="rounded-xl bg-primary px-3 py-2 text-xs font-semibold text-white transition hover:bg-primary-container sm:px-4 sm:text-sm">Pesan</a>
        </div>
    </div>
</article>
