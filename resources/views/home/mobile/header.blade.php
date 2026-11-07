@include('components.site-navbar')
<div class="space-y-2 border-b border-[#dce6f0] bg-white px-4 py-3">

<div class="flex items-center justify-between bg-surface-container-low px-3.5 py-2.5 rounded-xl border border-outline-variant/30 mb-2.5">
<div class="flex items-center gap-2 text-on-surface truncate">
<span class="material-symbols-outlined text-primary text-[20px] shrink-0">location_on</span>
<span class="font-bold text-body-md text-on-surface truncate" data-jw-location-label="true">Coblong, Bandung</span>
</div>
<button aria-label="Ganti lokasi" class="text-body-sm font-bold text-primary bg-surface-container-lowest px-2.5 py-1 rounded-lg border border-outline-variant/30 hover:underline shrink-0 ml-2" data-jw-action="location" type="button">
    Ganti
  </button>
</div>
<!-- Kotak Pencarian Jelas -->
<div class="relative">
<span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[22px]">search</span>
<input aria-label="Cari mitra jasa" autocomplete="off" class="w-full h-12 pl-11 pr-3 rounded-xl bg-surface-container text-body-md text-on-surface placeholder:text-outline border-none ring-1 ring-outline-variant/30 focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-all" data-jw-search="true" placeholder="Cari tukang, servis AC, pipa..." type="text"/>
</div></div>
