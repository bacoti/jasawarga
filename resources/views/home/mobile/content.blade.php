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
<p class="text-body-sm font-body-sm text-on-surface-variant">Peta interaktif dengan titik mitra contoh wilayah Bandung</p>
</div>
</div>
<!-- Peta mobile interaktif Leaflet + OpenStreetMap. -->
<div class="relative h-[265px] overflow-hidden rounded-2xl border border-[#d9e4ef] bg-[#E8F0F9]" data-jw-osm-map="mobile" role="region" aria-label="Peta OpenStreetMap Mitra Bandung">
  <div data-jw-osm-canvas class="h-full w-full"></div>
  <div data-jw-osm-loading class="pointer-events-none absolute inset-0 flex items-center justify-center bg-slate-100 text-xs font-semibold text-slate-600">Memuat peta OpenStreetMap…</div>
  <div class="absolute bottom-2 left-2 right-2 z-[400] flex items-center justify-between gap-2 rounded-xl bg-white/95 px-3 py-2.5 shadow-md">
    <div class="min-w-0"><p class="truncate text-xs font-extrabold text-[#0C3065]" data-jw-map-selected>Pilih penanda Mitra</p><p class="text-[10px] text-slate-500">Titik perkiraan · Data contoh</p></div>
    <a href="{{ route('providers.show', 1) }}" data-jw-map-profile-link class="shrink-0 rounded-lg bg-[#0C3065] px-3 py-2 text-[11px] font-bold text-white">Lihat Profil</a>
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
<div class="bg-surface-container-lowest p-3.5 rounded-2xl border border-outline-variant/30 shadow-sm space-y-3 jw-provider-card" data-jw-available="true" data-jw-category="ac" data-jw-distance="1.2" data-jw-name="Budi Teknik" data-jw-provider-id="1" data-jw-rating="4.9" data-jw-warranty="true">
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
<span class="">• Coblong (1.2 km)</span>
</div>
</div>
</div>
<div class="text-right">
<span class="text-[10px] text-outline block">Mulai</span>
<span class="text-label-md font-label-md font-bold text-primary">Rp 75.000</span>
</div>
</div>
<div class="flex items-center gap-2 pt-1 border-t border-outline-variant/20">
<button class="flex-1 py-2 px-3 rounded-xl bg-surface-container text-on-surface text-label-sm font-label-sm active:scale-[0.98] transition-all" data-jw-action="profile" data-jw-provider-id="1" data-jw-name="Budi Teknik" type="button">
                Lihat Profil
              </button>
<button class="flex-1 py-2 px-3 rounded-xl bg-primary text-on-primary text-label-sm font-label-sm font-bold shadow-sm active:scale-[0.98] transition-all" data-jw-action="order" data-jw-provider-id="1" data-jw-name="Budi Teknik" type="button">
                Pesan
              </button>
</div>
</div>
<!-- Card 2: Pak Joko Santoso -->
<div class="bg-surface-container-lowest p-3.5 rounded-2xl border border-outline-variant/30 shadow-sm space-y-3 jw-provider-card" data-jw-available="false" data-jw-category="pipa" data-jw-distance="2.1" data-jw-name="Pak Joko Santoso" data-jw-provider-id="2" data-jw-rating="4.8" data-jw-warranty="true">
<div class="flex items-start justify-between">
<div class="flex items-center gap-3">
<div class="relative">
<img alt="Portrait of an experienced Indonesian plumber in Bandung holding a pipe wrench, wearing work clothes with genuine trustworthy smi" class="w-12 h-12 rounded-xl object-cover ring-1 ring-outline-variant/30" data-alt="Portrait of an experienced Indonesian plumber in Bandung holding a pipe wrench, wearing work clothes with genuine trustworthy smile, warm daylight illuminating a residential street, authentic editorial photo." data-fallback-url="{{ asset('assets/images/provider-placeholder.svg') }}" loading="lazy" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAT4_1KjOsQEPwpGetNHj9NfICrm1KiZqnfXE15asbG_rrCW3WgnKreXiURh7suv2pckxB3ZGoN1t4k0z4Q7nY5PnHlxHj7BbuN-pKWnIM-KaQLuPCLY0v47Nu0uknQrK25DqdCwbN36dMIlhtGfn3NmeETFfX1g1DBPZnzenZ-5Ho9wlIrUeMTrtNJMYXs5NLHzca0v23PZujZes4Kk44qhRFut3fjSz-Fij431D0k"/>
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
<span class="">• Lengkong (2.1 km)</span>
</div>
</div>
</div>
<div class="text-right">
<span class="text-[10px] text-outline block">Mulai</span>
<span class="text-label-md font-label-md font-bold text-primary">Rp 60.000</span>
</div>
</div>
<div class="flex items-center gap-2 pt-1 border-t border-outline-variant/20">
<button class="flex-1 py-2 px-3 rounded-xl bg-surface-container text-on-surface text-label-sm font-label-sm active:scale-[0.98] transition-all" data-jw-action="profile" data-jw-provider-id="2" data-jw-name="Pak Joko Santoso" type="button">
                Lihat Profil
              </button>
<button class="flex-1 py-2 px-3 rounded-xl bg-primary text-on-primary text-label-sm font-label-sm font-bold shadow-sm active:scale-[0.98] transition-all" data-jw-action="order" data-jw-provider-id="2" data-jw-name="Pak Joko Santoso" type="button">
                Pesan
              </button>
</div>
</div>
<!-- Card 3: Elektrik Jaya Bandung -->
<div class="bg-surface-container-lowest p-3.5 rounded-2xl border border-outline-variant/30 shadow-sm space-y-3 jw-provider-card" data-jw-available="true" data-jw-category="listrik" data-jw-distance="0.9" data-jw-name="Elektrik Jaya Bandung" data-jw-provider-id="3" data-jw-rating="5.0" data-jw-warranty="true">
<div class="flex items-start justify-between">
<div class="flex items-center gap-3">
<div class="relative">
<img alt="Professional Indonesian residential electrician in Bandung wearing safety helmet and polo shirt, standing confident with electric" class="w-12 h-12 rounded-xl object-cover ring-1 ring-outline-variant/30" data-alt="Professional Indonesian residential electrician in Bandung wearing safety helmet and polo shirt, standing confident with electrical tester tools, sunny morning neighborhood setting, clean modern lighting." data-fallback-url="{{ asset('assets/images/provider-placeholder.svg') }}" loading="lazy" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB_4k48IlpwpdfQPMmICZ_0g48Bfx3TIT4OJ5pWfghGUUMqUxUk_yvMVRr9795ALKpqjRAqQ8zYUuy5YWXW23FKdjq1scZ-TQiiG5uiR8aSqUuUq6AXsslNrjt7kvCm4j45A9gBNqIlwMbJsn4pJ4ODvAaLyQmwo241lKBySoJsRblT9giRZJcpAIebFk4JdaUacKRVuJdS1lDc_HhrvewSbJvlJ0QIfCHuuVGEs7Ro"/>
<span class="absolute -bottom-1 -right-1 bg-primary text-on-primary rounded-full p-0.5">
<span class="material-symbols-outlined text-[12px] block">verified</span>
</span>
</div>
<div>
<div class="flex items-center gap-1.5">
<h3 class="text-label-lg font-label-lg text-on-surface">Elektrik Jaya Bandung</h3>
<span class="bg-secondary-fixed text-on-secondary-fixed text-[9px] font-bold px-1.5 py-0.2 rounded-full">Terverifikasi ✓</span>
</div>
<p class="text-body-sm font-body-sm text-on-surface-variant">Instalasi Listrik Rumah</p>
<div class="flex items-center gap-2 mt-0.5 text-body-sm font-body-sm text-outline">
<span class="flex items-center gap-0.5 text-tertiary font-bold text-label-sm">
<span class="material-symbols-outlined fill text-[14px]">star</span> 5.0
                    </span>
<span class="">• Sukajadi (0.9 km)</span>
</div>
</div>
</div>
<div class="text-right">
<span class="text-[10px] text-outline block">Mulai</span>
<span class="text-label-md font-label-md font-bold text-primary">Rp 85.000</span>
</div>
</div>
<div class="flex items-center gap-2 pt-1 border-t border-outline-variant/20">
<button class="flex-1 py-2 px-3 rounded-xl bg-surface-container text-on-surface text-label-sm font-label-sm active:scale-[0.98] transition-all" data-jw-action="profile" data-jw-provider-id="3" data-jw-name="Elektrik Jaya Bandung" type="button">
                Lihat Profil
              </button>
<button class="flex-1 py-2 px-3 rounded-xl bg-primary text-on-primary text-label-sm font-label-sm font-bold shadow-sm active:scale-[0.98] transition-all" data-jw-action="order" data-jw-provider-id="3" data-jw-name="Elektrik Jaya Bandung" type="button">
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