<main class="px-4 pt-4 space-y-6" id="beranda-mobile">
<!-- 2. Quick Category Grid (4 cols x 2 rows) -->
<section id="kategori-layanan">
<div class="flex items-center justify-between mb-3">
<h2 class="text-label-lg font-label-lg text-on-surface">Layanan Gotong Royong</h2>
<button aria-label="Lihat semua kategori layanan" class="text-label-sm font-label-sm text-primary font-medium" data-jw-action="category" data-jw-category="all" type="button">Lihat Semua</button>
</div>
<div class="grid grid-cols-4 gap-3">
<!-- 1. Bangunan -->
<button aria-label="Filter kategori bangunan" aria-pressed="false" class="flex flex-col items-center gap-1.5 group active:scale-95 transition-transform text-center" data-jw-action="category" data-jw-category="bangunan" type="button">
<div class="w-16 h-16 rounded-2xl bg-inverse-surface text-surface-container-lowest flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
<span class="material-symbols-outlined text-[28px]">home_repair_service</span>
</div>
<span class="text-label-sm font-label-sm text-on-surface">Bangunan</span>
</button>
<!-- 2. Servis AC -->
<button aria-label="Filter kategori ac" aria-pressed="false" class="flex flex-col items-center gap-1.5 group active:scale-95 transition-transform text-center" data-jw-action="category" data-jw-category="ac" type="button">
<div class="w-16 h-16 rounded-2xl bg-tertiary-fixed text-tertiary flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
<span class="material-symbols-outlined text-[28px]">ac_unit</span>
</div>
<span class="text-label-sm font-label-sm text-on-surface">Servis AC</span>
</button>
<!-- 3. Listrik -->
<button aria-label="Filter kategori listrik" aria-pressed="false" class="flex flex-col items-center gap-1.5 group active:scale-95 transition-transform text-center" data-jw-action="category" data-jw-category="listrik" type="button">
<div class="w-16 h-16 rounded-2xl bg-primary-fixed text-primary flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
<span class="material-symbols-outlined text-[28px]">bolt</span>
</div>
<span class="text-label-sm font-label-sm text-on-surface">Listrik</span>
</button>
<!-- 4. Air & Pipa -->
<button aria-label="Filter kategori pipa" aria-pressed="false" class="flex flex-col items-center gap-1.5 group active:scale-95 transition-transform text-center" data-jw-action="category" data-jw-category="pipa" type="button">
<div class="w-16 h-16 rounded-2xl bg-secondary-fixed text-secondary flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
<span class="material-symbols-outlined text-[28px]">water_drop</span>
</div>
<span class="text-label-sm font-label-sm text-on-surface">Air &amp; Pipa</span>
</button>
<!-- 5. Elektronik -->
<button aria-label="Filter kategori elektronik" aria-pressed="false" class="flex flex-col items-center gap-1.5 group active:scale-95 transition-transform text-center" data-jw-action="category" data-jw-category="elektronik" type="button">
<div class="w-16 h-16 rounded-2xl bg-surface-container-highest text-primary flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
<span class="material-symbols-outlined text-[28px]">tv</span>
</div>
<span class="text-label-sm font-label-sm text-on-surface">Elektronik</span>
</button>
<!-- 6. Jahit -->
<button aria-label="Filter kategori jahit" aria-pressed="false" class="flex flex-col items-center gap-1.5 group active:scale-95 transition-transform text-center" data-jw-action="category" data-jw-category="jahit" type="button">
<div class="w-16 h-16 rounded-2xl bg-surface-container text-on-secondary-container flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
<span class="material-symbols-outlined text-[28px]">content_cut</span>
</div>
<span class="text-label-sm font-label-sm text-on-surface">Jahit</span>
</button>
<!-- 7. Kebersihan -->
<button aria-label="Filter kategori kebersihan" aria-pressed="false" class="flex flex-col items-center gap-1.5 group active:scale-95 transition-transform text-center" data-jw-action="category" data-jw-category="kebersihan" type="button">
<div class="w-16 h-16 rounded-2xl bg-surface-container-high text-tertiary flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
<span class="material-symbols-outlined text-[28px]">cleaning_services</span>
</div>
<span class="text-label-sm font-label-sm text-on-surface">Kebersihan</span>
</button>
<!-- 8. Lainnya -->
<button aria-label="Filter kategori all" aria-pressed="false" class="flex flex-col items-center gap-1.5 group active:scale-95 transition-transform text-center" data-jw-action="category" data-jw-category="all" type="button">
<div class="w-16 h-16 rounded-2xl bg-surface-container-low text-outline flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
<span class="material-symbols-outlined text-[28px]">grid_view</span>
</div>
<span class="text-label-sm font-label-sm text-on-surface">Lainnya</span>
</button>
</div>
</section>
<!-- 3. Interactive Mobile Map Card -->
<section class="space-y-2" data-jw-map-target="true">
<div class="flex items-center justify-between">
<div>
<h2 class="text-headline-sm font-headline-sm text-on-surface">Jasa di Sekitar Anda</h2>
<p class="text-body-sm font-body-sm text-on-surface-variant">14 Mitra aktif siap dipanggil dalam radius 5 km</p>
</div>
</div>
<!-- Map Container Graphic with Detailed Vector Road Layout -->
<div aria-label="Ilustrasi peta mitra Makassar (data contoh)" class="relative w-full h-[250px] rounded-2xl bg-[#E8EEF5] border border-outline-variant/30 overflow-hidden shadow-sm" data-location="Makassar">
<!-- Vector Map Grid/Road Simulation -->
<svg class="absolute inset-0 w-full h-full text-slate-300" xmlns="http://www.w3.org/2000/svg">
<defs>
<pattern height="40" id="grid-pattern" patternunits="userSpaceOnUse" width="40">
<path d="M 40 0 L 0 0 0 40" fill="none" stroke="#DCE4EE" stroke-width="0.7"></path>
</pattern>
</defs>
<rect fill="#EBF2FA" height="100%" width="100%"></rect>
<rect fill="url(#grid-pattern)" height="100%" width="100%"></rect>
<!-- Major Arterials (Jl. Pettarani & Jl. Boulevard) -->
<path d="M -20 180 Q 120 140 390 160" fill="none" stroke="#FFFFFF" stroke-linecap="round" stroke-width="14"></path>
<path d="M -20 180 Q 120 140 390 160" fill="none" stroke="#CAD6E2" stroke-linecap="round" stroke-width="2"></path>
<path d="M 180 -20 L 210 280" fill="none" stroke="#FFFFFF" stroke-width="18"></path>
<path d="M 180 -20 L 210 280" fill="none" stroke="#CAD6E2" stroke-width="2"></path>
<!-- Secondary Arterials -->
<path d="M 60 0 L 110 280" fill="none" stroke="#FFFFFF" stroke-width="8"></path>
<path d="M 0 70 L 390 90" fill="none" stroke="#FFFFFF" stroke-width="7"></path>
</svg>
<!-- Street Names & Landmarks -->
<div class="absolute top-12 left-3 bg-surface-container-lowest/80 backdrop-blur-xs px-1.5 py-0.5 rounded text-[9px] font-semibold text-outline tracking-wider uppercase">
            Jl. A. P. Pettarani
          </div>
<div class="absolute bottom-24 right-4 bg-surface-container-lowest/80 backdrop-blur-xs px-1.5 py-0.5 rounded text-[9px] font-semibold text-outline tracking-wider uppercase">
            Jl. Boulevard
          </div>
<div class="absolute top-4 right-4 bg-surface-container-high/90 px-2 py-0.5 rounded-full text-[10px] font-medium text-primary">
            Komp. Panakkukang Mas
          </div>
<!-- Interactive Service Pins -->
<!-- 1. AC Pin (Budi Teknik) -->
<div class="absolute top-14 left-24 -translate-x-1/2 -translate-y-1/2 flex flex-col items-center cursor-pointer group">
<div class="w-8 h-8 rounded-full bg-tertiary-fixed text-tertiary shadow-md flex items-center justify-center ring-2 ring-surface-container-lowest animate-bounce duration-1000">
<span class="material-symbols-outlined text-[18px]">ac_unit</span>
</div>
<div class="mt-0.5 px-1.5 py-0.5 bg-inverse-surface text-surface-container-lowest rounded text-[9px] font-bold shadow">
              Budi AC
            </div>
</div>
<!-- 2. Tukang Bangunan Pin -->
<div class="absolute top-20 right-20 -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
<div class="w-7 h-7 rounded-full bg-inverse-surface text-surface-container-lowest shadow-md flex items-center justify-center ring-2 ring-surface-container-lowest">
<span class="material-symbols-outlined text-[16px]">home_repair_service</span>
</div>
</div>
<!-- 3. Listrik Pin -->
<div class="absolute bottom-28 left-16 -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
<div class="w-7 h-7 rounded-full bg-primary text-on-primary shadow-md flex items-center justify-center ring-2 ring-surface-container-lowest">
<span class="material-symbols-outlined text-[16px]">bolt</span>
</div>
</div>
<!-- 4. Pipa/Air Pin -->
<div class="absolute top-8 left-1/2 -translate-x-1/2 -translate-y-1/2 flex flex-col items-center">
<div class="w-7 h-7 rounded-full bg-secondary text-on-secondary shadow-md flex items-center justify-center ring-2 ring-surface-container-lowest">
<span class="material-symbols-outlined text-[16px]">water_drop</span>
</div>
</div>
<!-- User Pulsing Location Dot ("Lokasi Anda") -->
<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 flex items-center justify-center">
<div class="absolute w-8 h-8 bg-primary/20 rounded-full animate-ping-subtle"></div>
<div class="relative w-4 h-4 rounded-full bg-primary ring-4 ring-surface-container-lowest shadow-lg"></div>
<div class="absolute -bottom-5 bg-primary text-on-primary px-1.5 py-0.2 text-[8px] font-bold rounded-full shadow">
              Lokasi Anda
            </div>
</div>
<!-- Floating Map Controls -->
<div class="absolute top-2 left-2 flex items-center gap-1.5 z-10">
<button class="bg-surface-container-lowest/95 backdrop-blur-sm px-2.5 py-1.5 rounded-lg shadow-sm text-label-sm font-label-sm text-on-surface flex items-center gap-1 active:scale-95 border border-outline-variant/30" data-jw-action="my-location" type="button">
<span class="material-symbols-outlined text-primary text-[14px]">my_location</span>
<span class="">Lokasi Saya</span>
</button>
<button class="bg-surface-container-lowest/95 backdrop-blur-sm px-2.5 py-1.5 rounded-lg shadow-sm text-label-sm font-label-sm text-on-surface flex items-center gap-1 active:scale-95 border border-outline-variant/30" data-jw-action="map" type="button">
<span class="material-symbols-outlined text-secondary text-[14px]">map</span>
<span class="">Buka Peta Penuh</span>
</button>
</div>
<!-- Tap-preview Card Docked on Bottom of Map -->
<div class="absolute bottom-2 left-2 right-2 z-10 bg-surface-container-lowest/95 backdrop-blur-md p-2.5 rounded-xl border border-outline-variant/30 shadow-md flex items-center justify-between">
<div class="flex items-center gap-2 overflow-hidden">
<div class="w-10 h-10 rounded-lg bg-tertiary-fixed text-tertiary flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-[20px]">ac_unit</span>
</div>
<div class="truncate">
<div class="flex items-center gap-1">
<h4 class="text-label-md font-label-md text-on-surface truncate">Budi Teknik</h4>
<span class="text-[10px] text-on-surface-variant font-normal">• 1.2 km</span>
</div>
<div class="flex items-center gap-1.5 text-body-sm font-body-sm text-on-surface-variant">
<span class="text-tertiary font-bold text-label-sm flex items-center">
                    ★ 4.9 <span class="text-outline font-normal ml-0.5">(128)</span>
</span>
<span class="">• Mulai Rp 75rb</span>
</div>
</div>
</div>
<button class="bg-primary text-on-primary font-label-sm text-label-sm px-3 py-2 rounded-lg shrink-0 shadow-sm active:scale-95 transition-transform" data-jw-action="order" type="button">
              Pesan Cepat
            </button>
</div>
</div>
</section>
<!-- 4. Recommended Nearby Providers ("Rekomendasi untuk Anda") -->
<section class="space-y-3" data-jw-list-target="true">
<div class="flex items-center justify-between">
<h2 class="text-headline-sm font-headline-sm text-on-surface">Rekomendasi untuk Anda</h2>
<button class="text-label-sm font-label-sm text-primary font-semibold hover:underline" data-jw-action="list" type="button">Semua Mitra</button>
</div>
<div class="space-y-3">
<!-- Card 1: Budi Teknik -->
<div class="bg-surface-container-lowest p-3.5 rounded-2xl border border-outline-variant/30 shadow-sm space-y-3 jw-provider-card" data-jw-available="true" data-jw-category="ac" data-jw-distance="1.2" data-jw-name="Budi Teknik" data-jw-rating="4.9" data-jw-warranty="true">
<div class="flex items-start justify-between">
<div class="flex items-center gap-3">
<div class="relative">
<img alt="Close up portrait of a friendly Indonesian HVAC technician wearing a neat blue uniform, smiling warmly against a bright urban neig" class="w-12 h-12 rounded-xl object-cover ring-1 ring-outline-variant/30" data-alt="Close up portrait of a friendly Indonesian HVAC technician wearing a neat blue uniform, smiling warmly against a bright urban neighborhood background, professional light photography with clean soft contrast." data-fallback-url="{{ asset('assets/images/provider-placeholder.svg') }}" loading="lazy" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSSIrc0RKiy3KGDjk57N3KQsDnodDcJVjxwnPH0uX84ATena7tz_KMvgYWNyBEjbUpNetgXWV0RTFNKvO7c81VrTxa8WMqQYGoeZyO6QlFRQBJHpIpY2FMqFSERfa-ZUU4xLI0gB820zC_s6Ul8y9Y-AEYZ0nvGhhj6_AIm5joXn10Ni_g23AeF5E6Jh1HGnlzDihDYm9qUmML0RyeP4ZimJhyZHcFkATUf1M0SXs-"/>
<span class="absolute -bottom-1 -right-1 bg-primary text-on-primary rounded-full p-0.5">
<span class="material-symbols-outlined text-[12px] block">verified</span>
</span>
</div>
<div>
<div class="flex items-center gap-1.5">
<h3 class="text-label-lg font-label-lg text-on-surface">Budi Teknik</h3>
<span class="bg-secondary-fixed text-on-secondary-fixed text-[9px] font-bold px-1.5 py-0.2 rounded-full">Terverifikasi ✓</span>
</div>
<p class="text-body-sm font-body-sm text-on-surface-variant">Servis AC &amp; Pengisian Freon</p>
<div class="flex items-center gap-2 mt-0.5 text-body-sm font-body-sm text-outline">
<span class="flex items-center gap-0.5 text-tertiary font-bold text-label-sm">
<span class="material-symbols-outlined fill text-[14px]">star</span> 4.9
                    </span>
<span class="">• Panakkukang (1.2 km)</span>
</div>
</div>
</div>
<div class="text-right">
<span class="text-[10px] text-outline block">Mulai</span>
<span class="text-label-md font-label-md font-bold text-primary">Rp 75.000</span>
</div>
</div>
<div class="flex items-center gap-2 pt-1 border-t border-outline-variant/20">
<button class="flex-1 py-2 px-3 rounded-xl bg-surface-container text-on-surface text-label-sm font-label-sm active:scale-[0.98] transition-all" data-jw-action="profile" data-jw-name="Budi Teknik" type="button">
                Lihat Profil
              </button>
<button class="flex-1 py-2 px-3 rounded-xl bg-primary text-on-primary text-label-sm font-label-sm font-bold shadow-sm active:scale-[0.98] transition-all" data-jw-action="order" data-jw-name="Budi Teknik" type="button">
                Pesan
              </button>
</div>
</div>
<!-- Card 2: Pak Joko Santoso -->
<div class="bg-surface-container-lowest p-3.5 rounded-2xl border border-outline-variant/30 shadow-sm space-y-3 jw-provider-card" data-jw-available="false" data-jw-category="pipa" data-jw-distance="2.1" data-jw-name="Pak Joko Santoso" data-jw-rating="4.8" data-jw-warranty="true">
<div class="flex items-start justify-between">
<div class="flex items-center gap-3">
<div class="relative">
<img alt="Portrait of an experienced Indonesian plumber in Makassar holding a pipe wrench, wearing work clothes with genuine trustworthy smi" class="w-12 h-12 rounded-xl object-cover ring-1 ring-outline-variant/30" data-alt="Portrait of an experienced Indonesian plumber in Makassar holding a pipe wrench, wearing work clothes with genuine trustworthy smile, warm daylight illuminating a residential street, authentic editorial photo." data-fallback-url="{{ asset('assets/images/provider-placeholder.svg') }}" loading="lazy" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAT4_1KjOsQEPwpGetNHj9NfICrm1KiZqnfXE15asbG_rrCW3WgnKreXiURh7suv2pckxB3ZGoN1t4k0z4Q7nY5PnHlxHj7BbuN-pKWnIM-KaQLuPCLY0v47Nu0uknQrK25DqdCwbN36dMIlhtGfn3NmeETFfX1g1DBPZnzenZ-5Ho9wlIrUeMTrtNJMYXs5NLHzca0v23PZujZes4Kk44qhRFut3fjSz-Fij431D0k"/>
<span class="absolute -bottom-1 -right-1 bg-primary text-on-primary rounded-full p-0.5">
<span class="material-symbols-outlined text-[12px] block">verified</span>
</span>
</div>
<div>
<div class="flex items-center gap-1.5">
<h3 class="text-label-lg font-label-lg text-on-surface">Pak Joko Santoso</h3>
<span class="bg-secondary-fixed text-on-secondary-fixed text-[9px] font-bold px-1.5 py-0.2 rounded-full">Terverifikasi ✓</span>
</div>
<p class="text-body-sm font-body-sm text-on-surface-variant">Tukang Ledeng &amp; Saluran Mampet</p>
<div class="flex items-center gap-2 mt-0.5 text-body-sm font-body-sm text-outline">
<span class="flex items-center gap-0.5 text-tertiary font-bold text-label-sm">
<span class="material-symbols-outlined fill text-[14px]">star</span> 4.8
                    </span>
<span class="">• Tamalate (2.1 km)</span>
</div>
</div>
</div>
<div class="text-right">
<span class="text-[10px] text-outline block">Mulai</span>
<span class="text-label-md font-label-md font-bold text-primary">Rp 60.000</span>
</div>
</div>
<div class="flex items-center gap-2 pt-1 border-t border-outline-variant/20">
<button class="flex-1 py-2 px-3 rounded-xl bg-surface-container text-on-surface text-label-sm font-label-sm active:scale-[0.98] transition-all" data-jw-action="profile" data-jw-name="Pak Joko Santoso" type="button">
                Lihat Profil
              </button>
<button class="flex-1 py-2 px-3 rounded-xl bg-primary text-on-primary text-label-sm font-label-sm font-bold shadow-sm active:scale-[0.98] transition-all" data-jw-action="order" data-jw-name="Pak Joko Santoso" type="button">
                Pesan
              </button>
</div>
</div>
<!-- Card 3: Elektrik Daeng Rani -->
<div class="bg-surface-container-lowest p-3.5 rounded-2xl border border-outline-variant/30 shadow-sm space-y-3 jw-provider-card" data-jw-available="true" data-jw-category="listrik" data-jw-distance="0.9" data-jw-name="Elektrik Daeng Rani" data-jw-rating="5.0" data-jw-warranty="true">
<div class="flex items-start justify-between">
<div class="flex items-center gap-3">
<div class="relative">
<img alt="Professional Indonesian residential electrician in Makassar wearing safety helmet and polo shirt, standing confident with electric" class="w-12 h-12 rounded-xl object-cover ring-1 ring-outline-variant/30" data-alt="Professional Indonesian residential electrician in Makassar wearing safety helmet and polo shirt, standing confident with electrical tester tools, sunny morning neighborhood setting, clean modern lighting." data-fallback-url="{{ asset('assets/images/provider-placeholder.svg') }}" loading="lazy" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB_4k48IlpwpdfQPMmICZ_0g48Bfx3TIT4OJ5pWfghGUUMqUxUk_yvMVRr9795ALKpqjRAqQ8zYUuy5YWXW23FKdjq1scZ-TQiiG5uiR8aSqUuUq6AXsslNrjt7kvCm4j45A9gBNqIlwMbJsn4pJ4ODvAaLyQmwo241lKBySoJsRblT9giRZJcpAIebFk4JdaUacKRVuJdS1lDc_HhrvewSbJvlJ0QIfCHuuVGEs7Ro"/>
<span class="absolute -bottom-1 -right-1 bg-primary text-on-primary rounded-full p-0.5">
<span class="material-symbols-outlined text-[12px] block">verified</span>
</span>
</div>
<div>
<div class="flex items-center gap-1.5">
<h3 class="text-label-lg font-label-lg text-on-surface">Elektrik Daeng Rani</h3>
<span class="bg-secondary-fixed text-on-secondary-fixed text-[9px] font-bold px-1.5 py-0.2 rounded-full">Terverifikasi ✓</span>
</div>
<p class="text-body-sm font-body-sm text-on-surface-variant">Instalasi Listrik Rumah</p>
<div class="flex items-center gap-2 mt-0.5 text-body-sm font-body-sm text-outline">
<span class="flex items-center gap-0.5 text-tertiary font-bold text-label-sm">
<span class="material-symbols-outlined fill text-[14px]">star</span> 5.0
                    </span>
<span class="">• Rappocini (0.9 km)</span>
</div>
</div>
</div>
<div class="text-right">
<span class="text-[10px] text-outline block">Mulai</span>
<span class="text-label-md font-label-md font-bold text-primary">Rp 85.000</span>
</div>
</div>
<div class="flex items-center gap-2 pt-1 border-t border-outline-variant/20">
<button class="flex-1 py-2 px-3 rounded-xl bg-surface-container text-on-surface text-label-sm font-label-sm active:scale-[0.98] transition-all" data-jw-action="profile" data-jw-name="Elektrik Daeng Rani" type="button">
                Lihat Profil
              </button>
<button class="flex-1 py-2 px-3 rounded-xl bg-primary text-on-primary text-label-sm font-label-sm font-bold shadow-sm active:scale-[0.98] transition-all" data-jw-action="order" data-jw-name="Elektrik Daeng Rani" type="button">
                Pesan
              </button>
</div>
</div>
</div>
<p class="jw-empty-state hidden rounded-xl border border-outline-variant/40 bg-surface-container-lowest p-5 text-sm text-on-surface-variant" role="status">Belum ada mitra contoh yang cocok dengan pencarian atau filter ini. Coba kategori lain.</p></section>
<!-- 5. Trust Banner (Institutional Security & Gotong Royong) -->
<section class="bg-surface-container p-3.5 rounded-2xl border border-secondary-container/50 flex items-center gap-3">
<div class="w-10 h-10 rounded-xl bg-primary-container text-on-primary flex items-center justify-center shrink-0 shadow-sm">
<span class="material-symbols-outlined text-[22px]">policy</span>
</div>
<div class="space-y-0.5">
<h4 class="text-label-md font-label-md text-on-surface font-bold">Gotong Royong &amp; Aman</h4>
<p class="text-body-sm font-body-sm text-on-surface-variant leading-tight">
            100% Mitra dicek KTP &amp; terdaftar di Kelurahan setempat.
          </p>
</div>
</section>
</main>