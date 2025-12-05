<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($family) {
            // Delete all kids (which will cascade to transactions and point adjustments)
            $family->kids()->each(function ($kid) {
                $kid->delete();
            });

            // Delete all family invites
            $family->invites()->delete();

            // Detach all members from family
            $family->members()->detach();

            // Delete the owner user account
            if ($family->owner) {
                $family->owner->delete();
            }
        });
    }

    // Relationship: Family has one owner (SuperAdmin)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    // Relationship: Family has many members
    public function members()
    {
        return $this->belongsToMany(User::class, 'family_members')
            ->withPivot('role', 'permissions')
            ->withTimestamps();
    }

    // Relationship: Family has many kids
    public function kids()
    {
        return $this->hasMany(Kid::class);
    }

    // Relationship: Family has many invites
    public function invites()
    {
        return $this->hasMany(FamilyInvite::class);
    }

    // Helper: Get pending invites
    public function pendingInvites()
    {
        return $this->invites()->where('status', 'pending')->get();
    }

    // Helper: Check if user is owner
    public function isOwner(User $user)
    {
        return $this->owner_user_id === $user->id;
    }

    // Helper: Check if user is member
    public function isMember(User $user)
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    // Helper: Get user's role in family
    public function getUserRole(User $user)
    {
        $member = $this->members()->where('user_id', $user->id)->first();
        return $member ? $member->pivot->role : null;
    }
}