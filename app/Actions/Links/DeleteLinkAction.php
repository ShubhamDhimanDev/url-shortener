<?php

namespace App\Actions\Links;

use App\Events\Links\LinkDeleted;
use App\Models\Link;

class DeleteLinkAction
{
    public function execute(Link $link): void
    {
        $link->delete(); // Observer fires LinkDeleted event and clears cache
    }
}
