<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['bug', 'feature', 'general'])],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }
}
