<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
    }

    public function rules(): array
    {
        return [
            'plan_id'              => ['required', 'integer', 'exists:plans,id'],
            'status'               => ['required', 'string', 'in:trialing,active,past_due,cancelled,expired'],
            'current_period_start' => ['nullable', 'date'],
            'current_period_end'   => ['nullable', 'date'],
            'ends_at'              => ['nullable', 'date'],
        ];
    }
}
