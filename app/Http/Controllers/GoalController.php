<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\GoalTransaction;
use App\Models\Kid;
use App\Models\Transaction;
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

        // Calculate total allocated percentage for active goals
        $totalAllocated = $activeGoals->sum('auto_allocation_percentage');
        $remainingAllocation = 100 - $totalAllocated;

        return view('goals.index', compact('kid', 'activeGoals', 'completedGoals', 'totalAllocated', 'remainingAllocation'));
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
            ->whereIn('status', ['active', 'ready_to_redeem', 'pending_redemption'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pastGoals = $kid->goals()
            ->with(['goalTransactions.performedBy'])
            ->where('status', 'redeemed')
            ->orderBy('redeemed_at', 'desc')
            ->get();

        // Calculate total allocated percentage for active goals
        $totalAllocated = $activeGoals->sum('auto_allocation_percentage');
        $remainingAllocation = 100 - $totalAllocated;

        return view('goals.parent-index', compact('kid', 'activeGoals', 'pastGoals', 'totalAllocated', 'remainingAllocation'));
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

        // Validate total auto-allocation doesn't exceed 100%
        if ($request->auto_allocation_percentage && $request->auto_allocation_percentage > 0) {
            $existingAllocation = $kid->goals()
                ->whereIn('status', ['active', 'ready_to_redeem'])
                ->sum('auto_allocation_percentage');

            $totalAllocation = $existingAllocation + $request->auto_allocation_percentage;

            if ($totalAllocation > 100) {
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Total auto-allocation would exceed 100%. Currently allocated: ' . number_format($existingAllocation, 0) . '%'
                    ], 400);
                }
                return back()->withErrors(['auto_allocation_percentage' => 'Total auto-allocation would exceed 100%. Currently allocated: ' . number_format($existingAllocation, 0) . '%']);
            }
        }

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

        $goal = Goal::create([
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

        // Check if this is an AJAX request
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Goal created successfully!',
                'goal' => $goal,
                'redirect' => route('kid.goals.index')
            ]);
        }

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

        // Validate total auto-allocation doesn't exceed 100%
        if ($request->auto_allocation_percentage && $request->auto_allocation_percentage > 0) {
            $existingAllocation = $kid->goals()
                ->whereIn('status', ['active', 'ready_to_redeem'])
                ->sum('auto_allocation_percentage');

            $totalAllocation = $existingAllocation + $request->auto_allocation_percentage;

            if ($totalAllocation > 100) {
                return back()->withErrors(['auto_allocation_percentage' => 'Total auto-allocation would exceed 100%. Currently allocated: ' . number_format($existingAllocation, 0) . '%']);
            }
        }

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

        $goal = Goal::create([
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

        // Check if this is an AJAX request
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Goal created successfully!',
                'goal' => $goal,
                'redirect' => route('parent.goals.index', $kid)
            ]);
        }

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
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'error' => 'This goal cannot be edited because it is ' . ($goal->status === 'redeemed' ? 'completed' : 'pending redemption') . '.'
                ], 403);
            }
            return back()->with('error', 'This goal cannot be edited because it is ' . ($goal->status === 'redeemed' ? 'completed' : 'pending redemption') . '.');
        }

        // Return JSON for AJAX requests (from parent modal)
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'title' => $goal->title,
                'description' => $goal->description,
                'product_url' => $goal->product_url,
                'target_amount' => $goal->target_amount,
                'auto_allocation_percentage' => $goal->auto_allocation_percentage,
                'current_amount' => $goal->current_amount,
                'photo_path' => $goal->photo_path,
            ]);
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

        // Validate total auto-allocation doesn't exceed 100% (excluding this goal's current allocation)
        if ($request->auto_allocation_percentage && $request->auto_allocation_percentage > 0) {
            $existingAllocation = $goal->kid->goals()
                ->whereIn('status', ['active', 'ready_to_redeem'])
                ->where('id', '!=', $goal->id) // Exclude current goal
                ->sum('auto_allocation_percentage');

            $totalAllocation = $existingAllocation + $request->auto_allocation_percentage;

            if ($totalAllocation > 100) {
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Total auto-allocation would exceed 100%. Currently allocated: ' . number_format($existingAllocation, 0) . '%'
                    ], 400);
                }
                return back()->withErrors(['auto_allocation_percentage' => 'Total auto-allocation would exceed 100%. Currently allocated: ' . number_format($existingAllocation, 0) . '%']);
            }
        }

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
     * Remove the specified goal and return funds to main account
     */
    public function destroy(Request $request, Goal $goal)
    {
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

        // Prevent deletion of redeemed goals
        if ($goal->status === 'redeemed') {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete a redeemed goal. It is a permanent record.'
                ], 400);
            }
            return back()->with('error', 'Cannot delete a redeemed goal. It is a permanent record.');
        }

        $kidId = $goal->kid_id;

        DB::transaction(function () use ($goal, $isParent) {
            $kid = $goal->kid;
            $goalTitle = $goal->title;
            $fundsToReturn = $goal->current_amount;

            // Return funds to main account if there are any
            if ($fundsToReturn > 0) {
                $kid->balance += $fundsToReturn;
                $kid->save();

                // Create transaction record in main ledger
                Transaction::create([
                    'kid_id' => $kid->id,
                    'type' => 'deposit',
                    'amount' => $fundsToReturn,
                    'description' => 'Goal deleted, funds returned: ' . $goalTitle,
                    'category' => 'goal_transfer',
                    'initiated_by' => $isParent ? 'parent' : 'kid',
                ]);
            }

            // Delete photo if exists
            if ($goal->photo_path) {
                Storage::disk('public')->delete($goal->photo_path);
            }

            $goal->delete();
        });

        // Handle AJAX requests
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Goal deleted successfully! Funds have been returned to the account.'
            ]);
        }

        // Handle regular form submissions
        if (Auth::guard('kid')->check()) {
            return redirect()->route('kid.goals.index')->with('success', 'Goal deleted successfully! Funds have been returned to your account.');
        } else {
            return redirect()->route('parent.goals.index', $kidId)->with('success', 'Goal deleted successfully! Funds have been returned to the account.');
        }
    }

    /**
     * Get the fund form HTML for modal (parent only)
     */
    public function getFundForm(Goal $goal)
    {
        // Verify parent has access to this goal
        $familyIds = Auth::user()->families()->pluck('families.id');
        if (!$familyIds->contains($goal->family_id)) {
            abort(403, 'Unauthorized access to this goal.');
        }

        $kid = $goal->kid;

        $html = '
            <div class="goal-fund-form">
                <div class="goal-fund-info">
                    <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #1a1a1a;">' . htmlspecialchars($goal->title) . '</h3>
                    <p style="margin: 0 0 16px 0; color: #666; font-size: 14px;">
                        Progress: $' . number_format($goal->current_amount, 2) . ' of $' . number_format($goal->target_amount, 2) . '
                    </p>
                    <p style="margin: 0 0 16px 0; color: #666; font-size: 14px;">
                        <strong>' . htmlspecialchars($kid->name) . '\'s Available Balance:</strong> $' . number_format($kid->balance, 2) . '
                    </p>
                </div>
                <form action="' . route('parent.goals.add-funds', $goal) . '" method="POST" id="goalFundForm">
                    ' . csrf_field() . '
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Amount to Add</label>
                        <input type="number"
                               step="0.01"
                               min="0.01"
                               max="' . $kid->balance . '"
                               name="amount"
                               class="form-input"
                               style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;"
                               placeholder="0.00"
                               required>
                    </div>
                    <button type="submit"
                            class="submit-btn"
                            style="width: 100%; padding: 14px; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                        Add Funds to Goal
                    </button>
                </form>
            </div>
        ';

        return response($html);
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
            $isParent = false;
        } else {
            $familyIds = Auth::user()->families()->pluck('families.id');
            if (!$familyIds->contains($goal->family_id)) {
                abort(403, 'Unauthorized access to this goal.');
            }
            $performedById = Auth::id();
            $isParent = true;
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

        // Validate amount doesn't exceed what's needed to complete the goal
        $amountNeeded = $goal->target_amount - $goal->current_amount;
        if ($request->amount > $amountNeeded) {
            return response()->json([
                'success' => false,
                'message' => 'You can only add $' . number_format($amountNeeded, 2) . ' to complete this goal.'
            ], 400);
        }

        DB::transaction(function () use ($goal, $kid, $request, $performedById, $isParent) {
            // Deduct from kid's main balance
            $kid->balance -= $request->amount;
            $kid->save();

            // Add to goal
            $goal->current_amount += $request->amount;

            // Determine new status if goal is complete
            if ($goal->status === 'active' && $goal->current_amount >= $goal->target_amount) {
                // If parent added funds, go straight to pending_redemption
                // If kid added funds, go to ready_to_redeem (they need to request redemption)
                $goal->status = $isParent ? 'pending_redemption' : 'ready_to_redeem';
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

            // Create main transaction ledger entry
            Transaction::create([
                'kid_id' => $kid->id,
                'type' => 'spend',
                'amount' => $request->amount,
                'description' => 'Transferred to goal: ' . $goal->title,
                'category' => 'goal_transfer',
                'initiated_by' => $isParent ? 'parent' : 'kid',
            ]);
        });

        $refreshedGoal = $goal->fresh();
        $refreshedKid = $kid->fresh();

        return response()->json([
            'success' => true,
            'message' => 'Funds added successfully!',
            'new_balance' => $refreshedKid->balance,
            'goal_current_amount' => $refreshedGoal->current_amount,
            'goal_target_amount' => $refreshedGoal->target_amount,
            'goal_status' => $refreshedGoal->status,
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
            $isParent = false;
        } else {
            $familyIds = Auth::user()->families()->pluck('families.id');
            if (!$familyIds->contains($goal->family_id)) {
                abort(403, 'Unauthorized access to this goal.');
            }
            $performedById = Auth::id();
            $isParent = true;
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

        DB::transaction(function () use ($goal, $kid, $request, $performedById, $isParent) {
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

            // Create main transaction ledger entry
            Transaction::create([
                'kid_id' => $kid->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'description' => 'Withdrawn from goal: ' . $goal->title,
                'category' => 'goal_transfer',
                'initiated_by' => $isParent ? 'parent' : 'kid',
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
            // Create redemption transaction (funds stay locked in goal)
            GoalTransaction::create([
                'goal_id' => $goal->id,
                'kid_id' => $kid->id,
                'family_id' => $goal->family_id,
                'amount' => 0, // No funds transferred
                'transaction_type' => 'redemption',
                'description' => 'Goal redeemed - item purchased',
                'performed_by_user_id' => Auth::id(),
                'created_at' => now(),
            ]);

            // Mark goal as redeemed (funds remain locked in goal)
            $goal->status = 'redeemed';
            $goal->redeemed_at = now();
            $goal->redeemed_by_user_id = Auth::id();
            $goal->save();
        });

        return redirect()->route('parent.goals.index', $kid)->with('success', 'Goal redeemed successfully! ' . $kid->name . ' can now enjoy their purchase.');
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
            // Create redemption transaction (funds stay locked in goal)
            GoalTransaction::create([
                'goal_id' => $goal->id,
                'kid_id' => $kid->id,
                'family_id' => $goal->family_id,
                'amount' => 0, // No funds transferred
                'transaction_type' => 'redemption',
                'description' => 'Goal redemption approved by parent',
                'performed_by_user_id' => Auth::id(),
                'created_at' => now(),
            ]);

            // Mark goal as redeemed (funds remain locked in goal)
            $goal->status = 'redeemed';
            $goal->redeemed_at = now();
            $goal->redeemed_by_user_id = Auth::id();
            $goal->save();
        });

        return redirect()->route('parent.goals.index', $kid)->with('success', 'Redemption approved! ' . $kid->name . ' can now enjoy their purchase.');
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
