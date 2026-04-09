<?php

namespace App\Http\Requests\Admin;

class AdminTokenExportRequest extends AdminTokenIndexRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }
}
