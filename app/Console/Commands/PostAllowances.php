<?php

namespace App\Console\Commands;

use App\Models\Kid;
use Illuminate\Console\Command;

class PostAllowances extends Command
{
    protected $signature = 'allowance:post';
    protected $description = 'Post weekly allowances for all kids on their designated day';

    public function handle()
    {
        $today = now()->format('l'); // Get day name (e.g., 'Monday')
        $todayLower = strtolower($today);

        // Get all kids whose allowance day is today
        $kids = Kid::where('allowance_day', $todayLower)
            ->whereNotNull('username') // Only active accounts
            ->get();

        $posted = 0;
        $denied = 0;

        foreach ($kids as $kid) {
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

                $posted++;
                $this->info("✓ Posted $" . number_format($kid->allowance_amount, 2) . " allowance to {$kid->name}");
            } else {
                // Deny allowance due to insufficient points
                $kid->transactions()->create([
                    'type' => 'deposit',
                    'amount' => 0,
                    'description' => 'Allowance not earned - insufficient points',
                    'initiated_by' => 'parent'
                ]);

                $denied++;
                $this->warn("✗ Denied allowance to {$kid->name} (0 points)");
            }
        }

        $this->info("\nAllowance posting complete: {$posted} posted, {$denied} denied");
        return 0;
    }
}