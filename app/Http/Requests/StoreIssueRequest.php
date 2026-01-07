<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'issue_date' => ['required', 'date'],
            'issued_to' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'lines' => ['required', 'array', 'min:1'],
            'lines.*.group_id' => ['required', 'integer', 'exists:groups,id'],
            'lines.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'lines.*.specification' => ['nullable', 'string', 'max:2000'],
            'lines.*.issue_price' => ['nullable', 'numeric', 'min:0'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ];
    }

    public function messages(): array
    {
        return [
            'lines.required' => 'At least 1 item line is required.',
            'lines.min' => 'At least 1 item line is required.',
        ];
    }
}
