<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminSubscriptionIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:active,trialing,past_due,canceled,incomplete,incomplete_expired'],
            'tier' => ['nullable', 'string', 'in:pro,team,enterprise'],
            'sort' => ['nullable', 'string', 'in:created_at,stripe_status,user_name,quantity'],
            'dir' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }
}
