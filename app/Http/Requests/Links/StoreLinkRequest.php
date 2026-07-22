<?php

namespace App\Http\Requests\Links;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('links.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'destination_url'           => ['required', 'url', 'max:2048'],
            'short_code'                => ['nullable', 'string', 'alpha_dash', 'min:3', 'max:64', Rule::unique('links', 'short_code')],
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
            'is_bot_protection_enabled' => ['boolean'],
            'tag_ids'                   => ['nullable', 'array'],
            'tag_ids.*'                 => ['integer', 'exists:link_tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination_url.required' => 'Please provide a destination URL.',
            'destination_url.url'      => 'The destination must be a valid URL.',
            'short_code.unique'        => 'This custom slug is already taken. Please choose another.',
            'expires_at.after'         => 'Expiry date must be in the future.',
        ];
    }
}
