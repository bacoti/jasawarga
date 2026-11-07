@extends('layouts.app')

@section('title', 'Form Pemesanan ' . $provider['name'] . ' | JasaWarga')

@section('content')
<div id="jw-booking" class="min-h-screen bg-surface text-on-surface"
     data-provider-id="{{ $provider['id'] }}"
     data-provider-name="{{ $provider['name'] }}"
     data-provider-url="{{ route('providers.show', $provider['id']) }}"
     data-success-url="{{ route('bookings.success') }}"
     data-allow-sunday="{{ $allowSunday ? '1' : '0' }}" data-provider-category="{{ $provider['category'] }}">

    <script type="application/json" data-booking-categories-seed>@json(config('jasawarga.categories', []))</script>
    <script type="application/json" data-booking-services-seed>{!! json_encode($profile['services'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    {{-- Desktop navbar --}}
    @include('components.site-navbar')
    {{-- Mobile header --}}
    <main class="mx-auto max-w-7xl px-4 pb-36 pt-5 sm:px-6 lg:px-8 lg:pb-16 lg:pt-8">
        <nav class="mb-5 hidden items-center gap-2 text-xs text-outline lg:flex" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('categories.index', ['kategori' => $provider['category']]) }}" class="hover:text-primary">{{ $category['name'] ?? 'Kategori Jasa' }}</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <a href="{{ route('providers.show', $provider['id']) }}" class="hover:text-primary">{{ $provider['name'] }}</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span class="font-semibold text-primary">Form Pemesanan</span>
        </nav>

        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-secondary-fixed px-3 py-1 text-[11px] font-bold text-on-secondary-fixed">
                    <span class="material-symbols-outlined text-[16px]">edit_calendar</span> Langkah 1 dari 2
                </span>
                <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">Lengkapi pemesanan jasa</h2>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-on-surface-variant">
                    Pilih layanan dan jadwal, lalu jelaskan pekerjaan Anda. Konfirmasi akhir hanya simulasi; tidak ada pesanan atau pembayaran sungguhan.
                </p>
            </div>
            <div class="hidden items-center gap-2 text-xs font-semibold text-on-surface-variant sm:flex">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary text-white">1</span>
                Isi detail
                <span class="mx-1 h-px w-8 bg-outline-variant"></span>
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-surface-container-high text-outline">2</span>
                Konfirmasi
            </div>
        </div>

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_350px]">
            <form id="jw-booking-form" class="min-w-0 space-y-5" novalidate autocomplete="on">
                {{-- Global form validation --}}
                <div id="jw-booking-errors" role="alert" aria-live="assertive" tabindex="-1"
                     class="hidden rounded-xl border border-error/30 bg-error-container px-4 py-3 text-sm text-on-error-container"></div>

                <section class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="booking-service-title">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-fixed text-primary"><span class="material-symbols-outlined">handyman</span></span>
                        <div><h3 id="booking-service-title" class="text-base font-extrabold sm:text-lg">Layanan yang dipesan</h3><p class="text-xs text-outline">Mitra dan tarif dari halaman profil</p></div>
                    </div>

                    <div class="mb-4 flex items-center gap-3 rounded-xl bg-surface-container-low p-3.5">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold" style="background-color: {{ $provider['bg'] }}">{{ $provider['initials'] }}</div>
                        <div class="min-w-0 flex-1"><p class="truncate text-sm font-extrabold">{{ $provider['name'] }}</p><p class="mt-0.5 truncate text-xs text-on-surface-variant">{{ $category['name'] ?? 'Jasa' }} · {{ $provider['area'] }}, Bandung</p></div>
                        <a href="{{ route('providers.show', $provider['id']) }}#layanan" class="text-xs font-bold text-primary hover:underline">Profil</a>
                    </div>

                    <label for="booking-service" class="mb-1.5 block text-sm font-semibold">Pilih jenis layanan <span class="text-error">*</span></label>
                    <select id="booking-service" name="service_index" required class="w-full rounded-xl border-outline-variant/70 bg-white text-sm focus:border-primary focus:ring-primary">
                        @foreach($profile['services'] as $index => $service)
                            <option value="{{ $index }}" data-price="{{ $service['price'] }}" data-duration="{{ $service['duration'] }}" @selected($index === $selectedIndex)>{{ $service['name'] }}</option>
                        @endforeach
                    </select>
                    <p data-booking-service-notice role="status" class="mt-2 hidden rounded-lg border border-tertiary/30 bg-tertiary-fixed/30 px-3 py-2 text-xs font-semibold text-tertiary"></p>
                    <p class="mt-2 text-xs text-outline">Perkiraan durasi: <span data-booking-duration>{{ $profile['services'][$selectedIndex]['duration'] }}</span> · Satuan: <span data-booking-unit>per pekerjaan</span></p>
                    <p class="mt-1 text-xs leading-relaxed text-on-surface-variant" data-booking-fee-note>Biaya material dan kebutuhan tambahan dikonfirmasi saat survei.</p>
                </section>

                <section class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="booking-contact-title">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-fixed text-primary"><span class="material-symbols-outlined">person_pin_circle</span></span>
                        <div><h3 id="booking-contact-title" class="text-base font-extrabold sm:text-lg">Kontak & lokasi pengerjaan</h3><p class="text-xs text-outline">Data simulasi disimpan secara lokal di browser, bukan di server</p></div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="booking-name" class="mb-1.5 block text-sm font-semibold">Nama penerima <span class="text-error">*</span></label>
                            <input id="booking-name" name="contact_name" type="text" required minlength="3" maxlength="80" autocomplete="name" placeholder="Contoh: Andi Saputra" class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label for="booking-phone" class="mb-1.5 block text-sm font-semibold">Nomor WhatsApp <span class="text-error">*</span></label>
                            <input id="booking-phone" name="phone" type="tel" required inputmode="tel" autocomplete="tel" maxlength="20" placeholder="Contoh: 081234567890" class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="booking-area" class="mb-1.5 block text-sm font-semibold">Kecamatan / area layanan <span class="text-error">*</span></label>
                        <select id="booking-area" name="area" required class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary">
                            <option value="">Pilih area layanan mitra</option>
                            @foreach($profile['coverage'] as $area)
                                <option value="{{ $area }}">{{ $area }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-4">
                        <label for="booking-address" class="mb-1.5 block text-sm font-semibold">Alamat lengkap pengerjaan <span class="text-error">*</span></label>
                        <textarea id="booking-address" name="address" rows="3" required minlength="10" maxlength="300" autocomplete="street-address" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan..." class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></textarea>
                    </div>
                    <div class="mt-4">
                        <label for="booking-landmark" class="mb-1.5 block text-sm font-semibold">Patokan lokasi <span class="text-xs font-normal text-outline">(opsional)</span></label>
                        <input id="booking-landmark" name="landmark" type="text" maxlength="120" placeholder="Contoh: dekat minimarket / gerbang hijau" class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary">
                    </div>
                </section>

                <section class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="booking-schedule-title">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-fixed text-primary"><span class="material-symbols-outlined">event</span></span>
                        <div><h3 id="booking-schedule-title" class="text-base font-extrabold sm:text-lg">Jadwal kedatangan</h3><p class="text-xs text-outline">Seluruh jam menggunakan WIB (Bandung)</p></div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="booking-date" class="mb-1.5 block text-sm font-semibold">Tanggal <span class="text-error">*</span></label>
                            <input id="booking-date" name="date" type="date" required min="{{ now('Asia/Jakarta')->addDay()->toDateString() }}" max="{{ now('Asia/Jakarta')->addDays(30)->toDateString() }}" class="w-full rounded-xl border-outline-variant/70 bg-white text-sm focus:border-primary focus:ring-primary">
                        </div>
                        <div>
                            <label for="booking-time" class="mb-1.5 block text-sm font-semibold">Perkiraan jam tiba <span class="text-error">*</span></label>
                            <select id="booking-time" name="slot_time" required class="w-full rounded-xl border-outline-variant/70 bg-white text-sm focus:border-primary focus:ring-primary">
                                <option value="">Pilih jam kedatangan</option>
                                {{-- Slot dimuat dari pengaturan jadwal Mitra di localStorage. --}}
                            </select>
                        </div>
                    </div>
                    <p id="jw-booking-date-hint" class="mt-3 text-xs leading-relaxed text-outline">
                        Pilih tanggal untuk melihat slot WIB berdasarkan pengaturan Mitra. Slot yang sudah direservasi akan dinonaktifkan (simulasi browser ini).
                    </p>
                </section>

                <section class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="booking-notes-title">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-fixed text-primary"><span class="material-symbols-outlined">description</span></span>
                        <div><h3 id="booking-notes-title" class="text-base font-extrabold sm:text-lg">Detail kebutuhan</h3><p class="text-xs text-outline">Bantu mitra memahami masalah sebelum datang</p></div>
                    </div>

                    <label for="booking-description" class="mb-1.5 block text-sm font-semibold">Jelaskan pekerjaan / masalah <span class="text-error">*</span></label>
                    <textarea id="booking-description" name="description" rows="4" required minlength="15" maxlength="600" placeholder="Contoh: AC kamar tidak dingin sejak kemarin, ingin diperiksa dan dibersihkan..." class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></textarea>
                    <p class="mt-1.5 text-right text-[11px] text-outline"><span data-booking-desc-count>0</span>/600 karakter</p>

                    <label for="booking-note" class="mb-1.5 mt-4 block text-sm font-semibold">Catatan tambahan <span class="text-xs font-normal text-outline">(opsional)</span></label>
                    <textarea id="booking-note" name="note" rows="2" maxlength="250" placeholder="Contoh: parkir di samping rumah, mohon kabari sebelum datang" class="w-full rounded-xl border-outline-variant/70 text-sm focus:border-primary focus:ring-primary"></textarea>
                </section>

                <section class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-6">
                    <label class="flex cursor-pointer items-start gap-3 text-sm leading-relaxed text-on-surface-variant">
                        <input id="booking-consent" name="consent" type="checkbox" required class="mt-1 rounded border-outline-variant text-primary focus:ring-primary">
                        <span>Saya memahami bahwa seluruh data mitra, jadwal, tarif, dan kode pemesanan ini adalah <strong>simulasi</strong>. Tidak ada pesanan, pembayaran, atau pengiriman data ke penyedia jasa sungguhan. Riwayat, alamat, dan deskripsi tersimpan di localStorage perangkat ini; gunakan hanya data fiktif. <span class="text-error">*</span></span>
                    </label>
                </section>

                <div class="hidden lg:block">
                    <button type="submit" data-booking-submit disabled class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3.5 text-sm font-extrabold text-white transition hover:bg-primary-container">
                        Konfirmasi pemesanan simulasi
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </div>
            </form>

            {{-- Desktop booking summary --}}
            <aside class="hidden lg:block lg:sticky lg:top-[98px]" aria-label="Ringkasan estimasi biaya">
                <div class="overflow-hidden rounded-2xl border border-outline-variant/40 bg-white shadow-sm">
                    <div class="border-b border-outline-variant/30 bg-surface-container-low px-5 py-4">
                        <h3 class="text-base font-extrabold">Ringkasan pemesanan</h3>
                        <p class="mt-1 text-xs text-on-surface-variant">Harga awal layanan, bukan tagihan akhir</p>
                    </div>
                    <div class="space-y-4 p-5">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold" style="background-color: {{ $provider['bg'] }}">{{ $provider['initials'] }}</span>
                            <div class="min-w-0"><p class="truncate text-sm font-extrabold">{{ $provider['name'] }}</p><p class="truncate text-xs text-outline">{{ $category['name'] ?? 'Jasa lokal' }}</p></div>
                        </div>
                        <div class="border-t border-outline-variant/30 pt-4 text-sm">
                            <p class="text-xs text-outline">Layanan</p>
                            <p class="mt-1 font-bold" data-booking-service-label>{{ $profile['services'][$selectedIndex]['name'] }}</p>
                            <p class="mt-2 text-xs text-outline" data-booking-schedule-label>Jadwal belum dipilih</p>
                        </div>
                        <div class="space-y-3 border-t border-outline-variant/30 pt-4 text-sm">
                            <div class="flex justify-between gap-3"><span class="text-on-surface-variant">Tarif dasar</span><strong data-booking-base-price>Rp {{ number_format($profile['services'][$selectedIndex]['price'], 0, ',', '.') }}</strong></div>
                            <div class="flex justify-between gap-3"><span class="text-on-surface-variant">Biaya platform (demo)</span><strong>Rp 0</strong></div>
                            <div class="flex items-end justify-between gap-3 border-t border-outline-variant/30 pt-3"><span class="font-bold">Total estimasi</span><strong class="text-2xl text-primary" data-booking-total>Rp {{ number_format($profile['services'][$selectedIndex]['price'], 0, ',', '.') }}</strong></div>
                        </div>
                        <p class="rounded-xl bg-primary-fixed/40 p-3 text-xs leading-relaxed text-on-surface-variant"><span class="font-bold text-primary">Info tarif:</span> biaya suku cadang, material, dan pekerjaan tambahan belum dihitung. Total sebenarnya harus dikonfirmasi dengan mitra.</p>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    {{-- Mobile bottom CTA --}}
    <div class="fixed bottom-0 left-0 right-0 z-40 border-t border-outline-variant/40 bg-white/95 px-4 pt-3 shadow-[0_-4px_18px_rgba(14,28,49,.07)] backdrop-blur lg:hidden" style="padding-bottom: max(12px, env(safe-area-inset-bottom));">
        <div class="mx-auto flex max-w-lg items-center justify-between gap-3">
            <div class="min-w-0"><p class="text-[11px] text-outline">Total estimasi · demo</p><p class="truncate text-xl font-extrabold text-primary" data-booking-total>Rp {{ number_format($profile['services'][$selectedIndex]['price'], 0, ',', '.') }}</p></div>
            <button type="submit" data-booking-submit disabled form="jw-booking-form" class="flex shrink-0 items-center gap-1 rounded-xl bg-primary px-4 py-3 text-xs font-extrabold text-white sm:text-sm">Konfirmasi <span class="material-symbols-outlined text-[18px]">arrow_forward</span></button>
        </div>
    </div>

    <noscript><p class="fixed bottom-24 left-4 right-4 z-50 rounded-xl bg-error-container p-4 text-sm text-on-error-container">JavaScript harus aktif untuk memvalidasi dan membuat konfirmasi simulasi.</p></noscript>
</div>
@endsection
