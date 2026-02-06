<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ChartsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard/Charts');
    }
}
