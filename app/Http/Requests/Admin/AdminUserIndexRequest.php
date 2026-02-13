<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'admin' => ['nullable', 'in:0,1'],
            'sort' => ['nullable', 'string', 'in:name,email,created_at,last_login_at,is_admin'],
            'dir' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
