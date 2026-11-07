@extends('layouts.app')

@section('title', 'Konfirmasi Simulasi Pemesanan | JasaWarga')

@section('content')
<div id="jw-booking-success" class="min-h-screen bg-surface text-on-surface" data-home-url="{{ route('home') }}" data-categories-url="{{ route('categories.index') }}">
    @include('components.site-navbar')
    <main class="mx-auto max-w-2xl px-4 py-10 sm:px-6 sm:py-14">
        <section id="jw-success-content" class="hidden overflow-hidden rounded-2xl border border-outline-variant/40 bg-white shadow-sm" aria-labelledby="jw-success-title">
            <div class="border-b border-outline-variant/30 bg-primary-fixed/50 px-5 py-8 text-center sm:px-8">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white text-primary shadow-sm"><span class="material-symbols-outlined text-[38px]">check_circle</span></div>
                <p class="mt-3 text-[11px] font-extrabold uppercase tracking-wider text-primary">Langkah 2 dari 2 · Selesai</p>
                <h1 id="jw-success-title" class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Simulasi pemesanan berhasil</h1>
                <p class="mx-auto mt-2 max-w-lg text-sm leading-relaxed text-on-surface-variant">Ringkasan tersimpan sebagai riwayat simulasi di browser ini. Tidak ada transaksi, pembayaran, atau permintaan sungguhan kepada mitra.</p>
            </div>

            <div class="space-y-5 px-5 py-6 sm:px-8">
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-surface-container-low p-4">
                    <div><p class="text-xs text-outline">Kode referensi simulasi</p><p class="mt-1 font-mono text-base font-extrabold text-primary" data-booking-result="reference">—</p></div>
                    <span class="rounded-full bg-secondary-fixed px-2.5 py-1 text-[11px] font-bold text-on-secondary-fixed">BUKAN PESANAN AKTIF</span>
                </div>
                <div class="space-y-3">
                    <h2 class="text-base font-extrabold">Informasi layanan</h2>
                    <dl class="grid grid-cols-[110px_minmax(0,1fr)] gap-x-3 gap-y-2 text-sm sm:grid-cols-[150px_minmax(0,1fr)]">
                        <dt class="text-on-surface-variant">Mitra</dt><dd class="font-semibold" data-booking-result="provider">—</dd>
                        <dt class="text-on-surface-variant">Jenis layanan</dt><dd class="font-semibold" data-booking-result="service">—</dd>
                        <dt class="text-on-surface-variant">Tanggal & jam</dt><dd class="font-semibold" data-booking-result="schedule">—</dd>
                        <dt class="text-on-surface-variant">Nama pemesan</dt><dd class="break-words" data-booking-result="name">—</dd>
                        <dt class="text-on-surface-variant">WhatsApp</dt><dd data-booking-result="phone">—</dd>
                        <dt class="text-on-surface-variant">Area</dt><dd data-booking-result="area">—</dd>
                        <dt class="text-on-surface-variant">Alamat</dt><dd class="break-words" data-booking-result="address">—</dd>
                        <dt class="text-on-surface-variant">Detail pekerjaan</dt><dd class="whitespace-pre-wrap break-words" data-booking-result="description">—</dd>
                    </dl>
                </div>
                <div class="border-t border-outline-variant/30 pt-4">
                    <div class="flex items-end justify-between gap-3"><span class="text-sm font-bold">Total estimasi</span><strong class="text-2xl font-extrabold text-primary" data-booking-result="total">—</strong></div>
                    <p class="mt-2 text-xs leading-relaxed text-outline">Harga dummy, belum termasuk material atau pekerjaan tambahan. Biaya platform ditampilkan Rp 0 hanya untuk simulasi.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <button type="button" data-booking-copy class="flex flex-1 items-center justify-center gap-2 rounded-xl border border-outline-variant/60 px-4 py-3 text-sm font-bold hover:bg-surface-container-low"><span class="material-symbols-outlined text-[18px]">content_copy</span> Salin ringkasan</button>
                    <a href="{{ route('orders.index') }}" class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white hover:bg-primary-container">Lihat Pesanan Saya <span class="material-symbols-outlined text-[18px]">arrow_forward</span></a>
                </div>
                <button type="button" data-booking-clear class="w-full text-center text-xs font-semibold text-outline underline underline-offset-4 hover:text-primary">Hapus ringkasan sesi ini (riwayat tetap tersimpan)</button>
                <p data-booking-copy-status role="status" aria-live="polite" class="text-center text-xs text-primary"></p>
            </div>
        </section>

        <section id="jw-success-empty" class="hidden rounded-2xl border border-outline-variant/40 bg-white px-6 py-12 text-center shadow-sm" aria-labelledby="jw-success-empty-title">
            <span class="material-symbols-outlined text-[48px] text-outline">info</span>
            <h1 id="jw-success-empty-title" class="mt-3 text-xl font-extrabold">Belum ada ringkasan simulasi</h1>
            <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-on-surface-variant">Halaman ini hanya menampilkan pemesanan yang baru saja disimulasikan di tab browser ini. Mulai dari profil mitra untuk mencoba.</p>
            <a href="{{ route('orders.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white">Lihat Pesanan Saya <span class="material-symbols-outlined text-[18px]">arrow_forward</span></a>
        </section>
    </main>
</div>
@endsection
