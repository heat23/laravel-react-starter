<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class AdminFeedbackIndexRequest extends AdminListRequest
{
    protected function allowedSorts(): array
    {
        return ['created_at', 'priority', 'status', 'type'];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'type' => ['nullable', 'string', Rule::in(['bug', 'feature', 'general'])],
            'status' => ['nullable', 'string', Rule::in(['open', 'in_review', 'resolved', 'declined'])],
        ]);
    }
}
