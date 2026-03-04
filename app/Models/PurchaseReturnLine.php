<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseReturnLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_return_transaction_id',
        'purchase_line_id',
        'item_id',
        'specification',
        'purchase_price',
        'quantity',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'float',
        'line_total' => 'float',
        'purchase_price' => 'float',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturnTransaction::class, 'purchase_return_transaction_id');
    }

    public function purchaseLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseLine::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
