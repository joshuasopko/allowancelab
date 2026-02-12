<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Kid;

class KidAuthController extends Controller
{
    // Show kid login form
    public function showLogin()
    {
        return view('kid.login');
    }

    // Handle kid login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $kid = Kid::where('username', $request->username)->first();

        if ($kid && Hash::check($request->password, $kid->password)) {
            $kid->update(['last_login_at' => now()]);

            // Check if remember me is requested
            $remember = $request->boolean('remember');
            Auth::guard('kid')->login($kid, $remember);

            // If PWA mode and remember me, extend cookie to 6 months
            if ($remember && $request->has('pwa')) {
                $recaller = Auth::guard('kid')->getRecallerName();
                $value = request()->cookie($recaller);

                if ($value) {
                    cookie()->queue($recaller, $value, 60 * 24 * 180); // 180 days = 6 months
                }
            }

            return redirect()->route('kid.dashboard');
        }

        return back()->withErrors([
            'username' => 'Invalid username or password.',
        ]);
    }

    // Handle kid logout
    public function logout(Request $request)
    {
        Auth::guard('kid')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('kid.login');
    }

    // Show kid dashboard
    public function dashboard()
    {
        $kid = Auth::guard('kid')->user();

        // Get transactions (deposits and spends)
        $transactions = $kid->transactions()->latest()->get()->map(function ($t) {
            return [
                'type' => $t->type,
                'amount' => (float) $t->amount,
                'note' => $t->description ?? '',
                'date' => $t->created_at->format('Y-m-d'),
                'timestamp' => $t->created_at->timestamp, // ADD THIS
                'initiated_by' => $t->initiated_by ?? 'parent',
                'parentInitiated' => ($t->initiated_by ?? 'parent') === 'parent'
            ];
        });

        // Get point adjustments if points are enabled
        $pointAdjustments = collect([]);
        if ($kid->points_enabled) {
            $pointAdjustments = $kid->pointAdjustments()->latest()->get()->map(function ($p) {
                return [
                    'type' => 'points',
                    'amount' => $p->points_change,
                    'note' => $p->reason ?? '',
                    'date' => $p->created_at->format('Y-m-d'),
                    'timestamp' => $p->created_at->timestamp, // ADD THIS
                    'initiated_by' => 'parent',
                    'parentInitiated' => true
                ];
            });
        }

        // Combine and sort by timestamp (newest first)
        $allTransactions = $transactions->concat($pointAdjustments)
            ->sortByDesc('timestamp') // CHANGE THIS
            ->values();

        // Get active goals (limit to 3 for dashboard)
        $activeGoals = $kid->goals()
            ->whereIn('status', ['active', 'ready_to_redeem', 'pending_redemption'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Get active wishes (limit to 5 for dashboard)
        $activeWishes = $kid->wishes()
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('kid.dashboard', [
            'kid' => $kid,
            'transactions' => $allTransactions,
            'activeGoals' => $activeGoals,
            'activeWishes' => $activeWishes
        ]);
    }

    public function profile()
    {
        $kid = Auth::guard('kid')->user();
        $parent = $kid->family->owner; // Get the family owner

        return view('kid.profile', compact('kid', 'parent'));
    }

    public function updateColor(Request $request)
    {
        $request->validate([
            'color' => 'required|string'
        ]);

        $kid = Auth::guard('kid')->user();
        $kid->color = $request->color;
        $kid->save();

        return redirect()->route('kid.profile')->with('success', 'Theme color updated!');
    }

}