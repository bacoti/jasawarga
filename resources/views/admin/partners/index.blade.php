@extends('layouts.app')
@section('title', 'Manajemen Mitra & Verifikasi | Admin JasaWarga')
@section('content')
<div class="min-h-screen bg-[#F6F8FC] text-on-surface lg:flex">
    @include('admin.partials.sidebar')
    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex min-h-[72px] max-w-[1500px] items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <div><p class="text-[10px] font-bold uppercase tracking-[.16em] text-slate-500">JasaWarga / Administrasi</p><h1 class="text-base font-extrabold sm:text-lg">Mitra & Verifikasi</h1></div>
                <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-primary">Ringkasan</a>
            </div>
        </header>
        <main class="mx-auto max-w-[1500px] space-y-6 px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-10 lg:pt-8">
            <section class="flex flex-wrap items-end justify-between gap-4"><div>
                <p class="text-[10px] font-bold uppercase tracking-[.15em] text-primary">Akun Mitra · PostgreSQL</p>
                <h2 class="mt-2 text-2xl font-extrabold">Manajemen Mitra & Verifikasi</h2>
                <p class="mt-2 text-sm text-slate-600">Menampilkan Mitra yang mendaftar, termasuk yang <strong>sudah terverifikasi</strong>. Pencarian dan status berasal dari PostgreSQL, bukan daftar demo browser.</p>
            </div><a href="{{ route('admin.partners.demo') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600">Lihat katalog dummy terpisah</a></section>
            @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
            <section class="grid grid-cols-2 gap-3 xl:grid-cols-5" aria-label="Statistik Mitra PostgreSQL">
            @foreach([['all','Semua Mitra'],['approved','Terverifikasi'],['pending','Menunggu'],['rejected','Ditolak'],['reverification','Periksa ulang']] as $item)
                <a href="{{ route('admin.partners', ['status' => $item[0]]) }}" class="rounded-2xl border {{ $status === $item[0] ? 'border-blue-300 ring-1 ring-blue-200' : 'border-slate-200' }} bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-slate-600">{{ $item[1] }}</p><p class="mt-3 text-3xl font-extrabold text-primary">{{ $stats[$item[0]] }}</p></a>
            @endforeach
            </section>
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <form method="GET" action="{{ route('admin.partners') }}" class="grid gap-3 border-b border-slate-200 p-4 sm:grid-cols-[1fr_190px_auto] sm:p-5" role="search">
                    <label class="sr-only" for="db-partner-q">Cari Mitra</label><input id="db-partner-q" name="q" maxlength="100" value="{{ $search }}" placeholder="Cari usaha, nama pemilik, email, kecamatan..." type="search" class="w-full min-w-0 rounded-xl border-slate-300 text-sm">
                    <label class="sr-only" for="db-partner-status">Status verifikasi</label><select id="db-partner-status" name="status" class="rounded-xl border-slate-300 text-sm">
                        @foreach(['all'=>'Semua status','approved'=>'Terverifikasi','pending'=>'Menunggu','rejected'=>'Ditolak','reverification'=>'Periksa ulang'] as $key=>$label)<option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>@endforeach
                    </select><button class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white">Cari Mitra</button>
                </form>
                <div class="divide-y divide-slate-100">
                    @forelse($applications as $profile)
                    @php($published = $profile->verification_status === 'approved' && ! $profile->reverification_required)
                    <article class="p-5 sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3"><div class="min-w-0"><p class="text-[11px] text-slate-500">#{{ $profile->id }} · Terdaftar {{ $profile->created_at?->format('d/m/Y') }}</p><h3 class="mt-1 text-lg font-extrabold">{{ $profile->business_name }}</h3><p class="mt-1 text-sm text-slate-600">{{ $profile->user?->name }} · {{ $profile->service_area ?: $profile->city }}</p><p class="mt-1 text-xs text-slate-500">Email: {{ $profile->user?->email }} · {{ $profile->published_services_count }} layanan aktif dan publik</p></div>
                        <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $published ? 'bg-emerald-100 text-emerald-800' : ($profile->verification_status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">{{ $profile->reverification_required ? 'Perlu verifikasi ulang' : match($profile->verification_status) {'approved'=>'Terverifikasi','rejected'=>'Ditolak', default=>'Menunggu'} }}</span></div>
                        @if($profile->decision_reason)<p class="mt-3 text-xs text-slate-600"><strong>Keputusan terakhir:</strong> {{ $profile->decision_reason }}</p>@endif
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            @if($published && $profile->slug)<a href="{{ route('partners.public.show', $profile->slug) }}" target="_blank" rel="noopener" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-bold text-primary">Lihat profil Warga</a>@endif
                            @if($published && $profile->published_services_count)<a href="{{ route('categories.index', ['db_q' => $profile->business_name]) }}" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-bold text-primary">Lihat layanan di katalog</a>@endif
                        </div>
                        <form method="POST" action="{{ route('admin.partner-applications.decide', $profile) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 md:grid-cols-[1fr_auto_auto]">@csrf
                            <label class="block text-xs font-bold text-slate-700">Alasan keputusan (10–500 karakter)<textarea name="reason" minlength="10" maxlength="500" required rows="2" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm" placeholder="Catatan verifikasi Mitra..."></textarea></label>
                            <button type="submit" name="decision" value="approved" @disabled($published) class="self-end rounded-xl bg-emerald-700 px-4 py-3 text-xs font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">{{ $profile->reverification_required ? 'Verifikasi ulang' : 'Setujui' }}</button>
                            <button type="submit" name="decision" value="rejected" @disabled($profile->verification_status === 'rejected' && ! $profile->reverification_required) class="self-end rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold text-red-700 disabled:cursor-not-allowed disabled:opacity-40">Tolak</button>
                        </form>
                    </article>
                    @empty <div class="p-12 text-center text-sm text-slate-500">Tidak ada Mitra PostgreSQL yang cocok. Coba pilih Semua status atau hapus kata pencarian.</div>
                    @endforelse
                </div>
            </section>
            <div>{{ $applications->links() }}</div>
            <p class="text-xs text-slate-500">Status katalog dummy adalah simulasi terpisah; keputusan pada halaman ini benar-benar tercatat di database.</p>
        </main>
    </div>
</div>
@include('admin.partials.mobile-nav')
@endsection
