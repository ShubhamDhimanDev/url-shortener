<?php

namespace App\Console\Commands;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sync subscription statuses by calling the payment gateway API.
 *
 * Useful for catching webhooks that were missed or delayed.
 * Only processes active/trialing/past_due subscriptions that have a
 * gateway subscription ID so free/manual subscriptions are untouched.
 *
 * Schedule: daily at 02:00 (see routes/console.php).
 *
 * Usage:
 *   php artisan subscriptions:sync
 *   php artisan subscriptions:sync --dry-run
 */
class SyncSubscriptionStatuses extends Command
{
    protected $signature = 'subscriptions:sync
                            {--dry-run : Report status differences without writing to the database}
                            {--chunk=100 : Number of subscriptions to process per batch}';

    protected $description = 'Sync subscription statuses with the payment gateway (catches missed webhooks).';

    public function __construct(private readonly PaymentGatewayInterface $gateway)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun    = (bool) $this->option('dry-run');
        $chunkSize = (int)  $this->option('chunk');

        $query = Subscription::query()
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->whereNotNull('gateway_subscription_id');

        $total = $query->count();

        if ($total === 0) {
            $this->info('No subscriptions to sync.');
            return self::SUCCESS;
        }

        $this->info("Syncing {$total} subscription(s)." . ($dryRun ? ' [DRY RUN]' : ''));

        $updated = 0;
        $errors  = 0;

        $query->chunkById($chunkSize, function ($subscriptions) use ($dryRun, &$updated, &$errors) {
            foreach ($subscriptions as $subscription) {
                try {
                    $gatewayStatus = $this->gateway->getSubscriptionStatus(
                        $subscription->gateway_subscription_id
                    );

                    // Normalise gateway status to our internal enum values.
                    $mappedStatus = $this->mapStatus($gatewayStatus);

                    if ($mappedStatus === $subscription->status) {
                        continue; // Nothing to update.
                    }

                    $this->line(
                        "  [{$subscription->ulid}] {$subscription->status} → {$mappedStatus}"
                        . ($dryRun ? ' (skipped)' : '')
                    );

                    if (! $dryRun) {
                        $subscription->update(['status' => $mappedStatus]);
                    }

                    $updated++;
                } catch (\Throwable $e) {
                    $errors++;
                    $this->warn("  ⚠  Failed to sync subscription #{$subscription->id}: {$e->getMessage()}");
                    Log::warning('subscriptions:sync error', [
                        'subscription_id' => $subscription->id,
                        'error'           => $e->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Done. Updated: {$updated}, Errors: {$errors}.");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Map gateway-specific status strings to our internal enum values:
     * trialing | active | past_due | cancelled | expired
     */
    private function mapStatus(string $gatewayStatus): string
    {
        return match (strtolower($gatewayStatus)) {
            'created', 'authenticated'          => 'trialing',
            'active'                            => 'active',
            'pending', 'halted'                 => 'past_due',
            'cancelled', 'canceled', 'paused'   => 'cancelled',
            'completed', 'expired'              => 'expired',
            default                             => 'past_due',
        };
    }
}
