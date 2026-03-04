<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IssueReturnLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_return_transaction_id',
        'issue_line_id',
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

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(IssueReturnTransaction::class, 'issue_return_transaction_id');
    }

    public function issueLine(): BelongsTo
    {
        return $this->belongsTo(IssueLine::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
