<?php

namespace App\Listeners\Links;

use App\Events\Links\LinkClicked;
use App\Jobs\RecordLinkClickJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Responds to the LinkClicked event by dispatching a queued job
 * to record the click details asynchronously.
 *
 * Using a listener here keeps the event dispatch in RedirectController thin
 * and allows other listeners to hook into LinkClicked in the future
 * (e.g. real-time analytics streams) without touching the controller.
 */
class RecordLinkClick implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Route to the analytics queue so click recording workers can be
     * scaled independently of the general notification workers.
     */
    public string $queue = 'analytics';

    public function handle(LinkClicked $event): void
    {
        RecordLinkClickJob::dispatch(
            $event->link,
            $event->ipAddress,
            $event->userAgent,
            $event->referrerUrl,
            $event->utmParams,
            $event->honeypotHeader,
        );
    }
}
