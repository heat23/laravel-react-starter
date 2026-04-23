<?php

namespace App\Http\Requests\Webhook;

use App\Rules\PublicHttpsUrl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validEvents = config('webhooks.outgoing.events', []);

        return [
            'url' => ['sometimes', 'url:https,http', 'max:2048', new PublicHttpsUrl],
            'events' => ['sometimes', 'array', 'min:1'],
            'events.*' => ['required', 'string', 'in:'.implode(',', $validEvents)],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
