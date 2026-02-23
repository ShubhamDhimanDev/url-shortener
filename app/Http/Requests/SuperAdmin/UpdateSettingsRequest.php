<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'settings'     => ['required', 'array'],
            'settings.*'   => ['array'],
            'settings.*.*' => ['nullable', 'string', 'max:65535'],
        ];
    }
}
