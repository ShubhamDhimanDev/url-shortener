<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\Subscriptions\TrialEndingNotification;
use Illuminate\Console\Command;

/**
 * Notify users (and team owners) whose free trial ends within 3 days.
 *
 * Schedule: daily at 09:00 (see routes/console.php).
 *
 * Usage:
 *   php artisan subscriptions:trial-reminders
 *   php artisan subscriptions:trial-reminders --dry-run
 */
class SendTrialEndingReminders extends Command
{
    protected $signature = 'subscriptions:trial-reminders
                            {--days=3 : Number of days before trial end to send the reminder}
                            {--dry-run : List affected subscriptions without sending notifications}';

    protected $description = 'Send trial-ending reminder notifications to subscribers whose trial expires within N days.';

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $dryRun = (bool) $this->option('dry-run');

        $subscriptions = Subscription::query()
            ->with(['subscribable', 'plan'])
            ->where('status', 'trialing')
            ->whereBetween('trial_ends_at', [now(), now()->addDays($days)->endOfDay()])
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No trial subscriptions ending soon.');
            return self::SUCCESS;
        }

        $this->info("Found {$subscriptions->count()} trial subscription(s) ending within {$days} day(s)." . ($dryRun ? ' [DRY RUN]' : ''));

        foreach ($subscriptions as $subscription) {
            $notifiable = $subscription->subscribable;

            if (! $notifiable) {
                $this->warn("  ⚠  Skipping subscription #{$subscription->id} — subscribable not found.");
                continue;
            }

            // For a Team subscription, notify the owner.
            if ($notifiable instanceof \App\Models\Team) {
                $notifiable = $notifiable->owner;
            }

            if (! $notifiable || ! method_exists($notifiable, 'notify')) {
                continue;
            }

            $this->line("  → Notifying {$notifiable->email} (trial ends {$subscription->trial_ends_at->toDateString()})");

            if (! $dryRun) {
                $notifiable->notify(new TrialEndingNotification($subscription));
            }
        }

        $this->info($dryRun ? 'Dry run complete — no notifications sent.' : 'Reminders dispatched to queue.');

        return self::SUCCESS;
    }
}
