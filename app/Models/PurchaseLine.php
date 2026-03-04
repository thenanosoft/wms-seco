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
    
    protected $casts = [
        'quantity' => 'float',
        'line_total' => 'float',
    ];

    public function setPurchasePriceAttribute($value): void
    {
        // Price pending if empty or 0
        if ($value === null || $value === "" || (is_numeric($value) && (float) $value <= 0)) {
            $this->attributes["purchase_price"] = null;
            return;
        }
        $this->attributes["purchase_price"] = round((float) $value, 4);
    }
}
