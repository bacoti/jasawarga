<header class="bg-surface-container-lowest/90 backdrop-blur-md border-b border-outline-variant/30 sticky top-0 z-50 shadow-sm">
<!-- Top Utility & Brand Bar -->
<div class="max-w-7xl mx-auto px-4 md:px-8 h-16 flex items-center justify-between gap-4"><!-- Brand & Primary Nav Cluster -->
<div class="flex items-center gap-8">
<a class="flex flex-col select-none group" href="#beranda">
<span class="text-headline-md font-headline-md font-bold text-primary tracking-tight group-hover:text-primary-container transition-colors">JasaWarga</span>
<span class="text-[9px] font-label-sm tracking-wider text-outline uppercase font-semibold">Layanan Warga Makassar</span>
</a>
<!-- Simplified Primary Nav Links -->
<nav class="hidden md:flex items-center gap-6">
<a class="text-primary font-label-lg font-semibold border-b-2 border-primary pb-1" href="#beranda">Beranda</a>
<a class="text-on-surface-variant hover:text-primary font-label-lg transition-colors pb-1" data-jw-action="categories" href="#kategori-layanan">Kategori Jasa</a>
<a class="text-on-surface-variant hover:text-primary font-label-lg transition-colors pb-1" href="{{ route('orders.index') }}">Pesanan Saya</a><a class="text-on-surface-variant hover:text-primary font-label-lg transition-colors pb-1" href="{{ route('partner.demo.entry') }}">Ruang Mitra</a><a class="text-on-surface-variant hover:text-primary font-label-lg transition-colors pb-1" href="{{ route('admin.demo.entry') }}">Ruang Admin</a><a class="text-on-surface-variant hover:text-primary font-label-lg transition-colors pb-1" data-jw-action="help" href="#bantuan">Bantuan Warga</a>
</nav>
</div>
<!-- Right Cluster: Location Selector & Citizen Profile -->
<div class="flex items-center gap-3">
<!-- Location Selector Pill -->
<button aria-label="Ganti lokasi" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container-low hover:bg-surface-container text-on-surface-variant transition-colors border border-outline-variant/40 group active:scale-[0.98]" data-jw-action="location" type="button">
<span class="material-symbols-outlined text-primary text-[18px]" style="font-variation-settings: 'FILL' 1;">location_on</span>
<span class="font-body-sm text-body-sm font-medium text-on-surface" data-jw-location-label="true">Panakkukang, Makassar</span>
<span class="material-symbols-outlined text-outline text-[16px] group-hover:text-primary transition-colors">expand_more</span>
</button>
<!-- Notification Button -->
<button class="relative p-2 rounded-xl text-on-surface-variant hover:bg-surface-container-low transition-colors" data-jw-action="notifications" title="Notifikasi" type="button">
<span class="material-symbols-outlined text-[20px]">notifications</span>
<span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-tertiary"></span>
</button>
<!-- Simplified Friendly Citizen Profile -->
<a href="{{ route('account.profile') }}" data-jw-account-link class="flex items-center gap-2 rounded-xl border-l border-outline-variant/30 pl-3 hover:text-primary" aria-label="Buka akun Warga">
 <div data-jw-account-avatar class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-fixed text-xs font-extrabold text-primary ring-2 ring-primary/15">JW</div>
 <div class="hidden flex-col text-left sm:flex">
  <span data-jw-account-name class="max-w-[115px] truncate text-xs font-bold text-on-surface">Masuk / Daftar</span>
  <span class="text-[11px] text-outline">Akun Warga</span>
 </div>
</a>
</div></div>
<!-- Secondary Sticky Search Bar Container -->
<div class="bg-surface-container-low/70 border-t border-outline-variant/20 px-4 md:px-8 py-2.5">
<div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-3">
<!-- Large Search Bar Input -->
<div class="relative flex-1 max-w-3xl">
<div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-outline">
<span class="material-symbols-outlined text-[20px]">search</span>
</div>
<input aria-label="Cari mitra jasa" autocomplete="off" class="w-full pl-10 pr-24 py-2 bg-surface-container-lowest border border-outline-variant/60 rounded-xl font-body-md text-on-surface placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all shadow-sm" data-jw-search="true" placeholder="Cari tukang, servis AC, jahit, teknisi..." type="text"/>
<div class="absolute inset-y-0 right-1.5 flex items-center gap-1">
<button class="px-2.5 py-1 text-on-surface-variant hover:text-primary hover:bg-surface-container-low rounded-lg transition-colors flex items-center gap-1 font-label-md text-label-md" data-jw-action="filter-help" type="button">
<span class="material-symbols-outlined text-[18px]">tune</span>
<span class="hidden sm:inline">Filter</span>
</button>
<button class="px-3 py-1 bg-primary text-on-primary rounded-lg font-label-md text-label-md hover:bg-primary-container active:scale-[0.98] transition-all" data-jw-action="search" type="button">
              Cari
            </button>
</div>
</div>
<!-- Quick Trending Badges -->
<div class="hidden lg:flex items-center gap-2 text-on-surface-variant">
<span class="font-label-sm text-outline uppercase tracking-wider">Tren Makassar:</span>
<a class="px-2.5 py-1 rounded-full bg-surface-container-lowest border border-outline-variant/40 hover:border-primary font-label-sm text-on-surface transition-colors" data-jw-action="link-preview" href="#">Cuci AC Panakkukang</a>
<a class="px-2.5 py-1 rounded-full bg-surface-container-lowest border border-outline-variant/40 hover:border-primary font-label-sm text-on-surface transition-colors" data-jw-action="link-preview" href="#">Sedot WC Rappocini</a>
<a class="px-2.5 py-1 rounded-full bg-surface-container-lowest border border-outline-variant/40 hover:border-primary font-label-sm text-on-surface transition-colors" data-jw-action="link-preview" href="#">Tukang Talang Bocor</a>
</div>
</div>
</div>
</header>