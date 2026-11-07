@extends('layouts.app')
@section('title', 'Bantuan Password | JasaWarga')
@section('content')
<main class="flex min-h-screen items-center justify-center bg-[#F5F8FC] px-4"><section class="w-full max-w-md rounded-2xl border bg-white p-8 shadow-sm"><h1 class="text-2xl font-extrabold">Lupa password?</h1><p class="mt-3 text-sm leading-relaxed text-on-surface-variant">Reset password via email belum diterapkan pada tahap pertama. Untuk akun pengembangan, mintalah administrator proyek melakukan reset melalui jalur Laravel yang aman, bukan membagikan password melalui chat.</p><a href="{{ route('account.login') }}" class="mt-6 inline-flex rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white">Kembali ke login</a></section></main>
@endsection
