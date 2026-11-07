<?php

namespace App\Http\Requests\Analytics;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (string) $this->user()?->role === 'admin';
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['period' => $this->input('period', '30')]);
    }

    public function rules(): array
    {
        return [
            'period' => ['required', 'in:7,30,90,custom'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('period') !== 'custom') {
                return;
            }
            if (! $this->filled('from') || ! $this->filled('to')) {
                $validator->errors()->add('from', 'Tanggal awal dan akhir wajib diisi untuk rentang khusus.');
                return;
            }
            try {
                $from = CarbonImmutable::createFromFormat('!Y-m-d', $this->input('from'));
                $to = CarbonImmutable::createFromFormat('!Y-m-d', $this->input('to'));
                if (! $from || ! $to || $from->gt($to)) {
                    $validator->errors()->add('from', 'Rentang tanggal tidak valid.');
                    return;
                }
                if ($to->isAfter(today())) {
                    $validator->errors()->add('to', 'Tanggal akhir tidak boleh melewati hari ini.');
                }
                if ($from->diffInDays($to) + 1 > (int) config('jasawarga-analytics.date_filter_max_days', 366)) {
                    $validator->errors()->add('from', 'Rentang laporan maksimal 366 hari.');
                }
            } catch (\Throwable) {
                $validator->errors()->add('from', 'Format tanggal tidak valid.');
            }
        });
    }

    public function filters(): array
    {
        if ($this->input('period') === 'custom') {
            return ['from' => $this->input('from'), 'to' => $this->input('to'), 'period' => 'custom'];
        }

        $days = (int) $this->input('period', 30);
        return [
            'from' => today()->subDays($days - 1)->toDateString(),
            'to' => today()->toDateString(),
            'period' => (string) $days,
        ];
    }
}
