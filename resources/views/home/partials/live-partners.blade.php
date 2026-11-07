<section class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8" aria-label="Mitra PostgreSQL aktif">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3"><div><p class="text-[10px] font-extrabold uppercase tracking-[.16em] text-primary">Mitra bergabung · PostgreSQL</p><h2 class="mt-1 text-lg font-extrabold text-[#142B42] sm:text-2xl">Layanan terbaru dari Mitra terverifikasi</h2><p class="mt-1 text-xs text-slate-600">Data real dari akun dan layanan JasaWarga, bukan data kartu demo.</p></div><a href="{{ route('categories.index') }}#mitra-postgresql" class="rounded-lg border border-blue-200 bg-white px-3 py-2 text-xs font-bold text-primary">Cari semua Mitra →</a></div>
    <div class="grid gap-3 {{ $variant === 'mobile' ? '' : 'lg:grid-cols-2 xl:grid-cols-4' }}">
        @forelse($livePartners as $partner)@include('components.live-partner-card', ['partner' => $partner])
        @empty<div class="rounded-2xl border border-dashed border-blue-200 bg-white px-4 py-6 text-sm text-slate-600">Belum ada layanan PostgreSQL aktif dari Mitra terverifikasi. <a href="{{ route('categories.index') }}#mitra-postgresql" class="font-semibold text-primary underline">Lihat daftar Mitra</a>.</div>
        @endforelse
    </div>
</section>
