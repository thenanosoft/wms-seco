<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IssueLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_id',
        'purchase_line_id',
        'item_id',
        'specification',
        'issue_price',
        'quantity',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'float',
        'line_total' => 'float',
        'issue_price' => 'float',
    ];

    public function purchaseLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseLine::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
