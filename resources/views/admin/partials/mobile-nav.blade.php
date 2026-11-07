<nav class="fixed inset-x-0 bottom-0 z-40 flex gap-1 overflow-x-auto border-t border-outline-variant/40 bg-white/95 px-2 pb-[max(10px,env(safe-area-inset-bottom))] pt-2 shadow-[0_-5px_24px_rgba(14,28,49,0.07)] backdrop-blur lg:hidden" aria-label="Navigasi bawah Admin">
@foreach([
    ['route'=>'admin.dashboard','icon'=>'space_dashboard','label'=>'Ringkas'],
    ['route'=>'admin.citizens','icon'=>'groups','label'=>'Warga'],
    ['route'=>'admin.citizen-accounts','icon'=>'database','label'=>'Warga DB'],
    ['route'=>'admin.partners','icon'=>'verified_user','label'=>'Mitra'],
    ['route'=>'admin.partner-applications','icon'=>'fact_check','label'=>'Daftar DB'],
    ['route'=>'admin.db-categories.index','icon'=>'database','label'=>'Kategori DB'],
    ['route'=>'admin.categories','icon'=>'category','label'=>'Kategori'],
    ['route'=>'admin.orders','icon'=>'receipt_long','label'=>'Pesanan'],
    ['route'=>'admin.reviews','icon'=>'forum','label'=>'Ulasan'],
    ['route'=>'admin.reports','icon'=>'summarize','label'=>'Laporan'],
    ['route'=>'admin.settings','icon'=>'settings','label'=>'Atur'],
    ['route'=>'admin.analytics.database','icon'=>'analytics','label'=>'DB'],
    ['route'=>'admin.bookings.index','icon'=>'event_note','label'=>'Pesanan DB'],
    ['route'=>'notifications.index','icon'=>'notifications','label'=>'Notifikasi'],
] as $nav)
    <a href="{{ route($nav['route']) }}" @if(request()->routeIs($nav['route'])) aria-current="page" @endif class="flex min-w-[62px] flex-1 shrink-0 flex-col items-center gap-1 rounded-lg py-1 {{ request()->routeIs($nav['route']) ? 'bg-[#EAF2F8] font-bold text-primary' : 'text-on-surface-variant' }}"><span class="material-symbols-outlined text-[21px]">{{ $nav['icon'] }}</span><span class="max-w-full truncate text-[10px]">{{ $nav['label'] }}</span></a>
@endforeach
</nav>
