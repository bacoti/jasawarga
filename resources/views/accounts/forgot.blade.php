@extends('layouts.app')
    @section('title', 'Lupa Password | JasaWarga')
    @section('content')
    <div id="jw-account" data-account-page="forgot" class="min-h-screen bg-surface text-on-surface" data-login-url="{{ route('account.login') }}" data-profile-url="{{ route('account.profile') }}" data-home-url="{{ route('home') }}">
      @include('accounts.partials.header', ['pageTitle' => 'Lupa Password'])
      <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:py-14">
    <div class="mx-auto grid max-w-5xl gap-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
    <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-8">
    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-fixed text-primary"><span class="material-symbols-outlined text-[26px]">lock_reset</span></span><h1 class="mt-4 text-2xl font-extrabold">Pemulihan akun</h1><p class="mt-2 text-sm leading-relaxed text-on-surface-variant">Untuk tahap frontend, belum ada server email atau password yang dapat dipulihkan. Halaman ini hanya demonstrasi interaksi.</p>
    <div data-account-error role="alert" class="mt-4 hidden rounded-xl border border-error/30 bg-error-container px-4 py-3 text-sm text-on-error-container"></div>
    <form data-account-forgot-form class="mt-6 space-y-4" novalidate><div><label for="forgot-email" class="mb-1.5 block text-sm font-semibold">Email akun</label><input id="forgot-email" name="email" type="email" required maxlength="120" autocomplete="off" placeholder="nama@contoh.test" class="w-full rounded-xl border-outline-variant/70 focus:border-primary focus:ring-primary"></div><button type="submit" class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-bold text-white">Coba alur pemulihan</button></form>
    <div data-account-forgot-result role="status" class="mt-4 hidden rounded-xl border border-primary/20 bg-primary-fixed p-4 text-sm text-on-surface-variant">Ini hanya simulasi. Tidak ada email terkirim atau password yang diubah. Kamu bisa kembali ke Login dan menggunakan kata sandi demo apa pun.</div>
    <a href="{{ route('account.login') }}" class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline"><span class="material-symbols-outlined text-[18px]">arrow_back</span> Kembali ke Login</a>
</section>
    <aside class="space-y-4"><div class="rounded-2xl bg-primary-fixed p-6"><span class="material-symbols-outlined text-3xl text-primary">handshake</span><h2 class="mt-4 text-xl font-extrabold">Jasa lokal, lebih dekat dari rumah.</h2><p class="mt-2 text-sm leading-relaxed text-on-surface-variant">Akun Warga mempermudah akses ke profil, daftar alamat, riwayat pesanan, dan ulasan simulasi.</p><a href="{{ route('categories.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-primary">Jelajahi layanan <span class="material-symbols-outlined text-[18px]">arrow_forward</span></a></div>@include('accounts.partials.notice')</aside>
    </div></main>
      @include('accounts.partials.toast')
    </div>
    @endsection
