@extends('layouts.app')
@section('title', 'Dashboard Admin | JasaWarga')

@section('content')
<div id="jw-admin-dashboard" class="min-h-screen bg-[#F6F8FC] text-on-surface">
    <script type="application/json" id="jw-admin-seed">@json(['providers' => $providers, 'profiles' => $profiles, 'categories' => $categories])</script>

    <div class="flex min-h-screen">
        @include('admin.partials.sidebar')

        <div class="min-w-0 flex-1">
            {{-- Header desktop dan mobile --}}
            <header class="sticky top-0 z-30 border-b border-outline-variant/35 bg-white/95 backdrop-blur">
                <div class="mx-auto flex min-h-[72px] max-w-[1500px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary text-white lg:hidden"><span class="material-symbols-outlined">shield_person</span></div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-[.16em] text-outline">JasaWarga / Administrasi</p>
                            <p class="truncate text-sm font-extrabold sm:text-base">Dashboard Overview</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <form data-admin-global-search class="hidden items-center gap-2 rounded-xl border border-outline-variant/60 bg-[#F7F9FC] px-3 md:flex" role="search">
                            <span class="material-symbols-outlined text-[19px] text-outline">search</span>
                            <input data-admin-global-input aria-label="Cari kode atau mitra pada pesanan" class="w-[165px] border-0 bg-transparent py-2.5 text-xs focus:ring-0 xl:w-[240px]" placeholder="Cari pesanan lokal..." type="search">
                            <button class="font-bold text-primary" type="submit" aria-label="Cari pesanan"><span class="material-symbols-outlined text-[19px]">arrow_forward</span></button>
                        </form>
                        <button type="button" data-admin-refresh aria-label="Perbarui data dashboard" title="Perbarui dari browser" class="flex h-10 w-10 items-center justify-center rounded-xl border border-outline-variant/50 bg-white text-on-surface-variant hover:bg-surface-container-low"><span class="material-symbols-outlined text-[21px]">refresh</span></button>
                        <a href="{{ route('partner.dashboard') }}" class="hidden rounded-xl border border-outline-variant/60 px-3 py-2.5 text-xs font-bold text-on-surface-variant hover:bg-surface-container-low sm:inline-flex">Mode Mitra</a>
                        <span title="Admin terautentikasi; data dashboard masih demo" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-fixed text-sm font-extrabold text-primary">AD</span>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-[1500px] space-y-6 px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-8">
                {{-- Intro, jelas beda sumber data --}}
                <section class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-primary"></span><span class="text-[10px] font-bold uppercase tracking-[.17em] text-primary">Pusat kendali · prototipe</span></div>
                        <h1 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-[30px]">Ringkasan platform</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-on-surface-variant">Pantau aktivitas yang tersimpan di browser ini dan data katalog fiktif. Belum ada sinkronisasi antarperangkat ataupun akses data server.</p>
                    </div>
                    <button type="button" data-admin-seed class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-3 text-xs font-bold text-white shadow-sm transition hover:bg-primary-container"><span class="material-symbols-outlined text-[18px]">add_circle</span>Tambahkan 3 pesanan demo</button>
                </section>

                <div class="flex flex-wrap gap-x-5 gap-y-2 rounded-xl border border-outline-variant/35 bg-white px-4 py-3 text-xs text-on-surface-variant"><a href="{{ route('admin.citizens') }}" class="font-bold text-primary hover:underline">Kelola Warga</a><span>Nonaktif (demo): <strong data-admin-citizens-inactive>0</strong></span><a href="{{ route('admin.partners') }}" class="font-bold text-primary hover:underline">Kelola Mitra &amp; Verifikasi</a><span>Ditolak (demo): <strong data-admin-partners-rejected>0</strong></span><a href="{{ route('admin.categories') }}" class="font-bold text-primary hover:underline">Kategori Jasa</a><span>Aktif: <strong data-admin-categories-active>0</strong></span><a href="{{ route('admin.orders') }}" class="font-bold text-primary hover:underline">Monitoring Pesanan</a><a href="{{ route('admin.reviews') }}" class="font-bold text-primary hover:underline">Ulasan &amp; Pengaduan</a><span>Kasus aktif: <strong data-admin-feedback-open>0</strong></span></div>

                <div data-admin-storage-warning role="alert" class="hidden rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"></div>

                <section aria-label="Sumber data" class="grid gap-3 md:grid-cols-2">
                    <div class="flex items-start gap-3 rounded-xl border border-[#CBDDEE] bg-[#EDF5FB] p-4"><span class="material-symbols-outlined text-primary">hard_drive</span><div><p class="text-sm font-extrabold text-on-surface">Data browser</p><p class="mt-1 text-xs leading-relaxed text-on-surface-variant">Akun Warga, pesanan, ulasan: dibaca dari localStorage pada browser dan origin ini.</p></div></div>
                    <div class="flex items-start gap-3 rounded-xl border border-outline-variant/40 bg-white p-4"><span class="material-symbols-outlined text-secondary">inventory_2</span><div><p class="text-sm font-extrabold text-on-surface">Data contoh katalog</p><p class="mt-1 text-xs leading-relaxed text-on-surface-variant">Jumlah mitra dan kategori berasal dari konfigurasi Laravel. Layanan aktif menghitung katalog contoh dengan penyesuaian lokal.</p></div></div>
                </section>

                {{-- KPI cards --}}
                <section aria-label="Statistik utama" class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                    <article class="rounded-2xl border border-outline-variant/35 bg-white p-4 shadow-sm sm:p-5"><div class="flex items-center justify-between"><p class="text-xs font-semibold text-on-surface-variant">Akun Warga</p><span class="material-symbols-outlined text-[21px] text-primary">groups</span></div><p data-admin-kpi="citizens" class="mt-4 text-[32px] font-extrabold leading-none tracking-tight">0</p><p class="mt-3 text-[10px] font-semibold uppercase tracking-wide text-primary">Data browser</p></article>
                    <article class="rounded-2xl border border-outline-variant/35 bg-white p-4 shadow-sm sm:p-5"><div class="flex items-center justify-between"><p class="text-xs font-semibold text-on-surface-variant">Mitra dalam katalog</p><span class="material-symbols-outlined text-[21px] text-secondary">handyman</span></div><p data-admin-kpi="providers" class="mt-4 text-[32px] font-extrabold leading-none tracking-tight">0</p><p class="mt-3 text-[10px] font-semibold uppercase tracking-wide text-outline">Data contoh</p></article>
                    <article class="rounded-2xl border border-outline-variant/35 bg-white p-4 shadow-sm sm:p-5"><div class="flex items-center justify-between"><p class="text-xs font-semibold text-on-surface-variant">Total pesanan</p><span class="material-symbols-outlined text-[21px] text-primary">receipt_long</span></div><p data-admin-kpi="orders" class="mt-4 text-[32px] font-extrabold leading-none tracking-tight">0</p><p class="mt-3 text-[10px] font-semibold uppercase tracking-wide text-primary">Data browser</p></article>
                    <article class="rounded-2xl border border-outline-variant/35 bg-white p-4 shadow-sm sm:p-5"><div class="flex items-center justify-between"><p class="text-xs font-semibold text-on-surface-variant">Layanan aktif</p><span class="material-symbols-outlined text-[21px] text-secondary">construction</span></div><p data-admin-kpi="services" class="mt-4 text-[32px] font-extrabold leading-none tracking-tight">0</p><p class="mt-3 text-[10px] font-semibold uppercase tracking-wide text-outline">Katalog + override lokal</p></article>
                </section>

                {{-- Primary split content --}}
                <div class="grid gap-5 xl:grid-cols-[minmax(0,1.65fr)_minmax(310px,1fr)]">
                    <section id="jw-admin-chart-section" aria-labelledby="jw-admin-chart-title" class="min-w-0 scroll-mt-24 rounded-2xl border border-outline-variant/35 bg-white p-5 shadow-sm sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 id="jw-admin-chart-title" class="text-base font-extrabold sm:text-lg">Aktivitas pesanan · 7 hari</h2><p class="mt-1 text-xs text-outline">Jumlah pesanan di browser, berdasarkan tanggal lokal</p></div><div class="flex gap-1 rounded-xl border border-outline-variant/50 bg-surface-container-low p-1" role="group" aria-label="Pilih grafik"><button type="button" data-admin-chart-mode="created" aria-pressed="true" class="rounded-lg bg-primary px-2.5 py-1.5 text-[11px] font-bold text-white">Dibuat</button><button type="button" data-admin-chart-mode="completed" aria-pressed="false" class="rounded-lg bg-white px-2.5 py-1.5 text-[11px] font-bold text-on-surface-variant">Selesai</button></div></div>
                        <div class="mt-5 flex items-end gap-5"><div><p data-admin-chart-created class="text-2xl font-extrabold text-primary">0</p><p class="text-[11px] text-outline">Pesanan dibuat</p></div><div class="border-l border-outline-variant/50 pl-5"><p data-admin-chart-completed class="text-2xl font-extrabold">0</p><p class="text-[11px] text-outline">Pekerjaan selesai</p></div></div>
                        <div data-admin-chart class="mt-7 grid h-[195px] grid-cols-7 items-end gap-2 sm:gap-4" role="img" aria-label="Diagram jumlah pesanan per hari"></div>
                        <p class="mt-5 border-t border-outline-variant/30 pt-4 text-xs leading-relaxed text-outline">Grafik hanya menghitung pesanan yang dibuat lewat prototipe ini, bukan data produksi.</p>
                    </section>
                    <section class="rounded-2xl border border-outline-variant/35 bg-[#142D45] p-5 text-white shadow-sm sm:p-6" aria-labelledby="jw-admin-health-title">
                        <p class="text-[10px] font-bold uppercase tracking-[.16em] text-blue-200">Operasional browser</p><h2 id="jw-admin-health-title" class="mt-2 text-lg font-extrabold">Status pekerjaan</h2>
                        <div class="mt-6 grid grid-cols-2 gap-3"><div class="rounded-xl border border-white/15 bg-white/10 p-4"><span class="material-symbols-outlined text-[20px] text-[#9FCFFF]">schedule</span><p data-admin-kpi="waiting" class="mt-2 text-2xl font-extrabold">0</p><p class="mt-1 text-xs text-[#C3D5E8]">Menunggu</p></div><div class="rounded-xl border border-white/15 bg-white/10 p-4"><span class="material-symbols-outlined text-[20px] text-[#9FCFFF]">directions_run</span><p data-admin-kpi="active" class="mt-2 text-2xl font-extrabold">0</p><p class="mt-1 text-xs text-[#C3D5E8]">Dalam proses</p></div><div class="rounded-xl border border-white/15 bg-white/10 p-4"><span class="material-symbols-outlined text-[20px] text-emerald-200">check_circle</span><p data-admin-kpi="completed" class="mt-2 text-2xl font-extrabold">0</p><p class="mt-1 text-xs text-[#C3D5E8]">Selesai</p></div><div class="rounded-xl border border-white/15 bg-white/10 p-4"><span class="material-symbols-outlined text-[20px] text-amber-200">star</span><p data-admin-kpi="reviews" class="mt-2 text-2xl font-extrabold">0</p><p class="mt-1 text-xs text-[#C3D5E8]">Ulasan lokal</p></div></div>
                        <div class="mt-5 border-t border-white/15 pt-4"><p class="text-xs text-[#C3D5E8]">Total estimasi tarif pekerjaan selesai</p><p data-admin-estimated-tariff class="mt-2 text-[25px] font-extrabold tracking-tight">Rp 0</p><p class="mt-1 text-[11px] text-[#A9BED3]">Bukan pendapatan yang dibayarkan</p></div>
                    </section>
                </div>

                {{-- Orders and verification --}}
                <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.65fr)_minmax(310px,1fr)]">
                    <section id="jw-admin-orders" aria-labelledby="jw-admin-orders-title" class="min-w-0 scroll-mt-24 overflow-hidden rounded-2xl border border-outline-variant/35 bg-white shadow-sm">
                        <div class="p-5 pb-4 sm:p-6 sm:pb-4"><div class="flex items-center justify-between gap-4"><div><h2 id="jw-admin-orders-title" class="text-base font-extrabold sm:text-lg">Pesanan terbaru</h2><p class="mt-1 text-xs text-outline">Menampilkan <span data-admin-orders-limit>0</span> dari <span data-admin-orders-matched>0</span> pesanan sesuai filter</p></div><a href="{{ route('orders.index') }}" class="shrink-0 text-xs font-bold text-primary hover:underline">Pesanan Saya <span class="material-symbols-outlined text-[14px] align-middle">open_in_new</span></a></div>
                            <div class="mt-4 flex flex-col gap-2 sm:flex-row"><label class="relative min-w-0 flex-1"><span class="sr-only">Cari pesanan</span><span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[19px] text-outline">search</span><input data-admin-search class="w-full rounded-xl border-outline-variant/60 bg-white py-2.5 pl-10 pr-3 text-xs focus:border-primary focus:ring-primary" type="search" placeholder="Kode, mitra, layanan, kecamatan..."></label><select data-admin-status aria-label="Filter status pesanan" class="rounded-xl border-outline-variant/60 bg-white py-2.5 text-xs focus:border-primary focus:ring-primary"><option value="all">Semua status</option><option value="pending">Menunggu</option><option value="accepted">Diterima</option><option value="enroute">Dalam perjalanan</option><option value="working">Dikerjakan</option><option value="completed">Selesai</option><option value="cancelled">Dibatalkan</option><option value="rejected">Ditolak</option></select><button type="button" data-admin-reset class="rounded-xl border border-outline-variant/60 px-3 py-2.5 text-xs font-bold text-primary hover:bg-surface-container-low">Reset</button></div>
                        </div>
                        <div class="w-full overflow-x-auto"><table class="min-w-[680px] w-full border-collapse text-left"><thead class="bg-[#F7F9FC] text-[10px] font-extrabold uppercase tracking-[.07em] text-outline"><tr><th scope="col" class="px-4 py-3">Kode / Tanggal</th><th scope="col" class="px-4 py-3">Layanan / Mitra</th><th scope="col" class="px-4 py-3">Estimasi</th><th scope="col" class="px-4 py-3">Status</th></tr></thead><tbody data-admin-orders-list></tbody></table></div>
                        <div data-admin-orders-empty class="hidden px-5 py-12 text-center"><span class="material-symbols-outlined text-[36px] text-outline">inbox</span><h3 class="mt-2 text-sm font-bold">Belum ada pesanan sesuai filter</h3><p class="mt-1 text-xs text-outline">Buat simulasi pemesanan atau tambahkan tiga pesanan demo.</p></div>
                        <div class="border-t border-outline-variant/25 px-5 py-3 text-[11px] text-outline">Data lokal baca-saja · tanpa alamat lengkap atau nomor kontak</div>
                    </section>
                    <div class="space-y-5">
                        <section aria-labelledby="jw-admin-verification-title" class="rounded-2xl border border-outline-variant/35 bg-white p-5 shadow-sm sm:p-6"><div class="flex items-center justify-between gap-3"><h2 id="jw-admin-verification-title" class="text-base font-extrabold">Verifikasi Mitra</h2><span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-800"><span data-admin-kpi="unverified">0</span> contoh</span></div><p class="mt-2 text-xs leading-relaxed text-on-surface-variant">Status verifikasi dari katalog contoh + keputusan Admin lokal. <strong>Tidak ada pengajuan dokumen asli.</strong></p><ul data-admin-verification-list class="mt-3"></ul><p data-admin-verification-empty class="mt-3 hidden rounded-xl bg-surface-container-low p-4 text-xs text-outline">Semua profil contoh telah ditandai terverifikasi.</p><a href="{{ route('admin.partners') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline">Kelola verifikasi Mitra <span class="material-symbols-outlined text-[16px]">arrow_forward</span></a></section>
                        <section aria-labelledby="jw-admin-activity-title" class="rounded-2xl border border-outline-variant/35 bg-white p-5 shadow-sm sm:p-6"><div class="flex items-center justify-between gap-3"><h2 id="jw-admin-activity-title" class="text-base font-extrabold">Aktivitas terbaru</h2><span class="material-symbols-outlined text-[20px] text-primary">history</span></div><p class="mt-1 text-xs text-outline">Kejadian di data lokal browser</p><ul data-admin-activity-list class="mt-3"></ul><p data-admin-activity-empty class="mt-4 hidden rounded-xl bg-surface-container-low p-4 text-xs text-outline">Belum ada aktivitas yang tercatat.</p></section>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#CBDDEE] bg-white px-5 py-4"><div><h3 class="text-sm font-extrabold">Laporan dan pengaturan Admin siap digunakan</h3><p class="mt-1 text-xs text-on-surface-variant">Analitik periode, ekspor CSV anonim, preferensi Admin, serta mode demo.</p></div><div class="flex flex-wrap gap-2"><a href="{{ route('admin.reports') }}" class="rounded-xl bg-primary px-4 py-2.5 text-xs font-bold text-white">Buka laporan</a><a href="{{ route('admin.settings') }}" class="rounded-xl border border-outline-variant/60 px-4 py-2.5 text-xs font-bold text-primary">Pengaturan</a></div></div>
                <footer class="border-t border-outline-variant/30 pt-5 text-xs leading-relaxed text-outline">JasaWarga Admin UI · Prototipe frontend. Status Warga dan verifikasi Mitra dapat diubah dalam simulasi localStorage, dan dashboard kini dilindungi login role Admin melalui Laravel. Verifikasi identitas dan transaksi masih belum diimplementasikan.</footer>
            </main>
        </div>
    </div>

    @include('admin.partials.mobile-nav')
    <dialog id="jw-admin-info-dialog" aria-labelledby="jw-admin-info-title" class="w-[min(92vw,450px)] rounded-2xl border border-outline-variant/40 bg-white p-0 shadow-2xl backdrop:bg-on-surface/55"><div class="flex items-center justify-between border-b border-outline-variant/30 px-5 py-4"><h2 id="jw-admin-info-title" class="text-base font-extrabold">Informasi</h2><button type="button" data-admin-info-close class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container-low" aria-label="Tutup dialog"><span class="material-symbols-outlined">close</span></button></div><p id="jw-admin-info-body" class="px-5 py-5 text-sm leading-relaxed text-on-surface-variant"></p><div class="px-5 pb-5"><button type="button" data-admin-info-close class="w-full rounded-xl bg-primary py-3 text-sm font-bold text-white">Mengerti</button></div></dialog>
</div>
@endsection
