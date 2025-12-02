<?php

namespace App\Console\Commands;

use App\Models\Kid;
use Illuminate\Console\Command;

class ResetPoints extends Command
{
    protected $signature = 'points:reset';
    protected $description = 'Reset points to max for all kids on their allowance day';

    public function handle()
    {
        $today = now()->format('l');
        $todayLower = strtolower($today);

        // Get all kids whose allowance day is today
        $kids = Kid::where('allowance_day', $todayLower)
            ->where('points_enabled', true)
            ->whereNotNull('username')
            ->get();

        $reset = 0;

        foreach ($kids as $kid) {
            $oldPoints = $kid->points;
            $kid->points = $kid->max_points;
            $kid->save();

            $kid->pointAdjustments()->create([
                'points_change' => $kid->max_points - $oldPoints,
                'previous_points' => $oldPoints,
                'new_points' => $kid->max_points,
                'reason' => 'Weekly points reset'
            ]);

            $reset++;
            $this->info("✓ Reset {$kid->name}'s points: {$oldPoints} → {$kid->max_points}");
        }

        $this->info("\nPoints reset complete: {$reset} kids reset");
        return 0;
    }
}