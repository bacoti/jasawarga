<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;

class StorePortfolioRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'partner'; }
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'completed_at' => ['nullable', 'date', 'before_or_equal:today'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('jasawarga-profile.limits.image_kilobytes', 3072)],
        ];
    }
}
