@php
    $jwRole = auth()->user()?->role;
    $jwCitizen = $jwRole === 'citizen';
    $jwPartner = $jwRole === 'partner';
    $jwAdmin = $jwRole === 'admin';
    $jwNavLinks = [
        ['text' => 'Beranda', 'href' => route('home'), 'active' => request()->routeIs('home'), 'icon' => 'home'],
        ['text' => 'Kategori Jasa', 'href' => route('categories.index'), 'active' => request()->routeIs('categories.*', 'providers.show', 'bookings.*'), 'icon' => 'grid_view'],
    ];
    if ($jwCitizen) {
        $jwNavLinks[] = ['text' => 'Pesanan Saya', 'href' => route('account.bookings.index'), 'active' => request()->routeIs('account.bookings.*', 'orders.*'), 'icon' => 'receipt_long'];
        $jwNavLinks[] = ['text' => 'Akun Saya', 'href' => route('account.profile.show'), 'active' => request()->routeIs('account.*'), 'icon' => 'person'];
    } elseif ($jwPartner) {
        $jwNavLinks[] = ['text' => 'Ruang Mitra', 'href' => route('partner.portal'), 'active' => request()->is('mitra/*'), 'icon' => 'handyman'];
    } elseif ($jwAdmin) {
        $jwNavLinks[] = ['text' => 'Dashboard Admin', 'href' => route('admin.analytics.dashboard'), 'active' => request()->is('admin/*'), 'icon' => 'shield_person'];
    } else {
        $jwNavLinks[] = ['text' => 'Masuk', 'href' => route('account.login'), 'active' => request()->routeIs('account.login'), 'icon' => 'login'];
        $jwNavLinks[] = ['text' => 'Daftar', 'href' => route('account.register'), 'active' => request()->routeIs('account.register'), 'icon' => 'person_add'];
    }
@endphp
<header class="sticky top-0 z-50 border-b border-[#d9e4ef] bg-white/95 shadow-sm backdrop-blur" data-jw-site-navbar>
    <div class="mx-auto flex min-h-[70px] max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="inline-flex min-w-0 items-center gap-2.5" aria-label="JasaWarga, kembali ke beranda">
            <img src="{{ asset('assets/brand/jasawarga-icon-256.png') }}" width="43" height="43" alt="Logo JasaWarga" class="h-10 w-10 shrink-0 rounded-xl object-contain sm:h-11 sm:w-11">
            <span class="min-w-0"><strong class="block text-[19px] font-extrabold leading-none tracking-tight text-[#0C3065]">JasaWarga</strong><small class="mt-1 block text-[10px] font-semibold tracking-wide text-slate-500">Layanan lokal · Bandung</small></span>
        </a>
        <nav aria-label="Navigasi utama" class="hidden min-w-0 items-center gap-1 lg:flex">
            @foreach($jwNavLinks as $link)
                <a href="{{ $link['href'] }}" @if($link['active']) aria-current="page" @endif class="rounded-xl px-3.5 py-2.5 text-[13px] transition {{ $link['active'] ? 'bg-[#EBF4FF] font-bold text-[#0C3065]' : 'font-semibold text-slate-600 hover:bg-slate-100 hover:text-[#0C3065]' }}">{{ $link['text'] }}</a>
            @endforeach
        </nav>
        <div class="flex shrink-0 items-center gap-2">
            <span class="hidden items-center gap-1 rounded-full border border-[#e1e8f0] bg-slate-50 px-3 py-2 text-[11px] font-semibold text-slate-600 sm:inline-flex"><span class="material-symbols-outlined text-[16px] text-[#0C3065]">location_on</span> Bandung, Jawa Barat</span>
            @auth
                <a href="{{ $jwCitizen ? route('account.profile.show') : ($jwPartner ? route('partner.portal') : route('admin.analytics.dashboard')) }}" class="flex max-w-[150px] items-center gap-2 rounded-xl border border-[#e1e8f0] bg-white px-2.5 py-2 text-xs font-bold text-[#0C3065] hover:bg-[#EBF4FF]" aria-label="Buka akun {{ auth()->user()->role }}"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-[#E8F4FE] text-[10px]">{{ strtoupper(mb_substr(auth()->user()->name,0,1)) }}</span><span class="hidden truncate sm:inline">{{ auth()->user()->name }}</span></a>
            @endauth
        </div>
    </div>
    <nav aria-label="Navigasi utama mobile" class="flex items-center gap-1 overflow-x-auto border-t border-[#eff3f7] px-3 py-1.5 lg:hidden" style="scrollbar-width:none">
        @foreach($jwNavLinks as $link)
            <a href="{{ $link['href'] }}" @if($link['active']) aria-current="page" @endif class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-2 text-[11px] {{ $link['active'] ? 'bg-[#EBF4FF] font-extrabold text-[#0C3065]' : 'font-semibold text-slate-600' }}"><span class="material-symbols-outlined text-[17px]">{{ $link['icon'] }}</span>{{ $link['text'] }}</a>
        @endforeach
    </nav>
</header>
