<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Akun JasaWarga')</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F5F8FC] text-[#172B45] antialiased">
@include('components.site-navbar')
<nav class="mx-auto flex max-w-7xl gap-2 overflow-x-auto border-b border-slate-200 bg-white px-4 py-2 text-xs sm:px-6" aria-label="Subnavigasi pengaturan akun">
    @if(auth()->user()?->role === 'citizen')
        <a href="{{ route('account.profile.show') }}" class="whitespace-nowrap rounded-xl px-3 py-2 font-bold text-[#0C3065] hover:bg-slate-100">Profil Saya</a>
        <a href="{{ route('account.addresses.index') }}" class="whitespace-nowrap rounded-xl px-3 py-2 font-bold text-[#0C3065] hover:bg-slate-100">Alamat</a>
        <a href="{{ route('account.bookings.index') }}" class="whitespace-nowrap rounded-xl px-3 py-2 font-bold text-[#0C3065] hover:bg-slate-100">Pesanan</a>
    @elseif(auth()->user()?->role === 'partner')
        <a href="{{ route('partner.profile.edit') }}" class="whitespace-nowrap rounded-xl px-3 py-2 font-bold text-[#0C3065] hover:bg-slate-100">Profil Usaha</a>
        <a href="{{ route('partner.portfolio.index') }}" class="whitespace-nowrap rounded-xl px-3 py-2 font-bold text-[#0C3065] hover:bg-slate-100">Portofolio</a>
        <a href="{{ route('partner.bookings.index') }}" class="whitespace-nowrap rounded-xl px-3 py-2 font-bold text-[#0C3065] hover:bg-slate-100">Pesanan Masuk</a>
    @endif
    @if(in_array(auth()->user()?->role, ['citizen','partner'], true))
       <a href="{{ route('account.settings.show') }}" class="whitespace-nowrap rounded-xl px-3 py-2 font-bold text-[#0C3065] hover:bg-slate-100">Pengaturan</a>
    @endif
    <form class="ml-auto shrink-0" method="POST" action="{{ route('auth.logout') }}">@csrf<button type="submit" class="rounded-xl border border-slate-200 px-3 py-2 font-semibold text-slate-600 hover:bg-slate-100">Keluar</button></form>
</nav>
<main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10">
    @if(session('success'))<div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"><p class="font-bold">Periksa kembali data berikut:</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<footer class="border-t border-slate-200 bg-white py-5 text-center text-xs text-slate-500">© {{ date('Y') }} JasaWarga · Memudahkan warga menemukan jasa lokal.</footer>
<script>
document.addEventListener('change', function (event) {
    const input = event.target.closest('[data-image-preview-input]');
    if (!input || !input.files || !input.files[0]) return;
    const file = input.files[0];
    if (!file.type.startsWith('image/')) return;
    const targetId = input.getAttribute('data-image-preview-input');
    const preview = document.querySelector(`[data-image-preview="${targetId}"]`);
    if (!preview) return;
    if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
    const url = URL.createObjectURL(file);
    preview.src = url;
    preview.dataset.objectUrl = url;
    preview.classList.remove('hidden');
    const fallback = preview.parentElement?.querySelector('[data-image-preview-fallback]');
    if (fallback) fallback.classList.add('hidden');
});
</script>
</body>
</html>
