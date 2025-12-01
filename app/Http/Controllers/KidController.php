<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Kid;
use Carbon\Carbon;

use Illuminate\Support\Facades\Mail;
use App\Mail\KidInviteMail;

class KidController extends Controller
{
    // Store a new kid
    public function store(Request $request)
    {
        // Validate the form data
        $request->validate([
            'name' => 'required|string|max:255',
            'birthday' => 'required|date|before:today',
            'avatar' => 'required|string',
            'color' => 'required|string',
            'allowance_amount' => 'required|numeric|min:0',
            'allowance_day' => 'required|string',
            'points_enabled' => 'nullable',
            'max_points' => 'nullable|integer|min:1|max:100',
        ]);

        $pointsEnabled = $request->has('points_enabled');
        $maxPoints = $pointsEnabled ? ($request->max_points ?? 10) : 10;

        // Calculate next allowance date
        $nextAllowanceDate = Carbon::parse("next {$request->allowance_day}")->setTime(0, 0, 1);
        if ($nextAllowanceDate->isToday()) {
            $nextAllowanceDate = Carbon::today()->setTime(0, 0, 1);
        }

        // Create the kid without username/password (they'll set it when they accept invite)
        $kid = Kid::create([
            'user_id' => Auth::id(),
            'name' => ucwords(strtolower($request->name)),
            'username' => null, // Kid will create this during registration
            'password' => null, // Kid will create this during registration
            'birthday' => $request->birthday,
            'avatar' => $request->avatar,
            'color' => $request->color,
            'allowance_amount' => $request->allowance_amount,
            'allowance_day' => $request->allowance_day,
            'next_allowance_date' => $nextAllowanceDate,
            'points_enabled' => $pointsEnabled,
            'max_points' => $maxPoints,
            'balance' => 0,
            'points' => $maxPoints,
        ]);

        // Return kid data as JSON for the modal transition
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'kid' => [
                    'id' => $kid->id,
                    'name' => $kid->name,
                ]
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Kid added successfully!');
    }

    // Update kid balance
    public function updateBalance(Request $request, Kid $kid)
    {
        // Make sure this kid belongs to the logged-in parent
        if ($kid->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'amount' => 'required|numeric',
        ]);

        $kid->balance += $request->amount;
        $kid->save();

        return back()->with('success', 'Balance updated!');
    }

    // Update kid points
    public function updatePoints(Request $request, Kid $kid)
    {
        // Make sure this kid belongs to the logged-in parent
        if ($kid->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'points_change' => 'required|integer',
            'reason' => 'required|string',
        ]);

        $kid->points += $request->points_change;
        $kid->save();

        // Record the point adjustment
        $kid->pointAdjustments()->create([
            'points_change' => $request->points_change,
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Points updated!');
    }

    public function deposit(Request $request, Kid $kid)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255'
        ]);

        $kid->balance += $request->amount;
        $kid->save();

        $kid->transactions()->create([
            'type' => 'deposit',
            'amount' => $request->amount,
            'description' => $request->note,
            'initiated_by' => 'parent'
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Deposit recorded successfully',
                'new_balance' => $kid->balance
            ]);
        }

        return back()->with('success', 'Deposit recorded successfully');
    }

    public function spend(Request $request, Kid $kid)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255'
        ]);

        $kid->balance -= $request->amount;
        $kid->save();

        $kid->transactions()->create([
            'type' => 'spend',
            'amount' => $request->amount,
            'description' => $request->note,
            'initiated_by' => 'parent'
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Spending recorded successfully',
                'new_balance' => $kid->balance
            ]);
        }

        return back()->with('success', 'Spending recorded successfully');
    }

    public function adjustPoints(Request $request, Kid $kid)
    {
        $request->validate([
            'points' => 'required|integer',
            'reason' => 'nullable|string|max:255'
        ]);

        $kid->points += $request->points;
        $kid->save();

        $kid->pointAdjustments()->create([
            'points_change' => $request->points,
            'reason' => $request->reason
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Points adjusted successfully',
                'new_points' => $kid->points
            ]);
        }

        return back()->with('success', 'Points adjusted successfully');
    }

    public function getTransactions(Request $request, Kid $kid)
    {
        $type = $request->get('type', 'all');
        $days = $request->get('days', '30');

        // Get transactions
        $transactions = $kid->transactions()->latest();

        // Filter by date range
        if ($days !== 'all') {
            $transactions->where('created_at', '>=', now()->subDays((int) $days));
        }

        // Filter by type
        if ($type !== 'all') {
            $transactions->where('type', $type);
        }

        $transactionsData = $transactions->get()->map(function ($t) use ($kid) {
            $dateObj = $t->created_at;
            return [
                'date' => $dateObj->format('M d, Y'),
                'time' => $dateObj->format('g:i A'),
                'type' => $t->type,
                'type_label' => ucfirst($t->type),
                'amount_display' => '$' . number_format($t->amount, 2),
                'note' => $t->description,
                'initiated_by' => $t->initiated_by ?? 'parent',
                'kid_color' => $kid->color
            ];
        });

        // Get point adjustments
        $pointAdjustments = $kid->pointAdjustments()->latest();

        if ($days !== 'all') {
            $pointAdjustments->where('created_at', '>=', now()->subDays((int) $days));
        }

        if ($type === 'all' || $type === 'points') {
            $pointsData = $pointAdjustments->get()->map(function ($p) use ($kid) {
                $dateObj = $p->created_at;
                return [
                    'date' => $dateObj->format('M d, Y'),
                    'time' => $dateObj->format('g:i A'),
                    'type' => 'points',
                    'type_label' => 'Points',
                    'amount_display' => ($p->points_change > 0 ? '+' : '') . $p->points_change . ' pts',
                    'amount_class' => $p->points_change > 0 ? 'points-add' : 'points-deduct',
                    'note' => $p->reason,
                    'initiated_by' => 'parent',
                    'kid_color' => $kid->color
                ];
            });

            $transactionsData = $transactionsData->concat($pointsData);
        }

        // Sort by created_at timestamp before returning
        $transactionsData = $transactionsData->sortByDesc(function ($item) {
            return strtotime($item['date'] . ' ' . $item['time']);
        })->values();

        return response()->json($transactionsData);
    }

    // Display the Manage Kid page
    public function manage(Kid $kid)
    {
        // Make sure this kid belongs to the logged-in parent
        if ($kid->user_id !== Auth::id()) {
            abort(403);
        }

        return view('parent.manage-kid', compact('kid'));
    }

    // Update kid information
    public function update(Request $request, Kid $kid)
    {
        // Make sure this kid belongs to the logged-in parent
        if ($kid->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'birthday' => 'required|date|before:today',
            'color' => 'required|string',
            'allowance_amount' => 'required|numeric|min:0',
            'allowance_day' => 'required|string',
            'points_enabled' => 'nullable',
            'max_points' => 'nullable|integer|min:1|max:100',
        ]);

        $pointsEnabled = $request->has('points_enabled');

        $kid->update([
            'name' => ucwords(strtolower($request->name)),
            'birthday' => $request->birthday,
            'color' => $request->color,
            'allowance_amount' => $request->allowance_amount,
            'allowance_day' => $request->allowance_day,
            'points_enabled' => $pointsEnabled,
            'max_points' => $request->max_points ?? 10,
        ]);

        return redirect()->route('kids.manage', $kid)->with('success', 'Changes saved successfully!');
    }

    // Delete kid and all related data
    public function destroy(Kid $kid)
    {
        // Make sure this kid belongs to the logged-in parent
        if ($kid->user_id !== Auth::id()) {
            abort(403);
        }

        // Delete related data first
        $kid->transactions()->delete();
        $kid->pointAdjustments()->delete();

        // Delete the kid
        $kid->delete();

        return redirect()->route('dashboard')->with('success', $kid->name . ' has been removed.');
    }

    // Create an invite for a kid
    public function createInvite(Kid $kid)
    {
        // Make sure this kid belongs to the logged-in parent
        if ($kid->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if invite already exists
        $invite = $kid->invite;

        if (!$invite || $invite->isExpired() || $invite->status === 'accepted') {
            // Create new invite
            $invite = \App\Models\Invite::createForKid($kid->id);
        }

        return response()->json([
            'success' => true,
            'token' => $invite->token,
            'expires_at' => $invite->expires_at->format('M d, Y'),
        ]);
    }

    // Send email invite
    public function sendEmailInvite(Request $request, Kid $kid)
    {
        // Make sure this kid belongs to the logged-in parent
        if ($kid->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        // Create or get existing invite
        $invite = $kid->invite;

        if (!$invite || $invite->isExpired() || $invite->status === 'accepted') {
            $invite = \App\Models\Invite::createForKid($kid->id, $request->email);
        } else {
            // Update email on existing invite
            $invite->update(['email' => $request->email]);
        }

        // Send the email
        try {
            Mail::to($request->email)->send(new KidInviteMail($kid, $invite));

            return response()->json([
                'success' => true,
                'message' => 'Email invite sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Generate QR code for invite
    public function generateQRCode(Kid $kid)
    {
        // Make sure this kid belongs to the logged-in parent
        if ($kid->user_id !== Auth::id()) {
            abort(403);
        }

        // Get or create invite
        $invite = $kid->invite;

        if (!$invite || $invite->isExpired() || $invite->status === 'accepted') {
            $invite = \App\Models\Invite::createForKid($kid->id);
        }

        // Generate invite URL
        $inviteUrl = url('/invite/' . $invite->token);

        // Generate QR code as SVG (convert to string)
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
            ->margin(2)
            ->generate($inviteUrl);

        return response()->json([
            'success' => true,
            'qrCode' => (string) $qrCode,  // Cast to string
            'inviteUrl' => $inviteUrl,
            'expiresAt' => $invite->expires_at->format('F j, Y')
        ]);
    }

    // Show invite registration page
    public function showInvite($token)
    {
        // Find the invite by token
        $invite = \App\Models\Invite::where('token', $token)->first();

        // Check if invite exists
        if (!$invite) {
            return view('invite.invalid', ['message' => 'This invite link is invalid.']);
        }

        // Check if invite is expired
        if ($invite->isExpired()) {
            return view('invite.invalid', ['message' => 'This invite has expired.']);
        }

        // Check if invite is already accepted
        if ($invite->status === 'accepted') {
            return view('invite.invalid', ['message' => 'This invite has already been used.']);
        }

        // Get the kid
        $kid = $invite->kid;

        return view('invite.register', compact('kid', 'invite'));
    }

    // Accept invite and create kid account
    public function acceptInvite(Request $request, $token)
    {
        // Find and validate invite
        $invite = \App\Models\Invite::where('token', $token)->first();

        if (!$invite || $invite->isExpired() || $invite->status === 'accepted') {
            return back()->with('error', 'Invalid or expired invite.');
        }

        // Validate form
        $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'unique:kids,username',
                'regex:/^[a-zA-Z0-9._-]+$/'
            ],
            'password' => 'required|string|min:4|confirmed',
            'color' => 'required|string',
        ], [
            'username.regex' => 'Username can only contain letters, numbers, periods, dashes, and underscores.',
            'username.unique' => 'This username is already taken. Please choose another one.',
            'username.min' => 'Username must be at least 3 characters.',
            'password.min' => 'Password must be at least 4 characters.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        // Update kid with username and password
        $kid = $invite->kid;
        $kid->update([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'password_plaintext' => $request->password,
            'color' => $request->color,
        ]);

        // Mark invite as accepted
        $invite->markAsAccepted();

        // Log the kid in using kid guard
        Auth::guard('kid')->login($kid);

        // Redirect to kid dashboard (placeholder for now)
        return redirect('/kid/dashboard')->with('success', 'Welcome to AllowanceLab, ' . $kid->name . '!');
    }

    // Check if username is available
    public function checkUsername(Request $request)
    {
        $username = $request->input('username');

        // Check format
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            return response()->json([
                'available' => false,
                'message' => 'Username can only contain letters, numbers, periods, dashes, and underscores.'
            ]);
        }

        // Check if taken
        $exists = Kid::where('username', $username)->exists();

        if ($exists) {
            return response()->json([
                'available' => false,
                'message' => 'This username is already taken.'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Username is available!'
        ]);
    }

    // Change kid's username
    public function changeUsername(Request $request, Kid $kid)
    {
        // Make sure this kid belongs to the logged-in parent
        if ($kid->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'unique:kids,username,' . $kid->id,
                'regex:/^[a-zA-Z0-9._-]+$/'
            ],
        ], [
            'username.regex' => 'Username can only contain letters, numbers, periods, dashes, and underscores.',
            'username.unique' => 'This username is already taken.',
        ]);

        $oldUsername = $kid->username;
        $kid->update(['username' => $request->username]);

        // Send email notification if kid has email
        if ($kid->email) {
            try {
                Mail::to($kid->email)->send(new \App\Mail\UsernameChangedMail($kid, Auth::user(), $oldUsername, $request->username));
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Log::error('Failed to send username change email: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Username changed successfully',
        ]);
    }

    // Reset kid's password
    public function resetPassword(Request $request, Kid $kid)
    {
        // Make sure this kid belongs to the logged-in parent
        if ($kid->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'password' => 'required|string|min:4',
        ]);

        $kid->update([
            'password' => Hash::make($request->password),
            'password_plaintext' => $request->password,
        ]);

        // Send email notification if kid has email
        if ($kid->email) {
            try {
                Mail::to($kid->email)->send(new \App\Mail\PasswordResetMail($kid, Auth::user()));
            } catch (\Exception $e) {
                // Log error but don't fail the request
                \Log::error('Failed to send password reset email: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }

}