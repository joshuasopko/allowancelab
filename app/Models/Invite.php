<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Invite extends Model
{
    protected $fillable = [
        'token',
        'kid_id',
        'email',
        'expires_at',
        'accepted_at',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    // Relationship: Invite belongs to a Kid
    public function kid()
    {
        return $this->belongsTo(Kid::class);
    }

    // Helper: Check if invite is expired
    public function isExpired()
    {
        return $this->expires_at < Carbon::now();
    }

    // Helper: Check if invite is pending (not accepted and not expired)
    public function isPending()
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    // Helper: Mark invite as accepted
    public function markAsAccepted()
    {
        $this->update([
            'accepted_at' => Carbon::now(),
            'status' => 'accepted',
        ]);
    }

    // Static: Generate a new invite for a kid
    public static function createForKid($kidId, $email = null)
    {
        return self::create([
            'token' => Str::random(32), // Generate unique 32-character token
            'kid_id' => $kidId,
            'email' => $email,
            'expires_at' => Carbon::now()->addDays(15), // Expires in 15 days
            'status' => 'pending',
        ]);
    }
}