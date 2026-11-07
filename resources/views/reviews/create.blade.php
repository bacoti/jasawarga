@extends('layouts.app')

@section('title', 'Beri Rating & Ulasan | JasaWarga')

@section('content')
<div id="jw-review-create" class="min-h-screen bg-surface text-on-surface"
     data-reference="{{ $reference }}"
     data-order-base="{{ url('/pesanan-saya') }}"
     data-reviews-url="{{ route('reviews.index') }}">
    @include('components.site-navbar')
    <main class="mx-auto max-w-6xl px-4 pb-24 pt-6 sm:px-6 lg:px-8 lg:pb-14 lg:pt-9">
        <nav aria-label="Breadcrumb" class="mb-6 hidden items-center gap-2 text-xs text-outline lg:flex">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a><span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('orders.index') }}" class="hover:text-primary">Pesanan Saya</a><span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="font-semibold text-primary">Beri ulasan</span>
        </nav>

        <section data-review-blocked class="hidden rounded-2xl border border-outline-variant/40 bg-white p-7 text-center shadow-sm sm:p-12">
            <span class="material-symbols-outlined text-5xl text-outline">rate_review</span>
            <h1 class="mt-3 text-xl font-extrabold" data-review-blocked-title>Ulasan belum dapat dibuat</h1>
            <p class="mx-auto mt-2 max-w-lg text-sm leading-relaxed text-on-surface-variant" data-review-blocked-message>Periksa status pesanan Anda.</p>
            <a data-review-blocked-link href="{{ route('orders.index') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white">Lihat Pesanan Saya</a>
        </section>

        <div data-review-form-content class="hidden">
            <div class="mb-6"><span class="inline-flex items-center gap-1 rounded-full bg-secondary-fixed px-3 py-1 text-[11px] font-extrabold text-on-secondary-fixed"><span class="material-symbols-outlined text-[16px]">verified</span> Pesanan demo selesai</span><h1 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">Bagaimana pengalaman layanan Anda?</h1><p class="mt-2 max-w-xl text-sm leading-relaxed text-on-surface-variant">Berikan rating dan ulasan untuk pesanan simulasi ini. Data hanya tersimpan di browser Anda, tidak dikirim kepada mitra.</p></div>
            <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <form id="jw-review-form" class="space-y-5" novalidate>
                    <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="text-base font-extrabold sm:text-lg">1. Rating keseluruhan</h2><p class="mt-1 text-xs text-outline">Seberapa puas Anda dengan layanan ini?</p>
                        @include('reviews.partials.stars', ['key' => 'overall', 'label' => 'Rating keseluruhan', 'required' => true])
                    </section>
                    <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="text-base font-extrabold sm:text-lg">2. Penilaian detail</h2><p class="mt-1 text-xs text-outline">Nilai tiap aspek secara terpisah (wajib).</p>
                        <div class="mt-3 grid gap-5 sm:grid-cols-3">
                            @include('reviews.partials.stars', ['key' => 'quality', 'label' => 'Kualitas pekerjaan', 'required' => true])
                            @include('reviews.partials.stars', ['key' => 'punctuality', 'label' => 'Ketepatan waktu', 'required' => true])
                            @include('reviews.partials.stars', ['key' => 'friendliness', 'label' => 'Keramahan mitra', 'required' => true])
                        </div>
                    </section>
                    <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="text-base font-extrabold sm:text-lg">3. Ceritakan pengalaman Anda</h2>
                        <label for="jw-review-comment" class="mt-3 block text-sm font-semibold">Ulasan tertulis <span class="text-error">*</span></label>
                        <textarea id="jw-review-comment" name="comment" rows="5" minlength="20" maxlength="800" required class="mt-2 w-full rounded-xl border-outline-variant/60 text-sm leading-relaxed focus:border-primary focus:ring-primary" placeholder="Contoh: Proses layanan berjalan baik, mitra datang sesuai jadwal, hasil pekerjaan rapi..."></textarea>
                        <div class="mt-1 flex justify-between gap-3 text-[11px] text-outline"><span>Minimal 20 karakter, maksimal 800.</span><span><span data-review-count>0</span>/800</span></div>
                        <div class="mt-5 border-t border-outline-variant/30 pt-5"><label for="jw-review-photos" class="block text-sm font-semibold">Foto hasil pekerjaan <span class="font-normal text-outline">(opsional)</span></label><p class="mt-1 text-xs leading-relaxed text-on-surface-variant">Maksimal 2 gambar JPG/PNG/WebP, 2 MB per file. Foto diperkecil dan disimpan secara lokal sebagai pratinjau, tanpa upload ke server. Gunakan foto fiktif tanpa informasi pribadi.</p>
                            <input id="jw-review-photos" type="file" accept="image/jpeg,image/png,image/webp" multiple class="mt-3 block w-full cursor-pointer rounded-xl border border-outline-variant/60 bg-surface-container-low p-2 text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-primary-fixed file:px-3 file:py-2 file:font-semibold file:text-primary" />
                            <div data-review-photo-preview class="mt-3 flex flex-wrap gap-3" aria-live="polite"></div>
                        </div>
                    </section>
                    <div data-review-error role="alert" tabindex="-1" class="hidden rounded-xl border border-error/30 bg-error-container px-4 py-3 text-sm text-on-error-container"></div>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('orders.show', $reference) }}" class="rounded-xl border border-outline-variant/60 bg-white px-5 py-3 text-center text-sm font-bold hover:bg-surface-container-low">Kembali ke pesanan</a>
                        <button data-review-submit type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-white hover:bg-primary-container disabled:cursor-not-allowed disabled:opacity-50"><span class="material-symbols-outlined text-[19px]">send</span> Kirim ulasan simulasi</button>
                    </div>
                </form>
                <aside class="space-y-4 lg:sticky lg:top-[92px]">
                    <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm"><h2 class="text-base font-extrabold">Pesanan yang dinilai</h2><p class="mt-1 break-all font-mono text-[11px] text-outline" data-review-order-reference></p><div class="mt-4 flex gap-3"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-fixed text-primary"><span class="material-symbols-outlined">handyman</span></span><div class="min-w-0"><p class="font-bold" data-review-order-provider></p><p class="mt-1 text-sm text-on-surface-variant" data-review-order-service></p></div></div><p class="mt-4 flex items-center gap-2 border-t border-outline-variant/30 pt-4 text-xs text-on-surface-variant"><span class="material-symbols-outlined text-[17px] text-emerald-600">check_circle</span> Status: Selesai (simulasi)</p></section>
                    <section class="rounded-2xl bg-surface-container-low p-5 text-xs leading-relaxed text-on-surface-variant"><p class="flex items-center gap-2 font-bold text-primary"><span class="material-symbols-outlined">info</span> Tentang ulasan demo</p><p class="mt-2">Satu ulasan per pesanan selesai. Ulasan tidak dapat dikirim ke server, dan foto hanya dapat dilihat pada browser yang sama. Rating di profil mitra dipisahkan dari rating contoh bawaan.</p></section>
                </aside>
            </div>
        </div>
    </main>
</div>
@endsection
