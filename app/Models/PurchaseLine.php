<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'item_id',
        'specification',
        'purchase_price',
        'quantity',
        'line_total',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
    
    public function setPurchasePriceAttribute(): void
    {
        // Price pending if empty or 0
        if ( === null ||  === "" || (is_numeric() && (float) <= 0)) {
            ->attributes["purchase_price"] = null;
            return;
        }
        ->attributes["purchase_price"] = (float);
    }
}
