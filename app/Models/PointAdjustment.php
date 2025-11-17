<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'kid_id',
        'points_change',
        'reason',
    ];

    // Relationship: Point adjustment belongs to a Kid
    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }
}