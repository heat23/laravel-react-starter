<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminReorderRoadmapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:500'],
            'items.*.id' => ['required', 'integer', 'exists:roadmap_entries,id'],
            'items.*.status' => ['required', 'string', Rule::in(['planned', 'in_progress', 'completed'])],
            'items.*.display_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
