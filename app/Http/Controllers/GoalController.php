<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\GoalTransaction;
use App\Models\Kid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GoalController extends Controller
{
    /**
     * Display a listing of the kid's goals (kid view)
     */
    public function index()
    {
        $kid = Auth::guard('kid')->user();

        $activeGoals = $kid->goals()
            ->with('goalTransactions')
            ->whereIn('status', ['active', 'ready_to_redeem', 'pending_redemption'])
            ->orderBy('created_at', 'desc')
            ->get();

        $completedGoals = $kid->goals()
            ->with('goalTransactions')
            ->where('status', 'redeemed')
            ->orderBy('redeemed_at', 'desc')
            ->get();

        return view('goals.index', compact('kid', 'activeGoals', 'completedGoals'));
    }

    /**
     * Display goals for a specific kid (parent view)
     */
    public function parentIndex(Kid $kid)
    {
        // Verify parent has access to this kid
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($kid->family_id)) {
            abort(403, 'Unauthorized access to this kid.');
        }

        $activeGoals = $kid->goals()
            ->whereIn('status', ['active', 'ready_to_redeem'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pastGoals = $kid->goals()
            ->where('status', 'redeemed')
            ->orderBy('redeemed_at', 'desc')
            ->get();

        return view('goals.parent-index', compact('kid', 'activeGoals', 'pastGoals'));
    }

    /**
     * Show the form for creating a new goal (kid view)
     */
    public function create()
    {
        $kid = Auth::guard('kid')->user();
        return view('goals.create', compact('kid'));
    }

    /**
     * Store a newly created goal (kid creates)
     */
    public function store(Request $request)
    {
        $kid = Auth::guard('kid')->user();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'product_url' => 'nullable|url|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'target_amount' => 'required|numeric|min:0.01|max:999999.99',
            'auto_allocation_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('goal-photos', 'public');
        }

        $expectedCompletionDate = null;
        if ($request->auto_allocation_percentage && $request->auto_allocation_percentage > 0 && $kid->allowance_amount > 0) {
            $weeklyAllocation = ($kid->allowance_amount * $request->auto_allocation_percentage) / 100;
            if ($weeklyAllocation > 0) {
                $weeksRemaining = ceil($request->target_amount / $weeklyAllocation);
                $expectedCompletionDate = Carbon::now()->addWeeks($weeksRemaining);
            }
        }

        Goal::create([
            'family_id' => $kid->family_id,
            'kid_id' => $kid->id,
            'created_by_user_id' => null, // Kid created it
            'title' => $request->title,
            'description' => $request->description,
            'product_url' => $request->product_url,
            'photo_path' => $photoPath,
            'target_amount' => $request->target_amount,
            'current_amount' => 0,
            'auto_allocation_percentage' => $request->auto_allocation_percentage,
            'expected_completion_date' => $expectedCompletionDate,
            'status' => 'active',
        ]);

        return redirect()->route('kid.goals.index')->with('success', 'Goal created successfully!');
    }

    /**
     * Store a newly created goal (parent creates for kid)
     */
    public function parentStore(Request $request, Kid $kid)
    {
        // Verify parent has access to this kid
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($kid->family_id)) {
            abort(403, 'Unauthorized access to this kid.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'product_url' => 'nullable|url|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'target_amount' => 'required|numeric|min:0.01|max:999999.99',
            'auto_allocation_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('goal-photos', 'public');
        }

        $expectedCompletionDate = null;
        if ($request->auto_allocation_percentage && $request->auto_allocation_percentage > 0 && $kid->allowance_amount > 0) {
            $weeklyAllocation = ($kid->allowance_amount * $request->auto_allocation_percentage) / 100;
            if ($weeklyAllocation > 0) {
                $weeksRemaining = ceil($request->target_amount / $weeklyAllocation);
                $expectedCompletionDate = Carbon::now()->addWeeks($weeksRemaining);
            }
        }

        Goal::create([
            'family_id' => $kid->family_id,
            'kid_id' => $kid->id,
            'created_by_user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'product_url' => $request->product_url,
            'photo_path' => $photoPath,
            'target_amount' => $request->target_amount,
            'current_amount' => 0,
            'auto_allocation_percentage' => $request->auto_allocation_percentage,
            'expected_completion_date' => $expectedCompletionDate,
            'status' => 'active',
        ]);

        return redirect()->route('parent.goals.index', $kid)->with('success', 'Goal created successfully!');
    }

    /**
     * Display the specified goal
     */
    public function show(Goal $goal)
    {
        // Check if user is kid or parent with access
        if (Auth::guard('kid')->check()) {
            $kid = Auth::guard('kid')->user();
            if ($goal->kid_id !== $kid->id) {
                abort(403, 'Unauthorized access to this goal.');
            }
            $isParent = false;
        } else {
            $familyIds = Auth::user()->families()->pluck('families.id');
            if (!$familyIds->contains($goal->family_id)) {
                abort(403, 'Unauthorized access to this goal.');
            }
            $isParent = true;
        }

        $goal->load(['goalTransactions.performedBy']);

        return view('goals.show', compact('goal', 'isParent'));
    }

    /**
     * Show the form for editing the specified goal
     */
    public function edit(Goal $goal)
    {
        if (Auth::guard('kid')->check()) {
            $kid = Auth::guard('kid')->user();
            if ($goal->kid_id !== $kid->id) {
                abort(403, 'Unauthorized access to this goal.');
            }
        } else {
            $familyIds = Auth::user()->families()->pluck('families.id');
            if (!$familyIds->contains($goal->family_id)) {
                abort(403, 'Unauthorized access to this goal.');
            }
        }

        // Prevent editing of pending_redemption or redeemed goals
        if (in_array($goal->status, ['pending_redemption', 'redeemed'])) {
            return back()->with('error', 'This goal cannot be edited because it is ' . ($goal->status === 'redeemed' ? 'completed' : 'pending redemption') . '.');
        }

        return view('goals.edit', compact('goal'));
    }

    /**
     * Update the specified goal
     */
    public function update(Request $request, Goal $goal)
    {
        if (Auth::guard('kid')->check()) {
            $kid = Auth::guard('kid')->user();
            if ($goal->kid_id !== $kid->id) {
                abort(403, 'Unauthorized access to this goal.');
            }
        } else {
            $familyIds = Auth::user()->families()->pluck('families.id');
            if (!$familyIds->contains($goal->family_id)) {
                abort(403, 'Unauthorized access to this goal.');
            }
        }

        // Prevent updating of pending_redemption or redeemed goals
        if (in_array($goal->status, ['pending_redemption', 'redeemed'])) {
            return back()->with('error', 'This goal cannot be edited because it is ' . ($goal->status === 'redeemed' ? 'completed' : 'pending redemption') . '.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'product_url' => 'nullable|url|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'target_amount' => 'required|numeric|min:0.01|max:999999.99',
            'auto_allocation_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($goal->photo_path) {
                Storage::disk('public')->delete($goal->photo_path);
            }
            $goal->photo_path = $request->file('photo')->store('goal-photos', 'public');
        }

        $goal->title = $request->title;
        $goal->description = $request->description;
        $goal->product_url = $request->product_url;
        $goal->target_amount = $request->target_amount;
        $goal->auto_allocation_percentage = $request->auto_allocation_percentage;

        // Reset status to active if goal is no longer complete
        if ($goal->status === 'ready_to_redeem' && $goal->current_amount < $request->target_amount) {
            $goal->status = 'active';
        }

        // Recalculate expected completion date
        $expectedCompletionDate = null;
        if ($request->auto_allocation_percentage && $request->auto_allocation_percentage > 0 && $goal->kid->allowance_amount > 0) {
            $weeklyAllocation = ($goal->kid->allowance_amount * $request->auto_allocation_percentage) / 100;
            if ($weeklyAllocation > 0 && $goal->current_amount < $goal->target_amount) {
                $remaining = $goal->target_amount - $goal->current_amount;
                $weeksRemaining = ceil($remaining / $weeklyAllocation);
                $expectedCompletionDate = Carbon::now()->addWeeks($weeksRemaining);
            }
        }
        $goal->expected_completion_date = $expectedCompletionDate;

        $goal->save();

        // Check if this is an AJAX request
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Goal updated successfully!',
                'goal' => $goal
            ]);
        }

        if (Auth::guard('kid')->check()) {
            return redirect()->route('kid.goals.index')->with('success', 'Goal updated successfully!');
        } else {
            return redirect()->route('parent.goals.index', $goal->kid)->with('success', 'Goal updated successfully!');
        }
    }

    /**
     * Get goal data for editing (JSON response for modal)
     */
    public function getEditData(Goal $goal)
    {
        // Check if user is kid or parent with access
        if (Auth::guard('kid')->check()) {
            $kid = Auth::guard('kid')->user();
            if ($goal->kid_id !== $kid->id) {
                abort(403, 'Unauthorized access to this goal.');
            }
        } else {
            $familyIds = Auth::user()->families()->pluck('families.id');
            if (!$familyIds->contains($goal->family_id)) {
                abort(403, 'Unauthorized access to this goal.');
            }
        }

        return response()->json($goal);
    }

    /**
     * Remove the specified goal (only if no funds)
     */
    public function destroy(Goal $goal)
    {
        if (Auth::guard('kid')->check()) {
            $kid = Auth::guard('kid')->user();
            if ($goal->kid_id !== $kid->id) {
                abort(403, 'Unauthorized access to this goal.');
            }
        } else {
            $familyIds = Auth::user()->families()->pluck('families.id');
            if (!$familyIds->contains($goal->family_id)) {
                abort(403, 'Unauthorized access to this goal.');
            }
        }

        if ($goal->current_amount > 0) {
            return back()->with('error', 'Cannot delete a goal with funds. Please remove all funds first.');
        }

        // Delete photo if exists
        if ($goal->photo_path) {
            Storage::disk('public')->delete($goal->photo_path);
        }

        $goal->delete();

        if (Auth::guard('kid')->check()) {
            return redirect()->route('kid.goals.index')->with('success', 'Goal deleted successfully!');
        } else {
            return redirect()->route('parent.goals.index', $goal->kid)->with('success', 'Goal deleted successfully!');
        }
    }

    /**
     * Add funds to a goal (manual deposit)
     */
    public function addFunds(Request $request, Goal $goal)
    {
        if (Auth::guard('kid')->check()) {
            $kid = Auth::guard('kid')->user();
            if ($goal->kid_id !== $kid->id) {
                abort(403, 'Unauthorized access to this goal.');
            }
            $performedById = null;
        } else {
            $familyIds = Auth::user()->families()->pluck('families.id');
            if (!$familyIds->contains($goal->family_id)) {
                abort(403, 'Unauthorized access to this goal.');
            }
            $performedById = Auth::id();
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999.99',
        ]);

        $kid = $goal->kid;

        // Validate kid has sufficient balance
        if ($kid->balance < $request->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance in main account.'
            ], 400);
        }

        DB::transaction(function () use ($goal, $kid, $request, $performedById) {
            // Deduct from kid's main balance
            $kid->balance -= $request->amount;
            $kid->save();

            // Add to goal
            $goal->current_amount += $request->amount;

            // Set status to ready_to_redeem if goal is complete
            if ($goal->status === 'active' && $goal->current_amount >= $goal->target_amount) {
                $goal->status = 'ready_to_redeem';
            }

            $goal->save();

            // Create goal transaction
            GoalTransaction::create([
                'goal_id' => $goal->id,
                'kid_id' => $kid->id,
                'family_id' => $goal->family_id,
                'amount' => $request->amount,
                'transaction_type' => 'manual_deposit',
                'description' => 'Manual deposit to goal',
                'performed_by_user_id' => $performedById,
                'created_at' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Funds added successfully!',
            'new_goal_amount' => $goal->fresh()->current_amount,
            'new_balance' => $kid->fresh()->balance,
            'goal_status' => $goal->fresh()->status,
        ]);
    }

    /**
     * Remove funds from a goal (manual withdrawal)
     */
    public function removeFunds(Request $request, Goal $goal)
    {
        if (Auth::guard('kid')->check()) {
            $kid = Auth::guard('kid')->user();
            if ($goal->kid_id !== $kid->id) {
                abort(403, 'Unauthorized access to this goal.');
            }
            $performedById = null;
        } else {
            $familyIds = Auth::user()->families()->pluck('families.id');
            if (!$familyIds->contains($goal->family_id)) {
                abort(403, 'Unauthorized access to this goal.');
            }
            $performedById = Auth::id();
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:999999.99',
        ]);

        $kid = $goal->kid;

        // Validate goal has sufficient funds
        if ($goal->current_amount < $request->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient funds in goal.'
            ], 400);
        }

        DB::transaction(function () use ($goal, $kid, $request, $performedById) {
            // Remove from goal
            $goal->current_amount -= $request->amount;

            // Reset status to active if goal is no longer complete
            if ($goal->status === 'ready_to_redeem' && $goal->current_amount < $goal->target_amount) {
                $goal->status = 'active';
            }

            $goal->save();

            // Add to kid's main balance
            $kid->balance += $request->amount;
            $kid->save();

            // Create goal transaction
            GoalTransaction::create([
                'goal_id' => $goal->id,
                'kid_id' => $kid->id,
                'family_id' => $goal->family_id,
                'amount' => -$request->amount,
                'transaction_type' => 'manual_withdrawal',
                'description' => 'Manual withdrawal from goal',
                'performed_by_user_id' => $performedById,
                'created_at' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Funds removed successfully!',
            'new_goal_amount' => $goal->fresh()->current_amount,
            'new_balance' => $kid->fresh()->balance,
            'goal_status' => $goal->fresh()->status,
        ]);
    }

    /**
     * Kid requests redemption of a completed goal
     */
    public function requestRedemption(Goal $goal)
    {
        $kid = Auth::guard('kid')->user();

        // Verify kid owns this goal
        if ($goal->kid_id !== $kid->id) {
            abort(403, 'Unauthorized access to this goal.');
        }

        // Verify goal is active/ready_to_redeem and actually complete (current_amount >= target_amount)
        if (!in_array($goal->status, ['active', 'ready_to_redeem']) || $goal->current_amount < $goal->target_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Goal must be complete before requesting redemption.'
            ], 400);
        }

        // Update status to pending_redemption
        $goal->status = 'pending_redemption';
        $goal->save();

        // Create a goal transaction to record the redemption request
        GoalTransaction::create([
            'goal_id' => $goal->id,
            'kid_id' => $kid->id,
            'family_id' => $goal->family_id,
            'amount' => 0,
            'transaction_type' => 'redemption_requested',
            'description' => 'Kid requested goal redemption',
            'performed_by_user_id' => null,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Redemption requested! Your parent will be notified.',
            'goal_status' => $goal->status,
        ]);
    }

    /**
     * Redeem a completed goal (parent only)
     */
    public function redeem(Goal $goal)
    {
        // Verify parent has access
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($goal->family_id)) {
            abort(403, 'Unauthorized access to this goal.');
        }

        if (!$goal->isReadyToRedeem()) {
            return back()->with('error', 'Goal is not ready to redeem.');
        }

        $kid = $goal->kid;

        DB::transaction(function () use ($goal, $kid) {
            // Return all funds to kid's main balance
            $kid->balance += $goal->current_amount;
            $kid->save();

            // Create redemption transaction
            GoalTransaction::create([
                'goal_id' => $goal->id,
                'kid_id' => $kid->id,
                'family_id' => $goal->family_id,
                'amount' => -$goal->current_amount,
                'transaction_type' => 'redemption',
                'description' => 'Goal redeemed - funds returned to main balance',
                'performed_by_user_id' => Auth::id(),
                'created_at' => now(),
            ]);

            // Mark goal as redeemed
            $goal->current_amount = 0;
            $goal->status = 'redeemed';
            $goal->redeemed_at = now();
            $goal->redeemed_by_user_id = Auth::id();
            $goal->save();
        });

        return redirect()->route('parent.goals.index', $kid)->with('success', 'Goal redeemed successfully! Funds returned to main balance.');
    }

    /**
     * Approve a pending redemption request from a kid
     */
    public function approveRedemption(Goal $goal)
    {
        // Verify parent has access
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($goal->family_id)) {
            abort(403, 'Unauthorized access to this goal.');
        }

        if ($goal->status !== 'pending_redemption') {
            return back()->with('error', 'This goal does not have a pending redemption request.');
        }

        $kid = $goal->kid;

        DB::transaction(function () use ($goal, $kid) {
            // Return all funds to kid's main balance
            $kid->balance += $goal->current_amount;
            $kid->save();

            // Create redemption transaction
            GoalTransaction::create([
                'goal_id' => $goal->id,
                'kid_id' => $kid->id,
                'family_id' => $goal->family_id,
                'amount' => -$goal->current_amount,
                'transaction_type' => 'redemption',
                'description' => 'Goal redemption approved by parent',
                'performed_by_user_id' => Auth::id(),
                'created_at' => now(),
            ]);

            // Mark goal as redeemed
            $goal->current_amount = 0;
            $goal->status = 'redeemed';
            $goal->redeemed_at = now();
            $goal->redeemed_by_user_id = Auth::id();
            $goal->save();
        });

        return redirect()->route('parent.goals.index', $kid)->with('success', 'Redemption approved! Funds returned to ' . $kid->name . '\'s main balance.');
    }

    /**
     * Deny a pending redemption request from a kid
     */
    public function denyRedemption(Goal $goal)
    {
        // Verify parent has access
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($goal->family_id)) {
            abort(403, 'Unauthorized access to this goal.');
        }

        if ($goal->status !== 'pending_redemption') {
            return back()->with('error', 'This goal does not have a pending redemption request.');
        }

        $kid = $goal->kid;

        // Reset status to ready_to_redeem (since goal is still complete)
        $goal->status = 'ready_to_redeem';
        $goal->save();

        // Create transaction record for denial
        GoalTransaction::create([
            'goal_id' => $goal->id,
            'kid_id' => $kid->id,
            'family_id' => $goal->family_id,
            'amount' => 0,
            'transaction_type' => 'redemption_denied',
            'description' => 'Redemption request denied by parent',
            'performed_by_user_id' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->route('parent.goals.index', $kid)->with('success', 'Redemption denied. Goal remains active for ' . $kid->name . '.');
    }
}
