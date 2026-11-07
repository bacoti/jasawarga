@extends('layouts.app')

@section('title', 'Jadwal & Ketersediaan Mitra | JasaWarga')

@section('content')
<div id="jw-partner-schedule" class="min-h-screen bg-surface text-on-surface" data-partner-id="{{ $provider['id'] }}" data-partner-name="{{ $provider['name'] }}" data-allow-sunday="{{ str_starts_with($profile['hours'] ?? '', 'Setiap hari') ? '1' : '0' }}">
    <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        @include('partner.partials.sidebar')
        <div class="min-w-0">
            <header class="sticky top-0 z-30 hidden border-b border-outline-variant/40 bg-white/95 backdrop-blur lg:block">
                <div class="mx-auto flex h-[76px] max-w-[1380px] items-center justify-between gap-4 px-8 xl:px-10">
                    <div class="flex items-center gap-2 text-xs font-semibold text-outline"><span>Ruang Mitra</span><span class="material-symbols-outlined text-[16px]">chevron_right</span><span class="text-on-surface">Jadwal & ketersediaan</span></div>
                    <div class="flex items-center gap-4"><span class="text-xs text-outline">Waktu Indonesia Tengah (WITA)</span><span class="rounded-full bg-primary-fixed px-3 py-1.5 text-[11px] font-extrabold text-primary">Mitra · DEMO</span></div>
                </div>
            </header>
            <header class="sticky top-0 z-30 border-b border-outline-variant/40 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
                <div class="flex items-center justify-between gap-3"><div class="flex items-center gap-2.5"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-white"><span class="material-symbols-outlined">calendar_month</span></span><div><strong class="block text-base leading-tight text-primary">JasaWarga</strong><span class="text-[11px] text-outline">Jadwal Mitra · {{ $provider['name'] }}</span></div></div><a href="{{ route('partner.dashboard') }}" aria-label="Kembali ke dashboard" class="rounded-xl border border-outline-variant/60 bg-white p-2.5"><span class="material-symbols-outlined">space_dashboard</span></a></div>
            </header>

            <main class="mx-auto max-w-[1380px] space-y-6 px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-9 xl:px-10">
                <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="mb-2 text-[11px] font-extrabold uppercase tracking-[.14em] text-primary">Panel operasional / Mitra #{{ $provider['id'] }}</p><h1 class="text-[26px] font-extrabold tracking-tight sm:text-[30px]">Jadwal & ketersediaan</h1><p class="mt-1 max-w-2xl text-sm leading-relaxed text-on-surface-variant">Kelola jam kerja, slot kedatangan, dan hari libur. Pengaturan berlaku pada formulir pemesanan Warga di browser ini.</p></div><a href="{{ route('partner.orders.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-outline-variant/50 bg-white px-3.5 py-2.5 text-xs font-bold text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined text-[18px]">receipt_long</span>Kelola pesanan</a></div>
                <div class="flex items-start gap-3 rounded-xl border border-primary/15 bg-primary-fixed/35 px-4 py-3 text-xs leading-relaxed text-on-surface-variant" role="note"><span class="material-symbols-outlined mt-0.5 text-[20px] text-primary">info</span><p><strong class="text-on-surface">Jadwal simulasi.</strong> Setiap slot mengunci interval kedatangan 60 menit, bukan durasi pekerjaan sesungguhnya. Pesanan menunggu atau aktif memblokir slot; status selesai, ditolak, atau dibatalkan tidak memblokir. Tidak ada sinkronisasi antardevice atau jaminan anti-double-booking server.</p></div>

                <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Ringkasan jadwal">
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm"><div class="flex justify-between"><span class="text-xs font-semibold text-on-surface-variant">Hari operasional</span><span class="material-symbols-outlined text-primary">event_available</span></div><p data-sch-days-count class="mt-3 text-2xl font-extrabold">—</p><p class="mt-1 text-[11px] text-outline">Dalam satu minggu</p></article>
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm"><div class="flex justify-between"><span class="text-xs font-semibold text-on-surface-variant">Slot per hari</span><span class="material-symbols-outlined text-primary">schedule</span></div><p data-sch-slots-count class="mt-3 text-2xl font-extrabold">—</p><p class="mt-1 text-[11px] text-outline">Saat hari kerja normal</p></article>
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm"><div class="flex justify-between"><span class="text-xs font-semibold text-on-surface-variant">Reservasi mendatang</span><span class="material-symbols-outlined text-tertiary">pending_actions</span></div><p data-sch-upcoming-count class="mt-3 text-2xl font-extrabold">—</p><p class="mt-1 text-[11px] text-outline">Menunggu atau aktif</p></article>
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm"><div class="flex justify-between"><span class="text-xs font-semibold text-on-surface-variant">Tanggal libur</span><span class="material-symbols-outlined text-secondary">beach_access</span></div><p data-sch-holiday-count class="mt-3 text-2xl font-extrabold">—</p><p class="mt-1 text-[11px] text-outline">Tanggal yang disimpan</p></article>
                </section>

                <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5" aria-label="Status menerima pesanan">
                    <div class="flex items-start gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-fixed text-primary"><span class="material-symbols-outlined">toggle_on</span></span><div><h2 class="text-sm font-extrabold sm:text-base">Menerima pemesanan baru</h2><p data-sch-availability-label class="mt-0.5 text-xs text-on-surface-variant">Memuat status…</p></div></div>
                    <button type="button" data-sch-availability aria-pressed="true" class="rounded-xl border border-primary px-4 py-2.5 text-xs font-extrabold text-primary hover:bg-primary-fixed">Ubah status</button>
                </section>

                <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
                    <div class="min-w-0 space-y-5">
                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="sch-config-title">
                            <div class="mb-5 flex items-center justify-between gap-4"><div><h2 id="sch-config-title" class="text-lg font-extrabold">Pengaturan operasional</h2><p class="mt-1 text-xs text-outline">Simpan jadwal untuk diterapkan pada pemesanan baru.</p></div><span class="material-symbols-outlined text-primary">tune</span></div>
                            <form id="jw-sch-form" novalidate>
                                <fieldset class="mb-5"><legend class="mb-3 text-sm font-bold">Hari kerja</legend><div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                                    @foreach([1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 0 => 'Min'] as $day => $label)
                                        <label class="cursor-pointer"><input class="peer sr-only" type="checkbox" data-sch-day value="{{ $day }}"><span class="flex min-h-12 items-center justify-center rounded-xl border border-outline-variant/60 bg-white text-xs font-bold text-on-surface-variant transition peer-checked:border-primary peer-checked:bg-primary-fixed peer-checked:text-primary peer-focus-visible:ring-2 peer-focus-visible:ring-primary">{{ $label }}</span></label>
                                    @endforeach
                                </div></fieldset>
                                <div class="grid gap-4 border-t border-outline-variant/30 pt-5 sm:grid-cols-2"><div><label for="sch-open" class="mb-1.5 block text-sm font-semibold">Mulai bekerja</label><input id="sch-open" data-sch-open type="time" step="900" required class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></div><div><label for="sch-close" class="mb-1.5 block text-sm font-semibold">Selesai bekerja</label><input id="sch-close" data-sch-close type="time" step="900" required class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></div><div><label for="sch-break-start" class="mb-1.5 block text-sm font-semibold">Mulai istirahat <span class="font-normal text-outline">(opsional)</span></label><input id="sch-break-start" data-sch-break-start type="time" step="900" class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></div><div><label for="sch-break-end" class="mb-1.5 block text-sm font-semibold">Selesai istirahat <span class="font-normal text-outline">(opsional)</span></label><input id="sch-break-end" data-sch-break-end type="time" step="900" class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></div></div>
                                <div class="mt-5 border-t border-outline-variant/30 pt-5"><label for="sch-new-slot" class="block text-sm font-bold">Slot kedatangan (WITA)</label><p class="mb-3 mt-1 text-xs leading-relaxed text-outline">Setiap slot memakai jendela 60 menit. Hindari jam istirahat dan beri jarak minimal 60 menit antar slot.</p><div class="flex items-center gap-2"><input id="sch-new-slot" data-sch-new-slot type="time" step="900" aria-label="Jam slot baru" class="min-w-0 flex-1 rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"><button type="button" data-sch-add-slot class="inline-flex items-center gap-1 rounded-xl border border-primary px-4 py-2.5 text-xs font-bold text-primary hover:bg-primary-fixed"><span class="material-symbols-outlined text-[17px]">add</span>Tambah</button></div><div data-sch-slots-list class="mt-3 flex min-h-10 flex-wrap gap-2" aria-live="polite"></div></div>
                                <p data-sch-form-error role="alert" class="mt-4 hidden rounded-xl bg-error-container px-4 py-3 text-xs font-semibold text-on-error-container"></p>
                                <div class="mt-5 flex items-center justify-between gap-3 border-t border-outline-variant/30 pt-5"><span class="max-w-sm text-[11px] leading-relaxed text-outline">Perubahan tidak boleh menghilangkan slot pesanan yang masih aktif/menunggu.</span><button type="submit" class="shrink-0 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white hover:bg-primary-container">Simpan jadwal</button></div>
                            </form>
                        </section>

                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="sch-holidays-title">
                            <div class="mb-4"><h2 id="sch-holidays-title" class="text-lg font-extrabold">Tanggal libur & cuti</h2><p class="mt-1 text-xs text-outline">Tidak menerima booking baru pada tanggal yang ditandai.</p></div>
                            <form id="jw-sch-holiday-form" class="grid items-end gap-3 sm:grid-cols-[160px_1fr_auto]"><div><label for="sch-holiday-date" class="mb-1.5 block text-xs font-bold">Tanggal</label><input id="sch-holiday-date" data-sch-holiday-date type="date" required class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></div><div><label for="sch-holiday-label" class="mb-1.5 block text-xs font-bold">Keterangan</label><input id="sch-holiday-label" data-sch-holiday-label type="text" maxlength="100" placeholder="Cuti keluarga / libur" class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></div><button type="submit" class="rounded-xl bg-primary px-4 py-3 text-xs font-bold text-white">Tambahkan</button></form>
                            <p data-sch-holiday-error class="mt-3 hidden text-xs font-semibold text-error" role="alert"></p>
                            <div data-sch-holidays-list class="mt-4 space-y-2" aria-live="polite"></div>
                        </section>
                    </div>

                    <div class="min-w-0 space-y-5">
                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="sch-calendar-title">
                            <div class="flex items-center justify-between gap-2"><div><h2 id="sch-calendar-title" class="text-base font-extrabold">Kalender pekerjaan</h2><p class="mt-1 text-[11px] text-outline">Pilih tanggal untuk melihat jadwal</p></div><div class="flex gap-1"><button type="button" data-sch-prev-month aria-label="Bulan sebelumnya" class="rounded-lg border border-outline-variant/50 p-1.5 hover:bg-surface-container-low"><span class="material-symbols-outlined">chevron_left</span></button><button type="button" data-sch-next-month aria-label="Bulan berikutnya" class="rounded-lg border border-outline-variant/50 p-1.5 hover:bg-surface-container-low"><span class="material-symbols-outlined">chevron_right</span></button></div></div>
                            <p data-sch-month class="mb-4 mt-4 text-sm font-bold capitalize"></p>
                            <div class="mb-2 grid grid-cols-7 text-center text-[10px] font-bold text-outline"><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span></div>
                            <div data-sch-calendar class="grid grid-cols-7 gap-1" aria-label="Kalender pemesanan" aria-live="polite"></div>
                            <div class="mt-4 flex flex-wrap gap-3 text-[10px] text-outline"><span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-primary"></span>Reservasi</span><span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-tertiary"></span>Libur</span><span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-outline"></span>Nonoperasional</span></div>
                        </section>
                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm" aria-labelledby="sch-day-title"><h2 id="sch-day-title" class="text-base font-extrabold" data-sch-selected-title>Detail tanggal</h2><p data-sch-day-note class="mb-4 mt-1 text-xs text-outline">Memuat ketersediaan…</p><div data-sch-day-orders class="space-y-2"></div><h3 class="mb-2 mt-4 border-t border-outline-variant/30 pt-4 text-sm font-bold">Slot waktu kedatangan</h3><div data-sch-day-slots class="grid grid-cols-2 gap-2"></div></section>
                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm" aria-labelledby="sch-future-title"><h2 id="sch-future-title" class="text-base font-extrabold">Pekerjaan mendatang</h2><p class="mb-4 mt-1 text-xs text-outline">Reservasi aktif dan menunggu milik mitra ini</p><div data-sch-upcoming class="space-y-2"></div></section>
                    </div>
                </div>
            </main>
        </div>
        @include('partner.partials.mobile-nav')
    </div>
    <div data-sch-toast role="status" aria-live="polite" class="pointer-events-none fixed bottom-24 left-1/2 z-[70] hidden w-[min(92vw,440px)] -translate-x-1/2 rounded-xl bg-inverse-surface px-4 py-3 text-center text-sm font-semibold text-white shadow-xl lg:bottom-6"></div>
    <noscript><p class="fixed bottom-24 left-4 right-4 z-50 rounded-xl bg-error-container p-4 text-sm text-on-error-container">JavaScript harus aktif untuk mengelola jadwal.</p></noscript>
</div>
@endsection
