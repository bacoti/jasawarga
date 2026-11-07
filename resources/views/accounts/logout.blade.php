@extends('layouts.app')
    @section('title', 'Keluar Akun Warga | JasaWarga')
    @section('content')
    <div id="jw-account" data-account-page="logout" class="min-h-screen bg-surface text-on-surface" data-login-url="{{ route('account.login') }}" data-profile-url="{{ route('account.profile') }}" data-home-url="{{ route('home') }}">
      @include('accounts.partials.header', ['pageTitle' => 'Keluar Akun Warga'])
      <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:py-14">
    <div class="mx-auto grid max-w-5xl gap-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
    <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-8">
    <div class="py-6 text-center"><span class="material-symbols-outlined text-[56px] text-primary">logout</span><h1 class="mt-4 text-2xl font-extrabold" data-account-logout-title>Sesi simulasi ditutup</h1><p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-on-surface-variant">Kamu telah keluar dari akun demo pada tab ini. Profil tersimpan tetap tersedia pada browser ini, dan riwayat pesanan/ulasan lokal tidak dihapus.</p><div class="mt-7 flex flex-wrap justify-center gap-2"><a href="{{ route('account.login') }}" class="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white">Masuk kembali</a><a href="{{ route('home') }}" class="rounded-xl border border-outline-variant/70 px-5 py-3 text-sm font-semibold">Ke Beranda</a></div></div>
</section>
    <aside class="space-y-4"><div class="rounded-2xl bg-primary-fixed p-6"><span class="material-symbols-outlined text-3xl text-primary">handshake</span><h2 class="mt-4 text-xl font-extrabold">Jasa lokal, lebih dekat dari rumah.</h2><p class="mt-2 text-sm leading-relaxed text-on-surface-variant">Akun Warga mempermudah akses ke profil, daftar alamat, riwayat pesanan, dan ulasan simulasi.</p><a href="{{ route('categories.index') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-primary">Jelajahi layanan <span class="material-symbols-outlined text-[18px]">arrow_forward</span></a></div>@include('accounts.partials.notice')</aside>
    </div></main>
      @include('accounts.partials.toast')
    </div>
    @endsection
