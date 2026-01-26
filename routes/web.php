<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KidAuthController;
use App\Http\Controllers\KidController;
use App\Http\Controllers\GoalController;
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

// Email verification (public route)
Route::get('/parent/verify-email/{token}', [App\Http\Controllers\ParentAccountController::class, 'verifyEmailChange'])->name('parent.account.verify-email');

// Parent routes (protected by 'auth' middleware)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $kids = $user->accessibleKids()->sortBy('birthday');

        // Check for pending redemption requests across all kids
        $pendingRedemptionCount = 0;
        $kidsWithPendingRedemptions = [];
        foreach ($kids as $kid) {
            $count = $kid->goals()->where('status', 'pending_redemption')->count();
            if ($count > 0) {
                $pendingRedemptionCount += $count;
                $kidsWithPendingRedemptions[] = $kid;
            }
        }

        return view('parent.dashboard', compact('user', 'kids', 'pendingRedemptionCount', 'kidsWithPendingRedemptions'));
    })->name('dashboard');

    // Manage Family
    // Parent Account
    Route::get('/parent/account', [App\Http\Controllers\ParentAccountController::class, 'index'])->name('parent.account');
    Route::patch('/parent/account/profile', [App\Http\Controllers\ParentAccountController::class, 'updateProfile'])->name('parent.account.update-profile');
    Route::post('/parent/account/email', [App\Http\Controllers\ParentAccountController::class, 'requestEmailChange'])->name('parent.account.request-email-change');
    Route::patch('/parent/account/password', [App\Http\Controllers\ParentAccountController::class, 'changePassword'])->name('parent.account.change-password');
    Route::patch('/parent/account/timezone', [App\Http\Controllers\ParentAccountController::class, 'updateTimezone'])->name('parent.account.update-timezone');
    Route::delete('/parent/account', [App\Http\Controllers\ParentAccountController::class, 'deleteAccount'])->name('parent.account.delete');

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

    // Parent goal management routes
    Route::get('/kids/{kid}/goals', [GoalController::class, 'parentIndex'])->name('parent.goals.index');
    Route::post('/kids/{kid}/goals', [GoalController::class, 'parentStore'])->name('parent.goals.store');
    Route::get('/goals/{goal}', [GoalController::class, 'show'])->name('parent.goals.show');
    Route::get('/goals/{goal}/edit', [GoalController::class, 'edit'])->name('parent.goals.edit');
    Route::put('/goals/{goal}', [GoalController::class, 'update'])->name('parent.goals.update');
    Route::delete('/goals/{goal}', [GoalController::class, 'destroy'])->name('parent.goals.destroy');
    Route::get('/goals/{goal}/fund-form', [GoalController::class, 'getFundForm'])->name('parent.goals.fund-form');
    Route::post('/goals/{goal}/add-funds', [GoalController::class, 'addFunds'])->name('parent.goals.add-funds');
    Route::post('/goals/{goal}/remove-funds', [GoalController::class, 'removeFunds'])->name('parent.goals.remove-funds');
    Route::post('/goals/{goal}/redeem', [GoalController::class, 'redeem'])->name('parent.goals.redeem');
    Route::post('/goals/{goal}/approve-redemption', [GoalController::class, 'approveRedemption'])->name('parent.goals.approve-redemption');
    Route::post('/goals/{goal}/deny-redemption', [GoalController::class, 'denyRedemption'])->name('parent.goals.deny-redemption');
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

        // Kid profile
        Route::get('/profile', [KidAuthController::class, 'profile'])->name('profile');
        Route::patch('/update-color', [KidAuthController::class, 'updateColor'])->name('update-color');

        // Kid goal routes (use kid prefix)
        Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
        Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
        Route::get('/goals/{goal}/edit-data', [GoalController::class, 'getEditData'])->name('goals.edit-data');
        Route::put('/goals/{goal}', [GoalController::class, 'update'])->name('goals.update');
        Route::delete('/goals/{goal}', [GoalController::class, 'destroy'])->name('goals.destroy');
        Route::post('/goals/{goal}/add-funds', [GoalController::class, 'addFunds'])->name('goals.add-funds');
        Route::post('/goals/{goal}/remove-funds', [GoalController::class, 'removeFunds'])->name('goals.remove-funds');
        Route::post('/goals/{goal}/request-redemption', [GoalController::class, 'requestRedemption'])->name('goals.request-redemption');
    });
});

require __DIR__ . '/auth.php';