<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KidAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Landing page
Route::get('/', function () {
    return view('welcome');
});

// Parent routes (protected by 'auth' middleware)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $kids = $user->kids; // Get all kids for this parent
        return view('parent.dashboard', compact('user', 'kids'));
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Kid authentication routes
Route::prefix('kid')->name('kid.')->group(function () {
    Route::get('/login', [KidAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [KidAuthController::class, 'login']);
    Route::post('/logout', [KidAuthController::class, 'logout'])->name('logout');

    // Kid dashboard (protected)
    Route::middleware('auth:kid')->group(function () {
        Route::get('/dashboard', function () {
            return view('kid.dashboard');
        })->name('dashboard');
    });
});

require __DIR__ . '/auth.php';