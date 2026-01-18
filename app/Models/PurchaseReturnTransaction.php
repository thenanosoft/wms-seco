<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseReturnTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_date',
        'purchase_id',
        'notes',
        'created_by',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseReturnLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
