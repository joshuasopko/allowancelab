<?php

namespace App\Http\Traits;

use App\Models\Kid;
use Illuminate\Support\Facades\Auth;

/**
 * Shared authorization check used by controllers that act on behalf of a parent
 * managing a specific kid. Aborts 403 if the authenticated user does not belong
 * to the same family as the given kid.
 */
trait AuthorizesKidAccess
{
    protected function authorizeKidAccess(Kid $kid): void
    {
        $familyIds = Auth::user()->families()->pluck('families.id');

        if (!$familyIds->contains($kid->family_id)) {
            abort(403, 'Unauthorized access to this kid.');
        }
    }
}
