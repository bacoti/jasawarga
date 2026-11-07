<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplyServiceReviewRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'partner'; }
    public function rules(): array { return ['body' => ['required','string','min:10','max:600']]; }
}
