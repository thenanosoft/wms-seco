<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnTransaction extends Model
{
    protected $fillable = [
        'return_date',
        'type',
        'reference_no',
        'party',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'return_date' => 'date',
    ];

    public function lines()
    {
        return $this->hasMany(ReturnLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
