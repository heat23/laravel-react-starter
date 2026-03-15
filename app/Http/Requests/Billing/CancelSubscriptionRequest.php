<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class CancelSubscriptionRequest extends FormRequest
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
            'immediately' => ['sometimes', 'boolean'],
            'reason' => ['sometimes', 'nullable', 'string', 'in:too_expensive,switching_tools,no_longer_needed,missing_features,other'],
            'feedback' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
