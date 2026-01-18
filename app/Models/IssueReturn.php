<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssueReturn extends Model
{
    protected $fillable = [
        'return_date',
        'issue_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function lines()
    {
        return $this->hasMany(IssueReturnLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
