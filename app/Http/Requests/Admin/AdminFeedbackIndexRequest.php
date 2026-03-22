<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminFeedbackIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', 'string', Rule::in(['bug', 'feature', 'general'])],
            'status' => ['nullable', 'string', Rule::in(['open', 'in_review', 'resolved', 'declined'])],
        ];
    }
}
