<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $fillable = [
        'subscribable_type',
        'subscribable_id',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
    ];

    /**
     * The polymorphic owner of this subscription (User or Kid).
     */
    public function subscribable()
    {
        return $this->morphTo();
    }
}
