<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use App\Models\User;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\Goal;
use App\Models\Wish;
use App\Models\PushSubscription;

class AdminController extends Controller
{
    public function index()
    {
        $now = now();

        // Last signup — most recent across parents and kids
        $lastParentSignup = User::max('created_at');
        $lastKidSignup    = Kid::max('created_at');
        $lastSignup       = collect([$lastParentSignup, $lastKidSignup])
                                ->filter()
                                ->max();

        $stats = [
            // Accounts
            'total_parents'         => User::count(),
            'total_kids'            => Kid::count(),
            'total_families'        => Family::count(),
            'last_signup'           => $lastSignup ? \Carbon\Carbon::parse($lastSignup) : null,

            // New signups — last 7 / 30 days (combined parents + kids)
            'new_accounts_7d'       => User::where('created_at', '>=', $now->copy()->subDays(7))->count()
                                     + Kid::where('created_at',  '>=', $now->copy()->subDays(7))->count(),
            'new_accounts_30d'      => User::where('created_at', '>=', $now->copy()->subDays(30))->count()
                                     + Kid::where('created_at',  '>=', $now->copy()->subDays(30))->count(),

            // Activity — last 7 days
            'parents_active_7d'     => User::where('last_login_at', '>=', $now->copy()->subDays(7))->count(),
            'kids_active_7d'        => Kid::where('last_login_at',  '>=', $now->copy()->subDays(7))->count(),

            // Activity — last 30 days
            'parents_active_30d'    => User::where('last_login_at', '>=', $now->copy()->subDays(30))->count(),
            'kids_active_30d'       => Kid::where('last_login_at',  '>=', $now->copy()->subDays(30))->count(),

            // Money — three distinct views
            'total_balance'         => Kid::sum('balance'),
            'total_spent'           => Transaction::where('type', 'withdrawal')->sum('amount'),
            'total_deposited'       => Transaction::where('type', 'deposit')->sum('amount'),
            'total_transactions'    => Transaction::count(),

            // Goals & Wishes
            'total_goals'           => Goal::count(),
            'active_goals'          => Goal::where('status', 'active')->count(),
            'goals_saved_amount'    => Goal::whereIn('status', ['active', 'ready_to_redeem', 'pending_redemption'])->sum('current_amount'),
            'total_wishes'          => Wish::count(),

            // Push
            'push_subscriptions'    => PushSubscription::count(),
        ];

        return view('admin.index', compact('stats'));
    }
}
