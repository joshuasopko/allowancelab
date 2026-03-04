<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Transaction;
use App\Models\Wish;
use Illuminate\Support\Facades\Auth;

class FamilyHubController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $kids = $user->accessibleKids()->sortBy('name');
        $kidIds = $kids->pluck('id');

        // Stats bar aggregates
        $totalBalance    = $kids->sum('balance');
        $weeklyAllowance = $kids->sum('allowance_amount');

        $activeGoalsCount = Goal::whereIn('kid_id', $kidIds)
            ->whereIn('status', ['active', 'ready_to_redeem', 'pending_redemption'])
            ->count();

        $pendingWishesCount = Wish::whereIn('kid_id', $kidIds)
            ->where('status', 'pending_approval')
            ->count();

        // Recent activity — last 15 transactions across all kids
        $recentTransactions = Transaction::whereIn('kid_id', $kidIds)
            ->with('kid')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        // Active goals across all kids with kid eager loaded
        $activeGoals = Goal::whereIn('kid_id', $kidIds)
            ->whereIn('status', ['active', 'ready_to_redeem', 'pending_redemption'])
            ->with('kid')
            ->orderByRaw("CASE status WHEN 'pending_redemption' THEN 0 WHEN 'ready_to_redeem' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('family-hub.index', compact(
            'kids',
            'totalBalance',
            'weeklyAllowance',
            'activeGoalsCount',
            'pendingWishesCount',
            'recentTransactions',
            'activeGoals'
        ));
    }
}
