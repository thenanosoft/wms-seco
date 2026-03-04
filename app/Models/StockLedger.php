<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockLedger extends Model
{
    use HasFactory;

    protected $table = 'stock_ledger';

    protected $fillable = [
        'txn_date',
        'txn_type',
        'ref_table',
        'ref_id',
        'ref_line_id',
        'item_id',
        'qty_in',
        'qty_out',
        'unit_price',
        'specification_snapshot',
        'created_by',
    ];

    protected $casts = [
        'txn_date' => 'date',
        'qty_in' => 'float',
        'qty_out' => 'float',
        'unit_price' => 'float',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
