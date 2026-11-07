@extends('layouts.app')

@section('title', 'Pengaturan Akun Mitra | JasaWarga')

@section('content')
<div id="jw-partner-account" class="min-h-screen bg-surface text-on-surface" data-partner-id="{{ $provider['id'] }}">
    <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        @include('partner.partials.sidebar')

        <div class="min-w-0">
            {{-- Header desktop --}}
            <header class="sticky top-0 z-30 hidden border-b border-outline-variant/40 bg-white/95 backdrop-blur lg:block">
                <div class="mx-auto flex h-[76px] max-w-[1380px] items-center justify-between gap-4 px-8 xl:px-10">
                    <nav class="flex items-center gap-2 text-xs font-semibold text-outline" aria-label="Breadcrumb">
                        <a href="{{ route('partner.dashboard') }}" class="hover:text-primary">Ruang Mitra</a>
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                        <span class="text-on-surface">Pengaturan akun</span>
                    </nav>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-primary-fixed px-3 py-1.5 text-[11px] font-extrabold text-primary">Mode Mitra · DEMO</span>
                        <a href="{{ route('providers.show', ['id' => $provider['id']]) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">Lihat profil publik <span class="material-symbols-outlined text-[16px]">open_in_new</span></a>
                    </div>
                </div>
            </header>

            {{-- Header mobile --}}
            <header class="sticky top-0 z-30 border-b border-outline-variant/40 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
                <div class="mx-auto flex max-w-2xl items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <a href="{{ route('partner.dashboard') }}" aria-label="Kembali ke Dashboard Mitra" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-container-low text-on-surface"><span class="material-symbols-outlined">arrow_back</span></a>
                        <div><h1 class="text-base font-extrabold leading-tight">Pengaturan Akun</h1><p class="text-[11px] text-outline">Ruang Mitra JasaWarga</p></div>
                    </div>
                    <a href="{{ route('partner.profile.index') }}" aria-label="Kelola profil usaha" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-outline-variant/60 bg-white text-primary"><span class="material-symbols-outlined">badge</span></a>
                </div>
            </header>

            <main class="mx-auto max-w-[1380px] space-y-6 px-4 pb-32 pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-9 xl:px-10">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div><p class="text-[11px] font-extrabold uppercase tracking-[.14em] text-primary">Akun & preferensi / Mitra #{{ $provider['id'] }}</p><h1 class="mt-2 text-[26px] font-extrabold tracking-tight sm:text-[30px]">Pengaturan Akun Mitra</h1><p class="mt-1 text-sm text-on-surface-variant">Kelola informasi pemilik, privasi, notifikasi, dan preferensi operasional.</p></div>
                    <a href="{{ route('partner.profile.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-outline-variant/70 bg-white px-4 py-2.5 text-xs font-bold text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined text-[18px]">storefront</span>Edit profil usaha</a>
                </div>

                <div class="flex items-start gap-3 rounded-xl border border-primary/15 bg-primary-fixed/35 px-4 py-3 text-xs leading-relaxed text-on-surface-variant" role="note">
                    <span class="material-symbols-outlined mt-0.5 text-[19px] text-primary">info</span>
                    <p><strong class="text-on-surface">Pengaturan simulasi.</strong> Informasi hanya tersimpan di browser ini. Login Mitra kini memerlukan sesi Laravel, tetapi preferensi, notifikasi dan pergantian password pada halaman ini masih simulasi browser. Gunakan data fiktif.</p>
                </div>

                <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
                    <div class="min-w-0 space-y-5">
                        {{-- Identitas pemilik --}}
                        <section class="rounded-2xl border border-outline-variant/40 bg-white shadow-sm" aria-labelledby="ps-identity-title">
                            <div class="flex items-start justify-between gap-3 border-b border-outline-variant/30 p-5 sm:px-6">
                                <div><h2 id="ps-identity-title" class="flex items-center gap-2 text-base font-extrabold"><span class="material-symbols-outlined text-[21px] text-primary">person</span>Informasi pemilik akun</h2><p class="mt-1 text-xs text-outline">Data kontak internal demo, terpisah dari profil usaha publik.</p></div>
                                <span class="rounded-md bg-surface-container-low px-2 py-1 text-[10px] font-bold text-on-surface-variant">Pribadi · Demo</span>
                            </div>
                            <form data-ps-identity-form class="space-y-4 p-5 sm:p-6" novalidate>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2"><label for="ps-owner" class="mb-1.5 block text-sm font-semibold">Nama pemilik / pengelola <span class="text-error">*</span></label><input id="ps-owner" name="ownerName" type="text" maxlength="80" minlength="3" required autocomplete="name" class="w-full rounded-xl border-outline-variant/70 bg-white text-sm focus:border-primary focus:ring-primary" placeholder="Nama pengelola usaha"></div>
                                    <div><label for="ps-email" class="mb-1.5 block text-sm font-semibold">Email akun <span class="text-error">*</span></label><input id="ps-email" name="email" type="email" maxlength="120" required autocomplete="email" class="w-full rounded-xl border-outline-variant/70 bg-white text-sm focus:border-primary focus:ring-primary" placeholder="nama@contoh.test"></div>
                                    <div><label for="ps-phone" class="mb-1.5 block text-sm font-semibold">WhatsApp pengelola (opsional)</label><input id="ps-phone" name="phone" type="tel" maxlength="18" autocomplete="tel" inputmode="tel" class="w-full rounded-xl border-outline-variant/70 bg-white text-sm focus:border-primary focus:ring-primary" placeholder="08xxxxxxxxxx"></div>
                                </div>
                                <p class="text-xs leading-relaxed text-outline">Nomor ini untuk pengelola akun demo, <strong>tidak otomatis ditampilkan</strong> di profil publik. Ubah nomor kontak usaha melalui menu Profil & Portofolio.</p>
                                <p data-ps-identity-error role="alert" class="hidden rounded-xl bg-error-container px-3 py-2.5 text-xs font-medium text-on-error-container"></p>
                                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-outline-variant/30 pt-4"><p class="text-[11px] text-outline">Perubahan terakhir: <span data-ps-updated>Belum ada</span></p><button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white hover:bg-primary-container"><span class="material-symbols-outlined text-[18px]">save</span>Simpan kontak</button></div>
                            </form>
                        </section>

                        {{-- Preferences --}}
                        <form data-ps-preferences-form class="space-y-5" novalidate>
                            <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="ps-notifications-title">
                                <div class="mb-4"><h2 id="ps-notifications-title" class="flex items-center gap-2 text-base font-extrabold"><span class="material-symbols-outlined text-[21px] text-primary">notifications</span>Preferensi notifikasi</h2><p class="mt-1 text-xs text-outline">Preferensi UI saja. Belum ada push, email, atau WhatsApp yang dikirim.</p></div>
                                <div class="divide-y divide-outline-variant/25">
                                    <label class="flex cursor-pointer items-center justify-between gap-4 py-3"><span><strong class="block text-sm">Pesanan baru</strong><span class="mt-1 block text-xs text-outline">Pengingat ketika ada permintaan masuk</span></span><input type="checkbox" data-ps-notification="newOrder" class="h-5 w-5 shrink-0 rounded border-outline-variant text-primary focus:ring-primary"></label>
                                    <label class="flex cursor-pointer items-center justify-between gap-4 py-3"><span><strong class="block text-sm">Perubahan status pekerjaan</strong><span class="mt-1 block text-xs text-outline">Pembaruan status pesanan yang ditangani</span></span><input type="checkbox" data-ps-notification="orderUpdate" class="h-5 w-5 shrink-0 rounded border-outline-variant text-primary focus:ring-primary"></label>
                                    <label class="flex cursor-pointer items-center justify-between gap-4 py-3"><span><strong class="block text-sm">Ulasan pelanggan</strong><span class="mt-1 block text-xs text-outline">Pemberitahuan penilaian baru</span></span><input type="checkbox" data-ps-notification="newReview" class="h-5 w-5 shrink-0 rounded border-outline-variant text-primary focus:ring-primary"></label>
                                </div>
                            </section>

                            <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="ps-privacy-title">
                                <div class="mb-4"><h2 id="ps-privacy-title" class="flex items-center gap-2 text-base font-extrabold"><span class="material-symbols-outlined text-[21px] text-primary">shield</span>Privasi profil publik</h2><p class="mt-1 text-xs text-outline">Berpengaruh pada halaman detail profil Warga di browser yang sama.</p></div>
                                <div class="divide-y divide-outline-variant/25">
                                    <label class="flex cursor-pointer items-center justify-between gap-4 py-3"><span><strong class="block text-sm">Tampilkan nomor kontak usaha</strong><span class="mt-1 block text-xs text-outline">Hanya nomor kontak dari Profil & Portofolio, bukan nomor pemilik akun</span></span><input type="checkbox" data-ps-privacy="showBusinessPhone" class="h-5 w-5 shrink-0 rounded border-outline-variant text-primary focus:ring-primary"></label>
                                    <label class="flex cursor-pointer items-center justify-between gap-4 py-3"><span><strong class="block text-sm">Tampilkan area jangkauan</strong><span class="mt-1 block text-xs text-outline">Tampilkan daftar kecamatan pada bagian Tentang Mitra</span></span><input type="checkbox" data-ps-privacy="showCoverage" class="h-5 w-5 shrink-0 rounded border-outline-variant text-primary focus:ring-primary"></label>
                                </div>
                            </section>

                            <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="ps-operation-title">
                                <div class="mb-4"><h2 id="ps-operation-title" class="flex items-center gap-2 text-base font-extrabold"><span class="material-symbols-outlined text-[21px] text-primary">tune</span>Preferensi operasional</h2><p class="mt-1 text-xs text-outline">Sesuaikan informasi operasional di Dashboard Mitra.</p></div>
                                <div class="divide-y divide-outline-variant/25">
                                    <label class="flex cursor-pointer items-center justify-between gap-4 py-3"><span><strong class="block text-sm">Tampilkan ringkasan tarif</strong><span class="mt-1 block text-xs text-outline">Kartu estimasi tarif dan grafik pada Dashboard Overview</span></span><input type="checkbox" data-ps-operation="showRevenue" class="h-5 w-5 shrink-0 rounded border-outline-variant text-primary focus:ring-primary"></label>
                                    <label class="flex cursor-pointer items-center justify-between gap-4 py-3"><span><strong class="block text-sm">Preferensi pengingat jadwal</strong><span class="mt-1 block text-xs text-outline">Tersimpan sebagai preferensi, belum membuat notifikasi terjadwal</span></span><input type="checkbox" data-ps-operation="scheduleReminder" class="h-5 w-5 shrink-0 rounded border-outline-variant text-primary focus:ring-primary"></label>
                                </div>
                            </section>
                            <p data-ps-preferences-error role="alert" class="hidden rounded-xl bg-error-container px-4 py-3 text-xs font-medium text-on-error-container"></p>
                            <div class="flex justify-end"><button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white hover:bg-primary-container sm:w-auto"><span class="material-symbols-outlined text-[18px]">save</span>Simpan preferensi</button></div>
                        </form>

                        {{-- Password mock --}}
                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="ps-password-title">
                            <h2 id="ps-password-title" class="flex items-center gap-2 text-base font-extrabold"><span class="material-symbols-outlined text-[21px] text-primary">lock</span>Simulasi ubah password</h2>
                            <p class="mt-2 text-xs leading-relaxed text-outline">Bagian ini hanya mendemonstrasikan pemeriksaan form. Password <strong>tidak diverifikasi, tidak disimpan, dan tidak benar-benar diganti</strong>.</p>
                            <form data-ps-password-form class="mt-4 space-y-4" novalidate autocomplete="off">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div><label for="ps-new-password" class="mb-1.5 block text-sm font-semibold">Password baru (demo)</label><input id="ps-new-password" type="password" name="newPassword" required minlength="8" maxlength="128" autocomplete="new-password" class="w-full rounded-xl border-outline-variant/70 bg-white text-sm focus:border-primary focus:ring-primary" placeholder="Minimal 8 karakter"></div>
                                    <div><label for="ps-confirm-password" class="mb-1.5 block text-sm font-semibold">Konfirmasi password</label><input id="ps-confirm-password" type="password" name="confirmation" required minlength="8" maxlength="128" autocomplete="new-password" class="w-full rounded-xl border-outline-variant/70 bg-white text-sm focus:border-primary focus:ring-primary" placeholder="Ulangi password"></div>
                                </div>
                                <p data-ps-password-message role="status" class="hidden rounded-xl px-3 py-2.5 text-xs font-medium"></p>
                                <div class="flex justify-end"><button type="submit" class="w-full rounded-xl border border-primary/30 bg-primary-fixed px-4 py-2.5 text-sm font-bold text-primary hover:bg-secondary-fixed sm:w-auto">Uji validasi password</button></div>
                            </form>
                        </section>
                    </div>

                    {{-- right cards --}}
                    <aside class="min-w-0 space-y-5" aria-label="Ringkasan akun mitra">
                        <section class="overflow-hidden rounded-2xl border border-outline-variant/40 bg-white shadow-sm">
                            <div class="bg-primary-fixed/70 px-5 py-5"><span class="text-[10px] font-extrabold uppercase tracking-[.14em] text-primary">Usaha aktif</span><div class="mt-3 flex items-center gap-3"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary text-lg font-bold text-white">{{ $provider['initials'] }}</span><div class="min-w-0"><p class="truncate text-base font-extrabold">{{ $provider['name'] }}</p><p class="text-xs text-on-surface-variant">Mitra ID #{{ $provider['id'] }}</p></div></div></div>
                            <div class="space-y-4 p-5">
                                <div><p class="text-[11px] font-bold uppercase tracking-wider text-outline">Status verifikasi · hanya baca</p><span class="mt-2 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold {{ $provider['verified'] ? 'bg-emerald-50 text-emerald-700' : 'bg-surface-container-low text-on-surface-variant' }}"><span class="material-symbols-outlined text-[17px]">{{ $provider['verified'] ? 'verified' : 'pending' }}</span>{{ $provider['verified'] ? 'Terverifikasi (data demo)' : 'Belum terverifikasi (data demo)' }}</span><p class="mt-2 text-xs leading-relaxed text-outline">Status berasal dari konfigurasi Laravel. Tidak dapat diubah melalui pengaturan akun.</p></div>
                                <div class="border-t border-outline-variant/30 pt-4"><p class="text-xs font-bold">Menerima pesanan baru</p><p data-ps-availability-label class="mt-1 text-xs text-outline">Memuat status...</p><label class="mt-3 inline-flex cursor-pointer items-center gap-3"><input type="checkbox" data-ps-availability class="h-5 w-5 rounded border-outline-variant text-primary focus:ring-primary"><span class="text-xs font-semibold">Aktifkan penerimaan pesanan</span></label><p class="mt-2 text-[11px] leading-relaxed text-outline">Langsung tersinkron dengan Dashboard dan Form Pemesanan demo. Pesanan yang sudah ada tidak dibatalkan.</p></div>
                                <a href="{{ route('partner.schedule.index') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-outline-variant/60 py-2.5 text-xs font-bold text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined text-[17px]">calendar_month</span>Atur jadwal kerja</a>
                            </div>
                        </section>
                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm"><h2 class="text-base font-extrabold">Navigasi cepat</h2><div class="mt-4 grid grid-cols-2 gap-2 text-xs font-bold"><a href="{{ route('partner.profile.index') }}" class="rounded-xl bg-surface-container-low px-3 py-3 text-center text-primary hover:bg-primary-fixed">Profil Usaha</a><a href="{{ route('partner.orders.index') }}" class="rounded-xl bg-surface-container-low px-3 py-3 text-center text-primary hover:bg-primary-fixed">Pesanan</a><a href="{{ route('partner.services.index') }}" class="rounded-xl bg-surface-container-low px-3 py-3 text-center text-primary hover:bg-primary-fixed">Layanan</a><a href="{{ route('partner.reviews.index') }}" class="rounded-xl bg-surface-container-low px-3 py-3 text-center text-primary hover:bg-primary-fixed">Ulasan</a></div></section>
                        <section class="rounded-2xl border border-error/25 bg-white p-5 shadow-sm"><h2 class="text-base font-extrabold">Keluar dari Mode Mitra</h2><p class="mt-2 text-xs leading-relaxed text-on-surface-variant">Mengakhiri penanda sesi demo. Profil, pesanan, layanan, dan preferensi di browser tidak ikut dihapus. Akses dashboard sekarang memerlukan role Mitra dan hanya akun demo yang terhubung ke Budi Teknik.</p><button data-ps-logout type="button" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-error/40 px-4 py-2.5 text-sm font-bold text-error hover:bg-error-container"><span class="material-symbols-outlined text-[18px]">logout</span>Keluar dari mode Mitra</button></section>
                    </aside>
                </div>
            </main>
        </div>
    </div>
    @include('partner.partials.mobile-nav')

    <dialog id="jw-ps-logout-dialog" aria-labelledby="ps-logout-dialog-title" class="w-[min(94vw,460px)] rounded-2xl border border-outline-variant/40 bg-white p-0 text-on-surface shadow-2xl backdrop:bg-on-surface/55"><div class="p-6"><h2 id="ps-logout-dialog-title" class="text-lg font-extrabold">Keluar dari Mode Mitra?</h2><p class="mt-2 text-sm leading-relaxed text-on-surface-variant">Penanda sesi demo akan dihapus, tetapi data pekerjaan dan pengaturan lokal akan tetap ada di browser ini.</p><div class="mt-6 flex flex-wrap justify-end gap-2"><button type="button" data-ps-cancel-logout class="rounded-xl border border-outline-variant/70 px-4 py-2.5 text-sm font-semibold">Batal</button><form method="POST" action="{{ route('auth.logout') }}">@csrf<button type="submit" data-ps-confirm-logout class="rounded-xl bg-error px-4 py-2.5 text-sm font-bold text-white">Ya, keluar sesi Laravel</button></form></div></div></dialog>
    <div data-ps-toast role="status" aria-live="polite" class="pointer-events-none fixed bottom-24 left-1/2 z-[70] hidden w-[min(92vw,440px)] -translate-x-1/2 rounded-xl bg-inverse-surface px-4 py-3 text-center text-sm font-semibold text-white shadow-xl lg:bottom-6"></div>
</div>
@endsection
