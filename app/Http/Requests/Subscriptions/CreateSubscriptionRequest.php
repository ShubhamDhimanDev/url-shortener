<?php

namespace App\Http\Requests\Subscriptions;

use Illuminate\Foundation\Http\FormRequest;

class CreateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('billing.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'plan_id'          => ['required', 'integer', 'exists:plans,id'],
            'billing_cycle'    => ['nullable', 'in:monthly,yearly'],
            'payment_method'   => ['nullable', 'string'],
        ];
    }
}
