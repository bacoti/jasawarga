<aside class="hidden lg:block lg:sticky lg:top-[100px]">
  <div class="overflow-hidden rounded-2xl border border-outline-variant/40 bg-white p-4 shadow-sm">
    <div class="flex items-center gap-3 border-b border-outline-variant/30 p-2 pb-5">
      <div data-jw-account-avatar class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-primary-fixed text-lg font-extrabold text-primary">JW</div>
      <div class="min-w-0"><p data-jw-account-name class="truncate text-sm font-extrabold">{{ auth()->user()?->name ?? "Warga JasaWarga" }}</p><p data-jw-account-email class="truncate text-xs text-outline">{{ auth()->user()?->email ?? "" }}</p></div>
    </div>
    <nav class="mt-3 space-y-1 text-sm" aria-label="Menu Warga">
      @foreach([
        ['route'=>'account.analytics.dashboard','label'=>'Dashboard Saya','icon'=>'space_dashboard'],
        ['route'=>'account.profile.show','label'=>'Profil Saya','icon'=>'person'],
        ['route'=>'account.addresses.index','label'=>'Alamat Tersimpan','icon'=>'location_on'],
        ['route'=>'account.bookings.index','label'=>'Pesanan Saya','icon'=>'receipt_long'],
        ['route'=>'reviews.index','label'=>'Ulasan Demo','icon'=>'star'],
        ['route'=>'account.settings.show','label'=>'Pengaturan Akun','icon'=>'settings'],
      ] as $nav)
        <a href="{{ route($nav['route']) }}" @if(request()->routeIs($nav['route'])) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3 py-3 font-semibold transition {{ request()->routeIs($nav['route']) ? 'bg-primary-fixed text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
          <span class="material-symbols-outlined text-[20px]">{{ $nav['icon'] }}</span>{{ $nav['label'] }}
        </a>
      @endforeach
    </nav>
    <form method="POST" action="{{ route('auth.logout') }}" class="mt-4 border-t border-outline-variant/30 pt-3">
      @csrf
      <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-semibold text-error hover:bg-red-50"><span class="material-symbols-outlined">logout</span>Keluar akun</button>
    </form>
  </div>
</aside>
