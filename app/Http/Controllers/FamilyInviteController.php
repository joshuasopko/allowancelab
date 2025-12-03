<?php

namespace App\Http\Controllers;

use App\Models\FamilyInvite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FamilyInviteController extends Controller
{
    public function show($token)
    {
        $invite = FamilyInvite::where('token', $token)->firstOrFail();
        $family = $invite->family;

        return view('family.accept-invite', compact('invite', 'family'));
    }

    public function accept(Request $request, $token)
    {
        $invite = FamilyInvite::where('token', $token)->firstOrFail();

        // Check if invite is valid
        if ($invite->isExpired() || $invite->status !== 'pending') {
            return redirect()->route('family.accept-invite', ['token' => $token])
                ->withErrors(['error' => 'This invitation is no longer valid.']);
        }

        // Validate request
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if user already exists with this email
        $existingUser = User::where('email', $invite->email)->first();

        if ($existingUser) {
            // Link existing user to family
            $user = $existingUser;

            // Check if already a member
            if ($invite->family->isMember($user)) {
                return redirect()->route('family.accept-invite', ['token' => $token])
                    ->withErrors(['error' => 'You are already a member of this family.']);
            }
        } else {
            // Create new user
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $invite->email,
                'password' => Hash::make($request->password),
            ]);
        }

        // Add user to family
        $invite->family->members()->attach($user->id, [
            'role' => $invite->role,
            'permissions' => $invite->permissions,
        ]);

        // Mark invite as accepted
        $invite->markAsAccepted();

        // Log user in
        Auth::login($user);

        // Redirect to dashboard
        return redirect()->route('dashboard')->with('success', 'Welcome to the family! You now have full access.');
    }
}