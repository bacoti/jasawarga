@extends('layouts.app')
@section('title', 'Masuk | JasaWarga')
@section('content')
<main class="min-h-screen bg-[#F5F8FC] px-4 py-10 sm:py-16">
  <div class="mx-auto max-w-[490px]">
    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-bold text-primary"><span class="material-symbols-outlined">arrow_back</span>Kembali ke JasaWarga</a>
    <section class="mt-6 rounded-2xl border border-outline-variant/40 bg-white p-6 shadow-sm sm:p-9">
      <span class="inline-flex rounded-xl bg-primary-fixed px-3 py-2 text-xs font-extrabold text-primary">AKUN LARAVEL · SESSION</span>
      <h1 class="mt-5 text-3xl font-extrabold tracking-tight">Masuk JasaWarga</h1>
      <p class="mt-2 text-sm leading-6 text-on-surface-variant">Gunakan akun terdaftar di PostgreSQL. Kata sandi divalidasi di server dan tidak tersimpan di browser.</p>
      @if ($errors->any())<div role="alert" class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
      <form method="POST" action="{{ route('auth.login') }}" class="mt-7 space-y-5">
        @csrf
        <div><label for="login-role" class="mb-2 block text-sm font-bold">Masuk sebagai</label><select id="login-role" name="role" required class="w-full rounded-xl border-outline-variant/70 bg-white px-3 py-3 text-sm">
          <option value="citizen" @selected(old('role', $requestedRole)==='citizen')>Warga</option>
          <option value="partner" @selected(old('role', $requestedRole)==='partner')>Mitra</option>
          <option value="admin" @selected(old('role', $requestedRole)==='admin')>Admin</option>
        </select></div>
        <div><label for="login-email" class="mb-2 block text-sm font-bold">Email</label><input id="login-email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required maxlength="180" class="w-full rounded-xl border-outline-variant/70 px-3 py-3 text-sm" placeholder="email@contoh.com"></div>
        <div><label for="login-password" class="mb-2 block text-sm font-bold">Kata sandi</label><input id="login-password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-xl border-outline-variant/70 px-3 py-3 text-sm"></div>
        <label class="flex items-center gap-2 text-sm text-on-surface-variant"><input type="checkbox" name="remember" value="1" class="rounded border-outline-variant text-primary">Ingat sesi masuk</label>
        <button type="submit" class="w-full rounded-xl bg-primary px-5 py-3.5 text-sm font-extrabold text-white hover:bg-primary-container">Masuk dengan akun saya</button>
      </form>
      <div class="mt-6 grid gap-2 border-t border-outline-variant/40 pt-5 text-center text-sm"><a class="font-bold text-primary hover:underline" href="{{ route('account.register') }}">Daftar sebagai Warga</a><a class="font-bold text-primary hover:underline" href="{{ route('partner.register') }}">Daftar sebagai Mitra Jasa</a></div>
      <p class="mt-5 text-xs leading-relaxed text-outline">Admin hanya dapat dibuat oleh pengelola server melalui seeder lokal. Tidak ada fitur daftar Admin publik. Pemulihan password melalui email belum tersedia pada tahap ini.</p>
    </section>
  </div>
</main>
@endsection
