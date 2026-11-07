<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleBlockRequest extends FormRequest
{
    public function authorize(): bool { return (string) $this->user()?->role === 'partner'; }
    public function rules(): array
    {
        return [
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'reason' => ['nullable', 'string', 'max:180'],
        ];
    }
}
