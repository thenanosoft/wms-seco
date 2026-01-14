<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'item_code',
        'name',
        'default_spec',
        'low_stock_threshold',
    ];

    protected $casts = [
        'low_stock_threshold' => 'decimal:3',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function purchaseLines(): HasMany
    {
        return $this->hasMany(PurchaseLine::class);
    }

    public function issueLines(): HasMany
    {
        return $this->hasMany(IssueLine::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(StockLedger::class);
    }
}
