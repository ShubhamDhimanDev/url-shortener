<?php

namespace App\Jobs;

use App\Actions\Analytics\RecordClickAction;
use App\Models\Link;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordLinkClickJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /** Queue for high-volume click analytics processing */
    public string $queue = 'analytics';

    /** Retry up to 3 times before discarding */
    public int $tries = 3;

    public function __construct(
        public readonly Link    $link,
        public readonly string  $ipAddress,
        public readonly string  $userAgent,
        public readonly ?string $referrerUrl,
        public readonly array   $utmParams        = [],
        public readonly ?string $honeypotHeader   = null,
    ) {}

    public function handle(RecordClickAction $action): void
    {
        $action->execute(
            link:           $this->link,
            ipAddress:      $this->ipAddress,
            userAgent:      $this->userAgent,
            referrerUrl:    $this->referrerUrl,
            utmParams:      $this->utmParams,
            honeypotHeader: $this->honeypotHeader,
        );
    }
}
