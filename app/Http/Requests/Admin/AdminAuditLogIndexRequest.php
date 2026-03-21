<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminAuditLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'event' => ['nullable', 'string', 'max:64'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'ip' => ['nullable', 'string', 'max:45'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'in:event,created_at'],
            'dir' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ];
    }
}
