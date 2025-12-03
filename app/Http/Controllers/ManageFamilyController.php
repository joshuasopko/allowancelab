<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManageFamilyController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get user's family (they should have one from migration)
        $family = $user->families()->first();

        // If no family exists, create one (safety check)
        if (!$family) {
            $family = \App\Models\Family::create([
                'name' => null,
                'owner_user_id' => $user->id,
            ]);

            $family->members()->attach($user->id, [
                'role' => 'owner',
                'permissions' => null,
            ]);
        }

        // Check if user is owner
        $isOwner = $family->isOwner($user);
        $userRole = $family->getUserRole($user);

        // Get all family members
        $familyMembers = $family->members()->get();

        // Get all kids in family
        $kids = $family->kids;

        // Get pending invites (owner only)
        $pendingInvites = $isOwner ? $family->pendingInvites() : collect([]);

        return view('parent.manage-family', compact(
            'user',
            'family',
            'isOwner',
            'userRole',
            'familyMembers',
            'kids',
            'pendingInvites'
        ));
    }
}