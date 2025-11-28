<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KidDashboardController extends Controller
{
    // Kid records a deposit
    public function recordDeposit(Request $request)
    {
        $kid = Auth::guard('kid')->user();

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'required|string|max:255'
        ]);

        $kid->balance += $request->amount;
        $kid->save();

        $kid->transactions()->create([
            'type' => 'deposit',
            'amount' => $request->amount,
            'description' => $request->note,
            'initiated_by' => 'kid'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Deposit recorded successfully',
            'new_balance' => $kid->balance
        ]);
    }

    // Kid records a spend
    public function recordSpend(Request $request)
    {
        $kid = Auth::guard('kid')->user();

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'required|string|max:255'
        ]);

        $kid->balance -= $request->amount;
        $kid->save();

        $kid->transactions()->create([
            'type' => 'spend',
            'amount' => $request->amount,
            'description' => $request->note,
            'initiated_by' => 'kid'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Spending recorded successfully',
            'new_balance' => $kid->balance
        ]);
    }
}