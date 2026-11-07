<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterPartnerRequest extends RegisterCitizenRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'business_name' => ['required', 'string', 'min:3', 'max:120'],
            'business_phone' => ['required', 'regex:/^08[0-9]{8,11}$/'],
            'service_area' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
