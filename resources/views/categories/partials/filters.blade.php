<div class="space-y-5" data-catalog-filter-panel>
    <div>
        <h3 class="mb-3 text-sm font-bold text-on-surface">Jenis layanan</h3>
        <div class="space-y-1.5">
            <button type="button" data-catalog-category="all" aria-pressed="true" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm font-medium text-on-surface hover:bg-surface-container-low">
                <span>Semua kategori</span><span class="text-xs text-outline">{{ count($providers) }}</span>
            </button>
            @foreach($categories as $category)
                <button type="button" data-catalog-category="{{ $category['slug'] }}" aria-pressed="false" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm text-on-surface-variant hover:bg-surface-container-low">
                    <span>{{ $category['name'] }}</span>
                    <span class="text-xs text-outline">{{ $counts[$category['slug']] ?? 0 }}</span>
                </button>
            @endforeach
        </div>
    </div>
    <div class="border-t border-outline-variant/40 pt-4">
        <label for="{{ $prefix }}-price" class="mb-2 block text-sm font-bold text-on-surface">Batas harga awal</label>
        <select id="{{ $prefix }}-price" data-catalog-price class="w-full rounded-xl border-outline-variant/60 bg-surface-container-lowest text-sm focus:border-primary focus:ring-primary">
            <option value="0">Semua harga</option>
            <option value="75000">Maks. Rp 75.000</option>
            <option value="100000">Maks. Rp 100.000</option>
            <option value="150000">Maks. Rp 150.000</option>
        </select>
    </div>
    <div class="space-y-3 border-t border-outline-variant/40 pt-4">
        <label class="flex cursor-pointer items-center gap-3 text-sm text-on-surface-variant">
            <input type="checkbox" data-catalog-available class="rounded border-outline-variant text-primary focus:ring-primary">
            Tersedia hari ini
        </label>
        <label class="flex cursor-pointer items-center gap-3 text-sm text-on-surface-variant">
            <input type="checkbox" data-catalog-warranty class="rounded border-outline-variant text-primary focus:ring-primary">
            Memiliki garansi
        </label>
    </div>
    <button type="button" data-catalog-reset class="w-full rounded-xl border border-outline-variant/70 px-4 py-2.5 text-sm font-semibold text-primary hover:bg-surface-container-low">Atur ulang filter</button>
</div>
