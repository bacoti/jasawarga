<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class SubmitBookingOfferRequest extends FormRequest
{
    public function authorize(): bool { return (string) $this->user()?->role === 'partner'; }
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999'],
            'note' => ['nullable', 'string', 'max:1500'],
        ];
    }
}
