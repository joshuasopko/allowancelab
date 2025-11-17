<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KidAuthController;
use App\Http\Controllers\KidController;
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
        $kids = $user->kids;
        return view('parent.dashboard', compact('user', 'kids'));
    })->name('dashboard');

    // Kid management
    Route::post('/kids', [App\Http\Controllers\KidController::class, 'store'])->name('kids.store');
    Route::patch('/kids/{kid}/balance', [App\Http\Controllers\KidController::class, 'updateBalance'])->name('kids.updateBalance');
    Route::patch('/kids/{kid}/points', [App\Http\Controllers\KidController::class, 'updatePoints'])->name('kids.updatePoints');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/kids/{kid}/deposit', [KidController::class, 'deposit'])->name('kids.deposit');
    Route::post('/kids/{kid}/spend', [KidController::class, 'spend'])->name('kids.spend');
    Route::post('/kids/{kid}/points', [KidController::class, 'adjustPoints'])->name('kids.points');
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