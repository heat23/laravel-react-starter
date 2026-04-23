<?php

namespace App\Http\Requests\Admin;

class AdminEmailSendLogIndexRequest extends AdminListRequest
{
    protected function allowedSorts(): array
    {
        return ['sent_at', 'sequence_type', 'email_number'];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'sequence_type' => ['nullable', 'string', 'max:60'],
        ]);
    }
}
