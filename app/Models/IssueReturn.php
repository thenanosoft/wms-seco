<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IssueReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_date',
        'issue_id',
        'received_from',
        'reference_no',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(IssueReturnLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
