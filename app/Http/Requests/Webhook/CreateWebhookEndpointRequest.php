<?php

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;

class CreateWebhookEndpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validEvents = config('webhooks.outgoing.events', []);

        return [
            'url' => ['required', 'url:https,http', 'max:2048'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['required', 'string', 'in:'.implode(',', $validEvents)],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
