@extends('layouts.app')

@section('content')
    <script type="application/json" id="jw-home-categories-seed">@json(config('jasawarga.categories', []))</script>
    {{-- Kedua mode mempertahankan komposisi HTML asli. lg: adalah breakpoint 1024px. --}}
    <div class="jw-experience jw-mobile block min-h-screen bg-surface-container-low lg:hidden">
        <div class="relative mx-auto flex min-h-screen w-full max-w-[480px] flex-col border-x border-outline-variant/30 bg-background pb-28 shadow-2xl">
            @include('home.mobile.header')
            @include('home.mobile.content')
            @include('home.mobile.bottom-nav')
        </div>
    </div>

    <div class="jw-experience jw-desktop hidden min-h-screen flex-col bg-surface text-on-surface lg:flex">
        @include('home.desktop.header')
        @include('home.desktop.content')
        @include('home.desktop.footer')
    </div>

    @include('components.prototype-dialog')
    @include('components.toast')
@endsection
