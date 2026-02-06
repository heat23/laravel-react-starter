<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Pricing');
    }
}
