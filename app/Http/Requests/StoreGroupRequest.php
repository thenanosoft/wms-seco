<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'group_code' => ['required', 'string', 'max:50', 'unique:groups,group_code'],
            'group_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
