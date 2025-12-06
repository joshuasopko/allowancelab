<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Auto-detect and save timezone if not set
        $user = Auth::user();
        if (empty($user->timezone) && $request->has('timezone')) {
            $user->timezone = $request->input('timezone');
            $user->save();
        }

        // If PWA mode and remember me is checked, extend the remember cookie to 6 months
        if ($request->has('pwa') || $request->input('remember')) {
            $isPWA = $request->has('pwa') ||
                     $request->header('X-PWA-Mode') === '1' ||
                     ($request->hasHeader('User-Agent') &&
                      (str_contains($request->header('User-Agent'), 'Mobile') ||
                       str_contains($request->header('User-Agent'), 'Android') ||
                       str_contains($request->header('User-Agent'), 'iPhone')));

            if ($isPWA && $request->input('remember')) {
                // Extend remember cookie to 6 months for PWA users
                $recaller = Auth::guard()->getRecallerName();
                $value = request()->cookie($recaller);

                if ($value) {
                    cookie()->queue($recaller, $value, 60 * 24 * 180); // 180 days = 6 months
                }
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
