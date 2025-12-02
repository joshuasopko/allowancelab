<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FamilyMember extends Pivot
{
    protected $table = 'family_members';

    protected $fillable = [
        'family_id',
        'user_id',
        'role',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    // Relationship: Member belongs to family
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    // Relationship: Member belongs to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}