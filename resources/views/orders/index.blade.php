@extends('layouts.app')

@section('title', 'Pesanan Saya | JasaWarga')

@section('content')
<div id="jw-orders" class="min-h-screen bg-surface text-on-surface" data-detail-url="{{ url('/pesanan-saya') }}" data-booking-base="{{ url('/pemesanan') }}" data-review-create-base="{{ url('/ulasan/tulis') }}" data-reviews-url="{{ route('reviews.index') }}">
  {{-- Desktop navigation --}}
  <header class="sticky top-0 z-40 hidden border-b border-outline-variant/40 bg-white/95 backdrop-blur lg:block">
    <div class="mx-auto flex h-[76px] max-w-7xl items-center justify-between gap-8 px-8">
      <div class="flex items-center gap-9">
        <a href="{{ route('home') }}" class="flex flex-col leading-none"><span class="text-[22px] font-extrabold tracking-tight text-primary">JasaWarga</span><span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.11em] text-outline">Layanan Warga Makassar</span></a>
        <nav class="flex items-center gap-7 text-sm" aria-label="Navigasi utama">
          <a href="{{ route('home') }}" class="text-on-surface-variant hover:text-primary">Beranda</a>
          <a href="{{ route('categories.index') }}" class="text-on-surface-variant hover:text-primary">Kategori Jasa</a>
          <span class="border-b-2 border-primary pb-1 font-bold text-primary" aria-current="page">Pesanan Saya</span><a href="{{ route('reviews.index') }}" class="text-on-surface-variant hover:text-primary">Ulasan Saya</a><a href="{{ route('account.profile') }}" data-jw-account-link class="text-on-surface-variant hover:text-primary">Akun Warga</a>
        </nav>
      </div>
      <span class="inline-flex items-center gap-2 rounded-full bg-primary-fixed px-3 py-2 text-xs font-bold text-primary"><span class="material-symbols-outlined text-[18px]">science</span> Tracking simulasi</span>
    </div>
  </header>

  {{-- Mobile navigation --}}
  <header class="sticky top-0 z-40 border-b border-outline-variant/40 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
    <div class="mx-auto flex max-w-lg items-center gap-3">
      <a href="{{ route('home') }}" aria-label="Kembali ke beranda" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-container-low"><span class="material-symbols-outlined">arrow_back</span></a>
      <div class="min-w-0 flex-1"><h1 class="text-base font-extrabold">Pesanan Saya</h1><p class="text-xs text-outline">Riwayat & pelacakan simulasi</p></div>
      <span class="rounded-full bg-primary-fixed px-2.5 py-1.5 text-[10px] font-extrabold text-primary">DEMO</span>
    </div>
  </header>

  <main class="mx-auto max-w-7xl px-4 pb-32 pt-5 sm:px-6 lg:px-8 lg:pb-14 lg:pt-8">
    <nav class="mb-5 hidden items-center gap-2 text-xs text-outline lg:flex" aria-label="Breadcrumb"><a href="{{ route('home') }}" class="hover:text-primary">Beranda</a><span class="material-symbols-outlined text-[16px]">chevron_right</span><span class="font-semibold text-primary">Pesanan Saya</span></nav>

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
      <div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-secondary-fixed px-3 py-1 text-[11px] font-bold text-on-secondary-fixed"><span class="material-symbols-outlined text-[15px]">receipt_long</span> Pusat aktivitas warga</span>
        <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">Pantau pesanan jasa Anda</h2>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-on-surface-variant">Lihat riwayat, periksa tahapan pengerjaan, atau ulangi pesanan. Seluruh status hanya simulasi yang diubah secara manual.</p>
      </div>
      <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white transition hover:bg-primary-container"><span class="material-symbols-outlined text-[19px]">add</span> Pesan jasa baru</a>
    </div>

    {{-- Stats --}}
    <div class="mb-7 grid grid-cols-2 gap-3 lg:grid-cols-4">
      <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-outline">Total pesanan</p><div class="mt-2 flex items-center justify-between"><strong class="text-2xl font-extrabold text-on-surface" data-orders-total>0</strong><span class="material-symbols-outlined text-primary">inventory_2</span></div></article>
      <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-outline">Menunggu</p><div class="mt-2 flex items-center justify-between"><strong class="text-2xl font-extrabold text-on-surface" data-orders-pending>0</strong><span class="material-symbols-outlined text-amber-600">schedule</span></div></article>
      <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-outline">Dalam proses</p><div class="mt-2 flex items-center justify-between"><strong class="text-2xl font-extrabold text-on-surface" data-orders-active>0</strong><span class="material-symbols-outlined text-primary">construction</span></div></article>
      <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-outline">Selesai</p><div class="mt-2 flex items-center justify-between"><strong class="text-2xl font-extrabold text-on-surface" data-orders-completed>0</strong><span class="material-symbols-outlined text-emerald-700">task_alt</span></div></article>
    </div>

    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_278px]">
      <section class="min-w-0" aria-labelledby="orders-title">
        <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5">
          <div class="flex flex-wrap items-center justify-between gap-3"><h3 id="orders-title" class="text-lg font-extrabold">Daftar pesanan</h3><span class="text-xs text-outline" aria-live="polite"><strong data-orders-found>0</strong> pesanan ditampilkan</span></div>
          <label for="jw-orders-search" class="sr-only">Cari pesanan berdasarkan kode, mitra, atau layanan</label>
          <div class="relative"><span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[21px] text-outline">search</span><input id="jw-orders-search" data-orders-search type="search" autocomplete="off" placeholder="Cari kode pesanan, nama mitra, layanan..." class="w-full rounded-xl border border-outline-variant/60 bg-surface-container-lowest py-3 pl-10 pr-4 text-sm focus:border-primary focus:ring-primary"></div>
          <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar" role="group" aria-label="Filter status pesanan">
            @foreach(['all' => 'Semua', 'pending' => 'Menunggu', 'active' => 'Diproses', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', 'rejected' => 'Ditolak mitra'] as $status => $text)
              <button type="button" data-orders-filter="{{ $status }}" aria-pressed="{{ $status === 'all' ? 'true' : 'false' }}" class="shrink-0 rounded-full border border-outline-variant/50 bg-white px-3.5 py-2 text-xs font-semibold text-on-surface-variant transition hover:border-primary hover:text-primary">{{ $text }}</button>
            @endforeach
          </div>
        </div>

        <div data-orders-alert role="alert" class="mb-4 hidden rounded-xl border border-error/30 bg-error-container p-3 text-sm text-on-error-container"></div>
        <div data-orders-list class="space-y-3" aria-live="polite"></div>
        <section data-orders-empty class="hidden rounded-2xl border border-dashed border-outline-variant bg-white px-6 py-12 text-center">
          <span class="material-symbols-outlined text-[50px] text-outline" data-orders-empty-icon>inbox</span><h4 class="mt-3 text-lg font-extrabold" data-orders-empty-title>Belum ada pesanan</h4><p class="mx-auto mt-2 max-w-md text-sm text-on-surface-variant" data-orders-empty-description>Mulai dari Kategori Jasa atau isi tiga data contoh untuk mencoba tracking.</p>
          <a href="{{ route('categories.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white">Cari jasa <span class="material-symbols-outlined text-[18px]">arrow_forward</span></a>
        </section>
      </section>

      <aside class="space-y-4 lg:sticky lg:top-[95px]" aria-label="Informasi demo">
        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary">tips_and_updates</span><h3 class="text-sm font-extrabold">Coba simulasi tracking</h3></div><p class="mt-3 text-xs leading-relaxed text-on-surface-variant">Buat tiga pesanan ilustratif untuk melihat status berbeda. Buka <strong>Kelola Pesanan</strong> di Dashboard Mitra untuk menggerakkan status. Perubahannya terlihat di sini bila menggunakan browser dan origin yang sama.</p><button data-orders-seed type="button" class="mt-4 w-full rounded-xl border border-primary/30 bg-primary-fixed px-4 py-2.5 text-xs font-bold text-primary hover:bg-secondary-fixed">Tambahkan 3 pesanan contoh</button></section>
        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-outline">shield</span><h3 class="text-sm font-extrabold">Privasi perangkat</h3></div><p class="mt-3 text-xs leading-relaxed text-on-surface-variant">Riwayat, area, alamat, dan deskripsi simulasi disimpan di <strong>localStorage browser ini</strong>, bukan di server. Gunakan data fiktif. Hindari perangkat bersama.</p><button data-orders-clear type="button" class="mt-4 w-full rounded-xl border border-outline-variant/60 px-4 py-2.5 text-xs font-bold text-on-surface-variant hover:bg-surface-container-low">Hapus seluruh riwayat demo</button></section>
      </aside>
    </div>
  </main>

  {{-- Card generated in JS; no untrusted data inserted as HTML. --}}
  <template id="jw-order-card-template">
    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm transition hover:shadow-md sm:p-5">
      <div class="flex flex-wrap items-start justify-between gap-2"><div class="min-w-0"><p class="text-[11px] font-semibold text-outline">Kode pesanan demo</p><p class="mt-1 break-all font-mono text-xs font-extrabold text-primary sm:text-sm" data-card-reference></p></div><span data-card-status class="rounded-full px-2.5 py-1 text-[11px] font-bold"></span></div>
      <div class="my-4 border-t border-outline-variant/30"></div>
      <div class="flex items-start gap-3"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-fixed text-primary"><span class="material-symbols-outlined">handyman</span></span><div class="min-w-0 flex-1"><h4 class="font-extrabold" data-card-provider></h4><p class="mt-1 text-sm text-on-surface-variant" data-card-service></p><p class="mt-2 flex items-center gap-1 text-xs text-outline"><span class="material-symbols-outlined text-[16px]">event</span><span data-card-date></span></p></div><strong class="shrink-0 text-sm font-extrabold text-primary" data-card-price></strong></div>
      <div class="mt-4 flex flex-wrap justify-end gap-2 border-t border-outline-variant/30 pt-4"><a data-card-review href="{{ route('reviews.index') }}" class="hidden items-center gap-1 rounded-xl border border-primary/25 bg-primary-fixed/30 px-3.5 py-2.5 text-xs font-semibold text-primary hover:bg-primary-fixed"><span class="material-symbols-outlined text-[16px]">star</span><span data-card-review-label>Beri ulasan</span></a><a data-card-repeat class="inline-flex items-center gap-1 rounded-xl border border-outline-variant/70 px-3.5 py-2.5 text-xs font-semibold hover:bg-surface-container-low"><span class="material-symbols-outlined text-[16px]">replay</span>Pesan lagi</a><a data-card-detail class="inline-flex items-center gap-1 rounded-xl bg-primary px-3.5 py-2.5 text-xs font-bold text-white hover:bg-primary-container">Detail & tracking<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a></div>
    </article>
  </template>

  <dialog id="jw-orders-confirm-clear" class="w-[min(92vw,420px)] rounded-2xl border border-outline-variant/50 bg-white p-5 shadow-2xl backdrop:bg-on-surface/50"><h3 class="text-lg font-extrabold">Hapus semua riwayat?</h3><p class="mt-2 text-sm text-on-surface-variant">Semua pesanan demo di browser ini akan dihapus dan tidak dapat dipulihkan.</p><div class="mt-5 flex justify-end gap-2"><button type="button" data-orders-close-clear class="rounded-xl border border-outline-variant/70 px-4 py-2.5 text-sm font-semibold">Batal</button><button type="button" data-orders-confirm-clear class="rounded-xl bg-error px-4 py-2.5 text-sm font-bold text-white">Hapus</button></div></dialog>
  <div data-orders-toast role="status" aria-live="polite" class="pointer-events-none fixed bottom-24 left-1/2 z-[60] hidden w-[min(92vw,450px)] -translate-x-1/2 rounded-xl bg-inverse-surface px-4 py-3 text-center text-sm text-white shadow-xl lg:bottom-6"></div>

  <nav class="fixed bottom-0 left-0 right-0 z-40 flex items-center justify-around border-t border-outline-variant/40 bg-white/95 px-2 pb-[max(12px,env(safe-area-inset-bottom))] pt-2 shadow-[0_-4px_20px_rgba(14,28,49,0.06)] backdrop-blur lg:hidden" aria-label="Navigasi bawah">
    <a href="{{ route('home') }}" class="flex flex-1 flex-col items-center gap-1 py-1 text-on-surface-variant"><span class="material-symbols-outlined text-[25px]">home</span><span class="text-[11px]">Beranda</span></a>
    <a href="{{ route('categories.index') }}" class="flex flex-1 flex-col items-center gap-1 py-1 text-on-surface-variant"><span class="material-symbols-outlined text-[25px]">grid_view</span><span class="text-[11px]">Jelajahi</span></a>
    <a href="{{ route('orders.index') }}" aria-current="page" class="flex flex-1 flex-col items-center gap-1 py-1 font-bold text-primary"><span class="material-symbols-outlined fill text-[25px]">receipt_long</span><span class="text-[11px]">Pesanan</span></a>
    <a href="{{ route('account.profile') }}" data-jw-account-link class="flex flex-1 flex-col items-center gap-1 py-1 text-on-surface-variant"><span class="material-symbols-outlined text-[25px]">person</span><span class="text-[11px]">Akun</span></a>
  </nav>
</div>
@endsection
