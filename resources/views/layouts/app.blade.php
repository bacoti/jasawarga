<!DOCTYPE html>
<html lang="id" data-jw-server-auth="{{ auth()->check() ? '1' : '0' }}" data-jw-server-role="{{ auth()->user()?->role ?? '' }}" data-jw-server-name="{{ auth()->user()?->name ?? '' }}" data-jw-server-email="{{ auth()->user()?->email ?? '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#006098">
    <meta name="description" content="JasaWarga, prototipe halaman utama layanan jasa lokal warga Makassar.">
    <title>@yield('title', 'JasaWarga - Solusi Jasa Lokal Tepercaya Makassar')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface font-body-md text-on-surface antialiased selection:bg-secondary-container selection:text-on-secondary-container">
    @if(session('success'))<div role="status" class="mx-auto max-w-6xl px-4 pt-3"><div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div></div>@endif
    @yield('content')
</body>
</html>
