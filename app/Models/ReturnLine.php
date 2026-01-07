<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnLine extends Model
{
    protected $fillable = [
        'return_transaction_id',
        'item_id',
        'specification',
        'unit_price',
        'quantity',
        'line_total',
    ];

    public function transaction()
    {
        return $this->belongsTo(ReturnTransaction::class, 'return_transaction_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
