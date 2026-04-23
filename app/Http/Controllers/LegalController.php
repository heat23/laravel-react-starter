<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function terms(): Response
    {
        return Inertia::render('Public/Legal/Terms');
    }

    public function privacy(): Response
    {
        return Inertia::render('Public/Legal/Privacy');
    }

    public function about(): Response
    {
        return Inertia::render('Public/About');
    }
}
