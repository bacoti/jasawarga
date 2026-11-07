<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class PartnerWorkingHoursRequest extends FormRequest
{
    public function authorize(): bool { return (string) $this->user()?->role === 'partner'; }
    public function rules(): array
    {
        return [
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6', 'distinct'],
            'hours.*.is_active' => ['required', 'boolean'],
            'hours.*.start_time' => ['nullable', 'date_format:H:i'],
            'hours.*.end_time' => ['nullable', 'date_format:H:i'],
        ];
    }
}
