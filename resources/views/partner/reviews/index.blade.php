@extends('layouts.app')

@section('title', 'Kelola Ulasan Pelanggan | JasaWarga Mitra')

@section('content')
<div id="jw-partner-reviews" data-partner-id="{{ $provider['id'] }}" data-partner-name="{{ $provider['name'] }}" class="min-h-screen bg-surface text-on-surface">
    <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        @include('partner.partials.sidebar')

        <div class="min-w-0">
            {{-- Header desktop --}}
            <header class="sticky top-0 z-30 hidden border-b border-outline-variant/40 bg-white/95 backdrop-blur lg:block">
                <div class="mx-auto flex h-[76px] max-w-[1380px] items-center justify-between gap-4 px-8 xl:px-10">
                    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-semibold text-outline">
                        <a href="{{ route('partner.dashboard') }}" class="hover:text-primary">Ruang Mitra</a>
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                        <span class="text-on-surface">Ulasan pelanggan</span>
                    </nav>
                    <span class="rounded-full bg-primary-fixed px-3 py-1.5 text-[11px] font-extrabold text-primary">MITRA DEMO · BANDUNG</span>
                </div>
            </header>

            {{-- Header mobile --}}
            <header class="sticky top-0 z-30 flex items-center justify-between border-b border-outline-variant/40 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route('partner.dashboard') }}" aria-label="Kembali ke dashboard" class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-container-low"><span class="material-symbols-outlined">arrow_back</span></a>
                    <div class="min-w-0"><h1 class="truncate text-base font-extrabold">Ulasan pelanggan</h1><p class="truncate text-xs text-outline">{{ $provider['name'] }} · Ruang Mitra</p></div>
                </div>
                <a href="{{ route('providers.show', $provider['id']) }}#ulasan-lokal" aria-label="Lihat ulasan pada profil publik" class="flex h-10 w-10 items-center justify-center rounded-xl border border-outline-variant/60 bg-white text-primary"><span class="material-symbols-outlined">open_in_new</span></a>
            </header>

            <main class="mx-auto max-w-[1380px] space-y-6 px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-9 xl:px-10">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="mb-2 text-[11px] font-extrabold uppercase tracking-[.14em] text-primary">Reputasi usaha / Suara pelanggan</p>
                        <h2 class="text-[26px] font-extrabold tracking-tight sm:text-[30px]">Ulasan pelanggan</h2>
                        <p class="mt-1 max-w-2xl text-sm leading-relaxed text-on-surface-variant">Pantau penilaian pekerjaan dan tanggapi masukan Warga dengan profesional.</p>
                    </div>
                    <a href="{{ route('providers.show', $provider['id']) }}#ulasan-lokal" class="inline-flex items-center gap-2 rounded-xl border border-outline-variant/60 bg-white px-4 py-2.5 text-sm font-bold text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined text-[19px]">visibility</span> Lihat profil publik</a>
                </div>

                <div role="note" class="flex items-start gap-3 rounded-xl border border-primary/20 bg-primary-fixed/35 px-4 py-3 text-xs leading-relaxed text-on-surface-variant">
                    <span class="material-symbols-outlined mt-0.5 text-[21px] text-primary">info</span>
                    <p><strong class="text-on-surface">Mode prototipe.</strong> Hanya ulasan lokal untuk mitra {{ $provider['name'] }} yang dihitung. Balasan tersimpan di browser ini, bukan dikirim ke Warga atau server. Rating dummy bawaan profil tidak termasuk dalam statistik berikut.</p>
                </div>

                {{-- Ringkasan utama --}}
                <section aria-label="Ringkasan rating lokal" class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-2 text-xs font-semibold text-on-surface-variant"><span>Rata-rata rating</span><span class="material-symbols-outlined text-[21px] text-tertiary">star</span></div>
                        <div class="mt-4 flex items-baseline gap-1"><strong data-pr-average class="text-3xl font-extrabold tabular-nums">—</strong><span class="text-xs text-outline">/ 5,0</span></div>
                        <p class="mt-1 text-[11px] text-outline">Ulasan di browser ini</p>
                    </article>
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-2 text-xs font-semibold text-on-surface-variant"><span>Total ulasan</span><span class="material-symbols-outlined text-[21px] text-primary">reviews</span></div>
                        <strong data-pr-total class="mt-4 block text-3xl font-extrabold tabular-nums">0</strong>
                        <p class="mt-1 text-[11px] text-outline">Dari pekerjaan selesai</p>
                    </article>
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-2 text-xs font-semibold text-on-surface-variant"><span>Sudah dibalas</span><span class="material-symbols-outlined text-[21px] text-emerald-700">mark_chat_read</span></div>
                        <strong data-pr-replied class="mt-4 block text-3xl font-extrabold tabular-nums">0</strong>
                        <p class="mt-1 text-[11px] text-outline">Balasan lokal tersimpan</p>
                    </article>
                    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-2 text-xs font-semibold text-on-surface-variant"><span>Belum dibalas</span><span class="material-symbols-outlined text-[21px] text-secondary">chat_bubble_outline</span></div>
                        <strong data-pr-unanswered class="mt-4 block text-3xl font-extrabold tabular-nums">0</strong>
                        <p class="mt-1 text-[11px] text-outline">Menunggu tanggapan Mitra</p>
                    </article>
                </section>

                {{-- Distribusi rating dan aspek --}}
                <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_335px]">
                    <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="pr-distribution-title">
                        <div class="flex flex-wrap items-center justify-between gap-3"><h3 id="pr-distribution-title" class="text-base font-extrabold sm:text-lg">Distribusi rating</h3><span class="text-[11px] text-outline">Berdasarkan ulasan lokal</span></div>
                        <div class="mt-5 space-y-3">
                            @for($star = 5; $star >= 1; $star--)
                                <div class="flex items-center gap-3 text-xs" data-pr-star-row="{{ $star }}">
                                    <span class="inline-flex w-10 shrink-0 items-center gap-1 font-bold text-on-surface">{{ $star }} <span class="material-symbols-outlined text-[15px] text-tertiary">star</span></span>
                                    <div class="h-2.5 min-w-0 flex-1 overflow-hidden rounded-full bg-surface-container-low"><div data-pr-star-bar class="h-full rounded-full bg-primary transition-[width] duration-300" style="width: 0%"></div></div>
                                    <span data-pr-star-count class="w-7 shrink-0 text-right font-semibold tabular-nums text-outline">0</span>
                                </div>
                            @endfor
                        </div>
                    </section>
                    <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="pr-aspects-title">
                        <h3 id="pr-aspects-title" class="text-base font-extrabold sm:text-lg">Penilaian detail</h3>
                        <p class="mt-1 text-xs text-outline">Nilai rata-rata setiap aspek dari ulasan lokal</p>
                        <div class="mt-5 space-y-4">
                            @foreach(['quality' => 'Kualitas pekerjaan', 'punctuality' => 'Ketepatan waktu', 'friendliness' => 'Keramahan'] as $key => $label)
                                <div class="flex items-center justify-between gap-3 border-b border-outline-variant/25 pb-3 last:border-0 last:pb-0"><span class="text-sm text-on-surface-variant">{{ $label }}</span><strong data-pr-aspect="{{ $key }}" class="text-sm tabular-nums text-primary">—</strong></div>
                            @endforeach
                        </div>
                    </section>
                </div>

                {{-- Daftar & filter --}}
                <section class="rounded-2xl border border-outline-variant/40 bg-white shadow-sm" aria-labelledby="pr-list-title">
                    <div class="flex flex-col gap-4 border-b border-outline-variant/35 p-5 sm:p-6">
                        <div class="flex flex-wrap items-end justify-between gap-3">
                            <div><h3 id="pr-list-title" class="text-lg font-extrabold">Daftar ulasan</h3><p class="mt-1 text-xs text-outline"><span data-pr-visible>0</span> dari <span data-pr-total-inline>0</span> ulasan ditemukan</p></div>
                            <button type="button" data-pr-seed class="inline-flex items-center gap-2 rounded-xl border border-primary/40 bg-primary-fixed px-4 py-2.5 text-xs font-bold text-primary hover:bg-secondary-fixed"><span class="material-symbols-outlined text-[18px]">add_circle</span> Tambah 3 ulasan contoh</button>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_145px_160px_160px]">
                            <div><label for="pr-search" class="mb-1 block text-[11px] font-semibold text-outline">Cari ulasan / layanan / kode</label><div class="relative"><span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-outline">search</span><input id="pr-search" data-pr-search type="search" class="w-full rounded-xl border-outline-variant/60 bg-white pl-10 text-sm focus:border-primary focus:ring-primary" placeholder="Cari ulasan..."></div></div>
                            <div><label for="pr-stars" class="mb-1 block text-[11px] font-semibold text-outline">Rating</label><select id="pr-stars" data-pr-stars class="w-full rounded-xl border-outline-variant/60 bg-white text-sm focus:border-primary focus:ring-primary"><option value="all">Semua bintang</option>@for($star = 5; $star >= 1; $star--)<option value="{{ $star }}">{{ $star }} bintang</option>@endfor</select></div>
                            <div><label for="pr-period" class="mb-1 block text-[11px] font-semibold text-outline">Tanggal ulasan</label><select id="pr-period" data-pr-period class="w-full rounded-xl border-outline-variant/60 bg-white text-sm focus:border-primary focus:ring-primary"><option value="all">Semua tanggal</option><option value="7">7 hari terakhir</option><option value="30">30 hari terakhir</option></select></div>
                            <div><label for="pr-response" class="mb-1 block text-[11px] font-semibold text-outline">Balasan</label><select id="pr-response" data-pr-response class="w-full rounded-xl border-outline-variant/60 bg-white text-sm focus:border-primary focus:ring-primary"><option value="all">Semua</option><option value="answered">Sudah dibalas</option><option value="pending">Belum dibalas</option></select></div>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3"><label for="pr-service" class="sr-only">Filter berdasarkan layanan</label><select id="pr-service" data-pr-service class="max-w-full rounded-xl border-outline-variant/60 bg-white text-xs focus:border-primary focus:ring-primary"><option value="all">Semua layanan</option></select><div class="flex items-center gap-3"><label for="pr-sort" class="text-xs font-semibold text-outline">Urutkan</label><select id="pr-sort" data-pr-sort class="rounded-xl border-outline-variant/60 bg-white text-xs focus:border-primary focus:ring-primary"><option value="newest">Terbaru</option><option value="oldest">Terlama</option><option value="high">Bintang tertinggi</option><option value="low">Bintang terendah</option></select><button type="button" data-pr-reset class="text-xs font-bold text-primary hover:underline">Reset</button></div></div>
                    </div>
                    <div data-pr-list class="divide-y divide-outline-variant/30" aria-live="polite"></div>
                    <div data-pr-empty class="px-5 py-14 text-center sm:px-8"><span class="material-symbols-outlined text-[46px] text-outline">rate_review</span><h4 data-pr-empty-title class="mt-2 text-base font-extrabold">Belum ada ulasan pelanggan</h4><p data-pr-empty-desc class="mx-auto mt-2 max-w-md text-xs leading-relaxed text-on-surface-variant">Selesaikan pesanan lalu kirim ulasan melalui halaman Warga. Untuk demo, kamu bisa menambahkan tiga ulasan fiktif.</p></div>
                </section>
                <p class="text-[11px] leading-relaxed text-outline">Data contoh dan balasan hanya tersimpan di browser yang sama, tanpa moderasi, autentikasi Mitra, atau komunikasi sungguhan. Jangan gunakan informasi pelanggan nyata.</p>
            </main>
        </div>
    </div>
    @include('partner.partials.mobile-nav')

    <template id="jw-pr-card-template">
        <article class="p-5 sm:p-6" data-pr-card>
            <div class="flex flex-wrap items-start justify-between gap-3"><div class="min-w-0"><p class="text-sm font-extrabold">Warga JasaWarga <span class="font-normal text-outline">· Ulasan lokal</span></p><p data-pr-card-meta class="mt-1 break-all text-[11px] text-outline"></p></div><div class="text-right"><p data-pr-card-stars class="text-lg tracking-[.1em] text-tertiary" aria-label="Rating pelanggan"></p><p data-pr-card-rating class="text-[11px] font-bold text-on-surface-variant"></p></div></div>
            <p data-pr-card-comment class="mt-3 whitespace-pre-wrap break-words text-sm leading-relaxed text-on-surface-variant"></p>
            <div data-pr-card-aspects class="mt-3 flex flex-wrap gap-2"></div>
            <div data-pr-card-photos class="mt-3 flex flex-wrap gap-2"></div>
            <div data-pr-card-reply-box class="mt-4 hidden rounded-xl border-l-[3px] border-primary bg-primary-fixed/35 px-4 py-3"><div class="flex flex-wrap items-center justify-between gap-2"><strong class="text-xs text-primary">Balasan Mitra</strong><span data-pr-card-reply-date class="text-[10px] text-outline"></span></div><p data-pr-card-reply-text class="mt-2 whitespace-pre-wrap break-words text-sm leading-relaxed text-on-surface-variant"></p></div>
            <div class="mt-4 flex flex-wrap items-center gap-2"><button type="button" data-pr-reply class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-xs font-bold text-white hover:bg-primary-container"><span class="material-symbols-outlined text-[17px]">reply</span><span data-pr-action-label>Balas ulasan</span></button><button type="button" data-pr-delete class="hidden rounded-xl border border-outline-variant/60 px-4 py-2 text-xs font-semibold text-on-surface-variant hover:bg-surface-container-low">Hapus balasan</button></div>
        </article>
    </template>

    <dialog id="jw-pr-reply-dialog" aria-labelledby="pr-reply-title" class="w-[min(94vw,520px)] max-h-[85dvh] rounded-2xl border border-outline-variant/50 bg-white p-0 text-on-surface shadow-2xl backdrop:bg-on-surface/50">
        <form id="jw-pr-reply-form" class="space-y-4 p-5 sm:p-6" novalidate>
            <div class="flex items-start justify-between gap-3"><div><h3 id="pr-reply-title" data-pr-dialog-title class="text-lg font-extrabold">Balas ulasan</h3><p data-pr-dialog-hint class="mt-1 text-xs text-outline"></p></div><button type="button" data-pr-close-reply aria-label="Tutup formulir balasan" class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container-low"><span class="material-symbols-outlined">close</span></button></div>
            <div><label for="pr-reply-message" class="mb-1.5 block text-sm font-semibold">Tanggapan Mitra <span class="text-error">*</span></label><textarea id="pr-reply-message" data-pr-reply-input name="reply" rows="6" required minlength="10" maxlength="500" class="w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary" placeholder="Terima kasih atas penilaian Anda. Kami akan terus meningkatkan pelayanan..."></textarea><div class="mt-1 flex items-center justify-between text-[11px] text-outline"><span>10–500 karakter</span><span><span data-pr-reply-count>0</span> / 500</span></div></div>
            <p data-pr-reply-error role="alert" class="hidden rounded-xl bg-error-container px-3 py-2.5 text-xs font-medium text-on-error-container"></p>
            <div class="flex justify-end gap-2 border-t border-outline-variant/30 pt-4"><button type="button" data-pr-close-reply class="rounded-xl border border-outline-variant/70 px-4 py-2.5 text-sm font-semibold">Batal</button><button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white">Simpan balasan</button></div>
        </form>
    </dialog>

    <dialog id="jw-pr-delete-dialog" aria-labelledby="pr-delete-title" class="w-[min(94vw,440px)] rounded-2xl border border-outline-variant/50 bg-white p-0 shadow-2xl backdrop:bg-on-surface/50">
        <div class="p-6"><div class="flex items-start gap-3"><span class="material-symbols-outlined text-[26px] text-error">delete_forever</span><div><h3 id="pr-delete-title" class="text-lg font-extrabold">Hapus balasan?</h3><p class="mt-2 text-sm leading-relaxed text-on-surface-variant">Balasan Mitra akan dihapus dari browser ini. Ulasan dan rating Warga tetap utuh.</p></div></div><div class="mt-6 flex justify-end gap-2"><button type="button" data-pr-close-delete class="rounded-xl border border-outline-variant/70 px-4 py-2.5 text-sm font-semibold">Batal</button><button type="button" data-pr-confirm-delete class="rounded-xl bg-error px-4 py-2.5 text-sm font-bold text-white">Hapus balasan</button></div></div>
    </dialog>

    <dialog id="jw-pr-photo-dialog" aria-label="Pratinjau foto ulasan" class="w-[min(94vw,750px)] rounded-2xl bg-white p-3 shadow-2xl backdrop:bg-on-surface/70"><div class="mb-2 flex justify-end"><button type="button" data-pr-close-photo aria-label="Tutup pratinjau foto" class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container-low"><span class="material-symbols-outlined">close</span></button></div><img data-pr-photo-large alt="Foto ulasan pelanggan (simulasi)" class="max-h-[72dvh] w-full object-contain"></dialog>
    <div data-pr-toast class="pointer-events-none fixed bottom-24 left-1/2 z-[70] hidden w-[min(92vw,430px)] -translate-x-1/2 rounded-xl bg-inverse-surface px-4 py-3 text-center text-sm font-semibold text-white shadow-xl lg:bottom-6" role="status" aria-live="polite"></div>
</div>
@endsection
