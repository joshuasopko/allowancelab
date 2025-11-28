<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'kid_id',
        'type',
        'amount',
        'description',
        'category',
        'initiated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationship: Transaction belongs to a Kid
    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }
}