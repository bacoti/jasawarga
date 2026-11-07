<nav class="fixed bottom-0 w-full z-50 flex justify-around items-center px-4 py-2 pb-5 max-w-[390px] mx-auto left-0 right-0 bg-surface-container-lowest/95 backdrop-blur-lg border-t border-outline-variant/30 shadow-[0_-4px_16px_rgba(0,32,80,0.06)]"><!-- Tab 1: Beranda (Active) -->
<button class="flex-1 flex flex-col items-center justify-center py-1 text-primary gap-1 active:scale-95 transition-transform" data-jw-action="home" type="button">
<span class="material-symbols-outlined fill text-[26px]">home</span>
<span class="text-[12px] font-bold leading-none tracking-tight">Beranda</span>
</button>
<!-- Tab 2: Pesanan (with badge '1') -->
<a href="{{ route('orders.index') }}" class="flex-1 relative flex flex-col items-center justify-center py-1 text-on-surface-variant gap-1 hover:text-primary active:scale-95 transition-transform">
<span class="material-symbols-outlined text-[26px]">receipt_long</span>
<span class="text-[12px] font-medium leading-none tracking-tight">Pesanan</span>

</a>
<!-- Tab 3: Bantuan -->
<button class="flex-1 flex flex-col items-center justify-center py-1 text-on-surface-variant gap-1 hover:text-primary active:scale-95 transition-transform" data-jw-action="help" type="button">
<span class="material-symbols-outlined text-[26px]">help_outline</span>
<span class="text-[12px] font-medium leading-none tracking-tight">Bantuan</span>
</button>
<!-- Tab 4: Akun -->
<a href="{{ route('account.profile') }}" data-jw-account-link class="flex-1 flex flex-col items-center justify-center py-1 text-on-surface-variant gap-1 hover:text-primary active:scale-95 transition-transform">
<span class="material-symbols-outlined text-[26px]">person</span>
<span class="text-[12px] font-medium leading-none tracking-tight">Akun</span>
</a></nav>