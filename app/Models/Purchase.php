<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_date',
        'supplier_name',
        'reference_no',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
