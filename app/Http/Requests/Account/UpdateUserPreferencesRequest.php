<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->user()) return false;
        return ! $this->has('operational') || $this->user()->role === 'partner';
    }
    public function rules(): array
    {
        return [
            'notifications' => ['nullable', 'array:order_updates,review_updates,platform_news'],
            'notifications.order_updates' => ['sometimes', 'boolean'],
            'notifications.review_updates' => ['sometimes', 'boolean'],
            'notifications.platform_news' => ['sometimes', 'boolean'],
            'privacy' => ['nullable', 'array:profile_visible,show_phone'],
            'privacy.profile_visible' => ['sometimes', 'boolean'],
            'privacy.show_phone' => ['sometimes', 'boolean'],
            'operational' => ['nullable', 'array:accepting_orders'],
            'operational.accepting_orders' => ['sometimes', 'boolean'],
            'timezone' => ['nullable', 'in:Asia/Makassar,Asia/Jakarta,Asia/Jayapura'],
        ];
    }
}
