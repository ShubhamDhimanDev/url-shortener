<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('domains.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'domain' => [
                'required',
                'string',
                'max:253',
                Rule::unique('domains', 'domain'),
                'regex:/^([a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i',
            ],
            'type' => ['nullable', 'in:custom_subdomain,custom_domain'],
        ];
    }

    public function messages(): array
    {
        return [
            'domain.regex'  => 'Please enter a valid domain name (e.g. links.yourdomain.com).',
            'domain.unique' => 'This domain is already registered.',
        ];
    }
}
