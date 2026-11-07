@extends('layouts.app')
    @section('title', 'Edit Profil | JasaWarga')
    @section('content')
    <div id="jw-account" data-account-page="edit" class="min-h-screen bg-surface text-on-surface" data-login-url="{{ route('account.login') }}" data-profile-url="{{ route('account.profile') }}" data-home-url="{{ route('home') }}">
      @include('accounts.partials.header', ['pageTitle' => 'Edit Profil'])
      <main class="mx-auto max-w-7xl px-4 pb-28 pt-6 sm:px-6 lg:px-8 lg:pb-14 lg:pt-8">
    <nav aria-label="Breadcrumb" class="mb-5 hidden items-center gap-2 text-xs text-outline lg:flex"><a href="{{ route('home') }}" class="hover:text-primary">Beranda</a><span class="material-symbols-outlined text-[16px]">chevron_right</span><a href="{{ route('account.profile') }}" class="hover:text-primary">Akun Warga</a></nav>
    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[255px_minmax(0,1fr)]">@include('accounts.partials.sidebar')<div class="min-w-0 space-y-5">
    <section class="rounded-2xl border border-outline-variant/40 bg-white p-5 shadow-sm sm:p-7"><div class="mb-6"><h1 class="text-xl font-extrabold sm:text-2xl">Edit Profil Warga</h1><p class="mt-1 text-sm text-on-surface-variant">Perbarui nama, nomor kontak simulasi, atau foto profil. Email tidak dapat diubah pada prototype ini.</p></div><div data-account-error role="alert" class="mb-4 hidden rounded-xl border border-error/30 bg-error-container p-4 text-sm text-on-error-container"></div>
    <form data-account-edit-form class="space-y-5" novalidate>
      <div class="flex flex-wrap items-center gap-4 rounded-xl bg-surface-container-low p-4"><div data-account-avatar-preview class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-primary text-xl font-extrabold text-white">JW</div><div class="min-w-0 flex-1"><label for="edit-avatar" class="block text-sm font-bold">Foto profil (opsional)</label><input id="edit-avatar" name="avatar" accept="image/jpeg,image/png,image/webp" type="file" class="mt-2 block w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-primary-fixed file:px-3 file:py-2 file:font-semibold file:text-primary"><p class="mt-2 text-[11px] text-outline">JPG/PNG/WebP · maksimum 2 MB, dikecilkan otomatis.</p><button type="button" data-account-remove-avatar class="mt-2 text-xs font-bold text-error hover:underline">Hapus foto</button></div></div>
      <div><label for="edit-name" class="mb-1.5 block text-sm font-semibold">Nama lengkap *</label><input id="edit-name" name="name" required minlength="2" maxlength="80" class="w-full rounded-xl border-outline-variant/70 focus:border-primary focus:ring-primary"></div>
      <div><label for="edit-email" class="mb-1.5 block text-sm font-semibold">Email (tidak dapat diubah)</label><input id="edit-email" name="email" type="email" disabled class="w-full rounded-xl border-outline-variant/70 bg-surface-container-low text-outline"></div>
      <div><label for="edit-phone" class="mb-1.5 block text-sm font-semibold">WhatsApp simulasi</label><input id="edit-phone" name="phone" type="tel" inputmode="numeric" pattern="08[0-9]{8,11}" maxlength="13" placeholder="081200000000" class="w-full rounded-xl border-outline-variant/70 focus:border-primary focus:ring-primary"></div>
      <div class="flex flex-wrap gap-2"><button type="submit" class="rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primary-container">Simpan perubahan</button><a href="{{ route('account.profile') }}" class="rounded-xl border border-outline-variant/70 px-5 py-3 text-sm font-semibold hover:bg-surface-container-low">Batal</a></div>
    </form></section>
@include('accounts.partials.notice')</div></div></main>@include('accounts.partials.bottom-nav')
      @include('accounts.partials.toast')
    </div>
    @endsection
