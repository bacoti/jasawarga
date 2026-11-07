@extends('layouts.app')
    @section('title', 'Masuk Akun Warga | JasaWarga')
    @section('content')
    <div id="jw-account" data-account-page="login" class="min-h-screen bg-surface text-on-surface" data-login-url="{{ route('account.login') }}" data-profile-url="{{ route('account.profile') }}" data-home-url="{{ route('home') }}">
      @include('accounts.partials.header', ['pageTitle' => 'Masuk Akun Warga'])
      <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:py-14">
    <div class="mx-auto grid max-w-5xl gap-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
    <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-8">
    <div class="mb-7"><span class="inline-flex items-center gap-1 rounded-full bg-primary-fixed px-3 py-1 text-[11px] font-bold text-primary"><span class="material-symbols-outlined text-[16px]">person</span> Portal Warga · Demo</span><h1 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">Selamat datang kembali</h1><p class="mt-2 text-sm leading-relaxed text-on-surface-variant">Masuk untuk membuka profil akun simulasi JasaWarga.</p></div>
    <div data-account-error role="alert" class="mb-4 hidden rounded-xl border border-error/30 bg-error-container px-4 py-3 text-sm text-on-error-container"></div>
    <form data-account-login-form class="space-y-4" novalidate>
      <div><label for="login-email" class="mb-1.5 block text-sm font-semibold">Alamat email</label><input id="login-email" name="email" type="email" autocomplete="username" required maxlength="120" value="demo@jasawarga.test" class="w-full rounded-xl border-outline-variant/70 focus:border-primary focus:ring-primary" placeholder="email@contoh.test"></div>
      <div><div class="mb-1.5 flex items-center justify-between"><label for="login-password" class="text-sm font-semibold">Kata sandi simulasi</label><a href="{{ route('account.forgot') }}" class="text-xs font-semibold text-primary hover:underline">Lupa password?</a></div><div class="relative"><input id="login-password" name="password" type="password" autocomplete="off" required minlength="6" maxlength="72" class="w-full rounded-xl border-outline-variant/70 pr-14 focus:border-primary focus:ring-primary" placeholder="Minimal 6 karakter"><button type="button" data-account-show-password aria-label="Tampilkan kata sandi" class="absolute right-3 top-1/2 -translate-y-1/2 text-outline"><span class="material-symbols-outlined text-[20px]">visibility</span></button></div></div>
      <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white hover:bg-primary-container">Masuk (Simulasi)</button>
    </form>
    <div class="mt-5 rounded-xl bg-surface-container-low p-4 text-xs leading-relaxed text-on-surface-variant"><strong>Akun contoh:</strong> demo@jasawarga.test. Isi kata sandi demo apa pun minimal 6 karakter; kata sandi tidak diverifikasi, dikirim, atau disimpan. <strong>Jangan gunakan kata sandi asli.</strong></div>
    <p class="mt-6 text-center text-sm text-on-surface-variant">Belum punya akun simulasi? <a href="{{ route('account.register') }}" class="font-bold text-primary hover:underline">Daftar Warga</a></p>
</section>
    <aside class="space-y-4"><div class="rounded-2xl bg-primary-fixed p-6"><span class="material-symbols-outlined text-3xl text-primary">handshake</span><h2 class="mt-4 text-xl font-extrabold">Jasa lokal, lebih dekat dari rumah.</h2><p class="mt-2 text-sm leading-relaxed text-on-surface-variant">Akun Warga mempermudah akses ke profil, daftar alamat, riwayat pesanan, dan ulasan simulasi.</p><a href="{{ route('categories.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-primary">Jelajahi layanan <span class="material-symbols-outlined text-[18px]">arrow_forward</span></a></div>@include('accounts.partials.notice')</aside>
    </div></main>
      @include('accounts.partials.toast')
    </div>
    @endsection
