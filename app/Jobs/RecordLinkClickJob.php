<?php

namespace App\Jobs;

use App\Actions\Analytics\RecordClickAction;
use App\Models\Link;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordLinkClickJob implements ShouldQueue
{
    use Queueable;

    /** Retry up to 3 times before discarding */
    public int $tries = 3;

    public function __construct(
        public readonly Link    $link,
        public readonly string  $ipAddress,
        public readonly string  $userAgent,
        public readonly ?string $referrerUrl,
        public readonly array   $utmParams        = [],
        public readonly ?string $honeypotHeader   = null,
    ) {
        $this->onQueue('analytics');
    }

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
