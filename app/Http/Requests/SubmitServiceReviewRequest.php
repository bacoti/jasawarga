<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitServiceReviewRequest extends FormRequest
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
            'partner_service_id' => ['required','integer','exists:partner_services,id'],
            'booking_reference' => ['required','string','min:5','max:80','regex:/^[A-Z0-9][A-Z0-9\-\/]+$/'],
            'rating' => ['required','integer','between:1,5'],
            'quality_rating' => ['required','integer','between:1,5'],
            'timeliness_rating' => ['required','integer','between:1,5'],
            'friendliness_rating' => ['required','integer','between:1,5'],
            'comment' => ['required','string','min:20','max:2000'],
            'photo' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ];
    }
}
