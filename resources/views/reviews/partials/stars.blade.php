<fieldset data-review-rating="{{ $key }}" class="mt-4 min-w-0">
    <legend class="mb-2 text-sm font-semibold text-on-surface">{{ $label }} <span class="text-error" aria-label="wajib">*</span></legend>
    <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="{{ $label }}">
        @for($score = 1; $score <= 5; $score++)
            <label class="cursor-pointer rounded-lg p-1 transition hover:bg-tertiary-fixed/50 focus-within:outline focus-within:outline-2 focus-within:outline-primary" title="{{ $score }} dari 5 bintang">
                <input class="peer sr-only" type="radio" name="{{ $key }}" value="{{ $score }}" required aria-label="{{ $score }} dari 5 bintang">
                <span class="material-symbols-outlined select-none text-[29px] text-outline-variant sm:text-[32px]" data-review-star="{{ $score }}" aria-hidden="true">star</span>
            </label>
        @endfor
        <span class="ml-2 text-xs font-bold text-outline" data-review-rating-label>Pilih nilai</span>
    </div>
</fieldset>
