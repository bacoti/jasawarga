@extends('layouts.app')
@section('title', 'Dashboard Warga | JasaWarga')
@section('content')
<div class="min-h-screen bg-[#F6F8FC] text-on-surface">
  @include('accounts.partials.header')
  <div class="mx-auto grid max-w-[1500px] items-start gap-6 px-4 pb-28 pt-6 sm:px-6 lg:grid-cols-[255px_minmax(0,1fr)] lg:px-8 lg:pb-12 lg:pt-8">
    @include('accounts.partials.sidebar')
    <main class="min-w-0 space-y-6" aria-label="Dashboard Warga">
      <header class="flex flex-wrap items-end justify-between gap-3">
        <div><p class="text-[10px] font-extrabold uppercase tracking-[.17em] text-primary">JasaWarga / Area Warga</p>
          <h1 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-[30px]">Dashboard Warga</h1>
          <p class="mt-2 text-sm text-on-surface-variant">Selamat datang, {{ auth()->user()->name }}. Pantau pekerjaan dan jadwal dalam akun Anda.</p>
        </div>
        <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-3 text-xs font-bold text-white hover:opacity-90"><span class="material-symbols-outlined text-[18px]">search</span>Cari Jasa</a>
      </header>
      <div class="flex items-start gap-2 rounded-xl border border-[#CBDDEE] bg-[#EDF5FB] px-4 py-3 text-xs leading-relaxed text-on-surface-variant"><span class="material-symbols-outlined text-primary">verified_user</span><p>Statistik di halaman ini berasal dari <strong>pesanan PostgreSQL milik akun Anda</strong>, bukan riwayat demo browser.</p></div>
      <section aria-label="Statistik Warga" class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach([['Semua pesanan', $stats['total'], 'receipt_long'], ['Sedang berjalan', $stats['active'], 'handyman'], ['Selesai', $stats['completed'], 'task_alt'], ['Menunggu harga', $stats['pending_price'], 'price_check']] as $card)
          <article class="rounded-2xl border border-outline-variant/35 bg-white p-4 shadow-sm sm:p-5"><div class="flex items-center justify-between gap-3"><p class="text-xs font-semibold text-on-surface-variant">{{ $card[0] }}</p><span class="material-symbols-outlined text-primary">{{ $card[2] }}</span></div><p class="mt-4 text-[32px] font-extrabold leading-none tabular-nums">{{ number_format($card[1], 0, ',', '.') }}</p><p class="mt-3 text-[10px] font-semibold uppercase tracking-wide text-primary">Data akun</p></article>
        @endforeach
      </section>
      <section class="overflow-hidden rounded-2xl border border-outline-variant/35 bg-white shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-outline-variant/35 p-5"><div><h2 class="text-base font-extrabold">Pesanan terbaru</h2><p class="mt-1 text-xs text-outline">Riwayat booking yang terkait dengan akun login.</p></div><a href="{{ route('account.bookings.index') }}" class="rounded-xl border border-outline-variant/60 px-3 py-2 text-xs font-bold text-primary hover:bg-surface-container-low">Lihat semua</a></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[660px] text-left text-xs"><thead class="bg-surface-container-low text-on-surface-variant"><tr><th class="px-5 py-3">Kode</th><th class="px-5 py-3">Mitra</th><th class="px-5 py-3">Layanan</th><th class="px-5 py-3">Jadwal</th><th class="px-5 py-3">Status</th></tr></thead><tbody class="divide-y divide-outline-variant/30">@forelse($recent as $booking)
          <tr class="hover:bg-surface-container-low/60"><td class="px-5 py-4"><a href="{{ route('account.bookings.show', $booking->booking_code) }}" class="font-bold text-primary hover:underline">{{ $booking->booking_code }}</a></td><td class="px-5 py-4">{{ $booking->partnerProfile?->business_name ?? '—' }}</td><td class="px-5 py-4">{{ $booking->service_name_snapshot }}</td><td class="px-5 py-4">{{ $booking->scheduled_at?->format('d/m/Y H:i') ?? '—' }}</td><td class="px-5 py-4">{{ config('jasawarga-analytics.booking_statuses.'.$booking->status, $booking->status) }}</td></tr>
          @empty<tr><td colspan="5" class="px-5 py-12 text-center text-sm text-outline">Belum ada pesanan. Jelajahi kategori untuk memesan jasa.</td></tr>@endforelse</tbody></table></div>
      </section>
      <div class="grid gap-3 sm:grid-cols-2"><a href="{{ route('account.profile.show') }}" class="flex items-center justify-between rounded-xl border border-outline-variant/40 bg-white p-5 font-bold text-primary hover:border-primary/40"><span class="flex items-center gap-2"><span class="material-symbols-outlined">person</span>Profil Saya</span><span class="material-symbols-outlined">arrow_forward</span></a><a href="{{ route('account.addresses.index') }}" class="flex items-center justify-between rounded-xl border border-outline-variant/40 bg-white p-5 font-bold text-primary hover:border-primary/40"><span class="flex items-center gap-2"><span class="material-symbols-outlined">location_on</span>Alamat Tersimpan</span><span class="material-symbols-outlined">arrow_forward</span></a></div>
    </main>
  </div>
  @include('accounts.partials.bottom-nav')
</div>
@endsection
