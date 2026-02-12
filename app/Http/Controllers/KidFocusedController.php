<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use App\Models\Goal;
use App\Models\Wish;
use Illuminate\Http\Request;

class KidFocusedController extends Controller
{
    /**
     * Show kid-focused overview page
     */
    public function overview(Kid $kid)
    {
        // Verify parent has access to this kid
        $familyIds = auth()->user()->families()->pluck('families.id');
        if (!$familyIds->contains($kid->family_id)) {
            abort(403, 'Unauthorized access to this kid.');
        }

        // Get overview data
        $activeGoals = Goal::where('kid_id', $kid->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $pendingWishesCount = Wish::where('kid_id', $kid->id)
            ->where('status', 'pending_approval')
            ->count();

        $recentTransactions = $kid->transactions()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('kid-focused.overview', compact('kid', 'activeGoals', 'pendingWishesCount', 'recentTransactions'));
    }

    /**
     * Show kid-focused allowance page
     */
    public function allowance(Kid $kid)
    {
        // Verify parent has access to this kid
        $familyIds = auth()->user()->families()->pluck('families.id');
        if (!$familyIds->contains($kid->family_id)) {
            abort(403, 'Unauthorized access to this kid.');
        }

        // Get recent transactions
        $transactions = $kid->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('kid-focused.allowance', compact('kid', 'transactions'));
    }

    /**
     * Show kid-focused goals page
     */
    public function goals(Kid $kid)
    {
        // Verify parent has access to this kid
        $familyIds = auth()->user()->families()->pluck('families.id');
        if (!$familyIds->contains($kid->family_id)) {
            abort(403, 'Unauthorized access to this kid.');
        }

        // Get all goals for this kid
        $activeGoals = Goal::where('kid_id', $kid->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $completedGoals = Goal::where('kid_id', $kid->id)
            ->where('status', 'redeemed')
            ->orderBy('redeemed_at', 'desc')
            ->paginate(10);

        // Calculate total auto-allocation percentage used by active goals
        $totalAllocated = $activeGoals->sum('auto_allocation_percentage');

        return view('kid-focused.goals', compact('kid', 'activeGoals', 'completedGoals', 'totalAllocated'));
    }

    /**
     * Show kid-focused wishes page
     */
    public function wishes(Kid $kid)
    {
        // Verify parent has access to this kid
        $familyIds = auth()->user()->families()->pluck('families.id');
        if (!$familyIds->contains($kid->family_id)) {
            abort(403, 'Unauthorized access to this kid.');
        }

        // Get wishes organized by status
        $pendingWishes = Wish::where('kid_id', $kid->id)
            ->where('status', 'pending_approval')
            ->orderBy('requested_at', 'desc')
            ->get();

        $currentWishes = Wish::where('kid_id', $kid->id)
            ->whereIn('status', ['saved', 'approved'])
            ->orderBy('created_at', 'desc')
            ->get();

        $allWishes = Wish::where('kid_id', $kid->id)
            ->where('status', '!=', 'purchased')
            ->orderBy('created_at', 'desc')
            ->get();

        $redeemedWishes = Wish::where('kid_id', $kid->id)
            ->where('status', 'purchased')
            ->orderBy('purchased_at', 'desc')
            ->paginate(10);

        return view('kid-focused.wishes', compact('kid', 'pendingWishes', 'currentWishes', 'allWishes', 'redeemedWishes'));
    }
}
