<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterCitizenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'regex:/^08[0-9]{8,11}$/'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()],
            'terms' => ['accepted'],
        ];
    }
}
