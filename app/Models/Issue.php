<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        'issue_date',
        'issued_to',
        'reference_no',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(IssueLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
