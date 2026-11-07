<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePartnerServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'partner'
            && $this->user()?->account_status === 'active'
            && $this->user()?->partnerProfile?->verification_status === 'approved';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:120'],
            'service_category_id' => [
                'required', 'integer',
                Rule::exists('service_categories', 'id')->where('is_active', true),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'base_price' => ['required', 'integer', 'min:5000', 'max:100000000'],
            'duration_minutes' => ['required', 'integer', 'min:30', 'max:1440'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name', ''))]);
    }
}
