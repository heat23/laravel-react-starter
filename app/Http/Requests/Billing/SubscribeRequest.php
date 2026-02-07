<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\Billing\Concerns\HasPriceValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscribeRequest extends FormRequest
{
    use HasPriceValidation;

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
            'price_id' => ['required', 'string', Rule::in($this->allowedPriceIds())],
            'payment_method' => ['sometimes', 'string'],
            'coupon' => ['sometimes', 'nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
