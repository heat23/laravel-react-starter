<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class AdminUserIndexRequest extends AdminListRequest
{
    protected function allowedSorts(): array
    {
        return ['name', 'email', 'created_at', 'last_login_at', 'is_admin'];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'admin' => ['nullable', 'in:0,1'],
            'verified' => ['nullable', 'in:0,1'],
            'status' => ['nullable', 'string', Rule::in(['active', 'deactivated', 'all'])],
        ]);
    }
}
