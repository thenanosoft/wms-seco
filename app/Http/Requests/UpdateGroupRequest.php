<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        $groupId = $this->route('group')->id;

        return [
            'group_code' => ['required', 'string', 'max:50', Rule::unique('groups', 'group_code')->ignore($groupId)],
            'group_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
