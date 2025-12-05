<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\EmailVerification;
use Carbon\Carbon;

class ParentAccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get user's family
        $family = $user->families()->first();

        // Get all kids accessible to this user
        $kids = $user->accessibleKids();

        // Calculate account stats
        $totalKids = $kids->count();
        $totalParents = $family ? $family->members()->count() : 1;
        $combinedBalance = $kids->sum('balance');

        // Calculate next allowance date and total upcoming allowance
        $daysOfWeek = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];
        $today = now();

        // Get the earliest upcoming allowance day
        $upcomingAllowances = $kids->map(function ($kid) use ($daysOfWeek, $today) {
            $targetDay = $daysOfWeek[$kid->allowance_day] ?? 5;
            $daysUntil = ($targetDay - $today->dayOfWeek + 7) % 7;
            if ($daysUntil === 0) $daysUntil = 7;
            $nextDate = $today->copy()->addDays($daysUntil);

            return [
                'date' => $nextDate,
                'amount' => $kid->allowance_amount,
                'kid' => $kid
            ];
        })->sortBy('date');

        $nextAllowanceDate = $upcomingAllowances->first()['date'] ?? null;
        $totalUpcomingAllowance = $upcomingAllowances->where('date', $nextAllowanceDate)->sum('amount');

        // Get kids with low points (below 3)
        $lowPointsKids = $kids->filter(function ($kid) {
            return $kid->points_enabled && $kid->points < 3;
        });

        // Get available timezones (US timezones)
        $timezones = [
            'America/New_York' => 'Eastern Time (ET)',
            'America/Chicago' => 'Central Time (CT)',
            'America/Denver' => 'Mountain Time (MT)',
            'America/Phoenix' => 'Mountain Time - Arizona (No DST)',
            'America/Los_Angeles' => 'Pacific Time (PT)',
            'America/Anchorage' => 'Alaska Time (AKT)',
            'Pacific/Honolulu' => 'Hawaii Time (HST)',
        ];

        return view('parent.account', compact(
            'user',
            'family',
            'totalKids',
            'totalParents',
            'combinedBalance',
            'nextAllowanceDate',
            'totalUpcomingAllowance',
            'lowPointsKids',
            'timezones'
        ));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->name = $request->first_name . ' ' . $request->last_name;
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function requestEmailChange(Request $request)
    {
        $request->validate([
            'new_email' => 'required|email|max:255|unique:users,email',
            'password' => 'required',
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The provided password is incorrect.']);
        }

        // Delete any existing email verification for this user
        EmailVerification::where('user_id', $user->id)->delete();

        // Create new email verification token
        $token = Str::random(64);

        EmailVerification::create([
            'user_id' => $user->id,
            'new_email' => $request->new_email,
            'token' => $token,
            'expires_at' => now()->addHours(24),
        ]);

        // Send verification email
        $verifyUrl = route('parent.account.verify-email', ['token' => $token]);

        try {
            Mail::send('emails.verify-email-change', [
                'user' => $user,
                'newEmail' => $request->new_email,
                'verifyUrl' => $verifyUrl,
            ], function ($message) use ($request) {
                $message->to($request->new_email)
                    ->subject('Verify Your New Email Address - AllowanceLab');
            });

            return back()->with('success', 'Verification email sent to ' . $request->new_email . '. Please check your inbox.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send verification email. Please try again.']);
        }
    }

    public function verifyEmailChange($token)
    {
        $verification = EmailVerification::where('token', $token)->first();

        if (!$verification) {
            return redirect()->route('parent.account')->withErrors(['error' => 'Invalid verification link.']);
        }

        if ($verification->isExpired()) {
            $verification->delete();
            return redirect()->route('parent.account')->withErrors(['error' => 'Verification link has expired.']);
        }

        // Update user's email
        $user = $verification->user;
        $user->email = $verification->new_email;
        $user->save();

        // Delete verification record
        $verification->delete();

        return redirect()->route('parent.account')->with('success', 'Email address updated successfully!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password is incorrect.']);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Send notification email
        try {
            Mail::send('emails.password-changed', [
                'user' => $user,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your Password Has Been Changed - AllowanceLab');
            });
        } catch (\Exception $e) {
            // Continue even if email fails
        }

        // Log out user
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Password changed successfully. Please login with your new password.');
    }

    public function updateTimezone(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string',
        ]);

        $user = Auth::user();
        $user->timezone = $request->timezone;
        $user->save();

        return back()->with('success', 'Timezone updated successfully.');
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'confirmation_text' => 'required|in:CONFIRM DELETE',
        ]);

        $user = Auth::user();

        // Get user's family
        $family = $user->families()->first();

        if (!$family) {
            return back()->withErrors(['error' => 'No family found to delete.']);
        }

        // Only the owner can delete the entire family
        if (!$family->isOwner($user)) {
            return back()->withErrors(['error' => 'Only the family owner can delete the account.']);
        }

        // Delete entire family and all associated data
        // Laravel's cascade will handle related records
        $family->delete();

        // Log out user
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account and all family data have been permanently deleted.');
    }
}
