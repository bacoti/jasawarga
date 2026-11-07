@extends('layouts.app')
@section('title', 'Daftar Mitra | JasaWarga')
@section('content')
<main class="min-h-screen bg-[#F5F8FC] px-4 py-10 sm:py-14"><div class="mx-auto max-w-[720px]"><a href="{{ route('home') }}" class="text-sm font-bold text-primary">← Kembali ke Beranda</a>
<section class="mt-6 rounded-2xl border border-outline-variant/40 bg-white p-6 shadow-sm sm:p-9"><p class="text-xs font-extrabold uppercase tracking-widest text-primary">Mitra / Pendaftaran nyata di database lokal</p><h1 class="mt-3 text-3xl font-extrabold">Daftar Mitra Jasa</h1><p class="mt-2 text-sm text-on-surface-variant">Setelah mendaftar, profil berstatus <strong>menunggu verifikasi Admin</strong>. Belum otomatis tampil di katalog Mitra contoh.</p>
@if ($errors->any())<div role="alert" class="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('auth.register.partner') }}" class="mt-7 grid gap-4 sm:grid-cols-2">@csrf
@foreach([['name','Nama pemilik'],['email','Email'],['phone','WhatsApp pemilik (opsional)'],['business_name','Nama usaha'],['business_phone','WhatsApp usaha'],['service_area','Area layanan']] as $item)
<div class="{{ in_array($item[0], ['name','email','business_name']) ? 'sm:col-span-2' : '' }}"><label for="{{ $item[0] }}" class="mb-1.5 block text-sm font-bold">{{ $item[1] }}</label><input id="{{ $item[0] }}" name="{{ $item[0] }}" type="{{ $item[0]==='email' ? 'email' : 'text' }}" value="{{ old($item[0]) }}" {{ $item[0] !== 'phone' ? 'required' : '' }} maxlength="{{ $item[0]==='email' ? 180 : 120 }}" class="w-full rounded-xl border-outline-variant/70 px-3 py-3 text-sm"></div>
@endforeach
<div class="sm:col-span-2"><label for="description" class="mb-1.5 block text-sm font-bold">Deskripsi usaha (opsional)</label><textarea id="description" name="description" rows="3" maxlength="1000" class="w-full rounded-xl border-outline-variant/70 px-3 py-3 text-sm">{{ old('description') }}</textarea></div>
<div><label for="password" class="mb-1.5 block text-sm font-bold">Password</label><input id="password" type="password" name="password" required minlength="10" autocomplete="new-password" class="w-full rounded-xl border-outline-variant/70 px-3 py-3"></div>
<div><label for="password_confirmation" class="mb-1.5 block text-sm font-bold">Konfirmasi password</label><input id="password_confirmation" type="password" name="password_confirmation" required minlength="10" autocomplete="new-password" class="w-full rounded-xl border-outline-variant/70 px-3 py-3"></div>
<p class="sm:col-span-2 text-xs text-outline">Password minimal 10 karakter, huruf besar, huruf kecil, dan angka. Nomor WhatsApp memakai format 08 + 8–11 digit.</p>
<label class="sm:col-span-2 flex items-start gap-2 text-xs text-on-surface-variant"><input type="checkbox" name="terms" value="1" required class="mt-0.5 rounded"><span>Saya memahami pengajuan akan ditinjau Admin; status disetujui tidak menjamin akses ke dashboard Mitra dummy yang ada.</span></label>
<button class="rounded-xl bg-primary py-3.5 text-sm font-extrabold text-white sm:col-span-2">Kirim Pengajuan Mitra</button></form>
<p class="mt-6 text-center text-sm">Sudah terdaftar? <a href="{{ route('account.login', ['role'=>'partner']) }}" class="font-bold text-primary">Masuk Mitra</a></p></section></div></main>
@endsection
