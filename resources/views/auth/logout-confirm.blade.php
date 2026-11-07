@extends('layouts.app')
@section('title', 'Keluar | JasaWarga')
@section('content')
<main class="flex min-h-screen items-center justify-center bg-[#F5F8FC] px-4"><section class="w-full max-w-md rounded-2xl border bg-white p-8 text-center shadow-sm"><span class="material-symbols-outlined text-4xl text-primary">logout</span><h1 class="mt-4 text-2xl font-extrabold">Keluar dari JasaWarga?</h1><p class="mt-3 text-sm text-on-surface-variant">Session Laravel akan diakhiri. Data prototipe lokal di browser tidak ikut dihapus.</p><form method="POST" action="{{ route('auth.logout') }}" class="mt-6">@csrf<button class="w-full rounded-xl bg-primary py-3 text-sm font-bold text-white">Ya, keluar</button></form><a href="{{ route('account.profile') }}" class="mt-4 inline-block text-sm font-bold text-primary">Batal</a></section></main>
@endsection
