<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_line_id',
        'purchase_date',
        'item_id',
        'specification',
        'qty_purchased',
        'qty_available',
        'unit_price',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'unit_price' => 'integer',
        'qty_purchased' => 'integer',
        'qty_available' => 'integer',
    ];

    public function purchaseLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseLine::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
