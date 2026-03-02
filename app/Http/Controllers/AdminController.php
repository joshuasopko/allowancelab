<?php

namespace App\Http\Controllers;

use App\Models\Kid;
use App\Models\User;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\Goal;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $now = now();

        $stats = [
            // Accounts
            'total_parents'         => User::count(),
            'total_kids'            => Kid::count(),
            'total_families'        => Family::count(),

            // Activity — last 7 days
            'parents_active_7d'     => User::where('last_login_at', '>=', $now->copy()->subDays(7))->count(),
            'kids_active_7d'        => Kid::where('last_login_at', '>=', $now->copy()->subDays(7))->count(),

            // Activity — last 30 days
            'parents_active_30d'    => User::where('last_login_at', '>=', $now->copy()->subDays(30))->count(),
            'kids_active_30d'       => Kid::where('last_login_at', '>=', $now->copy()->subDays(30))->count(),

            // New signups — last 7 / 30 days
            'new_parents_7d'        => User::where('created_at', '>=', $now->copy()->subDays(7))->count(),
            'new_parents_30d'       => User::where('created_at', '>=', $now->copy()->subDays(30))->count(),
            'new_kids_7d'           => Kid::where('created_at', '>=', $now->copy()->subDays(7))->count(),
            'new_kids_30d'          => Kid::where('created_at', '>=', $now->copy()->subDays(30))->count(),

            // Money on platform
            'total_money_tracked'   => Transaction::where('type', 'deposit')->sum('amount'),
            'total_transactions'    => Transaction::count(),

            // Features
            'total_goals'           => Goal::count(),
            'active_goals'          => Goal::where('status', 'active')->count(),
            'push_subscriptions'    => PushSubscription::count(),
        ];

        return view('admin.index', compact('stats'));
    }
}
