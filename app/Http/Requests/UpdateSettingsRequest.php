<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'default_low_stock_threshold' => ['nullable','numeric','min:0'],

            'backup_enabled' => ['nullable','boolean'],
            'backup_frequency' => ['required','in:daily,weekly'],
            'backup_weekly_day' => ['required','integer','min:1','max:7'],
            'backup_time_hm' => ['required','regex:/^\d{2}:\d{2}$/'],
            'backup_path' => ['nullable','string'],
        ];
    }
}
