@extends('layouts.app')
@section('title', $title.' | JasaWarga')
@section('content')
<main class="min-h-screen bg-[#F5F8FC] px-4 py-16"><div class="mx-auto max-w-lg rounded-2xl border bg-white p-7 shadow-sm"><span class="material-symbols-outlined text-3xl text-primary">info</span><h1 class="mt-4 text-2xl font-extrabold">{{ $title }}</h1><p class="mt-3 text-sm leading-relaxed text-on-surface-variant">Fitur ini sudah mempunyai rancangan frontend prototipe, tetapi data alamat dan preferensi lama masih disimpan pada browser, bukan pada akun PostgreSQL. Agar tidak menampilkan data pengguna lain yang tersimpan di browser ini, integrasi database akan dilakukan pada tahap backend berikutnya.</p><a href="{{ route('account.profile') }}" class="mt-6 inline-block rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white">Kembali ke profil</a></div></main>
@endsection
