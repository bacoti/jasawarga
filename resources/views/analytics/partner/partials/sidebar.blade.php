<aside class="sticky top-0 hidden h-dvh w-[248px] shrink-0 flex-col border-r border-outline-variant/40 bg-white lg:flex" aria-label="Sidebar Mitra">
  <div class="border-b border-outline-variant/30 px-5 py-6"><a href="{{ route('partner.analytics.dashboard') }}" class="flex items-center gap-3"><img src="{{ asset('assets/brand/jasawarga-icon-256.png') }}" class="h-11 w-11 rounded-xl object-cover" alt="Logo JasaWarga"><span><strong class="block text-xl font-extrabold text-primary">JasaWarga</strong><small class="text-[10px] font-bold uppercase tracking-widest text-outline">Ruang Mitra</small></span></a></div>
  <div class="min-h-0 flex-1 overflow-y-auto px-4 py-5">
    <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-widest text-outline">Operasional Mitra</p>
    @foreach([
      ['route'=>'partner.analytics.dashboard','icon'=>'space_dashboard','label'=>'Ringkasan'],
      ['route'=>'partner.bookings.index','icon'=>'receipt_long','label'=>'Pesanan Masuk'],
      ['route'=>'partner.schedule.index','icon'=>'calendar_month','label'=>'Jadwal Kerja'],
      ['route'=>'partner.profile.edit','icon'=>'storefront','label'=>'Profil Usaha'],
      ['route'=>'partner.portfolio.index','icon'=>'imagesmode','label'=>'Portofolio'],
      ['route'=>'partner.portal','icon'=>'verified_user','label'=>'Status Verifikasi'],
      ['route'=>'account.settings.show','icon'=>'settings','label'=>'Pengaturan Akun'],
    ] as $nav)
      <a href="{{ route($nav['route']) }}" @if(request()->routeIs($nav['route'])) aria-current="page" @endif class="mb-1 flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm {{ request()->routeIs($nav['route']) ? 'bg-primary-fixed font-extrabold text-primary' : 'font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}"><span class="material-symbols-outlined text-[20px]">{{ $nav['icon'] }}</span>{{ $nav['label'] }}</a>
    @endforeach
    @if($partner->verification_status === 'approved' && ! $partner->reverification_required)
      <a href="{{ route('partner.db-services.index') }}" class="mb-1 flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-primary"><span class="material-symbols-outlined text-[20px]">build</span>Layanan &amp; Tarif</a>
      <a href="{{ route('partner.db-feedback.reviews.index') }}" class="mb-1 flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-primary"><span class="material-symbols-outlined text-[20px]">reviews</span>Ulasan Pelanggan</a>
    @endif
  </div>
  <div class="border-t border-outline-variant/30 p-4"><div class="mb-3 rounded-xl bg-surface-container-low p-3"><strong class="block truncate text-xs">{{ $partner->business_name }}</strong><span class="mt-1 block truncate text-[11px] text-outline">{{ auth()->user()->email }}</span></div><form method="POST" action="{{ route('auth.logout') }}">@csrf<button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-outline-variant/60 px-3 py-2.5 text-xs font-bold text-on-surface-variant hover:bg-surface-container-low"><span class="material-symbols-outlined text-[17px]">logout</span>Keluar akun</button></form></div>
</aside>
