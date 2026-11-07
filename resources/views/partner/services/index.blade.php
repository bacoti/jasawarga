@extends('layouts.app')

@section('title', 'Kelola Layanan & Tarif Mitra | JasaWarga')

@section('content')
<div id="jw-partner-services" class="min-h-screen bg-surface text-on-surface"
     data-partner-id="{{ $provider['id'] }}" data-partner-category="{{ $provider['category'] }}">
    <script type="application/json" data-services-seed>{!! json_encode($profile['services'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script type="application/json" data-services-categories>{!! json_encode($categories, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        @include('partner.partials.sidebar')

        <div class="min-w-0">
            {{-- Desktop top bar --}}
            <header class="sticky top-0 z-30 hidden border-b border-outline-variant/40 bg-white/95 backdrop-blur lg:block">
                <div class="mx-auto flex h-[76px] max-w-[1380px] items-center justify-between gap-4 px-8 xl:px-10">
                    <nav class="flex items-center gap-2 text-xs font-semibold text-outline" aria-label="Breadcrumb">
                        <a href="{{ route('partner.dashboard') }}" class="hover:text-primary">Ruang Mitra</a>
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                        <span class="text-on-surface">Layanan & Tarif</span>
                    </nav>
                    <span class="rounded-full bg-primary-fixed px-3 py-1.5 text-[11px] font-extrabold text-primary">MITRA DEMO · MAKASSAR</span>
                </div>
            </header>

            {{-- Mobile header --}}
            <header class="sticky top-0 z-30 flex items-center justify-between border-b border-outline-variant/40 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route('partner.dashboard') }}" aria-label="Kembali ke dashboard" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-container-low"><span class="material-symbols-outlined">arrow_back</span></a>
                    <div class="min-w-0"><h1 class="truncate text-base font-extrabold">Layanan & Tarif</h1><p class="truncate text-xs text-outline">{{ $provider['name'] }}</p></div>
                </div>
                <button type="button" data-ps-create aria-label="Tambah layanan" class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-white"><span class="material-symbols-outlined">add</span></button>
            </header>

            <main class="mx-auto max-w-[1380px] space-y-6 px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-9 xl:px-10">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="mb-2 text-[11px] font-extrabold uppercase tracking-[.14em] text-primary">Etalase layanan / Mitra #{{ $provider['id'] }}</p>
                        <h2 class="text-[26px] font-extrabold tracking-tight sm:text-[30px]">Layanan & tarif</h2>
                        <p class="mt-1 max-w-2xl text-sm text-on-surface-variant">Kelola jenis pekerjaan, harga awal, dan ketersediaan layanan Anda.</p>
                    </div>
                    <button type="button" data-ps-create class="hidden items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white transition hover:bg-primary-container lg:inline-flex">
                        <span class="material-symbols-outlined text-[20px]">add</span> Tambah layanan
                    </button>
                </div>

                <div role="note" class="flex gap-3 rounded-xl border border-primary/20 bg-primary-fixed/35 px-4 py-3 text-xs leading-relaxed text-on-surface-variant">
                    <span class="material-symbols-outlined text-[21px] text-primary">info</span>
                    <p><strong class="text-on-surface">Mode prototipe.</strong> Perubahan hanya tersimpan pada browser ini. Layanan nonaktif atau dihapus tidak tersedia untuk pemesanan baru, tetapi catatan pesanan sebelumnya tidak berubah. Tidak ada transaksi sungguhan.</p>
                </div>

                <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Statistik layanan">
                    <div class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5"><p class="text-xs font-semibold text-on-surface-variant">Total layanan</p><p data-ps-total class="mt-3 text-3xl font-extrabold">0</p><p class="mt-1 text-[11px] text-outline">Di etalase mitra</p></div>
                    <div class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5"><p class="text-xs font-semibold text-on-surface-variant">Layanan aktif</p><p data-ps-active class="mt-3 text-3xl font-extrabold text-emerald-700">0</p><p class="mt-1 text-[11px] text-outline">Bisa dipesan warga</p></div>
                    <div class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5"><p class="text-xs font-semibold text-on-surface-variant">Nonaktif</p><p data-ps-inactive class="mt-3 text-3xl font-extrabold text-tertiary">0</p><p class="mt-1 text-[11px] text-outline">Disembunyikan dari pemesanan</p></div>
                    <div class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5"><p class="text-xs font-semibold text-on-surface-variant">Tarif aktif terendah</p><p data-ps-min class="mt-3 text-xl font-extrabold text-primary sm:text-2xl">—</p><p class="mt-1 text-[11px] text-outline">Tarif awal, bukan tagihan</p></div>
                </section>

                <section class="rounded-2xl border border-outline-variant/40 bg-white shadow-sm" aria-labelledby="ps-list-title">
                    <div class="border-b border-outline-variant/30 p-4 sm:p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div><h2 id="ps-list-title" class="text-lg font-extrabold">Daftar layanan</h2><p class="mt-1 text-xs text-outline"><span data-ps-count>0</span> layanan sesuai filter</p></div>
                            <a href="{{ route('providers.show', $provider['id']) }}#layanan" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline"><span class="material-symbols-outlined text-[16px]">open_in_new</span>Lihat etalase Warga</a>
                        </div>
                        <div class="mt-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_190px_190px]">
                            <label class="relative block"><span class="sr-only">Cari layanan</span><span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-outline">search</span><input data-ps-search type="search" placeholder="Cari nama atau deskripsi layanan..." class="h-11 w-full rounded-xl border-outline-variant/60 pl-10 text-sm focus:border-primary focus:ring-primary"></label>
                            <label><span class="sr-only">Filter status</span><select data-ps-filter class="h-11 w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary"><option value="all">Semua status</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select></label>
                            <label><span class="sr-only">Filter kategori</span><select data-ps-category-filter class="h-11 w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary"><option value="all">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category['slug'] }}">{{ $category['name'] }}</option>@endforeach</select></label>
                        </div>
                    </div>
                    <div class="space-y-3 bg-surface-container-low/35 p-3 sm:p-5" data-ps-list aria-live="polite"></div>
                    <div data-ps-empty class="hidden px-6 py-12 text-center"><span class="material-symbols-outlined text-5xl text-outline">inventory_2</span><h3 class="mt-2 font-extrabold">Tidak ada layanan ditemukan</h3><p class="mt-1 text-sm text-outline">Coba ubah filter atau tambahkan layanan baru.</p><button type="button" data-ps-create class="mt-4 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white">Tambah layanan</button></div>
                </section>
            </main>
            @include('partner.partials.mobile-nav')
        </div>
    </div>

    {{-- Form dialog tambah/edit --}}
    <dialog id="jw-ps-form-dialog" aria-labelledby="jw-ps-form-title" class="w-[min(94vw,580px)] max-h-[90dvh] rounded-2xl border border-outline-variant/40 bg-white p-0 text-on-surface shadow-2xl backdrop:bg-on-surface/50">
        <form id="jw-ps-form" class="flex max-h-[90dvh] flex-col" novalidate>
            <div class="flex items-center justify-between border-b border-outline-variant/40 px-5 py-4"><div><h2 id="jw-ps-form-title" data-ps-dialog-title class="text-lg font-extrabold">Tambah layanan</h2><p class="mt-1 text-xs text-outline">Seluruh perubahan tersimpan secara lokal.</p></div><button data-ps-close-form type="button" class="rounded-lg p-2 hover:bg-surface-container-low" aria-label="Tutup"><span class="material-symbols-outlined">close</span></button></div>
            <div class="space-y-4 overflow-y-auto p-5">
                <p data-ps-error role="alert" class="hidden rounded-xl border border-error/30 bg-error-container px-3 py-2 text-xs font-semibold text-error"></p>
                <div><label for="ps-name" class="mb-1.5 block text-sm font-semibold">Nama layanan *</label><input id="ps-name" name="name" required minlength="3" maxlength="80" placeholder="Contoh: Servis AC 1 PK" class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></div>
                <div><label for="ps-description" class="mb-1.5 block text-sm font-semibold">Deskripsi pekerjaan *</label><textarea id="ps-description" name="description" rows="3" required minlength="15" maxlength="360" placeholder="Jelaskan cakupan pekerjaan dan kondisi yang termasuk dalam tarif." class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></textarea></div>
                <div class="grid gap-4 sm:grid-cols-2"><div><label for="ps-category" class="mb-1.5 block text-sm font-semibold">Kategori jasa *</label><select id="ps-category" name="category" required class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary">@foreach($categories as $category)<option value="{{ $category['slug'] }}">{{ $category['name'] }}</option>@endforeach</select></div><div><label for="ps-duration" class="mb-1.5 block text-sm font-semibold">Estimasi durasi *</label><input id="ps-duration" name="duration" required minlength="2" maxlength="60" placeholder="Contoh: 1–2 jam" class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></div></div>
                <div class="grid gap-4 sm:grid-cols-2"><div><label for="ps-price" class="mb-1.5 block text-sm font-semibold">Tarif dasar (Rp) *</label><input id="ps-price" name="price" type="number" min="10000" max="20000000" step="1000" required placeholder="Contoh: 75000" class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></div><div><label for="ps-unit" class="mb-1.5 block text-sm font-semibold">Satuan tarif *</label><select id="ps-unit" name="unit" required class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"><option value="per pekerjaan">Per pekerjaan</option><option value="per unit">Per unit</option><option value="per kunjungan">Per kunjungan</option><option value="per jam">Per jam</option></select></div></div>
                <div><label for="ps-fee-note" class="mb-1.5 block text-sm font-semibold">Keterangan biaya tambahan <span class="text-xs font-normal text-outline">(opsional)</span></label><textarea id="ps-fee-note" name="feeNote" rows="2" maxlength="200" placeholder="Contoh: Suku cadang dan material belum termasuk." class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></textarea></div>
                <p class="rounded-xl bg-primary-fixed/40 px-3 py-2 text-xs leading-relaxed text-on-surface-variant">Tarif ini adalah estimasi awal. Perubahan harga tidak mengubah nominal pesanan lama.</p>
            </div>
            <div class="flex justify-end gap-2 border-t border-outline-variant/40 bg-white p-4"><button type="button" data-ps-close-form class="rounded-xl border border-outline-variant/60 px-4 py-2.5 text-sm font-semibold">Batal</button><button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-extrabold text-white">Simpan layanan</button></div>
        </form>
    </dialog>

    {{-- Konfirmasi penghapusan --}}
    <dialog id="jw-ps-delete-dialog" aria-labelledby="jw-ps-delete-title" class="w-[min(92vw,450px)] rounded-2xl border border-outline-variant/40 bg-white p-5 text-on-surface shadow-2xl backdrop:bg-on-surface/50">
        <h2 id="jw-ps-delete-title" class="text-lg font-extrabold">Hapus layanan ini?</h2>
        <p class="mt-3 text-sm leading-relaxed text-on-surface-variant"><strong data-ps-delete-name></strong> akan dihapus dari etalase browser ini. Pesanan lama tetap tercatat, tetapi layanan yang dihapus tidak dapat dipesan baru.</p>
        <div class="mt-6 flex justify-end gap-2"><button type="button" data-ps-close-delete class="rounded-xl border border-outline-variant/60 px-4 py-2 text-sm font-semibold">Batal</button><button type="button" data-ps-confirm-delete class="rounded-xl bg-error px-4 py-2 text-sm font-bold text-white">Ya, hapus</button></div>
    </dialog>

    <div data-ps-toast role="status" aria-live="polite" class="pointer-events-none fixed bottom-24 left-1/2 z-[70] hidden w-[min(92vw,440px)] -translate-x-1/2 rounded-xl bg-inverse-surface px-4 py-3 text-center text-sm font-semibold text-white shadow-xl lg:bottom-6"></div>
</div>
@endsection
