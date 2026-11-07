<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'citizen'; }
    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+()\s.-]{8,25}$/'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:perempuan,laki-laki,tidak_disebutkan'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('jasawarga-profile.limits.image_kilobytes', 3072)],
        ];
    }
}
