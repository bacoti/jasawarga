@include('components.site-navbar')
<div class="border-b border-[#dce6f0] bg-white">

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
<span class="font-label-sm text-outline uppercase tracking-wider">Tren Bandung:</span>
<a class="px-2.5 py-1 rounded-full bg-surface-container-lowest border border-outline-variant/40 hover:border-primary font-label-sm text-on-surface transition-colors" data-jw-action="link-preview" href="#">Cuci AC Coblong</a>
<a class="px-2.5 py-1 rounded-full bg-surface-container-lowest border border-outline-variant/40 hover:border-primary font-label-sm text-on-surface transition-colors" data-jw-action="link-preview" href="#">Sedot WC Sukajadi</a>
<a class="px-2.5 py-1 rounded-full bg-surface-container-lowest border border-outline-variant/40 hover:border-primary font-label-sm text-on-surface transition-colors" data-jw-action="link-preview" href="#">Tukang Talang Bocor</a>
</div>
</div>
</div>
</div>
