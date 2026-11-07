<aside class="hidden lg:block lg:sticky lg:top-[100px]">
  <div class="overflow-hidden rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm">
    <div class="flex items-center gap-3 border-b border-outline-variant/30 p-2 pb-5">
      <div data-jw-account-avatar class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-primary-fixed text-lg font-extrabold text-primary">JW</div>
      <div class="min-w-0"><p data-jw-account-name class="truncate text-sm font-extrabold">Warga JasaWarga</p><p data-jw-account-email class="truncate text-xs text-outline">Akun simulasi</p></div>
    </div>
    <nav class="mt-3 space-y-1 text-sm" aria-label="Menu akun">
      @foreach([
        ['route'=>'account.profile','label'=>'Ringkasan akun','icon'=>'space_dashboard'],
        ['route'=>'account.edit','label'=>'Edit profil','icon'=>'person_edit'],
        ['route'=>'account.addresses','label'=>'Alamat tersimpan','icon'=>'location_on'],
        ['route'=>'orders.index','label'=>'Pesanan saya','icon'=>'receipt_long'],
        ['route'=>'reviews.index','label'=>'Ulasan saya','icon'=>'star'],
        ['route'=>'account.settings','label'=>'Pengaturan','icon'=>'settings'],
      ] as $nav)
        <a href="{{ route($nav['route']) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 font-semibold transition {{ request()->routeIs($nav['route']) ? 'bg-primary-fixed text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
          <span class="material-symbols-outlined text-[20px]">{{ $nav['icon'] }}</span>{{ $nav['label'] }}
        </a>
      @endforeach
    </nav>
    <a href="{{ route('account.logout') }}" class="mt-4 flex items-center gap-3 border-t border-outline-variant/30 px-3 pt-4 text-sm font-semibold text-error hover:underline"><span class="material-symbols-outlined text-[20px]">logout</span> Keluar akun demo</a>
  </div>
</aside>
