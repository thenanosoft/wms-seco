<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'item_code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'default_spec' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $groupId = $this->input('group_id');
            $itemCode = $this->input('item_code');

            if ($groupId && $itemCode) {
                $exists = \App\Models\Item::where('group_id', $groupId)
                    ->where('item_code', $itemCode)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('item_code', 'This item code already exists in the selected group.');
                }
            }
        });
    }
}
