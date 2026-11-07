<article class="flex flex-col rounded-2xl border border-[#dce6f0] bg-white p-4 shadow-sm sm:p-5">
    <div class="flex items-start gap-3">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#E8F3FC] text-lg font-extrabold text-[#0C3065]">
            @if($partner->profile_photo_path)<img src="{{ Storage::disk('public')->url($partner->profile_photo_path) }}" alt="Foto {{ $partner->business_name }}" class="h-full w-full object-cover">@else{{ mb_strtoupper(mb_substr($partner->business_name,0,1)) }}@endif
        </div>
        <div class="min-w-0 flex-1"><h3 class="text-base font-extrabold text-[#142B42]">{{ $partner->business_name }}</h3><p class="mt-1 text-xs text-slate-600">{{ $partner->service_area ?: $partner->city ?: 'Bandung' }}</p><span class="mt-2 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700"><span class="material-symbols-outlined text-[14px]">verified</span>Mitra terverifikasi</span></div>
    </div>
    <div class="mt-4 flex-1 space-y-2">
        @forelse($partner->services as $service)
        <div class="flex items-start justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2.5"><div class="min-w-0"><a href="{{ route('db-catalog.show', $service) }}" class="text-sm font-bold text-primary hover:underline">{{ $service->name }}</a><p class="text-[11px] text-slate-500">{{ $service->category?->name ?? 'Jasa' }} · {{ $service->duration_minutes }} menit</p></div><span class="shrink-0 text-sm font-extrabold text-[#0C3065]">Rp {{ number_format($service->base_price,0,',','.') }}</span></div>
        @empty<p class="rounded-xl bg-slate-50 px-3 py-3 text-xs text-slate-600">Mitra ini terverifikasi, tetapi belum memiliki layanan aktif yang dapat dipesan.</p>
        @endforelse
    </div>
    <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4"><a href="{{ route('partners.public.show', $partner->slug) }}" class="flex-1 rounded-xl border border-blue-200 px-3 py-2.5 text-center text-xs font-bold text-primary hover:bg-blue-50">Lihat Profil</a>@if($partner->services->isNotEmpty())<a href="{{ route('bookings.database.create', $partner->slug) }}" class="flex-1 rounded-xl bg-primary px-3 py-2.5 text-center text-xs font-bold text-white">Pesan Sekarang</a>@endif</div>
</article>
