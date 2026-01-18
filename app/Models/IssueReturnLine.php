<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueReturnLine extends Model
{
    protected $fillable = [
        'issue_return_id',
        'issue_line_id',
        'item_id',
        'quantity',
        'unit_price',
        'line_total',
    ];

    public function issueReturn()
    {
        return $this->belongsTo(IssueReturn::class);
    }

    public function issueLine()
    {
        return $this->belongsTo(IssueLine::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
