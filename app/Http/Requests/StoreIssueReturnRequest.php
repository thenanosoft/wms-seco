<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIssueReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'return_date' => ['required', 'date'],
            'issue_id' => ['required', 'exists:issues,id'],
            'notes' => ['nullable', 'string'],

            'lines' => ['required', 'array', 'min:1'],
            'lines.*.issue_line_id' => ['required', 'exists:issue_lines,id'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ];
    }
}
