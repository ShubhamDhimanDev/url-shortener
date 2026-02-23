<?php

namespace App\Actions\Links;

use App\Models\Link;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UpdateLinkAction
{
    /**
     * Update an existing link with the provided data.
     *
     * @param  array  $data  Validated data from UpdateLinkRequest
     */
    public function execute(Link $link, array $data): Link
    {
        $updates = Arr::except($data, ['password', 'tag_ids']);

        // Handle password changes
        if (array_key_exists('password', $data)) {
            if (! empty($data['password'])) {
                $updates['password']            = Hash::make($data['password']);
                $updates['is_password_protected'] = true;
            } else {
                // Password cleared
                $updates['password']            = null;
                $updates['is_password_protected'] = false;
            }
        }

        $link->update($updates);

        // Sync tags
        if (array_key_exists('tag_ids', $data)) {
            $link->tags()->sync($data['tag_ids'] ?? []);
        }

        return $link->refresh();
    }
}
