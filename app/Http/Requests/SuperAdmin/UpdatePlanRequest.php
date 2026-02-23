<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_yearly'  => ['required', 'numeric', 'min:0'],
            'currency'      => ['nullable', 'string', 'size:3'],
            'trial_days'    => ['nullable', 'integer', 'min:0'],
            'is_active'     => ['boolean'],
            'is_public'     => ['boolean'],
            'features'      => ['nullable', 'array'],
            'features.*'    => ['nullable', 'string', 'max:255'],
        ];
    }
}
