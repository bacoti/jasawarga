@extends('layouts.app')

@section('title', 'Kelola Pesanan Masuk | Mitra JasaWarga')

@section('content')
<div id="jw-partner-orders" class="min-h-screen bg-surface text-on-surface"
     data-partner-id="{{ $provider['id'] }}" data-partner-name="{{ $provider['name'] }}"
     data-warga-orders-url="{{ route('orders.index') }}">
  <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
    @include('partner.partials.sidebar')

    <div class="min-w-0">
      <header class="sticky top-0 z-30 hidden border-b border-outline-variant/40 bg-white/95 backdrop-blur lg:block">
        <div class="mx-auto flex h-[76px] max-w-[1380px] items-center justify-between px-8 xl:px-10">
          <div class="flex items-center gap-2 text-xs font-semibold text-outline"><span>Ruang Mitra</span><span class="material-symbols-outlined text-[16px]">chevron_right</span><span class="text-on-surface">Kelola Pesanan</span></div>
          <div class="flex items-center gap-3"><span class="text-xs text-on-surface-variant">{{ $provider['name'] }}</span><span class="rounded-full bg-primary-fixed px-3 py-1.5 text-[11px] font-extrabold text-primary">DEMO / LOCAL</span></div>
        </div>
      </header>

      <header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-outline-variant/40 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
        <a href="{{ route('partner.dashboard') }}" aria-label="Kembali ke dashboard Mitra" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-outline-variant/60"><span class="material-symbols-outlined">arrow_back</span></a>
        <div class="min-w-0 flex-1"><h1 class="truncate text-base font-extrabold text-primary">Pesanan Masuk</h1><p class="truncate text-[11px] text-outline">{{ $provider['name'] }} · Ruang Mitra</p></div>
        <span class="rounded-full bg-primary-fixed px-2.5 py-1.5 text-[10px] font-bold text-primary">SIMULASI</span>
      </header>

      <main class="mx-auto max-w-[1380px] space-y-6 px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-12 lg:pt-9 xl:px-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
          <div><p class="mb-2 text-[11px] font-extrabold uppercase tracking-[.14em] text-primary">Operasional / Pesanan</p><h1 class="text-[26px] font-extrabold tracking-tight sm:text-[30px]">Kelola pesanan masuk</h1><p class="mt-1 text-sm text-on-surface-variant">Konfirmasi permintaan, kelola proses kerja, dan periksa riwayat.</p></div>
          <a href="{{ route('partner.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-outline-variant/60 bg-white px-4 py-2.5 text-xs font-bold text-on-surface-variant hover:bg-surface-container-low"><span class="material-symbols-outlined text-[18px]">arrow_back</span>Ringkasan dashboard</a>
        </div>

        <div class="flex items-start gap-3 rounded-xl border border-primary/15 bg-primary-fixed/35 px-4 py-3 text-xs leading-relaxed text-on-surface-variant" role="note">
          <span class="material-symbols-outlined mt-0.5 text-[20px] text-primary">info</span>
          <p><strong class="text-on-surface">Mode prototipe.</strong> Hanya pesanan mitra {{ $provider['name'] }} yang muncul. Perubahan tersimpan di <code>localStorage</code> dan terbaca oleh Pesanan Saya (Warga) pada browser dan alamat localhost yang sama. Tidak ada akun Mitra asli, notifikasi pelanggan, atau pemesanan nyata.</p>
        </div>

        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="Statistik pesanan mitra">
          <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5"><div class="flex justify-between gap-2"><span class="text-xs font-semibold text-on-surface-variant">Butuh konfirmasi</span><span class="material-symbols-outlined text-amber-600">pending_actions</span></div><p class="mt-4 text-3xl font-extrabold tabular-nums" data-mpo-pending>0</p><p class="mt-1 text-[11px] text-outline">Pesanan menunggu</p></article>
          <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5"><div class="flex justify-between gap-2"><span class="text-xs font-semibold text-on-surface-variant">Pekerjaan aktif</span><span class="material-symbols-outlined text-primary">engineering</span></div><p class="mt-4 text-3xl font-extrabold tabular-nums" data-mpo-active>0</p><p class="mt-1 text-[11px] text-outline">Diterima hingga dikerjakan</p></article>
          <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5"><div class="flex justify-between gap-2"><span class="text-xs font-semibold text-on-surface-variant">Sudah selesai</span><span class="material-symbols-outlined text-emerald-600">task_alt</span></div><p class="mt-4 text-3xl font-extrabold tabular-nums" data-mpo-completed>0</p><p class="mt-1 text-[11px] text-outline">Riwayat selesai</p></article>
          <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5"><div class="flex justify-between gap-2"><span class="text-xs font-semibold text-on-surface-variant">Ditolak / batal</span><span class="material-symbols-outlined text-outline">block</span></div><p class="mt-4 text-3xl font-extrabold tabular-nums" data-mpo-closed>0</p><p class="mt-1 text-[11px] text-outline">Tidak dilanjutkan</p></article>
        </section>

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_285px]">
          <section class="min-w-0 space-y-4" aria-label="Daftar pesanan masuk">
            <div class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5">
              <div class="flex flex-wrap items-center justify-between gap-2"><h2 class="text-base font-extrabold sm:text-lg">Daftar pekerjaan</h2><span class="text-xs text-outline"><strong data-mpo-found>0</strong> dari <strong data-mpo-total>0</strong> pesanan</span></div>
              <label class="relative mt-4 block"><span class="sr-only">Cari berdasarkan kode, layanan, area, atau nama pelanggan</span><span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[20px] text-outline">search</span><input type="search" data-mpo-search placeholder="Cari kode, layanan, pelanggan, atau kecamatan..." class="w-full rounded-xl border border-outline-variant/60 bg-white py-3 pl-10 pr-3 text-sm focus:border-primary focus:ring-primary"></label>
              <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                <label class="block text-[11px] font-semibold text-on-surface-variant">Tanggal pengerjaan<input type="date" data-mpo-date class="mt-1 w-full rounded-xl border border-outline-variant/60 bg-white text-xs focus:border-primary focus:ring-primary" aria-label="Filter tanggal pengerjaan"></label>
                <label class="block text-[11px] font-semibold text-on-surface-variant">Jenis layanan<select data-mpo-service class="mt-1 w-full rounded-xl border border-outline-variant/60 bg-white text-xs focus:border-primary focus:ring-primary" aria-label="Filter jenis layanan"><option value="all">Semua layanan</option></select></label>
              </div>
              <div class="mt-4 flex gap-2 overflow-x-auto pb-1 no-scrollbar" role="group" aria-label="Filter status pesanan">
                @foreach(['all'=>'Semua', 'pending'=>'Baru', 'active'=>'Aktif', 'completed'=>'Selesai', 'rejected'=>'Ditolak', 'cancelled'=>'Dibatalkan'] as $status => $label)
                  <button type="button" data-mpo-filter="{{ $status }}" aria-pressed="{{ $status === 'all' ? 'true' : 'false' }}" class="shrink-0 rounded-full border border-outline-variant/60 px-3.5 py-2 text-xs font-semibold text-on-surface-variant transition hover:border-primary">{{ $label }}</button>
                @endforeach
              </div>
              <button type="button" data-mpo-reset class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline"><span class="material-symbols-outlined text-[16px]">restart_alt</span>Reset seluruh filter</button>
            </div>

            <div data-mpo-list class="space-y-3" aria-live="polite"></div>
            <div data-mpo-empty class="hidden rounded-2xl border border-dashed border-outline-variant/70 bg-white px-6 py-12 text-center"><span class="material-symbols-outlined text-[44px] text-outline">inbox</span><h3 data-mpo-empty-title class="mt-2 text-base font-extrabold">Belum ada pesanan</h3><p data-mpo-empty-description class="mx-auto mt-2 max-w-sm text-xs leading-relaxed text-on-surface-variant">Coba menambahkan data demo di Dashboard Mitra.</p><a href="{{ route('partner.dashboard') }}" class="mt-4 inline-flex rounded-xl bg-primary px-4 py-2.5 text-xs font-bold text-white">Ke dashboard</a></div>
          </section>

          <aside class="space-y-4 xl:sticky xl:top-[95px]" aria-label="Riwayat dan panduan">
            <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm"><h2 class="text-base font-extrabold">Riwayat pekerjaan</h2><p class="mt-1 text-xs text-outline">Selesai, ditolak, atau dibatalkan</p><div data-mpo-history class="mt-4 space-y-3" aria-live="polite"></div><p data-mpo-history-empty class="mt-3 text-xs text-outline">Belum ada riwayat pekerjaan.</p></section>
            <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm"><h2 class="text-sm font-extrabold">Alur status</h2><ol class="mt-3 space-y-2 text-xs text-on-surface-variant"><li>1. Menunggu → Terima / Tolak</li><li>2. Diterima → Dalam perjalanan</li><li>3. Dalam perjalanan → Dikerjakan</li><li>4. Dikerjakan → Selesai</li></ol><p class="mt-3 text-xs text-outline">Penolakan wajib menyertakan alasan. Status selesai maupun penolakan bersifat final dalam simulasi.</p></section>
            <a href="{{ route('orders.index') }}" class="flex items-center justify-center gap-2 rounded-xl border border-outline-variant/60 bg-white px-3 py-3 text-xs font-bold text-primary hover:bg-surface-container-low"><span class="material-symbols-outlined text-[18px]">swap_horiz</span>Lihat Tracking Warga</a>
          </aside>
        </div>
      </main>
    </div>
  </div>

  @include('partner.partials.mobile-nav')

  {{-- Template untuk satu pesanan. Semua data pelanggan dimasukkan dengan textContent dari JS. --}}
  <template id="jw-mpo-card-template">
    <article class="rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm sm:p-5">
      <div class="flex flex-wrap items-start justify-between gap-2"><div class="min-w-0"><p class="text-[10px] font-semibold uppercase tracking-wide text-outline">Kode pesanan demo</p><p data-mpo-card-ref class="mt-1 break-all font-mono text-xs font-extrabold text-primary"></p></div><span data-mpo-card-status class="rounded-full px-2.5 py-1 text-[11px] font-bold"></span></div>
      <div class="my-4 h-px bg-outline-variant/30"></div>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><div class="flex min-w-0 items-start gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-fixed text-primary"><span class="material-symbols-outlined">handyman</span></span><div class="min-w-0"><h3 data-mpo-card-service class="text-sm font-extrabold"></h3><p data-mpo-card-customer class="mt-1 text-xs text-on-surface-variant"></p><p data-mpo-card-schedule class="mt-1 text-xs text-outline"></p></div></div><p data-mpo-card-price class="text-right text-sm font-extrabold text-primary"></p></div>
      <div class="mt-4 flex flex-wrap items-center justify-end gap-2 border-t border-outline-variant/30 pt-4"><button type="button" data-mpo-view class="rounded-xl border border-outline-variant/70 px-3.5 py-2.5 text-xs font-bold text-on-surface hover:bg-surface-container-low">Lihat detail</button><button type="button" data-mpo-reject class="hidden rounded-xl border border-error/40 px-3.5 py-2.5 text-xs font-bold text-error hover:bg-error-container">Tolak</button><button type="button" data-mpo-next class="hidden rounded-xl bg-primary px-3.5 py-2.5 text-xs font-bold text-white hover:bg-primary-container"></button></div>
    </article>
  </template>

  {{-- Detail: bukan form POST, tidak mengirim data ke backend. --}}
  <dialog id="jw-mpo-detail" aria-labelledby="jw-mpo-detail-title" class="w-[min(94vw,620px)] max-h-[88dvh] rounded-2xl border border-outline-variant/50 bg-white p-0 text-on-surface shadow-2xl backdrop:bg-on-surface/50">
    <div class="sticky top-0 z-10 flex items-center justify-between border-b border-outline-variant/40 bg-white px-5 py-4"><div><h2 id="jw-mpo-detail-title" class="text-base font-extrabold">Detail permintaan</h2><p class="mt-1 text-[11px] text-outline">Informasi pemesanan simulasi</p></div><button type="button" data-mpo-close-detail class="flex h-9 w-9 items-center justify-center rounded-lg bg-surface-container-low" aria-label="Tutup detail"><span class="material-symbols-outlined">close</span></button></div>
    <div class="max-h-[65dvh] space-y-5 overflow-y-auto p-5">
      <div class="flex flex-wrap items-center justify-between gap-2"><strong data-mpo-detail-ref class="break-all font-mono text-sm text-primary"></strong><span data-mpo-detail-status class="rounded-full px-2.5 py-1 text-[11px] font-bold"></span></div>
      <section><h3 class="text-xs font-extrabold uppercase tracking-wide text-outline">Pelanggan</h3><dl class="mt-3 grid grid-cols-[112px_minmax(0,1fr)] gap-x-3 gap-y-2 text-sm"><dt class="text-outline">Nama</dt><dd data-mpo-detail-customer class="break-words font-semibold"></dd><dt class="text-outline">WhatsApp</dt><dd data-mpo-detail-phone class="break-words"></dd></dl></section>
      <section class="border-t border-outline-variant/30 pt-4"><h3 class="text-xs font-extrabold uppercase tracking-wide text-outline">Pekerjaan</h3><dl class="mt-3 grid grid-cols-[112px_minmax(0,1fr)] gap-x-3 gap-y-2 text-sm"><dt class="text-outline">Layanan</dt><dd data-mpo-detail-service class="break-words font-semibold"></dd><dt class="text-outline">Jadwal</dt><dd data-mpo-detail-schedule></dd><dt class="text-outline">Lokasi</dt><dd data-mpo-detail-area class="break-words"></dd><dt class="text-outline">Alamat</dt><dd data-mpo-detail-address class="break-words whitespace-pre-wrap"></dd><dt class="text-outline">Deskripsi</dt><dd data-mpo-detail-description class="break-words whitespace-pre-wrap"></dd><dt class="text-outline">Tarif estimasi</dt><dd data-mpo-detail-price class="font-extrabold text-primary"></dd></dl></section>
      <section class="border-t border-outline-variant/30 pt-4"><h3 class="text-xs font-extrabold uppercase tracking-wide text-outline">Riwayat status</h3><ol data-mpo-detail-history class="mt-3 space-y-3" aria-label="Timeline perubahan status"></ol></section>
    </div>
    <div class="sticky bottom-0 flex flex-wrap justify-end gap-2 border-t border-outline-variant/40 bg-white p-4"><button type="button" data-mpo-close-detail class="rounded-xl border border-outline-variant/70 px-4 py-2.5 text-xs font-semibold">Tutup</button><button type="button" data-mpo-detail-reject class="hidden rounded-xl border border-error/40 px-4 py-2.5 text-xs font-bold text-error">Tolak</button><button type="button" data-mpo-detail-next class="hidden rounded-xl bg-primary px-4 py-2.5 text-xs font-bold text-white"></button></div>
  </dialog>

  {{-- Konfirmasi untuk terima / lanjut / selesai / tolak. --}}
  <dialog id="jw-mpo-confirm" aria-labelledby="jw-mpo-confirm-title" class="w-[min(92vw,450px)] rounded-2xl border border-outline-variant/50 bg-white p-0 text-on-surface shadow-2xl backdrop:bg-on-surface/50">
    <form id="jw-mpo-confirm-form" class="p-5" novalidate>
      <div class="flex items-start justify-between gap-3"><div><h2 id="jw-mpo-confirm-title" class="text-lg font-extrabold"></h2><p data-mpo-confirm-summary class="mt-2 text-sm leading-relaxed text-on-surface-variant"></p></div><button type="button" data-mpo-close-confirm aria-label="Batal dan tutup dialog" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-container-low"><span class="material-symbols-outlined">close</span></button></div>
      <label data-mpo-reason-wrap class="mt-5 hidden text-xs font-bold text-on-surface">Alasan penolakan (wajib, 10–300 karakter)<textarea data-mpo-reason minlength="10" maxlength="300" rows="3" placeholder="Contoh: Jadwal penuh pada waktu yang diminta." class="mt-2 w-full resize-y rounded-xl border border-outline-variant/60 text-sm focus:border-primary focus:ring-primary"></textarea></label>
      <p data-mpo-reason-error role="alert" class="mt-2 hidden text-xs font-semibold text-error"></p>
      <div class="mt-5 flex justify-end gap-2"><button type="button" data-mpo-close-confirm class="rounded-xl border border-outline-variant/70 px-4 py-2.5 text-sm font-semibold">Batal</button><button type="submit" data-mpo-confirm-submit class="rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white">Konfirmasi</button></div>
    </form>
  </dialog>

  <div data-mpo-toast role="status" aria-live="polite" class="pointer-events-none fixed bottom-24 left-1/2 z-[70] hidden w-[min(92vw,440px)] -translate-x-1/2 rounded-xl bg-inverse-surface px-4 py-3 text-center text-xs font-semibold text-white shadow-xl lg:bottom-6"></div>
</div>
@endsection
