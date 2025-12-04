<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\FamilyInvite;

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

        // Get all family members with details
        $familyMembers = $family->members()
            ->withPivot('role', 'permissions', 'created_at')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $member->pivot->role,
                    'joined_at' => $member->pivot->created_at,
                ];
            });

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

    public function removeMember(User $user)
    {
        $authUser = Auth::user();
        $family = $authUser->families()->first();

        // Check if user is owner
        if (!$family || !$family->isOwner($authUser)) {
            return back()->withErrors(['error' => 'Only family owners can remove members.']);
        }

        // Can't remove yourself
        if ($user->id === $authUser->id) {
            return back()->withErrors(['error' => 'You cannot remove yourself from the family.']);
        }

        // Can't remove the owner
        if ($family->isOwner($user)) {
            return back()->withErrors(['error' => 'Cannot remove the family owner.']);
        }

        // Check if user is actually a member
        if (!$family->isMember($user)) {
            return back()->withErrors(['error' => 'This user is not a member of your family.']);
        }

        // Remove user from family
        $family->members()->detach($user->id);

        return redirect()->route('manage-family', ['tab' => 'user-accounts'])
            ->with('success', $user->name . ' has been removed from the family.');
    }

    public function resendInvite(FamilyInvite $invite)
    {
        $user = Auth::user();
        $family = $user->families()->first();

        // Check if user is owner and invite belongs to their family
        if (!$family || !$family->isOwner($user) || $invite->family_id !== $family->id) {
            return back()->withErrors(['error' => 'Unauthorized action.']);
        }

        // Check if invite is still pending
        if ($invite->status !== 'pending') {
            return back()->withErrors(['error' => 'This invitation is no longer pending.']);
        }

        // Check if expired, extend expiration
        if ($invite->isExpired()) {
            $invite->expires_at = now()->addDays(14);
            $invite->save();
        }

        // Resend email
        try {
            Mail::send('emails.parent-invite', [
                'invitedName' => $invite->email,
                'inviterName' => $user->name,
                'invite' => $invite,
                'acceptUrl' => route('family.accept-invite', ['token' => $invite->token]),
            ], function ($message) use ($invite) {
                $message->to($invite->email)
                    ->subject('Reminder: You\'ve been invited to join a family on AllowanceLab!');
            });

            return redirect()->route('manage-family', ['tab' => 'user-accounts'])
                ->with('success', 'Invitation resent to ' . $invite->email);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to resend invitation. Please try again.']);
        }
    }

    public function cancelInvite(FamilyInvite $invite)
    {
        $user = Auth::user();
        $family = $user->families()->first();

        // Check if user is owner and invite belongs to their family
        if (!$family || !$family->isOwner($user) || $invite->family_id !== $family->id) {
            return back()->withErrors(['error' => 'Unauthorized action.']);
        }

        // Mark as cancelled and delete
        $email = $invite->email;
        $invite->markAsCancelled();
        $invite->delete();

        return redirect()->route('manage-family', ['tab' => 'user-accounts'])
            ->with('success', 'Invitation to ' . $email . ' has been cancelled.');
    }

}