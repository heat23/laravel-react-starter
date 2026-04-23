<?php

namespace App\Http\Requests\Admin;

use App\Enums\AuditEvent;
use Illuminate\Validation\Rule;

class AdminAuditLogIndexRequest extends AdminListRequest
{
    protected function allowedSorts(): array
    {
        return ['event', 'created_at'];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'event' => ['nullable', 'string', Rule::in(array_column(AuditEvent::cases(), 'value'))],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'ip' => ['nullable', 'string', 'max:45'],
        ]);
    }
}
