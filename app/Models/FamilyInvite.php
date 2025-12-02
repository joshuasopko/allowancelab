<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'email',
        'token',
        'role',
        'permissions',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'expires_at' => 'datetime',
    ];

    // Relationship: Invite belongs to family
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    // Helper: Check if invite is pending
    public function isPending()
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    // Helper: Check if invite is expired
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    // Helper: Mark as accepted
    public function markAsAccepted()
    {
        $this->update(['status' => 'accepted']);
    }

    // Helper: Mark as cancelled
    public function markAsCancelled()
    {
        $this->update(['status' => 'cancelled']);
    }
}