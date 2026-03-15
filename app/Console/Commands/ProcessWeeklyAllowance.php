<?php

namespace App\Console\Commands;

use App\Models\Kid;
use App\Models\Goal;
use App\Models\GoalTransaction;
use App\Notifications\AllowanceProcessedNotification;
use App\Notifications\AllowanceReceivedNotification;
use App\Notifications\AllowanceDeniedNotification;
use App\Notifications\GoalCompletedNotification;
use App\Notifications\PointsLowWarningNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessWeeklyAllowance extends Command
{
    protected $signature = 'allowance:process';
    protected $description = 'Process weekly allowances and reset points for kids on their designated day';

    public function handle()
    {
        $now = now();
        $today = $now->format('l'); // Get day name (e.g., 'Monday')
        $todayLower = strtolower($today);

        // ─── Points low warning check ────────────────────────────────────────────
        // Warn parents if a kid has ≤ 3 points and their allowance day is today.
        // Wrapped in try-catch so a transient DB hiccup doesn't abort the whole run.
        try {
            $this->checkPointsLowWarnings($now, $todayLower);
        } catch (\Throwable $e) {
            $this->error('[checkPointsLowWarnings] DB error: ' . $e->getMessage());
            \Log::error('[allowance:process] checkPointsLowWarnings failed: ' . $e->getMessage());
        }

        // Get all kids whose allowance day is today
        $kids = Kid::where('allowance_day', $todayLower)
            ->whereNotNull('username') // Only active accounts
            ->get();

        if ($kids->isEmpty()) {
            $this->info("No kids with allowance day '{$todayLower}'. Nothing to process.");
            return 0;
        }

        $allowancePosted = 0;
        $allowanceDenied = 0;
        $pointsReset = 0;
        $goalsAutoAllocated = 0;

        foreach ($kids as $kid) {
            $this->info("\n--- Processing {$kid->name} ---");

            // Step 1: Check points and post allowance if eligible
            $allowanceAwarded = false;
            if ($kid->points >= 1) {
                // Award allowance
                $kid->balance += $kid->allowance_amount;
                $kid->save();

                $kid->transactions()->create([
                    'type' => 'deposit',
                    'amount' => $kid->allowance_amount,
                    'description' => 'Weekly Allowance',
                    'initiated_by' => 'parent'
                ]);

                $allowancePosted++;
                $allowanceAwarded = true;
                $this->info("✓ Posted $" . number_format($kid->allowance_amount, 2) . " allowance (had {$kid->points} points)");

                // Notify kid: allowance received
                try {
                    $kid->notify(new AllowanceReceivedNotification((float) $kid->allowance_amount));
                } catch (\Throwable $e) {
                    \Log::error("[allowance:process] Kid notification failed for {$kid->name}: " . $e->getMessage());
                }

                // Notify parents: allowance processed (awarded)
                foreach ($kid->familyParents() as $parent) {
                    try {
                        $parent->notify(new AllowanceProcessedNotification($kid, true, (float) $kid->allowance_amount));
                    } catch (\Throwable $e) {
                        \Log::error("[allowance:process] Parent notification failed for {$parent->email}: " . $e->getMessage());
                    }
                }
            } else {
                // Deny allowance due to insufficient points
                $kid->transactions()->create([
                    'type' => 'deposit',
                    'amount' => 0,
                    'description' => 'Allowance not earned - insufficient points',
                    'initiated_by' => 'parent'
                ]);

                $allowanceDenied++;
                $this->warn("✗ Denied allowance (had {$kid->points} points)");

                // Notify kid: allowance denied
                try {
                    $kid->notify(new AllowanceDeniedNotification((int) $kid->points, (int) $kid->max_points));
                } catch (\Throwable $e) {
                    \Log::error("[allowance:process] Kid denied notification failed for {$kid->name}: " . $e->getMessage());
                }

                // Notify parents: allowance processed (denied)
                foreach ($kid->familyParents() as $parent) {
                    try {
                        $parent->notify(new AllowanceProcessedNotification($kid, false));
                    } catch (\Throwable $e) {
                        \Log::error("[allowance:process] Parent notification failed for {$parent->email}: " . $e->getMessage());
                    }
                }
            }

            // Step 1.5: Process goal auto-allocations (only if allowance was awarded)
            if ($allowanceAwarded) {
                $activeGoals = Goal::where('kid_id', $kid->id)
                    ->where('status', 'active')
                    ->whereNotNull('auto_allocation_percentage')
                    ->where('auto_allocation_percentage', '>', 0)
                    ->get();

                foreach ($activeGoals as $goal) {
                    $allocationAmount = ($kid->allowance_amount * $goal->auto_allocation_percentage) / 100;

                    // Ensure we don't allocate more than the kid's current balance
                    $kid->refresh(); // Get fresh balance
                    if ($kid->balance >= $allocationAmount && $allocationAmount > 0) {
                        DB::transaction(function () use ($goal, $kid, $allocationAmount) {
                            // Deduct from kid's balance
                            $kid->balance -= $allocationAmount;
                            $kid->save();

                            // Add to goal
                            $goal->current_amount += $allocationAmount;
                            $goal->save();

                            // Create goal transaction
                            GoalTransaction::create([
                                'goal_id' => $goal->id,
                                'kid_id' => $kid->id,
                                'family_id' => $goal->family_id,
                                'amount' => $allocationAmount,
                                'transaction_type' => 'auto_allocation',
                                'description' => 'Weekly auto-allocation (' . number_format($goal->auto_allocation_percentage, 0) . '%)',
                                'performed_by_user_id' => null,
                                'created_at' => now(),
                            ]);
                        });

                        $goalsAutoAllocated++;
                        $this->info("  ✓ Auto-allocated $" . number_format($allocationAmount, 2) . " to '{$goal->title}' (" . number_format($goal->auto_allocation_percentage, 0) . "%)");

                        // Notify parents if goal is now complete
                        $goal->refresh();
                        if ($goal->current_amount >= $goal->target_amount) {
                            foreach ($kid->familyParents() as $parent) {
                                try {
                                    $parent->notify(new GoalCompletedNotification($kid, $goal));
                                } catch (\Throwable $e) {
                                    \Log::error("[allowance:process] Goal completed notification failed for {$parent->email}: " . $e->getMessage());
                                }
                            }
                        }
                    } else {
                        $this->warn("  ✗ Insufficient balance for auto-allocation to '{$goal->title}'");
                    }
                }
            }

            // Step 2: Reset points (regardless of whether allowance was posted)
            if ($kid->points_enabled) {
                $oldPoints = $kid->points;
                $kid->points = $kid->max_points;
                $kid->save();

                $kid->pointAdjustments()->create([
                    'points_change' => $kid->max_points - $oldPoints,
                    'previous_points' => $oldPoints,
                    'new_points' => $kid->max_points,
                    'reason' => 'Weekly points reset'
                ]);

                $pointsReset++;
                $this->info("✓ Reset points: {$oldPoints} → {$kid->max_points}");
            } else {
                $this->info("⊘ Points disabled for {$kid->name}, skipping reset");
            }
        }

        $this->info("\n=== Weekly Allowance Processing Complete ===");
        $this->info("Allowances: {$allowancePosted} posted, {$allowanceDenied} denied");
        $this->info("Goals: {$goalsAutoAllocated} auto-allocations processed");
        $this->info("Points: {$pointsReset} kids reset");

        return 0;
    }

    /**
     * Warn parents when a kid has ≤ 3 points and their allowance day is today.
     * Fires once per hourly run so parents get the notification during the day.
     */
    private function checkPointsLowWarnings(\Carbon\Carbon $now, string $todayLower): void
    {
        $kidsWithLowPoints = Kid::where('allowance_day', $todayLower)
            ->whereNotNull('username')
            ->where('points_enabled', true)
            ->where('points', '<=', 3)
            ->where('points', '>=', 0) // exclude already-zero (will be denied, separate notification)
            ->get();

        foreach ($kidsWithLowPoints as $kid) {
            $allowanceDayName = ucfirst($kid->allowance_day);

            foreach ($kid->familyParents() as $parent) {
                try {
                    $parent->notify(new PointsLowWarningNotification($kid, $allowanceDayName));
                } catch (\Throwable $e) {
                    \Log::error("[allowance:process] Low points notification failed for {$parent->email}: " . $e->getMessage());
                }
            }

            $this->warn("⚡ Low points warning sent for {$kid->name} ({$kid->points}/{$kid->max_points} pts)");
        }
    }
}
