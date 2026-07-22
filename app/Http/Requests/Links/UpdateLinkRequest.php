<?php

namespace App\Http\Requests\Links;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        $link = $this->route('link');
        return $this->user()?->can('update', $link) ?? false;
    }

    public function rules(): array
    {
        $linkId = $this->route('link')?->id;

        return [
            'destination_url'           => ['sometimes', 'required', 'url', 'max:2048'],
            'short_code'                => ['nullable', 'string', 'alpha_dash', 'min:3', 'max:64', Rule::unique('links', 'short_code')->ignore($linkId)],
            'domain_id'                 => ['nullable', 'integer', 'exists:domains,id'],
            'title'                     => ['nullable', 'string', 'max:255'],
            'description'               => ['nullable', 'string', 'max:1000'],
            'og_image'                  => ['nullable', 'url', 'max:2048'],
            'password'                  => ['nullable', 'string', 'min:4', 'max:255'],
            'expires_at'                => ['nullable', 'date', 'after:now'],
            'utm_source'                => ['nullable', 'string', 'max:255'],
            'utm_medium'                => ['nullable', 'string', 'max:255'],
            'utm_campaign'              => ['nullable', 'string', 'max:255'],
            'utm_term'                  => ['nullable', 'string', 'max:255'],
            'utm_content'               => ['nullable', 'string', 'max:255'],
            'meta_pixel_id'             => ['nullable', 'string', 'max:50'],
            'google_tag_id'             => ['nullable', 'string', 'max:50'],
            'is_active'                 => ['boolean'],
            'is_bot_protection_enabled' => ['boolean'],
            'tag_ids'                   => ['nullable', 'array'],
            'tag_ids.*'                 => ['integer', 'exists:link_tags,id'],
        ];
    }
}
