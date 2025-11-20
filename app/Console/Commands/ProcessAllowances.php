<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kid;
use App\Models\Transaction;
use Carbon\Carbon;

class ProcessAllowances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'allowances:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process weekly allowances and reset points for kids';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get current date/time
        $now = Carbon::now();

        // Find all kids whose next_allowance_date has passed or is now
        $kids = Kid::where('next_allowance_date', '<=', $now)
            ->where('allowance_amount', '>', 0) // Only kids with allowances set up
            ->whereNotNull('next_allowance_date') // Must have a scheduled date
            ->get();

        $processed = 0;

        foreach ($kids as $kid) {
            // Check if kid has enough points to earn allowance
            if ($kid->points > 0) {
                // Step 1: Create allowance transaction as a deposit
                Transaction::create([
                    'kid_id' => $kid->id,
                    'type' => 'deposit',
                    'amount' => $kid->allowance_amount,
                    'description' => 'Weekly Allowance',
                ]);

                // Step 2: Update kid's balance
                $kid->balance += $kid->allowance_amount;

                $this->info("Processed allowance for {$kid->name}: \${$kid->allowance_amount}");
            } else {
                // Create a $0 transaction to show in ledger that allowance was not earned
                Transaction::create([
                    'kid_id' => $kid->id,
                    'type' => 'adjustment',
                    'amount' => 0.00,
                    'description' => 'Allowance not earned - insufficient points',
                ]);

                $this->info("Skipped allowance for {$kid->name}: Not enough points");
            }

            // Step 3: Record points reset in point adjustments table (ALWAYS happens)
            \App\Models\PointAdjustment::create([
                'kid_id' => $kid->id,
                'points_change' => $kid->max_points,
                'reason' => 'Weekly points reset',
            ]);

            // Step 4: Reset points to max_points (regardless of whether allowance was paid)
            $kid->points = $kid->max_points;
        }

        $this->info("Processed allowances for {$processed} kids.");

        return 0;
    }
}