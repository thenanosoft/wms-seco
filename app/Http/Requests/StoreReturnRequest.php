<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'return_date' => ['required','date'],
            'type' => ['required','in:IN,OUT'],
            'party' => ['nullable','string'],
            'reference_no' => ['nullable','string'],
            'notes' => ['nullable','string'],

            'lines' => ['required','array','min:1'],
            'lines.*.item_id' => ['required','exists:items,id'],
            'lines.*.quantity' => ['required','numeric','min:0.001'],
            'lines.*.unit_price' => ['nullable','numeric','min:0'],
        ];
    }
}
