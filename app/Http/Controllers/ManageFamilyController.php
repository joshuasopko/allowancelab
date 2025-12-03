<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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

    public function sendInvite(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $user = Auth::user();
        $family = $user->families()->first();

        // Check if user is owner
        if (!$family || !$family->isOwner($user)) {
            return back()->withErrors(['error' => 'Only family owners can send invitations.']);
        }

        // Check if email is already a family member
        $existingMember = \App\Models\User::where('email', $request->email)->first();
        if ($existingMember && $family->isMember($existingMember)) {
            return back()->withErrors(['email' => 'This user is already a family member.']);
        }

        // Check if there's already a pending invite for this email
        $existingInvite = $family->invites()
            ->where('email', $request->email)
            ->where('status', 'pending')
            ->first();

        if ($existingInvite) {
            return back()->withErrors(['email' => 'An invitation has already been sent to this email.']);
        }

        // Create invite
        $invite = \App\Models\FamilyInvite::create([
            'family_id' => $family->id,
            'email' => $request->email,
            'token' => \Illuminate\Support\Str::random(64),
            'role' => 'co-parent',
            'permissions' => null,
            'status' => 'pending',
            'expires_at' => now()->addDays(14),
        ]);

        // Send email
        try {
            \Illuminate\Support\Facades\Mail::send('emails.parent-invite', [
                'invitedName' => $request->name,
                'inviterName' => $user->name,
                'invite' => $invite,
                'acceptUrl' => route('family.accept-invite', ['token' => $invite->token]),
            ], function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('You\'ve been invited to join a family on AllowanceLab!');
            });

            return back()->with('success', 'Invitation sent successfully to ' . $request->email);
        } catch (\Exception $e) {
            // If email fails, delete the invite
            $invite->delete();
            return back()->withErrors(['error' => 'Failed to send invitation email. Please try again.']);
        }
    }

}