<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Kid;

class KidController extends Controller
{
    // Store a new kid
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:kids,username',
            'password' => 'required|string|min:4',
            'birthday' => 'required|date',
            'avatar' => 'required|string',
            'color' => 'required|string',
            'allowance_amount' => 'required|numeric|min:0',
            'points_enabled' => 'boolean',
        ]);

        Kid::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'birthday' => $request->birthday,
            'avatar' => $request->avatar,
            'color' => $request->color,
            'allowance_amount' => $request->allowance_amount,
            'points_enabled' => $request->points_enabled ?? true,
            'balance' => 0,
            'points' => 10,
        ]);

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
}