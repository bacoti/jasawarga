@extends('layouts.app')

@section('title', 'Profil & Portofolio Mitra | JasaWarga')

@section('content')
<div id="jw-partner-profile" class="min-h-screen bg-surface text-on-surface" data-partner-id="{{ $provider['id'] }}">
    <script type="application/json" data-pp-seed>{!! json_encode(['provider' => $provider, 'profile' => $profile], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        @include('partner.partials.sidebar')

        <div class="min-w-0">
            <header class="sticky top-0 z-30 hidden border-b border-outline-variant/40 bg-white/95 backdrop-blur lg:block">
                <div class="mx-auto flex h-[76px] max-w-[1380px] items-center justify-between px-8 xl:px-10">
                    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-semibold text-outline">
                        <a href="{{ route('partner.dashboard') }}" class="hover:text-primary">Ruang Mitra</a>
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                        <span class="text-on-surface">Profil & portofolio</span>
                    </nav>
                    <span class="rounded-full bg-primary-fixed px-3 py-1.5 text-[11px] font-extrabold text-primary">MITRA DEMO · BANDUNG</span>
                </div>
            </header>

            <header class="sticky top-0 z-30 flex items-center justify-between border-b border-outline-variant/40 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route('partner.dashboard') }}" aria-label="Kembali ke dashboard" class="flex h-10 w-10 items-center justify-center rounded-xl bg-surface-container-low"><span class="material-symbols-outlined">arrow_back</span></a>
                    <div class="min-w-0"><h1 class="truncate text-base font-extrabold">Profil & portofolio</h1><p class="truncate text-xs text-outline" data-pp-brand-name>{{ $provider['name'] }}</p></div>
                </div>
                <a href="{{ route('providers.show', ['id' => $provider['id']]) }}" aria-label="Pratinjau profil publik" class="flex h-10 w-10 items-center justify-center rounded-xl border border-outline-variant/60 text-primary"><span class="material-symbols-outlined">open_in_new</span></a>
            </header>

            <main class="mx-auto max-w-[1380px] space-y-6 px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-9 xl:px-10">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="mb-2 text-[11px] font-extrabold uppercase tracking-[.14em] text-primary">Etalase mitra / Profil usaha</p>
                        <h2 class="text-[26px] font-extrabold tracking-tight sm:text-[30px]">Profil & portofolio</h2>
                        <p class="mt-1 max-w-2xl text-sm leading-relaxed text-on-surface-variant">Bangun identitas usaha yang mudah dikenali Warga dan tampilkan contoh hasil pekerjaan.</p>
                    </div>
                    <a href="{{ route('providers.show', ['id' => $provider['id']]) }}" class="inline-flex items-center gap-2 rounded-xl border border-outline-variant/60 bg-white px-4 py-2.5 text-sm font-bold text-primary transition hover:bg-surface-container-low"><span class="material-symbols-outlined text-[19px]">visibility</span> Preview profil Warga</a>
                </div>

                <div role="note" class="flex items-start gap-3 rounded-xl border border-primary/20 bg-primary-fixed/35 px-4 py-3 text-xs leading-relaxed text-on-surface-variant">
                    <span class="material-symbols-outlined mt-0.5 text-[21px] text-primary">info</span>
                    <p><strong class="text-on-surface">Mode prototipe.</strong> Data dan gambar hanya tersimpan di <code>localStorage</code> browser ini. Foto yang dipilih tidak diunggah ke server, dan status verifikasi adalah data contoh yang tidak dapat diubah.</p>
                </div>

                <section class="overflow-hidden rounded-2xl border border-outline-variant/40 bg-white shadow-sm" aria-labelledby="pp-identity-heading">
                    <div class="relative h-36 overflow-hidden bg-gradient-to-r from-primary-fixed via-surface-container-high to-surface-container-low sm:h-44" data-pp-cover-holder>
                        <img data-pp-cover-preview alt="Pratinjau cover mitra" class="hidden h-full w-full object-cover">
                        <span data-pp-cover-placeholder class="material-symbols-outlined absolute right-8 top-3 text-[128px] text-primary/10" aria-hidden="true">handyman</span>
                    </div>
                    <div class="relative flex flex-wrap items-end gap-4 px-5 pb-5 sm:px-6">
                        <div class="-mt-11 flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-4 border-white bg-primary-fixed text-2xl font-black text-primary shadow-sm" data-pp-avatar-holder>
                            <img data-pp-avatar-preview alt="Pratinjau foto profil mitra" class="hidden h-full w-full object-cover">
                            <span data-pp-avatar-placeholder>BT</span>
                        </div>
                        <div class="min-w-0 flex-1 pb-1 pt-3">
                            <h3 id="pp-identity-heading" class="truncate text-lg font-extrabold sm:text-xl" data-pp-brand-name>{{ $provider['name'] }}</h3>
                            <p class="mt-1 text-xs text-on-surface-variant">Tampilan pratinjau identitas usaha</p>
                        </div>
                        <div class="mb-1 inline-flex items-center gap-1.5 rounded-lg bg-secondary-fixed px-3 py-2 text-xs font-bold text-on-secondary-fixed" aria-label="Status verifikasi hanya baca">
                            <span data-partner-readonly-verify-icon class="material-symbols-outlined text-[18px]">{{ $provider['verified'] ? 'verified' : 'info' }}</span>
                            <span data-partner-readonly-verify>{{ $provider['verified'] ? 'Terverifikasi (data contoh)' : 'Belum terverifikasi (data contoh)' }}</span>
                            <span class="material-symbols-outlined text-[16px]" title="Tidak dapat diedit">lock</span>
                        </div>
                    </div>
                </section>

                <form id="jw-pp-profile-form" novalidate class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_330px]">
                    <div class="min-w-0 space-y-5">
                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="pp-business-title">
                            <h3 id="pp-business-title" class="text-lg font-extrabold">Informasi usaha</h3>
                            <p class="mb-5 mt-1 text-xs text-outline">Informasi yang tampil pada halaman profil publik Warga.</p>
                            <div class="space-y-4">
                                <div><label for="pp-name" class="mb-1.5 block text-sm font-semibold">Nama usaha <span class="text-error">*</span></label><input id="pp-name" name="name" type="text" required minlength="3" maxlength="90" class="w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary" placeholder="Nama usaha atau penyedia jasa"></div>
                                <div><label for="pp-headline" class="mb-1.5 block text-sm font-semibold">Tagline / keahlian utama <span class="text-error">*</span></label><input id="pp-headline" name="headline" type="text" required minlength="10" maxlength="170" class="w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary" placeholder="Contoh: Teknisi AC untuk perawatan rumah dan toko"></div>
                                <div><label for="pp-about" class="mb-1.5 block text-sm font-semibold">Deskripsi usaha <span class="text-error">*</span></label><textarea id="pp-about" name="about" rows="6" required minlength="30" maxlength="1400" class="w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary" placeholder="Ceritakan jenis pekerjaan dan cara Anda melayani pelanggan..."></textarea><p class="mt-1 text-right text-[11px] text-outline"><span data-pp-about-count>0</span> / 1400 karakter</p></div>
                                <div class="grid gap-4 sm:grid-cols-2"><div><label for="pp-experience" class="mb-1.5 block text-sm font-semibold">Pengalaman (tahun) <span class="text-error">*</span></label><input id="pp-experience" name="experienceYears" type="number" min="0" max="50" step="1" required class="w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary"></div><div><label for="pp-phone" class="mb-1.5 block text-sm font-semibold">Nomor WhatsApp <span class="font-normal text-outline">(opsional)</span></label><input id="pp-phone" name="phone" type="tel" inputmode="tel" maxlength="20" placeholder="08xxxxxxxxxx" class="w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary"></div></div>
                                <div><label for="pp-specialties" class="mb-1.5 block text-sm font-semibold">Spesialisasi layanan <span class="text-error">*</span></label><input id="pp-specialties" name="specialties" type="text" required maxlength="245" placeholder="Contoh: Servis AC, Cuci AC, Pemasangan AC" class="w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary"><p class="mt-1 text-[11px] leading-relaxed text-outline">Pisahkan dengan koma, maksimal 5 keahlian.</p></div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="pp-coverage-title">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-primary">location_on</span><h3 id="pp-coverage-title" class="text-lg font-extrabold">Area jangkauan</h3></div>
                            <p class="mb-4 mt-1 text-xs text-outline">Pilih kecamatan di Kota Bandung yang dilayani. Minimal satu kecamatan.</p>
                            <fieldset><legend class="sr-only">Kecamatan layanan di Bandung</legend><div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                @foreach($areas as $area)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-outline-variant/50 px-3 py-2.5 text-xs font-semibold text-on-surface-variant transition hover:border-primary/40"><input type="checkbox" data-pp-coverage value="{{ $area }}" class="rounded border-outline-variant text-primary focus:ring-primary"><span>{{ $area }}</span></label>
                                @endforeach
                            </div></fieldset>
                        </section>

                        <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="pp-photos-title">
                            <h3 id="pp-photos-title" class="text-lg font-extrabold">Foto profil & cover</h3><p class="mt-1 text-xs leading-relaxed text-outline">JPG, PNG, atau WebP hingga 2 MB per file. Gambar dikecilkan menjadi JPEG sebelum disimpan secara lokal.</p>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl border border-outline-variant/40 bg-surface-container-low/50 p-4"><label for="pp-avatar-file" class="block text-sm font-bold">Foto profil</label><input id="pp-avatar-file" data-pp-image-input="avatarDataUrl" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-xs text-on-surface-variant file:mr-3 file:rounded-lg file:border-0 file:bg-primary-fixed file:px-3 file:py-2 file:font-semibold file:text-primary"><button type="button" data-pp-image-clear="avatarDataUrl" class="mt-3 text-xs font-bold text-error hover:underline">Hapus foto profil</button></div>
                                <div class="rounded-xl border border-outline-variant/40 bg-surface-container-low/50 p-4"><label for="pp-cover-file" class="block text-sm font-bold">Gambar cover</label><input id="pp-cover-file" data-pp-image-input="coverDataUrl" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-xs text-on-surface-variant file:mr-3 file:rounded-lg file:border-0 file:bg-primary-fixed file:px-3 file:py-2 file:font-semibold file:text-primary"><button type="button" data-pp-image-clear="coverDataUrl" class="mt-3 text-xs font-bold text-error hover:underline">Hapus gambar cover</button></div>
                            </div>
                        </section>

                        <p data-pp-form-error role="alert" class="hidden rounded-xl border border-error/30 bg-error-container px-4 py-3 text-sm font-semibold text-error"></p>
                        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5"><p class="max-w-lg text-xs leading-relaxed text-outline">Perubahan profil tidak akan mengubah data pesanan atau ulasan yang sudah tersimpan.</p><button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-white transition hover:bg-primary-container sm:w-auto"><span class="material-symbols-outlined text-[20px]">save</span>Simpan profil</button></div>
                    </div>

                    <aside class="space-y-4 xl:sticky xl:top-[98px]" aria-label="Informasi profil">
                        <div class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm"><h3 class="text-base font-extrabold">Status verifikasi</h3><div class="mt-4 flex items-center gap-3 rounded-xl bg-surface-container-low p-3"><span class="material-symbols-outlined text-[25px] text-primary">lock</span><div><p class="text-sm font-bold">{{ $provider['verified'] ? 'Terverifikasi (contoh)' : 'Belum terverifikasi (contoh)' }}</p><p class="mt-1 text-xs text-outline">Hanya dapat dibaca · berasal dari konfigurasi Laravel</p></div></div><p class="mt-3 text-xs leading-relaxed text-outline">Tidak ada tombol untuk mengubah status verifikasi. Status pada prototipe bukan bukti pemeriksaan identitas.</p></div>
                        <div class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm"><h3 class="text-base font-extrabold">Tampilan profil publik</h3><p class="mt-2 text-xs leading-relaxed text-on-surface-variant">Setelah menyimpan perubahan, buka profil Warga untuk melihat foto, informasi usaha, area layanan, dan portofolio yang diperbarui.</p><a href="{{ route('providers.show', ['id' => $provider['id']]) }}" class="mt-4 flex items-center justify-center gap-2 rounded-xl border border-primary/30 px-4 py-2.5 text-xs font-bold text-primary hover:bg-primary-fixed"><span class="material-symbols-outlined text-[18px]">open_in_new</span>Buka profil publik</a></div>
                    </aside>
                </form>

                <section id="kelola-portofolio" class="rounded-2xl border border-outline-variant/40 bg-white shadow-sm" aria-labelledby="pp-portfolio-title">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-outline-variant/30 p-5 sm:p-6"><div><h3 id="pp-portfolio-title" class="text-lg font-extrabold">Portofolio pekerjaan</h3><p class="mt-1 text-xs text-outline"><span data-pp-project-count>0</span> dari 9 proyek · Dokumentasi lokal, bukan unggahan server.</p></div><button type="button" data-pp-add-project class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2.5 text-xs font-bold text-white hover:bg-primary-container"><span class="material-symbols-outlined text-[19px]">add_photo_alternate</span>Tambah portofolio</button></div>
                    <div data-pp-project-list class="grid gap-4 bg-surface-container-low/25 p-4 sm:grid-cols-2 sm:p-6 xl:grid-cols-3" aria-live="polite"></div>
                    <p data-pp-project-empty class="hidden px-6 py-12 text-center text-sm text-outline">Belum ada portofolio. Klik “Tambah portofolio” untuk membuatnya.</p>
                </section>
            </main>
            @include('partner.partials.mobile-nav')
        </div>
    </div>

    <dialog id="jw-pp-project-dialog" class="w-[min(94vw,580px)] max-h-[90dvh] rounded-2xl border border-outline-variant/40 bg-white p-0 text-on-surface shadow-2xl backdrop:bg-on-surface/50" aria-labelledby="pp-project-dialog-title">
        <form id="jw-pp-project-form" class="flex max-h-[90dvh] flex-col" novalidate>
            <div class="flex items-center justify-between border-b border-outline-variant/40 px-5 py-4"><div><h3 id="pp-project-dialog-title" class="text-lg font-extrabold" data-pp-project-dialog-heading>Tambah portofolio</h3><p class="mt-1 text-xs text-outline">Informasi pekerjaan contoh untuk prototipe.</p></div><button type="button" data-pp-close-project class="rounded-lg p-2 hover:bg-surface-container-low" aria-label="Tutup formulir"><span class="material-symbols-outlined">close</span></button></div>
            <div class="space-y-4 overflow-y-auto p-5">
                <p data-pp-project-error role="alert" class="hidden rounded-lg bg-error-container px-3 py-3 text-xs font-bold text-error"></p>
                <div><label for="pp-project-title" class="mb-1.5 block text-sm font-semibold">Judul pekerjaan *</label><input id="pp-project-title" name="title" minlength="3" maxlength="90" required class="w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary" placeholder="Contoh: Perbaikan AC ruang keluarga"></div>
                <div><label for="pp-project-description" class="mb-1.5 block text-sm font-semibold">Deskripsi pekerjaan *</label><textarea id="pp-project-description" name="description" rows="4" minlength="15" maxlength="360" required class="w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary" placeholder="Jelaskan masalah, tindakan perbaikan, dan hasil pekerjaan..."></textarea></div>
                <div><label for="pp-project-area" class="mb-1.5 block text-sm font-semibold">Lokasi pekerjaan *</label><select id="pp-project-area" name="area" required class="w-full rounded-xl border-outline-variant/60 text-sm focus:border-primary focus:ring-primary">@foreach($areas as $area)<option value="{{ $area }}">{{ $area }}, Bandung</option>@endforeach</select></div>
                <div><label for="pp-project-file" class="mb-1.5 block text-sm font-semibold">Foto pekerjaan <span class="font-normal text-outline">(opsional)</span></label><input id="pp-project-file" data-pp-project-image type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-on-surface-variant file:mr-3 file:rounded-lg file:border-0 file:bg-primary-fixed file:px-3 file:py-2 file:font-semibold file:text-primary"><div data-pp-project-image-box class="mt-3 hidden overflow-hidden rounded-xl border border-outline-variant/40"><img data-pp-project-image-preview alt="Pratinjau foto pekerjaan" class="aspect-video w-full object-cover"></div><button type="button" data-pp-clear-project-image class="mt-2 text-xs font-bold text-error hover:underline">Hapus foto pekerjaan</button><p class="mt-1 text-[11px] leading-relaxed text-outline">Maks. 2 MB. Foto diperkecil untuk menghemat ruang browser. Contoh proyek bawaan tidak memiliki foto asli.</p></div>
            </div>
            <div class="flex justify-end gap-2 border-t border-outline-variant/40 p-4"><button type="button" data-pp-close-project class="rounded-xl border border-outline-variant/60 px-4 py-2.5 text-sm font-semibold">Batal</button><button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-extrabold text-white">Simpan portofolio</button></div>
        </form>
    </dialog>

    <dialog id="jw-pp-delete-dialog" class="w-[min(94vw,440px)] rounded-2xl border border-outline-variant/40 bg-white p-5 text-on-surface shadow-2xl backdrop:bg-on-surface/50" aria-labelledby="pp-delete-title">
        <h3 id="pp-delete-title" class="text-lg font-extrabold">Hapus portofolio ini?</h3><p class="mt-3 text-sm leading-relaxed text-on-surface-variant"><strong data-pp-delete-title></strong> akan dihapus dari portofolio browser ini. Tindakan ini tidak dapat dibatalkan.</p><div class="mt-6 flex justify-end gap-2"><button type="button" data-pp-cancel-delete class="rounded-xl border border-outline-variant/60 px-4 py-2.5 text-sm font-semibold">Batal</button><button type="button" data-pp-confirm-delete class="rounded-xl bg-error px-4 py-2.5 text-sm font-bold text-white">Ya, hapus</button></div>
    </dialog>

    <div data-pp-toast role="status" aria-live="polite" class="pointer-events-none fixed bottom-24 left-1/2 z-[70] hidden w-[min(92vw,440px)] -translate-x-1/2 rounded-xl bg-inverse-surface px-4 py-3 text-center text-sm font-semibold text-white shadow-xl lg:bottom-6"></div>
</div>
@endsection
