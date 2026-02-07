<?php

namespace App\Http\Requests\TwoFactor;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorChallengeRequest extends FormRequest
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
            'code' => ['nullable', 'string', 'regex:/^\d{6}$/'],
            'recovery_code' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('code') && ! $this->filled('recovery_code')) {
                $validator->errors()->add('code', 'A verification code or recovery code is required.');
            }
        });
    }
}
