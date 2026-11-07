<header class="sticky top-0 z-40 border-b border-outline-variant/40 bg-white/95 backdrop-blur">
  <div class="mx-auto hidden h-[76px] max-w-7xl items-center justify-between gap-6 px-8 lg:flex">
    <div class="flex items-center gap-9">
      <a href="{{ route('home') }}" class="flex flex-col leading-none" aria-label="Beranda JasaWarga">
        <span class="text-[22px] font-extrabold tracking-tight text-primary">JasaWarga</span>
        <span class="mt-1 text-[10px] font-semibold uppercase tracking-[0.11em] text-outline">Layanan Warga Makassar</span>
      </a>
      <nav class="flex items-center gap-7 text-sm" aria-label="Navigasi utama">
        <a href="{{ route('home') }}" class="text-on-surface-variant hover:text-primary">Beranda</a>
        <a href="{{ route('categories.index') }}" class="text-on-surface-variant hover:text-primary">Kategori Jasa</a>
        <a href="{{ route('orders.index') }}" class="text-on-surface-variant hover:text-primary">Pesanan Saya</a>
        <a href="{{ route('account.profile') }}" class="border-b-2 border-primary pb-1 font-bold text-primary">Akun Warga</a>
      </nav>
    </div>
    <span class="inline-flex items-center gap-2 rounded-full bg-primary-fixed px-3 py-2 text-xs font-bold text-primary"><span class="material-symbols-outlined text-[17px]">science</span> Akun simulasi</span>
  </div>
  <div class="mx-auto flex max-w-lg items-center gap-3 px-4 py-3 lg:hidden">
    <a href="{{ route('home') }}" aria-label="Kembali ke beranda" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-container-low"><span class="material-symbols-outlined">arrow_back</span></a>
    <div class="min-w-0 flex-1"><h1 class="truncate text-base font-extrabold">{{ $pageTitle ?? 'Akun Warga' }}</h1><p class="text-xs text-outline">JasaWarga · Makassar</p></div>
    <span class="rounded-full bg-primary-fixed px-2.5 py-1 text-[10px] font-extrabold text-primary">DEMO</span>
  </div>
</header>
