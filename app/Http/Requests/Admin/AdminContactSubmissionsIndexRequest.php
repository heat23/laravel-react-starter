<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class AdminContactSubmissionsIndexRequest extends AdminListRequest
{
    protected function allowedSorts(): array
    {
        return ['created_at', 'status', 'name', 'email'];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'status' => ['nullable', 'string', Rule::in(['new', 'replied', 'spam'])],
        ]);
    }
}
