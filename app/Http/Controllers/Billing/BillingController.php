<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Billing/Index');
    }
}
