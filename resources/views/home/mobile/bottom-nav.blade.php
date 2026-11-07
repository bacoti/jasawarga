@php($jwRole = auth()->user()?->role)
<nav class="fixed bottom-0 left-0 right-0 z-40 mx-auto flex max-w-[480px] items-center justify-around border-t border-slate-200 bg-white/95 px-2 pb-[max(12px,env(safe-area-inset-bottom))] pt-2 shadow-lg backdrop-blur lg:hidden" aria-label="Akses cepat">
    <a href="{{ route('home') }}" class="flex flex-1 flex-col items-center gap-1 py-1 {{ request()->routeIs('home') ? 'font-bold text-primary' : 'text-slate-600' }}"><span class="material-symbols-outlined">home</span><span class="text-[11px]">Beranda</span></a>
    <a href="{{ route('categories.index') }}" class="flex flex-1 flex-col items-center gap-1 py-1 {{ request()->routeIs('categories.*') ? 'font-bold text-primary' : 'text-slate-600' }}"><span class="material-symbols-outlined">grid_view</span><span class="text-[11px]">Jelajahi</span></a>
    @if($jwRole === 'citizen')
        <a href="{{ route('account.bookings.index') }}" class="flex flex-1 flex-col items-center gap-1 py-1 text-slate-600"><span class="material-symbols-outlined">receipt_long</span><span class="text-[11px]">Pesanan</span></a>
        <a href="{{ route('account.profile.show') }}" class="flex flex-1 flex-col items-center gap-1 py-1 text-slate-600"><span class="material-symbols-outlined">person</span><span class="text-[11px]">Akun</span></a>
    @elseif($jwRole === 'partner')
        <a href="{{ route('partner.portal') }}" class="flex flex-1 flex-col items-center gap-1 py-1 text-slate-600"><span class="material-symbols-outlined">handyman</span><span class="text-[11px]">Ruang Mitra</span></a>
    @elseif($jwRole === 'admin')
        <a href="{{ route('admin.analytics.dashboard') }}" class="flex flex-1 flex-col items-center gap-1 py-1 text-slate-600"><span class="material-symbols-outlined">shield_person</span><span class="text-[11px]">Admin</span></a>
    @else
        <a href="{{ route('account.login') }}" class="flex flex-1 flex-col items-center gap-1 py-1 text-slate-600"><span class="material-symbols-outlined">login</span><span class="text-[11px]">Masuk</span></a>
        <a href="{{ route('account.register') }}" class="flex flex-1 flex-col items-center gap-1 py-1 text-slate-600"><span class="material-symbols-outlined">person_add</span><span class="text-[11px]">Daftar</span></a>
    @endif
</nav>
