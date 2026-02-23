<?php

namespace App\Events\Links;

use App\Models\Link;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LinkClicked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Link   $link,
        public readonly string $ipAddress,
        public readonly string $userAgent,
        public readonly ?string $referrerUrl,
        /** Raw query-string UTM values captured at click time */
        public readonly array  $utmParams = [],
        public readonly ?string $honeypotHeader = null,
    ) {}
}
