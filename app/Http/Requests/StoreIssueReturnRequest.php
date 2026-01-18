<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIssueReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'return_date' => ['required','date'],
            'issue_id' => ['required','integer','exists:issues,id'],
            'received_from' => ['nullable','string','max:255'],
            'reference_no' => ['nullable','string','max:255'],
            'notes' => ['nullable','string','max:255'],

            'lines' => ['required','array','min:1'],
            'lines.*.issue_line_id' => ['required','integer','exists:issue_lines,id'],
            'lines.*.quantity' => ['required','numeric','gt:0'],
        ];
    }
}
