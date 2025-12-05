<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KidAuthController;
use App\Http\Controllers\KidController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\KidDashboardController;

// Landing page
Route::get('/', function () {
    return view('welcome');
});
// Kid invite acceptance
Route::get('/invite/{token}', [KidController::class, 'showInvite'])->name('invite.show');
Route::post('/invite/{token}', [KidController::class, 'acceptInvite'])->name('invite.accept');
// Check username availability
Route::post('/check-username', [KidController::class, 'checkUsername'])->name('check.username');


// Family invite acceptance (public routes)
Route::get('/family/accept/{token}', [App\Http\Controllers\FamilyInviteController::class, 'show'])->name('family.accept-invite');
Route::post('/family/accept/{token}', [App\Http\Controllers\FamilyInviteController::class, 'accept'])->name('family.process-invite');

// Parent routes (protected by 'auth' middleware)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $kids = $user->accessibleKids()->sortBy('birthday');
        return view('parent.dashboard', compact('user', 'kids'));
    })->name('dashboard');

    // Manage Family
    Route::get('/manage-family', [App\Http\Controllers\ManageFamilyController::class, 'index'])->name('manage-family');
    // Family invitation and management routes
    Route::post('/family/invite', [App\Http\Controllers\ManageFamilyController::class, 'sendInvite'])->name('family.invite');
    Route::delete('/family/member/{user}', [App\Http\Controllers\ManageFamilyController::class, 'removeMember'])->name('family.remove-member');
    Route::post('/family/invite/{invite}/resend', [App\Http\Controllers\ManageFamilyController::class, 'resendInvite'])->name('family.resend-invite');
    Route::delete('/family/invite/{invite}', [App\Http\Controllers\ManageFamilyController::class, 'cancelInvite'])->name('family.cancel-invite');
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
    Route::get('/kids/{kid}/transactions', [KidController::class, 'getTransactions'])->name('kids.transactions');
    // Manage Kid route
    Route::get('/kids/{kid}/manage', [KidController::class, 'manage'])->name('kids.manage');
    // Update kid
    Route::patch('/kids/{kid}', [KidController::class, 'update'])->name('kids.update');
    // Delete kid
    Route::delete('/kids/{kid}', [KidController::class, 'destroy'])->name('kids.destroy');
    // Invite routes
    Route::post('/kids/{kid}/create-invite', [KidController::class, 'createInvite'])->name('kids.create-invite');
    Route::post('/kids/{kid}/send-email-invite', [KidController::class, 'sendEmailInvite'])->name('kids.send-email-invite');
    // Generate QR Code
    Route::get('/kids/{kid}/qr-code', [KidController::class, 'generateQRCode'])->name('kids.qr-code');
    // Username and password management
    Route::post('/kids/{kid}/change-username', [KidController::class, 'changeUsername'])->name('kids.change-username');
    Route::post('/kids/{kid}/reset-password', [KidController::class, 'resetPassword'])->name('kids.reset-password');
});

// Kid authentication routes
Route::prefix('kid')->name('kid.')->group(function () {
    Route::get('/login', [KidAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [KidAuthController::class, 'login']);
    Route::post('/logout', [KidAuthController::class, 'logout'])->name('logout');

    // Kid dashboard (protected)
    Route::middleware('auth:kid')->group(function () {
        Route::get('/dashboard', [KidAuthController::class, 'dashboard'])->name('dashboard');

        // Kid-initiated transactions
        Route::post('/deposit', [KidDashboardController::class, 'recordDeposit'])->name('deposit');
        Route::post('/spend', [KidDashboardController::class, 'recordSpend'])->name('spend');

        // Add this new route:
        Route::get('/profile', [KidAuthController::class, 'profile'])->name('profile');
        Route::patch('/update-color', [KidAuthController::class, 'updateColor'])->name('update-color');
    });
});

require __DIR__ . '/auth.php';