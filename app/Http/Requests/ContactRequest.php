<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'not_regex:/[\r\n\0]/'],
            'email' => ['required', 'email', 'max:255', 'not_regex:/[\r\n\0]/'],
            'subject' => ['required', 'string', Rule::in([
                'General inquiry',
                'Enterprise pricing',
                'Bug report',
                'Feature request',
            ])],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }
}
