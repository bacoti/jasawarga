@extends('layouts.app')
@section('title', 'Daftar Warga | JasaWarga')
@section('content')
<main class="min-h-screen bg-[#F5F8FC] px-4 py-10 sm:py-14"><div class="mx-auto max-w-[640px]"><a href="{{ route('home') }}" class="text-sm font-bold text-primary">← Kembali ke Beranda</a>
<section class="mt-6 rounded-2xl border border-outline-variant/40 bg-white p-6 shadow-sm sm:p-9"><p class="text-xs font-extrabold uppercase tracking-widest text-primary">Pendaftaran akun database</p><h1 class="mt-3 text-3xl font-extrabold">Daftar sebagai Warga</h1><p class="mt-2 text-sm text-on-surface-variant">Akun disimpan di PostgreSQL. Pesanan prototype sebelumnya tetap tersimpan terpisah di browser.</p>
@if ($errors->any())<div role="alert" class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('auth.register.citizen') }}" class="mt-7 grid gap-4 sm:grid-cols-2">@csrf
<div class="sm:col-span-2"><label for="name" class="mb-1.5 block text-sm font-bold">Nama lengkap *</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="100" class="w-full rounded-xl border-outline-variant/70 px-3 py-3"></div>
<div class="sm:col-span-2"><label for="email" class="mb-1.5 block text-sm font-bold">Email *</label><input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="180" class="w-full rounded-xl border-outline-variant/70 px-3 py-3"></div>
<div class="sm:col-span-2"><label for="phone" class="mb-1.5 block text-sm font-bold">WhatsApp (opsional)</label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" pattern="08[0-9]{8,11}" class="w-full rounded-xl border-outline-variant/70 px-3 py-3" placeholder="081200000000"></div>
<div><label for="password" class="mb-1.5 block text-sm font-bold">Password *</label><input id="password" name="password" type="password" autocomplete="new-password" required minlength="10" class="w-full rounded-xl border-outline-variant/70 px-3 py-3"></div>
<div><label for="password_confirmation" class="mb-1.5 block text-sm font-bold">Konfirmasi *</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="10" class="w-full rounded-xl border-outline-variant/70 px-3 py-3"></div>
<p class="sm:col-span-2 text-xs text-outline">Minimal 10 karakter, huruf besar, huruf kecil, dan angka.</p>
<label class="sm:col-span-2 flex items-start gap-2 text-xs text-on-surface-variant"><input type="checkbox" name="terms" value="1" required class="mt-0.5 rounded border-outline-variant"><span>Saya menyetujui pembuatan akun lokal pada server pengembangan ini.</span></label>
<button type="submit" class="rounded-xl bg-primary py-3.5 text-sm font-extrabold text-white sm:col-span-2">Buat Akun Warga</button></form><p class="mt-6 text-center text-sm">Sudah punya akun? <a href="{{ route('account.login') }}" class="font-bold text-primary">Masuk</a></p>
</section></div></main>
@endsection
