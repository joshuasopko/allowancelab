<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wish extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'family_id',
        'kid_id',
        'created_by_user_id',
        'item_name',
        'item_url',
        'image_path',
        'price',
        'reason',
        'status',
        'requested_at',
        'last_reminded_at',
        'approved_at',
        'purchased_at',
        'purchased_by_user_id',
        'declined_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'requested_at' => 'datetime',
        'last_reminded_at' => 'datetime',
        'approved_at' => 'datetime',
        'purchased_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    // Relationships
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function purchasedBy()
    {
        return $this->belongsTo(User::class, 'purchased_by_user_id');
    }

    public function wishTransactions()
    {
        return $this->hasMany(WishTransaction::class)->orderBy('created_at', 'desc');
    }

    // Helper Methods
    public function isSaved()
    {
        return $this->status === 'saved';
    }

    public function isPendingApproval()
    {
        return $this->status === 'pending_approval';
    }

    public function canRemindParent()
    {
        if ($this->status !== 'pending_approval' || !$this->requested_at) {
            return false;
        }

        // Allow reminder if 24 hours passed since request or last reminder
        $lastAction = $this->last_reminded_at ?? $this->requested_at;
        return now()->diffInHours($lastAction) >= 24;
    }

    public function isPurchased()
    {
        return $this->status === 'purchased';
    }

    public function isDeclined()
    {
        return $this->status === 'declined';
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['saved', 'pending_approval']);
    }

    /**
     * Whether the kid can re-ask after a decline (24-hour cooldown).
     */
    public function canReAsk(): bool
    {
        if ($this->status !== 'declined' || !$this->declined_at) {
            return false;
        }
        return now()->diffInHours($this->declined_at) >= 24;
    }

    /**
     * Fractional hours remaining until the kid can re-ask (0 when ready).
     */
    public function hoursUntilReAsk(): float
    {
        if (!$this->declined_at) return 0;
        $hoursElapsed = now()->floatDiffInHours($this->declined_at);
        return max(0, 24 - $hoursElapsed);
    }

    public function canBeRequested()
    {
        // Check if kid has sufficient balance
        return $this->status === 'saved' && $this->kid->balance >= $this->price;
    }
}
