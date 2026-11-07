@extends('layouts.app')

@section('title', 'Mode Mitra Demo | JasaWarga')

@section('content')
<div id="jw-partner-demo-entry" class="flex min-h-dvh items-center justify-center bg-surface px-4 py-10 text-on-surface">
    <main class="w-full max-w-[460px] overflow-hidden rounded-2xl border border-outline-variant/50 bg-white shadow-sm">
        <div class="border-b border-outline-variant/30 bg-primary-fixed/40 px-6 py-8 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary text-white"><span class="material-symbols-outlined text-[30px]">handyman</span></span>
            <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-primary">JasaWarga Mitra</h1>
            <p class="mt-2 text-sm text-on-surface-variant">Mode demonstrasi dashboard mitra lokal</p>
        </div>
        <div class="space-y-4 px-6 py-7">
            <div class="rounded-xl border border-outline-variant/50 bg-surface-container-low px-4 py-3"><strong class="text-sm">Budi Teknik</strong><p class="mt-1 text-xs text-outline">Mitra contoh · ID 1 · Makassar</p></div>
            <p class="text-sm leading-relaxed text-on-surface-variant">Tekan tombol untuk kembali ke dashboard demo. Tidak ada proses login, password, atau verifikasi akun. Siapa pun yang membuka URL dashboard masih dapat mengaksesnya karena prototipe ini belum menggunakan autentikasi server.</p>
            <button type="button" data-ps-enter class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white hover:bg-primary-container"><span class="material-symbols-outlined">login</span>Masuk ke Mode Mitra Demo</button>
            <a href="{{ route('home') }}" class="block text-center text-sm font-semibold text-primary hover:underline">Kembali ke halaman Warga</a>
        </div>
    </main>
</div>
@endsection
