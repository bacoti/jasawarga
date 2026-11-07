<aside class="sticky top-0 hidden h-dvh w-[246px] shrink-0 flex-col bg-[#13283f] text-white lg:flex" aria-label="Sidebar Admin">
    <div class="border-b border-white/10 px-6 py-6">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3" aria-label="Dashboard Admin JasaWarga">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#2477ac] text-white">
                <span class="material-symbols-outlined text-[23px]">shield_person</span>
            </span>
            <span><strong class="block text-[21px] font-extrabold tracking-tight">JasaWarga</strong><span class="block text-[10px] font-bold uppercase tracking-[.18em] text-blue-200">Ruang Admin · Demo</span></span>
        </a>
    </div>
    <div class="min-h-0 flex-1 overflow-y-auto px-3 py-6">
        <p class="px-3 pb-3 text-[10px] font-bold uppercase tracking-[.16em] text-[#92abc3]">Operasional platform</p>
        <nav aria-label="Navigasi admin" class="space-y-1.5 text-[13px]">
            <a href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-[#295474] font-bold text-white' : 'font-medium text-[#c9d7e5] hover:bg-white/10 hover:text-white' }}"><span class="material-symbols-outlined text-[20px]">space_dashboard</span>Ringkasan Admin</a>
            <a href="{{ route('admin.citizens') }}" @if(request()->routeIs('admin.citizens')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 transition {{ request()->routeIs('admin.citizens') ? 'bg-[#295474] font-bold text-white' : 'font-medium text-[#c9d7e5] hover:bg-white/10 hover:text-white' }}"><span class="material-symbols-outlined text-[20px]">groups</span>Manajemen Warga</a>
            <a href="{{ route('admin.partners') }}" @if(request()->routeIs('admin.partners')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 transition {{ request()->routeIs('admin.partners') ? 'bg-[#295474] font-bold text-white' : 'font-medium text-[#c9d7e5] hover:bg-white/10 hover:text-white' }}"><span class="material-symbols-outlined text-[20px]">verified_user</span>Mitra & Verifikasi</a>
            <a href="{{ route('admin.citizen-accounts') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-[#c9d7e5] hover:bg-white/10 hover:text-white"><span class="material-symbols-outlined text-[20px]">database</span>Akun Warga (DB)</a>
            <a href="{{ route('admin.partner-applications') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-[#c9d7e5] hover:bg-white/10 hover:text-white"><span class="material-symbols-outlined text-[20px]">verified_user</span>Pendaftaran Mitra (DB)</a>
            <a href="{{ route('admin.db-categories.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-[13px] font-semibold text-[#c9d7e5] hover:bg-white/10 hover:text-white"><span class="material-symbols-outlined text-[20px]">database</span>Kategori (PostgreSQL)</a>
            <a href="{{ route('admin.categories') }}" @if(request()->routeIs('admin.categories')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 transition {{ request()->routeIs('admin.categories') ? 'bg-[#295474] font-bold text-white' : 'font-medium text-[#c9d7e5] hover:bg-white/10 hover:text-white' }}"><span class="material-symbols-outlined text-[20px]">category</span>Kategori Jasa</a>
            <a href="{{ route('admin.orders') }}" @if(request()->routeIs('admin.orders')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 transition {{ request()->routeIs('admin.orders') ? 'bg-[#295474] font-bold text-white' : 'font-medium text-[#c9d7e5] hover:bg-white/10 hover:text-white' }}"><span class="material-symbols-outlined text-[20px]">receipt_long</span>Monitoring Pesanan</a>
            <a href="{{ route('admin.reviews') }}" @if(request()->routeIs('admin.reviews')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 transition {{ request()->routeIs('admin.reviews') ? 'bg-[#295474] font-bold text-white' : 'font-medium text-[#c9d7e5] hover:bg-white/10 hover:text-white' }}"><span class="material-symbols-outlined text-[20px]">forum</span>Ulasan & Pengaduan</a>
        </nav>
        <p class="px-3 pb-3 pt-8 text-[10px] font-bold uppercase tracking-[.16em] text-[#92abc3]">Operasional PostgreSQL</p>
        <nav aria-label="Modul database" class="space-y-1.5 text-[13px]">
            <a href="{{ route('admin.analytics.dashboard') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[#c9d7e5] hover:bg-white/10"><span class="material-symbols-outlined text-[19px]">monitoring</span>Dashboard DB</a>
            <a href="{{ route('admin.bookings.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[#c9d7e5] hover:bg-white/10"><span class="material-symbols-outlined text-[19px]">event_note</span>Pesanan DB</a>
            <a href="{{ route('admin.db-feedback.reviews.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[#c9d7e5] hover:bg-white/10"><span class="material-symbols-outlined text-[19px]">reviews</span>Moderasi Ulasan DB</a>
            <a href="{{ route('admin.db-feedback.complaints.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[#c9d7e5] hover:bg-white/10"><span class="material-symbols-outlined text-[19px]">support_agent</span>Pengaduan DB</a>
            <a href="{{ route('admin.analytics.reports') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[#c9d7e5] hover:bg-white/10"><span class="material-symbols-outlined text-[19px]">analytics</span>Laporan DB</a>
            <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[#c9d7e5] hover:bg-white/10"><span class="material-symbols-outlined text-[19px]">notifications</span>Notifikasi</a>
        </nav>
        <p class="px-3 pb-3 pt-8 text-[10px] font-bold uppercase tracking-[.16em] text-[#92abc3]">Analisis dan sistem</p>
        <nav aria-label="Analitik admin" class="space-y-1.5 text-[13px]">
            <a href="{{ route('admin.dashboard') }}#jw-admin-chart-section" class="flex items-center gap-3 rounded-xl px-3.5 py-3 font-medium text-[#c9d7e5] transition hover:bg-white/10 hover:text-white"><span class="material-symbols-outlined text-[20px]">bar_chart</span>Aktivitas 7 Hari</a>
            <a href="{{ route('admin.reports') }}" @if(request()->routeIs('admin.reports')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 transition {{ request()->routeIs('admin.reports') ? 'bg-[#295474] font-bold text-white' : 'font-medium text-[#c9d7e5] hover:bg-white/10 hover:text-white' }}"><span class="material-symbols-outlined text-[20px]">summarize</span>Laporan & Analitik</a>
            <a href="{{ route('admin.settings') }}" @if(request()->routeIs('admin.settings')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 transition {{ request()->routeIs('admin.settings') ? 'bg-[#295474] font-bold text-white' : 'font-medium text-[#c9d7e5] hover:bg-white/10 hover:text-white' }}"><span class="material-symbols-outlined text-[20px]">settings</span>Pengaturan Admin</a>
        </nav>
    </div>
    <div class="border-t border-white/10 p-4">
        <div class="flex items-center gap-3 rounded-xl bg-white/10 px-3 py-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-sm font-bold">AD</span>
            <div><p data-admin-display-name class="max-w-[135px] truncate text-xs font-bold text-white">Administrator Demo</p><p class="text-[11px] text-[#c9d7e5]">Masuk sebagai Admin (Laravel)</p></div>
        </div>
        <a href="{{ route('home') }}" class="mt-3 flex items-center justify-center gap-2 rounded-lg border border-white/20 px-3 py-2.5 text-xs font-semibold text-white/90 hover:bg-white/10"><span class="material-symbols-outlined text-[17px]">arrow_back</span> Kembali ke Warga</a>
    </div>
</aside>
