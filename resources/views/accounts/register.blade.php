@extends('layouts.app')
    @section('title', 'Daftar Akun Warga | JasaWarga')
    @section('content')
    <div id="jw-account" data-account-page="register" class="min-h-screen bg-surface text-on-surface" data-login-url="{{ route('account.login') }}" data-profile-url="{{ route('account.profile') }}" data-home-url="{{ route('home') }}">
      @include('accounts.partials.header', ['pageTitle' => 'Daftar Akun Warga'])
      <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:py-14">
    <div class="mx-auto grid max-w-5xl gap-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
    <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-8">
    <div class="mb-6"><span class="inline-flex items-center gap-1 rounded-full bg-primary-fixed px-3 py-1 text-[11px] font-bold text-primary"><span class="material-symbols-outlined text-[16px]">person_add</span> Pendaftaran simulasi</span><h1 class="mt-3 text-2xl font-extrabold sm:text-3xl">Buat akun Warga</h1><p class="mt-2 text-sm text-on-surface-variant">Daftar dengan identitas fiktif untuk mencoba fitur akun.</p></div>
    <div data-account-error role="alert" class="mb-4 hidden rounded-xl border border-error/30 bg-error-container px-4 py-3 text-sm text-on-error-container"></div>
    <form data-account-register-form class="grid gap-4 sm:grid-cols-2" novalidate>
      <div class="sm:col-span-2"><label for="reg-name" class="mb-1.5 block text-sm font-semibold">Nama lengkap *</label><input id="reg-name" name="name" required minlength="2" maxlength="80" autocomplete="off" placeholder="Contoh: Warga Makassar" class="w-full rounded-xl border-outline-variant/70 focus:border-primary focus:ring-primary"></div>
      <div class="sm:col-span-2"><label for="reg-email" class="mb-1.5 block text-sm font-semibold">Email *</label><input id="reg-email" name="email" type="email" required maxlength="120" autocomplete="off" placeholder="nama@contoh.test" class="w-full rounded-xl border-outline-variant/70 focus:border-primary focus:ring-primary"></div>
      <div class="sm:col-span-2"><label for="reg-phone" class="mb-1.5 block text-sm font-semibold">Nomor WhatsApp simulasi (opsional)</label><input id="reg-phone" name="phone" type="tel" inputmode="numeric" pattern="08[0-9]{8,11}" maxlength="13" autocomplete="off" placeholder="081200000000" class="w-full rounded-xl border-outline-variant/70 focus:border-primary focus:ring-primary"></div>
      <div><label for="reg-password" class="mb-1.5 block text-sm font-semibold">Kata sandi simulasi *</label><input id="reg-password" name="password" type="password" minlength="8" maxlength="72" required autocomplete="off" class="w-full rounded-xl border-outline-variant/70 focus:border-primary focus:ring-primary" placeholder="Minimal 8 karakter"></div>
      <div><label for="reg-confirm" class="mb-1.5 block text-sm font-semibold">Ulangi kata sandi *</label><input id="reg-confirm" name="confirm" type="password" minlength="8" maxlength="72" required autocomplete="off" class="w-full rounded-xl border-outline-variant/70 focus:border-primary focus:ring-primary" placeholder="Ketik ulang"></div>
      <label class="flex items-start gap-2 text-xs leading-relaxed text-on-surface-variant sm:col-span-2"><input type="checkbox" name="agreement" required class="mt-0.5 rounded border-outline-variant text-primary focus:ring-primary"><span>Saya paham ini hanya prototipe, bukan pembuatan akun asli; tidak memasukkan password atau data identitas sebenarnya.</span></label>
      <button type="submit" class="rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white hover:bg-primary-container sm:col-span-2">Daftar Akun Demo</button>
    </form>
    <p class="mt-6 text-center text-sm text-on-surface-variant">Sudah punya akun demo? <a href="{{ route('account.login') }}" class="font-bold text-primary hover:underline">Masuk</a></p>
</section>
    <aside class="space-y-4"><div class="rounded-2xl bg-primary-fixed p-6"><span class="material-symbols-outlined text-3xl text-primary">handshake</span><h2 class="mt-4 text-xl font-extrabold">Jasa lokal, lebih dekat dari rumah.</h2><p class="mt-2 text-sm leading-relaxed text-on-surface-variant">Akun Warga mempermudah akses ke profil, daftar alamat, riwayat pesanan, dan ulasan simulasi.</p><a href="{{ route('categories.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-primary">Jelajahi layanan <span class="material-symbols-outlined text-[18px]">arrow_forward</span></a></div>@include('accounts.partials.notice')</aside>
    </div></main>
      @include('accounts.partials.toast')
    </div>
    @endsection
