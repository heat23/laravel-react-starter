<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', Rule::in(['theme', 'timezone', 'onboarding_completed', 'sidebar_state'])],
            'value' => 'required|string|max:1024',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.in' => 'Invalid setting. Allowed: theme, timezone, onboarding_completed, sidebar_state.',
            'value.max' => 'Setting value must be 1024 characters or fewer.',
        ];
    }
}
