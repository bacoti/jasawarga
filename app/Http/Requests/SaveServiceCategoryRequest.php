<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin' && $this->user()?->account_status === 'active';
    }

    public function rules(): array
    {
        $existing = $this->route('serviceCategory');

        return [
            'name' => ['required', 'string', 'min:3', 'max:80'],
            'slug' => [
                'required', 'string', 'max:90', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('service_categories', 'slug')->ignore($existing?->id),
            ],
            'description' => ['nullable', 'string', 'max:300'],
            'icon' => ['required', Rule::in([
                'category', 'home_repair_service', 'ac_unit', 'bolt', 'water_drop',
                'tv', 'content_cut', 'cleaning_services', 'handyman', 'plumbing',
                'construction', 'devices', 'yard', 'local_shipping',
            ])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'slug' => strtolower(trim((string) $this->input('slug', ''))),
        ]);
    }
}
