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
            'store_name' => ['nullable','string','max:120'],
            'timezone' => ['nullable','string','in:Asia/Karachi'],
            'default_low_stock_threshold' => ['nullable','numeric','min:0'],
        ];
    }
}
