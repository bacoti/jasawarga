<!DOCTYPE html>
<html lang="id" data-jw-server-auth="{{ auth()->check() ? '1' : '0' }}" data-jw-server-role="{{ auth()->user()?->role ?? '' }}" data-jw-server-name="{{ auth()->user()?->name ?? '' }}" data-jw-server-email="{{ auth()->user()?->email ?? '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#006098">
    <meta name="description" content="JasaWarga, platform layanan jasa lokal Bandung.">
    <title>@yield('title', 'JasaWarga - Solusi Jasa Lokal Tepercaya Bandung')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @include('components.font-imports')
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @if(request()->routeIs('home'))
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
        <script defer src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface font-body-md text-on-surface antialiased selection:bg-secondary-container selection:text-on-secondary-container">
    @include('components.auth-flash-toast')
    @yield('content')
</body>
</html>
