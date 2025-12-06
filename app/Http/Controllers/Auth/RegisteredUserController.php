<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Family;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'timezone' => ['nullable', 'string', 'max:255'],
        ]);

        // Build user data
        $userData = [
            'name' => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        // Add timezone only if column exists
        try {
            $userData['timezone'] = $request->timezone ?? 'America/Chicago';
            $user = User::create($userData);
        } catch (\Exception $e) {
            // If timezone column doesn't exist, create user without it
            unset($userData['timezone']);
            $user = User::create($userData);
            \Log::info('User created without timezone: ' . $e->getMessage());
        }

        // Create a family for this user
        $family = Family::create([
            'name' => $request->first_name . "'s Family",
            'owner_user_id' => $user->id,
        ]);

        // Attach user to the family
        $user->families()->attach($family->id, ['role' => 'owner']);

        // Send welcome email
        Mail::to($user->email)->send(new WelcomeEmail($user));

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
