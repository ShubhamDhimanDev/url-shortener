<?php

namespace App\Console\Commands;

use App\Events\Links\LinkExpired;
use App\Models\Link;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Soft-delete links whose `expires_at` timestamp has passed.
 *
 * Schedule: every hour (or daily at midnight — see routes/console.php).
 *
 * Usage:
 *   php artisan links:expire
 *   php artisan links:expire --dry-run
 */
class ProcessExpiredLinks extends Command
{
    protected $signature = 'links:expire
                            {--dry-run : List expired links without deleting them}
                            {--chunk=200 : Number of links to process per batch}';

    protected $description = 'Soft-delete links that have passed their expiry date and fire the LinkExpired event.';

    public function handle(): int
    {
        $dryRun    = (bool) $this->option('dry-run');
        $chunkSize = (int)  $this->option('chunk');

        $query = Link::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->where('is_active', true)
            ->whereNull('deleted_at');

        $total = $query->count();

        if ($total === 0) {
            $this->info('No expired links found.');
            return self::SUCCESS;
        }

        $this->info("Found {$total} expired link(s)." . ($dryRun ? ' [DRY RUN]' : ''));

        if ($dryRun) {
            $query->select(['id', 'ulid', 'short_code', 'expires_at'])
                ->each(fn (Link $link) => $this->line("  • [{$link->ulid}] {$link->short_code} expired {$link->expires_at->diffForHumans()}"));

            return self::SUCCESS;
        }

        $processed = 0;

        $query->chunkById($chunkSize, function ($links) use (&$processed) {
            foreach ($links as $link) {
                // Deactivate and soft-delete in one query, then fire event.
                DB::table('links')
                    ->where('id', $link->id)
                    ->update(['is_active' => false, 'updated_at' => now()]);

                $link->delete();

                event(new LinkExpired($link));

                $processed++;
            }
        });

        $this->info("Processed {$processed} expired link(s).");

        return self::SUCCESS;
    }
}
