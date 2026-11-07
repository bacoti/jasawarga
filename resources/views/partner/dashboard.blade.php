@extends('layouts.app')

@section('title', 'Dashboard Mitra | JasaWarga')

@section('content')
<div id="jw-partner" class="min-h-screen bg-surface text-on-surface"
     data-partner-id="{{ $provider['id'] }}"
     data-partner-name="{{ $provider['name'] }}"
     data-partner-service="{{ $profile['services'][0]['name'] ?? $provider['service'] }}"
     data-partner-price="{{ $profile['services'][0]['price'] ?? $provider['price'] }}">
    <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        @include('partner.partials.sidebar')

        <div class="min-w-0">
            {{-- Desktop top bar --}}
            <header class="sticky top-0 z-30 hidden border-b border-outline-variant/40 bg-white/95 backdrop-blur lg:block">
                <div class="mx-auto flex h-[76px] max-w-[1380px] items-center justify-between gap-4 px-8 xl:px-10">
                    <div class="flex items-center gap-2 text-xs font-semibold text-outline">
                        <span>Ruang Mitra</span><span class="material-symbols-outlined text-[16px]">chevron_right</span>
                        <span class="text-on-surface">Ringkasan operasional</span>
                    </div>
                    <div class="flex items-center gap-5">
                        <span class="flex items-center gap-1.5 text-xs font-medium text-on-surface-variant"><span class="material-symbols-outlined text-[18px] text-primary">location_on</span>Makassar</span>
                        <span class="h-5 w-px bg-outline-variant/60" aria-hidden="true"></span>
                        <span class="rounded-full bg-primary-fixed px-3 py-1.5 text-[11px] font-extrabold text-primary">Mode Mitra · DEMO</span>
                    </div>
                </div>
            </header>

            {{-- Compact mobile top bar --}}
            <header class="sticky top-0 z-30 border-b border-outline-variant/40 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
                <div class="mx-auto flex max-w-2xl items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary text-white"><span class="material-symbols-outlined">handyman</span></span>
                        <div class="min-w-0"><strong class="block text-base leading-tight tracking-tight text-primary">JasaWarga</strong><span class="block truncate text-[11px] text-outline">Dashboard Mitra · {{ $provider['name'] }}</span></div>
                    </div>
                    <a href="{{ route('home') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-outline-variant/60 bg-white text-on-surface-variant" aria-label="Lihat tampilan Warga"><span class="material-symbols-outlined">swap_horiz</span></a>
                </div>
            </header>

            <main class="mx-auto max-w-[1380px] space-y-6 px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-9 xl:px-10">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="mb-2 text-[11px] font-extrabold uppercase tracking-[.14em] text-primary">Panel operasional / Mitra #{{ $provider['id'] }}</p>
                        <h1 class="text-[26px] font-extrabold tracking-tight sm:text-[30px]">Ringkasan operasional</h1>
                        <p class="mt-1 text-sm text-on-surface-variant">Pantau aktivitas {{ $provider['name'] }} dalam satu tampilan.</p>
                    </div>
                    <div class="flex items-center gap-2 rounded-xl border border-outline-variant/50 bg-white px-3 py-2.5 text-xs text-on-surface-variant">
                        <span class="material-symbols-outlined text-[18px] text-primary">devices</span>Data dari browser ini
                    </div>
                </div>

                {{-- Demo label --}}
                <div class="flex items-start gap-3 rounded-xl border border-primary/15 bg-primary-fixed/35 px-4 py-3 text-xs leading-relaxed text-on-surface-variant" role="note">
                    <span class="material-symbols-outlined mt-0.5 text-[20px] text-primary">info</span>
                    <div><strong class="text-on-surface">Dashboard simulasi.</strong> Pesanan dibaca dari localStorage yang sama dengan halaman Pesanan Saya (Warga). Dashboard ini khusus menampilkan pesanan {{ $provider['name'] }}, tidak menerima pesanan sungguhan dan tidak melakukan sinkronisasi antarperangkat.</div>
                </div>

                {{-- KPI cards --}}
                <section aria-label="Ringkasan statistik pesanan" class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between"><span class="text-xs font-semibold text-on-surface-variant">Pesanan masuk</span><span class="material-symbols-outlined text-[22px] text-tertiary">pending_actions</span></div>
                        <p data-partner-pending class="mt-4 text-3xl font-extrabold tabular-nums text-on-surface">0</p>
                        <p class="mt-1 text-[11px] text-outline">Menunggu konfirmasi</p>
                    </article>
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between"><span class="text-xs font-semibold text-on-surface-variant">Sedang berjalan</span><span class="material-symbols-outlined text-[22px] text-primary">engineering</span></div>
                        <p data-partner-active class="mt-4 text-3xl font-extrabold tabular-nums text-on-surface">0</p>
                        <p class="mt-1 text-[11px] text-outline">Diterima sampai dikerjakan</p>
                    </article>
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between"><span class="text-xs font-semibold text-on-surface-variant">Pekerjaan selesai</span><span class="material-symbols-outlined text-[22px] text-emerald-700">task_alt</span></div>
                        <p data-partner-completed class="mt-4 text-3xl font-extrabold tabular-nums text-on-surface">0</p>
                        <p class="mt-1 text-[11px] text-outline">Dari seluruh pesanan demo</p>
                    </article>
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between"><span class="text-xs font-semibold text-on-surface-variant">Estimasi tarif selesai</span><span class="material-symbols-outlined text-[22px] text-secondary">payments</span></div>
                        <p data-partner-revenue class="mt-4 break-words text-xl font-extrabold tabular-nums text-primary sm:text-2xl">Rp 0</p>
                        <p class="mt-1 text-[11px] text-outline">Bukan pendapatan yang dibayar</p>
                    </article>
                </section>

                <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_315px]">
                    <div class="min-w-0 space-y-5">
                        {{-- Revenue chart: native HTML bars, no chart plugin --}}
                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="partner-chart-title">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div><h2 id="partner-chart-title" class="text-base font-extrabold sm:text-lg">Ringkasan tarif pekerjaan</h2><p class="mt-1 text-xs text-outline">7 hari terakhir · dihitung dari pesanan selesai</p></div>
                                <span class="rounded-full bg-surface-container-low px-2.5 py-1 text-[11px] font-semibold text-on-surface-variant">Simulasi, bukan omzet aktual</span>
                            </div>
                            <p data-partner-week-revenue class="mt-5 text-[27px] font-extrabold tracking-tight text-primary">Rp 0</p>
                            <p class="text-[11px] text-outline">Total estimasi tarif selesai dalam 7 hari</p>
                            <div data-partner-chart class="mt-6 grid h-44 grid-cols-7 items-end gap-2 border-b border-outline-variant/50 pb-2 sm:gap-4" role="img" aria-label="Grafik estimasi tarif pekerjaan tujuh hari terakhir"></div>
                            <div data-partner-chart-labels class="mt-2 grid grid-cols-7 gap-2 text-center text-[10px] font-medium text-outline sm:gap-4"></div>
                        </section>

                        {{-- Recent orders --}}
                        <section class="overflow-hidden rounded-2xl border border-outline-variant/40 bg-white shadow-sm" aria-labelledby="partner-orders-title">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant/30 px-5 py-4 sm:px-6">
                                <div><h2 id="partner-orders-title" class="text-base font-extrabold sm:text-lg">Pesanan terbaru</h2><p class="mt-1 text-xs text-outline"><span data-partner-total>0</span> pesanan milik {{ $provider['name'] }}</p></div>
                                <a href="{{ route('partner.orders.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline">Kelola pesanan<span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
                            </div>
                            <div data-partner-orders-list class="divide-y divide-outline-variant/30" aria-live="polite"></div>
                            <div data-partner-orders-empty class="px-5 py-12 text-center">
                                <span class="material-symbols-outlined text-[42px] text-outline">inbox</span>
                                <h3 class="mt-2 text-sm font-extrabold">Belum ada pesanan untuk mitra ini</h3>
                                <p class="mx-auto mt-1 max-w-sm text-xs leading-relaxed text-on-surface-variant">Buat pesanan dari akun Warga untuk Budi Teknik, atau tambahkan data simulasi.</p>
                                <button type="button" data-partner-seed class="mt-4 rounded-xl bg-primary px-4 py-2.5 text-xs font-bold text-white hover:bg-primary-container">Tambahkan 4 pesanan contoh</button>
                            </div>
                        </section>
                    </div>

                    <aside class="min-w-0 space-y-5" aria-label="Ketersediaan dan aktivitas">
                        {{-- Availability --}}
                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm" aria-labelledby="partner-status-title">
                            <div class="flex items-center justify-between gap-2"><h2 id="partner-status-title" class="text-base font-extrabold">Status ketersediaan</h2><span class="material-symbols-outlined text-[22px] text-primary">schedule</span></div>
                            <p class="mt-2 text-xs leading-relaxed text-on-surface-variant">Atur status kerja mitra untuk demonstrasi dashboard.</p>
                            <div class="mt-5 flex items-center justify-between gap-4 rounded-xl border border-outline-variant/40 bg-surface-container-low/60 p-3.5">
                                <div class="flex min-w-0 items-start gap-2.5"><span data-partner-availability-dot class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500"></span><div><strong data-partner-availability-label class="block text-sm">Menerima pesanan</strong><p class="mt-0.5 text-[11px] text-outline">Status simulasi</p></div></div>
                                <button type="button" data-partner-availability role="switch" aria-checked="true" aria-label="Ubah ketersediaan mitra" class="relative h-7 w-12 shrink-0 rounded-full bg-primary transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"><span data-partner-toggle-knob class="absolute left-1 top-1 h-5 w-5 translate-x-5 rounded-full bg-white shadow-sm transition-transform"></span></button>
                            </div>
                            <p class="mt-3 text-[11px] leading-relaxed text-outline">Status ini sekarang memblokir slot pemesanan baru saat dinonaktifkan di browser yang sama. <a href="{{ route('partner.schedule.index') }}" class="font-bold text-primary underline underline-offset-2">Atur jadwal & slot</a>.</p>
                        </section>

                        {{-- Recent activity --}}
                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm" aria-labelledby="partner-activity-title">
                            <div class="flex items-center justify-between gap-2"><h2 id="partner-activity-title" class="text-base font-extrabold">Aktivitas terbaru</h2><span class="material-symbols-outlined text-[21px] text-primary">history</span></div>
                            <p class="mt-1 text-xs text-outline">Perubahan status pesanan lokal</p>
                            <div data-partner-activities class="mt-4 space-y-4" aria-live="polite"></div>
                            <p data-partner-activity-empty class="mt-4 rounded-xl bg-surface-container-low px-3 py-4 text-center text-xs text-outline">Belum ada aktivitas mitra.</p>
                        </section>

                        {{-- Helper panel --}}
                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm">
                            <h2 class="text-sm font-extrabold">Coba dashboard</h2>
                            <p class="mt-2 text-xs leading-relaxed text-on-surface-variant">Tambahkan empat pesanan dummy untuk Budi Teknik, termasuk pesanan selesai agar grafik tarif terisi.</p>
                            <button type="button" data-partner-seed class="mt-4 w-full rounded-xl border border-primary/40 bg-primary-fixed px-4 py-3 text-xs font-bold text-primary hover:bg-secondary-fixed">Tambahkan 4 pesanan demo</button>
                            <a href="{{ route('partner.reviews.index') }}" class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border border-outline-variant/60 px-4 py-3 text-xs font-bold text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined text-[17px]">reviews</span> Kelola ulasan pelanggan</a>
                            <a href="{{ route('orders.index') }}" class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border border-outline-variant/60 px-4 py-3 text-xs font-bold text-on-surface-variant hover:bg-surface-container-low">Lihat data di Pesanan Warga <span class="material-symbols-outlined text-[16px]">open_in_new</span></a>
                        </section>
                    </aside>
                </div>
                <p class="text-[11px] leading-relaxed text-outline">Semua nama, nominal, aktivitas, dan status di halaman ini merupakan ilustrasi. Dashboard hanya untuk akun Mitra seed demo #{{ $provider['id'] }} yang terautentikasi Laravel; datanya tetap simulasi browser. Hindari data pribadi sungguhan.</p>
            </main>
        </div>
    </div>

    @include('partner.partials.mobile-nav')

    {{-- Template diisi JS memakai textContent (tidak menggunakan innerHTML dari data pesanan). --}}
    <template id="jw-partner-order-template">
        <article class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex min-w-0 items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-fixed text-primary"><span class="material-symbols-outlined">handyman</span></span>
                <div class="min-w-0"><p data-partner-order-service class="text-sm font-extrabold"></p><p data-partner-order-ref class="mt-0.5 break-all font-mono text-[10px] text-outline"></p><p data-partner-order-date class="mt-1 text-xs text-on-surface-variant"></p></div>
            </div>
            <div class="flex items-center justify-between gap-3 sm:justify-end"><div class="text-right"><span data-partner-order-status class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold"></span><p data-partner-order-price class="mt-1 text-xs font-bold text-primary"></p></div><button type="button" data-partner-view-order class="flex h-9 w-9 items-center justify-center rounded-lg border border-outline-variant/60 text-primary hover:bg-surface-container-low" aria-label="Lihat ringkasan pesanan"><span class="material-symbols-outlined text-[19px]">arrow_forward</span></button></div>
        </article>
    </template>
    <template id="jw-partner-activity-template">
        <div class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-primary"></span><div class="min-w-0 flex-1"><p data-partner-event-label class="text-xs font-semibold text-on-surface"></p><p data-partner-event-service class="mt-1 truncate text-[11px] text-on-surface-variant"></p><p data-partner-event-date class="mt-1 text-[10px] text-outline"></p></div></div>
    </template>

    <dialog id="jw-partner-order-dialog" class="w-[min(94vw,480px)] max-h-[85dvh] rounded-2xl border border-outline-variant/50 bg-white p-0 text-on-surface shadow-2xl backdrop:bg-on-surface/50" aria-labelledby="partner-dialog-title">
        <div class="flex items-center justify-between border-b border-outline-variant/40 px-5 py-4"><h2 id="partner-dialog-title" class="text-base font-extrabold">Rincian pesanan simulasi</h2><button type="button" data-partner-close-dialog class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container-low" aria-label="Tutup rincian"><span class="material-symbols-outlined">close</span></button></div>
        <dl class="grid grid-cols-[112px_minmax(0,1fr)] gap-x-3 gap-y-3 px-5 py-5 text-xs"><dt class="text-outline">Kode</dt><dd data-partner-dialog-ref class="break-all font-mono font-bold"></dd><dt class="text-outline">Layanan</dt><dd data-partner-dialog-service class="font-semibold"></dd><dt class="text-outline">Status</dt><dd data-partner-dialog-status class="font-semibold"></dd><dt class="text-outline">Jadwal</dt><dd data-partner-dialog-date class="font-semibold"></dd><dt class="text-outline">Area</dt><dd data-partner-dialog-area class="font-semibold"></dd><dt class="text-outline">Estimasi</dt><dd data-partner-dialog-price class="font-extrabold text-primary"></dd></dl>
        <div class="border-t border-outline-variant/40 px-5 py-4"><p class="mb-3 text-[11px] text-outline">Untuk menerima, menolak, atau memperbarui pekerjaan, buka halaman Kelola Pesanan.</p><div class="flex gap-2"><button type="button" data-partner-close-dialog class="flex-1 rounded-xl border border-outline-variant/70 px-4 py-2.5 text-sm font-bold">Tutup</button><a href="{{ route('partner.orders.index') }}" class="flex-1 rounded-xl bg-primary px-4 py-2.5 text-center text-sm font-bold text-white">Kelola pesanan</a></div></div>
    </dialog>

    <div data-partner-toast class="pointer-events-none fixed bottom-24 left-1/2 z-[70] hidden w-[min(92vw,440px)] -translate-x-1/2 rounded-xl bg-inverse-surface px-4 py-3 text-center text-xs font-semibold text-white shadow-xl lg:bottom-6" role="status" aria-live="polite"></div>
</div>
@endsection
