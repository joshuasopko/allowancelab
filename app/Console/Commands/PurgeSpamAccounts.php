<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeSpamAccounts extends Command
{
    protected $signature = 'accounts:purge-spam
        {--hours=48 : Only consider accounts created at least this many hours ago}
        {--force : Actually delete the matched accounts, no prompt (default is a dry run). Standard Laravel --force semantics: this alone authorizes the deletion, same as migrate --force.}';

    protected $description = 'List (or delete, with --force) parent accounts that never logged back in and never added a kid — the signature of a bot-created registration';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $force = (bool) $this->option('force');

        $candidates = User::query()
            ->where('is_admin', false)
            ->whereNull('last_login_at')
            ->where('created_at', '<=', now()->subHours($hours))
            ->whereDoesntHave('families.kids')
            ->with('ownedFamilies')
            ->orderBy('created_at')
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('No spam-like accounts found.');
            return self::SUCCESS;
        }

        $this->info("Found {$candidates->count()} account(s) created more than {$hours}h ago, never logged in, with no kids attached:");
        $this->table(
            ['ID', 'Email', 'Created At'],
            $candidates->map(fn (User $u) => [$u->id, $u->email, $u->created_at->toDateTimeString()])
        );

        if (!$force) {
            $this->newLine();
            $this->comment('Dry run only — nothing was deleted. Re-run with --force to delete these accounts (and their empty, kid-less families).');
            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($candidates as $user) {
            try {
                DB::transaction(function () use ($user) {
                    $user->delete(); // cascades to owned families -> family_members / kids (kid-less, verified above)
                });
                $deleted++;
            } catch (\Throwable $e) {
                $this->error("Failed to delete user #{$user->id} ({$user->email}): {$e->getMessage()}");
                \Log::error('[accounts:purge-spam] failed to delete user', ['id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Deleted {$deleted} of {$candidates->count()} account(s).");

        return self::SUCCESS;
    }
}
