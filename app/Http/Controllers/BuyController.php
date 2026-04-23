<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class BuyController extends Controller
{
    public function show(): Response
    {
        $appUrl = rtrim(config('app.url'), '/');

        return Inertia::render('App/Buy', [
            'templatePrice' => config('app.template_price', '$[YOUR_PRICE]'),
            'appUrl' => $appUrl,
            'canonicalUrl' => $appUrl.'/buy',
        ]);
    }
}
