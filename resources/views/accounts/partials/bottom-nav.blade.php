<nav class="fixed inset-x-0 bottom-0 z-40 flex justify-around gap-1 border-t border-outline-variant/40 bg-white/95 px-2 pb-[max(12px,env(safe-area-inset-bottom))] pt-2 shadow-[0_-4px_20px_rgba(14,28,49,0.06)] backdrop-blur lg:hidden" aria-label="Navigasi bawah Warga">
  @foreach([
    ['route'=>'home','icon'=>'home','label'=>'Beranda'],
    ['route'=>'categories.index','icon'=>'grid_view','label'=>'Jelajahi'],
    ['route'=>'account.analytics.dashboard','icon'=>'space_dashboard','label'=>'Dashboard'],
    ['route'=>'account.bookings.index','icon'=>'receipt_long','label'=>'Pesanan'],
    ['route'=>'account.profile.show','icon'=>'person','label'=>'Akun'],
  ] as $nav)
    <a href="{{ route($nav['route']) }}" @if(request()->routeIs($nav['route'])) aria-current="page" @endif class="flex min-w-0 flex-1 flex-col items-center gap-1 rounded-xl px-1 py-1 text-center {{ request()->routeIs($nav['route']) ? 'font-extrabold text-primary' : 'text-on-surface-variant' }}">
      <span class="material-symbols-outlined text-[23px]">{{ $nav['icon'] }}</span><span class="text-[10px]">{{ $nav['label'] }}</span>
    </a>
  @endforeach
</nav>
