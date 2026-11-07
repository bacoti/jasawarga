<nav class="fixed inset-x-0 bottom-0 z-40 flex justify-around gap-1 border-t border-outline-variant/40 bg-white/95 px-2 pb-[max(12px,env(safe-area-inset-bottom))] pt-2 shadow-[0_-4px_20px_rgba(14,28,49,.06)] backdrop-blur lg:hidden" aria-label="Navigasi Mitra mobile">
  @foreach([
    ['route'=>'partner.analytics.dashboard','icon'=>'space_dashboard','label'=>'Ringkasan'],
    ['route'=>'partner.bookings.index','icon'=>'receipt_long','label'=>'Pesanan'],
    ['route'=>'partner.schedule.index','icon'=>'calendar_month','label'=>'Jadwal'],
    ['route'=>'partner.profile.edit','icon'=>'storefront','label'=>'Profil'],
    ['route'=>'partner.portal','icon'=>'verified_user','label'=>'Status'],
  ] as $nav)
    <a href="{{ route($nav['route']) }}" @if(request()->routeIs($nav['route'])) aria-current="page" @endif class="flex min-w-0 flex-1 flex-col items-center gap-1 rounded-xl px-1 py-1 {{ request()->routeIs($nav['route']) ? 'font-extrabold text-primary' : 'text-on-surface-variant' }}"><span class="material-symbols-outlined text-[23px]">{{ $nav['icon'] }}</span><span class="text-[10px]">{{ $nav['label'] }}</span></a>
  @endforeach
</nav>
