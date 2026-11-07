<aside data-ps-sidebar-provider="{{ $provider['id'] }}" class="sticky top-0 hidden h-dvh flex-col border-r border-outline-variant/40 bg-white lg:flex" aria-label="Menu Mitra Jasa">
    <div class="border-b border-outline-variant/30 px-6 py-6">
        <a href="{{ route('partner.dashboard') }}" class="flex items-center gap-3" aria-label="Dashboard JasaWarga Mitra">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary text-white">
                <span class="material-symbols-outlined text-[24px]">handyman</span>
            </span>
            <span class="min-w-0">
                <span class="block text-xl font-extrabold leading-tight tracking-tight text-primary">JasaWarga</span>
                <span class="block text-[10px] font-bold uppercase tracking-[.16em] text-outline">Ruang Mitra · Demo</span>
            </span>
        </a>
    </div>

    <div class="flex-1 overflow-y-auto px-4 py-5">
        <p class="mb-3 px-3 text-[10px] font-extrabold uppercase tracking-[.14em] text-outline">Operasional</p>
        <nav class="space-y-1.5 text-sm" aria-label="Navigasi dashboard mitra">
            <a href="{{ route('partner.dashboard') }}" @if(request()->routeIs('partner.dashboard')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 {{ request()->routeIs('partner.dashboard') ? 'bg-primary-fixed font-extrabold text-primary' : 'font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-[20px]">space_dashboard</span>Ringkasan
            </a>
            <a href="{{ route('partner.orders.index') }}" @if(request()->routeIs('partner.orders.*')) aria-current="page" @endif class="flex w-full items-center gap-3 rounded-xl px-3.5 py-3 text-left transition {{ request()->routeIs('partner.orders.*') ? 'bg-primary-fixed font-extrabold text-primary' : 'font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-[20px]">receipt_long</span>Pesanan masuk
                <span data-partner-pending-pill class="ml-auto hidden min-w-5 rounded-full bg-tertiary-fixed px-1.5 py-0.5 text-center text-[10px] font-bold text-tertiary"></span>
            </a>
            <a href="{{ route('partner.services.index') }}" @if(request()->routeIs('partner.services.*')) aria-current="page" @endif class="flex w-full items-center gap-3 rounded-xl px-3.5 py-3 text-left transition {{ request()->routeIs('partner.services.*') ? 'bg-primary-fixed font-extrabold text-primary' : 'font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-[20px]">build</span>Layanan & tarif
            </a>
            <a href="{{ route('partner.schedule.index') }}" @if(request()->routeIs('partner.schedule.*')) aria-current="page" @endif class="flex w-full items-center gap-3 rounded-xl px-3.5 py-3 text-left transition {{ request()->routeIs('partner.schedule.*') ? 'bg-primary-fixed font-extrabold text-primary' : 'font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-[20px]">calendar_month</span>Jadwal kerja
            </a>
        </nav>

        <p class="mb-3 mt-9 px-3 text-[10px] font-extrabold uppercase tracking-[.14em] text-outline">Akun & etalase</p>
        <nav class="space-y-1.5 text-sm" aria-label="Etalase mitra">
            <a href="{{ route('partner.profile.index') }}" @if(request()->routeIs('partner.profile.*')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 transition {{ request()->routeIs('partner.profile.*') ? 'bg-primary-fixed font-extrabold text-primary' : 'font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-[20px]">badge</span>Profil & portofolio
            </a>
            <a href="{{ route('partner.reviews.index') }}" @if(request()->routeIs('partner.reviews.*')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 transition {{ request()->routeIs('partner.reviews.*') ? 'bg-primary-fixed font-extrabold text-primary' : 'font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-[20px]">reviews</span>Ulasan pelanggan
            </a>
            <a href="{{ route('providers.show', ['id' => $provider['id']]) }}" class="flex items-center gap-3 rounded-xl px-3.5 py-3 font-semibold text-on-surface-variant transition hover:bg-surface-container-low hover:text-primary">
                <span class="material-symbols-outlined text-[20px]">storefront</span>Profil publik
                <span class="material-symbols-outlined ml-auto text-[17px]">open_in_new</span>
            </a>
            <a href="{{ route('partner.settings.index') }}" @if(request()->routeIs('partner.settings.*')) aria-current="page" @endif class="flex w-full items-center gap-3 rounded-xl px-3.5 py-3 text-left transition {{ request()->routeIs('partner.settings.*') ? 'bg-primary-fixed font-extrabold text-primary' : 'font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-[20px]">settings</span>Pengaturan akun
            </a>
        </nav>
    </div>

    <div class="border-t border-outline-variant/30 p-4">
        <div class="flex items-center gap-3 rounded-xl bg-surface-container-low px-3 py-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-fixed font-extrabold text-primary">BT</span>
            <span class="min-w-0 flex-1"><span class="block truncate text-xs font-extrabold">{{ $provider['name'] }}</span><span data-ps-owner-sidebar class="block truncate text-[10px] text-outline">Mitra tersambung (Laravel)</span></span>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="mt-3 flex items-center justify-center gap-2 rounded-xl border border-outline-variant/60 px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container-low"><span class="material-symbols-outlined text-[17px]">shield_person</span>Buka Dashboard Admin (demo)</a>
        <a href="{{ route('home') }}" class="mt-3 flex items-center justify-center gap-2 rounded-xl border border-outline-variant/60 px-3 py-2.5 text-xs font-semibold text-on-surface-variant hover:bg-surface-container-low">
            <span class="material-symbols-outlined text-[17px]">swap_horiz</span>Kembali ke tampilan Warga
        </a>
    </div>
</aside>
