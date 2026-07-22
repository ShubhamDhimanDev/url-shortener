<?php

namespace App\Events\Links;

use App\Models\Link;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LinkExpired
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Link $link) {}
}
