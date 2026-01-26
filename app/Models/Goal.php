<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'kid_id',
        'created_by_user_id',
        'title',
        'description',
        'product_url',
        'photo_path',
        'target_amount',
        'current_amount',
        'auto_allocation_percentage',
        'expected_completion_date',
        'status',
        'redeemed_at',
        'redeemed_by_user_id',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'auto_allocation_percentage' => 'decimal:2',
        'expected_completion_date' => 'date',
        'redeemed_at' => 'datetime',
    ];

    // Relationship: Goal belongs to a Family
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    // Relationship: Goal belongs to a Kid
    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }

    // Relationship: Goal was created by a User (parent or kid)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // Relationship: Goal was redeemed by a User (parent)
    public function redeemedBy()
    {
        return $this->belongsTo(User::class, 'redeemed_by_user_id');
    }

    // Relationship: Goal has many transactions
    public function goalTransactions()
    {
        return $this->hasMany(GoalTransaction::class)->orderBy('created_at', 'desc');
    }

    // Helper: Check if goal is ready to redeem
    public function isReadyToRedeem()
    {
        return $this->status === 'ready_to_redeem' ||
               $this->status === 'pending_redemption' ||
               ($this->status === 'active' && $this->current_amount >= $this->target_amount);
    }

    // Helper: Check if goal is active
    public function isActive()
    {
        return $this->status === 'active';
    }

    // Helper: Check if goal is pending redemption
    public function isPendingRedemption()
    {
        return $this->status === 'pending_redemption';
    }

    // Helper: Check if goal is redeemed
    public function isRedeemed()
    {
        return $this->status === 'redeemed';
    }

    // Helper: Check if goal is complete (ready, pending, or redeemed)
    public function isComplete()
    {
        return in_array($this->status, ['ready_to_redeem', 'pending_redemption', 'redeemed']);
    }

    // Helper: Check if goal can be edited
    public function canBeEdited()
    {
        return in_array($this->status, ['active', 'ready_to_redeem']);
    }

    // Helper: Get progress percentage
    public function getProgressPercentage()
    {
        if ($this->target_amount <= 0) {
            return 0;
        }
        return min(100, ($this->current_amount / $this->target_amount) * 100);
    }

    // Helper: Get amount remaining
    public function getAmountRemaining()
    {
        return max(0, $this->target_amount - $this->current_amount);
    }

    // Automatically update status when current amount reaches target
    protected static function booted()
    {
        static::saving(function ($goal) {
            if ($goal->current_amount >= $goal->target_amount && $goal->status === 'active') {
                $goal->status = 'ready_to_redeem';
            }
        });
    }
}
