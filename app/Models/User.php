<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationship: User owns families (as SuperAdmin)
    public function ownedFamilies()
    {
        return $this->hasMany(Family::class, 'owner_user_id');
    }

    // Relationship: User belongs to families (as member)
    public function families()
    {
        return $this->belongsToMany(Family::class, 'family_members')
            ->withPivot('role', 'permissions')
            ->withTimestamps();
    }

    // Helper: Get all kids this user can access
    public function accessibleKids()
    {
        $familyIds = $this->families()->pluck('families.id');
        return Kid::whereIn('family_id', $familyIds)->get();
    }

    // Override password reset notification to use custom branded email
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

}
