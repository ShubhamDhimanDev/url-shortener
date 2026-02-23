<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'confirmed', Password::min(8)],
            'account_type' => ['required', 'string', 'in:individual,team'],
            'team_name'    => ['nullable', 'string', 'max:100', 'required_if:account_type,team'],
        ];
    }

    public function messages(): array
    {
        return [
            'team_name.required_if' => 'A team name is required when registering a team account.',
        ];
    }
}
