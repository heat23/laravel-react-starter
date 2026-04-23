<?php

namespace App\Http\Requests\Admin;

class AdminSessionIndexRequest extends AdminListRequest
{
    protected function allowedSorts(): array
    {
        return ['last_activity', 'ip_address'];
    }
}
