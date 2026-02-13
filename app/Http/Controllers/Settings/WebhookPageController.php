<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class WebhookPageController extends Controller
{
    public function __construct()
    {
        abort_unless(feature_enabled('webhooks', auth()->user()), 404);
    }

    public function __invoke(): Response
    {
        return Inertia::render('Settings/Webhooks', [
            'available_events' => config('webhooks.outgoing.events', []),
        ]);
    }
}
