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
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'birthday' => 'date',
        'balance' => 'decimal:2',
        'points_enabled' => 'boolean',
        'allowance_amount' => 'decimal:2',
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

    // Helper: Check if kid has a pending invite
    public function hasPendingInvite()
    {
        return $this->invite && $this->invite->isPending();
    }
}