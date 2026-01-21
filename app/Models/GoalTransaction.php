<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoalTransaction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'goal_id',
        'kid_id',
        'family_id',
        'amount',
        'transaction_type',
        'description',
        'performed_by_user_id',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    // Relationship: Transaction belongs to a Goal
    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    // Relationship: Transaction belongs to a Kid
    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }

    // Relationship: Transaction belongs to a Family
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    // Relationship: Transaction was performed by a User
    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    // Helper: Check if this is a deposit
    public function isDeposit()
    {
        return in_array($this->transaction_type, ['auto_allocation', 'manual_deposit']);
    }

    // Helper: Check if this is a withdrawal
    public function isWithdrawal()
    {
        return in_array($this->transaction_type, ['manual_withdrawal', 'redemption']);
    }
}
