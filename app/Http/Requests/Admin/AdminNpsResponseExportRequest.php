<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminNpsResponseExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', Rule::in(['promoter', 'passive', 'detractor'])],
            'survey_trigger' => ['nullable', 'string', 'max:60'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
