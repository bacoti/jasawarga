@extends('backend-catalog.layout')
@section('title', 'Kategori Jasa Database | Admin JasaWarga')
@section('backend_catalog_content')
<div class="mb-7 flex flex-wrap items-end justify-between gap-4">
    <div><p class="text-[11px] font-extrabold uppercase tracking-[.14em] text-[#006098]">Admin / Pengelolaan katalog nyata</p><h1 class="mt-2 text-3xl font-extrabold tracking-tight">Kategori layanan</h1><p class="mt-2 text-sm text-[#657b8c]">Kelola kategori PostgreSQL. Tidak mengubah kategori demo yang masih tersimpan di browser.</p></div>
    <a href="{{ route('db-catalog.index') }}" class="rounded-xl border border-[#cbdce9] bg-white px-4 py-3 text-xs font-bold text-[#006098] hover:bg-[#e9f3f9]">Lihat katalog publik ↗</a>
</div>
<section class="mb-6 grid grid-cols-3 gap-3" aria-label="Statistik kategori">
    @foreach([['label'=>'Semua kategori','value'=>$totals['all']],['label'=>'Aktif','value'=>$totals['active']],['label'=>'Nonaktif','value'=>$totals['inactive']]] as $metric)
    <div class="rounded-2xl border border-[#e0e9f1] bg-white p-4 shadow-sm sm:p-5"><p class="text-xs text-[#64798a]">{{ $metric['label'] }}</p><strong class="mt-2 block text-3xl font-extrabold">{{ $metric['value'] }}</strong></div>
    @endforeach
</section>
<div class="grid gap-6 xl:grid-cols-[350px_minmax(0,1fr)]">
    <section class="h-fit rounded-2xl border border-[#dfe8f0] bg-white p-5 shadow-sm" aria-labelledby="add-category-title">
        <h2 id="add-category-title" class="text-lg font-extrabold">Tambah kategori</h2><p class="mt-1 text-xs leading-relaxed text-[#657b8c]">Nama dan slug harus unik. Gunakan slug huruf kecil, angka dan tanda hubung.</p>
        <form method="POST" action="{{ route('admin.db-categories.store') }}" class="mt-5 grid gap-4">@csrf
            <label class="block text-xs font-bold">Nama kategori<input required minlength="3" maxlength="80" name="name" data-slug-source value="{{ old('name') }}" placeholder="Contoh: Servis AC" class="mt-1.5 w-full rounded-xl border-[#ccd9e4] text-sm"></label>
            <label class="block text-xs font-bold">Slug<input required maxlength="90" pattern="[a-z0-9]+(-[a-z0-9]+)*" name="slug" data-slug-target value="{{ old('slug') }}" placeholder="servis-ac" class="mt-1.5 w-full rounded-xl border-[#ccd9e4] text-sm"></label>
            <label class="block text-xs font-bold">Deskripsi<textarea name="description" maxlength="300" rows="2" class="mt-1.5 w-full rounded-xl border-[#ccd9e4] text-sm" placeholder="Layanan yang termasuk di kategori ini">{{ old('description') }}</textarea></label>
            <div class="grid grid-cols-2 gap-3"><label class="block text-xs font-bold">Ikon<select name="icon" class="mt-1.5 w-full rounded-xl border-[#ccd9e4] text-sm">@foreach(['category','home_repair_service','ac_unit','bolt','water_drop','tv','content_cut','cleaning_services','handyman','plumbing','construction','devices','yard','local_shipping'] as $icon)<option value="{{ $icon }}" @selected(old('icon','category') === $icon)>{{ $icon }}</option>@endforeach</select></label>
            <label class="block text-xs font-bold">Urutan<input type="number" name="sort_order" min="0" max="65535" value="{{ old('sort_order',0) }}" required class="mt-1.5 w-full rounded-xl border-[#ccd9e4] text-sm"></label></div>
            <button class="rounded-xl bg-[#006098] px-5 py-3 text-sm font-bold text-white hover:bg-[#004b79]">Tambah kategori</button>
        </form>
    </section>
    <section class="min-w-0 overflow-hidden rounded-2xl border border-[#dfe8f0] bg-white shadow-sm" aria-labelledby="category-list-title">
        <div class="border-b border-[#e6edf3] p-5"><h2 id="category-list-title" class="text-lg font-extrabold">Daftar kategori database</h2>
            <form method="GET" action="{{ route('admin.db-categories.index') }}" class="mt-4 flex flex-wrap gap-2">
                <input name="q" type="search" value="{{ $search }}" aria-label="Cari kategori" placeholder="Cari nama atau slug" class="min-w-[160px] flex-1 rounded-xl border-[#cbd9e5] text-sm">
                <select name="status" aria-label="Filter status" class="rounded-xl border-[#cbd9e5] text-sm"><option value="all">Semua status</option><option value="active" @selected($status==='active')>Aktif</option><option value="inactive" @selected($status==='inactive')>Nonaktif</option></select>
                <button class="rounded-xl border border-[#cbd9e5] bg-[#f2f7fb] px-4 py-2.5 text-sm font-bold">Terapkan</button>
            </form>
        </div>
        <div class="divide-y divide-[#e6edf3]">
            @forelse($categories as $category)
            <article class="p-5">
                <div class="flex flex-wrap items-start justify-between gap-3"><div class="flex min-w-0 gap-3"><span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#e8f2fa] text-[#006098]"><span class="material-symbols-outlined">{{ $category->icon }}</span></span><div class="min-w-0"><h3 class="font-extrabold">{{ $category->name }}</h3><p class="mt-0.5 break-all text-xs text-[#68849b]">{{ $category->slug }} · {{ $category->services_count }} layanan aktif/arsip non-deleted</p><p class="mt-2 text-xs text-[#62798b]">{{ $category->description ?: 'Tanpa deskripsi' }}</p></div></div><span class="rounded-full px-3 py-1 text-[11px] font-extrabold {{ $category->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <form method="POST" action="{{ route('admin.db-categories.status',$category) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $category->is_active ? 0 : 1 }}"><button class="rounded-lg border border-[#ccdce8] px-3 py-2 text-xs font-bold text-[#33566d]">{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                    <details class="group"><summary class="cursor-pointer list-none rounded-lg border border-[#ccdce8] px-3 py-2 text-xs font-bold text-[#006098]">Edit detail</summary>
                        <form method="POST" action="{{ route('admin.db-categories.update',$category) }}" class="mt-4 grid gap-3 rounded-xl bg-[#f5f8fb] p-4 sm:grid-cols-2">@csrf @method('PATCH')
                            <label class="text-xs font-bold">Nama<input name="name" required minlength="3" maxlength="80" value="{{ $category->name }}" class="mt-1 block w-full rounded-lg border-[#ccd9e4] text-sm"></label>
                            <label class="text-xs font-bold">Slug<input name="slug" required maxlength="90" pattern="[a-z0-9]+(-[a-z0-9]+)*" value="{{ $category->slug }}" class="mt-1 block w-full rounded-lg border-[#ccd9e4] text-sm"></label>
                            <label class="text-xs font-bold sm:col-span-2">Deskripsi<textarea name="description" rows="2" maxlength="300" class="mt-1 block w-full rounded-lg border-[#ccd9e4] text-sm">{{ $category->description }}</textarea></label>
                            <label class="text-xs font-bold">Ikon<select name="icon" class="mt-1 block w-full rounded-lg border-[#ccd9e4] text-sm">@foreach(['category','home_repair_service','ac_unit','bolt','water_drop','tv','content_cut','cleaning_services','handyman','plumbing','construction','devices','yard','local_shipping'] as $icon)<option value="{{ $icon }}" @selected($icon===$category->icon)>{{ $icon }}</option>@endforeach</select></label>
                            <label class="text-xs font-bold">Urutan<input type="number" name="sort_order" min="0" max="65535" value="{{ $category->sort_order }}" class="mt-1 block w-full rounded-lg border-[#ccd9e4] text-sm"></label>
                            <button class="rounded-lg bg-[#006098] px-4 py-2.5 text-xs font-bold text-white sm:col-span-2">Simpan perubahan</button>
                        </form>
                    </details>
                    <form method="POST" action="{{ route('admin.db-categories.destroy',$category) }}" data-catalog-confirm="Hapus kategori kosong ini secara permanen?">@csrf @method('DELETE')<button type="submit" @disabled($category->services_count>0) class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700 disabled:cursor-not-allowed disabled:opacity-40">Hapus</button></form>
                </div>
            </article>
            @empty <div class="p-10 text-center text-sm text-[#64798a]">Belum ada kategori database sesuai filter.</div>
            @endforelse
        </div>
        <div class="p-4">{{ $categories->links() }}</div>
    </section>
</div>
<div class="mt-6 rounded-xl border border-[#cde1f0] bg-[#eaf3fa] px-5 py-4 text-xs leading-relaxed text-[#315b78]">Kategori nonaktif menyembunyikan layanan terkait dari katalog publik, tanpa menghapus record layanan. Kategori yang pernah dipakai tidak dapat dihapus. Katalog dan booking demo lama tidak berubah.</div>
@endsection
