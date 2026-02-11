<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WishTransaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'wish_id',
        'kid_id',
        'family_id',
        'performed_by_user_id',
        'transaction_type',
        'note',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function wish()
    {
        return $this->belongsTo(Wish::class);
    }

    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
