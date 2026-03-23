<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRoadmapIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(['planned', 'in_progress', 'completed'])],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
