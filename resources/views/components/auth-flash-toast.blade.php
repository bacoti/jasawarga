{{-- Pesan flash Laravel; otomatis tertutup setelah 3.000 ms, tidak menggunakan localStorage. --}}
@if(session()->has('success'))
    <div
        id="jw-auth-flash"
        data-jw-auth-flash
        data-jw-duration="3000"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        class="jw-auth-flash fixed right-4 top-4 z-[200] flex w-[min(calc(100vw-2rem),420px)] items-start gap-3 rounded-2xl border border-emerald-200 bg-white px-4 py-4 text-sm text-slate-800 shadow-[0_18px_48px_rgba(11,38,76,.18)] sm:right-6 sm:top-6"
    >
        <span aria-hidden="true" class="material-symbols-outlined mt-0.5 shrink-0 text-[23px] text-emerald-600">check_circle</span>
        <div class="min-w-0 flex-1">
            <strong class="block font-bold text-[#0C3065]">Berhasil</strong>
            <p class="mt-1 leading-5">{{ session('success') }}</p>
        </div>
        <button type="button" data-jw-auth-flash-close class="-mr-1 -mt-1 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Tutup notifikasi">
            <span aria-hidden="true" class="material-symbols-outlined text-[19px]">close</span>
        </button>
    </div>
@endif
