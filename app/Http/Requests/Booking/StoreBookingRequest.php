<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool { return (string) $this->user()?->role === 'citizen'; }
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'min:1'],
            'scheduled_at' => ['required', 'date'],
            'address_id' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1500'],
        ];
    }
}
