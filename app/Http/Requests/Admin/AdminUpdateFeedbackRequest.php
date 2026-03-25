<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['open', 'in_review', 'resolved', 'declined'])],
            'priority' => ['sometimes', 'string', Rule::in(['low', 'medium', 'high'])],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'roadmap_entry_id' => ['sometimes', 'nullable', 'integer', 'exists:roadmap_entries,id'],
        ];
    }
}
