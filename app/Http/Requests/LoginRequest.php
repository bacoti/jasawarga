<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:180'],
            'password' => ['required', 'string'],
            'role' => ['nullable', Rule::in(['citizen', 'partner', 'admin'])],
            'remember' => ['sometimes', 'boolean'],
        ];
    }
}
