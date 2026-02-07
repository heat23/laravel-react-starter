<?php

namespace App\Http\Requests\TwoFactor;

use Illuminate\Foundation\Http\FormRequest;

class DisableTwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }
}
