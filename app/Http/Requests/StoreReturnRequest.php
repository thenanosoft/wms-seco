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

    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $lines = $this->input('lines', []);
        $seen = [];

        foreach ($lines as $i => $line) {
            $itemId = $line['item_id'] ?? null;
            $groupId = $line['group_id'] ?? null;

            if (!$itemId || !$groupId) continue;

            if (isset($seen[$itemId])) {
                $validator->errors()->add("lines.$i.item_id", "This item is already added in another line.");
            }
            $seen[$itemId] = true;

            $exists = \App\Models\Item::where('id', $itemId)->where('group_id', $groupId)->exists();
            if (!$exists) {
                $validator->errors()->add("lines.$i.group_id", "Selected item does not belong to selected group.");
            }
        }
    });
}

}
