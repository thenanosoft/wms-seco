<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IssueReturnLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_return_id',
        'issue_line_id',
        'item_id',
        'specification_snapshot',
        'quantity',
        'unit_price',
        'line_total',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(IssueReturn::class, 'issue_return_id');
    }

    public function issueLine(): BelongsTo
    {
        return $this->belongsTo(IssueLine::class, 'issue_line_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
