<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitServiceComplaintRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'citizen'; }
    protected function prepareForValidation(): void
    {
        if ($this->filled('booking_reference')) {
            $this->merge(['booking_reference' => strtoupper(trim((string) $this->input('booking_reference')))]);
        }
    }
    public function rules(): array
    {
        return [
            'partner_service_id' => ['nullable','integer','exists:partner_services,id'],
            'service_review_id' => ['nullable','integer','exists:service_reviews,id'],
            'booking_reference' => ['nullable','string','min:5','max:80','regex:/^[A-Z0-9][A-Z0-9\-\/]+$/'],
            'subject' => ['required','string','min:8','max:140'],
            'description' => ['required','string','min:30','max:4000'],
        ];
    }
}
