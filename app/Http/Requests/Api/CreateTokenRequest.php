<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'abilities' => 'array|min:1|max:10',
            'abilities.*' => ['string', Rule::in(['read', 'write', 'delete'])],
            'expires_at' => 'nullable|date|after:now|before:'.now()->addYear()->format('Y-m-d'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'abilities.*.in' => 'Invalid permission. Allowed values: read, write, delete.',
            'abilities.min' => 'At least one permission must be specified.',
            'abilities.max' => 'You can assign up to 10 permissions per token.',
            'name.required' => 'Please provide a name for this token.',
            'expires_at.after' => 'Expiration date must be in the future.',
            'expires_at.before' => 'Expiration date cannot exceed 1 year from now.',
        ];
    }
}
