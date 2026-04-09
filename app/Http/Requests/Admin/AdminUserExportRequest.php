<?php

namespace App\Http\Requests\Admin;

class AdminUserExportRequest extends AdminUserIndexRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }
}
