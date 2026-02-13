<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminFeatureFlagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
