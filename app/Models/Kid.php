<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Kid extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'name',
        'email',
        'username',
        'password',
        'password_plaintext',
        'birthday',
        'avatar',
        'color',
        'balance',
        'points',
        'points_enabled',
        'allowance_amount',
        'allowance_day',
        'max_points',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'birthday' => 'date',
        'balance' => 'decimal:2',
        'points_enabled' => 'boolean',
        'allowance_amount' => 'decimal:2',
        'last_login_at' => 'datetime',
    ];

    // Relationship: Kid belongs to a Family
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    // Relationship: Kid has many transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Relationship: Kid has many point adjustments
    public function pointAdjustments()
    {
        return $this->hasMany(PointAdjustment::class);
    }

    // Relationship: Kid has one invite
    public function invite()
    {
        return $this->hasOne(Invite::class);
    }

    // Relationship: Kid has many goals
    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    // Helper: Check if kid has a pending invite
    public function hasPendingInvite()
    {
        return $this->invite && $this->invite->isPending();
    }

    // Helper: Get active goals count
    public function getActiveGoalsCount()
    {
        return $this->goals()->whereIn('status', ['active', 'ready_to_redeem'])->count();
    }

    // Helper: Check if kid has any ready to redeem goals (actually complete)
    public function hasReadyToRedeemGoals()
    {
        return $this->goals()
            ->where(function($query) {
                $query->where('status', 'ready_to_redeem')
                      ->orWhere(function($q) {
                          $q->where('status', 'active')
                            ->whereRaw('current_amount >= target_amount');
                      });
            })
            ->exists();
    }

    // Helper: Get count of ready to redeem goals (actually complete)
    public function getReadyToRedeemGoalsCount()
    {
        return $this->goals()
            ->where(function($query) {
                $query->where('status', 'ready_to_redeem')
                      ->orWhere(function($q) {
                          $q->where('status', 'active')
                            ->whereRaw('current_amount >= target_amount');
                      });
            })
            ->count();
    }

    // Helper: Get count of pending redemption goals
    public function getPendingRedemptionGoalsCount()
    {
        return $this->goals()->where('status', 'pending_redemption')->count();
    }
}