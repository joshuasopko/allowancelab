<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Kid;
use Carbon\Carbon;

class KidController extends Controller
{
    // Store a new kid
    public function store(Request $request)
    {
        // Validate the form data
        $request->validate([
            'name' => 'required|string|max:255',
            'birthday' => 'required|date',
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
            'name' => $request->name,
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
            'description' => $request->note
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
            'description' => $request->note
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

        $transactionsData = $transactions->get()->map(function ($t) {
            return [
                'date' => $t->created_at->format('M d, Y'),
                'type' => $t->type,
                'type_label' => ucfirst($t->type),
                'amount_display' => '$' . number_format($t->amount, 2),
                'note' => $t->description
            ];
        });

        // Get point adjustments
        $pointAdjustments = $kid->pointAdjustments()->latest();

        if ($days !== 'all') {
            $pointAdjustments->where('created_at', '>=', now()->subDays((int) $days));
        }

        if ($type === 'all' || $type === 'points') {
            $pointsData = $pointAdjustments->get()->map(function ($p) {
                return [
                    'date' => $p->created_at->format('M d, Y'),
                    'type' => 'points',
                    'type_label' => 'Points',
                    'amount_display' => ($p->points_change > 0 ? '+' : '') . $p->points_change . ' pts',
                    'amount_class' => $p->points_change > 0 ? 'points-add' : 'points-deduct',
                    'note' => $p->reason
                ];
            });

            $transactionsData = $transactionsData->concat($pointsData);
        }

        // Sort by date
        $transactionsData = $transactionsData->sortByDesc('date')->values();

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
            'birthday' => 'required|date',
            'color' => 'required|string',
            'allowance_amount' => 'required|numeric|min:0',
            'allowance_day' => 'required|string',
            'points_enabled' => 'nullable',
            'max_points' => 'nullable|integer|min:1|max:100',
        ]);

        $pointsEnabled = $request->has('points_enabled');

        $kid->update([
            'name' => $request->name,
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

        // TODO: Send actual email (we'll set this up later)
        // For now, just return success

        return response()->json([
            'success' => true,
            'message' => 'Email invite sent successfully',
        ]);
    }
}