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
            Auth::guard('kid')->login($kid);
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
}