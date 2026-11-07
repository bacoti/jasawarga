<?php

namespace App\Http\Requests\Partner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartnerProfileRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'partner'; }
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'min:3', 'max:120'],
            'category_name' => ['required', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:180'],
            'description' => ['required', 'string', 'min:30', 'max:3000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'phone' => ['required', 'string', 'max:22', 'regex:/^[0-9+()\s.-]{8,22}$/'],
            'whatsapp' => ['required', 'string', 'max:25', 'regex:/^[0-9+()\s.-]{8,25}$/'],
            'city' => ['required', Rule::in(['Bandung'])],
            'address_summary' => ['nullable', 'string', 'max:180'],
            'service_areas' => ['required', 'array', 'min:1', 'max:15'],
            'service_areas.*' => ['required', 'string', Rule::in(config('jasawarga-profile.bandung_districts', []))],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('jasawarga-profile.limits.image_kilobytes', 3072)],
            'cover_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('jasawarga-profile.limits.image_kilobytes', 3072)],
            // verification_status, verification_note, verified_at dan user_id sengaja tidak diterima.
        ];
    }
}
